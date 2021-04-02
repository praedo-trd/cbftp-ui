# IRC Relay

You'll need to send messages from IRC to the TRD daemon. There are some scripts for this under the `misc/` directory.

## Weechat
- There are two scripts for weechat. Load `trd.py` if you just want to do relay (be sure to configure it).
- If you want to remove all formatting from IRC channels and replace new/end/pre announce with pretty colours, you can try loading `trd_highlight.py` also.

## Irssi
- Put `trd.pl` script in `~/.irssi/scripts/`
- In irssi: `/load perl` and `/script load trd.pl`

## mIRC
- There is a script in the `misc/` directory but it probably needs fixing.
