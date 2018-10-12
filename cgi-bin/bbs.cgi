#!/usr/bin/perl
#################################################################
#
# bbs.cgi : 組込みCGI用 掲示板  Ver 1.43
#
#
# Copyright (C) 1999 NTT-ME Corporation
# Produced by M.Kawabe
#
#################################################################

use Common;     $common = new Common;

$common->read_parse(*argv);
$bbsid = $argv{'bbsid'};
$bbsid =~ s/\[(\$\w+)\]/$1/g;

$IMGDIR = "https://private1.asp.mewave.com/cobalt-images";
$CONFDIR = '../cgi-conf';
$DATA_FILE = "bbs_data_$bbsid.csv";
$TMPL_FILE = "bbs_tmpl_$bbsid.html";
$ERR_LOCK_FILE_EXSIST   = -9502;
$MSG{'-9502'}           = "現在混雑しています。しばらく経ってからお試し下さい。";

$lockfile  = "./.bbs.lock";

open CONT, "$CONFDIR/bbs_count_$bbsid.txt";
$count = <CONT>;
close CONT;

if (open NAME, "$CONFDIR/bbs_name_$bbsid.txt") {
        $bbs_name = <NAME>;
        close NAME;
}
else {
        $bbs_name = "掲示板";
}

################################################################
# 現在時刻の取得とリモートホスト名の取得
#################################################################
        my ($sec, $min, $hour, $day, $month, $year, $wday, $yday, $isdst)
                                                        = localtime(time);
        $year +=1900;
        $month++;

        my $ip  = $ENV{'REMOTE_ADDR'};
        my $host= gethostbyaddr(pack("C4", split(/\./, $ip)), 2);
        $host = $ip unless ($host);

        my @week = ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
        my $date = sprintf("%.4d/%d/%d (%s) %.2d:%.2d:%.2d", $year, $month, $day, $week[$wday], $hour, $min, $sec);


#################################################################
# 修正 / 削除処理
#################################################################
$func = $argv{'func'};
if ($func eq 'delpost' or $func eq 'updpost') {
        $id      = $argv{'id'};
        $parent  = $argv{'parent'};
        $level   = $argv{'level'};
        $name    = conv($argv{'name'});
        $mail    = conv($argv{'mail'});
        $subject = conv($argv{'subject'});
        $article = conv($argv{'article'});
        $url     = conv($argv{'url'});
        $passwd  = $argv{'passwd'};

#################################################################
# 記事の消込 / 修正
#################################################################
        if ($func eq 'delpost') {
                $func_name = '削除';
        }
        else {
                $func_name = '修正';
        }

        &lock;  #　ロック
        open DATA, "$CONFDIR/$DATA_FILE";
        open TEMP, ">$CONFDIR/tmp_$DATA_FILE";

        while (<DATA>) {
                my ($did,$null,$null,$null,$null,$null,$null,$null,$null,$null,$dpass) = (split /,/);
                if ($did == $id) {
                        if ($dpass ne $passwd) {
                                print <<__END_OF_HTML__;
Content-type:text/html

<HTML><HEAD><TITLE>$func_name失敗</TITLE></HEAD>
 <BODY><CENTER><BR><BR><BR>
 <FONT COLOR='red' SIZE=4><B>パスワードが違います。<BR>
 記事の$func_nameに失敗しました。<BR></FONT>
 <BR>
 <B>パスワードを確認してください。<BR>
 <BR>
 <INPUT TYPE='button' VALUE='　\戻る　\' OnClick='history.back();'>
__END_OF_HTML__

                                close DATA;
                                close TEMP;
                                &unlock;
                                exit;
                        }
                        elsif ($func eq 'updpost') {
                                print TEMP "$id,$level,$parent,$subject,,$name,$article,$mail,$attach,$url,$passwd,$date,$host,$cate\n";
                        }
                }
                else {
                        print TEMP;
                }
        }
        close DATA;
        close TEMP;

        rename "$CONFDIR/tmp_$DATA_FILE", "$CONFDIR/$DATA_FILE";
        &unlock;        #　アンロック

        $common->jump("bbs.cgi?bbsid=$bbsid&length=$length&page=$page");
        exit;
}
elsif ($func eq 'post' or $func eq 'reppost') {
        $parent  = $argv{'parent'};
        $level   = $argv{'level'} + 1;
        $name    = conv($argv{'name'});
        $mail    = conv($argv{'mail'});
        $subject = conv($argv{'subject'});
        $article = conv($argv{'article'});
        $url     = conv($argv{'url'});
        $passwd  = conv($argv{'passwd'});
        $count++;

#################################################################
# 記事の書込
#################################################################
        &lock;  #　ロック

        open DATA, "$CONFDIR/$DATA_FILE";
        open TEMP, ">$CONFDIR/tmp_$DATA_FILE";

        unless ($parent) {
                print TEMP "$count,$level,$parent,$subject,,$name,$article,$mail,$attach,$url,$passwd,$date,$host,$cate\n";
        }
        while (<DATA>) {
                print TEMP;
                my ($id, $null) = split /,/;
                if ($id == $parent) {
                        print TEMP "$count,$level,$parent,$subject,,$name,$article,$mail,$attach,$url,$passwd,$date,$host,$cate\n";
                }
        }
        close DATA;
        close TEMP;

        open CONT, ">$CONFDIR/bbs_count_$bbsid.txt";
        print CONT $count;
        close CONT;
        chmod (0664, "$CONFDIR/bbs_count_$bbsid.txt");

        rename "$CONFDIR/tmp_$DATA_FILE", "$CONFDIR/$DATA_FILE";
        chmod (0664, "$CONFDIR/$DATA_FILE");
        &unlock;        #　アンロック

        $common->jump("bbs.cgi?bbsid=$bbsid&length=$length&page=$page");
        exit;
}

#################################################################
# 表示ページの取得
#################################################################
if ($argv{'page'}) {
        $page = $argv{'page'};
}
else {
        $page = 1;
}

#################################################################
# 表示件数の取得
#################################################################
if ($argv{'length'}) {
        $length = $argv{'length'};
}
else {
        $length = 10;
}

#################################################################
# 画面テンプレートの作成
#################################################################
unless (-T "$CONFDIR/$TMPL_FILE") {
        open TMPL, ">$CONFDIR/$TMPL_FILE";
        print TMPL <<__END_OF_HTML__;
<HTML>
 <HEAD>
  <TITLE>[\$bbs_name]</TITLE>
 </HEAD>
 <META HTTP-EQUIV='Content-type' CONTENT='text/html; charset=Shift_JIS'>
 <META HTTP-EQUIV='Content-Language' content='ja'>
 <META HTTP-EQUIV='Pragma' CONTENT='no-cache'>
 <BODY BACKGROUND='wall.gif'>
 <TABLE BORDER=0 WIDTH='95%'><TR><TD VALIGN='middle' WIDTH=20>
  <FORM METHOD="post" ACTION='bbs.cgi'>
   <INPUT TYPE="hidden" NAME="bbsid" VALUE="[\$bbsid]">
   <INPUT TYPE="hidden" NAME="length" VALUE="[\$length]">
   <INPUT TYPE="hidden" NAME="page" VALUE="[\$page]">
   <INPUT TYPE="submit" VALUE="新規投稿">
 </TD></FORM><TD ALIGN="CENTER">
<!-- 以下の行は削除しないで下さい。掲示板が動かなくなります。 -->
 [\$form_start]
 <FONT SIZE="+2"><B>[\$bbs_name]</B></FONT></TD></TR>
 </TABLE>
 <HR>
 [\$message]
 <TABLE BORDER=0>
  <TR><TD BGCOLOR="#8080FF" ALIGN="CENTER"><B>名前</TD>
      <TD><INPUT NAME="name" VALUE="[\$name]" SIZE=30></TD></TR>
  <TR><TD BGCOLOR="#8080FF" ALIGN="CENTER"><B>E-mail</TD>
      <TD><INPUT NAME="mail" VALUE="[\$mail]" SIZE=40></TD></TR>
  <TR><TD BGCOLOR="#8080FF" ALIGN="CENTER"><B>題名</TD>
      <TD><INPUT NAME="subject" VALUE="[\$subject]" SIZE=70></TD></TR>
  <TR><TD BGCOLOR="#8080FF" ALIGN="CENTER"><B>本文</TD>
      <TD><TEXTAREA NAME="article" COLS=70 ROWS=8>[\$article]</TEXTAREA></TD>
  <TR><TD BGCOLOR="#8080FF" ALIGN="CENTER"><B>関連URL</TD>
      <TD><INPUT NAME="url" VALUE="[\$url]" SIZE=70></TD></TR>
  </TR>
  <TR><TD BGCOLOR="#8080FF" ALIGN="CENTER"><B>パスワード</TD>
      <TD><INPUT TYPE="password" NAME="passwd" SIZE=20></TD></TR>
  <TR><TD ALIGN="center" COLSPAN=2 BGCOLOR="#80FF80">
<!-- 投稿ボタンは以下の INPUT タグの行に続けて記入して下さい。 -->
<INPUT TYPE="hidden" NAME="func" VALUE="post">
<INPUT TYPE='submit' VALUE='　\投稿　\'>
  </TD></TR>
 </TABLE>
 <HR>
<!-- 以下の行は削除しないで下さい。掲示板が動かなくなります。 -->
 [\$form_end]
<TABLE WIDTH='95%'>
<!-- 下記の行は変更しないで下さい。記事の最初を表\します。 -->
<!-- Start of Loop -->
<TR><TD BGCOLOR='#D0F5FF' COLSPAN=5><SMALL>
<FONT SIZE=3 COLOR='#4040FF'>[\$levelspc][No.[\$id]]</FONT>
    <FONT SIZE=3>　<B>[\$subject]</B></FONT></TD>
</TR>
<TR>
    <TD BGCOLOR='#FFFFA0'><SMALL>投稿者　[\$mail][\$name][\$mail_end]</SMALL></TD>
    <TD BGCOLOR="#FFFFA0"><SMALL>投稿日付 [\$date]</SMALL>
    <!-- 書込み元アドレス:[\$host] -->
    <TD BGCOLOR="#FFFFA0" WIDTH=10>
<!-- 返信ボタンは以下の FORM/INPUT タグとセットにして下さい。 -->
    <FORM METHOD="post" ACTION="bbs.cgi">
      <INPUT TYPE="hidden" NAME="bbsid" VALUE="[\$bbsid]">
      <INPUT TYPE="hidden" NAME="id" VALUE="[\$id]">
      <INPUT TYPE="hidden" NAME="length" VALUE="[\$length]">
      <INPUT TYPE="hidden" NAME="page" VALUE="[\$page]">
      <INPUT TYPE="hidden" NAME="func" VALUE="reply">
      <INPUT TYPE="submit" VALUE="　返信　">
    </TD>
    </FORM>
<!-- 上記の TD タグの終了は、FORM タグによる改行を防ぐために内側に置いてあります。 -->
    <TD BGCOLOR="#FFFFA0" WIDTH=10>
<!-- 修正ボタンは以下の FORM/INPUT タグとセットにして下さい。 -->
    <FORM METHOD="post" ACTION="bbs.cgi">
      <INPUT TYPE="hidden" NAME="bbsid" VALUE="[\$bbsid]">
      <INPUT TYPE="hidden" NAME="id" VALUE="[\$id]">
      <INPUT TYPE="hidden" NAME="length" VALUE="[\$length]">
      <INPUT TYPE="hidden" NAME="page" VALUE="[\$page]">
      <INPUT TYPE="hidden" NAME="func" VALUE="update">
      <INPUT TYPE="submit" VALUE="　修正　">
    </TD>
    </FORM>
<!-- 上記の TD タグの終了は、FORM タグによる改行を防ぐために内側に置いてあります。 -->
    <TD BGCOLOR="#FFFFA0" WIDTH=10>
<!-- 削除ボタンは以下の FORM/INPUT タグとセットにして下さい。 -->
    <FORM METHOD="post" ACTION="bbs.cgi">
      <INPUT TYPE="hidden" NAME="bbsid" VALUE="[\$bbsid]">
      <INPUT TYPE="hidden" NAME="id" VALUE="[\$id]">
      <INPUT TYPE="hidden" NAME="length" VALUE="[\$length]">
      <INPUT TYPE="hidden" NAME="page" VALUE="[\$page]">
      <INPUT TYPE="hidden" NAME="func" VALUE="delete">
      <INPUT TYPE="submit" VALUE="　削除　">
    </TD>
    </FORM>
<!-- 上記の TD タグの終了は、FORM タグによる改行を防ぐために内側に置いてあります。 -->
</TR>
<TR><TD BGCOLOR="#FFD0FF" COLSPAN=5>[\$url]</TD></TR>
<TR><TD COLSPAN=5>[\$article]<BR></TD></TR>
<TR><TD>　</TD></TR>
<!-- End of Loop -->
<!-- 上記の行は変更しないで下さい。記事の最後を表\します。 -->
</TABLE>
<A HREF='bbs.cgi?bbsid=[\$bbsid]&length=[\$length]&page=[\$p_page]'>前のページへ</A>
[\$dot]
<A HREF='bbs.cgi?bbsid=[\$bbsid]&length=[\$length]&page=[\$n_page]'>次のページへ</A>
[\$separate]
<TABLE BORDER=0><TR><TD>
<FORM METHOD='post' ACTION='bbs.cgi'>
 <INPUT TYPE='hidden' NAME='bbsid' VALUE='[\$bbsid]'>
 <INPUT TYPE='hidden' NAME='length' VALUE='10'>
 <INPUT TYPE='submit' VALUE='10件ずつ表\示'>
</TD>
</FORM>
<TD>
<FORM METHOD='post' ACTION='bbs.cgi'>
 <INPUT TYPE='hidden' NAME='bbsid' VALUE='[\$bbsid]'>
 <INPUT TYPE='hidden' NAME='length' VALUE='30'>
 <INPUT TYPE='submit' VALUE='30件ずつ表\示'>
</TD>
</FORM>
<TD>
<FORM METHOD='post' ACTION='bbs.cgi'>
 <INPUT TYPE='hidden' NAME='bbsid' VALUE='[\$bbsid]'>
 <INPUT TYPE='hidden' NAME='length' VALUE='50'>
 <INPUT TYPE='submit' VALUE='50件ずつ表\示'>
</TD></TR></TABLE>
</FORM><BR><BR><BR><BR>
 </BODY>
</HTML>
__END_OF_HTML__

        close TMPL;
        chmod (0664, "$CONFDIR/$TMPL_FILE");
}

#################################################################
# 返信・削除・修正のための準備
#################################################################
$message = "<FONT COLOR='RED'><B>新規投稿<\/B><\/FONT>　\必要事項を入力後、[　\投稿　] ボタンを押して下さい。\n";
if ($func eq 'reply' or $func eq 'delete' or $func eq 'update') {
        $res_id = $argv{'id'};
        open DATA, "$CONFDIR/$DATA_FILE";
        while (<DATA>) {
                ($vid,
                $level,
                $parent,
                $subject,
                $null,
                $name,
                $article,
                $mail,
                $attach,
                $url,
                $passwd,
                $date,
                $host,
                $cate) = split /,/;

                if ($res_id == $vid) {
                        $id = sprintf( "[No.%02d]", $vid );
                        if ($func eq 'update') {
                                $message = "<FONT COLOR='RED'><B>記事$res_idの修正<\/B><\/FONT>　\パスワードを入力後、[記事修正] ボタンを押 して下さい。\n";
                        }
                        elsif ($func eq 'delete') {
                                $message = "<FONT COLOR='RED'><B>記事$res_idの削除<\/B><\/FONT>　\パスワードを入力後、[記事削除] ボタンを押 して下さい。\n";
                        }
                        else {
                                $name = "";
                                $mail = "";
                                $subject = 'Re: ' . $subject;
                                $article =~ s/<BR>/\n> /g;
                                $article = '> ' . $article;
                                $message = "<FONT COLOR='RED'><B>記事$res_idへの返信<\/B><\/FONT>　\必要事項を入力後、[返信投稿] ボタンを押 して下さい。\n";
                        }
                        $article = rconv($article);
                        last;
                }
        }
        close DATA;
}

$url = 'https://' unless ($url);

#################################################################
# 画面表示
#################################################################
print "Content-type: text/html\n\n";

open TMPL, "$CONFDIR/$TMPL_FILE";
while (<TMPL>) {
        $find = $FALSE;
        # フォームタグ表示
        if (/\[\$form_start\]/) {
                print "<FORM METHOD='post' ACTION='bbs.cgi'>\n";
                print "<INPUT TYPE='hidden' NAME='bbsid' VALUE='$bbsid'>\n";
                print "<INPUT TYPE='hidden' NAME='length' VALUE='$length'>\n";
                print "<INPUT TYPE='hidden' NAME='page' VALUE='$page'>\n";
                if ($func eq 'reply') {
                        print "<INPUT TYPE='hidden' NAME='parent' VALUE='$res_id'>\n";
                        print "<INPUT TYPE='hidden' NAME='level' VALUE='$level'>\n";
                }
                elsif ($func eq 'delete' or $func eq 'update') {
                        print "<INPUT TYPE='hidden' NAME='id' VALUE='$vid'>\n";
                }
        }
        elsif (/\[\$form_end\]/) {
                print "</FORM>\n";
        }

        # 変数置換
        while (/\[(\$\w+)\]/) {
                $val = eval $1;
                $val =~ s/\[(\$\w+)\]/$1/g;
                s/\[(\$\w+)\]/$val/;
        }

        if (/<INPUT TYPE=\"hidden\" NAME=\"func\" VALUE=\"post\">/) {
                $find = 1;
                if ($func eq 'reply') {
                        s/post/reppost/;
                }
                elsif ($func eq 'delete') {
                        s/post/delpost/;
                }
                elsif ($func eq 'update') {
                        s/post/updpost/;
                }
        }

        last if (/<!-- Start of Loop -->/);

        print $_;

        if ($find) {
                $_ = <TMPL>;
                if ($func eq 'reply') {
                        s/　\投稿　/返信投稿/;
                }
                elsif ($func eq 'delete') {
                        s/　\投稿　/記事削除/;
                }
                elsif ($func eq 'update') {
                        s/　\投稿　/記事修正/;
                }
                print $_;
        }
}

#################################################################
# 記事フォーマットの読込み
#################################################################
$art_tmpl = "";
while (<TMPL>) {
        last if (/<!-- End of Loop -->/);
        $art_tmpl .= $_;
}

#################################################################
# 表示前の記事読み飛ばし
#################################################################
$first  = $length * ($page - 1);
$last   = $first + $length;
$count  = 0;

open DATA, "$CONFDIR/$DATA_FILE";
while (<DATA>) {
        if (++$count > $first) {
                if ($count > $last) {
                        $line = $_;
                        last;
                };
                ($vid,
                $level,
                $parent,
                $subject,
                $null,
                $name,
                $article,
                $mail,
                $attach,
                $url,
                $passwd,
                $date,
                $host,
                $cate) = split /,/;

                $id = sprintf ("%02d", $vid);
                $name    = rconv($name);
                $mail    = rconv($mail);
                $subject = rconv($subject);
                $article = rconv($article);
                $url     = rconv($url);
                if ($url and $url ne 'https://') {
                        $url = "<B>関連URL:</B>　<A HREF='$url' TARGET='_new_$vid'>$url</A>";
                }
                else {
                        $url = '';
                }
                $article =~ s/\n/<BR>\n/g;
                $passwd  = rconv($passwd);

                $art_lines = $art_tmpl;
                $levelspc = '　'x($level*2-2);

                if ($mail) {
                        $mail = "<A HREF='mailto:$mail'>";
                        $mail_end = "</A>";
                }
                else {
                        $mail = "";
                        $mail_end = "";
                }

                while ($art_lines =~ /\[(\$\w+)\]/) {
                        $val = eval $1;
                        $val =~ s/\[(\$\w+)\]/$1/g;
                        $art_lines =~ s/\[(\$\w+)\]/$val/;
                }

                print $art_lines;
        }
}

if ($page != 1) {
        $p_page = $page-1;
}
else {
        $p_page = "";
}

if ($page != 1 and $line) {
        $dot = "・";
}
else {
        $dot = "";
}

if ($line) {
        $n_page = $page+1;
}
else {
        $n_page = "";
}

if ($page != 1 or $line) {
        $separate = "<BR>\n<HR>";
}
else {
        $separate = "";
}

while (<TMPL>) {

        if (/\[\$p_page\]/) {
                if ($p_page) {
                        s/\[\$p_page\]/$p_page/;
                }
                else {
                        next;
                }
        }

        if (/\[\$n_page\]/) {
                if ($n_page) {
                        s/\[\$n_page\]/$n_page/;
                }
                else {
                        next;
                }
        }

        while (/\[(\$\w+)\]/) {
                $key = $1;
                $val = eval $key;
                $val =~ s/\[(\$\w+)\]/$1/g;
                s/\[(\$\w+)\]/$val/;
        }

        print $_;
}

close TMPL;
exit;

#################################################################
# 記事の保存形式へ変換
#################################################################
sub conv {
        my $item = shift;

        $item =~ s/,/\f/g;
        $item =~ s/\n/<BR>/g;

        $item;
}

#################################################################
# 保存形式から表示形式へ変換
#################################################################
sub rconv {
        my $item = shift;

        $item =~ s/\f/,/g;
        $item =~ s/<BR>/\n/g;

        $item;
}

#################################################################
# ファイルのロック
#################################################################
sub lock
{
        $symlink_check = (eval {symlink("","");}, $@ eq "");
        if (!$symlink_check) {
                $c = 0;
                while(-f "$lockfile") {
                        $c++;
                        if (((stat $lockfile)[9] + 30) < (time)) {
                                &unlock;
                        }
                        elsif ($c >= 3) {
                                $common->error($ERR_LOCK_FILE_EXSIST);
                        }
                        sleep(2);
                }
                open(LOCK,">$lockfile");
                close(LOCK);
        }
        else {
                local($retry) = 3;
                while (!symlink(".", $lockfile)) {
                        if (((lstat $lockfile)[9] + 30) < (time)) {
                                &unlock;
                        }
                        elsif (--$retry <= 0) {
                                $common->error($ERR_LOCK_FILE_EXSIST);
                        }
                        sleep(2);
                }
        }
}

#################################################################
# ファイルのアンロック
#################################################################
sub unlock
{
        $symlink_check = (eval {symlink("","");}, $@ eq "");
        if (!$symlink_check) {
                unlink $lockfile if (-f $lockfile);
        }
        else {
                unlink $lockfile if (-l $lockfile);
        }
}

#################################################################
# successfully return
#################################################################
1;

#################################################################
# end of bbs.cgi
#################################################################
