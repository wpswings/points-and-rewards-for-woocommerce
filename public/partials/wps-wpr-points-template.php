<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    wps_wpr_points_template.php
 * @subpackage points-and-rewards-for-wooCommerce/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
Declarations
*/
$user_id = get_current_user_id();
$my_role = ! empty( get_user_meta( $user_id, 'membership_level', true ) ) ? get_user_meta( $user_id, 'membership_level', true ) : '';
if ( isset( $_POST['wps_wpr_save_level'] ) && isset( $_POST['membership-save-level'] ) && isset( $_POST['wps_wpr_membership_roles'] ) && sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_roles'] ) ) != $my_role ) {
	$wps_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['membership-save-level'] ) );

	if ( wp_verify_nonce( $wps_wpr_nonce, 'membership-save-level' ) ) {
		$selected_role             = isset( $_POST['wps_wpr_membership_roles'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_roles'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification
		$user_id                   = get_current_user_id();
		$user                      = get_user_by( 'ID', $user_id );
		$get_points                = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		$membership_detail         = get_user_meta( $user_id, 'points_details', true );
		$today_date                = date_i18n( 'Y-m-d h:i:sa', current_time( 'timestamp', 0 ) );
		$expiration_date           = '';
		$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
		$wps_wpr_membership_roles  = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();

		foreach ( $wps_wpr_membership_roles as $roles => $values ) {
			if ( $selected_role == $roles && ( $values['Points'] == $get_points || $values['Points'] < $get_points ) ) {
				/*Calculate the points*/
				$remaining_points = $get_points - $values['Points'];
				/*Update points log*/
				$data = array();
				$this->wps_wpr_update_points_details( $user_id, 'membership', $values['Points'], $data );

				if ( isset( $values['Exp_Number'] ) && ! empty( $values['Exp_Number'] ) && isset( $values['Exp_Days'] ) && ! empty( $values['Exp_Days'] ) ) {
					$expiration_date = date_i18n( 'Y-m-d', strtotime( $today_date . ' +' . $values['Exp_Number'] . ' ' . $values['Exp_Days'] ) );
				}
				update_user_meta( $user_id, 'wps_wpr_points', $remaining_points );
				update_user_meta( $user_id, 'membership_level', $selected_role );
				update_user_meta( $user_id, 'membership_expiration', $expiration_date );
				/*Send mail*/
				$user = get_user_by( 'ID', $user_id );
				$wps_wpr_shortcode = array(
					'[USERLEVEL]' => $selected_role,
					'[USERNAME]'  => $user->user_login,
				);

				$wps_wpr_subject_content = array(
					'wps_wpr_subject' => 'wps_wpr_membership_email_subject',
					'wps_wpr_content' => 'wps_wpr_membership_email_discription_custom_id',
				);
				$this->wps_wpr_send_notification_mail_product( $user_id, $values['Points'], $wps_wpr_shortcode, $wps_wpr_subject_content );
			}
		}
	}
}

$user_id = get_current_user_id();
/* Get points of the User*/
$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
/* Get points of the Membership Level*/
$wps_user_level = get_user_meta( $user_id, 'membership_level', true );

/* Get the General Settings*/
$general_settings              = get_option( 'wps_wpr_settings_gallery', true );
$enable_wps_refer              = isset( $general_settings['wps_wpr_general_refer_enable'] ) ? intval( $general_settings['wps_wpr_general_refer_enable'] ) : 0;
$wps_refer_value               = isset( $general_settings['wps_wpr_general_refer_value'] ) ? intval( $general_settings['wps_wpr_general_refer_value'] ) : 1;
$wps_text_points_value         = isset( $general_settings['wps_wpr_general_text_points'] ) ? $general_settings['wps_wpr_general_text_points'] : esc_html__( 'My Points', 'points-and-rewards-for-woocommerce' );
$wps_ways_to_gain_points_value = ! empty( $general_settings['wps_wpr_general_ways_to_gain_points'] ) ? $general_settings['wps_wpr_general_ways_to_gain_points'] : __( '[Refer Points] for Referral Points[Per Currency Spent Points] for Per currency spent points and[Per Currency Spent Price] for per currency spent price' );
// End Section of the Setings.

// Get the General Settings.
$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
$wps_wpr_mem_enable        = isset( $membership_settings_array['wps_wpr_membership_setting_enable'] ) ? intval( $membership_settings_array['wps_wpr_membership_setting_enable'] ) : 0;
$coupon_settings           = get_option( 'wps_wpr_coupons_gallery', true );
$get_points                = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
$coupon_redeem_price       = ( isset( $coupon_settings['coupon_redeem_price'] ) && null != $coupon_settings['coupon_redeem_price'] ) ? $coupon_settings['coupon_redeem_price'] : 1;
$coupon_redeem_points      = ( isset( $coupon_settings['coupon_redeem_points'] ) && null != $coupon_settings['coupon_redeem_points'] ) ? intval( $coupon_settings['coupon_redeem_points'] ) : 1;

$wps_per_currency_spent_price  = isset( $coupon_settings['wps_wpr_coupon_conversion_price'] ) ? intval( $coupon_settings['wps_wpr_coupon_conversion_price'] ) : 1;
$wps_per_currency_spent_points = isset( $coupon_settings['wps_wpr_coupon_conversion_points'] ) ? intval( $coupon_settings['wps_wpr_coupon_conversion_points'] ) : 1;
$wps_comment_value             = isset( $general_settings['wps_comment_value'] ) ? intval( $general_settings['wps_comment_value'] ) : 1;
$wps_refer_value_disable       = isset( $general_settings['wps_wpr_general_refer_value_disable'] ) ? intval( $general_settings['wps_wpr_general_refer_value_disable'] ) : 0;
$wps_user_point_expiry         = get_user_meta( $user_id, 'wps_wpr_points_expiration_date', true );
/* End the memebership settings*/

$get_referral        = get_user_meta( $user_id, 'wps_points_referral', true );
$get_referral_invite = get_user_meta( $user_id, 'wps_points_referral_invite', true );
if ( ! is_array( $coupon_settings ) ) {
	$coupon_settings = array();
}
?>
<div class="wps_wpr_points_wrapper_with_exp">
	<div class="wps_wpr_points_only"><p class="wps_wpr_heading_para" >
		<span class="wps_wpr_heading"><?php echo esc_html( $wps_text_points_value ) . ':'; ?></span></p>
		<?php
		$get_points = get_user_meta( $user_id, 'wps_wpr_points', true );
		$get_point  = get_user_meta( $user_id, 'points_details', true );
		?>
		<span class="wps_wpr_heading" id="wps_wpr_points_only">
			<?php
			echo ( isset( $get_points ) && null != $get_points ) ? esc_html( $get_points ) : 0;
			?>
		</span>
	</div>
	<?php
	if ( isset( $wps_user_point_expiry ) && ! empty( $wps_user_point_expiry ) && $get_points > 0 ) {
		$expiration_settings = get_option( 'wps_wpr_points_expiration_settings', true );
		if ( ! empty( $expiration_settings['wps_wpr_points_exp_onmyaccount'] ) ) {
			$wps_wpr_points_exp_onmyaccount = $expiration_settings['wps_wpr_points_exp_onmyaccount'];
		}
		if ( isset( $wps_wpr_points_exp_onmyaccount ) && ! empty( $wps_wpr_points_exp_onmyaccount ) ) {
			$date_format           = get_option( 'date_format' );
			$expiry_date_timestamp = strtotime( $wps_user_point_expiry );
			$expirydate_format     = date_i18n( $date_format, $expiry_date_timestamp );
			echo '<p class=wps_wpr_points_expiry> ' . esc_html_e( 'Get Expired: ', 'points-and-rewards-for-woocommerce' ) . esc_html( $expirydate_format ) . '</p>';
		}
	}
	?>
</div>		
<span class="wps_wpr_view_log">
	<a href="
	<?php
	echo esc_url( wc_get_endpoint_url( 'view-log' ) );
	?>
	">
	<?php
	esc_html_e( 'View Point Log', 'points-and-rewards-for-woocommerce' );
	?>
	</a>
</span>
<?php
if ( isset( $wps_ways_to_gain_points_value ) && ! empty( $wps_ways_to_gain_points_value ) ) {
	?>
	<div class ="wps_ways_to_gain_points_section">
	<p class="wps_wpr_heading"><?php echo esc_html__( 'Ways to gain more points:', 'points-and-rewards-for-woocommerce' ); ?></p>
			<?php
			$wps_ways_to_gain_points_value = str_replace( '[Comment Points]', $wps_comment_value, $wps_ways_to_gain_points_value );
			$wps_ways_to_gain_points_value = str_replace( '[Refer Points]', $wps_refer_value, $wps_ways_to_gain_points_value );
			$wps_ways_to_gain_points_value = str_replace( '[Per Currency Spent Points]', $wps_per_currency_spent_points, $wps_ways_to_gain_points_value );
			$wps_ways_to_gain_points_value = str_replace( '[Per Currency Spent Price]', $wps_per_currency_spent_price, $wps_ways_to_gain_points_value );
			echo '<fieldset class="wps_wpr_each_section">' . wp_kses_post( $wps_ways_to_gain_points_value ) . '</fieldset>';
			?>

	</div>
	<?php
}
if ( $wps_wpr_mem_enable ) {
	$enable_drop              = false;
	$wps_wpr_membership_roles = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();
	?>

	<p class="wps_wpr_heading"><?php esc_html_e( 'Membership List', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		if ( isset( $wps_user_level ) && ! empty( $wps_user_level ) && array_key_exists( $wps_user_level, $wps_wpr_membership_roles ) ) {

			?>
			<span class="wps_wpr_upgrade_level">
			<?php
			esc_html_e( 'Your membership level has been upgraded to ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( $wps_user_level );
			?>
			</span>
			<?php
		}
		?>

			<table class="woocommerce-MyAccount-points shop_table my_account_points account-points-table wps_wpr_membership_with_img">
				<thead>
					<tr>
						<th class="wps-wpr-points-points">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Level', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
						<th class="wps-wpr-points-code">
							<span class="wps_wpr_nobr"><?php echo esc_html__( 'Required Points', 'points-and-rewards-for-woocommerce' ); ?></span>
						</th>
						<?php
						if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {
							?>
							<th class="wps-wpr-points-expiry">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Membership Expiry', 'ultimate-woocommerce-points-and-rewards' ); ?></span>
							</th>
							<?php
						}
						?>
						<?php do_action( 'wps_wpr_membership_expiry_for_user_html' ); ?>
					</tr>
				</thead>
				<tbody>
				<?php
				if ( is_array( $wps_wpr_membership_roles ) && ! empty( $wps_wpr_membership_roles ) ) {
					foreach ( $wps_wpr_membership_roles as $wps_role => $values ) {//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						if ( ! is_array( $values ) ) {
							return;
						}
						?>
				<tr>
					<td>
						<?php
						$wps_member_name = strtolower( str_replace( ' ', '_', $wps_role ) );
						echo esc_html( $wps_role ) . '<br/><a class = "wps_wpr_level_benefits" data-id = "' . esc_html( $wps_member_name ) . '" href="javascript:;">' . esc_html__( 'View Benefits', 'points-and-rewards-for-woocommerce' ) . '</a>'; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.WP.I18n.NonSingularStringLiteralText
						?>
						</td>
						<div class="wps_wpr_popup_wrapper wps_rwpr_settings_display_none" id="wps_wpr_popup_wrapper_<?php echo esc_html( $wps_member_name ); ?>">
							<div class="wps_wpr_popup_content_section">
								<div class="wps_wpr_popup_content">
									<div class="wps_wpr_popup_notice_section">					
										<p>
											<span class="wps_wpr_intro_text">
											<?php
											esc_html_e( 'You will get ', 'points-and-rewards-for-woocommerce' );
											echo esc_html( $values['Discount'] );
											esc_html_e( '% discount on below products or categories', 'points-and-rewards-for-woocommerce' );
											?>
											</span>
											<span class="wps_wpr_close">
												<a href="javascript:;"><img src="<?php echo esc_url( WPS_RWPR_DIR_URL ); ?>public/images/cancel.png" alt=""></a>
											</span>
										</p>
									</div>
									<div class="wps_wpr_popup_thumbnail_section">
										<ul>
										<?php
										if ( is_array( $values['Product'] ) && ! empty( $values['Product'] ) ) {
											foreach ( $values['Product'] as $key => $pro_id ) {
												$pro_img = wp_get_attachment_image_src( get_post_thumbnail_id( $pro_id ), 'single-post-thumbnail' );
												$_product = wc_get_product( $pro_id );
												if ( is_object( $_product ) ) {

													$price = $_product->get_price();
													$product_name = $_product->get_title();
												}
												$pro_url = get_permalink( $pro_id );
												if ( empty( $pro_img[0] ) ) {
													$pro_img[0] = WPS_RWPR_DIR_URL . 'public/images/placeholder.png';
												}
												?>
												<li>
													<a href="<?php echo esc_url( $pro_url ); ?>">
														<span class="wps_wpr_thumbnail_img_wrap"><img src="<?php echo esc_url( $pro_img[0] ); ?>" alt=""></span>
														<span class="wps_wpr_thumbnail_product_name"><?php echo esc_html( $product_name ); ?></span>
														<span class="wps_wpr_thumbnail_price_wrap"><?php echo wp_kses( wc_price( $price ), $this->wps_wpr_allowed_html() ); ?></span>
													</a>
												</li>		
												<?php
											}
											?>
											</ul>
											<?php
										} else {
											if ( is_array( $values['Prod_Categ'] ) && ! empty( $values['Prod_Categ'] ) ) {
												?>
												<div class="wps_wpr_popup_cat">

													<?php
													foreach ( $values['Prod_Categ'] as $key => $wps_cat_id ) {//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
														if ( WC()->version < '3.6.0' ) {

															$thumbnail_id = get_woocommerce_term_meta( $wps_cat_id, 'thumbnail_id', true );
														} else {
															$thumbnail_id = get_term_meta( $wps_cat_id, 'thumbnail_id', true );
														}
														$cat_img = wp_get_attachment_url( $thumbnail_id );
														$category_title = get_term( $wps_cat_id, 'product_cat' );
														$category_link = get_category_link( $wps_cat_id );
														if ( empty( $cat_img ) ) {
															$cat_img = WPS_RWPR_DIR_URL . 'public/images/placeholder.png';
														}
														?>
															<div class="wps_wpr_cat_wrapper">
																<img src="<?php echo esc_url( $cat_img ); ?>" alt="" class="wps_wpr_width_height">
																<a href="<?php echo esc_url( $category_link ); ?>" class="wps_wpr_cat_list"><?php echo esc_html( $category_title->name ); ?></a>
															</div>
														<?php
													}
													?>
												</div>
												<?php
											}
										}
										?>
									</div>								
								</div>
							</div>
						</div>
					<td>
						<?php
						echo esc_html( $values['Points'] );
						?>
					</td>
					<td>
						<?php
						if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {
							echo esc_html( $values['Exp_Number'] ) . ' ' . esc_html( $values['Exp_Days'] );
						}
						do_action( 'wps_wpr_membership_expiry_date_for_user', $user_id, $values, $wps_role );
						if ( $wps_role == $wps_user_level ) {
							echo '<img class="wps_wpr_tick" src = "' . esc_url( WPS_RWPR_DIR_URL ) . 'public/images/tick.png">';
						}
						?>
					</td>
				</tr>
						<?php

						if ( $values['Points'] == $get_points || $values['Points'] < $get_points ) {
							$enable_drop = true;
						}
					}
				}
				?>
				</tbody>
			</table>
	<?php
}
if ( isset( $enable_drop ) && $enable_drop ) {
	if ( isset( $wps_user_level ) && ! empty( $wps_user_level ) && array_key_exists( $wps_user_level, $wps_wpr_membership_roles ) ) {
		unset( $wps_wpr_membership_roles[ $wps_user_level ] );
	}
	if ( ! empty( $wps_wpr_membership_roles ) && is_array( $wps_wpr_membership_roles ) ) {
		?>
			<p class="wps_wpr_heading"><?php echo esc_html_e( 'Upgrade User Level', 'points-and-rewards-for-woocommerce' ); ?></p>
			<fieldset class="wps_wpr_each_section">	
				<span class="wps_wpr_membership_message"><?php echo esc_html_e( 'Upgrade Your User Level: ', 'points-and-rewards-for-woocommerce' ); ?></span>
				<form action="" method="post" id="wps_wpr_membership">
					<?php wp_nonce_field( 'membership-save-level', 'membership-save-level' ); ?>
					<select id="wps_wpr_membership_roles" class="wps_wpr_membership_roles" name="wps_wpr_membership_roles">
						<option><?php echo esc_html__( 'Select Level', 'points-and-rewards-for-woocommerce' ); ?></option>
					<?php
					foreach ( $wps_wpr_membership_roles as $wps_role => $values ) {
						if ( $values['Points'] == $get_points
							|| $values['Points'] < $get_points ) {
							?>
							<option value="<?php echo esc_html( $wps_role ); ?>">
							<?php
							echo esc_html( $wps_role );
							?>
							</option>
							<?php
						}
					}
					?>
						</select>
						<input type="submit" id = "wps_wpr_upgrade_level" value='<?php esc_html_e( 'Upgrade Level', 'points-and-rewards-for-woocommerce' ); ?>' class="wps_rwpr_settings_display_none button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_level">
						<input type="button" id = "wps_wpr_upgrade_level_click" value='<?php esc_html_e( 'Upgrade Level', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes" name="wps_wpr_save_level_click">
				</form>
			</fieldset>	
		<?php
	}
}
do_action( 'wps_wpr_add_coupon_generation', $user_id );
?>
<br>
<?php
do_action( 'wps_wpr_list_coupons_generation', $user_id );

/*Start of the Referral Section*/
if ( $enable_wps_refer ) {
	$public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', '1.0.0' );
	$public_obj->wps_wpr_get_referral_section( $user_id );
}
/* of the Referral Section*/
do_action( 'wps_wpr_add_share_points', $user_id );
$wps_wpr_user_can_send_point = get_option( 'wps_wpr_user_can_send_point', 0 );

/* Hooks to Extends the point tab */
do_action( 'wps_extend_point_tab_section', $user_id );
