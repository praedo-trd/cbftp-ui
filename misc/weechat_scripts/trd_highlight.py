import weechat
import socket
import threading
import json
import glob
import os
import re

# configuration
TRD_CONFIG_PATH = "~/.trd" # no trailing slash
TRD_STRINGS = []
TRD_NEWSTRINGS_COLOR = "green,default"
TRD_ENDSTRINGS_COLOR = "red,default"
TRD_PRESTRINGS_COLOR = "lightmagenta,default"
TRD_SERVERS_STRIP = ["linknet"]
TRD_CHANNEL_EXCEPTIONS = ["#_trd", "#yourdatachannel"]

###################################################################################################
# Library-functions

def cleanRegex(res):
    if res[:1] == "/":
        return res

    return re.compile(res)

def php_regex_to_python_re(php_regex):
    _, pattern, php_flags = php_regex.split('/')
    flags = 0
    for flag in php_flags:
        flag = getattr(re, flag.upper())
        flags |= flag
    return pattern

def php_regex_to_python_re_compiled(php_regex):
    _, pattern, php_flags = php_regex.split('/')
    flags = 0
    for flag in php_flags:
        flag = getattr(re, flag.upper())
        flags |= flag
    return re.compile(pattern, flags)

###################################################################################################
# Loads all TRD strings from the config
def trd_load_strings():
    global TRD_CONFIG_PATH, TRD_STRINGS

    # reset it all
    TRD_STRINGS = []

    # open file and load the json
    path = os.path.expanduser(TRD_CONFIG_PATH + "/sites") + "/*.json"
    sFiles = glob.glob(path)
    for f in sFiles:
        with open(f) as data_file:
            data = json.load(data_file)
            TRD_STRINGS.append(data["irc"]["strings"])           

###################################################################################################
# Determine if a message on an irc channel is an announce string from TRD
def trd_isAnnounceString(string, matchString):

  if len(matchString) == 0 or len(string) == 0:
    return False

  escaped = re.escape(matchString)
  matchRegex = '[\s]?' + re.sub(re.compile('[\s]+'), 's+', escaped) + '[\s]?'

  lookup = '&section|&rlsname|&release|&user|&group|&altbookmark|&folder|&size|&date|&multiplier|&reason|&nuker|&time|&other'.split("|")
  newRegex = matchRegex;
  for item in lookup:
      newRegex = newRegex.replace('\\' + item, '([^\s]+)')

  newRegex = re.compile(newRegex, re.IGNORECASE)

  if newRegex.match(string):
      return True

  return False

def trd_modify_output_cb(data, modifier, modifier_data, string):

    if '\t' in string:
        prefix, message = string.split('\t', 1)

        # try to get buffer
        bufferBits = modifier_data.split(';')
        if len(bufferBits) < 2:
            weechat.prnt("", "Problem with: " + string)
            return string

        splitted = bufferBits[1].split('.')
        if len(splitted) < 2:
            return string

        buff = splitted[0]
        chan = splitted[1]

        if buff not in TRD_SERVERS_STRIP or chan in TRD_CHANNEL_EXCEPTIONS:
            return string

        message = weechat.string_remove_color(message, "")

        #hlwords = buffer_get_string(weechat.buffer_search('', buf), "highlight_words")

        #if(!string_has_highlight(message, hlwords)):
        #    return string

        for strings in TRD_STRINGS:

            # new strings
            if 'newstring' in strings:
                if 'newstring-isregex' in strings and strings["newstring-isregex"] == True:
                    pattern = re.compile(php_regex_to_python_re(strings["newstring"]))
                    if pattern.search(message):
                        return "%s\t%s%s" % (prefix, weechat.color(TRD_NEWSTRINGS_COLOR), message)
                else:
                    hm = trd_isAnnounceString(message, strings["newstring"])
                    if hm:
                        return "%s\t%s%s" % (prefix, weechat.color(TRD_NEWSTRINGS_COLOR), message)

            # end strings
            if 'endstring' in strings:
                if 'endstring-isregex' in strings and strings["endstring-isregex"] == True:
                    pattern = re.compile(php_regex_to_python_re(strings["endstring"]))
                    if pattern.search(message):
                        return "%s\t%s%s" % (prefix, weechat.color(TRD_ENDSTRINGS_COLOR), message)
                else:
                    hm = trd_isAnnounceString(message, strings["endstring"])
                    if hm:
                        return "%s\t%s%s" % (prefix, weechat.color(TRD_ENDSTRINGS_COLOR), message)

            # pre strings
            if 'prestring' in strings:
                if 'prestring-isregex' in strings and strings["prestring-isregex"] == True:
                    pattern = re.compile(php_regex_to_python_re(strings["prestring"]))
                    if pattern.search(message):
                        return "%s\t%s%s" % (prefix, weechat.color(TRD_PRESTRINGS_COLOR), message)
                else:
                    hm = trd_isAnnounceString(message, strings["prestring"])
                    if hm:
                        return "%s\t%s%s" % (prefix, weechat.color(TRD_PRESTRINGS_COLOR), message)
               
        if buff in TRD_SERVERS_STRIP:
            return weechat.string_remove_color(string, "")

    return string

if __name__ == '__main__':

    # setup and init
    weechat.register("trd_highlight", "null", "1.0", "GPL3", "TRD Highlighter", "unload", "")
    weechat.prnt("", "Hello, from TRD Highlighter");

    # load strings into memory
    trd_load_strings()

    # modify output
    weechat.hook_modifier("weechat_print", "trd_modify_output_cb", "")
