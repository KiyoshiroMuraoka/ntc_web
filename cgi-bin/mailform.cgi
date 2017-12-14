#!/usr/bin/perl
#################################################################
#
# mailform.cgi : 組込みCGI用 メールフォーム  Ver 1.46
#
#
# Copyright (C) 1999 NTT-ME Corporation
# Produced by M.Kawabe
#
#################################################################

$TMPDIR = '/tmp';
$CONFDIR = '../cgi-conf';

use Common;     $common = new Common;
use Smtp;       $smtp   = new Smtp;

$TRUE   = 1;
$FALSE  = 0;

$common->read_parse(*argv);

$IMGDIR = "http://private1.asp.mewave.com/cobalt-images";

$ERR_REMOTE_HOST_REPLY  = -9000;
$MSG{'-9000'}           = "サーバから以下の応答がありました。";
$ERR_FAILED_TO_CONNECT  = -9005;
$MSG{'-9005'}           = "サーバへの接続に失敗しました。";
$ERR_INVALID_HEADER     = -9010;
$MSG{'-9010'}           = "メールのヘッダー部分が正しくありません。";
$ERR_SENDER_NOT_FOUND   = -9011;
$MSG{'-9011'}           = "差出人アドレスの指定がありません。";
$ERR_RECIPIENT_NOT_FOUND= -9012;
$MSG{'-9012'}           = "受取人アドレスの指定がありません。";

$host   = 'localhost';
$from   = 'mailform@asp.mewave.com';
@to     = ();
$subject= 'メールフォームからの投稿';
$postid = $argv{"__id__"};
$postid = $argv{"__ID__"} unless($postid);
$postid = $argv{"_id_"} unless($postid);
$postid = $argv{"_ID_"} unless($postid);
$postid = "\L$postid";

#################################################################
# conf ファイルの読込
#################################################################

open CONF, "$CONFDIR/mail.conf";

$id = '';
while (<CONF>) {
        s/^#(.*)//;
        s/([\x00-\x7f\xa0-\xdf])#(.*)/$1/;
        if (/ID\s*:\s*(\w*)/i) {
                $id = "\L$1";
        }
        if ($id eq $postid and /(\w+)\s*=\s*(\S.*)/) {
                $key = $1;
                $value = $2;
                chomp $value;
                $value =~ s/[\r\n]+$//;
                if ($key =~ /^__mail__$/i) {
                        push @to, $value;
                }
                elsif (/^_mail_$/i) {
                        push @to, $value;
                }
                push @to, $value if ($key =~ /^mail$/i);
                push @to, $value if ($key =~ /^to$/i);
                @sort   = split /,/, $value if ($key =~ /^sort$/i);
                @item   = split /,/, $value if ($key =~ /^item$/i);
                if ($key =~ /^from$/i) {
                        if ($value =~ /\@/) {
                                $from = $value;
                        }
                        else {
                                $from = $argv{$value} if ($argv{$value});
                        }
                }
                $subject= $value if ($key =~ /^subj$/i);
                $subject= $value if ($key =~ /^subject$/i);
        }
}

$to = join ', ', @to;

close CONF;

#################################################################
# 表示順序・表示項目の設定
#################################################################
@sort = sort keys %argv unless (@sort);
if (@item) {
        foreach $key (@sort) {
                $item = shift @item;
                if ($item) {
                        $item{$key} = $item;
                }
                else {
                        $item{$key} = $key;
                }
        }
}
else {
        foreach $key (@sort) {
                $item{$key} = $key;
        }
}

if ($argv{"__func__"} ne '　送信　') {
        $TMPL_FILE = "$CONFDIR/conf_tmpl_$postid.html";
        unless (-T $TMPL_FILE) {
#################################################################
# 画面テンプレートの作成
#################################################################
                open TMPL, ">$TMPL_FILE";
                print TMPL <<__END_OF_HTML__;
<!--
     Copyright (C) 2001-2004 NTT Bizlink Corporation. All rights reserved.
     Produced by M.Kawabe
-->
<HTML>
 <HEAD>
  <TITLE>メールフォーム</TITLE>
 </HEAD>
 <META HTTP-EQUIV='Content-type' CONTENT='text/html; charset=Shift_JIS'>
 <META HTTP-EQUIV='Content-Language' content='ja'>
 <META HTTP-EQUIV='Pragma' CONTENT='no-cache'>
 <BODY>
<BR>
<BR>
<CENTER>
<H1>投稿内容の確認</H1>
 <TABLE>
  <TR>
   <TD><FONT SIZE="-1"><B>以下の内容で送信します。</B></FONT></TD>
  </TR>
  <TR>
   <TD><FONT SIZE="-1"><B>よろしければ、送信ボタンを押してください。</B></FONT></TD>
  </TR>
 </TABLE>
<FORM METHOD="post" ACTION="mailform.cgi">
<TABLE CELLSPACING=0 BORDER=1 BORDERCOLORLIGHT=black WIDTH="50%">
[LOOP]
<TR><TD WIDTH=20%>[\$key]</TD>
<TD>[\$value]</TD></TR>
[/LOOP]
</TABLE>
<BR>
<INPUT NAME='__func__' TYPE='submit' VALUE='　\送信　\'>
</FORM>
</BODY>
</HTML>
__END_OF_HTML__

                close TMPL;
                chmod (0664, $TMPL_FILE);
        }

#################################################################
# 投稿された内容を表示
#################################################################
        print "Content-type: text/html\n\n";
        $HEADER_OK = $TRUE;

        $found = 0;
        open TMPL, $TMPL_FILE;
        while (<TMPL>) {

                if (/\[LOOP\]/) {
                        $loop = '';
                        $loop .= $';
                        print $`;
                        while (<TMPL>) {
                                if (/\[\/LOOP\]/) {
                                        $loop .= $`;
                                        $_ = $';
                                        last;
                                }
                                $loop .= $_;
                        }
                        foreach $key (@sort) {
                                $string = $loop;
                                unless ($key eq '__mail__' or $key eq '__id__') {
                                        $string =~ s/\[\$key\]/$item{$key}/g;
                                        $string =~ s/\[\$value\]/$argv{$key}/g;
                                        print $string;
                                }
                        }
                }
                /\[\$value\{([^\}]+)\}\]/;
                s/\[\$value\{([^\}]+)\}\]/$argv{$1}/g;

                if (not $found and /<\/FORM>/i) {
                        foreach $key (keys %argv) {
                                print "<INPUT TYPE='hidden' NAME='$key' VALUE='$argv{$key}'>\n";
                        }
                }

                print;
        }
}
else {
        print "Content-type: text/html\n\n";
        $HEADER_OK = $TRUE;

#################################################################
# メール本文の作成
#################################################################
        $body  = "\n";
        $body .= "メールフォームより以下の内容を受付ました。\n";
        $body .= "\n";
        $body .= "\n";

        $itemlen        = 0;
        $valuelen       = 0;
        foreach $key (@sort) {
                $itemlen        = length($item{$key}) if ($itemlen < length($item{$key}));
                $valuelen       = length($argv{$key}) if ($valuelen < length($argv{$key}));
        }
        $itemlen++;
        $valuelen++;

        foreach $key (@sort) {
                unless ($key eq '__mail__' or $key eq '__func__' or $key eq '__id__') {
                        $body .= sprintf "%${itemlen}.9000s : %s\n", $item{$key}, $argv{$key};
                }
        }

        $body .= "\n\n";
        $body .= "------------------------------------------\n";
        $body .= "Copyright (C) 2001-2004 NTT Bizlink Corporation. All rights reserved.\n";
        $body .= "------------------------------------------\n";

        $smtp->send2($host, $from, $to, $subject, 3, '', '', \$body);

        $TMPL_FILE = "$CONFDIR/comp_tmpl_$postid.html";
        unless (-T $TMPL_FILE) {
#################################################################
# 画面テンプレートの作成
#################################################################
                open TMPL, ">$TMPL_FILE";
                print TMPL <<__END_OF_HTML__;
<!--
     Copyright (C) 2001-2004 NTT Bizlink Corporation. All rights reserved.
     Produced by M.Kawabe
-->
<HTML>
 <HEAD>
  <TITLE>
   送信完了
  </TITLE>
 </HEAD>
 <META HTTP-EQUIV='Content-type' CONTENT='text/html; charset=Shift_JIS'>
 <META HTTP-EQUIV='Content-Language' content='ja'>
 <META HTTP-EQUIV='Pragma' CONTENT='no-cache'>
 <BODY>
<BR>
<BR>
<CENTER>
<H1>送信完了</H1>
 <TABLE>
  <TR>
   <TD><FONT SIZE="-1"><B>送信を受付ました。</B></FONT></TD>
  </TR>
 </TABLE>

</BODY>
</HTML>
__END_OF_HTML__

                close TMPL;
                chmod (0664, $TMPL_FILE);
        }

        open TMPL, $TMPL_FILE;
        while (<TMPL>) {
                print;
        }
}

#################################################################
# successfully return
#################################################################
1;

#################################################################
# end of mailform.cgi
#################################################################
