<?php
error_reporting(E_ALL ^ E_STRICT);

// Set universal encoding and timezone defaults.
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
date_default_timezone_set('UTC');
ob_start('mb_output_handler');

function download($url) {
	$hash = sha1($url);
	$cache_file = "cache/$hash.txt";

	if (file_exists($cache_file)) {
		return file_get_contents($cache_file);
	}

	$curl_options = array(
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_HEADER			=> false
	);

	$ch = curl_init($url);
	curl_setopt_array($ch, $curl_options);
	$data = curl_exec($ch);
	file_put_contents($cache_file, $data);
	return $data;
}

function parse_html($html) {
	$doc = new DOMDocument();
	$doc->strictErrorChecking = false;
	$doc->loadHTML($html);
	return simplexml_import_dom($doc);
}

function strip_tag_prefix($name) {
	return trim(substr($name, strpos($name, ']') + 1));
}

require 'phpQuery.php';
?>
