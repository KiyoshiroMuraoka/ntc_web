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
  $company_name = filter_input(INPUT_POST, 'company_name');
  $address1 = filter_input(INPUT_POST, 'address1');
  $phone_number = filter_input(INPUT_POST, 'phone_number');
  //$department = isset($_POST['department']) ? $_POST['department'] : NULL;
  $name = filter_input(INPUT_POST, 'name');
  $furi_name = filter_input(INPUT_POST, 'furi_name');
  $emailadd = filter_input(INPUT_POST, 'emailadd');
  $url = filter_input(INPUT_POST, 'url');
  $service = filter_input(INPUT_POST, 'service');
  $source = filter_input(INPUT_POST, 'source');
  $output = isset($_POST['output']) ? $_POST['output'] : NULL;
  $item = filter_input(INPUT_POST, 'item');
  //$estimate = isset($_POST['estimate']) ? $_POST['estimate'] : NULL;
  //$demo = isset($_POST['demo']) ? $_POST['demo'] : NULL;
  //$startdate = isset($_POST['startdate']) ? $_POST['startdate'] : NULL;
  //$startdate2 = isset($_POST['startdate2']) ? $_POST['startdate2'] : NULL;
  $discussion = filter_input(INPUT_POST, 'discussion');
  $body = filter_input(INPUT_POST, 'body');

  //POSTされたデータを整形（前後にあるホワイトスペースを削除）
  $company_name = trim($company_name);
  $address1 = trim($address1);
  $phone_number = trim($phone_number);
  //$department = trim($department);
  $name = trim($name);
  $furi_name = trim($furi_name);
  $emailadd = trim($emailadd);
  $url = trim($url);
  $service = trim($service);
  $source = trim($source);
  $item = trim($item);
  // $estimate = trim($estimate);
  // $demo = trim($demo);
  // $startdate = trim($startdate);
  // $startdate2 = trim($startdate2);
  $discussion = trim($discussion);
  $body = trim($body);
  $agree = filter_input(INPUT_POST, 'agreement');

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
  // $data['department'] = $department;
  $data['name'] = $name;
  $data['furi_name'] = $furi_name;
  $data['emailadd'] = $emailadd;
  $data['url'] = $url;
  $data['service'] = $service;
  $data['source'] = $source;
  $data['output'] = $output;
  $data['item'] = $item;
  // $data['estimate'] = $estimate;
  // $data['demo'] = $demo;
  // $data['startdate'] = $startdate;
  // $data['startdate2'] = $startdate2;
  $data['discussion'] = $discussion;
  $data['body'] = $body;
  $data['ticket'] = $ticket;

  // テンプレート表示
  if(count($error) >0){    //エラーがあった場合
    $data['error'] = $error;
    display('contact_view.php', $data);
  }else{    //エラーがなかった場合
    //POSTされたデータをセッション変数に保存
    $_SESSION['company_name'] = $company_name;
    $_SESSION['address1'] = $address1;
    $_SESSION['phone_number'] = $phone_number;
    // $_SESSION['department'] = $department;
    $_SESSION['name'] = $name;
    $_SESSION['furi_name'] = $furi_name;
    $_SESSION['emailadd'] = $emailadd;
    $_SESSION['url'] = $url;
    $_SESSION['service'] = $service;
    $_SESSION['source'] = $source;
    $_SESSION['output'] = $output;
    $_SESSION['item'] = $item;
    // $_SESSION['estimate'] = $estimate;
    // $_SESSION['demo'] = $demo;
    // $_SESSION['startdate'] = $startdate;
    // $_SESSION['startdate2'] = $startdate2;
    $_SESSION['discussion'] = $discussion;
    $_SESSION['body'] = $body;
    $_SESSION['agreement'] = $agreement;
      //確認画面を表示
    display('confirm_view.php', $data);
  }
?>
