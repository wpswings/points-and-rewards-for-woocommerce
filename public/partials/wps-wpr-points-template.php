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
$user_id                             = get_current_user_id();
$my_role                             = ! empty( get_user_meta( $user_id, 'membership_level', true ) ) ? get_user_meta( $user_id, 'membership_level', true ) : '';
$get_points                          = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
$get_points                          = ! empty( $get_points ) ? $get_points : 0;
$wps_wpr_overall__accumulated_points = get_user_meta( $user_id, 'wps_wpr_overall__accumulated_points', true );
$wps_wpr_overall__accumulated_points = ! empty( $wps_wpr_overall__accumulated_points ) ? $wps_wpr_overall__accumulated_points : 0;

// get badges setting here.
$wps_wpr_user_badges_setting     = get_option( 'wps_wpr_user_badges_setting', array() );
$wps_wpr_show_accumulated_points = ! empty( $wps_wpr_user_badges_setting['wps_wpr_show_accumulated_points'] ) ? $wps_wpr_user_badges_setting['wps_wpr_show_accumulated_points'] : 'no';

if ( isset( $_POST['wps_wpr_save_level'] ) && isset( $_POST['membership-save-level'] ) && isset( $_POST['wps_wpr_membership_roles'] ) && sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_roles'] ) ) != $my_role ) {
	$wps_wpr_nonce = sanitize_text_field( wp_unslash( $_POST['membership-save-level'] ) );

	if ( wp_verify_nonce( $wps_wpr_nonce, 'membership-save-level' ) ) {
		$selected_role             = isset( $_POST['wps_wpr_membership_roles'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_roles'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification
		$user                      = get_user_by( 'ID', $user_id );
		$membership_detail         = get_user_meta( $user_id, 'points_details', true );
		$membership_detail         = ! empty( $membership_detail ) && is_array( $membership_detail ) ? $membership_detail : array();
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
				// send sms.
				wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your membership has been upgraded, and %1$s points have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $values['Points'], $remaining_points ) );
				// send messages on whatsapp.
				wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: whatsapp msg */ sprintf( esc_html__( 'Your membership has been upgraded, and %1$s points have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $values['Points'], $remaining_points ) );
				/*Send mail*/
				$user              = get_user_by( 'ID', $user_id );
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

/* Get points of the User*/
$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
/* Get points of the Membership Level*/
$wps_user_level = get_user_meta( $user_id, 'membership_level', true );

/* Get the General Settings*/
$general_settings              = get_option( 'wps_wpr_settings_gallery', true );
$enable_wps_refer              = isset( $general_settings['wps_wpr_general_refer_enable'] ) ? intval( $general_settings['wps_wpr_general_refer_enable'] ) : 0;
$wps_refer_value               = isset( $general_settings['wps_wpr_general_refer_value'] ) ? intval( $general_settings['wps_wpr_general_refer_value'] ) : 1;
$wps_text_points_value         = isset( $general_settings['wps_wpr_general_text_points'] ) ? $general_settings['wps_wpr_general_text_points'] : esc_html__( 'My Points', 'points-and-rewards-for-woocommerce' );
$wps_ways_to_gain_points_value = ! empty( $general_settings['wps_wpr_general_ways_to_gain_points'] ) ? $general_settings['wps_wpr_general_ways_to_gain_points'] : esc_html__( '[Refer Points] for Referral Points[Per Currency Spent Points] for Per currency spent points and[Per Currency Spent Price] for per currency spent price', 'points-and-rewards-for-woocommerce' );
// End Section of the Setings.

// Get the General Settings.
$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
$wps_wpr_mem_enable        = isset( $membership_settings_array['wps_wpr_membership_setting_enable'] ) ? intval( $membership_settings_array['wps_wpr_membership_setting_enable'] ) : 0;
$coupon_settings           = get_option( 'wps_wpr_coupons_gallery', true );
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
<div class="wps_wpr_badge_way_points_main_wrap">
	<?php do_action( 'wps_wpr_top_account_page_section_hook', $user_id ); ?>

	<div class="wps_wpr_points_wrapper_with_exp">
		<div class="wps_wpr_points_only wps_wpr_show_points_on_account_page">
			<div class="wps_wpr_heading_para">
				<span class="wps_wpr_heading"><?php echo esc_html( $wps_text_points_value ) . ':'; ?></span>
				<span class="wps_wpr_total_earn_points"><?php echo esc_html( number_format( $get_points ) ); ?></span>
			</div>
			<?php
			// Show overall earning points.
			if ( 'yes' === $wps_wpr_show_accumulated_points ) {
				?>
				<div class="wps_wpr_heading_para">
					<span class="wps_wpr_heading"><?php esc_html_e( 'Overall Points : ', 'points-and-rewards-for-woocommerce' ); ?></span>
					<span class="wps_wpr_total_earn_points"><?php echo esc_html( number_format( $wps_wpr_overall__accumulated_points ) ); ?></span>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		if ( ! empty( $wps_user_point_expiry ) && $get_points > 0 ) {

			$expiration_settings            = get_option( 'wps_wpr_points_expiration_settings', true );
			$expiration_settings            = ! empty( $expiration_settings ) && is_array( $expiration_settings ) ? $expiration_settings : array();
			$wps_wpr_points_exp_onmyaccount = ! empty( $expiration_settings['wps_wpr_points_exp_onmyaccount'] ) ? $expiration_settings['wps_wpr_points_exp_onmyaccount'] : 0;
			if ( 1 === $wps_wpr_points_exp_onmyaccount ) {

				$date_format       = get_option( 'date_format' );
				$expirydate_format = date_i18n( $date_format, strtotime( $wps_user_point_expiry ) );
				?>
				<div class="wps_wpr_points_expiry"><?php echo esc_html__( 'Expiring On : ', 'points-and-rewards-for-woocommerce' ) . esc_html( $expirydate_format ); ?></div>
				<?php
			}
		}
		?>

	<?php
	if ( isset( $wps_ways_to_gain_points_value ) && ! empty( $wps_ways_to_gain_points_value ) ) {
		?>
			<span class="wps_wpr_view_log">
				<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-log' ) ); ?>"><?php esc_html_e( 'View Point Log', 'points-and-rewards-for-woocommerce' ); ?></a>
			</span>
		</div>
	</div>
		<div class ="wps_ways_to_gain_points_section">
			<p class="wps_wpr_heading"><?php echo esc_html__( 'Ways to gain more points:', 'points-and-rewards-for-woocommerce' ); ?>
			</p>
			<div class="wps_wpr_each_section_wrap">
				<?php
				$wps_ways_to_gain_points_value = str_replace( '[Comment Points]', $wps_comment_value, $wps_ways_to_gain_points_value );
				$wps_ways_to_gain_points_value = str_replace( '[Refer Points]', $wps_refer_value, $wps_ways_to_gain_points_value );
				$wps_ways_to_gain_points_value = str_replace( '[Per Currency Spent Points]', $wps_per_currency_spent_points, $wps_ways_to_gain_points_value );
				$wps_ways_to_gain_points_value = str_replace( '[Per Currency Spent Price]', $wps_per_currency_spent_price, $wps_ways_to_gain_points_value );
				echo '<fieldset class="wps_wpr_each_section">' . wp_kses_post( $wps_ways_to_gain_points_value ) . '</fieldset>';
				?>
			</div>
		</div>
		<?php
	}

	if ( $wps_wpr_mem_enable ) {
		$enable_drop              = false;
		$wps_wpr_membership_roles = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();
		?>
	<div class="wps_wpr_membership_list_main_wrap wps_wpr_main_section_all_wrap">
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
		<div class="wps_wpr_membership_with_img-wrap">
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
						if ( ! wps_wpr_is_par_pro_plugin_active() ) {
							?>
							<th class="wps-wpr-points-expiry">
								<span class="wps_wpr_nobr"><?php echo esc_html__( 'Membership Expiry', 'points-and-rewards-for-woocommerce' ); ?></span>
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

								$enable_mem_reward_points = ! empty( $values['enable_mem_reward_points'] ) ? $values['enable_mem_reward_points'] : 0;
								$assign_mem_points_type   = ! empty( $values['assign_mem_points_type'] ) ? $values['assign_mem_points_type'] : 'fixed';
								$mem_rewards_points_val   = ! empty( $values['mem_rewards_points_val'] ) ? $values['mem_rewards_points_val'] : 0;
								$wps_member_name          = strtolower( str_replace( ' ', '_', $wps_role ) );
								$discount_value           = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
								echo esc_html( $wps_role ) . '<br/><a class = "wps_wpr_level_benefits" data-id = "' . esc_html( $wps_member_name ) . '" href="javascript:;">' . esc_html__( 'View Benefits', 'points-and-rewards-for-woocommerce' ) . '</a>'; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited,WordPress.WP.I18n.NonSingularStringLiteralText
								?>
								</td>
								<div class="wps_wpr_popup_wrapper wps_rwpr_settings_display_none" id="wps_wpr_popup_wrapper_<?php echo esc_html( $wps_member_name ); ?>">
									<div class="wps_wpr_popup_content_section">
										<div class="wps_wpr_popup_content">
											<div class="wps_wpr_popup_notice_section">
												<?php
												if ( $discount_value > 0 ) {
													?>
																									<p>
														<span class="wps_wpr_intro_text">
														<?php
														esc_html_e( 'You will get ', 'points-and-rewards-for-woocommerce' );
														echo esc_html( $discount_value );
														esc_html_e( '% discount on below products or categories', 'points-and-rewards-for-woocommerce' );
														?>
														</span>
													</p>
													<?php
												} else {

													?>
													<p>
														<span class="wps_wpr_intro_text"><?php echo esc_html( ucfirst( $wps_member_name ) ); ?></span>
													</p>
													<?php
												}
												?>
												<span class="wps_wpr_close">
													<a href="javascript:;"><img src="<?php echo esc_url( WPS_RWPR_DIR_URL ); ?>public/images/cancel.png" alt=""></a>
												</span>
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
													} elseif ( is_array( $values['Prod_Categ'] ) && ! empty( $values['Prod_Categ'] ) ) {
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
													?>
											</div>
											<?php
											if ( 1 == $enable_mem_reward_points ) {
												if ( 'percent' === $assign_mem_points_type ) {

													?>
													<p class="wps_wpr_mems_rewards">
														<span class="wps_wpr_intro_text">
															<?php
															/* translators: %s: list of percent wise points rewards */
															printf( esc_html__( 'As a %1$s member, you will earn an additional %2$s of your order total as bonus points as a reward!', 'points-and-rewards-for-woocommerce' ), esc_html( ucfirst( $wps_member_name ) ), esc_html( $mem_rewards_points_val . '%' ) );
															?>
														</span>
													</p>
													<?php
												} else {
													?>
													<p class="wps_wpr_mems_rewards">
														<span class="wps_wpr_intro_text">
															<?php
															/* translators: %s: list of fixed points rewards */
															printf( esc_html__( 'As a %1$s member, you will earn an additional %2$s bonus points as a reward!', 'points-and-rewards-for-woocommerce' ), esc_html( ucfirst( $wps_member_name ) ), esc_html( $mem_rewards_points_val ) );
															?>
														</span>
													</p>
													<?php
												}
											}
											?>
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
									if ( ! wps_wpr_is_par_pro_plugin_active() ) {
										echo esc_html( $values['Exp_Number'] ) . ' ' . esc_html( $values['Exp_Days'] );
									}
									do_action( 'wps_wpr_membership_expiry_date_for_user', $user_id, $values, $wps_role );
									if ( $wps_role == $wps_user_level ) {

										$wps_wpr_others_settings          = get_option( 'wps_wpr_other_settings', array() );
										$wps_wpr_others_settings          = ! empty( $wps_wpr_others_settings ) && is_array( $wps_wpr_others_settings ) ? $wps_wpr_others_settings : array();
										$wps_wpr_choose_account_page_temp = ! empty( $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] ) ? $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] : 0;
										if ( 'temp_two' === $wps_wpr_choose_account_page_temp ) {
											echo '<img class="wps_wpr_tick" src = "' . esc_url( WPS_RWPR_DIR_URL ) . 'public/images/tick2.svg">';
										} else {

											echo '<img class="wps_wpr_tick" src = "' . esc_url( WPS_RWPR_DIR_URL ) . 'public/images/tick.png">';
										}
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
		</div>
	</div>
		<?php
	}

	// check pro plugin is not and auto value is true, than make it false.
	$wps_wpr_enable_automate_membership = ! empty( $membership_settings_array['wps_wpr_enable_automate_membership'] ) ? $membership_settings_array['wps_wpr_enable_automate_membership'] : '';
	if ( ! wps_wpr_is_par_pro_plugin_active() && '1' == $wps_wpr_enable_automate_membership ) {

		$wps_wpr_enable_automate_membership = 0;
	}

	// check auto membership upgrade is enable than no need to show manual upgrade option.
	if ( 1 != $wps_wpr_enable_automate_membership && ( isset( $enable_drop ) && $enable_drop ) ) {
		if ( isset( $wps_user_level ) && ! empty( $wps_user_level ) && array_key_exists( $wps_user_level, $wps_wpr_membership_roles ) ) {

			$mem_expire_time = get_user_meta( $user_id, 'membership_expiration', true );
			if ( $mem_expire_time > gmdate( 'Y-m-d' ) ) {

				unset( $wps_wpr_membership_roles[ $wps_user_level ] );
			}
		}
		if ( ! empty( $wps_wpr_membership_roles ) && is_array( $wps_wpr_membership_roles ) ) {
			?>
			<div class="wps_wpr_upgrade_level_main_wrap wps_wpr_main_section_all_wrap">
				<p class="wps_wpr_heading wps_wpr_membrship_update_heading"><?php echo esc_html_e( 'Upgrade User Level', 'points-and-rewards-for-woocommerce' ); ?></p>
				<fieldset class="wps_wpr_each_section wps_wpr_membership_listing_class">	
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
			</div>
			<?php
		}
	}

	do_action( 'wps_wpr_add_coupon_generation', $user_id );

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
