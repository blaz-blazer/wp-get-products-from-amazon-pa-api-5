<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           WP_GPFA
 *
 * @wordpress-plugin
 * Plugin Name:       WP Get Products From Amazon PA API 5
 * Plugin URI:        https://blazzdev.com/wp-get-products-from-amazon-pa-api-5/
 * Description:       Easily add products and their details from Amazon PA API 5 to your website
 * Version:           1.0.0
 * Author:            Blaz K.
 * Author URI:        https://blazzdev.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-gpfa
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP_GPFA_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-gpfa-activator.php
 */
function activate_wp_gpfa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-gpfa-activator.php';
	WP_GPFA_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-gpfa-deactivator.php
 */
function deactivate_wp_gpfa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-gpfa-deactivator.php';
	WP_GPFA_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_gpfa' );
register_deactivation_hook( __FILE__, 'deactivate_wp_gpfa' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-gpfa.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_gpfa() {

	$plugin = new WP_GPFA();
	$plugin->run();

}
run_wp_gpfa();
