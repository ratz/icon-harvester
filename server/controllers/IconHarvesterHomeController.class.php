<?php
Library::import('recess.framework.controllers.Controller');

/**
 * !RespondsWith Layouts
 * !Prefix Views: home/, Routes: /
 */
class IconHarvesterHomeController extends Controller {

	/** !Route GET */
	function index() {
		$this->redirect('ThemeController::index');
	}

}
?>
