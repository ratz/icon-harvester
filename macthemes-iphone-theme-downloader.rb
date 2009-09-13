#!/usr/bin/env ruby -rubygems
require 'hpricot'
require 'open-uri'

topic_id = 16798060
MACTHEMES_VIEWTOPIC = "http://macthemes2.net/forum/viewtopic.php?id="
MACTHEMES_TITLE_PREFIX = 'MacThemes Forum / '

class Topic
	attr_reader :id, :pages

	PAGE_PARAM = 'p'

	def initialize viewtopic, id
		@viewtopic = viewtopic
		@id = id
		@pages = nil
		@docs = []
	end

	def url
		"#@viewtopic#@id"
	end

	def page_url n
		"#{url}&#{PAGE_PARAM}=#{n}"
	end

	def open page
		cache_file = "cache/#@id-#{page}.txt"
		data = nil

		if File.exists? cache_file
			File.open(cache_file, 'r') { |f| data = f.read }
		else
			Kernel.open(page_url(page)) { |f| data = f.read }
			File.open(cache_file, 'w') { |f| f.write data }
		end

		return data
	end

	def parse page
		if @docs[page].nil?
			@docs[page] = Hpricot(open(page))
		end

		return @docs[page]
	end

	def pages
		if @pages.nil?
			@pages = (parse(1)/'.pagelink'/'a').last.inner_html.to_i
		end

		return @pages
	end

	def clear_cache
		@docs = []
	end
end

class Theme
	attr_reader :title, :source, :author, :icons, :primary_download

	def initialize source
		@source = source
		@author = nil
		@title = nil
		@primary_download = nil
		@icons = nil
	end

	def retrive_info
		if @title.nil? or @author.nil? or @primary_download.nil?
			doc = @source.parse 1
			first_post = (doc/'.blockpost.firstpost')

			@title = (doc/:head/:title).text.gsub(MACTHEMES_TITLE_PREFIX, '')
			@author = (first_post/' .postleft dt').text
			@primary_download = (first_post/' .postmsg a img').first.parent['href']
		end
	end

	def title; retrive_info; @title; end
	def author; retrive_info; @author; end
	def primary_download; retrive_info; @primary_download; end

	def icons
		if @icons.nil?
			@icons = []

			1.upto @source.pages do |i|
				doc = @source.parse i
				(doc/'.blockpost:not(.firstpost) .postmsg img').each do |img|
					next if img['src'][0...('img/smilies/'.length)] == 'img/smilies/'
					author = (img.parent.parent.parent.parent/'.postleft dt').text
					icon = Icon.new(img['src'], author)
					@icons << icon
				end
			end
		end

		return @icons
	end
end

class Icon
	attr_reader :url, :author

	def initialize url, author
		@url = url
		@author = author
	end

	def to_html
		"<img src='#@url' /> by #@author"
	end

	def to_s
		"#@url by #@author"
	end
end

theme_cache = "cache/#{topic_id}.marshal"

=begin
if File.exists? theme_cache
	theme = File.open(theme_cache, 'r') { |f| Marshal.load(f) }
else
=end
	topic = Topic.new MACTHEMES_VIEWTOPIC, topic_id
	theme = Theme.new topic
	#theme.retrive_info
	#theme.icons
	#theme.source.clear_cache
	#File.open(theme_cache, 'w') { |f| Marshal.dump(theme, f) }
#end

puts theme.title
puts "Author: #{theme.author}"
puts "Pages: #{theme.source.pages}"
puts "Primary Download: #{theme.primary_download}"
puts "Extra Icons:"

theme.icons
