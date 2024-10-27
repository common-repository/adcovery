<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.adcovery.com
 * @since      1.0.0
 *
 * @package    Adcovery
 * @subpackage Adcovery/admin/partials
 */

?>

<?php

//Sets the strings according to update status
$last_update_at         = (int) get_option( 'adcovery_last_update_at' );
$last_update_at_str     = __( 'never', 'adcovery' );
$last_update_method_str = __( 'none' );

if ( $last_update_at > 0 ) {

	try {
		$dt = new DateTime( get_option( 'timezone_string' ) );
	} catch ( Exception $e ) {
		$dt = new DateTime();
	}

	$dt->setTimestamp( $last_update_at );
	$last_update_at_str     = date_format( $dt, 'Y-m-d H:i:s' );
	$last_update_method_str = get_option( 'adcovery_last_update_method' );
}

?>

<div id="adcovery_notification_message" class="updated below-h2 dismiss hide-if-js"><p></p></div>

<div class="wrap">

    <h2><?php _e( 'Adcovery Plugin', 'adcovery' ); ?></h2>

	<?php if ( array_key_exists( 'success', $_GET ) && $_GET['success'] == '1' && get_option( 'adcovery_enabled' ) ) { ?>

        <div class="updated below-h2" id="">
            <p><?php _e( 'Successfully saved details and refreshed the Adcovery Ad Server.', 'adcovery' ); ?></p>
        </div>

		<?php
	} elseif ( array_key_exists( 'success', $_GET ) && $_GET['success'] == '1' && ! get_option( 'adcovery_enabled' ) ) { ?>

        <div class="updated below-h2" id="">
            <p><?php _e( 'Successfully saved details.', 'adcovery' ); ?></p>
        </div>

		<?php
	} elseif ( array_key_exists( 'success', $_GET ) && $_GET['success'] == '0' || get_option( 'adcovery_last_error_msg' ) != '' ) { ?>

        <p class="adcovery_contact"><?php _e( 'To get your Website ID and API Key, please contact our team: contact@adcovery.com', 'adcovery' ); ?></p>
        <div class="error below-h2" id="">
            <p><?php _e( 'Error: ', 'adcovery' ); ?>
				<?php echo esc_html( get_option( 'adcovery_last_error_msg' ) ); ?>.
            </p>
        </div>

		<?php
	} elseif ( $last_update_at_str === 'never' ) { ?>

        <p class="adcovery_contact"><?php _e( 'To get your Website ID and API Key, please contact our team: contact@adcovery.com', 'adcovery' ); ?></p>
		<?php
	}
	?>

    <div class="adcovery_status_box">

        <p><?php _e( 'Last updated at: ', 'adcovery' ); ?><span><?php
				echo esc_html( $last_update_at_str ) ?></span></p>

        <p><?php _e( 'Last update method: ', 'adcovery' ); ?><span><?php
				echo esc_html( $last_update_method_str ) ?></span></p>

    </div>

    <form action="admin-post.php" method="post">

		<?php wp_nonce_field( 'adcovery-credentials-nonce' ); ?>

        <input type="hidden" name="action" value="save_adcovery_settings"/>

        <div class="adcovery_credentials">

            <label for="website_id"><?php _e( 'Website ID:', 'adcovery' ); ?>
                <input type="text" name="website_id" value="<?php echo esc_html( get_option( 'adcovery_website_id' ) ); ?>"/>
            </label>

            <label for="api_key"><?php _e( 'API KEY:', 'adcovery' ); ?>
                <input type="text" name="api_key" value="<?php echo esc_html( get_option( 'adcovery_api_key' ) ); ?>"/>
            </label>

            <label for="enabled"> <?php _e( 'Enabled: ', 'adcovery' ); ?>
                <input type="checkbox" name="enabled" value="1"
                       <?php if ( get_option( 'adcovery_enabled' ) == 1 ) {
                           ?>checked<?php
				} ?>/>
            </label>

            <input type="submit" value="<?php _e( 'Save And Refresh', 'adcovery' ); ?>" class="button-primary" name="Submit"/>

        </div>

    </form>

</div>