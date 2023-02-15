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
		'title'    => __( 'Enter the text which you want to display with shortcode [MYCURRENTPOINT]', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'desc_tip' => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'id'       => 'wps_wpr_shortcode_text_membership',
		'type'     => 'text',
		'title'    => __( 'Enter the text which you want to display with shortcode [MYCURRENTUSERLEVEL]', 'points-and-rewards-for-woocommerce' ),
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
		'title'    => __( 'Enable to show shortcode on Cart page [WPS_CART_PAGE_SECTION]', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_cart_page_apply_point_section',
		'heading'  => __( 'Cart page shortcode', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc_tip' => __( 'This shortcode is only work on Cart page.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Please check this box to show apply points section on Cart page using this shortcode.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Enable to show shortcode on Checkout page [WPS_CHECKOUT_PAGE_SECTION]', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_checkout_page_apply_point_section',
		'heading'  => __( 'Checkout page shortcode', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc_tip' => __( 'This shortcode is only work on Checkout page.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Please check this box to show apply points section on Checkout page using this shortcode.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),

);

$wps_wpr_other_settings = apply_filters( 'wps_wpr_others_settings', $wps_wpr_other_settings );
/*Save Settings*/
$current_tab = 'wps_wpr_othersetting_tab';

if ( isset( $_POST['wps_wpr_save_othersetting'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {
	$wps_par_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	if ( wp_verify_nonce( $wps_par_nonce, 'wps-wpr-nonce' ) ) {
		unset( $_POST['wps_wpr_save_othersetting'] );
		$other_settings = array();
		/* Check if input is not empty, if empty then assign them default value*/
		$postdata = $settings_obj->check_is_settings_is_not_empty( $wps_wpr_other_settings, $_POST );
		foreach ( $postdata as $key => $value ) {
			$value                  = stripcslashes( $value );
			$value                  = sanitize_text_field( $value );
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
/* Get Saved settings*/
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
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_othersetting">
</p>
