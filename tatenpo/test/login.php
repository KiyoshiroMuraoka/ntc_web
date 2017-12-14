<?php
// アプリケーション設定
define('CONSUMER_KEY', 'dj0zaiZpPTlZZWl6WUJYeXpmMCZzPWNvbnN1bWVyc2VjcmV0Jng9YjI-');
define('CALLBACK_URL', 'http://www.technocrats.jp/tatenpo/test/yh_cate.php');

define('AUTH_URL', 'https://auth.login.yahoo.co.jp/yconnect/v1/authorization');


//--------------------------------------
// 認証ページにリダイレクト
//--------------------------------------
$params = array(
	'client_id' => CONSUMER_KEY,
	'scope' => 'profile address email openid',
	'response_type' => 'code',
    'redirect_uri' => CALLBACK_URL
);

// リダイレクト
header("Location: " . AUTH_URL . '?' . http_build_query($params));