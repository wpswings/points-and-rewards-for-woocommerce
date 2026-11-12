<?php
/**
 * Migration script to fix redemption settings from 'yes'/'no' to '1'/'0'
 *
 * This script fixes the issue where setup wizard saves 'yes'/'no' but settings page expects '1'/'0'
 *
 * Development/maintenance helper only; it is excluded from release builds.
 * Open it in the browser as an administrator and confirm with the button
 * (POST + nonce). Plain GET requests never change settings.
 */

// Load WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
}

// Only administrators.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this script.', 'points-and-rewards-for-woocommerce' ), 403 );
}

// Show a confirmation form on anything other than a POST.
$wps_wpr_request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
if ( 'POST' !== $wps_wpr_request_method ) {
	echo '<form method="post">';
	wp_nonce_field( 'wps_wpr_reset_redemption_setting' );
	echo '<p>' . esc_html__( 'Convert the redemption setting from yes/no to 1/0.', 'points-and-rewards-for-woocommerce' ) . '</p>';
	echo '<button type="submit">' . esc_html__( 'Run migration', 'points-and-rewards-for-woocommerce' ) . '</button>';
	echo '</form>';
	exit;
}

// Valid nonce required.
check_admin_referer( 'wps_wpr_reset_redemption_setting' );

echo "<pre>\n";
echo "=== Redemption Setting Migration Script ===\n\n";

// Get current settings
$general_settings = get_option( 'wps_wpr_settings_gallery', array() );

$updated = false;

// Check if the redemption setting exists and has 'yes'/'no' value
if ( isset( $general_settings['wps_wpr_custom_points_on_cart'] ) ) {
	$current_value = $general_settings['wps_wpr_custom_points_on_cart'];
	echo "Current value: '" . esc_html( $current_value ) . "'\n\n";

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
		echo '✓ Redemption setting already has correct format: ' . esc_html( $current_value ) . "\n";
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
echo "\nFinal value: " . esc_html( isset( $general_settings['wps_wpr_custom_points_on_cart'] ) ? $general_settings['wps_wpr_custom_points_on_cart'] : 'not set' ) . "\n";

echo "\n=== Migration Complete ===\n";
echo "</pre>\n";
