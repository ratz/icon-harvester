<?php
Layout::input($title, 'string');
Layout::input($body, 'Block');
Layout::input($navigation, 'Block', new HtmlBlock());
Layout::input($style, 'Block', new HtmlBlock());
?>
<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<?php
		if(!$style->draw()) {
			Part::draw('parts/style');
		}
		?>
		<title>IconHarvester - <?php echo $title; ?></title>
	</head>
	<body>
	<div id="container">
		<div id="content">
			<?php echo $body; ?>
		</div>
		<div id="footer">
			Built by <a href="http://nanotech.nanotechcorp.net/">NanoTech</a>. Themes are owned by those that created them.
		</div>
	</div>
	</body>
</html>
