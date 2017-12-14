<?php
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
$time_table = load_sql("time_table", $link);
if (!$time_table) {
    $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
    goto html;
}
$time_table_list = array();
for($i = 0; $i < count($time_table); $i++) {
    $date = $time_table[$i]["register_date"];
    $use_template = $time_table[$i]["use_template"];
    $delivery_count = $time_table[$i]["delivery_schedule"] + 1;
    for ($j = 0; $j < count($template); $j++){
        if($template[$j]["name"] == $use_template){
            $schedule = $template[$j]["delivery_date_".$delivery_count];
            $time = $template[$j]["delivery_time"];
            switch (substr($schedule, 1, 1)){
            case "d":
                $strtime = "day";
                break;
            case "w":
                $strtime = "week";
                break;
            case "m":
                $strtime = "month";
                break;
            }
            if($schedule == "now"){
                $strtime = "now";
            }
            break;
        }
    }
    if($strtime == "now"){
        $del_date = date("Y/m/d", strtotime("now"));
        $del_time = $del_date." 以内";
    } else {
        $del_date = date("Y/m/d", strtotime($date." + ".substr($schedule, 0, 1).$strtime));
        $del_time = $del_date." ".$time;
    }
    $name = $time_table[$i]["name"];
    
    $count = $time_table[$i]["delivery_schedule"];
    $time_table_list[] = array('name'=>$name,
                               'date'=>$del_time,
                               'count'=>$count,
                               'template'=>$use_template);
}
foreach ((array) $time_table_list as $key => $value) {
    $sort[$key] = $value['date'];
}
array_multisort($sort, SORT_ASC, $time_table_list);

html:
$close_flag = pg_close($link);
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>メイン - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./table-style.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
    </head>

    <body>
        <ul id="top-menu">
            <li><a href="index.php" class="button">TOP</a></li>
            <li><a href="template.php" class="button">テンプレート</a></li>
            <li><a href="member.php" class="button">顧客管理</a></li>
            <li><a href="time-table.php" class="button">タイムテーブル</a></li>
            <li><a href="logout.php" class="button">ログアウト</a></li>
        </ul>
        <div id="contents">
            <div id="main-contents" class="top">
                <h1>イージースター メール配信システム</h1>
                <hr style="width: 600px; border: 1px solid #4197ee" />
                <h1>送信スケジュール</h1>
                <table id="gradient-style" class="time-schedule" align="center">
                    <thead>
                        <tr>
                            <th scope="col">タイムテーブル名</th>
                            <th scope="col">使用テンプレート</th>
                            <th scope="col">送信済み回数</th>
                            <th scope="col">次回配信日時</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(count($time_table_list)>0){
                            for($i=0; $i < count($time_table_list); $i++) {
                                if($time_table_list[$i]["name"] !== null){
                                    echo "<tr>\n                        <td>".$time_table_list[$i]["name"]."</td>\n                        <td>".$time_table_list[$i]["template"]."</td>\n                        <td class=\"schedule\">".$time_table_list[$i]["count"]." 回</td>\n                        <td class=\"date\">".$time_table_list[$i]["date"]."</td>\n                    </tr>\n                    ";
                                }
                            }
                        } else {
                            echo "<tr><td colspan=\"5\">送信スケジュールはありません。</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </body>

    </html>
