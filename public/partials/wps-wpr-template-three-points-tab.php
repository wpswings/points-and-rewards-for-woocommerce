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

$user_id    = get_current_user_id();
$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
$my_role    = ! empty( get_user_meta( $user_id, 'membership_level', true ) ) ? get_user_meta( $user_id, 'membership_level', true ) : '';

// Validate POST and role mismatch.
$save_level_flag = ! empty( $_POST['wps_wpr_save_level'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_save_level'] ) ) : '';
$selected_role   = ! empty( $_POST['wps_wpr_membership_roles'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_roles'] ) ) : '';
$nonce           = ! empty( $_POST['membership-save-level'] ) ? sanitize_text_field( wp_unslash( $_POST['membership-save-level'] ) ) : '';
if ( $save_level_flag && $selected_role !== $my_role && wp_verify_nonce( $nonce, 'membership-save-level' ) ) {

	$user = get_user_by( 'ID', $user_id );
	if ( ! $user ) {
		return;
	}

	$membership_detail = get_user_meta( $user_id, 'points_details', true );
	$membership_detail = is_array( $membership_detail ) ? $membership_detail : array();

	$today_date      = date_i18n( 'Y-m-d h:i:sa', current_time( 'timestamp', false ) );
	$expiration_date = '';

	$membership_settings = get_option( 'wps_wpr_membership_settings', true );
	$membership_roles    = $membership_settings['membership_roles'] ?? array();

	if ( ! isset( $membership_roles[ $selected_role ] ) ) {
		return;
	}

	$role_data = $membership_roles[ $selected_role ];

	if ( $role_data['Points'] > $get_points ) {
		return; // Not enough points.
	}

	$points_required  = $role_data['Points'];
	$remaining_points = $get_points - $points_required;

	// Update points log.
	$this->wps_wpr_update_points_details( $user_id, 'membership', $points_required, array() );

	// Calculate expiration.
	if ( ! empty( $role_data['Exp_Number'] ) && ! empty( $role_data['Exp_Days'] ) ) {
		$expiration_date = date_i18n( 'Y-m-d', strtotime( "+{$role_data['Exp_Number']} {$role_data['Exp_Days']}", strtotime( $today_date ) ) );
	}

	// Save updated meta.
	update_user_meta( $user_id, 'wps_wpr_points', $remaining_points );
	update_user_meta( $user_id, 'membership_level', $selected_role );
	update_user_meta( $user_id, 'membership_expiration', $expiration_date );

	$message = sprintf(
		/* translators: %s: notice */
		esc_html__( 'Your membership has been upgraded, and %1$s points have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ),
		$points_required,
		$remaining_points
	);

	// Send notifications.
	wps_wpr_send_sms_org( $user_id, $message );
	wps_wpr_send_messages_on_whatsapp( $user_id, $message );

	// Email Notification.
	$shortcodes = array(
		'[USERLEVEL]' => $selected_role,
		'[USERNAME]'  => $user->user_login,
	);
	$email_content = array(
		'wps_wpr_subject' => 'wps_wpr_membership_email_subject',
		'wps_wpr_content' => 'wps_wpr_membership_email_discription_custom_id',
	);

	$this->wps_wpr_send_notification_mail_product( $user_id, $points_required, $shortcodes, $email_content );
}


// user specific data.
$get_points                          = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
$wps_user_level                      = get_user_meta( $user_id, 'membership_level', true );
$wps_wpr_overall__accumulated_points = get_user_meta( $user_id, 'wps_wpr_overall__accumulated_points', true );
$wps_wpr_overall__accumulated_points = ! empty( $wps_wpr_overall__accumulated_points ) ? $wps_wpr_overall__accumulated_points : 0;
$wps_user_point_expiry               = get_user_meta( $user_id, 'wps_wpr_points_expiration_date', true );
$wps_wpr_total_referral_count        = ! empty( get_user_meta( $user_id, 'wps_wpr_total_referral_count', true ) ) ? get_user_meta( $user_id, 'wps_wpr_total_referral_count', true ) : 0;
$wps_wpr_redeemed_points             = ! empty( get_user_meta( $user_id, 'wps_wpr_redeemed_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_redeemed_points', true ) : 0;

/* Get the General Settings*/
$general_settings              = get_option( 'wps_wpr_settings_gallery', true );
$enable_wps_refer              = isset( $general_settings['wps_wpr_general_refer_enable'] ) ? intval( $general_settings['wps_wpr_general_refer_enable'] ) : 0;
$wps_refer_value               = isset( $general_settings['wps_wpr_general_refer_value'] ) ? intval( $general_settings['wps_wpr_general_refer_value'] ) : 1;
$wps_text_points_value         = isset( $general_settings['wps_wpr_general_text_points'] ) ? $general_settings['wps_wpr_general_text_points'] : esc_html__( 'My Points', 'points-and-rewards-for-woocommerce' );
$wps_ways_to_gain_points_value = ! empty( $general_settings['wps_wpr_general_ways_to_gain_points'] ) ? $general_settings['wps_wpr_general_ways_to_gain_points'] : esc_html__( '[Refer Points] for Referral Points[Per Currency Spent Points] for Per currency spent points and[Per Currency Spent Price] for per currency spent price', 'points-and-rewards-for-woocommerce' );
$wps_comment_value             = isset( $general_settings['wps_comment_value'] ) ? intval( $general_settings['wps_comment_value'] ) : 1;

// get badges setting here.
$wps_wpr_user_badges_setting     = get_option( 'wps_wpr_user_badges_setting', array() );
$wps_wpr_show_accumulated_points = ! empty( $wps_wpr_user_badges_setting['wps_wpr_show_accumulated_points'] ) ? $wps_wpr_user_badges_setting['wps_wpr_show_accumulated_points'] : 'no';

// points expire settings.
$expiration_settings            = get_option( 'wps_wpr_points_expiration_settings', true );
$expiration_settings            = ! empty( $expiration_settings ) && is_array( $expiration_settings ) ? $expiration_settings : array();
$wps_wpr_points_exp_onmyaccount = ! empty( $expiration_settings['wps_wpr_points_exp_onmyaccount'] ) ? $expiration_settings['wps_wpr_points_exp_onmyaccount'] : 0;

// membership settings.
$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
$wps_wpr_mem_enable        = isset( $membership_settings_array['wps_wpr_membership_setting_enable'] ) ? intval( $membership_settings_array['wps_wpr_membership_setting_enable'] ) : 0;

$coupon_settings               = get_option( 'wps_wpr_coupons_gallery', true );
$wps_per_currency_spent_price  = isset( $coupon_settings['wps_wpr_coupon_conversion_price'] ) ? intval( $coupon_settings['wps_wpr_coupon_conversion_price'] ) : 1;
$wps_per_currency_spent_points = isset( $coupon_settings['wps_wpr_coupon_conversion_points'] ) ? intval( $coupon_settings['wps_wpr_coupon_conversion_points'] ) : 1;
?>
<div class="wps-par_ma-badge-wrap">

	<?php do_action( 'wps_wpr_top_account_page_section_hook', $user_id ); ?>
	<div class="wps_wpr_points_only wps_wpr_show_points_on_account_page">
		<!-- tooltip on hover -->
		<div class="wps-par_ma-tool-tip">
			<svg
			class="wps-par_matt-icon"
			xmlns="http://www.w3.org/2000/svg"
			width="22"
			height="22"
			viewBox="0 0 22 22"
			fill="none"
			>
			<path
				d="M11 21C16.5228 21 21 16.5228 21 11C21 5.47715 16.5228 1 11 1C5.47715 1 1 5.47715 1 11C1 16.5228 5.47715 21 11 21Z"
				stroke="#E0E0E0"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			<path
				d="M11 7V11"
				stroke="#E0E0E0"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			<path
				d="M11 15H11.01"
				stroke="#E0E0E0"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			</svg>
			<div class="wps-par_matt-desc">
			<h3><?php echo esc_html__( 'Ways to gain more points:', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<ul>
			<li>
				<fieldset>
				<?php
					$wps_ways_to_gain_points_value = str_replace( '[Comment Points]', $wps_comment_value, $wps_ways_to_gain_points_value );
					$wps_ways_to_gain_points_value = str_replace( '[Refer Points]', $wps_refer_value, $wps_ways_to_gain_points_value );
					$wps_ways_to_gain_points_value = str_replace( '[Per Currency Spent Points]', $wps_per_currency_spent_points, $wps_ways_to_gain_points_value );
					$wps_ways_to_gain_points_value = str_replace( '[Per Currency Spent Price]', $wps_per_currency_spent_price, $wps_ways_to_gain_points_value );
					echo wp_kses_post( $wps_ways_to_gain_points_value );
				?>
				</fieldset>
			</li>
			</ul>
			</div>
		</div>

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
		<!-- displaying points log -->
		<div class="wps_wpr_heading_para">
			<span class="wps_wpr_view_log">
				<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-log' ) ); ?>"><?php esc_html_e( 'View Point Log', 'points-and-rewards-for-woocommerce' ); ?></a>
			</span>
		</div>
		<?php
		// show points expiration time.
		if ( ! empty( $wps_user_point_expiry ) && $get_points > 0 ) {
			if ( 1 === $wps_wpr_points_exp_onmyaccount ) {

				$date_format       = get_option( 'date_format' );
				$expirydate_format = date_i18n( $date_format, strtotime( $wps_user_point_expiry ) );
				?>
					<div class="wps_wpr_points_expiry"><?php echo esc_html__( 'Expiring On : ', 'points-and-rewards-for-woocommerce' ) . esc_html( $expirydate_format ); ?></div>
				<?php
			}
		}
		?>
	</div>
</div>

<!-- Cards for detail -->
<div class="wps-par_ma-desc-cards">
	<div class="wps-par_madc-card wps-par_tick">
		<div class="wps-par_madcc-desc">
			<h3><?php esc_html_e( 'Customer Rank', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p>#<?php echo esc_html( $this->wps_wpr_get_user_rank_by_points( $user_id ) ); ?></p>
		</div>
		<span class="wps-par_madcc-icon">
			<svg
			xmlns="http://www.w3.org/2000/svg"
			width="18"
			height="14"
			viewBox="0 0 18 14"
			fill="none"
			>
			<path
				d="M1 7.28571L5.8 12L17 1"
				stroke="white"
				stroke-width="2"
				stroke-linecap="round"
			/>
			</svg>
		</span>
	</div>
	<div class="wps-par_madc-card wps-par_star">
		<div class="wps-par_madcc-desc">
			<h3><?php esc_html_e( 'Membership Level', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html( $wps_user_level ); ?></p>
		</div>
		<span class="wps-par_madcc-icon">
			<svg
			xmlns="http://www.w3.org/2000/svg"
			width="16"
			height="15"
			viewBox="0 0 16 15"
			fill="none"
			>
			<path
				d="M7.12885 1.54558C7.51136 0.866931 8.48864 0.86693 8.87115 1.54557L10.3145 4.10634C10.4572 4.35952 10.703 4.53809 10.9879 4.59557L13.8693 5.17696C14.633 5.33104 14.9349 6.26048 14.4077 6.83399L12.4183 8.99802C12.2216 9.21197 12.1277 9.5009 12.1611 9.78961L12.4986 12.7097C12.588 13.4836 11.7974 14.058 11.089 13.7338L8.41616 12.5105C8.1519 12.3895 7.8481 12.3895 7.58384 12.5105L4.91096 13.7338C4.2026 14.058 3.41197 13.4836 3.50141 12.7097L3.83889 9.78961C3.87226 9.50091 3.77838 9.21197 3.58169 8.99802L1.59228 6.83399C1.06505 6.26048 1.36704 5.33104 2.13068 5.17696L5.01213 4.59557C5.29701 4.53809 5.54279 4.35952 5.6855 4.10634L7.12885 1.54558Z"
				stroke="white"
				stroke-width="1.5"
			/>
			</svg>
		</span>
	</div>
	<div class="wps-par_madc-card wps-par_share">
		<div class="wps-par_madcc-desc">
			<h3><?php esc_html_e( 'Referred', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html( $wps_wpr_total_referral_count ); ?></p>
		</div>
		<span class="wps-par_madcc-icon">
			<svg
			xmlns="http://www.w3.org/2000/svg"
			width="16"
			height="17"
			viewBox="0 0 16 17"
			fill="none"
			>
			<path
				d="M10.6918 5.0257L5.45955 7.80508M5.04248 10.5469L10.5023 13.2512"
				stroke="white"
				stroke-width="1.5"
				stroke-linecap="round"
			/>
			<path
				d="M13.1565 1.64453C14.3194 1.64459 15.2502 2.57706 15.2502 3.71191C15.25 4.84656 14.3193 5.77826 13.1565 5.77832C11.9937 5.77832 11.063 4.8466 11.0627 3.71191C11.0627 2.57703 11.9935 1.64453 13.1565 1.64453Z"
				stroke="white"
				stroke-width="1.5"
			/>
			<path
				d="M13.1565 12.0107C14.3194 12.0107 15.2502 12.9432 15.2502 14.078C15.25 15.2127 14.3193 16.1444 13.1565 16.1444C11.9937 16.1444 11.063 15.2127 11.0627 14.078C11.0627 12.9431 11.9935 12.0107 13.1565 12.0107Z"
				stroke="white"
				stroke-width="1.5"
			/>
			<path
				d="M2.84375 6.82761C4.00666 6.82767 4.9375 7.76014 4.9375 8.89499C4.93726 10.0296 4.00651 10.9613 2.84375 10.9614C1.68094 10.9614 0.750241 10.0297 0.75 8.89499C0.75 7.7601 1.68079 6.82761 2.84375 6.82761Z"
				stroke="white"
				stroke-width="1.5"
			/>
			</svg>
		</span>
	</div>
	<div class="wps-par_madc-card wps-par_dollar">
		<div class="wps-par_madcc-desc">
			<h3><?php esc_html_e( 'Redeemed', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html( $wps_wpr_redeemed_points ); ?></p>
		</div>
		<span class="wps-par_madcc-icon">
			<svg
			xmlns="http://www.w3.org/2000/svg"
			width="10"
			height="18"
			viewBox="0 0 10 18"
			fill="none"
			>
			<path
				d="M5.06637 14.7902C5.93563 14.7902 6.51391 14.6922 7.12166 14.4104C7.73309 14.1286 8.19718 13.7488 8.51395 13.2709C8.8344 12.7929 8.99462 12.2579 8.99462 11.6659C8.99462 11.1523 8.88596 10.7189 8.66865 10.3659C8.45133 10.0128 7.9407 9.06049 6.49097 9.06049L4.18223 9.01769C3.3893 9.01769 2.45219 8.80837 1.82307 7.95662C1.30925 7.26097 1.30925 6.90982 1.30925 6.17153C1.30925 5.55807 1.48053 5.02308 1.82307 4.56655C2.1693 4.10646 2.6334 3.7498 3.21536 3.49657C3.80101 3.23977 4.4548 3.11137 5.17673 3.11137M5.06637 14.7902C4.25604 14.7902 3.55437 14.6636 2.96136 14.4104C2.37203 14.1572 1.90794 13.8041 1.56907 13.3511C1.23389 12.8982 1.0442 12.3721 1 11.7729M5.06637 14.7902V17M5.17673 3.11137C5.90602 3.11137 6.55428 3.23799 7.12151 3.49121C7.68874 3.74088 8.13811 4.08327 8.4696 4.5184C8.80478 4.95353 8.98158 5.44751 9 6.00033M5.17673 3.11137V1"
				stroke="white"
				stroke-width="2"
				stroke-linecap="round"
			/>
			</svg>
		</span>
	</div>
</div>

<!-- tab switch -->
<div class="wps-par_ma-switch">
	<div class="wps-par_mas-mob-head">
		<span class="wps-p_masmh-item-label"><?php esc_html_e( 'Menu', 'points-and-rewards-for-woocommerce' ); ?></span>
		<span class="wps-p_masmh-item wps-p_mash-i-menu">
			<svg
			width="25"
			height="25"
			viewBox="0 0 25 25"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			>
			<path
				d="M12.4165 13.8582C12.9688 13.8582 13.4165 13.4104 13.4165 12.8582C13.4165 12.3059 12.9688 11.8582 12.4165 11.8582C11.8642 11.8582 11.4165 12.3059 11.4165 12.8582C11.4165 13.4104 11.8642 13.8582 12.4165 13.8582Z"
				stroke="black"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			<path
				d="M12.4165 6.85815C12.9688 6.85815 13.4165 6.41044 13.4165 5.85815C13.4165 5.30587 12.9688 4.85815 12.4165 4.85815C11.8642 4.85815 11.4165 5.30587 11.4165 5.85815C11.4165 6.41044 11.8642 6.85815 12.4165 6.85815Z"
				stroke="black"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			<path
				d="M12.4165 20.8582C12.9688 20.8582 13.4165 20.4104 13.4165 19.8582C13.4165 19.3059 12.9688 18.8582 12.4165 18.8582C11.8642 18.8582 11.4165 19.3059 11.4165 19.8582C11.4165 20.4104 11.8642 20.8582 12.4165 20.8582Z"
				stroke="black"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			/>
			</svg>
		</span>
	</div>

	<!-- Account page tabs -->
	<div class="wps-par_mas-head">
		<span class="wps-p_mash-item wps-p_mash-i-membership"><?php esc_html_e( 'Memberships', 'points-and-rewards-for-woocommerce' ); ?></span>
		<span class="wps-p_mash-item wps-p_mash-i-coupon"><?php esc_html_e( 'Coupons', 'points-and-rewards-for-woocommerce' ); ?></span>
		<span class="wps-p_mash-item wps-p_mash-i-claim"><?php esc_html_e( 'Claim', 'points-and-rewards-for-woocommerce' ); ?></span>
		<span class="wps-p_mash-item wps-p_mash-i-referral wps-p_mash-i--active"><?php esc_html_e( 'Referral', 'points-and-rewards-for-woocommerce' ); ?></span>
		<span class="wps-p_mash-item wps-p_mash-i-earn"><?php esc_html_e( 'Notices', 'points-and-rewards-for-woocommerce' ); ?></span>
	</div>

	<!-- Tab inside data -->
	<div class="wps-par_mas-desc">
		<div class="wps-p_masd-item wps-p_masd-i-membership">
			<!-- Progress bar -->
			<?php
			if ( ! empty( $membership_settings_array['membership_roles'] ) && is_array( $membership_settings_array['membership_roles'] ) ) {

				// Get level names and determine current level position.
				$level_names         = array_keys( $membership_settings_array['membership_roles'] );
				$current_level_index = array_search( $wps_user_level, $level_names );

				// Progress Bar HTML.
				echo '<div class="wps_wpr_progress-container">';
				echo '<ul class="wps_wpr_progress-bar">';

				foreach ( $level_names as $index => $level_name ) {

					$active_class   = ( $index <= $current_level_index ) ? 'active' : '';
					$current_class  = ( $level_name === $wps_user_level ) ? 'current-level' : '';
					$combined_class = trim( "$active_class $current_class" );
					$current_label  = ( $level_name === $wps_user_level ) ? ' (You)' : '';

					echo '<li class="' . esc_html( $combined_class ) . '">';
					echo '<span class="step-name">' . esc_html( $level_name ) . esc_html( $current_label ) . '</span>';
					echo '</li>';
				}
				echo '</ul>';
				echo '</div>';
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

													echo '<img class="wps_wpr_tick" src = "' . esc_url( WPS_RWPR_DIR_URL ) . 'public/images/tick2.svg">';
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
			?>
		</div>
		<!-- Coupon section tab details -->
		<div class="wps-p_masd-item wps-p_masd-i-coupon">
			<?php
			if ( wps_wpr_is_par_pro_plugin_active() ) {
				do_action( 'wps_wpr_add_coupon_generation', $user_id );
			} else {
				?>
				<div class="wps-par_ma-notice par-notice-error">
					<h4><?php esc_html_e( 'This feature is part of our Pro plugin.', 'points-and-rewards-for-woocommerce' ); ?><a class="wps_wpr_coupon_error_msg" href="'https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro'"><?php esc_html_e( ' Click here to upgrade and unlock Pro features!', 'points-and-rewards-for-woocommerce' ); ?></a></h4>
				</div>
				<?php
			}
			?>
		</div>
		<!-- Claim section tab details -->
		<div class="wps-p_masd-item wps-p_masd-i-claim">
			<?php
			if ( wps_wpr_is_par_pro_plugin_active() ) {

				do_action( 'wps_wpr_add_share_points', $user_id );
			} else {
				?>
				<div class="wps-par_ma-notice par-notice-error">
					<h4><?php esc_html_e( 'No relevant data found at the moment!.', 'points-and-rewards-for-woocommerce' ); ?></h4>
				</div>
				<?php
			}
			?>
		</div>
		<!-- Referral section tab details -->
		<div class="wps-p_masd-item wps-p_masd-i-referral wps-p_masd-i--active">
			<?php
			if ( $enable_wps_refer ) {
				$public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', '1.0.0' );
				$public_obj->wps_wpr_get_referral_section( $user_id );
			} else {
				?>
				<div class="wps-par_ma-notice par-notice-error">
					<h4><?php esc_html_e( 'Referral settings are currently inactive on this site!', 'points-and-rewards-for-woocommerce' ); ?></h4>
				</div>
				<?php
			}
			?>
		</div>
		<!-- Earn section tab details -->
		<div class="wps-p_masd-item wps-p_masd-i-earn">
			<!-- Notifications -->
			<?php
			$flag                               = true;
			$wps_wpr_custom_points_on_checkout  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
			$wps_wpr_custom_points_on_cart      = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
			$wps_wpr_show_redeem_notice         = $this->wps_wpr_get_general_settings_num( 'wps_wpr_show_redeem_notice' );
			$wps_wpr_points_redemption_messages = $this->wps_wpr_get_general_settings( 'wps_wpr_points_redemption_messages' );

			// Get the cart point rate.
			$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
			$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;

			// Get the cart price rate.
			$wps_wpr_cart_price_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
			$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;

			// show message on cart page for redemption settings.
			if ( ( 1 == $wps_wpr_custom_points_on_cart || 1 === $wps_wpr_custom_points_on_checkout ) && ! empty( $user_id ) && $wps_wpr_show_redeem_notice ) {

				$flag = false;
				?>
				<div class="wps-par_ma-notice par-notice-error">
					<h4><?php esc_html_e( 'Cart / Checkout Page Points Redeem', 'points-and-rewards-for-woocommerce' ); ?></h4>
					<p>
						<?php
						// WOOCS - WooCommerce Currency Switcher Compatibility.
						$wps_wpr_points_redemption_messages = str_replace( '[POINTS]', $wps_wpr_cart_points_rate, $wps_wpr_points_redemption_messages );
						$wps_wpr_points_redemption_messages = str_replace( '[CURRENCY]', wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_wpr_cart_price_rate ) ), $wps_wpr_points_redemption_messages );
						echo wp_kses_post( $wps_wpr_points_redemption_messages );
						?>
					</p>
				</div>
				<?php
			}

			// show message on cart page for per currency earn points.
			$wps_wpr_per_currency_discount_notice = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_per_currency_discount_notice' );
			$wps_wpr_per_curr_earning_messages    = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_per_curr_earning_messages' );
			if ( $this->is_order_conversion_enabled() && $wps_wpr_per_currency_discount_notice ) {

				$flag = false;
				?>
				<div class="wps-par_ma-notice par-notice-pending">
					<h4><?php esc_html_e( 'Per Currency Earning Points', 'points-and-rewards-for-woocommerce' ); ?></h4>
					<p>
						<?php
						// WOOCS - WooCommerce Currency Switcher Compatibility.
						$wps_wpr_per_curr_earning_messages = str_replace( '[POINTS]', $wps_wpr_cart_points_rate, $wps_wpr_per_curr_earning_messages );
						$wps_wpr_per_curr_earning_messages = str_replace( '[CURRENCY]', wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_wpr_cart_price_rate ) ), $wps_wpr_per_curr_earning_messages );
						echo wp_kses_post( $wps_wpr_per_curr_earning_messages );
						?>
					</p>
				</div>
				<?php
			}

			// sale product points restriction notice.
			$general_settings      = get_option( 'wps_wpr_settings_gallery' );
			$restrict_sale_on_cart = ! empty( $general_settings['wps_wpr_points_restrict_sale'] ) ? $general_settings['wps_wpr_points_restrict_sale'] : '';
			$points_apply_enable   = ! empty( $general_settings['wps_wpr_general_setting_enable'] ) ? $general_settings['wps_wpr_general_setting_enable'] : '';
			if ( '1' == $points_apply_enable && '1' == $restrict_sale_on_cart ) {
				$flag = false;
				?>
				<div class="wps-par_ma-notice par-notice-success">
					<h4><?php esc_html_e( 'Points Redemption Restriction', 'points-and-rewards-for-woocommerce' ); ?></h4>
					<p><?php esc_html_e( 'Points cannot be redeemed on sale products', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>
				<?php
			}

			// payment rewards notice.
			$other_settings                          = get_option( 'wps_wpr_other_settings', array() );
			$wps_wpr_enable_payment_rewards_settings = ! empty( $other_settings['wps_wpr_enable_payment_rewards_settings'] ) ? $other_settings['wps_wpr_enable_payment_rewards_settings'] : '';
			$wps_wpr_choose_payment_method           = ! empty( $other_settings['wps_wpr_choose_payment_method'] ) ? $other_settings['wps_wpr_choose_payment_method'] : '';
			$wps_wpr_payment_method_rewards_points   = ! empty( $other_settings['wps_wpr_payment_method_rewards_points'] ) ? $other_settings['wps_wpr_payment_method_rewards_points'] : '';
			if ( 1 === $wps_wpr_enable_payment_rewards_settings && $wps_wpr_choose_payment_method ) {
				$flag    = false;
				$gateway = WC_Payment_Gateways::instance()->get_available_payment_gateways()[ $wps_wpr_choose_payment_method ] ?? null;
				?>
				<div class="wps-par_ma-notice par-notice-hold">
					<h4><?php esc_html_e( 'Get Points via Payment Method', 'points-and-rewards-for-woocommerce' ); ?></h4>
					<p><?php /* translators: %s: payment */ printf( esc_html__( 'You will earn %1$s reward points when you choose the %2$s payment method at checkout.', 'points-and-rewards-for-woocommerce' ), esc_html( $wps_wpr_payment_method_rewards_points ), $gateway ? esc_html( $gateway->get_title() ) : '' ); ?></p>
				</div>
				<?php
			}

			// show error msg when no notices are available.
			if ( $flag ) {
				?>
				<div class="wps-par_ma-notice par-notice-error wps_wpr_error_notice_for_coupon_display">
					<h4><?php esc_html_e( 'There are no notices to display at the moment!', 'points-and-rewards-for-woocommerce' ); ?></h4>
				</div>
				<?php
			}
			do_action( 'wps_extend_point_tab_section', $user_id );
			?>
		</div>
	</div>
</div>
