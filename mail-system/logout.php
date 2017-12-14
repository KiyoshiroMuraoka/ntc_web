<?php
session_start();
if (isset($_SESSION["last_access"])){
    if ($_SESSION["last_access"] <= strtotime("-10 min")) {
        $errorMessage = "セッションがタイムアウトしました。";
    } else {
        $errorMessage = "ログアウトしました。";
    }
} else {
    $errorMessage = "ログインしてください。";
}
// セッション変数のクリア
$_SESSION = array();
// セッションクリア
@session_destroy();
?>

    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta charset="UTF-8">
        <title>イージースター メール配信システム</title>
        <link href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" rel="stylesheet" />
        <style>
            * {
                font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "Noto Sans Japanese", "ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN, Arial, Meiryo, sans-serif;
                color: #4e4e4e;
            }

            div#contents {
                width: 400px;
                margin: 16px auto;
                font-size: 16px;
            }

            a {
                text-align: center;
                box-sizing: border-box;
                display: block;
                width: 100%;
                border-width: 1px;
                border-style: solid;
                padding: 16px;
                outline: 0;
                font-family: inherit;
                font-size: 0.95em;
                background: #28d;
                border-color: transparent;
                color: #fff;
                cursor: pointer;
                text-decoration: none;
            }

            a:hover {
                background: #17c;
            }

            a:focus {
                border-color: #05a;
            }

            #login {
                width: 176px;
            }

            h1 {
                font-size: 24px;
            }

            .err {
                color: red;
            }

            .center {
                width: 176px;
                margin: 0 auto;
            }

        </style>
    </head>

    <body>
        <div id="contents">
            <h1>イージースター メール配信システム</h1>
            <p class="err">
                <?php if(isset($errorMessage)){echo $errorMessage;} ?>
            </p>
            <div class="center"><a href="./login.php" id="login">ログイン画面に戻る</a></div>
        </div>
    </body>

    </html>
