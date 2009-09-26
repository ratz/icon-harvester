<?php
Part::input($form, 'ModelForm');
Part::input($title, 'string');
?>
<?php $form->begin(); ?>
	<fieldset>
		<legend><?php echo $title ?></legend>
		<?php $form->input('id'); ?>		
				<p>
			<label for="<?php echo $form->topic_id->getName(); ?>">Topic Id</label><br />
			<?php $form->input('topic_id'); ?>
		</p>
		<p>
			<label for="<?php echo $form->name->getName(); ?>">Name</label><br />
			<?php $form->input('name'); ?>
		</p>
		<p>
			<label for="<?php echo $form->artist->getName(); ?>">Artist</label><br />
			<?php $form->input('artist'); ?>
		</p>

		<input type="submit" value="Save" />
	</fieldset>
<?php $form->end(); ?>