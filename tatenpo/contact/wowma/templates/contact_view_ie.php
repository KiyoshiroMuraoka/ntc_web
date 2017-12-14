<!doctype html>
<html lang="ja">
<head>
<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="./js/jquery.validationEngine.js"></script>
<script src="./js/jquery.validationEngine-ja.js"></script>
<link href="./skins/square/pink.css" rel="stylesheet">
<link href="./style.css" rel="stylesheet">
<script src="./js/icheck.min.js"></script>
<script src="./js/ajaxzip3.js" charset="UTF-8"></script>
<link rel="stylesheet" href="./js/validationEngine.jquery.css">
<script>
$(function() {
    jQuery("#form").validationEngine();
});
</script>
<script>
$(function(){
  $("#service-1").click(function(){
    $('#sourcebody').removeClass('invisible');
    $('#sourcebody2').removeClass('invisible');
    $('#sourcebody3').addClass('invisible');
  });
  $("#service-2").click(function(){
    $('#sourcebody').addClass('invisible');
    $('#sourcebody2').addClass('invisible');
    $('#sourcebody3').removeClass('invisible');
  });
});
</script>
</head>
<body>
<div id="contents">
<!--入力値にエラーがあった場合エラーメッセージを表示-->
<div id="errorDispaly">
<?php
  if(isset($error)) {
    foreach($error as $val) {
      echo $val . '<br />';
    }
    echo '<br />';
  }
?>
<!--End of #errorDispaly--></div>
<div id="formArea">
<fieldset id="contactForm">
<!-- <legend class="contact_form">お問い合わせフォーム</legend> -->
 <br />
<br />
<form action="confirm.php" method="post" role="form" id="form">
  <span class="tcell-l"><label for="company_name"><strong>御社名</strong>&nbsp;(必須)</label></span>
  <span class="tcell-r"><input type="text" name="company_name" id="company_name" value="<?php echo $company_name; ?>" size="50" class="form-control textbox validate[required]" /></span>
 
  <br />

  <span class="tcell-l"><label for="postcode">郵便番号</label></span>
  <span class="tcell-r"><input type="text" name="postcode" id="email" value="<?php echo $postcode; ?>" size="50" class="form-control textbox validate[maxSize[8],minSize[7]] boxmin" onKeyUp="AjaxZip3.zip2addr(this,'','address1','address1');" /></span>

  <br />

  <span class="tcell-l"><label for="address1"><strong>住所1</strong>&nbsp;(必須)</label></span>
  <span class="tcell-r"><input type="text" name="address1" id="address1" value="<?php echo $address1; ?>" size="50" class="form-control textbox validate[required]" /></span>

  <br />

  <span class="tcell-l"><label for="address2">住所2</label></span>
  <span class="tcell-r"><input type="text" name="address2" id="address2" value="<?php echo $address2; ?>" size="50" class="form-control textbox" /></span>

  <br />

  <span class="tcell-l"><label for="phone_number"><strong>電話番号</strong>&nbsp;(必須)</label></span>
  <span class="tcell-r"><input type="text" name="phone_number" id="phone_number" value="<?php echo $phone_number; ?>" size="20" class="form-control textbox validate[required,maxSize[13],minSize[10]] boxmin" placeholder="ハイフン省略可" /></span>

  <br />

  <span class="tcell-l"><label for="department">所属部署</label></span>
  <span class="tcell-r"><input type="text" name="department" id="department" value="<?php echo $department; ?>" size="50" class="form-control textbox boxmin" /></span>

  <br />

  <span class="tcell-l"><label for="name">担当者様の氏名</label></span>
  <span class="tcell-r"><input type="text" name="name" id="name" value="<?php echo $name; ?>" size="50" class="form-control textbox" /></span>

  <br />

  <span class="tcell-l"><label for="furi_name">ふりがな</label></span>
  <span class="tcell-r"><input type="text" name="furi_name" id="furi_name" value="<?php echo $furi_name; ?>" size="50" class="form-control textbox" /></span>

  <br />

  <span class="tcell-l"><label for="emaiaddl"><strong>E-mailアドレス</strong>&nbsp;(必須)</label></span>
  <span class="tcell-r"><input type="text" name="emailadd" id="emailadd" value="<?php echo $emailadd; ?>" size="50" class="form-control textbox validate[required,custom[email]]" /></span>

  <br />

  <span class="tcell-l"><label for="emailreq">E-mailアドレス確認</label></span>
  <span class="tcell-r"><input type="text" name="emailreq" id="emailreq" value="<?php echo $emailreq; ?>" size="50" class="form-control textbox validate[required,equals[emailadd]]" oncopy="return false" onpaste="return false" oncontextmenu="return false" /></span>

  <br />

  <span class="tcell-l"><label for="url">URL</label></span>
  <span class="tcell-r"><input type="text" name="url" id="url" value="<?php echo $url; ?>" size="50" class="form-control textbox validate[custom[url]]" /></span>

  <br />

  <span class="tcell-l"><label for="service">興味のあるサービス</label></span>
  <span class="tcell-r">
		<ul class="bg_radiobox">
			<span id="service-1"><li><input type="radio" name="service" value="ChangeOver" class="form-control"<?php if($service=="ChangeOver"){echo ' checked="checked"';}?> /><label for="service-1" class="check">&nbsp;商品データ移行サービス</label></li></span><br />
		  <span id="service-2"><li><input type="radio" name="service" value="RegularPurchases" class="form-control"<?php if($service=="RegularPurchases"){echo ' checked="checked"';}?> /><label for="service-2" class="check">&nbsp;商品定期登録サービス</label></li></span>
		</ul>
	</span>

  <br />
  
  <div id="sourcebody">
  <span class="tcell-l"><label for="source">移行元モール</label></span>
  <span class="tcell-r" >
		<ul class="bg_radiobox">
			<span id="base-1"><li><input type="radio" name="source" value="base-rk" class="form-control source"<?php if($source=="base-rk"){echo ' checked="checked"';}?> /><label for="base-1" class="check">&nbsp;楽天市場</label></li></span><br />
	  	<span id="base-2"><li><input type="radio" name="source" value="base-yh" class="form-control source"<?php if($source=="base-yh"){echo ' checked="checked"';}?> /><label for="base-2" class="check">&nbsp;Yahoo! ショッピング</label></li></span><br />
			<span id="base-3"><li><input type="radio" name="source" value="base-am" class="form-control source"<?php if($source=="base-am"){echo ' checked="checked"';}?> /><label for="base-3" class="check">&nbsp;Amazon</label></li></span><br />
			<span id="base-4"><li><input type="radio" name="source" value="base-pm" class="form-control source"<?php if($source=="base-pm"){echo ' checked="checked"';}?> /><label for="base-4" class="check">&nbsp;ポンパレモール</label></li></span><br />
			<span id="base-5"><li><input type="radio" name="source" value="base-bd" class="form-control source"<?php if($source=="base-bd"){echo ' checked="checked"';}?> /><label for="base-5" class="check">&nbsp;bidders / au ショッピングモール</label></li></span><br />
			<span id="base-6"><li><input type="radio" name="source" value="base-etc" class="form-control source"<?php if($source=="base-etc"){echo ' checked="checked"';}?> /><label for="base-6" class="check">&nbsp;その他自社カート等</label></li></span><br />
  	</ul>
  </span><br />
	<br />
  </div>
	<span class="tcell-l"><label for="source"><div id="sourcebody2">移行先モール</div><div id="sourcebody3" class="invisible">登録先モール</div></label></span>
  <span class="tcell-r" >
		<ul class="bg_checkbox">
			<span id="output-1"><li><input type="checkbox" name="output[]" value="rk" class="form-control source"<?php if(is_array($output)){if(in_array("rk",$output)){echo ' checked="checked"';}}?> /><label for="output-1" class="check">&nbsp;楽天市場</label></li></span><br />
	  	<span id="output-2"><li><input type="checkbox" name="output[]" value="yh" class="form-control source"<?php if(is_array($output)){if(in_array("yh",$output)){echo ' checked="checked"';}}?> /><label for="output-2" class="check">&nbsp;Yahoo! ショッピング</label></li></span><br />
			<span id="output-3"><li><input type="checkbox" name="output[]" value="am" class="form-control source"<?php if(is_array($output)){if(in_array("am",$output)){echo ' checked="checked"';}}?> /><label for="output-3" class="check">&nbsp;Amazon</label></li></span><br />
			<span id="output-4"><li><input type="checkbox" name="output[]" value="pm" class="form-control source"<?php if(is_array($output)){if(in_array("pm",$output)){echo ' checked="checked"';}}?> /><label for="output-4" class="check">&nbsp;ポンパレモール</label></li></span><br />
			<span id="output-5"><li><input type="checkbox" name="output[]" value="bd" class="form-control source"<?php if(is_array($output)){if(in_array("bd",$output)){echo ' checked="checked"';}}?> /><label for="output-5" class="check">&nbsp;bidders / au ショッピングモール</label></li></span><br />
			<span id="output-6"><li><input type="checkbox" name="output[]" value="etc" class="form-control source"<?php if(is_array($output)){if(in_array("etc",$output)){echo ' checked="checked"';}}?> /><label for="output-6" class="check">&nbsp;その他自社カート等</label></li></span><br />
  	</ul>
  </span>

  <br />

  <span class="tcell-l"><label for="item">対象商品点数（目安）</label></span>
  <span class="tcell-r">約 <input type="text" name="item" id="item" value="<?php echo $item; ?>" class="form-control numbox textmain validate[custom[number]]" /> 商品前後</span>
	
	<br />
	<span class="tcell-l">&nbsp;</span>
	<span class="tcell-r"><ul class="bg_checkbox">
		<span id="estimate"><li><input type="checkbox" name="estimate" value="estimate" class="form-control source"<?php if($estimate=="estimate"){echo ' checked="checked"';}?> /><label for="estimate" class="check">&nbsp;見積もり希望</label></li></span>
	</ul>
	</span>
	
  <br />
  
  <span class="tcell-l" style="vertical-align: top;"><label for="body">ご質問・ご要望など</label></span>
  <span class="tcell-r"><textarea name="body" id="body" cols="50" rows="8" class="form-control textbox textmain" placeholder="例：HP拝見しました。移行サービスの御見積を希望致します。"><?php echo $body; ?></textarea></span>

  <br />
 
  <span class="tcell-l">画像認証<!--画像認証（Securimage）--></span>

  <table style="border:0px; width:290px;" class="tcell-r" cellpadding="0" cellspacing="0"><tr><td rowspan="2"><img id="captcha" src="./securimage/securimage_show.php" alt="CAPTCHA Image" style="margin-top:12px;" /></td><td style="height:40px; margin:0px; padding:0px;">　<a href="#" onclick="document.getElementById('captcha').src = './securimage/securimage_show.php?' + Math.random(); return false"><img src="./securimage/images/refresh.png" alt="別の画像を表示" height="25" class="vertical-align:bottom;" id="refresh" /></a></td></tr><tr><td style="height:42px; margin:0px; padding:0px;"><input type="text" name="captcha_code" id="captcha_code" size="14" maxlength="6" class="form-control textbox keyword validate[required,maxSize[6],minSize[6],custom[onlyLetterNumber]]" /></td></tr></table>
  
  <!--画像認証ここまで）-->
  
  <br />

  <div align="center"><input class="btn btn-default submit" type="submit" value="確認画面へ" /></div>
 
  <!--確認ページへトークンをPOSTする、隠しフィールド「ticket」-->
  <input type="hidden" name="ticket" value="<?php echo $ticket; ?>" />
</form>
 
</fieldset>
<!--End of #formArea--></div>
 
<!--end of #contents--> </div>
</body>
</html>