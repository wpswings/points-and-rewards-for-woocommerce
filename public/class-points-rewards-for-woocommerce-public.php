<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/public
 * @author     makewebbetter <ticket@makewebbetter.com>
 */
class Points_Rewards_For_WooCommerce_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// enqueue css for points tab design.
		wp_enqueue_style( $this->plugin_name, WPS_RWPR_DIR_URL . 'public/css/points-rewards-for-woocommerce-public.min.css', array(), $this->version, 'all' );
		if ( $this->wps_wpr_check_new_template_active() ) {

			wp_enqueue_style( 'wps-account-page-design', WPS_RWPR_DIR_URL . 'public/css/points-and-rewards-for-woocommerce-account-page-design.css', array(), $this->version, 'all' );
		}

		// enqueue campaign css.
		if ( $this->wps_wpr_is_campaign_enable() && $this->wps_wpr_check_selected_page() ) {

			wp_enqueue_style( 'wps-campaign', WPS_RWPR_DIR_URL . 'public/css/points-and-rewards-campaign.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// get cart restriction message.
		$wps_wpr_other_settings           = get_option( 'wps_wpr_other_settings' );
		$wps_wpr_other_settings           = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
		$wps_wpr_restricted_cart_page_msg = ! empty( $wps_wpr_other_settings['wps_wpr_restricted_cart_page_msg'] ) ? $wps_wpr_other_settings['wps_wpr_restricted_cart_page_msg'] : esc_html__( 'You will not get any Reward Points', 'points-and-rewards-for-woocommerce' );

		$coupon_settings          = get_option( 'wps_wpr_coupons_gallery', array() );
		$wps_minimum_points_value = isset( $coupon_settings['wps_wpr_general_minimum_value'] ) ? $coupon_settings['wps_wpr_general_minimum_value'] : 50;

		// get user current points.
		$current_points = get_user_meta( get_current_user_id(), 'wps_wpr_points', true );
		$current_points = ! empty( $current_points ) && ! is_null( $current_points ) ? (int) $current_points : 0;

		// get gamification settings.
		$wps_wpr_save_gami_setting = get_option( 'wps_wpr_save_gami_setting', array() );
		$wps_wpr_game_color        = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_color'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_color'] : array();
		$wps_wpr_select_spin_stop  = ! empty( $wps_wpr_save_gami_setting['wps_wpr_select_spin_stop'] ) ? $wps_wpr_save_gami_setting['wps_wpr_select_spin_stop'] : array();

		// check game features is enable.enqueue gamification js.
		if ( $this->wps_wpr_check_gamification_is_enable() && $this->wps_wpr_win_wheel_page_display() && empty( get_user_meta( get_current_user_id(), 'wps_wpr_check_game_points_assign_timing', true ) ) ) {

			wp_enqueue_script( 'wps-wpr-tween-max', WPS_RWPR_DIR_URL . 'public/js/points-and-rewards-tween-max.js', array(), $this->version, true );
			wp_enqueue_script( 'wps-wpr-wheel-class', WPS_RWPR_DIR_URL . 'public/js/points-and-rewads-win-wheel.js', array(), $this->version, true );
			wp_enqueue_script( 'wps-wpr-game-public-js', WPS_RWPR_DIR_URL . 'public/js/points-and-rewards-gamification-public.js', array( 'jquery', 'wps-wpr-wheel-class', 'wps-wpr-tween-max' ), $this->version, true );
		}

		// verify nonce for restriction points earning.
		$wps_active_status = '';
		if ( wp_verify_nonce( ! empty( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '', 'wps-wpr-verify-nonce' ) ) {

			$wps_active_status = ! empty( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		}

		// main js file enqueue.
		wp_enqueue_script( $this->plugin_name, WPS_RWPR_DIR_URL . 'public/js/points-rewards-for-woocommerce-public.min.js', array( 'jquery', 'clipboard' ), $this->version, false );
		$wps_wpr = array(
			'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
			'message'                    => esc_html__( 'Please enter a valid points', 'points-and-rewards-for-woocommerce' ),
			'empty_notice'               => __( 'Please enter some points !!', 'points-and-rewards-for-woocommerce' ),
			'minimum_points'             => $wps_minimum_points_value,
			'confirmation_msg'           => __( 'Do you really want to upgrade your user level as this process will deduct the required points from your account?', 'points-and-rewards-for-woocommerce' ),
			'minimum_points_text'        => __( 'The minimum Points Required To Convert Points To Coupons is ', 'points-and-rewards-for-woocommerce' ) . $wps_minimum_points_value,
			'wps_wpr_custom_notice'      => __( 'The number of points you had entered will get deducted from your Account', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_nonce'              => wp_create_nonce( 'wps-wpr-verify-nonce' ),
			'not_allowed'                => __( 'Please enter some valid points!', 'points-and-rewards-for-woocommerce' ),
			'not_suffient'               => __( 'You do not have a sufficient amount of points', 'points-and-rewards-for-woocommerce' ),
			'above_order_limit'          => __( 'Entered points do not apply to this order.', 'points-and-rewards-for-woocommerce' ),
			'points_empty'               => __( 'Please enter points.', 'points-and-rewards-for-woocommerce' ),
			'checkout_page'              => is_checkout(),
			'wps_user_current_points'    => $current_points,
			'is_restrict_message_enable' => $this->wps_wpr_is_rewards_restrict_message_settings_enable(), // Restrict rewards points settings features.
			'is_restrict_status_set'     => $wps_active_status,
			'wps_restrict_rewards_msg'   => $wps_wpr_restricted_cart_page_msg,
			'wps_wpr_game_setting'       => $wps_wpr_game_color,
			'wps_wpr_select_spin_stop'   => $wps_wpr_select_spin_stop,
			'wps_is_user_login'          => is_user_logged_in(),
			'get_min_redeem_req'         => $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_value' ),
			'is_cart_redeem_sett_enable' => $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' ),
			'is_checkout_redeem_enable'  => $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' ),
			'points_coupon_name'         => esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' ),
			'wps_points_name'            => esc_html__( 'Points', 'points-and-rewards-for-woocommerce' ),
			'points_message_require'     => esc_html__( 'You require : ', 'points-and-rewards-for-woocommerce' ),
			'points_more_to_redeem'      => esc_html__( ' points more to get redeem', 'points-and-rewards-for-woocommerce' ),
			'wps_add_a_points'           => esc_html__( 'Add a points', 'points-and-rewards-for-woocommerce' ),
			'wps_apply_points'           => esc_html__( 'Apply Points', 'points-and-rewards-for-woocommerce' ),
		);
		wp_localize_script( $this->plugin_name, 'wps_wpr', $wps_wpr );

		// enqueue css for points tab design.
		if ( $this->wps_wpr_check_new_template_active() ) {

			wp_register_script( 'wp-wps-account-page-design', WPS_RWPR_DIR_URL . 'public/js/points-and-rewards-for-woocommerce-account-page-design.js', array(), $this->version, true );
			wp_enqueue_script( 'wp-wps-account-page-design' );
			wp_localize_script(
				$this->plugin_name,
				'points_tab_layout_obj',
				array(
					'points_tab_color' => ! empty( $wps_wpr_other_settings['wps_wpr_points_tab_layout_color'] ) ? $wps_wpr_other_settings['wps_wpr_points_tab_layout_color'] : '#0094ff',
					'design_temp_type' => ! empty( $wps_wpr_other_settings['wps_wpr_choose_account_page_temp'] ) ? $wps_wpr_other_settings['wps_wpr_choose_account_page_temp'] : '',
				)
			);
		}

		// enqueue campaign js.
		if ( $this->wps_wpr_is_campaign_enable() && $this->wps_wpr_check_selected_page() ) {

			$wps_wpr_campaign_settings  = get_option( 'wps_wpr_campaign_settings', array() );
			$wps_wpr_campaign_settings  = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
			$wps_wpr_campaign_color_one = ! empty( $wps_wpr_campaign_settings['wps_wpr_campaign_color_one'] ) ? $wps_wpr_campaign_settings['wps_wpr_campaign_color_one'] : '#a13a93';
			$wps_wpr_campaign_color_two = ! empty( $wps_wpr_campaign_settings['wps_wpr_campaign_color_two'] ) ? $wps_wpr_campaign_settings['wps_wpr_campaign_color_two'] : '#ffbb21';
			wp_enqueue_script( 'wps-campaign-js', WPS_RWPR_DIR_URL . 'public/js/points-and-rewards-campaign.js', array(), $this->version, true );
			wp_localize_script(
				$this->plugin_name,
				'wps_wpr_campaign_obj',
				array(
					'ajaxurl'              => admin_url( 'admin-ajax.php' ),
					'wps_wpr_nonce'        => wp_create_nonce( 'wps-wpr-verify-nonce' ),
					'is_user_login'        => is_user_logged_in(),
					'wps_modal_color_one'  => $wps_wpr_campaign_color_one,
					'wps_modal_color_two'  => $wps_wpr_campaign_color_two,
					'is_pro_plugin_active' => wps_wpr_is_par_pro_plugin_active(),
				)
			);
		}
	}

	/**
	 * This function is used to enequeue cart/block file.
	 *
	 * @return void
	 */
	public function wps_wpr_enqueue_cart_block_file() {

		if ( ! wps_wpr_restrict_user_fun() ) {

			$wps_wpr_other_settings                 = get_option( 'wps_wpr_other_settings', true );
			$wps_wpr_other_settings                 = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
			$wps_wpr_cart_page_total_earning_points = ! empty( $wps_wpr_other_settings['wps_wpr_cart_page_total_earning_points'] ) ? $wps_wpr_other_settings['wps_wpr_cart_page_total_earning_points'] : 0;
			wp_register_script( 'wp-wps-wpr-cart-class', WPS_RWPR_DIR_URL . 'public/js/points-and-rewards-cart-checkout-block.js', array(), $this->version, true );
			wp_enqueue_script( 'wp-wps-wpr-cart-class' );
			$wps_wpr = array(
				'ajaxurl'                                => admin_url( 'admin-ajax.php' ),
				'wps_wpr_nonce'                          => wp_create_nonce( 'wps-wpr-verify-nonce' ),
				'wps_wpr_cart_page_total_earning_points' => $wps_wpr_cart_page_total_earning_points,
				'current__points'                        => ! empty( get_user_meta( get_current_user_id(), 'wps_wpr_points', true ) ) ? (int) get_user_meta( get_current_user_id(), 'wps_wpr_points', true ) : 0,
				'available_points_msg'                   => esc_html__( 'Your available points', 'points-and-rewards-for-woocommerce' ),
			);
			wp_localize_script( 'wp-wps-wpr-cart-class', 'wps_wpr_cart_block_obj', $wps_wpr );
		}
	}

	/**
	 * This function is used get the general settings.
	 *
	 * @name wps_wpr_get_general_settings
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param string $id of the array.
	 */
	public function wps_wpr_get_general_settings( $id ) {
		$wps_wpr_value    = '';
		$general_settings = get_option( 'wps_wpr_settings_gallery', true );
		if ( ! empty( $general_settings[ $id ] ) ) {
			$wps_wpr_value = $general_settings[ $id ];
		}
		return $wps_wpr_value;
	}

	/**
	 * This function is used get the other settings.
	 *
	 * @name wps_wpr_get_other_settings
	 * @since    1.0.0
	 * @param string $id id of the settings.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_get_other_settings( $id ) {
		$wps_wpr_value    = '';
		$general_settings = get_option( 'wps_wpr_other_settings', true );
		if ( ! empty( $general_settings[ $id ] ) ) {
			$wps_wpr_value = $general_settings[ $id ];
		}
		return $wps_wpr_value;
	}

	/**
	 * This function is used get the general settings
	 *
	 * @name wps_wpr_get_general_settings
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param string $id  id of the general settings.
	 */
	public function wps_wpr_get_general_settings_num( $id ) {
		$wps_wpr_value    = 0;
		$general_settings = get_option( 'wps_wpr_settings_gallery', true );
		if ( ! empty( $general_settings[ $id ] ) ) {
			$wps_wpr_value = (int) $general_settings[ $id ];
		}
		return $wps_wpr_value;
	}

	/**
	 * This function is used get the coupon settings
	 *
	 * @name wps_wpr_get_general_settings
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param string $id name of the settings.
	 */
	public function wps_wpr_get_coupon_settings_num( $id ) {
		$wps_wpr_value    = 0;
		$general_settings = get_option( 'wps_wpr_coupons_gallery', true );
		if ( ! empty( $general_settings[ $id ] ) ) {
			$wps_wpr_value = $general_settings[ $id ];
		}
		return $wps_wpr_value;
	}

	/**
	 * This function is used get the other settings
	 *
	 * @name wps_wpr_get_other_settings_num
	 * @since    1.0.0
	 * @param string $id name of the settings.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_get_other_settings_num( $id ) {
		$wps_wpr_value    = 0;
		$general_settings = get_option( 'wps_wpr_other_settings', true );
		if ( ! empty( $general_settings[ $id ] ) ) {
			$wps_wpr_value = (int) $general_settings[ $id ];
		}
		return $wps_wpr_value;
	}

	/**
	 * This function is used get the order total settings
	 *
	 * @name wps_wpr_get_order_total_settings
	 * @since    1.0.0
	 * @param string $id name of the settings.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_get_order_total_settings( $id ) {
		$wps_wpr_value        = array();
		$order_total_settings = get_option( 'wps_wpr_order_total_settings', true );
		if ( ! empty( $order_total_settings[ $id ] ) ) {
			$wps_wpr_value = $order_total_settings[ $id ];
		}
		return $wps_wpr_value;
	}

	/**
	 * This function is used get the order total settings
	 *
	 * @name wps_wpr_get_order_total_settings
	 * @since    1.0.0
	 * @param string $id name of the settings.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_get_order_total_settings_num( $id ) {
		$wps_wpr_value        = 0;
		$order_total_settings = get_option( 'wps_wpr_order_total_settings', true );
		if ( ! empty( $order_total_settings[ $id ] ) ) {
			$wps_wpr_value = (int) $order_total_settings[ $id ];
		}
		return $wps_wpr_value;
	}

	/**
	 * This function is used to construct Points Tab in MY ACCOUNT Page.
	 *
	 * @name wps_wpr_add_my_account_endpoint
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_add_my_account_endpoint() {
		if ( ! wps_wpr_restrict_user_fun() ) {

			add_rewrite_endpoint( 'points', EP_PAGES );
			add_rewrite_endpoint( 'view-log', EP_PAGES );
			flush_rewrite_rules();
		}
	}

	/**
	 * This function is used to set User Role to see Points Tab in MY ACCOUNT Page.
	 *
	 * @name wps_wpr_points_dashboard
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $items array of the items.
	 */
	public function wps_wpr_points_dashboard( $items ) {

		if ( ! wps_wpr_restrict_user_fun() ) {

			$user_ID                 = get_current_user_ID();
			$user                    = new WP_User( $user_ID );
			$wps_wpr_points_tab_text = $this->wps_wpr_get_general_settings( 'wps_wpr_points_tab_text' );
			if ( empty( $wps_wpr_points_tab_text ) ) {
				$wps_wpr_points_tab_text = __( 'Points', 'points-and-rewards-for-woocommerce' );
			}

			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
			$items['points']          = $wps_wpr_points_tab_text;
			$items['customer-logout'] = $logout;
		}
		return apply_filters( 'wps_wpr_allowed_user_roles_points', $items );
	}

	/**
	 * This function is used to get user_id to get points in MY ACCOUNT Page Points Tab.
	 *
	 * @name wps_wpr_account_points
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_account_points() {

		$wps_wpr_others_settings          = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_others_settings          = ! empty( $wps_wpr_others_settings ) && is_array( $wps_wpr_others_settings ) ? $wps_wpr_others_settings : array();
		$wps_wpr_choose_account_page_temp = ! empty( $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] ) ? $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] : '';
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}

		$user_ID = get_current_user_ID();
		$user    = new WP_User( $user_ID );
		/* Include the template file in the woocommerce template*/
		if ( 'temp_three' === $wps_wpr_choose_account_page_temp ) {

			require plugin_dir_path( __FILE__ ) . 'partials/wps-wpr-template-three-points-tab.php';
		} else {

			require plugin_dir_path( __FILE__ ) . 'partials/wps-wpr-points-template.php';
		}
	}

	/**
	 * This function is used to include the working of View_point_log
	 *
	 * @name wps_wpr_account_viewlog
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_account_viewlog() {
		$user_ID = get_current_user_ID();
		$user    = new WP_User( $user_ID );
		require plugin_dir_path( __FILE__ ) . 'partials/wps-wpr-points-log-template.php';
	}

	/**
	 * This function is used to display the referral link
	 *
	 * @name wps_wpr_get_referral_section
	 * @since    1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $user_id of the user.
	 */
	public function wps_wpr_get_referral_section( $user_id ) {
		$get_referral        = get_user_meta( $user_id, 'wps_points_referral', true );
		$get_referral_invite = get_user_meta( $user_id, 'wps_points_referral_invite', true );

		if ( empty( $get_referral ) && empty( $get_referral_invite ) ) {
			$referral_key    = wps_wpr_create_referral_code();
			$referral_invite = 0;
			update_user_meta( $user_id, 'wps_points_referral', $referral_key );
			update_user_meta( $user_id, 'wps_points_referral_invite', $referral_invite );
		}

		do_action( 'wps_wpr_before_add_referral_section', $user_id );
		$get_referral        = get_user_meta( $user_id, 'wps_points_referral', true );
		$get_referral_invite = get_user_meta( $user_id, 'wps_points_referral_invite', true );

		$general_settings      = get_option( 'wps_wpr_settings_gallery', true );
		$wps_wpr_referral_page = ! empty( $general_settings['wps_wpr_referral_page'] ) ? $general_settings['wps_wpr_referral_page'] : '';
		$wps_wpr_page_url      = '';
		if ( ! empty( $wps_wpr_referral_page ) ) {
			$wps_wpr_page_url = get_page_link( $wps_wpr_referral_page[0] );
		} else {
			$wps_wpr_page_url = site_url();
		}

		$site_url = apply_filters( 'wps_wpr_referral_link_url', $wps_wpr_page_url );
		?>
		<div class="wps_account_wrapper wps_wpr_main_section_all_wrap">
			<p class="wps_wpr_heading"><?php echo esc_html__( 'Referral Link', 'points-and-rewards-for-woocommerce' ); ?></p>
			<fieldset class="wps_wpr_each_section">
				<div class="wps_wpr_refrral_code_copy">
					<p id="wps_wpr_copy"><code><?php echo esc_url( $site_url . '?pkey=' . $get_referral ); ?></code></p>
					<button class="wps_wpr_btn_copy wps_tooltip" data-clipboard-target="#wps_wpr_copy" aria-label="copied">
						<span class="wps_tooltiptext"><?php esc_html_e( 'Copy', 'points-and-rewards-for-woocommerce' ); ?></span>
						<img src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'public/images/copy.png' ); ?>" alt="Copy to clipboard">
					</button>
				</div>
				<?php
				do_action( 'wps_after_referral_link', $user_id );
				$this->wps_wpr_get_social_shraing_section( $user_id );
				?>
			</fieldset>
		</div>
		<?php
	}

	/**
	 * This is function is used for the validating the data.
	 *
	 * @name wps_wpr_allowed_html
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_allowed_html() {
		$allowed_tags = array(
			'span'  => array(
				'class'    => array(),
				'title'    => array(),
				'style'    => array(),
				'data-tip' => array(),
			),
			'min'    => array(),
			'max'    => array(),
			'class'  => array(),
			'style'  => array(),
			'<br>'   => array(),
			'div'    => array(
				'class' => array(),
				'id'                 => 'fb-root',
				'data-href'          => array(),
				'data-size'          => array(),
				'data-mobile-iframe' => array(),
				'data-layount'       => array( 'button_count' ),

			),
			'script' => '(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.9"; fjs.parentNode.insertBefore(js, fjs); }(document, "script", "facebook-jssdk"))',
			'a'      => array(
				'class'  => array(),
				'target' => array(),
				'href'   => array(),
				'src'    => array(),
			),
			'img' => array(
				'src' => array(),
			),
		);
		return apply_filters( 'wps_wpr_allowed_html', $allowed_tags );
	}

	/**
	 * This function used to display the social sharing
	 *
	 * @name wps_wpr_get_social_shraing_section
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $user_id userid of the customer.
	 */
	public function wps_wpr_get_social_shraing_section( $user_id ) {

		$user               = get_user_by( 'id', $user_id );
		$enable_wps_social  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_social_media_enable' );
		$user_reference_key = get_user_meta( $user_id, 'wps_points_referral', true );

		$general_settings      = get_option( 'wps_wpr_settings_gallery', true );
		$wps_wpr_referral_page = ! empty( $general_settings['wps_wpr_referral_page'] ) ? $general_settings['wps_wpr_referral_page'] : '';
		$wps_wpr_page_url      = '';

		if ( ! empty( $wps_wpr_referral_page ) ) {
			$wps_wpr_page_url = get_page_link( $wps_wpr_referral_page[0] );
		} else {
			$wps_wpr_page_url = site_url();
		}

		$page_permalink = apply_filters( 'wps_wpr_referral_link_url', $wps_wpr_page_url );
		do_action( 'wps_wpr_insta_refer', $user_reference_key, $enable_wps_social, $page_permalink );
		if ( $enable_wps_social ) {

			$user_email             = ! empty( $user->user_email ) ? $user->user_email : '';
			$wps_fb_and_twit_share  = '';
			$wps_email_share        = '';
			$wps_whats_and_pint     = '';
			$html_div               = '<div class="wps_wpr_wrapper_button">';
			$wps_fb_and_twit_share  = $wps_fb_and_twit_share . $html_div;
			$twitter_share_button   = '<div class="wps_wpr_btn wps_wpr_common_class"><a class="twitter-share-button" href="https://twitter.com/intent/tweet?text=' . $page_permalink . '?pkey=' . $user_reference_key . '" target="_blank"><img src ="' . WPS_RWPR_DIR_URL . '/public/images/xtwitter.svg"></a></div>';
			$facebook_share_button  = '<div id="fb-root"></div><div class="fb-share-button wps_wpr_common_class" data-href="' . $page_permalink . '?pkey=' . $user_reference_key . '" data-layout="button_count" data-size="small" data-mobile-iframe="true"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode( $page_permalink ) . '?pkey=' . $user_reference_key . '"><img src ="' . WPS_RWPR_DIR_URL . '/public/images/Facebook.svg"></a></div>';
			$mail_share_button      = '<a class="wps_wpr_mail_button wps_wpr_common_class" target="_blank" href="mailto:' . $user_email . '?subject=' . rawurlencode( 'Click on this link' ) . '&amp;body=' . rawurlencode( 'Check this out: ' . $page_permalink . '?pkey=' . $user_reference_key ) . '" rel="nofollow"><img src="' . WPS_RWPR_DIR_URL . 'public/images/email.svg"></a>';
			$email_share_button     = apply_filters( 'wps_mail_box', $wps_fb_and_twit_share, $user_id );
			$whatsapp_share_button  = '<a target="_blank" class="wps_wpr_whatsapp_share" href="https://api.whatsapp.com/send?text=' . rawurlencode( $page_permalink ) . '?pkey=' . $user_reference_key . '"><img src="' . WPS_RWPR_DIR_URL . 'public/images/WhatsApp.svg"></a>';
			$pinterest_share_button = '<div class="wps_wpr_btn wps_wpr_common_class wps_wpr_share_pinterest_btn"><a class="" href="http://pinterest.com/pin/create/link/?url=' . rawurlencode( $page_permalink ) . '?pkey=' . $user_reference_key . '" target="_blank"><img src ="' . WPS_RWPR_DIR_URL . 'public/images/pinterest.png"></a></div>';

			// facebook share button.
			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_facebook' ) == 1 ) {

				$wps_fb_and_twit_share = $wps_fb_and_twit_share . $facebook_share_button;
			}

			// twitter share button.
			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_twitter' ) == 1 ) {

				$wps_fb_and_twit_share = $wps_fb_and_twit_share . $twitter_share_button;
			}
			echo wp_kses_post( $wps_fb_and_twit_share );

			// email share button.
			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_email' ) == 1 ) {

				if ( $email_share_button != $html_div ) {

					$wps_email_share = $email_share_button;
				} else {
					$wps_email_share = $mail_share_button;
				}

				$allowed_html = array(
					'div' => array(
						'id'    => array(),
						'class' => array(),
					),
					'a' => array(
						'href'  => array(),
						'class' => array(),
						'target' => array(),
					),
					'p' => array(
						'id' => array(),
					),
					'button' => array(
						'id'    => array(),
						'class' => array(),
					),
					'img' => array(
						'src' => array(),
					),
					'input' => array(
						'type'        => array(),
						'style'       => array(),
						'id'          => array(),
						'value'       => array(),
						'placeholder' => array(),
						'name'        => array(),
						'data-id'     => array(),
						'class'       => array(),
					),
				);
				echo wp_kses( $wps_email_share, $allowed_html );
			}

			// whatsapp share button.
			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_whatsapp' ) == 1 ) {

				$wps_whats_and_pint .= $whatsapp_share_button;
			}

			// pinterest share button.
			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_pinterest' ) == 1 ) {

				$wps_whats_and_pint .= $pinterest_share_button;
			}

			echo wp_kses_post( $wps_whats_and_pint );
		}
	}

	/**
	 * The function is used for set the cookie for referee
	 *
	 * @name wps_wpr_referral_link_using_cookie
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_referral_link_using_cookie() {

		if ( ! is_user_logged_in() ) {
			$wps_wpr_ref_link_expiry = $this->wps_wpr_get_general_settings( 'wps_wpr_ref_link_expiry' );
			if ( empty( $wps_wpr_ref_link_expiry ) ) {
				$wps_wpr_ref_link_expiry = 365;
			}
			if ( isset( $_GET['pkey'] ) && ! empty( $_GET['pkey'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification
				$wps_referral_key = sanitize_text_field( wp_unslash( $_GET['pkey'] ) );// phpcs:ignore WordPress.Security.NonceVerification

				$referral_link = trim( $wps_referral_key );// phpcs:ignore WordPress.Security.NonceVerification

				if ( isset( $wps_wpr_ref_link_expiry ) && ! empty( $wps_wpr_ref_link_expiry ) && ! empty( $referral_link ) ) {
					setcookie( 'wps_wpr_cookie_set', $referral_link, time() + ( 86400 * $wps_wpr_ref_link_expiry ), '/' );
				}
			}
		}

		// calling function to unset points session.
		$this->wps_wpr_unset_points_session_while_points_negative();
		// calling function to calculate overall points.
		$this->wps_wpr_overall_accumulated_points();
	}

	/**
	 * Points update in time of new user registeration.
	 *
	 * @name wps_wpr_new_customer_registerd
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int $customer_id  user id of the customer.
	 */
	public function wps_wpr_new_customer_registerd( $customer_id ) {

		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features_signup', false, $customer_id ) ) {
			return;
		}

		if ( get_user_by( 'ID', $customer_id ) ) {

			$enable_wps_signup = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup' );
			$cookie_val        = isset( $_COOKIE['wps_wpr_cookie_set'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['wps_wpr_cookie_set'] ) ) : '';
			if ( $enable_wps_signup && apply_filters( 'wps_wpr_check_referral_cookie', true, $cookie_val, $customer_id ) ) {

				$wps_signup_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup_value' );
				/*Update User Points*/
				update_user_meta( $customer_id, 'wps_wpr_points', $wps_signup_value );
				/*Update the points Details of the users*/
				$data = array();
				$this->wps_wpr_update_points_details( $customer_id, 'registration', $wps_signup_value, $data );
				/*Send Email to user For the signup*/
				$this->wps_wpr_send_notification_mail( $customer_id, 'signup_notification' );
				// send sms.
				wps_wpr_send_sms_org( $customer_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received bonus points for signing up. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $wps_signup_value ) );
				// send messages on whatsapp.
				wps_wpr_send_messages_on_whatsapp( $customer_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received bonus points for signing up. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $wps_signup_value ) );
			}

			// Assign referral points.
			$enable_wps_refer = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_enable' );
			if ( $enable_wps_refer ) {

				$wps_refer_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_value' );
				$wps_refer_value = ! empty( $wps_refer_value ) ? $wps_refer_value : 1;
				$cookie_val      = isset( $_COOKIE['wps_wpr_cookie_set'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['wps_wpr_cookie_set'] ) ) : '';
				if ( ! empty( $cookie_val ) ) {
					$args['meta_query'] = array(
						array(
							'key'     => 'wps_points_referral',
							'value'   => trim( $cookie_val ),
							'compare' => '==',
						),
					);

					$refere_data = get_users( $args );
					$refere_id   = $refere_data[0]->data->ID;
					$get_points  = get_user_meta( $refere_id, 'wps_wpr_points', true );
					$get_points  = ! empty( $get_points ) ? (int) $get_points : 0;
					update_option( 'refereeid', $get_points );

					// updating total referral count.
					$wps_wpr_total_referral_count = ! empty( get_user_meta( $refere_id, 'wps_wpr_total_referral_count', true ) ) ? get_user_meta( $refere_id, 'wps_wpr_total_referral_count', true ) : 0;
					update_user_meta( $refere_id, 'wps_wpr_total_referral_count', $wps_wpr_total_referral_count + 1 );

					// filter that will add restriction.
					$wps_wpr_referral_program = true;
					$wps_wpr_referral_program = apply_filters( 'wps_wpr_referral_points', $wps_wpr_referral_program, $customer_id, $refere_id );

					// store all referral user name.
					$wps_store_referral_user_ids = get_user_meta( $refere_id, 'wps_store_referral_user_ids', true );
					$wps_store_referral_user_ids = ! empty( $wps_store_referral_user_ids ) && is_array( $wps_store_referral_user_ids ) ? $wps_store_referral_user_ids : array();
					if ( isset( $wps_store_referral_user_ids['wps_store_referral_user_ids'] ) && ! empty( $wps_store_referral_user_ids['wps_store_referral_user_ids'] ) ) {

						$custom_array = array(
							'refered_user' => $customer_id,
						);
						$wps_store_referral_user_ids['wps_store_referral_user_ids'][] = $custom_array;
					} else {

						$custom_array = array(
							'refered_user' => $customer_id,
						);
						$wps_store_referral_user_ids['wps_store_referral_user_ids'][] = $custom_array;
					}
					update_user_meta( $refere_id, 'wps_store_referral_user_ids', $wps_store_referral_user_ids );

					if ( $wps_wpr_referral_program ) {

						$total_points = (int) ( $get_points + $wps_refer_value );
						// update the points of the referred user.
						update_user_meta( $refere_id, 'wps_wpr_points', $total_points );

						$wps_store_referral_user_ids = get_user_meta( $refere_id, 'wps_store_referral_user_ids', true );
						$wps_store_referral_user_ids = ! empty( $wps_store_referral_user_ids ) && is_array( $wps_store_referral_user_ids ) ? $wps_store_referral_user_ids : array();
						$this->wps_wpr_update_points_details( $refere_id, 'reference_details', $wps_refer_value, $wps_store_referral_user_ids );
						/*Send Email to user For the signup*/
						$this->wps_wpr_send_notification_mail( $refere_id, 'referral_notification' );
						/*Destroy the cookie*/
						$this->wps_wpr_destroy_cookie();
						$wps_store_referral_user_ids = array();
						update_user_meta( $refere_id, 'wps_store_referral_user_ids', $wps_store_referral_user_ids );
						// send sms.
						wps_wpr_send_sms_org( $refere_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've earned referral points. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $total_points ) );
						// send messages on whatsapp.
						wps_wpr_send_messages_on_whatsapp( $refere_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've earned referral points. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $total_points ) );
						do_action( 'wps_wpr_referral_features_extend', $customer_id, $refere_id );
					}
				}
			}

			// Assign points to guest user.
			$this->wps_wpr_assign_points_to_guest_user( $customer_id );
		}
	}

	/**
	 * Update points details in the public section.
	 *
	 * @name wps_wpr_update_points_details
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int    $user_id  User id of the user.
	 * @param string $type type of description.
	 * @param int    $points  No. of points.
	 * @param array  $data  Data of the points details.
	 */
	public function wps_wpr_update_points_details( $user_id, $type, $points, $data ) {

		$today_date = date_i18n( 'Y-m-d h:i:sa' );
		/*Create the Referral Signup*/
		if ( 'reference_details' == $type ) {

			$get_referral_detail = get_user_meta( $user_id, 'points_details', true );
			$get_referral_detail = ! empty( $get_referral_detail ) && is_array( $get_referral_detail ) ? $get_referral_detail : array();

			if ( isset( $get_referral_detail[ $type ] ) && ! empty( $get_referral_detail[ $type ] ) ) {
				$custom_array = array(
					$type => $points,
					'date' => $today_date,
					'refered_user' => $data['wps_store_referral_user_ids'],
				);
				$get_referral_detail[ $type ][] = $custom_array;
			} else {
				if ( ! is_array( $get_referral_detail ) ) {
					$get_referral_detail = array();
				}
				$get_referral_detail[ $type ][] = array(
					$type => $points,
					'date' => $today_date,
					'refered_user' => $data['wps_store_referral_user_ids'],
				);
			}

			/*Update the user meta for the points details*/
			update_user_meta( $user_id, 'points_details', $get_referral_detail );
		}

		// referral product purchase points.
		if ( 'ref_product_detail' == $type ) {

			$get_referral_detail = get_user_meta( $user_id, 'points_details', true );
			$get_referral_detail = ! empty( $get_referral_detail ) && is_array( $get_referral_detail ) ? $get_referral_detail : array();

			if ( isset( $get_referral_detail[ $type ] ) && ! empty( $get_referral_detail[ $type ] ) ) {
				$custom_array = array(
					$type => $points,
					'date' => $today_date,
					'refered_user' => $data['referr_id'],
				);
				$get_referral_detail[ $type ][] = $custom_array;
			} else {

				$get_referral_detail[ $type ][] = array(
					$type => $points,
					'date' => $today_date,
					'refered_user' => $data['referr_id'],
				);
			}

			/*Update the user meta for the points details*/
			update_user_meta( $user_id, 'points_details', $get_referral_detail );
		}

		/*Here is cart discount through the points*/
		if ( 'cart_subtotal_point' == $type || 'product_details' == $type || 'registration' == $type || 'points_on_order' == $type || 'membership' == $type ) {
			$cart_subtotal_point_arr = get_user_meta( $user_id, 'points_details', true );
			$cart_subtotal_point_arr = ! empty( $cart_subtotal_point_arr ) && is_array( $cart_subtotal_point_arr ) ? $cart_subtotal_point_arr : array();
			if ( isset( $cart_subtotal_point_arr[ $type ] ) && ! empty( $cart_subtotal_point_arr[ $type ] ) ) {
				$cart_array = array(
					$type => $points,
					'date' => $today_date,
				);
				$cart_subtotal_point_arr[ $type ][] = $cart_array;
			} else {
				if ( ! is_array( $cart_subtotal_point_arr ) ) {
					$cart_subtotal_point_arr = array();
				}
				$cart_array = array(
					$type => $points,
					'date' => $today_date,
				);
				$cart_subtotal_point_arr[ $type ][] = $cart_array;
			}
			/*Update the user meta for the points details*/
			update_user_meta( $user_id, 'points_details', $cart_subtotal_point_arr );
		}

		if ( 'Receiver_point_details' == $type || 'Sender_point_details' == $type ) {
			$wps_points_sharing = get_user_meta( $user_id, 'points_details', true );
			$wps_points_sharing = ! empty( $wps_points_sharing ) && is_array( $wps_points_sharing ) ? $wps_points_sharing : array();
			if ( isset( $wps_points_sharing[ $type ] ) && ! empty( $wps_points_sharing[ $type ] ) ) {
				$custom_array = array(
					$type => $points,
					'date' => $today_date,
					$data['type'] => $data['user_id'],
				);
				$wps_points_sharing[ $type ][] = $custom_array;
			} else {
				if ( ! is_array( $wps_points_sharing ) ) {
					$wps_points_sharing = array();
				}
				$wps_points_sharing[ $type ][] = array(
					$type => $points,
					'date' => $today_date,
					$data['type'] => $data['user_id'],
				);
			}
			/*Update the user meta for the points details*/
			update_user_meta( $user_id, 'points_details', $wps_points_sharing );
		}

		// Refund points per currency setting conversions.
		if ( $points > 0 ) {
			if ( 'pro_conversion_points' == $type ) {
				$get_referral_detail = get_user_meta( $user_id, 'points_details', true );
				$get_referral_detail = ! empty( $get_referral_detail ) && is_array( $get_referral_detail ) ? $get_referral_detail : array();
				if ( isset( $get_referral_detail[ $type ] ) && ! empty( $get_referral_detail[ $type ] ) ) {
					$custom_array = array(
						$type => $points,
						'date' => $today_date,
						'refered_order_id' => $data['wps_par_order_id'],
					);
					$get_referral_detail[ $type ][] = $custom_array;
				} else {
					if ( ! is_array( $get_referral_detail ) ) {
						$get_referral_detail = array();
					}
					$get_referral_detail[ $type ][] = array(
						$type => $points,
						'date' => $today_date,
						'refered_order_id' => $data['wps_par_order_id'],
					);
				}
				update_user_meta( $user_id, 'points_details', $get_referral_detail );
			}
		}
		do_action( 'wps_wpr_update_points_log', $user_id );
		return 'Successfully';
	}

	/**
	 * Send mail to the users
	 *
	 * @name wps_wpr_update_points_details
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int    $user_id    User id of the user.
	 * @param string $type    Type of the mail.
	 */
	public function wps_wpr_send_notification_mail( $user_id, $type ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! empty( $user_id ) && $user ) {

			$user_name                 = $user->user_login;
			$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
			$total_points              = get_user_meta( $user_id, 'wps_wpr_points', true );
			/*check if not empty the notification array*/
			if ( ! empty( $wps_wpr_notificatin_array ) && is_array( $wps_wpr_notificatin_array ) ) {
				/*Get the Email Subject*/
				if ( 'signup_notification' == $type ) {
					$wps_wpr_email_subject = self::wps_wpr_get_email_notification_description( 'wps_wpr_signup_email_subject' );
					/*Get the Email Description*/
					$wps_wpr_email_discription = self::wps_wpr_get_email_notification_description( 'wps_wpr_signup_email_discription_custom_id' );
					/*SignUp value*/
					$wps_signup_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup_value' );
					/*Referral value*/
					$wps_refer_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_value' );
					$wps_refer_value = ( 0 == $wps_refer_value ) ? 1 : $wps_refer_value;

					$wps_wpr_email_discription = str_replace( '[Points]', $wps_signup_value, $wps_wpr_email_discription );
					$wps_wpr_email_discription = str_replace( '[Total Points]', $total_points, $wps_wpr_email_discription );
					$wps_wpr_email_discription = str_replace( '[Refer Points]', $wps_refer_value, $wps_wpr_email_discription );
					$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );
					$check_enable              = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'signup_notification' );

					/*check is mail notification is enable or not*/
					if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {

						/*Send the email to user related to the signup*/
						$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
						$email_status = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
					}
				}

				if ( 'referral_notification' == $type ) {
					$wps_wpr_email_subject = self::wps_wpr_get_email_notification_description( 'wps_wpr_referral_email_subject' );
					/*Get the Email Description*/
					$wps_wpr_email_discription = self::wps_wpr_get_email_notification_description( 'wps_wpr_referral_email_discription_custom_id' );
					/*SignUp value*/
					$wps_signup_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup_value' );
					/*Referral value*/
					$wps_refer_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_value' );
					$wps_refer_value = ( 0 == $wps_refer_value ) ? 1 : $wps_refer_value;

					$wps_wpr_email_discription = str_replace( '[Points]', $wps_refer_value, $wps_wpr_email_discription );
					$wps_wpr_email_discription = str_replace( '[Total Points]', $total_points, $wps_wpr_email_discription );
					$wps_wpr_email_discription = str_replace( '[Refer Points]', $wps_refer_value, $wps_wpr_email_discription );
					$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );
					$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'referral_notification' );

					/*check is mail notification is enable or not*/
					if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {

						/*Send the email to user related to the signup*/
						$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
						$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
					}
				}
			}
		}
	}

	/**
	 * This function is used to get the Email descriptiion
	 *
	 * @name wps_wpr_check_mail_notfication_is_enable
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param string $id  key of the settings.
	 */
	public static function wps_wpr_get_email_notification_description( $id ) {
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		$wps_wpr_email_discription = isset( $wps_wpr_notificatin_array[ $id ] ) ? $wps_wpr_notificatin_array[ $id ] : '';
		return $wps_wpr_email_discription;
	}

	/**
	 * The function is used for destroy the cookie
	 *
	 * @name wps_wpr_destroy_cookie
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_destroy_cookie() {
		if ( isset( $_COOKIE['wps_wpr_cookie_set'] ) && ! empty( $_COOKIE['wps_wpr_cookie_set'] ) ) {
			setcookie( 'wps_wpr_cookie_set', '', time() - 3600, '/' );
		}
	}

	/**
	 * The function is check is order total setting is enable or not
	 *
	 * @name check_enable_offer
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function check_enable_offer() {
		$is_enable = false;
		$enable    = $this->wps_wpr_get_order_total_settings_num( 'wps_wpr_thankyouorder_enable' );
		if ( $enable ) {
			$is_enable = true;
		}
		return $is_enable;
	}

	/**
	 * This function is used for calculate points in the order settings
	 *
	 * @name calculate_points
	 * @since 1.0.0
	 * @param int $order_id  order id of the order.
	 * @param int $user_id   user id of the user.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function calculate_points( $order_id, $user_id ) {
		// hpos.
		$points_on_order = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#points_assignedon_order_total", true );
		if ( ! isset( $points_on_order ) || 'yes' != $points_on_order ) {
			/*Get the minimum order total value*/
			$thankyouorder_min = $this->wps_wpr_get_order_total_settings( 'wps_wpr_thankyouorder_minimum' );
			/*Get the maxmimm order total value*/
			$thankyouorder_max = $this->wps_wpr_get_order_total_settings( 'wps_wpr_thankyouorder_maximum' );
			/*Get the order points value that will assigned to the user*/
			$thankyouorder_value = $this->wps_wpr_get_order_total_settings( 'wps_wpr_thankyouorder_current_type' );
			$order               = wc_get_order( $order_id );
			/*Get the order total points*/
			$order_total = $order->get_total();
			// WOOCS - WooCommerce Currency Switcher Compatibility.
			$order_total  = apply_filters( 'wps_wpr_convert_same_currency_base_price', $order_total, $order_id );
			$total_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			/*Get the user*/
			$user = get_user_by( 'ID', $user_id );
			/*Get the user email*/
			if ( empty( $total_points ) ) {
				$total_points = 0;
			}
			if ( is_array( $thankyouorder_value ) && ! empty( $thankyouorder_value ) ) {
				foreach ( $thankyouorder_value as $key => $value ) {
					if (
						isset( $thankyouorder_min[ $key ] ) && ! empty( $thankyouorder_min[ $key ] ) && isset( $thankyouorder_max[ $key ] ) &&
						! empty( $thankyouorder_max[ $key ] )
					) {

						if (
							$thankyouorder_min[ $key ] <= $order_total &&
							$order_total <= $thankyouorder_max[ $key ]
						) {
							$wps_wpr_point = (int) $thankyouorder_value[ $key ];
							$total_points = $total_points + $wps_wpr_point;
						}
					} else if (
						isset( $thankyouorder_min[ $key ] ) &&
						! empty( $thankyouorder_min[ $key ] ) &&
						empty( $thankyouorder_max[ $key ] )
					) {
						if ( $thankyouorder_min[ $key ] <= $order_total ) {
							$wps_wpr_point = (int) $thankyouorder_value[ $key ];
							$total_points = $total_points + $wps_wpr_point;
						}
					}
				}
			}

			/*if not empty the total points*/
			if ( ! empty( $total_points ) ) {
				update_user_meta( $user_id, 'wps_wpr_points', $total_points );
			}

			/*if not empty the total points*/
			if ( ! empty( $wps_wpr_point ) ) {
				$data = array();
				$this->wps_wpr_update_points_details( $user_id, 'points_on_order', $wps_wpr_point, $data );
				// hpos.
				wps_wpr_hpos_update_meta_data( $order_id, "$order_id#points_assignedon_order_total", 'yes' );
				$wps_wpr_shortcode = array(
					'[ORDERTOTALPOINT]' => $wps_wpr_point,
					'[TOTALPOINTS]'     => $total_points,
					'[USERNAME]'        => $user->user_login,
				);
				$wps_wpr_subject_content = array(
					'wps_wpr_subject' => 'wps_wpr_point_on_order_total_range_subject',
					'wps_wpr_content' => 'wps_wpr_point_on_order_total_range_desc',
				);
				/*Send mail to client regarding product purchase*/
				$this->wps_wpr_send_notification_mail_product( $user_id, $wps_wpr_point, $wps_wpr_shortcode, $wps_wpr_subject_content );
				// send sms.
				wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received points based on your order total. Your current points balance is now %s", 'points-and-rewards-for-woocommerce' ), $total_points ) );
				// send messages on whatsapp.
				wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received points based on your order total. Your current points balance is now %s", 'points-and-rewards-for-woocommerce' ), $total_points ) );
			}
		}
	}

	/**
	 * This function is used to give product points to user if order status of Product is complete and processing.
	 *
	 * @name wps_wpr_woocommerce_order_status_changed
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int    $order_id order  id of the order.
	 * @param string $old_status  old status of the order.
	 * @param string $new_status  new status of the order.
	 */
	public function wps_wpr_woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {
		// mypos
		// check allowed user for points features.
		if ( $old_status != $new_status ) {

			// restrict user from points table.
			if ( wps_wpr_restrict_user_fun() ) {

				return;
			}

			$points_key_priority_high              = false;
			$wps_wpr_one_email                     = false;
			$item_points                           = 0;
			$wps_wpr_assigned_points               = true;
			$conversion_points_is_enable_condition = true;
			/*product assigned points*/
			$wps_wpr_assigned_points = apply_filters( 'wps_wpr_assigned_points', $wps_wpr_assigned_points );
			/*Get the conversion value of the coupon*/
			$wps_wpr_coupon_conversion_enable = $this->is_order_conversion_enabled();
			/*Get the order from the order id*/
			$order      = wc_get_order( $order_id );
			$user_id    = absint( $order->get_user_id() );
			$today_date = date_i18n( 'Y-m-d h:i:sa' );
			if ( empty( $user_id ) || is_null( $user_id ) ) {

				return;
			}

			// Restrict rewards points features.
			if ( ! $this->wps_wpr_restrict_user_rewards_points_callback( $order_id ) ) {
				return;
			}

			// assign points according to user membership level.
			$this->wps_wpr_assign_membership_rewards_points( $order_id, $old_status, $new_status );

			$user = get_user_by( 'ID', $user_id );
			if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features_order', false, $user ) ) {
				return;
			}
			$user_email = $user->user_email;
			if ( 'completed' == $new_status ) {

				if ( isset( $user_id ) && ! empty( $user_id ) ) {
					$wps_wpr_ref_noof_order = (int) get_user_meta( $user_id, 'wps_wpr_no_of_orders', true );
					if ( isset( $wps_wpr_ref_noof_order ) && ! empty( $wps_wpr_ref_noof_order ) ) {
						// hpos.
						$order_limit = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#$wps_wpr_ref_noof_order", true );
						if ( isset( $order_limit ) && 'set' == $order_limit ) {
							return;
						} else {
							$wps_wpr_ref_noof_order++;
							update_user_meta( $user_id, 'wps_wpr_no_of_orders', $wps_wpr_ref_noof_order );
						}
					} else {
						update_user_meta( $user_id, 'wps_wpr_no_of_orders', 1 );
					}
				}

				if ( isset( $user_id ) && ! empty( $user_id ) ) {
					/*Order total points*/
					if ( $this->check_enable_offer() ) {
						$this->calculate_points( $order_id, $user_id );

					}
					foreach ( $order->get_items() as $item_id => $item ) {
						/*Get The item meta data*/
						$wps_wpr_items = $item->get_meta_data();
						foreach ( $wps_wpr_items as $key => $wps_wpr_value ) {
							if ( $wps_wpr_assigned_points ) {
								if ( isset( $wps_wpr_value->key ) && ! empty( $wps_wpr_value->key ) && ( 'Points' == $wps_wpr_value->key ) ) {
									// hpos.
									$itempointsset = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#$wps_wpr_value->id#set", true );

									if ( 'set' == $itempointsset ) {
										continue;
									}
									$wps_wpr_points    = (int) $wps_wpr_value->value;
									$item_points       += (int) $wps_wpr_points;
									$wps_wpr_one_email = true;
									$product_id        = $item->get_product_id();
									$check_enable      = wps_wpr_hpos_get_meta_data( $product_id, 'wps_product_points_enable', 'no' );
									if ( 'yes' == $check_enable ) {
										// hpos.
										wps_wpr_hpos_update_meta_data( $order_id, "$order_id#$wps_wpr_value->id#set", 'set' );
									}
									if ( $wps_wpr_coupon_conversion_enable ) {
										$points_key_priority_high = true;
									}
								}
							}
						}
					}
				}

				// Rewards Per Currency points.
				$order_total = $order->get_total();
				$order_total = apply_filters( 'wps_wpr_per_currency_points_on_subtotal', $order_total, $order );
				// WOOCS - WooCommerce Currency Switcher Compatibility.
				$order_total = apply_filters( 'wps_wpr_convert_same_currency_base_price', $order_total, $order_id );
				$order_total = str_replace( wc_get_price_decimal_separator(), '.', strval( $order_total ) );
				if ( $wps_wpr_coupon_conversion_enable ) {

					$item_conversion_id_set = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#item_conversion_id", true );
					if ( empty( $item_conversion_id_set ) ) {

						$user_id = $order->get_user_id();
						$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
						/*total calculation of the points*/
						$wps_wpr_coupon_conversion_points = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_price' );
						$wps_wpr_coupon_conversion_points = ( 0 == $wps_wpr_coupon_conversion_points ) ? 1 : $wps_wpr_coupon_conversion_points;
						$wps_wpr_coupon_conversion_price  = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_points' );
						$wps_wpr_coupon_conversion_price  = ( 0 == $wps_wpr_coupon_conversion_price ) ? 1 : $wps_wpr_coupon_conversion_price;

						/*Calculat points of the order*/
						$points_calculation = round( ( $order_total * $wps_wpr_coupon_conversion_points ) / $wps_wpr_coupon_conversion_price );
						$points_calculation = apply_filters( 'wps_round_down_cart_total_value', $points_calculation, $order_total, $wps_wpr_coupon_conversion_points, $wps_wpr_coupon_conversion_price );
						/*Total Point of the order*/
						$total_points = intval( $points_calculation + $get_points );

						$data = array(
							'wps_par_order_id' => $order_id,
						);
						/*Update points details in woocommerce*/
						$this->wps_wpr_update_points_details( $user_id, 'pro_conversion_points', $points_calculation, $data );
						/*update users totoal points*/
						update_user_meta( $user_id, 'wps_wpr_points', $total_points );
						/*update that user has get the rewards points hpos.*/
						wps_wpr_hpos_update_meta_data( $order_id, "$order_id#item_conversion_id", 'set' );
						/*Prepare Array to send mail*/
						$wps_wpr_shortcode = array(
							'[Points]'                    => $points_calculation,
							'[Total Points]'              => $total_points,
							'[Refer Points]'              => $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_value' ),
							'[Comment Points]'            => $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_comment_enable' ),
							'[Per Currency Spent Points]' => $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_points' ),
							'[USERNAME]'                  => $user->user_login,
						);

						$wps_wpr_subject_content = array(
							'wps_wpr_subject' => 'wps_wpr_amount_email_subject',
							'wps_wpr_content' => 'wps_wpr_amount_email_discription_custom_id',
						);
						/*Send mail to client regarding product purchase*/
						$this->wps_wpr_send_notification_mail_product( $user_id, $points_calculation, $wps_wpr_shortcode, $wps_wpr_subject_content );
						// send sms.
						wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received points based on the currency value of your purchase. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $total_points ) );
						// send messages on whatsapp.
						wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received points based on the currency value of your purchase. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $total_points ) );
					}
				}

				if ( $wps_wpr_one_email && 'yes' == $check_enable && isset( $item_points ) && ! empty( $item_points ) ) {
					$user_id = absint( $order->get_user_id() );
					if ( ! empty( $user_id ) ) {
						$user       = get_user_by( 'ID', $user_id );
						$user_email = $user->user_email;
						$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
						$data       = array();
						/*Update points details in woocommerce*/
						$this->wps_wpr_update_points_details( $user_id, 'product_details', $item_points, $data );
						/*Total Points of the products*/
						$total_points = $get_points + $item_points;
						/*Update User Points*/
						update_user_meta( $user_id, 'wps_wpr_points', $total_points );

						$wps_wpr_shortcode = array(
							'[Points]'                    => $item_points,
							'[Total Points]'              => $total_points,
							'[Refer Points]'              => $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_value' ),
							'[Comment Points]'            => $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_comment_enable' ),
							'[Per Currency Spent Points]' => $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_points' ),
							'[USERNAME]'                  => $user->user_login,
						);
						$wps_wpr_subject_content = array(
							'wps_wpr_subject' => 'wps_wpr_product_email_subject',
							'wps_wpr_content' => 'wps_wpr_product_email_discription_custom_id',
						);
						/*Send mail to client regarding product purchase*/
						$this->wps_wpr_send_notification_mail_product( $user_id, $item_points, $wps_wpr_shortcode, $wps_wpr_subject_content );
						// send sms.
						wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'You have received assigned product points. Your total points balance is now %s', 'points-and-rewards-for-woocommerce' ), $total_points ) );
						// send messages on whatsapp.
						wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'You have received assigned product points. Your total points balance is now %s', 'points-and-rewards-for-woocommerce' ), $total_points ) );
					}
				}
			}
		}

		// Applied points on cart refunded here.
		$mwb_wpr_array = array( 'processing', 'on-hold', 'pending', 'completed', 'failed' );
		if ( in_array( $old_status, $mwb_wpr_array, true ) && ( 'cancelled' === $new_status || 'refunded' === $new_status ) ) {

			$order          = wc_get_order( $order_id );
			$user_id        = absint( $order->get_user_id() );
			$wps_points_log = get_user_meta( $user_id, 'points_details', true );
			$wps_points_log = ! empty( $wps_points_log ) && is_array( $wps_points_log ) ? $wps_points_log : array();
			if ( array_key_exists( 'cart_subtotal_point', $wps_points_log ) ) {

				$today_date = date_i18n( 'Y-m-d h:i:sa' );
				// hpos.
				$wps_value_to_check = absint( wps_wpr_hpos_get_meta_data( $order_id, 'wps_cart_discount#points', true ) );
				foreach ( $wps_points_log['cart_subtotal_point'] as $key => $value ) {
					// hpos.
					$pre_wps_check = wps_wpr_hpos_get_meta_data( $order_id, 'refunded_points_by_cart', true );

					if ( ! isset( $pre_wps_check ) || 'done' != $pre_wps_check ) {
						if ( $value['cart_subtotal_point'] == $wps_value_to_check ) {

							$value_to_refund          = $value['cart_subtotal_point'];
							$wps_total_points_par     = get_user_meta( $user_id, 'wps_wpr_points', true );
							$wps_points_newly_updated = (int) ( $wps_total_points_par + $value_to_refund );
							$wps_refer_deduct_points  = get_user_meta( $user_id, 'points_details', true );
							$wps_refer_deduct_points  = ! empty( $wps_refer_deduct_points ) && is_array( $wps_refer_deduct_points ) ? $wps_refer_deduct_points : array();
							if ( isset( $wps_refer_deduct_points['refund_points_applied_on_cart'] ) && ! empty( $wps_refer_deduct_points['refund_points_applied_on_cart'] ) ) {

								$wps_par_refund_purchase = array();
								$wps_par_refund_purchase = array(
									'refund_points_applied_on_cart' => $value_to_refund,
									'date' => $today_date,
								);
								$wps_refer_deduct_points['refund_points_applied_on_cart'][] = $wps_par_refund_purchase;
							} else {
								if ( ! is_array( $wps_refer_deduct_points ) ) {
									$wps_refer_deduct_points = array();
								}
								$wps_par_refund_purchase = array();
								$wps_par_refund_purchase = array(
									'refund_points_applied_on_cart' => $value_to_refund,
									'date' => $today_date,
								);
								$wps_refer_deduct_points['refund_points_applied_on_cart'][] = $wps_par_refund_purchase;
							}
							update_user_meta( $user_id, 'wps_wpr_points', $wps_points_newly_updated );
							update_user_meta( $user_id, 'points_details', $wps_refer_deduct_points );
							// hpos.
							wps_wpr_hpos_update_meta_data( $order_id, 'refunded_points_by_cart', 'done' );
							// send sms.
							wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your points have been added back to your account due to the order cancellation. Your total points balance is now %s', 'points-and-rewards-for-woocommerce' ), $wps_points_newly_updated ) );
							// send messages on whatsapp.
							wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your points have been added back to your account due to the order cancellation. Your total points balance is now %s', 'points-and-rewards-for-woocommerce' ), $wps_points_newly_updated ) );
						}
					}
				}
			}

			// Refund subscription renewal awarded points when subscription is cancelled or refunded.
			if ( wps_wpr_check_is_subscription_plugin_active() ) {
				if ( array_key_exists( 'subscription_renewal_points', $wps_points_log ) ) {

					$today_date = date_i18n( 'Y-m-d h:i:sa' );
					// hpos.
					$wps_value_to_check = absint( wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_subscription_renewal_awarded_points', true ) );
					$wps_value_to_check = ! empty( $wps_value_to_check ) ? $wps_value_to_check : 0;

					foreach ( $wps_points_log['subscription_renewal_points'] as $key => $value ) {
						$pre_wps_check = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_subscription_renewal_refund', true );

						if ( ! isset( $pre_wps_check ) || 'done' != $pre_wps_check ) {
							if ( $value['subscription_renewal_points'] == $wps_value_to_check ) {

								$value_to_refund                 = $value['subscription_renewal_points'];
								$wps_total_points_par            = get_user_meta( $user_id, 'wps_wpr_points', true );
								$wps_points_newly_updated        = (int) ( $wps_total_points_par - $value_to_refund );
								$wps_subscription_renewal_refund = get_user_meta( $user_id, 'points_details', true );
								$wps_subscription_renewal_refund = ! empty( $wps_subscription_renewal_refund ) && is_array( $wps_subscription_renewal_refund ) ? $wps_subscription_renewal_refund : array();
								if ( isset( $wps_subscription_renewal_refund['refund_subscription__renewal_points'] ) && ! empty( $wps_subscription_renewal_refund['refund_subscription__renewal_points'] ) ) {

									$wps_par_refund_purchase = array();
									$wps_par_refund_purchase = array(
										'refund_subscription__renewal_points' => $value_to_refund,
										'date'                                => $today_date,
									);
									$wps_subscription_renewal_refund['refund_subscription__renewal_points'][] = $wps_par_refund_purchase;
								} else {
									if ( ! is_array( $wps_subscription_renewal_refund ) ) {
										$wps_subscription_renewal_refund = array();
									}
									$wps_par_refund_purchase = array();
									$wps_par_refund_purchase = array(
										'refund_subscription__renewal_points' => $value_to_refund,
										'date'                                => $today_date,
									);
									$wps_subscription_renewal_refund['refund_subscription__renewal_points'][] = $wps_par_refund_purchase;
								}
								update_user_meta( $user_id, 'wps_wpr_points', $wps_points_newly_updated );
								update_user_meta( $user_id, 'points_details', $wps_subscription_renewal_refund );
								// hpos.
								wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_subscription_renewal_refund', 'done' );
								// send sms.
								wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your subscription renewal order has been cancelled, and %1$s points have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $value_to_refund, $wps_points_newly_updated ) );
								// send messages on whatsapp.
								wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your subscription renewal order has been cancelled, and %1$s points have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $value_to_refund, $wps_points_newly_updated ) );
							}
						}
					}
				}
			}
		}

		if ( ! wps_wpr_is_par_pro_plugin_active() ) {

			$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
			$mwb_wpr_array             = array( 'completed' );

			// per currency refund here in org.
			if ( in_array( $old_status, $mwb_wpr_array, true ) && ( 'cancelled' === $new_status || 'refunded' === $new_status ) ) {

				if ( $this->is_order_conversion_enabled() ) {
					// hpos.
					$item_conversion_id_set = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#item_conversion_id", true );
					$order_total            = $order->get_total();
					$order_total            = str_replace( wc_get_price_decimal_separator(), '.', strval( $order_total ) );
					$round_down_setting     = $this->wps_wpr_set_org_general_setting();

					if ( ! empty( $item_conversion_id_set ) && 'set' == $item_conversion_id_set ) {
						$refund_per_currency_spend_points = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#refund_per_currency_spend_points", true );
						if ( empty( $refund_per_currency_spend_points ) || 'yes' != $refund_per_currency_spend_points ) {

							$get_points  = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
							$points_log  = get_user_meta( $user_id, 'points_details', true );
							$points_log  = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();
							$all_refunds = $order->get_refunds();

							/*total calculation of the points*/
							$wps_wpr_coupon_conversion_points = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_points' );
							$wps_wpr_coupon_conversion_points = ( 0 == $wps_wpr_coupon_conversion_points ) ? 1 : $wps_wpr_coupon_conversion_points;

							/*Get the value of the price*/
							$wps_wpr_coupon_conversion_price = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_price' );
							$wps_wpr_coupon_conversion_price = ( 0 == $wps_wpr_coupon_conversion_price ) ? 1 : $wps_wpr_coupon_conversion_price;
							$round_down_setting              = $this->wps_wpr_set_org_general_setting();

							if ( array_key_exists( 'pro_conversion_points', $points_log ) ) {
								foreach ( $points_log['pro_conversion_points'] as $key => $value ) {
									if ( ! empty( $value['refered_order_id'] ) ) {
										if ( $value['refered_order_id'] == $order_id ) {
											$refund_amount = $value['pro_conversion_points'];
										}
									}
								}
							}

							if ( 'wps_wpr_round_down' == $round_down_setting ) {
								$refund_amount = floor( $refund_amount );
							} else {
								$refund_amount = ceil( $refund_amount );
							}

							$deduct_currency_spent = $refund_amount;
							$remaining_points      = $get_points - $deduct_currency_spent;

							if ( isset( $points_log['deduction_currency_spent'] ) && ! empty( $points_log['deduction_currency_spent'] ) ) {
								$currency_arr = array();
								$currency_arr = array(
									'deduction_currency_spent' => $deduct_currency_spent,
									'date' => $today_date,
								);
								$points_log['deduction_currency_spent'][] = $currency_arr;
							} else {
								$currency_arr = array();
								$currency_arr = array(
									'deduction_currency_spent' => $deduct_currency_spent,
									'date' => $today_date,
								);
								$points_log['deduction_currency_spent'][] = $currency_arr;
							}

							update_user_meta( $user_id, 'wps_wpr_points', $remaining_points );
							update_user_meta( $user_id, 'points_details', $points_log );
							// send sms.
							wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your order has been cancelled, and %1$s points (based on the currency value) have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $deduct_currency_spent, $remaining_points ) );
							// send messages on whatsapp.
							wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your order has been cancelled, and %1$s points (based on the currency value) have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $deduct_currency_spent, $remaining_points ) );
							// hpos.
							wps_wpr_hpos_update_meta_data( $order_id, "$order_id#refund_per_currency_spend_points", 'yes' );

							if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {

								$wps_wpr_email_subject     = ! empty( $wps_wpr_notificatin_array['wps_wpr_deduct_per_currency_point_subject'] ) ? $wps_wpr_notificatin_array['wps_wpr_deduct_per_currency_point_subject'] : '';
								$wps_wpr_email_discription = ! empty( $wps_wpr_notificatin_array['wps_wpr_deduct_per_currency_point_description'] ) ? $wps_wpr_notificatin_array['wps_wpr_deduct_per_currency_point_description'] : '';
								$wps_wpr_email_discription = str_replace( '[DEDUCTEDPOINT]', $deduct_currency_spent, $wps_wpr_email_discription );
								$wps_wpr_email_discription = str_replace( '[TOTALPOINTS]', $remaining_points, $wps_wpr_email_discription );
								$user                      = get_user_by( 'email', $user_email );
								$user_name                 = $user->user_firstname;
								$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );

								/*Check is points Email notification is enable*/
								$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'deduct_per_currency_spent_notification' );
								if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {
									$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
									$email_status = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
								}
							}
						}
					}
				}

				// refund global assign points here in org.
				if ( isset( $order ) && ! empty( $order ) ) {
					foreach ( $order->get_items() as $item_id => $item ) {

						$wps_wpr_items       = $item->get_meta_data();
						$deduction_of_points = get_user_meta( $user_id, 'points_details', true );
						$deduction_of_points = ! empty( $deduction_of_points ) && is_array( $deduction_of_points ) ? $deduction_of_points : array();

						foreach ( $wps_wpr_items as $key => $wps_wpr_value ) {
							$wps_wpr_assign_products_points = get_option( 'wps_wpr_assign_products_points', true );
							$wps_check_global_points_assign = $wps_wpr_assign_products_points['wps_wpr_global_product_enable'];

							if ( '1' == $wps_check_global_points_assign ) {
								if ( isset( $wps_wpr_value->key ) && ! empty( $wps_wpr_value->key ) && ( 'Points' == $wps_wpr_value->key ) ) {
									// hpos.
									$is_refunded = wps_wpr_hpos_get_meta_data( $order_id, "$order_id#$item_id#refund_points", true );

									if ( empty( $is_refunded ) || 'yes' != $is_refunded ) {

										$get_points   = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
										$deduct_point = $wps_wpr_value->value;
										$total_points = $get_points - $deduct_point;

										if ( isset( $deduction_of_points['deduction_of_points'] ) && ! empty( $deduction_of_points['deduction_of_points'] ) ) {

											$deduction_arr = array();
											$deduction_arr = array(
												'deduction_of_points' => $deduct_point,
												'date' => $today_date,
											);
											$deduction_of_points['deduction_of_points'][] = $deduction_arr;
										} else {
											$deduction_arr = array();
											$deduction_arr = array(
												'deduction_of_points' => $deduct_point,
												'date' => $today_date,
											);
											$deduction_of_points['deduction_of_points'][] = $deduction_arr;
										}

										update_user_meta( $user_id, 'wps_wpr_points', $total_points );
										update_user_meta( $user_id, 'points_details', $deduction_of_points );
										// send sms.
										wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( '%1$s assigned product points have been deducted from your account due to order cancellation. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $deduct_point, $total_points ) );
										// send messages on whatsapp.
										wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( '%1$s assigned product points have been deducted from your account due to order cancellation. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $deduct_point, $total_points ) );
										// hpos.
										wps_wpr_hpos_update_meta_data( $order_id, "$order_id#$item_id#refund_points", 'yes' );

										if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {

											$wps_wpr_email_subject     = isset( $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_subject'] ) ? $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_subject'] : '';
											$wps_wpr_email_discription = isset( $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_desciption'] ) ? $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_desciption'] : '';
											$wps_wpr_email_discription = str_replace( '[DEDUCTEDPOINT]', $deduct_point, $wps_wpr_email_discription );
											$wps_wpr_email_discription = str_replace( '[TOTALPOINTS]', $total_points, $wps_wpr_email_discription );
											$user                      = get_user_by( 'email', $user_email );
											$user_name                 = $user->user_firstname;
											$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );

											/*check is mail notification is enable or not*/
											$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'deduct_assign_points_notification' );
											if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {
												$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
												$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
											}
										}
									}
								}
							}
						}
					}

					// order total range points refund here ( min max ).
					if ( $this->check_enable_offer() ) {
						$this->wps_refund_order_total_point( $order_id );
					}
				}
			}
		}
	}

	/**
	 * This function is used to refund order.
	 *
	 * @param int $order_id order id.
	 * @return void
	 */
	public function wps_refund_order_total_point( $order_id ) {
		// hpos.
		$is_refunded = wps_wpr_hpos_get_meta_data( $order_id, '$order_id#wps_point_on_order_total', true );
		if ( ! isset( $is_refunded ) || 'yes' !== $is_refunded ) {

			$today_date                = date_i18n( 'Y-m-d h:i:sa' );
			$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );

			/*Get the minimum order total value*/
			$thankyouorder_min = $this->wps_wpr_get_order_total_settings( 'wps_wpr_thankyouorder_minimum' );

			/*Get the maxmimm order total value*/
			$thankyouorder_max = $this->wps_wpr_get_order_total_settings( 'wps_wpr_thankyouorder_maximum' );

			/*Get the order points value that will assigned to the user*/
			$thankyouorder_value = $this->wps_wpr_get_order_total_settings( 'wps_wpr_thankyouorder_current_type' );
			$order               = wc_get_order( $order_id );

			/*Get the order total points*/
			$order_total         = $order->get_total();
			// WOOCS - WooCommerce Currency Switcher Compatibility.
			$order_total         = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $order_total );
			$user_id             = $order->get_user_id();
			$get_points          = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			$deduction_of_points = get_user_meta( $user_id, 'points_details', true );
			$deduction_of_points = ! empty( $deduction_of_points ) && is_array( $deduction_of_points ) ? $deduction_of_points : array();

			/*Get the user*/
			$user = get_user_by( 'ID', $user_id );

			/*Get the user email*/
			$user_email   = $user->user_email;
			$total_points = 0;

			if ( is_array( $thankyouorder_value ) && ! empty( $thankyouorder_value ) ) {
				foreach ( $thankyouorder_value as $key => $value ) {
					if (
						isset( $thankyouorder_min[ $key ] ) && ! empty( $thankyouorder_min[ $key ] ) && isset( $thankyouorder_max[ $key ] ) &&
						! empty( $thankyouorder_max[ $key ] )
					) {

						if (
							$thankyouorder_min[ $key ] <= $order_total &&
							$order_total <= $thankyouorder_max[ $key ]
						) {
							$wps_wpr_point = (int) $thankyouorder_value[ $key ];
							$total_points  = $total_points + $wps_wpr_point;
						}
					} else if (
						isset( $thankyouorder_min[ $key ] ) &&
						! empty( $thankyouorder_min[ $key ] ) &&
						empty( $thankyouorder_max[ $key ] )
					) {
						if ( $thankyouorder_min[ $key ] <= $order_total ) {
							$wps_wpr_point = (int) $thankyouorder_value[ $key ];
							$total_points  = $total_points + $wps_wpr_point;
						}
					}
				}
			}

			$deduct_currency_spent = $total_points;
			$remaining_points      = $get_points - $deduct_currency_spent;

			if ( isset( $deduction_of_points['refund_points_on_order'] ) && ! empty( $deduction_of_points['refund_points_on_order'] ) ) {
				$currency_arr = array();
				$currency_arr = array(
					'refund_points_on_order' => $deduct_currency_spent,
					'date' => $today_date,
				);
				$deduction_of_points['refund_points_on_order'][] = $currency_arr;
			} else {
				$currency_arr = array();
				$currency_arr = array(
					'refund_points_on_order' => $deduct_currency_spent,
					'date' => $today_date,
				);
				$deduction_of_points['refund_points_on_order'][] = $currency_arr;
			}

			update_user_meta( $user_id, 'wps_wpr_points', $remaining_points );
			update_user_meta( $user_id, 'points_details', $deduction_of_points );
			// send sms.
			wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your order was cancelled, so %1$s order total points have been deducted. Your new points balance is %2$s', 'points-and-rewards-for-woocommerce' ), $deduct_currency_spent, $remaining_points ) );
			// send messages on whatsapp.
			wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your order was cancelled, so %1$s order total points have been deducted. Your new points balance is %2$s', 'points-and-rewards-for-woocommerce' ), $deduct_currency_spent, $remaining_points ) );
			// hpos.
			wps_wpr_hpos_update_meta_data( $order_id, '$order_id#wps_point_on_order_total', 'yes' );

			if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {

				$wps_wpr_email_subject     = isset( $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_subject'] ) ? $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_subject'] : '';
				$wps_wpr_email_discription = isset( $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_desciption'] ) ? $wps_wpr_notificatin_array['wps_wpr_deduct_assigned_point_desciption'] : '';
				$wps_wpr_email_discription = str_replace( '[DEDUCTEDPOINT]', $deduct_currency_spent, $wps_wpr_email_discription );
				$wps_wpr_email_discription = str_replace( '[TOTALPOINTS]', $remaining_points, $wps_wpr_email_discription );
				$user                      = get_user_by( 'email', $user_email );
				$user_name                 = $user->user_firstname;
				$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );

				/*check is mail notification is enable or not*/
				$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'deduct_assign_points_notification' );
				if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {
					$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
					$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
				}
			}
		}
	}

	/**
	 * This Function returns a round_down setting.
	 * Mwb_general_Setting function.
	 */
	public function wps_wpr_set_org_general_setting() {
		$general_settings   = get_option( 'wps_wpr_settings_gallery' );
		$round_down_setting = ! empty( $general_settings['wps_wpr_point_round_off'] ) ? $general_settings['wps_wpr_point_round_off'] : '';
		return $round_down_setting;
	}

	/**
	 * This function is used to send mail to the client regarding the updatoon of the points.
	 *
	 * @name wps_wpr_send_notification_mail_product
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int    $user_id  user id of the user.
	 * @param int    $points  points of the user.
	 * @param string $shortcode  shotcode of the plugins.
	 * @param string $wps_wpr_subject_content  content of the shortcode.
	 */
	public function wps_wpr_send_notification_mail_product( $user_id, $points, $shortcode, $wps_wpr_subject_content ) {
		$user                      = get_user_by( 'ID', $user_id );
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		/*check if not empty the notification array*/
		if ( ! empty( $wps_wpr_notificatin_array ) && is_array( $wps_wpr_notificatin_array ) ) {
			/*Get the Email Subject*/
			$wps_wpr_email_subject = self::wps_wpr_get_email_notification_description( $wps_wpr_subject_content['wps_wpr_subject'] );
			/*Get the Email Description*/
			$wps_wpr_email_discription = self::wps_wpr_get_email_notification_description( $wps_wpr_subject_content['wps_wpr_content'] );
			/*Replace the shortcode in the woocommerce*/
			if ( ! empty( $shortcode ) && is_array( $shortcode ) ) {
				foreach ( $shortcode as $key => $value ) {
					$wps_wpr_email_discription = str_replace( $key, $value, $wps_wpr_email_discription );
				}
			}
			$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'product_notification' );
			/*check is mail notification is enable or not*/
			if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {

				/*Send the email to user related to the signup*/
				$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
				$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
			}
		}
	}

	/**
	 * This function is used to edit comment template for points
	 *
	 * @name wps_wpr_woocommerce_signup_point
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_woocommerce_signup_point() {
		/*Get the color of the*/
		$wps_wpr_notification_color = $this->wps_wpr_get_other_settings( 'wps_wpr_notification_color' );
		$wps_wpr_notification_color = ( ! empty( $wps_wpr_notification_color ) ) ? $wps_wpr_notification_color : '#55b3a5';

		$wps_wpr_signup_value                  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup_value' );
		$enable_wps_signup                     = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup' );
		$wps_wpr_signup_referral_points_option = ! empty( $this->wps_wpr_get_general_settings( 'wps_wpr_signup_referral_points_option' ) ) ? $this->wps_wpr_get_general_settings( 'wps_wpr_signup_referral_points_option' ) : 'one';
		if ( $enable_wps_signup ) {
			if ( 'two' === $wps_wpr_signup_referral_points_option ) {

				?>
				<div class="woocommerce-message" style="background-color<?php echo esc_attr( $wps_wpr_notification_color ); ?>">
					<?php
					/* translators: %s: signup points value */
					printf( esc_html__( 'You will get %s points for a successful signup using a referral link.', 'points-and-rewards-for-woocommerce' ), esc_html( $wps_wpr_signup_value ) );
					?>
				</div>
				<?php
			} else {

				?>
				<div class="woocommerce-message" style="background-color<?php echo esc_attr( $wps_wpr_notification_color ); ?>">
					<?php
					/* translators: %s: signup points value */
					printf( esc_html__( 'You will get %s points for a successful signup.', 'points-and-rewards-for-woocommerce' ), esc_html( $wps_wpr_signup_value ) );
					?>
				</div>
				<?php
			}
		}
	}

	/**
	 * This function is used to add the html boxes for "Redemption on Cart sub-total"
	 *
	 * @name wps_wgm_woocommerce_cart_coupon
	 * @since 1.0.0
	 * @author <ticket@makewebbetter.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_woocommerce_cart_coupon() {

		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}

		// get shortcode setting values.
		$wps_wpr_other_settings                = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_other_settings                = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
		$wps_wpr_cart_page_apply_point_section = ! empty( $wps_wpr_other_settings['wps_wpr_cart_page_apply_point_section'] ) ? $wps_wpr_other_settings['wps_wpr_cart_page_apply_point_section'] : '';
		// check if shortcode is exist then return from here.
		if ( '1' == $wps_wpr_cart_page_apply_point_section && ! empty( get_the_content() ) ) {
			if ( true == strpos( get_the_content(), '[WPS_CART_PAGE_SECTION' ) ) {

				return;
			}
		}

		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}

		/*Get the value of the custom points*/
		$wps_wpr_custom_points_on_cart = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
		if ( 1 == $wps_wpr_custom_points_on_cart ) {

			$user_id            = get_current_user_ID();
			$get_points         = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			$get_min_redeem_req = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_value' );

			if ( empty( $get_points ) ) {
				$get_points = 0;
			}

			// deduct points if Points Discount is applied.
			$wps_wpr_check_points_discount_applied_amount = ! empty( get_option( 'wps_wpr_check_points_discount_applied_amount' ) ) ? get_option( 'wps_wpr_check_points_discount_applied_amount' ) : 0;
			$get_points                                   = $get_points - $wps_wpr_check_points_discount_applied_amount;

			// deduct points if discount applied via product edit page( purchase throught only points ).
			$applied__points = 0;
			if ( isset( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart ) {
					if ( isset( $cart['product_meta'] ) && isset( $cart['product_meta']['meta_data'] ) && isset( $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'] ) ) {
						$applied__points += (int) $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'];
					}
				}
			}
			$get_points = $get_points - $applied__points;

			if ( isset( $user_id ) && ! empty( $user_id ) ) {
				$wps_wpr_order_points = apply_filters( 'wps_wpr_enable_points_on_order_total', false );
				if ( $wps_wpr_order_points ) {

					do_action( 'wps_wpr_points_on_order_total', $get_points, $user_id, $get_min_redeem_req );
				} else {
					?>
						<?php
						if ( $get_min_redeem_req <= $get_points ) {
							?>
							<div class="wps_wpr_apply_custom_points">
								<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>"/>
								<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0"><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
								<p class="wps_wpr_restrict_user_message"><?php esc_html_e( 'Your available points:', 'points-and-rewards-for-woocommerce' ); ?>
								<?php echo esc_html( $get_points ); ?></p>
								<p class="wps_wpr_show_restrict_message"></p>
							</div>	
							<?php
						} else {
							$extra_req = abs( $get_min_redeem_req - $get_points );
							?>
							<div class="wps_wpr_apply_custom_points">
								<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>" readonly/>
								<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0" disabled><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
								<p><?php esc_html_e( 'You require :', 'points-and-rewards-for-woocommerce' ); ?>
								<?php echo esc_html( $extra_req ); ?></p>
								<p><?php esc_html_e( 'more to get redeem', 'points-and-rewards-for-woocommerce' ); ?></p>
							</div>
							<?php
						}
				}
			}
		}
	}

	/**
	 * This function is used to apply fee on cart total
	 *
	 * @name wps_wpr_apply_fee_on_cart_subtotal
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_apply_fee_on_cart_subtotal() {

		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		$response['result']  = false;
		$response['message'] = __( 'Can not redeem!', 'points-and-rewards-for-woocommerce' );
		if ( isset( $_POST ) ) {

			// Get data via ajax.
			$user_id         = ! empty( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;
			$wps_cart_points = ! empty( $_POST['wps_cart_points'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_cart_points'] ) ) : 0;
			$get_points      = get_user_meta( get_current_user_id(), 'wps_wpr_points', true );
			$get_points      = ! empty( $get_points ) && $get_points > 0 ? $get_points : 0;

			// Redemption Conversion rate calculate.
			$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
			$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
			$wps_wpr_cart_price_rate  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
			$wps_wpr_cart_price_rate  = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;
			$wps_cart_points          = ( $wps_cart_points * $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );

			// when points value is grater than price than convert points.
			if ( $wps_wpr_cart_price_rate > $wps_wpr_cart_points_rate ) {

				$get_points = ( $get_points * $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );
			} else {

				$get_points = $get_points;
			}

			// deduct points if Points Discount is applied.
			$wps_wpr_check_points_discount_applied_amount = ! empty( get_option( 'wps_wpr_check_points_discount_applied_amount' ) ) ? get_option( 'wps_wpr_check_points_discount_applied_amount' ) : 0;
			$get_points                                   = (int) $get_points - $wps_wpr_check_points_discount_applied_amount;

			// deduct points if discount applied via product edit page( purchase throught only points ).
			$applied__points     = 0;
			$product_sale__price = 0;
			$discount_value      = 0;
			if ( isset( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart ) {

					// purchase through points only data.
					if ( 'yes' == wps_wpr_hpos_get_meta_data( $cart['product_id'], 'wps_product_purchase_points_only', true ) ) {
						if ( isset( $cart['product_meta'] ) && isset( $cart['product_meta']['meta_data'] ) && isset( $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'] ) ) {

							$applied__points += (int) $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'] * $cart['quantity'];
						}
					}

					// get sale product price.
					$product = wc_get_product( $cart['product_id'] );
					if ( $product->is_on_sale() ) {

						$product_sale__price += (int) $product->get_sale_price() * $cart['quantity'];
					}
				}
			}

			// purchase through points only data.
			$get_points = (int) $get_points - $applied__points;

			// Restriction on sale Product data.
			$general_settings      = get_option( 'wps_wpr_settings_gallery' );
			$restrict_sale_on_cart = ! empty( $general_settings['wps_wpr_points_restrict_sale'] ) ? $general_settings['wps_wpr_points_restrict_sale'] : '';

			// check pro plugin is enable.
			if ( wps_wpr_is_par_pro_plugin_active() ) {
				if ( $product_sale__price > 0 ) {

					// check sale restrict features is enable.
					if ( 1 === $restrict_sale_on_cart ) {

						$wps_user_level            = get_user_meta( get_current_user_id(), 'membership_level', true );
						$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
						$wps_wpr_membership_roles  = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();
						if ( ! empty( $wps_user_level ) && array_key_exists( $wps_user_level, $wps_wpr_membership_roles ) ) {
							if ( is_array( $wps_wpr_membership_roles ) && ! empty( $wps_wpr_membership_roles ) ) {
								// get membership discount amount.
								foreach ( $wps_wpr_membership_roles as $wps_role => $values ) {
									if ( ! is_array( $values ) ) {
										break;
									}
									if ( $wps_role == $wps_user_level ) {

										$discount_value = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
										break;
									}
								}
							}
						}

						// calculate membership discount on sale product.
						$discouted_sale_price = ( $product_sale__price * $discount_value ) / 100;
						$product_sale__price  = $product_sale__price - $discouted_sale_price;

						$cart_price = 0;
						if ( isset( WC()->cart ) ) {

							// get cart subtotal and minus sale product price and minus points discount price.
							$cart__subtotal = ! empty( WC()->cart->get_subtotal() ) && WC()->cart->get_subtotal() > 0 ? WC()->cart->get_subtotal() : 0;
							$cart__subtotal = $cart__subtotal - $wps_wpr_check_points_discount_applied_amount;
							$cart_price     = $cart__subtotal - $product_sale__price;
						}

						// check points is equal/lower than product price after sale product price calculated.
						if ( $wps_cart_points <= $cart_price ) {

							$wps_cart_points = $wps_cart_points;
						} else {

							$wps_cart_points = $cart_price;
						}
					}
				}
			}

			// check points redeem restriction by category.
			$wps_cart_points = apply_filters( 'wps_wpr_restrict_redeem_points_by_category_wise', $wps_cart_points );
			// Applied points here.
			if ( $get_points > 0 && $wps_cart_points > 0 ) {
				if ( $get_points >= $wps_cart_points ) {

					WC()->session->set( 'wps_cart_points', $wps_cart_points );
					$response['result']  = true;
					$response['message'] = apply_filters( 'wps_wpr_modify_points_success_msg', esc_html__( 'Custom Point has been applied Successfully!', 'points-and-rewards-for-woocommerce' ) );
				} else {

					$response['result']  = false;
					$response['message'] = esc_html__( 'Please enter some valid points!', 'points-and-rewards-for-woocommerce' );
				}
			} else {

				$response['result']  = false;
				$response['message'] = apply_filters( 'wps_wpr_modify_points_error_msg', esc_html__( 'Invalid Points!', 'points-and-rewards-for-woocommerce' ) );
			}
		}
		wp_send_json( $response );
	}

	/**
	 * This function is used to apply custom points on Cart Total.
	 *
	 * @name wps_wpr_woocommerce_cart_custom_points
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param object $cart  array of the cart.
	 */
	public function wps_wpr_woocommerce_cart_custom_points( $cart ) {
		global $woocommerce;
		/*Get the current user id*/
		$my_cart_change_return = 0;
		if ( isset( $cart ) && ! empty( $cart ) ) {
			$my_cart_change_return = apply_filters( 'wps_cart_content_check_for_sale_item', $cart );
		}
		if ( '1' == $my_cart_change_return ) {

			return;
		} else {
				$user_id = get_current_user_ID();
				/*Check is custom points on cart is enable*/
				$wps_wpr_custom_points_on_cart     = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
				$wps_wpr_custom_points_on_checkout = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
			if ( isset( $user_id ) && ! empty( $user_id ) && ( 1 == $wps_wpr_custom_points_on_cart || 1 == $wps_wpr_custom_points_on_checkout ) ) {

				if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {

					$wps_fee_on_cart = WC()->session->get( 'wps_cart_points' );
					$cart_discount   = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
					// apply points on subtotal.
					$subtotal = $cart->subtotal;
					if ( $subtotal > $wps_fee_on_cart ) {

						$wps_fee_on_cart = $wps_fee_on_cart;
					} else {

						$wps_fee_on_cart = $subtotal;
						// save tax amount.
						if ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) || 'excl' === get_option( 'woocommerce_tax_display_shop' ) ) {
							$taxes = $cart->get_taxes();
							if ( ( ! empty( $taxes ) && is_array( $taxes ) ) && empty( WC()->session->get( 'wps_wpr_tax_before_coupon' ) ) ) {

								$total_taxes        = array_sum( $taxes );
								$shipping_taxes     = $cart->get_shipping_taxes();
								$shipping_tax_total = array_sum( $shipping_taxes );
								$total_taxes        = $total_taxes - $shipping_tax_total;
								WC()->session->set( 'wps_wpr_tax_before_coupon', $total_taxes );
							}
						}
					}
					// WOOCS - WooCommerce Currency Switcher Compatibility.
					$wps_fee_on_cart = apply_filters( 'wps_wpr_show_conversion_price', $wps_fee_on_cart );
					do_action( 'wps_change_amount_cart', $wps_fee_on_cart, $cart, $cart_discount );

					// Paypal Issue Change Start.
					if ( isset( $woocommerce->cart ) ) {
						if ( ! $woocommerce->cart->has_discount( $cart_discount ) ) {
							if ( $woocommerce->cart->applied_coupons ) {
								foreach ( $woocommerce->cart->applied_coupons as $code ) {
									if ( strtolower( $cart_discount ) === strtolower( $code ) ) {
										return;
									}
								}
							}
							$woocommerce->cart->applied_coupons[] = $cart_discount;
						}
					}
				}
				// Paypal Issue Change End.
			}
		}
	}

	// Paypal Issue Change start.

	/**
	 * This function is used to apply discount using coupon.
	 *
	 * @param string $response response.
	 * @param string $coupon_data coupon data.
	 * @return string
	 */
	public function wps_wpr_validate_virtual_coupon_for_points( $response, $coupon_data ) {
		if ( ! wps_wpr_is_par_pro_plugin_active() ) {
			if ( ! is_admin() ) {
				if ( false !== $coupon_data && 0 !== $coupon_data ) {

					/*Get the current user id*/
					$my_cart_change_return = 0;
					if ( ! empty( WC()->cart ) ) {
						$my_cart_change_return = apply_filters( 'wps_cart_content_check_for_sale_item', WC()->cart->get_cart() );
					}
					$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
					if ( '1' == $my_cart_change_return ) {
						return;
					} else {
						$user_id = get_current_user_ID();
						/*Check is custom points on cart is enable*/
						$wps_wpr_custom_points_on_cart     = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
						$wps_wpr_custom_points_on_checkout = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
						if ( isset( $user_id ) && ! empty( $user_id ) && ( 1 == $wps_wpr_custom_points_on_cart || 1 == $wps_wpr_custom_points_on_checkout ) ) {

							if ( isset( WC()->session ) && WC()->session->has_session() ) {
								if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {

									global $woocommerce;
									$wps_fee_on_cart = WC()->session->get( 'wps_cart_points' );
									// apply points on subtotal.
									$subtotal = $woocommerce->cart->subtotal;
									// WOOCS - WooCommerce Currency Switcher Compatibility.
									if ( ! class_exists( 'WOOCS' ) ) {
										if ( $subtotal > $wps_fee_on_cart ) {
											$wps_fee_on_cart = $wps_fee_on_cart;
										} else {

											$wps_fee_on_cart = $subtotal;
										}
									}
									// WOOCS - WooCommerce Currency Switcher Compatibility.
									$wps_fee_on_cart = apply_filters( 'wps_wpr_show_conversion_price', $wps_fee_on_cart );
									if ( strtolower( $coupon_data ) == strtolower( $cart_discount ) ) {
										$discount_type = 'fixed_cart';
										$coupon        = array(
											'id'                         => time() . wp_rand( 2, 9 ),
											'amount'                     => $wps_fee_on_cart,
											'individual_use'             => false,
											'product_ids'                => array(),
											'exclude_product_ids'        => array(),
											'usage_limit'                => '',
											'usage_limit_per_user'       => '',
											'limit_usage_to_x_items'     => '',
											'usage_count'                => '',
											'expiry_date'                => '',
											'apply_before_tax'           => 'yes',
											'free_shipping'              => false,
											'product_categories'         => array(),
											'exclude_product_categories' => array(),
											'exclude_sale_items'         => false,
											'minimum_amount'             => '',
											'maximum_amount'             => '',
											'customer_email'             => '',
										);
										$coupon['discount_type'] = $discount_type;
										return $coupon;
									}
								}
							}
						}
					}
				}
			}
		}
		return $response;
	}
	// Paypal Issue Change End.

	/**
	 * This function is used to add notices over cart page.
	 *
	 * @name wps_wpr_woocommerce_before_cart_contents
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_woocommerce_before_cart_contents() {
		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}

		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}

		// Check is custom points on cart is enable.
		$wps_wpr_custom_points_on_checkout  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
		$wps_wpr_custom_points_on_cart      = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
		$wps_wpr_show_redeem_notice         = $this->wps_wpr_get_general_settings_num( 'wps_wpr_show_redeem_notice' );
		$wps_wpr_points_redemption_messages = $this->wps_wpr_get_general_settings( 'wps_wpr_points_redemption_messages' );

		// Get the Notification.
		$wps_wpr_notification_color = $this->wps_wpr_get_other_settings( 'wps_wpr_notification_color' );
		$wps_wpr_notification_color = ( ! empty( $wps_wpr_notification_color ) ) ? $wps_wpr_notification_color : '#55b3a5';

		// Get the cart point rate.
		$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
		$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;

		// Get the cart price rate.
		$wps_wpr_cart_price_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
		$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;
		$user_id                 = get_current_user_ID();

		// show message on cart page for redemption settings.
		if ( ( 1 == $wps_wpr_custom_points_on_cart || 1 === $wps_wpr_custom_points_on_checkout ) && ! empty( $user_id ) && $wps_wpr_show_redeem_notice ) {

			?>
			<div class="woocommerce-message wps_wpr_cart_redemption__notice" id="wps_wpr_order_notice" style="background-color: <?php echo esc_html( $wps_wpr_notification_color ); ?>;">
				<?php
				// WOOCS - WooCommerce Currency Switcher Compatibility.
				$wps_wpr_points_redemption_messages = str_replace( '[POINTS]', $wps_wpr_cart_points_rate, $wps_wpr_points_redemption_messages );
				$wps_wpr_points_redemption_messages = str_replace( '[CURRENCY]', wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_wpr_cart_price_rate ) ), $wps_wpr_points_redemption_messages );
				echo wp_kses_post( $wps_wpr_points_redemption_messages );
				?>
			</div>
			<div class="wps_rwpr_settings_display_none_notice" id="wps_wpr_cart_points_notice"></div>
			<div class="wps_rwpr_settings_display_none_notice" id="wps_wpr_cart_points_success"></div>
			<?php
		}

		// show message on cart page for per currency earn points.
		$wps_wpr_per_currency_discount_notice = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_per_currency_discount_notice' );
		$wps_wpr_per_curr_earning_messages    = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_per_curr_earning_messages' );
		if ( $this->is_order_conversion_enabled() && $wps_wpr_per_currency_discount_notice ) {

			?>
			<div class="woocommerce-message" id="wps_wpr_order_notice" style="background-color: <?php echo esc_html( $wps_wpr_notification_color ); ?>">
				<?php
				// WOOCS - WooCommerce Currency Switcher Compatibility.
				$wps_wpr_per_curr_earning_messages = str_replace( '[POINTS]', $wps_wpr_cart_points_rate, $wps_wpr_per_curr_earning_messages );
				$wps_wpr_per_curr_earning_messages = str_replace( '[CURRENCY]', wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_wpr_cart_price_rate ) ), $wps_wpr_per_curr_earning_messages );
				echo wp_kses_post( $wps_wpr_per_curr_earning_messages );
				?>
			</div>
			<?php
		}

		// ==== Order Rewards Points message show here ====

		// check if user is already awarded than return from here.
		$wps_wpr_rewards_points_awarded_check = get_user_meta( $user_id, 'wps_wpr_rewards_points_awarded_check', true );
		// add a filter to reset variable.
		$wps_wpr_rewards_points_awarded_check = apply_filters( 'wps_wpr_show_rewards_next_points_message', $wps_wpr_rewards_points_awarded_check );
		if ( empty( $wps_wpr_rewards_points_awarded_check ) ) {

			// get rewards setting here.
			$wps_wpr_settings_gallery                    = get_option( 'wps_wpr_settings_gallery', true );
			$wps_wpr_enable_order_rewards_settings       = ! empty( $wps_wpr_settings_gallery['wps_wpr_enable_order_rewards_settings'] ) ? $wps_wpr_settings_gallery['wps_wpr_enable_order_rewards_settings'] : 0;
			$wps_wpr_number_of_reward_order              = ! empty( $wps_wpr_settings_gallery['wps_wpr_number_of_reward_order'] ) ? $wps_wpr_settings_gallery['wps_wpr_number_of_reward_order'] : 0;
			$wps_wpr_number_of_rewards_points            = ! empty( $wps_wpr_settings_gallery['wps_wpr_number_of_rewards_points'] ) ? $wps_wpr_settings_gallery['wps_wpr_number_of_rewards_points'] : 0;
			$wps_wpr_enable_to_show_order_reward_message = ! empty( $wps_wpr_settings_gallery['wps_wpr_enable_to_show_order_reward_message'] ) ? $wps_wpr_settings_gallery['wps_wpr_enable_to_show_order_reward_message'] : 0;
			$wps_wpr_number_order_rewards_messages       = ! empty( $wps_wpr_settings_gallery['wps_wpr_number_order_rewards_messages'] ) ? $wps_wpr_settings_gallery['wps_wpr_number_order_rewards_messages'] : 'Place [ORDER] order and earn [POINTS] Points in return';
			$order_count                                 = 0;

			// check rewards setting is enable or not.
			if ( 1 === $wps_wpr_enable_order_rewards_settings ) {
				if ( 1 === $wps_wpr_enable_to_show_order_reward_message ) {

					// Get all user completed order.
					$args = array(
						'post_type'   => array( 'shop_order' ),
						'post_status' => array( 'wc-completed' ),
						'numberposts' => -1,
						'customer_id' => $user_id,
					);

					// add filter to modify query.
					$args                = apply_filters( 'wps_wpr_modify_get_user_order_query', $args, $user_id );
					$wps_customer_orders = wc_get_orders( $args );

					// Get user order count.
					if ( ! empty( $wps_customer_orders ) && ! is_null( $wps_customer_orders ) ) {
						$order_count = count( $wps_customer_orders );
					}

					// Replace order and points shortcode with order count and order rewards points.
					$wps_wpr_number_order_rewards_messages = str_replace( '[ORDER]', ( $wps_wpr_number_of_reward_order - $order_count ), $wps_wpr_number_order_rewards_messages );
					$wps_wpr_number_order_rewards_messages = str_replace( '[POINTS]', $wps_wpr_number_of_rewards_points, $wps_wpr_number_order_rewards_messages );
					// add a filter to change notice.
					$wps_wpr_number_order_rewards_messages = apply_filters( 'wps_wpr_modify_order_rewards_messages', $wps_wpr_number_order_rewards_messages, $order_count );
					?>
					<!-- Show awards discount notice -->
					<div class="woocommerce-message" id="wps_wpr_order_notice" style="background-color: <?php echo esc_attr( $wps_wpr_notification_color ); ?>">
						<p style="background-color: <?php echo esc_attr( $wps_wpr_notification_color ); ?>"><?php echo wp_kses_post( $wps_wpr_number_order_rewards_messages ); ?></p>
					</div>
					<?php
				}
			}
		}
		do_action( 'wps_wpr_show_total_earning_points', $wps_wpr_notification_color );
	}

	/**
	 * This function is used to check the order conversion feature is enabled or not
	 *
	 * @name is_order_conversion_enabled
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function is_order_conversion_enabled() {
		$enable                     = false;
		$is_order_conversion_enable = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_enable' );
		if ( $is_order_conversion_enable ) {
			$enable = true;
		}
		return $enable;
	}

	/**
	 * This function is used to return you the conversion rate of Order Total
	 *
	 * @name order_conversion_rate
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function order_conversion_rate() {

		$order_conversion_rate_value  = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_price' );
		$order_conversion_rate_points = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_points' );
		$order_conversion_rate        = array(
			'Value'  => $order_conversion_rate_value,
			'Points' => $order_conversion_rate_points,
			'curr'   => get_woocommerce_currency_symbol(),
		);
		return $order_conversion_rate;
	}

	/**
	 * This function is used to add Remove button along with Cart Discount Fee
	 *
	 * @name wps_wpr_woocommerce_cart_totals_fee_html
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param string $cart_totals_fee_html html of the fees.
	 * @param object $fee array of the fees.
	 */
	public function wps_wpr_woocommerce_cart_totals_fee_html( $cart_totals_fee_html, $fee ) {
		if ( isset( $fee ) && ! empty( $fee ) ) {
			$fee_name      = $fee->name;
			$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
			if ( isset( $fee_name ) && strtolower( $cart_discount ) == strtolower( $fee_name ) ) {
				$cart_totals_fee_html = $cart_totals_fee_html . '<a href="javascript:void(0);" id="wps_wpr_remove_cart_point">' . __( '[Remove]', 'points-and-rewards-for-woocommerce' ) . '</a>';
			}
		}
		return $cart_totals_fee_html;
	}

	/**
	 * This function is used to Remove Cart Discount Fee.
	 *
	 * @name wps_wpr_remove_cart_point
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_wpr_remove_cart_point() {
		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		$response['result']  = false;
		$response['message'] = __( 'Failed to Remove Cart Discount', 'points-and-rewards-for-woocommerce' );
		$cart_discount       = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		$coupon_code         = isset( $_POST['coupon_code'] ) && ! empty( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
		if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {
			WC()->session->__unset( 'wps_cart_points' );
			WC()->session->__unset( 'wps_wpr_tax_before_coupon' );
			$response['result'] = true;
			$response['message'] = __( 'Successfully Removed Cart Discount', 'points-and-rewards-for-woocommerce' );
		}
		if ( isset( WC()->cart ) ) {
			if ( null !== WC()->cart->get_applied_coupons() && ! empty( WC()->cart->get_applied_coupons() ) ) {
				foreach ( WC()->cart->get_applied_coupons() as $code ) {
					$coupon = new WC_Coupon( $code );
					if ( strtolower( $code ) === strtolower( $cart_discount ) ) {
						WC()->cart->remove_coupon( $code );
					}
				}
			}
		}
		wp_send_json( $response );
	}

	/**
	 * This function is used to allow customer can apply points during checkout.
	 *
	 * @name wps_overwrite_form_temp
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param string $path path of the templates.
	 * @param string $template_name name of the file.
	 */
	public function wps_overwrite_form_temp( $path, $template_name ) {
		/*Check is apply points on the cart is enable or not*/
		$wps_wpr_custom_points_on_checkout = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );

		if ( 1 == $wps_wpr_custom_points_on_checkout ) {
			if ( 'checkout/form-coupon.php' == $template_name ) {

				return WPS_RWPR_DIR_PATH . 'public/woocommerce/checkout/form-coupon.php';
			}
		}
		return $path;
	}

	/**
	 * This function will update the user points as they purchased products through points.
	 *
	 * @param  object $order_data order_data.
	 * @return void
	 */
	public function wps_wpr_woocommerce_checkout_update_order_meta( $order_data ) {

		// This function is triggered by two hooks, so we need to verify whether the parameter is an ID or an object.
		if ( ! is_object( $order_data ) ) {
			$order = wc_get_order( $order_data );
		} else {
			$order = $order_data;
		}

		$user_id    = get_current_user_id();
		$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		// cart block change.
		$order_id = $order->get_id();
		if ( isset( $order ) && ! empty( $order ) ) {

			// Paypal Issue Change Start.
			$order_data = $order->get_data();
			if ( ! empty( $order_data['coupon_lines'] ) ) {

				foreach ( $order_data['coupon_lines'] as $coupon ) {
					$coupon_data = $coupon->get_data();
					if ( ! empty( $coupon_data ) ) {

						$coupon_name   = $coupon_data['code'];
						$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
						if ( strtolower( $cart_discount ) == strtolower( $coupon_name ) ) {

							// get applied coupon(points) value and total tax amount and calculate.
							$coupon        = new WC_Coupon( $coupon_name );
							$coupon_amount = $coupon->get_amount();
							// hpos.
							wps_wpr_hpos_update_meta_data( $order_id, 'wps_cart_discount#$fee_id', $coupon_amount );
							if ( ! empty( WC()->session->get( 'wps_wpr_tax_before_coupon' ) ) ) {

								$coupon_amount = $coupon_amount - WC()->session->get( 'wps_wpr_tax_before_coupon' );
							}

							// Redemption Conversion rate calculate.
							$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
							$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
							$wps_wpr_cart_price_rate  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
							$wps_wpr_cart_price_rate  = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;
							$coupon_amount            = $coupon_amount / ( $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );
							// WOOCS - WooCommerce Currency Switcher Compatibility.
							$coupon_amount = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $coupon_amount );
							$fee_to_point    = ceil( $coupon_amount );
							$remaining_point = $get_points - $fee_to_point;
							if ( $remaining_point < 0 ) {
								$remaining_point = 0;
							}
							/*update the users points in the*/
							update_user_meta( $user_id, 'wps_wpr_points', $remaining_point );
							$data = array();
							/*update points of the customer*/
							$this->wps_wpr_update_points_details( $user_id, 'cart_subtotal_point', $fee_to_point, $data );
							/*Send mail to the customer*/
							$this->wps_wpr_send_points_deducted_mail( $user_id, 'wps_cart_discount', $fee_to_point );
							// send sms.
							wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( '%1$s applied points used at Cart/Checkout have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $fee_to_point, $remaining_point ) );
							// send messages on whatsapp.
							wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( '%1$s applied points used at Cart/Checkout have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $fee_to_point, $remaining_point ) );
							/*Unset the session*/
							if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {
								// hpos.
								wps_wpr_hpos_update_meta_data( $order_id, 'wps_cart_discount#points', $coupon_amount );
								WC()->session->__unset( 'wps_cart_points' );
								WC()->session->__unset( 'wps_wpr_tax_before_coupon' );
							}

							// updating redeemed points.
							$wps_wpr_redeemed_points  = get_user_meta( $user_id, 'wps_wpr_redeemed_points', true );
							$wps_wpr_redeemed_points  = ! empty( $wps_wpr_redeemed_points ) ? (int) $wps_wpr_redeemed_points : 0;
							$wps_wpr_redeemed_points += $fee_to_point;
							update_user_meta( $user_id, 'wps_wpr_redeemed_points', $wps_wpr_redeemed_points );
						}
					}
				}
			}
			// Paypal Issue Change End.
		}
	}

	/**
	 * This function will send deducted mail to the user
	 *
	 * @name wps_wpr_woocommerce_checkout_update_order_meta.
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int    $user_id user id of the user.
	 * @param string $type type of the mail.
	 * @param int    $fee_to_point points that will be applied.
	 */
	public function wps_wpr_send_points_deducted_mail( $user_id, $type, $fee_to_point ) {
		$user                      = get_user_by( 'ID', $user_id );
		$user_name                 = $user->user_login;
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		/*check if not empty the notification array*/
		if ( ! empty( $wps_wpr_notificatin_array ) && is_array( $wps_wpr_notificatin_array ) ) {
			$wps_wpr_total_points = get_user_meta( $user_id, 'wps_wpr_points', true );
			/*Get the Email Subject*/
			$wps_wpr_email_subject = self::wps_wpr_get_email_notification_description( 'wps_wpr_point_on_cart_subject' );
			/*Get the Email Description*/
			$wps_wpr_email_discription = self::wps_wpr_get_email_notification_description( 'wps_wpr_point_on_cart_desc' );
			$wps_wpr_email_discription = str_replace( '[DEDUCTCARTPOINT]', $fee_to_point, $wps_wpr_email_discription );
			$wps_wpr_email_discription = str_replace( '[TOTALPOINTS]', $wps_wpr_total_points, $wps_wpr_email_discription );
			$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );
			/*check is mail notification is enable or not*/
			$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'wps_cart_discount_notification' );
			if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {

				/*Send the email to user related to the signup*/
				$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
				$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
			}
		}
	}

	/**
	 * This function is used to save points in add to cart session.
	 *
	 * @name wps_wpr_woocommerce_add_cart_item_data
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $the_cart_data array of the cart data.
	 * @param int   $product_id id of the product.
	 * @param int   $variation_id id of the variation.
	 * @param int   $quantity quantity of the cart.
	 */
	public function wps_wpr_woocommerce_add_cart_item_data( $the_cart_data, $product_id, $variation_id, $quantity ) {
		// restrict user from points table.
		if ( wps_wpr_restrict_user_fun() ) {

			return $the_cart_data;
		}

		// verifying nonce.
		if ( ! wp_verify_nonce( ! empty( $_POST['wps_wpr_verify_cart_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_verify_cart_nonce'] ) ) : '', 'wps-cart-nonce' ) ) {
			return $the_cart_data;
		}
		/*Get the quantitiy of the product*/
		if ( ! empty( $_REQUEST['quantity'] ) && isset( $_REQUEST['quantity'] ) ) {
			$wps_get_quantity = sanitize_text_field( wp_unslash( $_REQUEST['quantity'] ) );
		}
		if ( isset( $wps_get_quantity ) && $wps_get_quantity && null != $wps_get_quantity ) {
			$quantity = (int) $wps_get_quantity;
		} else {
			$quantity = 1;
		}

		$check_enable = wps_wpr_hpos_get_meta_data( $product_id, 'wps_product_points_enable', 'no' );
		if ( 'yes' == $check_enable ) {
			/*Check is exists the variation id*/
			if ( isset( $variation_id ) && ! empty( $variation_id ) && $variation_id > 0 ) {
				$get_product_points          = wps_wpr_hpos_get_meta_data( $variation_id, 'wps_wpr_variable_points', 1 );
				$item_meta['wps_wpm_points'] = (int) $get_product_points * (int) $quantity;
			} else {
				$get_product_points          = wps_wpr_hpos_get_meta_data( $product_id, 'wps_points_product_value', 1 );
				$item_meta['wps_wpm_points'] = (int) $get_product_points * (int) $quantity;
			}
			$the_cart_data ['product_meta'] = array( 'meta_data' => $item_meta );
		}
		return $the_cart_data;
	}

	/**
	 * This function is used to show item poits in time of order .
	 *
	 * @name wps_wpr_woocommerce_get_item_data
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $item_meta array of the meta item.
	 * @param array $existing_item_meta array of the existing meta.
	 */
	public function wps_wpr_woocommerce_get_item_data( $item_meta, $existing_item_meta ) {
		/*Check is not empty product meta*/
		if ( isset( $existing_item_meta ['product_meta']['meta_data'] ) ) {
			if ( $existing_item_meta ['product_meta']['meta_data'] ) {

				foreach ( $existing_item_meta['product_meta'] ['meta_data'] as $key => $val ) {
					if ( 'wps_wpm_points' == $key && ! empty( $val ) ) {

						$item_meta [] = array(
							'name' => __( 'Points', 'points-and-rewards-for-woocommerce' ),
							'value' => stripslashes( $val ),
						);
					}
				}
				/*filter that can be used to add more product meta*/
				$item_meta = apply_filters( 'wps_wpm_product_item_meta', $item_meta, $key, $val );
			}
		}
		return $item_meta;
	}

	/**
	 * This function is used to show item points in product discription page.
	 *
	 * @name wps_display_product_points
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_display_product_points() {
		// restrict user from points table.
		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}

		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}
		global $post;
		/*Get the color of the*/
		$wps_wpr_notification_color = $this->wps_wpr_get_other_settings( 'wps_wpr_notification_color' );
		$wps_wpr_notification_color = ( ! empty( $wps_wpr_notification_color ) ) ? $wps_wpr_notification_color : '#55b3a5';
		/*Get the product*/
		$product = wc_get_product( $post->ID );
		/*Get the product text*/
		$wps_wpr_assign_pro_text = $this->wps_wpr_get_general_settings( 'wps_wpr_assign_pro_text' );
		$product_is_variable     = $this->wps_wpr_check_whether_product_is_variable( $product );
		/*Check is global per product points is enable or not*/
		$check_enable = wps_wpr_hpos_get_meta_data( $post->ID, 'wps_product_points_enable', 'no' );
		if ( 'yes' == $check_enable ) {
			if ( ! $product_is_variable ) {
				$get_product_points = wps_wpr_hpos_get_meta_data( $post->ID, 'wps_points_product_value', 1 );
				echo '<span class=wps_wpr_product_point style=background-color:' . esc_html( $wps_wpr_notification_color ) . '>' . esc_html( $wps_wpr_assign_pro_text ) . ' : ' . esc_html( $get_product_points );
				esc_html_e( 'Points', 'points-and-rewards-for-woocommerce' );
				echo '</span>';
			} elseif ( $product_is_variable ) {
				$get_product_points = '<span class=wps_wpr_variable_points></span>';
				echo '<span class=wps_wpr_product_point style="display:none;background-color:' . esc_html( $wps_wpr_notification_color ) . '">' . esc_html( $wps_wpr_assign_pro_text ) . ' : ' . wp_kses_post( $get_product_points );
				esc_html_e( ' Points', 'points-and-rewards-for-woocommerce' );
				echo '</span>';
			}
		}
	}

	/**
	 * The function is used for checking the product is variable or not?
	 *
	 * @name wps_wpr_check_whether_product_is_variable
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param object $product arry of the whole product.
	 */
	public function wps_wpr_check_whether_product_is_variable( $product ) {
		if ( isset( $product ) && ! empty( $product ) ) {
			if ( $product->is_type( 'variable' ) && $product->has_child() ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * The function is for let the meta keys translatable
	 *
	 * @name wps_wpr_woocommerce_order_item_display_meta_key
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param string $display_key display key in the cart.
	 */
	public function wps_wpr_woocommerce_order_item_display_meta_key( $display_key ) {
		if ( 'Points' == $display_key ) {
			$display_key = esc_html__( 'Points', 'points-and-rewards-for-woocommerce' );
		}
		return $display_key;
	}

	/**
	 * This function is used to save item points in time of order according to woocommerce 3.0.
	 *
	 * @name wps_wpr_woocommerce_add_order_item_meta_version_3
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param object $item  array of the items.
	 * @param string $cart_key  key of the cart.
	 * @param array  $values  array of the cart meta data.
	 * @param array  $order  array of the order.
	 */
	public function wps_wpr_woocommerce_add_order_item_meta_version_3( $item, $cart_key, $values, $order ) {
		/*Check is not empty product meta*/
		if ( isset( $values['product_meta'] ) ) {

			foreach ( $values['product_meta'] ['meta_data'] as $key => $val ) {
				$order_val = stripslashes( $val );
				if ( $val ) {
					if ( 'wps_wpm_points' == $key ) {

						$item->add_meta_data( 'Points', $order_val );
					}
				}
			}
		}
	}

	/**
	 * This function will add discounted price for selected products in any     Membership Level.
	 *
	 * @name wps_wpr_user_level_discount_on_price
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int    $price price of the product.
	 * @param object $product_data product data of the product.
	 */
	public function wps_wpr_user_level_discount_on_price( $price, $product_data ) {
		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return $price;
		}
		$today_date                = date_i18n( 'Y-m-d' );
		$user_id                   = get_current_user_ID();
		$new_price                 = '';
		$product_id                = $product_data->get_id();
		$_product                  = wc_get_product( $product_id );
		$product_is_variable       = $this->wps_wpr_check_whether_product_is_variable( $_product );
		$reg_price                 = $_product->get_price();
		$reg_price                 = ! empty( $reg_price ) ? $reg_price : 0;
		$prod_type                 = $_product->get_type();
		$user_level                = get_user_meta( $user_id, 'membership_level', true );
		$wps_wpr_mem_expr          = get_user_meta( $user_id, 'membership_expiration', true );
		$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
		$wps_wpr_membership_roles  = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();
		if ( isset( $user_level ) && ! empty( $user_level ) ) {
			/*check isset the membership is not expried*/
			if ( isset( $wps_wpr_mem_expr ) && ! empty( $wps_wpr_mem_expr ) && $today_date <= $wps_wpr_mem_expr ) {
				if ( is_array( $wps_wpr_membership_roles ) && ! empty( $wps_wpr_membership_roles ) ) {

					foreach ( $wps_wpr_membership_roles as $roles => $values ) {
						if ( $user_level == $roles ) {

							if ( ! is_array( $values ) ) {
								return;
							}

							if ( is_array( $values['Product'] ) && ! empty( $values['Product'] ) ) {
								if ( in_array( $product_id, $values['Product'] ) && ! $product_is_variable && ! $this->check_exclude_sale_products( $product_data ) ) {

									$discounts = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
									$new_price = $reg_price - ( $reg_price * $discounts ) / 100;
									$price     = '<del>' . wc_price( $reg_price ) . $product_data->get_price_suffix() . '</del><ins>' . wc_price( $new_price ) . $product_data->get_price_suffix() . '</ins>';// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
							} elseif ( ! $this->check_exclude_sale_products( $product_data ) ) {
								$terms = get_the_terms( $product_id, 'product_cat' );
								if ( is_array( $terms ) && ! empty( $terms ) && ! $product_is_variable ) {
									foreach ( $terms as $term ) {

										$cat_id            = $term->term_id;
										$parent_cat        = $term->parent;
										$product_saved_cat = ! empty( $values['Prod_Categ'] ) && is_array( $values['Prod_Categ'] ) ? $values['Prod_Categ'] : array();
										if ( in_array( $cat_id, $product_saved_cat ) || in_array( $parent_cat, $product_saved_cat ) ) {
											if ( ! empty( $reg_price ) ) {

												$discounts = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
												$new_price = $reg_price - ( $reg_price * $discounts ) / 100;
												$price     = '<del>' . wc_price( $reg_price ) . $product_data->get_price_suffix() . '</del><ins>' . wc_price( $new_price ) . $product_data->get_price_suffix() . '</ins>';// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $price;
	}

	/**
	 * This function is used to check whether the exclude product is enable or not for Membership Discount { if enable then sale product will not be having the membership discount anymore as they are already having some discounts }
	 *
	 * @name check_exclude_sale_products
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param object $products array of the products all details.
	 */
	public function check_exclude_sale_products( $products ) {
		$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
		$exclude_sale_product      = isset( $membership_settings_array['exclude_sale_product'] ) ? intval( $membership_settings_array['exclude_sale_product'] ) : 0;
		$exclude                   = false;
		if ( $exclude_sale_product && $products->is_on_sale() ) {
			$exclude = true;
		} else {
			$exclude = false;
		}
		return $exclude;
	}

	/**
	 * This function will add discounted price in cart page.
	 *
	 * @name wps_wpr_woocommerce_before_calculate_totals
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param object $cart array of the cart.
	 */
	public function wps_wpr_woocommerce_before_calculate_totals( $cart ) {
		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}
		$woo_ver = WC()->version;
		/*Get the current user id*/
		$user_id = get_current_user_ID();
		$new_price = '';
		$today_date = date_i18n( 'Y-m-d' );
		/*Get the current level of the user*/
		$user_level = get_user_meta( $user_id, 'membership_level', true );
		/*Expiration period of the membership*/
		$wps_wpr_mem_expr = get_user_meta( $user_id, 'membership_expiration', true );
		/*Get the user id of the user*/
		$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		$membership_settings_array = get_option( 'wps_wpr_membership_settings', true );
		/*Get the membership level*/
		$wps_wpr_membership_roles = isset( $membership_settings_array['membership_roles'] ) && ! empty( $membership_settings_array['membership_roles'] ) ? $membership_settings_array['membership_roles'] : array();
		/*Get the current user*/
		$user    = wp_get_current_user();
		$user_id = $user->ID;

		foreach ( $cart->cart_contents as $key => $value ) {
			$product_id          = $value['product_id'];
			$_product            = wc_get_product( $product_id );
			$product_is_variable = $this->wps_wpr_check_whether_product_is_variable( $_product );
			$reg_price           = $_product->get_price();
			$reg_price           = ! empty( $reg_price ) ? $reg_price : 0;

			if ( isset( $value['variation_id'] ) && ! empty( $value['variation_id'] ) ) {
				$variation_id     = $value['variation_id'];
				$variable_product = wc_get_product( $variation_id );
				$variable_price   = $variable_product->get_price();
				$variable_price   = ! empty( $variable_price ) ? $variable_price : 0;
			}

			if ( isset( $wps_wpr_mem_expr ) && ! empty( $wps_wpr_mem_expr ) && $today_date <= $wps_wpr_mem_expr ) {
				if ( isset( $user_level ) && ! empty( $user_level ) ) {

					foreach ( $wps_wpr_membership_roles as $roles => $values ) {
						if ( $user_level == $roles ) {
							if ( ! is_array( $values ) ) {
								return;
							}

							if ( is_array( $values['Product'] ) && ! empty( $values['Product'] ) ) {
								if ( in_array( $product_id, $values['Product'] ) && ! $this->check_exclude_sale_products( $_product ) ) {
									if ( ! $product_is_variable ) {

										$discounts = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
										$new_price = $reg_price - ( $reg_price * $discounts ) / 100;
										// WOOCS - WooCommerce Currency Switcher Compatibility.
										$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
										if ( version_compare( $woo_ver, '3.0.6', '<' ) ) {
											$value['data']->price = $new_price;
										} else {
											$value['data']->set_price( $new_price );
										}
									} elseif ( $product_is_variable ) {

										$discounts = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
										$new_price = $variable_price - ( $variable_price * $discounts ) / 100;
										// WOOCS - WooCommerce Currency Switcher Compatibility.
										$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
										if ( version_compare( $woo_ver, '3.0.6', '<' ) ) {

											$value['data']->price = $new_price;
										} else {
											$value['data']->set_price( $new_price );
										}
									}
								}
							} elseif ( ! $this->check_exclude_sale_products( $_product ) ) {

								$terms = get_the_terms( $product_id, 'product_cat' );
								if ( is_array( $terms ) && ! empty( $terms ) ) {
									foreach ( $terms as $term ) {

										$cat_id            = $term->term_id;
										$parent_cat        = $term->parent;
										$product_saved_cat = ! empty( $values['Prod_Categ'] ) && is_array( $values['Prod_Categ'] ) ? $values['Prod_Categ'] : array();
										if ( in_array( $cat_id, $product_saved_cat ) || in_array( $parent_cat, $product_saved_cat ) ) {
											if ( ! $product_is_variable ) {

												$discounts = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
												$new_price = $reg_price - ( $reg_price * $discounts ) / 100;
												// WOOCS - WooCommerce Currency Switcher Compatibility.
												$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
												if ( version_compare( $woo_ver, '3.0.6', '<' ) ) {
													$value['data']->price = $new_price;
												} else {
													$value['data']->set_price( $new_price );
												}
											} elseif ( $product_is_variable ) {

												$discounts = ! empty( $values['Discount'] ) ? $values['Discount'] : 0;
												$new_price = $variable_price - ( $variable_price * $discounts ) / 100;
												// WOOCS - WooCommerce Currency Switcher Compatibility.
												$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
												if ( version_compare( $woo_ver, '3.0.6', '<' ) ) {

													$value['data']->price = $new_price;
												} else {
													$value['data']->set_price( $new_price );
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * This function is used to update cart points.
	 *
	 * @name wps_update_cart_points
	 * @since 1.0.0
	 * @return array
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array $cart_updated array of the update cart.
	 */
	public function wps_update_cart_points( $cart_updated ) {
		if ( $cart_updated ) {
			$cart     = WC()->session->get( 'cart' );
			$user_id  = get_current_user_ID();
			$contents = WC()->cart->get_cart();
			if ( is_array( $contents ) && ! empty( $contents ) ) {
				foreach ( $contents as $key => $value ) {

					if ( isset( WC()->cart->cart_contents[ $key ]['product_meta'] ) ) {
						if ( isset( WC()->cart->cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] ) ) {
							$product = wc_get_product( $cart[ $key ]['product_id'] );
							if ( isset( $product ) && ! empty( $product ) ) {

								if ( $this->wps_wpr_check_whether_product_is_variable( $product ) ) {
									if ( isset( $cart[ $key ]['variation_id'] ) && ! empty( $cart[ $key ]['variation_id'] ) ) {

										$get_product_points = wps_wpr_hpos_get_meta_data( $cart[ $key ]['variation_id'], 'wps_wpr_variable_points', 1 );
									}
								} elseif ( isset( $cart[ $key ]['product_id'] ) && ! empty( $cart[ $key ]['product_id'] ) ) {
										$get_product_points = wps_wpr_hpos_get_meta_data( $cart[ $key ]['product_id'], 'wps_points_product_value', 1 );
								}
							}
							WC()->cart->cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] = (int) $get_product_points * (int) $value['quantity'];
						}
					}
				}
			}
		}
		return $cart_updated;
	}

	/**
	 * This function is used to handle the tax calculation when Fee is applying on Cart
	 *
	 * @name wps_wpr_fee_tax_calculation
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param array  $fee_taxes   taxes array.
	 * @param object $fee   object array of the fee.
	 * @param array  $object  object of the add fee.
	 */
	public function wps_wpr_fee_tax_calculation( $fee_taxes, $fee, $object ) {
		$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		if ( strtolower( $cart_discount ) == strtolower( $fee->object->name ) ) {
			foreach ( $fee_taxes as $key => $value ) {
				$fee_taxes[ $key ] = 0;
			}
		}
		$fee_taxes = apply_filters( 'wps_wpr_fee_tax_calculation_points', $fee_taxes, $fee, $object );
		return $fee_taxes;
	}

	/**
	 * This function is used for adding the template.
	 *
	 * @name wps_wpr_add_coupon_form
	 * @since 1.0.1
	 * @param array $checkout  Array of the checkout.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://makewebbetter.com
	 */
	public function wps_wpr_add_coupon_form( $checkout ) {
		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}
		$wps_wpr_custom_points_on_checkout = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
		$wps_wpr_custom_points_on_cart     = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );

		if ( 1 == $wps_wpr_custom_points_on_checkout && 1 == $wps_wpr_custom_points_on_cart ) {
			if ( 'Avada' == wp_get_theme()->Name ) {
				?>
				<div class="wps_wpr_avada_wrap checkout_coupon">
					<h2 class="promo-code-heading fusion-alignleft"><?php esc_html_e( 'Have A Points?', 'points-and-rewards-for-woocommerce' ); ?></h2>
					<?php $this->wps_wpr_display_apply_points_checkout(); ?>
				</div>
				<?php
			}
		}
	}

	/**
	 * This function is used for display the apply points Setting.
	 *
	 * @since 1.0.1
	 * @name wps_wpr_display_apply_points_checkout
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://makewebbetter.com
	 */
	public function wps_wpr_display_apply_points_checkout() {

		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}
		// get shortcode setting values.
		$wps_wpr_other_settings                    = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_other_settings                    = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
		$wps_wpr_checkout_page_apply_point_section = ! empty( $wps_wpr_other_settings['wps_wpr_checkout_page_apply_point_section'] ) ? $wps_wpr_other_settings['wps_wpr_checkout_page_apply_point_section'] : '';
		// check if shortcode is exist then return from here.
		if ( '1' == $wps_wpr_checkout_page_apply_point_section && ! empty( get_the_content() ) ) {
			if ( true == strpos( get_the_content(), '[WPS_CHECKOUT_PAGE_SECTION' ) ) {

				return;
			}
		}

		// check allowed user for points features.
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}

		$user_id = get_current_user_ID();
		if ( isset( $user_id ) && ! empty( $user_id ) ) {
			if ( class_exists( 'Points_Rewards_For_WooCommerce_Public' ) ) {

				$public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', '1.0.0' );
			}

			// deduct points if Points Discount is applied.
			$get_points                                   = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			$wps_wpr_check_points_discount_applied_amount = ! empty( get_option( 'wps_wpr_check_points_discount_applied_amount' ) ) ? get_option( 'wps_wpr_check_points_discount_applied_amount' ) : 0;
			$get_points                                   = $get_points - $wps_wpr_check_points_discount_applied_amount;

			// deduct points if discount applied via product edit page( purchase throught only points ).
			$applied__points = 0;
			if ( isset( WC()->cart ) ) {
				foreach ( WC()->cart->get_cart() as $cart ) {
					if ( isset( $cart['product_meta'] ) && isset( $cart['product_meta']['meta_data'] ) && isset( $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'] ) ) {
						$applied__points += (int) $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'];
					}
				}
			}
			$get_points = $get_points - $applied__points;

			$get_min_redeem_req = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_value' );
			/* Points Rate*/
			$wps_wpr_cart_points_rate = $public_obj->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
			$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
			/* Points Rate*/
			$wps_wpr_cart_price_rate = $public_obj->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
			$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;
			$conversion              = ( $get_points * $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );

			$wps_wpr_order_points = apply_filters( 'wps_wpr_enable_points_on_order_total', false );
			if ( $wps_wpr_order_points ) {
				do_action( 'wps_wpr_point_limit_on_order_checkout', $get_points, $user_id, $get_min_redeem_req );
			} elseif ( $get_min_redeem_req <= $get_points ) {
				?>
				<div class="custom_point_checkout woocommerce-info wps_wpr_checkout_points_class">
					<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>"/>
					<!-- WOOCS - WooCommerce Currency Switcher Compatibility. -->
					<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0"><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
					<p><?php echo esc_html( $get_points ) . esc_html__( ' Points', 'points-and-rewards-for-woocommerce' ) . ' = ' . wp_kses( wc_price( apply_filters( 'wps_wpr_show_conversion_price', $conversion ) ), $this->wps_wpr_allowed_html() ); ?></p>
				</div>
					<?php
			} else {
				$extra_req = abs( $get_min_redeem_req - $get_points );
				?>
				<div class="custom_point_checkout woocommerce-info wps_wpr_checkout_points_class">
				<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>" readonly/>
				<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0" disabled><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
				<p><?php esc_html_e( 'You require :', 'points-and-rewards-for-woocommerce' ); ?> <?php echo esc_html( $extra_req ); ?> <?php esc_html_e( 'more points to get redeem', 'points-and-rewards-for-woocommerce' ); ?></p>
				</div>
					<?php

			}
		}
	}

	/**
	 * This function is used to add endpoints on account page.
	 *
	 * @since 1.1.4
	 * @name wps_wpr_custom_endpoint_query_vars
	 * @param array $vars array.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://makewebbetter.com
	 */
	public function wps_wpr_custom_endpoint_query_vars( $vars ) {
		if ( ! wps_wpr_restrict_user_fun() ) {

			$vars[] = 'points';
			$vars[] = 'view-log';
		}
		return $vars;
	}

	/**
	 * This function is used to add endpoints compatibility with wpml.
	 *
	 * @since 1.1.4
	 * @name wps_wpr_wpml_register_endpoint
	 * @param array  $query_vars array.
	 * @param array  $wc_vars array.
	 * @param object $obj array.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://makewebbetter.com
	 */
	public function wps_wpr_wpml_register_endpoint( $query_vars, $wc_vars, $obj ) {

		if ( ! wps_wpr_restrict_user_fun() ) {
			$query_vars['points']   = $obj->get_endpoint_translation( 'points', isset( $wc_vars['points'] ) ? $wc_vars['points'] : 'points' );
			$query_vars['view-log'] = $obj->get_endpoint_translation( 'view-log', isset( $wc_vars['view-log'] ) ? $wc_vars['view-log'] : 'view-log' );
		}
		return $query_vars;
	}

	/**
	 * This function is used to add endpoints compatibility with wpml.
	 *
	 * @since 1.1.4
	 * @name wps_wpr_endpoint_permalink_filter
	 * @param array  $endpoint array.
	 * @param string $key string.
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://makewebbetter.com
	 */
	public function wps_wpr_endpoint_permalink_filter( $endpoint, $key ) {

		if ( ! wps_wpr_restrict_user_fun() ) {
			if ( 'points' == $key ) {
				return 'points';
			}
			if ( 'view-log' == $key ) {
				return 'view-log';
			}
		}
		return $endpoint;
	}

	/**
	 * This function updates cart contents before adding into the cart.
	 *
	 * @param [mixed] $cart_contents due to cart contents.
	 * @return $cart_contents.
	 */
	public function wps_wpr_woocommerce_content_change( $cart_contents ) {

		// restrict user from points table.
		if ( wps_wpr_restrict_user_fun() ) {

			return $cart_contents;
		}

		if ( ! empty( $cart_contents ) ) {
			foreach ( $cart_contents as $key => $value ) {

				$product       = wc_get_product( $cart_contents[ $key ]['product_id'] );
				$global_enable = get_option( 'wps_wpr_assign_products_points', true );
				if ( ! empty( $product ) ) {
					if ( $product->get_type() == 'variable' ) {

						if ( isset( $cart_contents[ $key ]['variation_id'] ) && ! empty( $cart_contents[ $key ]['variation_id'] ) ) {

							$get_product_points = wps_wpr_hpos_get_meta_data( $cart_contents[ $key ]['variation_id'], 'wps_wpr_variable_points', 1 );
							$check_enable       = wps_wpr_hpos_get_meta_data( $cart_contents[ $key ]['product_id'], 'wps_product_points_enable', 'no' );

							$cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] = (int) $get_product_points * (int) ( $cart_contents[ $key ]['quantity'] );
							if ( ! is_bool( $global_enable ) && isset( $global_enable['wps_wpr_global_product_enable'] ) ) {
								if ( '0' == $global_enable['wps_wpr_global_product_enable'] && 'no' == $check_enable ) {

									unset( $cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] );
								}
							}
							if ( ! wps_wpr_is_par_pro_plugin_active() ) {

								unset( $cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] );
							}
						}
					} else {
						if ( isset( $cart_contents[ $key ]['product_id'] ) && ! empty( $cart_contents[ $key ]['product_id'] ) ) {

							$get_product_points = wps_wpr_hpos_get_meta_data( $cart_contents[ $key ]['product_id'], 'wps_points_product_value', 1 );
							$cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] = (int) $get_product_points * (int) ( $cart_contents[ $key ]['quantity'] );
						}
						$check_enable = wps_wpr_hpos_get_meta_data( $cart_contents[ $key ]['product_id'], 'wps_product_points_enable', 'no' );
						if ( ! is_bool( $global_enable ) && isset( $global_enable['wps_wpr_global_product_enable'] ) ) {
							if ( '0' == $global_enable['wps_wpr_global_product_enable'] && ( 'no' == $check_enable ) ) {

								unset( $cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] );
							}
						}
					}
				}
			}
			return $cart_contents;
		}
	}

	/**
	 * Mwb_wpr_add_wallet_generation function
	 *
	 * @param [int] $user_id userid.
	 * @return void
	 */
	public function wps_wpr_add_wallet_generation( $user_id ) {
		if ( is_plugin_active( 'wallet-system-for-woocommerce/wallet-system-for-woocommerce.php' ) && ! empty( get_option( 'wps_wsfw_enable', '' ) ) ) {
			$wps_req_points                = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : '';
			$wps_wallet_enable             = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_setting_wallet_enablee' );
			$wps_points_par_value_wallet   = $this->wps_wpr_get_general_settings_num( 'wps_wpr_wallet_points_rate' );
			$wps_currency_par_value_wallet = $this->wps_wpr_get_general_settings_num( 'wps_wpr_wallet_price_rate' );
			if ( $wps_wallet_enable && ! empty( $wps_req_points ) ) {
				?>
				<div class="wps_wpr_wallet_conversion_wrap wps_wpr_main_section_all_wrap">
					<p class="wps_wpr_heading"><?php echo esc_html__( 'Convert Points to Currency Wallet Conversion', 'points-and-rewards-for-woocommerce' ); ?></p>
					<fieldset class="wps_wpr_each_section">
						<p>
							<?php echo esc_html__( 'Points Conversion: ', 'points-and-rewards-for-woocommerce' ); ?>
							<?php echo esc_html( $wps_points_par_value_wallet ) . esc_html__( 'points = ', 'points-and-rewards-for-woocommerce' ) . wp_kses( wc_price( $wps_currency_par_value_wallet ), $this->wps_wpr_allowed_html() ); ?>
						</p>
						<form id="points_wallet" enctype="multipart/form-data" action="" method="post">
							<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
								<label for="wps_custom_wallet_text">
									<?php esc_html_e( 'Enter your points:', 'points-and-rewards-for-woocommerce' ); ?>
								</label>
							</p>
							<p id="wps_wpr_wallet_notification"></p>
							<p class="wps-wpr_enter-points-wrap">
								<input type="number" placeholder="<?php esc_html_e( 'Enter your points:', 'points-and-rewards-for-woocommerce' ); ?>" class="woocommerce-Input woocommerce-Input--number input-number" name="wps_custom_number" min="1" id="wps_custom_wallet_point_num" style="width: 160px;">

								<input type="button" name="wps_wpr_custom_wallet" id= "wps_wpr_custom_wallet" class="wps_wpr_custom_wallet button" value="<?php esc_html_e( 'Redeem to Wallet', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>">
							</p>
						</form>
					</fieldset>
				</div>
				<?php
			}
		}
	}

	/**
	 * Mwb_wpr_generate_custom_wallet function
	 *
	 * @return void
	 */
	public function wps_wpr_generate_custom_wallet() {

		check_ajax_referer( 'wps-wpr-verify-nonce', 'wps_nonce' );
		$response['result']  = false;
		$response['message'] = 'Sorry ! Not Transfered';
		if ( isset( $_POST['user_id'] ) && ! empty( $_POST['user_id'] ) && isset( $_POST['points'] ) && ! empty( $_POST['points'] ) ) {
			/*Get the the user id*/
			$user_id = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
			$points  = sanitize_text_field( wp_unslash( $_POST['points'] ) );
			/*Get all user points*/
			$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			if ( empty( $points ) ) {
				$response['result']  = false;
				$response['message'] = esc_html__( 'Sorry ! Not Transfered', 'points-and-rewards-for-woocommerce' );
			}
			if ( $get_points >= $points && ! empty( $points ) ) {

				$wps_points_par_value_wallet   = $this->wps_wpr_get_general_settings_num( 'wps_wpr_wallet_points_rate' );
				$wps_currency_par_value_wallet = $this->wps_wpr_get_general_settings_num( 'wps_wpr_wallet_price_rate' );
				$wps_wpr_wallet_roundoff       = $points * ( $wps_currency_par_value_wallet / $wps_points_par_value_wallet );
				$prev_wps_mpr_data             = get_user_meta( $user_id, 'wps_wallet', true );
				$total_data_wps_par            = $prev_wps_mpr_data + $wps_wpr_wallet_roundoff;

				$new_update_points   = $get_points - $points;
				$response['result']  = true;
				$response['message'] = esc_html__( 'successfully transfered', 'points-and-rewards-for-woocommerce' );
				$points_log          = get_user_meta( $user_id, 'points_details', true );
				$points_log          = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();
				if ( isset( $points_log['points_deduct_wallet'] ) && ! empty( $points_log['points_deduct_wallet'] ) ) {

					$points_bday_arr = array();
					$points_bday_arr = array(
						'points_deduct_wallet' => $points,
						'date'                 => gmdate( 'Y-m-d' ),
					);
					$points_log['points_deduct_wallet'][] = $points_bday_arr;
				} else {
					if ( ! is_array( $points_log ) ) {
						$points_log = array();
					}
					$points_bday_arr = array();
					$points_bday_arr = array(
						'points_deduct_wallet' => $points,
						'date'                 => gmdate( 'Y-m-d' ),
					);
					$points_log['points_deduct_wallet'][] = $points_bday_arr;
				}

				$transaction_data = array(
					'user_id'          => $user_id,
					'amount'           => $wps_wpr_wallet_roundoff,
					'currency'         => get_woocommerce_currency(),
					'payment_method'   => 'manual',
					'transaction_type' => 'points conversion to amount',
					'order_id'         => '',
					'note'             => 'Through Points and rewards',
				);
				if ( class_exists( 'Wallet_System_For_Woocommerce' ) ) {

					$wps_par_wallet_payment_gateway = new Wallet_System_For_Woocommerce();
					update_user_meta( $user_id, 'wps_wallet', $total_data_wps_par );
					update_user_meta( $user_id, 'wps_wpr_points', $new_update_points );
					update_user_meta( $user_id, 'points_details', $points_log );
					$wps_par_wallet_payment_gateway->insert_transaction_data_in_table( $transaction_data );
					// send sms.
					wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your points have been successfully converted and added to your wallet account. Your wallet balance is now %1$s, and your total remaining points are %2$s', 'points-and-rewards-for-woocommerce' ), $total_data_wps_par, $new_update_points ) );
					// send messages on whatsapp.
					wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Your points have been successfully converted and added to your wallet account. Your wallet balance is now %1$s, and your total remaining points are %2$s', 'points-and-rewards-for-woocommerce' ), $total_data_wps_par, $new_update_points ) );
				}
			}
		}
		wp_send_json( $response );
		wp_die();
	}

	/**
	 * This function is used to rename discount type in cart page
	 *
	 * @param string $sprintf sprintf.
	 * @param object $coupon coupon.
	 * @return string
	 */
	public function wps_wpr_filter_woocommerce_coupon_label( $sprintf, $coupon ) {
		$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		$coupon_data   = $coupon->get_data();
		if ( ! empty( $coupon_data ) ) {
			if ( strtolower( $coupon_data['code'] ) === strtolower( $cart_discount ) ) {
				$sprintf = $cart_discount;
			}
		}
		return $sprintf;
	}

	/**
	 * This function is used to remove coupon.
	 *
	 * @param string $coupon_html coupon html.
	 * @param object $coupon coupon.
	 * @param string $discount_amount_html discount amount html.
	 * @return string
	 */
	public function wps_wpr_par_virtual_coupon_remove( $coupon_html, $coupon, $discount_amount_html ) {
		$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		$coupon_data   = $coupon->get_data();
		if ( ! empty( $coupon_data ) ) {
			if ( strtolower( $coupon_data['code'] ) === strtolower( $cart_discount ) ) {
				$coupon_html = $discount_amount_html . ' <a href="' . esc_url( add_query_arg( 'remove_coupon', urlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url() : wc_get_cart_url() ) ) . '" class="wps_remove_virtual_coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . esc_html__( '[Remove]', 'points-and-rewards-for-woocommerce' ) . '</a>';
			}
		}
		return $coupon_html;
	}

	/**
	 * This function is used to create points log shortcode.
	 *
	 * @return object
	 */
	public function wps_wpr_create_points_log_shortocde() {
		ob_start();
			$user_ID = get_current_user_ID();
			$user    = new WP_User( $user_ID );
			require plugin_dir_path( __FILE__ ) . 'partials/wps-wpr-points-log-template.php';
		return ob_get_clean();
	}

	/**
	 * This function is used to register shortcode for WordPress pages.
	 *
	 * @return void
	 */
	public function wps_wpr_shortocde_to_show_apply_points_section() {
		// get shortcode setting values.
		$wps_wpr_other_settings                    = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_other_settings                    = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
		$wps_wpr_cart_page_apply_point_section     = ! empty( $wps_wpr_other_settings['wps_wpr_cart_page_apply_point_section'] ) ? $wps_wpr_other_settings['wps_wpr_cart_page_apply_point_section'] : '';
		$wps_wpr_checkout_page_apply_point_section = ! empty( $wps_wpr_other_settings['wps_wpr_checkout_page_apply_point_section'] ) ? $wps_wpr_other_settings['wps_wpr_checkout_page_apply_point_section'] : '';
		// Shotcode to show apply points section on cart page.
		if ( 1 === $wps_wpr_cart_page_apply_point_section ) {
			add_shortcode( 'WPS_CART_PAGE_SECTION', array( $this, 'wps_wpr_create_cart_apply_point_shotcode' ) );
		}
		// Shortcode to show apply points section on checkout page.
		if ( 1 === $wps_wpr_checkout_page_apply_point_section ) {
			add_shortcode( 'WPS_CHECKOUT_PAGE_SECTION', array( $this, 'wps_wpr_create_checkout_page_shortcode' ) );
		}
		// Shortcode to show points log on WordPress pages.
		add_shortcode( 'SHOW_POINTS_LOG', array( $this, 'wps_wpr_create_points_log_shortocde' ) );
		// calling to function to grant permission.
		$this->wps_wpr_grant_permission_to_pay_again();
	}

	/**
	 * This function is used to create shortcode for apply points section on cart page.
	 *
	 * @return object
	 */
	public function wps_wpr_create_cart_apply_point_shotcode() {
		ob_start();

		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}
		// It only shows on cart page.
		if ( is_cart() ) {
			/*Get the value of the custom points*/
			$wps_wpr_custom_points_on_cart = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
			if ( 1 === $wps_wpr_custom_points_on_cart ) {
				$user_id            = get_current_user_ID();
				$get_points         = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
				$get_min_redeem_req = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_value' );

				if ( empty( $get_points ) ) {
					$get_points = 0;
				}

				// deduct points if Points Discount is applied.
				$wps_wpr_check_points_discount_applied_amount = ! empty( get_option( 'wps_wpr_check_points_discount_applied_amount' ) ) ? get_option( 'wps_wpr_check_points_discount_applied_amount' ) : 0;
				$get_points                                   = $get_points - $wps_wpr_check_points_discount_applied_amount;

				// deduct points if discount applied via product edit page( purchase throught only points ).
				$applied__points = 0;
				if ( isset( WC()->cart ) ) {
					foreach ( WC()->cart->get_cart() as $cart ) {
						if ( isset( $cart['product_meta'] ) && isset( $cart['product_meta']['meta_data'] ) && isset( $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'] ) ) {
							$applied__points += (int) $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'];
						}
					}
				}
				$get_points = $get_points - $applied__points;

				if ( isset( $user_id ) && ! empty( $user_id ) ) {
					$wps_wpr_order_points = apply_filters( 'wps_wpr_enable_points_on_order_total', false );
					if ( $wps_wpr_order_points ) {
						do_action( 'wps_wpr_points_on_order_total', $get_points, $user_id, $get_min_redeem_req );
					} else {
						?>
						<?php
						if ( $get_min_redeem_req <= $get_points ) {
							?>
							<div class="wps_wpr_apply_custom_points">
								<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>"/>
								<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0"><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
								<p><?php esc_html_e( 'Your available points:', 'points-and-rewards-for-woocommerce' ); ?>
								<?php echo esc_html( $get_points ); ?></p>
								<p class="wps_wpr_show_restrict_message"></p>
							</div>	
							<?php
						} else {
							$extra_req = abs( $get_min_redeem_req - $get_points );
							?>
							<div class="wps_wpr_apply_custom_points">
								<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>" readonly/>
								<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0" disabled><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
								<p><?php esc_html_e( 'You require :', 'points-and-rewards-for-woocommerce' ); ?>
								<?php echo esc_html( $extra_req ); ?></p>
								<p><?php esc_html_e( 'more to get redeem', 'points-and-rewards-for-woocommerce' ); ?></p>
							</div>
							<?php
						}
					}
				}
			}
		}
		return ob_get_clean();
	}

	/**
	 * This shortcode is used to show apply points section on checkout page.
	 *
	 * @return object
	 */
	public function wps_wpr_create_checkout_page_shortcode() {
		ob_start();

		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}
		// This shortoce only works on checkout page.
		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
			// check allowed user for points features.
			if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
				return;
			}
			$user_id = get_current_user_ID();
			if ( isset( $user_id ) && ! empty( $user_id ) ) {
				if ( class_exists( 'Points_Rewards_For_WooCommerce_Public' ) ) {
					$public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', '1.0.0' );
				}

				$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
				// deduct points if Points Discount is applied.
				$wps_wpr_check_points_discount_applied_amount = ! empty( get_option( 'wps_wpr_check_points_discount_applied_amount' ) ) ? get_option( 'wps_wpr_check_points_discount_applied_amount' ) : 0;
				$get_points                                   = $get_points - $wps_wpr_check_points_discount_applied_amount;

				// deduct points if discount applied via product edit page( purchase throught only points ).
				$applied__points = 0;
				if ( isset( WC()->cart ) ) {
					foreach ( WC()->cart->get_cart() as $cart ) {
						if ( isset( $cart['product_meta'] ) && isset( $cart['product_meta']['meta_data'] ) && isset( $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'] ) ) {
							$applied__points += (int) $cart['product_meta']['meta_data']['wps_wpr_purchase_point_only'];
						}
					}
				}
				$get_points = $get_points - $applied__points;

				$get_min_redeem_req = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_value' );
				/* Points Rate*/
				$wps_wpr_cart_points_rate = $public_obj->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
				$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
				/* Points Rate*/
				$wps_wpr_cart_price_rate = $public_obj->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
				$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;
				$conversion              = ( $get_points * $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );

				$wps_wpr_order_points = apply_filters( 'wps_wpr_enable_points_on_order_total', false );
				if ( $wps_wpr_order_points ) {
					do_action( 'wps_wpr_point_limit_on_order_checkout', $get_points, $user_id, $get_min_redeem_req );
				} elseif ( $get_min_redeem_req <= $get_points ) {
					?>
					<div class="custom_point_checkout woocommerce-info wps_wpr_checkout_points_class">
						<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>"/>
						<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0"><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
						<p><?php echo esc_html( $get_points ) . esc_html__( ' Points', 'points-and-rewards-for-woocommerce' ) . ' = ' . wp_kses( wc_price( $conversion ), $this->wps_wpr_allowed_html() ); ?></p>
					</div>
						<?php
				} else {
					$extra_req = abs( $get_min_redeem_req - $get_points );
					?>
					<div class="custom_point_checkout woocommerce-info wps_wpr_checkout_points_class">
						<input type="number" min="0" name="wps_cart_points" class="input-text" id="wps_cart_points" value="" placeholder="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>" readonly/>
						<button class="button wps_cart_points_apply" name="wps_cart_points_apply" id="wps_cart_points_apply" value="<?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>" data-order-limit="0" disabled><?php esc_html_e( 'Apply Points', 'points-and-rewards-for-woocommerce' ); ?></button>
						<p><?php esc_html_e( 'You require :', 'points-and-rewards-for-woocommerce' ); ?> <?php echo esc_html( $extra_req ); ?> <?php esc_html_e( 'more points to get redeem', 'points-and-rewards-for-woocommerce' ); ?></p>
					</div>
						<?php

				}
			}
		}
		return ob_get_clean();
	}

	// WOOCS - WooCommerce Currency Switcher Compatibility.
	/**
	 * This function is used to show converted price.
	 *
	 * @param string $amounts amount.
	 * @return string
	 */
	public function wps_wpr_conversion_price_callback( $amounts ) {
		if ( class_exists( 'WOOCS' ) ) {

			global $WOOCS;
			$amount = $WOOCS->woocs_exchange_value( $amounts );
			return round( $amount );
		} else {
			return round( $amounts );
		}
	}

	/**
	 * This function is used to convert price in to base price.
	 *
	 * @param  string $price prices.
	 * @return string
	 */
	public function wps_wpr_convert_diffrent_currency_base_price_callback( $price ) {
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			$amount = 0;

			if ( $WOOCS->is_multiple_allowed ) {
				$currrent = $WOOCS->current_currency;
				if ( $currrent != $WOOCS->default_currency ) {

					$currencies = $WOOCS->get_currencies();
					$rate       = $currencies[ $currrent ]['rate'];
					$amount     = $price / ( $rate );
					return round( $amount );
				} else {
					return round( $price );
				}
			}
		}
		return round( $price );
	}

	/**
	 * This function is used to convert in to base currency when currency are same.
	 *
	 * @param  string $order_total order_total.
	 * @param  string $order_id    order_id.
	 * @return string
	 */
	public function wps_wpr_convert_same_currency_base_price_callback( $order_total, $order_id ) {
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			// hpos.
			$wps_currency = wps_wpr_hpos_get_meta_data( $order_id, '_order_currency', true );
			if ( $wps_currency == $WOOCS->default_currency ) {

				$currencies  = $WOOCS->get_currencies();
				$rate        = $currencies[ $wps_currency ]['rate'];
				$order_total = round( $order_total / ( $rate ) );
			}
		}
		return $order_total;
	}

	/**
	 * This function is used to show subscription renewal message for user acknowledge.
	 *
	 * @param int $user_id user_id.
	 * @return void
	 */
	public function wps_wpr_show_subscription_message( $user_id ) {

		if ( wps_wpr_check_is_subscription_plugin_active() ) {

			// Renewal setting values.
			$wps_wpr_general_settings                 = get_option( 'wps_wpr_settings_gallery', array() );
			$wps_wpr_subscription__renewal_points     = ! empty( $wps_wpr_general_settings['wps_wpr_subscription__renewal_points'] ) ? $wps_wpr_general_settings['wps_wpr_subscription__renewal_points'] : 0;
			$wps_wpr_enable__renewal_message_settings = ! empty( $wps_wpr_general_settings['wps_wpr_enable__renewal_message_settings'] ) ? $wps_wpr_general_settings['wps_wpr_enable__renewal_message_settings'] : 0;
			$wps_wpr_subscription__renewal_message    = ! empty( $wps_wpr_general_settings['wps_wpr_subscription__renewal_message'] ) ? $wps_wpr_general_settings['wps_wpr_subscription__renewal_message'] : esc_html__( 'You will earn [Points] points when your subscription should be renewal.', 'points-and-rewards-for-woocommerce' );
			$wps_wpr_subscription__renewal_message    = str_replace( '[Points]', $wps_wpr_subscription__renewal_points, $wps_wpr_subscription__renewal_message );

			if ( '1' == $wps_wpr_enable__renewal_message_settings ) {
				?>
				<div class ="wps_wpr_subscription_notice_wrap">
					<p class="wps_wpr_heading"><?php echo esc_html__( 'Subscription Renewal Points Message :', 'points-and-rewards-for-woocommerce' ); ?></p>
					<?php
					echo '<fieldset class="wps_wpr_each_section">' . wp_kses_post( $wps_wpr_subscription__renewal_message ) . '</fieldset>';
					?>
				</div>
				<?php
			}
		}
	}

	/**
	 * This function is used to give points to user when user reaches order limit.
	 *
	 * @param  int    $order_id order_id.
	 * @param  object $order order.
	 * @return void
	 */
	public function wps_wpr_order_rewards_points_callback( $order_id, $order ) {

		// if user is not logged in then return from here.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Restrict rewards points features.
		if ( ! $this->wps_wpr_restrict_user_rewards_points_callback( $order_id ) ) {
			return;
		}

		$user_id                              = $order->get_user_id();
		$wps_wpr_rewards_points_awarded_check = get_user_meta( $user_id, 'wps_wpr_rewards_points_awarded_check', true );
		// add a filter to reset variable.
		$wps_wpr_rewards_points_awarded_check = apply_filters( 'wps_wpr_show_rewards_next_points_message', $wps_wpr_rewards_points_awarded_check );
		// check if user is already awarded than return from here.
		if ( ! empty( $wps_wpr_rewards_points_awarded_check ) || 'done' == $wps_wpr_rewards_points_awarded_check ) {
			return;
		}

		// get order rewards setting here.
		$wps_wpr_notificatin_array             = get_option( 'wps_wpr_notificatin_array', true );
		$wps_wpr_settings_gallery              = get_option( 'wps_wpr_settings_gallery', true );
		$wps_wpr_settings_gallery              = ! empty( $wps_wpr_settings_gallery ) && is_array( $wps_wpr_settings_gallery ) ? $wps_wpr_settings_gallery : array();
		$wps_wpr_enable_order_rewards_settings = ! empty( $wps_wpr_settings_gallery['wps_wpr_enable_order_rewards_settings'] ) ? $wps_wpr_settings_gallery['wps_wpr_enable_order_rewards_settings'] : '';
		$wps_wpr_number_of_reward_order        = ! empty( $wps_wpr_settings_gallery['wps_wpr_number_of_reward_order'] ) ? $wps_wpr_settings_gallery['wps_wpr_number_of_reward_order'] : 0;
		$wps_wpr_number_of_rewards_points      = ! empty( $wps_wpr_settings_gallery['wps_wpr_number_of_rewards_points'] ) ? $wps_wpr_settings_gallery['wps_wpr_number_of_rewards_points'] : 0;
		$wps_wpr_order_rewards_points_type     = ! empty( $wps_wpr_settings_gallery['wps_wpr_order_rewards_points_type'] ) ? $wps_wpr_settings_gallery['wps_wpr_order_rewards_points_type'] : 'fixed';

		// check order rewards setting enable or not.
		if ( 1 === $wps_wpr_enable_order_rewards_settings ) {

			// updating current date for getting order within date range.
			do_action( 'wps_wpr_update_last_renewal_date', $user_id );

			// get particular user completed order.
			$args = array(
				'post_type'   => array( 'shop_order' ),
				'post_status' => array( 'wc-completed' ),
				'numberposts' => -1,
				'customer_id' => $user_id,
			);

			// add a filter to modify query.
			$args                = apply_filters( 'wps_wpr_modify_get_user_order_query', $args, $user_id );
			$customer_orders = wc_get_orders( $args );

			// check user number of order.
			if ( ! empty( $customer_orders ) && ! is_null( $customer_orders ) ) {
				// check user reches order limit.
				if ( count( $customer_orders ) >= $wps_wpr_number_of_reward_order ) {

					$today_date                = date_i18n( 'Y-m-d h:i:sa' );
					$wps_order_rewards_details = get_user_meta( $user_id, 'points_details', true );
					$wps_order_rewards_details = ! empty( $wps_order_rewards_details ) && is_array( $wps_order_rewards_details ) ? $wps_order_rewards_details : array();
					$user_total_points         = get_user_meta( $user_id, 'wps_wpr_points', true );
					$user_total_points         = ! empty( $user_total_points ) && ! is_null( $user_total_points ) ? $user_total_points : 0;

					$order_total = 0;
					$order_count = count( $customer_orders );
					for ( $i = 0; $i < $order_count; $i++ ) {

						$order_total += $customer_orders[ $i ]->total;
					}

					if ( 'percent' === $wps_wpr_order_rewards_points_type ) {

						$wps_wpr_number_of_rewards_points = ceil( ( $order_total * $wps_wpr_number_of_rewards_points ) / 100 );
						$updated_points                   = (int) $user_total_points + $wps_wpr_number_of_rewards_points;
					} else {

						$updated_points = (int) $user_total_points + $wps_wpr_number_of_rewards_points;
					}

					// create log for order rewards points.
					if ( isset( $wps_order_rewards_details['order__rewards_points'] ) && ! empty( $wps_order_rewards_details['order__rewards_points'] ) ) {
						$daily_login_arr = array(
							'order__rewards_points' => $wps_wpr_number_of_rewards_points,
							'date'                  => $today_date,
						);
						$wps_order_rewards_details['order__rewards_points'][] = $daily_login_arr;

					} else {
						if ( ! is_array( $wps_order_rewards_details ) ) {
							$wps_order_rewards_details = array();
						}
						$daily_login_arr = array(
							'order__rewards_points' => $wps_wpr_number_of_rewards_points,
							'date'                  => $today_date,
						);
						$wps_order_rewards_details['order__rewards_points'][] = $daily_login_arr;
					}

					// update user total points, update user logs.
					update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
					update_user_meta( $user_id, 'points_details', $wps_order_rewards_details );
					update_user_meta( $user_id, 'wps_wpr_rewards_points_awarded_check', 'done' );
					// send sms.
					wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've reached the specified order limit and have been rewarded with points. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $updated_points ) );
					// send messages on whatsapp.
					wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've reached the specified order limit and have been rewarded with points. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $updated_points ) );
					// add a action for updating next renewal points date within range.
					do_action( 'wps_wpr_order_rewards_next_renewal_time', $order_id, $user_id );

					// add order count for offset value in order query.
					$wps_wpr_orders_count = get_user_meta( $user_id, 'wps_wpr_orders_count', true );
					$wps_wpr_orders_count = ! empty( $wps_wpr_orders_count ) ? $wps_wpr_orders_count : 0;
					$updated_order_count  = $wps_wpr_orders_count + $order_count;
					update_user_meta( $user_id, 'wps_wpr_orders_count', $updated_order_count );

					if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {

						$wps_wpr_email_subject     = isset( $wps_wpr_notificatin_array['wps_wpr_order_rewards_points_subject'] ) ? $wps_wpr_notificatin_array['wps_wpr_order_rewards_points_subject'] : '';
						$wps_wpr_email_discription = isset( $wps_wpr_notificatin_array['wps_wpr_order_rewards_points_description'] ) ? $wps_wpr_notificatin_array['wps_wpr_order_rewards_points_description'] : '';
						$wps_wpr_email_discription = str_replace( '[REWARDTOTALPOINT]', $wps_wpr_number_of_rewards_points, $wps_wpr_email_discription );
						$wps_wpr_email_discription = str_replace( '[TOTALPOINTS]', $updated_points, $wps_wpr_email_discription );
						$user                      = get_user_by( 'id', $user_id );
						$user_name                 = $user->user_firstname;
						$wps_wpr_email_discription = str_replace( '[USERNAME]', $user_name, $wps_wpr_email_discription );

						/*check is mail notification is enable or not*/
						$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'rewards_points_notify' );
						if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {
							$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
							$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
						}
					}
				}
			}
		}
	}

	// ============ Rewards Restrict Points settings feature ===========.

	/**
	 * This function is used to enable restrict user earn settings.
	 *
	 * @return boolean
	 */
	public function wps_wpr_is_restrict_rewrds_points_settings_enable() {

		$enable                          = false;
		$wps_wpr_other_settings          = get_option( 'wps_wpr_other_settings' );
		$wps_wpr_restrict_rewards_points = ! empty( $wps_wpr_other_settings['wps_wpr_restrict_rewards_points'] ) ? $wps_wpr_other_settings['wps_wpr_restrict_rewards_points'] : '';
		if ( isset( $wps_wpr_restrict_rewards_points ) && 1 == $wps_wpr_restrict_rewards_points ) {

			$enable = true;
		}
		return $enable;
	}

	/**
	 * This function is used to enable restrict message.
	 *
	 * @return boolean
	 */
	public function wps_wpr_is_rewards_restrict_message_settings_enable() {

		$enable = false;
		if ( $this->wps_wpr_is_restrict_rewrds_points_settings_enable() ) {

			$wps_wpr_other_settings            = get_option( 'wps_wpr_other_settings' );
			$wps_wpr_show_message_on_cart_page = ! empty( $wps_wpr_other_settings['wps_wpr_show_message_on_cart_page'] ) ? $wps_wpr_other_settings['wps_wpr_show_message_on_cart_page'] : '';
			if ( isset( $wps_wpr_show_message_on_cart_page ) && 1 == $wps_wpr_show_message_on_cart_page ) {

				$enable = true;
			}
		}
		return $enable;
	}

	/**
	 * This function is used to retrict user to earn points.
	 *
	 * @param int $order_id order id.
	 * @return bool
	 */
	public function wps_wpr_restrict_user_rewards_points_callback( $order_id ) {

		$validate = true;
		if ( $this->wps_wpr_is_restrict_rewrds_points_settings_enable() ) {
			if ( isset( $order_id ) && ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );

				if ( isset( $order ) && ! empty( $order ) ) {
					$order_data = $order->get_data();

					if ( ! empty( $order_data['coupon_lines'] ) ) {
						foreach ( $order_data['coupon_lines'] as $coupon ) {

							$coupon_data = $coupon->get_data();
							if ( ! empty( $coupon_data ) ) {

								$coupon_name   = $coupon_data['code'];
								$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );

								if ( strtolower( $cart_discount ) == strtolower( $coupon_name ) ) {

									$validate = false;
								}
							}
						}
					}
				}
			}
		}
		return $validate;
	}

	/** +++++ Gamification Features */

	/**
	 * This function is used to check game functionality is enable.
	 *
	 * @return bool
	 */
	public function wps_wpr_check_gamification_is_enable() {

		$game_enable                          = false;
		$wps_wpr_save_gami_setting            = get_option( 'wps_wpr_save_gami_setting', array() );
		$wps_wpr_enable_gamification_settings = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enable_gamification_settings'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enable_gamification_settings'] : 'no';
		if ( 'yes' === $wps_wpr_enable_gamification_settings ) {

			$game_enable = true;
		}
		return $game_enable;
	}

	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public function wps_wpr_show_canvas_icons() {

		// calling campaign modal function.
		$this->wps_wpr_show_campaign_modal();

		// assign guest user points, if he create account before order complete.
		$this->wps_wpr_assign_guest_user_points_after_create_account();

		// blocked by admin.
		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}

		// if game points is rewarded than retur from here.
		if ( ! empty( get_user_meta( get_current_user_id(), 'wps_wpr_check_game_points_assign_timing', true ) ) ) {

			return;
		}

		if ( $this->wps_wpr_check_gamification_is_enable() ) {
			if ( $this->wps_wpr_win_wheel_page_display() ) {

				$wps_wpr_save_gami_setting       = get_option( 'wps_wpr_save_gami_setting', array() );
				$wps_wpr_save_gami_setting       = ! empty( $wps_wpr_save_gami_setting ) && is_array( $wps_wpr_save_gami_setting ) ? $wps_wpr_save_gami_setting : array();
				$wps_wpr_enter_segment_name      = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_name'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_name'] : array();
				$wps_wpr_enter_segment_color     = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_color'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_color'] : array();
				$wps_wpr_enter_segment_points    = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_segment_points'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_segment_points'] : array();
				$wps_wpr_enter_sgemnet_font_size = ! empty( $wps_wpr_save_gami_setting['wps_wpr_enter_sgemnet_font_size'] ) ? $wps_wpr_save_gami_setting['wps_wpr_enter_sgemnet_font_size'] : array();
				$wps_wpr_game_rewards_type       = ! empty( $wps_wpr_save_gami_setting['wps_wpr_game_rewards_type'] ) ? $wps_wpr_save_gami_setting['wps_wpr_game_rewards_type'] : array();
				?>
				<div class="wps_wpr_wheel_icon <?php echo esc_html( $this->wps_wpr_canvas_icon_position_class() ); ?>">
					<div class="wps_wpr_icon_wheel_tooltip">
						<?php
						esc_html_e( 'Click here to play the game', 'points-and-rewards-for-woocommerce' );
						?>
					</div>
					<canvas id="wps_wpr_spin_canvas_id" width="150" height="150"></canvas>
				</div>

				<!-- Main Wheel Start and limit segment name to 10 letters only-->
				<div class="wps_wpr_wheel_icon_main">
					<span class="wps_wpr_container-close">+</span>
					<div class="wps_wpr_container-shadow"></div>
					<div class="wps_wpr_container">
						<audio controls class="wps_wpr_audio-spin">
							<source src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'public/audio/spin.wav' ); ?>" type="audio/wav">
						</audio>
						<div id="wps_wpr_spinWheelButton"><?php esc_html_e( 'Spin', 'points-and-rewards-for-woocommerce' ); ?></div>
						<div class="wps_wpr_wheel">
							<?php
							if ( ! empty( $wps_wpr_enter_segment_name ) && is_array( $wps_wpr_enter_segment_name ) ) {
								foreach ( $wps_wpr_enter_segment_name as $key => $game_value ) {
									?>
									<div class="wps_wpr_number" style="--color:<?php echo esc_attr( $wps_wpr_enter_segment_color[ $key ] ); ?>"><input type="text" style="--label-font-size:<?php echo esc_attr( $wps_wpr_enter_sgemnet_font_size[ $key ] ); ?>px" value="<?php echo esc_html( $wps_wpr_enter_segment_name[ $key ] ); ?>" data-attr="<?php echo esc_attr( $wps_wpr_enter_segment_points[ $key ] ); ?>" data-rewards="<?php echo esc_attr( $wps_wpr_game_rewards_type[ $key ] ); ?>" readonly /></div>
									<?php
								}
							}
							?>
						</div>
					</div>
					<div class="wps_wpr_container-popup">
						<audio controls class="wps_wpr_audio-cheer">
							<source src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'public/audio/applose.wav' ); ?>" type="audio/wav">
						</audio>
						<div class="wps_wpr_container-popup-gif">
							<canvas id="wps_wpr-canvas"></canvas>
						</div>
						<div class="wps_wpr_container-popup-in">
							<span class="wps_wpr_container-popup-close">+</span>
							<div class="wps_wpr_container-popup-content">
								<div class="wps_wpr_container-popup-val"><?php esc_html_e( 'Hurray! You have got', 'points-and-rewards-for-woocommerce' ); ?> <span class="wps_wpr-val"></span></div>
								<input type="hidden" class="wps_wpr_user_claim_points" value="" data-type="">
							</div>
							<button class="wps_wpr_container-popup-claim"><?php esc_html_e( 'Claim Now', 'points-and-rewards-for-woocommerce' ); ?></button>
							<div id="wps_wpr_show_claim_msg"></div>
						</div>
					</div>
				</div>
				<!-- Main Wheel ends -->

				<!-- Create a pop-up for Guest user to show message for login and play the game. -->
				<div class="wps_wpr_guest_user_main_wrapper">
					<div class="wps_wpr_guest_user_contain_wrapper">
						<span class="wps_wpr_guest_close_btn">&times;</span>
						<div class="wps_wpr_guest_popup_title"><?php esc_html_e( 'Oops! You are not logged in.', 'points-and-rewards-for-woocommerce' ); ?></div>
						<div class="wps_wpr_para"><?php esc_html_e( 'To play the game, please ', 'points-and-rewards-for-woocommerce' ); ?><a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>"><?php esc_html_e( 'click here', 'points-and-rewards-for-woocommerce' ); ?></a><?php esc_html_e( ' to login.', 'points-and-rewards-for-woocommerce' ); ?></div>
					</div>
				</div>
				<?php
			}
		}
	}

	/**
	 * This function is used add class in Canvas Icon.
	 *
	 * @return string
	 */
	public function wps_wpr_canvas_icon_position_class() {

		$wps_canvas_class = '';
		if ( $this->wps_wpr_check_gamification_is_enable() ) {

			$wps_wpr_save_gami_setting    = get_option( 'wps_wpr_save_gami_setting', array() );
			$wps_wpr_select_icon_position = ! empty( $wps_wpr_save_gami_setting['wps_wpr_select_icon_position'] ) ? $wps_wpr_save_gami_setting['wps_wpr_select_icon_position'] : 'top_right';

			switch ( $wps_wpr_select_icon_position ) {

				case 'top_left':
					$wps_canvas_class = 'wps_wpr_canvas_top_left_position';
					break;
				case 'top_right':
					$wps_canvas_class = 'wps_wpr_canvas_top_right_position';
					break;
				case 'middle_left':
					$wps_canvas_class = 'wps_wpr_canvas_middle_left_position';
					break;
				case 'middle_right':
					$wps_canvas_class = 'wps_wpr_canvas_middle_right_position';
					break;
				case 'bottom_left':
					$wps_canvas_class = 'wps_wpr_canvas_bottom_left_position';
					break;
				case 'bottom_right':
					$wps_canvas_class = 'wps_wpr_canvas_bottom_right_position';
					break;
				default:
					$wps_canvas_class = 'wps_wpr_canvas_top_right_position';
			}
		}
		return $wps_canvas_class;
	}

	/**
	 * This function is used to show canvas on Wordpress page.
	 *
	 * @return bool
	 */
	public function wps_wpr_win_wheel_page_display() {
		global $wp_query;

		if ( $this->wps_wpr_check_gamification_is_enable() ) {
			$is_selected                   = false;
			$wps_wpr_save_gami_setting     = get_option( 'wps_wpr_save_gami_setting', array() );
			$wps_wpr_select_win_wheel_page = ! empty( $wps_wpr_save_gami_setting['wps_wpr_select_win_wheel_page'] ) ? $wps_wpr_save_gami_setting['wps_wpr_select_win_wheel_page'] : array();

			if ( empty( $wps_wpr_select_win_wheel_page ) ) {

				$is_selected = true;
			} elseif ( is_single() ) {

				$page_id = 'details';
				if ( in_array( $page_id, $wps_wpr_select_win_wheel_page ) ) {

					$is_selected = true;
				}
			} elseif ( ! is_shop() && ! is_home() ) {

				$page    = $wp_query->get_queried_object();
				$page_id = isset( $page->ID ) ? $page->ID : '';
				if ( in_array( $page_id, $wps_wpr_select_win_wheel_page ) ) {

					$is_selected = true;
				}
			} elseif ( is_shop() ) {
				$page_id = wc_get_page_id( 'shop' );
				if ( in_array( $page_id, $wps_wpr_select_win_wheel_page ) ) {

					$is_selected = true;
				}
			} else {

				$is_selected = false;
			}
		}
		return $is_selected;
	}

	/**
	 * This function is used to assign user claim points.
	 *
	 * @return void
	 */
	public function wps_wpr_assign_claim_points() {

		check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );
		$user_id              = get_current_user_id();
		$response             = array();
		$response['result']   = false;
		$response['msg']      = esc_html__( 'Failed', 'points-and-rewards-for-woocommerce' );
		$already_assign_check = get_user_meta( $user_id, 'wps_wpr_check_game_points_assign_timing', true );
		if ( isset( $_POST['claim_points'] ) ) {
			if ( empty( $already_assign_check ) ) {

				$wps_claim_points = ! empty( $_POST['claim_points'] ) ? sanitize_text_field( wp_unslash( $_POST['claim_points'] ) ) : 0;
				$claim_type       = ! empty( $_POST['claim_type'] ) ? sanitize_text_field( wp_unslash( $_POST['claim_type'] ) ) : 'points';
				// wallet compatibility.
				if ( 'wallet' === $claim_type && $wps_claim_points > 0 ) {

					$wallet_payment_gateway = new Wallet_System_For_Woocommerce();
					$wallet_user            = get_user_by( 'id', $user_id );
					$current_currency       = apply_filters( 'wps_wsfw_get_current_currency', get_woocommerce_currency() );

					$walletamount = (float) get_user_meta( $user_id, 'wps_wallet', true );
					$walletamount = ! empty( $walletamount ) ? $walletamount : 0;

					// Credit wallet.
					$credited_amount = apply_filters( 'wps_wsfw_convert_to_base_price', $wps_claim_points );
					$walletamount   += $credited_amount;
					update_user_meta( $user_id, 'wps_wallet', $walletamount );

					// Send notification email if enabled.
					$balance = $current_currency . ' ' . $wps_claim_points;
					if ( isset( $send_email_enable ) && 'on' === $send_email_enable ) {
						$user_name = trim( $wallet_user->first_name . ' ' . $wallet_user->last_name );

						$mail_text  = sprintf( 'Hello %s', $user_name ) . ",\r\n";
						$mail_text .= __( 'Wallet credited by ', 'points-and-rewards-for-woocommerce' ) . esc_html( $balance ) . __( ' through successfully Win Wheel.', 'points-and-rewards-for-woocommerce' );

						$to       = $wallet_user->user_email;
						$from     = get_option( 'admin_email' );
						$subject  = __( 'Wallet updating notification', 'points-and-rewards-for-woocommerce' );
						$headers  = "MIME-Version: 1.0\r\n";
						$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
						$headers .= "From: $from\r\nReply-To: $to\r\n";

						// Prefer WooCommerce email if available.
						if ( ! empty( WC()->mailer()->emails['wps_wswp_wallet_credit'] ) ) {

							$customer_email = WC()->mailer()->emails['wps_wswp_wallet_credit'];
							$user_name      = trim( $wallet_user->first_name . ' ' . $wallet_user->last_name );
							$customer_email->trigger( $user_id, $user_name, $balance, '' );
						} else {
							$wallet_payment_gateway->send_mail_on_wallet_updation( $to, $subject, $mail_text, $headers );
						}
					}

					// Record transaction.
					$transaction_type = __( 'Wallet credited through Win Wheel ', 'points-and-rewards-for-woocommerce' );
					$transaction_data = array(
						'user_id'            => $user_id,
						'amount'             => $wps_claim_points,
						'currency'           => $current_currency,
						'payment_method'     => 'Win Wheel',
						'transaction_type'   => htmlentities( $transaction_type ),
						'transaction_type_1' => 'credit',
						'order_id'           => '',
						'note'               => '',
					);
					$wallet_payment_gateway->insert_transaction_data_in_table( $transaction_data );

					$response['result'] = true;
					$response['msg']    = esc_html__( 'Success', 'points-and-rewards-for-woocommerce' );
				} elseif ( 'points' == $claim_type && $wps_claim_points > 0 ) {

					// Next play date cal.
					$wps_wpr_save_gami_setting = get_option( 'wps_wpr_save_gami_setting', array() );
					$schedule_date             = ! empty( $wps_wpr_save_gami_setting['wps_wpr_days_after_user_play_again'] ) ? $wps_wpr_save_gami_setting['wps_wpr_days_after_user_play_again'] : 0;
					if ( $schedule_date > 0 ) {

						$next_date = strtotime( gmdate( 'Y-m-d', strtotime( " + $schedule_date day" ) ) );
						update_user_meta( $user_id, 'wps_wpr_check_game_points_assign_timing', $next_date );
					}

					$user_get_points = get_user_meta( $user_id, 'wps_wpr_points', true );
					$user_get_points = ! empty( $user_get_points ) ? (int) $user_get_points : 0;

					$wps_updated_points = (int) $user_get_points + $wps_claim_points;
					update_user_meta( $user_id, 'wps_wpr_points', $wps_updated_points );
					// send sms.
					wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received claim points from the Win Wheel. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $wps_updated_points ) );
					// send messages on whatsapp.
					wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( "You've received claim points from the Win Wheel. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $wps_updated_points ) );
					// calling function for creating points log.
					$this->wps_wpr_create_game_points_logs( $wps_claim_points, $user_id, $wps_updated_points );

					$response['result'] = true;
					$response['msg']    = esc_html__( 'Success', 'points-and-rewards-for-woocommerce' );
				}
			} else {

				$response['result'] = false;
				$response['msg']    = esc_html__( 'Already played by you', 'points-and-rewards-for-woocommerce' );
			}
		}
		wp_send_json( $response );
		wp_die();
	}

	/**
	 * This function is used to game points logs.
	 *
	 * @param  string $wps_claim_points   wps_claim_points.
	 * @param  string $user_id            user_id.
	 * @param  string $wps_updated_points wps_updated_points.
	 * @return void
	 */
	public function wps_wpr_create_game_points_logs( $wps_claim_points, $user_id, $wps_updated_points ) {

		$today_date               = date_i18n( 'Y-m-d h:i:sa' );
		$wps_wpr_game_points_logs = get_user_meta( $user_id, 'points_details', true );
		$wps_wpr_game_points_logs = ! empty( $wps_wpr_game_points_logs ) && is_array( $wps_wpr_game_points_logs ) ? $wps_wpr_game_points_logs : array();

		if ( isset( $wps_wpr_game_points_logs['game_claim_points'] ) && ! empty( $wps_wpr_game_points_logs['game_claim_points'] ) ) {
			$daily_login_arr                                 = array(
				'game_claim_points' => $wps_claim_points,
				'date'              => $today_date,
			);
			$wps_wpr_game_points_logs['game_claim_points'][] = $daily_login_arr;

		} else {
			if ( ! is_array( $wps_wpr_game_points_logs ) ) {
				$wps_wpr_game_points_logs = array();
			}
			$daily_login_arr                                 = array(
				'game_claim_points' => $wps_claim_points,
				'date'              => $today_date,
			);
			$wps_wpr_game_points_logs['game_claim_points'][] = $daily_login_arr;
		}
		update_user_meta( $user_id, 'points_details', $wps_wpr_game_points_logs );

		// Sending mail as well.
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {

			$wps_wpr_email_subject     = isset( $wps_wpr_notificatin_array['wps_wpr_game_points_mail_subject'] ) ? $wps_wpr_notificatin_array['wps_wpr_game_points_mail_subject'] : '';
			$wps_wpr_email_discription = isset( $wps_wpr_notificatin_array['wps_wpr_game_points_email_description'] ) ? $wps_wpr_notificatin_array['wps_wpr_game_points_email_description'] : '';
			$wps_wpr_email_discription = str_replace( '[GAMEPOINTS]', $wps_claim_points, $wps_wpr_email_discription );
			$wps_wpr_email_discription = str_replace( '[TOTALPOINTS]', $wps_updated_points, $wps_wpr_email_discription );

			/*check is mail notification is enable or not*/
			$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'game_points_notify' );
			if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {
				$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
				$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
			}
		}
	}

	/**
	 * This function is used to grat permission to play game again.
	 *
	 * @return void
	 */
	public function wps_wpr_grant_permission_to_pay_again() {

		$current_date  = strtotime( gmdate( 'Y-m-d' ) );
		$schedule_date = get_user_meta( get_current_user_id(), 'wps_wpr_check_game_points_assign_timing' );

		if ( ! empty( $schedule_date ) && is_array( $schedule_date ) ) {
			if ( $current_date >= $schedule_date[0] ) {

				delete_user_meta( get_current_user_id(), 'wps_wpr_check_game_points_assign_timing' );
			}
		}
	}

	/**
	 * This function is used to unset session points.
	 *
	 * @return void
	 */
	public function wps_wpr_unset_points_session_while_points_negative() {

		if ( is_user_logged_in() ) {

			$exist_points             = (int) get_user_meta( get_current_user_id(), 'wps_wpr_points', true );
			$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
			$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
			$wps_wpr_cart_price_rate  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
			$wps_wpr_cart_price_rate  = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;

			// when points value is grater than price than convert points.
			if ( $wps_wpr_cart_price_rate > $wps_wpr_cart_points_rate ) {

				$exist_points = ( $exist_points * $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );
			} else {

				$exist_points = $exist_points;
			}

			if ( null !== WC() ) {

				if ( isset( WC()->session ) && WC()->session->has_session() ) {
					if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {

						$applied_points = (int) WC()->session->get( 'wps_cart_points' );
						if ( $exist_points <= 0 ) {

							WC()->session->__unset( 'wps_cart_points' );
							WC()->session->__unset( 'wps_wpr_tax_before_coupon' );
						} elseif ( $applied_points > $exist_points ) {

							WC()->session->set( 'wps_cart_points', $exist_points );
						}
					}
				}
			}
		}
	}

	/** +++++++++ User Badges Features +++++++++++ */

	/**
	 * This function is used to calculate overall total accumulated points.
	 *
	 * @return void
	 */
	public function wps_wpr_overall_accumulated_points() {

		if ( is_user_logged_in() ) {

			$updated_points = 0;
			$get_points     = get_user_meta( get_current_user_id(), 'wps_wpr_points', true );
			$get_points     = ! empty( $get_points ) ? $get_points : 0;
			$silver_points  = get_user_meta( get_current_user_id(), 'wps_wpr_overall__accumulated_points', true );
			$silver_points  = ! empty( $silver_points ) ? $silver_points : 0;
			$random_points  = get_user_meta( get_current_user_id(), 'wps_wpr_store_random_points', true );
			$random_points  = ! empty( $random_points ) ? $random_points : 0;

			// check if points is not equal than update original points.
			if ( $get_points !== $random_points ) {
				if ( $get_points > $random_points ) {

					$updated_points = (int) $get_points - $random_points;
					update_user_meta( get_current_user_id(), 'wps_wpr_store_random_points', $get_points );
				} else {

					update_user_meta( get_current_user_id(), 'wps_wpr_store_random_points', $get_points );
				}
			}

			// if value is in minus than assign zero.
			if ( $updated_points < 0 ) {

				$updated_points = 0;
			}

			// updating overall accumulated points.
			if ( $updated_points > 0 ) {

				$updated_silver_points = (int) $silver_points + $updated_points;
				update_user_meta( get_current_user_id(), 'wps_wpr_overall__accumulated_points', $updated_silver_points );
			} elseif ( $get_points > $silver_points ) {

				$updated_silver_points = (int) $get_points - $silver_points;
				$updated_silver_points = (int) $silver_points + $updated_silver_points;
				update_user_meta( get_current_user_id(), 'wps_wpr_overall__accumulated_points', $updated_silver_points );
			}

			// calling function to assign user badges.
			$this->wps_wpr_assign_user_bagdes_level();
		}
	}

	/**
	 * This function is used to assign user badges to users.
	 *
	 * @return void
	 */
	public function wps_wpr_assign_user_bagdes_level() {

		// get badges values.
		$wps_wpr_user_badges_setting         = get_option( 'wps_wpr_user_badges_setting', array() );
		$wps_wpr_user_badges_setting         = ! empty( $wps_wpr_user_badges_setting ) && is_array( $wps_wpr_user_badges_setting ) ? $wps_wpr_user_badges_setting : array();
		$wps_wpr_enable_user_badges_settings = ! empty( $wps_wpr_user_badges_setting['wps_wpr_enable_user_badges_settings'] ) ? $wps_wpr_user_badges_setting['wps_wpr_enable_user_badges_settings'] : 'no';
		$wps_wpr_enter_badges_name           = ! empty( $wps_wpr_user_badges_setting['wps_wpr_enter_badges_name'] ) ? $wps_wpr_user_badges_setting['wps_wpr_enter_badges_name'] : array();
		$wps_wpr_badges_threshold_points     = ! empty( $wps_wpr_user_badges_setting['wps_wpr_badges_threshold_points'] ) ? $wps_wpr_user_badges_setting['wps_wpr_badges_threshold_points'] : array();
		$wps_wpr_badges_rewards_points       = ! empty( $wps_wpr_user_badges_setting['wps_wpr_badges_rewards_points'] ) ? $wps_wpr_user_badges_setting['wps_wpr_badges_rewards_points'] : array();
		$wps_wpr_image_attachment_id         = ! empty( $wps_wpr_user_badges_setting['wps_wpr_image_attachment_id'] ) ? $wps_wpr_user_badges_setting['wps_wpr_image_attachment_id'] : array();

		if ( 'yes' === $wps_wpr_enable_user_badges_settings ) {
			if ( ! empty( $wps_wpr_enter_badges_name[0] ) && ! empty( $wps_wpr_badges_threshold_points[0] ) && ! empty( $wps_wpr_badges_rewards_points[0] ) ) {

				$flag                                = false;
				$index                               = 0;
				$wps_user_points                     = get_user_meta( get_current_user_id(), 'wps_wpr_points', true );
				$wps_user_points                     = ! empty( $wps_user_points ) ? $wps_user_points : 0;
				$wps_wpr_overall__accumulated_points = get_user_meta( get_current_user_id(), 'wps_wpr_overall__accumulated_points', true );
				$wps_wpr_overall__accumulated_points = ! empty( $wps_wpr_overall__accumulated_points ) ? $wps_wpr_overall__accumulated_points : 0;
				$wps_wpr_assigned_badges_level_name  = get_user_meta( get_current_user_id(), 'wps_wpr_assigned_badges_level_name', true );
				$wps_wpr_index_check                 = get_user_meta( get_current_user_id(), 'wps_wpr_index_check', true );
				$wps_wpr_index_check                 = ! empty( $wps_wpr_index_check ) ? $wps_wpr_index_check : -1;

				if ( count( $wps_wpr_enter_badges_name ) === count( $wps_wpr_badges_threshold_points ) && count( $wps_wpr_badges_threshold_points ) === count( $wps_wpr_badges_rewards_points ) ) {
					foreach ( $wps_wpr_enter_badges_name as $key => $badges_level_name ) {

						if ( $wps_wpr_overall__accumulated_points >= $wps_wpr_badges_threshold_points[ $key ] ) {
							if ( $badges_level_name !== $wps_wpr_assigned_badges_level_name ) {

								if ( $key > $wps_wpr_index_check ) {

									$index = $key;
									$flag  = true;
								}
							}
						}
					}

					if ( $flag ) {

						$updated_points = (int) $wps_user_points + $wps_wpr_badges_rewards_points[ $index ];
						update_user_meta( get_current_user_id(), 'wps_wpr_points', $updated_points );
						update_user_meta( get_current_user_id(), 'wps_wpr_assigned_badges_icon', $wps_wpr_image_attachment_id[ $index ] );
						update_user_meta( get_current_user_id(), 'wps_wpr_assigned_badges_level_name', $wps_wpr_enter_badges_name[ $index ] );
						update_user_meta( get_current_user_id(), 'wps_wpr_index_check', $index );

						// calling function to creare points log.
						$this->wps_wpr_create_user_badges_log( get_current_user_id(), $wps_wpr_badges_rewards_points[ $index ], $updated_points );
						// send sms.
						wps_wpr_send_sms_org( get_current_user_id(), /* translators: %s: sms msg */ sprintf( esc_html__( "You've earned a badge, and as a reward for this achievement, points have been added to your account. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $updated_points ) );
						// send messages on whatsapp.
						wps_wpr_send_messages_on_whatsapp( get_current_user_id(), /* translators: %s: sms msg */ sprintf( esc_html__( "You've earned a badge, and as a reward for this achievement, points have been added to your account. Your total points balance is now %s", 'points-and-rewards-for-woocommerce' ), $updated_points ) );
					}
				}
			}
		}
	}

	/**
	 * This function is used to create points log for user badges features.
	 *
	 * @param  string $user_id        user_id.
	 * @param  string $badges_points  badges_points.
	 * @param  string $updated_points updated_points.
	 * @return void
	 */
	public function wps_wpr_create_user_badges_log( $user_id, $badges_points, $updated_points ) {

		$user_badges_log = get_user_meta( $user_id, 'points_details', true );
		$user_badges_log = ! empty( $user_badges_log ) && is_array( $user_badges_log ) ? $user_badges_log : array();
		if ( ! empty( $user_badges_log['user_badges_rewards_points'] ) ) {

			$user_badges_arr                                 = array(
				'user_badges_rewards_points' => $badges_points,
				'date'                       => date_i18n( 'Y-m-d h:i:sa' ),
			);
			$user_badges_log['user_badges_rewards_points'][] = $user_badges_arr;
		} else {

			$user_badges_arr                                 = array(
				'user_badges_rewards_points' => $badges_points,
				'date'                       => date_i18n( 'Y-m-d h:i:sa' ),
			);
			$user_badges_log['user_badges_rewards_points'][] = $user_badges_arr;
		}
		update_user_meta( $user_id, 'points_details', $user_badges_log );

		// Sending mail as well.
		$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
		if ( is_array( $wps_wpr_notificatin_array ) && ! empty( $wps_wpr_notificatin_array ) ) {

			$wps_wpr_email_subject     = isset( $wps_wpr_notificatin_array['wps_wpr_badges_points_mail_subject'] ) ? $wps_wpr_notificatin_array['wps_wpr_badges_points_mail_subject'] : '';
			$wps_wpr_email_discription = isset( $wps_wpr_notificatin_array['wps_wpr_badges_points_email_description'] ) ? $wps_wpr_notificatin_array['wps_wpr_badges_points_email_description'] : '';
			$wps_wpr_email_discription = str_replace( '[BADGESPOINTS]', $badges_points, $wps_wpr_email_discription );
			$wps_wpr_email_discription = str_replace( '[TOTALPOINTS]', $updated_points, $wps_wpr_email_discription );

			/*check is mail notification is enable or not*/
			$check_enable = apply_filters( 'wps_wpr_check_custom_points_notification_enable', true, 'badges_points_notify' );
			if ( Points_Rewards_For_WooCommerce_Admin::wps_wpr_check_mail_notfication_is_enable() && $check_enable ) {
				$customer_email = WC()->mailer()->emails['wps_wpr_email_notification'];
				$email_status   = $customer_email->trigger( $user_id, $wps_wpr_email_discription, $wps_wpr_email_subject );
			}
		}
	}

	/**
	 * This function is used to show earn user badges.Gold Member for Points and Rewards
	 *
	 * @param  string $user_id user id.
	 * @return void
	 */
	public function wps_wpr_display_earn_user_badges( $user_id ) {

		// get badges values.
		$wps_wpr_user_badges_setting         = get_option( 'wps_wpr_user_badges_setting', array() );
		$wps_wpr_user_badges_setting         = ! empty( $wps_wpr_user_badges_setting ) && is_array( $wps_wpr_user_badges_setting ) ? $wps_wpr_user_badges_setting : array();
		$wps_wpr_enable_user_badges_settings = ! empty( $wps_wpr_user_badges_setting['wps_wpr_enable_user_badges_settings'] ) ? $wps_wpr_user_badges_setting['wps_wpr_enable_user_badges_settings'] : 'no';
		$wps_wpr_enable_to_show_bades        = ! empty( $wps_wpr_user_badges_setting['wps_wpr_enable_to_show_bades'] ) ? $wps_wpr_user_badges_setting['wps_wpr_enable_to_show_bades'] : 'no';
		$wps_wpr_choose_badges_position      = ! empty( $wps_wpr_user_badges_setting['wps_wpr_choose_badges_position'] ) ? $wps_wpr_user_badges_setting['wps_wpr_choose_badges_position'] : 'center';
		$wps_wpr_assigned_badges_icon        = get_user_meta( $user_id, 'wps_wpr_assigned_badges_icon', true );
		$wps_wpr_assigned_badges_level_name  = get_user_meta( $user_id, 'wps_wpr_assigned_badges_level_name', true );
		$wps_wpr_overall__accumulated_points = get_user_meta( get_current_user_id(), 'wps_wpr_overall__accumulated_points', true );
		$wps_wpr_overall__accumulated_points = ! empty( $wps_wpr_overall__accumulated_points ) ? $wps_wpr_overall__accumulated_points : 0;

		if ( 'yes' === $wps_wpr_enable_user_badges_settings ) {
			if ( 'yes' === $wps_wpr_enable_to_show_bades ) {
				if ( ! empty( $wps_wpr_assigned_badges_level_name ) ) {

					?>
					<div id="wps-par__badge-wrap" class="wps-par__badge-wrap wps-badge-<?php echo esc_html( $wps_wpr_choose_badges_position ); ?>">
						<div class="wps-par__badge-img-wrap">
							<div class="wps-par__badge-img">
								<img src="<?php echo esc_url( $wps_wpr_assigned_badges_icon ); ?>" alt="">
							</div>
						</div>
						<div class="wps-par__badge-label">
							<div class="wps_wpr_account_page_badge_name_wrapper"><?php echo esc_html( $wps_wpr_assigned_badges_level_name ); ?></div>
							<span>
								<?php echo esc_html__( 'Congratulations! You have earned this badge for earning ', 'points-and-rewards-for-woocommerce' ) . esc_html( number_format( $wps_wpr_overall__accumulated_points ) ) . esc_html__( ' points.', 'points-and-rewards-for-woocommerce' ); ?>
							</span>
						</div>
					</div>
					<?php
				}
			}
		}
	}

	/**
	 * This function is used to assign membership points on the basis of user membership level.
	 *
	 * @param  string $order_id   order_id.
	 * @param  string $old_status old_status.
	 * @param  string $new_status new_status.
	 * @return bool
	 */
	public function wps_wpr_assign_membership_rewards_points( $order_id, $old_status, $new_status ) {

		if ( wps_wpr_restrict_user_fun() ) {

			return;
		}
		$updated_points        = 0;
		$calculated_points     = 0;
		$order                 = wc_get_order( $order_id );
		$user_id               = $order->get_user_id();
		$membership_level_name = ! empty( get_user_meta( $user_id, 'membership_level', true ) ) ? get_user_meta( $user_id, 'membership_level', true ) : '';
		// if membership not assign than return from here.
		if ( empty( $membership_level_name ) ) {
			return false;
		}

		// get membership setting here.
		$wps_wpr_membership_settings = get_option( 'wps_wpr_membership_settings', array() );
		$membership_roles            = ! empty( $wps_wpr_membership_settings['membership_roles'] ) ? $wps_wpr_membership_settings['membership_roles'] : array();
		$is_enable_mem_reward_points = ! empty( $membership_roles[ $membership_level_name ]['enable_mem_reward_points'] ) ? $membership_roles[ $membership_level_name ]['enable_mem_reward_points'] : '0';
		$assign_mem_points_type      = ! empty( $membership_roles[ $membership_level_name ]['assign_mem_points_type'] ) ? $membership_roles[ $membership_level_name ]['assign_mem_points_type'] : '';
		$mem_rewards_points_val      = ! empty( $membership_roles[ $membership_level_name ]['mem_rewards_points_val'] ) ? $membership_roles[ $membership_level_name ]['mem_rewards_points_val'] : 0;

		// check membership rewards features is enable.
		if ( '1' === $is_enable_mem_reward_points ) {
			if ( 'completed' === $new_status ) {

				$user_points                       = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : 0;
				$mem_logs                          = get_user_meta( $user_id, 'points_details', true );
				$mem_logs                          = ! empty( $mem_logs ) && is_array( $mem_logs ) ? $mem_logs : array();
				$wps_wpr_already_assign_mem_points = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_already_assign_mem_points', true );
				if ( empty( $wps_wpr_already_assign_mem_points ) ) {

					// check rewards.
					if ( $mem_rewards_points_val > 0 ) {
						// check points rewards points type.
						if ( 'fixed' === $assign_mem_points_type ) {

							$calculated_points = $mem_rewards_points_val;
							$updated_points    = (int) $user_points + $mem_rewards_points_val;
						} else {

							$calculated_points = (int) ( $order->get_total() * $mem_rewards_points_val ) / 100;
							$updated_points    = $user_points + $calculated_points;
						}

						// if updated points is greater than zero.
						if ( $updated_points > 0 ) {

							if ( ! empty( $mem_logs['membership_level_rewards_points'] ) ) {

								$user_badges_arr                              = array(
									'membership_level_rewards_points' => $calculated_points,
									'date'                            => date_i18n( 'Y-m-d h:i:sa' ),
								);
								$mem_logs['membership_level_rewards_points'][] = $user_badges_arr;
							} else {

								$user_badges_arr                              = array(
									'membership_level_rewards_points' => $calculated_points,
									'date'                            => date_i18n( 'Y-m-d h:i:sa' ),
								);
								$mem_logs['membership_level_rewards_points'][] = $user_badges_arr;
							}

							update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
							wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_already_assign_mem_points', 'done' );
							wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_reward_assign_mem_points', $calculated_points );
							update_user_meta( $user_id, 'points_details', $mem_logs );
							// send sms.
							wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'As a %1$s-level member, you have been awarded with points. Your updated balance is %2$s points', 'points-and-rewards-for-woocommerce' ), $membership_level_name, $updated_points ) );
							// send messages on whatsapp.
							wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'As a %1$s-level member, you have been awarded with points. Your updated balance is %2$s points', 'points-and-rewards-for-woocommerce' ), $membership_level_name, $updated_points ) );
						}
					}
				}
			}

			// Points will be manage when order is refunded or cancelled.
			if ( 'completed' === $old_status && ( 'cancelled' === $new_status || 'refunded' === $new_status ) ) {

				$users_points                     = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? get_user_meta( $user_id, 'wps_wpr_points', true ) : 0;
				$mem__refund_logs                 = get_user_meta( $user_id, 'points_details', true );
				$mem__refund_logs                 = ! empty( $mem__refund_logs ) && is_array( $mem__refund_logs ) ? $mem__refund_logs : array();
				$wps_wpr_reward_assign_mem_points = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_reward_assign_mem_points', true );
				$wps_wpr_mem_points_refund_check  = wps_wpr_hpos_get_meta_data( $order_id, 'wps_wpr_mem_points_refund_check', true );

				if ( empty( $wps_wpr_mem_points_refund_check ) ) {
					if ( $wps_wpr_reward_assign_mem_points > 0 ) {

						$updated_points = (int) $users_points - $wps_wpr_reward_assign_mem_points;
						if ( ! empty( $mem__refund_logs['membership_level_points_refunded'] ) ) {

							$user_badges_arr                                       = array(
								'membership_level_points_refunded' => $wps_wpr_reward_assign_mem_points,
								'date'                            => date_i18n( 'Y-m-d h:i:sa' ),
							);
							$mem__refund_logs['membership_level_points_refunded'][] = $user_badges_arr;
						} else {

							$user_badges_arr                                        = array(
								'membership_level_points_refunded' => $wps_wpr_reward_assign_mem_points,
								'date'                            => date_i18n( 'Y-m-d h:i:sa' ),
							);
							$mem__refund_logs['membership_level_points_refunded'][] = $user_badges_arr;
						}

						update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
						update_user_meta( $user_id, 'points_details', $mem__refund_logs );
						wps_wpr_hpos_update_meta_data( $order_id, 'wps_wpr_mem_points_refund_check', 'done' );
						// send sms.
						wps_wpr_send_sms_org( $user_id, /* translators: %s: sms msg */ sprintf( esc_html__( 'Due to the order cancellation, %1$s points earned from your membership level have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $wps_wpr_reward_assign_mem_points, $updated_points ) );
						// send messages on whatsapp.
						wps_wpr_send_messages_on_whatsapp( $user_id, /* translators: %s: whatsapp msg */ sprintf( esc_html__( 'Due to the order cancellation, %1$s points earned from your membership level have been deducted from your account. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $wps_wpr_reward_assign_mem_points, $updated_points ) );
					}
				}
			}
		}
	}

	/** Add PAR module function.
	 *
	 * @param [type] $payment_mode payment method for par.
	 * @return mixed
	 */
	public function wps_wpr_admin_mvx_list_modules( $payment_mode ) {

		$payment_mode['par_payment'] = __( 'Points', 'points-and-rewards-for-woocommerce' );
		return $payment_mode;
	}

	/**
	 * This function is used to verify nonce on cart page.
	 *
	 * @return void
	 */
	public function wps_wpr_verify_cart_page_nonce() {
		?>
		<input type="hidden" name="wps_wpr_verify_cart_nonce" value="<?php echo esc_html( wp_create_nonce( 'wps-cart-nonce' ) ); ?>">
		<?php
	}

	/**
	 * This function is used to make PAR compatible with Dokan Plugin.
	 *
	 * @param  bool   $valid valid.
	 * @param  object $coupon coupon.
	 * @param  array  $available_vendors available vendors.
	 * @param  array  $available_products available products.
	 * @return mixed
	 */
	public function wps_wpr_dokan_plugin_compatibility( $valid, $coupon, $available_vendors, $available_products ) {

		return dokan_pro()->coupon->is_admin_coupon_valid( $coupon, $available_vendors, $available_products, array(), $valid );
	}

	/**
	 * This function is used to activate and deactivate whatsapp / sms notification.
	 *
	 * @param  string $user_id user_id.
	 * @return void
	 */
	public function wps_wpr_sms_whatsapp_active_deact( $user_id ) {
		if ( is_user_logged_in() ) {

			$wps_wpr_save_sms_settings           = get_option( 'wps_wpr_save_sms_settings' );
			$wps_wpr_save_sms_settings           = ! empty( $wps_wpr_save_sms_settings ) && is_array( $wps_wpr_save_sms_settings ) ? $wps_wpr_save_sms_settings : array();
			$wps_wpr_enable_sms_api_settings     = ! empty( $wps_wpr_save_sms_settings['wps_wpr_enable_sms_api_settings'] ) ? $wps_wpr_save_sms_settings['wps_wpr_enable_sms_api_settings'] : 'no';
			$wps_wpr_active_deactive_sms_notify  = ! empty( $wps_wpr_save_sms_settings['wps_wpr_active_deactive_sms_notify'] ) ? $wps_wpr_save_sms_settings['wps_wpr_active_deactive_sms_notify'] : '';
			$wps_wpr_enable_whatsapp_api_feature = ! empty( $wps_wpr_save_sms_settings['wps_wpr_enable_whatsapp_api_feature'] ) ? $wps_wpr_save_sms_settings['wps_wpr_enable_whatsapp_api_feature'] : '';
			$wps_wpr_deactivate_whatsapp_api     = ! empty( $wps_wpr_save_sms_settings['wps_wpr_deactivate_whatsapp_api'] ) ? $wps_wpr_save_sms_settings['wps_wpr_deactivate_whatsapp_api'] : '';
			$wps_wpr_stop_sms_notify             = get_user_meta( $user_id, 'wps_wpr_stop_sms_notify', true );
			$wps_wpr_stop_whatsapp_notify        = get_user_meta( $user_id, 'wps_wpr_stop_whatsapp_notify', true );
			// check sms or whatsapp feature is enable.
			if ( ( 'yes' === $wps_wpr_enable_sms_api_settings || 'yes' === $wps_wpr_enable_whatsapp_api_feature ) && ( 'yes' === $wps_wpr_active_deactive_sms_notify || 'yes' === $wps_wpr_deactivate_whatsapp_api ) ) {

				?>
				<div class="wps_wpr_share_points_wrap wps_wpr_main_section_all_wrap">
					<p class="wps_wpr_heading"><?php echo esc_html__( 'Deactivate Notifications', 'points-and-rewards-for-woocommerce' ); ?></p>
					<fieldset id="wps_wpr_each_section">
						<?php if ( 'yes' === $wps_wpr_active_deactive_sms_notify ) : ?>
						<div class="wps_wpr_enable_offer_setting_wrapper">
							<label for="wps_wpr_off_sms_notify"><input type="checkbox" name="wps_wpr_off_sms_notify" class="wps_wpr_off_sms_notify" value="yes" <?php checked( $wps_wpr_stop_sms_notify, 'yes' ); ?>><?php esc_html_e( 'SMS Notification', 'points-and-rewards-for-woocommerce' ); ?></label>
						</div>
							<?php
						endif;
						if ( 'yes' === $wps_wpr_deactivate_whatsapp_api ) :
							?>
						<div class="wps_wpr_enable_offer_setting_wrapper">
							<label for="wps_wpr_off_whatsapp_notify"><input type="checkbox" name="wps_wpr_off_whatsapp_notify" class="wps_wpr_off_whatsapp_notify" value="yes" <?php checked( $wps_wpr_stop_whatsapp_notify, 'yes' ); ?>><?php esc_html_e( 'Whatspp Notification', 'points-and-rewards-for-woocommerce' ); ?></label>
						</div>
						<?php endif; ?>
						<div class="wps_wpr_notify_notice_wrap" style="display: none;"></div>
					</fieldset>	
				</div>
				<?php
			}
		}
	}

	/**
	 * Handles activation and deactivation of SMS and WhatsApp notifications via AJAX.
	 *
	 * @return void
	 */
	public function wps_wpr_stop_sms_whatsapp_notify_call() {
		check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );

		$user_id  = get_current_user_id();
		$response = array(
			'result' => false,
			'msg'    => esc_html__( 'No changes made.', 'points-and-rewards-for-woocommerce' ),
		);

		$notifications = array(
			'sms'      => 'wps_wpr_stop_sms_notify',
			'whatsapp' => 'wps_wpr_stop_whatsapp_notify',
		);

		foreach ( $notifications as $type => $meta_key ) {

			$stop_key = 'stop_' . $type;
			if ( isset( $_POST[ $stop_key ] ) ) {

				$stop_value = sanitize_text_field( wp_unslash( $_POST[ $stop_key ] ) );
				update_user_meta( $user_id, $meta_key, $stop_value );

				if ( 'yes' === $stop_value ) {

					$response['result'] = true;
					$response['msg']    = /* translators: %s: sms msg */ sprintf( esc_html__( '%s notification deactivated successfully!', 'points-and-rewards-for-woocommerce' ), ucfirst( $type ) );
				} elseif ( 'no' === $stop_value ) {

					$response['result'] = false;
					$response['msg']    = /* translators: %s: sms msg */ sprintf( esc_html__( '%s notification activated!', 'points-and-rewards-for-woocommerce' ), ucfirst( $type ) );
				}
			}
		}
		wp_send_json( $response );
		wp_die();
	}

	/**
	 * This function is used to make free shipping based on membership level.
	 *
	 * @param  array $rates   rates.
	 * @param  array $package package.
	 * @return array
	 */
	public function wps_wpr_membership_free_shipping( $rates, $package ) {

		// Exit early if no membership level is assigned.
		$user_id          = get_current_user_id();
		$membership_level = get_user_meta( $user_id, 'membership_level', true );
		if ( empty( $membership_level ) ) {

			return $rates;
		}

		// Retrieve membership settings.
		$membership_settings   = get_option( 'wps_wpr_membership_settings', array() );
		$membership_roles      = $membership_settings['membership_roles'] ?? array();
		$free_shipping_enabled = $membership_roles[ $membership_level ]['wps_par_free_shipping'] ?? '0';
		if ( '1' === $free_shipping_enabled && ! empty( $rates ) ) {
			foreach ( $rates as $rate ) {

				// Set taxes to zero if applicable.
				$rate->cost = 0;
				if ( ! empty( $rate->taxes ) && is_array( $rate->taxes ) ) {
					foreach ( $rate->taxes as $tax_id => $tax ) {

						$rate->taxes[ $tax_id ] = 0;
					}
				}
			}
		}
		return $rates;
	}

	/**
	 * Display cart discount amount on thank you page.
	 *
	 * @param  array  $totals totals.
	 * @param  object $order order.
	 * @param  string $tax_display tax_display.
	 * @return mixed
	 */
	public function wps_wpr_add_cart_discount_to_order_totals( $totals, $order, $tax_display ) {

		// Retrieve and sanitize the cart discount from order meta.
		$discount = floatval( wps_wpr_hpos_get_meta_data( $order->get_id(), 'wps_cart_discount#$fee_id', true ) );

		// If no discount is applied, return the original totals.
		if ( $discount <= 0 ) {
			return $totals;
		}

		$new_totals = array();
		$inserted   = false;
		foreach ( $totals as $key => $total ) {
			$new_totals[ $key ] = $total;

			// Insert the cart discount just after the 'discount' line.
			if ( ! $inserted && 'discount' === $key ) {
				$new_totals['cart_discount'] = array(
					'label' => esc_html__( 'Cart Discount:', 'points-and-rewards-for-woocommerce' ),
					'value' => '-' . wc_price( $discount ),
				);
				$inserted = true;
			}
		}

		// If 'discount' key doesn't exist, append at the end.
		if ( ! $inserted ) {
			$new_totals['cart_discount'] = array(
				'label' => esc_html__( 'Cart Discount:', 'points-and-rewards-for-woocommerce' ),
				'value' => '-' . wc_price( $discount ),
			);
		}

		return $new_totals;
	}


	/**
	 * Check new design template active.
	 *
	 * @return bool
	 */
	public function wps_wpr_check_new_template_active() {

		$flag                             = false;
		$wps_wpr_others_settings          = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_others_settings          = ! empty( $wps_wpr_others_settings ) && is_array( $wps_wpr_others_settings ) ? $wps_wpr_others_settings : array();
		$wps_wpr_choose_account_page_temp = ! empty( $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] ) ? $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] : '';
		if ( 'temp_two' === $wps_wpr_choose_account_page_temp || 'temp_three' === $wps_wpr_choose_account_page_temp ) {

			$flag = true;
		}
		return $flag;
	}

	/**
	 * This function is used to add class in body.
	 *
	 * @param  array $classes classes.
	 * @return array
	 */
	public function wps_wpr_add_class_in_body_for_temp_three( $classes ) {

		$wps_wpr_others_settings          = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_others_settings          = ! empty( $wps_wpr_others_settings ) && is_array( $wps_wpr_others_settings ) ? $wps_wpr_others_settings : array();
		$wps_wpr_choose_account_page_temp = ! empty( $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] ) ? $wps_wpr_others_settings['wps_wpr_choose_account_page_temp'] : 0;
		if ( 'temp_three' === $wps_wpr_choose_account_page_temp ) {

			// Add a simple custom class.
			$classes[] = 'wps_wpr_points_tab_temp_three_wrap';
		}
		return $classes;
	}

	/**
	 * Get user rank by points.
	 *
	 * @param  string $target_user_id target_user_id.
	 * @return string
	 */
	public function wps_wpr_get_user_rank_by_points( $target_user_id ) {

		// Get all users ordered by their 'wps_wpr_points' in descending order.
		$user_ids = ( new WP_User_Query(
			array(
				'meta_key'   => 'wps_wpr_points',
				'orderby'    => 'meta_value_num',
				'order'      => 'DESC',
				'fields'     => 'ID',
				'meta_query' => array(
					array(
						'key'     => 'wps_wpr_points',
						'value'   => 0,
						'compare' => '>=',
						'type'    => 'NUMERIC',
					),
				),
			)
		) )->get_results();

		if ( empty( $user_ids ) || ! in_array( $target_user_id, $user_ids ) ) {
			return false;
		}

		// Return the rank (array index + 1).
		return array_search( $target_user_id, $user_ids ) + 1;
	}

	/**
	 * Assign points to guest users.
	 *
	 * @param  int $user_id user_id.
	 * @return bool
	 */
	public function wps_wpr_assign_points_to_guest_user( $user_id ) {

		// Prevent duplicate point assignment.
		if ( get_user_meta( $user_id, 'wps_wpr_guest_user_assign_points_done', true ) ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return;
		}

		$user_email   = $user->user_email;
		$guest_points = ! empty( get_option( 'wps_wpr_guest_user_points_' . $user_email ) ) ? get_option( 'wps_wpr_guest_user_points_' . $user_email ) : 0;
		if ( $guest_points > 0 ) {

			// Get and update current points.
			$current_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			$new_points     = $current_points + $guest_points;
			update_user_meta( $user_id, 'wps_wpr_points', $new_points );

			// Prepare log entry.
			$guest_user_log = get_user_meta( $user_id, 'points_details', true );
			$guest_user_log = is_array( $guest_user_log ) ? $guest_user_log : array();

			$log_entry = array(
				'guest_user_rewards_points' => $guest_points,
				'date'                      => date_i18n( 'Y-m-d h:i:sa' ),
			);

			$guest_user_log['guest_user_rewards_points'][] = $log_entry;

			// Update log.
			update_user_meta( $user_id, 'points_details', $guest_user_log );

			// Send SMS and WhatsApp notifications.
			$message = /* translators: %s: guest msg */ sprintf(
				esc_html__( 'You have been awarded %1$s points as a guest user. Your total points balance is now %2$s.', 'points-and-rewards-for-woocommerce' ),
				$guest_points,
				$new_points
			);

			wps_wpr_send_sms_org( $user_id, $message );
			wps_wpr_send_messages_on_whatsapp( $user_id, $message );

			// Mark as done.
			update_user_meta( $user_id, 'wps_wpr_guest_user_assign_points_done', 'done' );
		}
	}

	/**
	 * Undocumented function.
	 *
	 * @return bool
	 */
	public function wps_wpr_is_campaign_enable() {

		$flag                             = false;
		$wps_wpr_campaign_settings        = get_option( 'wps_wpr_campaign_settings', array() );
		$wps_wpr_campaign_settings        = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
		$wps_wpr_enable_campaign_settings = ! empty( $wps_wpr_campaign_settings['wps_wpr_enable_campaign_settings'] ) ? $wps_wpr_campaign_settings['wps_wpr_enable_campaign_settings'] : '';
		if ( 'yes' === $wps_wpr_enable_campaign_settings ) {

			$flag = true;
		}
		return $flag;
	}

	/**
	 * This function is used to show campaign modal.
	 *
	 * @return void
	 */
	public function wps_wpr_show_campaign_modal() {
		if ( $this->wps_wpr_is_campaign_enable() && $this->wps_wpr_check_selected_page() ) {

			require_once plugin_dir_path( __FILE__ ) . 'partials/wps-wpr-points-campaign-template.php';
		}
	}

	/**
	 * This function is used to show referral section.
	 *
	 * @param int $user_id user_id.
	 * @return void
	 */
	public function wps_wpr_campaigns_html_referral( $user_id ) {
		if ( $this->wps_wpr_is_campaign_enable() ) {

			$get_referral        = get_user_meta( $user_id, 'wps_points_referral', true );
			$get_referral_invite = get_user_meta( $user_id, 'wps_points_referral_invite', true );

			if ( empty( $get_referral ) && empty( $get_referral_invite ) ) {
				$referral_key    = wps_wpr_create_referral_code();
				$referral_invite = 0;
				update_user_meta( $user_id, 'wps_points_referral', $referral_key );
				update_user_meta( $user_id, 'wps_points_referral_invite', $referral_invite );
			}

			do_action( 'wps_wpr_before_add_referral_section', $user_id );
			$get_referral        = get_user_meta( $user_id, 'wps_points_referral', true );
			$get_referral_invite = get_user_meta( $user_id, 'wps_points_referral_invite', true );

			$general_settings      = get_option( 'wps_wpr_settings_gallery', true );
			$wps_wpr_referral_page = ! empty( $general_settings['wps_wpr_referral_page'] ) ? $general_settings['wps_wpr_referral_page'] : '';
			$wps_wpr_page_url      = '';
			if ( ! empty( $wps_wpr_referral_page ) ) {
				$wps_wpr_page_url = get_page_link( $wps_wpr_referral_page[0] );
			} else {
				$wps_wpr_page_url = site_url();
			}

			$site_url = apply_filters( 'wps_wpr_referral_link_url', $wps_wpr_page_url );
			?>
			<div class="wps_wpr_campaign_referral_wrapper">
				<p>Share this url to give your friends an awesome offer! You’ll earn rewards when they make a purchase and signup.</p>
				<div class="wps_wpr_refrral_code_copy">
					<p id="wps_wpr_copy"><code><?php echo esc_url( $site_url . '?pkey=' . $get_referral ); ?></code></p>
					<button class="wps_wpr_btn_copy wps_tooltip" data-clipboard-target="#wps_wpr_copy" aria-label="copied">
						<span class="wps_tooltiptext"><?php esc_html_e( 'Copy', 'points-and-rewards-for-woocommerce' ); ?></span>
						<img src="<?php echo esc_url( WPS_RWPR_DIR_URL . 'public/images/copy.png' ); ?>" alt="Copy to clipboard">
					</button>
				</div>
				<?php
				$this->wps_wpr_get_social_shraing_section( $user_id );
				?>
			</div>
			<?php
		}
	}

	/**
	 * This function is used to save current page url when guest user click on create account.
	 *
	 * @return void
	 */
	public function wps_wpr_action_campaign_login() {

		if ( $this->wps_wpr_is_campaign_enable() ) {

			check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );
			$current_page_url = ! empty( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
			$guest_id         = $this->wps_get_guest_or_user_id();
			update_option( 'wps_wpr_campaign_login_data_' . $guest_id, $current_page_url );
		}
		wp_die();
	}

	/**
	 * This function is used to redirect user to current campaign page.
	 *
	 * @param  int $redirect redirect.
	 * @return string
	 */
	public function wps_wpr_redirect_user_to_current_campaign_page( $redirect ) {

		if ( $this->wps_wpr_is_campaign_enable() ) {

			$guest_id                    = $this->wps_get_guest_or_user_id();
			$wps_wpr_campaign_login_data = get_option( 'wps_wpr_campaign_login_data_' . $guest_id );
			if ( ! empty( $wps_wpr_campaign_login_data ) ) {

				delete_option( 'wps_wpr_campaign_login_data_' . $guest_id );
				return esc_url_raw( $wps_wpr_campaign_login_data );
			}
		}
		return $redirect;
	}

	/**
	 * This function is used to get guest.
	 *
	 * @return string
	 */
	public function wps_get_guest_or_user_id() {

		$guest_id = 0;
		if ( $this->wps_wpr_is_campaign_enable() ) {

			if ( isset( $_COOKIE['wps_guest_user_id'] ) ) {
				return sanitize_text_field( wp_unslash( $_COOKIE['wps_guest_user_id'] ) );
			}

			$guest_id = 'guest_' . wp_generate_uuid4();
			setcookie( 'wps_guest_user_id', $guest_id, time() + ( 7 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );
		}
		return $guest_id;
	}

	/**
	 * This function is used to save birthday date from campaign modal.
	 *
	 * @return void
	 */
	public function wps_wpr_save_birthday_date() {

		if ( $this->wps_wpr_is_campaign_enable() ) {

			check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );

			$response = array(
				'result' => false,
				'msg'    => esc_html__( 'Birthday is not saved.', 'points-and-rewards-for-woocommerce' ),
			);

			$user_id  = get_current_user_id();
			$birthday = isset( $_POST['birth_date'] ) ? sanitize_text_field( wp_unslash( $_POST['birth_date'] ) ) : '';
			$today    = gmdate( 'Y-m-d' );

			// Check: if birthday is valid and not already saved.
			if ( $user_id && $birthday && $birthday < $today && ! get_user_meta( $user_id, '_my_bday', true ) ) {

				$settings       = get_option( 'wps_wpr_settings_gallery', array() );
				$birthday_points = isset( $settings['wps_wpr_general_birthday_value'] ) ? $settings['wps_wpr_general_birthday_value'] : '';

				update_user_meta( $user_id, '_my_bday', $birthday );
				update_user_meta( $user_id, 'points_on_birthday_order', $birthday_points );

				$response['result'] = true;
				$response['msg']    = sprintf(
					/* translators: %s: sms msg */
					esc_html__( '🎉 Congratulations! You will receive %s bonus points on your birthday!', 'points-and-rewards-for-woocommerce' ),
					$birthday_points
				);
			} else {
				$response['msg'] = esc_html__( 'Sorry, you cannot save a future birthday or update an already saved date.', 'points-and-rewards-for-woocommerce' );
			}

			wp_send_json( $response );
		}
		wp_die();
	}

	/**
	 * This function is used to rewards quiz contest points.
	 *
	 * @return void
	 */
	public function wps_wpr_rewards_quiz_points() {
		if ( ! $this->wps_wpr_is_campaign_enable() ) {
			wp_die();
		}

		check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );

		$response = array(
			'result' => false,
			'msg'    => esc_html__( 'Incorrect answer!', 'points-and-rewards-for-woocommerce' ),
		);

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json( $response );
		}

		$submitted_answer = isset( $_POST['quiz_answer'] ) ? sanitize_text_field( wp_unslash( $_POST['quiz_answer'] ) ) : '';
		$wps_index        = isset( $_POST['index'] ) ? absint( $_POST['index'] ) : 0;

		$campaign_settings   = get_option( 'wps_wpr_campaign_settings', array() );
		$quiz_correct_answer = $campaign_settings['wps_wpr_quiz_answer'][ $wps_index ] ?? '';
		$reward_points       = intval( $campaign_settings['wps_wpr_quiz_rewards_points'][ $wps_index ] ?? 0 );

		if ( $submitted_answer && $submitted_answer === $quiz_correct_answer && $reward_points > 0 ) {

			// Update points.
			$current_points = intval( get_user_meta( $user_id, 'wps_wpr_points', true ) );
			update_user_meta( $user_id, 'wps_wpr_points', $current_points + $reward_points );

			// Update points log.
			$points_log = get_user_meta( $user_id, 'points_details', true );
			$points_log = is_array( $points_log ) ? $points_log : array();
			$points_log['quiz_points_log'][] = array(
				'quiz_points_log' => $reward_points,
				'date' => date_i18n( 'Y-m-d H:i:s' ),
			);
			update_user_meta( $user_id, 'points_details', $points_log );

			// Mark quiz as rewarded.
			update_user_meta( $user_id, 'wps_wpr_quiz_points_rewarded_' . $submitted_answer, 'done' );

			$response['result'] = true;
			$response['msg'] = sprintf(
				/* translators: %s: sms msg */                esc_html__( 'Great job! You answered correctly and earned %s points!', 'points-and-rewards-for-woocommerce' ),
				$reward_points
			);
		}

		wp_send_json( $response );
		wp_die();
	}

	/**
	 * This function is used to show campaign features on selected page.
	 *
	 * @return bool
	 */
	public function wps_wpr_check_selected_page() {
		if ( $this->wps_wpr_is_campaign_enable() ) {

			global $wp_query;

			$wps_wpr_campaign_settings = get_option( 'wps_wpr_campaign_settings', array() );
			$wps_wpr_campaign_settings = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
			$selected_pages            = ! empty( $wps_wpr_campaign_settings['wps_wpr_select_page_for_campaign'] ) ? $wps_wpr_campaign_settings['wps_wpr_select_page_for_campaign'] : array();

			if ( empty( $selected_pages ) ) {
				return true;
			}

			if ( is_home() && in_array( 'home', $selected_pages ) ) {
				return true;
			}

			if ( is_shop() ) {
				$shop_id = wc_get_page_id( 'shop' );
				if ( in_array( $shop_id, $selected_pages ) ) {
					return true;
				}
			}

			$page = $wp_query->get_queried_object();
			if ( isset( $page->ID ) && in_array( $page->ID, $selected_pages ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * This function is used to assign points when user create account before order complete.
	 *
	 * @return void
	 */
	public function wps_wpr_assign_guest_user_points_after_create_account() {

		$user_id = get_current_user_id();
		if ( empty( $user_id ) ) {
			return;
		}

		// Exit if points have already been assigned.
		if ( get_user_meta( $user_id, 'wps_wpr_guest_user_assign_points_done', true ) ) {
			return;
		}

		$user = get_user_by( 'ID', $user_id );
		if ( ! $user || empty( $user->user_email ) ) {
			return;
		}

		$user_email = $user->user_email;

		// Retrieve guest points from option.
		$guest_points = (int) get_option( 'wps_wpr_guest_user_points_' . $user_email, 0 );
		if ( $guest_points <= 0 ) {
			return;
		}

		// Get current user points and calculate new total.
		$current_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		$new_points     = $current_points + $guest_points;

		// Update user points.
		update_user_meta( $user_id, 'wps_wpr_points', $new_points );

		// Update points log.
		$guest_user_log = get_user_meta( $user_id, 'points_details', true );
		$guest_user_log = is_array( $guest_user_log ) ? $guest_user_log : array();

		$guest_user_log['guest_user_rewards_points'][] = array(
			'guest_user_rewards_points' => $guest_points,
			'date'                      => date_i18n( 'Y-m-d h:i:sa' ),
		);

		update_user_meta( $user_id, 'points_details', $guest_user_log );

		// Compose message.
		$message = sprintf(
			/* translators: %s: sms msg */
			esc_html__( 'You have been awarded %1$s points as a guest user. Your total points balance is now %2$s.', 'points-and-rewards-for-woocommerce' ),
			$guest_points,
			$new_points
		);

		// Send notifications.
		wps_wpr_send_sms_org( $user_id, $message );
		wps_wpr_send_messages_on_whatsapp( $user_id, $message );

		// Mark assignment complete.
		update_user_meta( $user_id, 'wps_wpr_guest_user_assign_points_done', 'done' );
	}

	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public function wps_wpr_assign_social_share_points() {

		check_ajax_referer( 'wps-wpr-verify-nonce', 'nonce' );

		$wps_wpr_campaign_settings              = get_option( 'wps_wpr_campaign_settings', array() );
		$wps_wpr_campaign_settings              = is_array( $wps_wpr_campaign_settings ) ? $wps_wpr_campaign_settings : array();
		$wps_wpr_social_share_campaign_label    = ! empty( $wps_wpr_campaign_settings['wps_wpr_social_share_campaign_label'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_social_share_campaign_label'] ) ? $wps_wpr_campaign_settings['wps_wpr_social_share_campaign_label'] : array();
		$wps_wpr_social_share_url               = ! empty( $wps_wpr_campaign_settings['wps_wpr_social_share_url'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_social_share_url'] ) ? $wps_wpr_campaign_settings['wps_wpr_social_share_url'] : array();
		$wps_wpr_social_share_points            = ! empty( $wps_wpr_campaign_settings['wps_wpr_social_share_points'] ) && is_array( $wps_wpr_campaign_settings['wps_wpr_social_share_points'] ) ? $wps_wpr_campaign_settings['wps_wpr_social_share_points'] : array();
		$wps_wpr_combined = array();
		if ( ! empty( $wps_wpr_social_share_campaign_label ) && is_array( $wps_wpr_social_share_campaign_label ) ) {
			foreach ( $wps_wpr_social_share_campaign_label as $index => $key ) {

				$wps_wpr_combined[ $key ] = array(
					'link' => $wps_wpr_social_share_url[ $index ] ?? null,
					'value' => $wps_wpr_social_share_points[ $index ] ?? null,
				);
			}
		}

		$social_tag_name = ! empty( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

		if ( ! array_key_exists( $social_tag_name, $wps_wpr_combined ) ) {
			return;
		}

		$campaign_templates = array(
			'mailing_list'           => 'Subscribe to our mailing list',
			'insta_profile'          => 'Visit Instagram Profile',
			'view_insta_photo'       => 'View Instagram Photo',
			'like_linkedin_post'     => 'Like Post on LinkedIn',
			'share_linkedin_post'    => 'Share Post on LinkedIn',
			'share_facebook_post'    => 'Share on Facebook',
			'like_facebook_page'     => 'Like Facebook Page',
			'subs_you_chann'         => 'Subscribe to YouTube Channel',
			'watch_you_vid'          => 'Watch a YouTube Video',
			'like_you_vid'           => 'Like a YouTube Video',
			'share_twitter'          => 'Share on Twitter (X)',
			'follow_twitter'         => 'Follow on Twitter (X)',
			'like_post_twitter'      => 'Like Post on Twitter (X)',
			'visit_pinterest'        => 'Visit Pinterest',
			'follow_pinterest'       => 'Follow on Pinterest',
			'follow_board_pinterest' => 'Follow a Pinterest Board',
		);

		$user_id        = get_current_user_id();
		$url            = $wps_wpr_combined[ $social_tag_name ]['link'];
		$points         = absint( $wps_wpr_combined[ $social_tag_name ]['value'] );
		$social_heading = $campaign_templates[ $social_tag_name ];
		if ( $points > 0 ) {

			$get_points     = ! empty( get_user_meta( $user_id, 'wps_wpr_points', true ) ) ? absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) : 0;
			$points_log     = ! empty( get_user_meta( $user_id, 'points_details', true ) ) && is_array( get_user_meta( $user_id, 'points_details', true ) ) ? get_user_meta( $user_id, 'points_details', true ) : array();
			$updated_points = $get_points + $points;
			if ( ! empty( $points_log['social_share_points_log'] ) ) {

				$user_social_arr                         = array(
					'social_share_points_log' => $points,
					'date'                    => date_i18n( 'Y-m-d h:i:sa' ),
					'social_heading'          => $social_heading,
				);
				$points_log['social_share_points_log'][] = $user_social_arr;
			} else {

				$user_social_arr                         = array(
					'social_share_points_log' => $points,
					'date'                    => date_i18n( 'Y-m-d h:i:sa' ),
					'social_heading'          => $social_heading,
				);
				$points_log['social_share_points_log'][] = $user_social_arr;
			}

			update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
			update_user_meta( $user_id, 'points_details', $points_log );

			// Make sure $social_tag_name is added as an array.
			$performed = (array) get_user_meta( $user_id, 'wps_wpr_social_action_performed', true );
			$performed = array_unique( array_merge( $performed, (array) $social_tag_name ) );
			update_user_meta( $user_id, 'wps_wpr_social_action_performed', $performed );

			// send mail.
			$this->wps_wpr_send_social_campaign_mail( $user_id, $points, $social_heading, $updated_points );

			wp_send_json( $url );
		}
		wp_die();
	}

	/**
	 * This function is used to send socail campaign earning points mail.
	 *
	 * @param  mixed  $user_id        user_id.
	 * @param  mixed  $points         points.
	 * @param  string $social_heading social_heading.
	 * @param  mixed  $total_points   total_points.
	 * @return void
	 */
	public function wps_wpr_send_social_campaign_mail( $user_id, $points, $social_heading, $total_points ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$to      = $user->user_email;
		$subject = __( '🎉 You’ve Earned New Rewards!', 'points-and-rewards-for-woocommerce' );

		ob_start();
		?>
		<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="padding:30px 0; background:#f4f6f9;">
			<tr>
				<td align="center">
					<table role="presentation" width="650" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,0.08);">
						
						<!-- Header Banner -->
						<tr>
							<td align="center" style="background:linear-gradient(135deg,#43a047,#2e7d32); padding:60px 30px; position:relative;">
								<h1 style="margin:0; font-size:34px; color:#ffffff; font-weight:800; letter-spacing:0.5px;">
									<?php echo esc_html( $social_heading ); ?>
								</h1>
								<p style="margin:15px 0 0; font-size:18px; color:#dcedc8;"><?php esc_html_e( 'You just unlocked something special ✨', 'points-and-rewards-for-woocommerce' ); ?></p>
							</td>
						</tr>
						
						<!-- Hero Section -->
						<tr>
							<td align="center" style="padding:50px 30px 20px;">
								<img src="https://cdn-icons-png.flaticon.com/512/4140/4140048.png" alt="Celebration" width="120" style="display:block; margin:0 auto 25px;">
								<p style="margin:0; font-size:20px; color:#333; font-weight:600;">
									Hi <span style="color:#43a047;"><?php echo esc_html( $user->display_name ); ?></span>,
								</p>
								<p style="margin:18px 0 25px; font-size:16px; color:#666; line-height:1.7; max-width:500px;">
									<?php
									printf(
										wp_kses(
											/* translators: %s: campaign name */
											__( 'Your effort in our <strong>%s</strong> is spreading joy 🌍 We’re thrilled to reward your contribution.', 'points-and-rewards-for-woocommerce' ),
											array(
												'strong' => array(),
											)
										),
										esc_html__( 'Social Share Campaign', 'points-and-rewards-for-woocommerce' )
									);
									?>
								</p>
							</td>
						</tr>

						<!-- Points Highlight -->
						<tr>
							<td align="center" style="padding:0 30px 50px;">
								<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:520px;">
									<tr>
										<td align="center" style="background:url('https://cdn.jsdelivr.net/gh/edent/SuperTinyIcons/images/svg/confetti.svg') center/40px repeat #f1fdf3; border-radius:14px; padding:40px; border:2px dashed #a5d6a7; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
											<p style="margin:0; font-size:22px; color:#2e7d32; font-weight:700;"><?php esc_html_e( '🏆 Rewards Unlocked!', 'points-and-rewards-for-woocommerce' ); ?></p>
											<p style="margin:18px 0; font-size:40px; font-weight:800; color:#2e7d32;">
												<?php
												printf(
													/* translators: %d: number of points earned */
													esc_html__( '+%d Points', 'points-and-rewards-for-woocommerce' ),
													intval( $points )
												);
												?>
											</p>
											<p style="margin:0; font-size:15px; color:#444;">
												<?php esc_html_e( 'Your new balance is:', 'points-and-rewards-for-woocommerce' ); ?>
											</p>
											<p style="margin:6px 0 0; font-size:26px; font-weight:bold; color:#1b5e20;">
												<?php
												printf(
													/* translators: %d: user total points */
													esc_html__( '%d Points', 'points-and-rewards-for-woocommerce' ),
													intval( $total_points )
												);
												?>
											</p>
										</td>
									</tr>
								</table>
							</td>
						</tr>

						<!-- CTA Button -->
						<tr>
							<td align="center" style="padding:0 20px 60px;">
								<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'points' ) ); ?>" 
								style="background:linear-gradient(135deg,#66bb6a,#2e7d32); color:#fff; text-decoration:none; padding:20px 50px; border-radius:60px; font-weight:700; font-size:18px; display:inline-block; box-shadow:0 6px 14px rgba(67,160,71,0.35);">
									<?php esc_html_e( '🌟 Claim My Rewards', 'points-and-rewards-for-woocommerce' ); ?>
								</a>
							</td>
						</tr>

						<!-- Divider -->
						<tr>
							<td style="padding:0 40px;">
								<hr style="border:none; border-top:1px solid #eee; margin:0;">
							</td>
						</tr>

						<!-- Footer -->
						<tr>
							<td align="center" style="background:#fafafa; padding:30px; font-size:13px; color:#777; line-height:1.6;">
								<p style="margin:0;">
									<?php esc_html_e( '💌 You’re receiving this because you’re part of our rewards community.', 'points-and-rewards-for-woocommerce' ); ?>
								</p>
								<p style="margin:12px 0 0;">
									<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" style="color:#43a047; text-decoration:none; font-weight:600;">
										<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
									</a>
								</p>
								<p style="margin:10px 0 0; font-size:12px; color:#aaa;">
									<?php esc_html_e( 'Not a fan?', 'points-and-rewards-for-woocommerce' ); ?> <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" style="color:#999; text-decoration:underline;"><?php esc_html_e( 'Unsubscribe', 'points-and-rewards-for-woocommerce' ); ?></a>.
								</p>
							</td>
						</tr>

					</table>
				</td>
			</tr>
		</table>
		<?php
		$message = ob_get_clean();
		// Headers.
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		// Send email.
		if ( function_exists( 'wc_mail' ) ) {
			wc_mail( $to, $subject, $message );
		} else {
			wp_mail( $to, $subject, $message, $headers );
		}
	}
}

