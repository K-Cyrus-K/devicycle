<?php
require_once "../classes/DeviceLogic.php";

// JavaScript からデータを受け取る
header('Content-Type: application/json; charset=UTF-8;');
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);
$keyword = $data["keyword"] ?? null;

// キーワードがない場合
if (empty($keyword)) {
    echo json_encode([]);
    exit;
}

$devices = DeviceLogic::searchDevices($keyword);
echo json_encode($devices, JSON_UNESCAPED_UNICODE);