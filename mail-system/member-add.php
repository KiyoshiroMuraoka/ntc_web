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

$link = connect_sql();
if (!$link) {
    $errorMsg = 'データベース接続に失敗しました。'.pg_last_error();
    goto html;
}
$result = pg_query("SELECT * FROM member");
if (!$result) {
    $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
    goto html;
}

try{
    if (!empty($_POST)) {
        if(isset($_POST["mail_address"]) && !email_check($_POST["mail_address"])){
            $errorMsg = 'メールアドレスの書式が正しくありません。';
            goto html;
        }
        if(isset($_POST["company_name"]) && isset($_POST["person_name"]) && isset($_POST["mail_address"])){
            $sql = "INSERT INTO member (mail_address, company_name, person_name) VALUES ('".$_POST["mail_address"]."', '".$_POST["company_name"]."', '".$_POST["person_name"]."')";
            $result_flag = pg_query($sql);
            $close_flag = pg_close($link);
            $successMsg = $_POST["mail_address"] . ' の顧客情報を登録しました。';
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
        <title>顧客登録 - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
        <script type="text/javascript" src="./object.js"></script>
    </head>

    <body id="member-add">
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
        <div id="contents" class="member-add">
            <h1>顧客登録</h1>
            <form action="" method="POST">
                <div class="source-area">
                    メールアドレス：<input type="text" name="mail_address" placeholder="英数字＋@のみ" value="<?php if(isset($_POST['mail_address'])){echo $_POST['mail_address'];} ?>" autocomplete="off" />
                    <br /> 会社名：
                    <input type="text" name="company_name" placeholder="株式会社等も忘れずに" value="<?php if(isset($_POST['company_name'])){echo $_POST['company_name'];} ?>" autocomplete="off" />
                    <br /> 担当者名：
                    <input type="text" name="person_name" placeholder="名字と名前の間には半角スペース" value="<?php if(isset($_POST['person_name'])){echo $_POST['person_name'];} ?>" />
                </div>
                <input type="submit" id="register" class="button" value="　　登録　　">

            </form>
        </div>
    </body>

    </html>
