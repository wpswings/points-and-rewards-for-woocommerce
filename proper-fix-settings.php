<?php
/**
 * Proper Fix for Points-to-Discount Notice
 * The bug: settings are read from wps_wpr_settings_gallery but coupon conversion is in wps_wpr_coupons_gallery
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo '<h1>Fixing Points-to-Discount Settings Bug</h1>';
echo '<style>body{font-family:monospace;padding:20px;}.success{color:green;}.error{color:red;}</style>';

echo '<h2>The Bug:</h2>';
echo '<p>The function reads coupon conversion settings from <code>wps_wpr_settings_gallery</code><br>';
echo 'But they are actually stored in <code>wps_wpr_coupons_gallery</code></p>';

echo '<hr><h2>Solution: Copy settings to the correct location</h2>';

// Get coupon settings
$coupon_settings = get_option('wps_wpr_coupons_gallery', array());
$general_settings = get_option('wps_wpr_settings_gallery', array());

echo '<h3>Current Coupon Settings (wps_wpr_coupons_gallery):</h3>';
echo '<pre>';
echo 'wps_wpr_coupon_conversion_points = ' . (isset($coupon_settings['wps_wpr_coupon_conversion_points']) ? $coupon_settings['wps_wpr_coupon_conversion_points'] : 'NOT SET') . "\n";
echo 'wps_wpr_coupon_conversion_price = ' . (isset($coupon_settings['wps_wpr_coupon_conversion_price']) ? $coupon_settings['wps_wpr_coupon_conversion_price'] : 'NOT SET') . "\n";
echo 'wps_wpr_general_minimum_value = ' . (isset($coupon_settings['wps_wpr_general_minimum_value']) ? $coupon_settings['wps_wpr_general_minimum_value'] : 'NOT SET') . "\n";
echo '</pre>';

echo '<h3>Current General Settings (wps_wpr_settings_gallery):</h3>';
echo '<pre>';
echo 'wps_wpr_coupon_conversion_points = ' . (isset($general_settings['wps_wpr_coupon_conversion_points']) ? $general_settings['wps_wpr_coupon_conversion_points'] : 'NOT SET') . "\n";
echo 'wps_wpr_coupon_conversion_price = ' . (isset($general_settings['wps_wpr_coupon_conversion_price']) ? $general_settings['wps_wpr_coupon_conversion_price'] : 'NOT SET') . "\n";
echo 'wps_wpr_apply_points_value = ' . (isset($general_settings['wps_wpr_apply_points_value']) ? $general_settings['wps_wpr_apply_points_value'] : 'NOT SET') . "\n";
echo '</pre>';

echo '<hr><h2>Applying Fix...</h2>';

// Copy conversion settings from coupon settings to general settings
if (!empty($coupon_settings['wps_wpr_coupon_conversion_points'])) {
    $general_settings['wps_wpr_coupon_conversion_points'] = $coupon_settings['wps_wpr_coupon_conversion_points'];
    echo '<p class="success">✅ Copied wps_wpr_coupon_conversion_points: ' . $general_settings['wps_wpr_coupon_conversion_points'] . '</p>';
} else {
    // Set default
    $general_settings['wps_wpr_coupon_conversion_points'] = 100;
    echo '<p class="success">✅ Set default wps_wpr_coupon_conversion_points: 100</p>';
}

if (!empty($coupon_settings['wps_wpr_coupon_conversion_price'])) {
    $general_settings['wps_wpr_coupon_conversion_price'] = $coupon_settings['wps_wpr_coupon_conversion_price'];
    echo '<p class="success">✅ Copied wps_wpr_coupon_conversion_price: ' . $general_settings['wps_wpr_coupon_conversion_price'] . '</p>';
} else {
    // Set default
    $general_settings['wps_wpr_coupon_conversion_price'] = 5;
    echo '<p class="success">✅ Set default wps_wpr_coupon_conversion_price: 5</p>';
}

// Set minimum redeem points
if (!isset($general_settings['wps_wpr_apply_points_value'])) {
    $general_settings['wps_wpr_apply_points_value'] = isset($coupon_settings['wps_wpr_general_minimum_value']) ? $coupon_settings['wps_wpr_general_minimum_value'] : 50;
    echo '<p class="success">✅ Set wps_wpr_apply_points_value: ' . $general_settings['wps_wpr_apply_points_value'] . '</p>';
}

// Make sure cart redemption is enabled
$general_settings['wps_wpr_custom_points_on_cart'] = 1;
echo '<p class="success">✅ Enabled wps_wpr_custom_points_on_cart</p>';

// Save
update_option('wps_wpr_settings_gallery', $general_settings);
echo '<p class="success"><strong>✅ Settings saved!</strong></p>';

echo '<hr><h2>Verification:</h2>';
$verify = get_option('wps_wpr_settings_gallery');
echo '<pre>';
echo 'wps_wpr_custom_points_on_cart = ' . $verify['wps_wpr_custom_points_on_cart'] . "\n";
echo 'wps_wpr_coupon_conversion_points = ' . $verify['wps_wpr_coupon_conversion_points'] . "\n";
echo 'wps_wpr_coupon_conversion_price = ' . $verify['wps_wpr_coupon_conversion_price'] . "\n";
echo 'wps_wpr_apply_points_value = ' . (isset($verify['wps_wpr_apply_points_value']) ? $verify['wps_wpr_apply_points_value'] : 'NOT SET') . "\n";
echo '</pre>';

$admin_points = get_user_meta(get_current_user_id(), 'wps_wpr_points', true);
echo '<h3>Expected Notice Calculation:</h3>';
echo '<p>Admin points: ' . $admin_points . '</p>';
echo '<p>Minimum redeem: ' . $verify['wps_wpr_apply_points_value'] . '</p>';
echo '<p>Points needed: ' . ($verify['wps_wpr_apply_points_value'] - $admin_points) . '</p>';
$discount_value = ($verify['wps_wpr_apply_points_value'] / $verify['wps_wpr_coupon_conversion_points']) * $verify['wps_wpr_coupon_conversion_price'];
echo '<p>Discount value: $' . $discount_value . '</p>';
echo '<p class="success"><strong>Expected notice: "You\'re ' . ($verify['wps_wpr_apply_points_value'] - $admin_points) . ' points away from a $' . $discount_value . ' discount!"</strong></p>';

echo '<hr>';
echo '<h2>Next Step:</h2>';
echo '<p><a href="http://localhost:10438/cart/" target="_blank">→ Visit Cart Page</a></p>';
echo '<p>You should now see the purple gradient notice!</p>';
?>
