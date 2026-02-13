<?php
require_once "../config/dbconnect.php";

class DeviceLogic
{
    /**
     * デバイス検索
     * Param: 検索キーワード
     */
    public static function searchDevices($keyword)
    {
        try {
            $stmt = connect()->prepare("CALL fuzzy_search_devices(?)");
            $stmt->bindParam(1, $keyword, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

        /**

         * ユーザデバイス登録

         * Param: デバイスデータ

         */

        public static function registerUserDevice($data)

        {

            $sql = "CALL add_user_device(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 12パラメータ

    

            $params = [

                $data['device_id'],

                $data['user_id'],

                $data['status'],

                $data['purchase_date'],

                $data['purchase_price'] ?? null,

                $data['sold_price'] ?? null,

                $data['unusable_date'],

                $data['unusable_reason'],

                $data['is_public'] ?? 1, // 公開フラグを追加

                $data['user_notes'],

                $data['warranty_end_date'],

                $data['return_due_date']

            ];

    

            try {

                $pdo = connect();

                $stmt = $pdo->prepare($sql);

                $stmt->execute($params);

    

                // ストアドプロシージャでは lastInsertId() が正しく機能しない場合があります。

                // より確実な方法は、明示的に問い合わせることです。

                return $pdo->query("SELECT LAST_INSERT_ID()")->fetchColumn();

    

            } catch (\PDOException $e) {

                error_log($e->getMessage());

                return false;

            }

        }

    

        /**

         * デバイスに画像を追加

         * Param: entry_id, image_path, image_type

         */

        public static function addImageForDevice($entryId, $imagePath, $imageType)

        {

            $sql = "INSERT INTO user_device_images (entry_id, image_path, image_type) VALUES (?, ?, ?)";

            try {

                $stmt = connect()->prepare($sql);

                return $stmt->execute([$entryId, $imagePath, $imageType]);

            } catch (\PDOException $e) {

                error_log($e->getMessage());

                return false;

            }

        }

    

        /**

         * ユーザーのデバイス情報を更新

         * Param: デバイスデータ

         */

        public static function updateUserDevice($data)

        {

            $sql = "CALL update_user_device(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 12パラメータ

    

            $params = [

                $data['entry_id'],

                $data['user_id'],

                $data['status'],

                $data['purchase_date'],

                $data['purchase_price'] ?? null,

                $data['sold_price'] ?? null,

                $data['unusable_date'],

                $data['unusable_reason'],

                $data['is_public'] ?? 1, // 公開フラグを追加

                $data['user_notes'],

                $data['warranty_end_date'],

                $data['return_due_date']

            ];

    

            try {

                $stmt = connect()->prepare($sql);

                return $stmt->execute($params);

            } catch (\PDOException $e) {

                error_log($e->getMessage());

                return false;

            }

        }

    

    /**
     * ユーザーの全デバイスを取得 (ストアドプロシージャ使用)
     * 戻り値: entry_id, デバイス名, ブランド名, 画像URL, 購入日, ステータス名など
     */
    public static function getDevicesByUserId($user_id)
    {
        try {
            $pdo = connect();
            // 1. ユーザーの全デバイスを取得
            $stmt = $pdo->prepare("CALL get_user_devices(?)");
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // 2. 各デバイスの画像を取得
            $image_stmt = $pdo->prepare("SELECT image_path, image_type FROM user_device_images WHERE entry_id = ?");

            foreach ($devices as $key => $device) {
                $image_stmt->bindParam(1, $device['entry_id'], PDO::PARAM_INT);
                $image_stmt->execute();
                $images = $image_stmt->fetchAll(PDO::FETCH_ASSOC);
                $devices[$key]['images'] = $images; // 画像をデバイス配列に追加
                $image_stmt->closeCursor();
            }

            return $devices;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    

        /**

         * デバイスの価格を取得する

         * @param mixed $device_id デバイスID

         * @return array|false 価格データ (avg_new_price, avg_used_price, last_checked) または false

         */

        public static function getDevicePrice($device_id)

        {

            try {

                $pdo = connect();

                // SPが使えるか確認、そうでなければ直接SELECTを使用

                // SP get_device_price は avg_new_price, avg_used_price, last_checked を選択する

                $stmt = $pdo->prepare("CALL get_device_price(?)");

                $stmt->bindParam(1, $device_id, PDO::PARAM_INT);

                $stmt->execute();

                $price = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt->closeCursor();

    

                return $price;

    

            } catch (\Exception $e) {

                error_log($e->getMessage());

                return false;

            }

        }

    

        /**

         * デバイス価格キャッシュを更新または挿入

         * @param int $deviceId

         * @param int|null $newPrice

         * @param int|null $usedPrice

         */

        public static function updateDevicePriceCache($deviceId, $newPrice, $usedPrice)

        {

            $sql = "INSERT INTO device_price_chart (device_id, avg_new_price, avg_used_price, last_checked) 

                    VALUES (?, ?, ?, CURDATE())

                    ON DUPLICATE KEY UPDATE 

                    avg_new_price = VALUES(avg_new_price), 

                    avg_used_price = VALUES(avg_used_price), 

                    last_checked = VALUES(last_checked)";

            try {

                $stmt = connect()->prepare($sql);

                return $stmt->execute([$deviceId, $newPrice, $usedPrice]);

            } catch (\PDOException $e) {

                error_log($e->getMessage());

                return false;

            }

        }

    

        /**

         * デバイスを評価する

         * @param int $userId

         * @param int $deviceId

         * @param float $rating

         * @return array|false 新しい平均評価と件数

         */

        public static function rateDevice($userId, $deviceId, $rating)

        {

            try {

                $pdo = connect();

                $stmt = $pdo->prepare("CALL add_or_update_device_rating(?, ?, ?)");

                $stmt->bindParam(1, $deviceId, PDO::PARAM_INT);

                $stmt->bindParam(2, $userId, PDO::PARAM_INT);

                $stmt->bindParam(3, $rating, PDO::PARAM_STR); // DECIMAL param

                $stmt->execute();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt->closeCursor();

                return $result;

            } catch (\Exception $e) {

                error_log($e->getMessage());

                return false;

            }

        }

    

        /**

         * ダッシュボード統計を取得 (複数結果セット)

         * @return array|false

         */

        public static function getDashboardStats()

        {

            try {

                $pdo = connect();

                $stmt = $pdo->prepare("CALL get_dashboard_stats()");

                $stmt->execute();

    

                $stats = [];

    

                // 1. Global Totals

                $stats['global'] = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt->nextRowset();

    

                // 2. Average Lifespan by Brand

                $stats['lifespan_by_brand'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt->nextRowset();

    

                // 3. Device Count by Brand

                $stats['popularity_by_brand'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt->nextRowset();

    

                // 4. Common Failure Reasons

                $stats['failure_reasons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

                $stmt->closeCursor();

                return $stats;

    

            } catch (\Exception $e) {

                error_log($e->getMessage());

                return false;

            }

        }

    

        /**

         * 殿堂入り: 高評価デバイス

         */

                public static function getTopRatedDevices($limit = 10)

                {

                    try {

                        $pdo = connect();

                        $sql = "SELECT 

                                    d.did AS device_id,

                                    d.dname AS device_name,

                                    b.bname AS brand_name,

                                    d.image_path,

                                    d.avg_rating,

                                    d.rating_count,

                                    d.launched_year

                                FROM devices d

                                LEFT JOIN brands b ON d.brand_id = b.bid

                                WHERE d.rating_count > 0

                                ORDER BY d.avg_rating DESC, d.rating_count DESC

                                LIMIT :limit";

                        $stmt = $pdo->prepare($sql);

                        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

                        $stmt->execute();

                        return $stmt->fetchAll();

                    } catch (\Exception $e) {

                        error_log("Hall of Fame Error (Top Rated): " . $e->getMessage());

                        return [];

                    }

                }

        

                /**

                 * 殿堂入り: 評価数が多いデバイス

                 */

                public static function getMostRatedDevices($limit = 10)

                {

                    try {

                        $pdo = connect();

                        $sql = "SELECT 

                                    d.did AS device_id,

                                    d.dname AS device_name,

                                    b.bname AS brand_name,

                                    d.image_path,

                                    d.avg_rating,

                                    d.rating_count,

                                    d.launched_year

                                FROM devices d

                                LEFT JOIN brands b ON d.brand_id = b.bid

                                WHERE d.rating_count > 0

                                ORDER BY d.rating_count DESC, d.avg_rating DESC

                                LIMIT :limit";

                        $stmt = $pdo->prepare($sql);

                        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

                        $stmt->execute();

                        return $stmt->fetchAll();

                    } catch (\Exception $e) {

                        error_log("Hall of Fame Error (Most Rated): " . $e->getMessage());

                        return [];

                    }

                }

        

    

        /**

         * 殿堂入り: 最長使用デバイス

         */

        public static function getLongestUsedDevices($limit = 10)

        {

            try {

                $pdo = connect();

                $sql = "SELECT 

                            ud.entry_id,

                            d.dname AS device_name,

                            b.bname AS brand_name,

                            IFNULL(d.image_path, 'https://placehold.co/600x400/e2e8f0/64748b?text=No+Image') AS image_path,

                            ud.purchased_date,

                            ud.unusable_date,

                            DATEDIFF(IFNULL(ud.unusable_date, CURRENT_DATE()), ud.purchased_date) AS usage_days,

                            u.uname AS owner_name

                        FROM user_device ud

                        INNER JOIN devices d ON ud.device_id = d.did

                        LEFT JOIN brands b ON d.brand_id = b.bid

                        LEFT JOIN users u ON ud.owned_userid = u.uid

                        WHERE DATEDIFF(IFNULL(ud.unusable_date, CURRENT_DATE()), ud.purchased_date) >= 365

                          AND ud.is_public = 1

                        ORDER BY usage_days DESC

                        LIMIT :limit";

                $stmt = $pdo->prepare($sql);

                $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

                $stmt->execute();

                return $stmt->fetchAll();

            } catch (\Exception $e) {

                error_log("Hall of Fame Error (Longest Used): " . $e->getMessage());

                return [];

            }

        }

    

        /**

         * 殿堂入り: 故障理由ピックアップ

         */

        public static function getInterestingFailures($limit = 10)

        {

            try {

                $pdo = connect();

                $sql = "SELECT 

                            ud.entry_id,

                            d.dname AS device_name,

                            b.bname AS brand_name,

                            ud.unusable_reason,

                            ud.unusable_date,

                            u.uname AS owner_name

                        FROM user_device ud

                        INNER JOIN devices d ON ud.device_id = d.did

                        LEFT JOIN brands b ON d.brand_id = b.bid

                        LEFT JOIN users u ON ud.owned_userid = u.uid

                        WHERE ud.unusable_reason IS NOT NULL 

                          AND ud.unusable_reason != ''

                          AND ud.current_status IN (2, 3)

                          AND ud.is_public = 1

                        ORDER BY ud.unusable_date DESC

                        LIMIT :limit";

                $stmt = $pdo->prepare($sql);

                $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

                $stmt->execute();

                return $stmt->fetchAll();

            } catch (\Exception $e) {

                error_log("Hall of Fame Error (Failures): " . $e->getMessage());

                return [];

            }

        }

    /**
     * 期限間近のデバイスを取得 (保証終了30日以内 または 返品期限7日以内)
     */
    public static function getExpiringDevices($user_id)
    {
        try {
            $pdo = connect();
            $sql = "SELECT 
                        ud.entry_id,
                        d.dname AS device_name,
                        ud.warranty_end_date,
                        ud.return_due_date,
                        DATEDIFF(ud.warranty_end_date, CURRENT_DATE()) AS warranty_days_left,
                        DATEDIFF(ud.return_due_date, CURRENT_DATE()) AS return_days_left
                    FROM user_device ud
                    INNER JOIN devices d ON ud.device_id = d.did
                    WHERE ud.owned_userid = :user_id
                      AND (
                          (ud.warranty_end_date IS NOT NULL AND DATEDIFF(ud.warranty_end_date, CURRENT_DATE()) BETWEEN 0 AND 30)
                          OR 
                          (ud.return_due_date IS NOT NULL AND DATEDIFF(ud.return_due_date, CURRENT_DATE()) BETWEEN 0 AND 7)
                      )
                    ORDER BY LEAST(IFNULL(warranty_days_left, 999), IFNULL(return_days_left, 999)) ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("通知取得エラー: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ウィッシュリストの切り替え（追加/削除）
     */
    public static function toggleWishlist($userId, $deviceId)
    {
        try {
            $pdo = connect();
            // 既に存在するかチェック
            $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND device_id = ?");
            $stmt->execute([$userId, $deviceId]);
            $exists = $stmt->fetch();

            if ($exists) {
                // 削除
                $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND device_id = ?");
                $stmt->execute([$userId, $deviceId]);
                return ['status' => 'removed'];
            } else {
                // 追加
                $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, device_id) VALUES (?, ?)");
                $stmt->execute([$userId, $deviceId]);
                return ['status' => 'added'];
            }
        } catch (\Exception $e) {
            error_log("Wishlist Toggle Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ウィッシュリストに入っているか確認
     */
    public static function isInWishlist($userId, $deviceId)
    {
        try {
            $stmt = connect()->prepare("SELECT id FROM wishlist WHERE user_id = ? AND device_id = ?");
            $stmt->execute([$userId, $deviceId]);
            return (bool)$stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ユーザーのウィッシュリスト取得
     */
    public static function getWishlistByUserId($userId)
    {
        try {
            $pdo = connect();
            $sql = "SELECT 
                        w.id as wishlist_id,
                        d.did as device_id,
                        d.dname as device_name,
                        b.bname as brand_name,
                        d.image_path,
                        d.avg_rating,
                        d.rating_count,
                        d.launched_year
                    FROM wishlist w
                    INNER JOIN devices d ON w.device_id = d.did
                    LEFT JOIN brands b ON d.brand_id = b.bid
                    WHERE w.user_id = ?
                    ORDER BY w.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Get Wishlist Error: " . $e->getMessage());
            return [];
        }
    }
}