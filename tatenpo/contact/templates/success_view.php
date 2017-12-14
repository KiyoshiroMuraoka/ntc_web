<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<?php 
$success = false;
$firstchar = substr($message, 0, 1);  //1文字目を取得
if($firstchar == "1") {
  $message = substr($message, 1);  //「1」を削除
  $success = true;
}
?>
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