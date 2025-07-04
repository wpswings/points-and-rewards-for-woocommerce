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

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj             = new Points_Rewards_For_WooCommerce_Settings();
$wps_wpr_general_settings = array(
	array(
		'title' => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Enable WooCommerce Points and Rewards', 'points-and-rewards-for-woocommerce' ),
		'id'       => 'wps_wpr_general_setting_enable',
		'desc_tip' => __( 'Toggle This to Enable The Plugin', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Signup', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Signup Points', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_general_signup',
		'heading'  => __( 'Sign Up', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc_tip' => __( 'Toggle This to Enable Signups Points.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
		'desc'     => __( 'Enable Signup Points for Rewards', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'             => __( 'Enter Signup Points', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'number',
		'default'           => 1,
		'id'                => 'wps_wpr_general_signup_value',
		'custom_attributes' => array( 'min' => '"1"' ),
		'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
		'desc_tip'          => __( 'Points That The New Customers Will Get After Signup.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Referral', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Referral Points', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'default'  => 0,
		'id'       => 'wps_wpr_general_refer_enable',
		'heading'  => __( 'Sign Up', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc_tip' => __( 'Toggle This to Enable Referral Points When The Customer Invites Another Customer', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Enable Referral Points for Rewards.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'             => __( 'Enter Referral Points', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'number',
		'default'           => 1,
		'id'                => 'wps_wpr_general_refer_value',
		'custom_attributes' => array( 'min' => '1' ),
		'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
		'desc_tip'          => __( 'The points which the customer will get when they successfully invite given number of customers.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Social Sharing', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Enable Social Links', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'default'  => 0,
		'id'       => 'wps_wpr_general_social_media_enable',
		'class'    => 'input-text',
		'desc_tip' => __( 'Enable Social Media Sharing.', 'points-and-rewards-for-woocommerce' ),
		'desc'     => __( 'Enable Social Media Sharing.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'             => __( 'Select Social Links', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'multiple_checkbox',
		'id'                => 'wps_wpr_facebook',
		'desc_tip'          => __( 'Check these boxes to share the referral link', 'points-and-rewards-for-woocommerce' ),
		'multiple_checkbox' => array(
			array(
				'type'  => 'checkbox',
				'id'    => 'wps_wpr_facebook',
				'class' => 'input-text',
				'desc'  => __( 'Facebook', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'wps_wpr_twitter',
				'class' => 'input-text',
				'desc'  => __( 'Twitter', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'wps_wpr_email',
				'class' => 'input-text',
				'desc'  => __( 'Email', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'wps_wpr_whatsapp',
				'class' => 'input-text',
				'desc'  => __( 'Whatsapp', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'type'  => 'checkbox',
				'id'    => 'wps_wpr_pinterest',
				'class' => 'input-text',
				'desc'  => __( 'Pinterest', 'points-and-rewards-for-woocommerce' ),
			),
		),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Text Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Enter Text', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_general_text_points',
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'The entered text will get displayed on the points page.', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'The entered text will append before the Total Number of Point', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'My Points', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'             => __( 'Enter Ways to Gain Points', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'custom_text_area',
		'custom_attributes' => array(
			'cols' => '"35"',
			'rows' => '"5"',
		),
		'id'                => 'wps_wpr_general_ways_to_gain_points',
		'class'             => 'input-text',
		'desc_tip'          => __( 'The entered text will append before the Total Number of Point', 'points-and-rewards-for-woocommerce' ),
		'desc2'             => '[Refer Points]' . __( ' for Referral Points', 'points-and-rewards-for-woocommerce' ) . '[Per Currency Spent Points]' . __( ' for Per currency spent points and', 'points-and-rewards-for-woocommerce' ) . '[Per Currency Spent Price]' . __( ' for per currency spent price', 'points-and-rewards-for-woocommerce' ),
		'desc'              => __( 'Use these shortcodes to provide ways to gain points on the front end', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Points Tab Text', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_points_tab_text',
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'Replace the Points tab Text With Your Text', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'The Entered Text Will Show on the Points Tab in the My Account Section', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Points', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Assigned Product Points Text', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_assign_pro_text',
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'Replace the Product Points Text With Your Text', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Enter the message you want to display for those products who have been assigned with some of the Points', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Product Points', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Redemption Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Redemption Over Cart Sub-total', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_custom_points_on_cart',
		'desc_tip' => __( 'Toggle this box if you want to let your customers redeem their earned points for the cart subtotal.', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc'     => __( 'Allow customers to apply points during Cart.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'       => __( 'Conversion rate for Cart Sub-total Redemption', 'points-and-rewards-for-woocommerce' ),
		'type'        => 'number_text',
		'number_text' => array(
			array(
				'type'             => 'number',
				'id'                => 'wps_wpr_cart_points_rate',
				'class'             => 'input-text wc_input_price wps_wpr_new_woo_ver_style_text',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc_tip'          => __(
					'Entered point will assign to that user by which another user referred from referral link and purchase some products.',
					'points-and-rewards-for-woocommerce'
				),
				'desc'              => __( ' Points = ', 'points-and-rewards-for-woocommerce' ),
				'curr'              => '',
			),
			array(
				'type'              => 'text',
				'id'                => 'wps_wpr_cart_price_rate',
				'class'             => 'input-text wps_wpr_new_woo_ver_style_text wc_input_price',
				'custom_attributes' => array( 'min' => '"1"' ),
				'desc_tip'          => __(
					'Entered point will assign to that user by which another user referred from referral link and purchase some products.',
					'points-and-rewards-for-woocommerce'
				),
				'default'           => '1',
				'curr'              => get_woocommerce_currency_symbol(),
			),
		),
	),
	array(
		'title'    => __( 'Apply Points on Checkout', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_apply_points_checkout',
		'desc_tip' => __( 'Toggle This to Allow Customers to Apply Points at Checkout', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc'     => __( 'Allow Customers to Apply Points During Checkout', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Show Redeem Notice on Cart Page', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_show_redeem_notice',
		'desc_tip' => __( 'Toggle This To Show redeem notice on Cart Page', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc'     => __( 'Please enable this setting to show redemption message on Cart Page', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Enter points redemption messages', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_points_redemption_messages',
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'Enter a message for your customers about the points redemption rate. Use the [POINTS] shortcode for points and the [CURRENCY] shortcode to display the currency value.', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Entered message will appears on Cart Page.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'Here is the Discount Rule for Applying your Points to Cart Total 1 Points = $1.00', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
	array(
		'title' => __( 'Order Rewards Points Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'Order Rewards Settings', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_enable_order_rewards_settings',
		'desc_tip' => __( 'Toggle This To Reward Users on Their Number of Orders', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc'     => __( 'Please enable this setting to awards user on the basis of his number of orders.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'             => __( 'Enter Number Of Orders', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'number',
		'default'           => 1,
		'id'                => 'wps_wpr_number_of_reward_order',
		'custom_attributes' => array( 'min' => '1' ),
		'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
		'desc'              => __( 'Enter the number of orders which users place to get rewards', 'points-and-rewards-for-woocommerce' ),
		'desc_tip'          => __( 'Set the number of order which placed by user to get rewards points', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'             => __( 'Enter Order Rewards Points', 'points-and-rewards-for-woocommerce' ),
		'type'              => 'number',
		'default'           => 1,
		'id'                => 'wps_wpr_number_of_rewards_points',
		'custom_attributes' => array( 'min' => '1' ),
		'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
		'desc'              => __( 'Enter order rewards points which user will get when reach the limit of order.', 'points-and-rewards-for-woocommerce' ),
		'desc_tip'          => __( 'Set the number of points that user will get when reach the maximum number of order.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Select Points Type', 'points-and-rewards-for-woocommerce' ),
		'id'       => 'wps_wpr_order_rewards_points_type',
		'class'    => 'wps_wgm_new_woo_ver_style_select',
		'type'     => 'singleSelectDropDownWithKeyvalue',
		'desc_tip' => __( 'Select the type to rewards points', 'points-and-rewards-for-woocommerce' ),
		'custom_attribute' => array(
			array(
				'id' => 'fixed',
				'name' => __( 'Fixed', 'points-and-rewards-for-woocommerce' ),
			),
			array(
				'id' => 'percent',
				'name' => __( 'Percentage', 'points-and-rewards-for-woocommerce' ),
			),
		),
	),
	array(
		'title'    => __( 'Show a message on the cart page', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'id'       => 'wps_wpr_enable_to_show_order_reward_message',
		'desc_tip' => __( 'Toggle This to Show a Message for Users How Much Points They Will Get When They Reach the Order Limit.', 'points-and-rewards-for-woocommerce' ),
		'class'    => 'input-text',
		'desc'     => __( 'Please enable this setting to show message on cart page.', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'title'    => __( 'Enter Order rewards Message', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_number_order_rewards_messages',
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text',
		'desc'     => __( 'Use these shortcodes to provide an appropriate message for your customers on his no. of [ORDER] and get rewards points [POINTS]', 'points-and-rewards-for-woocommerce' ),
		'desc_tip' => __( 'Entered message will appears on Cart Page.', 'points-and-rewards-for-woocommerce' ),
		'default'  => __( 'You will get 10 points when you reach order limit', 'points-and-rewards-for-woocommerce' ),
	),
	array(
		'type' => 'sectionend',
	),
);

$wps_wpr_general_settings = apply_filters( 'wps_wpr_general_settings', $wps_wpr_general_settings );
$current_tab              = 'wps_wpr_general_setting';
if ( isset( $_POST['wps_wpr_save_general'] ) && isset( $_POST['wps-wpr-nonce'] ) ) {

	$wps_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['wps-wpr-nonce'] ) );
	if ( wp_verify_nonce( $wps_wpr_nonce, 'wps-wpr-nonce' ) ) {
		if ( 'wps_wpr_general_setting' == $current_tab ) {

			$postdata = $_POST;
			$postdata = $settings_obj->check_is_settings_is_not_empty( $wps_wpr_general_settings, $postdata );
			/* End of the save Settings and check is not empty*/
			$general_settings_array = array();

			foreach ( $postdata as $key => $value ) {
				$general_settings_array[ $key ] = $value;
			}
			if ( is_array( $general_settings_array ) && ! empty( $general_settings_array ) ) {
				$general_settings_array = apply_filters( 'wps_wpr_general_settings_save_option', $general_settings_array );
				update_option( 'wps_wpr_settings_gallery', $general_settings_array );
			}
			$settings_obj->wps_wpr_settings_saved();
			do_action( 'wps_wpr_general_settings_save_option', $general_settings_array );
		}
	}
}

$general_settings = get_option( 'wps_wpr_settings_gallery', true );
if ( ! is_array( $general_settings ) ) {

	$general_settings = array();
}
do_action( 'wps_wpr_add_notice' );
?>
<div class="wps_wpr_table">
	<div class="wps_wpr_general_wrapper">
			<?php
			foreach ( $wps_wpr_general_settings as $key => $value ) {
				if ( 'title' == $value['type'] ) {
					?>
				<div class="wps_wpr_general_row_wrap">
					<?php $settings_obj->wps_rwpr_generate_heading( $value ); ?>
				<?php } ?>
				<?php if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) { ?>
				<div class="wps_wpr_general_row">
					<?php $settings_obj->wps_rwpr_generate_label( $value ); ?>
					<div class="wps_wpr_general_content">
						<?php
						$settings_obj->wps_rwpr_generate_tool_tip( $value );
						if ( 'checkbox' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_checkbox_html( $value, $general_settings );
						}
						if ( 'number' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_number_html( $value, $general_settings );
						}
						if ( 'multiple_checkbox' == $value['type'] ) {
							foreach ( $value['multiple_checkbox'] as $k => $val ) {
								$settings_obj->wps_rwpr_generate_checkbox_html( $val, $general_settings );
							}
						}
						if ( 'text' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_text_html( $value, $general_settings );
						}
						if ( 'textarea' == $value['type'] ) {
							$settings_obj->wps_rwpr_generate_textarea_html( $value, $general_settings );
						}
						if ( 'number_text' == $value['type'] ) {
							foreach ( $value['number_text'] as $k => $val ) {
								if ( 'text' == $val['type'] ) {

									echo isset( $val['curr'] ) ? esc_html( $val['curr'] ) : '';
									$settings_obj->wps_rwpr_generate_text_html( $val, $general_settings );
									echo '<br>';

								}
								if ( 'number' == $val['type'] ) {

									$settings_obj->wps_rwpr_generate_number_html( $val, $general_settings );
								}
								if ( 'singleSelectDropDownWithKeyvalue' == $val['type'] ) {

									$settings_obj->wps_wpr_org_generate_single_select_drop_down_with_key_value_pair( $val, $general_settings );
								}
							}
						}
						if ( 'custom_text_area' == $value['type'] ) {
							echo wp_kses_post( ' ' . $value['desc'] );
							$settings_obj->wps_wpr_custom_editor( $value, $general_settings );
							echo wp_kses_post( $value['desc2'] );
						}
						if ( 'radio_button' == $value['type'] ) {
							$settings_obj->wps_wps_generate_radio_html( $value, $general_settings );
						}
						if ( ! wps_wpr_is_par_pro_plugin_active() && 'singleSelectDropDownWithKeyvalue' == $value['type'] ) {
							$settings_obj->wps_wpr_org_generate_single_select_drop_down_with_key_value_pair( $value, $general_settings );
						}
						do_action( 'wps_wpr_additional_general_settings', $value, $general_settings );
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
	<input type="hidden" name="wps-wpr-nonce" value="<?php echo esc_html( wp_create_nonce( 'wps-wpr-nonce' ) ); ?>">
	<input type="submit" value='<?php esc_html_e( 'Save changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_general">
</p>
<?php

