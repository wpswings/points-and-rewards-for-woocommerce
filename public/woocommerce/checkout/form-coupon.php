<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.4
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}
/**
 * Allowed the html for the woocommerce.
 *
 * @name mwb_wpr_allowed_html
 * @since 1.0.0
 */
function mwb_wpr_allowed_html() {
		$allowed_tags = array(
			'span'  => array(
				'class'    => array(),
				'title'    => array(),
				'style'    => array(),
				'data-tip' => array(),
			),
			'min'   => array(),
			'max'   => array(),
			'class' => array(),
			'style' => array(),
			'<br>'  => array(),
		);
		return $allowed_tags;
}
?>
<div class="woocommerce-form-coupon-toggle">
	<?php wc_print_notice( apply_filters( 'woocommerce_checkout_coupon_message', __( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" class="showcoupon">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' ), 'notice' ); ?>
</div>
<!-- /*MWB CUSTOM CODE*/ -->
<div class="woocommerce-error mwb_rwpr_settings_display_none" id="mwb_wpr_cart_points_notice" >
	
</div>
<div class="woocommerce-message mwb_rwpr_settings_display_none" id="mwb_wpr_cart_points_success" >
	
</div>
<!-- /*END OF MWB CUSTOM CODE*/ -->
<form class="checkout_coupon woocommerce-form-coupon mwb_rwpr_settings_display_none" method="post">

	<p><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'woocommerce' ); ?></p>

	<p class="form-row form-row-first">
		<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
	</p>

	<p class="form-row form-row-last">
		<button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
	</p>

	<div class="clear"></div>
</form>

<?php

$user_id = get_current_user_ID();
if ( isset( $user_id ) && ! empty( $user_id ) ) {
	if ( class_exists( 'Points_Rewards_For_WooCommerce_Public' ) ) {
		$public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-rewards-for-woocommerce', '1.0.0' );
	}

	$get_points = (int) get_user_meta( $user_id, 'mwb_wpr_points', true );
	/* Points Rate*/
	$mwb_wpr_cart_points_rate = $public_obj->mwb_wpr_get_general_settings_num( 'mwb_wpr_cart_points_rate' );
	$mwb_wpr_cart_points_rate = ( 0 == $mwb_wpr_cart_points_rate ) ? 1 : $mwb_wpr_cart_points_rate;
	/* Points Rate*/
	$mwb_wpr_cart_price_rate = $public_obj->mwb_wpr_get_general_settings_num( 'mwb_wpr_cart_price_rate' );
	$mwb_wpr_cart_price_rate = ( 0 == $mwb_wpr_cart_price_rate ) ? 1 : $mwb_wpr_cart_price_rate;
	$conversion              = ( $get_points * $mwb_wpr_cart_price_rate / $mwb_wpr_cart_points_rate );
	?>
<div class="custom_point_checkout woocommerce-info">
	<input type="number" min="0" name="mwb_cart_points" class="input-text" id="mwb_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-rewards-for-woocommerce' ); ?>"/>
	<input type="button" name="mwb_cart_points_apply" data-point="<?php echo esc_html( $get_points ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" class="button mwb_cart_points_apply" id="mwb_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-rewards-for-woocommerce' ); ?>"/>
	<p><?php echo esc_html( $get_points ) . esc_html__( ' Points', 'points-rewards-for-woocommerce' ) . ' = ' . wp_kses( wc_price( $conversion ), mwb_wpr_allowed_html() ); ?></p>
</div>

	<?php
}
?>
