<?php
/**
 * Activation Wizard Backend Handler
 *
 * Handles the backend logic for the activation wizard, including:
 * - Enqueuing scripts and styles
 * - Processing wizard form submissions
 * - Saving wizard settings to database
 * - Redirecting users to wizard on first activation
 *
 * @package    Points_And_Rewards_For_WooCommerce
 * @subpackage Points_And_Rewards_For_WooCommerce/admin
 * @since      1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Activation Wizard Handler Class
 */
class MWB_WPR_Activation_Wizard {

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
		add_action( 'wp_ajax_mwb_wpr_save_wizard_settings', array( $this, 'save_wizard_settings' ) );

		// Redirect to wizard on first activation (if not already completed).
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_wizard' ) );

		// Handle skip wizard action.
		add_action( 'admin_init', array( $this, 'handle_skip_wizard' ) );
	}

	/**
	 * Register the activation wizard in the admin menu.
	 * Hidden from menu but accessible via direct URL.
	 */
	public function register_wizard_menu() {
		add_submenu_page(
			null, // No parent menu (hidden).
			__( 'Setup Wizard', 'points-and-rewards-for-woocommerce' ),
			__( 'Setup Wizard', 'points-and-rewards-for-woocommerce' ),
			'manage_options',
			'mwb-wpr-activation-wizard',
			array( $this, 'render_wizard_page' )
		);
	}

	/**
	 * Render the activation wizard page.
	 */
	public function render_wizard_page() {
		include plugin_dir_path( __FILE__ ) . 'partials/mwb-wpr-activation-wizard.php';
	}

	/**
	 * Enqueue wizard scripts and styles.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_wizard_assets( $hook ) {
		// Only load on the wizard page.
		if ( 'admin_page_mwb-wpr-activation-wizard' !== $hook ) {
			return;
		}

		// Enqueue Select2 for multi-select dropdowns.
		wp_enqueue_style(
			'select2',
			'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
			array(),
			'4.1.0'
		);

		wp_enqueue_script(
			'select2',
			'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
			array( 'jquery' ),
			'4.1.0',
			true
		);

		// Enqueue wizard CSS.
		wp_enqueue_style(
			$this->plugin_name . '-wizard',
			plugin_dir_url( __FILE__ ) . 'css/mwb-wpr-activation-wizard.css',
			array(),
			$this->version,
			'all'
		);

		// Enqueue wizard JavaScript.
		wp_enqueue_script(
			$this->plugin_name . '-wizard',
			plugin_dir_url( __FILE__ ) . 'js/mwb-wpr-activation-wizard.js',
			array( 'jquery', 'select2' ),
			$this->version,
			true
		);

		// Localize script with AJAX URL and nonce.
		wp_localize_script(
			$this->plugin_name . '-wizard',
			'mwbWprWizardData',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'mwb_wpr_wizard_save' ),
				'settingsUrl' => admin_url( 'admin.php?page=points_and_rewards_for_woocommerce_menu' ),
			)
		);
	}

	/**
	 * Handle AJAX request to save wizard settings.
	 */
	public function save_wizard_settings() {
		// Verify nonce.
		check_ajax_referer( 'mwb_wpr_wizard_save', 'nonce' );

		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'points-and-rewards-for-woocommerce' ),
				)
			);
		}

		// Get wizard data from request.
		$wizard_data = isset( $_POST['wizard_data'] ) ? $_POST['wizard_data'] : array();

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
			update_option( 'mwb_wpr_wizard_completed', true );

			wp_send_json_success(
				array(
					'message' => __( 'Settings saved successfully!', 'points-and-rewards-for-woocommerce' ),
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
		$general_settings = get_option( 'mwb_wpr_settings_gallery', array() );
		$other_settings   = get_option( 'mwb_wpr_other_settings', array() );

		// Step 1: Basic Setup.
		if ( isset( $wizard_data['step1'] ) ) {
			$step1 = $wizard_data['step1'];

			if ( ! empty( $step1['mwb_wpr_points_name'] ) ) {
				$general_settings['mwb_wpr_points_name'] = sanitize_text_field( $step1['mwb_wpr_points_name'] );
			}

			if ( ! empty( $step1['mwb_wpr_points_display_position'] ) ) {
				$general_settings['mwb_wpr_points_display_position'] = array_map( 'sanitize_text_field', (array) $step1['mwb_wpr_points_display_position'] );
			}
		}

		// Step 2: Earning Rules.
		if ( isset( $wizard_data['step2'] ) ) {
			$step2 = $wizard_data['step2'];

			// Enable earning.
			$general_settings['mwb_wpr_general_points_enable'] = isset( $step2['mwb_wpr_earning_enable'] ) && $step2['mwb_wpr_earning_enable'] ? 'yes' : 'no';

			// Earning rate.
			if ( isset( $step2['mwb_wpr_earning_rate'] ) ) {
				$general_settings['mwb_wpr_general_points'] = floatval( $step2['mwb_wpr_earning_rate'] );
			}

			if ( isset( $step2['mwb_wpr_earning_currency'] ) ) {
				$general_settings['mwb_wpr_general_price'] = floatval( $step2['mwb_wpr_earning_currency'] );
			}

			// Signup points.
			if ( isset( $step2['mwb_wpr_signup_points_enable'] ) && $step2['mwb_wpr_signup_points_enable'] ) {
				$general_settings['mwb_wpr_general_signup_value'] = isset( $step2['mwb_wpr_signup_points_value'] ) ? absint( $step2['mwb_wpr_signup_points_value'] ) : 100;
				$general_settings['mwb_wpr_general_signup'] = 'yes';
			} else {
				$general_settings['mwb_wpr_general_signup'] = 'no';
			}
		}

		// Step 3: Redemption.
		if ( isset( $wizard_data['step3'] ) ) {
			$step3 = $wizard_data['step3'];

			// Enable redemption.
			$general_settings['mwb_wpr_general_redeem_enable'] = isset( $step3['mwb_wpr_redemption_enable'] ) && $step3['mwb_wpr_redemption_enable'] ? 'yes' : 'no';

			// Redemption rate.
			if ( isset( $step3['mwb_wpr_redemption_rate'] ) ) {
				$general_settings['mwb_wpr_general_redeem_points'] = absint( $step3['mwb_wpr_redemption_rate'] );
			}

			if ( isset( $step3['mwb_wpr_redemption_value'] ) ) {
				$general_settings['mwb_wpr_general_redeem_price'] = floatval( $step3['mwb_wpr_redemption_value'] );
			}

			// Minimum redemption.
			if ( isset( $step3['mwb_wpr_min_redeem_points'] ) ) {
				$general_settings['mwb_wpr_general_minimum_value'] = absint( $step3['mwb_wpr_min_redeem_points'] );
			}

			// Redemption location.
			if ( isset( $step3['mwb_wpr_redeem_location'] ) ) {
				$redeem_location = sanitize_text_field( $step3['mwb_wpr_redeem_location'] );

				if ( 'cart' === $redeem_location ) {
					$other_settings['mwb_wpr_apply_points_checkout'] = 'no';
					$other_settings['mwb_wpr_apply_points_cart'] = 'yes';
				} elseif ( 'checkout' === $redeem_location ) {
					$other_settings['mwb_wpr_apply_points_checkout'] = 'yes';
					$other_settings['mwb_wpr_apply_points_cart'] = 'no';
				} else {
					$other_settings['mwb_wpr_apply_points_checkout'] = 'yes';
					$other_settings['mwb_wpr_apply_points_cart'] = 'yes';
				}
			}
		}

		// Step 4: Notifications.
		if ( isset( $wizard_data['step4'] ) ) {
			$step4 = $wizard_data['step4'];

			// On-site notifications.
			$other_settings['mwb_wpr_show_points_my_account'] = isset( $step4['mwb_wpr_show_points_my_account'] ) && $step4['mwb_wpr_show_points_my_account'] ? 'yes' : 'no';
			$other_settings['mwb_wpr_show_points_product_pages'] = isset( $step4['mwb_wpr_show_points_product_pages'] ) && $step4['mwb_wpr_show_points_product_pages'] ? 'yes' : 'no';
		}

		// Save all settings to database.
		$general_saved = update_option( 'mwb_wpr_settings_gallery', $general_settings );
		$other_saved = update_option( 'mwb_wpr_other_settings', $other_settings );

		// Store template information for reference.
		if ( isset( $wizard_data['selectedTemplate'] ) ) {
			update_option( 'mwb_wpr_wizard_template', sanitize_text_field( $wizard_data['selectedTemplate'] ) );
		}

		return $general_saved || $other_saved;
	}

	/**
	 * Redirect to wizard on first activation if not completed.
	 */
	public function maybe_redirect_to_wizard() {
		// Check if this is the activation redirect flag.
		if ( ! get_transient( 'mwb_wpr_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( 'mwb_wpr_activation_redirect' );

		// Don't redirect if wizard already completed.
		if ( get_option( 'mwb_wpr_wizard_completed' ) ) {
			return;
		}

		// Don't redirect if doing AJAX or on network admin.
		if ( wp_doing_ajax() || is_network_admin() ) {
			return;
		}

		// Don't redirect if user can't manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Redirect to wizard.
		wp_safe_redirect( admin_url( 'admin.php?page=mwb-wpr-activation-wizard' ) );
		exit;
	}

	/**
	 * Handle skip wizard action.
	 */
	public function handle_skip_wizard() {
		if ( ! isset( $_GET['mwb_wpr_skip_wizard'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Mark wizard as completed (skipped).
		update_option( 'mwb_wpr_wizard_completed', true );
		update_option( 'mwb_wpr_wizard_skipped', true );

		// Redirect to main settings page.
		$redirect_url = isset( $_GET['page'] ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : admin_url();
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Reset wizard completion status (for testing or re-running).
	 */
	public static function reset_wizard() {
		delete_option( 'mwb_wpr_wizard_completed' );
		delete_option( 'mwb_wpr_wizard_skipped' );
		delete_option( 'mwb_wpr_wizard_template' );
	}
}
