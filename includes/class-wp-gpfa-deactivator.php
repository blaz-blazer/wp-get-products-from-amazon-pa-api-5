<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_GPFA
 * @subpackage WP_GPFA/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_GPFA
 * @subpackage WP_GPFA/includes
 * @author     Blaz K. info@blazzdev.com
 */
class WP_GPFA_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// deregister cron / wp schedule event
		wp_clear_scheduled_hook( 'gpfa_cron_refresh_cache' );
	}

}
