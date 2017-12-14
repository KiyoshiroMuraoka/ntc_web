<?php
  session_start();
  session_regenerate_id(TRUE);
  require 'functions.php';
  $data = array();
  $data['company_name'] = isset($_SESSION['company_name']) ? $_SESSION['company_name'] : NULL; 
  $data['country'] = isset($_SESSION['country']) ? $_SESSION['country'] : NULL;
  $data['name'] = isset($_SESSION['name']) ? $_SESSION['name'] : NULL;
  $data['emailadd'] = isset($_SESSION['emailadd']) ? $_SESSION['emailadd'] : NULL; 
  $data['emailreq'] = isset($_SESSION['emailreq']) ? $_SESSION['emailreq'] : NULL;
  $data['url'] = isset($_SESSION['url']) ? $_SESSION['url'] : NULL;
  $data['service'] = isset($_SESSION['service']) ? $_SESSION['service'] : NULL;
  $data['body'] = isset($_SESSION['body']) ? $_SESSION['body'] : NULL;
  if(!isset($_SESSION['ticket'])){
    $_SESSION['ticket'] = sha1(uniqid(mt_rand(), TRUE));
  }
  $data['ticket'] = $_SESSION['ticket'];
  
  $ua = $_SERVER['HTTP_USER_AGENT'];
  if ((strpos($ua, 'Android') !== false) && (strpos($ua, 'Mobile') !== false) || (strpos($ua, 'iPhone') !== false) || (strpos($ua, 'Windows Phone') !== false) || (strpos($ua, 'iPad') !== false)) {
    display('contact_view_smp.php', $data);
  } else {
    display('contact_view.php', $data);
  }
?>