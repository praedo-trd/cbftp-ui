menu @trd {
    Connect:/trd.socket_connect
    Disconnect:/trd.socket_close
}

on 1:START: {
    trd.socket_connect
    .timertrd 0 60 trd.socket_connect
    window -eg0z @trd
    aline @trd $time Loaded debug window

    ; window -eg0z @trd.megalog

    ; make hash table for auto stuff
    ; hmake _trd_auto
}

on 1:UNLOAD: {
    .timertrd off
}

on 1:TEXT:*:#: {
    var %str = IRC $chan $nick $strip($1-)
    if ($sock(trd).status == active) {
        sockwrite -n trd $+($strip(%str),$chr(10))
        ; aline @trd $time >> %str
        ; aline @trd.megalog $time %str
    }
}

on 1:INPUT:#yourdatachannel: {
    var %str = IRC $chan $nick $remove($strip($1-), $chr(183), $chr(62))
    if ($sock(trd).status == active) {
        sockwrite -n trd $+($strip(%str),$chr(10))
        aline @trd $time >> %str
    }
}

on 1:INPUT:@trd: {
    aline @trd $time $1-
    sockwrite -n trd $+($strip($1-),$chr(10))
}

on 1:sockread:trd: {
    if ($sockerr > 0) return
    :nextread
    sockread %temp
    if ($sockbr == 0) return
    if (%temp == $null) %temp = -
    trd.handle %temp
    goto nextread
}

on 1:sockclose:trd: {
    aline @trd $time Socket disconnected
}

alias trd.socket_connect {
    if ($sock(trd).status != active) {
      sockopen trd 127.0.0.1 10001
    }
}

alias trd.socket_close {
    aline @trd Closing socket
    sockclose trd
}

alias trd.handle {
    tokenize 32 $strip($1-)
    aline @trd $time << $1-

    var %command_pattern = /^{"command":"(\w+)","\w+":/
    if ($regex($1-, %command_pattern)) {
        if ($regml(1)) {
            %command = $regml(1)
            aline @trd Command is %command
            if (%command == APPROVED) {
                aline @trd Reached approve
                var %approved_pattern = /^{"command":"(\S+)","tag":"(\S+)","chain":\[(\S+)\],"affilSites":\[(?:.*)\],"rlsname":"(\S+)","data"/
                if ($regex($1-, %approved_pattern)) {
                    if ($regml(1)) {
                        var %command = $regml(1)
                        var %tag = $regml(2)
                        var %chain = $remove($regml(3),$chr(34))
                        var %release = $regml(4)
                        aline @trd $time >> %command > %tag > %release > %chain
                        /*
                        Enable sending UDP in trd settings instead of using this, unless your trd server can't access cbftp but your shell chan.
                          Which is unlikely since you are using mirc.
                          Uncomment below to send udp to cbftp from mirc.
                        */
                        ; trd.trd %tag %release %chain
                    }
                }
            }
            elseif (%command == IRCREPLY) {
                aline @trd Reached IRCREPLY
                var %ircreply_pattern = /^{"command":"(\S+)","msg":"([\S\s]+)","channel":"(\S+)"}/
                if ($regex($1-, %ircreply_pattern)) {
                    if ($regml(1)) {
                        aline @trd Reached IRC code
                        var %msg = $regml(2)
                        var %channel = $regml(3)
                        scon -at1 if ($me ison #mydatachan) { msg %channel $replace(%msg,\/,$chr(47)) }
                    }
                }
            }
        }
    }

    /*
    TODO: Add (TRADE)
    Outdated code:
    ;  else if (%command == TRADE) {
    ;    trd.create_1_click $2-
    ;  }
    ;  else if (%command == SIMULATIONRESULT) {
    ;    var %c = 0
    ;    while ($gettok($2-,%c,124)) {
    ;      var %c = $calc(%c + 1)
    ;      var %tok = $gettok($2-,%c,124)
    ;      if ($len(%tok) != 0) {
    ;        aline @trd $time %tok
    ;      }
    ;    }
    */

}


/*
    Outdated code below, with some work this could allow click-trading directly using mirc
*/

/*
alias trd.create_1_click {
    tokenize 32 $strip($1-)
    var %title = TRD :: $3
    var %msg = $+($4,$crlf,$1)
    noop $tip(trd.1click,%title,%msg,30,,,trd.1click $1-)
}

alias trd.1click {
    tokenize 32 $strip($1-)
    trd.trd $1 $3 $4
}

*/

; $1: tag
; $2: rlsname
; $3: list of sites (seperated by comma) e.g. FTP1,FTP2,FTP3
alias trd.trd {
    var %udphost = 127.0.0.1
    var %udpport = 55477
    var %udppass = bestpass

    aline @trd $time >> Trading $3 on $2 to $1

    sockudp cbftp %udphost %udpport %udppass race $1 $2 $3
}
