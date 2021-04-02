# Logging 

TRD has robust logging out of the box, offering you a range of debug information
to figure out what is happening inside of the daemon.

You can configure logging in your `.env` file (instructions in the [Install guide](install.md))

Once you have set up a place for the logs to go to you will find three files:
- `general.info.log` - This is a general log showing information about race debug information.
- `general.debug.log` - This is a verbose log file containing lots of useful debug information about which irc channels + bots match, which new, end and pre strings match etc.
- `data.debug.log` - This file contains info about which lookups have failed.
