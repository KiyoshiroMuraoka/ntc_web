<?php
  session_start();    //セッションを開始

  require 'functions.php';
  //テンプレートエンジンの読み込み

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
  // 受付番号
  $acceptId = date("Ym"). str_pad(mt_rand(1, 9999), '0', STR_PAD_LEFT);
  //各種メール情報
  $subject = "【イージースター】 {$_SESSION['company_name']} {$_SESSION['name']} 様よりお問い合わせ 【受付番号】 {$acceptId}";
  $date = date("Y/m/d (D) G:i:s");
  $source_list = array(
    "base-rk" => "楽天市場",
    "base-yh" => "Yahoo! ショッピング",
    "base-am" => "Amazon",
    "base-pm" => "ポンパレモール",
    "base-bd" => "Wowma!",
    "base-ms" => "MakeShop",
    "base-etc" => "その他自社カート等"
  );
  $mall = array('rk' => "楽天市場",
    'yh' => "Yahoo! ショッピングモール",
    'am' => "Amazon",
    'pm' => "ポンパレモール",
    'bd' => "Wowma!",
    'ms' => "MakeShop",
    'etc' => "その他自社カート等");
  $startdate2value = array('notselect' => "中(特に指定なし)",
                           'fast' => "できるだけ早く",
                           'up' => "上旬頃までに",
                           'middle' => "中旬頃までに",
                           'down' => "下旬頃までに",
                           'last' => "月末までに",
                           'slow' => "特に急いでいない");
  $discussionvalue = array('notselect' => "指定なし",
                           '1' => "商戦に間に合わせたい",
                           '2' => "新しく店舗を増やしたい",
                           '3' => "新商品が増えたので一気に登録したい",
                           '4' => "モール毎に商品数を揃えたい",
                           '5' => "人が辞めてしまったため補完したい",
                           'other' => "その他");
  $output_list = "";
  if(isset($_SESSION['output']) && is_array($_SESSION['output'])){
    $temp_opt = array();
    foreach($_SESSION['output'] as $value){
      $temp_opt[] = $mall[$value];
    }
    $output_list = implode('、', $temp_opt);
  }
  // if($_SESSION['estimate'] = "estimate"){$estimate = "希望する";} else {$estimate = "希望しない";}
  // if($_SESSION['demo'] = "demo"){$demo = "希望する";} else {$demo = "希望しない";}
  // if(empty($_SESSION['startdate'])){$startdatevalue = "";} else {$startdatevalue = $_SESSION['startdate'] . "月 ";}
  $source = $source_list[$_SESSION['source']];
  //アクセスログからIPアドレスで検索し、ログに残っているリファラーを返す
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
  //リファラー関連END
  $body = <<< EOD
受付番号：{$acceptId}
下記の内容で【日本テクノクラーツ株式会社 イージースター】へのお問い合わせがありました。
内容を確認し、依頼者に連絡をしてください。

■依頼者ご登録内容　=============================

【 御社名 】             {$_SESSION['company_name']}
【 住所 】              {$_SESSION['address1']}
【 電話番号 】           {$_SESSION['phone_number']}
【 担当者様の氏名 】     {$_SESSION['name']}
【 フリガナ 】           {$_SESSION['furi_name']}
【 Email 】              {$_SESSION['emailadd']}
【 URL 】                {$_SESSION['url']}
【 興味のあるサービス 】 {$_SESSION['service']}
【 移行元サービス 】     {$source}
【 移行/登録先サービス 】{$output_list}
【 対象商品点数 】       約{$_SESSION['item']}商品前後
【 検討理由 】          {$discussionvalue[$_SESSION['discussion']]}
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
URL：http://www.technocrats.jp
E-mail：ec-contact@technocrats.jp
━━━━━━━━━━━━━━━━━━
EOD;

  //ここまでは PHPMailer とほぼ同じ。ここからが異なる。
  //メールの宛先
    $mailTo = 'ec-contact@technocrats.jp';
    //Return-Pathに指定するメールアドレス
    $returnMail = 'ec-contact@technocrats.jp';

    //mbstringの日本語設定
    mb_language('ja');
    mb_internal_encoding('UTF-8');

    //From ヘッダーを作成
    $header = 'From: ' . mb_encode_mimeheader($_SESSION['name']). ' <' . $_SESSION['emailadd']. '>';

    // 受付完了メールの内容の設定
    $acceptTo = $_SESSION['emailadd'];
    $acceptSubject = '受付を完了しました。【受付番号】'.$acceptId;
    $acceptHeader = 'From: ' . mb_encode_mimeheader('日本テクノクラーツ') . ' <ec-contact@technocrats.jp>';
    $acceptBody = <<< EOD
イージースターへお問い合わせありがとうございます。

受付を完了しました。

1営業日以内に折り返しメールにて、詳細を確認させていただきます。

問い合わせの際は、下記の受付番号をお申し出ください。
【受付番号】 {$acceptId}


━━━━━━━━━━━━━━━━━━
日本テクノクラーツ株式会社

東京都千代田区神田佐久間河岸84
サンユウビル1F

TEL：03-5835-5421
FAX：03-5835-5422
URL：http://www.technocrats.jp
E-mail：ec-contact@technocrats.jp
━━━━━━━━━━━━━━━━━━
EOD;

    //メールの送信、セーフモードがOnの場合は第5引数が使えない
    if(ini_get('safe_mode')){
        $result = mb_send_mail($mailTo, $subject, $body, $header);
        $acceptResult = mb_send_mail($acceptTo, $acceptSubject, $acceptBody, $acceptHeader);
    }else{
        $result = mb_send_mail($mailTo, $subject, $body, $header, '-f'. $returnMail);
        $acceptResult = mb_send_mail($acceptTo, $acceptSubject, $acceptBody, $acceptHeader, '-f'. $returnMail);
    }

   //送信結果を知らせる変数を初期化
   $message = '';

  //メール送信の結果判定
  if($result) {
    //冒頭の「1」は成功したかを判定するために使用（success_view.php で処理）
    $message = '1ありがとうございます。送信完了いたしました。';
    //成功した場合はセッションを破棄
    $_SESSION = array();   //空の配列を代入し、すべてのセッション変数を消去
    session_destroy();   //セッションを破棄
  }else{
    $message = '申し訳ございませんが、送信に失敗しました。';
  }

  $data = array();
  $data['message'] = $message;
  display('success_view.php', $data);
?>
