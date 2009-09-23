Icon Harvester
==============

A little tool for pulling iPhone icons out of forum threads;
[MacThemes][], in particular.

Dependencies
------------

You'll need [Ruby][] and [Hpricot][] to run the core program, and
[Sinatra][] if you want to run the web server.

Fire up Sinatra with `ruby site.rb`, and go to
`http://localhost:4567/theme/INSERT_TOPIC_ID_HERE` in your web browser.

[Ruby]: http://ruby-lang.org/
[Hpricot]: http://github.com/hpricot/hpricot
[Sinatra]: http://www.sinatrarb.com/
[MacThemes]: http://macthemes2.net/forums
