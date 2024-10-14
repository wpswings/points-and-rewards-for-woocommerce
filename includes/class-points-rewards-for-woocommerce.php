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
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes
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
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes
 * @author     makewebbetter <ticket@makewebbetter.com>
 */
class Points_Rewards_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Points_Rewards_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Responsible for maintaining all action that run on onboarding form.
	 *
	 * @var string
	 */
	protected $onboard;

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

		if ( defined( 'REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION' ) ) {

			$this->version = REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION;
		} else {

			$this->version = '2.5.1';
		}

		$this->plugin_name = 'points-and-rewards-for-woocommerce';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->init();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Points_Rewards_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Rewardeem_woocommerce_Points_Rewards_i18n. Defines internationalization functionality.
	 * - Rewardeem_woocommerce_Points_Rewards_Admin. Defines all hooks for the admin area.
	 * - Points_Rewards_For_WooCommerce_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-points-rewards-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-points-rewards-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-points-rewards-for-woocommerce-admin.php';

		// when pro plugin is not active than show dummy html.
		$wps_active_plugin = get_plugins();
		$wps_active_plugin = ! empty( $wps_active_plugin ) && is_array( $wps_active_plugin ) ? $wps_active_plugin : array();
		if ( ! array_key_exists( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php', $wps_active_plugin ) ) {

			/**
			 * The class responsible for defining all actions that occur in the admin area for dummy html.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-points-rewards-for-woocommerce-dummy-settings.php';
			new Points_Rewards_For_WooCommerce_Dummy_Settings( '', '' );
		}

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-points-rewards-for-woocommerce-public.php';

		$this->loader = new Points_Rewards_For_Woocommerce_Loader();

		/**
		 * The class responsible for defining all actions that occur in the onboarding the site data
		 * in the admin side of the site.
		 */
		! class_exists( 'WPSwings_Onboarding_Helper' ) && require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpswings-onboarding-helper.php';
		$this->onboard = new WPSwings_Onboarding_Helper();

	}

	/**
	 * Initialiation function to include mail teplate.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		add_filter( 'woocommerce_email_classes', array( $this, 'wps_wpr_woocommerce_email_classes' ) );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Points_Rewards_For_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Points_Rewards_For_Woocommerce_I18n();
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
		$plugin_admin = new Points_Rewards_For_WooCommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wps_rwpr_admin_menu', 10, 2 );
		$this->loader->add_action( 'wp_ajax_wps_wpr_points_update', $plugin_admin, 'wps_wpr_points_update' );
		$this->loader->add_action( 'wp_ajax_nopriv_wps_wpr_points_update', $plugin_admin, 'wps_wpr_points_update' );
		$this->loader->add_action( 'wp_ajax_wps_wpr_select_category', $plugin_admin, 'wps_wpr_select_category' );
		$this->loader->add_action( 'wp_ajax_nopriv_wps_wpr_select_category', $plugin_admin, 'wps_wpr_select_category' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'wps_wpr_add_membership_rule' );

		/*cron for notification*/
		$this->loader->add_action( 'admin_init', $plugin_admin, 'wps_wpr_check_for_notification_daily' );
		$this->loader->add_action( 'wps_wpr_check_for_notification_update', $plugin_admin, 'wps_wpr_save_notice_message' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'wps_wpr_display_notification_bar' );
		$this->loader->add_action( 'wp_ajax_wps_wpr_dismiss_notice', $plugin_admin, 'wps_wpr_dismiss_notice' );

		// Add your screen.
		$this->loader->add_filter( 'wps_helper_valid_frontend_screens', $plugin_admin, 'add_wps_frontend_screens' );
		// Add Deactivation screen.
		$this->loader->add_filter( 'wps_deactivation_supported_slug', $plugin_admin, 'add_wps_deactivation_screens' );
		// wallet compatibility.
		$this->loader->add_filter( 'wps_wpr_general_settings', $plugin_admin, 'wps_wpr_wallet_order_point' );
		// subscription compatibility.
		if ( is_plugin_active( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php' ) ) {
			$this->loader->add_filter( 'wps_wpr_general_settings', $plugin_admin, 'wps_wpr_subscription_settings' );
			$this->loader->add_action( 'wps_sfw_compatible_points_and_rewards', $plugin_admin, 'wps_wpr_subscription_renewal_point', 10, 1 );
		}
		// assign points on previous order.
		$this->loader->add_action( 'wp_ajax_assign_points_on_previous_order', $plugin_admin, 'wps_wpr_assign_points_on_previous_order_call' );
		// plugin banner notification.
		$this->loader->add_action( 'wps_wgm_check_for_notification_update', $plugin_admin, 'wps_wpr_save_banner_notice_message' );
		$this->loader->add_action( 'wp_ajax_wps_wpr_ajax_banner_action', $plugin_admin, 'wps_wpr_dismiss_notice__banner_callback' );
		// membership plugin compatible.
		if ( function_exists( 'wps_membership_check_plugin_enable' ) && wps_membership_check_plugin_enable() ) {
			$this->loader->add_action( 'wps_wpr_extend_membership_metabox_field', $plugin_admin, 'wps_wpr_membership_meta_fields', 10, 3 );
			$this->loader->add_action( 'save_post_wps_cpt_membership', $plugin_admin, 'wps_wpr_save_membership_fields' );
			$this->loader->add_action( 'wps_wpr_assign_points_to_user', $plugin_admin, 'wps_wpr_assign_points_to_user_call', 10, 1 );
			$this->loader->add_action( 'edit_post_wps_cpt_members', $plugin_admin, 'wps_wps_assign_points_member_edit_page', 10, 1 );
		}
		// Multivendor X compatibility.
		$this->loader->add_filter( 'mvx_vendor_payment_mode', $plugin_admin, 'wsfw_admin_mvx_list_mxfdxfodules' );
		$this->loader->add_filter( 'mvx_parent_order_to_vendor_order_statuses_to_sync', $plugin_admin, 'wsfw_mvx_parent_order_to_vendor_order_statuses_to_sync', 10, 1 );
		$this->loader->add_filter( 'woocommerce_order_status_changed', $plugin_admin, 'wps_wpr_assign_vendor_commission_points', 10, 3 );

		// restrict user from points table.
		$this->loader->add_action( 'wp_ajax_restrict_user_from_points_table', $plugin_admin, 'wps_wpr_restrict_user_from_points_table' );
		// import functionality from org.
		if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {

			$this->loader->add_action( 'wps_wpr_add_additional_import_points', $plugin_admin, 'wps_wpr_add_additional_import_points', 10 );
		}
		$this->loader->add_action( 'wp_ajax_wps_large_scv_import', $plugin_admin, 'wps_large_scv_import' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Points_Rewards_For_WooCommerce_Public( $this->get_plugin_name(), $this->get_version() );
		if ( $this->wps_rwpr_is_plugin_enable() ) {
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
			/* Include the points tab woocommrerce dashboard and template file*/
			$this->loader->add_action( 'init', $plugin_public, 'wps_wpr_add_my_account_endpoint' );
			$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public, 'wps_wpr_points_dashboard' );
			/*Add the points tabs page in woocommerce*/
			$this->loader->add_action( 'woocommerce_account_points_endpoint', $plugin_public, 'wps_wpr_account_points' );
			/*Add the view logs poitns page in woocommerce*/
			$this->loader->add_action( 'woocommerce_account_view-log_endpoint', $plugin_public, 'wps_wpr_account_viewlog' );
			/*Set the referral key in the woocommerce*/
			$this->loader->add_action( 'wp_loaded', $plugin_public, 'wps_wpr_referral_link_using_cookie' );
			/*Assign signup points and referral points in woocommerce*/
			$this->loader->add_action( 'user_register', $plugin_public, 'wps_wpr_new_customer_registerd', 10, 1 );
			$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_public, 'wps_wpr_woocommerce_order_status_changed', 10, 3 );

			$this->loader->add_action( 'woocommerce_before_customer_login_form', $plugin_public, 'wps_wpr_woocommerce_signup_point' );
			/*Add html in the cart for apply points*/
			$this->loader->add_action( 'woocommerce_cart_actions', $plugin_public, 'wps_wpr_woocommerce_cart_coupon' );
			$this->loader->add_action( 'wp_ajax_wps_wpr_apply_fee_on_cart_subtotal', $plugin_public, 'wps_wpr_apply_fee_on_cart_subtotal' );
			$this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'wps_wpr_woocommerce_cart_custom_points' );
			$this->loader->add_action( 'woocommerce_before_cart_contents', $plugin_public, 'wps_wpr_woocommerce_before_cart_contents' );
			$this->loader->add_action( 'woocommerce_blocks_enqueue_cart_block_scripts_after', $plugin_public, 'wps_wpr_woocommerce_before_cart_contents' );
			$this->loader->add_filter( 'woocommerce_cart_totals_fee_html', $plugin_public, 'wps_wpr_woocommerce_cart_totals_fee_html', 10, 2 );
			$this->loader->add_action( 'wp_ajax_wps_wpr_remove_cart_point', $plugin_public, 'wps_wpr_remove_cart_point' );
			/*Apply points on the cart sub total*/
			$this->loader->add_filter( 'wc_get_template', $plugin_public, 'wps_overwrite_form_temp', 10, 2 );
			// cart block change.
			$this->loader->add_action( 'woocommerce_store_api_checkout_order_processed', $plugin_public, 'wps_wpr_woocommerce_checkout_update_order_meta' );
			$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'wps_wpr_woocommerce_checkout_update_order_meta' );
			$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'wps_wpr_woocommerce_add_cart_item_data', 10, 4 );
			$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_public, 'wps_wpr_woocommerce_get_item_data', 10, 2 );
			$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'wps_display_product_points', 7 );
			/*Display the meta key*/
			$this->loader->add_filter( 'woocommerce_order_item_display_meta_key', $plugin_public, 'wps_wpr_woocommerce_order_item_display_meta_key', 10, 1 );
			$this->loader->add_action( 'woocommerce_checkout_create_order_line_item', $plugin_public, 'wps_wpr_woocommerce_add_order_item_meta_version_3', 10, 4 );
			$this->loader->add_filter( 'woocommerce_get_price_html', $plugin_public, 'wps_wpr_user_level_discount_on_price', 10, 2 );
			$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'wps_wpr_woocommerce_before_calculate_totals', 10, 1 );
			$this->loader->add_filter( 'woocommerce_update_cart_action_cart_updated', $plugin_public, 'wps_update_cart_points' );

			// Paypal Issue Change start.
			$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'wps_wpr_woocommerce_cart_custom_points' );
			// Paypal Issue Change End.

			/*Make Tax calculation 0 on the fees applied on the points*/
			$this->loader->add_filter( 'woocommerce_cart_totals_get_fees_from_cart_taxes', $plugin_public, 'wps_wpr_fee_tax_calculation', 10, 3 );
			$this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public, 'wps_wpr_add_coupon_form', 10, 1 );

			$this->loader->add_filter( 'query_vars', $plugin_public, 'wps_wpr_custom_endpoint_query_vars' );
			// wmpl.
			$this->loader->add_filter( 'wcml_register_endpoints_query_vars', $plugin_public, 'wps_wpr_wpml_register_endpoint', 10, 3 );
			$this->loader->add_filter( 'wcml_endpoint_permalink_filter', $plugin_public, 'wps_wpr_endpoint_permalink_filter', 10, 2 );
			$this->loader->add_filter( 'woocommerce_cart_contents_changed', $plugin_public, 'wps_wpr_woocommerce_content_change' );
			// custom_code.

			$this->loader->add_action( 'wps_wpr_add_coupon_generation', $plugin_public, 'wps_wpr_add_wallet_generation', 10, 1 );
			$this->loader->add_action( 'wp_ajax_wps_wpr_generate_custom_wallet', $plugin_public, 'wps_wpr_generate_custom_wallet' );
			$this->loader->add_action( 'wp_ajax_nopriv_wps_wpr_generate_custom_wallet', $plugin_public, 'wps_wpr_generate_custom_wallet' );

			// Paypal Issue Change start.
			$this->loader->add_filter( 'woocommerce_get_shop_coupon_data', $plugin_public, 'wps_wpr_validate_virtual_coupon_for_points', 10, 2 );
			$this->loader->add_filter( 'woocommerce_cart_totals_coupon_label', $plugin_public, 'wps_wpr_filter_woocommerce_coupon_label', 10, 2 );
			$this->loader->add_filter( 'woocommerce_cart_totals_coupon_html', $plugin_public, 'wps_wpr_par_virtual_coupon_remove', 30, 3 );
			// Paypal Issue Change End.

			// Shortcode to show apply points section.
			$this->loader->add_action( 'plugins_loaded', $plugin_public, 'wps_wpr_shortocde_to_show_apply_points_section' );
			// WOOCS - WooCommerce Currency Switcher Compatibility.
			if ( is_plugin_active( 'woocommerce-currency-switcher/index.php' ) ) {
				$this->loader->add_filter( 'wps_wpr_show_conversion_price', $plugin_public, 'wps_wpr_conversion_price_callback', 10, 1 );
				$this->loader->add_filter( 'wps_wpr_convert_base_price_diffrent_currency', $plugin_public, 'wps_wpr_convert_diffrent_currency_base_price_callback', 10, 1 );
				$this->loader->add_filter( 'wps_wpr_convert_same_currency_base_price', $plugin_public, 'wps_wpr_convert_same_currency_base_price_callback', 10, 2 );
			}
			// subscription compatibility ( show message on account page ).
			$this->loader->add_action( 'wps_extend_point_tab_section', $plugin_public, 'wps_wpr_show_subscription_message', 10, 1 );
			// order rewards points functionality.
			$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_public, 'wps_wpr_order_rewards_points_callback', 20, 2 );
			// Gamification features.
			$this->loader->add_action( 'wp_footer', $plugin_public, 'wps_wpr_show_canvas_icons' );
			// assign claim points.
			$this->loader->add_action( 'wp_ajax_assign_claim_points', $plugin_public, 'wps_wpr_assign_claim_points' );
			$this->loader->add_action( 'wps_wpr_top_account_page_section_hook', $plugin_public, 'wps_wpr_display_earn_user_badges', 10, 1 );
			// cart/checkout block js.
			$this->loader->add_action( 'woocommerce_blocks_enqueue_cart_block_scripts_after', $plugin_public, 'wps_wpr_enqueue_cart_block_file' );
			$this->loader->add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_before', $plugin_public, 'wps_wpr_enqueue_cart_block_file' );
			// Multivendor X compatibility.
			$this->loader->add_filter( 'mvx_available_payment_gateways', $plugin_public, 'wps_wpr_admin_mvx_list_modules', 10 );
			// verify cart page nonce.
			$this->loader->add_action( 'woocommerce_before_add_to_cart_button', $plugin_public, 'wps_wpr_verify_cart_page_nonce' );
			// Dokan Plugin Compatibility.
			if ( is_plugin_active( 'dokan-pro/dokan-pro.php' ) && function_exists( 'dokan_pro' ) ) {

				$this->loader->add_filter( 'dokan_ensure_admin_have_create_coupon', $plugin_public, 'wps_wpr_dokan_plugin_compatibility', PHP_INT_MAX, 4 );
			}
		}
	}

	/**
	 * Check is plugin is enable.
	 *
	 * @return true/false
	 * @since    1.0.0
	 */
	public function wps_rwpr_is_plugin_enable() {

		$is_enable        = false;
		$wps_wpr_enable   = '';
		$general_settings = get_option( 'wps_wpr_settings_gallery', true );
		if ( isset( $general_settings['wps_wpr_general_setting_enable'] ) ) {
			$wps_wpr_enable = $general_settings['wps_wpr_general_setting_enable'];
		}
		if ( ! empty( $wps_wpr_enable ) && 1 == $wps_wpr_enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}

	/**
	 * Initialization function to include mail template.
	 *
	 * @param array $emails email templates.
	 * @since    1.0.8
	 */
	public function wps_wpr_woocommerce_email_classes( $emails ) {
		$emails['wps_wpr_email_notification'] = include WPS_RWPR_DIR_PATH . 'emails/class-wps-wpr-emails-notification.php';
		return $emails;
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
	 * @return    Rewardeem_woocommerce_Points_Rewards_Loader    Orchestrates the hooks of the plugin.
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
