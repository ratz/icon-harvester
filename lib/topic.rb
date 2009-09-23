require 'hpricot'
require 'open-uri'

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
