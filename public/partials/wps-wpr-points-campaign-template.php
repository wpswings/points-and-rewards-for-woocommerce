<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    wps-wpr-points-log-template
 * @subpackage points-and-rewards-for-wooCommerce/public/partials
 */

// feature settings.
$user_id                                = get_current_user_id();
$wps_wpr_campaign_settings              = get_option( 'wps_wpr_campaign_settings', array() );
$wps_wpr_campaign_settings              = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
$wps_wpr_enable_sign_up_campaign        = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_sign_up_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_sign_up_campaign'] : '';
$wps_wpr_enable_referral_campaign       = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_referral_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_referral_campaign'] : '';
$wps_wpr_enable_comments_campaign       = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_comments_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_comments_campaign'] : '';
$wps_wpr_enable_birthday_campaign       = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_birthday_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_birthday_campaign'] : '';
$wps_wpr_enable_gami_points             = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_gami_points'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_gami_points'] : '';
$wps_wpr_enable_camp_first_order_points = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_camp_first_order_points'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_camp_first_order_points'] : '';
$wps_wpr_enable_quiz_contest_campaign   = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_quiz_contest_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_quiz_contest_campaign'] : '';
$wps_wpr_quiz_question                  = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_question'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_question'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_question'] : array();
$wps_wpr_quiz_option_one                = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_one'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_one'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_one'] : array();
$wps_wpr_quiz_option_two                = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_two'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_two'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_two'] : array();
$wps_wpr_quiz_option_three              = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_three'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_three'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_three'] : array();
$wps_wpr_quiz_option_four               = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_option_four'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_option_four'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_option_four'] : array();
$wps_wpr_quiz_answer                    = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_answer'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_answer'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_answer'] : array();
$wps_wpr_quiz_rewards_points            = ! empty( $wps_wpr_campaign_settings['wps_wpr_quiz_rewards_points'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_quiz_rewards_points'] ) ? $wps_wpr_campaign_settings['wps_wpr_quiz_rewards_points'] : array();
$wps_wpr_enter_campaign_heading         = ! empty( $wps_wpr_campaign_settings['wps_wpr_enter_campaign_heading'] ) ? $wps_wpr_campaign_settings['wps_wpr_enter_campaign_heading'] : 'Points and Rewards Program';
$wps_wpr_enter_campaign_image_url       = ! empty( $wps_wpr_campaign_settings['wps_wpr_enter_campaign_image_url'] ) ? $wps_wpr_campaign_settings['wps_wpr_enter_campaign_image_url'] : 'https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/wp-content/uploads/2025/08/reward.webp';
$wps_wpr_show_current_points_modal      = ! empty( $wps_wpr_campaign_settings['wps_wpr_show_current_points_modal'] ) ? $wps_wpr_campaign_settings['wps_wpr_show_current_points_modal'] : '';
$wps_wpr_show_total_referral_count      = ! empty( $wps_wpr_campaign_settings['wps_wpr_show_total_referral_count'] ) ? $wps_wpr_campaign_settings['wps_wpr_show_total_referral_count'] : '';
$wps_wpr_show_content_in_footer         = ! empty( $wps_wpr_campaign_settings['wps_wpr_show_content_in_footer'] ) ? $wps_wpr_campaign_settings['wps_wpr_show_content_in_footer'] : '';
$wps_wpr_modal_footer_content           = ! empty( $wps_wpr_campaign_settings['wps_wpr_modal_footer_content'] ) ? $wps_wpr_campaign_settings['wps_wpr_modal_footer_content'] : 'Created with ❤ by WP Swings';
$icons_url                              = plugin_dir_url( __DIR__ ) . 'images/trophy.png';

// get points and referral count.
$get_points                   = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : 0;
$wps_wpr_total_referral_count = ! empty( get_user_meta( $user_id, 'wps_wpr_total_referral_count', true ) ) ? get_user_meta( $user_id, 'wps_wpr_total_referral_count', true ) : 0;

// Get the cart point rate.
$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;

// Get the cart price rate.
$wps_wpr_cart_price_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;

// comment / review points value.
$wps_wpr_comment_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_comment_value' );

// signup value.
$wps_signup_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup_value' );

// get birthday points.
$birthday_points = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_birthday_value' );

// get birthday points.
$_my_bday = get_user_meta( get_current_user_id(), '_my_bday', true );

// first order points.
$wps_first_order_points = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_first_value' );

// gamification settings.
$wps_wpr_save_gami_setting    = get_option( 'wps_wpr_save_gami_setting', array() );
$wps_wpr_save_gami_setting    = ! empty( $wps_wpr_save_gami_setting ) && is_array( $wps_wpr_save_gami_setting ) ? $wps_wpr_save_gami_setting : array();
$wps_wpr_enter_segment_points = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_points'] ) && is_array( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_points'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_points'] : array();

// win wheel points assign check.
$wps_wpr_check_game_points_assign_timing = get_user_meta( $user_id, 'wps_wpr_check_game_points_assign_timing', true );
?>
<div class="wps-wpr">

	<!-- Modal 1 -->
	<div class="wps-wpr-campaign-modal-wrap">
		<div class="wps-wpr-hlw-shadow"></div>
		<div id="wps-wpr-campaign-modal" class="wps-wpr-hlw">
			<div class="wps-wpr-campaign-modal-in">

				<!-- Campaign Modal heading  -->
				<div class="wps_wpr_campaign_heading"><?php echo esc_html( $wps_wpr_enter_campaign_heading ); ?></div>
				<p class="wps-wpr-hlw_close">&times;</p>

				<!-- Campaign Modal banner image  -->
				<div class="wps-wpr-hlw_container" id="container">
					<img src="<?php echo esc_url( $wps_wpr_enter_campaign_image_url ); ?>" alt="Halloween Image" />

					<!-- Per currency earn msg  -->
					<p class="wps-wpr-hlw_co-p">
						<?php
						printf(
							/* translators: 1: Points, 2: Currency + Amount */
							esc_html__( 'Get %1$s points for every %2$s you spend!', 'points-and-rewards-for-woocommerce' ),
							esc_html( $wps_wpr_cart_points_rate ),
							esc_html( get_woocommerce_currency_symbol() . $wps_wpr_cart_price_rate )
						);
						?>
					</p>
					<div class="wps-wpr-hlw_co-buttons">

						<!-- Earn and Referral buttons -->
							<div class="wps-wpr_earn-ref-btn">
							<button id="wps_wpr_campaign_earn_btn" class="wps_wpr_campaign_earn_btn wps_wpr_campaign_btn"><?php esc_html_e( 'Earn', 'points-and-rewards-for-woocommerce' ); ?></button>
							<button id="wps_wpr_campaign_referral_btn" class="wps_wpr_campaign_referral_btn wps_wpr_campaign_btn wps_wpr_campaign_btn--active"><?php esc_html_e( 'Referral', 'points-and-rewards-for-woocommerce' ); ?></button>
						</div>
						<!-- Referral settings -->
						<div id="wps_wpr_campaign_referral_wrap" class="wps_wpr_campaign_promo">
							<div class="wps-wpr_campaign-h1"><?php esc_html_e( 'Refer friends. Earn rewards!', 'points-and-rewards-for-woocommerce' ); ?></div>
							<?php if ( is_user_logged_in() ) { ?>
								<?php
								if ( 'yes' === $wps_wpr_enable_referral_campaign ) :

									$this->wps_wpr_campaigns_html_referral( $user_id );
								endif;
								?>
								<div class="wps-wpr_co-items-wrap">
									<?php if ( 'yes' === $wps_wpr_show_total_referral_count ) : ?>
										<div class="wps-wpr-hlw_co-items-first wps-wpr_co-item">
											<div class="wps-wpr_campaign-h2"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php echo esc_html( $wps_wpr_total_referral_count ); ?></div>
											<h6><?php esc_html_e( 'Referral Count', 'points-and-rewards-for-woocommerce' ); ?></h6>
										</div>
									<?php endif; ?>
									<?php if ( 'yes' === $wps_wpr_show_current_points_modal ) : ?>
										<div class="wps-wpr-hlw_co-items-sec wps-wpr_co-item">
											<div class="wps-wpr_campaign-h2"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php echo esc_html( $get_points ); ?></div>
											<h6><?php esc_html_e( 'Total Rewards', 'points-and-rewards-for-woocommerce' ); ?></h6>
										</div>
									<?php endif; ?>
								</div>
								<?php
							} else {
								?>
								<div class="wps-wpr_camp-guest-part">
									<div class="wps-wpr_camp-guest-part-link">
										<a class="wps_wpr_campaign_login" data-url="<?php echo esc_url( home_url( add_query_arg( null, null ) ) ); ?>" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Create Account', 'points-and-rewards-for-woocommerce' ); ?></a>
										<p><?php esc_html_e( 'Already have an account?', 'points-and-rewards-for-woocommerce' ); ?> <a class="wps_wpr_campaign_login" data-url="<?php echo esc_url( home_url( add_query_arg( null, null ) ) ); ?>" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Login', 'points-and-rewards-for-woocommerce' ); ?></a></p>
									</div>
									<div class="wps-wpr_campaign-h1"><?php printf( /* translators: %s: sms msg */ esc_html__( 'Earn %s welcome points!', 'points-and-rewards-for-woocommerce' ), esc_html( $wps_signup_value ) ); ?></div>
									<div class="wps-wpr_campaign-p"><?php esc_html_e( 'Invite your friends and get rewards when they make a purchase.', 'points-and-rewards-for-woocommerce' ); ?></div>
								</div>
								<?php
							}
							?>
						</div>
					</div>

						<!-- // Earn settings  -->
					<div class="wps-wpr-hlw_co-buttons" style="display: none;">
						<div id="wps_wpr_campaign_earn_wrap" class="wps_wpr_campaign_promo">
							<?php
							if ( ! is_user_logged_in() ) {
								if ( 'yes' === $wps_wpr_enable_sign_up_campaign ) :
									?>
									<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php esc_html_e( 'Earn welcome points!', 'points-and-rewards-for-woocommerce' ); ?> <span class="wps-wpr_camp-h2-icon"><?php echo esc_html( $wps_signup_value ); ?>+</span></div>
								<?php endif; ?>
								<div class="wps-wpr_camp-guest-part">
									<div class="wps-wpr_camp-guest-part-link">
										<a class="wps_wpr_campaign_login-btn" data-url="<?php echo esc_url( home_url( add_query_arg( null, null ) ) ); ?>" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Create Account', 'points-and-rewards-for-woocommerce' ); ?></a>
										<p><?php esc_html_e( 'Already have an account?', 'points-and-rewards-for-woocommerce' ); ?> <a class="wps_wpr_campaign_login" data-url="<?php echo esc_url( home_url( add_query_arg( null, null ) ) ); ?>" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Login', 'points-and-rewards-for-woocommerce' ); ?></a></p>
									</div>
								</div>
								<?php
							}
							?>
							<?php if ( 'yes' === $wps_wpr_enable_birthday_campaign && empty( $_my_bday ) ) { ?>
								<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php esc_html_e( 'Enter your birthday', 'points-and-rewards-for-woocommerce' ); ?> <span class="wps-wpr_camp-h2-icon"><?php echo esc_html( $birthday_points ); ?>+</span></div>
								<div class="wps-wpr_camp-acc-wrap wps_wpr_guest_user_disable">
									<div class="wps-wpr_camp-birth">
										<input type="date" data-date-format="DD/MM/YYYY" class="" name="account_bday" id="account_bday" value="<?php echo esc_html( ! empty( $_my_bday ) ? $_my_bday : '' ); ?>" <?php echo esc_html( ! empty( $_my_bday ) ? 'disabled' : '' ); ?> placeholder="DD/MM/YYYY" />
										<input type="button" name="wps_wpr_campaign_save_birthday" id="wps_wpr_campaign_save_birthday" value="<?php esc_html_e( 'Update', 'points-and-rewards-for-woocommerce' ); ?>" <?php echo esc_html( ! empty( $_my_bday ) ? 'disabled' : '' ); ?>>
										<img class="wps_wpr_birthday_loader wps_wpr_img_loader" src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/loading.gif' ); ?>">
									</div>
									<p class="wps_wpr_birthday_success_notice"></p>
								</div>
								<?php
							} else {
								?>
								<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php esc_html_e( 'Enter your birthday', 'points-and-rewards-for-woocommerce' ); ?> <span class="wps-wpr_camp-h2-icon-done"><?php echo esc_html( $birthday_points ); ?><img class="wps-wpr-hlw_co-icon" src="<?php echo esc_html( plugin_dir_url( __DIR__ ) . 'images/tick3.svg' ); ?>"></span></div>
								<?php
							}
							?>
							<?php if ( 'yes' === $wps_wpr_enable_comments_campaign ) : ?>
								<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable">
									<?php
										printf(
											'<img src="%s" alt="user" class="wps-wpr-hlw_co-icon" /><span> %s </span><a href="%s" target="_blank">%s</a>',
											esc_url( $icons_url ),
											sprintf( /* translators: %s: sms msg */ esc_html__( 'Comment or review any post and earn %d reward points.', 'points-and-rewards-for-woocommerce' ), (int) $wps_wpr_comment_value ),
											esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ),
											esc_html__( 'Click here', 'points-and-rewards-for-woocommerce' )
										);
									?>
								</div>
							<?php endif; ?>
							<?php if ( 'yes' === $wps_wpr_enable_gami_points ) : ?>
								<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable">
									<img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" />
									<?php
										printf(
										/* translators: 1: Points, 2: Currency + Amount */
											esc_html__(
												'Spin the wheel, try your luck, and earn exciting points between %1$s to %2$s',
												'points-and-rewards-for-woocommerce'
											),
											is_array( $wps_wpr_enter_segment_points ) && ! empty( $wps_wpr_enter_segment_points ) ? esc_html( min( $wps_wpr_enter_segment_points ) ) : 0,
											is_array( $wps_wpr_enter_segment_points ) && ! empty( $wps_wpr_enter_segment_points ) ? esc_html( max( $wps_wpr_enter_segment_points ) ) : 0
										);
									if ( empty( $wps_wpr_check_game_points_assign_timing ) ) {
										?>
										<span class="wps-wpr_camp-h2-icon">
											<a href="javascript:void(0);" class="wps_wpr_show_campaign_win_wheel_modal"><?php esc_html_e( 'Play', 'points-and-rewards-for-woocommerce' ); ?></a>
										</span>
										<?php
									} else {
										?>
										<span class="wps-wpr_camp-h2-icon">
											<img class="wps-wpr-hlw_co-icon" src="<?php echo esc_html( plugin_dir_url( __DIR__ ) . 'images/tick3.svg' ); ?>">
										</span>
										<?php
									}
									?>
								</div>
							<?php endif; ?>
							<?php if ( 'yes' === $wps_wpr_enable_camp_first_order_points ) : ?>
								<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php esc_html_e( 'Unlock bonus points when you shop for the very first time', 'points-and-rewards-for-woocommerce' ); ?> <span class="wps-wpr_camp-h2-icon"><?php echo esc_html( $wps_first_order_points ); ?>+</span></div>
							<?php endif; ?>
							<?php if ( 'yes' === $wps_wpr_enable_quiz_contest_campaign ) { ?>
								<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php printf( /* translators: %s: sms msg */ esc_html__( 'Play the Quiz, have fun, and earn %1$s–%2$s points!', 'points-and-rewards-for-woocommerce' ), esc_html( min( $wps_wpr_quiz_rewards_points ) ), esc_html( max( $wps_wpr_quiz_rewards_points ) ) ); ?> <span class="wps-wpr_camp-h2-icon">🎁 <?php echo esc_html( max( $wps_wpr_quiz_rewards_points ) ); ?></span></div>
								<div class="wps-wpr_camp-acc-wrap wps_wpr_guest_user_disable">
									<?php
									if ( ! empty( $wps_wpr_quiz_question ) && ! empty( $wps_wpr_quiz_option_one ) && ! empty( $wps_wpr_quiz_option_four ) ) {
										foreach ( $wps_wpr_quiz_question as $index => $quiz_question ) {

											$rewarded_key     = isset( $wps_wpr_quiz_answer[ $index ] ) ? 'wps_wpr_quiz_points_rewarded_' . $wps_wpr_quiz_answer[ $index ] : '';
											$already_rewarded = $rewarded_key ? get_user_meta( $user_id, $rewarded_key, true ) : false;
											if ( ! $already_rewarded ) :
												?>
												<div class="wps-wpr_campaign-quiz-wrap">
													<div class="wps-wpr_campaign-h3"><?php echo esc_html( $quiz_question ); ?></div>
													<div class="wps-wpr_campaign-quiz-opt">
														<?php
														$options = array( $wps_wpr_quiz_option_one[ $index ] ?? '', $wps_wpr_quiz_option_two[ $index ] ?? '', $wps_wpr_quiz_option_three[ $index ] ?? '', $wps_wpr_quiz_option_four[ $index ] ?? '' );
														foreach ( $options as $option ) :
															if ( ! empty( $option ) ) :
																?>
																<label><input type="radio" class="wps_wpr_quiz_option_ans" name="wps_wpr_quiz_option_ans_<?php echo esc_attr( $index ); ?>"	value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></label>
																<?php
															endif;
														endforeach;
														?>
													</div>
												</div>

												<div class="wps-wpr_cam-quiz-btn">
													<input type="button" class="wps_wpr_submit_quiz_ans" value="<?php esc_html_e( 'Submit', 'points-and-rewards-for-woocommerce' ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
													<img class="wps_wpr_quiz_loader wps_wpr_img_loader" src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/loading.gif' ); ?>">
												</div>
												<p class="wps_wpr_quiz_notice"></p>
											<?php else : ?>
												<div class="wps-wpr_campaign-h2">
													<?php echo esc_html( $quiz_question ); ?>
													<span class="wps-wpr_camp-h2-icon-done">
														<?php echo esc_html( $wps_wpr_quiz_rewards_points[ $index ] ?? 0 ); ?>
														<img class="wps-wpr-hlw_co-icon" src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/tick3.svg' ); ?>">
													</span>
												</div>
												<?php
											endif;
										}
									}
									?>
								</div>
								<?php
							} else {
								?>
								<div class="wps-wpr_campaign-h2 wps_wpr_guest_user_disable"><img src="<?php echo esc_url( $icons_url ); ?>" alt="user" class="wps-wpr-hlw_co-icon" /><?php esc_html_e( 'Play a Quiz', 'points-and-rewards-for-woocommerce' ); ?> <span class="wps-wpr_camp-h2-icon-done"><?php echo esc_html( $wps_wpr_quiz_rewards_points ); ?><img class="wps-wpr-hlw_co-icon" src="<?php echo esc_html( plugin_dir_url( __DIR__ ) . 'images/tick3.svg' ); ?>"></span></div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
				<!-- // Footer section data  -->
				<?php if ( wps_wpr_is_par_pro_plugin_active() ) { ?>
					<div class="wps-wpr-hlt_co-footer">
						<?php if ( 'yes' === $wps_wpr_show_content_in_footer ) : ?>
							<p><?php echo wp_kses_post( $wps_wpr_modal_footer_content ); ?></p>
						<?php endif; ?>
					</div>
				<?php } else { ?>
					<div class="wps-wpr-hlt_co-footer">
						<p><?php esc_html_e( 'Created with ❤ by WP Swings’', 'points-and-rewards-for-woocommerce' ); ?></p>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php

