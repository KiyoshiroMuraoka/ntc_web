<!DOCTYPE html>
<html lang="ja">

<body>
    <?php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
// エラーを例外に変換する
throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
require './lib/password.php';
require './lib/sql.php';
$pass = "EASYSTER_EC_COORDINATE_20050318";
mb_language("Japanese");
mb_internal_encoding("UTF-8");
if(isset($_GET["id"]) && isset($_GET["mail"]) && isset($_GET["unique"])){
    $unique_id = base64_decode(base64_decode($_GET["unique"]));
    if($_GET["id"] == $unique_id) {
        try {
            $link = connect_sql();
            if (!$link) {
                header("Location: https://www.technocrats.jp/tatenpo/");
                exit;
            }
            $sql = "UPDATE member SET stop_send = 'true' WHERE id = ".$_GET["id"];
            $result_flag = pg_query($sql);
            $close_flag = pg_close($link);
            $mail = $_GET["mail"];
            echo "$mail のメール配信を停止しました。<br />再開したい場合は、&#101;&#x63;&#45;&#99;&#111;nt&#x61;&#x63;t&#x40;te&#99;&#104;&#110;ocr&#x61;t&#115;&#x2e;jp までご連絡ください。";
        } catch (\Exception $e){
            echo $e->getMessage();
        }
    } else {
        header("Location: https://www.technocrats.jp/tatenpo/");
    }
} else {
    header("Location: https://www.technocrats.jp/tatenpo/");
}
?>


</body>

</html>
