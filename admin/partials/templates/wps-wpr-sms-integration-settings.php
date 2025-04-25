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

if ( isset( $_POST['wps_wpr_save_sms_settings'] ) ) {

    $wps_wpr_sms_setting_nonce = ! empty( $_POST['wps_wpr_sms_setting_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_sms_setting_nonce'] ) ) : '';
    if ( wp_verify_nonce( $wps_wpr_sms_setting_nonce, 'sms-setting-nonce' ) ) {

        $arr                                        = array();
        $arr['wps_wpr_enable_sms_api_settings']     = ! empty( $_POST['wps_wpr_enable_sms_api_settings'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_sms_api_settings'] ) ) : 'no';
        $arr['wps_wpr_sms_account_sid']             = ! empty( $_POST['wps_wpr_sms_account_sid'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_sms_account_sid'] ) ) : '';
        $arr['wps_wpr_sms_account_token']           = ! empty( $_POST['wps_wpr_sms_account_token'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_sms_account_token'] ) ) : '';
        $arr['wps_wpr_sms_twilio_no']               = ! empty( $_POST['wps_wpr_sms_twilio_no'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_sms_twilio_no'] ) ) : '';
        $arr['wps_wpr_active_deactive_sms_notify']  = ! empty( $_POST['wps_wpr_active_deactive_sms_notify'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_active_deactive_sms_notify'] ) ) : '';
		$arr['wps_wpr_enable_whatsapp_api_feature'] = ! empty( $_POST['wps_wpr_enable_whatsapp_api_feature'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_whatsapp_api_feature'] ) ) : '';
		$arr['wps_wpr_whatsapp_access_token']       = ! empty( $_POST['wps_wpr_whatsapp_access_token'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_whatsapp_access_token'] ) ) : '';
		$arr['wps_wpr_whatsapp_phone_number']       = ! empty( $_POST['wps_wpr_whatsapp_phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_whatsapp_phone_number'] ) ) : '';
		$arr['wps_wpr_whatsapp_template_name']      = ! empty( $_POST['wps_wpr_whatsapp_template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_whatsapp_template_name'] ) ) : '';
		$arr['wps_wpr_deactivate_whatsapp_api']     = ! empty( $_POST['wps_wpr_deactivate_whatsapp_api'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_deactivate_whatsapp_api'] ) ) : '';
        update_option( 'wps_wpr_save_sms_settings', $arr );
    }

    // Show saved msg.
    $settings_obj->wps_wpr_settings_saved();
}

$wps_wpr_save_sms_settings           = get_option( 'wps_wpr_save_sms_settings' );
$wps_wpr_save_sms_settings           = ! empty( $wps_wpr_save_sms_settings ) && is_array( $wps_wpr_save_sms_settings ) ? $wps_wpr_save_sms_settings : array();
$wps_wpr_enable_sms_api_settings     = ! empty( $wps_wpr_save_sms_settings['wps_wpr_enable_sms_api_settings'] ) ? $wps_wpr_save_sms_settings['wps_wpr_enable_sms_api_settings'] : 'no';
$wps_wpr_sms_account_sid             = ! empty( $wps_wpr_save_sms_settings['wps_wpr_sms_account_sid'] ) ? $wps_wpr_save_sms_settings['wps_wpr_sms_account_sid'] : '';
$wps_wpr_sms_account_token           = ! empty( $wps_wpr_save_sms_settings['wps_wpr_sms_account_token'] ) ? $wps_wpr_save_sms_settings['wps_wpr_sms_account_token'] : '';
$wps_wpr_sms_twilio_no               = ! empty( $wps_wpr_save_sms_settings['wps_wpr_sms_twilio_no'] ) ? $wps_wpr_save_sms_settings['wps_wpr_sms_twilio_no'] : '';
$wps_wpr_active_deactive_sms_notify  = ! empty( $wps_wpr_save_sms_settings['wps_wpr_active_deactive_sms_notify'] ) ? $wps_wpr_save_sms_settings['wps_wpr_active_deactive_sms_notify'] : '';
$wps_wpr_enable_whatsapp_api_feature = ! empty( $wps_wpr_save_sms_settings['wps_wpr_enable_whatsapp_api_feature'] ) ? $wps_wpr_save_sms_settings['wps_wpr_enable_whatsapp_api_feature'] : '';
$wps_wpr_whatsapp_access_token       = ! empty( $wps_wpr_save_sms_settings['wps_wpr_whatsapp_access_token'] ) ? $wps_wpr_save_sms_settings['wps_wpr_whatsapp_access_token'] : '';
$wps_wpr_whatsapp_phone_number       = ! empty( $wps_wpr_save_sms_settings['wps_wpr_whatsapp_phone_number'] ) ? $wps_wpr_save_sms_settings['wps_wpr_whatsapp_phone_number'] : '';
$wps_wpr_whatsapp_template_name      = ! empty( $wps_wpr_save_sms_settings['wps_wpr_whatsapp_template_name'] ) ? $wps_wpr_save_sms_settings['wps_wpr_whatsapp_template_name'] : '';
$wps_wpr_deactivate_whatsapp_api     = ! empty( $wps_wpr_save_sms_settings['wps_wpr_deactivate_whatsapp_api'] ) ? $wps_wpr_save_sms_settings['wps_wpr_deactivate_whatsapp_api'] : '';
$url                                 = '<a href="https://console.twilio.com/?frameUrl=%2Fconsole%3Fx-target-region%3Dus1" class="wps_wpr_create_whatsapp_token_link" target="_blank">Click Here</a>';
$num                                 = '<a href="https://console.twilio.com/us1/develop/phone-numbers/manage/search?isoCountry=US&types[]=Local&types[]=Mobile&types[]=Tollfree&capabilities[]=Fax&capabilities[]=Mms&capabilities[]=Sms&capabilities[]=Voice&searchTerm=&searchFilter=left&searchType=number" class="wps_wpr_create_whatsapp_token_link" target="_blank">Click Here</a>';
$whtasapp_url                        = '<a href="https://business.facebook.com/business/loginpage/?next=https%3A%2F%2Fdevelopers.facebook.com%2Fapps%2F967217188484687%2Fwhatsapp-business%2Fwa-dev-console%2F%3Fbusiness_id%3D1466242894064567#" class="wps_wpr_create_whatsapp_token_link" target="_blank">Click Here</a>';
$whatsapp_num                        = '<a href="https://developers.facebook.com/apps/1306844587187157/whatsapp-business/wa-dev-console/?business_id=1466242894064567" class="wps_wpr_create_whatsapp_token_link" target="_blank">Click Here</a>';
$preview                             = '<a href="#" target="_blank" class="wps_wpr_preview_whatsapp_sample">Preview Sample Template</a>';
?>

<div class="wps_wpr_user_badges_main_wrappers">
	<form method="POST" action="" class="wps_wpr_user_badges_form">
		<main class="wps_wpr_main_user_badges_wrapper">
			<section class="wps_wpr_general_row_wrap">
				<div class="wps_wpr_user_badges_settings_heading wps_wpr_general_sign_title wps_wpr_doc_video_wrapper"><?php esc_html_e( 'SMS Integration', 'points-and-rewards-for-woocommerce' ); ?></div>
				<input type="hidden" name="wps_wpr_sms_setting_nonce" id="wps_wpr_sms_setting_nonce" value="<?php echo esc_html( wp_create_nonce( 'sms-setting-nonce' ) ); ?>">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_sms_api_settings" class="wps_wpr_general_label"><?php esc_html_e( 'Enable SMS API Features', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_sms_api_settings" class="wps_wpr_enable_sms_api_settings" value="yes" <?php checked( $wps_wpr_enable_sms_api_settings, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Enable this setting to receive an SMS notification whenever a user earns or spends points.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
                <article class="wps_wpr_general_row">
					<label for="wps_wpr_sms_account_sid" class="wps_wpr_general_label"><?php esc_html_e( 'Enter Account SID', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="text" name="wps_wpr_sms_account_sid" class="wps_wpr_sms_account_sid" value="<?php echo esc_html( $wps_wpr_sms_account_sid ); ?>">
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php /* translators: %s: sid */ printf( esc_html__( 'Please enter your Account SID. To create a SID, %s', 'points-and-rewards-for-woocommerce' ), wp_kses_post( $url ) ); ?></span>
					</div>
				</article>
                <article class="wps_wpr_general_row">
					<label for="wps_wpr_sms_account_token" class="wps_wpr_general_label"><?php esc_html_e( 'Enter Account Auth Token', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="text" name="wps_wpr_sms_account_token" class="wps_wpr_sms_account_token" value="<?php echo esc_html( $wps_wpr_sms_account_token ); ?>">
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php /* translators: %s: token */ printf( esc_html__( 'Please enter your auth token. To create a auth token, %s', 'points-and-rewards-for-woocommerce' ), wp_kses_post( $url ) ); ?></span>
					</div>
				</article>
                <article class="wps_wpr_general_row">
					<label for="wps_wpr_sms_twilio_no" class="wps_wpr_general_label"><?php esc_html_e( 'Enter Account Twilio Number', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="text" name="wps_wpr_sms_twilio_no" class="wps_wpr_sms_twilio_no" value="<?php echo esc_html( $wps_wpr_sms_twilio_no ); ?>">
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php /* translators: %s: twilio no */ printf( esc_html__( 'Please enter a valid twilio account number. To Buy a Twilio Number, %s', 'points-and-rewards-for-woocommerce' ), wp_kses_post( $num ) ); ?></span>
					</div>
				</article>
                <article class="wps_wpr_general_row">
					<label for="wps_wpr_active_deactive_sms_notify" class="wps_wpr_general_label"><?php esc_html_e( 'Enable this setting to allow users to deactivate SMS notifications.', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_active_deactive_sms_notify" class="wps_wpr_active_deactive_sms_notify" value="yes" <?php checked( $wps_wpr_active_deactive_sms_notify, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Let users manage their SMS notification preferences directly from their account page.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
			<section class="wps_wpr_general_row_wrap">
				<div class="wps_wpr_user_badges_settings_heading wps_wpr_general_sign_title wps_wpr_doc_video_wrapper"><?php esc_html_e( 'Whatsapp Integration', 'points-and-rewards-for-woocommerce' ); ?></div>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_enable_whatsapp_api_feature" class="wps_wpr_general_label"><?php esc_html_e( 'Enable whatsapp api feature.', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_enable_whatsapp_api_feature" class="wps_wpr_enable_whatsapp_api_feature" value="yes" <?php checked( $wps_wpr_enable_whatsapp_api_feature, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Enable this settings to send earning and redeeming message on whatsapp.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_whatsapp_access_token" class="wps_wpr_general_label"><?php esc_html_e( 'Enter Access Token', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="text" name="wps_wpr_whatsapp_access_token" class="wps_wpr_whatsapp_access_token" value="<?php echo esc_html( $wps_wpr_whatsapp_access_token ); ?>">
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php /* translators: %s: sid */ printf( esc_html__( 'Please enter your access token. To create a token, %s', 'points-and-rewards-for-woocommerce' ), wp_kses_post( $whtasapp_url ) ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_whatsapp_phone_number" class="wps_wpr_general_label"><?php esc_html_e( 'Enter Phone Number ID', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="number" name="wps_wpr_whatsapp_phone_number" class="wps_wpr_whatsapp_phone_number" value="<?php echo esc_html( $wps_wpr_whatsapp_phone_number ); ?>">
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php /* translators: %s: sid */ printf( esc_html__( 'Please enter you phone number id. To get Phone number ID, %s', 'points-and-rewards-for-woocommerce' ), wp_kses_post( $whatsapp_num ) ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_whatsapp_template_name" class="wps_wpr_general_label"><?php esc_html_e( 'Enter whatsapp template name', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="text" name="wps_wpr_whatsapp_template_name" class="wps_wpr_whatsapp_template_name" value="<?php echo esc_html( $wps_wpr_whatsapp_template_name ); ?>">
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php /* translators: %s: sid */ printf( esc_html__( 'The WhatsApp template name must remain fixed and cannot be modified. You can use this name when creating a custom template, %s', 'points-and-rewards-for-woocommerce' ), wp_kses_post( $preview ) ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_deactivate_whatsapp_api" class="wps_wpr_general_label"><?php esc_html_e( 'Enable this setting to allow users to deactivate Whatsapp notifications.', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_deactivate_whatsapp_api" class="wps_wpr_deactivate_whatsapp_api" value="yes" <?php checked( $wps_wpr_deactivate_whatsapp_api, 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Let users manage their Whatsapp notification preferences directly from their account page.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
		</main>
		<input type="submit" name="wps_wpr_save_sms_settings" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_save_sms_settings" value="<?php esc_html_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
	</form>
</div>

<!-- Whatsapp Sample template -->
<div class="wps_wpr_preview_whatsapp_template_img" style="display: none;">
	<img src='<?php echo esc_url( WPS_RWPR_DIR_URL ) . 'admin/images/par-whatsapp-temp.png'; ?>'>
</div>