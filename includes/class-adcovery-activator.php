<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.adcovery.com
 * @since      1.0.0
 *
 * @package    Adcovery
 * @subpackage Adcovery/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Adcovery
 * @subpackage Adcovery/includes
 * @author     Adcovery <contact@adcovery.com>
 */
class Adcovery_Activator {

	/**
	 * Clears any scheduled cron
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		//Clear cron
		wp_clear_scheduled_hook( 'wp_adcovery_cron' );

	}

}
