**v0.9.39:**
- Fix: added missing env.php
- Fix: for generated field issues causing incorrect values

**v0.9.38:**
- Fix: Don't consider multi as dupe sources - ever

**v0.9.37:**
- Change: Optimisation to avoid using full disk scan for dupe engine lookups
- Change: List credits for all bncs on site list

**v0.9.36:**
- Fix: is_scripted_english is now a generated field at run-time
- Fix: section pre times added via web gui are now consistent with sections added automatically

**v0.9.35:**
- Experimental support for dotenv
- Fix: Setting allowed_groups for a tag no longer sets it to a list with on empty item
- Fix: rules editor is now full width

**v0.9.34:**
- Cleanup: removed some redundant code
- Fix: Prevent writing a null value to any model file
- Change: Upgraded ace editor and added autocomplete to the rules editor for sites
- Don't ignore tests anymore

**v0.9.33:**
- Added: Find sites that have not been tagged correctly in cbftp
- Fix: clicking on chain on approval add form incorrectly brought up help dialog
- Fix: Make it explicit that the current cbftp tool only executes raw commands
- Change: Highlight races differently from approved in server window
- Added: Cron task to get credits for all sites where possible
- Change: send cbftp raw commands by specifying path

**v0.9.32:**
- Added: Integration with cbftp http api (requires composer update)
- Fix: heading descriptions have a bit more breathing space
- Fix: Cleanup of a bunch of views to add new headings style

**v0.9.31:**
- Change: Click rlsname on race list to go to debug log

**v0.9.30:**
- Fix: navbar only collapses at medium breakpoint and also works on all pages

**v0.9.29:**
- Fix: Ignore leading zeros and just get the IMDB id directly from the imdb page we find
- Fix: error in simulator
- Added: extra test for making sure dupe engine sources are actually filtered
- Fix: collapsible nav now works on smaller breakpoints in web gui
- Fix: JS warning on site edit page
- New: Option to skip adding items which match baddir to pre database

**v0.9.28:**
- Change: Redid the layout for race list
- Fix: Form styles of approval form
- Update: added info to docs about empty function
- Fix: Race log now shows affils in bold
- Fix: Simulator view now highlights affils in bold always
- Fix: Edge case on multiple episode release names giving warnings

**v0.9.27:**
- Fix: New test for rules engine
- Deleted: the weird old auto stuff
- Change: Send affils as dlonly to cbftp

**v0.9.26:**
- Feature: reset immutable data items
- Fix: Cleaned up a few react components which weren't doing className properly

**v0.9.25:**
- Fix: Adjustments for mobile navigation
- Fix: JS error when removing a tag under certain circumstances
- Fix: no more shit race log
- Cleanup: removed old react build stuff
- Fix: Reverted back to dark background for navbar
