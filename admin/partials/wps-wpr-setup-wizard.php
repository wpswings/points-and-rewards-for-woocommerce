<?php
/**
 * Setup Wizard Template
 *
 * Provides a guided 5-step setup wizard for new installations.
 *
 * @package    Points_And_Rewards_For_WooCommerce
 * @subpackage Points_And_Rewards_For_WooCommerce/admin/partials
 * @since      2.10.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get existing settings if available.
$general_settings = get_option( 'wps_wpr_settings_gallery', array() );
$other_settings   = get_option( 'wps_wpr_other_settings', array() );
$currency_symbol  = get_woocommerce_currency_symbol();
?>

<div class="wps-wpr-setup-wizard-wrap">
	<div class="wps-wpr-wizard-header">
		<div class="wps-wpr-wizard-logo">
			<h1><?php esc_html_e( 'Welcome to Points and Rewards for WooCommerce!', 'points-and-rewards-for-woocommerce' ); ?></h1>
			<p><?php esc_html_e( "Let's configure your loyalty program in just 5 quick steps.", 'points-and-rewards-for-woocommerce' ); ?></p>
		</div>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-rwpr-setting&wps_wpr_skip_wizard=1' ) ); ?>" class="wps-wpr-skip-wizard">
			<?php esc_html_e( 'Skip Setup', 'points-and-rewards-for-woocommerce' ); ?>
		</a>
	</div>

	<!-- Progress Bar -->
	<div class="wps-wpr-wizard-progress">
		<div class="wps-wpr-progress-bar">
			<div class="wps-wpr-progress-fill" style="width: 0%"></div>
		</div>
		<div class="wps-wpr-progress-steps">
			<?php
			$steps = array(
				0 => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
				1 => __( 'Redemption', 'points-and-rewards-for-woocommerce' ),
				2 => __( 'Referral', 'points-and-rewards-for-woocommerce' ),
				3 => __( 'Layout', 'points-and-rewards-for-woocommerce' ),
				4 => __( 'Signup', 'points-and-rewards-for-woocommerce' ),
				5 => __( 'Complete', 'points-and-rewards-for-woocommerce' ),
			);

			foreach ( $steps as $step_num => $step_name ) {
				?>
				<div class="wps-wpr-progress-step" data-step="<?php echo esc_attr( $step_num ); ?>">
					<span class="wps-wpr-step-number"><?php echo esc_html( $step_num ); ?></span>
					<span class="wps-wpr-step-name"><?php echo esc_html( $step_name ); ?></span>
				</div>
				<?php
			}
			?>
		</div>
	</div>

	<!-- Wizard Content -->
	<div class="wps-wpr-wizard-content">
		<form id="wps-wpr-wizard-form" method="post">
			<?php wp_nonce_field( 'wps_wpr_wizard_save', 'wps_wpr_wizard_nonce' ); ?>

			<!-- Step 0: Master Enable -->
			<div class="wps-wpr-wizard-step wps-wpr-wizard-step-active" data-step="0">
				<h2><?php esc_html_e( 'Welcome! Enable Points and Rewards', 'points-and-rewards-for-woocommerce' ); ?></h2>
				<p class="wps-wpr-step-description"><?php esc_html_e( 'Enable the Points and Rewards system for your WooCommerce store.', 'points-and-rewards-for-woocommerce' ); ?></p>

				<div class="wps-wpr-form-group wps-wpr-master-enable">
					<label class="wps-wpr-switch wps-wpr-switch-large">
						<input type="checkbox" name="step0[plugin_enable]" value="1" <?php checked( isset( $general_settings['wps_wpr_general_setting_enable'] ) && '1' === $general_settings['wps_wpr_general_setting_enable'], true, true ); checked( ! isset( $general_settings['wps_wpr_general_setting_enable'] ), true, true ); ?>>
						<span class="wps-wpr-slider"></span>
					</label>
					<label><strong><?php esc_html_e( 'Enable WooCommerce Points and Rewards', 'points-and-rewards-for-woocommerce' ); ?></strong></label>
				</div>

				<div class="wps-wpr-info-box">
					<strong><?php esc_html_e( 'What happens when enabled?', 'points-and-rewards-for-woocommerce' ); ?></strong>
					<ul>
						<li><?php esc_html_e( 'Customers can earn points on purchases', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Points can be redeemed for discounts', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Referral program becomes active', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Points tab appears in customer accounts', 'points-and-rewards-for-woocommerce' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Step 1: Redemption Settings -->
			<div class="wps-wpr-wizard-step" data-step="1">
				<h2><?php esc_html_e( 'Step 1: Redemption Settings', 'points-and-rewards-for-woocommerce' ); ?></h2>
				<p class="wps-wpr-step-description"><?php esc_html_e( 'Configure how customers can redeem their points for discounts.', 'points-and-rewards-for-woocommerce' ); ?></p>

				<div class="wps-wpr-form-group">
					<label class="wps-wpr-switch">
						<input type="checkbox" name="step1[redemption_enable]" value="1" <?php checked( isset( $general_settings['wps_wpr_custom_points_on_cart'] ) && '1' === $general_settings['wps_wpr_custom_points_on_cart'], true, true ); checked( ! isset( $general_settings['wps_wpr_custom_points_on_cart'] ), true, true ); ?>>
						<span class="wps-wpr-slider"></span>
					</label>
					<label><?php esc_html_e( 'Enable Point Redemption', 'points-and-rewards-for-woocommerce' ); ?></label>
				</div>

				<div class="wps-wpr-form-group">
					<label for="redemption_points"><?php esc_html_e( 'Points Required for Discount', 'points-and-rewards-for-woocommerce' ); ?></label>
					<input type="number" id="redemption_points" name="step1[redemption_points]"
						value="<?php echo esc_attr( isset( $general_settings['wps_wpr_cart_points_rate'] ) ? $general_settings['wps_wpr_cart_points_rate'] : 100 ); ?>"
						min="1" step="1" class="wps-wpr-input">
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Number of points needed to get discount', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>

				<div class="wps-wpr-form-group">
					<label for="redemption_value"><?php echo sprintf( esc_html__( 'Discount Value (%s)', 'points-and-rewards-for-woocommerce' ), esc_html( $currency_symbol ) ); ?></label>
					<input type="number" id="redemption_value" name="step1[redemption_value]"
						value="<?php echo esc_attr( isset( $general_settings['wps_wpr_cart_price_rate'] ) ? $general_settings['wps_wpr_cart_price_rate'] : 1 ); ?>"
						min="0" step="0.01" class="wps-wpr-input">
					<p class="wps-wpr-field-description"><?php echo sprintf( esc_html__( 'Discount amount in %s when points are redeemed', 'points-and-rewards-for-woocommerce' ), esc_html( $currency_symbol ) ); ?></p>
				</div>

				<div class="wps-wpr-form-group">
					<label><?php esc_html_e( 'Where Can Points Be Redeemed?', 'points-and-rewards-for-woocommerce' ); ?></label>
					<select name="step1[redeem_location]" class="wps-wpr-select">
						<option value="cart" selected><?php esc_html_e( 'Cart Page', 'points-and-rewards-for-woocommerce' ); ?></option>
					</select>
					<p class="wps-wpr-field-description"><?php esc_html_e( 'In the free version, redemption is available on the cart page', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>
			</div>

			<!-- Step 2: Referral Settings -->
			<div class="wps-wpr-wizard-step" data-step="2">
				<h2><?php esc_html_e( 'Step 2: Referral Program', 'points-and-rewards-for-woocommerce' ); ?></h2>
				<p class="wps-wpr-step-description"><?php esc_html_e( 'Reward customers for referring their friends to your store.', 'points-and-rewards-for-woocommerce' ); ?></p>

				<div class="wps-wpr-form-group">
					<label class="wps-wpr-switch">
						<input type="checkbox" name="step2[referral_enable]" value="1" <?php checked( isset( $general_settings['wps_wpr_general_refer_enable'] ) && '1' === $general_settings['wps_wpr_general_refer_enable'], true, true ); checked( ! isset( $general_settings['wps_wpr_general_refer_enable'] ), true, true ); ?>>
						<span class="wps-wpr-slider"></span>
					</label>
					<label><?php esc_html_e( 'Enable Referral Program', 'points-and-rewards-for-woocommerce' ); ?></label>
				</div>

				<div class="wps-wpr-form-group">
					<label for="referee_points"><?php esc_html_e( 'Referral Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<input type="number" id="referee_points" name="step2[referee_points]"
						value="<?php echo esc_attr( isset( $general_settings['wps_wpr_general_refer_value'] ) ? $general_settings['wps_wpr_general_refer_value'] : 50 ); ?>"
						min="0" step="1" class="wps-wpr-input">
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Points awarded to new customer when they sign up using a referral link', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>

				<div class="wps-wpr-info-box">
					<strong><?php esc_html_e( 'How it works:', 'points-and-rewards-for-woocommerce' ); ?></strong>
					<ul>
						<li><?php esc_html_e( 'Each customer gets a unique referral link in their My Account page', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'When a friend signs up using that link, both receive points', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Points are awarded after the referred friend completes their first order', 'points-and-rewards-for-woocommerce' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Step 3: Point Tab Layout -->
			<div class="wps-wpr-wizard-step" data-step="3">
				<h2><?php esc_html_e( 'Step 3: Point Tab Layout', 'points-and-rewards-for-woocommerce' ); ?></h2>
				<p class="wps-wpr-step-description"><?php esc_html_e( 'Customize how points are displayed in customer accounts.', 'points-and-rewards-for-woocommerce' ); ?></p>

				<div class="wps-wpr-form-group">
					<label><?php esc_html_e( 'Point Tab Template', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps-wpr-template-grid">
						<?php
						$templates = array(
							'template_one' => __( 'Template 1 - Classic', 'points-and-rewards-for-woocommerce' ),
						);

						$current_template = isset( $other_settings['wps_wpr_points_tab_template'] ) ? $other_settings['wps_wpr_points_tab_template'] : 'template_one';

						foreach ( $templates as $template_key => $template_name ) {
							?>
							<label class="wps-wpr-template-card wps-wpr-template-selected">
								<input type="radio" name="step3[point_tab_template]" value="<?php echo esc_attr( $template_key ); ?>" checked>
								<div class="wps-wpr-template-preview">
									<div class="wps-wpr-template-icon">📊</div>
									<span><?php echo esc_html( $template_name ); ?></span>
								</div>
							</label>
							<?php
						}
						?>
					</div>
					<p class="wps-wpr-field-description"><?php esc_html_e( 'More templates available in Pro version', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>

				<div class="wps-wpr-form-group">
					<label class="wps-wpr-switch">
						<input type="checkbox" name="step3[show_points_on_product]" value="1" <?php checked( isset( $other_settings['wps_wpr_show_points_on_product'] ) && 'yes' === $other_settings['wps_wpr_show_points_on_product'], true, true ); checked( ! isset( $other_settings['wps_wpr_show_points_on_product'] ), true, true ); ?>>
						<span class="wps-wpr-slider"></span>
					</label>
					<label><?php esc_html_e( 'Show Potential Points on Product Pages', 'points-and-rewards-for-woocommerce' ); ?></label>
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Display how many points customers can earn by purchasing each product', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>
			</div>

			<!-- Step 4: Signup Points -->
			<div class="wps-wpr-wizard-step" data-step="4">
				<h2><?php esc_html_e( 'Step 4: Signup Points', 'points-and-rewards-for-woocommerce' ); ?></h2>
				<p class="wps-wpr-step-description"><?php esc_html_e( 'Reward new customers with points when they create an account.', 'points-and-rewards-for-woocommerce' ); ?></p>

				<div class="wps-wpr-form-group">
					<label class="wps-wpr-switch">
						<input type="checkbox" name="step4[signup_enable]" value="1" <?php checked( isset( $general_settings['wps_wpr_general_signup'] ) && 1 === intval( $general_settings['wps_wpr_general_signup'] ), true, true ); checked( ! isset( $general_settings['wps_wpr_general_signup'] ), true, true ); ?>>
						<span class="wps-wpr-slider"></span>
					</label>
					<label><?php esc_html_e( 'Enable Signup Points', 'points-and-rewards-for-woocommerce' ); ?></label>
				</div>

				<div class="wps-wpr-form-group">
					<label for="signup_points"><?php esc_html_e( 'Signup Points Value', 'points-and-rewards-for-woocommerce' ); ?></label>
					<input type="number" id="signup_points" name="step4[signup_points]"
						value="<?php echo esc_attr( isset( $general_settings['wps_wpr_general_signup_value'] ) ? $general_settings['wps_wpr_general_signup_value'] : 10 ); ?>"
						min="0" step="1" class="wps-wpr-input">
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Points awarded when a new customer creates an account', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>

				<div class="wps-wpr-info-box">
					<strong><?php esc_html_e( 'Tip:', 'points-and-rewards-for-woocommerce' ); ?></strong>
					<p><?php esc_html_e( 'Signup points help attract new customers and encourage account creation.', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>
			</div>

			<!-- Step 5: Complete Setup -->
			<div class="wps-wpr-wizard-step" data-step="5">
				<h2><?php esc_html_e( 'Step 5: Review & Complete', 'points-and-rewards-for-woocommerce' ); ?></h2>
				<p class="wps-wpr-step-description"><?php esc_html_e( "You're all set! Review your settings and complete the setup.", 'points-and-rewards-for-woocommerce' ); ?></p>

				<div class="wps-wpr-info-box wps-wpr-success-box">
					<strong><?php esc_html_e( 'What happens next?', 'points-and-rewards-for-woocommerce' ); ?></strong>
					<ul>
						<li><?php esc_html_e( 'Your Points & Rewards system will be activated', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Customers can start earning and redeeming points', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'You can adjust all settings anytime from the main settings page', 'points-and-rewards-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Check out the Pro version for advanced features like membership levels, gamification, and more', 'points-and-rewards-for-woocommerce' ); ?></li>
					</ul>
				</div>

				<div class="wps-wpr-info-box wps-wpr-success-box">
					<strong><?php esc_html_e( 'Almost Done!', 'points-and-rewards-for-woocommerce' ); ?></strong>
					<p><?php esc_html_e( 'Click "Complete Setup" to save your settings and start rewarding your customers!', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>
			</div>

			<!-- Wizard Navigation -->
			<div class="wps-wpr-wizard-navigation">
				<button type="button" class="wps-wpr-btn wps-wpr-btn-secondary wps-wpr-btn-prev" style="display: none;">
					<?php esc_html_e( 'Previous', 'points-and-rewards-for-woocommerce' ); ?>
				</button>
				<button type="button" class="wps-wpr-btn wps-wpr-btn-primary wps-wpr-btn-next">
					<?php esc_html_e( 'Next Step', 'points-and-rewards-for-woocommerce' ); ?>
				</button>
				<button type="submit" class="wps-wpr-btn wps-wpr-btn-success wps-wpr-btn-finish" style="display: none;">
					<?php esc_html_e( 'Complete Setup', 'points-and-rewards-for-woocommerce' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
