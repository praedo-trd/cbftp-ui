# Fixing bad data 

TRD has built in support for correction of bad data lookups. An example of this might be a tv show that is from the UK but is actually made in the US.

To do this you need to configure a _data channel_. A data channel is a special IRC channel where you can type commands and it will fix the actual show/movie looked up, or if you prefer just specific fields.

Data channels can be shared amongst groups of users, and this is reccommended so that you can benefit from the fixes others make and vice-versa. e.g. if Alice and Bob are friends, Alice might fix a lookup in this channel, and Bob also has the channel configured and he will receieve the fix to his instance of TRD also.

## Setup

Depending on your IRC client there should be a configuration in that script that allows you to configure an IRC channel. Create this channel and add it to the IRC relay's configuration.

Next, go to the Settings in the Web GUI and enter the channel name there also.

## Commands 

Before continuing, you must understand the concept of a cleaned rlsname. TRD takes the name of a tv show or movie and tries to clean it in to a string seperated by spaces. e.g. `Game.Of.Thrones` would become `Game Of Thrones`. You can see this in the simulator under the key `rlsname.cleaned` and this is what you must _always_ use to fix your lookups if you are fixing an entire show or movie.

Type any of the following commands on your IRC data channel.

### TVMaze  

Fix an entire show:
`tvmaze <rlsname.cleaned> <new-id>` (ID from the tvmaze URL)

Fix a field:
`tvmazef <id> <field> <newValue>`

Check a lookup:
`ctvmaze <rlsname.cleaned>`

### IMDB

Fix a movie:
`imdb <rlsname.cleaned> <new-id>` (tt123455 for the ID)

Check a lookup:
`cimdb <rlsname.cleaned>`
