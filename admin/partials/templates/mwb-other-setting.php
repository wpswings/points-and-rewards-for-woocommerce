<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once MWB_RWPR_DIR_PATH . '/admin/partials/settings/class-rewardeem-wocoommerce-points-rewards-settings.php';
$settings_obj = new Rewardeem_woocommerce_Points_Rewards_Admin_settings();
/*
/*
 * Other Settings Template
 */
/* This is setttings array for the other settings*/
$mwb_wpr_other_settings = array(
	array(
		'title' => __( 'Shortcodes', 'points-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'id' => 'mwb_wpr_other_shortcodes',
		'title' => __( 'Shortcodes', 'points-rewards-for-woocommerce' ),
		'type'  => 'shortcode',
		'desc'  => array(
			'desc1' => __( 'Use shortcode [MYCURRENTUSERLEVEL] for displaying current Membership Level of Users', 'points-rewards-for-woocommerce' ),
			'desc2' => __( 'Use shortcode [MYCURRENTPOINT] for displaying current Points of Users', 'points-rewards-for-woocommerce' ),
			'desc3' => __( 'Use shortcode [SIGNUPNOTIFICATION] for displaying notification anywhere on site', 'points-rewards-for-woocommerce' ),
		),
	),
	array(
		'id'    => 'mwb_wpr_other_shortcode_text',
		'title' => __( 'Enter the text which you want to display with shortcode [MYCURRENTPOINT]', 'points-rewards-for-woocommerce' ),
		'type'  => 'text',
		'desc_tip'  => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', 'points-rewards-for-woocommerce' ),
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', 'points-rewards-for-woocommerce' ),
		'default'   => __( 'Your Current Point', 'points-rewards-for-woocommerce' ),
	),
	array(
		'id'    => 'mwb_wpr_shortcode_text_membership',
		'type'  => 'text',
		'title' => __( 'Enter the text which you want to display with shortcode [MYCURRENTUSERLEVEL]', 'points-rewards-for-woocommerce' ),
		'desc_tip'  => __( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', 'points-rewards-for-woocommerce' ),
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', 'points-rewards-for-woocommerce' ),
		'default'   => __( 'Your Current Level', 'points-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Other Settings', 'points-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'id'    => 'mwb_wpr_notification_color',
		'type'  => 'color',
		'title' => __( 'Select Color Notification Bar', 'points-rewards-for-woocommerce' ),
		'desc_tip'  => __( 'You can also choose the color for your Notification Bar.', 'points-rewards-for-woocommerce' ),
		'class' => 'input-text',
		'desc'  => __( 'Enable Point Sharing', 'points-rewards-for-woocommerce' ),
		'default' => '#55b3a5',
	),
	array(
		'type'  => 'sectionend',
	),

);
$mwb_wpr_other_settings = apply_filters( 'mwb_wpr_others_settings', $mwb_wpr_other_settings );
/*Save Settings*/
$current_tab = 'mwb_wpr_othersetting_tab';

if ( isset( $_GET['tab'] ) ) {
	$current_tab = $_GET['tab'];
}
if ( isset( $_POST['mwb_wpr_save_othersetting'] ) ) {
	if ( wp_verify_nonce( $_POST['mwb-wpr-nonce'], 'mwb-wpr-nonce' ) ) {
		unset( $_POST['mwb_wpr_save_othersetting'] );

		$other_settings = array();
		/* Check is input is not empty if empty then assign them default value*/
		$postdata = $settings_obj->check_is_settings_is_not_empty( $mwb_wpr_other_settings, $_POST );

		foreach ( $postdata as $key => $value ) {
			$value = stripcslashes( $value );
			$value = sanitize_text_field( $value );
			$other_settings[ $key ] = $value;
		}
		/* Save settings data into the database*/
		if ( ! empty( $other_settings ) && is_array( $other_settings ) ) {
			update_option( 'mwb_wpr_other_settings', $other_settings );
		}
		/* Save settings Notification*/
		$settings_obj->mwb_wpr_settings_saved();
		do_action( 'mwb_wpr_save_other_settings', $postdata );
	}
}
/* Get Saved settings*/
$other_settings = get_option( 'mwb_wpr_other_settings', array() );
?>
<div class="mwb_table">
		<div class="mwb_wpr_general_wrapper">
				<?php
				foreach ( $mwb_wpr_other_settings as $key => $value ) {
					if ( 'title' == $value['type'] ) {
						?>
					<div class="mwb_wpr_general_row_wrap">
						<?php $settings_obj->mwb_rwpr_generate_heading( $value ); ?>
					<?php } ?>
					<?php if ( $value['type'] != 'title' && $value['type'] != 'sectionend' ) { //phpcs:ignore WordPress.PHP.YodaConditions.NotYoda ?>
				<div class="mwb_wpr_general_row">
						<?php $settings_obj->mwb_rwpr_generate_label( $value ); ?>
					<div class="mwb_wpr_general_content">
						<?php
						$settings_obj->mwb_rwpr_generate_tool_tip( $value );
						if ( 'checkbox' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $other_settings );
						}
						if ( 'shortcode' == $value['type'] ) {
							$settings_obj->mwb_wpr_generate_shortcode( $value );
						}
						if ( 'number' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_number_html( $value, $other_settings );
						}
						if ( $value['type'] == 'color' ) {
							$settings_obj->mwb_rwpr_generate_color_box( $value, $other_settings );
						}
						if ( 'multiple_checkbox' == $value['type'] ) {
							foreach ( $value['multiple_checkbox'] as $k => $val ) {
								$settings_obj->mwb_rwpr_generate_checkbox_html( $val, $other_settings );
							}
						}
						if ( 'text' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_text_html( $value, $other_settings );
						}
						if ( 'textarea' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_textarea_html( $value, $other_settings );
						}
						if ( 'number_text' == $value['type'] ) {
							foreach ( $value['number_text'] as $k => $val ) {
								if ( 'text' == $val['type'] ) {
									$settings_obj->mwb_rwpr_generate_text_html( $val, $other_settings );

								}
								if ( 'number' == $val['type'] ) {
									$settings_obj->mwb_rwpr_generate_number_html( $val, $other_settings );
									echo get_woocommerce_currency_symbol();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
<p class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_othersetting">
</p>
