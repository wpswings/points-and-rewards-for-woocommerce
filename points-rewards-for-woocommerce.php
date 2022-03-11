<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpswings.com/
 * @since             1.0.0
 * @package           points-and-rewards-for-wooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Points and Rewards for WooCommerce
 * Description:       <code><strong>Points and Rewards for WooCommerce</strong></code> allow merchants to reward their customers with loyalty points.<a href="https://wpswings.com/product/?utm_source=wpswings-shop-page&utm_medium=par-org-page&utm_campaign=more-plugin" target="_blank"> Elevate your e-commerce store by exploring more on <strong> WP Swings </strong></a>
 * Version:           1.2.5
 * Author:            WP Swings
 * Author URI:        https://wpswings.com/
 * Plugin URI:        https://wpswings.com/product/?utm_source=wpswings-shop-page&utm_medium=par-org-page&utm_campaign=more-plugin
 * Text Domain:       points-and-rewards-for-woocommerce
 * Domain Path:       /languages
 *
 * Requires at least: 4.4.0
 * Tested up to:     5.9.2
 * WC requires at least: 3.0.0
 * WC tested up to:  6.3.1
 *
 * License:           GNU General Public License v3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
// To Activate plugin only when WooCommerce is active.
$activated = false;

// Check if WooCommerce is active.
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	$activated = true;
}

if ( is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {

	$plug = get_plugins();
	if ( isset( $plug['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php'] ) ) {

		if ( $plug['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php']['Version'] < '1.2.6' ) {
			unset( $_GET['activate'] );
			deactivate_plugins( plugin_basename( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) );
		}
	}
}
if ( $activated ) {

	/**
	 * Define the constatant of the plugin.
	 *
	 * @name define_rewardeem_woocommerce_points_rewards_constants.
	 */
	function define_rewardeem_woocommerce_points_rewards_constants() {

		rewardeem_woocommerce_points_rewards_constants( 'REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION', '1.2.6' );
		rewardeem_woocommerce_points_rewards_constants( 'WPS_RWPR_DIR_PATH', plugin_dir_path( __FILE__ ) );
		rewardeem_woocommerce_points_rewards_constants( 'WPS_RWPR_DIR_URL', plugin_dir_url( __FILE__ ) );
		rewardeem_woocommerce_points_rewards_constants( 'WPS_RWPR_HOME_URL', admin_url() );
	}


	/**
	 * Callable function for defining plugin constants.
	 *
	 * @name rewardeem_woocommerce_points_rewards_constants.
	 * @param string $key key of the constant.
	 * @param string $value value of the constant.
	 */
	function rewardeem_woocommerce_points_rewards_constants( $key, $value ) {

		if ( ! defined( $key ) ) {

			define( $key, $value );
		}
	}

	add_filter( 'plugin_row_meta', 'wps_wpr_doc_and_premium_link', 10, 2 );

	/**
	 * Callable function for adding plugin row meta.
	 *
	 * @name wps_wpr_doc_and_premium_link.
	 * @param string $links link of the constant.
	 * @param array  $file name of the plugin.
	 */
	function wps_wpr_doc_and_premium_link( $links, $file ) {

		if ( strpos( $file, 'points-rewards-for-woocommerce.php' ) !== false ) {

			$row_meta = array(
				'demo' => '<a target="_blank" href="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/?utm_source=wpswings-par-demo&utm_medium=par-org-backend&utm_campaign=demo"><i class="fas fa-laptop" style="margin-right:3px;"></i>' . esc_html__( 'Premium Demo', 'points-and-rewards-for-woocommerce' ) . '</a>',

				'docs'    => '<a target="_blank" href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=documentation"><i class="far fa-file-alt" style="margin-right:3px;"></i>' . esc_html__( 'Documentation', 'points-and-rewards-for-woocommerce' ) . '</a>',

				'support' => '<a target="_blank" href="https://wpswings.com/submit-query/?utm_source=wpswings-par-support&utm_medium=par-org-backend&utm_campaign=support"><i class="fas fa-user-ninja" style="margin-right:3px;"></i>' . esc_html__( 'Support', 'points-and-rewards-for-woocommerce' ) . '</a>',

			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Dynamically Generate referral Code
	 *
	 * @name wps_wpr_create_referral_code
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function wps_wpr_create_referral_code() {
		$length = 10;
		$pkey = '';
		$alphabets = range( 'A', 'Z' );
		$numbers = range( '0', '9' );
		$final_array = array_merge( $alphabets, $numbers );
		while ( $length-- ) {
			$key = array_rand( $final_array );
			$pkey .= $final_array[ $key ];
		}
		return $pkey;
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-points-rewards-for-woocommerce.php';

	add_shortcode( 'MYCURRENTPOINT', 'wps_wpr_mytotalpoint_shortcode' );

	/**
	 * Shortcode for the total points
	 *
	 * @name wps_wpr_mytotalpoint_shortcode
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function wps_wpr_mytotalpoint_shortcode() {
		$user_ID = get_current_user_ID();
		$wps_wpr_other_settings = get_option( 'wps_wpr_other_settings', array() );
		if ( ! empty( $wps_wpr_other_settings['wps_wpr_other_shortcode_text'] ) ) {
			$wps_wpr_shortcode_text_point = $wps_wpr_other_settings['wps_wpr_other_shortcode_text'];
		} else {
			$wps_wpr_shortcode_text_point = __( 'Your Current Point', 'points-and-rewards-for-woocommerce' );
		}
		if ( isset( $user_ID ) && ! empty( $user_ID ) ) {
			$get_points = (int) get_user_meta( $user_ID, 'wps_wpr_points', true );
			return '<div class="wps_wpr_shortcode_wrapper">' . $wps_wpr_shortcode_text_point . ' ' . $get_points . '</div>';
		}
	}
	add_shortcode( 'MYCURRENTUSERLEVEL', 'wps_wpr_mycurrentlevel_shortcode' );
	/**
	 * Display your Current Level by using shortcode
	 *
	 * @name wps_wpr_mycurrentlevel_shortcode
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function wps_wpr_mycurrentlevel_shortcode() {
		$user_ID = get_current_user_ID();
		$wps_wpr_other_settings = get_option( 'wps_wpr_other_settings', array() );
		if ( ! empty( $wps_wpr_other_settings['wps_wpr_shortcode_text_membership'] ) ) {
			$wps_wpr_shortcode_text_membership = $wps_wpr_other_settings['wps_wpr_shortcode_text_membership'];
		} else {
			$wps_wpr_shortcode_text_membership = __( 'Your Current Membership Level is', 'points-and-rewards-for-woocommerce' );
		}
		if ( isset( $user_ID ) && ! empty( $user_ID ) ) {
			$user_level = get_user_meta( $user_ID, 'membership_level', true );
			if ( isset( $user_level ) && ! empty( $user_level ) ) {
				return $wps_wpr_shortcode_text_membership . ' ' . $user_level;
			}
		}
	}
	add_shortcode( 'SIGNUPNOTIFICATION', 'wps_wpr_signupnotif_shortcode' );
	/**
	 * Display the SIgnup Notification by using shortcode
	 *
	 * @name wps_wpr_signupnotif_shortcode
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function wps_wpr_signupnotif_shortcode() {
		$general_settings = get_option( 'wps_wpr_settings_gallery', true );
		$enable_wps_signup = isset( $general_settings['wps_wpr_general_signup'] ) ? intval( $general_settings['wps_wpr_general_signup'] ) : 0;
		if ( $enable_wps_signup && ! is_user_logged_in() ) {
			$wps_wpr_signup_value = isset( $general_settings['wps_wpr_general_signup_value'] ) ? intval( $general_settings['wps_wpr_general_signup_value'] ) : 1;

			return '<div class="woocommerce-message">' . esc_html__( 'You will get ', 'points-and-rewards-for-woocommerce' ) . esc_html( $wps_wpr_signup_value ) . esc_html__( ' points for SignUp', 'points-and-rewards-for-woocommerce' ) . '</div>';
		}
	}

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_rewardeem_woocommerce_points_rewards() {
		define_rewardeem_woocommerce_points_rewards_constants();
		$plugin = new Points_Rewards_For_Woocommerce();
		$plugin->run();

	}
	run_rewardeem_woocommerce_points_rewards();

	// Add settings link on plugin page.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'rewardeem_woocommerce_points_rewards_settings_link' );

	/**
	 * Settings tab of the plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_settings_link
	 * @param array $links array of the links.
	 * @since    1.0.0
	 */
	function rewardeem_woocommerce_points_rewards_settings_link( $links ) {

		$my_link = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wps-rwpr-setting' ) . '">' . esc_html__( 'Settings', 'points-and-rewards-for-woocommerce' ) . '</a>',
		);

		if ( ! is_plugin_active( 'points-and-rewards-for-woocommerce-pro/points-and-rewards-for-woocommerce-pro.php' ) ) {

			$my_link['goPro'] = '<a class="wps-wpr-go-pro" target="_blank" href="https://wpswings.com/product/points-and-rewards-for-woocommerce-pro/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro">' . esc_html__( 'GO PRO', 'points-and-rewards-for-woocommerce' ) . '</a>';
		}

		return array_merge( $my_link, $links );
	}

	/**
	 * This function is used to return the date format as per WP settings
	 *
	 * @name wps_wpr_set_the_wordpress_date_format
	 * @param string $saved_date saved data in the WordPress formet.
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function wps_wpr_set_the_wordpress_date_format( $saved_date ) {
		if ( get_locale() == 'zh_TW' ) {
			return $saved_date;
		}
		$saved_date = strtotime( $saved_date );
		$date_format = get_option( 'date_format', 'Y-m-d' );
		$time_format = get_option( 'time_format', 'g:i a' );
		$wp_date = date_i18n( $date_format, $saved_date );
		$wp_time = date_i18n( $time_format, $saved_date );
		$return_date = $wp_date . ' ' . $wp_time;
		return $return_date;
	}
	if ( ! function_exists( 'array_key_first' ) ) {
		/**
		 * This function is used to return the first key
		 *
		 * @name array_key_first
		 * @param array $arr optional parameter.
		 * @author makewebbetter<ticket@makewebbetter.com>
		 * @link https://www.makewebbetter.com/
		 */
		function array_key_first( array $arr ) {
			foreach ( $arr as $key => $unused ) {
				return $key;
			}
			return null;
		}
	}

	register_activation_hook( __FILE__, 'wps_wpr_flush_rewrite_rules' );
	register_deactivation_hook( __FILE__, 'wps_wpr_flush_rewrite_rules' );
	/**
	 * This function is used to create tabs
	 *
	 * @name wps_wpr_flush_rewrite_rules
	 * @since 1.1.0.
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function wps_wpr_flush_rewrite_rules() {

		add_rewrite_endpoint( 'points', EP_PAGES );
		add_rewrite_endpoint( 'view-log', EP_PAGES );
		flush_rewrite_rules();

	}
	/**
	 * Wps_wpr_convert_db_keys function
	 *
	 * @return void
	 */
	function wps_wpr_convert_db_keys() {

		$wps_check_key_exist = get_option( 'wps_par_org_convert_keys', false );
		if ( ! $wps_check_key_exist && function_exists( 'wps_wpr_convert_db_keys' ) ) {
				wps_normal_update_option();
				wps_general_settings_update_option();
				wps_per_currency_update_option();
				wps_points_notification_update_option();
				wps_add_points_membership_option();
				wps_wpr_assign_points();
				wps_wp_other_settings();
				wps_par_update_user_meta();
				wps_update_post_meta();
				wps_order_total_migrate();
				update_option( 'wps_par_org_convert_keys', true );
		}
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_normal_update_option() {
		$wps_value                        = get_option( 'mwb_wpr_notify_new_msg_id' );
		$mesg_wps_value                   = get_option( 'mwb_wpr_notify_new_message' );
		$mwb_wpr_notify_hide_notification = get_option( 'mwb_wpr_notify_hide_notification' );
		if ( ! empty( $wps_value ) ) {
			update_option( 'wps_wpr_notify_new_msg_id', $wps_value );
		}
		if ( ! empty( $mesg_wps_value ) ) {
			update_option( 'wps_wpr_notify_new_message', $mesg_wps_value );
		}
		if ( ! empty( $mwb_wpr_notify_hide_notification ) ) {
			update_option( 'wps_wpr_notify_hide_notification', $mesg_wps_value );
		}
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_general_settings_update_option() {
		$general_settings         = get_option( 'mwb_wpr_settings_gallery' ); 
		$general_migrate_settings = array();
		if ( ! empty( $general_settings ) ) {

			foreach ( $general_settings as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_settings_gallery', $general_migrate_settings );

	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_update_post_meta() {
		$user_post_meta_keys = array(
			'mwb_cart_discount#$fee_id',
			'mwb_cart_discount#points',
		);
		foreach ( $user_post_meta_keys as $index => $meta_key ) {
			$wps_orders = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'shop_order',
					'fields'      => 'ids', // return only ids.
					'order'       => 'ASC',
					'meta_key'	  => $user_post_meta_keys,//phpcs:ignore
					'post_status' => 'any',
				)
			);
			if ( ! empty( $wps_orders ) ) {

				foreach ( $wps_orders as $order_id ) {
					$new_key    = str_replace( 'mwb_', 'wps_', $meta_key );
					$meta_value = get_post_meta( $order_id, $meta_key, true );
					if ( ! empty( $meta_value ) ) {
						update_user_meta( $order_id, $new_key, $meta_value );
					}
				}
			}
		}
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_per_currency_update_option() {
		$wps_coupon_settings      = get_option( 'mwb_wpr_coupons_gallery'); 
		$general_migrate_settings = array();
		if ( ! empty( $wps_coupon_settings ) ) {

			foreach ( $wps_coupon_settings as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_coupons_gallery', $general_migrate_settings );
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_points_notification_update_option() {

		$wps_notification_gallery = get_option( 'mwb_wpr_notificatin_array' ); 
		$general_migrate_settings = array();
		if ( ! empty( $wps_notification_gallery ) ) {

			foreach ( $wps_notification_gallery as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_notificatin_array', $general_migrate_settings );

	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_par_update_user_meta() {

		$user_meta_keys = array(
			'mwb_points_referral',
			'mwb_points_referral_invite',
			'mwb_wpr_points',
			'mwb_wpr_no_of_orders',
		);

		foreach ( $user_meta_keys as $index => $meta_key ) {
			$users = get_users(
				array(
					'fields'   => 'ids',
					'meta_key' => $meta_key, //phpcs:ignore
				)
			);
			if ( ! empty( $users ) ) {

				foreach ( $users as $user_id ) {
					$new_key    = str_replace( 'mwb_', 'wps_', $meta_key );
					$meta_value = get_user_meta( $user_id, $meta_key, true );
					if ( ! empty( $meta_value ) ) {
						update_user_meta( $user_id, $new_key, $meta_value );
					}
				}
			}
		}
	}
/**
 * Undocumented function
 *
 * @return void
 */
	function wps_add_points_membership_option() {
		$wps_membership_gallery   = get_option( 'mwb_wpr_membership_settings' );
		$general_migrate_settings = array();
		if ( ! empty( $wps_membership_gallery ) ) {

			foreach ( $wps_membership_gallery as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_membership_settings', $general_migrate_settings );
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_wpr_assign_points() {

		$wps_assign_points_settings = get_option( 'mwb_wpr_assign_products_points' );
		$general_migrate_settings   = array();
		if ( ! empty( $wps_assign_points_settings ) ) {

			foreach ( $wps_assign_points_settings as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_assign_products_points', $general_migrate_settings );
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_wp_other_settings() {

		$wps_assign_points_settings = get_option( 'mwb_wpr_other_settings'); 

		$general_migrate_settings   = array();
		if ( ! empty( $wps_assign_points_settings ) ) {

			foreach ( $wps_assign_points_settings as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}

		update_option( 'wps_wpr_other_settings', $general_migrate_settings );

	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_order_total_migrate() {

		$wps_assign_points_settings = get_option( 'mwb_wpr_order_total_settings' );

		$general_migrate_settings   = array();
		if ( ! empty( $wps_assign_points_settings ) ) {

			foreach ( $wps_assign_points_settings as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}

		update_option( 'wps_wpr_order_total_settings', $general_migrate_settings );
	}
	/**
	 * Migration to new domain notice.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data.
	 * @param string $status Status filter currently applied to the plugin list.
	 */
	function wps_wpr_upgrade_notice( $plugin_file, $plugin_data, $status ) {

		?>
			<tr class="plugin-update-tr active notice-warning notice-alt">
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-success inline update-message notice-alt">
					<div class='wps-notice-title wps-notice-section'>
						<p><strong>IMPORTANT NOTICE:</strong></p>
					</div>
					<div class='wps-notice-content wps-notice-section'>
						<p>From this update <strong>Version 1.2.5</strong> onwards, the plugin and its support will be handled by <strong>WP Swings</strong>.</p><p><strong>WP Swings</strong> is just our improvised and rebranded version with all quality solutions and help being the same, so no worries at your end.
						Please connect with us for all setup, support, and update related queries without hesitation.</p>
					</div>
				</div>
			</td>
		</tr>
		<style>
			.wps-notice-section > p:before {
				content: none;
			}
		</style>
		<?php

	}
	add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), 'wps_wpr_upgrade_notice', 0, 3 );

	add_action( 'admin_notices', 'wps_wpr_updgrade_notice' );

	/**
	 * Migration to new domain notice.
	 */
	function wps_wpr_updgrade_notice() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'wps-rwpr-setting' === $tab ) { ?>

		<tr class="plugin-update-tr active notice-warning notice-alt">
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-success inline update-message notice-alt">
					<div class='wps-notice-title wps-notice-section'>
						<p><strong>IMPORTANT NOTICE:</strong></p>
					</div>
					<div class='wps-notice-content wps-notice-section'>
						<p>From this update <strong>Version 1.2.5</strong> onwards, the plugin and its support will be handled by <strong>WP Swings</strong>.</p><p><strong>WP Swings</strong> is just our improvised and rebranded version with all quality solutions and help being the same, so no worries at your end.
						Please connect with us for all setup, support, and update related queries without hesitation.</p>
					</div>
				</div>
			</td>
		</tr>
		<style>
			.wps-notice-section > p:before {
				content: none;
			}
		</style>
			<?php
		}
	}
} else {


	// WooCommerce is not active so deactivate this plugin.
	add_action( 'admin_init', 'rewardeem_woocommerce_points_rewards_activation_failure' );

	/**
	 * This function is used to deactivate plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_activation_failure
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function rewardeem_woocommerce_points_rewards_activation_failure() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	// Add admin error notice.
	add_action( 'admin_notices', 'rewardeem_woocommerce_points_rewards_activation_failure_admin_notice' );

	/**
	 * This function is used to deactivate plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_activation_failure
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function rewardeem_woocommerce_points_rewards_activation_failure_admin_notice() {
			// hide Plugin activated notice.
		unset( $_GET['activate'] );
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'WooCommerce is not activated, Please activate WooCommerce first to activate Points and Rewards for WooCommerce.', 'points-and-rewards-for-woocommerce' ); ?></p>
			</div>

			<?php
		}

	}
}
