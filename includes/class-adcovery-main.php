<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.adcovery.com
 * @since      1.0.0
 *
 * @package    Adcovery
 * @subpackage Adcovery/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Adcovery
 * @subpackage Adcovery/includes
 * @author     Adcovery <contact@adcovery.com>
 */
class AdcoveryMain {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;


	/**
	 * Defines the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct() {

		if ( defined( 'ADCOVERY_VERSION' ) ) {
			$this->version = ADCOVERY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'adcovery';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Loads the required dependencies for this plugin.
	 * Creates an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 */
	private function load_dependencies() {

		//The class responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-adcovery-loader.php';

		//The class responsible for defining internationalization functionality of the plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-adcovery-i18n.php';

		//The class responsible for defining all actions that occur in the admin area.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-adcovery-admin.php';

		//The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-adcovery-public.php';

		$this->loader = new Adcovery_Loader();

	}

	/**
	 * Defines the locale for this plugin for internationalization.
	 *
	 * Uses the Adcovery_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	private function set_locale() {

		$plugin_i18n = new Adcovery_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Registers all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Adcovery_Admin( $this->get_plugin_name(), $this->get_version() );

		//Applies CSS
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		//Adds settings option on plugin list page
		$this->loader->add_action( 'plugin_action_links_' . ADCOVERY_PLUGIN_BASENAME, $plugin_admin, 'plugin_action_links');

		//Adds a submenu option under Settings
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'on_admin_menu' );

		//Saves settings
		$this->loader->add_action( 'admin_post_save_adcovery_settings', $plugin_admin, 'on_save_changes' );

	}

	/**
	 * Registers all of the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks() {

		$plugin_public = new Adcovery_Public();

		//Inserts JS
		$this->loader->add_action( 'wp_footer', $plugin_public, 'insert_init_js' );

		//Sends 404s
		$this->loader->add_action( 'template_redirect', $plugin_public, 'proxy' );

	}

	/**
	 * Runs the loader to execute all of the hooks with WordPress.
	 */
	public function run() {

		$this->loader->run();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {

		return $this->plugin_name;

	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 */
	public function get_loader() {

		return $this->loader;

	}

	/**
	 * Retrieve the version number of the plugin.
	 */
	public function get_version() {

		return $this->version;

	}


}
