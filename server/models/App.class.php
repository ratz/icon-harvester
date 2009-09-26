<?php
/**
 * !Database icon-harvester
 * !Table apps
 * !HasMany icons, Key: app_id
 */
class App extends Model {
	/** !Column PrimaryKey, Integer, AutoIncrement */
	public $id;

	/** !Column String */
	public $name;

}
?>
