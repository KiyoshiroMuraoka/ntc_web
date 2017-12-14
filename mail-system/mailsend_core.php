<?php
    chdir('C:\xampp\htdocs\mailsend');
    //送信者(From)
    $from_address = "ec-contact@technocrats.jp";
    $from_name = "イージースター サポート";

    //この先、分かる人だけ触ってください。

    //初期読込
    header("Content-Type: text/html; charset=UTF-8");
    require './lib/sql.php';
    addlog("easyster mail send system...initialize.");

    //データベースのエンコードタイプを指定
    $database_encode = "utf-8";

    //データベース接続開始。
    $link = connect_sql();
    if (!$link) {
      addlog("[ERROR] [mailsend] データベース接続失敗...".pg_last_error());
      exit();
    }
    addlog("[mailsend] database connected...");
    // 必要な情報をデータベースから読み込まないと何も始まらない
    $timetable = load_sql("time_table", $link);
    if (!$timetable) {
      addlog("[ERROR] クエリが失敗しました。クエリ内容 -> SELECT * FROM time_table");
      exit();
    }
    addlog("[time_table] テーブル読込...");
    $template = load_sql("template", $link);
    if (!$template) {
      $log = fopen($log_file, "a");
      addlog("[ERROR] クエリが失敗しました。クエリ内容 -> SELECT * FROM template");
      fclose($log);
      exit();
    }
    addlog("[template] テーブル読込...");
    $member = load_sql("member", $link);
    if (!$member) {
      $log = fopen($log_file, "a");
      addlog("[ERROR] クエリが失敗しました。クエリ内容 -> SELECT * FROM member");
      fclose($log);
      exit();
    }
    addlog("[member] テーブル読込...");

    //タイムテーブルの行数分だけループせねばなるまい。
    for($i=0; $i < count($timetable); $i++) {
      //タイムテーブルの情報を変数へ代入しよう。
      addlog("TimeTableID[".$timetable[$i]["id"]."] start.");
      $use_template = $timetable[$i]["use_template"];
      $delivery_member = explode(",",$timetable[$i]["delivery_member"]);
      $delivery_schedule = $timetable[$i]["delivery_schedule"];
      $register_date = $timetable[$i]["register_date"];
      if ($delivery_schedule != "12") {
        for($j=0; $j < count($template); $j++) {
          if($template[$j]["name"] == $use_template){
            $num = $delivery_schedule + 1;
            $schedule = $template[$j]["delivery_date_".$num];
            $nowtemplate = $template[$j]["source_".$num];
            $subject = $template[$j]["delivery_title_".$num];
            if($template[$j]["source_".$num] == null || $template[$j]["source_".$num] == "") {
              addlog("TimeTableID[".$timetable[$i]["id"]."] 配信が終わっているため、このタイムテーブルの処理はしません。");
              $nowdelivery = false;
            } else {
              //今が送るときか？それが問題だ…
              if($schedule == "now"){
                $nowdelivery = true;
              } else {
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
                //now値でない場合は、現在の時刻が送信スケジュールを過ぎているか判定するのだ
                if(strtotime(date("Y-m-d")) >= strtotime($register_date." + ".substr($schedule, 0, 1).$strtime)){
                  if(strtotime(date("H:i:s")) >= strtotime($template[$j]["delivery_time"])){
                    addlog("TimeTableID[".$timetable[$i]["id"]."] 日時がスケジュール日時 ".date("Y-m-d", strtotime($register_date." + ".substr($schedule, 0, 1).$strtime))." ".date("H:i:s", strtotime($template[$j]["delivery_time"]))." を超えているため、メール送信します。");
                    $nowdelivery = true;
                  } else {
                    addlog("TimeTableID[".$timetable[$i]["id"]."] 日時がスケジュールの前なので、未送信で終わります。(次回スケジュール日時:".date("Y-m-d", strtotime($register_date." + ".substr($schedule, 0, 1).$strtime))." ".date("H:i:s", strtotime($template[$j]["delivery_time"])).")");
                    $nowdelivery = false;
                  }
                } else {
                  addlog("TimeTableID[".$timetable[$i]["id"]."] 日時がスケジュールの前なので、未送信で終わります。(次回スケジュール日時:".date("Y-m-d", strtotime($register_date." + ".substr($schedule, 0, 1).$strtime))." ".date("H:i:s", strtotime($template[$j]["delivery_time"])).")");
                  $nowdelivery = false;
                }
              }

              if($nowdelivery){
                //どうやら今メールを送らねばならないらしい。やれやれだ
                //送るにはまず送り先を読み込まないとな…
                $mailaddress = "";
                for($k=0; $k < count($member); $k++) {
                    if(in_array($member[$k]["id"], $delivery_member) && $member[$k]["stop_send"] == "false"){
                    //対象に含まれていた場合、各種情報を変数へ代入
                    $mail_address = $member[$k]["mail_address"];
                    $company_name = $member[$k]["company_name"];
                    $person_name = $member[$k]["person_name"];

                    //署名設定
                      $signature ="\n\n今後、イージースターからのメールを一切受け取りたくない場合は、下記URLより停止を行ってください。\nhttp://220.221.252.160/mailsend/mail-stop.php?id=".$member[$k]["id"]."&mail=$mail_address&unique=".base64_encode(base64_encode($member[$k]["id"]));
                    
                    //メールを送ろう
                    $text = str_replace("##company_name##", $company_name, $nowtemplate);
                    $text = str_replace("##person_name##", $person_name, $text);
                      $text = $text.$signature;
                    mb_language("ja");
                    mb_internal_encoding("iso-2022-jp");

                    $mailfrom = mb_convert_encoding($from_name, "iso-2022-jp", "utf-8");
                    $mailfrom = "From:" .mb_encode_mimeheader($mailfrom) ."<$from_address>\n";
                    $to_name = $company_name." ".$person_name."様";
                    $to_base = mb_convert_encoding($to_name, "iso-2022-jp", "utf-8");
                    $to = mb_encode_mimeheader($to_base)."<$mail_address>\n";
                    $result = mb_send_mail($to, mb_convert_encoding($subject, "iso-2022-jp", "auto"), mb_convert_encoding($text, "iso-2022-jp", "auto"), $mailfrom);
                    if($result){ $result_str = "送信成功"; } else { $result_str = "送信失敗";}
                    addlog("TimeTableID[".$timetable[$i]["id"]."] メール送信($mail_address)...".$result_str);
                  }
                }
              }
            }
          }
        }
        if($nowdelivery){
          //送信カウントを増やして、現在のタイムテーブルに対しての処理は終わる
          $delivery_schedule++;
          $target_id = $timetable[$i]['id'];
          $sql = "UPDATE time_table SET delivery_schedule = $delivery_schedule WHERE id = $target_id";
          $result_flag = pg_query($sql);
          if (!$result_flag) {
            addlog("[ERROR] TimeTableID[".$timetable[$i]["id"]."] データベース更新に失敗しました。クエリ内容 -> ".$sql);
          } else {
            addlog("TimeTableID[".$timetable[$i]["id"]."] end...");
          }
        }
      } else {
        addlog("TimeTableID[".$timetable[$i]["id"]."] 配信が終わっているため、このタイムテーブルの処理はしません。");
      }
    }
    // データベースの切断
    $close_flag = pg_close($link);
    addlog("[mailsend] database disconnected...");
    addlog("easyster mail send system...done.");
