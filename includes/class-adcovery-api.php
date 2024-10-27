<?php

class Adcovery_API {

	static $instance = false;
	private $is_wp_vip = false;
	public $api_result = null;
	private $api_status;
	private $api_reachable;
	private $api_error_code;
	private $api_error_detail;

	/**
	 * Initialise the class or send the existing instance
	 *
	 * @return Adcovery_API
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new Adcovery_API;
		}

		return self::$instance;

	}

	/**
	 * Set the class' properties.
	 */
	function __construct() {

		$this->api_result       = null;
		$this->api_status       = null;
		$this->api_reachable    = null;
		$this->api_error_code   = null;
		$this->api_error_detail = null;

	}

	/**
	 * Sets boolean for WP VIP host
	 */
	public function set_wp_vip() {

		$this->is_wp_vip = true;

	}

	/**
	 * Returns arguments for API Request
	 *
	 * @param array $custom_args
	 * @return array
	 */
	public function get_api_arguments($custom_args = array()) {

		$default_args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'WordPress/ ' . sanitize_text_field( get_bloginfo( 'url' ) ),
			'blocking'    => true,
			'headers'     => $this->get_auth_headers(),
			'cookies'     => array(),
			'body'        => null,
			'compress'    => false,
			'decompress'  => true,
			'sslverify'   => true,
			'stream'      => false,
			'filename'    => null
		);

		return array_merge( $default_args, $custom_args );


	}

	/**
	 * Checks for SSL and returns protocol + url accordingly
	 *
	 * @param $path
	 * @param bool $use_ssl
	 *
	 * @return string
	 */
	private function get_api_url( $path, $use_ssl = true ) {

		if ( $use_ssl ) {
			return 'https://' . ADCOVERY_API_URL . $path;
		}

		return 'http://' . ADCOVERY_API_URL . $path;

	}

	/**
	 * Sets headers for API request
	 */
	private function get_auth_headers() {

		return array(
			'X-WebsiteID'     => sanitize_text_field( Adcovery_Options::get_option( 'website_id' ) ),
			'X-ApiKey'        => sanitize_text_field( Adcovery_Options::get_option( 'api_key' ) ),
			'wp'              => 1,
			'X-PluginVersion' => ADCOVERY_VERSION
		);

	}

	/**
	 * Attempt to reach API
	 */
	public function get_ad_server() {

		//Get response from API call
		$api_response = $this->send_api_request( ADCOVERY_API_PATH );

		//Assign data to API variables according to response
		$this->process_api_response( $api_response );

		if ( $this->api_reachable == true && $this->api_status == 200 ) {
			return true;
		}

		return false;

	}

	/**
	 * Performs the API call
	 *
	 * @param $action
	 * @param array $custom_args
	 *
	 * @return array|mixed|WP_Error
	 */
	protected function send_api_request( $action, $custom_args = array() ) {

		//Prepare arguments for request
		$api_args = $this->get_api_arguments( $custom_args );

		//Get SSL + NoSSL urls
		$api_url_ssl    = $this->get_api_url( $action );
		$api_url_no_ssl = $this->get_api_url( $action, $use_ssl = false );

		//Store both urls in array
		$api_urls_array = [ $api_url_ssl, $api_url_no_ssl ];

		//Perform API call
		if ( $this->is_wp_vip ) {
			$api_response = vip_safe_wp_remote_get( $api_url_ssl, $fallback_value = '', $threshold = 3, $timeout = 2,
				$retry = 20, $api_args );
		} else {
			$api_response = $this->make_api_request( $api_urls_array, $api_args, 0, 1 );
		}

		return $api_response;

	}

	/**
	 * Recursive function to try the following:
	 *
	 * Attempt 1: SSL + safe remote
	 * Attempt 2: No SSL + safe remote
	 * Attempt 3: SSL + remote
	 * Attempt 4: No SSL + remote
	 *
	 * @param $urls_array
	 * @param $args
	 * @param $index
	 * @param $attempt
	 *
	 * @return array|WP_Error|null
	 */
	private function make_api_request( $urls_array, $args, $index, $attempt ) {

		$response = null;

		//Reset index after 2 attempts
		if ( $index === 2 && $attempt === 3 ) {
			$index = 0;
		}

		//1st and 2nd attempt
		if ( $attempt === 1 || $attempt === 2 ) {
			$response = wp_safe_remote_get( $urls_array[ $index ], $args );
		}

		//3rd and 4th attempt
		if ( $attempt === 3 || $attempt === 4 ) {
			$response = wp_remote_get( $urls_array[ $index ], $args );
		}

		//Repeat function if no response and increment index + attempt
		if ( ! is_array( $response ) && $attempt <= 4) {
			return $this->make_api_request( $urls_array, $args, $index + 1, $attempt + 1 );
		}

		return $response;

	}

	/**
	 * Checks API response and sets the variables accordingly
	 *
	 * @param $response
	 */
	protected function process_api_response( $response ) {

		//Retrieve and decode response
		$response_body = wp_remote_retrieve_body( $response );
		$result        = json_decode( $response_body );

		//Default value for result
		$this->api_reachable = false;

		if ( $result != null ) {
			$this->api_reachable = true;

			if ( property_exists( $result, 'error' ) ) {
				$this->api_status       = sanitize_text_field( $result->error->status );
				$this->api_error_code   = sanitize_text_field( $result->error->code );
				$this->api_error_detail = sanitize_text_field( $result->error->detail );
			} else {
				$this->api_status = 200;
				$this->api_result = $result;
			}
		}

	}

	/**
	 * Returns error message
	 */
	public function get_full_error_msg() {

		$api_reachable = $this->api_reachable ? __( 'Yes', 'adcovery' ) : __( 'No', 'adcovery' );

		$msg = sprintf( __( 'API Reachable: %s, ', 'adcovery' ), $api_reachable );
		$msg .= sprintf( __( 'API Status: %s, ', 'adcovery' ), $this->api_status );
		$msg .= sprintf( __( 'API Error Code: %s, ', 'adcovery' ), $this->api_error_code );
		$msg .= sprintf( __( 'API Error Detail: %s', 'adcovery' ), $this->api_error_detail );

		return $msg;

	}

}
