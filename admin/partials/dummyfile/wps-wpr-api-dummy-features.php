<?php
/**
 * API Feature Template.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Points_And_Rewards_For_Woocommerce_Pro
 * @subpackage Points_And_Rewards_For_Woocommerce_Pro/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WPS_RWPR_DIR_PATH . 'admin/class-points-rewards-for-woocommerce-dummy-settings.php';
$settings_obj = new Points_Rewards_For_WooCommerce_Dummy_Settings( '', '' );

$wps_api_array = array(
	array(
		'title' => __( 'API Settings', 'points-and-rewards-for-woocommerce' ),
		'type'  => 'title',
	),
	array(
		'title'    => __( 'API Features', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'checkbox',
		'desc'     => __( 'Enable API Features.', 'points-and-rewards-for-woocommerce' ),
		'id'       => 'wps_wpr_api_enable',
		'class'    => 'wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Toggle This to Enable API Features.', 'points-and-rewards-for-woocommerce' ),
		'default'  => 0,
	),
	array(
		'title'    => __( 'API secret key', 'points-and-rewards-for-woocommerce' ),
		'type'     => 'text',
		'id'       => 'wps_wpr_api_secret_key',
		'class'    => 'text_points wps_wpr_new_woo_ver_style_text wps_wpr_pro_plugin_settings',
		'desc_tip' => __( 'Use this secret key to use API features', 'points-and-rewards-for-woocommerce' ),
		'default'  => '',
	),
);

$wps_api_array[] = array(
	'type' => 'sectionend',
);

$wps_api_array    = apply_filters( 'wps_wpr_api_feature_before_settings', $wps_api_array );
$general_settings = get_option( 'wps_wpr_api_features_settings', true );
$general_settings = ! empty( $general_settings ) && is_array( $general_settings ) ? $general_settings : array();
?>
<div class="wps_table">
	<div class="wps_wpr_general_wrapper wps_wpr_pro_plugin_settings">
		<?php
		foreach ( $wps_api_array as $key => $value ) {
			if ( 'title' == $value['type'] ) {
				?>
				<div class="wps_wpr_general_row_wrap">
					<?php $settings_obj->wps_rwpr_generate_dummy_heading( $value ); ?>
				<?php
			}
			if ( 'title' != $value['type'] && 'sectionend' != $value['type'] ) {
				?>
				<div class="wps_wpr_general_row">
					<?php $settings_obj->wps_rwpr_generate_dummy_label( $value ); ?>
					<div class="wps_wpr_general_content">
						<?php
						$settings_obj->wps_rwpr_generate_dummy_tool_tip( $value );
						if ( 'checkbox' == $value['type'] ) {

							$settings_obj->wps_rwpr_generate_dummy_checkbox_html( $value, $general_settings );
						}
						if ( 'text' == $value['type'] ) {

							$settings_obj->wps_rwpr_generate_dummy_text_html( $value, $general_settings );
						}
						if ( 'singleSelectDropDownWithKeyvalue' == $value['type'] ) {
							$settings_obj->wps_wpr_generate_dummy_single_select_drop_down_with_key_value_pair( $value, $general_settings );
						}
						?>
					</div>
				</div>
				<?php
			}
			if ( 'sectionend' == $value['type'] ) :
				?>
				</div>
				<?php
			endif;
		}
		?>
	</div>
</div>
<div class="clear"></div>
<div class="submit">
	<input type="submit" value='<?php esc_html_e( 'Save Changes', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes wps_wpr_disabled_pro_plugin" name="wps_wpr_api_feature_save_changes">
	<input type="submit" value='<?php esc_html_e( 'Generate Secret Key', 'points-and-rewards-for-woocommerce' ); ?>' class="button-primary woocommerce-save-button wps_wpr_save_changes wps_wpr_disabled_pro_plugin" name="wps_wpr_api_feature" id="wps_wpr_api_feature">
</div>

<!-- ========= Display Plugin API details ========== -->

<div class="wps_wpr_api_details_main_wrapper wps_wpr_pro_plugin_settings">
	<h3><?php esc_html_e( 'Plugin API Details', 'points-and-rewards-for-woocommerce' ); ?></h3>

	<!-- Show Authentication -->
	<h4><?php esc_html_e( 'Authentication', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
			<?php
			esc_html_e( 'For authentication you need ', 'points-and-rewards-for-woocommerce' );
			esc_html_e( ' Consumer Secret ', 'points-and-rewards-for-woocommerce' );
			echo '<strong>{consumer_secret}</strong>';
			esc_html_e( ' keys. Response on wrong api details:', 'points-and-rewards-for-woocommerce' );
			?>
		</p>
		<?php
		echo '<pre>
		{
		"code": "rest_forbidden",
		"message": "Sorry, you are not allowed to do that.",
		"data": {
			"status": 401
		}
		}
		</pre>';
		?>
	</div>

	<!-- To get user points -->
	<h4><?php esc_html_e( 'To Retrive Particular User Points', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
		<?php
		echo '<strong>' . esc_html__( 'Base Url to get user points : ', 'points-and-rewards-for-woocommerce' ) . '</strong>';
		echo '{site_url}/wp-json/wpr/wps-get-points/user';
		?>
		</p>
		<p>
			<strong>
			<?php
			esc_html_e( 'Example : ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( site_url() );
			esc_html_e( '/wp-json/wpr/wps-get-points/user', 'points-and-rewards-for-woocommerce' );
			?>
			</strong>
		<p>
		<?php
		esc_html_e( 'Parameters Required : ', 'points-and-rewards-for-woocommerce' );
		echo '<strong>{user_id}, </strong>';
		echo '<strong> {consumer_secret}</strong>';
		?>
		</p>
		<p><?php esc_html_e( 'JSON response example:', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		echo '<pre>
		{
		"data": {
			"user_id": 1,
			"total_points": 358,
			"referal_link": "http://par-development.local?pkey=KI9XRA3MXD"
		},
		"status": "success",
		"code": 200
		}
		</pre>';
		?>
	</div>

	<!-- To get user points -->
	<h4><?php esc_html_e( 'To Retrive Particular User Points Log', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
		<?php
		echo '<strong>' . esc_html__( 'Base Url to get user points log : ', 'points-and-rewards-for-woocommerce' ) . '</strong>';
		echo '{site_url}/wp-json/wpr/wps-get-points/user/log';
		?>
		</p>
		<p>
			<strong>
			<?php
			esc_html_e( 'Example : ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( site_url() );
			esc_html_e( '/wp-json/wpr/wps-get-points/user/log', 'points-and-rewards-for-woocommerce' );
			?>
			</strong>
		<p>
		<p>
		<?php
		esc_html_e( 'Parameters Required : ', 'points-and-rewards-for-woocommerce' );
		echo '<strong>{user_id}, </strong>';
		echo '<strong> {consumer_secret}</strong>';
		?>
		</p>
		<p><?php esc_html_e( 'JSON response example:', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		echo '<pre>
		{
		"data": {
			"user_id": 1,
			"points_log": {
			"updated_by_admin_points_log": [
				{
				"points": "2",
				"date": "2023-06-09 03:36:48pm",
				"sign": "+",
				"reason": "test"
				}
			]
			}
		},
		"status": "success",
		"code": 200
		}
		</pre>';
		?>
	</div>

	<!-- To update user points -->
	<h4><?php esc_html_e( 'To Update Points of Particular Users', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
		<?php
		echo '<strong>' . esc_html__( 'Base Url to add points : ', 'points-and-rewards-for-woocommerce' ) . '</strong>';
		echo '{site_url}/wp-json/wpr/wps-add-par-points/user';
		?>
		</p>
		<p>
			<strong>
			<?php
			esc_html_e( 'Example : ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( site_url() );
			esc_html_e( '/wp-json/wpr/wps-add-par-points/user', 'points-and-rewards-for-woocommerce' );
			?>
			</strong>
		<p>
		<p>
		<?php
		esc_html_e( 'Parameters Required : ', 'points-and-rewards-for-woocommerce' );
		echo '<strong>{user_id}, {points}, {reason}, {consumer_secret}</strong>';
		?>
		</p>
		<p><?php esc_html_e( 'JSON response example:', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		echo '<pre>
		{
		"data": {
			"status": "success",
			"code": 200,
			"message": "Points Updated Successfully"
		}
		}
		</pre>';
		?>
	</div>

	<!-- To Deduct user points -->
	<h4><?php esc_html_e( 'To Remove Points of Particular Users', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
		<?php
		echo '<strong>' . esc_html__( 'Base Url to remove points : ', 'points-and-rewards-for-woocommerce' ) . '</strong>';
		echo '{site_url}/wp-json/wpr/wps-remove-par-points/user';
		?>
		</p>
		<p>
			<strong>
			<?php
			esc_html_e( 'Example : ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( site_url() );
			esc_html_e( '/wp-json/wpr/wps-remove-par-points/user', 'points-and-rewards-for-woocommerce' );
			?>
			</strong>
		<p>
		<p>
		<?php
		esc_html_e( 'Parameters Required : ', 'points-and-rewards-for-woocommerce' );
		echo '<strong>{user_id}, {points}, {reason}, {consumer_secret}</strong>';
		?>
		</p>
		<p><?php esc_html_e( 'JSON response example:', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		echo '<pre>
		{
		"data": {
			"status": "success",
			"code": 200,
			"message": "Points Reduced Successfully"
		}
		}
		</pre>';
		?>
	</div>

	<!-- To get specific user member level -->
	<h4><?php esc_html_e( 'To Retrive Particular Users Membership Level', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
		<?php
		echo '<strong>' . esc_html__( 'Base Url to get user membership level : ', 'points-and-rewards-for-woocommerce' ) . '</strong>';
		echo '{site_url}/wp-json/wpr/wps-get-user-level/user';
		?>
		</p>
		<p>
			<strong>
			<?php
			esc_html_e( 'Example : ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( site_url() );
			esc_html_e( '/wp-json/wpr/wps-get-user-level/user', 'points-and-rewards-for-woocommerce' );
			?>
			</strong>
		<p>
		<p>
		<?php
		esc_html_e( 'Parameters Required : ', 'points-and-rewards-for-woocommerce' );
		echo '<strong>{user_id}, {consumer_secret}</strong>';
		?>
		</p>
		<p><?php esc_html_e( 'JSON response example:', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		echo '<pre>
		{
		"data": {
			"user_id": "1",
			"user_level": "Silver"
		},
		"status": "success",
		"code": "200"
		}
		</pre>';
		?>
	</div>

	<!-- To get membership details -->
	<h4><?php esc_html_e( 'To Retrive Membership Details', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
		<?php
		echo '<strong>' . esc_html__( 'Base Url to get membership details : ', 'points-and-rewards-for-woocommerce' ) . '</strong>';
		echo '{site_url}/wp-json/wpr/wps-membership-details/user';
		?>
		</p>
		<p>
			<strong>
			<?php
			esc_html_e( 'Example : ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( site_url() );
			esc_html_e( '/wp-json/wpr/wps-membership-details/user', 'points-and-rewards-for-woocommerce' );
			?>
			</strong>
		<p>
		<p>
		<?php
		esc_html_e( 'Parameters Required : ', 'points-and-rewards-for-woocommerce' );
		echo '<strong>{consumer_secret}</strong>';
		?>
		</p>
		<p><?php esc_html_e( 'JSON response example:', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		echo '<pre>
		{
		"membership_count": 2,
		"status": "success",
		"code": "200",
		"data": {
			"level_0": {
			"membership_name": "Silver",
			"required_points": "10",
			"expiry_date": "2023-06-19",
			"discount": "20"
			},
			"level_1": {
			"membership_name": "Gold",
			"required_points": "20",
			"expiry_date": "2024-06-09",
			"discount": "40"
			}
		}
		}
		</pre>';
		?>
	</div>

	<!-- To update user membership level -->
	<h4><?php esc_html_e( 'To Update Membership Level', 'points-and-rewards-for-woocommerce' ); ?></h4>
	<div class="wps_wpr_rest_api_response">
		<p>
		<?php
		echo '<strong>' . esc_html__( 'Base Url to update membership level : ', 'points-and-rewards-for-woocommerce' ) . '</strong>';
		echo '{site_url}/wp-json/wpr/wps-update-member-level/user';
		?>
		</p>
		<p>
			<strong>
			<?php
			esc_html_e( 'Example : ', 'points-and-rewards-for-woocommerce' );
			echo esc_html( site_url() );
			esc_html_e( '/wp-json/wpr/wps-update-member-level/user', 'points-and-rewards-for-woocommerce' );
			?>
			</strong>
		<p>
		<p>
		<?php
		esc_html_e( 'Parameters Required : ', 'points-and-rewards-for-woocommerce' );
		echo '<strong>{user_id}, {member_name}, {consumer_secret}</strong>';
		?>
		</p>
		<p><?php esc_html_e( 'JSON response example:', 'points-and-rewards-for-woocommerce' ); ?></p>
		<?php
		echo '<pre>
		{
		"data": "Membership assigned successfully",
		"status": "success",
		"code": "200"
		}
		</pre>';
		?>
	</div>
</div>
