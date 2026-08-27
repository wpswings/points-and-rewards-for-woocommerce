<?php
/**
 * Migration script to fix redemption settings from 'yes'/'no' to '1'/'0'
 *
 * This script fixes the issue where setup wizard saves 'yes'/'no' but settings page expects '1'/'0'
 *
 * Run this by accessing: http://your-site.local/wp-content/plugins/points-and-rewards-for-woocommerce/reset-redemption-setting.php
 * OR via command line: php reset-redemption-setting.php
 */

// Load WordPress
if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
}

// Check admin capability
if ( ! current_user_can( 'manage_options' ) && php_sapi_name() !== 'cli' ) {
	wp_die( 'You do not have permission to access this script.' );
}

echo "<pre>\n";
echo "=== Redemption Setting Migration Script ===\n\n";

// Get current settings
$general_settings = get_option( 'wps_wpr_settings_gallery', array() );

$updated = false;

// Check if the redemption setting exists and has 'yes'/'no' value
if ( isset( $general_settings['wps_wpr_custom_points_on_cart'] ) ) {
	$current_value = $general_settings['wps_wpr_custom_points_on_cart'];
	echo "Current value: '{$current_value}'\n\n";

	// Convert 'yes' to '1' and 'no' to '0'
	if ( 'yes' === $current_value ) {
		$general_settings['wps_wpr_custom_points_on_cart'] = '1';
		$updated = true;
		echo "✓ Converting redemption setting from 'yes' to '1'\n";
	} elseif ( 'no' === $current_value ) {
		$general_settings['wps_wpr_custom_points_on_cart'] = '0';
		$updated = true;
		echo "✓ Converting redemption setting from 'no' to '0'\n";
	} else {
		echo "✓ Redemption setting already has correct format: {$current_value}\n";
	}
} else {
	echo "! Redemption setting not found in database\n";
}

// Save the updated settings if changed
if ( $updated ) {
	$result = update_option( 'wps_wpr_settings_gallery', $general_settings );
	if ( $result ) {
		echo "\n✓ Settings saved successfully!\n";
	} else {
		echo "\n✗ Failed to save settings or no changes were made\n";
	}
} else {
	echo "\n✓ No migration needed\n";
}

// Display final value
$general_settings = get_option( 'wps_wpr_settings_gallery', array() );
echo "\nFinal value: " . ( isset( $general_settings['wps_wpr_custom_points_on_cart'] ) ? $general_settings['wps_wpr_custom_points_on_cart'] : 'not set' ) . "\n";

echo "\n=== Migration Complete ===\n";
echo "</pre>\n";
