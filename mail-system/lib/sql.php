<?php
function connect_sql()
{
    $conn = "host=localhost dbname=mailsend user=postgres password=postgres";
    return pg_connect($conn);
}

function load_sql($table, $link)
{
    $query = "SELECT * FROM ". $table ."";
    $result = pg_query($query);
    if (!$result) {
      $close_flag = pg_close($link);
      return false;
    }
    $rows    = pg_numrows($result);
    $row     = 0;
    while( $row < $rows ){
      $tmp = pg_fetch_array( $result, $row );
      $keys = array_keys($tmp);
      for($i=0; $i < count($keys) / 2; $i++){
        $data[$row][$keys[$i*2+1]] = $tmp[$i];
      }
      $row++;
    }
    return $data;
}

function addlog($msg)
{
    $log_file = "./log/mailsend_".date("Y-m-d_H").".log";
  $log = fopen($log_file, "a");
  @fwrite($log, "[".date("Y/m/d H:i:s")."]: ".$msg."\r\n");
  fclose($log);
}

function email_check($email)
{
    $arr = explode("@", $email);
    $domain = str_replace(array('[', ']'), "", array_pop($arr));
    return filter_var($email, FILTER_VALIDATE_EMAIL) &&
       // (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA'));
	(checkdnsrr($domain, 'MX') );
}
function session_check()
{
    if (!isset($_SESSION["username"])) {
        header("Location: logout.php");
        exit;
    } else if ($_SESSION["last_access"] <= strtotime("-10 min")) {
        header("Location: logout.php");
        exit;
    }
    
    $_SESSION["last_access"] = strtotime("now");
}
