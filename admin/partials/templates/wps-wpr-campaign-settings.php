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

if ( isset( $_POST['wps_wpr_user_campaign_setting_nonce'] ) ) {
	if ( wp_verify_nonce( ! empty( $_POST['wps_wpr_user_campaign_setting_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_user_campaign_setting_nonce'] ) ) : '', 'user-campaign-setting-nonce' ) ) {
		if ( isset( $_POST['wps_wpr_save_campaign_settings'] ) ) {

			$arr                                           = array();
			$arr['wps_wpr_enable_campaign_settings']       = isset( $_POST['wps_wpr_enable_campaign_settings'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_campaign_settings'] ) ) : '';
			$arr['wps_wpr_enable_sign_up_campaign']        = isset( $_POST['wps_wpr_enable_sign_up_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_sign_up_campaign'] ) ) : '';
			$arr['wps_wpr_enable_referral_campaign']       = isset( $_POST['wps_wpr_enable_referral_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_referral_campaign'] ) ) : '';
			$arr['wps_wpr_enable_birthday_campaign']       = isset( $_POST['wps_wpr_enable_birthday_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_birthday_campaign'] ) ) : '';
			$arr['wps_wpr_enable_gami_points']             = isset( $_POST['wps_wpr_enable_gami_points'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_gami_points'] ) ) : '';
			$arr['wps_wpr_enable_quiz_contest_campaign']   = isset( $_POST['wps_wpr_enable_quiz_contest_campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_quiz_contest_campaign'] ) ) : '';
			$arr['wps_wpr_quiz_question']                  = isset( $_POST['wps_wpr_quiz_question'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_quiz_question'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_quiz_option_one']                = isset( $_POST['wps_wpr_quiz_option_one'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_quiz_option_one'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_quiz_option_two']                = isset( $_POST['wps_wpr_quiz_option_two'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_quiz_option_two'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_quiz_option_three']              = isset( $_POST['wps_wpr_quiz_option_three'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_quiz_option_three'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_quiz_option_four']               = isset( $_POST['wps_wpr_quiz_option_four'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_quiz_option_four'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_quiz_answer']                    = isset( $_POST['wps_wpr_quiz_answer'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_quiz_answer'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_quiz_rewards_points']            = isset( $_POST['wps_wpr_quiz_rewards_points'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_quiz_rewards_points'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_enter_campaign_heading']         = isset( $_POST['wps_wpr_enter_campaign_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enter_campaign_heading'] ) ) : 'Points and Rewards Program';
			$arr['wps_wpr_enter_campaign_image_url']       = isset( $_POST['wps_wpr_enter_campaign_image_url'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enter_campaign_image_url'] ) ) : 'https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/reward.webp';
			$arr['wps_wpr_show_current_points_modal']      = isset( $_POST['wps_wpr_show_current_points_modal'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_show_current_points_modal'] ) ) : '';
			$arr['wps_wpr_show_total_referral_count']      = isset( $_POST['wps_wpr_show_total_referral_count'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_show_total_referral_count'] ) ) : '';
			$arr['wps_wpr_select_page_for_campaign']       = ! empty( $_POST['wps_wpr_select_page_for_campaign'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_select_page_for_campaign'] ), 'sanitize_text_field' ) : array();
			$arr['wps_wpr_campaign_color_one']             = ! empty( $_POST['wps_wpr_campaign_color_one'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_campaign_color_one'] ) ) : '#a13a93';
			$arr['wps_wpr_campaign_color_two']             = ! empty( $_POST['wps_wpr_campaign_color_two'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_campaign_color_two'] ) ) : '#ffbb21';
			$arr                                           = apply_filters( 'wps_wpr_save_campaign_extra_data', $arr );
			update_option( 'wps_wpr_campaign_settings', $arr );
		}
	}

	// Show saved msg.
	$settings_obj->wps_wpr_settings_saved();
}

$wps_wpr_campaign_settings            = get_option( 'wps_wpr_campaign_settings', array() );
$wps_wpr_campaign_settings            = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
$wps_wpr_enable_campaign_settings     = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_campaign_settings'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_campaign_settings'] : '';
$wps_wpr_enable_sign_up_campaign      = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_sign_up_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_sign_up_campaign'] : '';
$wps_wpr_enable_referral_campaign     = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_referral_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_referral_campaign'] : '';
$wps_wpr_enable_birthday_campaign     = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_birthday_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_birthday_campaign'] : '';
$wps_wpr_enable_gami_points           = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_gami_points'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_gami_points'] : '';
$wps_wpr_enable_quiz_contest_campaign = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_quiz_contest_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_quiz_contest_campaign'] : '';
$wps_wpr_quiz_question                = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_question'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_question'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_question'] : array();
$wps_wpr_quiz_option_one              = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_one'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_one'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_one'] : array();
$wps_wpr_quiz_option_two              = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_two'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_two'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_two'] : array();
$wps_wpr_quiz_option_three            = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_three'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_three'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_three'] : array();
$wps_wpr_quiz_option_four             = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_four'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_four'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_four'] : array();
$wps_wpr_quiz_answer                  = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_answer'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_answer'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_answer'] : array();
$wps_wpr_quiz_rewards_points          = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_rewards_points'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_rewards_points'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_rewards_points'] : array();
$wps_wpr_enter_campaign_heading       = ! empty( $wps_wpr_campaign_settings['wps_wpr_enter_campaign_heading'] ) ? $wps_wpr_campaign_settings['wps_wpr_enter_campaign_heading'] : 'Points and Rewards Program';
$wps_wpr_enter_campaign_image_url     = ! empty( $wps_wpr_campaign_settings['wps_wpr_enter_campaign_image_url'] ) ? $wps_wpr_campaign_settings['wps_wpr_enter_campaign_image_url'] : 'https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/reward.webp';
$wps_wpr_show_current_points_modal    = ! empty( $wps_wpr_campaign_settings['wps_wpr_show_current_points_modal'] ) ? $wps_wpr_campaign_settings['wps_wpr_show_current_points_modal'] : '';
$wps_wpr_show_total_referral_count    = ! empty( $wps_wpr_campaign_settings['wps_wpr_show_total_referral_count'] ) ? $wps_wpr_campaign_settings['wps_wpr_show_total_referral_count'] : '';
$wps_wpr_select_page_for_campaign     = ! empty( $wps_wpr_campaign_settings['wps_wpr_select_page_for_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_select_page_for_campaign'] : array();
$wps_wpr_campaign_color_one           = ! empty( $wps_wpr_campaign_settings['wps_wpr_campaign_color_one'] ) ? $wps_wpr_campaign_settings['wps_wpr_campaign_color_one'] : '#a13a93';
$wps_wpr_campaign_color_two           = ! empty( $wps_wpr_campaign_settings['wps_wpr_campaign_color_two'] ) ? $wps_wpr_campaign_settings['wps_wpr_campaign_color_two'] : '#ffbb21';
$upgrade_link                         = '<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro" target="_blank">Click here</a>';
$message                              = sprintf( /* translators: %s: sms msg */ esc_html__( 'Unlock this premium feature by upgrading to the Pro plugin. %s to get started!', 'points-and-rewards-for-woocommerce' ), $upgrade_link );

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
							<option value="home" <?php selected( in_array( 'home', $wps_wpr_select_page_for_campaign ) ); ?>>Home</option>
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
						foreach ( $wps_wpr_quiz_question as $index => $quiz ) : ?>
							<article class="wps_wpr_general_row wps_wpr_quiz_row">

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Question', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="text" name="wps_wpr_quiz_question[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $quiz ); ?>" required>
									<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'This field is required', 'points-and-rewards-for-woocommerce' ); ?></span>
								</div>

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Enter Options', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="text" name="wps_wpr_quiz_option_one[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_one[$index] ); ?>" required>
									<input type="text" name="wps_wpr_quiz_option_two[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_two[$index] ); ?>" required>
									<input type="text" name="wps_wpr_quiz_option_three[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_three[$index] ); ?>" required>
									<input type="text" name="wps_wpr_quiz_option_four[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_option_four[$index] ); ?>" required>
									<div class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'All options are required', 'points-and-rewards-for-woocommerce' ); ?></div>
								</div>

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Answer', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="text" name="wps_wpr_quiz_answer[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_answer[$index] ); ?>" required>
									<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'Answer is required', 'points-and-rewards-for-woocommerce' ); ?></span>
								</div>

								<label class="wps_wpr_general_label"><?php esc_html_e( 'Quiz Rewards Points', 'points-and-rewards-for-woocommerce' ); ?></label>
								<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
									<input type="number" min="0" name="wps_wpr_quiz_rewards_points[]" class="wps_wpr_quiz_field" value="<?php echo esc_html( $wps_wpr_quiz_rewards_points[$index] ); ?>" required>
									<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice validation_error" style="color:red; display:none;"><?php esc_html_e( 'Reward points are required', 'points-and-rewards-for-woocommerce' ); ?></span>
								</div>

								<div class="wps_wpr_general_actions" style="margin-top:10px;">
									<button type="button" class="button wps_wpr_remove_quiz">
										<?php esc_html_e( 'Remove Quiz', 'points-and-rewards-for-woocommerce' ); ?>
									</button>
								</div>

							</article>
						<?php endforeach;
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
									<?php esc_html_e( 'Remove Quiz', 'points-and-rewards-for-woocommerce' ); ?>
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
						<span><a href="javascript:void(0);" class="wps_wpr_view_campaign_existing_template" target="_blank">View Existing Templates</a></span>
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
		<div class="wps-popup_m-sidebar">
			<span class="wps-wpr_temp-select-haloween" id="wps_wpr_halloween"><?php esc_html_e( 'Halloween', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-black-friday" id="wps_wpr_black_friday"><?php esc_html_e( 'Black Friday', 'points-and-rewards-for-woocommerce' ); ?></span>
			<span class="wps-wpr_temp-select-easter" id="wps_wpr_happy_easter"><?php esc_html_e( 'Happy Easter', 'points-and-rewards-for-woocommerce' ); ?></span>
		</div>
		<div class="wps-popup_m-content wps_wpr_halloween active_tab">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/hal1.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Spooky deals await this Halloween Festival!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#582A47</span>
				<span class="wps_wpr_cam_sec_color">#B14539</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/hal2.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Halloween Festival treats you can’t miss!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#1D092D</span>
				<span class="wps_wpr_cam_sec_color">#9544D1</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/hal3.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Get your Halloween Festival savings now!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#3B1C57</span>
				<span class="wps_wpr_cam_sec_color">#703177</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/hal4.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Frightful fun during the Halloween Festival!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#69B827</span>
				<span class="wps_wpr_cam_sec_color">#388589</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/hal5.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Scare up some deals — Halloween Festival style!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#7671B4</span>
				<span class="wps_wpr_cam_sec_color">#4D43B3</span>
			</div>
		</div>
		<div class="wps-popup_m-content wps_wpr_black_friday">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/bf1.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Black Friday steals are here!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#F6CB31</span>
				<span class="wps_wpr_cam_sec_color">#E59E0C</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/bf2.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Don’t miss out this Black Friday!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#01A5C2</span>
				<span class="wps_wpr_cam_sec_color">#6ABA41</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/bf3.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Shop smart with Black Friday deals!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#20A1C3</span>
				<span class="wps_wpr_cam_sec_color">#72DBFF</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/bf4.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Massive savings waiting — Black Friday!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#DF368E</span>
				<span class="wps_wpr_cam_sec_color">#CE2350</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/bf5.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Grab it now, only for Black Friday!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#32BDFE</span>
				<span class="wps_wpr_cam_sec_color">#6CC9F8</span>
			</div>
		</div>
		<div class="wps-popup_m-content wps_wpr_happy_easter">
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/eas1.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Egg-citing deals this Happy Easter!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#B11618</span>
				<span class="wps_wpr_cam_sec_color">#910C0E</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/eas2.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Hop into savings — Happy Easter!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#6F5345</span>
				<span class="wps_wpr_cam_sec_color">#140F0C</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/eas3.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Happy Easter treats just for you!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#E11127</span>
				<span class="wps_wpr_cam_sec_color">#FC1C36</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/eas4.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Celebrate joy with Happy Easter deals!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#000000</span>
				<span class="wps_wpr_cam_sec_color">#1E1E1E</span>
			</div>
			<div class="wps-popup_mcb-img">
				<img class="wps_wpr_cam_banner_image" src="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/eas5.webp" alt="festive image" />
				<div class="h3 wps_wpr_camp_banner_heading"><?php esc_html_e( 'Spring into offers this Happy Easter!', 'points-and-rewards-for-woocommerce' ); ?></div>
				<button class="wps_wpr_apply_banner_img"><?php esc_html_e( 'Apply', 'points-and-rewards-for-woocommerce' ); ?></button>
				<span class="wps_wpr_cam_prim_color">#FF0009</span>
				<span class="wps_wpr_cam_sec_color">#84020F</span>
			</div>
		</div>
	</div>
</div>