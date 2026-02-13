<?php
session_start();

$keys_to_clear = [
    "signup-error", // 新規登録のエラー配列
    "login_err",    // ログインのエラー
    "msg",          // ログインダイアログ等のメッセージ
    "err",          // エラー
    "email",        // メールアドレス
    "form_inputs"   // フォーム入力
];

// 指定したキーだけをピンポイントで削除（ログアウトさせないため session_destroy は使わない）
foreach ($keys_to_clear as $key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

http_response_code(200);
echo "セッションクリアしました。";
?>