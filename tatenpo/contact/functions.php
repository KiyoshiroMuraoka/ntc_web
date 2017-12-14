<?php
//エスケープ処理を行う関数
function h($var) {
  if(is_array($var)){
    //$varが配列の場合、h()関数をそれぞれの要素について呼び出す（再帰的に処理）
    return array_map('h', $var);
  }else{
    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
  }
}
 
//テンプレートを処理する関数
//第1引数：テンプレートファイル名、第2引数：テンプレートで使うデータ（配列）
function display($_template, $data) {
  foreach($data as $key => $val){
    $$key = h($val);  //可変変数
    //テンプレートで使う変数を生成し、値をエスケープ処理
  }
  unset($data);
  //変数を作成後は、第2引数として受け取った「$data」は、不要になるので破棄
  include dirname(__FILE__) . '/templates/'. $_template;
  //テンプレートファイルを読み込む
}
 
//入力値に不正なデータがないかなどをチェックする関数
function checkInput($var){
  if(is_array($var)){
    return array_map('checkInput', $var);
  }else{
    if(get_magic_quotes_gpc()){  
      //php.iniでmagic_quotes_gpcが「on」の場合の対策
      $var = stripslashes($var);
    }
    if(preg_match('/\0/', $var)){  //NULLバイト攻撃対策
      die('不正な入力です。');
    }
    if(!mb_check_encoding($var, 'UTF-8')){ //文字エンコードのチェック
      die('不正な入力です。');
    }
    if(preg_match('/\A[\r\n\t[:^cntrl:]]*\z/u', $var) === 0){  //改行、タブ以外の制御文字のチェック
      die('不正な入力です。制御文字は使用できません。');
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