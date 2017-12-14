<?php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // エラーを例外に変換する
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

mb_language("Japanese");

mb_internal_encoding("UTF-8");
session_start();
require './lib/sql.php';
// ログイン状態のチェック
session_check();

if  (!isset($_POST["id"])) {
    header("Location: member-edit-target.php");
    exit;
}

$link = connect_sql();
if (!$link) {
    $errorMsg = 'データベース接続に失敗しました。'.pg_last_error();
    goto html;
}
$member = load_sql("member", $link);
if (!$member) {
    $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
    goto html;
}
for($i=0; $i < count($member); $i++) {
    if($member[$i]["id"] == $_POST["id"]){
        $mbr = $member[$i];
        break;
    }
}
try{
    if (!empty($_POST) && isset($_POST["editnow"])) {
        if(isset($_POST["mail_address"]) && !email_check($_POST["mail_address"])){
            $errorMsg = 'メールアドレスの書式が正しくありません。';
            goto html;
        }
        if(isset($_POST["company_name"]) && isset($_POST["person_name"]) && isset($_POST["mail_address"])){
            $sql = "UPDATE member SET mail_address = '".$_POST["mail_address"]."' WHERE id = ".$_POST["id"];
            $result_flag = pg_query($sql);
            $sql = "UPDATE member SET company_name = '".$_POST["company_name"]."' WHERE id = ".$_POST["id"];
            $result_flag = pg_query($sql);
            $sql = "UPDATE member SET person_name = '".$_POST["person_name"]."' WHERE id = ".$_POST["id"];
            $result_flag = pg_query($sql);
            if ($_POST["stop_send"] == "on") {$stop_send = "true";} else {$stop_send = "false";}
            $sql = "UPDATE member SET stop_send = '".$stop_send."' WHERE id = ".$_POST["id"];
            $mbr["stop_send"] = $stop_send;
            $result_flag = pg_query($sql);
            $successMsg = $_POST["mail_address"] . ' の顧客情報を編集しました。';
        } else if (!isset($_POST["mail_address"])){
            $errorMsg = 'メールアドレスが入力されていません。';
            goto html;
        } else if (!isset($_POST["company_name"])){
            $errorMsg = '会社名が入力されていません。';
            goto html;
        } else if (!isset($_POST["person_name"])){
            $errorMsg = '担当者名が入力されていません。';
            goto html;
        } else {
            $errorMsg = '内容が正しく入力されていません。';
            goto html;
        }
    }
    $close_flag = pg_close($link);
} catch (\Exception $e){
    if(strpos($e->getMessage(),'mail_address') !== false && strpos($e->getMessage(),'すでに存在します') !== false){
        $errorMsg = 'このメールアドレスは既に存在しています。';
    } else {
        $errorMsg = $e->getMessage();
    }
}
html:
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>顧客編集 - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
        <script type="text/javascript" src="./object.js"></script>
    </head>

    <body id="member-edit">
        <div id="err" <?php if(!isset($errorMsg)){echo 'style="display: none;"';} ?>>
            <?php if(isset($errorMsg)){echo $errorMsg;} ?>
        </div>
        <div id="success" <?php if(!isset($successMsg)){echo 'style="display: none;"';} ?>>
            <?php if(isset($successMsg)){echo $successMsg;} ?>
        </div>
        <ul id="top-menu">
            <li><a href="index.php" class="button">TOP</a></li>
            <li><a href="template.php" class="button">テンプレート</a></li>
            <li><a href="member.php" class="button">顧客管理</a></li>
            <li><a href="time-table.php" class="button">タイムテーブル</a></li>
            <li><a href="logout.php" class="button">ログアウト</a></li>
        </ul>
        <div id="contents" class="member-edit">
            <h1>顧客編集</h1>
            <form action="" method="POST">
                <input type="hidden" name="id" value="<?php if(isset($_POST['id'])){echo $_POST['id'];} ?>" />
                <input type="hidden" name="editnow" value="true" />
                <div class="source-area">
                    メールアドレス：<input type="text" name="mail_address" placeholder="英数字＋@のみ" value="<?php if (isset($mbr['mail_address'])){echo $mbr['mail_address'];} ?>" autocomplete="off" />
                    <br /> 会社名：
                    <input type="text" name="company_name" placeholder="株式会社等も忘れずに" value="<?php if (isset($mbr['company_name'])){echo $mbr['company_name'];} ?>" autocomplete="off" /><br /> 担当者名：
                    <input type="text" name="person_name" placeholder="名字と名前の間には半角スペース" value="<?php if (isset($mbr['person_name'])){echo $mbr['person_name'];} ?>" autocomplete="off" /><br />
                    <input type="hidden" name="stop_send" value="false" />
                    <input type="checkbox" name="stop_send" id="stop_send" <?php if (isset($mbr[ 'stop_send']) && $mbr[ 'stop_send']=="true" ){echo ' checked="checked"';} ?> /><label for="stop_send" class="checkbox">配信停止</label>
                </div>
                <input type="submit" id="register" class="button" value="　　編集完了　　" />

            </form>
        </div>
    </body>

    </html>
