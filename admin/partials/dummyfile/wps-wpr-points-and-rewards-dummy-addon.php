<?php
/**
 * Notification Addon Template
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

$wps_wpr_save_notification_addon = array(
	array(
		'title' => __( 'Points notification addon', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Enable points notification addon', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Enable points notification addon', 'points-and-rewards-for-woocommerce' ),
		'id'       => 'wps_wpr_enable_notification_addon',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Check this box to enable points notification addon', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
	),
	array(
		'title'            => __( 'Select Position', 'points-and-rewards-for-woocommerce' ),
		'type'             => 'singleSelectDropDownWithKeyvalue',
		'id'               => 'wps_wpr_enable_notification_addon_button_position',
		'class'            => 'wc-enhanced-select wps_wpr_pro_plugin_settings',
		'desc'             => __( 'Use this shortcode [wps_wpr_notification_button] to display a pop-up button.', 'points-and-rewards-for-woocommerce' ),
		'desc_tip'         => __( 'Select whether you want to display the Notification Button left, right, or want to use a shortcode.  If you select the shortcode [wps_wpr_notification_button] then the notification button will work only on the page you use this code. Note- If you select shortcode then Select Pages will not work plus you can’t change the position of the Button.', 'points-and-rewards-for-woocommerce' ),
		'custom_attribute' => array(
			array(
				'id'   => 'right_bottom',
				'name' => __( 'Right Bottom', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'id'   => 'left_bottom',
				'name' => __( 'Left Bottom', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'id'   => 'top_left',
				'name' => __( 'Top Left', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'id'   => 'top_right',
				'name' => __( 'Top Right', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'id'   => 'shortcode',
				'name' => __( 'shortcode', 'points-and-rewards-for-woocommerce' ),
			),
		),
	),
	array(
		'title'    => __( 'Select pages', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'search&select',
		'multiple' => 'multiple',
		'id'       => 'wps_wpr_notification_button_page',
		'class'    => 'wc-enhanced-select wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Select the page where you want to display the button, leave blank if you want to display on all the pages.', 'points-and-rewards-for-woocommerce' ),
		'options'  => $settings_obj->wps_wpr_get_dummy_pages(),
	),
	array(
		'id'       => 'wps_wpr_notification_color',
		'type'     => 'color',
		'title'    => __( 'Select Color Notification Bar', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'You can also choose the color for your Notification Bar.', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text wps_wpr_pro_plugin_settings',
		'desc'     => __( 'Enable Point Sharing', 'points-and-rewards-for-woocommerce' ),
		'default'  => '#ff0000',
	),
	array(
		'title'    => __( 'Notification button text', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_notification_button_text',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Set the notification button text', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Notify Me', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
);

$wps_wpr_save_notification_addon = apply_filters( 'wps_wpr_save_notification_addon', $wps_wpr_save_notification_addon );
$general_settings                = get_option( 'wps_wpr_notification_addon_settings', array() );
$general_settings                = ! empty( $general_settings ) && is_array( $general_settings ) ? $general_settings : array();
?>
<div class="wps_table">
	<div class="wps_wpr_general_wrapper wps_wpr_pro_plugin_settings">
			<?php
			foreach ( $wps_wpr_save_notification_addon as $key => $value ) {
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
								if ( 'text' == $value['type'] ) {
									$settings_obj->wps_rwpr_generate_dummy_text_html( $value, $general_settings );
								}
								if ( 'textarea' == $value['type'] ) {
									$settings_obj->wps_rwpr_generate_dummy_textarea_html( $value, $general_settings );
								}
								if ( 'color' == $value['type'] ) {
									$settings_obj->wps_rwpr_generate_dummy_color_box( $value, $general_settings );
								}
								if ( 'singleSelectDropDownWithKeyvalue' == $value['type'] ) {
									$settings_obj->wps_wpr_generate_dummy_single_select_drop_down_with_key_value_pair( $value, $general_settings );
								}
								if ( 'search&select' == $value['type'] ) {
									$settings_obj->wps_wpr_generate_dummy_search_select_html( $value, $general_settings );
								}
								if ( 'select' == $value['type'] ) {
									$settings_obj->wps_wpr__select_dummy_html( $value, $general_settings );
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
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes wps_wpr_disabled_pro_plugin" name="wps_wpr_save_notification_addon">
</p>
