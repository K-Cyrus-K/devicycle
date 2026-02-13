<?php
session_start();
require_once "../classes/UserLogic.php";

if (!filter_input(INPUT_POST, "logout")) {
    http_response_code(500);
    exit("不正なリクエストです。");
}

// ログイン判定、しなかったらメイン画面へ
$result = UserLogic::checkLogin();
if (!$result) {
    header("Location: ../public/");
    exit();
}

// ログアウトする処理
UserLogic::logout();
header("Location: ../public/");
exit();
