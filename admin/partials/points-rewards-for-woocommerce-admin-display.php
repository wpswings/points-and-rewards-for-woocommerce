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
define( 'ONBOARD_PLUGIN_NAME', 'Points and Rewards for WooCommerce' );

if ( class_exists( 'Makewebbetter_Onboarding_Helper' ) ) {
	$this->onboard = new Makewebbetter_Onboarding_Helper();
}

$mwb_wpr_setting_tab = array(
	'overview-setting' => array(
		'title'     => __( 'Overview', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . '/admin/partials/templates/mwb-wpr-overview-settings.php',
	),
	'general-setting' => array(
		'title'     => __( 'General', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . '/admin/partials/templates/mwb-generral-settings2.php',
	),
	'coupon-setting' => array(

		'title'     => apply_filters( 'mwb_coupon_tab_text', __( 'Per Currency Points Settings', 'points-and-rewards-for-woocommerce' ) ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-coupon-settings.php',
	),
	'points-table' => array(
		'title'     => __( 'Points Table', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/class-points-log-list-table.php',
	),
	'points-notification' => array(
		'title'     => __( 'Points Notification', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-points-notification-settings.php',
	),
	'membership' => array(
		'title' => __( 'Membership', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-membership-settings.php',
	),
	'assign-product-points' => array(
		'title' => __( 'Assign Product Points', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-assign-pro-points.php',
	),

	'other-setting' => array(
		'title' => __( 'Other Settings', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-other-setting.php',
	),
	'order-total-points' => array(
		'title' => __( 'Order Total Points', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-order-total.php',
	),
);
if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {
	$mwb_wpr_setting_tab['premium_plugin'] = array(
		'title' => esc_html__( 'Premium Features', 'points-and-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-wpr-premium-features.php',
	);
}
  $mwb_wpr_setting_tab = apply_filters( 'mwb_rwpr_add_setting_tab', $mwb_wpr_setting_tab );

?>
<div class="wrap woocommerce" id="mwb_rwpr_setting_wrapper">
	 <form enctype="multipart/form-data" action="" id="mainform" method="post">		<div class="mwb_rwpr_header">
			<div class="mwb_rwpr_header_content_left">
				<div>
					<h3 class="mwb_rwpr_setting_title"><?php esc_html_e( 'Points and Rewards for WooCommerce', 'points-and-rewards-for-woocommerce' ); ?></h3>
				</div>
			</div>
			<div class="mwb_rwpr_header_content_right">
				<ul>
					<li class="mwb_wpr_get_pro"><a href="https://makewebbetter.com/contact-us/" target="_blank">
						<span class="dashicons dashicons-phone"></span>
						<span class="mwb_wpr_contact_doc_text"><?php esc_html_e( 'Contact us', 'points-and-rewards-for-woocommerce' ); ?></span>
					</a>
				</li>
				<li class="mwb_wpr_get_pro"><a href="https://docs.makewebbetter.com/points-rewards-for-woocommerce?utm_source=MWB-PAR-org&utm_medium=MWB-org-plugin&utm_campaign=MWB-PAR-org" target="_blank">
					<span class="dashicons dashicons-media-document"></span>
					<span class="mwb_wpr_contact_doc_text"><?php esc_html_e( 'Doc', 'points-and-rewards-for-woocommerce' ); ?></span>
				</a>
			</li>
			<?php
			if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {
				?>
						<li class="mwb_wpr_get_pro"><a  href="https://makewebbetter.com/product/woocommerce-points-and-rewards?utm_source=MWB-PAR-org&utm_medium=MWB-org-plugin&utm_campaign=MWB-PAR-org"  target="_blank"><?php esc_html_e( 'GO PRO NOW', 'points-and-rewards-for-woocommerce' ); ?></a></li>
					<?php
			}
			?>
			<li>
				<a id="mwb-wpr-skype-link" href="<?php echo esc_url( 'https://join.skype.com/invite/IKVeNkLHebpC' ); ?>" target="_blank">
					<img src="<?php echo esc_url( MWB_RWPR_DIR_URL . 'admin/images/skype_logo.png' ); ?>" style="height: 15px;width: 15px;" ><?php esc_html_e( 'CHAT NOW', 'points-and-rewards-for-woocommerce' ); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
<?php do_action( 'mwb_wpr_add_notice' ); ?>
<?php wp_nonce_field( 'mwb-wpr-nonce', 'mwb-wpr-nonce' ); ?>
<div class="mwb_rwpr_main_template">
	<div class="mwb_rwpr_body_template">
		<div class="mwb_rwpr_mobile_nav">
			<span class="dashicons dashicons-menu"></span>
		</div>
		<div class="mwb_rwpr_navigator_template">
			<div class="hubwoo-navigations">
				<?php
				if ( ! empty( $mwb_wpr_setting_tab ) && is_array( $mwb_wpr_setting_tab ) ) {
					foreach ( $mwb_wpr_setting_tab as $key => $mwb_tab ) {
						if ( isset( $_GET['tab'] ) && $_GET['tab'] == $key ) {
							?>
							<div class="mwb_rwpr_tabs">
								<a class="mwb_gw_nav_tab nav-tab nav-tab-active " href="?page=mwb-rwpr-setting&tab=<?php echo esc_html( $key ); ?>"><?php echo esc_html( $mwb_tab['title'] ); ?></a>
							</div>
							<?php
						} else {
							if ( empty( $_GET['tab'] ) && 'overview-setting' == $key ) {
								?>
								<div class="mwb_rwpr_tabs">
									<a class="mwb_gw_nav_tab nav-tab nav-tab-active" href="?page=mwb-rwpr-setting&tab=<?php echo esc_html( $key ); ?>"><?php echo esc_html( $mwb_tab['title'] ); ?></a>
								</div>
								<?php
							} else {
								?>
											
								<div class="mwb_rwpr_tabs">
									<a class="mwb_gw_nav_tab nav-tab " href="?page=mwb-rwpr-setting&tab=<?php echo esc_html( $key ); ?>"><?php echo esc_html( $mwb_tab['title'] ); ?></a>
								</div>
								<?php
							}
						}
					}
				}
				?>
					
			</div>
		</div>
		<div class="loading-style-bg mwb_rwpr_settings_display_none" id="mwb_wpr_loader">
			<img src="<?php echo esc_url( MWB_RWPR_DIR_URL ); ?>public/images/loading.gif">
		</div>
		<?php
		if ( ! empty( $mwb_wpr_setting_tab ) && is_array( $mwb_wpr_setting_tab ) ) {

			foreach ( $mwb_wpr_setting_tab as $key => $mwb_file ) {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] == $key ) {
					$include_tab = $mwb_file['file_path'];
					?>
					<div class="mwb_rwpr_content_template">
						<?php include_once $include_tab; ?>
					</div>
					<?php
				} elseif ( empty( $_GET['tab'] ) && 'overview-setting' == $key ) {
					?>
					<div class="mwb_rwpr_content_template">
						<?php include_once $mwb_file['file_path']; ?>
					</div>
					<?php
					break;
				}
			}
		}
		?>
	</div>
</div>
</form>
</div>

