<?php
header('Content-Type: application/json; charset=UTF-8');
require_once "../config/dbconnect.php";

$deviceId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : null;

if (!$deviceId) {
    echo json_encode(['error' => 'Device ID is required']);
    exit;
}

try {
    $pdo = connect();
    // 統計情報も含めて取得
    $sql = "SELECT 
                d.*, 
                b.bname,
                (SELECT ROUND(AVG(DATEDIFF(IFNULL(stat_ud.unusable_date, CURDATE()), stat_ud.purchased_date)))
                 FROM user_device stat_ud 
                 WHERE stat_ud.device_id = d.did AND stat_ud.purchased_date IS NOT NULL) as avg_lifespan,
                (SELECT stat_ud.unusable_reason 
                 FROM user_device stat_ud 
                 WHERE stat_ud.device_id = d.did 
                   AND stat_ud.current_status = 2 
                   AND stat_ud.unusable_reason IS NOT NULL 
                   AND stat_ud.unusable_reason NOT LIKE _utf8mb4'%買い替え%'
                   AND stat_ud.unusable_reason != ''
                 GROUP BY unusable_reason 
                 ORDER BY COUNT(*) DESC 
                 LIMIT 1) as common_failure_reason
            FROM devices d 
            LEFT JOIN brands b ON d.brand_id = b.bid 
            WHERE d.did = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($device) {
        echo json_encode($device);
    } else {
        echo json_encode(['error' => 'Device not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
