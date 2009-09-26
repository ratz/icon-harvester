<?php
error_reporting(E_ALL ^ E_STRICT);

// Set universal encoding and timezone defaults.
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
date_default_timezone_set('UTC');

define('CACHE_EXPIRE', 60*60*3);

function url_cache_file($url) {
	$hash = sha1($url);
	return $_ENV['dir.temp']."url-$hash.txt";
}

function is_cache_valid($file) {
	return (file_exists($file) && filemtime($file) < CACHE_EXPIRE);
}

function download($url) {
	$cache_file = url_cache_file($url);

	if (is_cache_valid($cache_file)) {
		return file_get_contents($cache_file);
	}

	$curl_options = array(
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_HEADER			=> false,
		CURLOPT_FOLLOWLOCATION  => true,
		CURLOPT_MAXREDIRS       => 3,
	);

	$ch = curl_init($url);
	curl_setopt_array($ch, $curl_options);
	$data = curl_exec($ch);
	file_put_contents($cache_file, $data);
	return $data;
}

function strip_tag_prefix($name) {
	return trim(substr($name, strpos($name, ']') + 1));
}

require 'phpQuery.php';
?>
