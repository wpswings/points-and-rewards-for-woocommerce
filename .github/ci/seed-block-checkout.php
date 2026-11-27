<?php
/**
 * CI seed for tests/e2e/cart-checkout-blocks-redeem.spec.js (run with `wp eval-file`):
 * points program on with cart + checkout redemption at 100 points = 1, the admin
 * has 1500 points, and a published 20.00 product named "WPS-8324 E2E product".
 *
 * @package points-and-rewards
 */

$wpr_gallery = get_option( 'wps_wpr_settings_gallery', array() );
$wpr_gallery = is_array( $wpr_gallery ) ? $wpr_gallery : array();

$wpr_gallery['wps_wpr_general_setting_enable'] = 1;
$wpr_gallery['wps_wpr_custom_points_on_cart']  = 1;
$wpr_gallery['wps_wpr_apply_points_checkout']  = 1;
$wpr_gallery['wps_wpr_cart_points_rate']       = 100;
$wpr_gallery['wps_wpr_cart_price_rate']        = 1;
update_option( 'wps_wpr_settings_gallery', $wpr_gallery );

$wpr_admin = get_user_by( 'login', 'admin' );
update_user_meta( $wpr_admin->ID, 'wps_wpr_points', 1500 );

$wpr_product = new WC_Product_Simple();
$wpr_product->set_name( 'WPS-8324 E2E product' );
$wpr_product->set_regular_price( '20' );
$wpr_product->set_virtual( true );
$wpr_product->set_status( 'publish' );
$wpr_product->save();

echo 'Seeded block checkout data, product ' . (int) $wpr_product->get_id() . "\n";
