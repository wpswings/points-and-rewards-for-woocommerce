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
 * Description:       <code><strong>Points and Rewards for WooCommerce</strong></code> plugin allow merchants to reward their loyal customers with referral rewards points on store activities. <a href="https://wpswings.com/woocommerce-plugins/?utm_source=wpswings-shop-page&utm_medium=par-org-backend&utm_campaign=more-plugin" target="_blank"> Elevate your e-commerce store by exploring more on <strong> WP Swings </strong></a>
 * Version:           1.4.1
 * Author:            WP Swings
 * Author URI:        https://wpswings.com/?utm_source=wpswings-par-official&utm_medium=par-org-backend&utm_campaign=official
 * Plugin URI:        https://wordpress.org/plugins/points-and-rewards-for-woocommerce/
 * Text Domain:       points-and-rewards-for-woocommerce
 * Domain Path:       /languages
 *
 * Requires at least    : 5.5.0
 * Tested up to         : 6.1.1
 * WC requires at least : 5.5.0
 * WC tested up to      : 7.4.0
 *
 * License:           GNU General Public License v3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$wps_par_exists = false;
$plug           = get_plugins();

if ( isset( $plug['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php'] ) ) {
	if ( version_compare( $plug['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php']['Version'], '1.2.6', '<' ) ) {
		$wps_par_exists = true;
	}
}

// To Activate plugin only when WooCommerce is active.
$activated = false;
// Check if WooCommerce is active.
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$activated = true;
}

$plug = get_plugins();
if ( $activated ) {

	/**
	 * Define the constatant of the plugin.
	 *
	 * @name define_rewardeem_woocommerce_points_rewards_constants.
	 */
	function define_rewardeem_woocommerce_points_rewards_constants() {

		rewardeem_woocommerce_points_rewards_constants( 'REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION', '1.4.1' );
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
	 * @param string $file name of the plugin.
	 */
	function wps_wpr_doc_and_premium_link( $links, $file ) {

		if ( strpos( $file, 'points-rewards-for-woocommerce.php' ) !== false ) {

			$row_meta = array(
				'demo'     => '<a target="_blank" href="https://demo.wpswings.com/points-and-rewards-for-woocommerce-pro/?utm_source=wpswings-par-demo&utm_medium=par-org-backend&utm_campaign=par-demo"><i class="fas fa-laptop"></i><img src="' . esc_html( WPS_RWPR_DIR_URL ) . 'admin/images/Demo.svg" class="wps-info-img" alt="Demo image">' . esc_html__( 'Demo', 'points-and-rewards-for-woocommerce' ) . '</a>',
				'docs'     => '<a target="_blank" href="https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=par-doc"><i class="far fa-file-alt"></i><img src="' . esc_html( WPS_RWPR_DIR_URL ) . 'admin/images/Documentation.svg" class="wps-info-img" alt="Demo image">' . esc_html__( 'Documentation', 'points-and-rewards-for-woocommerce' ) . '</a>',
				'support'  => '<a target="_blank" href="https://wpswings.com/submit-query/?utm_source=wpswings-par-query&utm_medium=par-org-backend&utm_campaign=submit-query"><i class="fas fa-user-ninja"></i><img src="' . esc_html( WPS_RWPR_DIR_URL ) . 'admin/images/Support.svg" class="wps-info-img" alt="Demo image">' . esc_html__( 'Support', 'points-and-rewards-for-woocommerce' ) . '</a>',
				'services' => '<a target="_blank" href="https://wpswings.com/woocommerce-services/?utm_source=wpswings-par-services&utm_medium=par-org-backend&utm_campaign=woocommerce-services"><i class="fas fa-user-ninja"></i><img src="' . esc_html( WPS_RWPR_DIR_URL ) . 'admin/images/Services.svg" class="wps-info-img" alt="Demo image">' . esc_html__( 'Services', 'points-and-rewards-for-woocommerce' ) . '</a>',
			);
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}

	/**
	 * Dynamically Generate referral Code
	 *
	 * @name wps_wpr_create_referral_code
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_create_referral_code() {

		$length      = 10;
		$pkey        = '';
		$alphabets   = range( 'A', 'Z' );
		$numbers     = range( '0', '9' );
		$final_array = array_merge( $alphabets, $numbers );

		while ( $length-- ) {
			$key   = array_rand( $final_array );
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
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_mytotalpoint_shortcode() {
		$user_ID                = get_current_user_ID();
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
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_mycurrentlevel_shortcode() {
		$user_ID                = get_current_user_ID();
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
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_signupnotif_shortcode() {
		$general_settings  = get_option( 'wps_wpr_settings_gallery', true );
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
		$mfw_plugins = get_plugins();
		if ( ! isset( $mfw_plugins['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php'] ) ) {

			$my_link['goPro'] = '<a class="wps-wpr-go-pro" target="_blank" href="https://wpswings.com/product/points-and-rewards-for-woocommerce-pro/?utm_source=wpswings-par-pro&utm_medium=par-org-backend&utm_campaign=go-pro">' . esc_html__( 'GO PRO', 'points-and-rewards-for-woocommerce' ) . '</a>';
		}
		return array_merge( $my_link, $links );
	}

	/**
	 * This function is used to return the date format as per WP settings
	 *
	 * @name wps_wpr_set_the_wordpress_date_format
	 * @param string $saved_date saved data in the WordPress formet.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	function wps_wpr_set_the_wordpress_date_format( $saved_date ) {

		if ( get_locale() == 'zh_TW' ) {
			return $saved_date;
		}

		$saved_date  = strtotime( $saved_date );
		$date_format = get_option( 'date_format', 'Y-m-d' );
		$time_format = get_option( 'time_format', 'g:i a' );
		$wp_date     = date_i18n( $date_format, $saved_date );
		$wp_time     = date_i18n( $time_format, $saved_date );
		$return_date = $wp_date . ' ' . $wp_time;
		return $return_date;
	}

	if ( ! function_exists( 'array_key_first' ) ) {
		/**
		 * This function is used to return the first key
		 *
		 * @name array_key_first
		 * @param array $arr optional parameter.
		 * @author WP Swings <webmaster@wpswings.com>
		 * @link https://www.wpswings.com/
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
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
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
				wps_order_total_migrate();
				wps_product_purchase_point();
				wps_point_expiration_setting();
				wps_notification_add_on();
				wps_api_setting_addon();
				update_option( 'wps_par_org_convert_keys', true );
		}
	}

	/**
	 * Wps_api_setting_addon function
	 *
	 * @return void
	 */
	function wps_api_setting_addon() {

		$wps_prodct_points        = get_option( 'mwb_wpr_api_features_settings' );
		$general_migrate_settings = array();
		if ( ! empty( $wps_prodct_points ) ) {

			foreach ( $wps_prodct_points as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_api_features_settings', $general_migrate_settings );
	}

	/**
	 * Wps_product_purchase_point function
	 *
	 * @return void
	 */
	function wps_product_purchase_point() {

		$wps_prodct_points        = get_option( 'mwb_wpr_product_purchase_settings' );
		$general_migrate_settings = array();
		if ( ! empty( $wps_prodct_points ) ) {

			foreach ( $wps_prodct_points as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_product_purchase_settings', $general_migrate_settings );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_point_expiration_setting() {
		$wps_prodct_points        = get_option( 'mwb_wpr_points_expiration_settings' );
		$general_migrate_settings = array();
		if ( ! empty( $wps_prodct_points ) ) {

			foreach ( $wps_prodct_points as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_points_expiration_settings', $general_migrate_settings );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_notification_add_on() {
		$wps_prodct_points        = get_option( 'mwb_wpr_notification_addon_settings' );
		$general_migrate_settings = array();
		if ( ! empty( $wps_prodct_points ) ) {

			foreach ( $wps_prodct_points as $key => $value ) {
				$general_migrate_settings[ str_replace( 'mwb', 'wps', $key ) ] = str_replace( 'mwb', 'wps', $value );
			}
		}
		update_option( 'wps_wpr_notification_addon_settings', $general_migrate_settings );
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
	function wps_per_currency_update_option() {
		$wps_coupon_settings      = get_option( 'mwb_wpr_coupons_gallery' );
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

		$wps_assign_points_settings = get_option( 'mwb_wpr_other_settings' );
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

	if ( true === $wps_par_exists ) {

		add_action( 'admin_notices', 'wps_par_check_and_inform_update' );
		/**
		 * Check update if pro is old.
		 */
		function wps_par_check_and_inform_update() {

			$update_file = plugin_dir_path( dirname( __FILE__ ) ) . 'ultimate-woocommerce-points-and-rewards/class-ultimate-woocommerce-points-and-rewards-update.php';
			$wps_plug    = get_plugins();
			if ( isset( $wps_plug['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php'] ) ) {
				if ( version_compare( $wps_plug['ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php']['Version'], '1.2.1', '<' ) ) {
					$update_file = plugin_dir_path( dirname( __FILE__ ) ) . 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards-update.php';
				}
			}

			// If present but not active.
			if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {
				if ( file_exists( $update_file ) ) {
					$wps_par_pro_license_key = get_option( 'ultimate_woocommerce_points_and_rewards_lcns_key', '' );
					! defined( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_LICENSE_KEY' ) && define( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_LICENSE_KEY', $wps_par_pro_license_key );
					! defined( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_BASE_FILE' ) && define( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_BASE_FILE', 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' );
					! defined( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_VERSION' ) && define( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_VERSION', '1.4.1' );
				}
				require_once $update_file;
			}

			if ( defined( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_BASE_FILE' ) ) {
				$wps_par_version_old_pro = new Ultimate_Woocommerce_Points_And_Rewards_Update();
				$wps_par_version_old_pro->mwb_check_update();
			}
		}
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
				<div class="notice notice-error inline update-message notice-alt">
					<p class='wps-notice-title wps-notice-section'>
						<?php esc_html_e( 'The latest update includes some substantial changes across different areas of the plugin. Hence, if you are not a new user then', 'points-and-rewards-for-woocommerce' ); ?><strong><?php esc_html_e( ' please migrate your old data and settings from ', 'points-and-rewards-for-woocommerce' ); ?><a style="text-decoration:none;" href="<?php echo esc_url( admin_url( 'admin.php?page=wps-rwpr-setting' ) ); ?>"><?php esc_html_e( 'Dashboard', 'points-and-rewards-for-woocommerce' ); ?></strong></a><?php esc_html_e( ' page then Click On Start Import Button.', 'points-and-rewards-for-woocommerce' ); ?>
					</p>
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

	add_action( 'wp_loaded', 'wps_wpr_show_notice_on_plugin_dashboard' );
	/**
	 * This function is used to show notice on plugin listing page.
	 *
	 * @return void
	 */
	function wps_wpr_show_notice_on_plugin_dashboard() {
		if ( class_exists( 'Points_Rewards_For_WooCommerce_Admin' ) ) {

			$wps_par_get_count = new Points_Rewards_For_WooCommerce_Admin( 'points-and-rewards-for-woocommerce', '1.4.1' );
			$wps_pending_par   = $wps_par_get_count->wps_par_get_count( 'wc-pending' );
			$wps_pending_par   = ! empty( $wps_pending_par ) && is_array( $wps_pending_par ) ? count( $wps_pending_par ) : 0;
			$wps_count_users   = $wps_par_get_count->wps_par_get_count_users( 'users' );
			$wps_count_users   = ! empty( $wps_count_users ) && is_array( $wps_count_users ) ? count( $wps_count_users ) : 0;

			if ( 0 !== $wps_pending_par || 0 !== $wps_count_users ) {
				add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), 'wps_wpr_upgrade_notice', 0, 3 );
			}
		}
	}

	if ( true === $wps_par_exists ) {
		add_action( 'admin_notices', 'wps_wpr_updgrade_warning_notice' );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	function wps_wpr_updgrade_warning_notice() {
		$tab = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'wps-rwpr-setting' === $tab ) {
			?>
		<tr class="plugin-update-tr active notice-warning notice-alt">
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-error inline update-message notice-alt">
					<div class='wps-notice-title wps-notice-section'>
						<p><strong>IMPORTANT NOTICE:</strong></p>
					</div>
					<div class='wps-notice-content wps-notice-section'>
					<?php esc_html_e( 'You are Using the Old Version of Points and Rewards For WooCommerce Pro !!', 'points-and-rewards-for-woocommerce' ); ?><?php esc_html_e( ' Please Update it ', 'points-and-rewards-for-woocommerce' ); ?><a style="text-decoration:none;" href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><strong><?php esc_html_e( ' Click Here', 'points-and-rewards-for-woocommerce' ); ?></strong></a><?php esc_html_e( ' to update to the latest ', 'points-and-rewards-for-woocommerce' ); ?>	
					</div>
				</div>
			</td>
		</tr>
		<style>
			.treat-button {
			font-family: "Fascinate Inline", cursive;
			-webkit-appearance: none;
				-moz-appearance: none;
					appearance: none;
			background: linear-gradient(to bottom, #F46001, #E14802);
			border: none;
			color: #FFF;
			border-radius: 2em;
			padding: 0.6em 1.5em;
			width: 200px;
			font-size: 20px;
			overflow: hidden;
			-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none;
					user-select: none;
			cursor: pointer;
			z-index: 1;
			box-shadow: 0 0 1em rgba(255, 255, 255, 0.2);
			transition: transform 0.1s cubic-bezier(0.5, 0, 0.5, 1), box-shadow 0.2s;
			outline: none;
			}
			.treat-button:hover {
			box-shadow: 0 0 2em rgba(255, 255, 255, 0.3);
			}
			.wps-notice-section > p:before {
				content: none;
			}
		</style>
			<?php
		}
	}

	if ( true === $wps_par_exists ) {
		unset( $_GET['activate'] );
		deactivate_plugins( plugin_basename( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) );
	}
} else {

	// WooCommerce is not active so deactivate this plugin.
	add_action( 'admin_init', 'rewardeem_woocommerce_points_rewards_activation_failure' );

	/**
	 * This function is used to deactivate plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_activation_failure
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
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
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
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

