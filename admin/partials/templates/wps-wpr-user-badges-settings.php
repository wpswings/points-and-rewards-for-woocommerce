<?php
/**
 * This is the setting template
 *
 * Order total points settings.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . '/admin/partials/settings/class-points-rewards-for-woocommerce-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Settings();

// Save User Badges Settings.
if ( isset( $_POST['wps_wpr_user_badges_setting_nonce'] ) ) {
	if ( wp_verify_nonce( ! empty( $_POST['wps_wpr_user_badges_setting_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_user_badges_setting_nonce'] ) ) : '', 'user-badges-setting-nonce' ) ) {
		if ( isset( $_POST['wps_wpr_save_user_badges_settings'] ) ) {

			$wps_wpr_store_user_badges_settings                                        = array();
			$wps_wpr_store_user_badges_settings['wps_wpr_enable_user_badges_settings'] = ! empty( $_POST['wps_wpr_enable_user_badges_settings'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_user_badges_settings'] ) ) : 'no';
			$wps_wpr_store_user_badges_settings['wps_wpr_enter_badges_name']           = ! empty( $_POST['wps_wpr_enter_badges_name'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_enter_badges_name'] ), 'sanitize_text_field' ) : array();
			$wps_wpr_store_user_badges_settings['wps_wpr_badges_threshold_points']     = ! empty( $_POST['wps_wpr_badges_threshold_points'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_badges_threshold_points'] ), 'sanitize_text_field' ) : array();
			$wps_wpr_store_user_badges_settings['wps_wpr_badges_rewards_points']       = ! empty( $_POST['wps_wpr_badges_rewards_points'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_badges_rewards_points'] ), 'sanitize_text_field' ) : array();
			$wps_wpr_store_user_badges_settings['wps_wpr_image_attachment_id']         = ! empty( $_POST['wps_wpr_image_attachment_id'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_image_attachment_id'] ), 'sanitize_text_field' ) : array();

			$wps_wpr_user_badges_setting = array();
			if ( ! empty( $wps_wpr_store_user_badges_settings ) && is_array( $wps_wpr_store_user_badges_settings ) ) {
				foreach ( $wps_wpr_store_user_badges_settings as $key => $value ) {

					$wps_wpr_user_badges_setting[ $key ] = $value;
				}
			}

			if ( ! empty( $wps_wpr_user_badges_setting ) && is_array( $wps_wpr_user_badges_setting ) ) {

				update_option( 'wps_wpr_user_badges_setting', $wps_wpr_user_badges_setting );
			}

			// Show saved msg.
			$settings_obj->wps_wpr_settings_saved();
		}
	}
}

// get badges values.
$wps_wpr_user_badges_setting         = get_option( 'wps_wpr_user_badges_setting', array() );
$wps_wpr_user_badges_setting         = ! empty( $wps_wpr_user_badges_setting ) && is_array( $wps_wpr_user_badges_setting ) ? $wps_wpr_user_badges_setting : array();
$wps_wpr_enable_user_badges_settings = ! empty( $wps_wpr_user_badges_setting['wps_wpr_enable_user_badges_settings'] ) ? $wps_wpr_user_badges_setting['wps_wpr_enable_user_badges_settings'] : 'no';
$wps_wpr_enter_badges_name           = ! empty( $wps_wpr_user_badges_setting['wps_wpr_enter_badges_name'] ) ? $wps_wpr_user_badges_setting['wps_wpr_enter_badges_name'] : array();
$wps_wpr_badges_threshold_points     = ! empty( $wps_wpr_user_badges_setting['wps_wpr_badges_threshold_points'] ) ? $wps_wpr_user_badges_setting['wps_wpr_badges_threshold_points'] : array();
$wps_wpr_badges_rewards_points       = ! empty( $wps_wpr_user_badges_setting['wps_wpr_badges_rewards_points'] ) ? $wps_wpr_user_badges_setting['wps_wpr_badges_rewards_points'] : array();
$wps_wpr_image_attachment_id         = ! empty( $wps_wpr_user_badges_setting['wps_wpr_image_attachment_id'] ) ? $wps_wpr_user_badges_setting['wps_wpr_image_attachment_id'] : array();
?>

<div class="wps_wpr_user_badges_main_wrappers">
	<h4 class="wps_wpr_user_badges_settings_heading"><?php esc_html_e( 'User Level & Badges Settings', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<input type="hidden" name="wps_wpr_user_badges_setting_nonce" id="wps_wpr_user_badges_setting_nonce" value="<?php echo esc_html( wp_create_nonce( 'user-badges-setting-nonce' ) ); ?>">
	<form method="POST" action="" class="wps_wpr_user_badges_form">
		<main class="wps_wpr_main_user_badges_wrapper">
			<section>
				<article>
					<label for="wps_wpr_enable_user_badges_settings"><?php esc_html_e( 'Enable User Badges Settings', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_user_badges_setting_wrapper">
						<input type="checkbox" name="wps_wpr_enable_user_badges_settings" class="wps_wpr_enable_user_badges_settings" value="yes" <?php checked( 'yes', 'yes' ); ?>>
						<span class="wps_wpr_enable_user_badges_notices wps_wpr_label_notice"><?php esc_html_e( 'Check this box to enable this settings.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
			<section>
				<div class="wps_wpr_user_badges_data_wrapper">
					<h4 for="wps_wpr_user__badges_settings"><?php esc_html_e( 'User Badges Settings', 'points-and-rewards-for-woocommerce' ); ?></h4>
					<div class="wps_wpr_win_wheel_segments_data-table">
						<table class="wps_wpr_user_badges_table_settings_wrappers">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Badges Name', 'points-and-rewards-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Threshold Points', 'points-and-rewards-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Rewards Points', 'points-and-rewards-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Add Image', 'points-and-rewards-for-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								// calculation to show cross symbol.
								if ( count( $wps_wpr_enter_badges_name ) > 2 ) {

									$wps_wpr_remove_badges_counter = 0;
								} else {

									$wps_wpr_remove_badges_counter = 1;
								}
								if ( ! empty( $wps_wpr_enter_badges_name[0] ) && ! empty( $wps_wpr_badges_threshold_points[0] ) && ! empty( $wps_wpr_badges_rewards_points[0] ) ) {
									if ( count( $wps_wpr_enter_badges_name ) === count( $wps_wpr_badges_threshold_points ) && count( $wps_wpr_badges_threshold_points ) === count( $wps_wpr_badges_rewards_points ) ) {
										foreach ( $wps_wpr_enter_badges_name as $key => $value ) {
											?>
											<tr class="wps_wpr_add_user_badges_dynamic">
												<td><input type="text" name="wps_wpr_enter_badges_name[]" id="wps_wpr_enter_badges_name" class="wps_wpr_enter_badges_name" value="<?php echo esc_html( $wps_wpr_enter_badges_name[ $key ] ); ?>" required></td>
												<td><input type="number" min="1" name="wps_wpr_badges_threshold_points[]" id="wps_wpr_badges_threshold_points" class="wps_wpr_badges_threshold_points" value="<?php echo esc_html( $wps_wpr_badges_threshold_points[ $key ] ); ?>" required></td>
												<td><input type="number" min="1" name="wps_wpr_badges_rewards_points[]" id="wps_wpr_badges_rewards_points" class="wps_wpr_badges_rewards_points" value="<?php echo esc_html( $wps_wpr_badges_rewards_points[ $key ] ); ?>" required></td>
												<td>
													<img src="<?php echo esc_url( $wps_wpr_image_attachment_id[ $key ] ); ?>" class="wps_wpr_icon_user_badges">
													<input type="button" class="wps_wpr_add_user_badges_img" value="<?php esc_html_e( 'Badges', 'points-and-rewards-for-woocommerce' ); ?>">
													<input type="hidden" name="wps_wpr_image_attachment_id[]" class="wps_wpr_image_attachment_id" value="<?php echo esc_url( $wps_wpr_image_attachment_id[ $key ] ); ?>"/>
												</td>
												<?php
												if ( $key > $wps_wpr_remove_badges_counter ) {
													?>
													<td><input type="button" name="wps_wpr_remove_user_badges" id="wps_wpr_remove_user_badges" class="wps_wpr_remove_user_badges" value="Remove"></td>
													<?php
												}
												?>
											</tr>
											<?php
										}
									}
								} else {
									$default_threshold_points   = 5000;
									$default_badge_award_points = 10;
									$img_array_store            = array(
										'1' => WPS_RWPR_DIR_URL . 'admin/images/base.png',
										'2' => WPS_RWPR_DIR_URL . 'admin/images/gold.png',
									);
									for ( $i = 1; $i <= 2; $i++ ) {
										?>
										<tr class="wps_wpr_add_user_badges_dynamic">
											<td><input type="text" name="wps_wpr_enter_badges_name[]" id="wps_wpr_enter_badges_name" class="wps_wpr_enter_badges_name" value="<?php echo esc_html( 'Badge -' . $i ); ?>" required></td>
											<td><input type="number" min="1" name="wps_wpr_badges_threshold_points[]" id="wps_wpr_badges_threshold_points" class="wps_wpr_badges_threshold_points" value="<?php echo esc_html( $default_threshold_points ); ?>" required></td>
											<td><input type="number" min="1" name="wps_wpr_badges_rewards_points[]" id="wps_wpr_badges_rewards_points" class="wps_wpr_badges_rewards_points" value="<?php echo esc_html( $default_badge_award_points ); ?>" required></td>
											<td>
												<img src="<?php echo esc_url( $img_array_store[ $i ] ); ?>" class="wps_wpr_icon_user_badges">
												<input type="button" class="wps_wpr_add_user_badges_img" value="<?php esc_html_e( 'Badges', 'points-and-rewards-for-woocommerce' ); ?>">
												<input type="hidden" name="wps_wpr_image_attachment_id[]" class="wps_wpr_image_attachment_id" value="<?php echo esc_url( $img_array_store[ $i ] ); ?>"/>
											</td>
										</tr>
										<?php
										$default_threshold_points   += 5000;
										$default_badge_award_points += 10;
									}
								}
								?>
							</tbody>
						</table>
					</div>
					<input type="button" name="wps_wpr_user_badges_fields_add" id="wps_wpr_user_badges_fields_add" value="<?php esc_html_e( 'Add More', 'points-and-rewards-for-woocommerce' ); ?>">
				</div>
			</section>
		</main>
		<input type="submit" name="wps_wpr_save_user_badges_settings" id="wps_wpr_save_user_badges_settings" value="<?php esc_html_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
	</form>
</div>
