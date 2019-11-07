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
$mwb_wpr_setting_tab = array(
	'overview_setting' => array(
		'title'     => __( 'OverView', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . '/admin/partials/templates/mwb-rwpr-overview-setting.php',
	),
	'general-setting' => array(
		'title'     => __( 'General', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . '/admin/partials/templates/mwb-generral-settings2.php',
	),
	'coupon-setting' => array(

		'title'     => __( 'Per Currency Points & Coupon Settings', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-coupon-settings.php',
	),
	'points-table' => array(
		'title'     => __( 'Points Table', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-points-table.php',
	),
	'points-notification' => array(
		'title'     => __( 'Points Notification', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-points-notification-settings.php',
	),
	'membership' => array(
		'title' => __( 'Membership', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-membership-settings.php',
	),
	'assign-product-points' => array(
		'title' => __( 'Assign Product Points', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-assign-pro-points.php',
	),

	'other-setting' => array(
		'title' => __( 'Other Settings', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-other-setting.php',
	),
	'order-total-points' => array(
		'title' => __( 'Order Total Points', 'points-rewards-for-woocommerce' ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb-order-total.php',
	),
);
  $mwb_wpr_setting_tab = apply_filters( 'mwb_rwpr_add_setting_tab', $mwb_wpr_setting_tab );

?>
<div class="wrap woocommerce" id="mwb_rwpr_setting_wrapper">
	 <form enctype="multipart/form-data" action="" id="mainform" method="post">		<div class="mwb_rwpr_header">
			<div class="mwb_rwpr_header_content_left">
				<div>
					<h3 class="mwb_rwpr_setting_title"><?php esc_html_e( 'Points and Rewards for WooCommerce Lite', 'points-rewards-for-woocommerce' ); ?></h3>
				</div>
			</div>
			<div class="mwb_rwpr_header_content_right">
				<ul>
					<li><a href="https://makewebbetter.com/contact-us/" target="_blank">
						<span class="dashicons dashicons-phone"></span>
					</a>
				</li>
				<li><a href="https://docs.makewebbetter.com/woocommerce-gift-cards-lite/" target="_blank">
					<span class="dashicons dashicons-media-document"></span>
				</a>
			</li>
			<li class="mwb_rwpr_header_menu_button"><a  href="" class="" title="" target="_blank"><?php esc_html_e( 'GO PRO NOW', 'points-rewards-for-woocommerce' ); ?></a></li>
		</ul>
	</div>
</div>
<?php do_action( 'mwb_wpr_add_notice' ); ?>
<input type="hidden" name="mwb-wpr-nonce" value="<?php echo wp_create_nonce( 'mwb-wpr-nonce' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
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
								<a class="mwb_gw_nav_tab nav-tab nav-tab-active " href="?page=mwb-rwpr-setting&tab=<?php echo $key; ?>"><?php echo $mwb_tab['title']; ?></a>
							</div>
							<?php
						} else {
							if ( empty( $_GET['tab'] ) && 'overview_setting' == $key ) {
								?>
								<div class="mwb_rwpr_tabs">
									<a class="mwb_gw_nav_tab nav-tab nav-tab-active" href="?page=mwb-rwpr-setting&tab=<?php echo $key; ?>"><?php echo $mwb_tab['title']; ?></a>
								</div>
								<?php
							} else {
								?>
											
								<div class="mwb_rwpr_tabs">
									<a class="mwb_gw_nav_tab nav-tab " href="?page=mwb-rwpr-setting&tab=<?php echo $key; ?>"><?php echo $mwb_tab['title']; ?></a>
								</div>
								<?php
							}
						}
					}
				}
				?>
					
			</div>
		</div>
		<div style="display:none;" class="loading-style-bg" id="mwb_wpr_loader">
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
				} elseif ( empty( $_GET['tab'] ) && 'overview_setting' == $key ) {
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

