<!doctype html>
<html lang="ja">
<head>
<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="./style.css" rel="stylesheet">
</head>
<body>
<div id="contents">
<fieldset id="contactForm">
<div id="confirmArea">
<p style="width:500px;">If the following information is correct then press the "submit" button.</p>
 <span class="tr">
  <span class="tcell-l">Company name: </span>
	<span class="tcell-r"><?php echo $company_name; ?></span>
 </span>
 <span class="tr">
	<span class="tcell-l">Country: </span>
	<span class="tcell-r"><?php echo $country; ?></span>
 </span>
 <span class="tr">
	<span class="tcell-l">Supervisor's name: </span>
	<span class="tcell-r"><?php echo $name; ?></span>
 </span>
 <span class="tr">
	<span class="tcell-l">E-mail address: </span>
	<span class="tcell-r"><?php echo $emailadd; ?></span>
 </span>
 <span class="tr">
	<span class="tcell-l">URL: </span>
	<span class="tcell-r"><?php echo $url; ?></span>
 </span>
 <span class="tr">
	<span class="tcell-l">Services of interest: </span>
	<span class="tcell-r"><?php if($service=="ChangeOver"){ echo "Product transfer service";} else { echo "Limited-time product registration service";} ?></span>
 </span>
 <span class="tr">
	<span class="tcell-l">Inquiries: </span>
	<span class="tcell-r"><?php echo $body; ?></span>
 </span>
<!--end of #confirmArea--></div>
<div align="center">
<table border="0"><tr><td>
<form action="contact.php" method="post">
  <input type="submit" value="Go back" class="btn btn-default submit" />
</form>
</td>
<td>
<form action="success.php" method="post">
  <!-- ticket -->
  <input type="hidden" name="ticket" value="<?php echo $ticket; ?>" />
  <input type="submit" value="Submit" class="btn btn-default submit" />
</form>
</td>
</tr>
</table>
</div>
 </fieldset>
<!--end of #contents--></div>