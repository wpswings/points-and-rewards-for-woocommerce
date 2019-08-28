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
		if ( $current_tab == 'mwb_wpr_notificatin_tab' ) {

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
		'title' => __( 'Enable Points Notification Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Enable', MWB_RWPR_Domain ),
		'type'          => 'checkbox',
		'id'            => 'mwb_wpr_notification_setting_enable',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Check this box to enable the points notification.', MWB_RWPR_Domain ),
		'desc'          => __( 'Enable Points Notification', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( "Points table's Custom Points Notification Settings", MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_email_subject',
		'class'             => 'input-text mwb_wpr_new_woo_ver_style_text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Custom Points Notification', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[Total Points]' . __( ' shortcode in place of Total Points, ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username and', MWB_RWPR_Domain ) . __( 'In this section donot use', MWB_RWPR_Domain ) . '[Points]' . __( ' shortcode ', MWB_RWPR_Domain ),
		'default'   => __( 'Your points is updated and your total points is ', MWB_RWPR_Domain ) . '[Total Points].',
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Signup Points Notification Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_signup_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Signup Points Notification', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_signup_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[Points]' . __( ' shortcode to be placed Signup Points dynamically, ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username', MWB_RWPR_Domain ) . '[Refer Points]' . __( ' in place of Referral points', MWB_RWPR_Domain ) . '[Comment Points]' . __( ' in place of comment points ', MWB_RWPR_Domain ) . '[Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', MWB_RWPR_Domain ) . '[Total Points]' . __( ' shortcode in place of Total Points.', MWB_RWPR_Domain ),
		'default'   => __( 'You have received [Points] points and your total points is [Total Points].', MWB_RWPR_Domain ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Product Purchase Points Notification Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_product_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Product Purchase Points Notification', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_product_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'You have received [Points] points and your total points is [Total Points].', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[Points]' . __( ' shortcode in place of Product Purchase Points ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ) . '[Refer Points]' . __( ' in place of Referral points', MWB_RWPR_Domain ) . '[Comment Points]' . __( ' in place of comment points ', MWB_RWPR_Domain ) . '[Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', MWB_RWPR_Domain ) . '[Total Points]' . __( ' shortcode in place of Total Points.', MWB_RWPR_Domain ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Order Amount Points Notification Settings(Per ', MWB_RWPR_Domain ) . get_woocommerce_currency_symbol() . __( ' Spent Points)', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_amount_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Order Amount Points Notification', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_amount_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'       => __( 'You have received [Points] points and your total points is [Total Points].', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[Points]' . __( ' shortcode in place of per currency spent points', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ) . '[Refer Points]' . __( ' in place of Referral points', MWB_RWPR_Domain ) . '[Comment Points]' . __( 'in place of comment points and ', MWB_RWPR_Domain ) . '[Total Points]' . __( ' shortcode in place of Total Points.', MWB_RWPR_Domain ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Referral Points Notification Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_referral_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Referral Points Notification', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_referral_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'You have received', MWB_RWPR_Domain ) . '[Points]' . __( 'points and your total points is.', MWB_RWPR_Domain ) . '[Total Points]',
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[Points]' . __( ' shortcode in place of per currency spent points', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ) . '[Refer Points]' . __( ' in place of Referral points', MWB_RWPR_Domain ) . '[Comment Points]' . __( 'in place of comment points and ', MWB_RWPR_Domain ) . '[Total Points]' . __( ' shortcode in place of Total Points.', MWB_RWPR_Domain ),


	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Referral Purchase Points Notification Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_referral_purchase_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Referral Purchase Points Notification', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_referral_purchase_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'You have received', MWB_RWPR_Domain ) . '[Points]' . __( 'points and your total points is', MWB_RWPR_Domain ) . '[Total Points]',
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[Points]' . __( ' shortcode in place of Referral Purchase Points ', MWB_RWPR_Domain ) . '[Refer Points]' . __( ' in place of Referral points', MWB_RWPR_Domain ) . '[Comment Points]' . __( ' in place of comment points', MWB_RWPR_Domain ) . ' [Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', MWB_RWPR_Domain ) . '[Total Points]' . __( ' shortcode in place of Total Points.', MWB_RWPR_Domain ),


	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Upgrade Membership Level Notification', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_membership_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Upgrade Membership Level Notification', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_membership_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'Your User Level has been Upgraded to [USERLEVEL]. And Now You will get some offers on some products.', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[USERLEVEL]' . __( ' shortcode in place of User Level ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Deduct Assigned Point Notification', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_deduct_assigned_point_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Your Points has been Deducted', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_deduct_assigned_point_desciption',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'Your [DEDUCTEDPOINT] has been deducted from your total points as you have requested for your refund, and your Total Point is [TOTALPOINTS].', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[DEDUCTEDPOINT]' . __( ' shortcode in place of points which has been deducted ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Points.', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( "Deduct 'Per Currency Spent' Point Notification", MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_deduct_per_currency_point_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Your Points has been Deducted', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_deduct_per_currency_point_description',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'Your [DEDUCTEDPOINT] has been deducted from your total points as you have requested for your refund, and your Total Point is [TOTALPOINTS].', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[DEDUCTEDPOINT]' . __( ' shortcode in place of points which has been deducted ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Points.', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Points On Cart Sub-Total', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_point_on_cart_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Points Deducted!!', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_point_on_cart_desc',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'Your [DEDUCTCARTPOINT] Points has been deducted from your account, now your Total Point is [TOTALPOINTS].', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[DEDUCTCARTPOINT]' . __( ' shortcode in place of points which has been deducted ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Points On Order Total Range', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', MWB_RWPR_Domain ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_point_on_order_total_range_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', MWB_RWPR_Domain ),
		'default'   => __( 'Points Added', MWB_RWPR_Domain ),
	),
	array(
		'title'         => __( 'Email Description', MWB_RWPR_Domain ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_point_on_order_total_range_desc',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', MWB_RWPR_Domain ),
		'default'   => __( 'Your [ORDERTOTALPOINT] Points has been added in now your Total Point is [TOTALPOINTS].', MWB_RWPR_Domain ),
		'desc'          => __( 'Use ', MWB_RWPR_Domain ) . '[DEDUCTCARTPOINT]' . __( ' shortcode in place of points which has been deducted ', MWB_RWPR_Domain ) . '[USERNAME]' . __( ' shortcode in place of username ', MWB_RWPR_Domain ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
);
	$mwb_settings = apply_filters( 'mwb_wpr_email_notification_settings', $mwb_settings );
foreach ( $mwb_settings as $key => $value ) {
	if ( $value['type'] == 'title' ) {
		?>
					<div class="mwb_wpr_general_row_wrap">
				<?php
				$settings_obj->mwb_rwpr_generate_heading( $value );
	}
	if ( $value['type'] != 'title' && $value['type'] != 'sectionend' ) {
		?>
					<div class="mwb_wpr_general_row">
			<?php $settings_obj->mwb_rwpr_generate_label( $value ); ?>
						<div class="mwb_wpr_general_content">
				<?php
				$settings_obj->mwb_rwpr_generate_tool_tip( $value );
				if ( $value['type'] == 'checkbox' ) {
					$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $mwb_wpr_notification_settings );
				}
				if ( $value['type'] == 'textarea_email' ) {
					echo $value['desc'];
					$settings_obj->mwb_rwpr_generate_wp_editor( $value, $mwb_wpr_notification_settings );
				}
				if ( $value['type'] == 'text' ) {
					$settings_obj->mwb_rwpr_generate_text_html( $value, $mwb_wpr_notification_settings );
				}
				?>
						</div>
					</div>
			<?php
	}
	if ( $value['type'] == 'sectionend' ) {
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
	<input type="submit" value='<?php _e( 'Save changes', MWB_RWPR_Domain ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_notification">
</p>	
