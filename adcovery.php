<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.adcovery.com
 * @since             1.0.0
 * @package           Adcovery
 *
 * @wordpress-plugin
 * Plugin Name:       Adcovery
 * Description:       Resilient Advertising For Your Website.
 * Version:           1.0.1
 * Author:            Adcovery
 * Author URI:        https://www.adcovery.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       adcovery
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Constants used in plugin */

define( 'ADCOVERY_VERSION', '1.0.1' );
define( 'ADCOVERY_VERSION_TRACE', 'bb' );
define( 'ADCOVERY_URL', plugins_url( '', __FILE__ ) );
define( 'ADCOVERY_PLUGIN_PATH', __DIR__ . '/');
define( 'ADCOVERY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADCOVERY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'ADCOVERY_PLUGIN_PREFIX', 'adcovery' );
define( 'ADCOVERY_PLUGIN_ADMIN_PAGE', 'adcovery_admin_page' );
define( 'ADCOVERY_API_URL', 'api.adcovery.com/v2');
define( 'ADCOVERY_API_PATH', '/get-ad-server' );


/** Includes the php files **/

require_once( ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery-main.php' );
require_once( ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery.php' );
require_once( ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery-options.php' );
require_once( ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery-api.php' );
require_once( ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery-heartbeat.php' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-adcovery-activator.php
 */
function activate_adcovery() {
	require_once ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery-activator.php';
	Adcovery_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-adcovery-deactivator.php
 */
function deactivate_adcovery() {
	require_once ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery-deactivator.php';
	Adcovery_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_adcovery' );
register_deactivation_hook( __FILE__, 'deactivate_adcovery' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
function run_adcovery() {

	Adcovery_Heartbeat::init();

	$plugin = new AdcoveryMain();
	$plugin->run();

}
run_adcovery();
