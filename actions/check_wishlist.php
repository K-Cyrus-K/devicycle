<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
require_once "../classes/DeviceLogic.php";

$uid = isset($_SESSION['login_user']) ? $_SESSION['login_user']['uid'] : null;
$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : null;

if (!$uid || !$deviceId) {
    echo json_encode(['in_wishlist' => false]);
    exit;
}

$inWishlist = DeviceLogic::isInWishlist($uid, $deviceId);
echo json_encode(['in_wishlist' => $inWishlist]);
