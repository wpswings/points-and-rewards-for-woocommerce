<?php
/**
 * Setup Wizard Handler
 *
 * Handles the backend logic for the setup wizard, including:
 * - Enqueuing scripts and styles
 * - Processing wizard form submissions
 * - Saving wizard settings to database
 * - Redirecting users to wizard on first activation
 *
 * @package    Points_And_Rewards_For_WooCommerce
 * @subpackage Points_And_Rewards_For_WooCommerce/admin
 * @since      2.10.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup Wizard Handler Class
 */
class WPS_WPR_Setup_Wizard {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Register admin menu.
		add_action( 'admin_menu', array( $this, 'register_wizard_menu' ), 99 );

		// Enqueue scripts and styles on wizard page.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_wizard_assets' ) );

		// Handle AJAX request to save wizard settings.
		add_action( 'wp_ajax_wps_wpr_save_wizard_settings', array( $this, 'save_wizard_settings' ) );

		// Redirect to wizard on first activation (if not already completed).
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_wizard' ) );

		// Handle skip wizard action.
		add_action( 'admin_init', array( $this, 'handle_skip_wizard' ) );
	}

	/**
	 * Register the setup wizard in the admin menu.
	 * Hidden from menu but accessible via direct URL.
	 */
	public function register_wizard_menu() {
		add_submenu_page(
			null, // No parent menu (hidden).
			__( 'Setup Wizard', 'points-and-rewards-for-woocommerce' ),
			__( 'Setup Wizard', 'points-and-rewards-for-woocommerce' ),
			'manage_options',
			'wps-wpr-setup-wizard',
			array( $this, 'render_wizard_page' )
		);
	}

	/**
	 * Render the setup wizard page.
	 */
	public function render_wizard_page() {
		include plugin_dir_path( __FILE__ ) . 'partials/wps-wpr-setup-wizard.php';
	}

	/**
	 * Enqueue wizard scripts and styles.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_wizard_assets( $hook ) {
		// Only load on the wizard page.
		if ( 'admin_page_wps-wpr-setup-wizard' !== $hook ) {
			return;
		}

		// Enqueue wizard styles.
		wp_enqueue_style(
			'wps-wpr-wizard-style',
			WPS_RWPR_DIR_URL . 'admin/css/wps-wpr-setup-wizard.css',
			array(),
			$this->version,
			'all'
		);

		// Enqueue select2 for better dropdowns.
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2' );

		// Enqueue wizard script.
		wp_enqueue_script(
			'wps-wpr-wizard-script',
			WPS_RWPR_DIR_URL . 'admin/js/wps-wpr-setup-wizard.js',
			array( 'jquery', 'select2' ),
			$this->version,
			true
		);

		// Localize script with AJAX URL and nonce.
		wp_localize_script(
			'wps-wpr-wizard-script',
			'wps_wpr_wizard_obj',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'wps_wpr_wizard_save' ),
				'settings_url'     => admin_url( 'admin.php?page=wps-rwpr-setting' ),
				'currency_symbol'  => get_woocommerce_currency_symbol(),
				'i18n'             => array(
					'saving'           => __( 'Saving...', 'points-and-rewards-for-woocommerce' ),
					'success'          => __( 'Settings saved successfully!', 'points-and-rewards-for-woocommerce' ),
					'error'            => __( 'Failed to save settings. Please try again.', 'points-and-rewards-for-woocommerce' ),
					'validation_error' => __( 'Please fill all required fields.', 'points-and-rewards-for-woocommerce' ),
				),
			)
		);
	}

	/**
	 * Handle AJAX request to save wizard settings.
	 */
	public function save_wizard_settings() {
		// Verify nonce.
		check_ajax_referer( 'wps_wpr_wizard_save', 'nonce' );

		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'points-and-rewards-for-woocommerce' ),
				)
			);
		}

		// Get wizard data from request.
		$wizard_data = isset( $_POST['wizard_data'] ) ? map_deep( wp_unslash( $_POST['wizard_data'] ), 'sanitize_text_field' ) : array();

		if ( empty( $wizard_data ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No data received. Please try again.', 'points-and-rewards-for-woocommerce' ),
				)
			);
		}

		// Process and save wizard settings.
		$result = $this->process_wizard_data( $wizard_data );

		if ( $result ) {
			// Mark wizard as completed.
			update_option( 'wps_wpr_wizard_completed', true );

			wp_send_json_success(
				array(
					'message'      => __( 'Settings saved successfully!', 'points-and-rewards-for-woocommerce' ),
					'redirect_url' => admin_url( 'admin.php?page=wps-rwpr-setting' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to save settings. Please try again.', 'points-and-rewards-for-woocommerce' ),
				)
			);
		}
	}

	/**
	 * Process wizard data and save to database.
	 *
	 * @param array $wizard_data The wizard data from the form.
	 * @return bool True on success, false on failure.
	 */
	private function process_wizard_data( $wizard_data ) {
		// Get existing settings.
		$general_settings = get_option( 'wps_wpr_settings_gallery', array() );
		$other_settings   = get_option( 'wps_wpr_other_settings', array() );

		// Step 0: Master Enable Plugin.
		if ( isset( $wizard_data['step0'] ) ) {
			$step0 = $wizard_data['step0'];

			// Enable/disable plugin (save as integer 1 or empty string to match main settings).
			$general_settings['wps_wpr_general_setting_enable'] = isset( $step0['plugin_enable'] ) && '1' === $step0['plugin_enable'] ? 1 : '';
		}

		// Step 1: Redemption Settings.
		if ( isset( $wizard_data['step1'] ) ) {
			$step1 = $wizard_data['step1'];

			// Enable redemption on cart (save as integer 1 or empty string to match main settings).
			$general_settings['wps_wpr_custom_points_on_cart'] = isset( $step1['redemption_cart_enable'] ) && '1' === $step1['redemption_cart_enable'] ? 1 : '';

			// Enable redemption on checkout (save as integer 1 or empty string to match main settings).
			$general_settings['wps_wpr_apply_points_checkout'] = isset( $step1['redemption_checkout_enable'] ) && '1' === $step1['redemption_checkout_enable'] ? 1 : '';

			// Redemption rate (conversion rate).
			if ( isset( $step1['redemption_points'] ) ) {
				$general_settings['wps_wpr_cart_points_rate'] = absint( $step1['redemption_points'] );
			}

			if ( isset( $step1['redemption_value'] ) ) {
				$general_settings['wps_wpr_cart_price_rate'] = floatval( $step1['redemption_value'] );
			}
		}

		// Step 2: Referral Settings.
		if ( isset( $wizard_data['step2'] ) ) {
			$step2 = $wizard_data['step2'];

			// Enable referral program (save as integer 1 or empty string to match main settings).
			$general_settings['wps_wpr_general_refer_enable'] = isset( $step2['referral_enable'] ) && '1' === $step2['referral_enable'] ? 1 : '';

			// Referral points (free version only has one value).
			if ( isset( $step2['referee_points'] ) ) {
				$general_settings['wps_wpr_general_refer_value'] = absint( $step2['referee_points'] );
			}
		}

		// Step 3: Point Tab Layout.
		if ( isset( $wizard_data['step3'] ) ) {
			$step3 = $wizard_data['step3'];

			// Point tab template selection.
			if ( isset( $step3['point_tab_template'] ) ) {
				$allowed_templates = array( 'temp_one', 'temp_two', 'temp_three', 'temp_four' );
				if ( in_array( $step3['point_tab_template'], $allowed_templates, true ) ) {
					$other_settings['wps_wpr_choose_account_page_temp'] = sanitize_text_field( $step3['point_tab_template'] );
				}
			}

			// Show points on product pages.
			$other_settings['wps_wpr_show_points_on_product'] = isset( $step3['show_points_on_product'] ) && '1' === $step3['show_points_on_product'] ? 'yes' : 'no';
		}

		// Step 4: Signup Points.
		if ( isset( $wizard_data['step4'] ) ) {
			$step4 = $wizard_data['step4'];

			// Enable signup points (save as integer 1 or empty string to match main settings).
			$general_settings['wps_wpr_general_signup'] = isset( $step4['signup_enable'] ) && '1' === $step4['signup_enable'] ? 1 : '';

			// Signup points value.
			if ( isset( $step4['signup_points'] ) ) {
				$general_settings['wps_wpr_general_signup_value'] = absint( $step4['signup_points'] );
			}
		}

		// Step 5 is just review/complete - no settings to save.

		// Save all settings to database.
		$general_saved = update_option( 'wps_wpr_settings_gallery', $general_settings );
		$other_saved   = update_option( 'wps_wpr_other_settings', $other_settings );

		return $general_saved || $other_saved;
	}

	/**
	 * Redirect to wizard on first activation if not completed.
	 */
	public function maybe_redirect_to_wizard() {
		// Check if this is the activation redirect flag.
		if ( ! get_transient( 'wps_wpr_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( 'wps_wpr_activation_redirect' );

		// Don't redirect if wizard already completed.
		if ( get_option( 'wps_wpr_wizard_completed' ) ) {
			return;
		}

		// Don't redirect if doing AJAX or on network admin.
		if ( wp_doing_ajax() || is_network_admin() ) {
			return;
		}

		// Don't redirect if user cannot manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Perform the redirect.
		wp_safe_redirect( admin_url( 'admin.php?page=wps-wpr-setup-wizard' ) );
		exit;
	}

	/**
	 * Handle skip wizard action.
	 */
	public function handle_skip_wizard() {
		if ( ! isset( $_GET['wps_wpr_skip_wizard'] ) ) {
			return;
		}

		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Mark wizard as completed and skipped.
		update_option( 'wps_wpr_wizard_completed', true );
		update_option( 'wps_wpr_wizard_skipped', true );

		// Redirect to main settings page.
		wp_safe_redirect( admin_url( 'admin.php?page=wps-rwpr-setting' ) );
		exit;
	}
}
