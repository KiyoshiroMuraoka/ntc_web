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
$member = load_sql("member", $link);
if (!$member) {
    $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
    goto html;
}
foreach ((array) $member as $key => $value) {
    $sort[$key] = $value['id'];
}
array_multisort($sort, SORT_ASC, $member);

html:
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>対象選択 - 顧客情報編集 - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <link rel="stylesheet" type="text/css" href="./table-style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
        <script type="text/javascript" src="./object.js"></script>
    </head>

    <body id="template-add">
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
        <div id="contents" class="member-add">
            <h1>顧客情報編集 - 対象選択</h1>
            <span id="prev"><img src="./images/back.gif" /></span>
            <span id="page"></span>
            <span id="next"><img src="./images/next.gif" /></span>
            <table id="gradient-style">
                <thead>
                    <tr>
                        <th scope="col">メールアドレス</th>
                        <th scope="col">会社名</th>
                        <th scope="col">担当者名</th>
                        <th scope="col">停止</th>
                        <th scope="col">編集</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for($i=0; $i < count($member); $i++) {
                        if($member[$i]["mail_address"] !== null){
                            if($member[$i]["stop_send"] == "true"){$stop_send = "○";} else {$stop_send = "×";}
                            $form = '                    <td><form action="member-edit.php" method="POST"><input type="hidden" name= "id" value="'.$member[$i]["id"].'" /><input type="submit" name="edit" value="編集" class="button2" /></form></td>';
                            echo "<tr>\n                        <td>".$member[$i]["mail_address"]."</td>\n                        <td>".$member[$i]["company_name"]."</td>                        <td>".$member[$i]["person_name"]."</td>                        <td class=\"stop-send\">$stop_send</td>\n                        $form\n                    </tr>\n";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>

    </html>
