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
$result = pg_query("SELECT * FROM template");
if (!$result) {
    $errorMsg = 'SELECTクエリが失敗しました。'.pg_last_error();
    goto html;
}

$insert_column = "";
$insert_data = "";

try{
    if (!empty($_POST)) {
        for($i=1;$i<12;$i++){
            if(isset($_POST["delivery_title_".$i]) && isset($_POST["source_".$i]) && isset($_POST["delivery_date_".$i]) && !empty($_POST["delivery_title_".$i]) && !empty($_POST["source_".$i]) && !empty($_POST["delivery_date_".$i])){
                if(preg_match('/[0-9]+[dwm]+/', $_POST["delivery_date_".$i], $m) || strpos($_POST["delivery_date_".$i],'now') !== false){
                    $insert_column = $insert_column.", delivery_title_".$i.", source_".$i.", delivery_date_".$i;
                    if (strpos($_POST["delivery_date_".$i],'now') !== false) { $date = "now";} else {$date = $m[0];}
                    $insert_data = $insert_data.", '".$_POST["delivery_title_".$i]."', '".$_POST["source_".$i]."', '".$date."'";
                } else {
                    $errorMsg = '配信日設定が不正です。';
                    goto html;
                }
            } else if (isset($_POST["delivery_title_".$i]) && !empty($_POST["delivery_title_".$i])){
                if(!isset($_POST["source_".$i]) || empty($_POST["source_".$i])){
                    $errorMsg = $i.' 回目の本文が空欄になっています。';
                    goto html;
                } else if (!isset($_POST["delivery_date_".$i]) || empty($_POST["delivery_date_".$i])){
                    $errorMsg = $i.' 回目の配信日が空欄になっています。';
                    goto html;
                }
            }
        }
        if(isset($insert_column) && isset($insert_data) && isset($_POST["template_name"]) && isset($_POST["delivery_time"])){
            $sql = "INSERT INTO template (name, delivery_time".$insert_column.") VALUES ('".$_POST["template_name"]."', '".$_POST["delivery_time"]."'".$insert_data.")";
            $result_flag = pg_query($sql);
            $close_flag = pg_close($link);
            $successMsg = $_POST["template_name"] . ' テンプレートを登録しました。';
        } else if (!isset($_POST["template_name"])){
            $errorMsg = 'テンプレート名が入力されていません。';
            goto html;
        } else if (!isset($_POST["delivery_time"])){
            $errorMsg = '配信時刻が入力されていません。';
        } else {
            $errorMsg = 'テンプレートの内容が正しく入力されていません。';
        }
    }
} catch (\Exception $e){
    if(strpos($e->getMessage(),'name') !== false && strpos($e->getMessage(),'すでに存在します') !== false){
        $errorMsg = 'このテンプレート名は既に存在しています。';
    } else if(strpos($e->getMessage(), 'Undefined variable: insert_column') !== false)  {
        $errorMsg = 'テンプレートの内容が正しく入力されていません。ご確認ください。';
    } else if(strpos($e->getMessage(), 'source') !== false && strpos($e->getMessage(), 'NULL値はNOT NULL制約違反です') !== false) {
        $errorMsg = 'メール本文が未記入になっている箇所があります。';
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
        <title>テンプレート登録 - イージースター メール配信システム</title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosansjapanese.css" />
        <link rel="stylesheet" type="text/css" href="./style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
        <script type="text/javascript" src="./object.js"></script>
    </head>

    <body id="template-add">
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
        <div id="contents" class="template-add">
            <h1>テンプレート登録</h1>
            <form action="" method="POST">
                <div class="source-area">
                    <div onclick="obj0=document.getElementById('open0').style; obj0.display=(obj0.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">■ 入力説明 ■</a></div>
                    <div id="open0" style="display:none;clear:both;">
                        <div id="template-desc">
                            <p>＜＜会社名、担当者名の入力方法＞＞
                                <br /> 本文に以下の文字を入れてください。
                                <br /> 会社名：##company_name##
                                <br /> 担当者名：##person_name##
                            </p>
                            <p>＜＜配信日の記載方法＞＞
                                <br /> d…日
                                <br /> w…週
                                <br /> m…月
                                <br />
                                <br /> 例：1d…1日後
                                <br /> 2w…2週間後
                                <br /> 3m…3ヶ月後
                                <br /> ※組み合わせての指定はできません。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="source-area">
                    テンプレート名：
                    <input type="text" name="template_name" placeholder="英数字のみ" value="<?php if(isset($_POST['template_name'])){echo $_POST['template_name'];} ?>" autocomplete="off" />
                    <br />配信時刻：
                    <input type="time" name="delivery_time" value="<?php if(isset($_POST['delivery_time'])){echo $_POST['delivery_time'];} else {echo '07:00:00';} ?>" step="1" />
                </div>
                <div class="source-area">
                    <span class="click-area">▼ 1回目 ▼</span>
                    <div id="open1">
                        <input type="text" name="delivery_title_1" placeholder="件名" value="<?php if(isset($_POST['delivery_title_1'])){echo $_POST['delivery_title_1'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_1" placeholder="メール内容"><?php if(isset($_POST['source_1'])){echo $_POST['source_1'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_1" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_1'])){echo $_POST['delivery_date_1'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj2=document.getElementById('open2').style; obj2.display=(obj2.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 2回目 ▼</a></div>
                    <div id="open2" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_2" placeholder="件名" value="<?php if(isset($_POST['delivery_title_2'])){echo $_POST['delivery_title_2'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_2" placeholder="メール内容"><?php if(isset($_POST['source_2'])){echo $_POST['source_2'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_2" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_2'])){echo $_POST['delivery_date_2'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj3=document.getElementById('open3').style; obj3.display=(obj3.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 3回目 ▼</a></div>
                    <div id="open3" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_3" placeholder="件名" value="<?php if(isset($_POST['delivery_title_3'])){echo $_POST['delivery_title_3'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_3" placeholder="メール内容"><?php if(isset($_POST['source_3'])){echo $_POST['source_3'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_3" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_3'])){echo $_POST['delivery_date_3'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj4=document.getElementById('open4').style; obj4.display=(obj4.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 4回目 ▼</a></div>
                    <div id="open4" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_4" placeholder="件名" value="<?php if(isset($_POST['delivery_title_4'])){echo $_POST['delivery_title_4'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_4" placeholder="メール内容"><?php if(isset($_POST['source_4'])){echo $_POST['source_4'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_4" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_4'])){echo $_POST['delivery_date_4'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj5=document.getElementById('open5').style; obj5.display=(obj5.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 5回目 ▼</a></div>
                    <div id="open5" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_5" placeholder="件名" value="<?php if(isset($_POST['delivery_title_5'])){echo $_POST['delivery_title_5'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_5" placeholder="メール内容"><?php if(isset($_POST['source_5'])){echo $_POST['source_5'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_5" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_5'])){echo $_POST['delivery_date_5'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj6=document.getElementById('open6').style; obj6.display=(obj6.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 6回目 ▼</a></div>
                    <div id="open6" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_6" placeholder="件名" value="<?php if(isset($_POST['delivery_title_6'])){echo $_POST['delivery_title_6'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_6" placeholder="メール内容"><?php if(isset($_POST['source_6'])){echo $_POST['source_6'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_6" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_6'])){echo $_POST['delivery_date_6'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj7=document.getElementById('open7').style; obj7.display=(obj7.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 7回目 ▼</a></div>
                    <div id="open7" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_7" placeholder="件名" value="<?php if(isset($_POST['delivery_title_7'])){echo $_POST['delivery_title_7'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_7" placeholder="メール内容"><?php if(isset($_POST['source_7'])){echo $_POST['source_7'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_7" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_7'])){echo $_POST['delivery_date_7'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj8=document.getElementById('open8').style; obj8.display=(obj8.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 8回目 ▼</a></div>
                    <div id="open8" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_8" placeholder="件名" value="<?php if(isset($_POST['delivery_title_8'])){echo $_POST['delivery_title_8'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_8" placeholder="メール内容"><?php if(isset($_POST['source_8'])){echo $_POST['source_8'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_8" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_8'])){echo $_POST['delivery_date_8'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj9=document.getElementById('open9').style; obj9.display=(obj9.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 9回目 ▼</a></div>
                    <div id="open9" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_9" placeholder="件名" value="<?php if(isset($_POST['delivery_title_9'])){echo $_POST['delivery_title_9'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_9" placeholder="メール内容"><?php if(isset($_POST['source_9'])){echo $_POST['source_9'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_9" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_9'])){echo $_POST['delivery_date_9'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj10=document.getElementById('open10').style; obj10.display=(obj10.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 10回目 ▼</a></div>
                    <div id="open10" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_10" placeholder="件名" value="<?php if(isset($_POST['delivery_title_10'])){echo $_POST['delivery_title_10'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_10" placeholder="メール内容"><?php if(isset($_POST['source_10'])){echo $_POST['source_10'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_10" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_10'])){echo $_POST['delivery_date_10'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj11=document.getElementById('open11').style; obj11.display=(obj11.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 11回目 ▼</a></div>
                    <div id="open11" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_11" placeholder="件名" value="<?php if(isset($_POST['delivery_title_11'])){echo $_POST['delivery_title_11'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_11" placeholder="メール内容"><?php if(isset($_POST['source_11'])){echo $_POST['source_11'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_11" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_11'])){echo $_POST['delivery_date_11'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="source-area">
                    <div onclick="obj12=document.getElementById('open12').style; obj12.display=(obj12.display=='none')?'block':'none';">
                        <a class="click-area" style="cursor:pointer;">▼ 12回目 ▼</a></div>
                    <div id="open12" style="display:none;clear:both;">
                        <input type="text" name="delivery_title_12" placeholder="件名" value="<?php if(isset($_POST['delivery_title_12'])){echo $_POST['delivery_title_12'];} ?>" autocomplete="off" />
                        <br />
                        <textarea name="source_12" placeholder="メール内容"><?php if(isset($_POST['source_12'])){echo $_POST['source_12'];} ?></textarea>
                        <br />
                        <input type="text" name="delivery_date_12" placeholder="配信日" value="<?php if(isset($_POST['delivery_date_12'])){echo $_POST['delivery_date_12'];} ?>" autocomplete="off" />
                    </div>
                </div>
                <input type="submit" id="register" class="button" value="　　登録　　">

            </form>
        </div>
    </body>

    </html>
