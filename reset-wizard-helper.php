<?php
/**
 * Helper Script to Reset Setup Wizard
 *
 * Development/maintenance helper only; it is excluded from release builds.
 * Open it in the browser as an administrator and confirm with the button
 * (POST + nonce). Plain GET requests never change settings.
 */

// Load WordPress.
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Security check - only admins.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'points-and-rewards-for-woocommerce' ), 403 );
}

$wps_wpr_request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';

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
if ( 'POST' === $wps_wpr_request_method && isset( $_POST['action'] ) && 'reset' === $_POST['action'] ) {
	// Verify nonce.
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'reset_wizard' ) ) {
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
	?>
		<div class="info">
			<strong>This will:</strong><br>
			• Delete the wizard completed flag<br>
			• Delete the wizard skipped flag<br>
			• Set the activation redirect transient<br>
			• Allow the setup wizard to be displayed again
		</div>

		<form method="post">
			<input type="hidden" name="action" value="reset">
			<?php wp_nonce_field( 'reset_wizard' ); ?>
			<button type="submit" class="button">Reset Setup Wizard</button>
		</form>

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
