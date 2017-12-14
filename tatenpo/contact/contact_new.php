<?php
  session_start();   //セッションを開始
  //session_start()関数は、必ず、Webブラウザへの出力が行われる前に、呼び出す必要がある

  session_regenerate_id(TRUE);    //セッションIDを変更（セッションハイジャック対策）

  require 'functions.php';   //テンプレートエンジンの読み込み

  //テンプレートに渡す変数の初期化
  $data = array(); //配列を初期化
  //初回以外ですでにセッション変数に値が代入されていれば、その値を。そうでなければNULLで初期化
  //（contact1_view.phpを表示する際、最初は入力データがないのでこの初期化をしないとエラーとなる）
  $data['company_name'] = isset($_SESSION['company_name']) ? $_SESSION['company_name'] : NULL;
  $data['address1'] = isset($_SESSION['address1']) ? $_SESSION['address1'] : NULL;
  $data['phone_number'] = isset($_SESSION['phone_number']) ? $_SESSION['phone_number'] : NULL;
  $data['department'] = isset($_SESSION['department']) ? $_SESSION['department'] : NULL;
  $data['name'] = isset($_SESSION['name']) ? $_SESSION['name'] : NULL;
  $data['furi_name'] = isset($_SESSION['furi_name']) ? $_SESSION['furi_name'] : NULL;
  $data['emailadd'] = isset($_SESSION['emailadd']) ? $_SESSION['emailadd'] : NULL;
  $data['emailreq'] = isset($_SESSION['emailreq']) ? $_SESSION['emailreq'] : NULL;
  $data['url'] = isset($_SESSION['url']) ? $_SESSION['url'] : NULL;
  $data['service'] = isset($_SESSION['service']) ? $_SESSION['service'] : NULL;
  $data['source'] = isset($_SESSION['source']) ? $_SESSION['source'] : NULL;
  $data['output'] = isset($_SESSION['output']) ? $_SESSION['output'] : NULL;
  $data['item'] = isset($_SESSION['item']) ? $_SESSION['item'] : NULL;
  $data['estimate'] = isset($_SESSION['estimate']) ? $_SESSION['estimate'] : NULL;
  $data['demo'] = isset($_SESSION['demo']) ? $_SESSION['demo'] : NULL;
$data['startdate'] = isset($_SESSION['startdate']) ? $_SESSION['startdate'] : date('n');
$data['startdate2'] = isset($_SESSION['startdate2']) ? $_SESSION['startdate2'] : "指定なし";
$data['discussion'] = isset($_SESSION['discussion']) ? $_SESSION['discussion'] : "指定なし";
  $data['body'] = isset($_SESSION['body']) ? $_SESSION['body'] : NULL;
  //CSRF対策の固定トークンを生成
  if(!isset($_SESSION['ticket'])){
    //セッション変数にトークンを代入
    $_SESSION['ticket'] = sha1(uniqid(mt_rand(), TRUE));
  }

  //トークンをテンプレートに渡す
  $data['ticket'] = $_SESSION['ticket'];

  $ua = $_SERVER['HTTP_USER_AGENT'];
  if ((strpos($ua, 'Android') !== false) && (strpos($ua, 'Mobile') !== false) || (strpos($ua, 'iPhone') !== false) || (strpos($ua, 'Windows Phone') !== false) || (strpos($ua, 'iPad') !== false)) {
    // スマートフォンからアクセスされた場合
    display('contact_view_smp_new.php', $data);  //通常テンプレートの表示
  } else {
    display('contact_view_new.php', $data);  //通常テンプレートの表示
  }

  //functions.php の display() を呼び出すと $data はエスケープ処理され、include によりテンプレートが読み込まれ表示される
?>
