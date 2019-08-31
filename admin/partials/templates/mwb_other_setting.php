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
		'title' => __( 'Shortcodes', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'id' => 'mwb_wpr_other_shortcodes',
		'title' => __( 'Shortcodes', MWB_RWPR_Domain ),
		'type'  => 'shortcode',
		'desc'  => array(
			'desc1' => __( 'Use shortcode [MYCURRENTUSERLEVEL] for displaying current Membership Level of Users', MWB_RWPR_Domain ),
			'desc2' => __( 'Use shortcode [MYCURRENTPOINT] for displaying current Points of Users', MWB_RWPR_Domain ),
			'desc3' => __( 'Use shortcode [SIGNUPNOTIFICATION] for displaying notification anywhere on site', MWB_RWPR_Domain ),
		),
	),
	array(
		'id'    => 'mwb_wpr_other_shortcode_text',
		'title' => __( 'Enter the text which you want to display with shortcode [MYCURRENTPOINT]', MWB_RWPR_Domain ),
		'type'  => 'text',
		'desc_tip'  => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', MWB_RWPR_Domain ),
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', MWB_RWPR_Domain ),
		'default'   => __( 'Your Current Point', MWB_RWPR_Domain ),
	),
	array(
		'id'    => 'mwb_wpr_shortcode_text_membership',
		'type'  => 'text',
		'title' => __( 'Enter the text which you want to display with shortcode [MYCURRENTUSERLEVEL]', MWB_RWPR_Domain ),
		'desc_tip'  => __( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', MWB_RWPR_Domain ),
		'class' => 'text_points mwb_wpr_new_woo_ver_style_text',
		'desc'  => __( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', MWB_RWPR_Domain ),
		'default'   => __( 'Your Current Level', MWB_RWPR_Domain ),
	),
	array(
		'type'  => 'sectionend',
	),
	array(
		'title' => __( 'Notification Settings', MWB_RWPR_Domain ),
		'type'  => 'title',
	),
	array(
		'id'    => 'mwb_wpr_notification_color',
		'type'  => 'color',
		'title' => __( 'Select Color Notification Bar', MWB_RWPR_Domain ),
		'desc_tip'  => __( 'You can also choose the color for your Notification Bar.', MWB_RWPR_Domain ),
		'class' => 'input-text',
		'desc'  => __( 'Enable Point Sharing', MWB_RWPR_Domain ),
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
							$settings_obj->mwb_rwpr_generate_checkbox_html( $value, $other_settings );
						}
						if ( $value['type'] == 'shortcode' ) {
							$settings_obj->mwb_wpr_generate_shortcode( $value );
						}
						if ( $value['type'] == 'number' ) {
							$settings_obj->mwb_rwpr_generate_number_html( $value, $other_settings );
						}
						if ( $value['type'] == 'color' ) {
							$settings_obj->mwb_rwpr_generate_color_box( $value, $other_settings );
						}
						if ( $value['type'] == 'multiple_checkbox' ) {
							foreach ( $value['multiple_checkbox'] as $k => $val ) {
								$settings_obj->mwb_rwpr_generate_checkbox_html( $val, $other_settings );
							}
						}
						if ( $value['type'] == 'text' ) {
							$settings_obj->mwb_rwpr_generate_text_html( $value, $other_settings );
						}
						if ( $value['type'] == 'textarea' ) {
							$settings_obj->mwb_rwpr_generate_textarea_html( $value, $other_settings );
						}
						if ( $value['type'] == 'number_text' ) {
							foreach ( $value['number_text'] as $k => $val ) {
								if ( $val['type'] == 'text' ) {
									$settings_obj->mwb_rwpr_generate_text_html( $val, $other_settings );

								}
								if ( $val['type'] == 'number' ) {
									$settings_obj->mwb_rwpr_generate_number_html( $val, $other_settings );
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
<!-- <div class="mwb_table">
	<table class="form-table mwb_wpr_general_setting mwp_wpr_settings">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_other_shortcodes"><?php _e( 'Shortcodes', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
						<p class="description"><?php _e( 'Use shortcode [MYCURRENTUSERLEVEL] for displaying current Membership Level of Users', MWB_RWPR_Domain ); ?></p>
						<p class="description"><?php _e( 'Use shortcode [MYCURRENTPOINT] for displaying current Points of Users', MWB_RWPR_Domain ); ?></p>
						<p class="description"><?php _e( 'Use shortcode [SIGNUPNOTIFICATION] for displaying notification anywhere on site', MWB_RWPR_Domain ); ?></p>	
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_other_shortcode_text"><?php _e( 'Enter the text which you want to display with shortcode [MYCURRENTPOINT]', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
						<label for="mwb_wpr_shortcode_text_point">
						<input type="text" name="mwb_wpr_shortcode_text_point" value="<?php echo $mwb_wpr_shortcode_text_point; ?>" id="mwb_wpr_shortcode_text_point" class="text_points mwb_wpr_new_woo_ver_style_text"></label>
						<p class="description"><?php _e( 'Entered text will get displayed along with [MYCURRENTPOINT] shortcode', MWB_RWPR_Domain ); ?></p>	
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_general_signup"><?php _e( 'Enter the text which you want to display with shortcode [MYCURRENTUSERLEVEL]', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
						<label for="mwb_wpr_shortcode_text_membership">
						<input type="text" name="mwb_wpr_shortcode_text_membership" value="<?php echo $mwb_wpr_shortcode_text_membership; ?>" id="mwb_wpr_shortcode_text_membership" class="text_points mwb_wpr_new_woo_ver_style_text"></label>
						<p class="description"><?php _e( 'Entered text will get displayed along with [MYCURRENTUSERLEVEL] shortcode', MWB_RWPR_Domain ); ?></p>				
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_thnku_order_msg"><?php _e( 'Enter Thankyou Order Message When your customer gain some points', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears at thankyou page when any order item is having some of the points', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_thnku_order_msg" id="mwb_wpr_thnku_order_msg" class="input-text" ><?php echo $mwb_wpr_thnku_order_msg; ?></textarea>
					<p class="description"><?php _e( 'Use these shortcodes for providing an appropriate message for your customers ', MWB_RWPR_Domain ); ?>
														<?php
														echo '[POINTS]';
														_e( ' for product points ', MWB_RWPR_Domain );
														echo ' [TOTALPOINT]';
														_e( ' for their Total Points ', MWB_RWPR_Domain );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_thnku_order_msg_usin_points"><?php _e( 'Enter Thankyou Order Message When your customer spent some of the points', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Entered Message will appears at thankyou page when any item has been purchased through points', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<textarea cols="35" rows="5" name="mwb_wpr_thnku_order_msg_usin_points" id="mwb_wpr_thnku_order_msg_usin_points" class="input-text" ><?php echo $mwb_wpr_thnku_order_msg_usin_points; ?></textarea>
					<p class="description"><?php _e( 'Use these shortcodes for providing an appropriate message for your customers ', MWB_RWPR_Domain ); ?>
														<?php
														echo '[POINTS]';
														_e( ' for product points ', MWB_RWPR_Domain );
														echo ' [TOTALPOINT]';
														_e( ' for their Total Points ', MWB_RWPR_Domain );
														?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_disable_coupon_generation"><?php _e( 'Disable Points Conversion', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this box if you want to disable the coupon generation functionality for customers', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_disable_coupon_generation">
						<input type="checkbox" <?php checked( $mwb_wpr_disable_coupon_generation, 1 ); ?> name="mwb_wpr_disable_coupon_generation" id="mwb_wpr_disable_coupon_generation" class="input-text"> <?php _e( 'Disable Points Conversion Fields', MWB_RWPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_user_can_send_point"><?php _e( 'Point Sharing', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this box if you want to let your customers to send some of the points from his/her account to any other user', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_user_can_send_point">
						<input type="checkbox" <?php checked( $mwb_wpr_user_can_send_point, 1 ); ?> name="mwb_wpr_user_can_send_point" id="mwb_wpr_user_can_send_point" class="input-text"> <?php _e( 'Enable Point Sharing', MWB_RWPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_referral_link_permanent"><?php _e( 'Static Referral Link', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this box if you want to make your referral link permanent.', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_referral_link_permanent">
						<input type="checkbox" <?php checked( $mwb_wpr_referral_link_permanent, 1 ); ?> name="mwb_wpr_referral_link_permanent" id="mwb_wpr_referral_link_permanent" class="input-text"> <?php _e( 'Make Referral Link Permanent', MWB_RWPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_ref_link_expiry"><?php _e( 'Referral Link Expiry', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Set the number of days after that the system will not able to remember the reffered user anymore', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_ref_link_expiry">
						<input type="number"  name="mwb_wpr_ref_link_expiry" id="mwb_wpr_ref_link_expiry" value="<?php echo $mwb_wpr_ref_link_expiry; ?>" class="input-text mwb_wpr_new_woo_ver_style_text" required> <?php _e( 'Days', MWB_RWPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_custom_points_on_cart"><?php _e( 'Redemption Over Cart Sub-Total', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this box if you want to let your customers to redeem their earned points for the cart subtotal, there would be no relation with product purchase through point feature', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_custom_points_on_cart">
						<input type="checkbox" <?php checked( $mwb_wpr_custom_points_on_cart, 1 ); ?> name="mwb_wpr_custom_points_on_cart" id="mwb_wpr_custom_points_on_cart" class="input-text"> <?php _e( 'No relation with Purchase Product Through Point Feature', MWB_RWPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_cart_points_rate"><?php _e( 'Conversion rate for Cart Sub-Total Redemption', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Enter the redeem points for cart sub-total. (i.e., how many points will be equivalent to your currency)', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_cart_points_rate">
						 
						<input type="number" min="1" value="<?php echo $mwb_wpr_cart_points_rate; ?>" name="mwb_wpr_cart_points_rate" id="mwb_wpr_cart_points_rate" class="input-text wc_input_price mwb_wpr_new_woo_ver_style_text">
						<?php echo __( 'Points ', MWB_RWPR_Domain ); ?>  =
						<?php echo get_woocommerce_currency_symbol(); ?>
						<input type="text" value="<?php echo $mwb_wpr_cart_price_rate; ?>" name="mwb_wpr_cart_price_rate" id="mwb_wpr_cart_price_rate" class="input-text mwb_wpr_new_woo_ver_style_text wc_input_price ">
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_apply_points_checkout"><?php _e( 'Enable apply points during checkout', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this box if you want that customer can apply also apply points on checkout', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_apply_points_checkout">
						<input type="checkbox" <?php checked( $mwb_wpr_apply_points_checkout, 1 ); ?> name="mwb_wpr_apply_points_checkout" id="mwb_wpr_apply_points_checkout" class="input-text"> <?php _e( 'Allow customers to apply points during checkout also', MWB_RWPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_make_readonly"><?php _e( 'Make "Per Product Redemption" Readonly', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'Check this box if you want to make the redemption box readonly(where end user can enter the number of points they want to redeem)', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
					<label for="mwb_wpr_make_readonly">
						<input type="checkbox" <?php checked( $mwb_wpr_make_readonly, 1 ); ?> name="mwb_wpr_make_readonly" id="mwb_wpr_make_readonly" class="input-text"> <?php _e( 'Readonly for Enter Number of Points for Redemption', MWB_RWPR_Domain ); ?>
					</label>						
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="mwb_wpr_notification_color"><?php _e( 'Select Color Notification Bar', MWB_RWPR_Domain ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<?php
					$attribute_description = __( 'You can also choose the color for your Notification Bar.', MWB_RWPR_Domain );
					echo wc_help_tip( $attribute_description );
					?>
											
					<input type="color" id="mwb_wpr_notification_color" name="mwb_wpr_notification_color" value="<?php echo $mwb_wpr_notification_color; ?>">				
					
				</td>				
			</tr>
		</tbody>
	</table></div> -->
<p class="submit">
	<input type="submit" value='<?php _e( 'Save changes', MWB_RWPR_Domain ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_othersetting">
</p>
