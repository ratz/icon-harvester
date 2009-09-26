<?php
Layout::extend('layouts/theme');
$title = 'Import';
?>

<?php if(isset($flash)): ?>
	<div class="error">
	<?php echo $flash; ?>
	</div>
<?php endif; ?>

<?php foreach($themes as $theme): ?>
	<?php var_dump($theme); ?>
	<hr />
<?php endforeach; ?>
