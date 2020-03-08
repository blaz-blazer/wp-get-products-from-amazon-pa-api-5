<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_GPFA
 * @subpackage WP_GPFA/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_GPFA
 * @subpackage WP_GPFA/includes
 * @author     Blaz K. info@blazzdev.com
 */
class WP_GPFA_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if( ! get_option( 'wp_gpfa' ) ) { // first time use
			// create a table for cache
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix . 'gpfa_products';

			$sql = "CREATE TABLE $table_name (
				product tinytext NOT NULL,
				title tinytext NOT NULL,
				image tinytext NOT NULL,
				features mediumtext,
				link tinytext NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		// schedule cron job
		if ( ! wp_next_scheduled( 'gpfa_cron_refresh_cache' ) ) {
				wp_schedule_event( time(), 'daily', 'gpfa_cron_refresh_cache' );
		}

		// add version to db for update purposes
		add_option( 'wp_gpfa', WP_GPFA_VERSION );

	}

}
