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
 * General Settings Template
 */
/*Array of the Settings*/
$mwb_wpr_general_settings = array(
	array(
		'title' => __( 'Enable', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable', MWB_RWPR_Domain ),
		'type'  => 'checkbox',
		'desc'  => __( 'Enable WooCommerce Points and Rewards', MWB_RWPR_Domain ),
		'id'    => 'mwb_wpr_general_setting_enable',
		'desc_tip' => __( 'Check this box to enable the plugin.', MWB_RWPR_Domain ),
		'default'   => 0,
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Signup', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable Signup Points', MWB_RWPR_Domain ),
		'type'  => 'checkbox',
		'id'    => 'mwb_wpr_general_signup',
		'heading' => __( 'Sign Up', MWB_RWPR_Domain ),
		'class'   => 'input-text',
		'desc_tip' => __( 'Check this box to enable the Signup Points.', MWB_RWPR_Domain ),
		'default'   => 0,
		'desc'    => __( 'Enable Signup Points for Rewards', MWB_RWPR_Domain ),
	),
	array(
		'title' => __( 'Enter Signup Points', MWB_RWPR_Domain ),
		'type'  => 'number',
		'default'   => 1,
		'id'    => 'mwb_wpr_general_signup_value',
		'custom_attributes'   => array( 'min' => '"1"' ),
		'class'   => 'input-text mwb_wpr_new_woo_ver_style_text',
		'desc_tip' => __( 'The points which the new customer will get after signup.', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Referral', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable Referral Points', MWB_RWPR_Domain ),
		'type'  => 'checkbox',
		'default'   => 0,
		'id'    => 'mwb_wpr_general_refer_enable',
		'heading' => __( 'Sign Up', MWB_RWPR_Domain ),
		'class'   => 'input-text',
		'desc_tip' => __( 'Check this box to enable the Referral Points when customer invites another customers.', MWB_RWPR_Domain ),
		'desc'    => __( 'Enable Referral Points for Rewards.', MWB_RWPR_Domain ),
	),
	array(
		'title' => __( 'Enter Referral Points', MWB_RWPR_Domain ),
		'type'  => 'number',
		'default'   => 1,
		'id'    => 'mwb_wpr_general_refer_value',
		'custom_attributes'   => array( 'min' => '1' ),
		'class'   => 'input-text mwb_wpr_new_woo_ver_style_text',
		'desc_tip' => __( 'The points which the customer will get when they successfully invites given number of customers.', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Social Sharing', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable Social Links', MWB_RWPR_Domain ),
		'type'  => 'checkbox',
		'default'   => 0,
		'id'    => 'mwb_wpr_general_social_media_enable',
		'class'   => 'input-text',
		'desc_tip' => __( 'Enable Social Media Sharing.', MWB_RWPR_Domain ),
		'desc'  => __( 'Enable Social Media Sharing.', MWB_RWPR_Domain ),
	),
	array(
		'title' => __( 'Select Social Links', MWB_RWPR_Domain ),
		'type'  => 'multiple_checkbox',
		'id'    => 'mwb_wpr_facebook',
		'desc_tip' => __( 'Check these boxes to share referral link', MWB_RWPR_Domain ),
		'multiple_checkbox' => array(
			array(
				'type'  => 'checkbox',
				'id'    => 'mwb_wpr_facebook',
				'class'   => 'input-text',
				'desc'  => __( 'Facebook', MWB_RWPR_Domain ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'mwb_wpr_twitter',
				'class'   => 'input-text',
				'desc'  => __( 'Twitter', MWB_RWPR_Domain ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'mwb_wpr_email',
				'class'   => 'input-text',
				'desc'  => __( 'Email', MWB_RWPR_Domain ),
			),
		),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Text Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enter Text', MWB_RWPR_Domain ),
		'type'  => 'text',
		'id'    => 'mwb_wpr_general_text_points',
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Entered text will get displayed on points page.', MWB_RWPR_Domain ),
		'desc_tip' => __( 'Entered text will append before the Total Number of Point', MWB_RWPR_Domain ),
		'default' => __( 'My Points', MWB_RWPR_Domain ),
	),
	array(
		'title' => __( 'Enter Ways to Gain Points', MWB_RWPR_Domain ),
		'type'  => 'textarea',
		'custom_attributes' => array(
			'cols' => '"35"',
			'rows' => '"5"',
		),
		'id'    => 'mwb_wpr_general_ways_to_gain_points',
		'class' => 'input-text',
		'desc_tip' => __( 'Entered text will append before the Total Number of Point', MWB_RWPR_Domain ),
		'default' => __( 'My Points', MWB_RWPR_Domain ),
		'desc2' => '[Refer Points]' . __( ' for Referral Points', MWB_RWPR_Domain ) . ' [Comment Points]' . __( ' for Comment Points ', MWB_RWPR_Domain ) . '[Per Currency Spent Points]' . __( ' for Per currency spent points and', MWB_RWPR_Domain ) . '[Per Currency Spent Price]' . __( ' for per currency spent price', MWB_RWPR_Domain ),
		'desc'  => __( 'Use these shortcodes for providing ways to gain points at front end.', MWB_RWPR_Domain ),
	),
	array(
		'title' => __( 'Points Tab Text', MWB_RWPR_Domain ),
		'type'  => 'text',
		'id'    => 'mwb_wpr_points_tab_text',
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Points Tab replaced with your text.', MWB_RWPR_Domain ),
		'desc_tip' => __( 'Entered text will be replaced the Points tab at Myaccount Page', MWB_RWPR_Domain ),
		'default' => __( 'Points', MWB_RWPR_Domain ),
	),
	array(
		'title' => __( 'Assigned Product Points Text', MWB_RWPR_Domain ),
		'type'  => 'text',
		'id'    => 'mwb_wpr_assign_pro_text',
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Product Point text can be replaced with entered text', MWB_RWPR_Domain ),
		'desc_tip' => __( 'Enter the message you want to display for those product who have assigned with some of the Points', MWB_RWPR_Domain ),
		'default' => __( 'Product Points', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Redemption Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Redemption Over Cart Sub-Total', MWB_RWPR_Domain ),
		'type'  => 'checkbox',
		'id'    => 'mwb_wpr_custom_points_on_cart',
		'desc_tip' => __( 'Check this box if you want to let your customers to redeem their earned points for the cart subtotal, there would be no relation with product purchase through point feature', MWB_RWPR_Domain ),
		'class' => 'input-text',
		'desc'  => __( 'No relation with Purchase Product Through Point Feature', MWB_RWPR_Domain ),
	),
	array(
		'title' => __( 'Conversion rate for Cart Sub-Total Redemption', MWB_RWPR_Domain ),
		'type'  => 'number_text',
		'number_text' => array(
			array(
				'type'  => 'number',
				'id'    => 'mwb_wpr_cart_points_rate',
				'class'   => 'input-text wc_input_price mwb_wpr_new_woo_ver_style_text',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc_tip' => __(
					'Entered point will assign to that user by which another user reffered from refrral link and purchase some products.',
					MWB_RWPR_Domain
				),
				'desc' => __( 'Points =', MWB_RWPR_Domain ),
			),
			array(
				'type'  => 'text',
				'id'    => 'mwb_wpr_cart_price_rate',
				'class'   => 'input-text mwb_wpr_new_woo_ver_style_text wc_input_price',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc_tip' => __(
					'Entered point will assign to that user by which another user reffered from refrral link and purchase some products.',
					MWB_RWPR_Domain
				),
				'default' => '1',
			),
		),
	),
	array(
		'title' => __( 'Enable apply points during checkout', MWB_RWPR_Domain ),
		'type'  => 'checkbox',
		'id'    => 'mwb_wpr_apply_points_checkout',
		'desc_tip' => __( 'Check this box if you want that customer can apply also apply points on checkout', MWB_RWPR_Domain ),
		'class' => 'input-text',
		'desc'  => __( 'Allow customers to apply points during checkout also', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
);
$mwb_wpr_general_settings = apply_filters( 'mwb_wpr_general_settings', $mwb_wpr_general_settings );
$current_tab = 'mwb_wpr_general_setting';
if ( isset( $_POST['mwb_wpr_save_general'] ) ) {
	if ( wp_verify_nonce( $_POST['mwb-wpr-nonce'], 'mwb-wpr-nonce' ) ) {
		?>
		<?php
		if ( $current_tab == 'mwb_wpr_general_setting' ) {

			/* Save Settings and check is not empty*/
			$postdata = $settings_obj->check_is_settings_is_not_empty( $mwb_wpr_general_settings, $_POST );
			/* End of the save Settings and check is not empty*/
			$general_settings_array = array();

			foreach ( $postdata as $key => $value ) {
				$value = stripcslashes( $value );
				$value = sanitize_text_field( $value );
				$general_settings_array[ $key ] = $value;
			}
			if ( is_array( $general_settings_array ) && ! empty( $general_settings_array ) ) {
				$general_settings_array = apply_filters( 'mwb_wpr_general_settings_save_option', $general_settings_array );
				update_option( 'mwb_wpr_settings_gallery', $general_settings_array );
			}
			$settings_obj->mwb_wpr_settings_saved();
			do_action( 'mwb_wpr_general_settings_save_option', $general_settings_array );
		}
	}
}
?>
	<?php $general_settings = get_option( 'mwb_wpr_settings_gallery', true ); ?>
	<?php
	if ( ! is_array( $general_settings ) ) :
		$general_settings = array();
endif;
	?>
	<div class="mwb_table">
		<div class="mwb_wpr_general_wrapper">
				<?php
				foreach ( $mwb_wpr_general_settings as $key => $value ) {
					if ( $value['type'] == 'title' ) {
						?>
					<div class="mwb_wpr_general_row_wrap">
						<?php $settings_obj->mwb_rwpr_generate_heading( $value ); ?>
					<?php } ?>
					<?php if ( $value['type'] != 'title' && $value['type'] != 'sectionend' ) { ?>
				<div class="mwb_wpr_general_row">
						<?php $settings_obj->mwb_rwpr_generate_label( $value ); ?>
					<div class="mwb_wpr_general_content">
						<?php
						$settings_obj->mwb_rwpr_generate_tool_tip( $value );
						if ( $value['type'] == 'checkbox' ) {
							$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $general_settings );
						}
						if ( $value['type'] == 'number' ) {
							$settings_obj->mwb_rwpr_generate_number_html( $value, $general_settings );
						}
						if ( $value['type'] == 'multiple_checkbox' ) {
							foreach ( $value['multiple_checkbox'] as $k => $val ) {
								$settings_obj->mwb_rwpr_generate_checkbox_html( $val, $general_settings );
							}
						}
						if ( $value['type'] == 'text' ) {
							$settings_obj->mwb_rwpr_generate_text_html( $value, $general_settings );
						}
						if ( $value['type'] == 'textarea' ) {
							$settings_obj->mwb_rwpr_generate_textarea_html( $value, $general_settings );
						}
						if ( $value['type'] == 'number_text' ) {
							foreach ( $value['number_text'] as $k => $val ) {
								if ( $val['type'] == 'text' ) {
									$settings_obj->mwb_rwpr_generate_text_html( $val, $general_settings );

								}
								if ( $val['type'] == 'number' ) {
									$settings_obj->mwb_rwpr_generate_number_html( $val, $general_settings );
									echo get_woocommerce_currency_symbol();
								}
							}
						}
						?>
					</div>
				</div>
				<?php } ?>
					<?php if ( $value['type'] == 'sectionend' ) : ?>
				 </div>	
				<?php endif; ?>
			<?php } ?> 		
		</div>
	</div>
	<div class="clear"></div>
	<p class="submit">
		<input type="submit" value='<?php _e( 'Save changes', MWB_RWPR_Domain ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_general">
	</p>
