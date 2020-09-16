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
?>
<div class="woocommerce-form-coupon-toggle">
	<?php wc_print_notice( apply_filters( 'woocommerce_checkout_coupon_message', __( 'Have a coupon?', 'points-and-rewards-for-woocommerce' ) . ' <a href="#" class="showcoupon">' . __( 'Click here to enter your code', 'points-and-rewards-for-woocommerce' ) . '</a>' ), 'notice' ); ?>
</div>
<!-- /*MWB CUSTOM CODE*/ -->
<div class="woocommerce-error mwb_rwpr_settings_display_none_notice" id="mwb_wpr_cart_points_notice" >
	
</div>
<div class="woocommerce-message mwb_rwpr_settings_display_none_notice" id="mwb_wpr_cart_points_success" >
	
</div>
<!-- /*END OF MWB CUSTOM CODE*/ -->
<form class="checkout_coupon woocommerce-form-coupon" method="post">

	<p><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'points-and-rewards-for-woocommerce' ); ?></p>

	<p class="form-row form-row-first">
		<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'points-and-rewards-for-woocommerce' ); ?>" id="coupon_code" value="" />
	</p>

	<p class="form-row form-row-last">
		<button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'points-and-rewards-for-woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'points-and-rewards-for-woocommerce' ); ?></button>
	</p>

	<div class="clear"></div>
</form>
<?php
  $public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-rewads-for-woocommerce', '1.0.0' );
  $public_obj->mwb_wpr_display_apply_points_checkout();
?>
