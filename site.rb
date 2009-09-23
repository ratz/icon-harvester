#!/usr/bin/env ruby -rubygems
require 'sinatra'
require 'haml'

MACTHEMES_VIEWTOPIC = "http://macthemes2.net/forum/viewtopic.php?id="
MACTHEMES_TITLE_PREFIX = 'MacThemes Forum / '

get '/theme/:id' do |topic_id|
	#topic_id = 16793626 #16798060

	%w{topic theme icon}.each { |x| require "lib/#{x}" }

	theme_cache = "cache/#{topic_id}.marshal"

=begin
if File.exists? theme_cache
	theme = File.open(theme_cache, 'r') { |f| Marshal.load(f) }
else
=end
	topic = Topic.new MACTHEMES_VIEWTOPIC, topic_id
	@theme = Theme.new topic
	#theme.retrive_info
	#theme.icons
	#theme.source.clear_cache
	#File.open(theme_cache, 'w') { |f| Marshal.dump(theme, f) }
	#end

	#puts theme.title
	#puts "Author: #{theme.author}"
	#puts "Pages: #{theme.source.pages}"
	#puts "Primary Download: #{theme.primary_download}"
	#puts "Extra Icons:"
  haml :theme
end

__END__

@@ layout
!!!
%html
  %head
    %title Icon Harvester
  %body
    = yield

@@ theme
- @theme.icons.each do |icon|
  %img{ :src => icon.url, :title => "#{icon.name} by #{icon.author}" }
