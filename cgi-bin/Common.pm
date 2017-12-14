package Common;
#################################################################
#
# Common.pm : ME WAVE GW 共通処理モジュール  Ver 1.2
#
#     含まれるパッケージ
#          Common : 共通処理
#
# Copyright (C) 1999 NTT-ME Corporation
#
#################################################################
#
# Interface:
#
#     header($title)
#	Output HTML header and title
#
#     footer
#	Output HTML footer
#
#     error($error_code)
#	Output error page
#
#     jump
#	Output jump page
#
#################################################################

$TRUE	= 1;
$FALSE	= 0;

%HTML_TAGS = (
	'A',		'1:HREF/NAME',
	'ADDRESS',	'1',
	'B',		'1',
	'BLOCKQUOTE',	'1',
	'BR',		'0',
	'CITE',		'1',
	'CODE',		'1',
	'DD',		'0',
	'DIR',		'1',
	'DL',		'1:COMPACT',
	'DT',		'0',
	'EM',		'1',
	'FONT',		'1:SIZE/COLOR',
	'H1',		'1:ALIGN',
	'H2',		'1:ALIGN',
	'H3',		'1:ALIGN',
	'H4',		'1:ALIGN',
	'H5',		'1:ALIGN',
	'H6',		'1:ALIGN',
	'HR',		'0:SIZE/WIDTH/ALIGN',
	'I',		'1',
	'IMG',		'0:SRC/ALT/ALIGN/WIDTH/HEIGHT/BORDER',
	'KBD',		'1',
	'LI',		'0:TYPE/VALUE',
	'LISTING',	'1',
	'MARQUEE',	'1',
	'MENU',		'1',
	'OL',		'1:START',
	'P',		'0:ALIGN',
	'PRE',		'1',
	'S',		'1',
	'SAMP',		'1',
	'STRONG',	'1',
	'TT',		'1',
	'UL',		'1',
	'VAR',		'1',
	'XMP',		'1'
);

require 'jcode.pl';

%filesize;

#################################################################
# Constructor
#################################################################
sub new {
	my $self = {};

	bless($self);
	return $self;
}

#################################################################
# Destructor
#################################################################
sub DESTROY {
	my $tfile;
	foreach $tfile (keys %filesize) {
#		unlink "$main'TMPDIR/$tfile";
	}
}

#################################################################
# Output header
#################################################################
sub header {
	my $self = shift;

	if ($main'HEADER_OK) {
		return $main'FALSE;
	}
	#
	# タイトル行の取得
	#
	my $title	= shift;
	my $property	= shift;
	my $unload	= shift;

	#
	# ヘッダーの書き出し
	#
	print "Content-type: text/html\n\n";
	print "<HTML>\n";
	print "  <HEAD>\n";
	print "    <TITLE>\n";
	print "      $title\n";
	print "    </TITLE>\n";
	print "  </HEAD>\n";
	print "  <META HTTP-EQUIV='Content-type' CONTENT='text/html; charset=Shift_JIS'>\n";
	print "  <META HTTP-EQUIV='Content-Language' content='ja'>\n";
	print "  <META HTTP-EQUIV='Pragma' CONTENT='no-cache'>\n";
	print "  <BODY ";
	if ($property) {
		print $property;
	}
	else {
		print "BACKGROUND='$main'IMGDIR/wall.gif'";
	}
	print " OnUnload='$unload'" if ($unload);
	print ">\n";

	$main'HEADER_OK = $main'TRUE;
}

#################################################################
# Output footer
#################################################################
sub footer {
	my $self = shift;

	#
	# フッターの書き出し
	#
	print "  </BODY>\n";
	print "</HTML>\n";
}

#################################################################
# Output error
#################################################################
sub error {
	my $self = shift;
	my $err = 1;

	if (@_) {
		$err = shift;
	}
	my($mes);

	$self->header("エラー");
	print "<CENTER><BR><BR><TABLE><TR><TD ALIGN='center'>\n";
	print "<IMG SRC='$main'IMGDIR/warnbar.gif'><BR>\n";
	print "<BR>\n";
	print "<IMG SRC='$main'IMGDIR/warn.gif'><BR><BR>\n";
	print "<B>次のエラーが発生しました<BR>\n";
	print "<FONT COLOR=red>Error Code: $err</FONT><BR>\n";
	print "$main'MSG{$err}<BR>\n";
	print "</B><BR>\n";
	print "<IMG SRC='$main'IMGDIR/warnbar.gif'><BR>\n";
	print "</TD></TR></TABLE>\n";
	print "<BR>\n";
	print "<INPUT TYPE=button VALUE='　\戻る　\' OnClick='history.back();'>\n";
	print "</CENTER>\n";

	$self->footer;
	exit;
}

#################################################################
# Output jump tag
#################################################################
sub jump {
	my $self = shift;
	my $jump;

	#
	# Get jump page
	#
	if (@_) {
		$jump = shift;
		$self->header('jump', ' ');
#		if ($main'HEADER_OK) {
			print "<META HTTP-EQUIV='Refresh' CONTENT='0;URL=$jump'>\n";
			$self->footer;
#		}
#		else {
#			print "Status: 307 Temporary Redirect\n";
#			print "Location: $jump\n\n";
#		}
	}
}

#################################################################
# Read parse
#################################################################
sub read_parse {
	my $self = shift;
	local(*in) = shift;
	my $sPath = shift;
	my $sMode = 0777;
	my $sFileName = "";
	my $sFileNamet = "";
	my $FName, $name, $compare, $compareb, $len, $type;
	my $old, $key, $val, $aa, $buf, $wbuf;
	my $query;
	my $sType = $ENV{'CONTENT_TYPE'};
	binmode (STDIN);

	my ($mime, $boundary) = split(/;/, $sType);
	$boundary =~ s/^.+boundary=//;

	if ($sType =~ /boundary=/) {
		$old = <STDIN>;	# ----Add

		while(1) {
			if ($old =~ /--\r\n$/) {
				close IMG;
				chmod $sMode, $sFileName if (-e $sFileName);
				return;
			}
			$old = <STDIN>;
			if ($old =~ /--\r\n$/) {
				close IMG;
				chmod $sMode, $sFileName if (-e $sFileName);
				return;
			}

			while ($old !~ /filename/) {
				if ($old =~ /--\r\n$/) {
					close IMG;
					chmod $sMode, $sFileName if (-e $sFileName);
					return;
				}
				if ($old =~ /\bname=\"[a-zA-Z0-9]+\"/) {
					$old =~ s/\bname=\"([a-zA-Z0-9]+)\"/$1/;
					$key = $1;
					$aa = <STDIN>;
					$val = '';
					$old = <STDIN>;
					while ($old !~ /$boundary/) {
						$val .= $old;
						$old = <STDIN>;
						($old =~ /$boundary--\r\n$/) && last;	# ----Add
					}
					$val =~ s/\r\n$//;
					&jcode::h2z_sjis(*val);
					&jcode::convert(*val, 'sjis');
					$val =~ s/\r\n/\n/g;
					if ($in{$key}) {
						$in{$key} .= " , " . $val;
					}
					else {
						$in{$key} = $val;
					}
					if ($old =~ /--\r\n$/) {
						close IMG;
						chmod $sMode, $sFileName if (-e $sFileName);
						return;
					}
				}
				$old = <STDIN>;
			}
			if ($old =~ /filename/) {
				$old =~ s/filename\=\"(.*)\"/$1/;
				$FName = $1;
				$old =~ s/name\=\"(.*)\"\;/$1/;
				$name = $1;
				$in{$name} = $FName;
				$sFileName = "$main'TMPDIR/$name";
				$sFileNamet= $name;

				open (IMG, ">$sFileName");
				binmode IMG;
				$filesize{$sFileNamet} = 0;
				if ($old =~ /--\r\n$/) {
					close IMG;
					chmod $sMode, $sFileName if (-e $sFileName);
					return;
				}

				$type = <STDIN>;
				$type =~ s/Content-Type\: (.*)\//$1/;
				$type = $1;
				my $tYpeFileName = $sFileName . '.type';
				chmod $sMode, $tYpeFileName;
				if ($old =~ /--\r\n$/) {
					close IMG;
					chmod $sMode, $sFileName if (-e $sFileName);
					return;
				}

				$old = <STDIN>;
				if ($old =~ /--\r\n$/) {
					close IMG;
					chmod $sMode, $sFileName if (-e $sFileName);
					return;
				}
				$compare = $boundary;
				$compare =~ s/^-*//;
				$len = (length($compare)-1);
				while (read(STDIN,$buf,1) == 1) {
					if (eof(STDIN)) {
						last;
					}
					if ($buf =~ /^-/) {
						while(read(STDIN,$wbuf,1) == 1) {
							if (eof(STDIN)) {
								last;
							}
							$buf = $buf . $wbuf;
							if ($wbuf !~ /^-/) {
								$compareb = $wbuf;
								last;
							}
						}
						if (read(STDIN,$wbuf,$len) == $len) {
							$compareb .= $wbuf;
							if ($compareb =~ /^$compare/) {
								last;
							}
							$buf .= $wbuf;
						}
						else {
							last;
						}
					}
					$filesize{$sFileNamet} += length($buf);
					print IMG $buf;
				}
				if ( (eof(STDIN)) or ($compareb =~ /^$compare/) ) {
					read(STDIN, $wbuf, 1);
					if ($wbuf =~ /^-/) {
						close(IMG);
						chmod $sMode, $sFileName if (-e $sFileName);
						last;
					}
					read(STDIN, $wbuf, 1);
				}
			} # end the if

		} # end the last while
	}
	local($strInputWay) = $ENV{'REQUEST_METHOD'};
	if ($strInputWay eq 'GET'|| $strInputWay eq 'HEAD') {
		$query = $ENV{'QUERY_STRING'};
	}
	elsif ($strInputWay eq 'POST') {
		read(STDIN, $query, $ENV{'CONTENT_LENGTH'});
	}
	else {
		return $FALSE;
	}
	local(@query) = split /&/, $query;
	foreach (@query) {
		tr/+/ /;
		($key, $val) = split(/=/);
		$key =~ s/%([A-Fa-f0-9][A-Fa-f0-9])/pack("c", hex($1))/ge;
		$val =~ s/%([A-Fa-f0-9][A-Fa-f0-9])/pack("c", hex($1))/ge;

		$val =~ s/\r\n/\n/g;

		&jcode::h2z_sjis(*val);
		&jcode::convert(*val, 'sjis');

		if ($in{$key}) {
			$in{$key} .= " , " . $val;
		}
		else {
			$in{$key} = $val;
		}
	}
	return;
}

#################################################################
# Get form file size
#################################################################
sub form_file_size {
	my $self = shift;
	my $name = shift;

	return $filesize{$name};
}

#################################################################
# Copy form file
#################################################################
sub copy_form_file {
	my $self = shift;
	my $name = shift;
	my $cpto = shift;

	if ($filesize{$name}) {
		unlink $cpto;
		return rename "$main'TMPDIR/$name", $cpto;
	}
	else {
		return $FALSE;
	}
}

#################################################################
# Delete all file of directory
#################################################################
sub rmdir {
	my $self = shift;
	my $rdir = shift;
	my $depth = shift;
	my $file;

	eval("opendir(BBSDIR".$depth.', $rdir)');
	eval("readdir(BBSDIR".$depth.")");
	eval("readdir(BBSDIR".$depth.")");
	while ($file = eval("readdir(BBSDIR".$depth.")")) {
		if (-d "$rdir/$file") {
			$self->rmdir("$rdir/$file", $depth+1) or return $FALSE;
		}
		else {
			unlink "$rdir/$file" or return $FALSE;
		}
	}
	eval("close BBSDIR".$depth.")");
	rmdir "$rdir";

	return $TRUE;
}

#################################################################
# Replace dangerous tag
#################################################################
sub tag {
	my $self	= shift;
	my $string	= shift;
	my $notag	= shift;

	my $key, $value;
	my @property;
	my $property;
	my $sand;

	$string =~ s/</&lt/g;
	$string =~ s/>/&gt/g;

	unless ($notag) {
		foreach $key (keys %HTML_TAGS) {
			$string =~ s/&lt($key)&gt/<$1>/gi;
			($sand, $property) = split /:/, $HTML_TAGS{$key};
			if ($property) {
				@property = split /\//, $property;
				if ($sand) {
					while ($string =~ /&lt$key\s+(.*)&gt.*&lt\/$key&gt/i) {
						$string =~ s/&lt($key\s+(?:.*))&gt(.*)&lt(\/$key)&gt/<$1>$2<$3>/i;
					}
				}
				while ($string =~ /&lt$key\s+(.*)&gt/i) {
					$string =~ s/&lt($key\s+(.*))&gt/<$1>/i;
				}
			}
		}
	}
	return $string;
}

#################################################################
# successfully return
#################################################################
1;

#################################################################
# end of Common.pm
#################################################################
