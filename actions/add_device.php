<?php
session_start();
require_once "../classes/DeviceLogic.php";
require_once "../includes/functions.php";

header('Content-Type: application/json; charset=utf-8');

// post メソッドじゃない場合
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => '不正アクセスです']);
    exit;
}

// ログインしてない場合
if (!isset($_SESSION['login_user'])) {
    echo json_encode(['status' => false, 'message' => 'ログインが必要です']);
    exit;
}

$input = $_POST;

// バリデーション
if (empty($input['user_id']) || empty($input['device_id']) || empty($input['purchase_date'])) {
    echo json_encode(['status' => false, 'message' => '必須項目が不足しています']);
    exit;
}

$now = new DateTime();
$now->setTime(0, 0, 0); // 日付のみの比較のため時間を00:00:00に設定
$purchase_date = new DateTime($input['purchase_date']);
if ($purchase_date > $now) {
    echo json_encode(['status' => false, 'message' => '購入日が今日より先に入力できません']);
    exit;
}

// ステータスの数値変換
$statusMap = [
    'active' => 1,
    'broken' => 2,
    'sold' => 3,
    'storage' => 4,
    'unknown' => 5
];
$statusInt = isset($statusMap[$input['device_status']]) ? $statusMap[$input['device_status']] : 5;

$unusableDate = !empty($input['unusable_date']) ? $input['unusable_date'] : null;
$unusableReason = !empty($input['unusable_reason']) ? $input['unusable_reason'] : null;

if ($statusInt === 1) { // 使用中
    $unusableDate = null;
    $unusableReason = null;
}

if ($unusableDate !== null) {
    if ($input['purchase_date'] > $unusableDate) {
        echo json_encode(['status' => false, 'message' => '日付エラー：故障/手放した日は購入日より後に設定してください。']);
        exit;
    }
}

// 新しい日付フィールドの取得
$warrantyEndDate = !empty($input['warranty_end_date']) ? $input['warranty_end_date'] : null;

// 新しい価格フィールドの取得

$purchasePrice = !empty($input['purchase_price']) && is_numeric($input['purchase_price']) ? (int)$input['purchase_price'] : null;

$soldPrice = !empty($input['sold_price']) && is_numeric($input['sold_price']) ? (int)$input['sold_price'] : null;

$isPublic = isset($input['is_public']) ? 1 : 0; // 修正：存在すれば1、なければ0



$returnDueDate = null;

if (!empty($input['return_days']) && is_numeric($input['return_days']) && $input['return_days'] > 0) {

    // 戻り値の日付が入力されていれば

    $purchaseDateObj = new DateTime($input['purchase_date']);

    $purchaseDateObj->modify('+' . (int) $input['return_days'] . ' days');

    $returnDueDate = $purchaseDateObj->format('Y-m-d');

}



// データ配列の作成 (画像以外)

$deviceData = [

    'device_id' => $input['device_id'],

    'user_id' => $input['user_id'],

    'status' => $statusInt,

    'purchase_date' => $input['purchase_date'],

    'purchase_price' => $purchasePrice,

    'sold_price' => $soldPrice,

    'is_public' => $isPublic, // 追加

    'unusable_date' => $unusableDate,

    'unusable_reason' => $unusableReason,

    'user_notes' => !empty($input['comment']) ? $input['comment'] : null,

    'warranty_end_date' => $warrantyEndDate,

    'return_due_date' => $returnDueDate

];

// DB登録処理 (デバイス本体)
$entryId = DeviceLogic::registerUserDevice($deviceData);

if (!$entryId) {
    echo json_encode(['status' => false, 'message' => 'デバイスの登録に失敗しました。']);
    exit;
}

// レーティング登録処理
if (!empty($input['rating'])) {
    $rating = floatval($input['rating']);
    if ($rating >= 0.5 && $rating <= 5.0) {
        DeviceLogic::rateDevice($input['user_id'], $input['device_id'], $rating);
    }
}

// 画像アップロード処理
if (isset($_FILES['images'])) {
    $imageTypes = $_POST['image_types'] ?? [];

    // 種類とディレクトリのマッピング
    $typeDirMap = [
        'RECEIPT' => 'receipts/',
        'WARRANTY' => 'warranties/',
        'DEVICE_IMAGE' => 'device_images/',
        'OTHER' => 'others/'
    ];

    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = $_FILES['images']['name'][$key];
            $fileTmpPath = $_FILES['images']['tmp_name'][$key];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $imageType = isset($imageTypes[$key]) ? $imageTypes[$key] : 'OTHER';

            // 保存先ディレクトリの決定
            $subDir = isset($typeDirMap[$imageType]) ? $typeDirMap[$imageType] : 'others/';
            $targetDir = __DIR__ . '/../public/uploads/' . $subDir;

            // ディレクトリが存在しない場合は作成
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $allowedfileExtensions = ['jpg', 'gif', 'png', 'pdf', 'jpeg'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $destPath = $targetDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $imagePath = BASE_URL . 'public/uploads/' . $subDir . $newFileName;
                    DeviceLogic::addImageForDevice($entryId, $imagePath, $imageType);
                }
            }
        }
    }
}

echo json_encode(['status' => true, 'message' => 'デバイスを登録しました！']);