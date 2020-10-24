<?php
/**
 * This is setttings array for the General settings
 *
 * General Settings Template
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
$mwb_wpr_general_settings = array(
	array(
		'title' => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'desc'  => __( 'Enable WooCommerce Points and Rewards', 'points-and-rewards-for-woocommerce' ),
		'id'    => 'mwb_wpr_general_setting_enable',
		'desc_tip' => __( 'Check this box to enable the plugin.', 'points-and-rewards-for-woocommerce' ),
		'default'   => 0,
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Signup', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable Signup Points', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'id'    => 'mwb_wpr_general_signup',
		'heading' => __( 'Sign Up', 'points-and-rewards-for-woocommerce' ),
		'class'   => 'input-text',
		'desc_tip' => __( 'Check this box to enable the Signup Points.', 'points-and-rewards-for-woocommerce' ),
		'default'   => 0,
		'desc'    => __( 'Enable Signup Points for Rewards', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Enter Signup Points', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'number',
		'default'   => 1,
		'id'    => 'mwb_wpr_general_signup_value',
		'custom_attributes'   => array( 'min' => '"1"' ),
		'class'   => 'input-text mwb_wpr_new_woo_ver_style_text',
		'desc_tip' => __( 'The points which the new customer will get after signup.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Referral', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable Referral Points', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'default'   => 0,
		'id'    => 'mwb_wpr_general_refer_enable',
		'heading' => __( 'Sign Up', 'points-and-rewards-for-woocommerce' ),
		'class'   => 'input-text',
		'desc_tip' => __( 'Check this box to enable the Referral Points when customer invites another customers.', 'points-and-rewards-for-woocommerce' ),
		'desc'    => __( 'Enable Referral Points for Rewards.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Enter Referral Points', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'number',
		'default'   => 1,
		'id'    => 'mwb_wpr_general_refer_value',
		'custom_attributes'   => array( 'min' => '1' ),
		'class'   => 'input-text mwb_wpr_new_woo_ver_style_text',
		'desc_tip' => __( 'The points which the customer will get when they successfully invite given number of customers.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Social Sharing', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enable Social Links', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'default'   => 0,
		'id'    => 'mwb_wpr_general_social_media_enable',
		'class'   => 'input-text',
		'desc_tip' => __( 'Enable Social Media Sharing.', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Enable Social Media Sharing.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Select Social Links', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'multiple_checkbox',
		'id'    => 'mwb_wpr_facebook',
		'desc_tip' => __( 'Check these boxes to share referral link', 'points-and-rewards-for-woocommerce' ),
		'multiple_checkbox' => array(
			array(
				'type'  => 'checkbox',
				'id'    => 'mwb_wpr_facebook',
				'class'   => 'input-text',
				'desc'  => __( 'Facebook', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'mwb_wpr_twitter',
				'class'   => 'input-text',
				'desc'  => __( 'Twitter', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'mwb_wpr_email',
				'class'   => 'input-text',
				'desc'  => __( 'Email', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'mwb_wpr_whatsapp',
				'class'   => 'input-text',
				'desc'  => __( 'Whatsapp', 'points-and-rewards-for-woocommerce' ),
			),
		),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Text Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Enter Text', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'text',
		'id'    => 'mwb_wpr_general_text_points',
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Entered text will get displayed on points page.', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Entered text will append before the Total Number of Point', 'points-and-rewards-for-woocommerce' ),
		'default' => __( 'My Points', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Enter Ways to Gain Points', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'textarea',
		'custom_attributes' => array(
			'cols' => '"35"',
			'rows' => '"5"',
		),
		'id'    => 'mwb_wpr_general_ways_to_gain_points',
		'class' => 'input-text',
		'desc_tip' => __( 'Entered text will append before the Total Number of Point', 'points-and-rewards-for-woocommerce' ),
		'desc2' => '[Refer Points]' . __( ' for Referral Points', 'points-and-rewards-for-woocommerce' ) . '[Per Currency Spent Points]' . __( ' for Per currency spent points and', 'points-and-rewards-for-woocommerce' ) . '[Per Currency Spent Price]' . __( ' for per currency spent price', 'points-and-rewards-for-woocommerce' ),
		'desc'  => __( 'Use these shortcodes for providing ways to gain points at the front end.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Points Tab Text', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'text',
		'id'    => 'mwb_wpr_points_tab_text',
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Points Tab replaced with your text.', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Entered text will be replaced the Points tab at Myaccount Page', 'points-and-rewards-for-woocommerce' ),
		'default' => __( 'Points', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Assigned Product Points Text', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'text',
		'id'    => 'mwb_wpr_assign_pro_text',
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Product Point text can be replaced with the entered text', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Enter the message you want to display for those products who have assigned with some of the Points', 'points-and-rewards-for-woocommerce' ),
		'default' => __( 'Product Points', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Redemption Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title' => __( 'Redemption Over Cart Sub-Total', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'id'    => 'mwb_wpr_custom_points_on_cart',
		'desc_tip' => __( 'Check this box if you want to let your customers redeem their earned points for the cart subtotal.', 'points-and-rewards-for-woocommerce' ),
		'class' => 'input-text',
		'desc'  => __( 'Allow customers to apply points during Cart.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title' => __( 'Conversion rate for Cart Sub-Total Redemption', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'number_text',
		'number_text' => array(
			array(
				'type'  => 'number',
				'id'    => 'mwb_wpr_cart_points_rate',
				'class'   => 'input-text wc_input_price mwb_wpr_new_woo_ver_style_text',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc_tip' => __(
					'Entered point will assign to that user by which another user referred from referral link and purchase some products.',
					'points-and-rewards-for-woocommerce'
				),
				'desc' => __( 'Points =', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'text',
				'id'    => 'mwb_wpr_cart_price_rate',
				'class'   => 'input-text mwb_wpr_new_woo_ver_style_text wc_input_price',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc_tip' => __(
					'Entered point will assign to that user by which another user referred from referral link and purchase some products.',
					'points-and-rewards-for-woocommerce'
				),
				'default' => '1',
			),
		),
	),
	array(
		'title' => __( 'Enable apply points during checkout', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'checkbox',
		'id'    => 'mwb_wpr_apply_points_checkout',
		'desc_tip' => __( 'Check this box if you want that customer can apply also apply points on checkout', 'points-and-rewards-for-woocommerce' ),
		'class' => 'input-text',
		'desc'  => __( 'Allow customers to apply points during checkout also.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type'  => 'sectionend',
	),
);
$mwb_wpr_general_settings = apply_filters( 'mwb_wpr_general_settings', $mwb_wpr_general_settings );
$current_tab = 'mwb_wpr_general_setting';
if ( isset( $_POST['mwb_wpr_save_general'] ) && isset( $_POST['mwb-wpr-nonce'] ) ) {
	$mwb_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['mwb-wpr-nonce'] ) );
	if ( wp_verify_nonce( $mwb_wpr_nonce, 'mwb-wpr-nonce' ) ) {
		?>
		<?php
		if ( 'mwb_wpr_general_setting' == $current_tab ) {

			/* Save Settings and check is not empty*/
			$postdata = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
			$postdata = $settings_obj->check_is_settings_is_not_empty( $mwb_wpr_general_settings, $postdata );
			/* End of the save Settings and check is not empty*/
			$general_settings_array = array();

			foreach ( $postdata as $key => $value ) {
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
	<div class="mwb_wpr_table">
		<div class="mwb_wpr_general_wrapper">
				<?php
				foreach ( $mwb_wpr_general_settings as $key => $value ) {
					if ( 'title' == $value['type'] ) {
						?>
					<div class="mwb_wpr_general_row_wrap">
						<?php $settings_obj->mwb_rwpr_generate_heading( $value ); ?>
					<?php } ?>
					<?php if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) { ?>
				<div class="mwb_wpr_general_row">
						<?php $settings_obj->mwb_rwpr_generate_label( $value ); ?>
					<div class="mwb_wpr_general_content">
						<?php
						$settings_obj->mwb_rwpr_generate_tool_tip( $value );
						if ( 'checkbox' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $general_settings );
						}
						if ( 'number' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_number_html( $value, $general_settings );
						}
						if ( 'multiple_checkbox' == $value['type'] ) {
							foreach ( $value['multiple_checkbox'] as $k => $val ) {
								$settings_obj->mwb_rwpr_generate_checkbox_html( $val, $general_settings );
							}
						}
						if ( 'text' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_text_html( $value, $general_settings );
						}
						if ( 'textarea' == $value['type'] ) {
							$settings_obj->mwb_rwpr_generate_textarea_html( $value, $general_settings );
						}
						if ( 'number_text' == $value['type'] ) {
							foreach ( $value['number_text'] as $k => $val ) {
								if ( 'text' == $val['type'] ) {
									$settings_obj->mwb_rwpr_generate_text_html( $val, $general_settings );

								}
								if ( 'number' == $val['type'] ) {
									$settings_obj->mwb_rwpr_generate_number_html( $val, $general_settings );
									echo esc_html( get_woocommerce_currency_symbol() );
								}
							}
						}
						do_action( 'mwb_wpr_additional_general_settings', $value, $general_settings );
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
		<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_general">
	</p>
	<?php

