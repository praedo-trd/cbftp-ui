import weechat
import socket
import threading
import json
import glob
import os
import re
import json

# configuration
trdconfig = {
    'host': '127.0.0.1',                                    # host for TRD
    'port': 10000,                                          # port for TRD
    'connect_retry_interval': 60,                           # the amount of seconds inbetween reconnect attempts to TRD
    'servers': ['linknet', 'efnet'],                        # name of irc servers (buffers) that you wish to send messages to TRD from
    'exclude_channels': [],                                 # list of channels you dont ever want to send messages to TRD from
    'data_server': 'linknet',                               # the name of the server (buffer) that the data channel resides on
    'data_channels': ["#yourdatachannel"]                   # the name of the data channel
}

colormap = {
    'APPROVED': 'green,default',
    'TRADE': 'yellow,default',
    'VERSION': 'cyan,default'
}

# create the socket
trdclient = {
    'socket': None,
    'hook_fd': None,
    'buffer': '',
    'connected': False
}

trdbuffer = None

def trd_color(color, message):
    return "%s%s%s" % (weechat.color(color), message, weechat.color("reset"))

def trd_command_prefix(prefix):
    return "[" + prefix + "] "

def trd_client_start():

    global trdclient

    if trdclient['socket']:
        weechat.prnt(trdbuffer, 'TRD already connected')
        return

    trdclient['socket'] = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    response = trdclient['socket'].connect_ex((trdconfig['host'],trdconfig['port']))
    if response != 0:
        weechat.prnt(trdbuffer, "Failed to connect")
        trd_client_stop()
        return
    else:
        weechat.prnt(trdbuffer, "Connected!")

    trdclient['hook_fd'] = weechat.hook_fd(trdclient['socket'].fileno(), 1, 0, 0, 'trd_client_fd_cb', '')

def trd_client_stop():
    global trdclient
    if trdclient['socket'] or trdclient['hook_fd']:
        if trdclient['socket']:
            trdclient['socket'].close()
            trdclient['socket'] = None
        if trdclient['hook_fd']:
            weechat.unhook(trdclient['hook_fd'])
            trdclient['hook_fd'] = None

def trd_client_fd_cb(data, fd):

    global trdclient
    if not trdclient['socket']:
        return weechat.WEECHAT_RC_OK

    response = trdclient['socket'].recv(4096)
    if len(response):
        trd_client_handle_response(response.rstrip())

    return weechat.WEECHAT_RC_OK

def trd_client_handle_response(response):
    global trdconfig

    #bits = response.split(' ')
    try:
        jo = json.loads(response.decode("utf-8"))
        
        prefix = ""
        if jo["command"]:
            if jo["command"] in colormap:
                prefix = trd_command_prefix(trd_color(colormap[jo["command"]], jo["command"]))
            else:
                prefix = trd_command_prefix(jo["command"])

        if jo["command"] == 'IRCREPLY':
            channel = jo["channel"]
            msg = jo["msg"]
            getbuffer = weechat.info_get("irc_buffer", trdconfig['data_server'] + "," + channel)
            weechat.command(getbuffer, msg)
        elif jo["command"] == "VERSION":
            weechat.buffer_set(trdbuffer, "title", "TRD (" + jo["version"] + ")")
            weechat.prnt(trdbuffer, prefix + "Logged in to TRD server (" + jo["version"] + ")")
        elif jo["command"] == "TRADE" or jo["command"] == "APPROVED":
            weechat.prnt(trdbuffer, prefix + jo["rlsname"])
            weechat.prnt(trdbuffer, jo["tag"] + " > " + ",".join(jo["chain"]))
    except ValueError as e:
        weechat.prnt(trdbuffer, "Error parsing json: " + response.decode("utf-8"))
        return


def trd_input_cb(data, modifier, modifier_data, string):

    global trdconfig

    bits = string.split(" ")

    ptr_buffer = weechat.current_buffer()
    buffer_name = weechat.buffer_get_string(ptr_buffer, "short_name")
    buffer_type = weechat.buffer_get_string(ptr_buffer, 'localvar_type')
    
    #if buffer_type == "channel" and string[0] != "/" and  buffer_name in trdconfig['data_channels']:

        #trdclient['socket'].send("IRC %s trd %s" % (buffer_name, string))

    return "%s" % string

def trd_output_cb(data, buff, time, tags, display, hilight, prefix, msg):

    global trdclient, trdconfig

    if not trdclient['socket']:
        return weechat.WEECHAT_RC_OK

    tags = tags.split(",")

    nick = prefix.replace('@', '')
    channel = weechat.buffer_get_string(buff, 'short_name')

    full_buffer_name = weechat.buffer_get_string(buff, 'name')
    server = full_buffer_name.split(".")[0]

    if server in trdconfig['servers'] and channel not in trdconfig['exclude_channels']:
        try:
            packet = "IRC " + channel + " " + nick + " " + msg + '\n'
            hm = trdclient['socket'].send(packet.encode("utf-8"))
        except Exception as e:
            #weechat.prnt("", "FAIL: " + e.args[0])
            weechat.prnt(trdbuffer, "Disconnected....")
            trd_client_stop()
            trd_client_start()


    return weechat.WEECHAT_RC_OK

def trd_check_connection_status(data, remaining_calls):

    global trdclient

    if not trdclient['socket']:
        weechat.prnt(trdbuffer, "Attempting to reconnect....")
        trd_client_stop()
        trd_client_start()

    return weechat.WEECHAT_RC_OK

def trd_buffer_input_cb(data, buffer, input_data):
    return weechat.WEECHAT_RC_OK

def trd_buffer_close_cb(data, buffer):
    return weechat.WEECHAT_RC_OK

if __name__ == '__main__':

    weechat.register("trd_relay_2", "null", "1.0", "GPL3", "TRD Relay", "unload", "")

    # create buffer
    trdbuffer = weechat.buffer_new("TRD", "trd_buffer_input_cb", "", "trd_buffer_close_cb", "")
    weechat.buffer_set(trdbuffer, "localvar_set_no_log", "1") # don't log

    weechat.prnt(trdbuffer, "Initialized TRD relay script")

    # connect on start
    trd_client_start()
    #trd_client_stop()

    # catch input to channels - doesn't appear to be needed as the hook to output works
    # weechat.hook_modifier("input_text_for_buffer", "trd_input_cb", "")

    # catch output to channels
    weechat.hook_print('', 'irc_privmsg', '', 1, 'trd_output_cb', '')

    # set a timer to check connect status all the time
    weechat.hook_timer(trdconfig['connect_retry_interval'] * 1000, 60, 0, "trd_check_connection_status", "")
