<?php
  $ua = $_SERVER['HTTP_USER_AGENT'];
  if ((strpos($ua, 'Android') !== false) && (strpos($ua, 'Mobile') !== false) || (strpos($ua, 'iPhone') !== false) || (strpos($ua, 'Windows Phone') !== false) || (strpos($ua, 'iPad') !== false)) {
    // スマートフォンからアクセスされた場合
    display('footer_sp.html');
  } else {
    display('footer.html');
  }

function display($_template) {
  include dirname(__FILE__) . '/'. $_template;
}
?>