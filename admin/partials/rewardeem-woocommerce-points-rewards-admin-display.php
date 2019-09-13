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
		'title'     => __( 'OverView', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . '/admin/partials/templates/mwb_rwpr_overview_setting.php',
	),
	'general-setting' => array(
		'title'     => __( 'General', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . '/admin/partials/templates/mwb_generral-settings2.php',
	),
	'coupon-setting' => array(

		'title'     => __( 'Coupon Settings', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb_coupon_settings.php',
	),
	'points-table' => array(
		'title'     => __( 'Points Table', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb_points_table.php',
	),
	'points-notification' => array(
		'title'     => __( 'Points Notification', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb_points_notification_settings.php',
	),
	'membership' => array(
		'title' => __( 'Membership', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb_membership_settings.php',
	),
	'assign-product-points' => array(
		'title' => __( 'Assign Product Points', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb_assign_pro_points.php',
	),

	'other-setting' => array(
		'title' => __( 'Other Settings', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb_other_setting.php',
	),
	'order-total-points' => array(
		'title' => __( 'Order Total Points', MWB_RWPR_Domain ),
		'file_path' => MWB_RWPR_DIR_PATH . 'admin/partials/templates/mwb_order_total.php',
	),
);
  $mwb_wpr_setting_tab = apply_filters( 'mwb_rwpr_add_setting_tab', $mwb_wpr_setting_tab );

?>
<div class="wrap woocommerce" id="mwb_rwpr_setting_wrapper">
	 <form enctype="multipart/form-data" action="" id="mainform" method="post">		<div class="mwb_rwpr_header">
			<div class="mwb_rwpr_header_content_left">
				<div>
					<h3 class="mwb_rwpr_setting_title"><?php _e( 'Ultimate WooCommerce Points and Rewards Lite', MWB_RWPR_Domain ); ?></h3>
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
			<li class="mwb_rwpr_header_menu_button"><a  href="https://makewebbetter.com/product/giftware-woocommerce-gift-cards/?utm_source=mwb-giftcard-org&utm_medium=mwb-org&utm_campaign=giftcard-org" class="" title="" target="_blank">GO PRO NOW</a></li>
		</ul>
	</div>
</div>
<?php do_action( 'mwb_wpr_add_notice' ); ?>
<input type="hidden" name="mwb-wpr-nonce" value="<?php echo wp_create_nonce( 'mwb-wpr-nonce' ); ?>">
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
							if ( empty( $_GET['tab'] ) && $key == 'overview_setting' ) {
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
			<img src="<?php echo MWB_RWPR_DIR_URL; ?>public/images/loading.gif">
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
				} elseif ( empty( $_GET['tab'] ) && $key == 'overview_setting' ) {
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

