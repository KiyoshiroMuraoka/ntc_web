<!doctype html>
<html lang="ja">
<head>
<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=0.5">
<script src="./js/jquery-1.8.2.min.js"></script>
<script src="./js/jquery.validationEngine.js"></script>
<script src="./js/jquery.validationEngine-ja.js"></script>
<link href="./skins/square/pink.css" rel="stylesheet">
<link href="./style_smp.css" rel="stylesheet">
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

<form action="confirm.php" method="post" role="form" id="form">
  <div class="tcell">
  <span><label for="company_name"><strong>Company name</strong>&nbsp;(necessary)</label></span>
  <br />
  <span><input type="text" name="company_name" id="company_name" value="<?php echo $company_name; ?>" size="50" class="form-control textbox validate[required]" /></span>
  </div>

  <br />
  <br />

  <div class="tcell">
  <span ><label for="country">Country</label></span>
  <br />
  <span ><input type="text" name="country" id="country" value="<?php echo $country; ?>" size="50" class="form-control textbox" /></span>
  </div>

  <br />
  <br />

  <div class="tcell">
  <span ><label for="name">Supervisor's name</label></span>
  <br />
  <span ><input type="text" name="name" id="name" value="<?php echo $name; ?>" size="50" class="form-control textbox" /></span>
  </div>

  <br />
  <br />

  <div class="tcell">
  <span ><label for="emaiaddl"><strong>E-mail address</strong>&nbsp;(necessary)</label></span>
  <br />
  <span ><input type="text" name="emailadd" id="emailadd" value="<?php echo $emailadd; ?>" size="50" class="form-control textbox validate[required,custom[email]]" /></span>
  </div>

  <br />
  <br />

  <div class="tcell">
  <span ><label for="emailreq">Confirm e-mail address</label></span>
  <br />
  <span ><input type="text" name="emailreq" id="emailreq" value="<?php echo $emailreq; ?>" size="50" class="form-control textbox validate[required,equals[emailadd]]" oncopy="return false" onpaste="return false" oncontextmenu="return false" /></span>
  </div>

  <br />
  <br />

  <div class="tcell">
  <span ><label for="url">URL</label></span>
  <br />
  <span ><input type="text" name="url" id="url" value="<?php echo $url; ?>" size="50" class="form-control textbox validate[custom[url]]" /></span>
  </div>

  <br />
  <br />

  <div class="tcell">
  <span ><label for="service">Services of interest</label></span>
  <br />
  <span >
	<ul class="bg_radiobox">
		<span id="service-1"><li><input type="radio" name="service" value="ChangeOver" class="form-control"<?php if($service=="ChangeOver"){echo ' checked="checked"';}?> /><label for="service-1" class="check">&nbsp;&nbsp;Product data transfer service</label></li></span><br /><br />
		<span id="service-2"><li><input type="radio" name="service" value="RegularPurchases" class="form-control"<?php if($service=="RegularPurchases"){echo ' checked="checked"';}?> /><label for="service-2" class="check">&nbsp;&nbsp;Limited-time product registration service</label></li></span>
	</ul>
  </span>
  </div>
  <br /><br />
  <div class="tcell"> 
  <span style="vertical-align: top;"><label for="body">Inquiries</label></span>
  <br />
  <span><textarea name="body" id="body" cols="50" rows="8" class="form-control textbox textmain"><?php echo $body; ?></textarea></span>
  </div>

  <br />
  <br />

  <div class="tcell"> 
  <span>Image certification<!-- start of Securimage --></span>
  <table style="border:0px; width:580px;" cellpadding="0" cellspacing="0">
	<tr>
		<td rowspan="2"><img id="captcha" class="securimage " src="./securimage/securimage_show.php" alt="CAPTCHA Image" style="margin-top:12px;" /></td>
  		<td style="margin:0px; padding:0px;">　<a href="#" onclick="document.getElementById('captcha').src = './securimage/securimage_show.php?' + Math.random(); return false"><img src="./securimage/images/refresh.png" class="securimage" alt="" height="25" class="vertical-align:bottom;" id="refresh" /></a></td>
	</tr>
  </table>
  <table style="border:0px; width:580px;" cellpadding="0" cellspacing="0">  	
	<tr>
		<td style="height:42px; margin:0px; padding:0px;"><input type="text" name="captcha_code" id="captcha_code" size="14" maxlength="6" class="form-control textbox keyword validate[required,maxSize[6],minSize[6],custom[onlyLetterNumber]]" /></td>
	</tr>
  </table>

  <!-- end of Securimage -->

  <br /><br />

  <div align="center"><input class="btn btn-default submit securimage" type="submit" value="To confirmation screen" /></div>
  
  <!-- ticket -->
  <input type="hidden" name="ticket" value="<?php echo $ticket; ?>" />
  </div>

</form>
 
</fieldset>
<!--End of #formArea--></div>
 
<!--end of #contents--> </div>
</body>
</html>