<?php
Part::input($theme, 'Theme');
?>
<h1><?php echo Html::anchor(Url::action('ThemeController::details', $theme->id), $theme->name) ?> by <?php echo $theme->artist ?></h1>

<ul>
	<?php foreach ($icons as $icon):?>
		<li><?php echo $icon->src;?></li>
	<?php endforeach?>
</ul>
