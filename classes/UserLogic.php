<?php
require_once "../config/dbconnect.php";

class UserLogic
{
    /**
     * ユーザ登録する
     * @param array $userData
     * @return bool $result
     */
    public static function createUser($userData)
    {
        $result = false;
        $sql = "INSERT INTO users (uname, email, hashed_password) VALUES (?,?,?)";

        $arr = [];
        $arr[] = $userData["username"];
        $arr[] = $userData["email"];
        $arr[] = password_hash($userData["password"], PASSWORD_DEFAULT);

        try {
            $stmt = connect()->prepare($sql);
            $result = $stmt->execute($arr);
            return $result;
        } catch (\Exception $e) {
            return $result;
        }
    }

    /**
     * ログイン処理
     * @param string $email
     * @param string $password
     * @return bool $result
     */
    public static function login($email, $password)
    {
        // 結果
        $result = false;
        // ユーザをメールから検索する
        $user = self::getUserByEmail($email);

        if (!$user) {
            $_SESSION["msg"] = "メールアドレスが一致しません。";
            return $result;
        }

        // パスワードチェック
        if (password_verify($password, $user["hashed_password"])) {
            session_regenerate_id(true);
            unset($user['hashed_password']); // ★追加: パスワードハッシュはセッションに入れない
            $_SESSION["login_user"] = $user;
            return true;
        }

        $_SESSION["msg"] = "パスワードが一致しません。";
        return $result;
    }


    /**
     * メールアドレスからユーザ取得
     * @param string $email
     * @return array|bool $result|false
     */
    public static function getUserByEmail($email)
    {
        // メールアドレスを用いてユーザを検索
        $sql = "SELECT * FROM users WHERE email = ?";

        $arr = [];
        // emailを配列に入れる
        $arr[] = $email;
        try {
            // SQLの準備
            $stmt = connect()->prepare($sql);
            // SQLの実行
            $stmt->execute($arr);
            // SQLの結果を返す
            return $username = $stmt->fetch();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ログインチェック
     * @return bool $result
     */
    public static function checkLogin()
    {
        $result = false;

        if (isset($_SESSION["login_user"]) && $_SESSION["login_user"]["uid"] > 0) {
            return $result = true;
        }
        return $result;
    }

    /**
     * 管理者チェック
     * @return bool
     */
    public static function isAdmin()
    {
        return isset($_SESSION["login_user"]) && !empty($_SESSION["login_user"]["is_admin"]);
    }

    /**
     * ログアウト処理
     */
    public static function logout()
    {
        $_SESSION = array();
        session_destroy();
    }
}
