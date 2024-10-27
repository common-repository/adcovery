<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.adcovery.com
 * @since      1.0.0
 *
 * @package    Adcovery
 * @subpackage Adcovery/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Adcovery
 * @subpackage Adcovery/public
 * @author     Adcovery <contact@adcovery.com>
 */
class Adcovery_Public {

	/**
	 * @var false
	 */
	private $has_run;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->has_run = false;

	}

	/**
	 * Returns arguments for the proxy
	 *
	 * @param array $custom_args
	 * @return array
	 */
	public function get_proxy_arguments( $custom_args = array() ) {

		$default_args = array(
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.1',
			'user-agent'  => 'WordPress/ ' . sanitize_text_field( get_bloginfo( 'url' ) ),
			'blocking'    => true,
			'headers'     => array(
				'X-Forwarded-For'  => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ),
				'X-Forwarded-Host' => sanitize_text_field( $_SERVER['HTTP_HOST'] ),
			),
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
	 * Sends HTTP headers if proxy response is successful
	 *
	 * @param $response
	 */
	public function send_http_header ( $response ) {

		if ( $response["response"]['code'] == 200 ) {

			$GLOBALS['wp_query']->is_404 = false;
			header( "HTTP/1.1 200 OK" );

			foreach ( $response['headers'] as $key => $value ) {
				header( $key . ': ' . $value );
			}

			echo $response['body'];
			exit();

		}

	}

	/**
	 * Inserts js script.
	 *
	 */
	public function insert_init_js() {

		if ( $this->has_run ) {
			return;
		}

		$this->has_run = true;

		//Retrieve value stored in key 'exact'
		$push = get_query_var( 'exact', '' );

		if ( strstr( $push, 'adcoverypush' ) ) {

			//Initialise Heartbeat
			$heartbeat = Adcovery_Heartbeat::init();

			//Manual update
			$result = $heartbeat->push_update();

			//Add message depending on result
			$result_msg  = $result ? __( 'yes', 'adcovery' ) : __( 'no', 'adcovery' );

			//Add error message if any
			$error_msg = $this->get_error_msg();

			//$result and $error_message replace the 2 %s
			$msg = sprintf( __( 'Adcovery push, updated: %s. %s', 'adcovery' ), $result_msg, $error_msg  );

			//Display message as comment in wp_footer
			echo "\n\n<!-- " . esc_html( $msg ) . " -->\n\n";
		}

		$init_js = Adcovery_Options::get_option( 'init_js' );

		//Insert init_js
		if ( ! empty( $init_js ) && Adcovery_Options::get_option( 'enabled' ) == 1 ) {
			echo $init_js . "\n";
		}

	}

	/**
	 * Catches 404 urls and sends them to the Ad Server
	 */
	public function proxy() {

		global $wp_query;

		//Get URI
		$uri = $_SERVER['REQUEST_URI'];

		//Remove the leading slash as adServer URL already has one
		if(strpos( $uri, '/') === 0) {
			$uri = preg_replace('/\//', '', $uri, 1);
		}

		$url =  sanitize_text_field( Adcovery_Options::get_option( 'ad_server_url' ) . $uri );

		//Catches 404s
		if ( $wp_query->is_404 && preg_match( '/(.*\.?(|jpg|jpeg|gif|png|js|css))/i', $uri ) ) {

			//Prepare default arguments
			$proxy_args = $this->get_proxy_arguments();

			//Sends 404s to Ad server
			$proxy_response = wp_remote_get( $url, $proxy_args );

			//Ends exec of script if Wordpress Error encountered
			if ( is_wp_error( $proxy_response ) ) {
				return false;
			}

			//Update http header if success
			$this->send_http_header( $proxy_response );

		}

		return false;

	}

	/**
	 * Returns a full error message if any.
	 *
	 * @return string
	 */
	public function get_error_msg() {

		//Retrieve last error message if any
		$last_error_msg = Adcovery_Options::get_option( 'last_error_msg' );

		//Initialize error message string
		$error_msg = '';

		//Insert last error message if any
		if ( $last_error_msg != '' ) {
			$error_msg = esc_html( sprintf( __( 'Last error msg: %s', 'adcovery' ), $last_error_msg ) );
		}

		return $error_msg;

	}

}
