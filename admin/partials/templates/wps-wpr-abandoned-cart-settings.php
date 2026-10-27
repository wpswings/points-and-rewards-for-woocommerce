<?php
/**
 * This is the setting template
 *
 * Abandoned cart recovery settings.
 *
 * @link       https://wpswings.com/
 * @since      2.10.5
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

// Save Abandoned Cart Settings.
if ( isset( $_POST['wps_wpr_abandoned_cart_setting_nonce'] ) ) {
	if ( wp_verify_nonce( ! empty( $_POST['wps_wpr_abandoned_cart_setting_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_abandoned_cart_setting_nonce'] ) ) : '', 'abandoned-cart-setting-nonce' ) ) {
		if ( isset( $_POST['wps_wpr_save_abandoned_cart_settings'] ) ) {

			$wps_wpr_abandoned_cart_settings                              = array();
			$wps_wpr_abandoned_cart_settings['wps_wpr_ac_enable']         = ! empty( $_POST['wps_wpr_ac_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_ac_enable'] ) ) : 'no';
			$wps_wpr_abandoned_cart_settings['wps_wpr_ac_abandon_hours']  = ! empty( $_POST['wps_wpr_ac_abandon_hours'] ) ? max( 1, absint( wp_unslash( $_POST['wps_wpr_ac_abandon_hours'] ) ) ) : 1;
			$wps_wpr_abandoned_cart_settings['wps_wpr_ac_bonus_points']   = ! empty( $_POST['wps_wpr_ac_bonus_points'] ) ? absint( wp_unslash( $_POST['wps_wpr_ac_bonus_points'] ) ) : 0;
			$wps_wpr_abandoned_cart_settings['wps_wpr_ac_validity_days']  = ! empty( $_POST['wps_wpr_ac_validity_days'] ) ? max( 1, absint( wp_unslash( $_POST['wps_wpr_ac_validity_days'] ) ) ) : 1;
			$wps_wpr_abandoned_cart_settings['wps_wpr_ac_min_cart_total'] = ! empty( $_POST['wps_wpr_ac_min_cart_total'] ) ? (float) wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['wps_wpr_ac_min_cart_total'] ) ) ) : 0;
			$wps_wpr_abandoned_cart_settings['wps_wpr_ac_email_subject']  = ! empty( $_POST['wps_wpr_ac_email_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_ac_email_subject'] ) ) : '';
			$wps_wpr_abandoned_cart_settings['wps_wpr_ac_email_content']  = ! empty( $_POST['wps_wpr_ac_email_content'] ) ? wp_kses_post( wp_unslash( $_POST['wps_wpr_ac_email_content'] ) ) : '';

			update_option( WPS_WPR_Abandoned_Cart_Recovery::OPTION, $wps_wpr_abandoned_cart_settings );
			WPS_WPR_Abandoned_Cart_Recovery::wps_wpr_sync_cron_schedule();
		}
	}

	// Show saved msg.
	$settings_obj->wps_wpr_settings_saved();
}

// get abandoned cart values.
$wps_wpr_abandoned_cart_settings = WPS_WPR_Abandoned_Cart_Recovery::wps_wpr_get_settings();
$wps_wpr_ac_enable               = $wps_wpr_abandoned_cart_settings['wps_wpr_ac_enable'];
$wps_wpr_ac_abandon_hours        = $wps_wpr_abandoned_cart_settings['wps_wpr_ac_abandon_hours'];
$wps_wpr_ac_bonus_points         = $wps_wpr_abandoned_cart_settings['wps_wpr_ac_bonus_points'];
$wps_wpr_ac_validity_days        = $wps_wpr_abandoned_cart_settings['wps_wpr_ac_validity_days'];
$wps_wpr_ac_min_cart_total       = $wps_wpr_abandoned_cart_settings['wps_wpr_ac_min_cart_total'];
$wps_wpr_ac_email_subject        = $wps_wpr_abandoned_cart_settings['wps_wpr_ac_email_subject'];
$wps_wpr_ac_email_content        = $wps_wpr_abandoned_cart_settings['wps_wpr_ac_email_content'];
?>

<div class="wps_wpr_abandoned_cart_main_wrappers">
	<main class="wps_wpr_main_abandoned_cart_wrapper">
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Abandoned Cart Recovery', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<input type="hidden" name="wps_wpr_abandoned_cart_setting_nonce" id="wps_wpr_abandoned_cart_setting_nonce" value="<?php echo esc_html( wp_create_nonce( 'abandoned-cart-setting-nonce' ) ); ?>">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_ac_enable" class="wps_wpr_general_label"><?php esc_html_e( 'Enable Abandoned Cart Recovery', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_ac_enable" id="wps_wpr_ac_enable" value="yes" <?php checked( $wps_wpr_ac_enable, 'yes' ); ?>>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Email logged-in customers who leave items in their cart and offer them bonus points to complete the purchase.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_ac_abandon_hours" class="wps_wpr_general_label"><?php esc_html_e( 'Consider Cart Abandoned After (Hours)', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="1" name="wps_wpr_ac_abandon_hours" id="wps_wpr_ac_abandon_hours" value="<?php echo esc_attr( $wps_wpr_ac_abandon_hours ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Hours of cart inactivity after which the offer email is sent. Carts are checked hourly.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_ac_bonus_points" class="wps_wpr_general_label"><?php esc_html_e( 'Bonus Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="1" name="wps_wpr_ac_bonus_points" id="wps_wpr_ac_bonus_points" value="<?php echo esc_attr( $wps_wpr_ac_bonus_points ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Points awarded when the customer completes the order within the offer validity.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_ac_validity_days" class="wps_wpr_general_label"><?php esc_html_e( 'Offer Validity (Days)', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="1" name="wps_wpr_ac_validity_days" id="wps_wpr_ac_validity_days" value="<?php echo esc_attr( $wps_wpr_ac_validity_days ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Number of days the customer has to place the order after the email is sent.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_ac_min_cart_total" class="wps_wpr_general_label"><?php esc_html_e( 'Minimum Cart Total', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="0" step="0.01" name="wps_wpr_ac_min_cart_total" id="wps_wpr_ac_min_cart_total" value="<?php echo esc_attr( $wps_wpr_ac_min_cart_total ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Only send the offer for carts worth at least this amount. Use 0 for no minimum.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_ac_email_subject" class="wps_wpr_general_label"><?php esc_html_e( 'Email Subject', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" name="wps_wpr_ac_email_subject" id="wps_wpr_ac_email_subject" class="input-text" value="<?php echo esc_attr( $wps_wpr_ac_email_subject ); ?>">
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_ac_email_content" class="wps_wpr_general_label"><?php esc_html_e( 'Email Content', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<?php
						wp_editor(
							$wps_wpr_ac_email_content,
							'wps_wpr_ac_email_content',
							array(
								'textarea_name' => 'wps_wpr_ac_email_content',
								'textarea_rows' => 8,
								'media_buttons' => false,
							)
						);
						?>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Shortcodes: [USERNAME], [POINTS], [CARTTOTAL], [CARTLINK], [EXPIRYDATE]', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</div>
		</section>
	</main>
	<input type="submit" name="wps_wpr_save_abandoned_cart_settings" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_abandoned_cart_settings" value="<?php esc_html_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
