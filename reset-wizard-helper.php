<?php
/**
 * Helper Script to Reset Setup Wizard
 *
 * Load this file in your browser to reset the setup wizard.
 * URL: http://your-site.local/wp-content/plugins/points-and-rewards-for-woocommerce/reset-wizard-helper.php
 *
 * IMPORTANT: Delete this file after use for security!
 */

// Load WordPress.
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Security check - only admins.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You do not have permission to access this page.' );
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Reset Setup Wizard</title>
	<style>
		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			max-width: 600px;
			margin: 50px auto;
			padding: 20px;
			background: #f0f0f1;
		}
		.container {
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		h1 {
			color: #1d2327;
			margin-top: 0;
		}
		.success {
			background: #00a32a;
			color: white;
			padding: 12px 16px;
			border-radius: 4px;
			margin: 20px 0;
		}
		.info {
			background: #f0f6fc;
			border-left: 4px solid #0073aa;
			padding: 12px 16px;
			margin: 20px 0;
		}
		.button {
			background: #2271b1;
			color: white;
			padding: 10px 20px;
			text-decoration: none;
			border-radius: 4px;
			display: inline-block;
			margin-top: 10px;
		}
		.button:hover {
			background: #135e96;
		}
		.warning {
			background: #fcf0f1;
			border-left: 4px solid #d63638;
			padding: 12px 16px;
			margin: 20px 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>Reset Points and Rewards Setup Wizard</h1>

<?php
if ( isset( $_GET['action'] ) && 'reset' === $_GET['action'] ) {
	// Verify nonce.
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'reset_wizard' ) ) {
		echo '<div class="warning">Security check failed. Please try again.</div>';
	} else {
		// Delete wizard flags.
		delete_option( 'wps_wpr_wizard_completed' );
		delete_option( 'wps_wpr_wizard_skipped' );

		// Set activation redirect transient.
		set_transient( 'wps_wpr_activation_redirect', true, 3600 );

		echo '<div class="success">✓ Setup wizard has been reset successfully!</div>';
		echo '<div class="info">';
		echo '<strong>What to do next:</strong><br>';
		echo '• Click the button below to access the setup wizard<br>';
		echo '• Or, you will be automatically redirected on your next admin page visit<br>';
		echo '• Remember to delete this file (reset-wizard-helper.php) for security!';
		echo '</div>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=wps-wpr-setup-wizard' ) ) . '" class="button">Go to Setup Wizard</a>';
	}
} else {
	$reset_url = add_query_arg(
		array(
			'action'   => 'reset',
			'_wpnonce' => wp_create_nonce( 'reset_wizard' ),
		)
	);
	?>
		<div class="info">
			<strong>This will:</strong><br>
			• Delete the wizard completed flag<br>
			• Delete the wizard skipped flag<br>
			• Set the activation redirect transient<br>
			• Allow the setup wizard to be displayed again
		</div>

		<a href="<?php echo esc_url( $reset_url ); ?>" class="button">Reset Setup Wizard</a>

		<div class="warning" style="margin-top: 30px;">
			<strong>Security Notice:</strong><br>
			Please delete this file (reset-wizard-helper.php) after use to prevent unauthorized access.
		</div>
	<?php
}
?>
	</div>
</body>
</html>
