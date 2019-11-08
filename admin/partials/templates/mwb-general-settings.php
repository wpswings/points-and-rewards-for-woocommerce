<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * General Settings Template
 */
$current_tab = 'mwb_wpr_general_setting';
if ( isset( $_POST['mwb_wpr_save_general'] ) ) {
	?>
	<?php
	if ( $current_tab == 'mwb_wpr_general_setting' ) {
		$general_settings_array = array();
		$enable_mwb_wpr = isset( $_POST['mwb_wpr_general_setting_enable'] ) ? 1 : 0;
		$enable_mwb_signup = isset( $_POST['mwb_wpr_general_signup'] ) ? 1 : 0;

		$mwb_signup_value = ( isset( $_POST['mwb_wpr_general_signup_value'] ) && $_POST['mwb_wpr_general_signup_value'] != null ) ? sanitize_post( $_POST['mwb_wpr_general_signup_value'] ) : 1;
		// $enable_mwb_comment = isset($_POST['mwb_wpr_general_comment_enable']) ? 1 : 0;
		// $mwb_comment_value = (isset($_POST['mwb_wpr_general_comment_value']) && $_POST['mwb_wpr_general_comment_value'] != null) ? sanitize_post($_POST['mwb_wpr_general_comment_value']) : 1;
		$enable_mwb_refer = isset( $_POST['mwb_wpr_general_refer_enable'] ) ? 1 : 0;
		// $mwb_refer_value_disable = isset($_POST['mwb_wpr_general_refer_value_disable']) ? 1 : 0;
		$enable_mwb_social = isset( $_POST['mwb_wpr_general_social_media_enable'] ) ? 1 : 0;
		$mwb_wpr_facebook = isset( $_POST['mwb_wpr_facebook'] ) ? 1 : 0;
		$mwb_wpr_twitter = isset( $_POST['mwb_wpr_twitter'] ) ? 1 : 0;
		$mwb_wpr_google = isset( $_POST['mwb_wpr_google'] ) ? 1 : 0;
		$mwb_wpr_email = isset( $_POST['mwb_wpr_email'] ) ? 1 : 0;

		$mwb_social_selection = array(
			'Facebook' => $mwb_wpr_facebook,
			'Twitter' => $mwb_wpr_twitter,
			'Email' => $mwb_wpr_email,
		);
		$mwb_refer_value = ( isset( $_POST['mwb_wpr_general_refer_value'] ) && $_POST['mwb_wpr_general_refer_value'] != null ) ? sanitize_post( $_POST['mwb_wpr_general_refer_value'] ) : 1;
		// $mwb_refer_min = (isset($_POST['mwb_wpr_general_refer_minimum']) && $_POST['mwb_wpr_general_refer_minimum'] != null) ? sanitize_post($_POST['mwb_wpr_general_refer_minimum']) : 1;
		$mwb_wpr_general_product_points = ( isset( $_POST['mwb_wpr_general_product_points'] ) && $_POST['mwb_wpr_general_product_points'] != null ) ? sanitize_post( $_POST['mwb_wpr_general_product_points'] ) : 1;

		$mwb_text_points_value = ( isset( $_POST['mwb_wpr_general_text_points'] ) && $_POST['mwb_wpr_general_text_points'] != null ) ? stripcslashes( $_POST['mwb_wpr_general_text_points'] ) : __( 'My Points', MWB_RWPR_Domain );
		// $mwb_ways_to_gain_points_value = (isset($_POST['mwb_wpr_general_ways_to_gain_points']) && $_POST['mwb_wpr_general_ways_to_gain_points'] !=null) ? stripcslashes($_POST['mwb_wpr_general_ways_to_gain_points']) : '';

		$mwb_referral_purchase_enable = isset( $_POST['mwb_wpr_general_referal_purchase_enable'] ) ? 1 : 0;
		// $mwb_referral_purchase_limit = isset($_POST['mwb_wpr_general_referal_purchase_limit']) ? 1 : 0;
		$mwb_referral_purchase_value = ( isset( $_POST['mwb_wpr_general_referal_purchase_value'] ) && $_POST['mwb_wpr_general_referal_purchase_value'] != null ) ? sanitize_post( $_POST['mwb_wpr_general_referal_purchase_value'] ) : 1;
		// $mwb_wpr_general_referal_order_limit = (isset($_POST['mwb_wpr_general_referal_order_limit']) && $_POST['mwb_wpr_general_referal_order_limit'] !=null) ? sanitize_post($_POST['mwb_wpr_general_referal_order_limit']) : 1;
		// $enable_purchase_points = isset($_POST['mwb_wpr_product_purchase_points']) ? 1 : 0;
		// $mwb_wpr_restrict_pro_by_points = isset($_POST['mwb_wpr_restrict_pro_by_points']) ? 1 : 0;
		$mwb_wpr_purchase_product_text = ( isset( $_POST['mwb_wpr_purchase_product_text'] ) && $_POST['mwb_wpr_purchase_product_text'] != null ) ? stripcslashes( $_POST['mwb_wpr_purchase_product_text'] ) : __( 'Use your Points for purchasing this Product', MWB_RWPR_Domain );
		// $mwb_wpr_purchase_points = (isset($_POST['mwb_wpr_purchase_points']) && $_POST['mwb_wpr_purchase_points'] != null) ? sanitize_post($_POST['mwb_wpr_purchase_points']) : 1;

		// $mwb_wpr_product_purchase_price = (isset($_POST['mwb_wpr_product_purchase_price']) && $_POST['mwb_wpr_product_purchase_price'] != null) ? sanitize_post($_POST['mwb_wpr_product_purchase_price']) : 1;
		$mwb_wpr_points_tab_text = ( isset( $_POST['mwb_wpr_points_tab_text'] ) && $_POST['mwb_wpr_points_tab_text'] != null ) ? stripcslashes( $_POST['mwb_wpr_points_tab_text'] ) : '';
		$mwb_wpr_assign_pro_text = ( isset( $_POST['mwb_wpr_assign_pro_text'] ) && $_POST['mwb_wpr_assign_pro_text'] != null ) ? stripcslashes( $_POST['mwb_wpr_assign_pro_text'] ) : __( 'Product Points', MWB_RWPR_Domain );
		$mwb_wpr_set_preferences = isset( $_POST['mwb_wpr_set_preferences'] ) ? $_POST['mwb_wpr_set_preferences'] : 'to_both';
		$mwb_wpr_custom_points_on_cart = isset( $_POST['mwb_wpr_custom_points_on_cart'] ) ? 1 : 0;
		$mwb_wpr_cart_points_rate = isset( $_POST['mwb_wpr_cart_points_rate'] ) && ! empty( $_POST['mwb_wpr_cart_points_rate'] ) ? sanitize_text_field( $_POST['mwb_wpr_cart_points_rate'] ) : 1;
		$mwb_wpr_cart_price_rate = isset( $_POST['mwb_wpr_cart_price_rate'] ) && ! empty( $_POST['mwb_wpr_cart_price_rate'] ) ? sanitize_text_field( $_POST['mwb_wpr_cart_price_rate'] ) : 1;

		$mwb_wpr_apply_points_checkout = isset( $_POST['mwb_wpr_apply_points_checkout'] ) && ! empty( $_POST['mwb_wpr_apply_points_checkout'] ) ? 1 : 0;
		// $mwb_wpr_categ_list= (isset($_POST['mwb_wpr_restrictions_for_purchasing_cat']) && !empty($_POST['mwb_wpr_restrictions_for_purchasing_cat'])) ? $_POST['mwb_wpr_restrictions_for_purchasing_cat'] : '';
		$general_settings_array['enable_mwb_wpr'] = $enable_mwb_wpr;
		$general_settings_array['enable_mwb_signup'] = $enable_mwb_signup;
		$general_settings_array['mwb_signup_value'] = $mwb_signup_value;
		// $general_settings_array['enable_mwb_comment'] = $enable_mwb_comment;
		// $general_settings_array['mwb_comment_value'] = $mwb_comment_value;
		$general_settings_array['enable_mwb_refer'] = $enable_mwb_refer;
		$general_settings_array['mwb_refer_value'] = $mwb_refer_value;
		// $general_settings_array['mwb_refer_min'] = $mwb_refer_min;
		$general_settings_array['enable_mwb_social'] = $enable_mwb_social;
		$general_settings_array['mwb_text_points_value'] = $mwb_text_points_value;
		// $general_settings_array['mwb_ways_to_gain_points_value'] = $mwb_ways_to_gain_points_value;
		$general_settings_array['mwb_referral_purchase_enable'] = $mwb_referral_purchase_enable;
		// $general_settings_array['mwb_referral_purchase_limit'] = $mwb_referral_purchase_limit;
		$general_settings_array['mwb_referral_purchase_value'] = $mwb_referral_purchase_value;
		// $general_settings_array['mwb_wpr_general_referal_order_limit'] = $mwb_wpr_general_referal_order_limit;
		$general_settings_array['mwb_social_selection'] = $mwb_social_selection;
		// $general_settings_array['mwb_wpr_general_refer_value_disable'] = $mwb_refer_value_disable;
		// $general_settings_array['enable_purchase_points'] = $enable_purchase_points;
		// $general_settings_array['mwb_wpr_restrict_pro_by_points'] = $mwb_wpr_restrict_pro_by_points;
		$general_settings_array['mwb_wpr_purchase_product_text'] = $mwb_wpr_purchase_product_text;
		// $general_settings_array['mwb_wpr_purchase_points'] = $mwb_wpr_purchase_points;
		// $general_settings_array['mwb_wpr_product_purchase_price'] = $mwb_wpr_product_purchase_price;
		$general_settings_array['mwb_wpr_points_tab_text'] = $mwb_wpr_points_tab_text;
		$general_settings_array['mwb_wpr_assign_pro_text'] = $mwb_wpr_assign_pro_text;
		$general_settings_array['mwb_wpr_set_preferences'] = $mwb_wpr_set_preferences;
		$general_settings_array['mwb_wpr_custom_points_on_cart'] = $mwb_wpr_custom_points_on_cart;
		$general_settings_array['mwb_wpr_cart_points_rate'] = $mwb_wpr_cart_points_rate;
		$general_settings_array['mwb_wpr_cart_price_rate'] = $mwb_wpr_cart_price_rate;
		$general_settings_array['mwb_wpr_apply_points_checkout'] = $mwb_wpr_apply_points_checkout;

		// $general_settings_array['mwb_wpr_restrictions_for_purchasing_cat'] = $mwb_wpr_categ_list;
		if ( is_array( $general_settings_array ) ) {
			if ( isset( $enable_purchase_points ) && $enable_purchase_points == 1 ) {
				$mwb_wpr_custom_points_on_cart = get_option( 'mwb_wpr_custom_points_on_cart', 0 );
				update_option( 'mwb_wpr_custom_points_on_cart', 0 );
			}
		}
			update_option( 'mwb_wpr_settings_gallery', $general_settings_array );
	}
	?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php _e( 'Settings saved.', MWB_RWPR_Domain ); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php _e( 'Dismiss this notices.', MWB_RWPR_Domain ); ?></span>
			</button>
		</div>
		<?php
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
			$enable_mwb_wpr = isset( $general_settings['enable_mwb_wpr'] ) ? intval( $general_settings['enable_mwb_wpr'] ) : 0;
			$enable_mwb_signup = isset( $general_settings['enable_mwb_signup'] ) ? intval( $general_settings['enable_mwb_signup'] ) : 0;
			$mwb_signup_value = isset( $general_settings['mwb_signup_value'] ) ? intval( $general_settings['mwb_signup_value'] ) : 1;
			$enable_mwb_comment = isset( $general_settings['enable_mwb_comment'] ) ? intval( $general_settings['enable_mwb_comment'] ) : 0;
			$mwb_comment_value = isset( $general_settings['mwb_comment_value'] ) ? intval( $general_settings['mwb_comment_value'] ) : 1;
			$enable_mwb_refer = isset( $general_settings['enable_mwb_refer'] ) ? intval( $general_settings['enable_mwb_refer'] ) : 0;
			$enable_mwb_social = isset( $general_settings['enable_mwb_social'] ) ? intval( $general_settings['enable_mwb_social'] ) : 0;
			$mwb_refer_value = isset( $general_settings['mwb_refer_value'] ) ? intval( $general_settings['mwb_refer_value'] ) : 1;
			$mwb_refer_min = isset( $general_settings['mwb_refer_min'] ) ? intval( $general_settings['mwb_refer_min'] ) : 1;
			$mwb_text_points_value = isset( $general_settings['mwb_text_points_value'] ) ? $general_settings['mwb_text_points_value'] : __( 'My Points', MWB_RWPR_Domain );
			$mwb_ways_to_gain_points_value = isset( $general_settings['mwb_ways_to_gain_points_value'] ) ? $general_settings['mwb_ways_to_gain_points_value'] : '';
			$mwb_referral_purchase_enable = isset( $general_settings['mwb_referral_purchase_enable'] ) ? intval( $general_settings['mwb_referral_purchase_enable'] ) : 0;
			$mwb_referral_purchase_limit = isset( $general_settings['mwb_referral_purchase_limit'] ) ? intval( $general_settings['mwb_referral_purchase_limit'] ) : 0;
			$mwb_referral_purchase_value = isset( $general_settings['mwb_referral_purchase_value'] ) ? intval( $general_settings['mwb_referral_purchase_value'] ) : 1;
			$mwb_wpr_general_referal_order_limit = isset( $general_settings['mwb_wpr_general_referal_order_limit'] ) ? intval( $general_settings['mwb_wpr_general_referal_order_limit'] ) : 1;
			$mwb_social_selection = isset( $general_settings['mwb_social_selection'] ) ? $general_settings['mwb_social_selection'] : array();
			$mwb_refer_value_disable = isset( $general_settings['mwb_wpr_general_refer_value_disable'] ) ? intval( $general_settings['mwb_wpr_general_refer_value_disable'] ) : 0;
			$enable_purchase_points = isset( $general_settings['enable_purchase_points'] ) ? intval( $general_settings['enable_purchase_points'] ) : 0;
			$mwb_wpr_restrict_pro_by_points = isset( $general_settings['mwb_wpr_restrict_pro_by_points'] ) ? intval( $general_settings['mwb_wpr_restrict_pro_by_points'] ) : 0;
			$mwb_wpr_purchase_product_text = isset( $general_settings['mwb_wpr_purchase_product_text'] ) ? $general_settings['mwb_wpr_purchase_product_text'] : __( 'Use your Points for purchasing this Product', MWB_RWPR_Domain );
			$mwb_wpr_purchase_points = ( isset( $general_settings['mwb_wpr_purchase_points'] ) && $general_settings['mwb_wpr_purchase_points'] != null ) ? $general_settings['mwb_wpr_purchase_points'] : 1;

			$mwb_wpr_product_purchase_price = ( isset( $general_settings['mwb_wpr_product_purchase_price'] ) && $general_settings['mwb_wpr_product_purchase_price'] != null ) ? intval( $general_settings['mwb_wpr_product_purchase_price'] ) : 1;
			$mwb_wpr_points_tab_text = isset( $general_settings['mwb_wpr_points_tab_text'] ) ? $general_settings['mwb_wpr_points_tab_text'] : '';
			$mwb_wpr_assign_pro_text = isset( $general_settings['mwb_wpr_assign_pro_text'] ) ? $general_settings['mwb_wpr_assign_pro_text'] : __( 'Product Points', MWB_RWPR_Domain );
			$mwb_wpr_set_preferences = isset( $general_settings['mwb_wpr_set_preferences'] ) ? $general_settings['mwb_wpr_set_preferences'] : 'to_both';
			$mwb_wpr_categ_list = isset( $general_settings['mwb_wpr_restrictions_for_purchasing_cat'] ) ? $general_settings['mwb_wpr_restrictions_for_purchasing_cat'] : array();
			$mwb_wpr_custom_points_on_cart = isset( $general_settings['mwb_wpr_custom_points_on_cart'] ) ? intval( $general_settings['mwb_wpr_custom_points_on_cart'] ) : 1;
			$mwb_wpr_cart_points_rate = isset( $general_settings['mwb_wpr_cart_points_rate'] ) ? intval( $general_settings['mwb_wpr_cart_points_rate'] ) : 1;
			$mwb_wpr_cart_price_rate = isset( $general_settings['mwb_wpr_cart_price_rate'] ) ? intval( $general_settings['mwb_wpr_cart_price_rate'] ) : 1;
			$mwb_wpr_apply_points_checkout = isset( $general_settings['mwb_wpr_apply_points_checkout'] ) ? intval( $general_settings['mwb_wpr_apply_points_checkout'] ) : 1;
			?>
			<!-- Enable Sections-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Enable', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_setting_enable"><?php _e( 'Enable', MWB_RWPR_Domain ); ?></label>
					</div>

					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to enable the plugin.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_setting_enable">
							<input type="checkbox" <?php checked( $enable_mwb_wpr, 1 ); ?> name="mwb_wpr_general_setting_enable" id="mwb_wpr_general_setting_enable"> <?php _e( 'Enable WooCommerce Points and Rewards', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
			</div>
			<!--Sign Up Sections-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Sign Up', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_signup"><?php _e( 'Enable Signup Points', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to enable the Signup Points.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_signup">
							<input type="checkbox" <?php checked( $enable_mwb_signup, 1 ); ?> name="mwb_wpr_general_signup" id="mwb_wpr_general_signup" class="input-text"> <?php _e( 'Enable Signup Points for Rewards', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_signup_value"><?php _e( 'Enter Signup Points', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'The points which the new customer will get after signup.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_signup_value">
							<input type="number" min="1" value="<?php echo $mwb_signup_value; ?>" name="mwb_wpr_general_signup_value" id="mwb_wpr_general_signup_value" class="input-text mwb_wpr_new_woo_ver_style_text">
						</label>						
					</div>
				</div>
			</div>
			<!--Comment Sections-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Comment', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_comment_enable"><?php _e( 'Enable Comments Points', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to enable the Comments Points when comment is approved.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_comment_enable">
							<input type="checkbox" <?php checked( $enable_mwb_comment, 1 ); ?> name="mwb_wpr_general_comment_enable" id="mwb_wpr_general_comment_enable" class="input-text"> <?php _e( 'Enable Comments Points for Rewards', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_comment_value"><?php _e( 'Enter Comments Points', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'The points which the new customer will get after their comments are approved.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_comment_value">
							<input type="number" min="1" value="<?php echo $mwb_comment_value; ?>" name="mwb_wpr_general_comment_value" id="mwb_wpr_general_comment_value" class="input-text mwb_wpr_new_woo_ver_style_text">
						</label>						
					</div>
				</div>
			</div>
			<!--Referral Sections-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Referral', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_refer_enable"><?php _e( 'Enable Referral Points', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to enable the Referral Points when customer invites another customers.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_refer_enable">
							<input type="checkbox" <?php checked( $enable_mwb_refer, 1 ); ?> name="mwb_wpr_general_refer_enable" id="mwb_wpr_general_refer_enable" class="input-text"> <?php _e( 'Enable Referral Points for Rewards.', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_refer_value"><?php _e( 'Enter Referral Points', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'The points which the customer will get when they successfully invites given number of customers.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_refer_value">
							<input type="number" min="1" value="<?php echo $mwb_refer_value; ?>" name="mwb_wpr_general_refer_value" id="mwb_wpr_general_refer_value" class="input-text mwb_wpr_new_woo_ver_style_text">
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_refer_minimum"><?php _e( 'Minimum Referrals Required', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Minimum number of referrals required to get referral points when the new customer sign ups.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_refer_minimum">
							<input type="number" min="1" value="<?php echo $mwb_refer_min; ?>" name="mwb_wpr_general_refer_minimum" id="mwb_wpr_general_refer_minimum" class="input-text mwb_wpr_new_woo_ver_style_text">
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_refer_value_disable"><?php _e( 'Assign Only Referral Purchase Points', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this if you want to assign only purchase points to referred user not referral points.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_refer_value_disable">
							<input type="checkbox" <?php checked( $mwb_refer_value_disable, 1 ); ?> name="mwb_wpr_general_refer_value_disable" id="mwb_wpr_general_refer_value_disable" class="input-text"> <?php _e( 'Make sure Referral Points & Referral Purchase Points should be enable ', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_referal_purchase_enable"><?php _e( 'Enable Referral Purchase Points', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to enable the referral purchase points', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_referal_purchase_enable">
							<input type="checkbox" <?php checked( $mwb_referral_purchase_enable, 1 ); ?> name="mwb_wpr_general_referal_purchase_enable" id="mwb_wpr_general_referal_purchase_enable" class="input-text"> <?php _e( 'Enable Referral Purchase Points', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_referal_purchase_value"><?php _e( 'Enter Referral Purchase Points', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Entered point will assign to that user by which another user reffered from refrral link and purchase some products ', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_referal_purchase_value">
							<input type="number" min="1" value="<?php echo $mwb_referral_purchase_value; ?>" name="mwb_wpr_general_referal_purchase_value" id="mwb_wpr_general_referal_purchase_value" class="input-text mwb_wpr_new_woo_ver_style_text">
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_referal_purchase_limit"><?php _e( 'Enable Referral Purchase Limit', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to provide some limitation for referral purchase point, where you can set the number of orders for refree', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_referal_purchase_limit">
							<input type="checkbox" <?php checked( $mwb_referral_purchase_limit, 1 ); ?> name="mwb_wpr_general_referal_purchase_limit" id="mwb_wpr_general_referal_purchase_limit" class="input-text"> <?php _e( 'Enable limit for Referral Purchase Option', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_referal_order_limit"><?php _e( 'Set the Number of Orders for Referral Purchase Limit', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Enter the number of orders, Refree would get assigned only till the limit(no of orders) would be reached', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_referal_order_limit">
							<input type="number" min="1" value="<?php echo $mwb_wpr_general_referal_order_limit; ?>" name="mwb_wpr_general_referal_order_limit" id="mwb_wpr_general_referal_order_limit" class="input-text mwb_wpr_new_woo_ver_style_text">
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">

					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_referral_link_permanent"><?php _e( 'Static Referral Link', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box if you want to make your referral link permanent.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_referral_link_permanent">
							<input type="checkbox" name="mwb_wpr_referral_link_permanent" id="mwb_wpr_referral_link_permanent" class="input-text"> <?php _e( 'Make Referral Link Permanent', MWB_RWPR_Domain ); ?>
						</label>
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_ref_link_expiry"><?php _e( 'Referral Link Expiry', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Set the number of days after that the system will not able to remember the reffered user anymore', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_ref_link_expiry">
							<input type="number"  name="mwb_wpr_ref_link_expiry" id="mwb_wpr_ref_link_expiry" value="" class="input-text mwb_wpr_new_woo_ver_style_text"> <?php _e( 'Days', MWB_RWPR_Domain ); ?>
						</label>
					</div>
				</div>
			</div>
			<!--Social Sharing Sections-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Social Sharing', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_social_media_enable"><?php _e( 'Enable Social Links', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to share the Referral Link on social media', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_social_media_enable">
							<input type="checkbox" <?php checked( $enable_mwb_social, 1 ); ?> name="mwb_wpr_general_social_media_enable" id="mwb_wpr_general_social_media_enable" class="input-text"> <?php _e( 'Enable Social Media Sharing.', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_facebook"><?php _e( 'Select Social Links', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check these boxes to share referral link', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_facebook">
							<input type="checkbox" <?php checked( isset( $mwb_social_selection['Facebook'] ) ? $mwb_social_selection['Facebook'] : 0 ); ?> name="mwb_wpr_facebook" id="mwb_wpr_facebook" class="input-text"><?php _e( 'Facebook', MWB_RWPR_Domain ); ?>
						</label>
						<label for="mwb_wpr_twitter">
							<input type="checkbox" <?php checked( isset( $mwb_social_selection['Twitter'] ) ? $mwb_social_selection['Twitter'] : 0 ); ?> name="mwb_wpr_twitter" id="mwb_wpr_twitter" class="input-text"><?php _e( 'Twitter', MWB_RWPR_Domain ); ?>
						</label>
						<label for="mwb_wpr_email">
							<input type="checkbox" <?php checked( isset( $mwb_social_selection['Email'] ) ? $mwb_social_selection['Email'] : 0 ); ?> name="mwb_wpr_email" id="mwb_wpr_email" class="input-text"><?php _e( 'Email', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
			</div>
			<!--Text Settings Sections-->		
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Text Settings', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_text_points" class="mwb_wpr_label"><?php _e( 'Enter Text', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Entered text will append before the Total Number of Point', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_general_text_points">
							<input type="text" name="mwb_wpr_general_text_points" value="<?php echo $mwb_text_points_value; ?>" id="mwb_wpr_general_text_points" class="text_points mwb_wpr_new_woo_ver_style_text"><?php _e( 'Entered text will get displayed on points page.', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_general_ways_to_gain_points" class="mwb_wpr_label"><?php _e( 'Enter Ways to Gain Points', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Entered ways will get displayed on points page.', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<span class="description"><?php _e( 'Use these shortcodes for providing ways to gain points at front end.', MWB_RWPR_Domain ); ?></span>	
						<label for="mwb_wpr_general_text_points" class="mwb_wpr_label">
							<textarea cols="35" rows="5" name="mwb_wpr_general_ways_to_gain_points" id="mwb_wpr_general_ways_to_gain_points" class="input-text" ><?php echo $mwb_ways_to_gain_points_value; ?></textarea>
						</label>
						<p class="description">
						<?php
						echo '[Refer Points]';
						_e( ' for Referral Points', MWB_RWPR_Domain );
						echo ' [Comment Points]';
						_e( ' for Comment Points ', MWB_RWPR_Domain );
						echo '[Per Currency Spent Points]';
						_e( ' for Per currency spent points and', MWB_RWPR_Domain );
						echo '[Per Currency Spent Price]';
						_e( ' for per currency spent price', MWB_RWPR_Domain );
						?>
						</p>
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_purchase_product_text" class="mwb_wpr_label"><?php _e( 'Enter Text', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Entered text will get displayed on Single Product Page', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_purchase_product_text">
							<input type="text" name="mwb_wpr_purchase_product_text" value="<?php echo $mwb_wpr_purchase_product_text; ?>" id="mwb_wpr_purchase_product_text" class="text_points mwb_wpr_new_woo_ver_style_text"><?php _e( 'Entered text will get displayed on single product page', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_points_tab_text" class="mwb_wpr_label"><?php _e( 'Points Tab Text', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Entered text will be replaced the Points tab at Myaccount Page', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_points_tab_text" class="mwb_wpr_label">
							<input type="text" name="mwb_wpr_points_tab_text" value="<?php echo $mwb_wpr_points_tab_text; ?>" id="mwb_wpr_points_tab_text" class="text_points mwb_wpr_new_woo_ver_style_text"><?php _e( 'Points Tab replaced with your text', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_assign_pro_text"><?php _e( 'Assigned Product Points Text', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Enter the message you want to display for those product who have assigned with some of the Points', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_assign_pro_text">
							<input type="text" name="mwb_wpr_assign_pro_text" value="<?php echo $mwb_wpr_assign_pro_text; ?>" id="mwb_wpr_assign_pro_text" class="text_points mwb_wpr_new_woo_ver_style_text"><?php _e( 'Product Point text can be replaced with entered text', MWB_RWPR_Domain ); ?>
						</label>			
					</div>
				</div>
			</div>
			<!--Purchase Through Points Sections-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Purchase Through Points', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_product_purchase_points"><?php _e( 'Enable Purchase through Points', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box to enable puchasing products through points', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_product_purchase_points">
							<input type="checkbox" <?php checked( $enable_purchase_points, 1 ); ?> name="mwb_wpr_product_purchase_points" id="mwb_wpr_product_purchase_points" class="input-text"> <?php _e( 'Purchase Products through Points', MWB_RWPR_Domain ); ?>
						</label>						
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_restrict_pro_by_points"><?php _e( 'Enable restrictions for above setting', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box if you want to allow some of the products for purchasing through points not all', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_restrict_pro_by_points">
							<input type="checkbox" <?php checked( $mwb_wpr_restrict_pro_by_points, 1 ); ?> name="mwb_wpr_restrict_pro_by_points" id="mwb_wpr_restrict_pro_by_points" class="input-text"> <?php _e( 'Allow some of the products for purchasing through points', MWB_RWPR_Domain ); ?>
						</label>	
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_restrictions_for_purchasing_cat"><?php _e( 'Select Product Category', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Select those categories which you want to allow to customers for purchase that product through points', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<select id="mwb_wpr_restrictions_for_purchasing_cat"  multiple="multiple" name="mwb_wpr_restrictions_for_purchasing_cat[]">
							<?php
							$args = array( 'taxonomy' => 'product_cat' );
							$categories = get_terms( $args );
							if ( isset( $categories ) && ! empty( $categories ) ) {
								foreach ( $categories as $category ) {
									$catid = $category->term_id;
									$catname = $category->name;
									$catselect = '';
									if ( is_array( $mwb_wpr_categ_list ) && in_array( $catid, $mwb_wpr_categ_list ) ) {
										$catselect = "selected='selected'";
									}
									?>
									<option value="<?php echo $catid; ?>" <?php echo $catselect; ?>><?php echo $catname; ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_purchase_pro_points_value"><?php _e( 'Purchase Points Conversion', MWB_RWPR_Domain ); ?></label>
						<span class="mwb_wpr_general_pro">Pro</span>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Entered points will be converted to price.(i.e., how many points will be equivalent to the product price)', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_purchase_pro_points_value" class="mwb_wpr_label">

							<input type="number" min="1" value="<?php echo $mwb_wpr_purchase_points; ?>" name="mwb_wpr_purchase_points" id="mwb_wpr_purchase_points" class="input-text wc_input_price mwb_wpr_new_woo_ver_style_text">
							<?php echo __( 'Points ', MWB_RWPR_Domain ); ?>  =
							<?php echo get_woocommerce_currency_symbol(); ?>
							<input type="text" value="<?php echo $mwb_wpr_product_purchase_price; ?>" name="mwb_wpr_product_purchase_price" id="mwb_wpr_product_purchase_price" class="input-text mwb_wpr_new_woo_ver_style_text wc_input_price">
						</label>						
					</div>
				</div>
			</div>
			<!--Set Preferences Sections-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Set Preferences', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">		
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_set_preferences"><?php _e( 'Set Preferences', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'What you want to choose when you have enabled "Per currency Spent Point" and a product with some "Points" assigned ?', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_set_preferences_to_points">
							<input type="radio" <?php echo ( $mwb_wpr_set_preferences == 'to_assign_point' ) ? "checked='checked'" : ''; ?> name="mwb_wpr_set_preferences" value="to_assign_point"class="mwb_wpr_set_preferences_to_points" id="mwb_wpr_set_preferences_to_points"><?php _e( 'Only Product Assigned Points', MWB_RWPR_Domain ); ?><br/>
						</label>
						<label for="mwb_wpr_set_preferences_to_per_currency">
							<input  type="radio" <?php echo ( $mwb_wpr_set_preferences == 'to_per_currency' ) ? "checked='checked'" : ''; ?> name="mwb_wpr_set_preferences" value="to_per_currency"><?php _e( 'Only Per Currency Spent Point', MWB_RWPR_Domain ); ?><br/>
						</label>
						<label for="mwb_wpr_set_preferences_to_both">
							<input  type="radio" <?php echo ( $mwb_wpr_set_preferences == 'to_both' ) ? "checked='checked'" : ''; ?> name="mwb_wpr_set_preferences" value="to_both" id="mwb_wpr_set_preferences_to_both"><?php _e( 'Both', MWB_RWPR_Domain ); ?>
						</label>					
					</div>
				</div>
			</div>
			<!--Redemption over the cart-->
			<div class="mwb_wpr_general_row_wrap">
				<div class="mwb_wpr_general_sign_title"><?php _e( 'Redemption Settings', MWB_RWPR_Domain ); ?></div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_custom_points_on_cart"><?php _e( 'Redemption Over Cart Sub-Total', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box if you want to let your customers to redeem their earned points for the cart subtotal, there would be no relation with product purchase through point feature', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_custom_points_on_cart">
							<input type="checkbox"  name="mwb_wpr_custom_points_on_cart" <?php checked( $mwb_wpr_custom_points_on_cart, 1 ); ?>id="mwb_wpr_custom_points_on_cart" class="input-text"> <?php _e( 'No relation with Purchase Product Through Point Feature', MWB_RWPR_Domain ); ?>
						</label>
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_cart_points_rate"><?php _e( 'Conversion rate for Cart Sub-Total Redemption', MWB_RWPR_Domain ); ?></label>
					</div>
					<div class="mwb_wpr_general_content">
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
					</div>
				</div>
				<div class="mwb_wpr_general_row">
					<div class="mwb_wpr_general_label">
						<label for="mwb_wpr_apply_points_checkout"><?php _e( 'Enable apply points during checkout', MWB_RWPR_Domain ); ?>
						</label>
					</div>
					<div class="mwb_wpr_general_content">
						<?php
						$attribute_description = __( 'Check this box if you want that customer can apply also apply points on checkout', MWB_RWPR_Domain );
						echo wc_help_tip( $attribute_description );
						?>
						<label for="mwb_wpr_apply_points_checkout">
							<input type="checkbox" name="mwb_wpr_apply_points_checkout" <?php checked( $mwb_wpr_apply_points_checkout, 1 ); ?>id="mwb_wpr_apply_points_checkout" class="input-text"> <?php _e( 'Allow customers to apply points during checkout also', MWB_RWPR_Domain ); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
	<p class="submit">
		<input type="submit" value='<?php _e( 'Save changes', MWB_RWPR_Domain ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_general">
	</p>
