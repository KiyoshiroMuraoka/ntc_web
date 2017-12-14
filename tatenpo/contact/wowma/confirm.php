<?php
  const RECAPTCHA__API = 'https://www.google.com/recaptcha/api/siteverify';
  const RECAPTCHA_SKEY = '6LfxKDwUAAAAAAJv0HYFKN-tdjNi_GfIm8oIIkMv';
  session_start();    //セッションを開始

  require 'functions.php';   //テンプレートエンジンの読み込み

  //画像認証ライブラリの読み込み(securimage)
  include_once './securimage/securimage.php';
  $securimage = new Securimage();

  //POSTされたデータをチェック
  $_POST = checkInput($_POST);

  //固定トークンを確認（CSRF対策）
  if(isset($_POST['ticket'], $_SESSION['ticket'])){
    $ticket = $_POST['ticket'];
    if($ticket !== $_SESSION['ticket']){
      die('不正な値が検出されました。');
    }
  }else{
    die('不正な値が検出されました。');
  }

  //POSTされたデータを変数に格納
  $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : NULL;
  $address1 = isset($_POST['address1']) ? $_POST['address1'] : NULL;
  $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : NULL;
  $department = isset($_POST['department']) ? $_POST['department'] : NULL;
  $name = isset($_POST['name']) ? $_POST['name'] : NULL;
  $furi_name = isset($_POST['furi_name']) ? $_POST['furi_name'] : NULL;
  $emailadd = isset($_POST['emailadd']) ? $_POST['emailadd'] : NULL;
  $emailreq = isset($_POST['emailreq']) ? $_POST['emailreq'] : NULL;
  $url = isset($_POST['url']) ? $_POST['url'] : NULL;
  $service = isset($_POST['service']) ? $_POST['service'] : NULL;
  $source = isset($_POST['source']) ? $_POST['source'] : NULL;
  $item = isset($_POST['item']) ? $_POST['item'] : NULL;
  $introbody = isset($_POST['introbody']) ? $_POST['introbody'] : NULL;
  $estimate = isset($_POST['estimate']) ? $_POST['estimate'] : NULL;
  // $demo = isset($_POST['demo']) ? $_POST['demo'] : NULL;
  // $startdate = isset($_POST['startdate']) ? $_POST['startdate'] : NULL;
  // $startdate2 = isset($_POST['startdate2']) ? $_POST['startdate2'] : NULL;
  $discussion = isset($_POST['discussion']) ? $_POST['discussion'] : NULL;
  $body = isset($_POST['body']) ? $_POST['body'] : NULL;
  $captcha_code = isset($_POST['captcha_code']) ? $_POST['captcha_code'] : NULL;  //画像認証用データ

  //POSTされたデータを整形（前後にあるホワイトスペースを削除）
  $company_name = trim($company_name);
  $address1 = trim($address1);
	$phone_number = trim($phone_number);
	$department = trim($department);
	$name = trim($name);
	$furi_name = trim($furi_name);
	$emailadd = trim($emailadd);
	$emailreq = trim($emailreq);
	$url = trim($url);
	$service = trim($service);
	$item = trim($item);
  $introbody = trim($introbody);
	$estimate = trim($estimate);
	// $demo = trim($demo);
  // $startdate = trim($startdate);
  // $startdate2 = trim($startdate2);
  $discussion = trim($discussion);
  $intro = trim($intro);
	$body = trim($body);
  $captcha_code = trim($captcha_code);

  //エラーメッセージを保存する配列の初期化
  $error = array();

  if($emailadd == ''){
    $error[] = '*メールアドレスは必須です。';
  }else{   //メールアドレスを正規表現でチェック
    $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/uiD';
    if(!preg_match($pattern, $emailadd)){
      $error[] = '*メールアドレスの形式が正しくありません。';
    }
  }

  // reCAPTCHA認証のチェック
  $query = array(
      'secret' => RECAPTCHA_SKEY,
      'response' => $_REQUEST['g-recaptcha-response'],
      'remoteip' => $_SERVER['REMOTE_ADDR'],
  );
  $re_cap_url = RECAPTCHA__API.'?' . http_build_query($query);
  // 判定結果の取得
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL , $re_cap_url );
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER , false );  // 証明書の検証を行わない
  curl_setopt($curl , CURLOPT_RETURNTRANSFER , true ); // curl_execの結果を文字列で返す
  curl_setopt($curl , CURLOPT_TIMEOUT , 5 ); // タイムアウトの秒数
  $apiResponse = curl_exec( $curl );
  curl_close( $curl );
  $jsonData = json_decode($apiResponse, TRUE);
  if($jsonData['success'] !== TRUE){
      $error[] = '認証に失敗しました。';
  }

  // テンプレートパラメーターセット
  $data = array();
  $data['company_name'] = $company_name;
  $data['address1'] = $address1;
  $data['phone_number'] = $phone_number;
  $data['department'] = $department;
  $data['name'] = $name;
  $data['furi_name'] = $furi_name;
  $data['emailadd'] = $emailadd;
  $data['emailreq'] = $emailreq;
  $data['url'] = $url;
  $data['service'] = $service;
  $data['source'] = $source;
  $data['item'] = $item;
  $data['estimate'] = $estimate;
  // $data['demo'] = $demo;
  // $data['startdate'] = $startdate;
  // $data['startdate2'] = $startdate2;
  $data['discussion'] = $discussion;
  $data['introbody'] = $introbody;
  $data['body'] = $body;
  $data['ticket'] = $ticket;

  //チェックの結果にエラーがあった場合は、テンプレートの表示に必要な入力されたデータとエラーメッセージを配列「$data」に代入し、display()関数でform1_view.phpを表示
  if(count($error) >0){    //エラーがあった場合
    $data['error'] = $error;
    display('contact_view.php', $data);
  }else{    //エラーがなかった場合
    //POSTされたデータをセッション変数に保存
    $_SESSION['company_name'] = $company_name;
		$_SESSION['address1'] = $address1;
		$_SESSION['phone_number'] = $phone_number;
		$_SESSION['department'] = $department;
		$_SESSION['name'] = $name;
		$_SESSION['furi_name'] = $furi_name;
		$_SESSION['emailadd'] = $emailadd;
		$_SESSION['emailreq'] = $emailreq;
		$_SESSION['url'] = $url;
		$_SESSION['service'] = $service;
		$_SESSION['source'] = $source;
		$_SESSION['item'] = $item;
		$_SESSION['estimate'] = $estimate;
		$_SESSION['demo'] = $demo;
    $_SESSION['startdate'] = $startdate;
    $_SESSION['startdate2'] = $startdate2;
    $_SESSION['discussion'] = $discussion;
    $_SESSION['introbody'] = $introbody;
		$_SESSION['body'] = $body;
    display('confirm_view.php', $data);
  }
?>
