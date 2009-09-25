<?php
require_once 'common.php';

$return_json = false;

if (!isset($_GET['id'])) {
	die('No Theme ID specified!');
}

$id = (int) $_GET['id'];
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

$html = download("http://macthemes2.net/forum/viewtopic.php?id=$id&p=$page");
$doc = phpQuery::newDocument($html);

$posts = $doc->find('#punviewtopic .blockpost');
$icons = array();

$theme_title = $doc->find('head title');
$theme_title = strip_tag_prefix((string) $theme_title[0]);
$theme_author = false;

foreach ($posts as $full_post) {
	$full_post = pq($full_post);
	#$full_post = simplexml_load_string($xpost->asXML());
	$author = $full_post['.postleft dt a'];
	$author = $author[0]->text();

	if (!$theme_author && strpos($full_post->attr('class'), 'firstpost') !== false) {
		$theme_author = $author;
	}

	$post = $full_post['.postmsg'][0];
	$post_icons = $post['img.postimg'];

	if (empty($post_icons)) {
		continue;
	}

	$post['blockquote']->remove();

	$post_text = trim($post->html());
	$post_edit = $post->find('.postedit');
	#var_dump($post_edit);

	$post_text = str_replace($post_edit, '', $post_text);
	#gsub(postedit, '').strip.
		#gsub('</p>\s+<p>', "\n").gsub(/^<p>|<\/p>$/, '').gsub(/<br *\/?X>/, "\n")

	echo $post_icons;

	/*
	$icons[] = array(
		'app' => $app,
		'id' => substr($a['href'], $viewtopic_length),
		'author' => $author,
	);
	 */
}

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
