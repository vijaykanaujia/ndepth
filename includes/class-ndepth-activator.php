<?php

/**
 * Fired during plugin activation
 *
 * @link       vijay.thoughtsole.in
 * @since      1.0.0
 *
 * @package    Ndepth
 * @subpackage Ndepth/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ndepth
 * @subpackage Ndepth/includes
 * @author     vijay kanaujia <vijaykanaujia3@gmail.com>
 */
class Ndepth_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		global $wpdb;
		$table_name = "{$wpdb->prefix}ndepth";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			`id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`category_list_tag` varchar(100) NOT NULL,
			`name` text NOT NULL,
			`value` text NULL,
			`parent_id` bigint unsigned NOT NULL,
			`sub_tag` text NULL,
			`image_id` bigint unsigned NULL
		  ) $charset_collate;";

		// echo $sql;
		// die;

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
