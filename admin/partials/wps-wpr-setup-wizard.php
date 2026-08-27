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
						<input type="checkbox" name="step0[plugin_enable]" value="1" <?php checked( ! empty( $general_settings['wps_wpr_general_setting_enable'] ), true ); ?>>
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
						<input type="checkbox" name="step1[redemption_cart_enable]" value="1" <?php checked( ! empty( $general_settings['wps_wpr_custom_points_on_cart'] ), true ); ?>>
						<span class="wps-wpr-slider"></span>
					</label>
					<label><?php esc_html_e( 'Redemption Over Cart Sub-total', 'points-and-rewards-for-woocommerce' ); ?></label>
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Allow customers to apply points during Cart', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>

				<div class="wps-wpr-form-group">
					<label class="wps-wpr-switch">
						<input type="checkbox" name="step1[redemption_checkout_enable]" value="1" <?php checked( ! empty( $general_settings['wps_wpr_apply_points_checkout'] ), true ); ?>>
						<span class="wps-wpr-slider"></span>
					</label>
					<label><?php esc_html_e( 'Apply Points on Checkout', 'points-and-rewards-for-woocommerce' ); ?></label>
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Allow customers to apply points during Checkout', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>

				<div class="wps-wpr-form-group">
					<label for="redemption_points"><?php esc_html_e( 'Conversion Rate for Cart Sub-total Redemption', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps-wpr-currency-row">
						<input type="number" id="redemption_points" name="step1[redemption_points]"
							value="<?php echo esc_attr( isset( $general_settings['wps_wpr_cart_points_rate'] ) ? $general_settings['wps_wpr_cart_points_rate'] : 100 ); ?>"
							min="1" step="1" class="wps-wpr-input" style="width: 120px;">
						<span style="margin: 0 8px;"><?php esc_html_e( 'Points =', 'points-and-rewards-for-woocommerce' ); ?></span>
						<span style="margin-right: 8px;"><?php echo esc_html( $currency_symbol ); ?></span>
						<input type="number" id="redemption_value" name="step1[redemption_value]"
							value="<?php echo esc_attr( isset( $general_settings['wps_wpr_cart_price_rate'] ) ? $general_settings['wps_wpr_cart_price_rate'] : 1 ); ?>"
							min="0" step="0.01" class="wps-wpr-input" style="width: 120px;">
					</div>
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Set how many points equal to discount amount', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>
			</div>

			<!-- Step 2: Referral Settings -->
			<div class="wps-wpr-wizard-step" data-step="2">
				<h2><?php esc_html_e( 'Step 2: Referral Program', 'points-and-rewards-for-woocommerce' ); ?></h2>
				<p class="wps-wpr-step-description"><?php esc_html_e( 'Reward customers for referring their friends to your store.', 'points-and-rewards-for-woocommerce' ); ?></p>

				<div class="wps-wpr-form-group">
					<label class="wps-wpr-switch">
						<input type="checkbox" name="step2[referral_enable]" value="1" <?php checked( ! empty( $general_settings['wps_wpr_general_refer_enable'] ), true ); ?>>
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
					<label><?php esc_html_e( 'Select a Points Tab Template for My Account', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps-wpr-template-grid">
						<?php
						$templates = array(
							'temp_one'   => __( 'Template One', 'points-and-rewards-for-woocommerce' ),
							'temp_two'   => __( 'Template Two', 'points-and-rewards-for-woocommerce' ),
							'temp_three' => __( 'Template Three', 'points-and-rewards-for-woocommerce' ),
							'temp_four'  => __( 'Template Four', 'points-and-rewards-for-woocommerce' ),
						);

						$current_template = isset( $other_settings['wps_wpr_choose_account_page_temp'] ) ? $other_settings['wps_wpr_choose_account_page_temp'] : 'temp_one';

						foreach ( $templates as $template_key => $template_name ) {
							$is_checked = ( $template_key === $current_template ) ? 'checked' : '';
							$card_class = ( $template_key === $current_template ) ? 'wps-wpr-template-card wps-wpr-template-selected' : 'wps-wpr-template-card';
							?>
							<label class="<?php echo esc_attr( $card_class ); ?>">
								<input type="radio" name="step3[point_tab_template]" value="<?php echo esc_attr( $template_key ); ?>" <?php echo esc_attr( $is_checked ); ?>>
								<div class="wps-wpr-template-preview">
									<div class="wps-wpr-template-icon">📊</div>
									<span><?php echo esc_html( $template_name ); ?></span>
								</div>
							</label>
							<?php
						}
						?>
					</div>
					<p class="wps-wpr-field-description"><?php esc_html_e( 'Choose a layout template for the Points section under the My Account tab', 'points-and-rewards-for-woocommerce' ); ?></p>
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
						<input type="checkbox" name="step4[signup_enable]" value="1" <?php checked( ! empty( $general_settings['wps_wpr_general_signup'] ), true ); ?>>
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
				<!-- Content will be shown below navigation -->
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

			<!-- Additional Information Section - Only visible on Step 5 -->
			<div class="wps-wpr-wizard-info-section" data-step="5" style="display: none; margin-top: 40px; padding-top: 30px; border-top: 2px solid #e5e7eb;">

				<!-- Hero Section -->
				<div style="text-align: center; padding: 40px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
					<div style="font-size: 48px; margin-bottom: 16px;">🎉</div>
					<h2 style="font-size: 32px; margin: 0 0 12px 0; color: white; font-weight: 700;">
						<?php esc_html_e( 'Congratulations! Your Loyalty Program is Ready!', 'points-and-rewards-for-woocommerce' ); ?>
					</h2>
					<p style="font-size: 18px; margin: 0; color: rgba(255,255,255,0.95); line-height: 1.6;">
						<?php esc_html_e( 'You\'re just steps away from launching a powerful rewards system for your customers!', 'points-and-rewards-for-woocommerce' ); ?>
					</p>
				</div>

				<!-- Configuration Summary Cards -->
				<div style="margin-bottom: 40px;">
					<h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 0 0 20px 0; text-align: center;">
						<?php esc_html_e( 'Your Current Configuration', 'points-and-rewards-for-woocommerce' ); ?>
					</h3>
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin: 24px 0;">

						<!-- Plugin Status -->
						<div style="background: #ffffff; border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.3s ease;">
							<div style="font-size: 40px; margin-bottom: 12px;">⚙️</div>
							<h4 style="font-size: 16px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">
								<?php esc_html_e( 'Plugin Status', 'points-and-rewards-for-woocommerce' ); ?>
							</h4>
							<p style="font-size: 18px; font-weight: 600; color: #10b981; margin: 0;">
								<?php echo ! empty( $general_settings['wps_wpr_general_setting_enable'] ) ? esc_html__( 'Enabled ✓', 'points-and-rewards-for-woocommerce' ) : esc_html__( 'Disabled', 'points-and-rewards-for-woocommerce' ); ?>
							</p>
						</div>

						<!-- Redemption Settings -->
						<div style="background: #ffffff; border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.3s ease;">
							<div style="font-size: 40px; margin-bottom: 12px;">🎁</div>
							<h4 style="font-size: 16px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">
								<?php esc_html_e( 'Redemption', 'points-and-rewards-for-woocommerce' ); ?>
							</h4>
							<p style="font-size: 18px; font-weight: 600; color: #10b981; margin: 0 0 8px 0;">
								<?php
								$cart_enabled     = ! empty( $general_settings['wps_wpr_custom_points_on_cart'] );
								$checkout_enabled = ! empty( $general_settings['wps_wpr_apply_points_checkout'] );
								if ( $cart_enabled || $checkout_enabled ) {
									$locations = array();
									if ( $cart_enabled ) {
										$locations[] = esc_html__( 'Cart', 'points-and-rewards-for-woocommerce' );
									}
									if ( $checkout_enabled ) {
										$locations[] = esc_html__( 'Checkout', 'points-and-rewards-for-woocommerce' );
									}
									echo esc_html( implode( ' & ', $locations ) ) . ' ✓';
								} else {
									esc_html_e( 'Disabled', 'points-and-rewards-for-woocommerce' );
								}
								?>
							</p>
							<p style="font-size: 14px; color: #6b7280; margin: 0;">
								<?php
								$points = isset( $general_settings['wps_wpr_cart_points_rate'] ) ? $general_settings['wps_wpr_cart_points_rate'] : 100;
								$value  = isset( $general_settings['wps_wpr_cart_price_rate'] ) ? $general_settings['wps_wpr_cart_price_rate'] : 1;
								/* translators: %1$d: points value, %2$s: currency symbol, %3$s: price value */
								echo sprintf( esc_html__( '%1$d Points = %2$s%3$s', 'points-and-rewards-for-woocommerce' ), absint( $points ), esc_html( $currency_symbol ), esc_html( $value ) );
								?>
							</p>
						</div>

						<!-- Referral Program -->
						<div style="background: #ffffff; border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.3s ease;">
							<div style="font-size: 40px; margin-bottom: 12px;">👥</div>
							<h4 style="font-size: 16px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">
								<?php esc_html_e( 'Referral Program', 'points-and-rewards-for-woocommerce' ); ?>
							</h4>
							<p style="font-size: 18px; font-weight: 600; color: #10b981; margin: 0 0 8px 0;">
								<?php echo ! empty( $general_settings['wps_wpr_general_refer_enable'] ) ? esc_html__( 'Enabled ✓', 'points-and-rewards-for-woocommerce' ) : esc_html__( 'Disabled', 'points-and-rewards-for-woocommerce' ); ?>
							</p>
							<p style="font-size: 14px; color: #6b7280; margin: 0;">
								<?php
								$referral_points = isset( $general_settings['wps_wpr_general_refer_value'] ) ? $general_settings['wps_wpr_general_refer_value'] : 50;
								/* translators: %d: points per referral */
								echo sprintf( esc_html__( '%d points per referral', 'points-and-rewards-for-woocommerce' ), absint( $referral_points ) );
								?>
							</p>
						</div>

						<!-- Signup Points -->
						<div style="background: #ffffff; border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.3s ease;">
							<div style="font-size: 40px; margin-bottom: 12px;">⭐</div>
							<h4 style="font-size: 16px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">
								<?php esc_html_e( 'Signup Points', 'points-and-rewards-for-woocommerce' ); ?>
							</h4>
							<p style="font-size: 18px; font-weight: 600; color: #10b981; margin: 0 0 8px 0;">
								<?php echo ! empty( $general_settings['wps_wpr_general_signup'] ) ? esc_html__( 'Enabled ✓', 'points-and-rewards-for-woocommerce' ) : esc_html__( 'Disabled', 'points-and-rewards-for-woocommerce' ); ?>
							</p>
							<p style="font-size: 14px; color: #6b7280; margin: 0;">
								<?php
								$signup_points = isset( $general_settings['wps_wpr_general_signup_value'] ) ? $general_settings['wps_wpr_general_signup_value'] : 10;
								/* translators: %d: points on signup */
								echo sprintf( esc_html__( '%d points on signup', 'points-and-rewards-for-woocommerce' ), absint( $signup_points ) );
								?>
							</p>
						</div>

						<!-- Points Tab Layout -->
						<div style="background: #ffffff; border: 2px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.3s ease;">
							<div style="font-size: 40px; margin-bottom: 12px;">🎨</div>
							<h4 style="font-size: 16px; font-weight: 600; color: #374151; margin: 0 0 12px 0;">
								<?php esc_html_e( 'Points Tab Layout', 'points-and-rewards-for-woocommerce' ); ?>
							</h4>
							<p style="font-size: 18px; font-weight: 600; color: #667eea; margin: 0;">
								<?php
								$template_names = array(
									'temp_one'   => __( 'Template One', 'points-and-rewards-for-woocommerce' ),
									'temp_two'   => __( 'Template Two', 'points-and-rewards-for-woocommerce' ),
									'temp_three' => __( 'Template Three', 'points-and-rewards-for-woocommerce' ),
									'temp_four'  => __( 'Template Four', 'points-and-rewards-for-woocommerce' ),
								);
								$selected_template = isset( $other_settings['wps_wpr_choose_account_page_temp'] ) ? $other_settings['wps_wpr_choose_account_page_temp'] : 'temp_one';
								echo isset( $template_names[ $selected_template ] ) ? esc_html( $template_names[ $selected_template ] ) : esc_html__( 'Template One', 'points-and-rewards-for-woocommerce' );
								?>
							</p>
						</div>

					</div>
				</div>

				<!-- What Happens Next Section -->
				<div style="background: #f9fafb; border-left: 4px solid #667eea; border-radius: 8px; padding: 24px; margin-bottom: 30px;">
					<h3 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
						<span style="font-size: 24px;">🚀</span>
						<?php esc_html_e( 'What Happens Next?', 'points-and-rewards-for-woocommerce' ); ?>
					</h3>
					<ul style="margin: 0; padding: 0 0 0 20px; color: #4b5563; line-height: 1.8;">
						<li style="margin-bottom: 8px;">
							<strong><?php esc_html_e( 'Plugin Activation:', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<?php esc_html_e( 'Your loyalty program will be immediately active on your store', 'points-and-rewards-for-woocommerce' ); ?>
						</li>
						<li style="margin-bottom: 8px;">
							<strong><?php esc_html_e( 'Customer Points Tab:', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<?php esc_html_e( 'A new "Points" tab will appear in customer My Account pages', 'points-and-rewards-for-woocommerce' ); ?>
						</li>
						<li style="margin-bottom: 8px;">
							<strong><?php esc_html_e( 'Redemption Ready:', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<?php esc_html_e( 'Customers can start earning and redeeming points immediately', 'points-and-rewards-for-woocommerce' ); ?>
						</li>
						<li style="margin-bottom: 8px;">
							<strong><?php esc_html_e( 'Customize Anytime:', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<?php esc_html_e( 'You can adjust all settings from the main settings page whenever needed', 'points-and-rewards-for-woocommerce' ); ?>
						</li>
					</ul>
				</div>

				<!-- Pro Features Highlight -->
				<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px; padding: 32px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
					<div style="text-align: center; margin-bottom: 20px;">
						<div style="font-size: 48px; margin-bottom: 12px;">💎</div>
						<h3 style="font-size: 24px; font-weight: 700; color: white; margin: 0 0 8px 0;">
							<?php esc_html_e( 'Want More? Upgrade to Pro!', 'points-and-rewards-for-woocommerce' ); ?>
						</h3>
						<p style="font-size: 16px; margin: 0; color: rgba(255,255,255,0.9);">
							<?php esc_html_e( 'Unlock powerful features to maximize customer engagement', 'points-and-rewards-for-woocommerce' ); ?>
						</p>
					</div>
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
						<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; backdrop-filter: blur(10px);">
							<div style="font-size: 24px; margin-bottom: 8px;">🏆</div>
							<strong style="display: block; margin-bottom: 4px; color: white;"><?php esc_html_e( 'Membership Levels', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<span style="font-size: 14px; color: rgba(255,255,255,0.8);"><?php esc_html_e( 'Create VIP tiers with exclusive benefits', 'points-and-rewards-for-woocommerce' ); ?></span>
						</div>
						<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; backdrop-filter: blur(10px);">
							<div style="font-size: 24px; margin-bottom: 8px;">🎮</div>
							<strong style="display: block; margin-bottom: 4px; color: white;"><?php esc_html_e( 'Gamification', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<span style="font-size: 14px; color: rgba(255,255,255,0.8);"><?php esc_html_e( 'Badges, levels, and spin-to-win wheels', 'points-and-rewards-for-woocommerce' ); ?></span>
						</div>
						<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; backdrop-filter: blur(10px);">
							<div style="font-size: 24px; margin-bottom: 8px;">⏰</div>
							<strong style="display: block; margin-bottom: 4px; color: white;"><?php esc_html_e( 'Points Expiration', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<span style="font-size: 14px; color: rgba(255,255,255,0.8);"><?php esc_html_e( 'Set expiry rules to drive urgency', 'points-and-rewards-for-woocommerce' ); ?></span>
						</div>
						<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; backdrop-filter: blur(10px);">
							<div style="font-size: 24px; margin-bottom: 8px;">📧</div>
							<strong style="display: block; margin-bottom: 4px; color: white;"><?php esc_html_e( 'Email Notifications', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<span style="font-size: 14px; color: rgba(255,255,255,0.8);"><?php esc_html_e( 'Automated emails for point activities', 'points-and-rewards-for-woocommerce' ); ?></span>
						</div>
					</div>
					<div style="text-align: center;">
						<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-pro/" target="_blank" rel="noopener noreferrer" style="display: inline-block; background: white; color: #667eea; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; transition: all 0.3s ease;">
							<?php esc_html_e( 'View Pro Features →', 'points-and-rewards-for-woocommerce' ); ?>
						</a>
					</div>
				</div>

				<!-- Help Links -->
				<div style="text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb;">
					<div style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
						<a href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/" target="_blank" rel="noopener noreferrer" style="color: #6b7280; text-decoration: none; font-size: 14px; transition: color 0.2s;">
							📚 <?php esc_html_e( 'Documentation', 'points-and-rewards-for-woocommerce' ); ?>
						</a>
						<span style="color: #d1d5db;">|</span>
						<a href="https://wpswings.com/submit-query/" target="_blank" rel="noopener noreferrer" style="color: #6b7280; text-decoration: none; font-size: 14px; transition: color 0.2s;">
							💬 <?php esc_html_e( 'Need Help?', 'points-and-rewards-for-woocommerce' ); ?>
						</a>
						<span style="color: #d1d5db;">|</span>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-rwpr-setting' ) ); ?>" style="color: #6b7280; text-decoration: none; font-size: 14px; transition: color 0.2s;">
							⚙️ <?php esc_html_e( 'Go to Settings', 'points-and-rewards-for-woocommerce' ); ?>
						</a>
					</div>
				</div>

			</div>
		</form>
	</div>
</div>
