<?php
/**
 * Exit if accessed directly
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * Points notitfication Settings Template
 */
include_once MWB_RWPR_DIR_PATH . '/admin/partials/settings/class-rewardeem-wocoommerce-points-rewards-settings.php';
$settings_obj = new Rewardeem_woocommerce_Points_Rewards_Admin_settings();
$current_tab = 'mwb_wpr_notificatin_tab';
if ( isset( $_POST['mwb_wpr_save_notification'] ) ) {
	if ( wp_verify_nonce( $_POST['mwb-wpr-nonce'], 'mwb-wpr-nonce' ) ) {
		if ( 'mwb_wpr_notificatin_tab' == $current_tab ) {

			$mwb_wpr_notificatin_array = array();
			/* Enable Settings*/
			$settings_obj->mwb_rwpr_filter_checkbox_notification_settings( $_POST, 'mwb_wpr_notification_setting_enable' );
			$mwb_wpr_post_data = $_POST;
			if ( ! empty( $mwb_wpr_post_data ) && is_array( $mwb_wpr_post_data ) ) {
				foreach ( $mwb_wpr_post_data as $key => $value ) {
					$value = $settings_obj->mwb_rwpr_filter_subj_email_notification_settings( $mwb_wpr_post_data, $key );
					$value = stripcslashes( $value );
					$value = sanitize_text_field( $value );
					$mwb_wpr_notificatin_array[ $key ] = $value;
				}
			}
			/* Filter for saving*/
			$mwb_wpr_notificatin_array = apply_filters( 'mwb_wpr_notification_settings_saved', $mwb_wpr_notificatin_array );
			/* Save the Notification settings in the database*/
			if ( is_array( $mwb_wpr_notificatin_array ) ) {
				update_option( 'mwb_wpr_notificatin_array', $mwb_wpr_notificatin_array );
			}
		}
		/* Show Notification When Settings Get Saved*/
		$settings_obj->mwb_wpr_settings_saved();
	}
}
?>
<?php $mwb_wpr_notification_settings = get_option( 'mwb_wpr_notificatin_array', true ); ?>
<?php
if ( ! is_array( $mwb_wpr_notification_settings ) ) :
	$mwb_wpr_notification_settings = array();
endif;
?>
<div class="mwb_table">
	<div class="mwb_wpr_general_wrapper">
<?php
$mwb_settings = array(
	array(
		'title' => __( 'Enable Points Notification Settings', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Enable', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'checkbox',
		'id'            => 'mwb_wpr_notification_setting_enable',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Check this box to enable the points notification.', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Enable Points Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( "Points table's Custom Points Notification Settings", 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_email_subject',
		'class'             => 'input-text mwb_wpr_new_woo_ver_style_text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Custom Points Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]' . __( ' shortcode in place of Total Points, ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username and', 'rewardeem-woocommerce-points-rewards' ) . __( 'In this section donot use', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( ' shortcode ', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your points is updated and your total points is ', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points].',
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Signup Points Notification Settings', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_signup_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Signup Points Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_signup_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( ' shortcode to be placed Signup Points dynamically, ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username', 'rewardeem-woocommerce-points-rewards' ) . '[Refer Points]' . __( ' in place of Referral points', 'rewardeem-woocommerce-points-rewards' ) . '[Comment Points]' . __( ' in place of comment points ', 'rewardeem-woocommerce-points-rewards' ) . '[Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'You have received [Points] points and your total points is [Total Points].', 'rewardeem-woocommerce-points-rewards' ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Product Purchase Points Notification Settings', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_product_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Product Purchase Points Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_product_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'You have received [Points] points and your total points is [Total Points].', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( ' shortcode in place of Product Purchase Points ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ) . '[Refer Points]' . __( ' in place of Referral points', 'rewardeem-woocommerce-points-rewards' ) . '[Comment Points]' . __( ' in place of comment points ', 'rewardeem-woocommerce-points-rewards' ) . '[Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'rewardeem-woocommerce-points-rewards' ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Order Amount Points Notification Settings(Per ', 'rewardeem-woocommerce-points-rewards' ) . get_woocommerce_currency_symbol() . __( ' Spent Points)', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_amount_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Order Amount Points Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_amount_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'       => __( 'You have received [Points] points and your total points is [Total Points].', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( ' shortcode in place of per currency spent points', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ) . '[Refer Points]' . __( ' in place of Referral points', 'rewardeem-woocommerce-points-rewards' ) . '[Comment Points]' . __( 'in place of comment points and ', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'rewardeem-woocommerce-points-rewards' ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Referral Points Notification Settings', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_referral_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Referral Points Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_referral_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'You have received', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( 'points and your total points is.', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]',
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( ' shortcode in place of per currency spent points', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ) . '[Refer Points]' . __( ' in place of Referral points', 'rewardeem-woocommerce-points-rewards' ) . '[Comment Points]' . __( 'in place of comment points and ', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'rewardeem-woocommerce-points-rewards' ),


	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Referral Purchase Points Notification Settings', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_referral_purchase_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Referral Purchase Points Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_referral_purchase_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'You have received', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( 'points and your total points is', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]',
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[Points]' . __( ' shortcode in place of Referral Purchase Points ', 'rewardeem-woocommerce-points-rewards' ) . '[Refer Points]' . __( ' in place of Referral points', 'rewardeem-woocommerce-points-rewards' ) . '[Comment Points]' . __( ' in place of comment points', 'rewardeem-woocommerce-points-rewards' ) . ' [Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', 'rewardeem-woocommerce-points-rewards' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'rewardeem-woocommerce-points-rewards' ),


	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Upgrade Membership Level Notification', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_membership_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Upgrade Membership Level Notification', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_membership_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your User Level has been Upgraded to [USERLEVEL]. And Now You will get some offers on some products.', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[USERLEVEL]' . __( ' shortcode in place of User Level ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Deduct Assigned Point Notification', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_deduct_assigned_point_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your Points has been Deducted', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_deduct_assigned_point_desciption',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your [DEDUCTEDPOINT] has been deducted from your total points as you have requested for your refund, and your Total Point is [TOTALPOINTS].', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[DEDUCTEDPOINT]' . __( ' shortcode in place of points which has been deducted ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Points.', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( "Deduct 'Per Currency Spent' Point Notification", 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_deduct_per_currency_point_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your Points has been Deducted', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_deduct_per_currency_point_description',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your [DEDUCTEDPOINT] has been deducted from your total points as you have requested for your refund, and your Total Point is [TOTALPOINTS].', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[DEDUCTEDPOINT]' . __( ' shortcode in place of points which has been deducted ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Points.', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Points On Cart Sub-Total', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_point_on_cart_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Points Deducted!!', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_point_on_cart_desc',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your [DEDUCTCARTPOINT] Points has been deducted from your account, now your Total Point is [TOTALPOINTS].', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[DEDUCTCARTPOINT]' . __( ' shortcode in place of points which has been deducted ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Points On Order Total Range', 'rewardeem-woocommerce-points-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_point_on_order_total_range_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Points Added', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'title'         => __( 'Email Description', 'rewardeem-woocommerce-points-rewards' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_point_on_order_total_range_desc',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'rewardeem-woocommerce-points-rewards' ),
		'default'   => __( 'Your [ORDERTOTALPOINT] Points has been added in now your Total Point is [TOTALPOINTS].', 'rewardeem-woocommerce-points-rewards' ),
		'desc'          => __( 'Use ', 'rewardeem-woocommerce-points-rewards' ) . '[DEDUCTCARTPOINT]' . __( ' shortcode in place of points which has been deducted ', 'rewardeem-woocommerce-points-rewards' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'rewardeem-woocommerce-points-rewards' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'rewardeem-woocommerce-points-rewards' ),
	),
	array(
		'type'  => 'sectionend',
	),
);
	$mwb_settings = apply_filters( 'mwb_wpr_email_notification_settings', $mwb_settings );
foreach ( $mwb_settings as $key => $value ) {
	if (  'title' == $value['type'] ) {
		?>
					<div class="mwb_wpr_general_row_wrap">
				<?php
				$settings_obj->mwb_rwpr_generate_heading( $value );
	}
	if ( $value['type'] != 'title' && $value['type'] != 'sectionend' ) { //phpcs:ignore WordPress.PHP.YodaConditions.NotYoda
		?>
					<div class="mwb_wpr_general_row">
			<?php $settings_obj->mwb_rwpr_generate_label( $value ); ?>
						<div class="mwb_wpr_general_content">
				<?php
				$settings_obj->mwb_rwpr_generate_tool_tip( $value );
				if ( 'checkbox' == $value['type'] ) {
					$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $mwb_wpr_notification_settings );
				}
				if ( 'textarea_email' == $value['type'] ) {
					echo $value['desc'];
					$settings_obj->mwb_rwpr_generate_wp_editor( $value, $mwb_wpr_notification_settings );
				}
				if ( 'text' == $value['type'] ) {
					$settings_obj->mwb_rwpr_generate_text_html( $value, $mwb_wpr_notification_settings );
				}
				?>
						</div>
					</div>
			<?php
	}
	if ( 'sectionend' == $value['type'] ) {
		?>
				 </div> 
			<?php
	}
}
?>
				
	</div>
</div>
<div class="clear"></div>
<p class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'rewardeem-woocommerce-points-rewards' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_notification">
</p>	
