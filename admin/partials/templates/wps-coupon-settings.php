<?php
/**
 * This is setttings array for the Coupon settings
 *
 * Coupon Settings Template
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
$settings_obj            = new Points_Rewards_For_WooCommerce_Settings();
$wps_wpr_coupon_settings = array(
	array(
		'title' => __( 'Earn Points Per Currency Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Enable Per Currency Points Conversion', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_coupon_conversion_enable',
		'class'    => 'input-text',
		'desc'     => __( 'Allow per currency points conversion', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Check this box if you want to enable per currency points conversion.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'       => __( 'Per ', 'points-and-rewards-for-woocommerce' ) . get_woocommerce_currency_symbol() . __( 'Points Conversion', 'points-and-rewards-for-woocommerce' ),
		'desc_tip'    => __( 'Enter the redeem price for points. (i.e. how much amounts will be equivalent to the points)', 'points-and-rewards-for-woocommerce' ),
		'type'        => 'number_text',
		'number_text' => apply_filters(
			'wps_wpr_currency_filter',
			array(

				array(
					'type'              => 'text',
					'id'                => 'wps_wpr_coupon_conversion_points',
					'class'             => 'input-text wc_input_price wps_wpr_new_woo_ver_style_text',
					'custom_attributes' => array( 'min' => '"1"' ),
					'desc'              => __( ' = ', 'points-and-rewards-for-woocommerce' ),
					'curr'              => get_woocommerce_currency_symbol(),

				),
				array(
					'type'              => 'number',
					'id'                => 'wps_wpr_coupon_conversion_price',
					'class'             => 'input-text wps_wpr_new_woo_ver_style_text wc_input_price',
					'default'           => '1',
					'custom_attributes' => array( 'min' => '"1"' ),
					'desc'              => __( ' Points ', 'points-and-rewards-for-woocommerce' ),
					'curr'              => '',
				),
			)
		),
	),
	array(
		'type' => 'sectionend',
	),

);
$wps_wpr_coupon_settings = apply_filters( 'wps_wpr_coupon_settings', $wps_wpr_coupon_settings );
$current_tab             = 'wps_wpr_coupons_tab';
if ( isset( $_POST['wps_wpr_save_coupon'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {
	$wps_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	if ( wp_verify_nonce( $wps_wpr_nonce, 'wps-wpr-nonce' ) ) {
		?>
		<?php
		$settings_obj->wps_wpr_settings_saved();
		if ( 'wps_wpr_coupons_tab' == $current_tab ) {
			unset( $_POST['wps_wpr_save_coupon'] );
			$coupon_settings_array = array();
			$postdata              = $settings_obj->check_is_settings_is_not_empty( $wps_wpr_coupon_settings, $_POST );
			if ( is_array( $postdata ) && ! empty( $postdata ) ) {
				foreach ( $postdata as $key => $value ) {
					$value                         = stripcslashes( $value );
					$value                         = sanitize_text_field( $value );
					$coupon_settings_array[ $key ] = $value;
				}
			}
			if ( is_array( $coupon_settings_array ) && ! empty( $coupon_settings_array ) ) {
				/*This is coupon settings filter */
				update_option( 'wps_wpr_coupons_gallery', $coupon_settings_array );
			}
			do_action( 'wps_wpr_coupon_settings_save_option', $coupon_settings_array );
		}
	}
}
?>
<?php
$coupon_settings = get_option( 'wps_wpr_coupons_gallery', true );
?>
<?php
if ( ! is_array( $coupon_settings ) ) :
	$coupon_settings = array();
endif;
?>
<?php do_action( 'wps_wpr_add_notice' ); ?>
<div class="wps_wpr_table">
		<div class="wps_wpr_general_wrapper">
		<?php
		foreach ( $wps_wpr_coupon_settings as $key => $value ) {
			if ( 'title' == $value['type'] ) {
				?>
				<div class="wps_wpr_general_row_wrap">
					<?php
					$settings_obj->wps_rwpr_generate_heading( $value );
			}
			if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) {
				?>
				<div class="wps_wpr_general_row">
				<?php $settings_obj->wps_rwpr_generate_label( $value ); ?>
					<div class="wps_wpr_general_content">
					<?php
					$settings_obj->wps_rwpr_generate_tool_tip( $value );
					if ( 'checkbox' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_checkbox_html( $value, $coupon_settings );
					}
					if ( 'number' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_number_html( $value, $coupon_settings );
					}
					if ( 'text' == $value['type'] ) {

						$settings_obj->wps_rwpr_generate_text_html( $value, $coupon_settings );
					}
					if ( 'textarea' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_textarea_html( $value, $coupon_settings );
					}
					if ( 'number_text' == $value['type'] ) {
						foreach ( $value['number_text'] as $k => $val ) {
							if ( 'text' == $val['type'] ) {
								echo '<br>';
								echo isset( $val['curr'] ) ? esc_html( $val['curr'] ) : '';
								$settings_obj->wps_rwpr_generate_text_html( $val, $coupon_settings );
							}
							if ( 'number' == $val['type'] ) {
								$settings_obj->wps_rwpr_generate_number_html( $val, $coupon_settings );
							}
						}
					}
					do_action( 'wps_wpr_additional_coupon_settings', $value, $coupon_settings );
					?>
					</div>
				</div>
				<?php
			}
			if ( 'sectionend' == $value['type'] ) {
				?>
				</div> 
				<?php
			}
		}
		?>
	</div>
</div>
<div class="clear"></div>	
<p class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_coupon">
</p>
