<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.adcovery.com
 * @since      1.0.0
 *
 * @package    Adcovery
 * @subpackage Adcovery/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Adcovery
 * @subpackage Adcovery/admin
 * @author     Adcovery <contact@adcovery.com>
 */
class Adcovery_Admin extends Adcovery {

	/**
	 * The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param $plugin_name
	 * @param $version
	 */
	public function __construct( $plugin_name, $version ) {

		parent::__construct();
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/adcovery-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Adds a link to the plugin settings from the Plugins List page
	 *
	 * @param $links
	 * @return array
	 */
	public function plugin_action_links($links) {

		$action_links = array(
			'settings' => 
                '<a href="'
                . esc_url_raw(admin_url( 'options-general.php?page=' . ADCOVERY_PLUGIN_ADMIN_PAGE ) ) .
                '" title="'
                . esc_attr( __( 'View Settings', 'adcovery') ) .
                '">'
                . esc_attr( __( 'Settings', 'adcovery' ) ) .
                '</a>',
		);
		return array_merge( $action_links, $links );

	}


	/**
	 * Adds a submenu section in Settings
	 */
	public function on_admin_menu() {

		add_submenu_page(
			'options-general.php',
			__( 'Adcovery', 'adcovery' ),
			__( 'Adcovery', 'adcovery' ),
			'manage_options',
			ADCOVERY_PLUGIN_ADMIN_PAGE,
			array( $this, 'on_show_page' )
		);

	}

	/**
	 * Displays Plugin's Settings
	 */
	public function on_show_page() {

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied' ) );
		}

		//Returns the Admin view
		require_once 'partials/adcovery-admin-display.php';

	}

	/**
	 * Processes and saves form inputs
	 */
	public function on_save_changes() {

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied' ) );
		}
		
		check_admin_referer( 'adcovery-credentials-nonce' );

		//Checkbox value enabled/disabled default value
		$enabled = 0;

		if (array_key_exists( 'enabled', $_POST ) && sanitize_text_field( $_POST['enabled'] == '1' ) ) {
			$enabled = 1;
		}

		//Update values in database
		Adcovery_Options::update_options( [
			'website_id' => sanitize_text_field( $_POST['website_id'] ),
			'api_key' => sanitize_text_field( $_POST['api_key'] ),
			'enabled' => $enabled,
		]);

		//Success default value
		$success = 1;

		//If enabling, refresh ad server
		if ($enabled == 1) {
			$success = (int) $this->refresh_ad_server( $update_method = 'manual' );
		}

		//If enabling or disabling, make sure cache gets cleared
		$this->cache_clear_all();

		wp_redirect( esc_url_raw( admin_url( 'options-general.php?page=' . ADCOVERY_PLUGIN_ADMIN_PAGE . '&success=' . $success, $this->get_current_scheme() ) ) );

	}

}
