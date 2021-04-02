# Configuring a site

## Capturing output from IRC

### IRC Channel + IRC Nicks

Firstly you need to configure a regexp for the IRC channel and bot. 
If the site has multiple channels you might write a regex like so which matches all of them:

`/#site(-spam)?/i` which will match both #site and #site-spam

Similarly if there are many bot nicks, make sure your regex matches them all:

e.g. `/(Bot1|Bot2|Bot3)/i` for example.

### Announce strings 

When a new race is started, when a race ends, or when a pre arrives, you will want to find a way to match these strings. There are two ways you can do this.

Announce strings can be written in two formats. The simplest is to enter a regex matching the race. For example to
match the new race string:

`New race: Hello-world in GAMES`

you could use the regex:

`/New\s+race:\s+(.*?)\s+in\s+(.*?)/i`

Note the two matching groups here with the parentheses. The order of these is important. You would need to enter 1 for section and 2 for rlsname in the adjacent fields.

The alternative and easier way to enter these strings is to just copy paste the important part of the announce string and replace the variables with placeholders. In the previous example you would enter this as the new race string:

`New race: &release in &section`

As the rlsname appears first, you would enter 1 for that and as the section appears second, you would enter 2 for that.
A more complicated example would be the following:

`New race started by someircuser at 11:13am in GAMES: Hello-world`

The key thing to remember is that you must substitute all variable items in the race string with placeholders like so:

> New race started by &user at &other in &section: &release

Here is a full list of valid variables:

`&section|&release|&user|&group|&altbookmark|&folder|&size|&date|&multiplier|&reason|&nuker|&time|&other`

It doesn't really matter if you use the wrong one. Just get the order right when entering the number of section and rlsname.

## Sections

### Adding a section 

There are two ways of doing this. Firstly, if you configure your IRC information and announce strings correctly, the sections will be captured from IRC automatically and added, ready for you to configure. Alternatively, you can just add the section manually using the exact string (and case) that is announced on IRC.

### Tagging 

All sections must have at least one tag. This allows the TRD daemon to determine when matching a new announce string which tag to use when trying to find destination sites to race to.

For example, let's say you have a section called announced on irc called `video-games`. This will probably correspond to a tag you define in settings called `games`. You should _tag_ the `video-games` section with the _tag_ `games`.

A more complicated example might be in the case of a section called `tv` which might accept multiple resolutions of tv shows, which you have multiple tags for. In this case you would apply multiple tags to the `tv` section.

Triggers can be used when _tagging_ a section as a filter for matching. But this is quite often un-necessary if you define regular expressions for what is allowed and not allowed in a tag in the Settings page.

### Rules

More information can be found about this on the [Rules](rules.md) page.

### Dupes

Currently this feature only supports a very basic set of functionality for format dupes in tv sections. For example HDTV might not be allowed after WEB.

You can either configure `First format wins` whereby which ever format arrives first will be the only one allowed. e.g. if HDTV is raced, WEB won't be allowed after and vice-versa.

Or, you can configure the priority field as `hdtv,web` which means: Firstly hdtv is allowed, followed by web (but not the other way around)

Or, you can configure the priority field as `web,hdtv` which means: Firstly web is allowed, followed by hdtv (but not the other way around)

The dupe engine needs some work and improvement to handle more complex cases.

### Skiplists

You can apply any number of skiplists to a section. More info on how to set them up on the [Skiplists](skiplists.md) page.
