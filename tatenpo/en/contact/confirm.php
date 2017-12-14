<?php
  session_start();
  require 'functions.php';
  include_once './securimage/securimage.php';
  $securimage = new Securimage();
  $_POST = checkInput($_POST);
  if(isset($_POST['ticket'], $_SESSION['ticket'])){
    $ticket = $_POST['ticket'];
    if($ticket !== $_SESSION['ticket']){
      die('Invalid value has been detected.');
    }
  }else{
    die('Invalid value has been detected.');
  }
  $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : NULL; 
  $country = isset($_POST['country']) ? $_POST['country'] : NULL;
  $name = isset($_POST['name']) ? $_POST['name'] : NULL;
  $emailadd = isset($_POST['emailadd']) ? $_POST['emailadd'] : NULL; 
  $emailreq = isset($_POST['emailreq']) ? $_POST['emailreq'] : NULL;
  $url = isset($_POST['url']) ? $_POST['url'] : NULL;
  $service = isset($_POST['service']) ? $_POST['service'] : NULL;
  $body = isset($_POST['body']) ? $_POST['body'] : NULL;
  $captcha_code = isset($_POST['captcha_code']) ? $_POST['captcha_code'] : NULL;
  $company_name = trim($company_name);
  $address1 = trim($country);
  $name = trim($name);
  $emailadd = trim($emailadd);
  $emailreq = trim($emailreq);
  $url = trim($url);
  $service = trim($service);
  $body = trim($body);
  $captcha_code = trim($captcha_code);

  $error = array();
  
  if($emailadd == ''){
    $error[] = '*E-mail address is required.';
  }else{
    $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/uiD';
    if(!preg_match($pattern, $emailadd)){
      $error[] = '*Format of the e-mail address is incorrect.';
    }
  }
  if(mb_strlen($captcha_code) <> 6){
    $error[] = '* Check keyword of image authentication Please enter 6 characters.';
  }else if ($securimage->check($captcha_code) == false) {
    $error[] = '* Check keyword of image authentication is incorrect.';
  }
  if(count($error) >0){
    $data['company_name'] = $company_name;
    $data['country'] = $country;
    $data['name'] = $name;
    $data['emailadd'] = $emailadd;
    $data['emailreq'] = $emailreq;
    $data['url'] = $url;
    $data['service'] = $service;
    $data['body'] = $body;
    $data['ticket'] = $ticket;
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if ((strpos($ua, 'Android') !== false) && (strpos($ua, 'Mobile') !== false) || (strpos($ua, 'iPhone') !== false) || (strpos($ua, 'Windows Phone') !== false) || (strpos($ua, 'iPad') !== false)) {
      display('contact_view_smp.php', $data);
    } else {
      display('contact_view.php', $data);
    }
  }else{
    $_SESSION['company_name'] = $company_name;
    $_SESSION['country'] = $country;
    $_SESSION['name'] = $name;
    $_SESSION['emailadd'] = $emailadd;
    $_SESSION['emailreq'] = $emailreq;
    $_SESSION['url'] = $url;
    $_SESSION['service'] = $service;
    $_SESSION['body'] = $body;
    $data = array();
    $data['company_name'] = $company_name;
    $data['country'] = $country;
    $data['name'] = $name;
    $data['emailadd'] = $emailadd;
    $data['emailreq'] = $emailreq;
    $data['url'] = $url;
    $data['service'] = $service;
    $data['body'] = $body;
    $data['ticket'] = $ticket;
    display('confirm_view.php', $data);
  }
?>