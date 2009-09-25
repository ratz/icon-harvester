<?php
require_once 'common.php';
require_once 'icon.php';

$return_json = false;

if (!isset($_GET['id'])) {
	die('No Theme ID specified!');
}

$id = (int) $_GET['id'];
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

class Theme
{
	public $id;

	public $title;
	public $artist;
	public $icons;

	protected $page_count;

	const WORD_REGEXP = '[a-zA-Z0-9\-]+';
	const WORDS_REGEXP = '[a-zA-Z0-9\-]+[a-zA-Z0-9\- ]+';
	static protected $IGNORED_ICON_NAMES = array(
		'wrote'  => true,
		'http'   => true,
		'alt'    => true,
		'icon'   => true,
		'more'   => true,
		'the'    => true,
		'and'    => true,
		'thanks' => true,
		'these'  => true,
		'is'     => true,
		'edit'   => true,
		'here'   => true,
		'made'   => true,
	);

	public function __construct($id)
	{
		$this->id = $id;
	}

	public function load()
	{
		$this->icons = array();
		$page_count = $this->page_count();
		echo 'Processing...';

		for ($i = 1; $i <= $page_count; $i++) {
			$doc = $this->get_page($i);

			$posts = $doc['#punviewtopic .blockpost:not(.firstpost)'];

			if ($i === 1) {
				$this->title = strip_tag_prefix($doc['head title']->text());
				$this->artist = $doc['#punviewtopic .firstpost .postleft dt a']->text();
			}

			foreach ($posts as $full_post) {
				$full_post = pq($full_post);
				$artist = $full_post['.postleft dt'][0]->text();

				$post = $full_post['.postmsg'][0];
				$post_imgs = $post['img.postimg'];

				$post_icons = $this->icons_in_post($full_post);
				$this->icons = array_merge($this->icons, $post_icons);
			}

			$progress = ($i / $page_count) * 100;
			echo(" <div class='progress'>$progress%</div>");
			ob_flush();
			flush();
		}
	}

	public function page_count()
	{
		if (!isset($this->page_count)) {
			$doc = $this->get_page(1);
			$this->page_count = (int) $doc['.pagelink a:last']->text();
		}

		return $this->page_count;
	}

	public function get_page($n)
	{
		$n = (int) $n;
		$html = download("http://macthemes2.net/forum/viewtopic.php?id={$this->id}&p=$n");
		return phpQuery::newDocument($html);
	}

	public function icons_in_post($full_post)
	{
		$post = $full_post['.postmsg'][0];

		if (strpos($post->html(), '<img') === false) {
			return array();
		}

		$post_text = $this->clean_up_post($post);
		$artist = $full_post['.postleft dt'][0]->text();
		$post_imgs = $post['img.postimg'];

		if (empty($post_imgs)) {
			return array();
		}

		$icons = $this->find_icon_names_in_post($post_imgs, $post_text);
		$post_icons = array();

		foreach ($icons as $img_src => $name) {
			$post_icons[] = new Icon($name, $img_src, $artist);
		}

		$matches = array();
		$maybe_names = array();

		if (preg_match('/(([a-z0-9\-]{2,}), )+([a-z0-9\- ]{2,})/i', $post_text, $matches)) {
			$maybe_names = explode(',', $matches[0]); # [0] == entire match
			$maybe_names = array_map('trim', $maybe_names);
		} else {
			$w = self::WORD_REGEXP;
			$and_match = array();
			if (preg_match('/'.$w.' and '.$w.'/iu', $post_text, $and_match)) {
				$maybe_names = explode(' and ', $and_match[0]);
				$maybe_names = array_map('trim', $maybe_names);
			}
		}

		if ($maybe_names) {
			foreach ($post_icons as &$post_icon) {
				$post_icon->maybe_names = array_merge($post_icon->maybe_names, $maybe_names);
			}
		}

		return $post_icons;
	}

	/**
	 * Convert <p>s and <br>s to newlines, remove quotations and edit
	 * notices, and trim whitespace.
	 */
	public function clean_up_post($post)
	{
		$post['blockquote']->remove();
		$post['.postedit']->remove();

		$post_text = trim($post->html());
		$post_text = preg_replace('/^<p>|<\/p>$/', '', $post_text);
		$post_text = preg_replace('/<\/p>\s*<p>|<br *\/?'.'>/', "\n", $post_text);
		$post_text = trim($post_text);
		return $post_text;
	}

	public function find_icon_names_in_post($post_imgs, $post_text)
	{
		$images = array();
		$name_side = null; // -1 == left, 1 == right

		foreach ($post_imgs as $img) {
			$img = pq($img);
			$lines = $this->lines_from_text($post_text);
			$image_line_number = $this->line_number_of_image($img, $lines);
			if ($image_line_number === null) {
				continue;
			}

			$image_line    = $lines[$image_line_number];

			if ($image_line_number > 0) {
				$previous_line = $lines[$image_line_number-1];
			} else {
				$previous_line = null;
			}

			$image_line = str_ireplace('&nbsp;', ' ', $image_line);
			$image_line = preg_replace('/<\/?(strong|em|u|b|i)( [^>]*)?'.'>/iu', '', $image_line);

			$img_regex = preg_quote((string) $img);
			$img_regex = str_replace('!', '\!', $img_regex);

			$name = null;

			# TODO: make this DRYer

			if (($name_side === -1 || $name_side === null) && $name === null) {
				$matches = array();
				preg_match('!('.self::WORDS_REGEXP.') *((<img [^>]*>) *)*'.$img_regex.'!', $image_line, $matches);

				if (isset($matches[1]) && !empty($matches[1])) {
					$name = $matches[1];
					if ($name_side == null) {
						$name_side = -1;
					}
				}
			}

			if (($name_side === 1 || $name_side === null) && $name === null) {
				$matches = array();
				preg_match('!'.$img_regex.' *((<img [^>]*>) *)*('.self::WORDS_REGEXP.')!', $image_line, $matches);

				if (isset($matches[3]) && !empty($matches[3])) {
					$name = $matches[3];
					if ($name_side == null) {
						$name_side = 1;
					}
				}
			}

			if ($name) {
				$name = trim($name);
			} else if ($previous_line) {
				$name = $this->find_icon_name_in_string($this->strip_html($previous_line));
			}

			$images[$img->attr('src')] = $name;
		}

		return $images;
	}

	public function find_icon_name_in_string($maybe_name)
	{
		$name = null;
		$w = self::WORD_REGEXP;
		$ws = self::WORDS_REGEXP;

		$s = array();
		preg_match("/here's an? ($w) (:?icon)?|here are some ($w)|($w) icons|($w) alt|($w):|^($w)$/iu", $maybe_name, $s);
		# Capture indexes        1                            2    3          4        5      6

		$s2 = array();
		preg_match("/([a-z]?[A-Z]$w):|([a-z]?[A-Z]$ws):|([a-z]?[A-Z]$ws)/u", $maybe_name, $s2);
		#             1                2                 3

		if (!empty($s) || !empty($s2)) {
			// captured names
			$ss = array(&$s2[1], &$s2[2], &$s[1], &$s[2], &$s[3], &$s[4], &$s[2], &$s[5], &$s2[3], &$s[6]);
			foreach ($ss as $maybe_name) {
				if (isset($maybe_name) && !empty($maybe_name)
					&& !isset(self::$IGNORED_ICON_NAMES[strtolower($maybe_name)])
				) {
					$name = $maybe_name;
					break;
				}
			}
		}

		return $name;
	}

	public function strip_html($name)
	{
		return trim(preg_replace('/<\/?[^>]*>/', '', $name));
	}

	public function lines_from_text($text)
	{
		$lines = explode("\n", $text);

		foreach ($lines as $k => &$line) {
			$line = trim($line);

			if (empty($line)) {
				unset($lines[$k]);
			}
		}

		return array_values($lines);
	}

	public function line_number_of_image($img, $lines)
	{
		$img_string = (string) $img;

		foreach ($lines as $k => $line) {
			if (strpos($line, $img_string) !== false) {
				return $k;
			}
		}

		return null;
	}
}

$theme = new Theme($id);
$theme->load($page);


if ($return_json) {
	header('Content-type: application/json');
	echo json_encode($theme->icons);
} else {
	echo '<ul class="icons">';
	foreach($theme->icons as $icon) {
		echo '<li><img src="'.$icon->src.'" alt="'.$icon->name.'" title=\'"'.$icon->name.'" by '.$icon->artist.'\'/></li>';
	}
	echo '</ul>';
}
?>
