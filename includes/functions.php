<?php
// タイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

/**
 * ベースURL
 */
/**
 * ベースURL (config/env.phpで定義)
 */
// define('BASE_URL', '/devicycle/');

/**
 * XSS対策：エスケープ処理
 * @param string $str 対象の文字列
 * @return string 処理された文字列
 */
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

/**
 * CSRF対策
 */
function setToken()
{
    // トークンがセッションにない場合のみ生成
    if (!isset($_SESSION["csrf_token"])) {
        $csrf_token = bin2hex((random_bytes(32)));
        $_SESSION["csrf_token"] = $csrf_token;
    }

    return $_SESSION["csrf_token"];
}

/**
 * 今のページ名＋サイドタイトルを返す
 * @param mixed $str ページ名、空文字ならサイドタイトルだけ返す
 * @return string
 */
function getTitle($str)
{
    $siteTitle = "デジクル・DEVICYCLE";

    if ($str) {
        return h($str . "｜" . $siteTitle);
    } else {
        return h($siteTitle);
    }
}