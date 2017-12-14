package Mime;
#################################################################
#
# Mime.pm : ME WAVE GW Mime 処理モジュール  Ver 1.2
#
#     含まれるパッケージ
#          Mime : Mime encoding and decoding etc.
#
# Copyright (C) 1999 NTT-ME Corporation
# Produced by M.Kawabe
#
#################################################################
#
# Interface:
#
#     setdb($create)
#	Set the values into the user's DB
#	
#
#################################################################

$TRUE	= 1;
$FALSE	= 0;

use Common;	$common = new Common;
#use Maildb;

require 'jcode.pl';

$JisCharsetName = "ISO-2022-JP";
$SjisCharsetName = "Shift_JIS";
$EucCharsetNmae = "EUC-JP";

$From;
@To;

$part;
$rows;
@attach;

#################################################################
# Constructor
#################################################################
sub new {
	my $self = {};

	$self->{FROM}	= undef;
	$self->{TO}	= undef;
	$self->{SUBJECT}= undef;
	$self->{TIME}	= undef;
	$self->{MSID}	= undef;
	$self->{PRIORITY}=undef;
	$self->{AHEADER}= undef;

	bless($self);
	return $self;
}

sub encode {
	my $self	= shift;

	$self->{FROM}	= shift;
	$self->{TO}	= shift;
	$self->{SUBJECT}= shift;
	$self->{TIME}	= shift;
	$self->{MSID}	= shift;
	$self->{PRIORITY}=shift;
	$self->{AHEADER}= shift;
	my $rbody	= shift;
	my @attachfiles	= @_;

	my $encodefile, @to;
	my $i, $header, $multipart, $boundary, @header;
	my $pos, $npos, $found = $FALSE;
	my $date, $rows, $jheader = '';

	$multipart = @attachfiles;
	$boundary = $self->make_boundary_string;

	@header = ();
	if ($self->{FROM} ne '') {
		push @header, $self->check_header("From", $self->{FROM});
		$jheader .= "From:$self->{FROM}\n";
	}

	if ($self->{TO} ne '') {
		push @header, $self->check_header("To", $self->{TO});
		$jheader .= "To:$self->{TO}\n";
	}

	if ($self->{SUBJECT} ne '') {
		push @header, $self->check_header("Subject", $self->{SUBJECT});
		$jheader .= "Subject:$self->{SUBJECT}\n";
	}

	$date =  "Date:$self->{TIME}\n";
	$jheader .= $date;

	$jheader .= "X-Priority:$self->{PRIORITY}\n";
	$jheader .= "Status:RS\n";

	$self->{AHEADER} .= "Message-Id: $self->{MSID}\n";

	$npos = 0;
	while (($pos = index($self->{AHEADER}, "\n", $npos)) > -1) {
		$_ = substr($self->{AHEADER}, $npos, $pos - $npos) . "\n";
		$jheader .= $_;
		if ( /^([!-9;-~]+) *: *(.+)/i ) {
			push(@header, $self->check_header($header, $value)) if ($header ne '');
			$header = $1;
			$value = "$2\n";
		}
		# connect to the before header if the top is space
		elsif ( /^[ \t]+(.+)/ ) {
			$value .= " $1\n";
		}
		# finish if the line is nothing
		elsif ( /^$/ ) {
			push(@header, $self->check_header($header, $value)) if ($header ne '');
			$found = $TRUE;
			last;
		}
		else {
			$common->error($main'ERR_INVALID_HEADER);
		}
		$npos = ++$pos;
	}
	if (not $found) {
		$_ = substr($self->{AHEADER}, $npos);
		if ( /^[ \t]+(.+)/ ) {
			$value .= " $1\n";
		}
		if ( /^([!-9;-~]+) *: *(.+)/i ) {
			push(@header, $self->check_header($header, $value)) if ($header ne '');
			$header = $1;
			$value = "$2\n";
		}
		push(@header, $self->check_header($header, $value)) if ($header ne '');
		$jheader .= $_;
	}

	$common->error($main'ERR_SENDER_NOT_FOUND) if ($From eq '');
	$common->error($main'ERR_RECIPIENT_NOT_FOUND) if (@To == 0);

	# Open the file which is encoded
	for ($i = 1; -f "$main'TMPDIR/send$i.txt"; $i++) {}
	$encodefile = "$main'TMPDIR/send$i.txt";
	open OUT, ">$encodefile" or $common->error($main'ERR_CANT_CREATE_TEMP_F);

	for (@header) {
		print OUT;
	}

	print OUT "MIME-Version: 1.0\n";
	print OUT "Content-Transfer-Encoding: 7bit\n";
	print OUT $date, "X-Mailer: GWave Ver1.0 Produced by M.Kawabe\n";

	# Mime Header
	if ($multipart) {
		$header = "Content-Type: multipart/mixed boundary=\"$boundary\"\n";
		$header .= "--$boundary\n";
		$header .= "Content-Type: text/plain; charset=\"$JisCharsetName\"\n";
	}
	else {
		$header = "Content-Type: text/plain; charset=\"$JisCharsetName\"\n";
	}
	print OUT $header;

	print OUT "\n";
	$npos = 0;
	$rows = 1;
	while (($pos = index($$rbody, "\n", $npos)) > -1) {
		$line = substr($$rbody, $npos, $pos - $npos) . "\n";
		&jcode::convert(*line, 'jis');
		print OUT $line;
		$npos = ++$pos;
		$rows++;
	}
	if (length($$rbody) > $npos) {
		$line = substr($$rbody, $npos);
		&jcode::convert(*line, 'jis');
		print OUT $line;
		$rows++;
	}

	# 添付ファイルがあれば
	if ($multipart) {
		for $attachfile (@attachfiles) {
			open FILE, "$attachfile" or next;
			binmode FILE;
			($filename) = $attachfile =~ /([^\\\/]+)$/;
			if (!$self->is_ascii_string($filename)) {
				&jcode::convert(*filename, 'sjis');
				$filename = $self->encode_base64_parts($filename, "\n",
						$JisCharsetName, 62); }
			print OUT <<__END_OF_HEADER__;

--$boundary
Content-Type: application/octet-stream; 
  name=\"$filename\"
Content-Transfer-Encoding: Base64

__END_OF_HEADER__
			while ($len = read(FILE, $data, 57)) {
				print OUT $self->encode_base64(substr($data, 0, $len)), "\n";
			}
			close FILE;
		}
		print OUT "\n\n--$boundary--\n";
	}

	close OUT;
	($encodefile, $jheader, $rows, $From, @To);
}

sub check_header {
	my $self	= shift;

	my $header	= shift;
	my $value	= shift;
	my $result, $len;

	local($_);

	if ($header =~ /^(from|to|cc|bcc)$/i ) {
		$value =~ s/\n//g;
		if ($header =~ /from/i) {
			$From = ($self->pick_address($value))[0] if ($From eq '');
		}
		else {
			push(@To, $self->pick_address($value));
		}
		$result = $self->encode_base64_parts("$header: $value", " *[:()<>,] *",
				$JisCharsetName, 62) . "\n";
	}
	elsif ($header =~ /^subject$/i) {
		$value =~ s/\n//g;
		$result = "$header: " . $self->encode_base64_parts($value, "\n",
			$JisCharsetName, 62) . "\n";
	}
	else {
		$value	=~ s/\n//g;
		$value	= $self->encode_base64_parts($value, "\n", $JisCharsetName, 62);
		$result = "$header: $value" . "\n";
	}
}

sub pick_address {
	my $self	= shift;
	my $string	= shift;

	local ($_);
	my $addr, @result;

	@result = ();
	$string =~ s/\n//g;
	for (split / *, */, $string) {

		# exclude comment as ()
		s/ *\([^)]*\) *//g;
		$addr = '';

		# the address itself if it doesn't contain <>
		unless ( /[<>]/ ) {
			$addr = $_;
		}

		# else the inside of <> is the address
		elsif ( /< *([^>]+) *>/ ) {
			$addr = $1;
		}
		$addr =~ s/^ +//;
		$addr =~ s/ +$//;
		push (@result, $addr) if ( $addr ne '' );
	}
	@result;
}

sub decode {
	my $self	= shift;
	my $file	= shift;
	my $codetype	= shift;
	my $account	= shift;
	my $attach_dir	= shift;
	my $rheader	= shift;
	my $rbody	= shift;
	my $outfile;
	my $from, $to, $subject, $date, $status, $charset;
	@attach = ();

	undef $status;
	$$rheader = '';
	$$rbody = '';
	open FILE, "$file" or $common->error($main'ERR_CANT_OPEN_TEMP_F);

	$part = 0;
	$rows = 1;
	(undef, $from, $to, $subject, $date, $status, $rows, $charset) =
		$self->decode_mime_part(FILE, "", $codetype,
			$account, $attach_dir, $rbody, $rheader);

	close FILE;

	$rows-- if (@attach);

	($from, $to, $subject, $date, $status, $rows, $charset, @attach);
}

sub decode_mime_part {
	my $self	= shift;
	my $filehandle	= shift;
	my $boundary	= shift;
	my $codetype	= shift;
	my $account	= shift;
	my $attach_dir	= shift;
	my $rbody	= shift;
	my $rheader	= shift;
	my $mode, $header, $type, $partnum;
	my $bodyquoted, $subboundary;
	my $from, $to, $subject, $date, $charset, $status;

	my $result = 0;
	$mode = 'header';
	$partnum = 1;
	while (<FILE>) {
		s/\r\n/\n/g;
		last if (/^\.$/);
		s/^\.//;
		if ($mode eq 'header') {
			s/=\?[^=?]+\?Q\?([^?]+)\?=/$self->decode_quote($1)/ge;
			s/=\?[^=?]+\?B\?([^?]+)\?=/$self->decode_base64($1)/ge;
			if ($codetype) {
				&jcode::convert(*_, $codetype);
			}
			if (/^[ \t](.*)/) {
				chomp $header;
				$header .= $1;
			}
			else {
				if ($header =~ /^Content-Type:/i) {
					if ($header =~ /text/i) {
						$type = 'text';
						if ($header =~ /charset=\"?([^\"^\n]+)/i) {
							$charset = $1;
						}
					}
					elsif ($header =~ /multipart\//i) {
						$type = 'multi';
						($subboundary) = $header =~ /boundary *= *\"?([^\"]+)/i;
					}
					else {
						$type = 'bin';
						if ($header =~ /name *= *\"?([^\"]+)/i) {
							for ($j = 0; -f ($name = "$attach_dir" . '_' . (sprintf "%02d" , $j) . "/$1"); $j++) {}
							mkdir ($attach_dir . '_' . (sprintf "%.2d" , $j), 0777) unless (-d $attach_dir . '_' . (sprintf "%.2d" , $j));
							push @attach, $name;
						}
						else {
							for ($j = 0; -f ($name = $attach_dir . '_00/attach' . (sprintf "%02d" , $j) . 'bin'); $j++) {}
							mkdir ($attach_dir . '_00', 0777) unless (-d $attach_dir . '_00');
							push @attach, $name;
						}
						open OB, ">$name" or $common->error($main'ERR_CANT_CREATE_TEMP_F);
						binmode OB;
					}
				}
				elsif ($header =~ /^Content-Transfer-Encoding:/i) {
					if ($header =~ /quoted-printable/i) {
						$bodyquoted = 'Q';
					}
					elsif ($header =~ /base64/i) {
						$bodyquoted = 'B';
					}
				}

				if ($header =~ /^From: ([^\n]+)/) {
					$from = $1;
				}
				elsif ($header =~ /^To: ([^\n]+)/) {
					$to = $1;
				}
				elsif ($header =~ /^Subject: ([^\n]+)/) {
					$subject = $1;
				}
				elsif ($header =~ /^Date: ([^\n]+)/) {
					$date = $1;
				}
				elsif ($header =~ /^Status: ([^\n]+)/) {
					$status = $1;
				}
				else {
					$$rheader .= $header;
				}

				$header = $_;

				if (/^$/) {
					$mode = 'body';
					next if ($type eq 'bin');
				}
			}
		}
		else {
			# End of the part
			if ($boundary ne '' && index($_,"--$boundary--") == 0 ) {
				$result = 0;
				last;
			}
			# Start of the part
			elsif ($boundary ne '' && index($_,"--$boundary") == 0) {
				$part++;
				$result = 1;
				last;
			}
			elsif ($subboundary ne '' && index($_,"--$subboundary") == 0) {
				close(OB);
				while( ($self->decode_mime_part(FILE, $subboundary, $codetype,
						$account, $attach_dir, $rbody))[0] ) {
					$partnum++;
				}
				$mode = 'header';
				$type = 'text';
				$bodyquoted = '';
				next;
			}
			if( $bodyquoted eq 'Q' ) {
				s/=\n//;
				$_ = $self->decode_quote($_);
			}
			elsif ($bodyquoted eq 'B') {
				chomp;
				$_ = $self->decode_base64($_);
			}
			if ($type ne 'bin' && $codetype) {
				&jcode::convert(*_, $codetype);
			}
			if ($part < 1) {
				$$rbody .= $_;
				$rows++;
			}
		}
		if ($mode eq 'body' and $type eq 'bin') {
			print OB;
		}
#		else {
#			print OUT;
#		}
	}
	undef $header;

	$result, $from, $to, $subject, $date, $status, $rows, $charset;
}

sub is_ascii_string {
	$_[1] =~ /[^\t\n\x20-\x7e]/ ? 0 : 1;
}

sub make_boundary_string {
	my $self	= shift;
	my $result;
	my $i;
	srand(time);
	for ($i = 0; $i < 42; $i++) {
		$result .= substr("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", int(rand(62)), 1);
	}
	$result . '==';
}

sub decode_quote {
	my $self	= shift;
	my $string	= shift;
	$string =~ s/=([A-Fa-f0-9][A-Fa-f0-9])/pack('H2',$1)/ge;

	$string;
}

sub encode_base64 {
	my $self	= shift;
	my $string	= shift;

	my $result = "";
	pos($string) = 0;
	while( $string =~ /(.{1,45})/gs ) {
		$result .= substr(pack('u', $1), 1);
		chop($result);
	}
	$result =~ tr|` -_|AA-Za-z0-9+/|;
	my $padding = (3 - length($string) % 3) % 3;
	$result =~ s/.{$padding}$/'=' x $padding/e if( $padding );

	$result;
}

sub decode_base64 {
	my $self	= shift;
	my $string	= shift;

	my $len;
	my $result = "";

	$string =~ tr|A-Za-z0-9+=/||cd;
	if (length($string) % 4) {
		return $_[0];
	}

	$string =~ s/=+$//;
	#
	# Convert to uuencode
	#
	$string =~ tr|A-Za-z0-9+/| -_|;
	while ($string =~ /(.{1,60})/g) {
		$len = pack("C",32 + int(length($1)*3/4));
		$result .= unpack("u", $len . $1 );
	}
	$result;
}

sub encode_base64_header {
	my $self	= shift;
	my ($str, $charset, $maxlen) = @_;

	my $start = "=?$charset?B?";
	my $end = "?=";
	my $leftstr, $substring;
	$maxlen -= length($start) + length($end) + 6;

	if ($maxlen > 0) {
		my $encodelen = $maxlen * 3 / 4;
		$encodelen-- if (substr($str, $encodelen - 1, 1) =~ /[\x80-\x9F\xE0-\xEF]/);
		$substring = substr($str, 0, $encodelen);
		&jcode::convert(*substring, 'jis');
		$result = $start . $self->encode_base64($substring) . $end;
		$leftstr = substr($str, $encodelen);
	}
	else {
		$result = "";
		$leftstr = $str;
	}

	($result, $leftstr);
}

sub encode_base64_parts {
	my $self	= shift;
	my ($str, $pat, $charset, $maxlen) = @_;
	my ($j, $left, $len, $result, $tmp, @work, @tmp);

	while ( $str =~ /$pat/ ) {
		push (@tmp, $`) if ($` ne '');
		push (@tmp, $&);
		$str = $';
	}
	push (@tmp, $str) if ( $str ne '' );

	grep {
		&jcode::convert(*_, 'sjis') unless ($self->is_ascii_string($_));
	} @tmp;

	# ASCII単語と日本語に分割
	foreach $tmp (@tmp) {
		while ($tmp =~ /(^|\s+)[\x20-\x7F]+(\s+|$)/) {
			push (@work, $`) if ($` ne '');
			push (@work, $&);
			$tmp = $';
		}
		push (@work, $tmp) if ($tmp ne '');
	}

	$len = 0;
	while( @work > 0 ) {
		$str = shift(@work);
		if ($self->is_ascii_string($str)) {
			if ($len + length($str) > $maxlen) {
				$result .= "\n ";
				$len = 1;
			}
			$result .= $str;
			$len += length($str);
		}
		else {
			if ($len > $maxlen) {
				$result .= "\n ";
				$len = 1;
			}
			($str, $left) = $self->encode_base64_header($str, $charset, $maxlen - $len);

			if (($len + length($str) > $maxlen)) {
				$len = 0;
			}
			elsif ($str eq '') {
				$result .= "\n ";
				$len = 1;
			}
			$result .= $str;
			$len += length($str);
			unshift (@work, $left) if ($left ne '');
		}
	}
	chomp($result);
	$result;
}

#################################################################
# successfully return
#################################################################
1;

#################################################################
# end of Mime.pm
#################################################################
