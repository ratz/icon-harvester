<?php
Layout::extend('layouts/theme');
$title = 'Index';
?>

<h1>IconHarvester</h1>

<?php if(isset($flash)): ?>
	<div class="error">
	<?php echo $flash; ?>
	</div>
<?php endif; ?>

<ul>
<?php foreach($themeSet as $theme): ?>
	<li><?php echo Html::anchor(Url::action('ThemeController::details', $theme->id), $theme->name) ?> by <?php echo $theme->artist ?></li>
<?php endforeach; ?>
</ul>

<?php echo Html::anchor(Url::action('ThemeController::index').'?page='.($page+1), 'Load More'); ?>
