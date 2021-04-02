#
# Irssi script for trd
#

# changelog:
# 20210214 - 0.4 - pick up own commands in data channel
# 20200527 - 0.3 - socket connect non blocking, handle 'VERSION' JSON instead of 'HELLO' string on startup

use strict;
use Irssi;
use Irssi::Irc;
use IO::Socket;
use JSON;
use Switch;

use vars qw($VERSION %IRSSI);

our $VERSION = '0.4';
our %IRSSI =
(
	authors     => "sark",
	contact     => "/QUIT",
	name        => "trd client for trd 0.9.17+",
	description => "it is a client for trd",
	license     => "GNU GPL",
	url         => "no thanks",
);

# settings
my $TRD_HOST = "127.0.0.1"; # "127.0.0.1";
my $TRD_PORT = "10000";
my $TRD_WINDOW = "\@trd";
my $TRD_DEBUG = 0;
my $TRD_AUTOCONNECT = 1;
my $TRD_ONLOAD_OPEN_WINDOWS = 1;

# irc 
my $TRD_DATA_CHANNEL = "#yourdatachannel";
my $TRD_DATA_CHANNEL_SERVER = undef;
my $TRD_FISH_PREFIX = "> ";
my @TRD_IGNORE_CHANNELS = ( "#channel1", "#channel2" );

# udp
my $FTP_HOST = "127.00.1";
my $FTP_PORT = 12345;
my $FTP_PASS = "password";

# globals
my $TRD_CONNECTION = undef;
my $TRD_AUTOCONNECT_INTERVAL = 60;   # seconds
my %TRD_LATEST = ();

#  tags (identifiers) for irssi callbacks
my $TRD_INPUT_TAG = undef;
my $TRD_AUTOCONNECT_TAG = undef;

### low level i/o routines
sub socket_connect
{
    my $host = shift;
    my $port = shift;
 
    return IO::Socket::INET->new( Proto => 'tcp', PeerPort => $port, PeerAddr => $host, Timeout => 3, Blocking => 0 );
}

sub socket_write
{
    my $s = shift;
    my $line = shift;

    print $s $line . "\n";
}

sub socket_read
{
    my $s = shift(@_);
    return <$s>;
}

### udp
sub udp_send
{
    my ($host, $port, $data) = @_;

    my $udp_sock = new IO::Socket::INET( PeerPort => $port, Proto => 'udp', PeerAddr => $host );

    $udp_sock->send($data);
}

### trd
sub trd_get_data_channel_server 
{
    if (!defined $TRD_DATA_CHANNEL_SERVER) 
    { 
        foreach my $server ( Irssi::servers() ) {
            my $channel = $server->channel_find($TRD_DATA_CHANNEL);
            if ( defined($channel) )
            {
                trd_debug("$TRD_DATA_CHANNEL channel found on $server->{tag}");
                return $channel;
            }
            else
            {
                trd_debug("$TRD_DATA_CHANNEL channel not found on $server->{tag}");
            }
        }

        $TRD_DATA_CHANNEL_SERVER = undef;
    }
    else 
    {
        trd_debug($TRD_DATA_CHANNEL_SERVER->{tag});
        return $TRD_DATA_CHANNEL_SERVER;
    }
}

sub trd_cancel_autoconnect
{
    if (defined $TRD_AUTOCONNECT_TAG)
    {
        Irssi::timeout_remove($TRD_AUTOCONNECT_TAG);
    }
}

sub trd_trade
{
    my ($cmd, $tag, $release, $chain) = @_;

    my $udp_data = $FTP_PASS . " " . $cmd . " ". $tag . " " . $release . " " . $chain;

    udp_send( $FTP_HOST, $FTP_PORT, $udp_data );
}

sub trd_add_latest
{
    my ($tag, $release, $chain) = @_;

    $TRD_LATEST{ $tag }{ release } = $release; 
    $TRD_LATEST{ $tag }{ chain } = $chain;
}

sub trd_print_latest
{
    if (%TRD_LATEST) {

        trd_print("\00305LATEST\003");

        while ( my ( $tag, $release ) = each(%TRD_LATEST) ) {
             trd_print("$tag - $release->{release} - $release->{chain}");
        }
    }
}

sub trd_clear_latest
{
    %TRD_LATEST = ();
}

sub trd_trade_latest
{
    my ($tag) = @_;

    if (!$tag || !$TRD_LATEST{$tag}->{release}) { return; }

    trd_print("TRADING :: " . $tag . " - \002" . $TRD_LATEST{$tag}->{release} . "\002 - " . $TRD_LATEST{$tag}->{chain});

    trd_trade("race", $tag, $TRD_LATEST{$tag}->{release}, $TRD_LATEST{$tag}->{chain});
}

# irssi callback, data is available on socket
sub trd_read($$)
{
    my $window = Irssi::window_find_name($TRD_WINDOW);

    trd_debug($TRD_CONNECTION);

    my $strMessage = socket_read($TRD_CONNECTION);

#    trd_debug($strMessage);

    chomp($strMessage);

    # connection closed server from server side
    if (length($strMessage) == 0)
    {
        trd_post_disconnect();

        trd_print("\00304OFFLINE\003: Unable to contact the system at the moment.");

        if ($TRD_AUTOCONNECT)
        {
            $TRD_AUTOCONNECT_TAG = Irssi::timeout_add_once($TRD_AUTOCONNECT_INTERVAL * 1000, \&trd_connect, undef);
        }

        return;
    }

#    $strMessage =~ s/\r//g;

#    trd_debug($strMessage);

    my $json = JSON->new->allow_nonref;
    my $jsonMessage = $json->decode( $strMessage );
    my $command = $jsonMessage->{'command'};

#    trd_debug($command);

    if ($command eq "VERSION")
    {
	trd_print("\00309CONNECTED - " . $jsonMessage->{'version'} . "\003");
    }
    elsif ($command eq "APPROVED")
    {
        trd_trade( "race", $jsonMessage->{'tag'}, $jsonMessage->{'rlsname'}, join(",", @{ $jsonMessage->{'chain'} }) );
        trd_print("\00311APPROVED\003 :: " . $jsonMessage->{'tag'} . " - \002" . $jsonMessage->{'rlsname'} . "\002 - " . join(",", @{ $jsonMessage->{'chain'} }));
    }
    elsif ($command eq "TRADE")
    {
        trd_trade( "prepare", $jsonMessage->{'tag'}, $jsonMessage->{'rlsname'}, join(",", @{ $jsonMessage->{'chain'} }) );
        trd_print("TRADE :: " . $jsonMessage->{'tag'} . " - \002" . $jsonMessage->{'rlsname'} . "\002 - " . join(",", @{ $jsonMessage->{'chain'} }));
        trd_add_latest($jsonMessage->{'tag'}, $jsonMessage->{'rlsname'}, join(",", @{ $jsonMessage->{'chain'} }));
    }
    elsif ($command eq "PREPARE")
    {
        trd_trade( "prepare", $jsonMessage->{'tag'}, $jsonMessage->{'rlsname'}, join(",", @{ $jsonMessage->{'chain'} }) );
#        trd_print("PREPARE :: " . $jsonMessage->{'tag'} . " - \002" . $jsonMessage->{'rlsname'} . "\002 - " . join(",", @{ $jsonMessage->{'chain'} }));
        trd_add_latest($jsonMessage->{'tag'}, $jsonMessage->{'rlsname'}, join(",", @{ $jsonMessage->{'chain'} }));
        }
    elsif ($command eq "IRCREPLY") 
    {
        trd_print("\00307IRC\003 :: " . Irssi::strip_codes($jsonMessage->{'msg'}));

        # reply confirmation to data channel
        my $server = trd_get_data_channel_server( );
        if (defined($server)) {
            my $msg = "msg " . $TRD_DATA_CHANNEL . " " . Irssi::strip_codes($jsonMessage->{'msg'});
            trd_debug("$msg");
            $server->command($msg);
        }
    }
    elsif ($command eq "RACECOMPLETESTATUS")
    {
#        trd_debug("RACECOMPLETESTATUS: " . $jsonMessage->{'rlsname'});
    }
    else 
    {
        trd_debug("unhandled :: " . $jsonMessage->{'command'} . " - " . $strMessage);	
    }
}

sub trd_get_window($)
{
    my $strWindowName = shift;

    my $window = Irssi::window_find_name($strWindowName);

    if (!defined($window))
    {
        $window = Irssi::Windowitem::window_create($strWindowName, 1);
        $window->set_name($strWindowName);
    }

    return $window;
}

sub trd_get_status_window()
{
    return trd_get_window($TRD_WINDOW);
}

sub trd_print($)
{
    my $strMsg = shift;

    my $window = trd_get_status_window();
    $window->print($strMsg, MSGLEVEL_CLIENTCRAP);
}

sub trd_debug($)
{
    if ($TRD_DEBUG)
    {
        my $strMsg = shift;
        trd_print("DEBUG " . $strMsg);
    }
}

sub trd_listen_public_messages
{
    my ($server, $msg, $nick, $nick_addr, $target) = @_;

    # standard processing of signal
    Irssi::signal_continue($server, $msg, $nick, $nick_addr, $target);

    if (grep lc($_) eq $target, @TRD_IGNORE_CHANNELS)
    {
#        trd_debug("wrong channel " . $target . " - " . $msg);
        return ;
    }

    # not connected
    if (not defined $TRD_CONNECTION)
    {
        return ;
    }

    # convert \xA0 (non-breaking space) to \x20 (regular space)
    $msg =~ s/\xA0/\x20/g;

    # strip color codes
    $msg = Irssi::strip_codes($msg);

    # strip fish prefix
    $msg =~ s/$TRD_FISH_PREFIX//;

    trd_debug("trd_listen_public_messages " . $msg);

    socket_write($TRD_CONNECTION, "IRC $target $nick $msg");
}

sub trd_listen_own_public_messages
{
    if (defined $TRD_DATA_CHANNEL)
    {
        my ($server, $msg, $target) = @_;

        if ($target eq $TRD_DATA_CHANNEL)
        {
            trd_debug("trd_listen_own_public_messages " . $msg);

            socket_write($TRD_CONNECTION, "IRC $target $server->{nick} $msg");
        }
    }
}

# /trd xxx command
sub trd_irssi_cmd
{    
    my ($data, $server, $witem) = @_;

    chomp($data);

    if ($data eq "connect")
    {
        trd_connect();
    }
    elsif ($data eq "disconnect")
    {
        trd_disconnect();
    }
    elsif ($data eq "clear")
    {
        trd_clear_latest();
    }
    elsif ($data eq "print")
    {
        trd_print_latest();
    }
    elsif ($data =~ m/trade/)
    {
        my ($data, $tag) = split(/\s/, $data, 2);
        if ($tag) {
            trd_trade_latest($tag);
        }
    } 
    else {
        trd_print("Unsupported command: $data");
    }
}

# user input received
sub trd_user_input
{
    my ($strCmd, $server, $wnd) = @_;

    chomp($strCmd);

    my $strActiveWndName = Irssi::active_win()->{name};

    # not in trd window
    if ($strActiveWndName !~ m/^$TRD_WINDOW.*$/ || $strCmd =~ m/^\//)
    {
        return ;
    }

    if (defined $TRD_CONNECTION)
    {
	# trd_print("writing $strCmd");
	socket_write($TRD_CONNECTION, $strCmd);
    }
}


sub trd_connect
{
    if(defined $TRD_CONNECTION)
    {
        trd_disconnect();
    }

    my $strHost = $TRD_HOST;
    my $strPort = $TRD_PORT;

    $TRD_CONNECTION = socket_connect($strHost, $strPort);

    if (not defined $TRD_CONNECTION)
    {
        trd_print("\00304OFFLINE\003: Unable to contact the system at the moment.");

        if ($TRD_AUTOCONNECT)
        {
            $TRD_AUTOCONNECT_TAG = Irssi::timeout_add_once($TRD_AUTOCONNECT_INTERVAL * 1000, \&trd_connect, undef);
        }

        return 0;
    }
    else
    {
        $TRD_AUTOCONNECT_TAG = undef;
    }

    $TRD_INPUT_TAG = Irssi::input_add(fileno($TRD_CONNECTION), INPUT_READ, \&trd_read, undef);

    return 1;
}

# user initiated disconnect
sub trd_disconnect
{
    trd_print("\00304DISCONNECTED\003");

    trd_cancel_autoconnect();

    trd_post_disconnect();
}

# cleanup after disconnect
sub trd_post_disconnect
{
    # stop listening to socket
    Irssi::input_remove($TRD_INPUT_TAG);
    $TRD_INPUT_TAG = undef;

    # close socket
    if (defined $TRD_CONNECTION)
    {
        $TRD_CONNECTION->close();
        $TRD_CONNECTION = undef;
    }
}

sub UNLOAD()
{
    trd_post_disconnect();
}

# listen to public messages
Irssi::signal_add("message public", \&trd_listen_public_messages);
Irssi::signal_add("message own_public", \&trd_listen_own_public_messages);

# register /trd command
Irssi::command_bind("trd", \&trd_irssi_cmd);

# handle user input in status window
Irssi::signal_add("send text", \&trd_user_input);

if ($TRD_ONLOAD_OPEN_WINDOWS)
{
    trd_get_status_window();
}

if ($TRD_AUTOCONNECT)
{
    trd_connect();
}

my $strIrssiVer = "Irssi " . Irssi::version;
Irssi::print("trd script version $VERSION loaded (" . $strIrssiVer . ")");
