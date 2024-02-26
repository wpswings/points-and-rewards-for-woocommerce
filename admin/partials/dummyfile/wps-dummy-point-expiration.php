<?php
/**
 * Points Expiration Template.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Points_And_Rewards_For_Woocommerce_Pro
 * @subpackage Points_And_Rewards_For_Woocommerce_Pro/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . 'admin/class-points-rewards-for-woocommerce-dummy-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Dummy_Settings( '', '' );

$wps_points_exipration_array = array(
	array(
		'title' => __( 'Points Expiration', 'ultimate-woocommerce-points-and-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Points Expiration', 'ultimate-woocommerce-points-and-rewards' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Enable Point Expiration.', 'ultimate-woocommerce-points-and-rewards' ),
		'id'       => 'wps_wpr_points_expiration_enable',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Check this, If you want to set the expiration period for the Rewarded Points.', 'ultimate-woocommerce-points-and-rewards' ),
		'default'  => 0,
	),
	array(
		'title'    => __( 'Show Points expiration on My Account Page', 'ultimate-woocommerce-points-and-rewards' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_points_exp_onmyaccount',
		'class'    => 'input-text wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Check this, If you want to show the expiration period for the Rewarded Points on My Account Page.', 'ultimate-woocommerce-points-and-rewards' ),
		'default'  => 0,
		'desc'     => __( 'Expiration will get displayed just below the Total Point.', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'title'             => __( 'Set the Required Threshold', 'ultimate-woocommerce-points-and-rewards' ),
		'type'              => 'number',
		'default'           => 1,
		'id'                => 'wps_wpr_points_expiration_threshold',
		'custom_attributes' => array( 'min' => '"1"' ),
		'class'             => 'input-text wps_wpr_common_width wps_wpr_pro_plugin_settings',
		'desc_tip'          => __( 'Set the threshold for points expiration, The expiration period will be calculated when the threshold has been reached.', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'title'       => __( 'Set Expiration Time', 'ultimate-woocommerce-points-and-rewards' ),
		'type'        => 'number_text',
		'class'       => 'wps_wpr_pro_plugin_settings',
		'desc_tip'    => __( 'Set the Time-Period for "When do the points need to expire?" It will calculated over the above Threshold Time', 'ultimate-woocommerce-points-and-rewards' ),
		'number_text' => array(
			array(
				'type'              => 'number',
				'id'                => 'wps_wpr_points_expiration_time_num',
				'class'             => 'input-text wps_wpr_common_width wps_wpr_pro_plugin_settings',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc_tip'          => __(
					'Set the Time-Period for "When do the points need to expire?" It will calculated over the above Threshold Time',
					'ultimate-woocommerce-points-and-rewards'
				),
			),
			array(
				'id'       => 'wps_wpr_points_expiration_time_drop',
				'class'    => 'wps_wpr_pro_plugin_settings',
				'type'     => 'search&select',
				'desc_tip' => __( 'Select those categories which you want to allow to customers for purchase that product through points.', 'ultimate-woocommerce-points-and-rewards' ),
				'options'  => $settings_obj->wps_wpr_get_dummy_option_of_points(),
			),
		),
	),
	array(
		'title'    => __( 'Enable this setting to Notify user on Mail', 'ultimate-woocommerce-points-and-rewards' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_enable_expire_notify_setting',
		'class'    => 'input-text wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'After enabling this setting, user will get notification for his points expire.', 'ultimate-woocommerce-points-and-rewards' ),
		'default'  => 0,
		'desc'     => __( 'Check this, If you want to notify user on mail for points expiration notifications.', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'title'             => __( 'Email Notification(Re-Notify Days)', 'ultimate-woocommerce-points-and-rewards' ),
		'type'              => 'text',
		'custom_attributes' => array( 'min' => '0' ),
		'id'                => 'wps_wpr_points_expiration_email',
		'class'             => 'text_points wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
		'desc'              => __( 'Days.', 'ultimate-woocommerce-points-and-rewards' ),
		'desc_tip'          => __( 'Set the number of days before the Email will get sent out.', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Points Expiry Notifications', 'ultimate-woocommerce-points-and-rewards' ),
		'type'  => 'title',
	),
	array(
		'title'             => __( 'Threshold Message', 'ultimate-woocommerce-points-and-rewards' ),
		'type'              => 'textarea',
		'custom_attributes' => array(
			'cols' => '"35"',
			'rows' => '"5"',
		),
		'id'                => 'wps_wpr_threshold_notif',
		'class'             => 'input-text wps_wpr_pro_plugin_settings',
		'desc_tip'          => __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have reached the Threshold now they should redeem their Points before it will get expired', 'ultimate-woocommerce-points-and-rewards' ),
		'default'           => __( 'You have reached your Threshold and your Total Point is:', 'ultimate-woocommerce-points-and-rewards' ) . ' [TOTALPOINT]' . __( ', which will get expired on', 'ultimate-woocommerce-points-and-rewards' ) . '[EXPIRYDATE]',
		'desc2'             => __( 'Use these shortcodes to provide an appropriate message to your customers that their total points are expiring on', 'ultimate-woocommerce-points-and-rewards' ) . __( ' [EXPIRYDATE] .', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'title'             => __( 'Re-notify Message before some days', 'ultimate-woocommerce-points-and-rewards' ),
		'type'              => 'textarea',
		'custom_attributes' => array(
			'cols' => '"35"',
			'rows' => '"5"',
		),
		'id'                => 'wps_wpr_re_notification',
		'class'             => 'input-text wps_wpr_pro_plugin_settings',
		'desc_tip'          => __( 'Entered Message will appears inside the Email Template for notifying the Customer that they have left just some days more before the expiration', 'ultimate-woocommerce-points-and-rewards' ),
		'default'           => __( 'Do not forget to redeem your points', 'ultimate-woocommerce-points-and-rewards' ) . ' [TOTALPOINT]' . __( 'before it will get expired on', 'ultimate-woocommerce-points-and-rewards' ) . '[EXPIRYDATE]',
		'desc2'             => __( 'Use these shortcodes for providing an appropriate message for your customers', 'ultimate-woocommerce-points-and-rewards' ) . __( 'for their Total Points [EXPIRYDATE] for the Expiration Date ', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'title'             => __( 'Message when Points has been Expired', 'ultimate-woocommerce-points-and-rewards' ),
		'type'              => 'textarea',
		'custom_attributes' => array(
			'cols' => '"35"',
			'rows' => '"5"',
		),
		'id'                => 'wps_wpr_expired_notification',
		'class'             => 'input-text wps_wpr_pro_plugin_settings',
		'desc_tip'          => __( 'Entered Message will appears inside the Email Template for notifying the Customer that the Points has been expired', 'ultimate-woocommerce-points-and-rewards' ),
		'default'           => __( 'Your Points has been expired, you may earn more Points and use the benefit more', 'ultimate-woocommerce-points-and-rewards' ),
		'desc2'             => __( 'This message will be sent when the points are expired.', 'ultimate-woocommerce-points-and-rewards' ),
	),
	array(
		'type' => 'sectionend',
	),
);

$wps_points_exipration_array = apply_filters( 'wps_wpr_points_exprition_settings', $wps_points_exipration_array );
$general_settings            = get_option( 'wps_wpr_product_purchase_settings', array() );
$general_settings            = ! empty( $general_settings ) && is_array( $general_settings ) ? $general_settings : array();
?>

<div class="wps_table">
	<div class="wps_wpr_general_wrapper wps_wpr_pro_plugin_settings">
			<?php
			foreach ( $wps_points_exipration_array as $key => $value ) {
				if ( 'title' == $value['type'] ) {
					?>
				<div class="wps_wpr_general_row_wrap">
					<?php $settings_obj->wps_rwpr_generate_dummy_heading( $value ); ?>
				<?php } ?>
				<?php if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) { ?>
				<div class="wps_wpr_general_row">
						<?php $settings_obj->wps_rwpr_generate_dummy_label( $value ); ?>
					<div class="wps_wpr_general_content">
						<?php
						$settings_obj->wps_rwpr_generate_dummy_tool_tip( $value );
						if ( 'checkbox' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_dummy_checkbox_html( $value, $general_settings );
						}
						if ( 'number' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_dummy_number_html( $value, $general_settings );
						}
						if ( 'multiple_checkbox' == $value['type'] ) {
							foreach ( $value['multiple_checkbox'] as $k => $val ) {
								$settings_obj->wps_rwpr_generate_dummy_checkbox_html( $val, $general_settings );
							}
						}
						if ( 'text' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_dummy_text_html( $value, $general_settings );
						}
						if ( 'textarea' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_dummy_textarea_html( $value, $general_settings );
						}
						if ( 'number_text' == $value['type'] ) {
							foreach ( $value['number_text'] as $k => $val ) {
								if ( 'number' == $val['type'] ) {
									$settings_obj->wps_rwpr_generate_dummy_number_html( $val, $general_settings );
								}
								if ( 'search&select' == $val['type'] ) {
									$settings_obj->wps_wpr_generate_dummy_search_select_html( $val, $general_settings );

								}
							}
						}
						?>
					</div>
				</div>
			<?php } ?>
				<?php if ( 'sectionend' == $value['type'] ) : ?>
			</div>	
			<?php endif; ?>
		<?php } ?> 		
	</div>
</div>
<div class="clear"></div>
<p class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'ultimate-woocommerce-points-and-rewards' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes wps_wpr_disabled_pro_plugin" name="wps_wpr_save_point_expiration">
</p>
