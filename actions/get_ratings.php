<?php
header('Content-Type: application/json; charset=UTF-8');
require_once "../config/dbconnect.php";

/**
 * デバイス評価一覧取得 API
 * GET /actions/get_ratings.php
 */

try {
    $pdo = connect();
    
    // 全デバイスの評価情報を取得（評価があるもの優先）
    $sql = "SELECT 
                d.did AS device_id,
                d.dname AS device_name,
                b.bname AS brand_name,
                d.rating AS display_rating,
                d.avg_rating,
                d.rating_count
            FROM devices d
            LEFT JOIN brands b ON d.brand_id = b.bid
            WHERE d.rating_count > 0
            ORDER BY d.avg_rating DESC
            LIMIT 50";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '評価データの取得に失敗しました。'
    ], JSON_UNESCAPED_UNICODE);
}
