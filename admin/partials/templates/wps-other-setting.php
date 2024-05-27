<?php
/**
 * This is setttings array for the other settings
 *
 * Other Settings Template
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

$wps_wpr_other_settings = array(
	array(
		'title' => __( 'Shortcodes', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'id'    => 'wps_wpr_other_shortcodes',
		'title' => __( 'Shortcodes', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'shortcode',
		'desc'  => $settings_obj->wps_wpr_display_shortcode(),
	),
	array(
		'id'       => 'wps_wpr_other_shortcode_text',
		'title'    => __( 'Text You Want to Display Along With [MYCURRENTPOINT] Shortcode', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'desc_tip' => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'id'       => 'wps_wpr_shortcode_text_membership',
		'type'     => 'text',
		'title'    => __( 'Text You Want to Display Along With [MYCURRENTUSERLEVEL] Shortcode', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', 'points-and-rewards-for-woocommerce' ),

	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Other Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'id'       => 'wps_wpr_notification_color',
		'type'     => 'color',
		'title'    => __( 'Select Color Notification Bar', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'You can also choose the color for your Notification Bar.', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc'     => __( 'Enable Point Sharing', 'points-and-rewards-for-woocommerce' ),
		'default'  => '#55b3a5',
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Cart and Checkout Page Shortcode', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Show Shortcode on Cart Page [WPS_CART_PAGE_SECTION]', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_cart_page_apply_point_section',
		'heading'  => __( 'Cart page shortcode', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc_tip' => __( 'This shortcode is only work on Cart page.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Toggle This to show the Apply Points Section on the Cart page using this shortcode.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Show shortcode on Checkout Page [WPS_CHECKOUT_PAGE_SECTION]', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_checkout_page_apply_point_section',
		'heading'  => __( 'Checkout page shortcode', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc_tip' => __( 'This shortcode is only work on Checkout page.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Toggle This to show the Apply Points Section on the Checkout page using this shortcode.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Restrict Rewards Points Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Toggle Restrict Points Settings', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_restrict_rewards_points',
		'class'    => 'input-text',
		'desc_tip' => __( 'User will not earn any points when he/she redeem points on cart/checkout page', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Toggle this setting if you want to restrict user to not earn any points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Toggle Message Settings', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_show_message_on_cart_page',
		'class'    => 'input-text',
		'desc_tip' => __( 'Toggle this box to show the Message on Cart Page, when user redeem points', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Toggle this setting to show message on cart page after apply points section.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'id'       => 'wps_wpr_restricted_cart_page_msg',
		'title'    => __( 'Enter Message', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'desc_tip' => __( 'Entered message will appear on cart page after apply points section.', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( ' Enter Rewards Restriction Message', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Point Tab Layout Setting', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Points Tab New layout', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_choose_account_page_temp',
		'class'    => 'input-text',
		'desc_tip' => __( 'To utilize the new template for the My Account section within the points tab.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( ' Activate this setting to enable the new layout for the points tab. ', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'id'       => 'wps_wpr_points_tab_layout_color',
		'type'     => 'color',
		'title'    => __( 'Choose the color scheme for the Points tab layout', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'The selected color will be displayed on the Points tab layout on the My Account page.', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc'     => __( 'Enable Point Sharing', 'points-and-rewards-for-woocommerce' ),
		'default'  => '#0094ff',
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Rewards Points via Payment Method', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Enable Payment Reward Settings', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_enable_payment_rewards_settings',
		'class'    => 'input-text',
		'desc_tip' => __( 'By enabling this setting, users have the ability to earn points based on their chosen payment method.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Activate this setting to reward users according to their order payment method.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Select Payment Method', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'select',
		'id'       => 'wps_wpr_choose_payment_method',
		'class'    => 'wc-enhanced-select',
		'desc_tip' => __( 'Choose the payment method on which users will earn points accordingly.', 'points-and-rewards-for-woocommerce' ),
		'options'  => $settings_obj->wps_wpr_list_payment_method(),
	),
	array(
		'title'             => __( 'Enter Points', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'number',
		'default'           => 1,
		'id'                => 'wps_wpr_payment_method_rewards_points',
		'custom_attributes' => array( 'min' => '"1"' ),
		'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
		'desc_tip'          => __( 'Points will be rewarded to the user when the order status is marked as completed.', 'points-and-rewards-for-woocommerce' ),
		'desc'              => __( 'Enter the points that will be rewarded to the user according to their chosen payment method.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
);

$wps_wpr_other_settings = apply_filters( 'wps_wpr_others_settings', $wps_wpr_other_settings );
$current_tab            = 'wps_wpr_othersetting_tab';
if ( isset( $_POST['wps_wpr_save_othersetting'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {

	$wps_par_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	if ( wp_verify_nonce( $wps_par_nonce, 'wps-wpr-nonce' ) ) {

		unset( $_POST['wps_wpr_save_othersetting'] );
		$other_settings = array();
		$postdata       = $settings_obj->check_is_settings_is_not_empty( $wps_wpr_other_settings, $_POST );
		foreach ( $postdata as $key => $value ) {

			$other_settings[ $key ] = $value;
		}
		/* Save settings data into the database*/
		if ( ! empty( $other_settings ) && is_array( $other_settings ) ) {
			update_option( 'wps_wpr_other_settings', $other_settings );
		}
		/* Save settings Notification*/
		$settings_obj->wps_wpr_settings_saved();
		do_action( 'wps_wpr_save_other_settings', $postdata );
	}
}

$other_settings = get_option( 'wps_wpr_other_settings', array() );
?>
<?php do_action( 'wps_wpr_add_notice' ); ?>
<div class="wps_wpr_table">
		<div class="wps_wpr_general_wrapper">
				<?php
				foreach ( $wps_wpr_other_settings as $key => $value ) {
					if ( 'title' == $value['type'] ) {
						?>
						<div class="wps_wpr_general_row_wrap">
							<?php $settings_obj->wps_rwpr_generate_heading( $value ); ?>
							<?php } ?>
							<?php if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) { ?>
							<div class="wps_wpr_general_row">
								<?php $settings_obj->wps_rwpr_generate_label( $value ); ?>
								<div class="wps_wpr_general_content">
									<?php
									$settings_obj->wps_rwpr_generate_tool_tip( $value );
									if ( 'checkbox' == $value['type'] ) {
										$settings_obj->wps_rwpr_generate_checkbox_html( $value, $other_settings );
									}
									if ( 'shortcode' == $value['type'] ) {
										$settings_obj->wps_wpr_generate_shortcode( $value );
									}
									if ( 'number' == $value['type'] ) {
										$settings_obj->wps_rwpr_generate_number_html( $value, $other_settings );
									}
									if ( 'color' == $value['type'] ) {
										$settings_obj->wps_rwpr_generate_color_box( $value, $other_settings );
									}
									if ( 'multiple_checkbox' == $value['type'] ) {
										foreach ( $value['multiple_checkbox'] as $k => $val ) {
											$settings_obj->wps_rwpr_generate_checkbox_html( $val, $other_settings );
										}
									}
									if ( 'text' == $value['type'] ) {
										$settings_obj->wps_rwpr_generate_text_html( $value, $other_settings );
									}
									if ( 'textarea' == $value['type'] ) {
										$settings_obj->wps_rwpr_generate_textarea_html( $value, $other_settings );
									}
									if ( 'number_text' == $value['type'] ) {
										foreach ( $value['number_text'] as $k => $val ) {
											if ( 'text' == $val['type'] ) {
												$settings_obj->wps_rwpr_generate_text_html( $val, $other_settings );

											}
											if ( 'number' == $val['type'] ) {
												$settings_obj->wps_rwpr_generate_number_html( $val, $other_settings );
												echo esc_html( get_woocommerce_currency_symbol() );
											}
										}
									}
									if ( 'select' == $value['type'] ) {
										$settings_obj->wps_wpr_generate_select_dropdown( $value, $other_settings );
									}
									do_action( 'wps_wpr_additional_other_settings', $value, $other_settings );
									?>
								</div>
							</div>
								<?php
							}
							?>
							<?php if ( 'sectionend' == $value['type'] ) : ?>
						</div>
					<?php endif; ?>
			<?php } ?> 		
		</div>
	</div>
<p class="submit">
	<input type="hidden" name="wps-wpr-nonce" value="<?php echo esc_html( wp_create_nonce( 'wps-wpr-nonce' ) ); ?>">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_othersetting">
</p>
