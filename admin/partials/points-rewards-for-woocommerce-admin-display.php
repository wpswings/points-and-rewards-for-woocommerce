<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Rewardeem_woocommerce_Points_Rewards
 * @subpackage Rewardeem_woocommerce_Points_Rewards/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPS_PAR_ONBOARD_PLUGIN_NAME' ) ) {
	define( 'WPS_PAR_ONBOARD_PLUGIN_NAME', 'Points and Rewards for WooCommerce' );
}

if ( class_exists( 'WPSwings_Onboarding_Helper' ) ) {
	$onboard = new WPSwings_Onboarding_Helper();
}

$wps_wpr_setting_tab = array(
	'overview-setting'      => array(
		'title'     => __( 'Overview', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . '/admin/partials/templates/wps-wpr-overview-settings.php',
	),
	'general-setting'       => array(
		'title'     => __( 'General', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . '/admin/partials/templates/wps-generral-settings2.php',
	),
	'coupon-setting'        => array(
		'title'     => apply_filters( 'wps_coupon_tab_text', __( 'Per Currency Points Settings', 'points-and-rewards-for-woocommerce' ) ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-coupon-settings.php',
	),
	'points-table'          => array(
		'title'     => __( 'Points Table', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/class-points-log-list-table.php',
	),
	'points-notification'   => array(
		'title'     => __( 'Points Notification', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-points-notification-settings.php',
	),
	'membership'            => array(
		'title'     => __( 'Membership', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-membership-settings.php',
	),
	'assign-product-points' => array(
		'title'     => __( 'Assign Product Points', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-assign-pro-points.php',
	),
	'other-setting'         => array(
		'title'     => __( 'Other Settings', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-other-setting.php',
	),
	'order-total-points'    => array(
		'title'     => __( 'Order Total Points', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-order-total.php',
	),
	'gamification-settings' => array(
		'title'     => __( 'Gamification', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-wpr-gamifications-settings.php',
	),
	'user-badges-settings'  => array(
		'title'     => __( 'Badges', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-wpr-user-badges-settings.php',
	),
	'wps-sms-settings'  => array(
		'title'     => __( 'SMS / Whatsapp Integration', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-wpr-sms-integration-settings.php',
	),
	'wps-wpr-user-report-settings'  => array(
		'title'     => '',
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-wpr-user-report-settings.php',
	),
);

$wps_wpr_setting_tab    = apply_filters( 'wps_rwpr_add_setting_tab', $wps_wpr_setting_tab );
$wps_wpr_plugin_version = 'v' . REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION;
$wps_wpr_plugin_name    = apply_filters( 'wps_wpr_pro_plugin_name', /* translators: %s: org name */ sprintf( '%s <span>%s</span>', esc_html__( 'Points and Rewards for WooCommerce', 'points-and-rewards-for-woocommerce' ), esc_html( $wps_wpr_plugin_version ) ) );

// check if user is admin.
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

?>
<div class="wrap woocommerce" id="wps_rwpr_setting_wrapper">
	<form enctype="multipart/form-data" action="" id="mainform"  method="post">
		<div class="wps_rwpr_header">
			<div class="wps_rwpr_header_content_left">
				<div>
					<h3 class="wps_rwpr_setting_title"><?php echo wp_kses_post( $wps_wpr_plugin_name ); ?></h3>					
				</div>
			</div>
			<div class="wps_rwpr_header_content_right">
				<ul>
					<li class="wps_wpr_get_pro">
						<a href="https://wpswings.com/contact-us/?utm_source=wpswings-contact-us&utm_medium=par-org-backend&utm_campaign=contact-us" target="_blank">
							<span class="dashicons dashicons-phone"></span>
							<span class="wps_wpr_contact_doc_text"><?php esc_html_e( 'Contact us', 'points-and-rewards-for-woocommerce' ); ?></span>
						</a>
					</li>
					<li class="wps_wpr_get_pro">
						<a href="https://www.youtube.com/watch?v=9BFowjkTU2Q" target="_blank">
							<img src="<?php echo esc_url( WPS_RWPR_DIR_URL ) . 'admin/images/wps-youtube-dash.svg'; ?>" class="wps_wpr_dash_video_svg_img" alt="Demo image">
							<span class="wps_wpr_contact_doc_text"><?php esc_html_e( 'Video', 'points-and-rewards-for-woocommerce' ); ?></span>
						</a>
					</li>
					<li class="wps_wpr_get_pro">
						<a href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=doc" target="_blank">
							<span class="dashicons dashicons-media-document"></span>
							<span class="wps_wpr_contact_doc_text"><?php esc_html_e( 'Doc', 'points-and-rewards-for-woocommerce' ); ?></span>
						</a>
					</li>
					<?php
					if ( ! wps_wpr_is_par_pro_plugin_active() ) {
						?>
						<li class="wps_wpr_get_pro">
							<a href="https://wpswings.com/product/points-and-rewards-for-woocommerce-plugin/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro"  target="_blank"><?php esc_html_e( 'GO PRO NOW', 'points-and-rewards-for-woocommerce' ); ?></a>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
		</div>
		<div class="wps_rwpr_main_template">
			<div class="wps_rwpr_body_template">
				<div class="wps_rwpr_mobile_nav">
					<span class="dashicons dashicons-menu"></span>
				</div>
				<div class="wps_rwpr_navigator_template">
					<div class="hubwoo-navigations">
						<?php
						$secure_nonce = wp_create_nonce( 'wps-par-admin-nonce' );
						if ( ! empty( $wps_wpr_setting_tab ) && is_array( $wps_wpr_setting_tab ) ) {
							foreach ( $wps_wpr_setting_tab as $key => $wps_tab ) {
								if ( wp_verify_nonce( $secure_nonce, 'wps-par-admin-nonce' ) ) {
									if ( isset( $_GET['tab'] ) && $_GET['tab'] == $key ) {
										?>
										<div class="wps_rwpr_tabs">
											<a class="wps_gw_nav_tab nav-tab nav-tab-active " href="?page=wps-rwpr-setting&nonce=<?php echo esc_html( wp_create_nonce( 'par_main_setting' ) ); ?>&tab=<?php echo esc_html( $key ); ?>"><?php echo esc_html( $wps_tab['title'] ); ?></a>
										</div>
										<?php
									} elseif ( empty( $_GET['tab'] ) && 'overview-setting' == $key ) {
										?>
											<div class="wps_rwpr_tabs">
												<a class="wps_gw_nav_tab nav-tab nav-tab-active" href="?page=wps-rwpr-setting&nonce=<?php echo esc_html( wp_create_nonce( 'par_main_setting' ) ); ?>&tab=<?php echo esc_html( $key ); ?>"><?php echo esc_html( $wps_tab['title'] ); ?></a>
											</div>
											<?php
									} else {
										?>
											<div class="wps_rwpr_tabs">
												<a class="wps_gw_nav_tab nav-tab " href="?page=wps-rwpr-setting&nonce=<?php echo esc_html( wp_create_nonce( 'par_main_setting' ) ); ?>&tab=<?php echo esc_html( $key ); ?>"><?php echo esc_html( $wps_tab['title'] ); ?></a>
											</div>
											<?php

									}
								}
							}
						}
						?>
					</div>
				</div>
				<div class="loading-style-bg wps_rwpr_settings_display_none" id="wps_wpr_loader">
					<img src="<?php echo esc_url( WPS_RWPR_DIR_URL ); ?>public/images/loading.gif">
				</div>
				<?php
				$secure_nonce = wp_create_nonce( 'wps-par-admin-nonce' );
				if ( ! empty( $wps_wpr_setting_tab ) && is_array( $wps_wpr_setting_tab ) ) {

					foreach ( $wps_wpr_setting_tab as $key => $wps_file ) {
						if ( wp_verify_nonce( $secure_nonce, 'wps-par-admin-nonce' ) ) {
							if ( isset( $_GET['tab'] ) && $_GET['tab'] == $key ) {
								$include_tab = $wps_file['file_path'];
								?>
								<div class="wps_rwpr_content_template">
									<?php include_once $include_tab; ?>
								</div>
								<?php
							} elseif ( empty( $_GET['tab'] ) && 'overview-setting' == $key ) {
								?>
								<div class="wps_rwpr_content_template">
									<?php include_once $wps_file['file_path']; ?>
								</div>
								<?php
								break;
							}
						}
					}
				}
				?>
			</div>
		</div>
	</form>
</div>
