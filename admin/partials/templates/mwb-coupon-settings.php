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

include_once MWB_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();
$mwb_wpr_coupon_settings = array(
	array(
		'title' => __( 'Earn Points Per Currency Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable Per Currency Points Conversion', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'id'  => 'mwb_wpr_coupon_conversion_enable',
		'class' => 'input-text',
		'desc'  => __( 'Allow per currency points conversion', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Check this box if you want enable per currency points conversion.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Per ', 'points-and-rewards-for-woocommerce' ) . get_woocommerce_currency_symbol() . __( 'Points Conversion', 'points-and-rewards-for-woocommerce' ),
		'desc_tip'  => __( 'Enter the redeem price for points. (i.e. how much amounts will be equivalent to the points)', 'points-and-rewards-for-woocommerce' ),
		'type'    => 'number_text',
		'number_text' => array(
			array(
				'type'  => 'number',
				'id'    => 'mwb_wpr_coupon_conversion_points',
				'class'   => 'input-text wc_input_price mwb_wpr_new_woo_ver_style_text',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc' => __( 'Points =', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'text',
				'id'    => 'mwb_wpr_coupon_conversion_price',
				'class'   => 'input-text mwb_wpr_new_woo_ver_style_text wc_input_price',
				'default'  => '1',
				'custom_attributes' => array( 'min' => '"1"' ),
			),
		),
	),
	array(
		'type'  => 'sectionend',
	),

);
	$mwb_wpr_coupon_settings = apply_filters( 'mwb_wpr_coupon_settings', $mwb_wpr_coupon_settings );
$current_tab = 'mwb_wpr_coupons_tab';
if ( isset( $_POST['mwb_wpr_save_coupon'] ) && isset( $_POST['mwb-wpr-nonce'] ) ) {
	$mwb_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['mwb-wpr-nonce'] ) );
	if ( wp_verify_nonce( $mwb_wpr_nonce, 'mwb-wpr-nonce' ) ) {
		?>
		<?php
		$settings_obj->mwb_wpr_settings_saved();
		if ( 'mwb_wpr_coupons_tab' == $current_tab ) {
			unset( $_POST['mwb_wpr_save_coupon'] );
			$coupon_settings_array = array();
			$postdata = $settings_obj->check_is_settings_is_not_empty( $mwb_wpr_coupon_settings, $_POST );
			if ( is_array( $postdata ) && ! empty( $postdata ) ) {
				foreach ( $postdata as $key => $value ) {
					$value = stripcslashes( $value );
					$value = sanitize_text_field( $value );
					$coupon_settings_array[ $key ] = $value;
				}
			}
			if ( is_array( $coupon_settings_array ) && ! empty( $coupon_settings_array ) ) {
				/*This is coupon settings filter */
				update_option( 'mwb_wpr_coupons_gallery', $coupon_settings_array );
			}
			do_action( 'mwb_wpr_coupon_settings_save_option', $coupon_settings_array );
		}
	}
}
?>
<?php
$coupon_settings = get_option( 'mwb_wpr_coupons_gallery', true );
?>
<?php
if ( ! is_array( $coupon_settings ) ) :
	$coupon_settings = array();
endif;
?>
<div class="mwb_wpr_table">
		<div class="mwb_wpr_general_wrapper">
		<?php
		foreach ( $mwb_wpr_coupon_settings as $key => $value ) {
			if ( 'title' == $value['type'] ) {
				?>
					<div class="mwb_wpr_general_row_wrap">
					<?php
						$settings_obj->mwb_rwpr_generate_heading( $value );
			}
			if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) {
				?>
				<div class="mwb_wpr_general_row">
				<?php $settings_obj->mwb_rwpr_generate_label( $value ); ?>
					<div class="mwb_wpr_general_content">
					<?php
					$settings_obj->mwb_rwpr_generate_tool_tip( $value );
					if ( 'checkbox' == $value['type'] ) {
						$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $coupon_settings );
					}
					if ( 'number' == $value['type'] ) {
						$settings_obj->mwb_rwpr_generate_number_html( $value, $coupon_settings );
					}
					if ( 'text' == $value['type'] ) {
						$settings_obj->mwb_rwpr_generate_text_html( $value, $coupon_settings );
					}
					if ( 'textarea' == $value['type'] ) {
						$settings_obj->mwb_rwpr_generate_textarea_html( $value, $coupon_settings );
					}
					if ( 'number_text' == $value['type'] ) {
						foreach ( $value['number_text'] as $k => $val ) {
							if ( 'text' == $val['type'] ) {
								$settings_obj->mwb_rwpr_generate_text_html( $val, $coupon_settings );

							}
							if ( 'number' == $val['type'] ) {
								$settings_obj->mwb_rwpr_generate_number_html( $val, $coupon_settings );
								echo esc_html( get_woocommerce_currency_symbol() );
							}
						}
					}
					do_action( 'mwb_wpr_additional_coupon_settings', $value, $coupon_settings );
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
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_coupon">
</p>
