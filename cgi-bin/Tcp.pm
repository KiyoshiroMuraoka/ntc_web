package Tcp;
#################################################################
#
# Tcp.pm : ME WAVE GW Tcp処理モジュール
#
#     含まれるパッケージ
#          Tcp : Create TCP connection etc.
#
# Copyright (C) 1999 NTT-ME Corporation
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

require Exporter;
use Socket;
@ISA= qw(Exporter);

# Symbol which this export as default
@EXPORT = qw(TcpConnect TcpListen);

#################################################################
# Constructor
#################################################################
sub new {
	my $self = {};

	$self->{ERROR}	= '';

	bless($self);
	return $self;
}

#################################################################
# Destructor
#################################################################
sub DESTROY {
}

#################################################################
# Set and get error code
#################################################################
sub error {
	my $self = shift;

	#
	# Set error code
	#
	if (@_) {
		$self->{ERROR} = shift;
	}

	return $self->{ERROR};
}

#################################################################
# Open socket and connect
#################################################################
sub TcpConnect {
	my $self = shift;

	my ($socket, $host, $port, $sockopt) = @_;
	my ($ipaddr, $result, $pack);
	
	$result = $TRUE;
	$pack = caller;
	$socket = $pack . '::' . $socket;
	$port = getservbyname($port,'tcp') if ( $port =~ /\D/ );
	$common->error($main'ERR_PORT_NOT_FOUND) unless ($port);
	$ipaddr = inet_aton($host) or $common->error($main'ERR_HOST_NOT_FOUND);
	unless(socket($socket, PF_INET, SOCK_STREAM, getprotobyname('tcp') )) {
		$self->{ERROR} = $main'ERR_CANT_OPEN_SOCKET;
		return $FALSE;
	}
	USESOCKET:
	{
		if ($sockopt ne '') {
			unless(setsockopt($socket, SOL_SOCKET, $sockopt, 1)) {
				$self->{ERROR} = $main'ERR_CANT_SET_SOCK_OPT;
				$result = $FALSE;
				last;
			}
		}
		unless (connect($socket, sockaddr_in($port, $ipaddr))) {
			$self->{ERROR} = $main'ERR_FAILED_TO_CONNECT;
			$result = $FALSE;
			last;
		}
	}
	# end USESOCKET:
	close($socket) unless( $result );
	$result;
}

#
# Listen port
#
sub TcpListen {
	my $self = shift;

	my ($socket, $port, $ipaddr, $queuesize, $sockopt) = @_;
	my ($result, $pack);
	
	$result = $TRUE;
	$pack = caller;
	$socket = $pack . '::' . $socket;
	$ipaddr = $ipaddr || INADDR_ANY;
	$queuesize = $queuesize || SOMAXCONN;
	unless(socket($socket, PF_INET, SOCK_STREAM, getprotobyname('tcp') )) {
		$self->{ERROR} = $main'ERR_CANT_OPEN_SOCKET;
		return $FALSE;
	}
	USESOCKET:
	{
		if ( $sockopt ne '' ) {
			unless(setsockopt($socket, SOL_SOCKET, $sockopt, 1)) {
				$self->{ERROR} = $main'ERR_CANT_SET_SOCK_OPT;
				$result = $FALSE;
				last;
			}
		}
		unless (bind($socket, sockaddr_in($port, $ipaddr))) {
			$self->{ERROR} = $main'ERR_CANT_BIND_SOCKET;
			$result = $FALSE;
			last;
		}
		unless (listen($socket,$queuesize)) {
			$self->{ERROR} = $main'ERR_FAILED_TO_LISTEN;
			$result = $FALSE;
			last;
		}
	}
	# end USESOCKET:
	close($socket) unless( $result );
	$result;
}

#################################################################
# successfully return
#################################################################
1;

#################################################################
# end of Tcp.pm
#################################################################
