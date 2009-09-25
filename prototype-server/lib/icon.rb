require 'set'

class Icon
	attr_reader :name, :url, :author
	attr_accessor :maybe_names

	def initialize name, url, author
		@name = name
		@url = url
		@author = author
		@maybe_names = Set.new
	end

	def to_html
		"<img src='#@url' /> by #@author"
	end

	def to_s
		inspect
	end

	def inspect
		"<Icon \"#@name\" by #@author>"
	end
end
