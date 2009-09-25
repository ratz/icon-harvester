Icon Harvester
==============

A little tool for pulling iPhone icons out of forum threads;
[MacThemes][], in particular.

The original prototype was written in Ruby, but due to personal web host
constraints, the live server is written in PHP.

Dependencies
------------

### Live Server (PHP) ###

* PHP >= 5.2
* cURL extension
* [phpQuery][]. Drop phpQuery.php and the phpQuery folder into the root
  folder of Icon Harvester.

### Prototype (Ruby) ###

You'll need [Ruby][] and [Hpricot][] to run the core program, and
[Sinatra][] if you want to run the web server.

Fire up Sinatra with `ruby site.rb`, and go to
`http://localhost:4567/theme/INSERT_TOPIC_ID_HERE` in your web browser.

[Ruby]: http://ruby-lang.org/
[Hpricot]: http://github.com/hpricot/hpricot
[Sinatra]: http://www.sinatrarb.com/
[MacThemes]: http://macthemes2.net/forums
[phpQuery]: http://code.google.com/p/phpquery/
