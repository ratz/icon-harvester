<?php
/**
 * !Database icon-harvester
 * !Table artists
 * !HasMany themes, Key: artist_id
 * !HasMany icons, Key: artist_id
 */
class Artist extends Model {
    /** !Column PrimaryKey, Integer, AutoIncrement */
    public $id;

    /** !Column String */
    public $name;

}
?>
