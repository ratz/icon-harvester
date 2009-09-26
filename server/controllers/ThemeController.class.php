<?php
Library::import('iconHarvester.models.Theme');
Library::import('iconHarvester.models.Icon');
Library::import('iconHarvester.models.App');
Library::import('iconHarvester.models.Artist');
Library::import('recess.framework.forms.ModelForm');

/**
 * !RespondsWith Layouts
 * !Prefix theme/
 */
class ThemeController extends Controller {

	/** @var Theme */
	protected $theme;

	/** @var Form */
	//protected $_form;

	function init() {
		$this->theme = new Theme();
		//$this->_form = new ModelForm('theme', $this->request->data('theme'), $this->theme);
	}

	/** !Route GET */
	function index() {
		$page = isset($this->request->get['page']) ? (int) $this->request->get['page'] : 1;
		$this->page = $page;

		$url = "http://macthemes2.net/forum/viewforum.php?id=24&p=$page";

		if (!is_cache_valid(url_cache_file($url))) {
			$html = download($url);
			$doc = phpQuery::newDocument($html);

			$rows = $doc['#punviewforum table tr > td.tcl']->parent();
			$viewtopic_length = strlen('viewtopic.php?id=');

			foreach ($rows as $row) {
				$row = pq($row);
				$a = $row['a:first'][0];
				$name = trim($a->text());

				if (preg_match('/^\[(theme|contest|contest theme)\]/iu', $name)) {

					$id = substr($a->attr('href'), $viewtopic_length);

					if (!Make::a('Theme')->like('topic_id', $id)->exists()) {
						$artist = $row['td.tc4'][0]->text();

						$byPos = strrpos($name, ' by ');
						if ($byPos !== false) {
							$name = substr($name, 0, $byPos);
						}

						$name = strip_tag_prefix($name);

						$theme = new Theme;
						$theme->topic_id = $id;
						$theme->name = $name;
						$theme->artist = $artist;
						$theme->save();
					}
				}
			}
		}

		$this->themeSet = $this->theme->all();
		if(isset($this->request->get['flash'])) {
			$this->flash = $this->request->get['flash'];
		}
	}

	/** !Route GET, $id */
	function details($id) {
		$this->theme->id = $id;
		if($this->theme->exists()) {
			$this->icons = $this->find_icons();
			return $this->ok('details');
		} else {
			return $this->forwardNotFound($this->urlTo('index'));
		}
	}

	/** ! Route GET, new */
	/*
	function newForm() {
		$this->_form->to(Methods::POST, $this->urlTo('insert'));
		return $this->ok('editForm');
	}
	 */

	/** ! Route POST */
	/*
	function insert() {
		try {
			$this->theme->insert();
			return $this->created($this->urlTo('details', $this->theme->id));
		} catch(Exception $exception) {
			return $this->conflict('editForm');
		}
	}
	 */

	/** ! Route GET, $id/edit */
	/*
	function editForm($id) {
		$this->theme->id = $id;
		if($this->theme->exists()) {
			$this->_form->to(Methods::PUT, $this->urlTo('update', $id));
		} else {
			return $this->forwardNotFound($this->urlTo('index'), 'Theme does not exist.');
		}
	}
	 */

	/** ! Route PUT, $id */
	/*
	function update($id) {
		$oldTheme = new Theme($id);
		if($oldTheme->exists()) {
			$oldTheme->copy($this->theme)->save();
			return $this->forwardOk($this->urlTo('details', $id));
		} else {
			return $this->forwardNotFound($this->urlTo('index'), 'Theme does not exist.');
		}
	}
	 */

	/** ! Route DELETE, $id */
	/*
	function delete($id) {
		$this->theme->id = $id;
		if($this->theme->delete()) {
			return $this->forwardOk($this->urlTo('index'));
		} else {
			return $this->forwardNotFound($this->urlTo('index'), 'Theme does not exist.');
		}
	}
	 */

	protected $page_count;

	const WORD_REGEXP = '[a-zA-Z0-9\-.]+';
	const WORDS_REGEXP = '[a-zA-Z0-9\-]+[a-zA-Z0-9\-\. ]+';
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

	public function find_icons()
	{
		$time_since_last_scan = time() - $this->theme->last_scan_time;

		$icons = array();
		$page_count = $this->page_count();
		$i = $this->theme->processed_pages;

		if ($i <= 0) {
			$i = 1;
		} else if ($i < $page_count) {
			$i += 1;
		}

		if ($i >= $page_count) {
			$i = $page_count;
		}

		$this->current_page = $i;
		$this->total_pages  = $page_count;

		if ($time_since_last_scan < 4
			|| ($i == $page_count && $time_since_last_scan < CACHE_EXPIRE))
		{
			return $this->theme->icons();
		}

		if ($this->theme->processed_pages < $i) {
			$this->theme->processed_pages = $i;
		}

		$this->theme->save();

		#for ($i = $start; $i <= $page_count; $i++) {
			$doc = $this->get_page($i);

			$posts = $doc['#punviewtopic .blockpost:not(.firstpost)'];

			/*
			if ($i === 1) {
				$this->theme->title = strip_tag_prefix($doc['head title']->text());
				$this->theme->artist = $doc['#punviewtopic .firstpost .postleft dt a']->text();
			}
			 */

			foreach ($posts as $full_post) {
				$full_post = pq($full_post);
				$artist = $full_post['.postleft dt'][0]->text();

				$post = $full_post['.postmsg'][0];
				$post_imgs = $post['img.postimg'];

				$post_icons = $this->icons_in_post($full_post);
				$icons = array_merge($icons, $post_icons);
			}
		#}

		$this->theme->last_scan_time = time();
		$this->theme->save();

		return $this->theme->icons();
	}

	public function page_count()
	{
		if (!isset($this->page_count)) {
			$doc = $this->get_page(1);
			$this->page_count = (int) $doc['.pagelink a:last']->text();

			if ($this->page_count < 0) {
				$this->page_count = 0;
			}
		}

		return $this->page_count;
	}

	public function get_page($n)
	{
		$n = (int) $n;
		$html = download("http://macthemes2.net/forum/viewtopic.php?id={$this->theme->topic_id}&p=$n");
		return phpQuery::newDocument($html);
	}

	protected function icons_in_post($full_post)
	{
		$post = $full_post['.postmsg'][0];

		if (strpos($post->html(), '<img') === false) {
			return array();
		}

		$post_text = self::clean_up_post($post);
		$artist_name = $full_post['.postleft dt'][0]->text();
		$post_imgs = $post['img.postimg'];

		if (empty($post_imgs)) {
			return array();
		}

		$icons = self::find_icon_names_in_post($post_imgs, $post_text);
		$post_icons = array();

		foreach ($icons as $img_src => $name) {
			$icon = Make::an('Icon')->like('src', $img_src)->first();
			$changed = false;

			if (!$icon) {
				$icon = new Icon;
				$icon->src = $img_src;
				$icon->theme_id = $this->theme->id;

				// TODO: make this DRYer too

				if ($artist_name) {
					$artist = Make::an('Artist')->like('name', $artist_name)->first();

					if (!$artist) {
						$artist = new Artist;
						$artist->name = $artist_name;
						$artist->save();
					}

					$icon->setArtist($artist);
				}

				$changed = true;
			}

			if ($name && !$icon->app()) {
				$app = Make::an('App')->like('name', $name)->first();

				if (!$app) {
					$app = new App;
					$app->name = $name;
					$app->save();
				}

				$icon->setApp($app);
				$changed = true;
			}

			if ($changed) {
				$icon->save();
			}

			$post_icons[] = $icon;
		}

		/*
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
		 */

		return $post_icons;
	}

	/**
	 * Convert <p>s and <br>s to newlines, remove quotations and edit
	 * notices, and trim whitespace.
	 */
	static protected function clean_up_post($post)
	{
		$post['blockquote']->remove();
		$post['.postedit']->remove();

		$post_text = trim($post->html());
		$post_text = preg_replace('/^<p>|<\/p>$/', '', $post_text);
		$post_text = preg_replace('/<\/p>\s*<p>|<br *\/?'.'>/', "\n", $post_text);
		$post_text = trim($post_text);
		return $post_text;
	}

	static protected function find_icon_names_in_post($post_imgs, $post_text)
	{
		$images = array();
		$name_side = null; // -1 == left, 1 == right

		foreach ($post_imgs as $img) {
			$img = pq($img);
			$lines = self::lines_from_text($post_text);
			$image_line_number = self::line_number_of_image($img, $lines);
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

			if (!$name && $previous_line) {
				$name = self::find_icon_name_in_string(self::strip_html($previous_line));
			}

			if ($name) {
				$name = trim($name, '- ');
			}

			$images[$img->attr('src')] = $name;
		}

		return $images;
	}

	static protected function find_icon_name_in_string($maybe_name)
	{
		$name = null;
		$w = self::WORD_REGEXP;
		$ws = self::WORDS_REGEXP;

		$s = array();
		preg_match("/here'?s an? ($w) (:?icon)?|here are some ($w)|($w) icons|($w) alt|($w):|^($w)$/iu", $maybe_name, $s);
		# Capture indexes         1                            2    3          4        5      6

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

	static protected function strip_html($name)
	{
		return trim(preg_replace('/<\/?[^>]*>/', '', $name));
	}

	static protected function lines_from_text($text)
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

	static protected function line_number_of_image($img, $lines)
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
?>
