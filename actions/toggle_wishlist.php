<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
require_once "../classes/DeviceLogic.php";

/**
 * ウィッシュリスト追加/削除 API
 * POST /actions/toggle_wishlist.php
 */

$uid = isset($_SESSION['login_user']) ? $_SESSION['login_user']['uid'] : null;

if (!$uid) {
    echo json_encode(['status' => 'error', 'message' => 'ログインが必要です。']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$deviceId = isset($input['device_id']) ? (int) $input['device_id'] : null;

if (!$deviceId) {
    echo json_encode(['status' => 'error', 'message' => 'デバイスIDが不足しています。']);
    exit;
}

$result = DeviceLogic::toggleWishlist($uid, $deviceId);

if ($result) {
    echo json_encode([
        'status' => 'success',
        'action' => $result['status'],
        'message' => $result['status'] === 'added' ? 'ウィッシュリストに追加しました。' : 'ウィッシュリストから削除しました。'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => '操作に失敗しました。']);
}
