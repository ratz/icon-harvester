<?php
Layout::extend('layouts/theme');
$title = $theme->name.' by '.$theme->artist;
?>

<h1><?php echo Html::anchor(Url::action('ThemeController::details', $theme->id), $theme->name) ?> by <?php echo $theme->artist ?></h1>

<?php
if ($current_page != $total_pages) {
	echo "<p>Processed page $current_page of $total_pages.</p>";
}
?>

<?php echo Html::anchor(Url::action('ThemeController::index'), 'Back to list of themes') ?>

<ul class="icons">
	<?php foreach ($icons as $icon):
		$app = $icon->app();
		$artist = $icon->artist();
		$icon_name = $app ? $app->name : 'Unknown';
		$artist_name = $artist ? $artist->name : 'Unknown';

		$class = $app ? '' : 'unknown-app';
	?>
	<li><img class="<?php echo $class;?>" src="<?php echo $icon->src;?>" alt="<?php echo $icon_name;?>" title="<?php echo "$icon_name by {$artist_name}";?>" /></li>
	<?php endforeach?>
</ul>
