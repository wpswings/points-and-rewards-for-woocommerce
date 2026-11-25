<?php
/**
 * This is the setting template
 *
 * Basic points expiry settings.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

// Save points expiry settings.
if ( isset( $_POST['wps_wpr_points_expiry_setting_nonce'] ) && isset( $_POST['wps_wpr_save_points_expiry_settings'] ) && current_user_can( 'manage_options' ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wpr_points_expiry_setting_nonce'] ) ), 'points-expiry-setting-nonce' ) ) {

		$wps_wpr_expiry_request = array();
		foreach ( array_keys( WPS_WPR_Points_Expiry::wps_wpr_get_default_settings() ) as $wps_wpr_expiry_key ) {
			if ( isset( $_POST[ $wps_wpr_expiry_key ] ) ) {
				$wps_wpr_expiry_request[ $wps_wpr_expiry_key ] = wp_unslash( $_POST[ $wps_wpr_expiry_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in wps_wpr_sanitize_settings().
			}
		}
		update_option( WPS_WPR_Points_Expiry::OPTION, WPS_WPR_Points_Expiry::wps_wpr_sanitize_settings( $wps_wpr_expiry_request ) );
		WPS_WPR_Points_Expiry::wps_wpr_sync_cron_schedule();

		// Show saved msg.
		$settings_obj->wps_wpr_settings_saved();
	}
}

$wps_wpr_expiry_settings = WPS_WPR_Points_Expiry::wps_wpr_get_settings();
$wps_wpr_expiry_unit     = WPS_WPR_Points_Expiry::wps_wpr_get_unit( $wps_wpr_expiry_settings );
?>

<div class="wps_wpr_points_expiry_main_wrappers">
	<main class="wps_wpr_main_points_expiry_wrapper">
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Points Expiration', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<input type="hidden" name="wps_wpr_points_expiry_setting_nonce" id="wps_wpr_points_expiry_setting_nonce" value="<?php echo esc_attr( wp_create_nonce( 'points-expiry-setting-nonce' ) ); ?>">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_points_expiration_enable" class="wps_wpr_general_label"><?php esc_html_e( 'Enable Points Expiration', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_points_expiration_enable" id="wps_wpr_points_expiration_enable" value="1" <?php checked( (int) $wps_wpr_expiry_settings['wps_wpr_points_expiration_enable'], 1 ); ?>>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Unused points expire after the period below. The period starts when the customer\'s balance reaches the threshold. Checked once a day.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_points_expiration_threshold" class="wps_wpr_general_label"><?php esc_html_e( 'Balance Threshold', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="1" name="wps_wpr_points_expiration_threshold" id="wps_wpr_points_expiration_threshold" value="<?php echo esc_attr( $wps_wpr_expiry_settings['wps_wpr_points_expiration_threshold'] ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'The expiry period starts once the balance reaches this many points.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_points_expiration_time_num" class="wps_wpr_general_label"><?php esc_html_e( 'Points Expire After', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="1" name="wps_wpr_points_expiration_time_num" id="wps_wpr_points_expiration_time_num" value="<?php echo esc_attr( $wps_wpr_expiry_settings['wps_wpr_points_expiration_time_num'] ); ?>">
						<select name="wps_wpr_points_expiration_time_drop" id="wps_wpr_points_expiration_time_drop">
							<?php foreach ( WPS_WPR_Points_Expiry::wps_wpr_get_units() as $wps_wpr_unit_key => $wps_wpr_unit_label ) : ?>
								<option value="<?php echo esc_attr( $wps_wpr_unit_key ); ?>" <?php selected( $wps_wpr_expiry_unit, $wps_wpr_unit_key ); ?>><?php echo esc_html( $wps_wpr_unit_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_points_exp_onmyaccount" class="wps_wpr_general_label"><?php esc_html_e( 'Show Expiry Date on My Account', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_points_exp_onmyaccount" id="wps_wpr_points_exp_onmyaccount" value="1" <?php checked( (int) $wps_wpr_expiry_settings['wps_wpr_points_exp_onmyaccount'], 1 ); ?>>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Show the date the customer\'s points expire below their balance on the My Account points page.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</div>
		</section>
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Expiry Emails', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_expire_notify_setting" class="wps_wpr_general_label"><?php esc_html_e( 'Email Customers', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_expire_notify_setting" id="wps_wpr_enable_expire_notify_setting" value="1" <?php checked( (int) $wps_wpr_expiry_settings['wps_wpr_enable_expire_notify_setting'], 1 ); ?>>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Send one reminder before points expire, and an email when they expire.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_points_expiration_email" class="wps_wpr_general_label"><?php esc_html_e( 'Reminder (Days Before Expiry)', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="0" name="wps_wpr_points_expiration_email" id="wps_wpr_points_expiration_email" value="<?php echo esc_attr( $wps_wpr_expiry_settings['wps_wpr_points_expiration_email'] ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Use 0 to skip the reminder.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_re_notification" class="wps_wpr_general_label"><?php esc_html_e( 'Reminder Message', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<textarea name="wps_wpr_re_notification" id="wps_wpr_re_notification" rows="4" cols="50"><?php echo esc_textarea( $wps_wpr_expiry_settings['wps_wpr_re_notification'] ); ?></textarea>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Placeholders: [USERNAME], [TOTALPOINT], [EXPIRYDATE], [SITENAME]', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_expired_notification" class="wps_wpr_general_label"><?php esc_html_e( 'Expired Message', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<textarea name="wps_wpr_expired_notification" id="wps_wpr_expired_notification" rows="4" cols="50"><?php echo esc_textarea( $wps_wpr_expiry_settings['wps_wpr_expired_notification'] ); ?></textarea>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Placeholders: [USERNAME], [TOTALPOINT], [SITENAME]', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</div>
		</section>
		<?php if ( ! ( function_exists( 'wps_wpr_is_active' ) && wps_wpr_is_active() ) ) : ?>
			<p class="wps_wpr_points_expiry_pro_note"><?php esc_html_e( 'Need threshold alerts, a points expiry shortcode, or letting customers extend their expiry with points? These are available in the Pro version.', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php endif; ?>
	</main>
	<input type="submit" name="wps_wpr_save_points_expiry_settings" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_points_expiry_settings" value="<?php esc_attr_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
