<?php

/**
 * Generic class defining main plugin mechanisms
 */
class Adcovery {

	/**
	 * Determines whether host is WP VIP or not
	 */
	var $host = array(

		'is_wp_vip' => false,

	);

	/**
	 * Cache types
	 */
	var $cache = array(

		'w3_total_cache' => false,
		'super_cache'    => false,
		'fast_cache'     => false,
		'wp_rocket'      => false,

	);

	/**
	 * Performs cache and host checks
	 * upon initialisation
	 */
	public function __construct() {

		$this->do_host_detection();
		$this->do_cache_detection();

	}

	/**
	 * Ad Server refresh
	 * Returns boolean depending on operation success
	 *
	 * @param string $update_method
	 *
	 * @return bool
	 */
	public function refresh_ad_server( $update_method = 'manual' ) {

		//Initialise API class
		$api = Adcovery_API::init();

		//Set WP VIP host
		if ( $this->host['is_wp_vip'] ) {
			$api->set_wp_vip();
		}

		//Perform API call and collect response in API variables
		$success = $api->get_ad_server();

		//Save API Response and return success/failure
		return $this->save_api_response( $success, $api, $update_method );

	}

	/**
	 * Processes API call results - saves any errors and/or data retrieved
	 * in database
	 *
	 * @param $success
	 * @param $api
	 * @param $update_method
	 * @return bool
	 */
	public function save_api_response ( $success, $api, $update_method ) {

		//Save error message and exit if any
		if ( ! $success ) {
			Adcovery_Options::update_option( 'last_error_msg', sanitize_text_field( $api->get_full_error_msg() ) );
			return false;
		}

		//Set default value to the update method if non-existent
		if ( ! in_array( $update_method, array( 'manual', 'cron', 'push' ) ) ) {
			$update_method = 'manual';
		}

		//Save the API result info in database
		Adcovery_Options::update_options( [
			'last_error_msg'     => '',
			'init_js'            => $api->api_result->data->init_js,
			'last_update_at'     => time(),
			'ad_server_url'      => sanitize_text_field( $api->api_result->data->adserver ),
			'last_update_method' => sanitize_text_field( $update_method )
		] );

		return true;
	}

	/**
	 * Checks for VIP hosts and sets the variable accordingly
	 */
	public function do_host_detection() {

		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$this->host['is_wp_vip'] = true;
		}

	}


	/**
	 * Checks for Cache extensions sets the variables accordingly
	 */
	public function do_cache_detection() {

		if ( function_exists( 'w3tc_flush_all' ) ) {
			$this->cache['w3_total_cache'] = true;
		}

		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			$this->cache['super_cache'] = true;
		}

		global $wpfc;

		if ( $wpfc ) {
			$this->cache['fast_cache'] = true;
		}

		if ( function_exists( 'rocket_clean_domain' ) ) {
			$this->cache['wp_rocket'] = true;
		}

	}

	/**
	 * Clears W3 Cache
	 */
	public function cache_clear_w3_total_cache() {

		w3tc_flush_all();

	}

	/**
	 * Clears Super Cache
	 */
	public function cache_clear_super_cache() {

		wp_cache_clear_cache();

	}

	/**
	 * Clears Fastest Cache
	 */
	public function cache_clear_fast_cache() {

		global $wpfc;

		if ( $wpfc ) {
			$wpfc->deleteCache();
		}

	}

	/**
	 * Clears Rocket Cache
	 */
	public function cache_clear_wp_rocket_cache() {

		rocket_clean_domain();

	}

	/**
	 * Clears all cache
	 */
	public function cache_clear_all() {

		//Cache purging is not necessary on wordpress.com hosted sites
		if ( $this->host['is_wp_vip'] ) {
			return;
		}

		if ( $this->cache['w3_total_cache'] ) {
			$this->cache_clear_w3_total_cache();
		}

		if ( $this->cache['super_cache'] ) {
			$this->cache_clear_super_cache();
		}

		if ( $this->cache['fast_cache'] ) {
			$this->cache_clear_fast_cache();
		}

		if ( $this->cache['wp_rocket'] ) {
			$this->cache_clear_wp_rocket_cache();
		}

	}

	/**
	 * Defines current scheme
	 */
	public function get_current_scheme() {

		$scheme = 'http';
		if ( is_ssl() ) {
			$scheme = 'https';
		}

		return $scheme;

	}

}
