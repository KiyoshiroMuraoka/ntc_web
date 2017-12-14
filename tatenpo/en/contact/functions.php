<?php
function h($var) {
  if(is_array($var)){
    return array_map('h', $var);
  }else{
    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
  }
}
function display($_template, $data) {
  foreach($data as $key => $val){
    $$key = h($val);
  }
  unset($data);
  include dirname(__FILE__) . '/templates/'. $_template;
}
 
function checkInput($var){
  if(is_array($var)){
    return array_map('checkInput', $var);
  }else{
    if(get_magic_quotes_gpc()){  
      $var = stripslashes($var);
    }
    if(preg_match('/\0/', $var)){
      die('Invalid input.');
    }
    if(!mb_check_encoding($var, 'UTF-8')){
      die('Invalid input.');
    }
    if(preg_match('/\A[\r\n\t[:^cntrl:]]*\z/u', $var) === 0){
      die('Invalid input. Control characters can not be used.');
    }
    
    return $var;
  }
}
function file_get_csv($filename, $length=null,$delim=",",$enclosure="\"") {
	$data = array();
	$fp = fopen($filename,"r");
	while( ($buff=fgetcsv($fp,$length,$delim,$enclosure)) !==FALSE ){
		$data[]=$buff;
	}
	return $data;
}
?>