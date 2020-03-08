<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_GPFA
 * @subpackage WP_GPFA/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WP_GPFA
 * @subpackage WP_GPFA/public
 * @author     Blaz K. info@blazzdev.com
 */
class WP_GPFA_Public {

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
	 * @param      string    $wp_gpfa       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wp_gpfa, $version ) {

		$this->wp_gpfa = $wp_gpfa;
		$this->version = $version;

	}

	private function settings( $key ) {
		$settings = get_option( 'wp_gpfa_settings' );
		if( !is_array($settings) ) {
			return false;
		}
		return $settings[$key];
	}

	// style
	public function enqueue_styles() {
		wp_enqueue_style( $this->wp_gpfa, plugin_dir_url( __FILE__ ) . 'css/wp-gpfa-public.css', array(), $this->version, 'all' );
	}

	// shortcode
	public function register_shortcodes() {
		add_shortcode( 'gpfa', array( $this, 'display_product' ) );
		add_shortcode( 'amazon', array( $this, 'display_product_migration_from_al' ) );
	}

	// Easy Migration from Amazon Link Plugin - https://wordpress.org/plugins/amazon-link/
	public function display_product_migration_from_al( $atts ) {
		$output = array(
			'asin' => false,
			'template' => false,
		);

		if( is_array( $atts ) ) {
			if( array_key_exists( 'asin', $atts ) ) {
				$asin = $atts['asin'];
				$asin = substr( $asin, 0, 10 );
				$output['asin'] = $asin;
			} else {
				foreach( $atts as $att ) {
					$position = strpos($att, 'asin=');
					if( $position ) {
						$asin = substr( $att, 0, $position + 15);
						$asin = substr( $asin, $position + 5 );
						$output['asin'] = $asin;
						break;
					}
				}
			}
		}

		return $this->display_product( $output );
	}

	// display product
	public function display_product( $atts ) {
		$info = shortcode_atts( array(
			'asin' => false,
			'template' => false,
		), $atts );

		$asin = $info['asin'];

		if( ! $asin ) {
			return 'Please enter a valid asin';
		}

		// check that all required info was inserted in the settings
		if( !$this->settings('wp_gpfa_access_key') || !$this->settings('wp_gpfa_secret_key') || !$this->settings('wp_gpfa_tag') ) {
			return 'Could not find access key, secret key and/or associates tag. Please update settings!';
		}

		// get details about the product from cache or amazon API
		$product = $this->get_product_data( $asin );

		// echo '<pre>';
		// print_r($product);
		// echo '</pre>';

		if( ! $product['validProduct'] ) {
			return $product['error'];
		}

		// allow custom templates
		$product_template = locate_template( 'wp-gpfa/basic-template.php' );
		if( ! $product_template ) {
			$product_template = plugin_dir_path( __FILE__ ) . 'templates/basic-template.php';
		}

		// print the template
		ob_start();
		include $product_template;
		return $this->remove_line_breaks( ob_get_clean() );

	}

	// retrieves product data
	private function get_product_data( $asin ) {
		$asin = sanitize_text_field( $asin );
		$staticLink = 'https://www.amazon.com/gp/product/' . $asin . '?ie=UTF8&linkCode=as2&camp=1634&creative=6738&tag=' . $this->settings( 'wp_gpfa_tag' ) . '&creativeASIN=' . $asin;
		$defaultImage = esc_url( plugins_url( 'img/unavailable.jpg', __FILE__ ) );

		$productDetails = array(
			'validProduct' 	=> true,
			'staticLink' 		=> $staticLink, // used if error, so there is still a link
			'img'						=> $defaultImage,
			'features'			=> array(),
			'title' 				=> '',
			'link'  				=> '',
			'lastUpdated'		=> current_time('mysql', false),
			'error'				  => '',
		);

		if( $this->settings('wp_gpfa_cache') == 1 ) { // cache is enabled
			$cacheQuery = $this->cache_query( $asin );
		} else {
			$cacheQuery = false;
		}

		if( $cacheQuery ) { // we use data from cache
			// var_dump('cache query running');
			$cachedProductDetails = $cacheQuery[0];
			// populate array
			$productDetails['img'] = $cachedProductDetails->image;
			$productDetails['title'] = $cachedProductDetails->title;
			$productDetails['link'] = $cachedProductDetails->link;
			$productDetails['staticLink'] = $cachedProductDetails->link;
			$productDetails['features'] = unserialize($cachedProductDetails->features);
			$productDetails['lastUpdated'] = $cachedProductDetails->time;

			return $productDetails;
		}

		// nothing in cache we contact the Amazon API
		$query = $this->amazon_query( array( $asin ), $this->settings( 'wp_gpfa_tag' ), $this->settings( 'wp_gpfa_access_key' ), $this->settings( 'wp_gpfa_secret_key' ));
		// var_dump('API query running');

		// something went wrong, API is supposed to return data in array
		if( ! is_array( $query ) || empty( $query ) ) {
			$productDetails['validProduct'] = false;
			$productDetails['error'] = 'Error retrieving data!';
			return $productDetails;
		}

		// API reported an error - product doesn't exists or such
		if( array_key_exists( 'Errors', $query ) ) {
			$productDetails['error'] = $query['Errors'][0]['Code'];
			return $productDetails;
		}

		// continue and get product info, print it and save it to cache
		$productInfo = $query['ItemsResult']['Items'][0];

		// populate array
		$productDetails['img'] = sanitize_text_field( $productInfo['Images']['Primary']['Large']['URL'] );
		$productDetails['title'] = sanitize_text_field( $productInfo['ItemInfo']['Title']['DisplayValue'] );
		$productDetails['link'] = sanitize_text_field( $productInfo['DetailPageURL'] );

		// check if features exist
		if( array_key_exists( 'Features', $productInfo['ItemInfo'] ) ) {
			if( array_key_exists( 'DisplayValues', $productInfo['ItemInfo']['Features'] ) ) {
				$productDetails['features'] = $productInfo['ItemInfo']['Features']['DisplayValues'];
			}
		}

		// Change static link to api link if api link exists
		if( $productDetails['link'] ) {
			$productDetails['staticLink'] = $productDetails['link'];
		}

		// add to cache
		if( $this->settings('wp_gpfa_cache') == 1 ) { // cache is enabled
			$this->add_product_to_cache( $productDetails, $asin );
		}

		// return product details for shortcode output
		return $productDetails;

	}

	// gets the product from cache
	private function cache_query( $asin ) {
		global $wpdb;
		$wp_gpfa_table = $wpdb->prefix . 'gpfa_products';
		$match = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wp_gpfa_table WHERE product = %s", array( $asin ) ) );

		if ( ! count( $match ) ) {
			return false;
		}

		return $match;
	}

	private function add_product_to_cache( $productDetails, $asin ) {
		if( $productDetails['error'] ) { // error returned, for example product asin not available or reqest throtled - do not cache such requests
			return;
		}

		global $wpdb;
		//table name
		$table_name = $wpdb->prefix . 'gpfa_products';
		//insert into the table
		$wpdb->insert(
			$table_name,
			array(
				'product' 			=> $asin,
				'title' 				=> $productDetails['title'],
				'image' 				=> $productDetails['img'],
				'features' 			=> serialize( $productDetails['features'] ),
				'link' 					=> $productDetails['link'],
				'time' 					=> current_time('mysql', false),
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
		// note that this will alway return false as there are no ids
		return $wpdb->insert_id;
	}

	// gets the product from Amazon API
	private function amazon_query( $asin, $partnerTag, $accessKey, $secretKey) {
		$getItemsRequest = new GetItems();
		$getItemsRequest->PartnerType = 'Associates';
		$getItemsRequest->PartnerTag = $partnerTag;
		$getItemsRequest->ItemIdType = 'ASIN';
		$getItemsRequest->ItemIds = $asin;
		$getItemsRequest->Resources = ["Images.Primary.Large","ItemInfo.Title","ItemInfo.Features"];
		$path = "/paapi5/getitems";
		$payload = json_encode ($getItemsRequest);
		$host = "webservices.amazon.com";
		$region = 'us-east-1';

		if( has_filter( 'gpfa_host' ) ) {
			$host = apply_filters( 'gpfa_host', $host );
		}

		if( has_filter( 'gpfa_region' ) ) {
			$region = apply_filters( 'gpfa_region', $region );
		}

		$awsv4 = new AwsV4 ($accessKey, $secretKey);
		$awsv4->setRegionName($region);
		$awsv4->setServiceName("ProductAdvertisingAPI");
		$awsv4->setPath ($path);
		$awsv4->setPayload ($payload);
		$awsv4->setRequestMethod ("POST");
		$awsv4->addHeader ('content-encoding', 'amz-1.0');
		$awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
		$awsv4->addHeader ('host', $host);
		$awsv4->addHeader ('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems');
		$headers = $awsv4->getHeaders ();

		$url = 'https://'.$host.$path;
		$data = array(
			'method'      => 'POST',
    	'timeout'     => 15,
			'headers'     => $headers,
			'body'				=> $payload
		);

		$request = wp_remote_post( $url, $data );

		// in case of error return false
		if( is_wp_error( $request ) ) {
			return false;
		}

		$data = wp_remote_retrieve_body( $request );

		$data = json_decode( $data, true );

		return $data;

	}

	// remove line breaks from a string to avoid issues with wpautop
	private function remove_line_breaks( $string ) {
		$string = str_replace( array( "\r", "\n" ), '', $string);
		return $string;
	}

	// once product details are stored in cache we update cache in the background so there is no effect on the performance
	public function update_cache_in_the_background() {
		// check that all required info was inserted in the settings
		if( !$this->settings('wp_gpfa_access_key') || !$this->settings('wp_gpfa_secret_key') || !$this->settings('wp_gpfa_tag') ) {
			return;
		}
		// max number of products to be updated at once
		$limit = 10000;
		$interval = 1;

		// allow filter max number of products
		if( has_filter( 'gpfa_max_products' ) ) {
			$limit = apply_filters( 'gpfa_max_products', $limit );
		}

		if( has_filter( 'gpfa_query_interval' ) ) {
			$interval = apply_filters( 'gpfa_query_interval', $interval );
		}

		$report = array(
			'scheduledProducts' => 0,
			'updatedProducts'		=> 0,
			'errors'						=> array(),
		);

		global $wpdb;
		$wp_gpfa_table = $wpdb->prefix . 'gpfa_products';

		$cached_products = $wpdb->get_results( "SELECT * FROM $wp_gpfa_table ORDER BY time ASC LIMIT $limit" );

		if ( ! count( $cached_products ) ) { // nothing to update
			return;
		}

		$report['scheduledProducts'] = count( $cached_products );

		// store asins in array
		$asins = array();

		foreach( $cached_products as $cached_product ) {
			$asins[] = $cached_product->product;
		}
		// create multidimensional array where each array contains max 10 asins as that's the max size of API request
		$asins = array_chunk( $asins, 10 );

		// contact API and do the magic
		$batchIteration = 0;

		foreach( $asins as $asinBatch ) {
			$batchIteration++;
			$productsDetails = $this->amazon_query( $asinBatch, $this->settings( 'wp_gpfa_tag' ), $this->settings( 'wp_gpfa_access_key' ), $this->settings( 'wp_gpfa_secret_key' ) );
			// echo '<pre>';
			// print_r($productsDetails);
			// echo '</pre>';

			// something went wrong, API is supposed to return data in array
			if( ! is_array( $productsDetails ) || empty( $productsDetails ) ) { // something went wrong
				sleep($interval);
				$report['errors'][] = 'Invalid response from API in batch iteration: ' . $batchIteration;
				continue; // go to next iteration
			}

			// let user know about the errors such as not available
			if( array_key_exists( 'Errors', $productsDetails ) ) {
				$errors = $productsDetails['Errors'];
				foreach( $errors as $error) {
					if( array_key_exists( 'Message', $error ) ) {
						$report['errors'][] = $error['Message'];
					} else {
						$report['errors'][] = $error['Code'];
					}
				}
			}

			if( ! array_key_exists( 'ItemsResult', $productsDetails ) ) {
				$report['errors'][] = 'No results in batch iteration: ' . $batchIteration;
				sleep($interval);
				continue; // go to next iteration
			}

			$productsDetails = $productsDetails['ItemsResult'];

			if( ! array_key_exists( 'Items', $productsDetails ) ) {
				$report['errors'][] = 'No items in batch iteration: ' . $batchIteration;
				sleep($interval);
				continue; // go to next iteration
			}

			$productsDetails = $productsDetails['Items'];

			// organize the data and insert in the table
			foreach( $productsDetails as $productDetails ) {
				// create thumb
				$asin = $productDetails['ASIN'];
				$imageURL = sanitize_text_field( $productDetails['Images']['Primary']['Large']['URL'] );
				$title = sanitize_text_field( $productDetails['ItemInfo']['Title']['DisplayValue'] );
				$link = sanitize_text_field( $productDetails['DetailPageURL'] );
				$features = array();

				// check if features exist
				if( array_key_exists( 'Features', $productDetails['ItemInfo'] ) ) {
					if( array_key_exists( 'DisplayValues', $productDetails['ItemInfo']['Features'] ) ) {
						$features = $productDetails['ItemInfo']['Features']['DisplayValues'];
					}
				}


				if( ! $imageURL || ! $title || ! $link || ! $asin ) {
					$report['errors'][] = 'Information missing for product: ' . $asin;
					continue; //something is missing - do not insert
				}

				// we have all data - update the entry
				global $wpdb;
				$wp_gpfa_table = $wpdb->prefix . 'gpfa_products';

				$data = array (
					'title' 		=> $title,
					'image' 		=> $imageURL,
					'features' 	=> serialize( $features ),
					'link'  		=> $link,
					'time'  		=> current_time('mysql', false),
				);

				$where = array (
					'product' => $asin
				);

				$format = array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				);

				$where_format = array(
					'%s'
				);

				$update = $wpdb->update( $wp_gpfa_table, $data, $where, $format, $where_format );

				if( $update ) {
					$report['updatedProducts'] = $report['updatedProducts'] + 1;
				} else {
					$report['errors'][] = 'Failed to insert in DB product: ' . $asin;
				}

			} // end foreach product

			sleep($interval);

		} // end foreach chunk

		// report is generated - allow custom action
		do_action( 'gpfa_after_refresh_cache', $report );

		// error_log(print_r($report, true));

	}

}
