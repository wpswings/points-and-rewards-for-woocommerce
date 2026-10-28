<?php
/**
 * This is the setting template
 *
 * AI settings assistant: settings and chat panel.
 *
 * @link       https://wpswings.com/
 * @since      2.10.5
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

// Save AI Assistant Settings.
if ( isset( $_POST['wps_wpr_ai_assistant_setting_nonce'] ) ) {
	if ( wp_verify_nonce( ! empty( $_POST['wps_wpr_ai_assistant_setting_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_ai_assistant_setting_nonce'] ) ) : '', 'ai-assistant-setting-nonce' ) ) {
		if ( isset( $_POST['wps_wpr_save_ai_assistant_settings'] ) ) {

			$wps_wpr_ai_assistant_settings                         = array();
			$wps_wpr_ai_assistant_settings['wps_wpr_ai_enable']    = ! empty( $_POST['wps_wpr_ai_enable'] ) ? 'yes' : 'no';
			$wps_wpr_ai_assistant_settings['wps_wpr_ai_can_edit']  = ! empty( $_POST['wps_wpr_ai_can_edit'] ) ? 'yes' : 'no';

			update_option( WPS_WPR_AI_Assistant::OPTION, $wps_wpr_ai_assistant_settings );
		}
	}

	// Show saved msg.
	$settings_obj->wps_wpr_settings_saved();
}

$wps_wpr_ai_settings  = WPS_WPR_AI_Assistant::wps_wpr_get_settings();
$wps_wpr_ai_has_api   = WPS_WPR_AI_Assistant::wps_wpr_has_ai_api();
$wps_wpr_ai_connected = $wps_wpr_ai_has_api && WPS_WPR_AI_Assistant::wps_wpr_is_ai_available();
$wps_wpr_ai_enabled   = 'yes' === $wps_wpr_ai_settings['wps_wpr_ai_enable'];
$wps_wpr_ai_ready     = $wps_wpr_ai_connected && $wps_wpr_ai_enabled;

$wps_wpr_ai_suggestions = array(
	__( 'Give an overview of my current points setup', 'points-and-rewards-for-woocommerce' ),
	__( 'How do I give 2x points on weekends?', 'points-and-rewards-for-woocommerce' ),
	__( 'Give new customers 100 points on signup', 'points-and-rewards-for-woocommerce' ),
	__( 'Let customers earn 1 point for every 10 spent', 'points-and-rewards-for-woocommerce' ),
);
?>

<div class="wps_wpr_ai_assistant_wrapper">
	<section class="wps_wpr_general_row_wrap">
		<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'AI Assistant Settings', 'points-and-rewards-for-woocommerce' ); ?></div>
		<div class="wps_wpr_section_content">
			<input type="hidden" name="wps_wpr_ai_assistant_setting_nonce" id="wps_wpr_ai_assistant_setting_nonce" value="<?php echo esc_html( wp_create_nonce( 'ai-assistant-setting-nonce' ) ); ?>">
			<article class="wps_wpr_general_row">
				<label for="wps_wpr_ai_enable" class="wps_wpr_general_label"><?php esc_html_e( 'Enable AI Assistant', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<input type="checkbox" name="wps_wpr_ai_enable" id="wps_wpr_ai_enable" value="yes" <?php checked( $wps_wpr_ai_settings['wps_wpr_ai_enable'], 'yes' ); ?>>
					<span class="wps_wpr_label_notice"><?php esc_html_e( 'Let administrators chat with AI about the loyalty program from this tab.', 'points-and-rewards-for-woocommerce' ); ?></span>
				</div>
			</article>
			<article class="wps_wpr_general_row">
				<label for="wps_wpr_ai_can_edit" class="wps_wpr_general_label"><?php esc_html_e( 'Allow AI to Propose Setting Changes', 'points-and-rewards-for-woocommerce' ); ?></label>
				<div class="wps_wpr_general_content">
					<input type="checkbox" name="wps_wpr_ai_can_edit" id="wps_wpr_ai_can_edit" value="yes" <?php checked( $wps_wpr_ai_settings['wps_wpr_ai_can_edit'], 'yes' ); ?>>
					<span class="wps_wpr_label_notice"><?php esc_html_e( 'The assistant can prepare changes to plugin settings. Nothing is saved until you click Apply on the proposed change.', 'points-and-rewards-for-woocommerce' ); ?></span>
				</div>
			</article>
			<article class="wps_wpr_general_row">
				<span class="wps_wpr_general_label"><?php esc_html_e( 'AI Provider', 'points-and-rewards-for-woocommerce' ); ?></span>
				<div class="wps_wpr_general_content">
					<?php if ( ! $wps_wpr_ai_has_api ) : ?>
						<span class="wps_wpr_ai_status is-error"><?php esc_html_e( 'Not available', 'points-and-rewards-for-woocommerce' ); ?></span>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'The AI Assistant needs WordPress 7.0 or later, which includes the WordPress AI Client. Please update WordPress to use it.', 'points-and-rewards-for-woocommerce' ); ?></span>
					<?php elseif ( ! $wps_wpr_ai_connected ) : ?>
						<span class="wps_wpr_ai_status is-error"><?php esc_html_e( 'Not connected', 'points-and-rewards-for-woocommerce' ); ?></span>
						<span class="wps_wpr_label_notice">
							<?php esc_html_e( 'Install an AI provider plugin (for example Anthropic, OpenAI or Google) and add its API key in Settings > Connectors.', 'points-and-rewards-for-woocommerce' ); ?>
							<a href="<?php echo esc_url( admin_url( 'options-connectors.php' ) ); ?>"><?php esc_html_e( 'Open Connectors', 'points-and-rewards-for-woocommerce' ); ?></a>
						</span>
					<?php else : ?>
						<span class="wps_wpr_ai_status is-success"><?php esc_html_e( 'Connected', 'points-and-rewards-for-woocommerce' ); ?></span>
						<span class="wps_wpr_label_notice">
							<?php esc_html_e( 'The assistant uses the provider configured in Settings > Connectors.', 'points-and-rewards-for-woocommerce' ); ?>
							<a href="<?php echo esc_url( admin_url( 'options-connectors.php' ) ); ?>"><?php esc_html_e( 'Manage Connectors', 'points-and-rewards-for-woocommerce' ); ?></a>
						</span>
					<?php endif; ?>
				</div>
			</article>
		</div>
	</section>

	<section class="wps_wpr_general_row_wrap">
		<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Chat With AI Assistant', 'points-and-rewards-for-woocommerce' ); ?></div>
		<div class="wps_wpr_section_content">
			<?php if ( ! $wps_wpr_ai_connected ) : ?>
				<p class="wps_wpr_ai_unavailable"><?php esc_html_e( 'Connect an AI provider to start chatting. The chat will be enabled once a provider is connected.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<?php elseif ( ! $wps_wpr_ai_enabled ) : ?>
				<p class="wps_wpr_ai_unavailable"><?php esc_html_e( 'Enable the AI Assistant above and save changes to start chatting.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<?php else : ?>
				<p class="wps_wpr_label_notice"><?php esc_html_e( 'Ask questions about your loyalty program or ask the assistant to change settings. Changes are only saved after you click Apply.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<?php endif; ?>
			<div class="wps_wpr_ai_chat<?php echo $wps_wpr_ai_ready ? '' : ' is-disabled'; ?>" id="wps_wpr_ai_chat">
				<div class="wps_wpr_ai_messages" id="wps_wpr_ai_messages" aria-live="polite"></div>
				<div class="wps_wpr_ai_suggestions" id="wps_wpr_ai_suggestions">
					<?php foreach ( $wps_wpr_ai_suggestions as $wps_wpr_ai_suggestion ) : ?>
						<button type="button" class="button wps_wpr_ai_suggestion" <?php disabled( ! $wps_wpr_ai_ready ); ?>><?php echo esc_html( $wps_wpr_ai_suggestion ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="wps_wpr_ai_composer">
					<textarea id="wps_wpr_ai_input" rows="2" placeholder="<?php esc_attr_e( 'Ask about your points and rewards setup…', 'points-and-rewards-for-woocommerce' ); ?>" <?php disabled( ! $wps_wpr_ai_ready ); ?>></textarea>
					<button type="button" class="button button-primary" id="wps_wpr_ai_send" <?php disabled( ! $wps_wpr_ai_ready ); ?>><?php esc_html_e( 'Send', 'points-and-rewards-for-woocommerce' ); ?></button>
				</div>
			</div>
		</div>
	</section>
	<input type="submit" name="wps_wpr_save_ai_assistant_settings" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_ai_assistant_settings" value="<?php esc_html_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
