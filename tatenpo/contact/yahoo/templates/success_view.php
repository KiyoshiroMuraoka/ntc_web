<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<?php 
$success = false;
$firstchar = substr($message, 0, 1);  //1文字目を取得
if($firstchar == "1") {
  $message = substr($message, 1);  //「1」を削除
  $success = true;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="noindex,nofollow">
<link href="../style.css" rel="stylesheet">
<title>送信完了 | イージースター お問い合わせフォーム - Yahoo!コマースパートナーマーケットプレイス専用</title>
</head>
<body>
<div id="contents">
  お問い合わせ（<?php echo $success ? "完了" : "送信失敗"; ?>）<br />
  <div id="message">
    <p><?php
        if($success) {
            echo '<button type="button" class="btn btn-primary active">送信完了</button>';
        }else{
            echo '<a class="btn btn-danger" href="contact.php">送信失敗</a>';
        }
     ?></p>
        <p><?php echo $message; ?></p>
    </div><!--end of #message-->
</div><!--end of #contents-->
</body>
</html>