<?php
/**
 * !Database icon-harvester
 * !Table icons
 * !BelongsTo theme, Key: theme_id
 * !BelongsTo app, Key: app_id
 * !BelongsTo artist, Key: artist_id
 */
class Icon extends Model {
	/** !Column PrimaryKey, Integer, AutoIncrement */
	public $id;

	/** !Column Integer */
	public $app_id;

	/** !Column Integer */
	public $theme_id;

	/** !Column Integer */
	public $artist_id;

	/** !Column String */
	public $src;

}
?>
