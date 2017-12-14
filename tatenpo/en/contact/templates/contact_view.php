<!doctype html>
<html lang="ja">
<head>
<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script src="./js/jquery-1.8.2.min.js"></script>
<script src="./js/jquery.validationEngine.js"></script>
<script src="./js/jquery.validationEngine-ja.js"></script>
<link href="./skins/square/pink.css" rel="stylesheet">
<link href="./style.css" rel="stylesheet">
<script src="./js/icheck.min.js"></script>
<script src="../js/ajaxzip3.js" charset="UTF-8"></script>
<link rel="stylesheet" href="./js/validationEngine.jquery.css">
<script>
$(function() {
    jQuery("#form").validationEngine();
});
</script>
</head>
<body>
<div id="contents">
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
 <br />
<br />
<form action="confirm.php" method="post" role="form" id="form">
  <span class="tcell-l"><label for="company_name"><strong>Company name</strong>&nbsp;(necessary)</label></span>
  <span class="tcell-r"><input type="text" name="company_name" id="company_name" value="<?php echo $company_name; ?>" size="50" class="form-control textbox validate[required]" /></span>
 
  <br />

  <span class="tcell-l"><label for="country">Country</label></span>
  <span class="tcell-r"><input type="text" name="country" id="country" value="<?php echo $country; ?>" size="50" class="form-control textbox" /></span>

  <br />

  <span class="tcell-l"><label for="name">Supervisor's name&nbsp;(necessary)</label></span>
  <span class="tcell-r"><input type="text" name="name" id="name" value="<?php echo $name; ?>" size="50" class="form-control textbox" /></span>

  <br />

  <span class="tcell-l"><label for="emaiaddl"><strong>E-mail address</strong>&nbsp;(necessary)</label></span>
  <span class="tcell-r"><input type="text" name="emailadd" id="emailadd" value="<?php echo $emailadd; ?>" size="50" class="form-control textbox validate[required,custom[email]]" /></span>

  <br />

  <span class="tcell-l"><label for="emailreq">Confirm e-mail address</label></span>
  <span class="tcell-r"><input type="text" name="emailreq" id="emailreq" value="<?php echo $emailreq; ?>" size="50" class="form-control textbox validate[required,equals[emailadd]]" oncopy="return false" onpaste="return false" oncontextmenu="return false" /></span>

  <br />

  <span class="tcell-l"><label for="url">Shop URL</label></span>
  <span class="tcell-r"><input type="text" name="url" id="url" value="<?php echo $url; ?>" size="50" class="form-control textbox validate[custom[url]]" /></span>

  <br />

  <span class="tcell-l"><label for="service">Services of interest</label></span>
  <span class="tcell-r">
		<ul class="bg_radiobox">
			<span id="service-1"><li><input type="radio" name="service" value="ChangeOver" class="form-control"<?php if($service=="ChangeOver"){echo ' checked="checked"';}?> /><label for="service-1" class="check">&nbsp;Product data transfer</label></li></span><br />
		  <span id="service-2"><li><input type="radio" name="service" value="RegularPurchases" class="form-control"<?php if($service=="RegularPurchases"){echo ' checked="checked"';}?> /><label for="service-2" class="check">&nbsp;Limited-time product registration service</label></li></span>
		</ul>
	</span>

  <br />
  <span class="tcell-l" style="vertical-align: top;"><label for="body">Inquiries</label></span>
  <span class="tcell-r"><textarea name="body" id="body" cols="50" rows="8" class="form-control textbox textmain"><?php echo $body; ?></textarea></span>

  <br />
 
  <span class="tcell-l">Image certification<!-- start of Securimage --></span>

  <table style="border:0px; width:290px;" class="tcell-r" cellpadding="0" cellspacing="0"><tr><td rowspan="2"><img id="captcha" src="./securimage/securimage_show.php" alt="CAPTCHA Image" style="margin-top:12px;" /></td><td style="height:40px; margin:0px; padding:0px;">　<a href="#" onclick="document.getElementById('captcha').src = './securimage/securimage_show.php?' + Math.random(); return false"><img src="./securimage/images/refresh.png" alt="" height="25" class="vertical-align:bottom;" id="refresh" /></a></td></tr><tr><td style="height:42px; margin:0px; padding:0px;"><input type="text" name="captcha_code" id="captcha_code" size="14" maxlength="6" c
  lass="form-control textbox keyword validate[required,maxSize[6],minSize[6],custom[onlyLetterNumber]]" /></td></tr></table>
  
  <!-- end of Securimage-->
  <br />

  <div align="center"><input class="btn btn-default submit" type="submit" value="To confirmation screen" /></div>
 
  <!--ticket-->
  <input type="hidden" name="ticket" value="<?php echo $ticket; ?>" />
</form>
 
</fieldset>
<!--End of #formArea--></div>
 
<!--end of #contents--><br /><br /><br /><br /><br /><br /> </div>
</body>
</html>