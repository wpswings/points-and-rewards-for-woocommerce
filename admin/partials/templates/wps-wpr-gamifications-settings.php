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

// Save gamification settings.
if ( isset( $_POST['wps_wpr_gamification_setting_nonce'] ) ) {
	if ( wp_verify_nonce( ! empty( $_POST['wps_wpr_gamification_setting_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_gamification_setting_nonce'] ) ) : '', 'gamification-setting-nonce' ) ) {
		if ( isset( $_POST['wps_wpr_save_gamification_settings'] ) ) {

			$wps_wpr_user_gamification_settings = array();
			$wps_wpr_user_gamification_settings['wps_wpr_enable_gamification_settings'] = ! empty( $_POST['wps_wpr_enable_gamification_settings'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_enable_gamification_settings'] ) ) : '';
			$wps_wpr_user_gamification_settings['wps_wpr_select_icon_position']         = ! empty( $_POST['wps_wpr_select_icon_position'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_select_icon_position'] ) ) : '';
			$wps_wpr_user_gamification_settings['wps_wpr_select_win_wheel_page']        = ! empty( $_POST['wps_wpr_select_win_wheel_page'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_select_win_wheel_page'] ), 'sanitize_text_field' ) : array();
			$wps_wpr_user_gamification_settings['wps_wpr_select_spin_stop']             = ! empty( $_POST['wps_wpr_select_spin_stop'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_select_spin_stop'] ), 'sanitize_text_field' ) : array();
			$wps_wpr_user_gamification_settings['wps_wpr_days_after_user_play_again']   = ! empty( $_POST['wps_wpr_days_after_user_play_again'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_days_after_user_play_again'] ) ) : 0;

			$wps_wpr_user_gamification_settings['wps_wpr_enter_segment_name']      = ! empty( $_POST['wps_wpr_enter_segment_name'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_enter_segment_name'] ), 'sanitize_text_field' ) : array();
			$wps_wpr_user_gamification_settings['wps_wpr_enter_segment_points']    = ! empty( $_POST['wps_wpr_enter_segment_points'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_enter_segment_points'] ), 'sanitize_text_field' ) : array();
			$wps_wpr_user_gamification_settings['wps_wpr_enter_sgemnet_font_size'] = ! empty( $_POST['wps_wpr_enter_sgemnet_font_size'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_enter_sgemnet_font_size'] ), 'sanitize_text_field' ) : '15';
			$wps_wpr_user_gamification_settings['wps_wpr_enter_segment_color']     = ! empty( $_POST['wps_wpr_enter_segment_color'] ) ? map_deep( wp_unslash( $_POST['wps_wpr_enter_segment_color'] ), 'sanitize_text_field' ) : '#00b3b3';

			$wps_wpr_save_gami_setting = array();
			if ( ! empty( $wps_wpr_user_gamification_settings ) && is_array( $wps_wpr_user_gamification_settings ) ) {
				foreach ( $wps_wpr_user_gamification_settings as $key => $value ) {

					$wps_wpr_save_gami_setting[ $key ] = $value;
				}
			}

			if ( ! empty( $wps_wpr_save_gami_setting ) && is_array( $wps_wpr_save_gami_setting ) ) {

				update_option( 'wps_wpr_save_gami_setting', $wps_wpr_save_gami_setting );
			}

			// Show saved msg.
			$settings_obj->wps_wpr_settings_saved();
		}
	}
}

// get gami settings.
$wps_wpr_save_gami_setting            = get_option( 'wps_wpr_save_gami_setting', array() );
$wps_wpr_save_gami_setting            = ! empty( $wps_wpr_save_gami_setting ) && is_array( $wps_wpr_save_gami_setting ) ? $wps_wpr_save_gami_setting : array();
$wps_wpr_enable_gamification_settings = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enable_gamification_settings'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enable_gamification_settings'] : '';
$wps_wpr_select_icon_position         = ! empty( $wps_wpr_save_gami_setting['wps_wpr_select_icon_position'] ) ? $wps_wpr_save_gami_setting['wps_wpr_select_icon_position'] : '';
$wps_wpr_select_win_wheel_page        = ! empty( $wps_wpr_save_gami_setting['wps_wpr_select_win_wheel_page'] ) ? $wps_wpr_save_gami_setting['wps_wpr_select_win_wheel_page'] : array();
$wps_wpr_select_spin_stop             = ! empty( $wps_wpr_save_gami_setting['wps_wpr_select_spin_stop'] ) ? $wps_wpr_save_gami_setting['wps_wpr_select_spin_stop'] : array();
$wps_wpr_days_after_user_play_again   = ! empty( $wps_wpr_save_gami_setting['wps_wpr_days_after_user_play_again'] ) ? $wps_wpr_save_gami_setting['wps_wpr_days_after_user_play_again'] : 0;

$wps_wpr_enter_segment_name      = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_name'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_name'] : array();
$wps_wpr_enter_segment_points    = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_points'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_points'] : array();
$wps_wpr_enter_sgemnet_font_size = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_sgemnet_font_size'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_sgemnet_font_size'] : '15';
$wps_wpr_enter_segment_color     = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_color'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_color'] : '#00b3b3';

/**
 * This function is used to get random color.
 *
 * @return string
 */
function wps_wpr_rand_color() {

	return '#' . str_pad( dechex( wp_rand( 0, 0xFFFFFF ) ), 6, '0', STR_PAD_LEFT );
}
?>

<div class="wps_wpr_user_gamifications_main_wrappers">
	<h4 class="wps_wpr_gamifications_settings_heading wps_wpr_doc_video_wrapper"><?php esc_html_e( 'Gamification', 'points-and-rewards-for-woocommerce' ); ?><div class="wps_wpr_doc_video"><a href="https://docs.wpswings.com/gamification/?utm_source=wpswings-gamification-doc&utm_medium=par-org-page&utm_campaign=gamification-documentation" target="_blank" class="button"><?php esc_html_e( 'Docs', 'points-and-rewards-for-woocommerce' ); ?></a><a href="https://www.youtube.com/watch?v=DQ2iN9GHVK8" target="_blank" class="button"><?php esc_html_e( 'Video', 'points-and-rewards-for-woocommerce' ); ?></a></div></h4>
	<input type="hidden" name="wps_wpr_gamification_setting_nonce" id="wps_wpr_gamification_setting_nonce" value="<?php echo esc_html( wp_create_nonce( 'gamification-setting-nonce' ) ); ?>">
	<form method="POST" action="" class="wps_wpr_gamification_form">
		<main class="wps_wpr_main_gamification_wrapper">
			<section>
				<article>
					<label for="wps_wpr_enable_gamification_settings"><?php esc_html_e( 'Enable Gamification', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_enable_gamification_setting_wrapper">
						<input type="checkbox" name="wps_wpr_enable_gamification_settings" class="wps_wpr_enable_gamification_settings" value="yes" <?php checked( $wps_wpr_enable_gamification_settings, 'yes' ); ?>>
						<span class="wps_wpr_enable_gamification_notices wps_wpr_label_notice"><?php esc_html_e( 'Toggle this to enable this settings.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
			<section>
				<article>
					<label for="wps_wpr_select_icon_position"><?php esc_html_e( 'Select Canvas Icon Position', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_select_icon_wrapper">
						<select id="wps_wpr_select_icon_position" name="wps_wpr_select_icon_position">
							<option value="top_left" <?php selected( $wps_wpr_select_icon_position, 'top_left' ); ?>><?php esc_html_e( 'Top Left', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="top_right" <?php selected( $wps_wpr_select_icon_position, 'top_right' ); ?>><?php esc_html_e( 'Top Right', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="middle_left" <?php selected( $wps_wpr_select_icon_position, 'middle_left' ); ?>><?php esc_html_e( 'Middle Left', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="middle_right" <?php selected( $wps_wpr_select_icon_position, 'middle_right' ); ?>><?php esc_html_e( 'Middle Right', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="bottom_left" <?php selected( $wps_wpr_select_icon_position, 'bottom_left' ); ?>><?php esc_html_e( 'Bottom Left', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="bottom_right" <?php selected( $wps_wpr_select_icon_position, 'bottom_right' ); ?>><?php esc_html_e( 'Bottom Right', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
						<span class="wps_wpr_select_icon_notices wps_wpr_label_notice"><?php esc_html_e( 'Choose Win Wheel Icon position where you want show.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
			<section>
				<article>
					<label for="wps_wpr_select_win_wheel_page"><?php esc_html_e( 'Select Pages To Show Win Wheel', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_select_win_wheel_wrapper">
						<select id="wps_wpr_select_win_wheel_page" name="wps_wpr_select_win_wheel_page[]" multiple>
							<?php
							foreach ( get_pages() as $page_data ) {
								?>
								<option value="<?php echo esc_html( $page_data->ID ); ?>" <?php selected( in_array( $page_data->ID, $wps_wpr_select_win_wheel_page ) ); ?>><?php echo esc_html( $page_data->post_title ); ?></option>
								<?php
							}
							?>
						</select>
						<span class="wps_wpr_select_win_wheel_page_notices wps_wpr_label_notice"><?php esc_html_e( 'Choose Win Wheel Pages', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
			<section>
				<article>
					<label for="wps_wpr_select_spin_stop"><?php esc_html_e( 'Choose Segments To Stop Spinner', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_select_win_wheel_wrapper">
						<select id="wps_wpr_select_spin_stop" name="wps_wpr_select_spin_stop[]" multiple>
							<?php
							if ( ! empty( $wps_wpr_enter_segment_name ) && is_array( $wps_wpr_enter_segment_name ) ) {
								$segment_counter = 0;
								foreach ( $wps_wpr_enter_segment_name as $segment__name ) {
									++$segment_counter;
									?>
									<option value="<?php echo esc_html( $segment_counter ); ?>" <?php selected( in_array( $segment_counter, $wps_wpr_select_spin_stop ) ); ?>><?php echo esc_html( $segment__name ); ?></option>
									<?php
								}
							} else {
								$stop_count = 0;
								for ( $i = 1; $i <= 6; $i++ ) {
									++$stop_count;
									?>
									<option value="<?php echo esc_html( $stop_count ); ?>"><?php echo esc_html( 'Segment - ' . $stop_count ); ?></option>
									<?php
								}
							}
							?>
						</select>
						<span class="wps_wpr_select__spin_stop_notices wps_wpr_label_notice"><?php esc_html_e( 'Choose Segments on which spinner is stop, else it will stop randomly', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
			<section>
				<article>
					<label for="wps_wpr_days_after_user_play_again"><?php esc_html_e( 'Specify the Duration ( days )', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_play_again_wrapper">
						<input type="number" min="0" name="wps_wpr_days_after_user_play_again" class="wps_wpr_days_after_user_play_again" value="<?php echo esc_html( $wps_wpr_days_after_user_play_again ); ?>">
						<span class="wps_wpr_play_again_notices wps_wpr_label_notice"><?php esc_html_e( 'Admin can set a cooldown period after which users can play the game again.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</section>
			<div class="wps_wpr_win_wheel_segments_data">
				<div>
					<h4 for="wps_wpr_win_wheel_segments_settings"><?php esc_html_e( 'Win Wheel Segments', 'points-and-rewards-for-woocommerce' ); ?></h4>
					<div class="wps_wpr_win_wheel_segments_data-table">
						<table class="wps_wpr_segment_gamification_settings_wrappers">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Segment Name', 'points-and-rewards-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Segment Points', 'points-and-rewards-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Segment Font Size', 'points-and-rewards-for-woocommerce' ); ?></th>
									<th><?php esc_html_e( 'Segment Color', 'points-and-rewards-for-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$count = 1;

								// calculation to show cross symbol.
								if ( count( $wps_wpr_enter_segment_name ) > 6 ) {

									$wps_wpr_remove_segments_counter = 0;
								} else {

									$wps_wpr_remove_segments_counter = 5;
								}
								if ( ! empty( $wps_wpr_enter_segment_name[0] ) && ! empty( $wps_wpr_enter_segment_points[0] ) && ! empty( $wps_wpr_enter_segment_color[0] ) && ! empty( $wps_wpr_enter_sgemnet_font_size[0] ) ) {
									if ( count( $wps_wpr_enter_segment_name ) === count( $wps_wpr_enter_segment_points ) && count( $wps_wpr_enter_segment_points ) === count( $wps_wpr_enter_segment_color ) && count( $wps_wpr_enter_segment_color ) === count( $wps_wpr_enter_sgemnet_font_size ) ) {
										foreach ( $wps_wpr_enter_segment_name as $key => $value ) {
											?>
											<tr class="wps_wpr_add_game_segment_dynamically">
												<td><input type="text" name="wps_wpr_enter_segment_name[]" id="wps_wpr_enter_segment_name" class="wps_wpr_enter_segment_name" value="<?php echo ! empty( $wps_wpr_enter_segment_name[ $key ] ) ? esc_html( $wps_wpr_enter_segment_name[ $key ] ) : ''; ?>" required></td>
												<td><input type="number" min="1" name="wps_wpr_enter_segment_points[]" id="wps_wpr_enter_segment_points" class="wps_wpr_enter_segment_points" value="<?php echo ! empty( $wps_wpr_enter_segment_points[ $key ] ) ? esc_html( $wps_wpr_enter_segment_points[ $key ] ) : ''; ?>" required></td>
												<td><input type="number" max="20" min="1" name="wps_wpr_enter_sgemnet_font_size[]" id="wps_wpr_enter_sgemnet_font_size" class="wps_wpr_enter_sgemnet_font_size" value="<?php echo ! empty( $wps_wpr_enter_sgemnet_font_size[ $key ] ) ? esc_html( $wps_wpr_enter_sgemnet_font_size[ $key ] ) : ''; ?>" required></td>
												<td><input type="color" name="wps_wpr_enter_segment_color[]" id="wps_wpr_enter_segment_color" class="wps_wpr_enter_segment_color" value="<?php echo ! empty( $wps_wpr_enter_segment_color[ $key ] ) ? esc_html( $wps_wpr_enter_segment_color[ $key ] ) : ''; ?>" required></td>
												<?php
												if ( $key > $wps_wpr_remove_segments_counter ) {
													?>
													<td><input type="button" name="wps_wpr_remove_game_segment" id="wps_wpr_remove_game_segment" class="wps_wpr_remove_game_segment" value="<?php esc_html_e( '+', 'points-and-rewards-for-woocommerce' ); ?>"></td>
													<?php
												}
												?>
											</tr>
											<?php
											++$count;
										}
									}
								} else {
									$points    = 5;
									$seg_count = 1;
									for ( $i = 1; $i <= 6; $i++ ) {
										?>
										<tr class="wps_wpr_add_game_segment_dynamically">
											<td><input type="text" name="wps_wpr_enter_segment_name[]" id="wps_wpr_enter_segment_name" class="wps_wpr_enter_segment_name" value="<?php echo esc_html( 'Segment - ' . $seg_count ); ?>" required></td>
											<td><input type="number" min="1" name="wps_wpr_enter_segment_points[]" id="wps_wpr_enter_segment_points" class="wps_wpr_enter_segment_points" value="<?php echo esc_html( $points ); ?>" required></td>
											<td><input type="number" max="20" min="1" name="wps_wpr_enter_sgemnet_font_size[]" id="wps_wpr_enter_sgemnet_font_size" class="wps_wpr_enter_sgemnet_font_size" value="<?php echo esc_html( '15' ); ?>" required></td>
											<td><input type="color" name="wps_wpr_enter_segment_color[]" id="wps_wpr_enter_segment_color" class="wps_wpr_enter_segment_color" value="<?php echo esc_html( wps_wpr_rand_color() ); ?>" required></td>
										</tr>
										<?php
										$points += 5;
										++$seg_count;
									}
								}
								?>
							</tbody>
						</table>
					</div>
					<input type="button" name="wps_wpr_gamification_fields_add" id="wps_wpr_gamification_fields_add" value="<?php esc_html_e( '+', 'points-and-rewards-for-woocommerce' ); ?>" data-count="<?php echo esc_html( $count ); ?>">
				</div>
			</div>
		</main>
		<input type="submit" name="wps_wpr_save_gamification_settings" id="wps_wpr_save_gamification_settings" value="<?php esc_html_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>">
	</form>
</div>
