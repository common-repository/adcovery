<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.adcovery.com
 * @since      1.0.0
 *
 * @package    Adcovery
 * @subpackage Adcovery/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Adcovery
 * @subpackage Adcovery/includes
 * @author     Adcovery <contact@adcovery.com>
 */
class Adcovery_Deactivator {

	/**
	 * Clears any scheduled cron
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'wp_adcovery_cron' );
	}

}
