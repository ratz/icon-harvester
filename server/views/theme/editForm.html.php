<?php 
Layout::extend('layouts/theme');
if(isset($theme->id)) {
	$title = 'Edit Theme #' . $theme->id;
} else {
	$title = 'Create New Theme';
}
$title = $title;
?>

<?php Part::draw('theme/form', $_form, $title) ?>

<?php echo Html::anchor(Url::action('ThemeController::index'), 'Theme List') ?>