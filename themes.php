<?php
require_once 'common.php';

$return_json = false;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

$html = download("http://macthemes2.net/forum/viewforum.php?id=24&p=$page");
$xml = parse_html($html);

$rows = $xml->xpath('//div[@id="punviewforum"]//table//tr/td[@class="tcl"]/..');
$themes = array();
$viewtopic_length = strlen('viewtopic.php?id=');

foreach ($rows as $rsrow) {
	$row = simplexml_load_string($rsrow->asXML());
	$a = $row->xpath('//a[1]');
	$a = $a[0];
	$name = (string) $a;

	if (preg_match('/\[(theme|contest|contest theme)\]/i', $name)) {
		$author = $row->xpath('//td[@class="tc4"]');
		$author = (string) $author[0];

		$byPos = strpos($name, 'by');
		if ($byPos !== false) {
			$name = substr($name, 0, $byPos);
		}

		$name = strip_tag_prefix($name);

		$themes[] = array(
			'name' => $name,
			'id' => substr($a['href'], $viewtopic_length),
			'author' => $author,
		);
	}
}

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
?>
