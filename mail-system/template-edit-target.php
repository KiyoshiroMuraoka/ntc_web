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
$template = load_sql("template", $link);
if (!$template) {
    $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
    goto html;
}
foreach ((array) $template as $key => $value) {
    $sort[$key] = $value['id'];
}
array_multisort($sort, SORT_ASC, $template);


html:
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>対象選択 - テンプレート編集 - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
        <script type="text/javascript" src="./object.js"></script>
        <script>
            function cnfmAndSubmit() {
                var event = document.getElementById('template_name');
                var indx = document.template.template_name.selectedIndex;
                var i = document.template.template_name.options;

                var val = document.template.template_name.options[indx].value;

                if (indx != 0) {
                    location.href = "./template-edit.php?template=" + val;
                } else {
                    alert("テンプレートが選択されていません。\n必ず選択して下さい。");
                    return false;
                }
            }

        </script>
    </head>

    <body id="template-edit">
        <div id="err" <?php if(!isset($errorMsg)){echo 'style="display: none;"';} ?>>
            <?php if(isset($errorMsg)){echo $errorMsg;} ?>
        </div>
        <ul id="top-menu">
            <li><a href="index.php" class="button">TOP</a></li>
            <li><a href="template.php" class="button">テンプレート</a></li>
            <li><a href="member.php" class="button">顧客管理</a></li>
            <li><a href="time-table.php" class="button">タイムテーブル</a></li>
            <li><a href="logout.php" class="button">ログアウト</a></li>
        </ul>
        <div id="contents" class="template-edit">
            <h1>テンプレート編集 - 対象選択</h1>
            <form action="" method="POST" name="template">
                テンプレート名：
                <select name="template_name" id="template_name" onchange="pagereload();">
                    <option value="">▼選択してください▼</option><?php
                    for($j=0; $j < count($template); $j++) {
                        if($template[$j]["name"] !== null){
                            echo "\n".'                    <option value="'.$template[$j]["name"].'">'.$template[$j]["name"].'</option>';
                        }
                    }
                    echo "\n";
                    ?>
                </select><br />
                <input type="button" id="register" class="button" value="　　編集　　" onclick="cnfmAndSubmit();">
            </form>
        </div>
    </body>

    </html>
