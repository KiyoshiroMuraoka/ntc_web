<?php @header("Content-Type: text/html; charset=UTF-8"); ?>
<?php 
$success = false;
$firstchar = substr($message, 0, 1);
if($firstchar == "1") {
  $message = substr($message, 1);
  $success = true;
}
?>
<div id="contents">
  Contact Us（<?php echo $success ? "completed!" : "Transmission failure"; ?>）<br />
  <div id="message">
    <p><?php
        if($success) {
            echo '<button type="button" class="btn btn-primary active">send complete</button>';
        }else{
            echo '<a class="btn btn-danger" href="contact.php">Transmission failure</a>';
        }
     ?></p>
        <p><?php echo $message; ?></p>
    </div><!--end of #message-->
</div><!--end of #contents-->