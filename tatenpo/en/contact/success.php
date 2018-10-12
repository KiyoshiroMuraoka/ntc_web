<?php
  session_start();
  require 'functions.php';   
  $_POST = checkInput($_POST);
  if(isset($_POST['ticket'], $_SESSION['ticket'])){
    $ticket = $_POST['ticket'];
    if($ticket !== $_SESSION['ticket']){
      die('Invalid value has been detected.');
    }
  }else{
    die('Invalid value has been detected.');
  }
  $subject = "【イージースター(海外)】 {$_SESSION['company_name']} {$_SESSION['name']} 様よりお問い合わせ";
  $date = date("Y/m/d (D) G:i:s");
  $file = "access.log";
  $aclist = file_get_csv($file);
  $count = count(file($file));
  for($i = $count; $i >= 0; $i--){
    if($aclist[$i][1] == $_SERVER["REMOTE_ADDR"]){
      $result = $aclist[$i][2];
      break;
    }
  }
  if(isset($result)){
    if($result != ""){
      $referer = $result;
    }else{
      $referer = "(no referer)";
    }
  }else{
    $referer = "(error)";
  }
  if($_SESSION['service'] == "ChangeOver"){
    $demo = "-";
  }
  $body = <<< EOD
下記の内容で【日本テクノクラーツ株式会社 イージースター】へ海外からのお問い合わせがありました。
内容を確認し、依頼者に連絡をしてください。

■依頼者ご登録内容　=============================

【 御社名 】             {$_SESSION['company_name']}
【 国名 】               {$_SESSION['country']}
【 担当者様の氏名 】     {$_SESSION['name']}
【 Email 】              {$_SESSION['emailadd']}
【 URL 】                {$_SESSION['url']}
【 興味のあるサービス 】 {$_SESSION['service']}
【 ご質問・ご要望 】     {$_SESSION['body']}

=================================================
送信された日時：{$date}
送信者のIPアドレス：{$_SERVER["REMOTE_ADDR"]}
送信者のホスト名：{$_SERVER["REMOTE_HOST"]}
リファラー：{$referer}
ブラウザ(UserAgent)：{$_SERVER["HTTP_USER_AGENT"]}

━━━━━━━━━━━━━━━━━━
日本テクノクラーツ株式会社
 
東京都千代田区神田佐久間河岸84
サンユウビル1F
 
TEL：03-5835-5421
FAX：03-5835-5422
URL：https://www.technocrats.jp
E-mail：ec-contact@technocrats.jp
━━━━━━━━━━━━━━━━━━
EOD;
    $mailTo = 'ec-contact@technocrats.jp';
    $returnMail = 'ec-contact@technocrats.jp';
    mb_language('ja');
    mb_internal_encoding('UTF-8');
    $header = 'From: ' . mb_encode_mimeheader($_SESSION['name']). ' <' . $_SESSION['emailadd']. '>';
    if(ini_get('safe_mode')){
        $result = mb_send_mail($mailTo, $subject, $body, $header);
    }else{
        $result = mb_send_mail($mailTo, $subject, $body, $header, '-f'. $returnMail);
  }
   $message = '';
  if($result) {
    $message = '1Thank you very much!! Transmission was completed.';
    $_SESSION = array();
    session_destroy();
  }else{
    $message = " I'm terribly sorry. Transmission has failed.";
  }
    
  $data = array();
  $data['message'] = $message;
  display('success_view.php', $data);
?>