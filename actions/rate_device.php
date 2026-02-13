<?php
session_start();
require_once "../classes/DeviceLogic.php";
require_once "../classes/UserLogic.php";

header('Content-Type: application/json');

// ログイン確認
$result = UserLogic::checkLogin();
if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['login_user']['uid'];

// POSTデータを取得
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['device_id']) || !isset($input['rating'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

$device_id = $input['device_id'];
$rating = floatval($input['rating']);

// 評価の検証 (0.5 から 5.0, 0.5刻み)
if ($rating < 0.5 || $rating > 5.0 || fmod($rating * 2, 1) != 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid rating value']);
    exit;
}

$result = DeviceLogic::rateDevice($user_id, $device_id, $rating);

if ($result !== false) {
    echo json_encode([
        'status' => 'success',
        'new_avg_rating' => $result['new_avg_rating'],
        'new_rating_count' => $result['new_rating_count']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save rating']);
}
