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
		'title'     => __( 'Advance Settings', 'points-and-rewards-for-woocommerce' ),
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
	'wps-campaign-settings'  => array(
		'title'     => __( 'Campaigning', 'points-and-rewards-for-woocommerce' ),
		'file_path' => WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-wpr-campaign-settings.php',
	),
);

$wps_wpr_setting_tab    = apply_filters( 'wps_rwpr_add_setting_tab', $wps_wpr_setting_tab );
$wps_wpr_plugin_version = 'v' . REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION;
$wps_wpr_plugin_name    = apply_filters( 'wps_wpr_pro_plugin_name', /* translators: %s: org name */ sprintf( '%s <span>%s</span>', esc_html__( 'Points and Rewards for WooCommerce', 'points-and-rewards-for-woocommerce' ), esc_html( $wps_wpr_plugin_version ) ) );
$wps_wpr_tabs           = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

// check if user is admin.
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$wps_wpr_default_tab = 'overview-setting';
if ( empty( $wps_wpr_setting_tab[ $wps_wpr_default_tab ] ) ) {
	$tab_keys            = array_keys( $wps_wpr_setting_tab );
	$wps_wpr_default_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';
}

$wps_wpr_active_tab = $wps_wpr_default_tab;
if ( ! empty( $wps_wpr_tabs ) && isset( $wps_wpr_setting_tab[ $wps_wpr_tabs ] ) ) {
	$wps_wpr_active_tab = $wps_wpr_tabs;
}

$wps_wpr_active_tab_title = ! empty( $wps_wpr_setting_tab[ $wps_wpr_active_tab ]['title'] ) ? $wps_wpr_setting_tab[ $wps_wpr_active_tab ]['title'] : __( 'Overview', 'points-and-rewards-for-woocommerce' );
$wps_wpr_active_tab_file  = ! empty( $wps_wpr_setting_tab[ $wps_wpr_active_tab ]['file_path'] ) ? $wps_wpr_setting_tab[ $wps_wpr_active_tab ]['file_path'] : '';
$wps_wpr_tab_descriptions = apply_filters(
	'wps_rwpr_setting_tab_descriptions',
	array(
		'overview-setting'      => __( 'Get a quick summary of plugin capabilities, key highlights, and compatibility details.', 'points-and-rewards-for-woocommerce' ),
		'general-setting'       => __( 'Configure core earning, redemption, and points behavior across your WooCommerce store.', 'points-and-rewards-for-woocommerce' ),
		'coupon-setting'        => __( 'Set per-currency point conversion values for earning and redeeming reward points.', 'points-and-rewards-for-woocommerce' ),
		'points-table'          => __( 'View customer points records, update balances, and manage points history from one table.', 'points-and-rewards-for-woocommerce' ),
		'points-notification'   => __( 'Manage notification templates and messaging for points credit, redeem, and expiry events.', 'points-and-rewards-for-woocommerce' ),
		'membership'            => __( 'Create and manage membership levels, eligibility rules, and member reward benefits.', 'points-and-rewards-for-woocommerce' ),
		'assign-product-points' => __( 'Assign and manage reward points for specific products and product-level actions.', 'points-and-rewards-for-woocommerce' ),
		'other-setting'         => __( 'Configure advanced plugin behavior and additional controls for custom reward workflows.', 'points-and-rewards-for-woocommerce' ),
		'order-total-points'    => __( 'Define points allocation rules based on order value ranges and total purchase amounts.', 'points-and-rewards-for-woocommerce' ),
		'gamification-settings' => __( 'Set up gamification campaigns to reward engagement and increase repeat interactions.', 'points-and-rewards-for-woocommerce' ),
		'user-badges-settings'  => __( 'Create badge tiers and user level achievements based on collected reward points.', 'points-and-rewards-for-woocommerce' ),
		'wps-sms-settings'      => __( 'Configure SMS and WhatsApp notifications to communicate points activity in real time.', 'points-and-rewards-for-woocommerce' ),
		'wps-campaign-settings' => __( 'Create referral and promotional campaigns to drive engagement using reward incentives.', 'points-and-rewards-for-woocommerce' ),
	)
);
$wps_wpr_active_tab_desc = ! empty( $wps_wpr_tab_descriptions[ $wps_wpr_active_tab ] ) ? $wps_wpr_tab_descriptions[ $wps_wpr_active_tab ] : __( 'Configure and manage all points and rewards options from this section.', 'points-and-rewards-for-woocommerce' );
$wps_wpr_active_tab_class = 'wps-tab-' . sanitize_html_class( $wps_wpr_active_tab );
$wps_wpr_is_pro_active    = function_exists( 'wps_wpr_is_active' ) ? wps_wpr_is_active() : false;
$wps_wpr_active_version   = defined( 'REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION' ) ? REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION : '';
if ( $wps_wpr_is_pro_active && defined( 'POINTS_AND_REWARDS_FOR_WOOCOMMERCE_PRO_VERSION' ) ) {
	$wps_wpr_active_version = POINTS_AND_REWARDS_FOR_WOOCOMMERCE_PRO_VERSION;
}
$wps_wpr_active_version_label = ! empty( $wps_wpr_active_version ) ? 'v' . $wps_wpr_active_version : '';
$wps_wpr_active_plugin_label  = $wps_wpr_is_pro_active ? __( 'Points and Rewards for WooCommerce Pro', 'points-and-rewards-for-woocommerce' ) : __( 'Points and Rewards for WooCommerce', 'points-and-rewards-for-woocommerce' );
$wps_wpr_tab_nonce        = wp_create_nonce( 'par_main_setting' );
$wps_wpr_visible_tab_limit = (int) apply_filters( 'wps_rwpr_visible_tabs_limit', 8 );
if ( $wps_wpr_visible_tab_limit < 1 ) {
	$wps_wpr_visible_tab_limit = 1;
}
$wps_wpr_primary_tabs  = array();
$wps_wpr_overflow_tabs = array();
if ( ! empty( $wps_wpr_setting_tab ) && is_array( $wps_wpr_setting_tab ) ) {
	$wps_wpr_tab_index = 0;
	foreach ( $wps_wpr_setting_tab as $key => $wps_tab ) {
		if ( $wps_wpr_tab_index < $wps_wpr_visible_tab_limit ) {
			$wps_wpr_primary_tabs[ $key ] = $wps_tab;
		} else {
			$wps_wpr_overflow_tabs[ $key ] = $wps_tab;
		}
		$wps_wpr_tab_index++;
	}
}
$wps_wpr_is_overflow_active = ! empty( $wps_wpr_overflow_tabs ) && isset( $wps_wpr_overflow_tabs[ $wps_wpr_active_tab ] );

?>
<div class="wrap woocommerce wps-rma-admin-wrap" id="wps_rwpr_setting_wrapper" data-wps-rma-active-tab="<?php echo esc_attr( $wps_wpr_active_tab ); ?>">
	<form enctype="multipart/form-data" action="" id="mainform"  method="post">
			<div class="wps_rma_top_notices" id="wps-rma-top-notices"></div>
			<div class="wps_rma_notice_strips">
					<div class="wps_rma_notice_strip wps_rma_notice_strip--status">
						<span class="wps_rma_status_chip <?php echo $wps_wpr_is_pro_active ? 'is-pro' : 'is-lite'; ?>"><?php echo $wps_wpr_is_pro_active ? esc_html__( 'PRO ACTIVE', 'points-and-rewards-for-woocommerce' ) : esc_html__( 'LITE ACTIVE', 'points-and-rewards-for-woocommerce' ); ?></span>
						<p><?php echo esc_html( $wps_wpr_active_plugin_label ); ?></p>
						<span class="wps_rma_plugin_version_top"><?php echo esc_html( $wps_wpr_active_version_label ); ?></span>
				</div>
			</div>
		<div class="wps_rma_license_notices" id="wps-rma-license-notices"></div>

		<div class="wps_rwpr_header">
			<div class="wps_rwpr_header_content_left">
				<h3 class="wps_rwpr_setting_title"><?php echo wp_kses_post( $wps_wpr_plugin_name ); ?></h3>
			</div>
			<div class="wps_rwpr_header_content_right">
				<ul>
					<li class="wps_wpr_get_pro">
						<a href="https://wpswings.com/woocommerce-services/?utm_source=wpswings-par-services&utm_medium=par-pro-backend&utm_campaign=woocommerce-services" target="_blank" rel="noopener noreferrer">
							<span class="dashicons dashicons-admin-generic"></span>
							<span class="wps_wpr_contact_doc_text"><?php esc_html_e( 'Our Services', 'points-and-rewards-for-woocommerce' ); ?></span>
						</a>
					</li>
					<li class="wps_wpr_get_pro">
						<a href="https://wpswings.com/contact-us/?utm_source=wpswings-contact-us&utm_medium=par-org-backend&utm_campaign=contact-us" target="_blank" rel="noopener noreferrer">
							<span class="dashicons dashicons-phone"></span>
							<span class="wps_wpr_contact_doc_text"><?php esc_html_e( 'Contact us', 'points-and-rewards-for-woocommerce' ); ?></span>
						</a>
					</li>
					<li class="wps_wpr_get_pro">
						<a href="https://www.youtube.com/watch?v=9BFowjkTU2Q" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( WPS_RWPR_DIR_URL ) . 'admin/images/wps-youtube-dash.svg'; ?>" class="wps_wpr_dash_video_svg_img" alt="Demo image">
							<span class="wps_wpr_contact_doc_text"><?php esc_html_e( 'Video', 'points-and-rewards-for-woocommerce' ); ?></span>
						</a>
					</li>
					<li class="wps_wpr_get_pro">
						<a href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=doc" target="_blank" rel="noopener noreferrer">
							<span class="dashicons dashicons-media-document"></span>
							<span class="wps_wpr_contact_doc_text"><?php esc_html_e( 'Doc', 'points-and-rewards-for-woocommerce' ); ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="wps_rwpr_main_template wps_rma_shell">
			<div class="wps_rwpr_body_template wps_rma_body_template">
				<div class="wps_rwpr_mobile_nav wps_rma_mobile_nav">
					<span class="dashicons dashicons-menu"></span>
					<span class="wps_rma_mobile_nav_text"><?php esc_html_e( 'Toggle Tabs', 'points-and-rewards-for-woocommerce' ); ?></span>
				</div>
				<div class="wps_rwpr_navigator_template wps_rma_navigator_template">
					<div class="hubwoo-navigations wps_rma_tabs">
						<?php
						if ( ! empty( $wps_wpr_primary_tabs ) && is_array( $wps_wpr_primary_tabs ) ) {
							foreach ( $wps_wpr_primary_tabs as $key => $wps_tab ) {
								$is_tab_active = ( $wps_wpr_active_tab === $key ) ? ' nav-tab-active' : '';
								?>
								<div class="wps_rwpr_tabs">
									<a class="wps_gw_nav_tab nav-tab<?php echo esc_attr( $is_tab_active ); ?>" href="?page=wps-rwpr-setting&nonce=<?php echo esc_html( $wps_wpr_tab_nonce ); ?>&tab=<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $wps_tab['title'] ); ?></a>
								</div>
								<?php
							}
						}
						if ( ! empty( $wps_wpr_overflow_tabs ) ) {
							$wps_wpr_more_active_class = $wps_wpr_is_overflow_active ? ' nav-tab-active' : '';
							?>
							<div class="wps_rwpr_tabs wps_rma_more_tabs<?php echo esc_attr( $wps_wpr_more_active_class ); ?>">
								<button type="button" class="wps_gw_nav_tab nav-tab wps_rma_more_toggle<?php echo esc_attr( $wps_wpr_more_active_class ); ?>" aria-expanded="false">
									<?php esc_html_e( 'More', 'points-and-rewards-for-woocommerce' ); ?>
									<span class="wps_rma_more_caret">&#9662;</span>
								</button>
								<div class="wps_rma_more_menu" role="menu">
									<?php
									foreach ( $wps_wpr_overflow_tabs as $key => $wps_tab ) {
										$is_more_tab_active = ( $wps_wpr_active_tab === $key ) ? ' nav-tab-active' : '';
										?>
										<a class="wps_rma_more_link<?php echo esc_attr( $is_more_tab_active ); ?>" role="menuitem" href="?page=wps-rwpr-setting&nonce=<?php echo esc_html( $wps_wpr_tab_nonce ); ?>&tab=<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $wps_tab['title'] ); ?></a>
										<?php
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>

				<div class="loading-style-bg wps_rwpr_settings_display_none" id="wps_wpr_loader">
					<img src="<?php echo esc_url( WPS_RWPR_DIR_URL ); ?>public/images/loading.gif">
				</div>

				<div class="wps_rma_dashboard_layout <?php echo esc_attr( $wps_wpr_active_tab_class ); ?>">
					<div class="wps_rwpr_content_template wps_rma_content_template">
						<div class="wps_rma_section_heading">
							<div class="wps_rma_section_heading_text">
								<span class="wps_rma_heading_kicker"><?php esc_html_e( 'Settings', 'points-and-rewards-for-woocommerce' ); ?></span>
								<h2><?php echo esc_html( $wps_wpr_active_tab_title ); ?></h2>
								<p><?php echo esc_html( $wps_wpr_active_tab_desc ); ?></p>
							</div>
							<div class="wps_rma_section_heading_actions">
								<a class="button wps_rma_dark_button" href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=doc" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read Documentation', 'points-and-rewards-for-woocommerce' ); ?></a>
							</div>
						</div>
						<?php
						if ( ! empty( $wps_wpr_active_tab_file ) ) {
							include_once $wps_wpr_active_tab_file;
						}
						?>
					</div>

					<aside class="wps_rma_right_sidebar">
						<div class="wps_rma_side_card wps_rma_side_card--help">
							<h4><?php esc_html_e( 'Need help with this plugin?', 'points-and-rewards-for-woocommerce' ); ?></h4>
							<div class="wps-rma-sidebar-card__actions">
								<a class="wps-rma-sidebar-link" href="https://www.youtube.com/watch?v=9BFowjkTU2Q" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Watch Video', 'points-and-rewards-for-woocommerce' ); ?></a>
								<a class="wps-rma-sidebar-link" href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=doc" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'points-and-rewards-for-woocommerce' ); ?></a>
								<a class="wps-rma-sidebar-link" href="https://wpswings.com/submit-query/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'points-and-rewards-for-woocommerce' ); ?></a>
							</div>
						</div>
						<?php
						$wps_wpr_services_url   = Points_Rewards_For_WooCommerce_Talk_To_Expert_Form::wps_wpr_get_services_landing_url();
						$wps_wpr_marketing_rows = array(
							array(
								'icon'        => 'seo',
								'title'       => esc_html__( 'SEO Services', 'points-and-rewards-for-woocommerce' ),
								'description' => esc_html__( 'Improve rankings & organic traffic', 'points-and-rewards-for-woocommerce' ),
							),
							array(
								'icon'        => 'ads',
								'title'       => esc_html__( 'Google Ads Setup And G4 Setup', 'points-and-rewards-for-woocommerce' ),
								'description' => esc_html__( 'Run profitable ad campaigns', 'points-and-rewards-for-woocommerce' ),
							),
							array(
								'icon'        => 'speed',
								'title'       => esc_html__( 'Speed Optimization', 'points-and-rewards-for-woocommerce' ),
								'description' => esc_html__( 'Faster store, happier customers', 'points-and-rewards-for-woocommerce' ),
							),
							array(
								'icon'        => 'dev',
								'title'       => esc_html__( 'WooCommerce Development Services', 'points-and-rewards-for-woocommerce' ),
								'description' => esc_html__( 'Custom Solution For your store needs', 'points-and-rewards-for-woocommerce' ),
							),
						);
						?>
						<div class="wps_rma_side_card wps_rma_side_card--services">
							<div class="wps-rma-sidebar-card__header">
								<h4><?php esc_html_e( 'Grow Your Store With WP Swings', 'points-and-rewards-for-woocommerce' ); ?></h4>
								<span class="wps-rma-sidebar-card__badge" aria-hidden="true"></span>
							</div>
							<p><?php esc_html_e( "Expert solutions to boost your store's performance.", 'points-and-rewards-for-woocommerce' ); ?></p>
							<div class="wps-rma-service-rail">
								<?php foreach ( $wps_wpr_marketing_rows as $wps_wpr_marketing_row ) : ?>
									<a href="<?php echo esc_url( $wps_wpr_services_url ); ?>" target="_blank" rel="noopener noreferrer" class="wps-rma-service-rail__item">
										<span class="wps-rma-service-rail__icon wps-rma-service-rail__icon--<?php echo esc_attr( $wps_wpr_marketing_row['icon'] ); ?>" aria-hidden="true"></span>
										<span class="wps-rma-service-rail__content">
											<span class="wps-rma-service-rail__title"><?php echo esc_html( $wps_wpr_marketing_row['title'] ); ?></span>
											<span class="wps-rma-service-rail__description"><?php echo esc_html( $wps_wpr_marketing_row['description'] ); ?></span>
										</span>
										<span class="wps-rma-service-rail__arrow" aria-hidden="true">&rsaquo;</span>
									</a>
								<?php endforeach; ?>
							</div>
							<button type="button" class="wps-rma-sidebar-button wps-rma-sidebar-button--full" data-wps-wpr-open-expert-modal><?php esc_html_e( 'Talk to an Expert', 'points-and-rewards-for-woocommerce' ); ?></button>
							<div class="wps-rma-service-rail__footer"><?php esc_html_e( 'Services by WP Swings', 'points-and-rewards-for-woocommerce' ); ?></div>
						</div>
						<div class="wps_rma_side_card wps_rma_side_card--soft">
							<h4><?php esc_html_e( 'Still facing problems?', 'points-and-rewards-for-woocommerce' ); ?></h4>
							<p><?php esc_html_e( 'Connect with our team for workflow and integration support.', 'points-and-rewards-for-woocommerce' ); ?></p>
							<a class="wps_rma_contact_button" href="https://wpswings.com/contact-us/?utm_source=wpswings-contact-us&utm_medium=par-org-backend&utm_campaign=contact-us" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contact Us', 'points-and-rewards-for-woocommerce' ); ?></a>
						</div>
						<div class="wps_rma_side_card">
							<h4><?php esc_html_e( 'Explore more plugins', 'points-and-rewards-for-woocommerce' ); ?></h4>
							<p><?php esc_html_e( 'Discover additional plugins from the same product family.', 'points-and-rewards-for-woocommerce' ); ?></p>
							<a class="wps-rma-sidebar-link" href="https://wpswings.com/woocommerce-plugins/?utm_source=wpswings-par-shop&utm_medium=par-pro-backend&utm_campaign=shop-page" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View More Plugins', 'points-and-rewards-for-woocommerce' ); ?></a>
						</div>
					</aside>
				</div>
				</div>
			</div>
		</form>
		<?php Points_Rewards_For_WooCommerce_Talk_To_Expert_Form::wps_wpr_render_modal(); ?>
	</div>
<script type="text/javascript">
	(function() {
		function moveDashboardNotices() {
			var topNotices = document.getElementById('wps-rma-top-notices');
			var licenseNotices = document.getElementById('wps-rma-license-notices');
			var wrapper = document.getElementById('wps_rwpr_setting_wrapper');
			if (!topNotices || !licenseNotices || !wrapper) {
				return;
			}

			var licenseNodes = wrapper.querySelectorAll('#points-and-rewards-for-woocommerce-pro-thirty-days-notify');
			for (var i = 0; i < licenseNodes.length; i++) {
				if (!licenseNotices.contains(licenseNodes[i])) {
					licenseNotices.appendChild(licenseNodes[i]);
				}
			}

			var noticeNodes = wrapper.querySelectorAll('.notice');
			for (var j = 0; j < noticeNodes.length; j++) {
				var node = noticeNodes[j];
				if (node.id === 'points-and-rewards-for-woocommerce-pro-thirty-days-notify') {
					continue;
				}
				if (topNotices.contains(node)) {
					continue;
				}
				if (node.closest('.wps_rma_top_notices') || node.closest('.wps_rma_license_notices')) {
					continue;
				}
				topNotices.appendChild(node);
			}
		}

		function initNoticeMover() {
			moveDashboardNotices();

			var wrapper = document.getElementById('wps_rwpr_setting_wrapper');
			if (wrapper && 'MutationObserver' in window) {
				var observer = new MutationObserver(function() {
					moveDashboardNotices();
				});
				observer.observe(wrapper, { childList: true, subtree: true });
			}

			// Fallback for late DOM manipulations by other scripts.
			setTimeout(moveDashboardNotices, 100);
			setTimeout(moveDashboardNotices, 300);
			setTimeout(moveDashboardNotices, 600);
		}

		function initMoreTabsDropdown() {
			var moreWrapper = document.querySelector('#wps_rwpr_setting_wrapper .wps_rma_more_tabs');
			if (!moreWrapper) {
				return;
			}

			var toggleButton = moreWrapper.querySelector('.wps_rma_more_toggle');
			if (!toggleButton) {
				return;
			}

			function closeMenu() {
				moreWrapper.classList.remove('is-open');
				toggleButton.setAttribute('aria-expanded', 'false');
			}

			toggleButton.addEventListener('click', function(event) {
				event.preventDefault();
				var shouldOpen = !moreWrapper.classList.contains('is-open');
				if (shouldOpen) {
					moreWrapper.classList.add('is-open');
					toggleButton.setAttribute('aria-expanded', 'true');
				} else {
					closeMenu();
				}
			});

			document.addEventListener('click', function(event) {
				if (!moreWrapper.contains(event.target)) {
					closeMenu();
				}
			});

			document.addEventListener('keydown', function(event) {
				if (event.key === 'Escape') {
					closeMenu();
				}
			});
		}

			function normalizeSidebarCards() {
				var wrapper = document.getElementById('wps_rwpr_setting_wrapper');
				if (!wrapper) {
					return;
				}

			var dashboard = wrapper.querySelector('.wps_rma_dashboard_layout');
			var sidebar = wrapper.querySelector('.wps_rma_dashboard_layout > .wps_rma_right_sidebar');
			var content = wrapper.querySelector('.wps_rma_dashboard_layout > .wps_rwpr_content_template');
			if (!dashboard || !sidebar || !content) {
				return;
			}

			// If cards are rendered inside content by malformed tab markup, move them back to sidebar.
			var misplacedCards = content.querySelectorAll('.wps_rma_side_card');
			for (var i = 0; i < misplacedCards.length; i++) {
				sidebar.appendChild(misplacedCards[i]);
			}

			// If an entire sidebar block is nested in content, merge it with the main sidebar.
			var nestedSidebar = content.querySelector('.wps_rma_right_sidebar');
				if (nestedSidebar && nestedSidebar !== sidebar) {
					while (nestedSidebar.firstChild) {
						sidebar.appendChild(nestedSidebar.firstChild);
					}
					nestedSidebar.remove();
				}
			}

			function normalizeDashboardSpacing() {
				var wrapper = document.getElementById('wps_rwpr_setting_wrapper');
				if (!wrapper) {
					return;
				}

				var dashboard = wrapper.querySelector('.wps_rma_dashboard_layout');
				var sidebar = wrapper.querySelector('.wps_rma_dashboard_layout > .wps_rma_right_sidebar');
				var content = wrapper.querySelector('.wps_rma_dashboard_layout > .wps_rwpr_content_template');
				if (!dashboard || !sidebar || !content) {
					return;
				}

				// Ensure left content does not stretch to sidebar height.
				dashboard.style.alignItems = 'flex-start';
				dashboard.style.gridAutoRows = 'min-content';
				content.style.alignSelf = 'flex-start';
				content.style.height = 'auto';
				content.style.minHeight = '0';
				sidebar.style.alignSelf = 'flex-start';
				sidebar.style.height = 'auto';

				// Keep only practical bottom space for fixed Save button.
				var saveButton = wrapper.querySelector('.button-primary.woocommerce-save-button.wps_wpr_save_changes');
				if (saveButton && window.getComputedStyle(saveButton).position === 'fixed') {
					var buttonHeight = Math.ceil(saveButton.getBoundingClientRect().height || 0);
					var requiredPadding = Math.max(20, buttonHeight + 16);
					wrapper.style.paddingBottom = requiredPadding + 'px';
					return;
				}

				wrapper.style.paddingBottom = '16px';
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', function() {
					initNoticeMover();
					initMoreTabsDropdown();
					normalizeSidebarCards();
					normalizeDashboardSpacing();
					setTimeout(normalizeSidebarCards, 50);
					setTimeout(normalizeSidebarCards, 250);
					setTimeout(normalizeDashboardSpacing, 50);
					setTimeout(normalizeDashboardSpacing, 250);
					setTimeout(normalizeDashboardSpacing, 600);
				});
			} else {
				initNoticeMover();
				initMoreTabsDropdown();
				normalizeSidebarCards();
				normalizeDashboardSpacing();
				setTimeout(normalizeSidebarCards, 50);
				setTimeout(normalizeSidebarCards, 250);
				setTimeout(normalizeDashboardSpacing, 50);
				setTimeout(normalizeDashboardSpacing, 250);
				setTimeout(normalizeDashboardSpacing, 600);
			}

			window.addEventListener('resize', normalizeDashboardSpacing);
		})();
	</script>
