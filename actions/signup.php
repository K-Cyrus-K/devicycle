<?php
session_start();
require_once "../classes/UserLogic.php";

$err = [];

// トークンチェック
$token = filter_input(INPUT_POST, "csrf_token");
if (!isset($_SESSION["csrf_token"]) || $token !== $_SESSION["csrf_token"]) {
    exit("不正なリクエスト");
}

// バリデーション
if (!$username = filter_input(INPUT_POST, "username")) {
    $err[] = "ユーザ名を入力してください。";
}
if (!$email = filter_input(INPUT_POST, "email")) {
    $err[] = "メールアドレスを入力してください。";
}
$password = filter_input(INPUT_POST, "password");
if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=])[ -~]{8,}$/', $password)) {
    $err[] = "半角の英大文字・英小文字・数字・記号を全て含み、8文字以上にしてください！";
}
$password_confirm = filter_input(INPUT_POST, "password_confirm");
if ($password !== $password_confirm) {
    $err[] = "確認用パスワードと異なっています。";
}
if (!filter_input(INPUT_POST, "terms_agreed")) {
    $err[] = "利用規約への同意が必要です。";
}

// エラーがない場合
if (count($err) === 0) {
    // ユーザ作成
    $regSuccessful = UserLogic::createUser($_POST);

    if (!$regSuccessful) {
        $err[] = "登録に失敗しました（メールアドレスが既に使用されている可能性があります）。";
    } else {
        // ログイン処理
        UserLogic::login($email, $password);

        // 成功時のみトークンを削除
        unset($_SESSION["csrf_token"]);
        // 入力保持用セッションも削除
        unset($_SESSION["signup_inputs"]);

        // マイページに移動
        header("Location: ../public/mypage");
        exit();
    }
}

// エラーがある場合
if (count($err) > 0) {
    $_SESSION["signup-error"] = $err;

    // ★追加: 入力値をセッションに保存してダイアログに戻す
    $_SESSION["signup_inputs"] = [
        "username" => $username,
        "email" => $email
    ];

    header("Location: ../public/index.php?error=signup");
    exit();
}