<?php
/**
 * This is the setting template
 *
 * Order total points settings.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

/**
 * Process campaign settings form submission
 */
if ( isset( $_POST['wps_wpr_user_campaign_setting_nonce'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['wps_wpr_user_campaign_setting_nonce'] ?? '' ) );

	if ( ! wp_verify_nonce( $nonce, 'user-campaign-setting-nonce' ) ) {
		return;
	}

	if ( ! isset( $_POST['wps_wpr_save_campaign_settings'] ) ) {
		return;
	}

	// Process and save campaign settings.
	$campaign_settings = wps_wpr_process_campaign_settings( $_POST );
	update_option( 'wps_wpr_campaign_settings', $campaign_settings );

	// Show saved message.
	$settings_obj->wps_wpr_settings_saved();
}

/**
 * Process campaign settings from POST data.
 *
 * @param array $post_data The $_POST data.
 * @return array Processed campaign settings.
 */
function wps_wpr_process_campaign_settings( $post_data ) {
	// Define field mappings with their sanitization types.
	$field_mappings = array(
		// Basic settings.
		'wps_wpr_enable_campaign_settings' => 'text',
		'wps_wpr_enable_sign_up_campaign' => 'text',
		'wps_wpr_enable_referral_campaign' => 'text',
		'wps_wpr_enable_birthday_campaign' => 'text',
		'wps_wpr_enable_gami_points' => 'text',
		'wps_wpr_enable_quiz_contest_campaign' => 'text',

		// Quiz settings (arrays).
		'wps_wpr_quiz_question' => 'array',
		'wps_wpr_quiz_option_one' => 'array',
		'wps_wpr_quiz_option_two' => 'array',
		'wps_wpr_quiz_option_three' => 'array',
		'wps_wpr_quiz_option_four' => 'array',
		'wps_wpr_quiz_answer' => 'array',
		'wps_wpr_quiz_rewards_points' => 'array',

		// Campaign display settings.
		'wps_wpr_enter_campaign_heading' => 'text',
		'wps_wpr_enter_campaign_image_url' => 'url',
		'wps_wpr_show_current_points_modal' => 'text',
		'wps_wpr_show_total_referral_count' => 'text',
		'wps_wpr_select_page_for_campaign' => 'array',

		// Color settings.
		'wps_wpr_campaign_color_one' => 'text',
		'wps_wpr_campaign_color_two' => 'text',

		// social share campaign settings.
		'wps_wpr_social_share_url' => 'array',
		'wps_wpr_social_share_points' => 'array',
		'wps_wpr_social_share_campaign_label' => 'array',

	);

	// Default values.
	$defaults = array(
		'wps_wpr_enter_campaign_heading' => 'Points and Rewards Program',
		'wps_wpr_enter_campaign_image_url' => 'https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/reward.webp',
		'wps_wpr_campaign_color_one' => '#a13a93',
		'wps_wpr_campaign_color_two' => '#ffbb21',
	);

	$processed_data = array();

	foreach ( $field_mappings as $field => $sanitization_type ) {
		$processed_data[ $field ] = wps_wpr_sanitize_field( $post_data[ $field ] ?? null, $sanitization_type, $defaults[ $field ] ?? '' );
	}

	// Apply filters for additional processing.
	return apply_filters( 'wps_wpr_save_campaign_extra_data', $processed_data );
}

/**
 * Sanitize field based on type.
 *
 * @param mixed  $value The value to sanitize.
 * @param string $type  The sanitization type.
 * @param mixed  $default Default value if empty.
 * @return mixed Sanitized value.
 */
function wps_wpr_sanitize_field( $value, $type, $default = '' ) {
	if ( empty( $value ) && ! empty( $default ) ) {
		return $default;
	}

	switch ( $type ) {

		case 'text':
			return ! empty( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : $default;

		case 'url':
			return ! empty( $value ) ? esc_url_raw( wp_unslash( $value ) ) : $default;

		case 'array':
			return ! empty( $value ) ? map_deep( wp_unslash( $value ), 'sanitize_text_field' ) : array();

		default:
			return sanitize_text_field( wp_unslash( $value ) );
	}
}

/**
 * Undocumented function.
 *
 * @param  string $field field.
 * @param  string $type  type.
 * @param  string $default default.
 * @return mixed
 */
function wps_wpr_get_campaign_settings( $field, $type, $default = '' ) {

	$wps_wpr_campaign_settings = get_option( 'wps_wpr_campaign_settings', array() );
	$wps_wpr_campaign_settings = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
	switch ( $type ) {

		case 'string':
			return ! empty( $wps_wpr_campaign_settings[ $field ] ) ? $wps_wpr_campaign_settings[ $field ] : $default;
		case 'array':
			return ! empty( $wps_wpr_campaign_settings[ $field ] ) && is_array( $wps_wpr_campaign_settings[ $field ] ) ? $wps_wpr_campaign_settings[ $field ] : array();
		default:
			return $default;
	}
}

// Define campaign types once.
$wps_wpr_campaign_types = array(
	'mailing_list'           => __( 'Subscribe to Mailing List', 'points-and-rewards-for-woocommerce' ),
	'insta_profile'          => __( 'Visit Instagram Profile', 'points-and-rewards-for-woocommerce' ),
	'view_insta_photo'       => __( 'View Instagram Photo', 'points-and-rewards-for-woocommerce' ),
	'like_linkedin_post'     => __( 'Like Post on LinkedIn', 'points-and-rewards-for-woocommerce' ),
	'share_linkedin_post'    => __( 'Share Post on LinkedIn', 'points-and-rewards-for-woocommerce' ),
	'share_facebook_post'    => __( 'Share on Facebook', 'points-and-rewards-for-woocommerce' ),
	'like_facebook_page'     => __( 'Like Facebook Page', 'points-and-rewards-for-woocommerce' ),
	'subs_you_chann'         => __( 'Subscribe to YouTube Channel', 'points-and-rewards-for-woocommerce' ),
	'watch_you_vid'          => __( 'Watch a YouTube Video', 'points-and-rewards-for-woocommerce' ),
	'like_you_vid'           => __( 'Like a YouTube Video', 'points-and-rewards-for-woocommerce' ),
	'share_twitter'          => __( 'Share on Twitter (X)', 'points-and-rewards-for-woocommerce' ),
	'follow_twitter'         => __( 'Follow on Twitter (X)', 'points-and-rewards-for-woocommerce' ),
	'like_post_twitter'      => __( 'Like Post on Twitter (X)', 'points-and-rewards-for-woocommerce' ),
	'visit_pinterest'        => __( 'Visit Pinterest', 'points-and-rewards-for-woocommerce' ),
	'follow_pinterest'       => __( 'Follow on Pinterest', 'points-and-rewards-for-woocommerce' ),
	'follow_board_pinterest' => __( 'Follow a Pinterest Board', 'points-and-rewards-for-woocommerce' ),
);
$wps_wpr_campaign_types = apply_filters( 'wps_wpr_add_campaign_types', $wps_wpr_campaign_types );

/**
 * Render one campaign article (keeps markup DRY).
 *
 * @param string $url url.
 * @param string $points points.
 * @param string $selected_label selected_label.
 * @param array  $types types.
 */
function wps_wpr_render_campaign_block( $url = '', $points = '', $selected_label = '', $types = array() ) {
	?>
	<article class="wps_wpr_general_row">
		<div class="wps_wpr_social_campaign_main_wrap wps_wpr_general_content">

			<label class="wps_wpr_general_label">
				<?php esc_html_e( 'Select Campaign Type', 'points-and-rewards-for-woocommerce' ); ?>
				<select name="wps_wpr_social_share_campaign_label[]" class="wps_wpr_social_share_campaign_label" required>
					<?php
					foreach ( $types as $value => $label ) {
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $value ),
							selected( $selected_label, $value, false ),
							esc_html( $label )
						);
					}
					?>
				</select>
			</label>

			<label class="wps_wpr_general_label">
				<?php esc_html_e( 'Campaign URL', 'points-and-rewards-for-woocommerce' ); ?>
				<input type="text" name="wps_wpr_social_share_url[]" class="wps_wpr_social_share_url" value="<?php echo esc_attr( $url ); ?>">
			</label>

			<label class="wps_wpr_general_label">
				<?php esc_html_e( 'Reward Points', 'points-and-rewards-for-woocommerce' ); ?>
				<input type="number" min="0" name="wps_wpr_social_share_points[]" class="wps_wpr_social_share_points" value="<?php echo esc_attr( $points ); ?>">
			</label>

			<?php do_action( 'wps_wpr_after_campaign_fields', $url, $points, $selected_label, $types ); ?>

			<div class="wps-campaign-actions">
				<button type="button" class="button wps_wpr_remove_campaign" aria-label="<?php esc_attr_e( 'Remove', 'points-and-rewards-for-woocommerce' ); ?>">
					<?php esc_html_e( 'Remove', 'points-and-rewards-for-woocommerce' ); ?>
				</button>
			</div>
		</div>
	</article>
	<?php
}

$wps_wpr_campaign_settings            = get_option( 'wps_wpr_campaign_settings', array() );
$wps_wpr_campaign_settings            = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
$wps_wpr_enable_campaign_settings     = wps_wpr_get_campaign_settings( 'wps_wpr_enable_campaign_settings', 'string' );
$wps_wpr_enable_sign_up_campaign      = wps_wpr_get_campaign_settings( 'wps_wpr_enable_sign_up_campaign', 'string' );
$wps_wpr_enable_referral_campaign     = wps_wpr_get_campaign_settings( 'wps_wpr_enable_referral_campaign', 'string' );
$wps_wpr_enable_birthday_campaign     = wps_wpr_get_campaign_settings( 'wps_wpr_enable_birthday_campaign', 'string' );
$wps_wpr_enable_gami_points           = wps_wpr_get_campaign_settings( 'wps_wpr_enable_gami_points', 'string' );
$wps_wpr_enable_quiz_contest_campaign = wps_wpr_get_campaign_settings( 'wps_wpr_enable_quiz_contest_campaign', 'string' );
$wps_wpr_quiz_question                = wps_wpr_get_campaign_settings( 'wps_wpr_quiz_question', 'array' );
$wps_wpr_quiz_option_one              = wps_wpr_get_campaign_settings( 'wps_wpr_quiz_option_one', 'array' );
$wps_wpr_quiz_option_two              = wps_wpr_get_campaign_settings( 'wps_wpr_quiz_option_two', 'array' );
$wps_wpr_quiz_option_three            = wps_wpr_get_campaign_settings( 'wps_wpr_quiz_option_three', 'array' );
$wps_wpr_quiz_option_four             = wps_wpr_get_campaign_settings( 'wps_wpr_quiz_option_four', 'array' );
$wps_wpr_quiz_answer                  = wps_wpr_get_campaign_settings( 'wps_wpr_quiz_answer', 'array' );
$wps_wpr_quiz_rewards_points          = wps_wpr_get_campaign_settings( 'wps_wpr_quiz_rewards_points', 'array' );
$wps_wpr_enter_campaign_heading       = wps_wpr_get_campaign_settings( 'wps_wpr_enter_campaign_heading', 'string', 'Points and Rewards Program' );
$wps_wpr_enter_campaign_image_url     = wps_wpr_get_campaign_settings( 'wps_wpr_enter_campaign_image_url', 'string', 'https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/reward.webp' );
$wps_wpr_show_current_points_modal    = wps_wpr_get_campaign_settings( 'wps_wpr_show_current_points_modal', 'string' );
$wps_wpr_show_total_referral_count    = wps_wpr_get_campaign_settings( 'wps_wpr_show_total_referral_count', 'string' );
$wps_wpr_select_page_for_campaign     = wps_wpr_get_campaign_settings( 'wps_wpr_select_page_for_campaign', 'array' );
$wps_wpr_campaign_color_one           = wps_wpr_get_campaign_settings( 'wps_wpr_campaign_color_one', 'string', '#a13a93' );
$wps_wpr_campaign_color_two           = wps_wpr_get_campaign_settings( 'wps_wpr_campaign_color_two', 'string', '#ffbb21' );
$wps_wpr_social_share_url             = wps_wpr_get_campaign_settings( 'wps_wpr_social_share_url', 'array' );
$wps_wpr_social_share_points          = wps_wpr_get_campaign_settings( 'wps_wpr_social_share_points', 'array' );
$wps_wpr_social_share_campaign_label  = wps_wpr_get_campaign_settings( 'wps_wpr_social_share_campaign_label', 'array' );
$upgrade_link                            = '<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro" target="_blank">Click here</a>';
$message                                 = sprintf( /* translators: %s: sms msg */ esc_html__( 'Unlock this premium feature by upgrading to the Pro plugin. %s to get started!', 'points-and-rewards-for-woocommerce' ), $upgrade_link );

// display only one quiz when pro is not activated.
if ( wps_wpr_is_par_pro_plugin_active() ) {
	// Keep all quiz questions if pro is active.
	$wps_wpr_quiz_question = $wps_wpr_quiz_question;
} else {
	// Keep only the first question if not pro.
	$wps_wpr_quiz_question = array_slice( $wps_wpr_quiz_question, 0, 1 );
}
?>

<div class="wps_wpr_user_badges_main_wrappers">
	<form method="POST" action="" class="wps_wpr_user_badges_form">
		<main class="wps_wpr_main_user_badges_wrapper">
			<section class="wps_wpr_general_row_wrap">
				<div class="wps_wpr_user_badges_settings_heading wps_wpr_general_sign_title wps_wpr_doc_video_wrapper"><?php esc_html_e( 'Campaign', 'points-and-rewards-for-woocommerce' ); ?></div>
				<input type="hidden" name="wps_wpr_user_campaign_setting_nonce" id="wps_wpr_user_campaign_setting_nonce" value="<?php echo esc_html( wp_create_nonce( 'user-campaign-setting-nonce' ) ); ?>">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_campaign_settings" class="wps_wpr_general_label"><?php esc_html_e( 'Enable Campaign', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_campaign_settings" class="wps_wpr_enable_campaign_settings" value="yes" <?php checked( $wps_wpr_enable_campaign_settings, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Turn on this setting to start the points and rewards campaign on your site.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_campaign_settings" class="wps_wpr_general_label"><?php esc_html_e( 'Select the page where you want to show the Campaign modal.', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<select id="wps_wpr_select_page_for_campaign" name="wps_wpr_select_page_for_campaign[]" multiple>
							<option value="home" <?php selected( in_array( 'home', $wps_wpr_select_page_for_campaign ) ); ?>><?php esc_html_e( 'Home', 'points-and-rewards-for-woocommerce' ); ?></option>
							<?php
							$wps_pages = get_pages();
							if ( ! empty( $wps_pages ) && is_array( $wps_pages ) ) {
								foreach ( $wps_pages as $page_data ) {
									?>
									<option value="<?php echo esc_attr( $page_data->ID ); ?>" <?php selected( in_array( $page_data->ID, $wps_wpr_select_page_for_campaign ) ); ?>>
										<?php echo esc_html( $page_data->post_title ); ?>
									</option>
									<?php
								}
							}
							?>
						</select>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Campaign modal will only show on the selected pages.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_sign_up_campaign" class="wps_wpr_general_label"><?php esc_html_e( 'Sign Up Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_sign_up_campaign" class="wps_wpr_enable_sign_up_campaign" value="yes" <?php checked( $wps_wpr_enable_sign_up_campaign, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Use this toggle to start the signup points campaign', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_referral_campaign" class="wps_wpr_general_label"><?php esc_html_e( 'Referral Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_referral_campaign" class="wps_wpr_enable_referral_campaign" value="yes" <?php checked( $wps_wpr_enable_referral_campaign, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Enable this to launch the referral points campaign.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_birthday_campaign" class="wps_wpr_general_label"><?php esc_html_e( 'Birthday Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_birthday_campaign" class="wps_wpr_enable_birthday_campaign" value="yes" <?php checked( $wps_wpr_enable_birthday_campaign, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php echo wp_kses_post( $message ); ?></span>
					</div>
				</article>
				<?php do_action( 'wps_wpr_add_campaign_general_section', $wps_wpr_campaign_settings ); ?>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_gami_points" class="wps_wpr_general_label"><?php esc_html_e( 'Gamification Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_gami_points" class="wps_wpr_enable_gami_points" value="yes" <?php checked( $wps_wpr_enable_gami_points, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Enable this setting to show the option for Spin Wheel gameplay.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>

			<!--  +++++++    Quiz Contest HTMl   ++++++++  -->
			<section class="wps_wpr_general_row_wrap">
				<div class="wps_wpr_user_badges_settings_heading wps_wpr_general_sign_title wps_wpr_doc_video_wrapper">
					<?php esc_html_e( 'Quiz Contest', 'points-and-rewards-for-woocommerce' ); ?>
				</div>

				 <!-- Global Enable Quiz -->
				<article class="wps_wpr_general_row">
					<label class="wps_wpr_general_label"><?php esc_html_e( 'Enable Quiz', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_quiz_contest_campaign" class="wps_wpr_enable_quiz_contest_campaign" value="yes" <?php checked( $wps_wpr_enable_quiz_contest_campaign, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Start the quiz contest by enabling this setting.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>

				<div id="wps_wpr_quiz_container">
					<?php
					if ( ! empty( $wps_wpr_quiz_question ) && ! empty( $wps_wpr_quiz_option_one ) && ! empty( $wps_wpr_quiz_answer ) && ! empty( $wps_wpr_quiz_rewards_points ) ) {
						foreach ( $wps_wpr_quiz_question as $index => $quiz ) {
							?>
							<article class="wps_wpr_general_row wps_wpr_quiz_row">

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Question', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="text" name="wps_wpr_quiz_question[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $quiz ); ?>" required>
									<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'This field is required', 'points-and-rewards-for-woocommerce' ); ?></span>
								</div>

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Enter Options', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="text" name="wps_wpr_quiz_option_one[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_one[ $index ] ); ?>" required>
									<input type="text" name="wps_wpr_quiz_option_two[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_two[ $index ] ); ?>" required>
									<input type="text" name="wps_wpr_quiz_option_three[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_three[ $index ] ); ?>" required>
									<input type="text" name="wps_wpr_quiz_option_four[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_four[ $index ] ); ?>" required>
									<div class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'All options are required', 'points-and-rewards-for-woocommerce' ); ?></div>
								</div>

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Answer', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="text" name="wps_wpr_quiz_answer[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_answer[ $index ] ); ?>" required>
									<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'Answer is required', 'points-and-rewards-for-woocommerce' ); ?></span>
								</div>

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Rewards Points', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="number" min="0" name="wps_wpr_quiz_rewards_points[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_rewards_points[ $index ] ); ?>" required>
									<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'Reward points are required', 'points-and-rewards-for-woocommerce' ); ?></span>
								</div>

								<div class="wps_wpr_general_actions" style="margin-top:10px;">
									<button type="button" class="button wps_wpr_remove_quiz">
										<?php esc_html_e( 'Remove', 'points-and-rewards-for-woocommerce' ); ?>
									</button>
								</div>

							</article>
							<?php
						}
					} else {
						?>
						<article class="wps_wpr_general_row wps_wpr_quiz_row">

							<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Question', 'points-and-rewards-for-woocommerce' ); ?></label>
							<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
								<input type="text" name="wps_wpr_quiz_question[]" class="wps_wpr_quiz_field" value="">
								<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'This field is required', 'points-and-rewards-for-woocommerce' ); ?></span>
							</div>

							<label class="wps_wpr_general_label"><?php esc_html_e( 'Enter Options', 'points-and-rewards-for-woocommerce' ); ?></label>
							<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
								<input type="text" name="wps_wpr_quiz_option_one[]" class="wps_wpr_quiz_field" value="">
								<input type="text" name="wps_wpr_quiz_option_two[]" class="wps_wpr_quiz_field" value="">
								<input type="text" name="wps_wpr_quiz_option_three[]" class="wps_wpr_quiz_field" value="">
								<input type="text" name="wps_wpr_quiz_option_four[]" class="wps_wpr_quiz_field" value="">
								<div class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'All options are required', 'points-and-rewards-for-woocommerce' ); ?></div>
							</div>

							<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Answer', 'points-and-rewards-for-woocommerce' ); ?></label>
							<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
								<input type="text" name="wps_wpr_quiz_answer[]" class="wps_wpr_quiz_field" value="">
								<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'Answer is required', 'points-and-rewards-for-woocommerce' ); ?></span>
							</div>

							<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Rewards Points', 'points-and-rewards-for-woocommerce' ); ?></label>
							<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
								<input type="number" min="0" name="wps_wpr_quiz_rewards_points[]" class="wps_wpr_quiz_field" value="">
								<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'Reward points are required', 'points-and-rewards-for-woocommerce' ); ?></span>
							</div>

							<div class="wps_wpr_general_actions" style="margin-top:10px;">
								<button type="button" class="button wps_wpr_remove_quiz">
									<?php esc_html_e( 'Remove', 'points-and-rewards-for-woocommerce' ); ?>
								</button>
							</div>

						</article>
						<?php
					}
					?>
				</div>
				
				<div class="wps_wpr_insert_pro_html" style="display: none;"></div>
				<button type="button" id="wps_wpr_add_quiz" class="button"><?php esc_html_e( 'Add Quiz', 'points-and-rewards-for-woocommerce' ); ?></button>
			</section>

			<section class="wps_wpr_general_row_wrap">
				<div class="wps_wpr_user_badges_settings_heading wps_wpr_general_sign_title wps_wpr_doc_video_wrapper"><?php esc_html_e( 'Campaign Modal – Additional Data', 'points-and-rewards-for-woocommerce' ); ?></div>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enter_campaign_heading" class="wps_wpr_general_label"><?php esc_html_e( 'Set Modal Heading', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="text" name="wps_wpr_enter_campaign_heading" class="wps_wpr_enter_campaign_heading" id="wps_wpr_enter_campaign_heading" value="<?php echo esc_html( $wps_wpr_enter_campaign_heading ); ?>">
						<div class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Set the heading title for the campaign’s modal.', 'points-and-rewards-for-woocommerce' ); ?></div>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enter_campaign_image_url" class="wps_wpr_general_label"><?php esc_html_e( 'Enter Campaign Image URL', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="text" name="wps_wpr_enter_campaign_image_url" class="wps_wpr_enter_campaign_image_url" id="wps_wpr_enter_campaign_image_url" value="<?php echo esc_html( $wps_wpr_enter_campaign_image_url ); ?>">
						<div class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Set the image URL for the campaign modal.', 'points-and-rewards-for-woocommerce' ); ?></div>
						<span><button type="button" class="wps_wpr_view_campaign_existing_template" class="button"><?php esc_html_e( 'View Templates', 'points-and-rewards-for-woocommerce' ); ?></button></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_show_current_points_modal" class="wps_wpr_general_label"><?php esc_html_e( 'Show current points on modal', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_show_current_points_modal" class="wps_wpr_show_current_points_modal" value="yes" <?php checked( $wps_wpr_show_current_points_modal, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Enable showing current points in the campaign modal.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_show_total_referral_count" class="wps_wpr_general_label"><?php esc_html_e( 'Show total referral count on modal', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_show_total_referral_count" class="wps_wpr_show_total_referral_count" value="yes" <?php checked( $wps_wpr_show_total_referral_count, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Enable showing total referral count in the campaign modal.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_campaign_color_one" class="wps_wpr_general_label"><?php esc_html_e( 'Choose Modal color', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="color" name="wps_wpr_campaign_color_one" class="wps_wpr_campaign_color_one" value="<?php echo esc_html( $wps_wpr_campaign_color_one ); ?>">
						<input type="color" name="wps_wpr_campaign_color_two" class="wps_wpr_campaign_color_two" value="<?php echo esc_html( $wps_wpr_campaign_color_two ); ?>">
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Please choose both primary and secondary colors for the modal design.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<?php do_action( 'wps_wpr_add_campaign_additional_html', $wps_wpr_campaign_settings ); ?>
			</section>

			<section class="wps_wpr_general_row_wrap social_share_main_wrap" id="wps_social_share_main_wrap">
				<div class="wps_wpr_user_badges_settings_heading wps_wpr_general_sign_title wps_wpr_doc_video_wrapper">
					<?php esc_html_e( 'Social Share Campaign', 'points-and-rewards-for-woocommerce' ); ?>
				</div>

				<?php
				// Render existing campaign blocks (safely handle mismatched lengths).
				if ( ! empty( $wps_wpr_social_share_url ) && is_array( $wps_wpr_social_share_url ) ) {

					$points_arr   = is_array( $wps_wpr_social_share_points ) ? $wps_wpr_social_share_points : array();
					$labels_arr   = is_array( $wps_wpr_social_share_campaign_label ) ? $wps_wpr_social_share_campaign_label : array();
					$total_blocks = max( count( $wps_wpr_social_share_url ), count( $points_arr ), count( $labels_arr ) );

					for ( $i = 0; $i < $total_blocks; $i++ ) {

						$url   = isset( $wps_wpr_social_share_url[ $i ] ) ? $wps_wpr_social_share_url[ $i ] : '';
						$pts   = isset( $points_arr[ $i ] ) ? $points_arr[ $i ] : '';
						$label = isset( $labels_arr[ $i ] ) ? $labels_arr[ $i ] : '';
						wps_wpr_render_campaign_block( $url, $pts, $label, $wps_wpr_campaign_types );
					}
				} else {

					// Render a single blank block by default.
					wps_wpr_render_campaign_block( '', '', '', $wps_wpr_campaign_types );
				}
				?>

				<!-- Add button -->
				<div>
					<button type="button" id="wps_wpr_add_social_share_campaign" class="button"><?php esc_html_e( 'Add Campaign', 'points-and-rewards-for-woocommerce' ); ?></button>
				</div>

				<!-- Note -->
				<div class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice share_notices">
					<b><?php esc_html_e( 'Note: Choose a campaign type, add the post URL, and decide the points to reward. It helps motivate users to take action and earn rewards!', 'points-and-rewards-for-woocommerce' ); ?></b>
				</div>

				<?php
				// Template used by JS to clone new blocks (keeps client-side logic simple & avoids duplicate IDs).
				?>
				<template id="wps_wpr_campaign_template">
					<?php wps_wpr_render_campaign_block( '', '', '', $wps_wpr_campaign_types ); ?>
				</template>

				<?php do_action( 'wps_wpr_add_social_share_additional_html', $wps_wpr_campaign_settings ); ?>
			</section>
		</main>
		<input type="submit" name="wps_wpr_save_campaign_settings" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_campaign_settings" value="<?php esc_html_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
	</form>
</div>

<!--    +++++++++   Existing Campaign Modal HTML    +++++++++++    -->
<div class="wps-popup">
	<div class="wps-popup_shadow"></div>
	<div class="wps-popup_main">
		<div class="wps-popup_m-header">
			<div class="h2"><?php esc_html_e( 'Select Campaign Banner', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps-popup_m-close">&times;</div>
		</div>

		<!--  Festive wise tabs -->
		<div class="wps-popup_m-sidebar">
			<span class="wps-wpr_temp-select-haloween" id="wps_wpr_halloween"><?php esc_html_e( 'Halloween', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-black-friday" id="wps_wpr_black_friday"><?php esc_html_e( 'Black Friday', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-easter" id="wps_wpr_happy_easter"><?php esc_html_e( 'Happy Easter', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-easter" id="wps_wpr_merry_christmas"><?php esc_html_e( 'Merry Christmas', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-easter" id="wps_wpr_mothers_day"><?php esc_html_e( 'Mother\'s Day', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-easter" id="wps_wpr_thanksgiving"><?php esc_html_e( 'Thanksgiving', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-easter" id="wps_wpr_womens_day"><?php esc_html_e( 'Women\'s Day', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-easter" id="wps_wpr_valentines_day"><?php esc_html_e( 'Valentine\'s Day', 'points-and-rewards-for-woocommerce' ); ?></span>
		</div>

		<!-- Halloween Banners templates -->
		<div class="wps-popup_m-content wps_wpr_halloween active_tab">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/hal1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Spooky deals await this Halloween Festival!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#582A47</span>
				<span class="wps_wpr_cam_sec_color">#B14539</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/hal2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Halloween Festival treats you can’t miss!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#1D092D</span>
				<span class="wps_wpr_cam_sec_color">#9544D1</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/hal3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Get your Halloween Festival savings now!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#3B1C57</span>
				<span class="wps_wpr_cam_sec_color">#703177</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/hal4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Frightful fun during the Halloween Festival!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#69B827</span>
				<span class="wps_wpr_cam_sec_color">#388589</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/hal5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Scare up some deals — Halloween Festival style!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#7671B4</span>
				<span class="wps_wpr_cam_sec_color">#4D43B3</span>
			</div>
		</div>

		<!-- Black Friday Banners templates -->
		<div class="wps-popup_m-content wps_wpr_black_friday">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/bf1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Black Friday steals are here!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#F6CB31</span>
				<span class="wps_wpr_cam_sec_color">#E59E0C</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/bf2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Don’t miss out this Black Friday!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#01A5C2</span>
				<span class="wps_wpr_cam_sec_color">#6ABA41</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/bf3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Shop smart with Black Friday deals!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#20A1C3</span>
				<span class="wps_wpr_cam_sec_color">#72DBFF</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/bf4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Massive savings waiting — Black Friday!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#DF368E</span>
				<span class="wps_wpr_cam_sec_color">#CE2350</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/bf5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Grab it now, only for Black Friday!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#32BDFE</span>
				<span class="wps_wpr_cam_sec_color">#6CC9F8</span>
			</div>
		</div>

		<!-- Easter Banners templates -->
		<div class="wps-popup_m-content wps_wpr_happy_easter">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/eas1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Egg-citing deals this Happy Easter!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#B11618</span>
				<span class="wps_wpr_cam_sec_color">#910C0E</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/eas2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Hop into savings — Happy Easter!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#6F5345</span>
				<span class="wps_wpr_cam_sec_color">#140F0C</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/eas3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Happy Easter treats just for you!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#E11127</span>
				<span class="wps_wpr_cam_sec_color">#FC1C36</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/eas4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Celebrate joy with Happy Easter deals!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#000000</span>
				<span class="wps_wpr_cam_sec_color">#1E1E1E</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/eas5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Spring into offers this Happy Easter!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#FF0009</span>
				<span class="wps_wpr_cam_sec_color">#84020F</span>
			</div>
		</div>

		<!-- Merry Christmas Banners templates -->
		<div class="wps-popup_m-content wps_wpr_merry_christmas">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Chr1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Snow-body beats our Christmas offers!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#AD0003</span>
				<span class="wps_wpr_cam_sec_color">#380001</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Chr2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Merry Christmas treats just for you!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#C42424</span>
				<span class="wps_wpr_cam_sec_color">#5B0E0C</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Chr3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Ring in Christmas with special deals!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#B1123F</span>
				<span class="wps_wpr_cam_sec_color">#7B233A</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Chr4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Warm Christmas greetings & offers!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#007CC3</span>
				<span class="wps_wpr_cam_sec_color">#89AEC1</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Chr5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Make spirits bright with Christmas offers!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#DAAD61</span>
				<span class="wps_wpr_cam_sec_color">#5E1012</span>
			</div>
		</div>

		<!-- Mother Day Banners templates -->
		<div class="wps-popup_m-content wps_wpr_mothers_day">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/MD1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Mom-entous deals this Mother\'s Day!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#383E5E</span>
				<span class="wps_wpr_cam_sec_color">#BC626E</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/MD2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Mom deserves the best — Happy Mother\'s Day!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#EC1A54</span>
				<span class="wps_wpr_cam_sec_color">#FB8E76</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/MD3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Sweet surprises for sweet Moms!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#631500</span>
				<span class="wps_wpr_cam_sec_color">#03504C</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/MD4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Treat Mom like the queen she is!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#9E1968</span>
				<span class="wps_wpr_cam_sec_color">#ED9587</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/MD5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Love, gratitude & Mother\'s Day offers!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#440003</span>
				<span class="wps_wpr_cam_sec_color">#940506</span>
			</div>
		</div>

		<!-- Thanks Giving Banners templates -->
		<div class="wps-popup_m-content wps_wpr_thanksgiving">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/TG1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Gobble up these Thanksgiving offers!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#4A000B</span>
				<span class="wps_wpr_cam_sec_color">#F65706</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/TG2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Grateful hearts, great deals — Happy Thanksgiving!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#A04B02</span>
				<span class="wps_wpr_cam_sec_color">#B43229</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/TG3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Share the love with Thanksgiving savings!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#A0173F</span>
				<span class="wps_wpr_cam_sec_color">#1C3F39</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/TG4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Thanksgiving traditions and treats!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#801F0A</span>
				<span class="wps_wpr_cam_sec_color">#FF9233</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/TG5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Give thanks for these special offers!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#FFA437</span>
				<span class="wps_wpr_cam_sec_color">#CB2525</span>
			</div>
		</div>

		<!-- Women's Day Banners templates -->
		<div class="wps-popup_m-content wps_wpr_womens_day">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Wo1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Women’s Day Special Sale — Because You Deserve the Best!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#9F0308</span>
				<span class="wps_wpr_cam_sec_color">#E1151C</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Wo2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'This Women’s Day, Celebrate Your Journey!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#FA9DCA</span>
				<span class="wps_wpr_cam_sec_color">#FE2B57</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Wo3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Empowering Every Woman — Happy Women’s Day!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#E65485</span>
				<span class="wps_wpr_cam_sec_color">#FF0956</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Wo4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Exclusive Women’s Day Offers — Celebrate Yourself!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#FF6D79</span>
				<span class="wps_wpr_cam_sec_color">#AF0815</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Wo5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Strong. Bold. Fearless. It’s Your Women’s Day!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#FF7885</span>
				<span class="wps_wpr_cam_sec_color">#BD0515</span>
			</div>
		</div>

		<!-- Valentine's Day Banners templates -->
		<div class="wps-popup_m-content wps_wpr_valentines_day">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Va1.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Make Every Moment Special This Valentine’s Day', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#FF6B74</span>
				<span class="wps_wpr_cam_sec_color">#CB374F</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Va2.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Feel the Romance in the Air This Valentine’s Day', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#F48C92</span>
				<span class="wps_wpr_cam_sec_color">#A60620</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Va3.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Fall in Love with Our Valentine’s Day Offers', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#91090B</span>
				<span class="wps_wpr_cam_sec_color">#EB3C40</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Va4.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Spread Love and Kindness This Valentine’s Day', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#DF3C84</span>
				<span class="wps_wpr_cam_sec_color">#C40A0E</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'admin/camp-images/Va5.webp' ); ?>" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Sweet Deals for Your Valentine’s Day', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#600101</span>
				<span class="wps_wpr_cam_sec_color">#E30000</span>
			</div>
		</div>
	</div>
</div>
