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
 * @package           Ultimate_Woocommerce_Points_And_Rewards
 *
 * @wordpress-plugin
 * Plugin Name:       Ultimate WooCommerce Points and Rewards
 * Plugin URI:        https://makewebbetter.com/product/ultimate-woocommerce-points-and-rewards/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            makewebbetter
 * Author URI:        https://makewebbetter.com/
 * Text Domain:       ultimate-woocommerce-points-and-rewards
 * Domain Path:       /languages
 *
 * Requires at least: 4.6
 * Tested up to: 	  4.9.5
 *
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// To Activate plugin only when WooCommerce is active.
$activated = false;

// Check if WooCommerce is active.
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if (  is_plugin_active( 'woocommerce/woocommerce.php' ) &&  is_plugin_active( 'rewardeem-woocommerce-points-rewards/rewardeem-woocommerce-points-rewards.php') ) {

	$activated = true;
}

if($activated) {
		// Define plugin constants.
	function define_ultimate_woocommerce_points_and_rewards_constants() {

		ultimate_woocommerce_points_and_rewards_constants( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_VERSION', '1.0.0' );
		ultimate_woocommerce_points_and_rewards_constants( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_PATH', plugin_dir_path( __FILE__ ) );
		ultimate_woocommerce_points_and_rewards_constants( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_DIR_URL', plugin_dir_url( __FILE__ ) );
		ultimate_woocommerce_points_and_rewards_constants('MWB_UWPR_DOMAIN', "ultimate-woocommerce-points-and-rewards");

		// For License Validation.
		ultimate_woocommerce_points_and_rewards_constants( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_SPECIAL_SECRET_KEY', '59f32ad2f20102.74284991' );
		ultimate_woocommerce_points_and_rewards_constants( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_SERVER_URL', 'https://makewebbetter.com' );
		ultimate_woocommerce_points_and_rewards_constants( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_ITEM_REFERENCE', 'Ultimate WooCommerce Points and Rewards' );
		ultimate_woocommerce_points_and_rewards_constants( 'WPR_DOMAIN', 'ultimate-woocommerce-points-and-rewards' );
	}
	// Plugin Auto Update.
	function ultimate_woocommerce_points_and_rewards_auto_update() {

		$license_key = get_option( 'ultimate_woocommerce_points_and_rewards_lcns_key', '' );
		define( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_LICENSE_KEY', $license_key );
		define( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_BASE_FILE', __FILE__ );
		$update_check = "https://makewebbetter.com/pluginupdates/ultimate-woocommerce-points-and-rewards/update.php";
		require_once( 'ultimate-woocommerce-points-and-rewards-update.php' );
	}


	// Callable function for defining plugin constants.
	function ultimate_woocommerce_points_and_rewards_constants( $key, $value ) {

		if( ! defined( $key ) ) {

			define( $key, $value );
		}
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-ultimate-woocommerce-points-and-rewards-activator.php
	 */
	function activate_ultimate_woocommerce_points_and_rewards() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ultimate-woocommerce-points-and-rewards-activator.php';
		Ultimate_Woocommerce_Points_And_Rewards_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-ultimate-woocommerce-points-and-rewards-deactivator.php
	 */
	function deactivate_ultimate_woocommerce_points_and_rewards() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-ultimate-woocommerce-points-and-rewards-deactivator.php';
		Ultimate_Woocommerce_Points_And_Rewards_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_ultimate_woocommerce_points_and_rewards' );
	register_deactivation_hook( __FILE__, 'deactivate_ultimate_woocommerce_points_and_rewards' );

	/**
	* The core plugin class that is used to define internationalization,
	* admin-specific hooks, and public-facing site hooks.
	*/
	require plugin_dir_path( __FILE__ ) . 'includes/class-ultimate-woocommerce-points-and-rewards.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	*/
	function run_ultimate_woocommerce_points_and_rewards() {

		define_ultimate_woocommerce_points_and_rewards_constants();
		ultimate_woocommerce_points_and_rewards_auto_update();

		$plugin = new Ultimate_Woocommerce_Points_And_Rewards();
		$plugin->run();
	}
	run_ultimate_woocommerce_points_and_rewards();

	// Add settings link on plugin page.
	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'ultimate_woocommerce_points_and_rewards_settings_link' );

	// Settings link.
	function ultimate_woocommerce_points_and_rewards_settings_link( $links ) {

		$my_link = array(
			'<a href="' . admin_url( 'admin.php?page=mwb-rwpr-setting') . '">' . __( 'Settings', 'rewardeem-woocommerce-points-rewards' ) . '</a>',
			);
		return array_merge( $my_link, $links );
	}

	
}
else {
	
	// WooCommerce is not active so deactivate this plugin.
	add_action( 'admin_init', 'ultimate_woocommerce_points_rewards_activation_failure' );

	// Deactivate this plugin.
	function ultimate_woocommerce_points_rewards_activation_failure() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	// Add admin error notice.
	add_action( 'admin_notices', 'ultimate_woocommerce_points_rewards_activation_failure_admin_notice' );

	// This function is used to display admin error notice when WooCommerce is not active.
	function ultimate_woocommerce_points_rewards_activation_failure_admin_notice() {

		// to hide Plugin activated notice.
		unset( $_GET['activate'] );
		if ( !is_plugin_active( 'woocommerce/woocommerce.php' )) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'WooCommerce is not activated, Please activate WooCommerce first to activate Ultimate WooCommerce Points and Rewards.','ultimate-woocommerce-points-and-rewards'); ?></p>
			</div>

			<?php
		}elseif (!is_plugin_active( 'rewardeem-woocommerce-points-rewards/rewardeem-woocommerce-points-rewards.php')) {
			?>

			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'Ultimate WooCommerce Points and Rewards Lite is not activated, Please activate Ultimate WooCommerce Points and Rewards Lite first to activate Ultimate WooCommerce Points and Rewards.','ultimate-woocommerce-points-and-rewards'); ?></p>
			</div>

			<?php
		} 
	    
	}

}





