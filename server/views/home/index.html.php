<?php
Layout::extend('layouts/master');
$title = 'Home';
?>
<ul>
<?php
// This is a really slow way to generate navigation.
// You probably should not use this in a real app. Thx -Kris
Library::import('recess.lang.Inflector');
$app = $controller->application();
$controllers = $app->listControllers();
foreach($controllers as $controllerClass):
	$title = Inflector::toEnglish(str_replace('Controller','',$controllerClass));
?>
	<li><?php echo Html::anchor(Url::action($controllerClass . '::index'), $title)?></li>
<?php endforeach; ?>
</ul>
