<?php
session_start();
require_once "../classes/UserLogic.php";

// エラーメッセージ
$err = [];

// バリデーション
if (!$email = filter_input(INPUT_POST, "email")) {
    $err["email"] = "メールアドレスを入力してください。";
}
if (!$password = filter_input(INPUT_POST, "password")) {
    $err["password"] = "パスワードを入力してください。";
}

// ログインする処理
if (count($err) > 0) {
    // エラーがあったら戻す
    $_SESSION = $err;
    header("Location: ../public/index.php?error=login");
    exit;
}

// ログイン
$result = UserLogic::login($email, $password);
// ログイン失敗の処理
if ($result) {
    header("Location: ../public/mypage");
} else {
    header("Location: ../public/index.php?error=login");
}
