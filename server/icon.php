<?php
require_once 'common.php';

class Icon
{
	public $name;
	public $src;
	public $artist;

	public $maybe_names = array();

	function __construct($name, $src, $artist)
	{
		$this->name   = $name;
		$this->src    = $src;
		$this->artist = $artist;
	}
}
?>
