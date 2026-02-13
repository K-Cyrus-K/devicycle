<?php
session_start();
require_once "../classes/DeviceLogic.php";
require_once "../includes/functions.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => '不正アクセスです']);
    exit;
}

if (!isset($_SESSION['login_user'])) {
    echo json_encode(['status' => false, 'message' => 'ログインが必要です']);
    exit;
}

$input = $_POST;

// バリデーション (entry_idの存在を確認)
if (empty($input['entry_id']) || empty($input['user_id']) || empty($input['purchase_date'])) {
    echo json_encode(['status' => false, 'message' => '必須項目が不足しています']);
    exit;
}

// TODO: add_device.phpと同様のバリデーションを追加する

$statusMap = [
    '使用中' => 1,
    '故障' => 2,
    '売却済' => 3,
    '保管中' => 4
];
$statusInt = isset($statusMap[$input['status_name']]) ? $statusMap[$input['status_name']] : 5;


$deviceData = [


    'entry_id' => $input['entry_id'],


    'user_id' => $input['user_id'],


    'status' => $statusInt,


    'purchase_date' => $input['purchase_date'],


    'purchase_price' => !empty($input['purchase_price']) && is_numeric($input['purchase_price']) ? (int) $input['purchase_price'] : null,


    'sold_price' => !empty($input['sold_price']) && is_numeric($input['sold_price']) ? (int) $input['sold_price'] : null,


    'is_public' => isset($input['is_public']) ? 1 : 0, // 修正：存在すれば1、なければ0


    'warranty_end_date' => !empty($input['warranty_end_date']) ? $input['warranty_end_date'] : null,


    'return_due_date' => !empty($input['return_due_date']) ? $input['return_due_date'] : null,


    'user_notes' => !empty($input['user_notes']) ? $input['user_notes'] : null,


    // 使用不可フィールドもここに追加する必要がある場合があります


    'unusable_date' => !empty($input['unusable_date']) ? $input['unusable_date'] : null,


    'unusable_reason' => !empty($input['unusable_reason']) ? $input['unusable_reason'] : null


];

// DB更新処理
$result = DeviceLogic::updateUserDevice($deviceData);

// レーティング更新処理
if (!empty($input['device_id']) && !empty($input['rating'])) {
    $rating = floatval($input['rating']);
    if ($rating >= 0.5 && $rating <= 5.0) {
        DeviceLogic::rateDevice($input['user_id'], $input['device_id'], $rating);
    }
}

// 画像アップロード処理
if (isset($_FILES['images']) && $result) {
    $imageTypes = $_POST['image_types'] ?? [];
    $entryId = $input['entry_id'];

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

if ($result) {
    echo json_encode(['status' => true, 'message' => 'デバイス情報を更新しました！']);
} else {
    echo json_encode(['status' => false, 'message' => '更新に失敗しました。']);
}
