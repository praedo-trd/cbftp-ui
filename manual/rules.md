# Rules

Use regex as little as possible, it might be suitable for skiplist, but try to avoid it as much as possible in rules.

## Rule evaluation

There are 3 types of rule actions:

* `EXCEPT`
  * Always evaluated first, if rule matches then no further evaluation will be done
* `ALLOW`
  * Needs to match this and all other `ALLOW` rules
* `DROP`
  * Drops on first `DROP` rule

## Rule context (site view)

There are two types of rules when editing a site section.

* Section rules
* Tag rules

As an example `Site1` has the section `TV-HD` that allows TV-720P and TV-1080P English.
We can then specify then map the tags `tv-1080p` and `tv-720p` and leave both the trigger and rules default.

Then in the section rules we can add our rules that are common for both tags. If we wanted to allow reality for 720p 
releases but not 1080p you could go deeper and put the relevant rule under the tag rules.

## Operators

* `==` is
* `!=` is not
* `>=` is greater than or equal to
* `<=` is less than or equal to
* `isin` is in comma separated list
* `iswm` is wildcard match
* `matches` matches regex
* `contains` check if array contains one value
* `containsany` check if array contains any of the values

These operator can also be negated by adding a `!` in front of the operator. But most of the times it makes sense to just switch the `ALLOW` for a `DROP` or vice-versa.

### Examples

#### Is

* Drop internal:

  ```php
  [rlsname.internal] == true DROP
  ```

* Allow current season only:

  ```php
   [rlsname.season] == [tvmaze.current_season] ALLOW
  ```

#### Is not

* Drop if music year is not current year:

  ```php
  [music.year] != [datetime.this_year] DROP
  ```

#### Is greater than or equal to

* Drop if more than 5CD's:

  ```php
  [music.disk_count] >= 5 DROP
  ```

#### Is less than or equal to

* Allow if music year is 2010 or earlier:

  ```php
  [music.year] <= 2010 ALLOW
  ```

#### Is in

* Music source is one of:

  ```php
  [music.source] isin CD,DVD,ViNYL,WEB ALLOW
  ```

* Check for TV Country OR a Web TV Network:

  ```php
  [tvmaze.country] isin United States,United Kingdom,New Zealand,Australia,Canada OR ([tvmaze.network] isin Amazon Prime,Apple TV+,Disney+,HBO,Netflix) ALLOW
  ```

* Movies was released in the last two years:

  ```php
  [imdb.year] isin [datetime.last_2_years] ALLOW
  ```

#### Is wildcard match

* Wildcard match in string, dROP if "DOKU" is found:

  ```php
  [rlsname] iswm *DOKU* DROP
  ```

#### Matches

* Regex evaluation, avoid this as regex is slow.:

  ```php
  # Please don't do this
  [rlsname] matches /(norwegian|swedish|danish)\.(720p|1080p)\.(Bluray|WEBRIP|WEB)\.(x|h)264/i except
  ```

#### Contains

* Array of TV genres contains "Children":

  ```php
  [tvmaze.genres] contains Children ALLOW
  ```

#### Contains any of

* Check if any of the right-hand side value are in the array (only works on arrays):

  ```php
  [tvmaze.genres] containsany Sports,Children DROP
  ```

* Array of TV genres contains "Children" OR "Comedy":

  ```php
  [tvmaze.genres] containsany Children,Comedy ALLOW
  ```

## Special
   
* empty([field])
   
  Can be used to check if a field is empty, can not be negated:
   
  ```php
  empty([rlsname.country]) DROP
  ```
