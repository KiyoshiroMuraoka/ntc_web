<?php
// アクセスログ保存先
$fileName ="./contact/access.log";
if($_SERVER["REMOTE_ADDR"] == "220.221.252.160"){
   exit;
}
if ($_GET["title"] == "商品登録・商品移行なら【イージースター】") {
	$title[1] = "トップページ";
} else {
	preg_match('/【(.*?)】/', $_GET["title"] , $title);
}

$user_agent = $_SERVER['HTTP_USER_AGENT'];
if (preg_match('/Windows NT 10.0/', $user_agent)) {
	$os = 'Windows 10';
} elseif (preg_match('/Windows NT 6.3/', $user_agent)) {
	$os = 'Windows 8.1';
} elseif (preg_match('/Windows NT 6.2/', $user_agent)) {
	$os = 'Windows 8';
} elseif (preg_match('/Windows NT 6.1/', $user_agent)) {
	$os = 'Windows 7';
} elseif (preg_match('/Windows NT 6.0/', $user_agent)) {
	$os = 'Windows Vista';
} elseif (preg_match('/Windows NT 5.2/', $user_agent)) {
	$os = 'Windows Server 2003 / Windows XP x64 Edition';
} elseif (preg_match('/Windows NT 5.1/', $user_agent)) {
	$os = 'Windows XP';
} elseif (preg_match('/Windows NT 5.0/', $user_agent)) {
	$os = 'Windows 2000';
} elseif (preg_match('/Windows NT 4.0/', $user_agent)) {
	$os = 'Microsoft Windows NT 4.0';
} elseif (preg_match('/Mac OS X ([0-9\._]+)/', $user_agent, $matches)) {
	$os = 'Macintosh Intel ' . str_replace('_', '.', $matches[1]);
} elseif (preg_match('/Linux ([a-z0-9_]+)/', $user_agent, $matches)) {
	$os = 'Linux ' . $matches[1];
} elseif (preg_match('/OS ([a-z0-9_]+)/', $user_agent, $matches)) {
	$os = 'iOS ' . str_replace('_', '.', $matches[1]);
} elseif (preg_match('/Android ([a-z0-9\.]+)/', $user_agent, $matches)) {
	$os = 'Android ' . $matches[1];
} else {
	$os = '不明';
}


$outline = $_SERVER["REMOTE_ADDR"].",".$_GET["page"].",".$title[1].",".urldecode($_GET["ref"]).",".$_GET["region"].",".$_GET["city"].",".$_GET["country"].",".$os;

$time = time() + 9 * 3600;  //GMTとの時差9時間を足す
$time2= gmdate("Y/m/d H:i:s ", $time);

//------------------------------
//ファイルがないときは作成
//------------------------------
if( !file_exists($fileName) ){
   touch( $fileName );
   $output = "Access Time,IP Address,Access Page,Page Title,Referer,Region,City,Country,Operating System\n";
}else{
   $output = "";
}
//------------------------------
//追記
//------------------------------
$fp = fopen($fileName, "a");
fwrite($fp, $output.$time2.",".$outline."\n");
fclose($fp);

?>