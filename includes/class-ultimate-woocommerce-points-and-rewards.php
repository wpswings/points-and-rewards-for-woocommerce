<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ultimate_Woocommerce_Points_And_Rewards
 * @subpackage Ultimate_Woocommerce_Points_And_Rewards/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Ultimate_Woocommerce_Points_And_Rewards {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ultimate_Woocommerce_Points_And_Rewards_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		if ( defined( 'ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_VERSION' ) ) {

			$this->version = ULTIMATE_WOOCOMMERCE_POINTS_AND_REWARDS_VERSION;
		} 
		
		else {

			$this->version = '1.0.0';
		}

		$this->plugin_name = 'ultimate-woocommerce-points-and-rewards';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ultimate_Woocommerce_Points_And_Rewards_Loader. Orchestrates the hooks of the plugin.
	 * - Ultimate_Woocommerce_Points_And_Rewards_i18n. Defines internationalization functionality.
	 * - Ultimate_Woocommerce_Points_And_Rewards_Admin. Defines all hooks for the admin area.
	 * - Ultimate_Woocommerce_Points_And_Rewards_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ultimate-woocommerce-points-and-rewards-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ultimate-woocommerce-points-and-rewards-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ultimate-woocommerce-points-and-rewards-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ultimate-woocommerce-points-and-rewards-public.php';

		$this->loader = new Ultimate_Woocommerce_Points_And_Rewards_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ultimate_Woocommerce_Points_And_Rewards_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ultimate_Woocommerce_Points_And_Rewards_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ultimate_Woocommerce_Points_And_Rewards_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_filter('mwb_rwpr_add_setting_tab',$plugin_admin,'mwb_add_license_panel');

		/*Running action for ajax license.*/ 
		$this->loader->add_action( 'wp_ajax_ultimate_woocommerce_points_and_rewards_license', $plugin_admin, 'validate_license_handle' );
		
		$this->loader->add_action('mwb_wpr_add_notice',$plugin_admin,'mwb_wpr_add_notice');
		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();

		/*Condition for validating.*/ 
		if( Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() || 0 <= $day_count ) {

			/*All admin actions and filters after License Validation goes here.*/ 
			/*Using Settings API for settings menu.*/ 
			$this->loader->add_filter('mwb_wpr_general_settings',$plugin_admin,'add_mwb_settings',10,1);
			/*Daily ajax license action.*/ 
			$this->loader->add_action( 'ultimate_woocommerce_points_and_rewards_license_daily', $plugin_admin, 'validate_license_daily' );
			$this->loader->add_action('transition_comment_status', $plugin_admin,'mwb_wpr_give_points_on_comment',10,3);
			$this->loader->add_filter('mwb_wpr_email_notification_settings',$plugin_admin,'mwb_wpr_add_email_notification_settings');
			$this->loader->add_filter('mwb_wpr_coupon_settings',$plugin_admin,'mwb_wpr_add_coupon_settings');
			$this->loader->add_action('mwb_wpr_product_assign_points',$plugin_admin,'mwb_wpr_add_new_catories_wise_settings');
			$this->loader->add_action( 'wp_ajax_mwb_wpr_per_pro_category',$plugin_admin,'mwb_wpr_per_pro_category');
			$this->loader->add_action( 'wp_ajax_nopriv_mwb_wpr_per_pro_category',$plugin_admin,'mwb_wpr_per_pro_category');
			$this->loader->add_action('mwb_wpr_others_settings',$plugin_admin,'mwb_wpr_other_settings');
			$this->loader->add_filter('mwb_rwpr_add_setting_tab',$plugin_admin,'mwb_add_purchase_through_points_settings_tab',20,1);
			$this->loader->add_action( 'wp_ajax_mwb_wpr_per_pro_pnt_category',$plugin_admin, 'mwb_wpr_per_pro_pnt_category');
			$this->loader->add_action( 'wp_ajax_nopriv_mwb_wpr_per_pro_pnt_category',$plugin_admin, 'mwb_wpr_per_pro_pnt_category');
			$this->loader->add_filter('woocommerce_product_data_tabs',$plugin_admin,'mwb_wpr_add_points_tab',15,1);
			$this->loader->add_action('woocommerce_product_data_panels', $plugin_admin, 'mwb_wpr_points_input');
			$this->loader->add_action( 'woocommerce_variation_options',$plugin_admin, 'mwb_wpr_woocommerce_variation_options_pricing',10,3 );
			$this->loader->add_action('woocommerce_save_product_variation',$plugin_admin,'mwb_wpr_woocommerce_save_product_variation',10,2);
			$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'woo_add_custom_points_fields_save');
			$this->loader->add_action( 'mwb_wpr_points_expiration_cron_schedule',$plugin_admin,'mwb_wpr_check_daily_about_points_expiration');
			// $this->loader->add_action( 'init',$plugin_admin,'mwb_wpr_check_daily_about_points_expiration');
			$this->loader->add_action( 'widgets_init',$plugin_admin, 'mwb_wpr_custom_widgets');
			$this->loader->add_action( 'woocommerce_admin_order_item_headers',$plugin_admin,'mwb_wpr_woocommerce_admin_order_item_headers');
			$this->loader->add_action( 'woocommerce_admin_order_item_values',$plugin_admin,'mwb_wpr_woocommerce_admin_order_item_values',10,3 );
			$this->loader->add_action('admin_head',$plugin_admin,'mwb_wpr_remove_action');
			$this->loader->add_action('mwb_wpr_save_membership_settings',$plugin_admin,'mwb_wpr_save_membership_settings_pro',10,1);
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ultimate_Woocommerce_Points_And_Rewards_Public( $this->get_plugin_name(), $this->get_version() );

		$callname_lic = Ultimate_Woocommerce_Points_And_Rewards::$lic_callback_function;
		$callname_lic_initial = Ultimate_Woocommerce_Points_And_Rewards::$lic_ini_callback_function;
		$day_count = Ultimate_Woocommerce_Points_And_Rewards::$callname_lic_initial();

		/*Condition for validating.*/ 
		if( Ultimate_Woocommerce_Points_And_Rewards::$callname_lic() || 0 <= $day_count ) {

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
			$this->loader->add_action('mwb_wpr_before_add_referral_section',$plugin_public,'mwb_wpr_add_referral_section');
			$this->loader->add_action('mwb_after_referral_link',$plugin_public,'mwb_wpr_add_invite_text');
			$this->loader->add_filter('mwb_wpr_referral_points',$plugin_public,'mwb_wpr_add_referral_resctrictions',10,3);
			$this->loader->add_filter('woocommerce_product_review_comment_form_args',$plugin_public,'mwb_wpr_woocommerce_comment_point',10,1);
			/*This action is used for assigning the product referral purchase points*/
			$this->loader->add_action( 'woocommerce_order_status_changed',$plugin_public, 'mwb_wpr_pro_woocommerce_order_status_changed',11, 3 );
			/*This action is used for generation of the user coupon*/
			$this->loader->add_action('mwb_wpr_add_coupon_generation',$plugin_public,'mwb_wpr_add_coupon_conversion_settings',10,1);

			$this->loader->add_action('mwb_wpr_list_coupons_generation',$plugin_public,'mwb_wpr_list_coupons_generation',10,1);
			$this->loader->add_action( 'wp_ajax_mwb_wpr_generate_original_coupon', $plugin_public, 'mwb_wpr_generate_original_coupon');
			$this->loader->add_action( 'wp_ajax_nopriv_mwb_wpr_generate_original_coupon',$plugin_public, 'mwb_wpr_generate_original_coupon');
			$this->loader->add_action( 'wp_ajax_mwb_wpr_generate_custom_coupon',$plugin_public, 'mwb_wpr_generate_custom_coupon');
			$this->loader->add_action('wp_ajax_nopriv_mwb_wpr_generate_custom_coupon',$plugin_public,'mwb_wpr_generate_custom_coupon');
			$this->loader->add_action( 'woocommerce_new_order_item',$plugin_public, 'mwb_wpr_woocommerce_order_add_coupon_woo_latest_version',10,2);
			$this->loader->add_action('mwb_wpr_add_share_points',$plugin_public,'mwb_wpr_share_points_section');
			$this->loader->add_action( 'wp_ajax_mwb_wpr_sharing_point_to_other',$plugin_public, 'mwb_wpr_sharing_point_to_other');
			$this->loader->add_action( 'wp_ajax_nopriv_mwb_wpr_sharing_point_to_other',$plugin_public, 'mwb_wpr_sharing_point_to_other');
			$this->loader->add_action( 'woocommerce_before_add_to_cart_button', $plugin_public, "mwb_wpr_woocommerce_before_add_to_cart_button", 10, 1);
			$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'mwb_wpr_woocommerce_add_cart_item_data_pro', 10, 4);
			$this->loader->add_filter( 'woocommerce_get_item_data',$plugin_public, 'mwb_wpr_woocommerce_get_item_data_pro', 10, 2);
			$this->loader->add_action('woocommerce_cart_calculate_fees',$plugin_public,'mwb_wpr_woocommerce_cart_calculate_fees_pro');
			$this->loader->add_filter('woocommerce_update_cart_action_cart_updated',$plugin_public, 'mwb_update_cart_points_pro');
			$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public,'mwb_wpr_woocommerce_before_calculate_totals_pro',20, 1);
			$this->loader->add_action('woocommerce_checkout_create_order_line_item',$plugin_public,'mwb_wpr_woocommerce_add_order_item_meta_version_3',20,4);
			/*Display the meta key*/
			$this->loader->add_filter('woocommerce_order_item_display_meta_key',$plugin_public,'mwb_wpr_woocommerce_order_item_display_meta_key_pro',20,1 );
			/*Update order meta of the order*/
			$this->loader->add_action('woocommerce_checkout_update_order_meta',$plugin_public,'mwb_wpr_woocommerce_checkout_update_order_meta_pro',20,2);
			$this->loader->add_action('mwb_wpr_membership_cron_schedule',$plugin_public, 'mwb_wpr_do_this_hourly');
			$this->loader->add_filter( 'woocommerce_get_price_html',$plugin_public, 'mwb_wpr_user_level_discount_on_price_pro',20,2);
			$this->loader->add_action( 'wp_ajax_mwb_wpr_append_variable_point', $plugin_public, 'mwb_wpr_append_variable_point');
			$this->loader->add_action( 'wp_ajax_nopriv_mwb_wpr_append_variable_point',$plugin_public, 'mwb_wpr_append_variable_point');
			$this->loader->add_action( 'wp_ajax_mwb_pro_purchase_points_only',$plugin_public, 'mwb_pro_purchase_points_only');
			$this->loader->add_action( 'wp_ajax_nopriv_mwb_pro_purchase_points_only',$plugin_public,'mwb_pro_purchase_points_only');
			$this->loader->add_filter('woocommerce_thankyou_order_received_text',$plugin_public,'mwb_wpr_woocommerce_thankyou',10,2);
		}
	}

	/*public static variable to be accessed in this plugin.*/ 
	public static $lic_callback_function = 'check_lcns_validity';

	/*public static variable to be accessed in this plugin.*/ 
	public static $lic_ini_callback_function = 'check_lcns_initial_days';

	/**
	 * Validate the use of features of this plugin.
	 *
	 * @since    1.0.0
	 */
	public static function check_lcns_validity() {

		$ultimate_woocommerce_points_and_rewards_lcns_key = get_option( 'ultimate_woocommerce_points_and_rewards_lcns_key', '' );

		$ultimate_woocommerce_points_and_rewards_lcns_status = get_option( 'ultimate_woocommerce_points_and_rewards_lcns_status', '' );

		if( $ultimate_woocommerce_points_and_rewards_lcns_key && 'true' === $ultimate_woocommerce_points_and_rewards_lcns_status ) {
			
			return true;
		}

		else {

			return false;
		}
	}

	/**
	 * Validate the use of features of this plugin for initial days.
	 *
	 * @since    1.0.0
	 */
	public static function check_lcns_initial_days() {

		$thirty_days = get_option( 'ultimate_woocommerce_points_and_rewards_lcns_thirty_days', 0 );

		$current_time = current_time( 'timestamp' );

		$day_count = ( $thirty_days - $current_time ) / (24 * 60 * 60);

		return $day_count;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ultimate_Woocommerce_Points_And_Rewards_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
