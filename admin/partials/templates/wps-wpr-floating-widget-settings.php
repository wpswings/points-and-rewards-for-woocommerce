<?php
/**
 * This is the setting template
 *
 * Floating loyalty widget settings.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

// Save floating widget settings.
if ( isset( $_POST['wps_wpr_floating_widget_nonce'], $_POST['wps_wpr_save_floating_widget_settings'] ) && current_user_can( 'manage_options' ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wpr_floating_widget_nonce'] ) ), 'wps-wpr-floating-widget-nonce' ) ) {
		$wps_wpr_fw_fields = array_keys( WPS_WPR_Floating_Widget::wps_wpr_get_default_settings() );
		$wps_wpr_fw_input  = array();
		foreach ( $wps_wpr_fw_fields as $wps_wpr_fw_field ) {
			if ( isset( $_POST[ $wps_wpr_fw_field ] ) ) {
				$wps_wpr_fw_input[ $wps_wpr_fw_field ] = sanitize_text_field( wp_unslash( $_POST[ $wps_wpr_fw_field ] ) );
			}
		}
		update_option( WPS_WPR_Floating_Widget::OPTION, WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings( $wps_wpr_fw_input ) );
		$settings_obj->wps_wpr_settings_saved();
	}
}

$wps_wpr_fw = WPS_WPR_Floating_Widget::wps_wpr_get_settings();
?>

<div class="wps_wpr_floating_widget_main_wrappers">
	<main class="wps_wpr_main_floating_widget_wrapper">
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Floating Loyalty Widget', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<input type="hidden" name="wps_wpr_floating_widget_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wps-wpr-floating-widget-nonce' ) ); ?>">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_enable" class="wps_wpr_general_label"><?php esc_html_e( 'Enable Floating Widget', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_fw_enable" id="wps_wpr_fw_enable" value="yes" <?php checked( $wps_wpr_fw['wps_wpr_fw_enable'], 'yes' ); ?>>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Show a rewards button on every storefront page. It opens a panel with the customer\'s points balance, level, ways to earn and referral link.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_position" class="wps_wpr_general_label"><?php esc_html_e( 'Position', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<select name="wps_wpr_fw_position" id="wps_wpr_fw_position">
							<option value="bottom-right" <?php selected( $wps_wpr_fw['wps_wpr_fw_position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom right', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="bottom-left" <?php selected( $wps_wpr_fw['wps_wpr_fw_position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom left', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_offset" class="wps_wpr_general_label"><?php esc_html_e( 'Distance From Bottom (px)', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="number" min="0" max="400" name="wps_wpr_fw_offset" id="wps_wpr_fw_offset" value="<?php echo esc_attr( $wps_wpr_fw['wps_wpr_fw_offset'] ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Raise the button if it overlaps a chat or cookie banner.', 'points-and-rewards-for-woocommerce' ); ?></span>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'If the gamification wheel icon is set to the same bottom corner, the button is raised above it automatically.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_color" class="wps_wpr_general_label"><?php esc_html_e( 'Primary Color', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="color" name="wps_wpr_fw_color" id="wps_wpr_fw_color" value="<?php echo esc_attr( $wps_wpr_fw['wps_wpr_fw_color'] ); ?>">
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_label" class="wps_wpr_general_label"><?php esc_html_e( 'Button Label', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" maxlength="40" name="wps_wpr_fw_label" id="wps_wpr_fw_label" class="input-text" value="<?php echo esc_attr( $wps_wpr_fw['wps_wpr_fw_label'] ); ?>">
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_icon" class="wps_wpr_general_label"><?php esc_html_e( 'Button Icon', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" maxlength="4" name="wps_wpr_fw_icon" id="wps_wpr_fw_icon" value="<?php echo esc_attr( $wps_wpr_fw['wps_wpr_fw_icon'] ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'An emoji or short symbol shown before the label. Leave empty for no icon.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_show_to" class="wps_wpr_general_label"><?php esc_html_e( 'Show To', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<select name="wps_wpr_fw_show_to" id="wps_wpr_fw_show_to">
							<option value="all" <?php selected( $wps_wpr_fw['wps_wpr_fw_show_to'], 'all' ); ?>><?php esc_html_e( 'All visitors (guests see a join call to action)', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="logged_in" <?php selected( $wps_wpr_fw['wps_wpr_fw_show_to'], 'logged_in' ); ?>><?php esc_html_e( 'Logged-in customers only', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_hide_cart" class="wps_wpr_general_label"><?php esc_html_e( 'Hide On Cart Page', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_fw_hide_cart" id="wps_wpr_fw_hide_cart" value="yes" <?php checked( $wps_wpr_fw['wps_wpr_fw_hide_cart'], 'yes' ); ?>>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_hide_checkout" class="wps_wpr_general_label"><?php esc_html_e( 'Hide On Checkout Page', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_fw_hide_checkout" id="wps_wpr_fw_hide_checkout" value="yes" <?php checked( $wps_wpr_fw['wps_wpr_fw_hide_checkout'], 'yes' ); ?>>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_show_mobile" class="wps_wpr_general_label"><?php esc_html_e( 'Show On Mobile', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_fw_show_mobile" id="wps_wpr_fw_show_mobile" value="yes" <?php checked( $wps_wpr_fw['wps_wpr_fw_show_mobile'], 'yes' ); ?>>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'When unchecked, the widget is hidden on screens 768px wide or less.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</div>
		</section>
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Rewards Launcher', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_mode" class="wps_wpr_general_label"><?php esc_html_e( 'Widget Mode', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<select name="wps_wpr_fw_mode" id="wps_wpr_fw_mode">
							<option value="compact" <?php selected( $wps_wpr_fw['wps_wpr_fw_mode'], 'compact' ); ?>><?php esc_html_e( 'Compact panel', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="launcher" <?php selected( $wps_wpr_fw['wps_wpr_fw_mode'], 'launcher' ); ?>><?php esc_html_e( 'Full rewards launcher (tabs)', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'The full launcher opens a larger, chat-style panel with Home, Earn, Redeem, Activity and Refer tabs. It hides itself while a campaign popup is open.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<span class="wps_wpr_general_label"><?php esc_html_e( 'Launcher Tabs', 'points-and-rewards-for-woocommerce' ); ?></span>
					<div class="wps_wpr_general_content">
						<?php foreach ( WPS_WPR_Rewards_Launcher::wps_wpr_get_all_tabs() as $wps_wpr_fw_tab_key => $wps_wpr_fw_tab_label ) : ?>
							<label class="wps_wpr_fw_tab_toggle" style="margin-right:14px;">
								<input type="checkbox" name="<?php echo esc_attr( 'wps_wpr_fw_tab_' . $wps_wpr_fw_tab_key ); ?>" id="<?php echo esc_attr( 'wps_wpr_fw_tab_' . $wps_wpr_fw_tab_key ); ?>" value="yes" <?php checked( $wps_wpr_fw[ 'wps_wpr_fw_tab_' . $wps_wpr_fw_tab_key ], 'yes' ); ?>>
								<?php echo esc_html( $wps_wpr_fw_tab_label ); ?>
							</label>
						<?php endforeach; ?>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Activity and Refer are only shown to logged-in customers.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_welcome" class="wps_wpr_general_label"><?php esc_html_e( 'Welcome Text', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" maxlength="120" name="wps_wpr_fw_welcome" id="wps_wpr_fw_welcome" class="input-text" value="<?php echo esc_attr( $wps_wpr_fw['wps_wpr_fw_welcome'] ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Shown at the top of the Home tab. [NAME] is replaced with the customer\'s first name.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_targeting" class="wps_wpr_general_label"><?php esc_html_e( 'Page Targeting', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<select name="wps_wpr_fw_targeting" id="wps_wpr_fw_targeting">
							<option value="all" <?php selected( $wps_wpr_fw['wps_wpr_fw_targeting'], 'all' ); ?>><?php esc_html_e( 'Show on all pages', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="include" <?php selected( $wps_wpr_fw['wps_wpr_fw_targeting'], 'include' ); ?>><?php esc_html_e( 'Show only on the pages below', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="exclude" <?php selected( $wps_wpr_fw['wps_wpr_fw_targeting'], 'exclude' ); ?>><?php esc_html_e( 'Hide on the pages below', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_fw_page_ids" class="wps_wpr_general_label"><?php esc_html_e( 'Page / Post IDs', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" name="wps_wpr_fw_page_ids" id="wps_wpr_fw_page_ids" class="input-text" placeholder="12, 34, 56" value="<?php echo esc_attr( $wps_wpr_fw['wps_wpr_fw_page_ids'] ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Comma-separated IDs of pages, posts or products. Use the Shop page ID for the shop archive.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</div>
		</section>
	</main>
	<input type="submit" name="wps_wpr_save_floating_widget_settings" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_floating_widget_settings" value="<?php esc_attr_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
