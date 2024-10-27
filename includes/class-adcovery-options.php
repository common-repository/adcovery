<?php

class Adcovery_Options {

	/**
	 * Returns an array of options to be stored locally.
	 *
	 * @return string[]
	 */
	public static function get_option_names() {

		return array(
			'website_id',                   // (int)    The Website ID.
			'api_key',                      // (string) The Website's API Key.
			'enabled',                      // (bool)   Whether the whole plugin is enabled or not.
			'last_heartbeat',               // (int)    The timestamp of the last heartbeat that fired.
			'last_error_msg',               // (string) Last error message, if any.
			'last_update_method',           // (string) manual or cron.
			'last_update_at',               // (string) manual or cron.
			'init_js',                      // (string) ad_server object
			'api_url',                      // (string) api url
			'ad_server_url'                 // (string) ad server url
		);
	}

	/**
	 * Checks options names.
	 *
	 * @param $name
	 * @return bool
	 */
	public static function is_valid( $name ) {

		if ( in_array( $name, self::get_option_names() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the requested option.  Looks in adcovery_options.
	 *
	 * @param string $name Option name
	 * @param mixed $default (optional)
	 * @return false|mixed|void
	 */
	public static function get_option( $name, $default = false ) {

		if ( self::is_valid( $name ) ) {
			return get_option( ADCOVERY_PLUGIN_PREFIX . '_' . $name, $default );
		}

		trigger_error( sprintf( __( 'Invalid Adcovery Resilience option name: %s' ), $name ), E_USER_WARNING );

		return $default;
	}

	/**
	 * Updates the single given option.  Updates adcovery_options.
	 *
	 * @param string $name Option name
	 * @param mixed $value Option value
	 * @return bool
	 */
	public static function update_option( $name, $value ) {

		if ( self::is_valid( $name ) ) {
			return update_option( ADCOVERY_PLUGIN_PREFIX . '_' . $name, $value );
		}

		trigger_error( sprintf( __( 'Invalid Adcovery Resilience option name: %s', 'adcovery' ), $name ), E_USER_WARNING );

		return false;
	}

	/**
	 * Updates the multiple given options.  Updates adcovery_options.
	 *
	 * @param array $array array( option name => option value, ... )
	 * @return bool
	 */
	public static function update_options( $array ) {

		$result = true;
		$names = array_keys( $array );

		$unknown_names = array_diff( $names, self::get_option_names() );

		foreach ( $unknown_names as $unknown_name ) {
			trigger_error( sprintf( __( 'Invalid Adcovery option name: %s', 'adcovery' ), $unknown_name ), E_USER_WARNING );
			unset( $array[ $unknown_name ] );
			$result = false;
		}

		$names = array_diff( $names, $unknown_names );

		foreach ( $names as $name ) {
			$result = self::update_option( $name, $array[ $name ] ) && $result;
		}

		return $result;
	}

	/**
	 * Deletes the given option.  May be passed multiple option names as an array.
	 * Updates adcovery_options.
	 *
	 * @param string|array $names
	 * @return bool
	 */
	public static function delete_option( $names ) {

		$result = true;
		$names  = ( array ) $names;

		foreach ( $names as $name ) {
			if ( ! self::is_valid( $name ) ) {
				trigger_error( sprintf( __('Invalid Adcovery option names: %s', 'adcovery' ), print_r( $names, 1 ) ), E_USER_WARNING );
				$result = false;
			}
			$result = delete_option( 'adcovery_' . $name ) && $result;
		}

		return $result;

	}

}
