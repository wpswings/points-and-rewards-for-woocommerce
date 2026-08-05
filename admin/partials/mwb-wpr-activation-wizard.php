<?php
/**
 * Activation Wizard Template
 *
 * Provides a guided 5-step setup wizard for new installations.
 * Reduces setup complexity and improves time-to-value for merchants.
 *
 * @package    Points_And_Rewards_For_WooCommerce
 * @subpackage Points_And_Rewards_For_WooCommerce/admin/partials
 * @since      1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get current step from query param or default to 1.
$current_step = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
$current_step = max( 1, min( 5, $current_step ) ); // Clamp between 1-5.

// Get existing settings if available.
$general_settings = get_option( 'mwb_wpr_settings_gallery', array() );
$other_settings   = get_option( 'mwb_wpr_other_settings', array() );
?>

<div class="mwb-wpr-activation-wizard-wrap">
	<div class="mwb-wpr-wizard-header">
		<div class="mwb-wpr-wizard-logo">
			<h1><?php esc_html_e( 'Welcome to Points and Rewards for WooCommerce!', 'points-and-rewards-for-woocommerce' ); ?></h1>
			<p><?php esc_html_e( "Let's get your points and rewards system set up in just 5 quick steps.", 'points-and-rewards-for-woocommerce' ); ?></p>
		</div>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=mwb-rwpr-setting&mwb_wpr_skip_wizard=1' ) ); ?>" class="mwb-wpr-skip-wizard">
			<?php esc_html_e( 'Skip Setup Wizard', 'points-and-rewards-for-woocommerce' ); ?>
		</a>
	</div>

	<!-- Progress Bar -->
	<div class="mwb-wpr-wizard-progress">
		<div class="mwb-wpr-progress-bar">
			<div class="mwb-wpr-progress-fill" style="width: <?php echo esc_attr( ( $current_step / 5 ) * 100 ); ?>%"></div>
		</div>
		<div class="mwb-wpr-progress-steps">
			<?php
			$steps = array(
				1 => __( 'Basic Setup', 'points-and-rewards-for-woocommerce' ),
				2 => __( 'Earning Rules', 'points-and-rewards-for-woocommerce' ),
				3 => __( 'Redemption', 'points-and-rewards-for-woocommerce' ),
				4 => __( 'Notifications', 'points-and-rewards-for-woocommerce' ),
				5 => __( 'Complete', 'points-and-rewards-for-woocommerce' ),
			);

			foreach ( $steps as $step_num => $step_name ) {
				$step_class = 'mwb-wpr-progress-step';
				if ( $step_num < $current_step ) {
					$step_class .= ' completed';
				} elseif ( $step_num === $current_step ) {
					$step_class .= ' active';
				}
				?>
				<div class="<?php echo esc_attr( $step_class ); ?>">
					<span class="mwb-wpr-step-number"><?php echo esc_html( $step_num ); ?></span>
					<span class="mwb-wpr-step-name"><?php echo esc_html( $step_name ); ?></span>
				</div>
				<?php
			}
			?>
		</div>
	</div>

	<!-- Wizard Content -->
	<div class="mwb-wpr-wizard-content">
		<form id="mwb-wpr-wizard-form" method="post">
			<?php wp_nonce_field( 'mwb_wpr_wizard_save', 'mwb_wpr_wizard_nonce' ); ?>
			<input type="hidden" name="current_step" value="<?php echo esc_attr( $current_step ); ?>">

			<?php if ( 1 === $current_step ) : ?>
				<!-- Step 1: Basic Setup -->
				<div class="mwb-wpr-wizard-step" data-step="1">
					<h2><?php esc_html_e( 'Step 1: Basic Setup', 'points-and-rewards-for-woocommerce' ); ?></h2>
					<p class="mwb-wpr-step-description"><?php esc_html_e( "Let's start with the basics. Choose how you want to refer to points in your store.", 'points-and-rewards-for-woocommerce' ); ?></p>

					<!-- Quick Start Templates -->
					<div class="mwb-wpr-quick-start-templates">
						<h3><?php esc_html_e( 'Quick Start Templates', 'points-and-rewards-for-woocommerce' ); ?></h3>
						<p><?php esc_html_e( 'Choose a pre-configured template to get started faster:', 'points-and-rewards-for-woocommerce' ); ?></p>
						<div class="mwb-wpr-template-cards">
							<div class="mwb-wpr-template-card" data-template="simple">
								<div class="mwb-wpr-template-icon">🏪</div>
								<h4><?php esc_html_e( 'Simple Store', 'points-and-rewards-for-woocommerce' ); ?></h4>
								<p><?php esc_html_e( 'Perfect for straightforward reward programs. Earn points on purchases, redeem at checkout.', 'points-and-rewards-for-woocommerce' ); ?></p>
								<button type="button" class="button button-primary mwb-wpr-apply-template" data-template="simple">
									<?php esc_html_e( 'Use This Template', 'points-and-rewards-for-woocommerce' ); ?>
								</button>
							</div>

							<div class="mwb-wpr-template-card" data-template="subscription">
								<div class="mwb-wpr-template-icon">🔄</div>
								<h4><?php esc_html_e( 'Subscription Store', 'points-and-rewards-for-woocommerce' ); ?></h4>
								<p><?php esc_html_e( 'Optimized for recurring revenue. Higher points for subscriptions, loyalty bonuses.', 'points-and-rewards-for-woocommerce' ); ?></p>
								<button type="button" class="button button-primary mwb-wpr-apply-template" data-template="subscription">
									<?php esc_html_e( 'Use This Template', 'points-and-rewards-for-woocommerce' ); ?>
								</button>
							</div>

							<div class="mwb-wpr-template-card" data-template="multivendor">
								<div class="mwb-wpr-template-icon">🏬</div>
								<h4><?php esc_html_e( 'Multi-Vendor', 'points-and-rewards-for-woocommerce' ); ?></h4>
								<p><?php esc_html_e( 'Built for marketplace platforms. Flexible earning rules, vendor-specific settings.', 'points-and-rewards-for-woocommerce' ); ?></p>
								<button type="button" class="button button-primary mwb-wpr-apply-template" data-template="multivendor">
									<?php esc_html_e( 'Use This Template', 'points-and-rewards-for-woocommerce' ); ?>
								</button>
							</div>

							<div class="mwb-wpr-template-card" data-template="custom">
								<div class="mwb-wpr-template-icon">⚙️</div>
								<h4><?php esc_html_e( 'Custom Configuration', 'points-and-rewards-for-woocommerce' ); ?></h4>
								<p><?php esc_html_e( 'Configure everything manually. Full control over all settings.', 'points-and-rewards-for-woocommerce' ); ?></p>
								<button type="button" class="button mwb-wpr-apply-template" data-template="custom">
									<?php esc_html_e( 'Configure Manually', 'points-and-rewards-for-woocommerce' ); ?>
								</button>
							</div>
						</div>
					</div>

					<div class="mwb-wpr-wizard-fields" style="display: none;" id="mwb-wpr-custom-fields">
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="mwb_wpr_points_name"><?php esc_html_e( 'Points Name', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<input type="text" id="mwb_wpr_points_name" name="mwb_wpr_points_name"
										value="<?php echo esc_attr( isset( $general_settings['mwb_wpr_points_name'] ) ? $general_settings['mwb_wpr_points_name'] : 'Points' ); ?>"
										class="regular-text" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>">
									<p class="description"><?php esc_html_e( 'How you refer to points in your store (e.g., "Points", "Coins", "Stars").', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="mwb_wpr_points_display_position"><?php esc_html_e( 'Display Points On', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<select id="mwb_wpr_points_display_position" name="mwb_wpr_points_display_position[]" class="mwb-wpr-select2" multiple="multiple">
										<option value="shop_page"><?php esc_html_e( 'Shop Page', 'points-and-rewards-for-woocommerce' ); ?></option>
										<option value="product_page"><?php esc_html_e( 'Product Page', 'points-and-rewards-for-woocommerce' ); ?></option>
										<option value="cart_page"><?php esc_html_e( 'Cart Page', 'points-and-rewards-for-woocommerce' ); ?></option>
										<option value="checkout_page"><?php esc_html_e( 'Checkout Page', 'points-and-rewards-for-woocommerce' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Where should customers see their points and earning potential?', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

			<?php elseif ( 2 === $current_step ) : ?>
				<!-- Step 2: Earning Rules -->
				<div class="mwb-wpr-wizard-step" data-step="2">
					<h2><?php esc_html_e( 'Step 2: Earning Rules', 'points-and-rewards-for-woocommerce' ); ?></h2>
					<p class="mwb-wpr-step-description"><?php esc_html_e( 'Configure how customers earn points in your store.', 'points-and-rewards-for-woocommerce' ); ?></p>

					<div class="mwb-wpr-wizard-fields">
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="mwb_wpr_earning_enable"><?php esc_html_e( 'Enable Points Earning', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<label class="mwb-wpr-toggle">
										<input type="checkbox" id="mwb_wpr_earning_enable" name="mwb_wpr_earning_enable" value="1" checked>
										<span class="mwb-wpr-toggle-slider"></span>
									</label>
									<p class="description"><?php esc_html_e( 'Allow customers to earn points on purchases.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="mwb_wpr_earning_rate"><?php esc_html_e( 'Earning Rate', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<input type="number" id="mwb_wpr_earning_rate" name="mwb_wpr_earning_rate" value="1" min="0" step="0.01" class="small-text">
									<?php esc_html_e( 'points per', 'points-and-rewards-for-woocommerce' ); ?>
									<input type="number" id="mwb_wpr_earning_currency" name="mwb_wpr_earning_currency" value="1" min="0.01" step="0.01" class="small-text">
									<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
									<p class="description"><?php esc_html_e( 'Example: 1 point per $1 spent means customers earn 100 points on a $100 order.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="mwb_wpr_signup_points_enable"><?php esc_html_e( 'Signup Bonus', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<label class="mwb-wpr-toggle">
										<input type="checkbox" id="mwb_wpr_signup_points_enable" name="mwb_wpr_signup_points_enable" value="1">
										<span class="mwb-wpr-toggle-slider"></span>
									</label>
									<input type="number" id="mwb_wpr_signup_points_value" name="mwb_wpr_signup_points_value" value="100" min="0" class="small-text" style="margin-left: 10px;">
									<span><?php esc_html_e( 'points for new account registration', 'points-and-rewards-for-woocommerce' ); ?></span>
									<p class="description"><?php esc_html_e( 'Reward customers for creating an account.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

			<?php elseif ( 3 === $current_step ) : ?>
				<!-- Step 3: Redemption -->
				<div class="mwb-wpr-wizard-step" data-step="3">
					<h2><?php esc_html_e( 'Step 3: Redemption', 'points-and-rewards-for-woocommerce' ); ?></h2>
					<p class="mwb-wpr-step-description"><?php esc_html_e( 'Set up how customers can redeem their points for discounts.', 'points-and-rewards-for-woocommerce' ); ?></p>

					<div class="mwb-wpr-wizard-fields">
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="mwb_wpr_redemption_enable"><?php esc_html_e( 'Enable Points Redemption', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<label class="mwb-wpr-toggle">
										<input type="checkbox" id="mwb_wpr_redemption_enable" name="mwb_wpr_redemption_enable" value="1" checked>
										<span class="mwb-wpr-toggle-slider"></span>
									</label>
									<p class="description"><?php esc_html_e( 'Allow customers to redeem points for discounts.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="mwb_wpr_redemption_rate"><?php esc_html_e( 'Redemption Rate', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<input type="number" id="mwb_wpr_redemption_rate" name="mwb_wpr_redemption_rate" value="100" min="1" step="1" class="small-text">
									<?php esc_html_e( 'points =', 'points-and-rewards-for-woocommerce' ); ?>
									<input type="number" id="mwb_wpr_redemption_value" name="mwb_wpr_redemption_value" value="1" min="0.01" step="0.01" class="small-text">
									<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
									<p class="description"><?php esc_html_e( 'Example: 100 points = $1 means 1000 points gives a $10 discount.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="mwb_wpr_min_redeem_points"><?php esc_html_e( 'Minimum Points to Redeem', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<input type="number" id="mwb_wpr_min_redeem_points" name="mwb_wpr_min_redeem_points" value="100" min="0" class="small-text">
									<span><?php esc_html_e( 'points', 'points-and-rewards-for-woocommerce' ); ?></span>
									<p class="description"><?php esc_html_e( 'Customers must have at least this many points to redeem.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="mwb_wpr_redeem_location"><?php esc_html_e( 'Allow Redemption On', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<select id="mwb_wpr_redeem_location" name="mwb_wpr_redeem_location">
										<option value="cart"><?php esc_html_e( 'Cart Page Only', 'points-and-rewards-for-woocommerce' ); ?></option>
										<option value="checkout"><?php esc_html_e( 'Checkout Page Only', 'points-and-rewards-for-woocommerce' ); ?></option>
										<option value="both" selected><?php esc_html_e( 'Both Cart and Checkout', 'points-and-rewards-for-woocommerce' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Where customers can apply their points.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

			<?php elseif ( 4 === $current_step ) : ?>
				<!-- Step 4: Notifications -->
				<div class="mwb-wpr-wizard-step" data-step="4">
					<h2><?php esc_html_e( 'Step 4: Notifications', 'points-and-rewards-for-woocommerce' ); ?></h2>
					<p class="mwb-wpr-step-description"><?php esc_html_e( 'Configure how you want to notify customers about their points.', 'points-and-rewards-for-woocommerce' ); ?></p>

					<div class="mwb-wpr-wizard-fields">
						<table class="form-table">
							<tr>
								<th scope="row">
									<label><?php esc_html_e( 'On-Site Notifications', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<label>
										<input type="checkbox" name="mwb_wpr_show_points_my_account" value="1" checked>
										<?php esc_html_e( 'Display points balance in My Account', 'points-and-rewards-for-woocommerce' ); ?>
									</label>
									<br>
									<label>
										<input type="checkbox" name="mwb_wpr_show_points_product_pages" value="1" checked>
										<?php esc_html_e( 'Show earning potential on product pages', 'points-and-rewards-for-woocommerce' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Make points visible throughout your store.', 'points-and-rewards-for-woocommerce' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label><?php esc_html_e( 'Pro Features', 'points-and-rewards-for-woocommerce' ); ?></label>
								</th>
								<td>
									<p class="description">
										<?php
										printf(
											/* translators: %s: Link to upgrade */
											esc_html__( 'Want email notifications, SMS alerts, and advanced notification features? %s', 'points-and-rewards-for-woocommerce' ),
											'<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-wizard&utm_medium=par-org-backend&utm_campaign=go-pro" target="_blank"><strong>' . esc_html__( 'Upgrade to Pro', 'points-and-rewards-for-woocommerce' ) . '</strong></a>'
										);
										?>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</div>

			<?php elseif ( 5 === $current_step ) : ?>
				<!-- Step 5: Complete -->
				<div class="mwb-wpr-wizard-step" data-step="5">
					<div class="mwb-wpr-wizard-complete">
						<div class="mwb-wpr-complete-icon">✓</div>
						<h2><?php esc_html_e( 'Setup Complete!', 'points-and-rewards-for-woocommerce' ); ?></h2>
						<p class="mwb-wpr-step-description"><?php esc_html_e( 'Your points and rewards program is ready to go. Here\'s a summary of your configuration:', 'points-and-rewards-for-woocommerce' ); ?></p>

						<div class="mwb-wpr-wizard-summary" id="mwb-wpr-wizard-summary">
							<!-- Summary will be populated by JavaScript -->
						</div>

						<div class="mwb-wpr-next-steps">
							<h3><?php esc_html_e( 'Next Steps', 'points-and-rewards-for-woocommerce' ); ?></h3>
							<div class="mwb-wpr-next-steps-cards">
								<div class="mwb-wpr-next-step-card">
									<span class="dashicons dashicons-admin-settings"></span>
									<h4><?php esc_html_e( 'Advanced Settings', 'points-and-rewards-for-woocommerce' ); ?></h4>
									<p><?php esc_html_e( 'Fine-tune your points program with additional options.', 'points-and-rewards-for-woocommerce' ); ?></p>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=mwb-rwpr-setting' ) ); ?>" class="button">
										<?php esc_html_e( 'Go to Settings', 'points-and-rewards-for-woocommerce' ); ?>
									</a>
								</div>

								<div class="mwb-wpr-next-step-card">
									<span class="dashicons dashicons-book"></span>
									<h4><?php esc_html_e( 'Documentation', 'points-and-rewards-for-woocommerce' ); ?></h4>
									<p><?php esc_html_e( 'Learn everything about the plugin from our comprehensive docs.', 'points-and-rewards-for-woocommerce' ); ?></p>
									<a href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/" target="_blank" class="button">
										<?php esc_html_e( 'View Documentation', 'points-and-rewards-for-woocommerce' ); ?>
									</a>
								</div>

								<div class="mwb-wpr-next-step-card">
									<span class="dashicons dashicons-video-alt3"></span>
									<h4><?php esc_html_e( 'Video Tutorials', 'points-and-rewards-for-woocommerce' ); ?></h4>
									<p><?php esc_html_e( 'Watch step-by-step video guides to master the plugin.', 'points-and-rewards-for-woocommerce' ); ?></p>
									<a href="https://www.youtube.com/@wpswings" target="_blank" class="button">
										<?php esc_html_e( 'Watch Videos', 'points-and-rewards-for-woocommerce' ); ?>
									</a>
								</div>

								<div class="mwb-wpr-next-step-card">
									<span class="dashicons dashicons-star-filled"></span>
									<h4><?php esc_html_e( 'Upgrade to Pro', 'points-and-rewards-for-woocommerce' ); ?></h4>
									<p><?php esc_html_e( 'Unlock advanced features like referrals, memberships, and more.', 'points-and-rewards-for-woocommerce' ); ?></p>
									<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-wizard&utm_medium=par-org-backend&utm_campaign=go-pro" target="_blank" class="button button-primary">
										<?php esc_html_e( 'View Pro Features', 'points-and-rewards-for-woocommerce' ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Navigation Buttons -->
			<div class="mwb-wpr-wizard-actions">
				<?php if ( $current_step > 1 && $current_step < 5 ) : ?>
					<button type="button" class="button button-large mwb-wpr-wizard-prev">
						<?php esc_html_e( 'Previous', 'points-and-rewards-for-woocommerce' ); ?>
					</button>
				<?php endif; ?>

				<?php if ( $current_step < 4 ) : ?>
					<button type="button" class="button button-primary button-large mwb-wpr-wizard-next">
						<?php esc_html_e( 'Next Step', 'points-and-rewards-for-woocommerce' ); ?>
					</button>
				<?php elseif ( 4 === $current_step ) : ?>
					<button type="submit" class="button button-primary button-large mwb-wpr-wizard-complete">
						<?php esc_html_e( 'Complete Setup', 'points-and-rewards-for-woocommerce' ); ?>
					</button>
				<?php elseif ( 5 === $current_step ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mwb-rwpr-setting' ) ); ?>" class="button button-primary button-large">
						<?php esc_html_e( 'Go to Dashboard', 'points-and-rewards-for-woocommerce' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</form>
	</div>
</div>
