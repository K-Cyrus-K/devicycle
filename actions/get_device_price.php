<?php
require_once "../classes/DeviceLogic.php";

header('Content-Type: application/json');

$device_id = isset($_GET['device_id']) ? $_GET['device_id'] : null;
$query = isset($_GET['query']) ? $_GET['query'] : null;
$force = isset($_GET['force']) && $_GET['force'] == '1';

if (!$device_id || !$query) {
    echo json_encode(['error' => 'デバイスIDまたはクエリが不足しています']);
    exit;
}

// 1. キャッシュの確認 (forceフラグがない場合のみ)
if (!$force) {
    $cachedPrice = DeviceLogic::getDevicePrice($device_id);

    if ($cachedPrice) {
        $lastChecked = new DateTime($cachedPrice['last_checked']);
        $today = new DateTime();

        // 時間をリセットして日付のみで比較
        $lastChecked->setTime(0, 0, 0);
        $today->setTime(0, 0, 0);

            // キャッシュが今日取得されたものなら、そのまま返す
            if ($lastChecked == $today) {
                echo json_encode([
                    'source' => 'cache',
                    'data' => [
                        'avg_new_price' => $cachedPrice['avg_new_price'],
                        'avg_used_price' => $cachedPrice['avg_used_price'],
                        'last_checked' => $cachedPrice['last_checked'] // DBの日付をそのまま返す
                    ]
                ]);
                exit;
            }    }
}

// 2. Yahoo Shopping API から取得
$appId = 'dj00aiZpPTIwV2lHMDJ2T3A0MCZzPWNvbnN1bWVyc2VjcmV0Jng9Yjk-';

// 「本体」を追加して、互換アクセサリではなく実際のデバイスを取得するようにする
// 除外キーワードを追加して、アクセサリ、ジャンク品、並行輸入品を除外する
// 除外: ケース, カバー, フィルム, ガラス, 保護, 充電器, ケーブル, アダプタ, 部品, ジャンク, レンタル, 再生品, バッテリー, 並行輸入, 海外版
$negativeKeywords = ' -ケース -カバー -フィルム -ガラス -保護 -充電 -ケーブル -アダプタ -部品 -ジャンク -訳あり -レンタル -再生 -バッテリー -Cable -並行 -輸入 -香港版 -海外版 -Global -India -米国版 -国際版';
$searchQuery = $query . ' 本体' . $negativeKeywords;

// 安価なアクセサリを除外するために最低価格を設定 (例: 10000円)
$priceMin = 30000;
$priceMax = 300000;

$url = "https://shopping.yahooapis.jp/ShoppingWebService/V3/itemSearch?appid=$appId&query=" . urlencode($searchQuery) . "&results=50&price_from=$priceMin&price_to=$priceMax&in_stock=true&";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// タイムアウトの設定
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    echo json_encode(['error' => 'APIリクエストに失敗しました']);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['hits'])) {
    // 厳密なフィルタリングでヒットしない場合、フィルタを緩めるかnullを返す
    // 今のところは空/nullを返してUIで「-」を表示する
    echo json_encode(['error' => '該当する商品が見つかりませんでした', 'raw' => $data]);
    exit;
}

$newPrices = [];
$usedPrices = [];

foreach ($data['hits'] as $item) {
    $price = $item['price'];

    // Yahoo Shopping API V3 は 'condition' (new/used) を返す
    $condition = isset($item['condition']) ? $item['condition'] : 'new';

    // 商品名がクエリと一致するか検証 (タイトルが製品名と一致するか確認)
    if (!validate_item_name($item['name'], $query)) {
        continue;
    }

    if ($condition === 'new') {
        $newPrices[] = $price;
    } else {
        $usedPrices[] = $price;
    }
}

function validate_item_name($name, $query)
{
    // 正規化: 全角英数字/スペースを半角に変換
    $name = strtolower(mb_convert_kana($name, 'as'));
    $query = strtolower(mb_convert_kana($query, 'as'));



    // 1. 並行輸入品 / 海外版を除外
    // 除外する地域/用語のリスト
    $excludePatterns = '/(並行|輸入|香港|海外|global|india|taiwan|korea|china)/u';

    if (preg_match($excludePatterns, $name)) {
        return false;
    }

    $tokens = array_filter(explode(' ', $query), function ($t) {
        return trim($t) !== '';
    });



    foreach ($tokens as $token) {
        if (ctype_digit($token)) {
            // 数字のトークン: 部分一致を避けるために境界を確認 (例: "512" の中の "12")
            if (!preg_match('/(?<!\d)' . preg_quote($token, '/') . '(?!\d)/', $name)) {
                return false;
            }
        } else {
            // テキストトークン: 名前に含まれているか確認
            if (strpos($name, $token) === false) {
                return false;
            }
        }
    }
    return true;
}

// 中央値を使用して平均を計算し、外れ値を除外する
// max() を使用する以前のロジックは、1つの高い外れ値がすべての正常な価格を除外してしまうため欠陥があった
function calculate_smart_average($prices)
{
    if (empty($prices))
        return null;

    sort($prices);
    $count = count($prices);

    // 中央値の計算
    $middle = floor(($count - 1) / 2);
    if ($count % 2) {
        $median = $prices[$middle];
    } else {
        $median = ($prices[$middle] + $prices[$middle + 1]) / 2;
    }

    // 中央値に基づいてフィルタリング
    // 中央値の50%から150%の間の価格を保持する
    // これにより、極端に安いアクセサリや極端に高いまとめ売り/エラーを除去する
    $lowThreshold = $median * 0.5;
    $highThreshold = $median * 1.5;

    $filtered = array_filter($prices, function ($p) use ($lowThreshold, $highThreshold) {
        return $p >= $lowThreshold && $p <= $highThreshold;
    });

    if (empty($filtered))
        return round($median / 10) * 10; // フォールバック

    $avg = array_sum($filtered) / count($filtered);
    return round($avg / 10) * 10;
}

$avgNew = calculate_smart_average($newPrices);

// 平均新品価格より高い中古価格をフィルタリング
if ($avgNew !== null) {
    $usedPrices = array_filter($usedPrices, function ($p) use ($avgNew) {
        return $p <= $avgNew;
    });
}

$avgUsed = calculate_smart_average($usedPrices);

// 3. キャッシュの更新
DeviceLogic::updateDevicePriceCache($device_id, $avgNew, $avgUsed);

echo json_encode([
    'source' => 'api',
    'data' => [
        'avg_new_price' => $avgNew,
        'avg_used_price' => $avgUsed,
        'last_checked' => date('Y-m-d')
    ]
]);