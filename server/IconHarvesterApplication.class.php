<?php
Library::import('recess.framework.Application');

class IconHarvesterApplication extends Application {
	public function __construct() {

		$this->name = 'IconHarvester';

		$this->viewsDir = $_ENV['dir.apps'] . 'iconHarvester/views/';

		$this->assetUrl = $_ENV['url.assetbase'] . 'apps/iconHarvester/public/';

		$this->modelsPrefix = 'iconHarvester.models.';

		$this->controllersPrefix = 'iconHarvester.controllers.';

		$this->routingPrefix = 'harvester/';

	}
}

require_once 'common.php';
?>
