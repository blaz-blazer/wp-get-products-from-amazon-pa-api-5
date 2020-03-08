<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_GPFA
 * @subpackage WP_GPFA/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_GPFA
 * @subpackage WP_GPFA/admin
 * @author     Blaz K. info@blazzdev.com
 */
class WP_GPFA_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wp_gpfa    The ID of this plugin.
	 */
	private $wp_gpfa;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wp_gpfa       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wp_gpfa, $version ) {

		$this->wp_gpfa = $wp_gpfa;
		$this->version = $version;

	}

	// Enqueue styles
	public function enqueue_styles() {
		wp_enqueue_style( $this->wp_gpfa, plugin_dir_url( __FILE__ ) . 'css/wp-gpfa-admin.css', array(), $this->version, 'all' );
	}

	public function settings_page() {
		add_options_page( 'WP GPFA', 'WP Get Products From Amazon API', 'manage_options', 'wp-gpfa', array( $this, 'settings_display' ) );
	}

	public function settings_init() {
		register_setting( 'wp_gpfa', 'wp_gpfa_settings', array( 'sanitize_callback' => array( $this, 'validate_options' ) ) );

    add_settings_section(
        'wp_gpfa_section',
        __( 'Basic Settings', 'wp-gpfa' ),
        array( $this, 'wp_gpfa_settings_callback' ),
        'wp_gpfa'
    );

		add_settings_field(
        'wp_gpfa_access_key',
        __( 'Amazon PA API Access Key', 'wp-gpfa' ),
        array( $this, 'wp_gpfa_access_key_render' ),
        'wp_gpfa',
        'wp_gpfa_section'
    );

		add_settings_field(
        'wp_gpfa_secret_key',
        __( 'Amazon PA API Secret Key', 'wp-gpfa' ),
        array( $this, 'wp_gpfa_secret_key_render' ),
        'wp_gpfa',
        'wp_gpfa_section'
    );

		add_settings_field(
        'wp_gpfa_tag',
        __( 'Amazon Associates Tag', 'wp-gpfa' ),
        array( $this, 'wp_gpfa_tag_render' ),
        'wp_gpfa',
        'wp_gpfa_section'
    );

		add_settings_field(
        'wp_gpfa_cache',
        __( 'Enable Smart Cache', 'wp-gpfa' ),
        array( $this, 'wp_gpfa_cache_render' ),
        'wp_gpfa',
        'wp_gpfa_section'
    );

		add_settings_field(
        'wp_gpfa_cache_refresh',
        __( 'Refresh Cache', 'wp-gpfa' ),
        array( $this, 'wp_gpfa_cache_refresh_render' ),
        'wp_gpfa',
        'wp_gpfa_section'
    );

		add_settings_field(
        'wp_gpfa_cache_clear',
        __( 'Clear Cache', 'wp-gpfa' ),
        array( $this, 'wp_gpfa_cache_clear_render' ),
        'wp_gpfa',
        'wp_gpfa_section'
    );

	}

	public function wp_gpfa_access_key_render() {
    $options = get_option( 'wp_gpfa_settings' );
    ?>
    <input type='text' name='wp_gpfa_settings[wp_gpfa_access_key]' value='<?php echo $options['wp_gpfa_access_key']; ?>'>
    <?php
	}

	public function wp_gpfa_secret_key_render() {
    $options = get_option( 'wp_gpfa_settings' );
    ?>
    <input type='text' name='wp_gpfa_settings[wp_gpfa_secret_key]' value='<?php echo $options['wp_gpfa_secret_key']; ?>'>
    <?php
	}

	public function wp_gpfa_tag_render() {
		$options = get_option( 'wp_gpfa_settings' );
    ?>
    <input type='text' name='wp_gpfa_settings[wp_gpfa_tag]' value='<?php echo $options['wp_gpfa_tag']; ?>'>
    <?php
	}

	public function wp_gpfa_cache_render() {
		$options = get_option( 'wp_gpfa_settings' );
    ?>
		<select name='wp_gpfa_settings[wp_gpfa_cache]'>
      <option value='1' <?php selected( $options['wp_gpfa_cache'], 1 ); ?>>Yes (Recommended)</option>
      <option value='2' <?php selected( $options['wp_gpfa_cache'], 2 ); ?>>No</option>
    </select>
		<p class="gpda-notice">
			<?php echo __( 'Smart cache saves products information in the database for better performance. Information about products is kept in the database but regularly updated via CRON job. This prevents issues with page speed and throttled requests from Amazon API.', 'wp-gpfa' ); ?>
		</p>
    <?php
	}

	public function wp_gpfa_cache_refresh_render() {
		$options = get_option( 'wp_gpfa_settings' );
    ?>
		<select name='wp_gpfa_settings[wp_gpfa_cache_refresh]'>
      <option value='daily' <?php selected( $options['wp_gpfa_cache_refresh'], 'daily' ); ?>>Daily</option>
      <option value='twicedaily' <?php selected( $options['wp_gpfa_cache_refresh'], 'twicedaily' ); ?>>Twice Daily</option>
			<option value='hourly' <?php selected( $options['wp_gpfa_cache_refresh'], 'hourly' ); ?>>Hourly</option>
    </select>
		<p class="gpda-notice">
			<?php echo __( 'Select how often information about the products should be updated in the database.', 'wp-gpfa' ); ?>
		</p>
    <?php
	}

	public function wp_gpfa_cache_clear_render() {
		$options = get_option( 'wp_gpfa_settings' );
    ?>
		<select name='wp_gpfa_settings[wp_gpfa_cache_clear]'>
      <option value='1' <?php selected( $options['wp_gpfa_cache_clear'], '1' ); ?>>No</option>
      <option value='2' <?php selected( $options['wp_gpfa_cache_clear'], '2' ); ?>>Yes</option>
    </select>
		<p class="gpda-notice">
			<?php echo __( 'If yes, cache will be cleared after you save changes!', 'wp-gpfa' ); ?>
		</p>
    <?php
	}


	public function wp_gpfa_settings_callback() {
		echo __( 'Insert the credentials to Amazon PA API and start using the plugin with the shortcode: [gpfa asin="{PRODUCT ASIN}"]', 'wp-gpfa' );
	}

	public function settings_display() {
		?>
		<h2>WP Get Products From Amazon PA API 5 Settings</h2>
    <form action='options.php' method='post'>

        <?php
        settings_fields( 'wp_gpfa' );
        do_settings_sections( 'wp_gpfa' );
        submit_button();
        ?>

    </form>
		<hr />
		<div class="gpfa-admin-info">
			<a class="gpfa-admin-info__link" target="_blank" href="https://blazzdev.com/documentation/wp-get-products-from-amazon-api/">
				<?php echo __( 'Documentation', 'wp-gpfa' ); ?>
			</a>
			<a class="gpfa-admin-info__link" target="_blank" href="mailto:info@blazzdev.com?subject=WP GPFA Support">
				<?php echo __( 'Support', 'wp-gpfa' ); ?>
			</a>
			<p class="gpfa-admin-info__text">Created by BlazzDev - blazzdev.com</p>
		</div>
    <?php

	}

	function validate_options( $options ) {
		$valid = true;
		foreach ($options as $key => $value) {
			$value = sanitize_text_field( $value );
			if( !$value ) {
				$valid = false;
			}
			$options[$key] = $value;
		}

		if( $options['wp_gpfa_cache_clear'] == 2 ) {
			$this->clear_cache();
		}

		if( !$valid ) {
			add_settings_error( 'wp_gpfa', 'wp-gpfa-error', __( 'Saving failed. Please fill all fields!', 'wp-gpfa' ), 'error' );
		} else {
			// set cron job
			$cron = $this->set_cron( $options['wp_gpfa_cache_refresh'] );

			if( !$cron ) {
				add_settings_error( 'wp_gpfa', 'wp-gpfa-error', __( 'Failed to schedule refresh cache interval!', 'wp-gpfa' ), 'error' );
			}
		}

		return $options;
	}

	private function set_cron( $interval ) {
		wp_clear_scheduled_hook( 'gpfa_cron_refresh_cache' );
		if ( ! wp_next_scheduled( 'gpfa_cron_refresh_cache' ) ) {
				wp_schedule_event( time(), $interval, 'gpfa_cron_refresh_cache' );
				return true;
		}
		return false;
	}

	private function clear_cache() {
		global $wpdb;
		$table  = $wpdb->prefix . 'gpfa_products';
		$delete = $wpdb->query("TRUNCATE TABLE $table");
	}

}
