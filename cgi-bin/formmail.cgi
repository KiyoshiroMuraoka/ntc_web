#!/usr/bin/perl --
# フォームメールシステム Version 3.2
# Copyright 1999 武藤 健志 <kmuto@isoternet.org>
#
# 必要なモジュール: CGI.pm, Socket.pm, jcode.pl, base64.pl
# modified by m.sakurai 2001
# Version 3.2.b

# Perl内部コード (Windows:sjis UNIX:euc)
$charcode = 'sjis';

# 設定ファイルのあるディレクトリ
$filebase = "mailtmps";
#$mailcmd='/usr/bin/sendmail';
$mailcmd='/usr/sbin/sendmail';

# 危険な命令の禁止 (0:解除 1:禁止)
$warning = 1;

# デフォルト値
$param{'sendokname'} = "ok";
$param{'subject'} = "Form Mail System";
$param{'to'} = "postmaster";
$param{'from'} = "nobody";
$param{'fromhost'} = "localhost";
$param{'tohost'} = "localhost";

# CGIモジュール組み込み
use CGI;
# ソケットモジュール組み込み
use Socket;

# 日本語変換
require 'jcode.pl';
&jcode::init();

# base64
require 'base64.pl';

# CGIのパラメータで示されるサブディレクトリ
$dirname = "";

# CGI入力取り込み
$query = new CGI;

# エラーメッセージ文字列
$errorstring = "";

# 自分自身のURL
$myurl = $query->url;

# CGIパラメータ値格納
undef %CGIvalue;
# 設定ファイルパラメータ
undef %param;
# 制限事項パラメータ
undef %limit;
# エラーメッセージパラメータ
undef %errormessage;
# 計算パラメータ
undef %calceval;

# ロックディレクトリ
$lockdir = "lock";

# リトライ回数
$retry = 5;

# CGIvalue へCGIパラメータ値を格納し、内部コードに統一
foreach ($query->param) {
  local($key) = $_;
  local(@values) = $query->param($key);
  local($value);
  if (@values > 1) {
    $value = join("\t", @values);
  } else {
    $value = $values[0];
  }
  $CGIvalue{$key} = jcode::to($charcode, $value);
}

# 設定ファイル読み込み
&loadconf;

# CGIモードによって表示ページ選択
if ($query->param('CGImode') eq '1') {
  # 値チェック
  if (&valuecheck ne '') {
    # エラーが発生
    if ($param{'errorfile'} ne '') {
      # エラー画面があるなら表示
      &errorload();
    } else {
      # 必要でないならフォーム画面に表示
      &formload();
    }
  } else {
    # 送信フォームから届いたとき
    if ($param{'commitfile'} ne '') {
      # 確認画面が必要なら表示
      &commitload();
    } else {
      # 必要でないなら送信
      &sendandthanks();
    }
  }
} elsif ($query->param('CGImode') eq '2') {
  # 確認画面から届いたとき
  if ($query->param($param{'sendokname'})) {
    # 送信する
    &sendandthanks();
  } else {
    &error("送信できませんでした");
    #&formload();
  }
} else {
  # まだ選択されていないかエラーから届いたとき
  &formload();
}

sub formload {
  # フォーム入力画面読み込み
  if (!open(F, "$filebase/$dirname/" . $param{'formfile'})) {
    &error("formfile エントリで示されるファイルが開けません。");
  }

  # HTMLヘッダ
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 1);
  }
  close(F);
}

sub commitload {
  # 確認画面読み込み
  if (!open(F, "$filebase/$dirname/" . $param{'commitfile'})) {
    &error("formfile エントリで示されるファイルが開けません。");
  }

  # HTMLヘッダ
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 2);
  }
  close(F);
}

sub errorload {
  # エラー画面読み込み
  if (!open(F, "$filebase/$dirname/" . $param{'errorfile'})) {
    &error("errorfile エントリで示されるファイルが開けません。");
  }

  # HTMLヘッダ
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 3);
  }
  close(F);
}

sub sendandthanks {
  # メール送信 
  if(exists($param{'sendsw'})){
    if($param{'sendsw'} == 0){
    &mailsend;
    }elsif($param{'sendsw'} == 1){
    &mailsend2;
    }elsif($param{'sendsw'} == 2){
    &mailsend3;
    }elsif($param{'sendsw'} == 3){
    if(&mailsend3){
        &mailsend2;
    }
    }else{
    &mailsend2;
    }
  }else{
    &mailsend2;
  }
  # 送信し感謝画面読み込み
  if (!open(F, "$filebase/$dirname/" . $param{'thanksfile'})) {
    &error("thanksfile エントリで示されるファイルが開けません。");
  }

  # HTMLヘッダ
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 4);
  }
  close(F);

}

sub valuecheck {
  # 変数の値を制限事項などを基にチェック
  $errorstring = "";
  # 必須項目チェック
  local(@require) = split(/[ \t,]+/, $param{'require'});
  for (@require) {
    if (!$CGIvalue{$_}) {
      # 必須に入っていない
      $errorstring .= $errormessage{$_} . "<br>\n";
    }
  }
  foreach (keys %limit) {
    # 制限事項チェック
    local($key) = $_;
    local($flag) = 0;
    local(@list) = split(/,/, $limit{$key});
    for (@list) {
      if (/^([\d]+)\-([\d]+)$/) {
    # 数値範囲
    local($from) = $1;
    local($to) = $2;
    if ($CGIvalue{$key} !~ /^\d+$/) {
      # 数値ではないので失敗
      next;
    }
    if ($from <= $CGIvalue{$key} && $CGIvalue{$key} <= $to) {
      # 範囲に該当
      $flag = 1;
      last;
    }
      } elsif ($_ eq 'MAILADDRESS') {
    # メールアドレス形式
    if ($CGIvalue{$key} =~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/) {
      # メールアドレスに似たものに該当
      $flag = 1;
      last;
    }
      } else {
    # 完全一致
    if ($_ eq $CGIvalue{$key}) {
      $flag = 1;
      last;
    }
      }
    }
    if ($flag == 0) {
      # マッチしなかった
      $errorstring .= $errormessage{$key} . "<br>\n";
    }
  }
  foreach (keys % calceval) {
    # 動的計算
    local($key) = $_;
    local($value) = $calceval{$key};

    while ($value =~ /<!--([^>]+)-->/go) {
      local($value2) = $1;
      local($pre) = $`;
      local($post) = $';
      if ($CGIvalue{$value2}) {
    local($value3) = $CGIvalue{$value2};
    # @や$などを退避
    $value3 =~ s/\@/\\\@/go;
    $value3 =~ s/\$/\\\$/go;
    $value3 =~ s/\%/\\\%/go;
    $value3 =~ s/\&/\\\&/go;
    $value = $pre . $value3 . $post;
      } else {
    &error("$value2が未定義です。");
      }
    }

    # 危険な関数のチェック
    if ($warning) {
      if ($value =~ /open/ || $value =~ /unlink/ || $value =~ /symlink/ || $value =~ /socket/ || $value =~ /mkdir/ || $value =~ /rmdir/ || $value =~ /system/ || $value =~ /exec/) {
    &error("許可されていない計算文字列が含まれています。");
      }
    }

    local($result) = '';
    if ($value =~ /\$result[ \t]*=/) {
      # 複雑な式
      eval("$value");
    } else {
      # 簡単な式
      eval("\$result = $value");
    }
    &error("式の評価中にエラーが発生しました。:$@") if ($@);
    $CGIvalue{$key} = $result;
  }

 # 個々の使用に特化した条件
    # メールアドレス一致
    if($CGIvalue{'email'} ne $CGIvalue{'confirm_email'}){
        $errorstring .= $errormessage{'confirm_email'} . "<br>\n";
}
    
    

    $errorstring;
}

sub fileparse {
  # ファイル内容を解析し、変換を行い、JISにして戻す
  local($line, $type) = @_;
  # 内部コードへ変換
  $line = jcode::to($charcode, $line);
  # 再帰パース実行
  $line = &recursiveparse($line, $type);
  # JISへ変換
  #$line = jcode::to('jis', $line);
  $line;
}


sub recursiveparse {
  # 再帰でパーシング
  local($value, $type) = @_;
  local($head) = "";
  local($tail) = "";
  if ($value =~ /<!--([^>]+)-->/go) {
    $command = $1;
    $head = $`;
    $tail = $';
    if ($command =~ /^MYSELF$/i) {
      # 自身のURL
      $value = $myurl;
    } elsif ($command =~ /^DATE$/i) {
      # 日付
      local($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime(time);
      $value = sprintf("%d年%d月%d日 %02d:%02d:%02d", $year + 1900, ++$mon, $mday, $hour, $min, $sec);
    } elsif ($command =~ /^REMOTE_HOST$/i) {
      # 相手ホスト名
      $value = $query->remote_host();
    } elsif ($command =~ /^USER_AGENT$/i) {
      # 相手ブラウザ名
      $value = $query->user_agent();
    } elsif ($command =~ /^VALUE/i) {
      # 文字変数値
      local($tmp, $name, $val) = split(/:/, $command, 3);
      if ($CGIvalue{$name} ne '') {
        # すでに値が入っているときにはそれを使う
        $val = $CGIvalue{$name};
      }
      $value = $val;
      if($type==2){
	  $value=~s/&/&amp;/g;
	  $value=~s/</&lt;/g;
	  $value=~s/>/&gt;/g;
	  $value=~s/"/&quot;/g;
	  $value=~s/'/&#39;/g;
	  $value=~s/\r\n/<br>/g;
     }
    } elsif ($command =~ /^INCLUDE:(.*)/i) {
      # ファイルインクルード
      local($filename) = $1;
      if (!open(F2, "$filebase/$dirname/$filename")) {
        $value = "<font color=\"#ff0000\">$filename は開けません。:$!</font><br>\n";
      } else {
        $value = "";
        while (<F2>) {
          $_ = jcode::to($charcode, $_);
          $value .= $_;
        }
        close(F2);
      }
    } elsif ($command =~ /^SELECT/i || $command =~ /^CHOICE/i) {
      # 選択
      local($tmp, $name, $val, $checked) = split(/:/, $command, 4);
      if ($query->param('CGImode') eq '1' || $query->param('CGImode') eq '4' ||
           ($query->param('CGImode') eq '2' &&
               !$query->param($param{'sendokname'})) || $query->param('CGImode') eq '3') {
        # 確認画面から届いたとき
        # 入力を切り分ける
        local(@values) = split(/\t/, $CGIvalue{$name});
        $value = "";
        for (@values) {
          if ($val eq $_) {
            if ($command =~ /^SELECT/i) {
              $value = " selected ";
            } else {
              $value = " checked ";
            }
            last;
          }
        }
      } else {
        # デフォルト値に基き選択
        $value = "";
        if ($checked) {
          if ($command =~ /^SELECT/i) {
            $value = " selected ";
          } else {
            $value = " checked ";
          }
        }
      }
    } elsif ($command =~ /^ERROR$/i) {
      # エラーメッセージ
      $value = $errorstring;
    } elsif ($command =~ /^INFO$/i) {
      # Hidden文字列
      $value = "<input type=\"hidden\" name=\"CGImode\" value=\"$type\">\n";
      # ページ形式によって選択
      if ($type == 1) {
        # フォーム入力
        $value .= "<input type=\"hidden\" name=\"keywords\" value=\"$dirname\">";
      } elsif ($type == 2 || $type == 3) {
        # 確認またはエラーのときにはhiddenで転送
        foreach (keys %CGIvalue) {
          if ($_ eq 'CGImode') {
            # CGImode変数は飛ばす
            next;
          }
          # そのまま転送
	  $val=$CGIvalue{$_};
          $val=~ s/"/'/g;
	  $value .= "<input type=\"hidden\" name=\"$_\" value=\"" . $val . "\">\n";
        }
      } elsif ($type == 5) {
        # メール送信内容
        $value = "";
        foreach (keys %CGIvalue) {
          if ($_ eq 'CGImode') {
            # CGImode変数は飛ばす
            next;
          }
          # そのまま転送
          $value .= "$_:" . $CGIvalue{$_} . "\n";
        }
      }
    }
    # まだパースしていない可能性があるので、再帰
    $tail = &recursiveparse($tail);
  }
  # 結果を出力
  $head . $value . $tail;
}

sub loadconf {
  # 設定ファイル読み込み
  $dirname = $query_string;
  if (/^keywords=/) {
    $dirname =~ s/keywords=//;
  } else {
    $dirname = $CGIvalue{'keywords'};
  }
  if (!open(F, "$filebase/$dirname/config")) {
    &error("無効なQUERY式です。");
  }
  while (<F>) {
    chomp;
    if (/^\#/ || /^[\t ]*$/) {
      # コメントは飛ばす
      next;
    }
    ($key, $value) = split(/=/, $_, 2);
    if ($key eq 'to' ||
    $key eq 'tohost' ||
    $key eq 'fromhost' ||
    $key eq 'from' ||
    $key eq 'sendfile' ||
    $key eq 'sendfile2' ||
    $key eq 'sendsw' ||
    $key eq 'csvfile' ||
    $key eq 'formfile' ||
    $key eq 'errorfile' ||
    $key eq 'commitfile' ||
    $key eq 'thanksfile' ||
    $key eq 'require' ||
    $key eq 'sendokname' ||
    $key eq 'col_join' || $key eq 'raw_join' ) {
      $param{$key} = $value;
    } elsif ($key eq 'subject') {
      # サブジェクト
      local($b64value) = jcode::to('jis', $value);
      $b64value =~ s/(\x1b\x24\x42[^\x1b]*\x1b\x28[\x42\x4a])/"=?ISO-2022-JP?B?" . base64::b64encode($1) . "?="/goe;
      $b64value =~ tr/\r\n//d;
      $param{$key} = $b64value;
    } elsif ($key eq 'limit') {
      # 制限事項
      local($name, $val) = split(/:/, $value, 2);
      $limit{$name} = $val;
    } elsif ($key eq 'error') {
      # エラーメッセージ
      local($name, $val) = split(/:/, $value, 2);
      $errormessage{$name} = $val;
    } elsif ($key eq 'eval') {
      # 式評価
      local($name, $val) = split(/:/, $value, 2);
      $calceval{$name} = $val;
    } else {
      &error("未知のキー $key があります。");
    }
  }
  close(F);

  if($param{'sendsw'} eq '0'){
    &error("sendsw エントリ=0は使用しないでください。");
  }
  if ($param{'to'} eq '') {
    &error("to エントリが設定ファイル中にありません。");
  }
  if ($param{'from'} eq '') {
    &error("from エントリが設定ファイル中にありません。");
  }
  local(@sendfiles) = split(/[ \t]*,[ \t]*/, $param{'sendfile'});
  foreach (@sendfiles) {
    if (!-f "$filebase/$dirname/" . $_) {
      &error("sendfile エントリで示されるファイルが開けません。");
    }
  }
  local(@tos) = split(/[ \t]*,[ \t]*/, $param{'to'});
  local(@froms) = split(/[ \t]*,[ \t]*/, $param{'from'});
  local(@subjects) = split(/[ \t]*,[ \t]*/, $param{'subject'});
  if (@tos != @sendfiles) {
    &error("送信先とテンプレートの数が合っていません。");
  }

  if (@tos != @froms) {
    &error("送信先と送信元の数が合っていません。");
  }

  #if(@tos != @subjects){
  #    &error("送信先とサブジェクトの数が合っていません。");
  #}
  if (!-f "$filebase/$dirname/" . $param{'formfile'}) {
    &error("formfile エントリで示されるファイルが開けません。");
  }
  if ($param{'errorfile'} ne '' &&
      !-f "$filebase/$dirname/" . $param{'errorfile'}) {
    &error("errorfile エントリで示されるファイルが開けません。");
  }
  if ($param{'commitfile'} ne '' &&
      !-f "$filebase/$dirname/" . $param{'commitfile'}) {
    &error("commitfile エントリで示されるファイルが開けません。");
  }
  if (!-f "$filebase/$dirname/" . $param{'thanksfile'}) {
    &error("thanksfile エントリで示されるファイルが開けません。");
  }
  # 複数のチェック
  if($param{'sendsw'}==3 || $param{'sendsw'}==2){
    local(@sendfiles2) = split(/[ \t]*,[ \t]*/, $param{'sendfile2'});
    local($sendfile2) = shift(@sendfiles2);
    if (!-f "$filebase/$dirname/" . $sendfile2) {
      &error("sendfile2 エントリで示されるファイルが開けません。");
    }
    #データベースファイル
    if (!exists($param{'csvfile'})) {
      &error("csvfile エントリが設定ファイル中にありません。");
    }
  }
}

sub mailsend {
  # メール送信
  # ソケット作成

  # 動的fromのチェック
  if ($param{'from'} =~ /<!--([^>]+)-->/) {
    local($value) = $1;
    if ($CGIvalue{$value}) {
      $param{'from'} = $CGIvalue{$value};
    } else {
      &error("動的送信元アドレス$valueが定義されていません。");
    }
  }
  if ( $param{'from'} !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/ ) {
    &error("不正な送信元アドレスです。:" . $param{'from'});
  }

  # 動的toのチェック
  # 複数のチェック
  local(@tos) = split(/[ \t]*,[ \t]*/, $param{'to'});
  local(@sendfiles) = split(/[ \t]*,[ \t]*/, $param{'sendfile'});
  if (@tos != @sendfiles) {
    &error("送信先とテンプレートの数が合っていません。");
  }

  for (local($i) == 0; $i < @tos; $i++) {
    local($to) = $tos[$i];
    if ($to =~ /<!--([^>]+)-->/) {
      local($value) = $1;
      if ($CGIvalue{$value}) {
    $tos[$i] = $CGIvalue{$value};
      } else {
    &error("動的送信先アドレス$valueが定義されていません。");
      }
    }
    if ( $tos[$i] !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/) {
      &error("不正な送信先アドレスです。:" . $tos[$i]);
    }
  }

  local($local_address) = (gethostbyname($param{'fromhost'}))[4];
  local($local_socket_address) = pack('S n a4 x8', AF_INET, 0, $local_address);
  local($server_address) = (gethostbyname($param{'tohost'}))[4];
  local($server_socket_address) = pack('S n a4 x8', AF_INET, 25, $server_address);
  local($protocol) = (getprotobyname('tcp'))[2];
  local($result) = '';
  
  if (!socket(SMTP, AF_INET, SOCK_STREAM, $protocol)) {
    &error("ソケットエラーが発生しました。:$!");
  }
  if (!bind(SMTP, $local_socket_address)) {
    &error("バインドエラーが発生しました。:$!");
  }
  if (!connect(SMTP, $server_socket_address)) {
    &error("接続エラーが発生しました。:$!");
  }
  # バッファリングしない
  local($old_selected) = select(SMTP);
  $| = 1;
  select($old_selected);
  $* = 1;

  print SMTP "HELO " . $param{'fromhost'} . "\n";
  $result = <SMTP>;
  foreach (@tos) {
    local($to) = $_;
    local($sendfile) = shift(@sendfiles);
    print SMTP "MAIL FROM: <" . $param{'from'} . ">\n";
    $result = <SMTP>;
    print SMTP "RCPT TO: <" . $to . ">\n";
    $result = <SMTP>;
    print SMTP "DATA\n";
    $result = <SMTP>;

    print SMTP "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n";
    print SMTP "Content-Transfer-Encoding: 7bit\n";
    print SMTP "Mime-Version: 1.0\n";
    print SMTP "X-Mailer: formmail\n";
    print SMTP "From:" . $param{'from'} . "\n";
    print SMTP "To:" . $to . "\n";
    print SMTP "Subject:" . $param{'subject'} . "\n";
    print SMTP "\n";
    # メール内容添付
    if (!open(F, "$filebase/$dirname/" . $sendfile)) {
      &error("sendfile エントリで示されるファイルが開けません。");
    }
    while (<F>) {
      print SMTP jcode::to('jis', &fileparse($_, 5));
    }
    close(F);
    print SMTP ".\n";
    $result = <SMTP>;
    # ウェイトを入れる
    sleep(2);
  }
  print SMTP "QUIT\n";
}

sub mailsend2 {
  # メール送信

  # 動的fromのチェック
  local(@froms) = split(/[ \t]*,[ \t]*/, $param{'from'});
  for (local($j) == 0; $j < @froms; $j++) {
    local($from) = $froms[$j];
    if ($from =~ /<!--([^>]+)-->/) {
      local($value) = $1;
      if ($CGIvalue{$value}) {
        $froms[$j] = $CGIvalue{$value};
      } else {
        &error("動的送信元アドレス$valueが定義されていません。");
      }
    }
    if ( $froms[$j] !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/ ) {
      &error("不正な送信元アドレスです。:" . $froms[$j]);
    }
  }

  # 動的toのチェック
  # 複数のチェック
  local(@tos) = split(/[ \t]*,[ \t]*/, $param{'to'});
  local(@sendfiles) = split(/[ \t]*,[ \t]*/, $param{'sendfile'});
  local(@subjects) = split(/[ \t]*,[ \t]*/, $param{'subject'});

  if (@tos != @sendfiles) {
    &error("送信先とテンプレートの数が合っていません。");
  }

  if (@tos != @froms) {
    &error("送信先と送信元の数が合っていません。");
  }

  #if(@tos != @subjects){
  #    &error("送信先とサブジェクトの数が合っていません。");
  #}
  for (local($i) == 0; $i < @tos; $i++) {
    local($to) = $tos[$i];
    if ($to =~ /<!--([^>]+)-->/) {
      local($value) = $1;
      if ($CGIvalue{$value}) {
    $tos[$i] = $CGIvalue{$value};
      } else {
    &error("動的送信先アドレス$valueが定義されていません。");
      }
    }
    if ( $tos[$i] !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/) {
      &error("不正な送信先アドレスです。:" . $tos[$i]);
    }
  }

  if(exists($param{'col_join'})){
      local($head, $col_str) = split(/:/, $param{'col_join'});
      local(@join_str) = split(/[ \t]*,[ \t]*/, $col_str);
      local($targ, $count) = split(/[ \t]*,[ \t]*/, $head);
      #print "Content-type: text/html\n\n";
      
      for($i=1; $i<=$count; $i++){
      $sw=0;
      #print "$sw<br>";
 
      foreach (@join_str) {
          if($CGIvalue{"$_$i"} eq ''){
          delete $CGIvalue{"$targ$i"};
          $sw++;
          #print "$sw: $#join_str+1<br>";
          next;
          }
          $CGIvalue{"$targ$i"}=$CGIvalue{"$targ$i"}.$CGIvalue{"$_$i"}."    ";
      }
      if($sw !=0 && $sw!=$#join_str+1){ 
          &error("選択が正しく行われておりません。");
      }
      }
      #exit;
  }
  if(exists($param{'raw_join'})){
    local($targ, $count) = split(/:/, $param{'raw_join'});
    for($i=1; $i<=$count; $i++){
      if($CGIvalue{"$targ$i"} eq '' ){ next;}
      $CGIvalue{$targ}=$CGIvalue{$targ}.$CGIvalue{"$targ$i"}."\n";
    }
  }

  local($subject) = shift(@subjects);
  foreach (@tos) {
    local($to) = $_;
    local($sendfile) = shift(@sendfiles);
    local($from) = shift(@froms);
    open(SMTP, "| $mailcmd $to");
    print SMTP "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n";
    print SMTP "Content-Transfer-Encoding: 7bit\n";
    print SMTP "Mime-Version: 1.0\n";
    print SMTP "X-Mailer: formmail\n";
    print SMTP "From:" . $from . "\n";
    print SMTP "To:" . $to . "\n";
    print SMTP "Subject:" . $subject . "\n";
    print SMTP "\n";
    # メール内容添付
    if (!open(F, "$filebase/$dirname/" . $sendfile)) {
      &error("sendfile エントリで示されるファイルが開けません。");
    }
    while (<F>) {
        print SMTP jcode::to('jis', &fileparse($_, 5));
    }
    close(F);
    close(SMTP);

    # ウェイトを入れる
    #sleep(2);
  }
}

sub mailsend3 {

  # 複数のチェック
  local(@sendfiles2) = split(/[ \t]*,[ \t]*/, $param{'sendfile2'});
  local($sendfile2) = shift(@sendfiles2);

  #データベースファイル
  local(@CSVS) = split(/[ \t]*,[ \t]*/, $param{'csvfile'});
  local($CSV) = shift(@CSVS);

  while(!mkdir($lockdir, 0755)) {                       # ディレクトリを作成できなければ待つ
    if(--$retry <= 0) { &error("混み合っています。"); } # リトライ回数分試行してもダメならあきらめる
    #sleep(1);                                           # 1秒待つ
  }

  open(DATA, ">>$filebase/$dirname/$CSV");
  #flock(DATA, 2);
  seek(DATA, 0, SEEK_END);
  
  # メール内容添付
  if (!open(F, "$filebase/$dirname/" . $sendfile2)) {
     &error("sendfile エントリで示されるファイルが開けません。");
  }
  while (<F>) {
      if($_ =~ /^$/){ next;}; 
      $str= &fileparse($_, 5);
      $str = jcode::to('sjis', $str);
      $str =~ s/\n//g;
      $str =~ s/\r/ /g;
      open(CHK, "$filebase/$dirname/$CSV");
      while(<CHK>){
      $end_str=$_;
      }
      $end_str =~ s/\n//g;
      $end_str =~ s/\r//g;
      
      $str2=$str;
      #open(TEST, ">>$filebase/$dirname/test.txt");
      #print TEST "$end_str,$str2\r\n";
      #close (TEST);
      if($end_str ne $str2){
      print DATA $str;
      print DATA "\r\n";
      $ret=1;
      }else{
      $ret=0;
      }
      close(CHK);
  }
  close(F);
  #flock(DATA, 8);
  close(DATA);
  rmdir($lockdir);                                      # ディレクトリを削除する

  # ウェイトを入れる
  #sleep(2);
  return ($ret);
}

sub error {
  # エラー
  local($message) = @_;
  print "Content-type: text/html\n\n";
  print "$message\n";
  exit 1;
}


