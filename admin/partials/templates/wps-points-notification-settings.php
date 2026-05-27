<?php
/**
 * Points notitfication Settings Template
 *
 * Points notitfication Settings Template
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

$wps_settings = array(
	array(
		'title' => __( 'Enable Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_notification_setting_enable',
		'class'    => 'input-text',
		'desc_tip' => __( 'Toggle this box to enable the points notification.', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Enable Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( "Points table's Custom Points Notification Settings", 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_email_subject',
		'class'    => 'input-text wps_wpr_new_woo_ver_style_text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Custom Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_email_discription_custom_id',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points. Use ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username. ', 'points-and-rewards-for-woocommerce' ) . __( 'Note : Do not use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in this section ', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your points are updated and your total points are ', 'points-and-rewards-for-woocommerce' ) . '[Total Points].',
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Signup Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_signup_email_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Signup Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_signup_email_discription_custom_id',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode to place Signup points dynamically. Use ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode to place username. Use ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' to place Referral points and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total points.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'You have received [Points] points and your total points are [Total Points].', 'points-and-rewards-for-woocommerce' ),

	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Product Purchase Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_product_email_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Product Purchase Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_product_email_discription_custom_id',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'You have received [Points] points and your total points are [Total Points].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode to place the product purchase points. Use ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode to place the username. Use ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of referral points. Use ', 'points-and-rewards-for-woocommerce' ) . '[Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),

	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Order Amount Points Notification Settings(Per ', 'points-and-rewards-for-woocommerce' ) . get_woocommerce_currency_symbol() . __( ' Spent Points)', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'     => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'      => 'text',
		'id'        => 'wps_wpr_amount_email_subject',
		'class'     => 'input-text',
		'desc_tip'  => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Order Amount Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_amount_email_discription_custom_id',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'You have received [Points] points and your total points are [Total Points].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in place of per currency spent points. Use ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username. Use ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of Referral points and Use  ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Referral Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_referral_email_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Referral Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_referral_email_discription_custom_id',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'You have received ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' points and your total points are ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]',
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in place of per currency spent points, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of a username, ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of Referral points, and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Upgrade Membership Level Notification', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_membership_email_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Upgrade Membership Level Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_membership_email_discription_custom_id',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your User Level has been Upgraded to [USERLEVEL] and Now You will get more offers on some products.', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[USERLEVEL]' . __( ' shortcode in place of User Level and ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username. ', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Deduct Assigned Point Notification', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_deduct_assigned_point_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your Points have been Deducted', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_deduct_assigned_point_desciption',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your [DEDUCTEDPOINT] has been deducted from your total points as you have request for your refund, and your Total Point are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[DEDUCTEDPOINT]' . __( ' shortcode in place of points which have been deducted, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username, and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Point.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Points On Cart Sub-Total', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_point_on_cart_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Points Deducted!!', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_point_on_cart_desc',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your [DEDUCTCARTPOINT] Points have been deducted from your account, now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[DEDUCTCARTPOINT]' . __( ' shortcode in place of points which have been deducted,  ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username, and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Points On Order Total Range', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_point_on_order_total_range_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_point_on_order_total_range_desc',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your [ORDERTOTALPOINT] Points have been added. Now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[ORDERTOTALPOINT]' . __( ' shortcode in place of points which have been added. Use ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Order Rewards Points', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_order_rewards_points_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_order_rewards_points_description',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your [REWARDTOTALPOINT] Points have been added. Now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[REWARDTOTALPOINT]' . __( ' shortcode in place of points which have been added, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username, and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Gamification Points Notifications', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_game_points_mail_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_game_points_email_description',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your [GAMEPOINTS] Points have been added. Now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[GAMEPOINTS]' . __( ' shortcode in place of points which have been added ', 'points-and-rewards-for-woocommerce' ) . __( ' and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'User Badges Points Notifications', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_badges_points_mail_subject',
		'class'    => 'input-text',
		'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'textarea_email',
		'id'       => 'wps_wpr_badges_points_email_description',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Your [BADGESPOINTS] Points have been added. Now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[BADGESPOINTS]' . __( ' shortcode in place of points which have been added ', 'points-and-rewards-for-woocommerce' ) . __( ' and ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
);

$wps_settings = apply_filters( 'wps_wpr_email_notification_settings', $wps_settings );
$current_tab  = 'wps_wpr_notificatin_tab';
if ( isset( $_POST['wps_wpr_save_notification'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {

	$wps_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	if ( wp_verify_nonce( $wps_nonce, 'wps-wpr-nonce' ) ) {
		if ( 'wps_wpr_notificatin_tab' == $current_tab ) {

			$wps_wpr_notificatin_array = array();
			/* Enable Settings*/
			$settings_obj->wps_rwpr_filter_checkbox_notification_settings( $_POST, 'wps_wpr_notification_setting_enable' );
			$wps_wpr_post_data = $_POST;
			$wps_wpr_post_data = apply_filters( 'wps_wpr_notification_posted_data', $wps_wpr_post_data, $wps_settings );

			if ( ! empty( $wps_wpr_post_data ) && is_array( $wps_wpr_post_data ) ) {
				foreach ( $wps_wpr_post_data as $key => $value ) {

					$value                             = $settings_obj->wps_rwpr_filter_subj_email_notification_settings( $wps_wpr_post_data, $key );
					$wps_wpr_notificatin_array[ $key ] = $value;
				}
			}
			/* Filter for saving*/
			$wps_wpr_notificatin_array = apply_filters( 'wps_wpr_notification_settings_saved', $wps_wpr_notificatin_array );
			/* Save the Notification settings in the database*/
			if ( is_array( $wps_wpr_notificatin_array ) ) {
				update_option( 'wps_wpr_notificatin_array', $wps_wpr_notificatin_array );
			}
		}
		/* Show Notification When Settings Get Saved*/
		$settings_obj->wps_wpr_settings_saved();
	}
}

$wps_wpr_notification_settings = get_option( 'wps_wpr_notificatin_array', true );
if ( ! is_array( $wps_wpr_notification_settings ) ) {

	$wps_wpr_notification_settings = array();
}

$wps_wpr_notification_enabled = ! empty( $wps_wpr_notification_settings['wps_wpr_notification_setting_enable'] );
$wps_wpr_section_count        = 0;
$wps_wpr_section_index        = array();
if ( ! empty( $wps_settings ) && is_array( $wps_settings ) ) {
	foreach ( $wps_settings as $setting_key => $setting ) {
		$setting_type = ! empty( $setting['type'] ) ? $setting['type'] : '';
		if ( 'title' === $setting_type ) {
			$wps_wpr_section_count++;
			$section_id = 'wps_wpr_notification_section_' . $wps_wpr_section_count;
			$section_title = ! empty( $setting['title'] ) ? $setting['title'] : __( 'Notification Section', 'points-and-rewards-for-woocommerce' );

			$wps_settings[ $setting_key ]['section_id'] = $section_id;
			$wps_wpr_section_index[]                    = array(
				'id'          => $section_id,
				'title'       => $section_title,
				'field_count' => 0,
			);
			continue;
		}

		if ( 'sectionend' !== $setting_type && ! empty( $wps_wpr_section_index ) ) {
			$last_index = count( $wps_wpr_section_index ) - 1;
			$wps_wpr_section_index[ $last_index ]['field_count']++;
		}
	}
}

do_action( 'wps_wpr_add_notice' ); ?>

<div class="wps_wpr_notification_panel">
	<div class="wps_wpr_notification_overview_card">
		<div class="wps_wpr_notification_overview_content">
			<h3><?php esc_html_e( 'Notification Templates', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Manage subject lines and email content for every points event from one place.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<div class="wps_wpr_notification_status">
				<span class="wps_wpr_notification_status_label"><?php esc_html_e( 'Status', 'points-and-rewards-for-woocommerce' ); ?>:</span>
				<span class="wps_wpr_notification_status_badge <?php echo $wps_wpr_notification_enabled ? 'is-enabled' : 'is-disabled'; ?>">
					<?php echo $wps_wpr_notification_enabled ? esc_html__( 'Enabled', 'points-and-rewards-for-woocommerce' ) : esc_html__( 'Disabled', 'points-and-rewards-for-woocommerce' ); ?>
				</span>
			</div>
		</div>
		<div class="wps_wpr_notification_tokens">
			<span class="wps_wpr_notification_tokens_title"><?php esc_html_e( 'Common Placeholders', 'points-and-rewards-for-woocommerce' ); ?></span>
			<ul>
				<li><code>[USERNAME]</code></li>
				<li><code>[TOTALPOINTS]</code></li>
				<li><code>[Points]</code></li>
				<li><code>[Refer Points]</code></li>
			</ul>
		</div>
	</div>

</div>

<div class="wps_wpr_table wps_wpr_notifications_table">
	<div class="wps_wpr_general_wrapper">
		<?php
		$wps_wpr_section_open = false;
		foreach ( $wps_settings as $key => $value ) {
			$value_type = isset( $value['type'] ) ? $value['type'] : '';
				if ( 'title' === $value_type ) {
					if ( $wps_wpr_section_open ) {
						?>
						</div>
						</div>
						<?php
					}
					$section_dom_id = ! empty( $value['section_id'] ) ? $value['section_id'] : '';
					?>
				<div class="wps_wpr_general_row_wrap wps_wpr_notification_section_wrap" id="<?php echo esc_attr( $section_dom_id ); ?>">
				<?php
				$settings_obj->wps_rwpr_generate_heading( $value );
				$wps_wpr_section_open = true;
				?>
				<div class="wps_wpr_section_content">
				<?php
			}
			if ( 'title' !== $value_type && 'sectionend' !== $value_type ) {
				?>
				<div class="wps_wpr_general_row">
				<?php $settings_obj->wps_rwpr_generate_label( $value ); ?>
					<div class="wps_wpr_general_content">
					<?php
					$settings_obj->wps_rwpr_generate_tool_tip( $value );
					if ( 'checkbox' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_checkbox_html( $value, $wps_wpr_notification_settings );
					}
					if ( 'textarea_email' == $value['type'] ) {
						if ( ! empty( $value['desc'] ) ) {
							?>
							<p class="wps_wpr_notification_field_hint"><?php echo esc_html( $value['desc'] ); ?></p>
							<?php
						}
						$settings_obj->wps_rwpr_generate_wp_editor( $value, $wps_wpr_notification_settings );
					}
					if ( 'text' == $value['type'] ) {
						$settings_obj->wps_rwpr_generate_text_html( $value, $wps_wpr_notification_settings );
					}
					do_action( 'wps_wpr_additional_points_notification_settings', $value, $wps_wpr_notification_settings );
					?>
					</div>
				</div>
				<?php
			}
			if ( 'sectionend' === $value_type && $wps_wpr_section_open ) {
				?>
				</div>
				</div>
				<?php
				$wps_wpr_section_open = false;
			}
		}
		if ( $wps_wpr_section_open ) {
			?>
			</div>
			</div>
			<?php
		}
		?>
	</div>
</div>
<div class="clear"></div>
<p class="submit wps_wpr_notification_submit">
	<input type="hidden" name="wps-wpr-nonce" value="<?php echo esc_html( wp_create_nonce( 'wps-wpr-nonce' ) ); ?>">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_notification">
</p>
