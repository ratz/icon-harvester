require 'hpricot'

class Theme
	attr_reader :title, :source, :author, :icons, :primary_download

	WORD_REGEXP = '[a-zA-Z0-9\-]+'
	WORDS_REGEXP = '[a-zA-Z0-9\-]+[a-zA-Z0-9\- ]+'
	IGNORED_ICON_NAMES = ['wrote', 'http', 'alt', 'icon', 'more', 'the', 'and', 'thanks', 'these', 'is', 'edit', 'here']

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
			@icons = Set.new

			1.upto @source.pages do |i|
				doc = @source.parse i
				(doc/'.blockpost:not(.firstpost) .postmsg').each do |post|
					@icons.merge icons_in_post(post)
				end
			end
		end

		return @icons
	end

	def icons_in_post post
		post_imgs = (post/'img.postimg')
		return Set.new if post_imgs.length == 0

		author = (post.parent.parent/'.postleft dt').text
		post_text = clean_up_post(post)
		
		post_icons = Set.new
		#icons_having_names_count = 0

		find_icon_names_in_post(post_imgs, post_text).each do |img, name|
			icon = Icon.new(name, img['src'], author)
			#icons_having_names_count += 1 if name and !name.empty?
			post_icons << icon
		end

		matches = post_text.match(/(([a-z0-9\-]{2,}), )+([a-z0-9\- ]{2,})/i)

		if matches
			maybe_names = matches[0].split(',').map { |x| x.strip } # [0] == entire match
		else
			w = WORD_REGEXP
			matches = post_text.match(/#{w} and #{w}/i)
			maybe_names = matches[0].split(' and ').map { |x| x.strip } if matches
		end

		post_icons.each { |icon| icon.maybe_names.merge(maybe_names) } if maybe_names
		return post_icons
	end

	def clean_up_post post
		postedit = (post/'.postedit').to_s
		text = post.inner_html.gsub(postedit, '').strip.
			gsub('</p>\s+<p>', "\n").gsub(/^<p>|<\/p>$/, '').gsub(/<br *\/?>/, "\n")

		(post/'blockquote').each do |b|
			text.gsub!(b.to_s, '')
		end

		return text
	end

	def strip_html name
		name.gsub(/<\/?[^>]*>/, '').strip
	end

	def lines_from_text text
		lines = text.split("\n").map { |l| l.strip }.delete_if { |l| l.empty? }
	end

	def line_number_of_image img, lines
		return lines.find_index { |l| l.include? img.to_s }
	end

	def find_icon_names_in_post post_imgs, post_text
		images = {}
		name_side = nil

		post_imgs.each do |img|
			lines = lines_from_text post_text
			image_line_number = line_number_of_image img, lines
			next unless image_line_number

			image_line    = lines[image_line_number].gsub('&nbsp;', ' ').gsub(/<\/?(strong|em|u|b|i)( [^>]*)?>/, '')
			previous_line = lines[image_line_number-1]

			img_regex = Regexp.escape(img.to_s)

			name = nil

			if (name_side == :left or name_side.nil?) and name.nil?
				name = image_line.match(/(#{WORDS_REGEXP}) *((<img [^>]*>) *)*#{img_regex}/).to_a[1]
				name_side = :left if name and name_side.nil?
			end

			if (name_side == :right or name_side.nil?) and name.nil?
				name = image_line.match(/#{img_regex} *((<img [^>]*>) *)*(#{WORDS_REGEXP})/).to_a[3]
				name_side = :right if name and name_side.nil?
			end

			name = find_icon_name_in_string(strip_html(previous_line)) unless name
			name.strip! if name
			images[img] = name
		end

		images
	end

	def find_icon_name_in_string maybe_name
		name = nil
		w = WORD_REGEXP
		ws = WORDS_REGEXP

		s = maybe_name.match(/here's an? (#{w}) (icon)?|here are some (#{w})|(#{w}) icons|(#{w}):|^(#{w})$/i)
		# Capture indexes                 1      2                     3      4            5        6

		s2 = maybe_name.match(/([a-z]?[A-Z]#{w}):|([a-z]?[A-Z]#{ws}):|([a-z]?[A-Z]#{ws})/)
		#                       1            2            3

		if s or s2
			s  = [] unless s
			s2 = [] unless s2
			ss = [s2[1], s2[2], s[1], s[3], s2[3], s[4], s[3], s[5], s[6]] # captured names
			name = ss.find { |x| !x.nil? and !(IGNORED_ICON_NAMES.include? x.downcase) }
		end

		return name
	end
end
