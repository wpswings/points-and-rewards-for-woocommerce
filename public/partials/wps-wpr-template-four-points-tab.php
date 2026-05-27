<?php
/**
 * Template Four — Premium Loyalty Design for the Points Tab on My Account page.
 * Visual direction: airline / hotel tier-program feel.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Membership upgrade POST handling ──────────────────────────────────────────
$user_id    = get_current_user_id();
$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
$my_role    = ! empty( get_user_meta( $user_id, 'membership_level', true ) ) ? get_user_meta( $user_id, 'membership_level', true ) : '';

$save_level_flag = ! empty( $_POST['wps_wpr_save_level'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_save_level'] ) ) : '';
$selected_role   = ! empty( $_POST['wps_wpr_membership_roles'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_membership_roles'] ) ) : '';
$nonce           = ! empty( $_POST['membership-save-level'] ) ? sanitize_text_field( wp_unslash( $_POST['membership-save-level'] ) ) : '';

if ( $save_level_flag && $selected_role !== $my_role && wp_verify_nonce( $nonce, 'membership-save-level' ) ) {
	$user = get_user_by( 'ID', $user_id );
	if ( $user ) {
		$membership_settings = get_option( 'wps_wpr_membership_settings', true );
		$membership_roles    = isset( $membership_settings['membership_roles'] ) ? $membership_settings['membership_roles'] : array();
		if ( isset( $membership_roles[ $selected_role ] ) ) {
			$role_data = $membership_roles[ $selected_role ];
			if ( (int) $role_data['Points'] <= $get_points ) {
				$points_required  = (int) $role_data['Points'];
				$remaining_points = $get_points - $points_required;
				$today_date       = date_i18n( 'Y-m-d h:i:sa', current_time( 'timestamp', false ) );
				$expiration_date  = '';
				$this->wps_wpr_update_points_details( $user_id, 'membership', $points_required, array() );
				if ( ! empty( $role_data['Exp_Number'] ) && ! empty( $role_data['Exp_Days'] ) ) {
					$expiration_date = date_i18n( 'Y-m-d', strtotime( "+{$role_data['Exp_Number']} {$role_data['Exp_Days']}", strtotime( $today_date ) ) );
				}
				update_user_meta( $user_id, 'wps_wpr_points', $remaining_points );
				update_user_meta( $user_id, 'membership_level', $selected_role );
				update_user_meta( $user_id, 'membership_expiration', $expiration_date );
				$msg = sprintf(
					/* translators: %1$s points deducted, %2$s remaining */
					esc_html__( 'Your membership has been upgraded, and %1$s points have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ),
					$points_required, $remaining_points
				);
				wps_wpr_send_sms_org( $user_id, $msg );
				wps_wpr_send_messages_on_whatsapp( $user_id, $msg );
				$this->wps_wpr_send_notification_mail_product(
					$user_id, $points_required,
					array( '[USERLEVEL]' => $selected_role, '[USERNAME]' => $user->user_login ),
					array( 'wps_wpr_subject' => 'wps_wpr_membership_email_subject', 'wps_wpr_content' => 'wps_wpr_membership_email_discription_custom_id' )
				);
			}
		}
	}
}

// ── Reload data after possible membership change ──────────────────────────────
$get_points                          = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
$wps_user_level                      = get_user_meta( $user_id, 'membership_level', true );
$wps_wpr_overall__accumulated_points = (int) get_user_meta( $user_id, 'wps_wpr_overall__accumulated_points', true );
$wps_user_point_expiry               = get_user_meta( $user_id, 'wps_wpr_points_expiration_date', true );
$wps_wpr_total_referral_count        = (int) get_user_meta( $user_id, 'wps_wpr_total_referral_count', true );
$wps_wpr_redeemed_points             = (int) get_user_meta( $user_id, 'wps_wpr_redeemed_points', true );

$current_user = wp_get_current_user();
$display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : $current_user->user_login;
// Shorten to first name.
$first_name   = explode( ' ', trim( $display_name ) );
$first_name   = $first_name[0];

// ── General settings ──────────────────────────────────────────────────────────
$general_settings              = get_option( 'wps_wpr_settings_gallery', true );
$enable_wps_refer              = isset( $general_settings['wps_wpr_general_refer_enable'] ) ? intval( $general_settings['wps_wpr_general_refer_enable'] ) : 0;
$wps_refer_value               = isset( $general_settings['wps_wpr_general_refer_value'] ) ? intval( $general_settings['wps_wpr_general_refer_value'] ) : 1;
$wps_text_points_value         = isset( $general_settings['wps_wpr_general_text_points'] ) ? $general_settings['wps_wpr_general_text_points'] : esc_html__( 'My Points', 'points-and-rewards-for-woocommerce' );
$wps_ways_to_gain_points_value = ! empty( $general_settings['wps_wpr_general_ways_to_gain_points'] ) ? $general_settings['wps_wpr_general_ways_to_gain_points'] : '';
$wps_comment_value             = isset( $general_settings['wps_comment_value'] ) ? intval( $general_settings['wps_comment_value'] ) : 1;

// ── Badges / accumulated ───────────────────────────────────────────────────────
$wps_wpr_user_badges_setting     = get_option( 'wps_wpr_user_badges_setting', array() );
$wps_wpr_show_accumulated_points = ! empty( $wps_wpr_user_badges_setting['wps_wpr_show_accumulated_points'] ) ? $wps_wpr_user_badges_setting['wps_wpr_show_accumulated_points'] : 'no';

// ── Expiry ───────────────────────────────────────────────────────────────────
$expiration_settings            = get_option( 'wps_wpr_points_expiration_settings', true );
$expiration_settings            = is_array( $expiration_settings ) ? $expiration_settings : array();
$wps_wpr_points_exp_onmyaccount = ! empty( $expiration_settings['wps_wpr_points_exp_onmyaccount'] ) ? $expiration_settings['wps_wpr_points_exp_onmyaccount'] : 0;

// ── Membership ───────────────────────────────────────────────────────────────
$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
$wps_wpr_mem_enable        = isset( $membership_settings_array['wps_wpr_membership_setting_enable'] ) ? intval( $membership_settings_array['wps_wpr_membership_setting_enable'] ) : 0;

// ── Coupons ──────────────────────────────────────────────────────────────────
$coupon_settings               = get_option( 'wps_wpr_coupons_gallery', true );
$coupon_settings               = is_array( $coupon_settings ) ? $coupon_settings : array();
$wps_per_currency_spent_price  = isset( $coupon_settings['wps_wpr_coupon_conversion_price'] ) ? intval( $coupon_settings['wps_wpr_coupon_conversion_price'] ) : 1;
$wps_per_currency_spent_points = isset( $coupon_settings['wps_wpr_coupon_conversion_points'] ) ? intval( $coupon_settings['wps_wpr_coupon_conversion_points'] ) : 1;

// ── Accent color ──────────────────────────────────────────────────────────────
$wps_wpr_other_settings = get_option( 'wps_wpr_other_settings', array() );
$wps_t4_accent          = ! empty( $wps_wpr_other_settings['wps_wpr_points_tab_layout_color'] ) ? $wps_wpr_other_settings['wps_wpr_points_tab_layout_color'] : '#c89a3a';

// ── User rank ────────────────────────────────────────────────────────────────
$wps_user_rank = $this->wps_wpr_get_user_rank_by_points( $user_id );

// ── Tier / progress-to-next calculation ──────────────────────────────────────
$tier_progress_pct   = 0;
$next_tier_name      = '';
$next_tier_pts       = 0;
$pts_to_next_tier    = 0;
$all_tier_names      = array();
$wps_wpr_mem_roles   = array();
$enable_drop         = false;

if ( $wps_wpr_mem_enable && ! empty( $membership_settings_array['membership_roles'] ) ) {
	$wps_wpr_mem_roles = $membership_settings_array['membership_roles'];
	$all_tier_names    = array_keys( $wps_wpr_mem_roles );
	$curr_idx          = false !== $wps_user_level ? array_search( $wps_user_level, $all_tier_names, true ) : false;

	if ( false !== $curr_idx && isset( $all_tier_names[ $curr_idx + 1 ] ) ) {
		$next_tier_name   = $all_tier_names[ $curr_idx + 1 ];
		$next_tier_pts    = (int) $wps_wpr_mem_roles[ $next_tier_name ]['Points'];
		$curr_tier_pts    = (int) $wps_wpr_mem_roles[ $wps_user_level ]['Points'];
		$range            = max( 1, $next_tier_pts - $curr_tier_pts );
		$tier_progress_pct = min( 100, (int) round( ( max( 0, $get_points - $curr_tier_pts ) / $range ) * 100 ) );
		$pts_to_next_tier  = max( 0, $next_tier_pts - $get_points );
	} elseif ( false === $curr_idx && ! empty( $all_tier_names ) ) {
		$next_tier_name   = $all_tier_names[0];
		$next_tier_pts    = (int) $wps_wpr_mem_roles[ $next_tier_name ]['Points'];
		$tier_progress_pct = min( 100, $next_tier_pts > 0 ? (int) round( ( $get_points / $next_tier_pts ) * 100 ) : 0 );
		$pts_to_next_tier  = max( 0, $next_tier_pts - $get_points );
	} else {
		$tier_progress_pct = 100;
		$next_tier_name    = '';
	}
}

// ── Redeem value: 1 pt = coupon_redeem_price / coupon_redeem_points ───────────
$coupon_redeem_price  = ! empty( $coupon_settings['coupon_redeem_price'] ) ? (float) $coupon_settings['coupon_redeem_price'] : 1;
$coupon_redeem_points = ! empty( $coupon_settings['coupon_redeem_points'] ) ? (int) $coupon_settings['coupon_redeem_points'] : 1;
$redeem_value         = $coupon_redeem_points > 0 ? round( ( $get_points / $coupon_redeem_points ) * $coupon_redeem_price, 2 ) : 0;
?>
<style>.wps-t4-wrap{--t4a:<?php echo esc_attr( $wps_t4_accent ); ?>;}</style>

<div class="wps-t4-wrap wps_wpr_show_points_on_account_page">

	<?php do_action( 'wps_wpr_top_account_page_section_hook', $user_id ); ?>

	<!-- ══════════════════════════════════════════════════════════
	     PAGE HEADER
	     ══════════════════════════════════════════════════════════ -->
	<header class="wps-t4-page-header">
		<div class="wps-t4-ph-left">
			<p class="wps-t4-breadcrumb"><?php esc_html_e( 'My account', 'points-and-rewards-for-woocommerce' ); ?> &middot; <?php esc_html_e( 'Rewards', 'points-and-rewards-for-woocommerce' ); ?></p>
			<h1 class="wps-t4-welcome">
				<?php
				/* translators: %s: user first name */
				printf( esc_html__( 'Welcome back, %s.', 'points-and-rewards-for-woocommerce' ), esc_html( $first_name ) );
				?>
			</h1>
		</div>
		<div class="wps-t4-ph-actions">
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-log' ) ); ?>" class="wps-t4-btn wps-t4-btn--ghost">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
				<?php esc_html_e( 'Points log', 'points-and-rewards-for-woocommerce' ); ?>
			</a>
		</div>
	</header>

	<!-- ══════════════════════════════════════════════════════════
	     HERO — dark tier card
	     ══════════════════════════════════════════════════════════ -->
	<section class="wps-t4-hero" aria-label="<?php esc_attr_e( 'Your points balance', 'points-and-rewards-for-woocommerce' ); ?>">
		<!-- Decorative background rings -->
		<svg class="wps-t4-hero-deco" viewBox="0 0 200 200" aria-hidden="true">
			<circle cx="100" cy="100" r="90" stroke="currentColor" stroke-width=".4" fill="none"/>
			<circle cx="100" cy="100" r="68" stroke="currentColor" stroke-width=".4" fill="none"/>
			<circle cx="100" cy="100" r="46" stroke="currentColor" stroke-width=".4" fill="none"/>
		</svg>

		<div class="wps-t4-hero-grid">
			<!-- Left: balance + CTAs -->
			<div class="wps-t4-hero-balance">
				<div class="wps-t4-hero-tier-badge">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 18h20l-2-9-5 4-3-7-3 7-5-4-2 9z"/><path d="M2 22h20"/></svg>
					<?php
					if ( $wps_user_level ) {
						echo esc_html( $wps_user_level ) . ' ' . esc_html__( 'Member', 'points-and-rewards-for-woocommerce' );
					} else {
						esc_html_e( 'Rewards Member', 'points-and-rewards-for-woocommerce' );
					}
					?>
					&nbsp;&middot;&nbsp;<?php echo esc_html__( 'since', 'points-and-rewards-for-woocommerce' ) . ' ' . esc_html( gmdate( 'Y', strtotime( $current_user->user_registered ) ) ); ?>
				</div>

				<p class="wps-t4-hero-pts-label"><?php esc_html_e( 'Available points', 'points-and-rewards-for-woocommerce' ); ?></p>
				<div class="wps-t4-hero-pts-row">
					<span class="wps-t4-hero-pts-num"><?php echo esc_html( number_format( $get_points ) ); ?></span>
					<span class="wps-t4-hero-pts-unit"><?php esc_html_e( 'PTS', 'points-and-rewards-for-woocommerce' ); ?></span>
				</div>

				<div class="wps-t4-hero-meta-row">
					<?php if ( $redeem_value > 0 ) : ?>
					<span class="wps-t4-hero-meta-item">
						<?php
						/* translators: %s: redeem value with currency */
						printf( esc_html__( '≈ %s redeem value', 'points-and-rewards-for-woocommerce' ), '<strong>' . wp_kses_post( wc_price( $redeem_value ) ) . '</strong>' );
						?>
					</span>
					<?php endif; ?>
					<?php if ( 'yes' === $wps_wpr_show_accumulated_points && $wps_wpr_overall__accumulated_points > 0 ) : ?>
					<span class="wps-t4-hero-sep" aria-hidden="true"></span>
					<span class="wps-t4-hero-meta-item">
						<?php esc_html_e( 'Lifetime', 'points-and-rewards-for-woocommerce' ); ?>&nbsp;<strong><?php echo esc_html( number_format( $wps_wpr_overall__accumulated_points ) ); ?></strong>&nbsp;<?php esc_html_e( 'pts', 'points-and-rewards-for-woocommerce' ); ?>
					</span>
					<?php endif; ?>
					<?php
					if ( ! empty( $wps_user_point_expiry ) && $get_points > 0 && 1 === $wps_wpr_points_exp_onmyaccount ) :
						$date_format       = get_option( 'date_format' );
						$expirydate_format = date_i18n( $date_format, strtotime( $wps_user_point_expiry ) );
					?>
					<span class="wps-t4-hero-sep" aria-hidden="true"></span>
					<span class="wps-t4-hero-meta-item wps-t4-hero-expiry">
						<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						<?php echo esc_html__( 'Expires', 'points-and-rewards-for-woocommerce' ) . ' ' . esc_html( $expirydate_format ); ?>
					</span>
					<?php endif; ?>
				</div>

				<div class="wps-t4-hero-ctas">
					<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-log' ) ); ?>" class="wps-t4-btn wps-t4-btn--log">
						<?php esc_html_e( 'View points log', 'points-and-rewards-for-woocommerce' ); ?>
					</a>
					<a href="#wps-t4-redeem" class="wps-t4-btn wps-t4-btn--primary">
						<?php esc_html_e( 'Redeem points', 'points-and-rewards-for-woocommerce' ); ?>
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
					</a>
				</div>
			</div><!-- /.wps-t4-hero-balance -->

			<!-- Right: next tier progress -->
			<?php if ( $wps_wpr_mem_enable ) : ?>
			<div class="wps-t4-hero-tier">
				<?php if ( $next_tier_name ) : ?>
				<p class="wps-t4-ht-label"><?php esc_html_e( 'Next tier', 'points-and-rewards-for-woocommerce' ); ?></p>
				<p class="wps-t4-ht-name"><?php echo esc_html( $next_tier_name ); ?></p>
				<p class="wps-t4-ht-pts"><?php echo esc_html( number_format( $pts_to_next_tier ) ) . ' ' . esc_html__( 'pts to unlock', 'points-and-rewards-for-woocommerce' ); ?></p>

				<!-- Arc progress SVG -->
				<?php
				$arc_r = 58;
				$arc_c = 2 * M_PI * $arc_r;
				$arc_half = $arc_c / 2;
				$arc_dash = $arc_half * ( $tier_progress_pct / 100 );
				?>
				<svg class="wps-t4-ht-arc" viewBox="-75 -75 150 90" aria-label="<?php echo esc_attr( $tier_progress_pct . '% to ' . $next_tier_name ); ?>">
					<path d="M -<?php echo esc_attr( $arc_r ); ?> 0 A <?php echo esc_attr( $arc_r ); ?> <?php echo esc_attr( $arc_r ); ?> 0 0 1 <?php echo esc_attr( $arc_r ); ?> 0"
					      stroke="rgba(255,255,255,.08)" stroke-width="9" fill="none" stroke-linecap="round"/>
					<path d="M -<?php echo esc_attr( $arc_r ); ?> 0 A <?php echo esc_attr( $arc_r ); ?> <?php echo esc_attr( $arc_r ); ?> 0 0 1 <?php echo esc_attr( $arc_r ); ?> 0"
					      stroke="var(--t4a)" stroke-width="9" fill="none" stroke-linecap="round"
					      stroke-dasharray="<?php echo esc_attr( $arc_half ); ?>"
					      stroke-dashoffset="<?php echo esc_attr( $arc_half - $arc_dash ); ?>"/>
					<text x="0" y="-14" text-anchor="middle" font-size="26" font-weight="700" fill="#fff" font-family="'Instrument Serif',serif"><?php echo esc_html( $tier_progress_pct ); ?>%</text>
					<text x="0" y="2" text-anchor="middle" font-size="7.5" fill="rgba(255,255,255,.5)" font-family="Inter,system-ui,sans-serif" letter-spacing=".12em"><?php esc_html_e( 'TO NEXT TIER', 'points-and-rewards-for-woocommerce' ); ?></text>
				</svg>

				<?php if ( ! empty( $wps_wpr_mem_roles[ $next_tier_name ] ) ) :
					$next_pts_req = $wps_wpr_mem_roles[ $next_tier_name ]['Points'];
				?>
				<p class="wps-t4-ht-hint">
					<?php
					/* translators: %s: next tier name */
					printf( esc_html__( 'Unlock at %s:', 'points-and-rewards-for-woocommerce' ), '<strong>' . esc_html( $next_tier_name ) . '</strong>' );
					?>
					<?php esc_html_e( 'exclusive discounts &amp; benefits.', 'points-and-rewards-for-woocommerce' ); ?>
				</p>
				<?php endif; ?>

				<?php else : ?>
				<!-- Already at top tier -->
				<p class="wps-t4-ht-label"><?php esc_html_e( 'Tier status', 'points-and-rewards-for-woocommerce' ); ?></p>
				<p class="wps-t4-ht-name"><?php echo esc_html( $wps_user_level ); ?></p>
				<p class="wps-t4-ht-pts wps-t4-ht-pts--top">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
					<?php esc_html_e( 'Highest tier achieved!', 'points-and-rewards-for-woocommerce' ); ?>
				</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div><!-- /.wps-t4-hero-grid -->
	</section><!-- /.wps-t4-hero -->

	<!-- ══════════════════════════════════════════════════════════
	     STAT STRIP
	     ══════════════════════════════════════════════════════════ -->
	<div class="wps-t4-stats" role="list">
		<div class="wps-t4-stat" role="listitem">
			<span class="wps-t4-stat-icon wps-t4-si--rank">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0z"/><path d="M17 5h3a2 2 0 0 1 0 4h-3M7 5H4a2 2 0 0 0 0 4h3"/></svg>
			</span>
			<div class="wps-t4-stat-body">
				<span class="wps-t4-stat-val">#<?php echo esc_html( $wps_user_rank ); ?></span>
				<span class="wps-t4-stat-lbl"><?php esc_html_e( 'Customer Rank', 'points-and-rewards-for-woocommerce' ); ?></span>
			</div>
		</div>
		<div class="wps-t4-stat" role="listitem">
			<span class="wps-t4-stat-icon wps-t4-si--level">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z"/></svg>
			</span>
			<div class="wps-t4-stat-body">
				<span class="wps-t4-stat-val"><?php echo $wps_user_level ? esc_html( $wps_user_level ) : '&mdash;'; ?></span>
				<span class="wps-t4-stat-lbl"><?php esc_html_e( 'Membership', 'points-and-rewards-for-woocommerce' ); ?></span>
			</div>
		</div>
		<div class="wps-t4-stat" role="listitem">
			<span class="wps-t4-stat-icon wps-t4-si--lifetime">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
			</span>
			<div class="wps-t4-stat-body">
				<span class="wps-t4-stat-val"><?php echo esc_html( number_format( $wps_wpr_overall__accumulated_points ) ); ?></span>
				<span class="wps-t4-stat-lbl"><?php esc_html_e( 'Lifetime Pts', 'points-and-rewards-for-woocommerce' ); ?></span>
			</div>
		</div>
		<div class="wps-t4-stat" role="listitem">
			<span class="wps-t4-stat-icon wps-t4-si--ref">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
			</span>
			<div class="wps-t4-stat-body">
				<span class="wps-t4-stat-val"><?php echo esc_html( $wps_wpr_total_referral_count ); ?></span>
				<span class="wps-t4-stat-lbl"><?php esc_html_e( 'Friends Invited', 'points-and-rewards-for-woocommerce' ); ?></span>
			</div>
		</div>
	</div><!-- /.wps-t4-stats -->

	<!-- ══════════════════════════════════════════════════════════
	     TWO-COL: Redeem + Ways to earn
	     ══════════════════════════════════════════════════════════ -->
	<div class="wps-t4-two-col">

		<!-- REDEEM POINTS (coupon generation hook) -->
		<section id="wps-t4-redeem" class="wps-t4-card wps-t4-card--redeem">
			<div class="wps-t4-card-head">
				<p class="wps-t4-card-eyebrow"><?php esc_html_e( 'Available rewards', 'points-and-rewards-for-woocommerce' ); ?></p>
				<h2 class="wps-t4-card-title"><?php esc_html_e( 'Redeem your points', 'points-and-rewards-for-woocommerce' ); ?></h2>
			</div>
			<div class="wps-t4-redeem-inner">
				<?php do_action( 'wps_wpr_add_coupon_generation', $user_id ); ?>
			</div>
		</section>

		<!-- WAYS TO EARN -->
		<section class="wps-t4-card wps-t4-card--earn">
			<div class="wps-t4-card-head">
				<p class="wps-t4-card-eyebrow"><?php esc_html_e( 'Ways to earn', 'points-and-rewards-for-woocommerce' ); ?></p>
				<h2 class="wps-t4-card-title"><?php esc_html_e( 'Stack your points', 'points-and-rewards-for-woocommerce' ); ?></h2>
			</div>

			<ul class="wps-t4-earn-list">
				<li class="wps-t4-earn-item">
					<span class="wps-t4-earn-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
					</span>
					<div class="wps-t4-earn-text">
						<strong><?php esc_html_e( 'Place an order', 'points-and-rewards-for-woocommerce' ); ?></strong>
						<span><?php esc_html_e( 'Earn points on every purchase', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</li>
				<?php if ( $enable_wps_refer ) : ?>
				<li class="wps-t4-earn-item">
					<span class="wps-t4-earn-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
					</span>
					<div class="wps-t4-earn-text">
						<strong><?php esc_html_e( 'Refer a friend', 'points-and-rewards-for-woocommerce' ); ?></strong>
						<span><?php echo esc_html( number_format( $wps_refer_value ) ) . ' ' . esc_html__( 'pts per referral', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</li>
				<?php endif; ?>
				<?php if ( $wps_comment_value > 0 ) : ?>
				<li class="wps-t4-earn-item">
					<span class="wps-t4-earn-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
					</span>
					<div class="wps-t4-earn-text">
						<strong><?php esc_html_e( 'Write a review', 'points-and-rewards-for-woocommerce' ); ?></strong>
						<span><?php echo '+' . esc_html( number_format( $wps_comment_value ) ) . ' ' . esc_html__( 'pts per review', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</li>
				<?php endif; ?>
				<?php if ( ! empty( $wps_ways_to_gain_points_value ) ) :
					$wps_ways_clean = str_replace(
						array( '[Comment Points]', '[Refer Points]', '[Per Currency Spent Points]', '[Per Currency Spent Price]' ),
						array( $wps_comment_value, $wps_refer_value, $wps_per_currency_spent_points, $wps_per_currency_spent_price ),
						$wps_ways_to_gain_points_value
					);
				?>
				<li class="wps-t4-earn-item wps-t4-earn-item--custom">
					<span class="wps-t4-earn-icon">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
					</span>
					<div class="wps-t4-earn-text">
						<?php echo wp_kses_post( $wps_clean ?? $wps_ways_clean ); ?>
					</div>
				</li>
				<?php endif; ?>
			</ul>
		</section>
	</div><!-- /.wps-t4-two-col -->

	<!-- ══════════════════════════════════════════════════════════
	     MEMBERSHIP TIERS (if enabled)
	     ══════════════════════════════════════════════════════════ -->
	<?php if ( $wps_wpr_mem_enable && ! empty( $wps_wpr_mem_roles ) ) : ?>
	<section class="wps-t4-card wps-t4-card--tiers">
		<div class="wps-t4-card-head">
			<div>
				<p class="wps-t4-card-eyebrow"><?php esc_html_e( 'Membership tiers', 'points-and-rewards-for-woocommerce' ); ?></p>
				<h2 class="wps-t4-card-title"><?php esc_html_e( 'Levels &amp; benefits', 'points-and-rewards-for-woocommerce' ); ?></h2>
			</div>
			<?php if ( $wps_user_level ) : ?>
			<span class="wps-t4-current-badge"><?php esc_html_e( 'Your tier:', 'points-and-rewards-for-woocommerce' ); ?> <?php echo esc_html( $wps_user_level ); ?></span>
			<?php endif; ?>
		</div>

		<div class="wps-t4-tiers-grid wps_wpr_membership_list_main_wrap wps_wpr_main_section_all_wrap">
			<?php
			foreach ( $wps_wpr_mem_roles as $wps_role => $values ) :
				if ( ! is_array( $values ) ) continue;
				$is_current      = ( $wps_role === $wps_user_level );
				$wps_member_name = strtolower( str_replace( ' ', '_', $wps_role ) );
				$discount_value  = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
				$enable_mem_reward_points = ! empty( $values['enable_mem_reward_points'] ) ? $values['enable_mem_reward_points'] : 0;
				$assign_mem_points_type   = ! empty( $values['assign_mem_points_type'] ) ? $values['assign_mem_points_type'] : 'fixed';
				$mem_rewards_points_val   = ! empty( $values['mem_rewards_points_val'] ) ? $values['mem_rewards_points_val'] : 0;
				if ( $values['Points'] == $get_points || $values['Points'] < $get_points ) {
					$enable_drop = true;
				}
			?>
			<div class="wps-t4-tier-card <?php echo $is_current ? 'wps-t4-tier-card--current' : ''; ?>">
				<?php if ( $is_current ) : ?>
				<span class="wps-t4-tier-you"><?php esc_html_e( 'YOUR TIER', 'points-and-rewards-for-woocommerce' ); ?></span>
				<?php endif; ?>

				<div class="wps-t4-tier-top">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 18h20l-2-9-5 4-3-7-3 7-5-4-2 9z"/><path d="M2 22h20"/></svg>
					<span class="wps-t4-tier-name"><?php echo esc_html( strtoupper( $wps_role ) ); ?></span>
				</div>
				<div class="wps-t4-tier-pts"><?php echo esc_html( number_format( (int) $values['Points'] ) ); ?><span class="wps-t4-tier-pts-unit">&nbsp;<?php esc_html_e( 'pts', 'points-and-rewards-for-woocommerce' ); ?></span></div>

				<ul class="wps-t4-tier-benefits">
					<?php if ( $discount_value > 0 ) : ?>
					<li>
						<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
						<?php echo esc_html( $discount_value ) . '% ' . esc_html__( 'discount', 'points-and-rewards-for-woocommerce' ); ?>
					</li>
					<?php endif; ?>
					<?php if ( 1 == $enable_mem_reward_points ) : ?>
					<li>
						<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
						<?php
						if ( 'percent' === $assign_mem_points_type ) {
							echo esc_html( $mem_rewards_points_val . '% ' . __( 'bonus points', 'points-and-rewards-for-woocommerce' ) );
						} else {
							echo esc_html( '+' . $mem_rewards_points_val . ' ' . __( 'bonus pts per order', 'points-and-rewards-for-woocommerce' ) );
						}
						?>
					</li>
					<?php endif; ?>
					<?php if ( ! wps_wpr_is_active() && ! empty( $values['Exp_Number'] ) ) : ?>
					<li>
						<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
						<?php echo esc_html__( 'Valid:', 'points-and-rewards-for-woocommerce' ) . ' ' . esc_html( $values['Exp_Number'] . ' ' . $values['Exp_Days'] ); ?>
					</li>
					<?php endif; ?>
				</ul>

				<div class="wps-t4-tier-footer">
					<a class="wps_wpr_level_benefits wps-t4-tier-benefits-link" data-id="<?php echo esc_attr( $wps_member_name ); ?>" href="javascript:;">
						<?php esc_html_e( 'View benefits', 'points-and-rewards-for-woocommerce' ); ?>
					</a>
					<?php if ( $is_current ) : ?>
					<span class="wps-t4-tier-active-tick">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
						<?php esc_html_e( 'Active', 'points-and-rewards-for-woocommerce' ); ?>
					</span>
					<?php endif; ?>
				</div>

				<!-- Popup (handled by plugin JS) -->
				<div class="wps_wpr_popup_wrapper wps_rwpr_settings_display_none" id="wps_wpr_popup_wrapper_<?php echo esc_attr( $wps_member_name ); ?>">
					<div class="wps_wpr_popup_content_section">
						<div class="wps_wpr_popup_content">
							<div class="wps_wpr_popup_notice_section">
								<div class="wps_wpr_popup_notice_section_in">
									<?php if ( $discount_value > 0 ) : ?>
									<p><span class="wps_wpr_intro_text"><?php esc_html_e( 'You will get ', 'points-and-rewards-for-woocommerce' ); echo esc_html( $discount_value ); esc_html_e( '% discount on below products or categories', 'points-and-rewards-for-woocommerce' ); ?></span></p>
									<?php else : ?>
									<p><span class="wps_wpr_intro_text"><?php echo esc_html( ucfirst( $wps_member_name ) ); ?></span></p>
									<?php endif; ?>
									<span class="wps_wpr_close"><a href="javascript:;"><img src="<?php echo esc_url( WPS_RWPR_DIR_URL ); ?>public/images/cancel.png" alt="<?php esc_attr_e( 'Close', 'points-and-rewards-for-woocommerce' ); ?>"></a></span>
								</div>
								<div class="wps_wpr_popup_notice_section_in">
									<?php
									$wps_wpr_enable_mem_wise_per_curr = isset( $values['wps_wpr_enable_mem_wise_per_curr'] ) ? $values['wps_wpr_enable_mem_wise_per_curr'] : 0;
									if ( '1' === $wps_wpr_enable_mem_wise_per_curr ) {
										$wps_wpr_per_curr_earn_msg  = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_per_curr_earning_messages' );
										$wps_mem_wise_price         = isset( $values['wps_wpr_membership_wise_price'] ) ? (float) $values['wps_wpr_membership_wise_price'] : 0;
										$wps_mem_wise_points        = isset( $values['wps_wpr_membership_wise_points'] ) ? (float) $values['wps_wpr_membership_wise_points'] : 0;
										$wps_wpr_per_curr_earn_msg  = str_replace( '[POINTS]', $wps_mem_wise_points, $wps_wpr_per_curr_earn_msg );
										$wps_wpr_per_curr_earn_msg  = str_replace( '[CURRENCY]', wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_mem_wise_price ) ), $wps_wpr_per_curr_earn_msg );
										echo '<span class="wps_wpr_messages">' . wp_kses_post( $wps_wpr_per_curr_earn_msg ) . '</span>';
									}
									?>
								</div>
							</div>
							<div class="wps_wpr_popup_thumbnail_section">
								<ul>
								<?php
								if ( ! empty( $values['Product'] ) && is_array( $values['Product'] ) ) {
									foreach ( $values['Product'] as $pro_id ) {
										$pro_img = wp_get_attachment_image_src( get_post_thumbnail_id( $pro_id ), 'single-post-thumbnail' );
										$_product = wc_get_product( $pro_id );
										if ( is_object( $_product ) ) {
											$price = $_product->get_price();
											$product_name = $_product->get_title();
										}
										if ( empty( $pro_img[0] ) ) $pro_img[0] = WPS_RWPR_DIR_URL . 'public/images/placeholder.png';
										?>
									<li><a href="<?php echo esc_url( get_permalink( $pro_id ) ); ?>">
										<span class="wps_wpr_thumbnail_img_wrap"><img src="<?php echo esc_url( $pro_img[0] ); ?>" alt=""></span>
										<span class="wps_wpr_thumbnail_product_name"><?php echo esc_html( $product_name ); ?></span>
										<span class="wps_wpr_thumbnail_price_wrap"><?php echo wp_kses( wc_price( $price ), $this->wps_wpr_allowed_html() ); ?></span>
									</a></li>
										<?php
									}
									?>
								</ul>
									<?php
								} elseif ( ! empty( $values['Prod_Categ'] ) && is_array( $values['Prod_Categ'] ) ) {
									?>
									<div class="wps_wpr_popup_cat">
										<?php
										foreach ( $values['Prod_Categ'] as $wps_cat_id ) {
											$thumbnail_id   = version_compare( WC()->version, '3.0.6', '<' ) ? get_woocommerce_term_meta( $wps_cat_id, 'thumbnail_id', true ) : get_term_meta( $wps_cat_id, 'thumbnail_id', true );
											$cat_img        = wp_get_attachment_url( $thumbnail_id );
											$category_title = get_term( $wps_cat_id, 'product_cat' );
											$category_link  = get_category_link( $wps_cat_id );
											if ( empty( $cat_img ) ) $cat_img = WPS_RWPR_DIR_URL . 'public/images/placeholder.png';
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
								do_action( 'wps_wpr_membership_expiry_date_for_user', $user_id, $values, $wps_role );
								?>
							</div>
						</div>
					</div>
				</div><!-- /.wps_wpr_popup_wrapper -->
			</div><!-- /.wps-t4-tier-card -->
			<?php endforeach; ?>
		</div><!-- /.wps-t4-tiers-grid -->

		<!-- Upgrade level form -->
		<?php
		if ( $enable_drop ) {
			if ( ! empty( $wps_user_level ) && array_key_exists( $wps_user_level, $wps_wpr_mem_roles ) ) {
				$mem_expire_time = get_user_meta( $user_id, 'membership_expiration', true );
				if ( $mem_expire_time > gmdate( 'Y-m-d' ) ) {
					unset( $wps_wpr_mem_roles[ $wps_user_level ] );
				}
			}
			if ( ! empty( $wps_wpr_mem_roles ) ) :
		?>
		<div class="wps-t4-upgrade-bar wps_wpr_upgrade_level_main_wrap wps_wpr_main_section_all_wrap">
			<div class="wps-t4-upgrade-left">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
				<span><?php esc_html_e( 'You qualify for an upgrade!', 'points-and-rewards-for-woocommerce' ); ?></span>
			</div>
			<form action="" method="post" id="wps_wpr_membership" class="wps-t4-upgrade-form">
				<?php wp_nonce_field( 'membership-save-level', 'membership-save-level' ); ?>
				<select id="wps_wpr_membership_roles" class="wps_wpr_membership_roles wps-t4-select" name="wps_wpr_membership_roles">
					<option><?php esc_html_e( 'Select tier', 'points-and-rewards-for-woocommerce' ); ?></option>
					<?php
					foreach ( $wps_wpr_mem_roles as $wps_role => $values ) {
						if ( is_array( $values ) && $values['Points'] <= $get_points ) :
						?>
						<option value="<?php echo esc_attr( $wps_role ); ?>"><?php echo esc_html( $wps_role ); ?></option>
						<?php
						endif;
					}
					?>
				</select>
				<input type="submit" id="wps_wpr_upgrade_level" value="<?php esc_attr_e( 'Upgrade tier', 'points-and-rewards-for-woocommerce' ); ?>" class="wps_rwpr_settings_display_none wps-t4-btn wps-t4-btn--upgrade" name="wps_wpr_save_level">
				<input type="button" id="wps_wpr_upgrade_level_click" value="<?php esc_attr_e( 'Upgrade tier', 'points-and-rewards-for-woocommerce' ); ?>" class="wps-t4-btn wps-t4-btn--upgrade" name="wps_wpr_save_level_click">
			</form>
		</div>
		<?php
			endif;
		}
		?>
	</section><!-- /.wps-t4-card--tiers -->
	<?php endif; ?>

	<!-- ══════════════════════════════════════════════════════════
	     TWO-COL: Earn Notices + Referral (dark card)
	     ══════════════════════════════════════════════════════════ -->
	<div class="wps-t4-two-col wps-t4-two-col--activity">

		<!-- EARN NOTICES -->
		<section class="wps-t4-card wps-t4-card--notices">
			<div class="wps-t4-card-head">
				<p class="wps-t4-card-eyebrow"><?php esc_html_e( 'Active promotions', 'points-and-rewards-for-woocommerce' ); ?></p>
				<h2 class="wps-t4-card-title"><?php esc_html_e( 'Earn notices', 'points-and-rewards-for-woocommerce' ); ?></h2>
			</div>
			<div class="wps-t4-notices-list">
				<?php
				$flag = true;
				$wps_wpr_custom_points_on_checkout  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
				$wps_wpr_custom_points_on_cart      = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
				$wps_wpr_show_redeem_notice         = $this->wps_wpr_get_general_settings_num( 'wps_wpr_show_redeem_notice' );
				$wps_wpr_points_redemption_messages = $this->wps_wpr_get_general_settings( 'wps_wpr_points_redemption_messages' );
				$wps_wpr_cart_points_rate           = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
				$wps_wpr_cart_points_rate           = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
				$wps_wpr_cart_price_rate            = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
				$wps_wpr_cart_price_rate            = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;

				if ( ( 1 == $wps_wpr_custom_points_on_cart || 1 === $wps_wpr_custom_points_on_checkout ) && ! empty( $user_id ) && $wps_wpr_show_redeem_notice ) {
					$flag = false;
					$msg = str_replace( array( '[POINTS]', '[CURRENCY]' ), array( $wps_wpr_cart_points_rate, wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_wpr_cart_price_rate ) ) ), $wps_wpr_points_redemption_messages );
					?>
					<div class="wps-t4-notice-row wps-t4-nr--info">
						<span class="wps-t4-nr-dot"></span>
						<div class="wps-t4-nr-body">
							<strong><?php esc_html_e( 'Cart / Checkout Points Redeem', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<p><?php echo wp_kses_post( $msg ); ?></p>
						</div>
					</div>
					<?php
				}

				$wps_wpr_per_currency_discount_notice = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_per_currency_discount_notice' );
				$wps_wpr_per_curr_earning_messages    = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_per_curr_earning_messages' );
				if ( $this->is_order_conversion_enabled() && $wps_wpr_per_currency_discount_notice ) {
					$flag = false;
					$msg2 = str_replace( array( '[POINTS]', '[CURRENCY]' ), array( $wps_wpr_cart_points_rate, wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_wpr_cart_price_rate ) ) ), $wps_wpr_per_curr_earning_messages );
					?>
					<div class="wps-t4-notice-row wps-t4-nr--warn">
						<span class="wps-t4-nr-dot"></span>
						<div class="wps-t4-nr-body">
							<strong><?php esc_html_e( 'Per Currency Earning Points', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<p><?php echo wp_kses_post( $msg2 ); ?></p>
						</div>
					</div>
					<?php
				}

				$general_settings_v    = get_option( 'wps_wpr_settings_gallery' );
				$restrict_sale_on_cart = ! empty( $general_settings_v['wps_wpr_points_restrict_sale'] ) ? $general_settings_v['wps_wpr_points_restrict_sale'] : '';
				$points_apply_enable   = ! empty( $general_settings_v['wps_wpr_general_setting_enable'] ) ? $general_settings_v['wps_wpr_general_setting_enable'] : '';
				if ( '1' == $points_apply_enable && '1' == $restrict_sale_on_cart ) {
					$flag = false;
					?>
					<div class="wps-t4-notice-row wps-t4-nr--success">
						<span class="wps-t4-nr-dot"></span>
						<div class="wps-t4-nr-body">
							<strong><?php esc_html_e( 'Points Redemption Restriction', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<p><?php esc_html_e( 'Points cannot be redeemed on sale products', 'points-and-rewards-for-woocommerce' ); ?></p>
						</div>
					</div>
					<?php
				}

				$other_settings_v                        = get_option( 'wps_wpr_other_settings', array() );
				$wps_wpr_enable_payment_rewards_settings = ! empty( $other_settings_v['wps_wpr_enable_payment_rewards_settings'] ) ? $other_settings_v['wps_wpr_enable_payment_rewards_settings'] : '';
				$wps_wpr_choose_payment_method           = ! empty( $other_settings_v['wps_wpr_choose_payment_method'] ) ? $other_settings_v['wps_wpr_choose_payment_method'] : '';
				$wps_wpr_payment_method_rewards_points   = ! empty( $other_settings_v['wps_wpr_payment_method_rewards_points'] ) ? $other_settings_v['wps_wpr_payment_method_rewards_points'] : '';
				if ( 1 === $wps_wpr_enable_payment_rewards_settings && $wps_wpr_choose_payment_method ) {
					$flag    = false;
					$gateway = WC_Payment_Gateways::instance()->get_available_payment_gateways()[ $wps_wpr_choose_payment_method ] ?? null;
					?>
					<div class="wps-t4-notice-row wps-t4-nr--purple">
						<span class="wps-t4-nr-dot"></span>
						<div class="wps-t4-nr-body">
							<strong><?php esc_html_e( 'Get Points via Payment Method', 'points-and-rewards-for-woocommerce' ); ?></strong>
							<p><?php printf( esc_html__( 'Earn %1$s reward points when you choose %2$s at checkout.', 'points-and-rewards-for-woocommerce' ), esc_html( $wps_wpr_payment_method_rewards_points ), $gateway ? esc_html( $gateway->get_title() ) : '' ); ?></p>
						</div>
					</div>
					<?php
				}
				if ( $flag ) : ?>
				<div class="wps-t4-notice-row wps-t4-nr--empty">
					<span class="wps-t4-nr-dot"></span>
					<div class="wps-t4-nr-body"><strong><?php esc_html_e( 'No active notices at the moment.', 'points-and-rewards-for-woocommerce' ); ?></strong></div>
				</div>
				<?php endif;
				do_action( 'wps_extend_point_tab_section', $user_id );
				?>
			</div><!-- /.wps-t4-notices-list -->
		</section>

		<!-- REFERRAL (dark card) -->
		<?php if ( $enable_wps_refer ) : ?>
		<section class="wps-t4-referral-card">
			<div class="wps-t4-rc-deco" aria-hidden="true">
				<svg viewBox="0 0 100 100"><polygon points="50,5 95,50 50,95 5,50" stroke="currentColor" stroke-width=".4" fill="none"/><polygon points="50,17 83,50 50,83 17,50" stroke="currentColor" stroke-width=".4" fill="none"/><polygon points="50,29 71,50 50,71 29,50" stroke="currentColor" stroke-width=".4" fill="none"/></svg>
			</div>
			<div class="wps-t4-rc-inner">
				<p class="wps-t4-rc-eyebrow"><?php esc_html_e( 'Refer &amp; earn', 'points-and-rewards-for-woocommerce' ); ?></p>
				<h2 class="wps-t4-rc-heading">
					<?php esc_html_e( 'Give a discount,', 'points-and-rewards-for-woocommerce' ); ?><br>
					<?php
					printf(
						/* translators: %s: referral points value */	
						esc_html__( 'get %s points.', 'points-and-rewards-for-woocommerce' ), '<em>' . esc_html( number_format( $wps_refer_value ) ) . '</em>'
					);
					?>
				</h2>
				<div class="wps-t4-referral-hook-wrap">
					<?php $this->wps_wpr_get_referral_section( $user_id ); ?>
				</div>
			</div>
		</section>
		<?php endif; ?>
	</div><!-- /.wps-t4-two-col--activity -->

	<!-- Share Points hook -->
	<div class="wps-t4-share-wrap">
		<?php do_action( 'wps_wpr_add_share_points', $user_id ); ?>
	</div>

	<!-- ══════════════════════════════════════════════════════════
	     HOW IT WORKS — 3-col rules
	     ══════════════════════════════════════════════════════════ -->
	<section class="wps-t4-card wps-t4-card--rules">
		<div class="wps-t4-card-head">
			<p class="wps-t4-card-eyebrow"><?php esc_html_e( 'How it works', 'points-and-rewards-for-woocommerce' ); ?></p>
			<h2 class="wps-t4-card-title"><?php esc_html_e( 'The fine print, simply.', 'points-and-rewards-for-woocommerce' ); ?></h2>
		</div>
		<div class="wps-t4-rules-grid">
			<div class="wps-t4-rule">
				<span class="wps-t4-rule-num">01</span>
				<h3 class="wps-t4-rule-title"><?php esc_html_e( 'Earn', 'points-and-rewards-for-woocommerce' ); ?></h3>
				<p class="wps-t4-rule-body"><?php esc_html_e( 'Earn points on every order, plus bonuses for referrals, reviews and birthdays.', 'points-and-rewards-for-woocommerce' ); ?></p>
			</div>
			<div class="wps-t4-rule">
				<span class="wps-t4-rule-num">02</span>
				<h3 class="wps-t4-rule-title"><?php esc_html_e( 'Climb', 'points-and-rewards-for-woocommerce' ); ?></h3>
				<p class="wps-t4-rule-body"><?php esc_html_e( 'Hit tier thresholds to unlock exclusive discounts, free shipping and early access.', 'points-and-rewards-for-woocommerce' ); ?></p>
			</div>
			<div class="wps-t4-rule">
				<span class="wps-t4-rule-num">03</span>
				<h3 class="wps-t4-rule-title"><?php esc_html_e( 'Redeem', 'points-and-rewards-for-woocommerce' ); ?></h3>
				<p class="wps-t4-rule-body"><?php
				/* translators: %1$s: redeem price, %2$s: redeem points */
				printf( esc_html__( 'Convert %1$s off per %2$s points, or generate a coupon any time.', 'points-and-rewards-for-woocommerce' ), wp_kses_post( wc_price( $coupon_redeem_price ) ), esc_html( $coupon_redeem_points ) );
				?></p>
			</div>
		</div>
	</section>

</div><!-- /.wps-t4-wrap -->
