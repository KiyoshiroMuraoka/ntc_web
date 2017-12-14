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

try{
    if(!empty($_GET)){
        $id = $_GET["member_id"];
        $sql = "DELETE FROM member WHERE id = '".$id."'";
        $mail_address = $_GET["mail_address"];
        $result_flag = pg_query($sql);
        $successMsg = "顧客情報 $mail_address を削除しました。";
        $member = load_sql("member", $link);
        $sort = "";
        foreach ((array) $member as $key => $value) {
            $sort[$key] = $value['id'];
        }
        if(count($member) > 1){array_multisort($sort, SORT_ASC, $member);}
    }
} catch (\Exception $e){
    $errorMsg = $e->getMessage();
}
html:
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>顧客情報削除 - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <link rel="stylesheet" type="text/css" href="./table-style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
        <script type="text/javascript" src="./object.js"></script>
        <script>
            function cnfmAndSubmit(indx, mailaddress) {
                if (indx != 0 & indx != "") {
                    if (confirm("顧客情報 " + mailaddress + " を本当に削除しますか？\nこの処理は取り消しできません。") == true) {
                        location.href = "./member-delete.php?member_id=" + indx + "&mail_address=" + mailaddress;
                    }
                } else {
                    alert("対象が選択されていません。\n選択して下さい。");
                    return false;
                }
            }

        </script>
    </head>

    <body id="member-delete">
        <div id="success" <?php if(!isset($successMsg)){echo 'style="display: none;"';} ?>>
            <?php if(isset($successMsg)){echo $successMsg;} ?>
        </div>
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
        <div id="contents" class="member-delete">
            <h1>顧客情報削除</h1>
            <span id="prev"><img src="./images/back.gif" /></span>
            <span id="page"></span>
            <span id="next"><img src="./images/next.gif" /></span>
            <form action="" method="POST" name="member">
                <table id="gradient-style">
                    <thead>
                        <tr>
                            <th scope="col">メールアドレス</th>
                            <th scope="col">会社名</th>
                            <th scope="col">担当者名</th>
                            <th scope="col">停止</th>
                            <th scope="col">削除</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    for($i=0; $i < count($member); $i++) {
                        if($member[$i]["mail_address"] !== null){
                            if($member[$i]["stop_send"] == "true"){$stop_send = "○";} else {$stop_send = "×";}
                            $form = "<td class=\"delete\"><input type=\"button\" name=\"delete\" value=\"削除\" class=\"button2\" onclick=\"cnfmAndSubmit(".$member[$i]["id"].", '".$member[$i]["mail_address"]."');\" /></td>";
                            echo "<tr>\n                        <td>".$member[$i]["mail_address"]."</td>\n                        <td>".$member[$i]["company_name"]."</td>\n                        <td>".$member[$i]["person_name"]."</td>\n                        <td class=\"stop-send\">$stop_send</td>\n                        $form\n                    </tr>\n";
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </form>
        </div>
    </body>

    </html>
