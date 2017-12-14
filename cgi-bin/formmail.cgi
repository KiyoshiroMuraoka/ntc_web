#!/usr/bin/perl --
# �t�H�[�����[���V�X�e�� Version 3.2
# Copyright 1999 ���� ���u <kmuto@isoternet.org>
#
# �K�v�ȃ��W���[��: CGI.pm, Socket.pm, jcode.pl, base64.pl
# modified by m.sakurai 2001
# Version 3.2.b

# Perl�����R�[�h (Windows:sjis UNIX:euc)
$charcode = 'sjis';

# �ݒ�t�@�C���̂���f�B���N�g��
$filebase = "mailtmps";
#$mailcmd='/usr/bin/sendmail';
$mailcmd='/usr/sbin/sendmail';

# �댯�Ȗ��߂̋֎~ (0:���� 1:�֎~)
$warning = 1;

# �f�t�H���g�l
$param{'sendokname'} = "ok";
$param{'subject'} = "Form Mail System";
$param{'to'} = "postmaster";
$param{'from'} = "nobody";
$param{'fromhost'} = "localhost";
$param{'tohost'} = "localhost";

# CGI���W���[���g�ݍ���
use CGI;
# �\�P�b�g���W���[���g�ݍ���
use Socket;

# ���{��ϊ�
require 'jcode.pl';
&jcode::init();

# base64
require 'base64.pl';

# CGI�̃p�����[�^�Ŏ������T�u�f�B���N�g��
$dirname = "";

# CGI���͎�荞��
$query = new CGI;

# �G���[���b�Z�[�W������
$errorstring = "";

# �������g��URL
$myurl = $query->url;

# CGI�p�����[�^�l�i�[
undef %CGIvalue;
# �ݒ�t�@�C���p�����[�^
undef %param;
# ���������p�����[�^
undef %limit;
# �G���[���b�Z�[�W�p�����[�^
undef %errormessage;
# �v�Z�p�����[�^
undef %calceval;

# ���b�N�f�B���N�g��
$lockdir = "lock";

# ���g���C��
$retry = 5;

# CGIvalue ��CGI�p�����[�^�l���i�[���A�����R�[�h�ɓ���
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

# �ݒ�t�@�C���ǂݍ���
&loadconf;

# CGI���[�h�ɂ���ĕ\���y�[�W�I��
if ($query->param('CGImode') eq '1') {
  # �l�`�F�b�N
  if (&valuecheck ne '') {
    # �G���[������
    if ($param{'errorfile'} ne '') {
      # �G���[��ʂ�����Ȃ�\��
      &errorload();
    } else {
      # �K�v�łȂ��Ȃ�t�H�[����ʂɕ\��
      &formload();
    }
  } else {
    # ���M�t�H�[������͂����Ƃ�
    if ($param{'commitfile'} ne '') {
      # �m�F��ʂ��K�v�Ȃ�\��
      &commitload();
    } else {
      # �K�v�łȂ��Ȃ瑗�M
      &sendandthanks();
    }
  }
} elsif ($query->param('CGImode') eq '2') {
  # �m�F��ʂ���͂����Ƃ�
  if ($query->param($param{'sendokname'})) {
    # ���M����
    &sendandthanks();
  } else {
    &error("���M�ł��܂���ł���");
    #&formload();
  }
} else {
  # �܂��I������Ă��Ȃ����G���[����͂����Ƃ�
  &formload();
}

sub formload {
  # �t�H�[�����͉�ʓǂݍ���
  if (!open(F, "$filebase/$dirname/" . $param{'formfile'})) {
    &error("formfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }

  # HTML�w�b�_
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 1);
  }
  close(F);
}

sub commitload {
  # �m�F��ʓǂݍ���
  if (!open(F, "$filebase/$dirname/" . $param{'commitfile'})) {
    &error("formfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }

  # HTML�w�b�_
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 2);
  }
  close(F);
}

sub errorload {
  # �G���[��ʓǂݍ���
  if (!open(F, "$filebase/$dirname/" . $param{'errorfile'})) {
    &error("errorfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }

  # HTML�w�b�_
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 3);
  }
  close(F);
}

sub sendandthanks {
  # ���[�����M 
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
  # ���M�����Ӊ�ʓǂݍ���
  if (!open(F, "$filebase/$dirname/" . $param{'thanksfile'})) {
    &error("thanksfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }

  # HTML�w�b�_
  print "Content-type: text/html\n\n";
  while (<F>) {
    print &fileparse($_, 4);
  }
  close(F);

}

sub valuecheck {
  # �ϐ��̒l�𐧌������Ȃǂ���Ƀ`�F�b�N
  $errorstring = "";
  # �K�{���ڃ`�F�b�N
  local(@require) = split(/[ \t,]+/, $param{'require'});
  for (@require) {
    if (!$CGIvalue{$_}) {
      # �K�{�ɓ����Ă��Ȃ�
      $errorstring .= $errormessage{$_} . "<br>\n";
    }
  }
  foreach (keys %limit) {
    # ���������`�F�b�N
    local($key) = $_;
    local($flag) = 0;
    local(@list) = split(/,/, $limit{$key});
    for (@list) {
      if (/^([\d]+)\-([\d]+)$/) {
    # ���l�͈�
    local($from) = $1;
    local($to) = $2;
    if ($CGIvalue{$key} !~ /^\d+$/) {
      # ���l�ł͂Ȃ��̂Ŏ��s
      next;
    }
    if ($from <= $CGIvalue{$key} && $CGIvalue{$key} <= $to) {
      # �͈͂ɊY��
      $flag = 1;
      last;
    }
      } elsif ($_ eq 'MAILADDRESS') {
    # ���[���A�h���X�`��
    if ($CGIvalue{$key} =~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/) {
      # ���[���A�h���X�Ɏ������̂ɊY��
      $flag = 1;
      last;
    }
      } else {
    # ���S��v
    if ($_ eq $CGIvalue{$key}) {
      $flag = 1;
      last;
    }
      }
    }
    if ($flag == 0) {
      # �}�b�`���Ȃ�����
      $errorstring .= $errormessage{$key} . "<br>\n";
    }
  }
  foreach (keys % calceval) {
    # ���I�v�Z
    local($key) = $_;
    local($value) = $calceval{$key};

    while ($value =~ /<!--([^>]+)-->/go) {
      local($value2) = $1;
      local($pre) = $`;
      local($post) = $';
      if ($CGIvalue{$value2}) {
    local($value3) = $CGIvalue{$value2};
    # @��$�Ȃǂ�ޔ�
    $value3 =~ s/\@/\\\@/go;
    $value3 =~ s/\$/\\\$/go;
    $value3 =~ s/\%/\\\%/go;
    $value3 =~ s/\&/\\\&/go;
    $value = $pre . $value3 . $post;
      } else {
    &error("$value2������`�ł��B");
      }
    }

    # �댯�Ȋ֐��̃`�F�b�N
    if ($warning) {
      if ($value =~ /open/ || $value =~ /unlink/ || $value =~ /symlink/ || $value =~ /socket/ || $value =~ /mkdir/ || $value =~ /rmdir/ || $value =~ /system/ || $value =~ /exec/) {
    &error("������Ă��Ȃ��v�Z�����񂪊܂܂�Ă��܂��B");
      }
    }

    local($result) = '';
    if ($value =~ /\$result[ \t]*=/) {
      # ���G�Ȏ�
      eval("$value");
    } else {
      # �ȒP�Ȏ�
      eval("\$result = $value");
    }
    &error("���̕]�����ɃG���[���������܂����B:$@") if ($@);
    $CGIvalue{$key} = $result;
  }

 # �X�̎g�p�ɓ�����������
    # ���[���A�h���X��v
    if($CGIvalue{'email'} ne $CGIvalue{'confirm_email'}){
        $errorstring .= $errormessage{'confirm_email'} . "<br>\n";
}
    
    

    $errorstring;
}

sub fileparse {
  # �t�@�C�����e����͂��A�ϊ����s���AJIS�ɂ��Ė߂�
  local($line, $type) = @_;
  # �����R�[�h�֕ϊ�
  $line = jcode::to($charcode, $line);
  # �ċA�p�[�X���s
  $line = &recursiveparse($line, $type);
  # JIS�֕ϊ�
  #$line = jcode::to('jis', $line);
  $line;
}


sub recursiveparse {
  # �ċA�Ńp�[�V���O
  local($value, $type) = @_;
  local($head) = "";
  local($tail) = "";
  if ($value =~ /<!--([^>]+)-->/go) {
    $command = $1;
    $head = $`;
    $tail = $';
    if ($command =~ /^MYSELF$/i) {
      # ���g��URL
      $value = $myurl;
    } elsif ($command =~ /^DATE$/i) {
      # ���t
      local($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime(time);
      $value = sprintf("%d�N%d��%d�� %02d:%02d:%02d", $year + 1900, ++$mon, $mday, $hour, $min, $sec);
    } elsif ($command =~ /^REMOTE_HOST$/i) {
      # ����z�X�g��
      $value = $query->remote_host();
    } elsif ($command =~ /^USER_AGENT$/i) {
      # ����u���E�U��
      $value = $query->user_agent();
    } elsif ($command =~ /^VALUE/i) {
      # �����ϐ��l
      local($tmp, $name, $val) = split(/:/, $command, 3);
      if ($CGIvalue{$name} ne '') {
        # ���łɒl�������Ă���Ƃ��ɂ͂�����g��
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
      # �t�@�C���C���N���[�h
      local($filename) = $1;
      if (!open(F2, "$filebase/$dirname/$filename")) {
        $value = "<font color=\"#ff0000\">$filename �͊J���܂���B:$!</font><br>\n";
      } else {
        $value = "";
        while (<F2>) {
          $_ = jcode::to($charcode, $_);
          $value .= $_;
        }
        close(F2);
      }
    } elsif ($command =~ /^SELECT/i || $command =~ /^CHOICE/i) {
      # �I��
      local($tmp, $name, $val, $checked) = split(/:/, $command, 4);
      if ($query->param('CGImode') eq '1' || $query->param('CGImode') eq '4' ||
           ($query->param('CGImode') eq '2' &&
               !$query->param($param{'sendokname'})) || $query->param('CGImode') eq '3') {
        # �m�F��ʂ���͂����Ƃ�
        # ���͂�؂蕪����
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
        # �f�t�H���g�l�Ɋ�I��
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
      # �G���[���b�Z�[�W
      $value = $errorstring;
    } elsif ($command =~ /^INFO$/i) {
      # Hidden������
      $value = "<input type=\"hidden\" name=\"CGImode\" value=\"$type\">\n";
      # �y�[�W�`���ɂ���đI��
      if ($type == 1) {
        # �t�H�[������
        $value .= "<input type=\"hidden\" name=\"keywords\" value=\"$dirname\">";
      } elsif ($type == 2 || $type == 3) {
        # �m�F�܂��̓G���[�̂Ƃ��ɂ�hidden�œ]��
        foreach (keys %CGIvalue) {
          if ($_ eq 'CGImode') {
            # CGImode�ϐ��͔�΂�
            next;
          }
          # ���̂܂ܓ]��
	  $val=$CGIvalue{$_};
          $val=~ s/"/'/g;
	  $value .= "<input type=\"hidden\" name=\"$_\" value=\"" . $val . "\">\n";
        }
      } elsif ($type == 5) {
        # ���[�����M���e
        $value = "";
        foreach (keys %CGIvalue) {
          if ($_ eq 'CGImode') {
            # CGImode�ϐ��͔�΂�
            next;
          }
          # ���̂܂ܓ]��
          $value .= "$_:" . $CGIvalue{$_} . "\n";
        }
      }
    }
    # �܂��p�[�X���Ă��Ȃ��\��������̂ŁA�ċA
    $tail = &recursiveparse($tail);
  }
  # ���ʂ��o��
  $head . $value . $tail;
}

sub loadconf {
  # �ݒ�t�@�C���ǂݍ���
  $dirname = $query_string;
  if (/^keywords=/) {
    $dirname =~ s/keywords=//;
  } else {
    $dirname = $CGIvalue{'keywords'};
  }
  if (!open(F, "$filebase/$dirname/config")) {
    &error("������QUERY���ł��B");
  }
  while (<F>) {
    chomp;
    if (/^\#/ || /^[\t ]*$/) {
      # �R�����g�͔�΂�
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
      # �T�u�W�F�N�g
      local($b64value) = jcode::to('jis', $value);
      $b64value =~ s/(\x1b\x24\x42[^\x1b]*\x1b\x28[\x42\x4a])/"=?ISO-2022-JP?B?" . base64::b64encode($1) . "?="/goe;
      $b64value =~ tr/\r\n//d;
      $param{$key} = $b64value;
    } elsif ($key eq 'limit') {
      # ��������
      local($name, $val) = split(/:/, $value, 2);
      $limit{$name} = $val;
    } elsif ($key eq 'error') {
      # �G���[���b�Z�[�W
      local($name, $val) = split(/:/, $value, 2);
      $errormessage{$name} = $val;
    } elsif ($key eq 'eval') {
      # ���]��
      local($name, $val) = split(/:/, $value, 2);
      $calceval{$name} = $val;
    } else {
      &error("���m�̃L�[ $key ������܂��B");
    }
  }
  close(F);

  if($param{'sendsw'} eq '0'){
    &error("sendsw �G���g��=0�͎g�p���Ȃ��ł��������B");
  }
  if ($param{'to'} eq '') {
    &error("to �G���g�����ݒ�t�@�C�����ɂ���܂���B");
  }
  if ($param{'from'} eq '') {
    &error("from �G���g�����ݒ�t�@�C�����ɂ���܂���B");
  }
  local(@sendfiles) = split(/[ \t]*,[ \t]*/, $param{'sendfile'});
  foreach (@sendfiles) {
    if (!-f "$filebase/$dirname/" . $_) {
      &error("sendfile �G���g���Ŏ������t�@�C�����J���܂���B");
    }
  }
  local(@tos) = split(/[ \t]*,[ \t]*/, $param{'to'});
  local(@froms) = split(/[ \t]*,[ \t]*/, $param{'from'});
  local(@subjects) = split(/[ \t]*,[ \t]*/, $param{'subject'});
  if (@tos != @sendfiles) {
    &error("���M��ƃe���v���[�g�̐��������Ă��܂���B");
  }

  if (@tos != @froms) {
    &error("���M��Ƒ��M���̐��������Ă��܂���B");
  }

  #if(@tos != @subjects){
  #    &error("���M��ƃT�u�W�F�N�g�̐��������Ă��܂���B");
  #}
  if (!-f "$filebase/$dirname/" . $param{'formfile'}) {
    &error("formfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }
  if ($param{'errorfile'} ne '' &&
      !-f "$filebase/$dirname/" . $param{'errorfile'}) {
    &error("errorfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }
  if ($param{'commitfile'} ne '' &&
      !-f "$filebase/$dirname/" . $param{'commitfile'}) {
    &error("commitfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }
  if (!-f "$filebase/$dirname/" . $param{'thanksfile'}) {
    &error("thanksfile �G���g���Ŏ������t�@�C�����J���܂���B");
  }
  # �����̃`�F�b�N
  if($param{'sendsw'}==3 || $param{'sendsw'}==2){
    local(@sendfiles2) = split(/[ \t]*,[ \t]*/, $param{'sendfile2'});
    local($sendfile2) = shift(@sendfiles2);
    if (!-f "$filebase/$dirname/" . $sendfile2) {
      &error("sendfile2 �G���g���Ŏ������t�@�C�����J���܂���B");
    }
    #�f�[�^�x�[�X�t�@�C��
    if (!exists($param{'csvfile'})) {
      &error("csvfile �G���g�����ݒ�t�@�C�����ɂ���܂���B");
    }
  }
}

sub mailsend {
  # ���[�����M
  # �\�P�b�g�쐬

  # ���Ifrom�̃`�F�b�N
  if ($param{'from'} =~ /<!--([^>]+)-->/) {
    local($value) = $1;
    if ($CGIvalue{$value}) {
      $param{'from'} = $CGIvalue{$value};
    } else {
      &error("���I���M���A�h���X$value����`����Ă��܂���B");
    }
  }
  if ( $param{'from'} !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/ ) {
    &error("�s���ȑ��M���A�h���X�ł��B:" . $param{'from'});
  }

  # ���Ito�̃`�F�b�N
  # �����̃`�F�b�N
  local(@tos) = split(/[ \t]*,[ \t]*/, $param{'to'});
  local(@sendfiles) = split(/[ \t]*,[ \t]*/, $param{'sendfile'});
  if (@tos != @sendfiles) {
    &error("���M��ƃe���v���[�g�̐��������Ă��܂���B");
  }

  for (local($i) == 0; $i < @tos; $i++) {
    local($to) = $tos[$i];
    if ($to =~ /<!--([^>]+)-->/) {
      local($value) = $1;
      if ($CGIvalue{$value}) {
    $tos[$i] = $CGIvalue{$value};
      } else {
    &error("���I���M��A�h���X$value����`����Ă��܂���B");
      }
    }
    if ( $tos[$i] !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/) {
      &error("�s���ȑ��M��A�h���X�ł��B:" . $tos[$i]);
    }
  }

  local($local_address) = (gethostbyname($param{'fromhost'}))[4];
  local($local_socket_address) = pack('S n a4 x8', AF_INET, 0, $local_address);
  local($server_address) = (gethostbyname($param{'tohost'}))[4];
  local($server_socket_address) = pack('S n a4 x8', AF_INET, 25, $server_address);
  local($protocol) = (getprotobyname('tcp'))[2];
  local($result) = '';
  
  if (!socket(SMTP, AF_INET, SOCK_STREAM, $protocol)) {
    &error("�\�P�b�g�G���[���������܂����B:$!");
  }
  if (!bind(SMTP, $local_socket_address)) {
    &error("�o�C���h�G���[���������܂����B:$!");
  }
  if (!connect(SMTP, $server_socket_address)) {
    &error("�ڑ��G���[���������܂����B:$!");
  }
  # �o�b�t�@�����O���Ȃ�
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
    # ���[�����e�Y�t
    if (!open(F, "$filebase/$dirname/" . $sendfile)) {
      &error("sendfile �G���g���Ŏ������t�@�C�����J���܂���B");
    }
    while (<F>) {
      print SMTP jcode::to('jis', &fileparse($_, 5));
    }
    close(F);
    print SMTP ".\n";
    $result = <SMTP>;
    # �E�F�C�g������
    sleep(2);
  }
  print SMTP "QUIT\n";
}

sub mailsend2 {
  # ���[�����M

  # ���Ifrom�̃`�F�b�N
  local(@froms) = split(/[ \t]*,[ \t]*/, $param{'from'});
  for (local($j) == 0; $j < @froms; $j++) {
    local($from) = $froms[$j];
    if ($from =~ /<!--([^>]+)-->/) {
      local($value) = $1;
      if ($CGIvalue{$value}) {
        $froms[$j] = $CGIvalue{$value};
      } else {
        &error("���I���M���A�h���X$value����`����Ă��܂���B");
      }
    }
    if ( $froms[$j] !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/ ) {
      &error("�s���ȑ��M���A�h���X�ł��B:" . $froms[$j]);
    }
  }

  # ���Ito�̃`�F�b�N
  # �����̃`�F�b�N
  local(@tos) = split(/[ \t]*,[ \t]*/, $param{'to'});
  local(@sendfiles) = split(/[ \t]*,[ \t]*/, $param{'sendfile'});
  local(@subjects) = split(/[ \t]*,[ \t]*/, $param{'subject'});

  if (@tos != @sendfiles) {
    &error("���M��ƃe���v���[�g�̐��������Ă��܂���B");
  }

  if (@tos != @froms) {
    &error("���M��Ƒ��M���̐��������Ă��܂���B");
  }

  #if(@tos != @subjects){
  #    &error("���M��ƃT�u�W�F�N�g�̐��������Ă��܂���B");
  #}
  for (local($i) == 0; $i < @tos; $i++) {
    local($to) = $tos[$i];
    if ($to =~ /<!--([^>]+)-->/) {
      local($value) = $1;
      if ($CGIvalue{$value}) {
    $tos[$i] = $CGIvalue{$value};
      } else {
    &error("���I���M��A�h���X$value����`����Ă��܂���B");
      }
    }
    if ( $tos[$i] !~ /^[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:_]+\@[\.a-zA-Z0-9!\#\$\%\&~=\-\^\*\+;:\[\]_]+/) {
      &error("�s���ȑ��M��A�h���X�ł��B:" . $tos[$i]);
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
          &error("�I�����������s���Ă���܂���B");
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
    # ���[�����e�Y�t
    if (!open(F, "$filebase/$dirname/" . $sendfile)) {
      &error("sendfile �G���g���Ŏ������t�@�C�����J���܂���B");
    }
    while (<F>) {
        print SMTP jcode::to('jis', &fileparse($_, 5));
    }
    close(F);
    close(SMTP);

    # �E�F�C�g������
    #sleep(2);
  }
}

sub mailsend3 {

  # �����̃`�F�b�N
  local(@sendfiles2) = split(/[ \t]*,[ \t]*/, $param{'sendfile2'});
  local($sendfile2) = shift(@sendfiles2);

  #�f�[�^�x�[�X�t�@�C��
  local(@CSVS) = split(/[ \t]*,[ \t]*/, $param{'csvfile'});
  local($CSV) = shift(@CSVS);

  while(!mkdir($lockdir, 0755)) {                       # �f�B���N�g�����쐬�ł��Ȃ���Α҂�
    if(--$retry <= 0) { &error("���ݍ����Ă��܂��B"); } # ���g���C�񐔕����s���Ă��_���Ȃ炠����߂�
    #sleep(1);                                           # 1�b�҂�
  }

  open(DATA, ">>$filebase/$dirname/$CSV");
  #flock(DATA, 2);
  seek(DATA, 0, SEEK_END);
  
  # ���[�����e�Y�t
  if (!open(F, "$filebase/$dirname/" . $sendfile2)) {
     &error("sendfile �G���g���Ŏ������t�@�C�����J���܂���B");
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
  rmdir($lockdir);                                      # �f�B���N�g�����폜����

  # �E�F�C�g������
  #sleep(2);
  return ($ret);
}

sub error {
  # �G���[
  local($message) = @_;
  print "Content-type: text/html\n\n";
  print "$message\n";
  exit 1;
}


