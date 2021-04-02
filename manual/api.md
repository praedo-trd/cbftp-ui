## API Specification 

The TRD daemon has a very simple TCP-based API. Messages are encoded with JSON and contain a `command` key indicating what command we are processing, and some other data as part of the payload depending on what you are doing.

## Logging on to TRD

Simply connect to the daemon using TCP on the port specified in your `.env` file.

The first command you receieve will look like so:
```
{
   "command":"VERSION",
   "version":"1234"
}
``` 

## Receiving commands

`TRADE` - This command indicates a race has been evaluated and you could optionally trade it.

---

`APPROVED` - This command indicates that a race has been evaluated and it has either matched an approval rule, or an autorule and therefore can be blindly traded.

---

`IRCREPLY` - This command is special, and used for data channels. If you type a command that matches a certain format you will get an `IRCREPLY` command back, and you should parse it and write a message back to the channel using the data in the payload.

---

`RACECOMPLETESTATUS` - This command is received in real(ish)-time indicating which sites a race is complete on.

## Sending commands 

`IRC` - Used to send messages to the daemon from IRC so announces can be caught.

---

`APPROVED` - Send an approval to the daemon and it will be added to the list of approvals.

---

`PRETIP` - Docs coming soon

---

`RACED` or `TRADED` - Docs coming soon
