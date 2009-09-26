<?php
/**
 * !Database icon-harvester
 * !Table themes
 * !HasMany icons, Key: theme_id
 */
class Theme extends Model {
	/** !Column PrimaryKey, Integer, AutoIncrement */
	public $id;

	/** !Column Integer */
	public $topic_id;

	/** !Column Integer */
	public $processed_pages;

	/** !Column String */
	public $name;

	/** !Column String */
	public $artist;

	/** !Column Timestamp */
	public $last_scan_time;

}
?>
