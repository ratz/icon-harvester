<?php
require_once 'common.php';

$return_json = false;

if (!isset($_GET['id'])) {
	die('No Theme ID specified!');
}

$id = (int) $_GET['id'];
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

$html = download("http://macthemes2.net/forum/viewtopic.php?id=$id&p=$page");
$xml = parse_html($html);

$posts = $xml->xpath('//div[@id="punviewtopic"]//div[contains(@class, "blockpost")]');
$icons = array();

$theme_title = $xml->xpath('//head/title');
$theme_title = strip_tag_prefix((string) $theme_title[0]);
$theme_author = false;

foreach ($posts as $xpost) {
	$full_post = simplexml_load_string($xpost->asXML());
	$author = $full_post->xpath('//div[@class="postleft"]//a');
	$author = (string) $author[0];

	if (!$theme_author && strpos($full_post['class'], 'firstpost') !== false) {
		$theme_author = $author;
	}

	$post = $full_post->xpath('//div[@class="postmsg"][1]');
	$post = simplexml_load_string($post[0]->asXML());

	$post_icons = $post->xpath('//img[@class="postimg"]');

	if (empty($post_icons)) {
		continue;
	}

	$post_text = $post->xpath('*');
	$post_edit = $post->xpath('div[@class="postedit"]');
	var_dump($post_edit);

	$post_text = str_replace($post_edit, '', $post_text);
	#gsub(postedit, '').strip.
		#gsub('</p>\s+<p>', "\n").gsub(/^<p>|<\/p>$/, '').gsub(/<br *\/?X>/, "\n")

	var_dump($post_icons);
	var_dump($post_text[0]->asXml());

	/*
	$icons[] = array(
		'app' => $app,
		'id' => substr($a['href'], $viewtopic_length),
		'author' => $author,
	);
	 */
}

var_dump($icons);

/*
if ($return_json) {
	header('Content-type: application/json');
	echo json_encode($themes);
} else {
	echo '<ul>';
	foreach ($themes as $theme) {
		echo '<li><a href="theme.php?id='.$theme['id'].'">'.$theme['name'].'</a> by '.$theme['author'].'</li>';
	}
	echo '</ul>';
}
 */
?>
