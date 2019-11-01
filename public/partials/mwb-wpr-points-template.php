<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    mwb_wpr_points_template.php
 * @subpackage Rewardeem_woocommerce_Points_Rewards/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
Declarations
*/

if ( isset( $_POST['mwb_wpr_save_level'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
	$selected_role = isset( $_POST['mwb_wpr_membership_roles'] ) ? sanitize_text_field( wp_unslash( $_POST['mwb_wpr_membership_roles'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification
	$user_id = get_current_user_id();
	$user = get_user_by( 'ID', $user_id );
	$user_email = $user->user_email;// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$get_points = (int) get_user_meta( $user_id, 'mwb_wpr_points', true );
	$membership_detail = get_user_meta( $user_id, 'points_details', true );
	$today_date = date_i18n( 'Y-m-d h:i:sa' );
	$expiration_date = '';
	$membership_settings_array = get_option( 'mwb_wpr_membership_settings', true );
	$mwb_wpr_membership_roles = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();
	foreach ( $mwb_wpr_membership_roles as $roles => $values ) {
		if ( $selected_role == $roles && ( $values['Points'] == $get_points || $values['Points'] < $get_points ) ) {
			/*Calculate the points*/
			$remaining_points = $get_points - $values['Points'];
			/*Update points log*/
			$data = array();
			$this->mwb_wpr_update_points_details( $user_id, 'membership', $values['Points'], $data );

			if ( isset( $values['Exp_Number'] ) && ! empty( $values['Exp_Number'] ) && isset( $values['Exp_Days'] ) && ! empty( $values['Exp_Days'] ) ) {
				$expiration_date = date_i18n( 'Y-m-d', strtotime( $today_date . ' +' . $values['Exp_Number'] . ' ' . $values['Exp_Days'] ) );
			}
			update_user_meta( $user_id, 'mwb_wpr_points', $remaining_points );
			update_user_meta( $user_id, 'membership_level', $selected_role );
			update_user_meta( $user_id, 'membership_expiration', $expiration_date );
			/*Send mail*/
			$user = get_user_by( 'ID', $user_id );
			$mwb_wpr_shortcode = array(
				'[USERLEVEL]' => $selected_role,
				'[USERNAME]'  => $user->user_firstname,
			);

			$mwb_wpr_subject_content = array(
				'mwb_wpr_subject' => 'mwb_wpr_membership_email_subject',
				'mwb_wpr_content' => 'mwb_wpr_membership_email_discription_custom_id',
			);
			$this->mwb_wpr_send_notification_mail_product( $user_id, $values['Points'], $mwb_wpr_shortcode, $mwb_wpr_subject_content );
		}
	}
}

$user_id = get_current_user_id();
/* Get points of the User*/
$get_points = (int) get_user_meta( $user_id, 'mwb_wpr_points', true );
/* Get points of the Membership Level*/
$user_level = get_user_meta( $user_id, 'membership_level', true );// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
/* Get the General Settings*/
$general_settings = get_option( 'mwb_wpr_settings_gallery', true );
$enable_mwb_refer = isset( $general_settings['mwb_wpr_general_refer_enable'] ) ? intval( $general_settings['mwb_wpr_general_refer_enable'] ) : 0;
$mwb_refer_value = isset( $general_settings['mwb_wpr_general_refer_value'] ) ? intval( $general_settings['mwb_wpr_general_refer_value'] ) : 1;
// $mwb_refer_min = isset($general_settings['mwb_refer_min']) ? intval($general_settings['mwb_refer_min']) : 1;
$mwb_text_points_value = isset( $general_settings['mwb_wpr_general_text_points'] ) ? $general_settings['mwb_wpr_general_text_points'] : esc_html__( 'My Points', 'rewardeem-woocommerce-points-rewards' );
$mwb_ways_to_gain_points_value = isset( $general_settings['mwb_wpr_general_ways_to_gain_points'] ) ? $general_settings['mwb_wpr_general_ways_to_gain_points'] : '';
// End Section of the Setings.
// Get the General Settings.
$membership_settings_array = get_option( 'mwb_wpr_membership_settings', true );
$mwb_wpr_mem_enable = isset( $membership_settings_array['mwb_wpr_membership_setting_enable'] ) ? intval( $membership_settings_array['mwb_wpr_membership_setting_enable'] ) : 0;
$coupon_settings = get_option( 'mwb_wpr_coupons_gallery', true );
$get_points = (int) get_user_meta( $user_id, 'mwb_wpr_points', true );
$coupon_redeem_price = ( isset( $coupon_settings['coupon_redeem_price'] ) && $coupon_settings['coupon_redeem_price'] != null ) ? $coupon_settings['coupon_redeem_price'] : 1;//phpcs:ignore WordPress.PHP.YodaConditions.NotYoda
$coupon_redeem_points = ( isset( $coupon_settings['coupon_redeem_points'] ) && $coupon_settings['coupon_redeem_points'] != null ) ? intval( $coupon_settings['coupon_redeem_points'] ) : 1;//phpcs:ignore WordPress.PHP.YodaConditions.NotYoda

$mwb_per_currency_spent_price = isset( $coupon_settings['mwb_wpr_coupon_conversion_price'] ) ? intval( $coupon_settings['mwb_wpr_coupon_conversion_price'] ) : 1;
$mwb_per_currency_spent_points = isset( $coupon_settings['mwb_wpr_coupon_conversion_points'] ) ? intval( $coupon_settings['mwb_wpr_coupon_conversion_points'] ) : 1;
$mwb_comment_value = isset( $general_settings['mwb_comment_value'] ) ? intval( $general_settings['mwb_comment_value'] ) : 1;
$mwb_refer_value_disable = isset( $general_settings['mwb_wpr_general_refer_value_disable'] ) ? intval( $general_settings['mwb_wpr_general_refer_value_disable'] ) : 0;
$mwb_user_point_expiry = get_user_meta( $user_id, 'mwb_wpr_points_expiration_date', true );
/* End the memebership settings*/
$get_referral = get_user_meta( $user_id, 'mwb_points_referral', true );
$get_referral_invite = get_user_meta( $user_id, 'mwb_points_referral_invite', true );
if ( ! is_array( $coupon_settings ) ) {
	$coupon_settings = array();
}
?>
<div class="mwb_wpr_points_wrapper_with_exp">
	<div class="mwb_wpr_points_only"><p class="mwb_wpr_heading_para" >
		<span class="mwb_wpr_heading"><?php echo esc_html( $mwb_text_points_value ) . ':'; ?></span></p>
		<?php
		$get_points = (int) get_user_meta( $user_id, 'mwb_wpr_points', true );
		$get_point = get_user_meta( $user_id, 'points_details', true );
		?>
		<span class="mwb_wpr_heading" id="mwb_wpr_points_only">
			<?php
			echo ( isset( $get_points ) && $get_points != null ) ? esc_html( $get_points ) : 0;//phpcs:ignore WordPress.PHP.YodaConditions.NotYoda
			?>
		</span>
	</div>
	<?php

	if ( isset( $mwb_user_point_expiry ) && ! empty( $mwb_user_point_expiry ) && $get_points > 0 ) {
		$mwb_wpr_points_exp_onmyaccount = get_option( 'mwb_wpr_points_exp_onmyaccount', 'off' );
		if ( 'on' == $mwb_wpr_points_exp_onmyaccount ) {
			$date_format = get_option( 'date_format' );
			$expiry_date_timestamp = strtotime( $mwb_user_point_expiry );
			$expirydate_format = date_i18n( $date_format, $expiry_date_timestamp );
			echo '<p class=mwb_wpr_points_expiry> ' . esc_html_e( 'Get Expired: ', 'rewardeem-woocommerce-points-rewards' ) . $expirydate_format . '</p>';// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	?>
</div>		
	<span class="mwb_wpr_view_log">
		<a href="
		<?php
		echo esc_url( get_permalink() . 'view-log/' . $user_id );
		?>
		">
		<?php
		esc_html_e( 'View Point Log', 'rewardeem-woocommerce-points-rewards' );
		?>
		</a>
	</span>
<?php
if ( isset( $mwb_ways_to_gain_points_value ) && ! empty( $mwb_ways_to_gain_points_value ) ) {
	?>
	<div class ="mwb_ways_to_gain_points_section">
	<p class="mwb_wpr_heading"><?php echo esc_html__( 'Ways to gain more points:', 'rewardeem-woocommerce-points-rewards' ); ?></p>
			<?php
				$mwb_ways_to_gain_points_value = str_replace( '[Comment Points]', $mwb_comment_value, $mwb_ways_to_gain_points_value );
				$mwb_ways_to_gain_points_value = str_replace( '[Refer Points]', $mwb_refer_value, $mwb_ways_to_gain_points_value );
				$mwb_ways_to_gain_points_value = str_replace( '[Per Currency Spent Points]', $mwb_per_currency_spent_points, $mwb_ways_to_gain_points_value );
				$mwb_ways_to_gain_points_value = str_replace( '[Per Currency Spent Price]', $mwb_per_currency_spent_price, $mwb_ways_to_gain_points_value );
				 echo '<fieldset class="mwb_wpr_each_section">' . esc_html( $mwb_ways_to_gain_points_value ) . '</fieldset>';
			?>
		
	</div>
	<?php
}
if ( $mwb_wpr_mem_enable ) {
	$enable_drop = false;
	$mwb_wpr_membership_roles = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();
	?>
		
	<p class="mwb_wpr_heading"><?php esc_html_e( 'Membership List', 'rewardeem-woocommerce-points-rewards' ); ?></p>
		<?php
		if ( isset( $user_level ) && ! empty( $user_level ) ) {
			?>
			<span class="mwb_wpr_upgrade_level">
			<?php
			esc_html_e( 'Your level has been upgraded to ', 'rewardeem-woocommerce-points-rewards' );
			echo esc_html( $user_level );
			?>
			</span>
			<?php
		}
		?>
			
			<table class="woocommerce-MyAccount-points shop_table my_account_points account-points-table mwb_wpr_membership_with_img">
				<thead>
					<tr>
						<th class="points-points">
							<span class="nobr"><?php echo esc_html__( 'Level', 'rewardeem-woocommerce-points-rewards' ); ?></span>
						</th>
						<th class="points-code">
							<span class="nobr"><?php echo esc_html__( 'Required Points', 'rewardeem-woocommerce-points-rewards' ); ?></span>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
				if ( is_array( $mwb_wpr_membership_roles ) && ! empty( $mwb_wpr_membership_roles ) ) {
					foreach ( $mwb_wpr_membership_roles as $role => $values ) {//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						?>
				<tr>
					<td>
						<?php
							echo esc_html( $role ) . '<br/><a class = "mwb_wpr_level_benefits" data-id = "' . esc_attr_e( $role ) . '" href="javascript:;">' . esc_html__( 'View Benefits', 'rewardeem-woocommerce-points-rewards' ) . '</a>'; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.WP.I18n.NonSingularStringLiteralText
						?>
						</td>
						<div class="mwb_wpr_popup_wrapper" style="display: none;" id="mwb_wpr_popup_wrapper_<?php echo esc_html( $role ); ?>">
							<div class="mwb_wpr_popup_content_section">
								<div class="mwb_wpr_popup_content">
									<div class="mwb_wpr_popup_notice_section">					
										<p>
											<span class="mwb_wpr_intro_text">
											<?php
											esc_html_e( 'You will get ', 'rewardeem-woocommerce-points-rewards' );
											echo esc_html( $values['Discount'] );
											esc_html_e( '% discount on below products or categories', 'rewardeem-woocommerce-points-rewards' );
											?>
											</span>
											<span class="mwb_wpr_close">
												<a href="javascript:;"><img src="<?php echo esc_url( MWB_RWPR_DIR_URL ); ?>public/images/cancel.png" alt=""></a>
											</span>
										</p>
									</div>
									<div class="mwb_wpr_popup_thumbnail_section">
										<ul>
										<?php
										if ( is_array( $values['Product'] ) && ! empty( $values['Product'] ) ) {
											foreach ( $values['Product'] as $key => $pro_id ) {
												$pro_img = wp_get_attachment_image_src( get_post_thumbnail_id( $pro_id ), 'single-post-thumbnail' );
												$_product = wc_get_product( $pro_id );
												$price = $_product->get_price();
												$product_name = $_product->get_title();
												$pro_url = get_permalink( $pro_id );
												if ( empty( $pro_img[0] ) ) {
														$pro_img[0] = MWB_WPR_URL . '/assets/images/placeholder.png';
												}
												?>
												<li>
													<a href="<?php echo esc_url( $pro_url ); ?>">
														<span class="mwb_wpr_thumbnail_img_wrap"><img src="<?php echo esc_url( $pro_img[0] ); ?>" alt=""></span>
														<span class="mwb_wpr_thumbnail_product_name"><?php echo esc_html( $product_name ); ?></span>
														<span class="mwb_wpr_thumbnail_price_wrap"><?php echo wc_price( $price ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
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
												<div class="mwb_wpr_popup_cat">

													<?php
													foreach ( $values['Prod_Categ'] as $key => $cat_id ) {//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
														if ( WC()->version < '3.6.0' ) {

															$thumbnail_id = get_woocommerce_term_meta( $cat_id, 'thumbnail_id', true );
														} else {
															$thumbnail_id = get_term_meta( $cat_id, 'thumbnail_id', true );
														}
														$cat_img = wp_get_attachment_url( $thumbnail_id );
														$category_title = get_term( $cat_id, 'product_cat' );
														$category_link = get_category_link( $cat_id );
														if ( empty( $cat_img ) ) {
															$cat_img = MWB_RWPR_DIR_URL . 'public/images/placeholder.png';
														}
														?>
															<div class="mwb_wpr_cat_wrapper">
																<img src="<?php echo esc_url( $cat_img ); ?>" alt="" style="height: 100px;width: 100px;">
																<a href="<?php echo esc_url( $category_link ); ?>" class="mwb_wpr_cat_list"><?php echo esc_html( $category_title->name ); ?></a>
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
						if ( $role == $user_level ) {
							echo '<img class="mwb_wpr_tick" src = "' . esc_url( MWB_RWPR_DIR_URL ) . 'public/images/tick.png">';
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
	if ( isset( $user_level ) && ! empty( $user_level ) && array_key_exists( $user_level, $mwb_wpr_membership_roles ) ) {
		unset( $mwb_wpr_membership_roles[ $user_level ] );
	}
	if ( ! empty( $mwb_wpr_membership_roles ) && is_array( $mwb_wpr_membership_roles ) ) {
		?>
			<p class="mwb_wpr_heading"><?php echo esc_html_e( 'Upgrade User Level', 'rewardeem-woocommerce-points-rewards' ); ?></p>
			<fieldset class="mwb_wpr_each_section">	
				<span class="mwb_wpr_membership_message"><?php echo esc_html_e( 'Upgrade Your User Level: ', 'rewardeem-woocommerce-points-rewards' ); ?></span>
				<form action="" method="post" id="mwb_wpr_membership">
					<select id="mwb_wpr_membership_roles" class="mwb_wpr_membership_roles" name="mwb_wpr_membership_roles">
						<option><?php echo esc_html__( 'Select Level', 'rewardeem-woocommerce-points-rewards' ); ?></option>
					<?php
					foreach ( $mwb_wpr_membership_roles as $role => $values ) { //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						if ( $values['Points'] == $get_points
							|| $values['Points'] < $get_points ) {
							?>
										
									<option value="<?php echo esc_html( $role ); ?>">
									<?php
									echo esc_html( $role );
									?>
									</option>
									<?php
						}
					}
					?>
						</select>
						<input style="display:none;"type="submit" id = "mwb_wpr_upgrade_level" value='<?php esc_html_e( 'Upgrade Level', 'rewardeem-woocommerce-points-rewards' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_level">
						<input type="button" id = "mwb_wpr_upgrade_level_click" value='<?php esc_html_e( 'Upgrade Level', 'rewardeem-woocommerce-points-rewards' ); ?>' class="button-primary woocommerce-save-button mwb_wpr_save_changes" name="mwb_wpr_save_level_click">
				</form>
			</fieldset>	
		<?php
	}
}

do_action( 'mwb_wpr_add_coupon_generation', $user_id );
?>
<br>
<?php
do_action( 'mwb_wpr_list_coupons_generation', $user_id );

/*Start of the Referral Section*/
if ( $enable_mwb_refer ) {
	$public_obj = new Rewardeem_woocommerce_Points_Rewards_Public( 'rewardeem-woocommerce-points-rewards', '1.0.0' );
	$public_obj->mwb_wpr_get_referral_section( $user_id );
}
/* of the Referral Section*/
do_action( 'mwb_wpr_add_share_points', $user_id );
$mwb_wpr_user_can_send_point = get_option( 'mwb_wpr_user_can_send_point', 0 );
?>
	
</div>
