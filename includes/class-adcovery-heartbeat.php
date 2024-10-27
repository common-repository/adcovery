<?php

class Adcovery_Heartbeat extends Adcovery {

	private static $instance = false;

	private $cron_schedule_name = 'adcovery_interval';
	private $cron_name = 'adcovery_heartbeat';

	/**
	 * Initialise the class or send the existing instance
	 *
	 * @return Adcovery_Heartbeat
	 */
	public static function init() {

		if ( ! self::$instance ) {
			self::$instance = new Adcovery_Heartbeat;
		}

		return self::$instance;

	}

	/**
	 * Set the class' hooks and sets up the cron.
	 */
	public function __construct() {

		parent::__construct();

		// Make sure our own hourly timer is present
		add_filter( 'cron_schedules', array( $this, 'adcovery_schedule_filter' ) );

		$this->setup_cron();

		// Schedule the task
		add_action( $this->cron_name, array( $this, 'cron_exec' ) );

	}

	/**
	 * Schedules the cron
	 */
	public function setup_cron() {

		if ( ! wp_next_scheduled( $this->cron_name ) ) {
			wp_schedule_event( time(), $this->cron_schedule_name, $this->cron_name );
		}

	}

	/**
	 * Sets and returns the schedule filters
	 *
	 * @param $schedules
	 *
	 * @return
	 */
	public function adcovery_schedule_filter( $schedules ) {

		$schedules[ $this->cron_schedule_name ] = array(
			'interval' => 3600,
			'display'  => __( 'Custom Interval' )
		);

		return $schedules;

	}

	/**
	 * Cron execution of the refresh + update of last heartbeat in database + cache clear
	 */
	public function cron_exec() {

		//Make sure this doesn't happen more than once per hour even if called more often
		$last = ( int ) Adcovery_Options::get_option( 'last_heartbeat' );

		if ( $last && ( $last + HOUR_IN_SECONDS - MINUTE_IN_SECONDS * 10 > time() ) ) {
			return;
		}

		//Update the last heartbeat in database
		Adcovery_Options::update_option( 'last_heartbeat', time() );

		//Ad server refresh + cache clear
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$this->refresh_ad_server( $update_method = 'cron' );
			$this->cache_clear_all();
		} else {
			wp_die( __( 'Access denied' ) );
		}

	}

	/**
	 * Manual refresh + update of last heartbeat in database + cache clear
	 * returns boolean depending on refresh processed or not
	 */
	public function push_update() {

		//Make sure the update hasn't happened in the last minute
		$last = ( int ) Adcovery_Options::get_option( 'last_heartbeat' );

		if ( $last && ( $last + MINUTE_IN_SECONDS > time() ) ) {
			return false;
		}

		//Update the last heartbeat in database
		Adcovery_Options::update_option( 'last_heartbeat', time() );

		//Ad server refresh + cache clear
		$this->refresh_ad_server( $update_method = 'push' );
		$this->cache_clear_all();

		return true;

	}

	/**
	 * Deactivates Cron
	 */
	public function deactivate() {

		$timestamp = wp_next_scheduled( $this->cron_name );
		wp_unschedule_event( $timestamp, $this->cron_name );

	}

}
