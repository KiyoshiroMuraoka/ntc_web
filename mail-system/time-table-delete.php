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
$time_table = load_sql("time_table", $link);
if (!$time_table) {
    $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
    goto html;
}
foreach ((array) $time_table as $key => $value) {
    $sort[$key] = $value['id'];
}
array_multisort($sort, SORT_ASC, $time_table);

try{
    if (!empty($_POST)) {
        if(!isset($_POST["id"])){
            $errorMsg = '削除するテンプレートが指定されていません。';
            goto html;
        }
        if(isset($_POST["id"])){
            $id_list = explode(",", $_POST["id"]);
            foreach ($id_list as $value) {
                $sql = "DELETE FROM time_table WHERE id = '".$value."'";
                $result_flag = pg_query($sql);
            }
            $time_table = load_sql("time_table", $link);
            if (!$time_table) {
                $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
                goto html;
            }
            foreach ((array) $time_table as $key => $value) {
                $sort[$key] = $value['id'];
            }
            if(count($time_table) > 1){array_multisort($sort, SORT_ASC, $time_table);}
            $close_flag = pg_close($link);
            $successMsg = '選択されたタイムテーブルを削除しました。';
        } else {
            $errorMsg = '内容が正しく入力されていません。';
            goto html;
        }
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
        <title>タイムテーブル登録 - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <link rel="stylesheet" type="text/css" href="./table-style.css" />
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
            <h1>タイムテーブル削除</h1>
            <form action="" method="POST" id="time-table-delete-form">
                <span id="prev"><img src="./images/back.gif" /></span>
                <span id="page"></span>
                <span id="next"><img src="./images/next.gif" /></span>
                <table id="gradient-style">
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">タイムテーブル名</th>
                            <th scope="col">使用テンプレート</th>
                            <th scope="col">送信済み回数</th>
                            <th scope="col">登録日</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(count($time_table)>0){
                            for($i=0; $i < count($time_table); $i++) {
                                if($time_table[$i]["name"] !== null){
                                    if(isset($_POST['id'])){
                                        if(strpos($_POST['id'], $time_table[$i]["id"]) !== false){
                                            $selected = " checked";
                                        } else {
                                            $selected = "";
                                        }
                                    } else {
                                        $selected = "";
                                    }
                                    echo "<tr>\n                        <td><input type=\"checkbox\" name=\"id\" id=\"id-".$time_table[$i]["id"]."\" value=\"".$time_table[$i]["id"]."\"".$selected." /><label for=\"id-".$time_table[$i]["id"]."\" class=\"checkbox\"></label></td>\n                        <td>".$time_table[$i]["name"]."</td>\n                        <td>".$time_table[$i]["use_template"]."</td>\n                        <td class=\"schedule\">".$time_table[$i]["delivery_schedule"]." 回</td>\n                        <td class=\"date\">".$time_table[$i]["register_date"]."</td>\n                    </tr>\n                    ";
                                }
                            }
                        } else {
                            echo "<tr><td colspan=\"5\">タイムテーブル情報がありません。</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <input type="submit" id="register" class="button" value="　　削除　　">

            </form>
        </div>
    </body>

    </html>
