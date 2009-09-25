<?php
require_once 'common.php';

$return_json = false;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

$html = download("http://macthemes2.net/forum/viewforum.php?id=24&p=$page");
$doc = phpQuery::newDocument($html);

$rows = $doc['#punviewforum table tr > td.tcl']->parent();
$themes = array();
$viewtopic_length = strlen('viewtopic.php?id=');

foreach ($rows as $row) {
	$row = pq($row);
	$a = $row['a:first'][0];
	$name = $a->text();

	if (preg_match('/\[(theme|contest|contest theme)\]/iu', $name)) {
		$author = $row['td.tc4'][0]->text();

		$byPos = strrpos($name, ' by ');
		if ($byPos !== false) {
			$name = substr($name, 0, $byPos);
		}

		$name = strip_tag_prefix($name);

		$themes[] = array(
			'name' => $name,
			'id' => substr($a->attr('href'), $viewtopic_length),
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
