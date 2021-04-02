#### IRC information
So you want to know how to configure a site, huh?

Field        |
------------ | -------------
Channel | A regex matching the site channels on IRC
Bot | A regex matching the nicks of the IRC bots announced new/end races

#### Announce strings

Announce strings can be written in two formats. The simplest is to enter a regex matching the race. For example to
match the new race string:

```New race: Hello-world in GAMES```

you could use the regex:

```/New\s+race:\s+(.*?)\s+in\s+(.*?)/i```

Note the two matching groups here with the parentheses. The order of these is important. You would need to enter 1 for section and 2 for rlsname in the adjacent fields.

The alternative and easier way to enter these strings is to just copy paste the important part of the announce string and replace the variables with placeholders. In the previous example you would enter this as the new race string:

```New race: &release in &section```

As the rlsname appears first, you would enter 1 for that and as the section appears second, you would enter 2 for that.
A more complicated example would be the following:

```New race started by someircuser at 11:13am in GAMES: Hello-world```

The key thing to remember is that you must substitute all variable items in the race string with placeholders like so:

```New race started by &user at &other in &section: &release```

Here is a full list of valid variables:

```&section|&release|&user|&group|&altbookmark|&folder<br>&size|&date|&multiplier|&reason|&nuker|&time|&other```

It doesn't really matter if you use the wrong one. Just get the order right when entering the number of section and rlsname.
