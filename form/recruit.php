<?php header("Content-Type:text/html;charset=utf-8"); ?>
<?php //error_reporting(E_ALL | E_STRICT);
###############################################################################################
##
#  PHPメールプログラム　メールアドレス2重チェック版
#　改造や改変は自己責任で行ってください。
#	
#  今のところ特に問題点はありませんが、不具合等がありましたら下記までご連絡ください。
#  MailAddress: info@php-factory.net
#  name: K.Numata
#  HP: https://www.php-factory.net/
#
#  重要！！サイトでチェックボックスを使用する場合のみですが。。。
#  チェックボックスを使用する場合はinputタグに記述するname属性の値を必ず配列の形にしてください。
#  例　name="当サイトをしったきっかけ[]"  として下さい。
#  nameの値の最後に[と]を付ける。じゃないと複数の値を取得できません！
##
###############################################################################################

// フォームページ内の「名前」と「メール」項目のname属性の値は特に理由がなければ以下が最適です。
// お名前 <input size="30" type="text" name="名前" />　メールアドレス <input size="30" type="text" name="Email" />
// メールアドレスのname属性の値が「Email」ではない場合、または変更したい場合は、以下必須設定箇所の「$Email」の値も変更下さい。


/*
★以下設定時の注意点　
・値（=の後）は数字以外の文字列はすべて（一部を除く）ダブルクオーテーション（"）、またはシングルクォーテーション（'）で囲んでいます。
・これをを外したり削除したりしないでください。後ろのセミコロン「;」も削除しないください。プログラムが動作しなくなります。
・またドルマーク（$）が付いた左側の文字列は絶対に変更しないでください。数字の1または0で設定しているものは必ず半角数字でお願いします。
*/


//---------------------------　必須設定　必ず設定してください　-----------------------

//サイトのトップページのURL　※デフォルトでは送信完了後に「トップページへ戻る」ボタンが表示されますので
$site_top = "https://www.technocrats.jp/";

// 管理者メールアドレス ※メールを受け取るメールアドレス(複数指定する場合は「,」で区切ってください)
$to = "saiyou@technocrats.jp";

//フォームのメールアドレス入力箇所のname属性の値（メール形式チェックに使用。※2重アドレスチェック導入時にも使用します）
$Email = "Email";

/*------------------------------------------------------------------------------------------------
以下スパム防止のための設定　※このファイルとフォームページが同一ドメイン内にある必要があります（XSS対策）
------------------------------------------------------------------------------------------------*/

//スパム防止のためのリファラチェック（フォームページが同一ドメインであるかどうかのチェック）(する=1, しない=0)
$Referer_check = 0;

//リファラチェックを「する」場合のドメイン ※以下例を参考に設置するサイトのドメインを指定して下さい。
$Referer_check_domain = "technocrats.jp";

//---------------------------　必須設定　ここまで　------------------------------------


//---------------------- 任意設定　以下は必要に応じて設定してください ------------------------

// このPHPファイルの名前 ※ファイル名を変更した場合は必ずここも変更してください。
$file_name ="recruit.php";

// 管理者宛のメールで差出人を送信者のメールアドレスにする(する=1, しない=0)
// する場合は、メール入力欄のname属性の値を「$Email」で指定した値にしてください。
//メーラーなどで返信する場合に便利なので「する」がおすすめです。
$userMail = 1;

// Bccで送るメールアドレス(複数指定する場合は「,」で区切ってください)
$BccMail = "";

// 管理者宛に送信されるメールのタイトル（件名）
$subject = "【日本テクノクラーツ株式会社 採用エントリー ご確認】";

// 送信確認画面の表示(する=1, しない=0)
$confirmDsp = 1;

// 送信完了後に自動的に指定のページ(サンクスページなど)に移動する(する=1, しない=0)
// CV率を解析したい場合などはサンクスページを別途用意し、URLをこの下の項目で指定してください。
// 0にすると、デフォルトの送信完了画面が表示されます。
$jumpPage = 1;

// 送信完了後に表示するページURL（上記で1を設定した場合のみ）※httpから始まるURLで指定ください。
$thanksPage = "https://www.technocrats.jp/confirm/recruit.html";

// 必須入力項目を設定する(する=1, しない=0)
$esse = 1;

/* 必須入力項目(入力フォームで指定したname属性の値を指定してください。（上記で1を設定した場合のみ）
値はシングルクォーテーションで囲んで下さい。複数指定する場合は「,」で区切ってください)*/
$eles = array('希望職種','経験','氏名','フリガナ','生年月日（年）','生年月日（月）','生年月日（日）','郵便番号','住所','Email','電話番号');


//----------------------------------------------------------------------
//  自動返信メール設定(START)
//----------------------------------------------------------------------

// 差出人に送信内容確認メール（自動返信メール）を送る(送る=1, 送らない=0)
// 送る場合は、フォーム側のメール入力欄のname属性の値が上記「$Email」で指定した値と同じである必要があります
$remail = 1;

//自動返信メールの送信者欄に表示される名前　※あなたの名前や会社名など（もし自動返信メールの送信者名が文字化けする場合ここは空にしてください）
$refrom_name = "";

// 差出人に送信確認メールを送る場合のメールのタイトル（上記で1を設定した場合のみ）
$re_subject = "【日本テクノクラーツ株式会社 採用エントリー　ご確認】";

//フォーム側の「名前」箇所のname属性の値　※自動返信メールの「○○様」の表示で使用します。
//指定しない、または存在しない場合は、○○様と表示されないだけです。あえて無効にしてもOK
$dsp_name = '氏名';

//自動返信メールの文言 ※日本語部分は変更可です
$remail_text = <<< TEXT

この度は、採用にご応募いただきましてありがとうございます。
以下の内容で確かに承りました。
追って当社採用担当者よりご連絡させていただきます。

TEXT;


//自動返信メールに署名を表示(する=1, しない=0)※管理者宛にも表示されます。
$mailFooterDsp = 1;

//上記で「1」を選択時に表示する署名（FOOTER〜FOOTER;の間に記述してください）
$mailSignature = <<< FOOTER

━━━━━━━━━━━━━━━━━━
日本テクノクラーツ株式会社
 
東京都千代田区神田佐久間河岸84
サンユウビル1F
 
TEL：03-5835-5421
FAX：03-5835-5422
URL：https://www.technocrats.jp
E-mail：saiyou@technocrats.jp
━━━━━━━━━━━━━━━━━━

FOOTER;


//----------------------------------------------------------------------
//  自動返信メール設定(END)
//----------------------------------------------------------------------


//メールアドレスの形式チェックを行うかどうか。(する=1, しない=0)
//※デフォルトは「する」。特に理由がなければ変更しないで下さい。メール入力欄のname属性の値が上記「$Email」で指定した値である必要があります。
$mail_check = 1;


//----------------------------------------------------------------------
// メールアドレス2重チェック用設定 （START）
//----------------------------------------------------------------------

//メールアドレス2重チェックする？(する=1, しない=0)
$mail_2check = 1;

//確認メールアドレス入力箇所のname属性の値（2重チェックに使用）
$ConfirmEmail = "Email（確認）";

/*　
確認用のメールアドレスはあくまでメールアドレスが一致するかチェックするだけです。
管理者宛メール、送信者宛メール（自動返信）の本文内には表示されません
*/

//----------------------------------------------------------------------
// メールアドレス2重チェック用設定 （END）
//----------------------------------------------------------------------


//------------------------------- 任意設定ここまで ---------------------------------------------



// 以下の変更は知識のある方のみ自己責任でお願いします。

//----------------------------------------------------------------------
//  関数定義(START)
//----------------------------------------------------------------------
function checkMail($str){
	$mailaddress_array = explode('@',$str);
	if(preg_match("/^[\.!#%&\-_0-9a-zA-Z\?\/\+]+\@[!#%&\-_0-9a-z]+(\.[!#%&\-_0-9a-z]+)+$/", "$str") && count($mailaddress_array) ==2){
		return true;
	}
	else{
		return false;
	}
}
function h($string) {
  return htmlspecialchars($string, ENT_QUOTES,'utf-8');
}
function sanitize($arr){
	if(is_array($arr)){
		return array_map('sanitize',$arr);
	}
	return str_replace("\0","",$arr);
}
if(isset($_GET)) $_GET = sanitize($_GET);//NULLバイト除去//
if(isset($_POST)) $_POST = sanitize($_POST);//NULLバイト除去//
if(isset($_COOKIE)) $_COOKIE = sanitize($_COOKIE);//NULLバイト除去//

//----------------------------------------------------------------------
//  関数定義(END)
//----------------------------------------------------------------------

if($Referer_check == 1 && !empty($Referer_check_domain)){
	if(strpos($_SERVER['HTTP_REFERER'],$Referer_check_domain) === false){
		echo '<p align="center">リファラチェックエラー。フォームページのドメインとこのファイルのドメインが一致しません</p>';exit();
	}
}
$sendmail = 0;
$empty_flag = 0;
$post_mail = '';

foreach($_POST as $key=>$val) {
  if($val == "confirm_submit") $sendmail = 1;
	if($key == $Email && $mail_check == 1){
	  if(!checkMail($val)){
          $errm .= "<p class=\"error_messe\">「".$key."」はメールアドレスの形式が正しくありません。</p>\n";
          $empty_flag = 1;
	  }else{
		  $post_mail = h($val);
	  }
	}
	//メール2重チェック用確認メールアドレス取得
	if($key == $ConfirmEmail){
		$post_mail2 = h($val);
	}
}

	//----------------------------------------------------------------------
	//  メール2重チェック(BEGIN)
	//----------------------------------------------------------------------
	if(!empty($post_mail) && $post_mail != $post_mail2 && $mail_2check == 1){
			  $errm .= "<p class=\"error_messe\">確認メールアドレスが一致しません。</p>\n";
			  $empty_flag = 1;
	}
	//----------------------------------------------------------------------
	//  メール2重チェック(BEGIN)
	//----------------------------------------------------------------------

// 必須設定項目のチェック
if($esse == 1) {
  $length = count($eles) - 1;
  foreach($_POST as $key=>$val) {
    
    if($val == "confirm_submit") ;
    else {
      for($i=0; $i<=$length; $i++) {
        if($key == $eles[$i] && empty($val)) {
          $errm .= "<p class=\"error_messe\">「".$key."」は必須入力項目です。</p>\n";
          $empty_flag = 1;
        }
      }
    }
  }
  foreach($_POST as $key=>$val) {
    
    for($i=0; $i<=$length; $i++) {
      if($key == $eles[$i]) {
        $eles[$i] = "confirm_ok";
      }
    }
  }
  for($i=0; $i<=$length; $i++) {
    if($eles[$i] != "confirm_ok") {
      $errm .= "<p class=\"error_messe\">「".$eles[$i]."」が未選択です。</p>\n";
      $eles[$i] = "confirm_ok";
      $empty_flag = 1;
    }
  }
}
// 管理者宛に届くメールの編集
$body="下記の内容で".$subject."のご依頼がありました。\n内容を確認し、依頼者に連絡をしてください。\n\n";
$body.="■依頼者ご登録内容　=============================\n\n";
foreach($_POST as $key=>$val) {
  
  $out = '';
  if(is_array($val)){
  foreach($val as $item){ 
  $out .= $item . ','; 
  }
  if(substr($out,strlen($out) - 1,1) == ',') { 
  $out = substr($out, 0 ,strlen($out) - 1); 
  }
 }else { $out = $val;} //チェックボックス（配列）追記ここまで
  if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
  if($out == "confirm_submit" or $key == "httpReferer" or $key == $ConfirmEmail) ;
  else $body.="【 ".$key." 】 ".$out."\n";
}
$body.="\n=============================\n";
$body.="送信された日時：".date( "Y/m/d (D) H:i:s", time() )."\n";
$body.="送信者のIPアドレス：".$_SERVER["REMOTE_ADDR"]."\n";
$body.="送信者のホスト名：".getHostByAddr(getenv('REMOTE_ADDR'))."\n";
$body.="お問い合わせのページURL：".$_POST['httpReferer']."\n";
if($mailFooterDsp == 1) $body.= $mailSignature;
//--- 管理者宛に届くメールの編集終了 --->


if($remail == 1) {
//--- 差出人への自動返信メールの編集
if(isset($_POST[$dsp_name])){ $rebody = h($_POST[$dsp_name]). " 様\n";}
$rebody.= $remail_text;
$rebody.="\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
foreach($_POST as $key=>$val) {
  
  $out = '';
  if(is_array($val)){
  foreach($val as $item){ 
  $out .= $item . ','; 
  }
  if(substr($out,strlen($out) - 1,1) == ',') { 
  $out = substr($out, 0 ,strlen($out) - 1); 
  }
 }else { $out = $val; }//チェックボックス（配列）追記ここまで
  if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
  if($out == "confirm_submit" or $key == "httpReferer" or $key == $ConfirmEmail) ;
  else $rebody.="【 ".$key." 】 ".$out."\n";
}
$rebody.="\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
$rebody.="送信日時：".date( "Y/m/d (D) H:i:s", time() )."\n";
if($mailFooterDsp == 1) $rebody.= $mailSignature;
$reto = $post_mail;
$rebody=mb_convert_encoding($rebody,"JIS","utf-8");
$re_subject="=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($re_subject,"JIS","utf-8"))."?=";

	if(!empty($refrom_name)){
	
		$default_internal_encode = mb_internal_encoding();
		if($default_internal_encode != 'UTF-8'){
		  mb_internal_encoding('UTF-8');
		}
		$reheader="From: ".mb_encode_mimeheader($refrom_name)." <".$to.">\nReply-To: ".$to."\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
	
	}else{
		$reheader="From: $to\nReply-To: ".$to."\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
	}
}
$body=mb_convert_encoding($body,"JIS","utf-8");
$subject="=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($subject,"JIS","utf-8"))."?=";

if($userMail == 1 && !empty($post_mail)) {
  $from = $post_mail;
  $header="From: $from\n";
	  if($BccMail != '') {
		$header.="Bcc: $BccMail\n";
	  }
	$header.="Reply-To: ".$from."\n";
}else {
	  if($BccMail != '') {
		$header="Bcc: $BccMail\n";
	  }
	$header.="Reply-To: ".$to."\n";
}
	$header.="Content-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
  

if(($confirmDsp == 0 || $sendmail == 1) && $empty_flag != 1){
  mail($to,$subject,$body,$header);
  if($remail == 1) { mail($reto,$re_subject,$rebody,$reheader); }
}
else if($confirmDsp == 1){ 


/*　▼▼▼送信確認画面のレイアウト※編集可　オリジナルのデザインも適用可能▼▼▼　*/
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="robots" content="index,follow">
<title>プライバシーポリシー｜日本テクノクラーツ株式会社</title>
<meta name="description" content="">
<link rel="shortcut icon" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="../common/css/reset.css" media="all">
<link rel="stylesheet" type="text/css" href="../common/css/common.css" media="all">
<script type="text/javascript" src="../common/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="../common/js/common.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-42863547-2', 'technocrats.jp');
  ga('send', 'pageview');

</script>

</head>
<body id="form">
<div class="whiteWrap">
	<header>
		<div id="header">
			<div id="headerInner">
				<p class="logo"><a href="../index.html"><img src="../common/images/logo01.png" alt="日本テクノクラーツ株式会社" width="250" height="36"></a></p>
				<div id="siteSearch">
					<form action="https://www.google.com/cse">
						<span id="srchtxtBg">
							<input type="hidden" name="cx" value="002981879892081928293:pkfmkdducru">
						<input name="q" id="srchtxt" type="text" class="jq-placeholder" title="サイト内検索">
						</span>
						<input type="image" src="/common/images/search_bt01.png" id="srchbtn" alt="検索" onMouseOver="this.src='/common/images/search_bt01on.png'" onMouseOut="this.src='/common/images/search_bt01.png'">
					</form>
				</div>
				<nav>
					<ul id="globalNav">
						<li><a href="../index.html"><img src="../common/images/nav01.png" width="82" height="40" alt="HOME"></a></li>
						<li><a href="../solution/index.html"><img src="../common/images/nav02.png" width="193" height="40" alt="ソリューションサービス"></a>
							<ul class="child01">
								<li><a href="../solution/system.html"><img src="../common/images/nav01-01.png" width="260" height="91" alt="システムインテグレーション"></a></li>
								<li><a href="../solution/privacy.html"><img src="../common/images/nav01-02.png" width="260" height="93" alt="プライバシーマーク取得支援"></a></li>
								<li><a href="../solution/netshop.html"><img src="../common/images/nav01-03.png" width="260" height="95" alt="ネットショップ商品データ移行"></a></li>
							</ul>
						</li>
						<li><a href="../company/index.html"><img src="../common/images/nav03.png" width="96" height="40" alt="会社情報"></a>
							<ul class="child02">
								<li><a href="../company/index.html"><img src="../common/images/nav02-01.png" width="260" height="91" alt="会社概要／アクセス"></a></li>
								<li><a href="../company/policy.html"><img src="../common/images/nav02-03.png" width="260" height="91" alt="企業理念"></a></li>
							</ul>
						</li>
						<li><a href="../recruit/index.html"><img src="../common/images/nav04.png" width="97" height="40" alt="採用情報"></a>
							<ul class="child03">
								<li><a href="../recruit/index.html"><img src="../common/images/nav03-01.png" width="248" height="95" alt="採用情報　エントリー受付"></a></li>
							</ul>
						</li>
					</ul>
					<p class="inqBt"><a href="../inquiry/index.html"><img src="../common/images/bt02.png" width="250" height="30" alt="お問い合わせ" class="rollover"></a></p>
				</nav>
			</div>
		</div>
	</header>

<?php if($empty_flag == 1){ ?>

<div class="contentsWrap">
	<div class="contents">
<h2 class="heading01">採用エントリー</h2>
			<h3 class="heading01-2 mb15">入力内容をご確認ください。</h3>
			<div class="columnLayout mb00">
				<div class="colLeft w525">
					<p class="lead01 mb00">■ご記入項目が不足しています。</p>
					<p class="lead01">■必須項目<span class="f-red">（※）</span>をご確認ください。未入力箇所がありますと送信できません。</p>
				</div>
				<div class="colRight formSize02">
					<div class="formSecureBox"><script src=https://seal.verisign.com/getseal?host_name=www.technocrats.jp&size=S&use_flash=YES&use_transparent=YES&lang=ja></script></div>
				</div>
			</div>
			<form action="https://www.technocrats.jp/cgi-bin/formmail.cgi" method="post">
				<input type="hidden" name="CGImode" value="1">
				<input type="hidden" name="keywords" value="pMark">
				<div class="formWrap error mb35">
					<p><?php echo $errm; ?></p>
					<!--INFO END--> 
				</div>
				
				<p class="aC"><a href="javascript:history.back();"><img src="../common/images/bt09.png" alt="戻る" width="207" height="51" class="rollover"></a></p>
			</form>
			<!-- /contents --></div>
	<nav>
		<div class="subNav">
<ul class="subNavigation">
					<li><a href="../info/privacy.html">プライバシーポリシー</a></li>
					<li><a href="../info/about.html">当サイトについて</a></li>
				</ul>
				<!-- /subNav --></div>
	</nav>
	<!-- /contentsWrap --> 
</div>

<?php
		}else{
?>

<div class="contentsWrap">
	<div class="contents">
<h2 class="heading01">採用エントリー</h2>
			<h3 class="heading01-2 mb15">入力内容をご確認ください。</h3>
			<div class="columnLayout mb00">
				<div class="colLeft w525">
					<p class="lead01">下記の内容をご確認の上、『送信ボタン』をクリックしてください。<br>
					内容を修正する場合には『戻るボタン』で入力画面に戻ります。。</p>
				</div>
				<div class="colRight formSize02">
					<div class="formSecureBox"><script src=https://seal.verisign.com/getseal?host_name=www.technocrats.jp&size=S&use_flash=YES&use_transparent=YES&lang=ja></script></div>
				</div>
			</div>

<form action="<?php echo $file_name; ?>" method="POST">

				<div class="formWrap mb35">
<table class="formBox">
<?php
foreach($_POST as $key=>$val) {
  $out = '';
  if(is_array($val)){
  foreach($val as $item){ 
  $out .= $item . ','; 
  }
  if(substr($out,strlen($out) - 1,1) == ',') { 
  $out = substr($out, 0 ,strlen($out) - 1); 
  }
 }
  else { $out = $val; }//チェックボックス（配列）追記ここまで
  if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
  $out = h($out);
  $out=nl2br($out);//※追記 改行コードを<br>タグに変換
  $key = h($key);
  print("<tr><th class=\"l_Cel\">".$key."</th><td>".$out);
  $out=str_replace("<br />","",$out);//※追記 メール送信時には<br>タグを削除

?>
<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $out; ?>">
<?php
  print("</td></tr>\n");
}
?>
</table><br>

				</div>

				<p class="aC imgButton"><a href="javascript:history.back();"><img src="../common/images/bt09.png" alt="戻る" width="207" height="51" class="rollover mr75"></a><input type="image" class="rollover" onClick="this.form.submit(); return false;" src="../common/images/bt04.png" alt="送信"></p>
<input type="hidden" name="mail_set" value="confirm_submit">
<input type="hidden" name="httpReferer" value="<?php echo $_SERVER['HTTP_REFERER'] ;?>">

			</form>
			<!-- /contents --></div>
	<nav>
		<div class="subNav">
<ul class="subNavigation">
					<li><a href="../info/privacy.html">プライバシーポリシー</a></li>
					<li><a href="../info/about.html">当サイトについて</a></li>
				</ul>
				<!-- /subNav --></div>
	</nav>
	<!-- /contentsWrap --> 
</div>

<?php if(!empty($copyrights)) echo $copyrights; } //著作権表記削除禁止（要申請）?>

	<footer>
		<div id="footerLogoArea">
			<div class="footerInner">
				<div class="columnLayout">
					<div class="colLeft">
						<p class="mt08"><img src="../common/images/footer_logo.png" width="207" height="33" alt="日本テクノクラーツ株式会社"></p>
					</div>
					
					
				</div>
				<!-- /footerInner --> 
			</div>
			<!-- /footer --> 
		</div>
		<div id="footer">
			<div class="footerInner">
				<nav>
					<div class="columnLayout">
						<div class="colLeft fcol01">
							<p class="heading05"><a href="../solution/index.html">ソリューションサービス</a></p>
							<ul class="footerLink02">
								<li><a href="../solution/system.html">システムインテグレーション</a></li>
								<li><a href="../solution/privacy.html">プライバシーマーク取得支援</a></li>
								<li><a href="../solution/netshop.html">商品データ移行</a></li>
							</ul>
						</div>
						<div class="colLeft fcol02">
							<p class="heading05"><a href="../company/index.html">会社情報</a></p>
							<ul class="footerLink02">
										<li><a href="../company/index.html">会社概要／アクセス</a></li>
										<li><a href="../company/policy.html">企業理念</a></li>
									</ul>
						</div>
						<div class="colLeft fcol03">
							<p class="heading05"><a href="../recruit/index.html">採用情報</a></p>
							<ul class="footerLink02">
								<li><a href="../recruit/index.html">採用情報／エントリー受付</a></li>
							</ul>
						</div>
						<div class="colLeft fcol04">
							<ul class="footerLink01">
								<li><a href="../inquiry/index.html">お問い合わせ</a></li>
								<li><a href="../info/privacy.html">プライバシーポリシー</a></li>
								<li><a href="../info/about.html">当サイトについて</a></li>
							</ul>
						</div>
					</div>
				</nav>
				<!-- /footerInner --> 
			</div>
			<!-- /footer --> 
		</div>
		<div id="footer2">
			<div class="footerInner">
				<p id="copyright">&copy; NIHON TECHNOCRATS CORPORATION All right reserved 2013</p>
				<!-- /footerInner --> 
			</div>
			<!-- /footer --> 
		</div>
		<div class="pagetop_FIX"><a href="#header"><img src="../common/images/bt01_btn.png" alt="このページの上部へ" width="51" height="51"></a></div>
	</footer>
	<!-- /whiteWrap --> 
</div>
</body>
</html>


<?php
/* ▲▲▲送信確認画面のレイアウト　※オリジナルのデザインも適用可能▲▲▲　*/
}

if(($jumpPage == 0 && $sendmail == 1) || ($jumpPage == 0 && ($confirmDsp == 0 && $sendmail == 0))) { 

/* ▼▼▼送信完了画面のレイアウト　編集可 ※送信完了後に指定のページに移動しない場合のみ表示▼▼▼　*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>お問い合わせ完了画面</title>
</head>
<body>
<div align="center">
<?php if($empty_flag == 1){ ?>
<h3>入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h3><?php echo $errm; ?><br><br><input type="button" value=" 前画面に戻る " onClick="history.back()">
<?php
  }else{
?>
送信ありがとうございました。<br>
送信は正常に完了しました。<br><br>
<a href="<?php echo $site_top ;?>">トップページへ戻る⇒</a>
</div>
<?php if(!empty($copyrights)) echo $copyrights;?>
<!--  CV率を計測する場合ここにAnalyticsコードを貼り付け -->
</body>
</html>
<?php 
/* ▲▲▲送信完了画面のレイアウト 編集可 ※送信完了後に指定のページに移動しない場合のみ表示▲▲▲　*/
  }
}
//完了時、指定のページに移動する設定の場合、指定ページヘリダイレクト
else if(($jumpPage == 1 && $sendmail == 1) || $confirmDsp == 0) { 
	 if($empty_flag == 1){ ?>
<div align="center"><h3>入力にエラーがあります。下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h3><?php echo $errm; ?><br><br><input type="button" value=" 前画面に戻る " onClick="history.back()"></div>
<?php }else{ header("Location: ".$thanksPage); }
} ?>
