package Smtp;
#################################################################
#
# Smtp.pm : ME WAVE GW Mail送信モジュール Ver 1.2
#
#     含まれるパッケージ
#          Smtp : Send mails etc.
#
# Copyright (C) 1999 NTT-ME Corporation
#
#################################################################
#
# Interface:
#
#     send
#       Send the e-mail with any host you like.
#
#
#################################################################

$TRUE   = 1;
$FALSE  = 0;

use Common;     $common = new Common;
use Mime;       $mime   = new Mime;
use Tcp;        $tcp    = new Tcp;

use Socket;

#################################################################
# Constructor
#################################################################
sub new {
        my $self = {};

        $self->{HOST}   = undef;
        $self->{FROM}   = undef;
        $self->{TO}     = undef;
        $self->{SUBJECT}= undef;
        $self->{PRIORITY}=undef;
        $self->{ACCOUNT}= undef;
        $self->{AHEADER}= undef;
        $self->{TIME}   = undef;
        $self->{BODY}   = undef;
        $self->{LINE}   = undef;

        bless($self);
        return $self;
}

sub send {
        my $self        = shift;
        $self->{HOST}   = shift;
        $self->{FROM}   = shift;
        $self->{TO}     = shift;
        $self->{SUBJECT}= shift;
        $self->{PRIORITY}=shift;
        $self->{ACCOUNT}= shift;
        $self->{AHEADER}= shift;
        my $rbody       = shift;
        my @attach      = @_;
        my $attach;

        $self->{MSID} = $self->message_id($self->date_string);

        my ($file, $header, $rows, $from, @to) =
                $mime->encode($self->{FROM}, $self->{TO}, $self->{SUBJECT}, $self->{TIME},
                $self->{MSID}, $self->{PRIORITY}, $self->{AHEADER}, $rbody, @attach);

        $tcp->TcpConnect(SOCK, $self->{HOST}, 'smtp')
                or $common->error($tcp->error);

        # No buffering what to socket
        select SOCK;
        $| = 1;
        select STDOUT;

        # ホストとの送受信開始
        $self->ReceiveCode == 220 or $self->error(220);
        $self->SendString("HELO ".$self->{HOST}."\n");
        $self->ReceiveCode == 250 or $self->error(250);

        $self->SendString("MAIL FROM:<".$from.">\n");
        $self->ReceiveCode == 250 or $self->error(250);

        foreach (@to) {
                $self->SendString("RCPT TO:<$_>\n");
                $self->ReceiveCode == 250 or $self->error(250);
        }

        $self->SendString("DATA\n");
        $self->ReceiveCode == 354 or $self->error(354);

        open OUT, $file;
        while (<OUT>) {
                $self->SendString('.', 1) if ( /^\./ );
                $self->SendString($_, 1);
        }
        close OUT;
        unlink $file;

        $self->SendString("\n.\n");
        $self->ReceiveCode == 250 or $self->error(250);

        $self->SendString("QUIT\n");
        $self->ReceiveCode == 221 or $self->error(221);

        close(SOCK);
        return $TRUE;
}

sub send2 {
        my $self        = shift;
        $self->{HOST}   = shift;
        $self->{FROM}   = shift;
        $self->{TO}     = shift;
        $self->{SUBJECT}= shift;
        $self->{PRIORITY}=shift;
        $self->{ACCOUNT}= shift;
        $self->{AHEADER}= shift;
        my $rbody       = shift;
        my @attach      = @_;
        my $attach;

        my $subject     = $self->{SUBJECT};
        my $from        = $self->{FROM};
        my $to          = $self->{TO};

        if (!open(MAIL, "| /usr/sbin/sendmail -f $from $to")) {
                print "Content-type: text/html\n\n";
                print "<HEAD>\n";
                print "<TITLE>mail</TITLE>\n";
                print "</HEAD>\n";
                print "<BODY>\n";
                print "<H2>$Message_NG</H2><p>";
                print "</BODY>\n";
                exit 1;
        }

        $$rbody = "From: $from\n".  "To: $to\n" . "Subject: $subject\n\n" . $$rbody;

        $$rbody =~ tr/+/ /;
        $$rbody =~ s/%(..)/pack("C", hex($1))/eg;
        &jcode'convert($rbody, 'jis');
        print MAIL $$rbody;

        close MAIL;

}

#################################################################
# Error treatment
#################################################################
sub error {
        my $self = shift;
        my $code = shift;

        close(SOCK);

        $main'MSG{$main'ERR_REMOTE_HOST_REPLY} .= '<BR><FONT COLOR=blue>' .
                                                        ($self->{LINE}) . '</FONT>';
        $common->error($main'ERR_REMOTE_HOST_REPLY);
}

#################################################################
# Send string
#################################################################
sub SendString {
        my $self = shift;
        local($_) = @_;

        #
        # Send end of line as CR+LF
        #
        s/([^\r])\n/$1\r\n/g;
        print SOCK;
}

#################################################################
# Get receive code
#################################################################
sub ReceiveCode {
        my $self = shift;
        my $line;

        $ReceiveCode = 0;
        while( $self->{LINE} = <SOCK> ) {
#               SMTP とのやりとりが、表示される。
#               print $line;
                #
                # Reciving is over if reply is recive code + space
                #
                if ( $self->{LINE} =~ /^(\d+) / ) {
                        $ReceiveCode = $1;
                        last;
                }
        }
        $ReceiveCode;
}

#################################################################
# Create date string
#################################################################
sub date_string {
        my $self        = shift;
        my $time;
        my @t           = localtime($time = time);
        $self->{TIME} = sprintf("%s, %02d %s %d %02d:%02d:%02d +0900 (JST)",
                (qw(Sun Mon Tue Wed Thu Fri Sat))[$t[6]],
                $t[3],
                (qw(Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec))[$t[4]],
                1900 + $t[5],
                $t[2],$t[1],$t[0]);

        $time;
}

#################################################################
# Create message id
#################################################################
sub message_id {
        my $self        = shift;
        my $time        = shift;
        my $account;

        my ($sec, $min, $hour, $day, $month, $year) = gmtime($time);
        $year += 1900;
        $month++;

        my $ran;
        my $i;
        srand(time);
        for ($i = 0; $i < 3; $i++) {
                $ran .= substr("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", int(rand(62)), 1);
        }

        sprintf("<%04d%02d%02d%02d%02d%02d%s.%s\@%s>",
                $year, $month, $day, $hour, $min, $sec, $ran, "\U$self->{ACCOUNT}", $self->{HOST});
}

#################################################################
# successfully return
#################################################################
1;

#################################################################
# end of Smtp.pm
#################################################################
