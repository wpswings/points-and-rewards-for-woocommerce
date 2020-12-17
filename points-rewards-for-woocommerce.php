<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://makewebbetter.com/
 * @since             1.0.0
 * @package           points-and-rewards-for-wooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Points and Rewards for WooCommerce
 * Description:       <code><strong>Points and Rewards for WooCommerce</strong></code> allow merchants to reward their customers with loyalty points.<a href="https://makewebbetter.com/wordpress-plugins/?utm_source=org-plugin&utm_medium=plugin-desc&utm_campaign=MWB-PAR-org" target="_blank"> Elevate your e-commerce store by exploring more on <strong> MakeWebBetter </strong></a>
 * Version:           1.0.11
 * Author:            MakeWebBetter
 * Author URI:        https://makewebbetter.com/
 * Plugin URI:        https://makewebbetter.com/product/woocommerce-points-and-rewards?utm_source=MWB-PAR-org&utm_medium=MWB-org-plugin&utm_campaign=MWB-PAR-org
 * Text Domain:       points-and-rewards-for-woocommerce
 * Domain Path:       /languages
 *
 * Requires at least: 5.0.0
 * Tested up to:      5.5
 * WC requires at least: 4.0
 * WC tested up to: 4.7
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
if ( $activated ) {
	/**
	 * Define the constatant of the plugin.
	 *
	 * @name define_rewardeem_woocommerce_points_rewards_constants.
	 */
	function define_rewardeem_woocommerce_points_rewards_constants() {

		rewardeem_woocommerce_points_rewards_constants( 'REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION', '1.0.11' );
		rewardeem_woocommerce_points_rewards_constants( 'MWB_RWPR_DIR_PATH', plugin_dir_path( __FILE__ ) );
		rewardeem_woocommerce_points_rewards_constants( 'MWB_RWPR_DIR_URL', plugin_dir_url( __FILE__ ) );
		rewardeem_woocommerce_points_rewards_constants( 'MWB_RWPR_HOME_URL', admin_url() );
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

	add_filter( 'plugin_row_meta', 'mwb_wpr_doc_and_premium_link', 10, 2 );

	/**
	 * Callable function for adding plugin row meta.
	 *
	 * @name mwb_wpr_doc_and_premium_link.
	 * @param string $links link of the constant.
	 * @param array  $file name of the plugin.
	 */
	function mwb_wpr_doc_and_premium_link( $links, $file ) {

		if ( strpos( $file, 'points-rewards-for-woocommerce.php' ) !== false ) {

			$row_meta = array(
				'demo' => '<a target="_blank" href="https://demo.makewebbetter.com/points-and-rewards-for-woocommerce/my-account?utm_source=MWB-PAR-org&utm_medium=MWB-org-plugin&utm_campaign=MWB-PAR-org"><i class="fas fa-laptop" style="margin-right:3px;"></i>' . esc_html__( 'Premium Demo', 'points-and-rewards-for-woocommerce' ) . '</a>',

				'docs'    => '<a target="_blank" href="https://docs.makewebbetter.com/points-rewards-for-woocommerce?utm_source=MWB-PAR-org&utm_medium=MWB-org-plugin&utm_campaign=MWB-PAR-org"><i class="far fa-file-alt" style="margin-right:3px;"></i>' . esc_html__( 'Documentation', 'points-and-rewards-for-woocommerce' ) . '</a>',

				'support' => '<a target="_blank" href="https://makewebbetter.com/submit-query/"><i class="fas fa-user-ninja" style="margin-right:3px;"></i>' . esc_html__( 'Support', 'points-and-rewards-for-woocommerce' ) . '</a>',

			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Dynamically Generate referral Code
	 *
	 * @name mwb_wpr_create_referral_code
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function mwb_wpr_create_referral_code() {
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

	add_shortcode( 'MYCURRENTPOINT', 'mwb_wpr_mytotalpoint_shortcode' );

	/**
	 * Shortcode for the total points
	 *
	 * @name mwb_wpr_mytotalpoint_shortcode
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function mwb_wpr_mytotalpoint_shortcode() {
		$user_ID = get_current_user_ID();
		$mwb_wpr_other_settings = get_option( 'mwb_wpr_other_settings', array() );
		if ( ! empty( $mwb_wpr_other_settings['mwb_wpr_other_shortcode_text'] ) ) {
			$mwb_wpr_shortcode_text_point = $mwb_wpr_other_settings['mwb_wpr_other_shortcode_text'];
		} else {
			$mwb_wpr_shortcode_text_point = __( 'Your Current Point', 'points-and-rewards-for-woocommerce' );
		}
		if ( isset( $user_ID ) && ! empty( $user_ID ) ) {
			$get_points = (int) get_user_meta( $user_ID, 'mwb_wpr_points', true );
			return '<div class="mwb_wpr_shortcode_wrapper">' . $mwb_wpr_shortcode_text_point . ' ' . $get_points . '</div>';
		}
	}
	add_shortcode( 'MYCURRENTUSERLEVEL', 'mwb_wpr_mycurrentlevel_shortcode' );
	/**
	 * Display your Current Level by using shortcode
	 *
	 * @name mwb_wpr_mycurrentlevel_shortcode
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function mwb_wpr_mycurrentlevel_shortcode() {
		$user_ID = get_current_user_ID();
		$mwb_wpr_other_settings = get_option( 'mwb_wpr_other_settings', array() );
		if ( ! empty( $mwb_wpr_other_settings['mwb_wpr_shortcode_text_membership'] ) ) {
			$mwb_wpr_shortcode_text_membership = $mwb_wpr_other_settings['mwb_wpr_shortcode_text_membership'];
		} else {
			$mwb_wpr_shortcode_text_membership = __( 'Your Current Level', 'points-and-rewards-for-woocommerce' );
		}
		if ( isset( $user_ID ) && ! empty( $user_ID ) ) {
			$user_level = get_user_meta( $user_ID, 'membership_level', true );
			if ( isset( $user_level ) && ! empty( $user_level ) ) {
				return $mwb_wpr_shortcode_text_membership . ' ' . $user_level;
			}
		}
	}
	add_shortcode( 'SIGNUPNOTIFICATION', 'mwb_wpr_signupnotif_shortcode' );
	/**
	 * Display the SIgnup Notification by using shortcode
	 *
	 * @name mwb_wpr_signupnotif_shortcode
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function mwb_wpr_signupnotif_shortcode() {
		$general_settings = get_option( 'mwb_wpr_settings_gallery', true );
		$enable_mwb_signup = isset( $general_settings['mwb_wpr_general_signup'] ) ? intval( $general_settings['mwb_wpr_general_signup'] ) : 0;
		if ( $enable_mwb_signup && ! is_user_logged_in() ) {
			$mwb_wpr_signup_value = isset( $general_settings['mwb_wpr_general_signup_value'] ) ? intval( $general_settings['mwb_wpr_general_signup_value'] ) : 1;

			 return '<div class="woocommerce-message">' . esc_html__( 'You will get ', 'points-and-rewards-for-woocommerce' ) . esc_html( $mwb_wpr_signup_value ) . esc_html__( ' points for SignUp', 'points-and-rewards-for-woocommerce' ) . '</div>';
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
			'settings' => '<a href="' . admin_url( 'admin.php?page=mwb-rwpr-setting' ) . '">' . esc_html__( 'Settings', 'points-and-rewards-for-woocommerce' ) . '</a>',
		);

		if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {

			$my_link['goPro'] = '<a class="mwb-wpr-go-pro" target="_blank" href="https://makewebbetter.com/product/woocommerce-points-and-rewards?utm_source=MWB-PAR-org&utm_medium=MWB-org-plugin&utm_campaign=MWB-PAR-org">' . esc_html__( 'GO PRO', 'points-and-rewards-for-woocommerce' ) . '</a>';
		}

		return array_merge( $my_link, $links );
	}

	/**
	 * This function is used to return the date format as per WP settings
	 *
	 * @name mwb_wpr_set_the_wordpress_date_format
	 * @param string $saved_date saved data in the wordpress formet.
	 * @author makewebbetter<ticket@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	function mwb_wpr_set_the_wordpress_date_format( $saved_date ) {
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
