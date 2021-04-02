# Configuring prebots 

You have two choices when using TRD. The yolo-approach where you don't feed pretimes into your database to check before trading. Or, the safe approach where you feed in pre announces from an addpre or announce channel.

If you choose to feed in pretimes, you can of course find a way to feed data into the `pre` database table yourself, or configure TRD to catch announces.

To do this go to the `Prebots` tab in the web GUI and add a bot. It requires 3 fields:

- `channel` A regular expression to match the IRC channel. e.g. for #mychannel you would use regex `/#mychannel/i`
- `bot` A regular expression to match the bot that is announcing in the IRC channel you just configured. If the bot is `FooBar69` you would use `/FooBar69/i`
- `string_match` A regular expression to capture the _rlsname_. So if an IRC line looked like this `[Section] This.Is.A.Release.Name-Group` you would use a regex like so: `/^\[.*?\]\s+(.*?)$/i`. Notice we are using parenthese to _capture_ the rlsname here. This is critical. If you don't do this it won't find the release names. 
