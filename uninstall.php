<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://www.adcovery.com
 * @since      1.0.0
 *
 * @package    Adcovery
 */

// If uninstall not called from WordPress, then exit.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) || !WP_UNINSTALL_PLUGIN  || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) )
) {
	status_header( 404 );
	exit;
}

define( 'ADCOVERY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( ADCOVERY_PLUGIN_DIR . 'includes/class-adcovery-options.php' );

Adcovery_Options::delete_option( [
	'website_id',
	'api_key',
	'enabled',
	'last_heartbeat',
	'last_error_msg',
	'last_update_method',
	'last_update_at',
	'init_js',
	'ad_server_url',
	]);
