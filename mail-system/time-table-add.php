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
$sort = "";
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
    if (!empty($_POST)) {
        if(!isset($_POST["name"])){
            $errorMsg = 'タイムテーブル名が入力されていません。';
            goto html;
        }
        if(!isset($_POST["use_template"])){
            $errorMsg = '使用するテンプレートが指定されていません。';
            goto html;
        }
        if(!isset($_POST["delivery_member"])){
            $errorMsg = '送信先顧客が指定されていません。';
            goto html;
        }
        if(!isset($_POST["delivery_schedule"])){
            $errorMsg = 'スケジュール数値設定エラー。';
            goto html;
        }
        if(!isset($_POST["register_date"])){
            $errorMsg = 'スケジュール日時設定エラー。';
            goto html;
        }
        if(isset($_POST["name"]) && isset($_POST["use_template"]) && isset($_POST["delivery_member"]) && isset($_POST["delivery_schedule"]) && isset($_POST["register_date"])){
            $sql = "INSERT INTO time_table (name, use_template, delivery_member, delivery_schedule, register_date) VALUES ('".$_POST["name"]."', '".$_POST["use_template"]."', '".$_POST["delivery_member"]."', '".$_POST["delivery_schedule"]."', '".$_POST["register_date"]."')";
            $result_flag = pg_query($sql);
            $close_flag = pg_close($link);
            $successMsg = 'タイムテーブル '.$_POST["name"] . ' を登録しました。';
        } else {
            $errorMsg = '内容が正しく入力されていません。';
            goto html;
        }
    }
} catch (\Exception $e){
    if(strpos($e->getMessage(),'name') !== false && strpos($e->getMessage(),'すでに存在します') !== false){
        $errorMsg = 'このタイムテーブル名は既に存在しています。';
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
            <h1>タイムテーブル登録</h1>
            <form action="" method="POST" id="time-table-add-form">
                <div class="source-area">
                    タイムテーブル名：<input type="text" name="time_table_name" placeholder="任意の文字列" autocomplete="off" value="<?php if(isset($_POST['name'])){echo $_POST['name'];} ?>" />
                    <br /> 使用するテンプレート：
                    <select name="use_template" id="use_template">
                    <option value="">▼選択してください▼</option><?php
                        if(isset($_POST['use_template'])){
                            $use_template = $_POST['use_template'];
                        } else {
                            $use_template = "";
                        }
                        $selected = "";
                    for($j=0; $j < count($template); $j++) {
                        if($template[$j]["name"] !== null){
                            if($template[$j]["name"] == $use_template){
                                $selected = " selected";
                            } else {
                                $selected = "";
                            }
                            echo "\n".'                    <option value="'.$template[$j]["name"].'"'.$selected.'>'.$template[$j]["name"].'</option>';
                        }
                    }
                    echo "\n";
                    ?>
                    </select>
                </div>
                <span id="prev"><img src="./images/back.gif" /></span>
                <span id="page"></span>
                <span id="next"><img src="./images/next.gif" /></span>
                <table id="gradient-style">
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">メールアドレス</th>
                            <th scope="col">会社名</th>
                            <th scope="col">担当者名</th>
                            <th scope="col">停止</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(count($member)>0){
                            for($i=0; $i < count($member); $i++) {
                                if($member[$i]["mail_address"] !== null){
                                    if($member[$i]["stop_send"] == "true"){$stop_send = "○";} else {$stop_send = "×";}
                                    if(isset($_POST['delivery_member'])){
                                        if(strpos($_POST['delivery_member'], $member[$i]["id"]) !== false){
                                            $selected = " checked";
                                        } else {
                                            $selected = "";
                                        }
                                    } else {
                                        $selected = "";
                                    }
                                    echo "<tr>\n                        <td><input type=\"checkbox\" name=\"member\" id=\"member-".$member[$i]["id"]."\" value=\"".$member[$i]["id"]."\"".$selected." /><label for=\"member-".$member[$i]["id"]."\" class=\"checkbox\"></label></td>\n                        <td>".$member[$i]["mail_address"]."</td>\n                        <td>".$member[$i]["company_name"]."</td>\n                        <td>".$member[$i]["person_name"]."</td>\n                        <td class=\"stop-send\">$stop_send</td>\n                    </tr>\n                    ";
                                }
                            }
                        } else {
                            echo "<tr><td colspan=\"5\">顧客情報がありません。</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php echo "<input type=\"hidden\" name=\"member-count\" value=\"".count($member)."\" />\n"; ?>
                <input type="submit" id="register" class="button" value="　　登録　　">

            </form>
        </div>
    </body>

    </html>
