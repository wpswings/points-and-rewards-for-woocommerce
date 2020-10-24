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
include_once MWB_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();
$current_tab = 'mwb_wpr_notificatin_tab';
if ( isset( $_POST['mwb_wpr_save_notification'] ) && isset( $_POST['mwb-wpr-nonce'] ) ) {
	$mwb_nonce = sanitize_text_field( wp_unslash( $_POST['mwb-wpr-nonce'] ) );
	if ( wp_verify_nonce( $mwb_nonce, 'mwb-wpr-nonce' ) ) {
		if ( 'mwb_wpr_notificatin_tab' == $current_tab ) {

			$mwb_wpr_notificatin_array = array();
			/* Enable Settings*/
			$settings_obj->mwb_rwpr_filter_checkbox_notification_settings( $_POST, 'mwb_wpr_notification_setting_enable' );
			$mwb_wpr_post_data = $_POST;
			if ( ! empty( $mwb_wpr_post_data ) && is_array( $mwb_wpr_post_data ) ) {
				foreach ( $mwb_wpr_post_data as $key => $value ) {
					$value = $settings_obj->mwb_rwpr_filter_subj_email_notification_settings( $mwb_wpr_post_data, $key );
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
<div class="mwb_wpr_table">
	<div class="mwb_wpr_general_wrapper">
<?php
$mwb_settings = array(
	array(
		'title' => __( 'Enable Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'checkbox',
		'id'            => 'mwb_wpr_notification_setting_enable',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Check this box to enable the points notification.', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Enable Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( "Points table's Custom Points Notification Settings", 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_email_subject',
		'class'             => 'input-text mwb_wpr_new_woo_ver_style_text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Custom Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username and ', 'points-and-rewards-for-woocommerce' ) . __( 'in this section do not use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode ', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Your points are updated and your total points are ', 'points-and-rewards-for-woocommerce' ) . '[Total Points].',
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Signup Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_signup_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Signup Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_signup_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode to be placed Signup Points dynamically, ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of Referral points ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'You have received [Points] points and your total points are [Total Points].', 'points-and-rewards-for-woocommerce' ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Product Purchase Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_product_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Product Purchase Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_product_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'You have received [Points] points and your total points are [Total Points].', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in place of Product Purchase Points ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of Referral points ', 'points-and-rewards-for-woocommerce' ) . '[Per Currency Spent Points]' . __( ' in place of Per Currency spent points and ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Order Amount Points Notification Settings(Per ', 'points-and-rewards-for-woocommerce' ) . get_woocommerce_currency_symbol() . __( ' Spent Points)', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_amount_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Order Amount Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_amount_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'default'       => __( 'You have received [Points] points and your total points are [Total Points].', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in place of per currency spent points ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of Referral points ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),

	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Referral Points Notification Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_referral_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Referral Points Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_referral_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'You have received ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' points and your total points are ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]',
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[Points]' . __( ' shortcode in place of per currency spent points ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[Refer Points]' . __( ' in place of Referral points ', 'points-and-rewards-for-woocommerce' ) . '[Total Points]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),


	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Upgrade Membership Level Notification', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_membership_email_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Upgrade Membership Level Notification', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_membership_email_discription_custom_id',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Your User Level has been Upgraded to [USERLEVEL]. And Now You will get some offers on some products.', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[USERLEVEL]' . __( ' shortcode in place of User Level ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Deduct Assigned Point Notification', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_deduct_assigned_point_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Your Points has been Deducted', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_deduct_assigned_point_desciption',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Your [DEDUCTEDPOINT] has been deducted from your total points as you have requested for your refund, and your Total Point are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[DEDUCTEDPOINT]' . __( ' shortcode in place of points which has been deducted ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Remaining Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Points On Cart Sub-Total', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_point_on_cart_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Points Deducted!!', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_point_on_cart_desc',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Your [DEDUCTCARTPOINT] Points has been deducted from your account, now your Total Point are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[DEDUCTCARTPOINT]' . __( ' shortcode in place of points which has been deducted ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Points On Order Total Range', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'         => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'text',
		'id'            => 'mwb_wpr_point_on_order_total_range_subject',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Input subject for email.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'         => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
		'type'          => 'textarea_email',
		'id'            => 'mwb_wpr_point_on_order_total_range_desc',
		'class'             => 'input-text',
		'desc_tip'      => __( 'Enter Email Description for user.', 'points-and-rewards-for-woocommerce' ),
		'default'   => __( 'Your [ORDERTOTALPOINT] Points has been added in now your Total Points are [Total Points].', 'points-and-rewards-for-woocommerce' ),
		'desc'          => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[ORDERTOTALPOINT]' . __( ' shortcode in place of points which has been added ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' shortcode in place of username ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' shortcode in place of Total Points.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
);
	$mwb_settings = apply_filters( 'mwb_wpr_email_notification_settings', $mwb_settings );
foreach ( $mwb_settings as $key => $value ) {
	if ( 'title' == $value['type'] ) {
		?>
					<div class="mwb_wpr_general_row_wrap">
				<?php
				$settings_obj->mwb_rwpr_generate_heading( $value );
	}
	if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) {
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
					echo esc_html( $value['desc'] );
					$settings_obj->mwb_rwpr_generate_wp_editor( $value, $mwb_wpr_notification_settings );
				}
				if ( 'text' == $value['type'] ) {
					$settings_obj->mwb_rwpr_generate_text_html( $value, $mwb_wpr_notification_settings );
				}
				do_action( 'mwb_wpr_additional_points_notification_settings', $value, $mwb_wpr_notification_settings );
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
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_notification">
</p>	
