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
		wp_enqueue_style( $this->plugin_name, WPS_RWPR_DIR_URL . 'public/css/points-rewards-for-woocommerce-public.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$coupon_settings          = get_option( 'wps_wpr_coupons_gallery', array() );
		$wps_minimum_points_value = isset( $coupon_settings['wps_wpr_general_minimum_value'] ) ? $coupon_settings['wps_wpr_general_minimum_value'] : 50;
		$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );

		$wps_wpr_cart_price_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );

		// get user current points.
		$current_points = get_user_meta( get_current_user_id(), 'wps_wpr_points', true );
		$current_points = ! empty( $current_points ) && ! is_null( $current_points ) ? $current_points : 0;

		wp_enqueue_script( $this->plugin_name, WPS_RWPR_DIR_URL . 'public/js/points-rewards-for-woocommerce-public.js', array( 'jquery', 'clipboard' ), $this->version, false );
		$wps_wpr = array(
			'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
			'message'                  => esc_html__( 'Please enter a valid points', 'points-and-rewards-for-woocommerce' ),
			'empty_notice'             => __( 'Please enter some points !!', 'points-and-rewards-for-woocommerce' ),
			'minimum_points'           => $wps_minimum_points_value,
			'confirmation_msg'         => __( 'Do you really want to upgrade your user level as this process will deduct the required points from your account?', 'points-and-rewards-for-woocommerce' ),
			'minimum_points_text'      => __( 'The minimum Points Required To Convert Points To Coupons is ', 'points-and-rewards-for-woocommerce' ) . $wps_minimum_points_value,
			'wps_wpr_custom_notice'    => __( 'The number of points you had entered will get deducted from your Account', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_nonce'            => wp_create_nonce( 'wps-wpr-verify-nonce' ),
			'wps_wpr_cart_points_rate' => $wps_wpr_cart_points_rate,
			'wps_wpr_cart_price_rate'  => $wps_wpr_cart_price_rate,
			'not_allowed'              => __( 'Please enter some valid points!', 'points-and-rewards-for-woocommerce' ),
			'not_suffient'             => __( 'You do not have a sufficient amount of points', 'points-and-rewards-for-woocommerce' ),
			'above_order_limit'        => __( 'Entered points do not apply to this order.', 'points-and-rewards-for-woocommerce' ),
			'points_empty'             => __( 'Please enter points.', 'points-and-rewards-for-woocommerce' ),
			'checkout_page'            => is_checkout(),
			'wps_user_current_points'  => $current_points,
		);
		wp_localize_script( $this->plugin_name, 'wps_wpr', $wps_wpr );

		if ( is_account_page() ) {
			wp_enqueue_script( 'wps_wpr_fb_js', WPS_RWPR_DIR_URL . 'public/js/points-rewards-for-woocommerce-public-fb.js', array(), $this->version, false );
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
			$wps_wpr_value = (int) $general_settings[ $id ];
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
		add_rewrite_endpoint( 'points', EP_PAGES );
		add_rewrite_endpoint( 'view-log', EP_PAGES );
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

		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return;
		}

		$user_ID = get_current_user_ID();
		$user    = new WP_User( $user_ID );

		/* Include the template file in the woocommerce template*/
		require plugin_dir_path( __FILE__ ) . 'partials/wps-wpr-points-template.php';
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
		<div class="wps_account_wrapper">
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

			$content  = '';
			$content2 = '';
			$content3 = '';
			$html_div = '<div class="wps_wpr_wrapper_button">';
			$content  = $content . $html_div;

			$twitter_share_button  = '<div class="wps_wpr_btn wps_wpr_common_class"><a class="twitter-share-button" href="https://twitter.com/intent/tweet?text=' . $page_permalink . '?pkey=' . $user_reference_key . '" target="_blank"><img src ="' . WPS_RWPR_DIR_URL . '/public/images/twitter.png">' . __( 'Tweet', 'points-and-rewards-for-woocommerce' ) . '</a></div>';
			$facebook_share_button = '<div id="fb-root"></div><div class="fb-share-button wps_wpr_common_class" data-href="' . $page_permalink . '?pkey=' . $user_reference_key . '" data-layout="button_count" data-size="small" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse">' . __( 'Share', 'points-and-rewards-for-woocommerce' ) . '</a></div>';
			$mail_share_button     = '<a class="wps_wpr_mail_button wps_wpr_common_class" href="mailto:enteryour@addresshere.com?subject=Click on this link &body=Check%20this%20out:%20' . $page_permalink . '?pkey=' . $user_reference_key . '" rel="nofollow"><img src ="' . WPS_RWPR_DIR_URL . 'public/images/email.png"></a>';
			$email_share_button    = apply_filters( 'wps_mail_box', $content, $user_id );
			$whatsapp_share_button = '<a target="_blank" class="wps_wpr_whatsapp_share" href="https://api.whatsapp.com/send?text=' . rawurlencode( $page_permalink ) . '?pkey=' . $user_reference_key . '"><img src="' . WPS_RWPR_DIR_URL . 'public/images/whatsapp.png"></a>';

			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_facebook' ) == 1 ) {

				$content = $content . $facebook_share_button;
			}
			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_twitter' ) == 1 ) {

				$content = $content . $twitter_share_button;
			}
			echo wp_kses_post( $content );

			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_email' ) == 1 ) {

				if ( $email_share_button != $html_div ) {
					$content2 = $email_share_button;
				} else {
					$content2 = $mail_share_button;
				}
			}
			$allowed_html = array(
				'div' => array(
					'id'    => array(),
					'class' => array(),
				),
				'a' => array(
					'href'  => array(),
					'class' => array(),
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
				),
			);
			echo wp_kses( $content2, $allowed_html );
			if ( $this->wps_wpr_get_general_settings_num( 'wps_wpr_whatsapp' ) == 1 ) {
				$content3 = $whatsapp_share_button;
			}

			$content3 = $content3 . '</div>';
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_kses_post( $content3 );
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
			if ( $enable_wps_signup ) {
				$wps_signup_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup_value' );
				/*Update User Points*/
				update_user_meta( $customer_id, 'wps_wpr_points', $wps_signup_value );
				/*Update the points Details of the users*/
				$data = array();
				$this->wps_wpr_update_points_details( $customer_id, 'registration', $wps_signup_value, $data );
				/*Send Email to user For the signup*/
				$this->wps_wpr_send_notification_mail( $customer_id, 'signup_notification' );
			}
			$enable_wps_refer = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_enable' );
			/*Check for the Referral*/
			if ( $enable_wps_refer ) {
				$wps_refer_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_refer_value' );
				$wps_refer_value = ( 0 == $wps_refer_value ) ? 1 : $wps_refer_value;
				/*Get Data from the Cookies*/
				$cookie_val   = isset( $_COOKIE['wps_wpr_cookie_set'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['wps_wpr_cookie_set'] ) ) : '';
				$retrive_data = $cookie_val;
				if ( ! empty( $retrive_data ) ) {
					$args['meta_query'] = array(
						array(
							'key'     => 'wps_points_referral',
							'value'   => trim( $retrive_data ),
							'compare' => '==',
						),
					);
					$refere_data = get_users( $args );
					$refere_id   = $refere_data[0]->data->ID;
					$refere      = get_user_by( 'ID', $refere_id );
					/*Check */
					$get_points = (int) get_user_meta( $refere_id, 'wps_wpr_points', true );
					if ( empty( $get_points ) ) {
						$get_points = 0;
					}
					update_option( 'refereeid', $get_points );
					$wps_wpr_referral_program = true;
					/*filter that will add restriction*/
					$wps_wpr_referral_program = apply_filters( 'wps_wpr_referral_points', $wps_wpr_referral_program, $customer_id, $refere_id );
					if ( $wps_wpr_referral_program ) {
						$total_points = (int) ( $get_points + $wps_refer_value );
						/*update the points of the referred user*/
						update_user_meta( $refere_id, 'wps_wpr_points', $total_points );
						$data = array(
							'referr_id' => $customer_id,
						);

						/*
						Update the points Details of the users
						*/
						$this->wps_wpr_update_points_details( $refere_id, 'reference_details', $wps_refer_value, $data );
						/*Send Email to user For the signup*/
						$this->wps_wpr_send_notification_mail( $refere_id, 'referral_notification' );
						/*Destroy the cookie*/
						$this->wps_wpr_destroy_cookie();
					}
				}
			}
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
		if ( 'reference_details' == $type || 'ref_product_detail' == $type ) {
			$get_referral_detail = get_user_meta( $user_id, 'points_details', true );

			if ( isset( $get_referral_detail[ $type ] ) && ! empty( $get_referral_detail[ $type ] ) ) {
				$custom_array = array(
					$type => $points,
					'date' => $today_date,
					'refered_user' => $data['referr_id'],
				);
				$get_referral_detail[ $type ][] = $custom_array;
			} else {
				if ( ! is_array( $get_referral_detail ) ) {
					$get_referral_detail = array();
				}
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
		$user                      = get_user_by( 'ID', $user_id );
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
		$points_on_order = get_post_meta( $order_id, "$order_id#points_assignedon_order_total", true );
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
				update_post_meta( $order_id, "$order_id#points_assignedon_order_total", 'yes' );
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
			$order   = wc_get_order( $order_id );
			$user_id = absint( $order->get_user_id() );
			if ( empty( $user_id ) || is_null( $user_id ) ) {
				return;
			}
			$user    = get_user_by( 'ID', $user_id );
			if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features_order', false, $user ) ) {
				return;
			}
			$user_email = $user->user_email;
			if ( 'completed' == $new_status ) {

				if ( isset( $user_id ) && ! empty( $user_id ) ) {
					$wps_wpr_ref_noof_order = (int) get_user_meta( $user_id, 'wps_wpr_no_of_orders', true );
					if ( isset( $wps_wpr_ref_noof_order ) && ! empty( $wps_wpr_ref_noof_order ) ) {
						$order_limit = get_post_meta( $order_id, "$order_id#$wps_wpr_ref_noof_order", true );
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
									$itempointsset = get_post_meta( $order_id, "$order_id#$wps_wpr_value->id#set", true );

									if ( 'set' == $itempointsset ) {
										continue;
									}
									$wps_wpr_points    = (int) $wps_wpr_value->value;
									$item_points       += (int) $wps_wpr_points;
									$wps_wpr_one_email = true;
									$product_id        = $item->get_product_id();
									$check_enable      = get_post_meta( $product_id, 'wps_product_points_enable', 'no' );
									if ( 'yes' == $check_enable ) {
										update_post_meta( $order_id, "$order_id#$wps_wpr_value->id#set", 'set' );
									}
									if ( $wps_wpr_coupon_conversion_enable ) {
										$points_key_priority_high = true;
									}
								}
							}
						}
						$order_total                 = $order->get_total();
						$order_total                 = apply_filters( 'wps_wpr_per_currency_points_on_subtotal', $order_total, $order );
						// WOOCS - WooCommerce Currency Switcher Compatibility.
						$order_total = apply_filters( 'wps_wpr_convert_same_currency_base_price', $order_total, $order_id );

						$order_total = str_replace( wc_get_price_decimal_separator(), '.', strval( $order_total ) );
						if ( $wps_wpr_coupon_conversion_enable ) {
							if ( $conversion_points_is_enable_condition || ! $points_key_priority_high ) {
								/*Get*/
								$item_conversion_id_set = get_post_meta( $order_id, "$order_id#item_conversion_id", true );
								if ( 'set' != $item_conversion_id_set ) {

									$user_id = $order->get_user_id();
									$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
									/*total calculation of the points*/
									$wps_wpr_coupon_conversion_points = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_price' );
									$wps_wpr_coupon_conversion_points = ( 0 == $wps_wpr_coupon_conversion_points ) ? 1 : $wps_wpr_coupon_conversion_points;
									$wps_wpr_coupon_conversion_price  = $this->wps_wpr_get_coupon_settings_num( 'wps_wpr_coupon_conversion_points' );
									$wps_wpr_coupon_conversion_price  = ( 0 == $wps_wpr_coupon_conversion_price ) ? 1 : $wps_wpr_coupon_conversion_price;

									/*Calculat points of the order*/
									$points_calculation = ceil( ( $order_total * $wps_wpr_coupon_conversion_points ) / $wps_wpr_coupon_conversion_price );
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
									/*update that user has get the rewards points*/
									update_post_meta( $order_id, "$order_id#item_conversion_id", 'set' );
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
								}
							}
						}
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
					}
				}
			}
		}

		// Applied points on cart refunded here.
		$mwb_wpr_array = array( 'processing', 'on-hold', 'pending', 'completed' );
		if ( in_array( $old_status, $mwb_wpr_array, true ) && ( 'cancelled' === $new_status || 'refunded' === $new_status ) ) {

			$order          = wc_get_order( $order_id );
			$user_id        = absint( $order->get_user_id() );
			$wps_points_log = get_user_meta( $user_id, 'points_details', true );
			$wps_points_log = ! empty( $wps_points_log ) && is_array( $wps_points_log ) ? $wps_points_log : array();
			if ( array_key_exists( 'cart_subtotal_point', $wps_points_log ) ) {

				$today_date         = date_i18n( 'Y-m-d h:i:sa' );
				$wps_value_to_check = absint( get_post_meta( $order_id, 'wps_cart_discount#points', true ) );
				foreach ( $wps_points_log['cart_subtotal_point'] as $key => $value ) {
					$pre_wps_check = get_post_meta( $order_id, 'refunded_points_by_cart', true );

					if ( ! isset( $pre_wps_check ) || 'done' != $pre_wps_check ) {
						if ( $value['cart_subtotal_point'] == $wps_value_to_check ) {

							$value_to_refund          = $value['cart_subtotal_point'];
							$wps_total_points_par     = get_user_meta( $user_id, 'wps_wpr_points', true );
							$wps_points_newly_updated = (int) ( $wps_total_points_par + $value_to_refund );
							$wps_refer_deduct_points  = get_user_meta( $user_id, 'points_details', true );
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
							update_post_meta( $order_id, 'refunded_points_by_cart', 'done' );
						}
					}
				}
			}

			// Refund subscription renewal awarded points when subscription is cancelled or refunded.
			if ( is_plugin_active( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php' ) ) {
				if ( array_key_exists( 'subscription_renewal_points', $wps_points_log ) ) {

					$today_date         = date_i18n( 'Y-m-d h:i:sa' );
					$wps_value_to_check = absint( get_post_meta( $order_id, 'wps_wpr_subscription_renewal_awarded_points', true ) );
					$wps_value_to_check = ! empty( $wps_value_to_check ) ? $wps_value_to_check : 0;

					foreach ( $wps_points_log['subscription_renewal_points'] as $key => $value ) {
						$pre_wps_check = get_post_meta( $order_id, 'wps_wpr_subscription_renewal_refund', true );

						if ( ! isset( $pre_wps_check ) || 'done' != $pre_wps_check ) {
							if ( $value['subscription_renewal_points'] == $wps_value_to_check ) {

								$value_to_refund                 = $value['subscription_renewal_points'];
								$wps_total_points_par            = get_user_meta( $user_id, 'wps_wpr_points', true );
								$wps_points_newly_updated        = (int) ( $wps_total_points_par - $value_to_refund );
								$wps_subscription_renewal_refund = get_user_meta( $user_id, 'points_details', true );
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
								update_post_meta( $order_id, 'wps_wpr_subscription_renewal_refund', 'done' );
							}
						}
					}
				}
			}
		}

		if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {

			$wps_wpr_notificatin_array = get_option( 'wps_wpr_notificatin_array', true );
			$mwb_wpr_array             = array( 'completed' );

			// per currency refund here in org.
			if ( in_array( $old_status, $mwb_wpr_array, true ) && ( 'cancelled' === $new_status || 'refunded' === $new_status ) ) {

				if ( $this->is_order_conversion_enabled() ) {

					$item_conversion_id_set = get_post_meta( $order_id, "$order_id#item_conversion_id", false );
					$order_total            = $order->get_total();
					$order_total            = str_replace( wc_get_price_decimal_separator(), '.', strval( $order_total ) );
					$round_down_setting     = $this->wps_wpr_set_org_general_setting();

					if ( isset( $item_conversion_id_set[0] ) && ! empty( $item_conversion_id_set[0] ) && 'set' == $item_conversion_id_set[0] ) {
						$refund_per_currency_spend_points = get_post_meta( $order_id, "$order_id#refund_per_currency_spend_points", true );
						if ( empty( $refund_per_currency_spend_points ) || 'yes' != $refund_per_currency_spend_points ) {

							$get_points  = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
							$points_log  = get_user_meta( $user_id, 'points_details', true );
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
							update_post_meta( $order_id, "$order_id#refund_per_currency_spend_points", 'yes' );

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

						foreach ( $wps_wpr_items as $key => $wps_wpr_value ) {
							$wps_wpr_assign_products_points = get_option( 'wps_wpr_assign_products_points', true );
							$wps_check_global_points_assign = $wps_wpr_assign_products_points['wps_wpr_global_product_enable'];

							if ( '1' == $wps_check_global_points_assign ) {
								if ( isset( $wps_wpr_value->key ) && ! empty( $wps_wpr_value->key ) && ( 'Points' == $wps_wpr_value->key ) ) {
									$is_refunded = get_post_meta( $order_id, "$order_id#$item_id#refund_points", true );

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
										update_post_meta( $order_id, "$order_id#$item_id#refund_points", 'yes' );

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

		$is_refunded = get_post_meta( $order_id, '$order_id#wps_point_on_order_total', true );
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
			update_post_meta( $order_id, '$order_id#wps_point_on_order_total', 'yes' );

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

		$wps_wpr_signup_value = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup_value' );
		$enable_wps_signup    = $this->wps_wpr_get_general_settings_num( 'wps_wpr_general_signup' );
		if ( $enable_wps_signup ) {
			?>
			<div class="woocommerce-message" style="background-color<?php echo esc_attr( $wps_wpr_notification_color ); ?>">
				<?php
				echo esc_html__( 'You will Get ', 'points-and-rewards-for-woocommerce' ) . esc_html( $wps_wpr_signup_value ) . esc_html__( ' Points on a successful Sign-Up', 'points-and-rewards-for-woocommerce' )
				?>
			</div>
			<?php
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
		// get shortcode setting values.
		$wps_wpr_other_settings                = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_other_settings                = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
		$wps_wpr_cart_page_apply_point_section = ! empty( $wps_wpr_other_settings['wps_wpr_cart_page_apply_point_section'] ) ? $wps_wpr_other_settings['wps_wpr_cart_page_apply_point_section'] : '';
		// check if shortcode is exist then return from here.
		if ( '1' === $wps_wpr_cart_page_apply_point_section ) {
			$content = get_the_content();
			if ( ! empty( $content ) ) {
				$shortcode = '[WPS_CART_PAGE_SECTION';
				$check     = strpos( $content, $shortcode );
				if ( true == $check ) {
					return;
				}
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
		$response['result'] = false;
		$response['message'] = __( 'Can not redeem!', 'points-and-rewards-for-woocommerce' );
		if ( ! empty( $_POST['user_id'] ) && isset( $_POST['user_id'] ) ) {
			$user_id = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
		}
		if ( ! empty( $_POST['wps_cart_points'] ) && isset( $_POST['wps_cart_points'] ) ) {
			$wps_cart_points = sanitize_text_field( wp_unslash( $_POST['wps_cart_points'] ) );
		}
		if ( isset( $user_id ) && ! empty( $user_id ) ) {
			if ( isset( $wps_cart_points ) && ! empty( $wps_cart_points ) ) {
				WC()->session->set( 'wps_cart_points', $wps_cart_points );
				$response['result'] = true;
				$response['message'] = esc_html__( 'Custom Point has been applied Successfully!', 'points-and-rewards-for-woocommerce' );
			} else {
				$response['result'] = false;
				$response['message'] = __( 'Please enter some valid points!', 'points-and-rewards-for-woocommerce' );
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
				/*Get the cart point rate*/
				$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
				$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
				$wps_wpr_cart_price_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
				$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;

				if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {
					$wps_wpr_points = WC()->session->get( 'wps_cart_points' );
					$wps_fee_on_cart = ( $wps_wpr_points * $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );
					$cart_discount = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
					// apply points on subtotal.
					$subtotal = $cart->get_subtotal();
					if ( $subtotal > $wps_fee_on_cart ) {
						$wps_fee_on_cart = $wps_fee_on_cart;
					} else {

						$wps_fee_on_cart = $subtotal;
					}
					// WOOCS - WooCommerce Currency Switcher Compatibility.
					$wps_fee_on_cart = apply_filters( 'wps_wpr_show_conversion_price', $wps_fee_on_cart );
					do_action( 'wps_change_amount_cart', $wps_fee_on_cart, $cart, $cart_discount );

					// Paypal Issue Change Start.

					if ( isset( $woocommerce->cart ) ) {
						if ( ! $woocommerce->cart->has_discount( $cart_discount ) ) {
							if ( $woocommerce->cart->applied_coupons ) {
								foreach ( $woocommerce->cart->applied_coupons as $code ) {
									if ( $cart_discount === $code ) {
										return;
									}
								}
							}
							$woocommerce->cart->applied_coupons[] = $cart_discount;
						}
					}

					// $cart->add_fee( $cart_discount, -$wps_fee_on_cart, true, '' );
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
	 * @param object $coupon_data coupon data.
	 * @return string
	 */
	public function wps_wpr_validate_virtual_coupon_for_points( $response, $coupon_data ) {
		if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {
			if ( ! is_admin() ) {
				if ( false !== $coupon_data && 0 !== $coupon_data ) {

					/*Get the current user id*/
					$my_cart_change_return = 0;
					if ( ! empty( WC()->cart ) ) {
						$my_cart_change_return = apply_filters( 'wps_cart_content_check_for_sale_item', WC()->cart->get_cart() );
					}
					$cart_discount = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
					if ( '1' == $my_cart_change_return ) {
						return;
					} else {
							$user_id = get_current_user_ID();
							/*Check is custom points on cart is enable*/
							$wps_wpr_custom_points_on_cart     = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
							$wps_wpr_custom_points_on_checkout = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
						if ( isset( $user_id ) && ! empty( $user_id ) && ( 1 == $wps_wpr_custom_points_on_cart || 1 == $wps_wpr_custom_points_on_checkout ) ) {
							/*Get the cart point rate*/
							$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
							$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
							$wps_wpr_cart_price_rate  = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
							$wps_wpr_cart_price_rate  = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;

							if ( isset( WC()->session ) && WC()->session->has_session() ) {
								if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {
									$wps_wpr_points  = WC()->session->get( 'wps_cart_points' );
									$wps_fee_on_cart = ( $wps_wpr_points * $wps_wpr_cart_price_rate / $wps_wpr_cart_points_rate );

									global $woocommerce;

									// apply points on subtotal.
									$subtotal = $woocommerce->cart->get_subtotal();
									if ( $subtotal > $wps_fee_on_cart ) {
										$wps_fee_on_cart = $wps_fee_on_cart;
									} else {

										$wps_fee_on_cart = $subtotal;
									}
									// WOOCS - WooCommerce Currency Switcher Compatibility.
									$wps_fee_on_cart = apply_filters( 'wps_wpr_show_conversion_price', $wps_fee_on_cart );
									if ( $coupon_data == $cart_discount ) {
										$discount_type = 'fixed_cart';
										$coupon = array(
											'id' => time() . rand( 2, 9 ),
											'amount' => $wps_fee_on_cart,
											'individual_use' => false,
											'product_ids' => array(),
											'exclude_product_ids' => array(),
											'usage_limit' => '',
											'usage_limit_per_user' => '',
											'limit_usage_to_x_items' => '',
											'usage_count' => '',
											'expiry_date' => '',
											'apply_before_tax' => 'yes',
											'free_shipping' => false,
											'product_categories' => array(),
											'exclude_product_categories' => array(),
											'exclude_sale_items' => false,
											'minimum_amount' => '',
											'maximum_amount' => '',
											'customer_email' => '',
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
		/*Check is custom points on cart is enable*/
		$wps_wpr_custom_points_on_checkout = $this->wps_wpr_get_general_settings_num( 'wps_wpr_apply_points_checkout' );
		$wps_wpr_custom_points_on_cart     = $this->wps_wpr_get_general_settings_num( 'wps_wpr_custom_points_on_cart' );
		/*Get the Notification*/
		$wps_wpr_notification_color = $this->wps_wpr_get_other_settings( 'wps_wpr_notification_color' );
		$wps_wpr_notification_color = ( ! empty( $wps_wpr_notification_color ) ) ? $wps_wpr_notification_color : '#55b3a5';
		/*Get the cart point rate*/
		$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
		$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
		/*Get the cart price rate*/
		$wps_wpr_cart_price_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
		$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;
		/*Get current user id*/
		$user_id = get_current_user_ID();
		if ( ( 1 == $wps_wpr_custom_points_on_cart || 1 === $wps_wpr_custom_points_on_checkout ) && isset( $user_id ) && ! empty( $user_id ) ) {
			?>
			<div class="woocommerce-message wps_wpr_cart_redemption__notice"><?php esc_html_e( 'Here is the Discount Rule for Applying your Points to Cart Total', 'points-and-rewards-for-woocommerce' ); ?>
				<ul>
					<li>
					<?php
					// WOOCS - WooCommerce Currency Switcher Compatibility.
					$allowed_tags = $this->wps_wpr_allowed_html();
					echo esc_html( $wps_wpr_cart_points_rate ) . esc_html__( ' Points', 'points-and-rewards-for-woocommerce' ) . ' = ' . wp_kses( wc_price( apply_filters( 'wps_wpr_show_conversion_price', $wps_wpr_cart_price_rate ) ), $allowed_tags );
					?>
					</li>
				</ul>
			</div>
			<div class="wps_rwpr_settings_display_none_notice" id="wps_wpr_cart_points_notice"></div>
			<div class="wps_rwpr_settings_display_none_notice" id="wps_wpr_cart_points_success"></div>
			<?php
		}
		if ( $this->is_order_conversion_enabled() ) {
			$order_conversion_rate = $this->order_conversion_rate();
			?>
			<div class="woocommerce-message" id="wps_wpr_order_notice" style="background-color: <?php echo esc_html( $wps_wpr_notification_color ); ?>">
				<?php
				esc_html_e( 'Place Order and Earn Reward Points in Return.', 'points-and-rewards-for-woocommerce' );
				?>
				<p style="background-color: 
				<?php
				echo esc_html( $wps_wpr_notification_color ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				">
				<?php
				// WOOCS - WooCommerce Currency Switcher Compatibility.
				esc_html_e( 'Conversion Rate: ', 'points-and-rewards-for-woocommerce' );
				$allowed_tags = $this->wps_wpr_allowed_html();
				echo wp_kses_post( $order_conversion_rate['curr'] ) . ' ' . wp_kses_post( apply_filters( 'wps_wpr_show_conversion_price', $order_conversion_rate['Points'] ) ) . ' = ' . wp_kses( $order_conversion_rate['Value'], $allowed_tags );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html_e( ' Points', 'points-and-rewards-for-woocommerce' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				</p>
			</div>
			<?php
		}

		// ==== Order Rewards Points message show here ====

		// check if user is already awarded than return from here.
		$wps_wpr_rewards_points_awarded_check = get_user_meta( $user_id, 'wps_wpr_rewards_points_awarded_check', true );
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
					$wps_customer_orders = get_posts(
						array(
							'numberposts' => -1,
							'meta_key'    => '_customer_user',
							'meta_value'  => $user_id,
							'post_type'   => wc_get_order_types(),
							'post_status' => array( 'wc-completed' ),
						)
					);

					// Get user order count.
					if ( ! empty( $wps_customer_orders ) && ! is_null( $wps_customer_orders ) ) {
						$order_count = count( $wps_customer_orders );
					}

					// Replace order and points shortcode with order count and order rewards points.
					$wps_wpr_number_order_rewards_messages = str_replace( '[ORDER]', ( $wps_wpr_number_of_reward_order - $order_count ), $wps_wpr_number_order_rewards_messages );
					$wps_wpr_number_order_rewards_messages = str_replace( '[POINTS]', $wps_wpr_number_of_rewards_points, $wps_wpr_number_order_rewards_messages );

					?>
					<!-- Show awards discount notice -->
					<div class="woocommerce-message" id="wps_wpr_order_notice" style="background-color: <?php echo esc_attr( $wps_wpr_notification_color ); ?>">
						<p style="background-color: <?php echo esc_attr( $wps_wpr_notification_color ); ?>"><?php echo wp_kses_post( $wps_wpr_number_order_rewards_messages ); ?></p>
					</div>
					<?php
				}
			}
		}
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
	 * @param object  $fee array of the fees.
	 */
	public function wps_wpr_woocommerce_cart_totals_fee_html( $cart_totals_fee_html, $fee ) {
		if ( isset( $fee ) && ! empty( $fee ) ) {
			$fee_name      = $fee->name;
			$cart_discount = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
			if ( isset( $fee_name ) && $cart_discount == $fee_name ) {
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
		$cart_discount       = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		$coupon_code         = isset( $_POST['coupon_code'] ) && ! empty( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
		if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {
			WC()->session->__unset( 'wps_cart_points' );
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
	 * This function will update the user points as they purchased products through points
	 *
	 * @name wps_wpr_woocommerce_checkout_update_order_meta..
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 * @param int   $order_id id of the order.
	 * @param array $data data of the order.
	 */
	public function wps_wpr_woocommerce_checkout_update_order_meta( $order_id, $data ) {
		$user_id    = get_current_user_id();
		$get_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		/*Get the cart points rate*/
		$wps_wpr_cart_points_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_points_rate' );
		$wps_wpr_cart_points_rate = ( 0 == $wps_wpr_cart_points_rate ) ? 1 : $wps_wpr_cart_points_rate;
		/*Get the cart price rate*/
		$wps_wpr_cart_price_rate = $this->wps_wpr_get_general_settings_num( 'wps_wpr_cart_price_rate' );
		$wps_wpr_cart_price_rate = ( 0 == $wps_wpr_cart_price_rate ) ? 1 : $wps_wpr_cart_price_rate;
		/*Order*/
		$order = wc_get_order( $order_id );
		if ( isset( $order ) && ! empty( $order ) ) {

			// Paypal Issue Change Start.
			$order_data = $order->get_data();
			if ( ! empty( $order_data['coupon_lines'] ) ) {

				foreach ( $order_data['coupon_lines'] as $coupon ) {
					$coupon_data = $coupon->get_data();
					if ( ! empty( $coupon_data ) ) {

						$coupon_name   = $coupon_data['code'];
						$cart_discount = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
						if ( strtolower( $cart_discount ) == strtolower( $coupon_name ) ) {

							$coupon_meta   = $coupon_data['meta_data'][0]->get_data();
							$coupon_amount = $coupon_meta['value']['amount'];
							// WOOCS - WooCommerce Currency Switcher Compatibility.
							$coupon_amount = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $coupon_amount );
							update_post_meta( $order_id, 'wps_cart_discount#$fee_id', $coupon_amount );
							$fee_to_point    = ceil( ( $wps_wpr_cart_points_rate * $coupon_amount ) / $wps_wpr_cart_price_rate );
							$fee_to_point    = apply_filters( 'wps_round_down_cart_total_value_amount', $fee_to_point, $wps_wpr_cart_points_rate, $coupon_amount, $wps_wpr_cart_price_rate );
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
							/*Unset the session*/
							if ( ! empty( WC()->session->get( 'wps_cart_points' ) ) ) {
								update_post_meta( $order_id, 'wps_cart_discount#points', WC()->session->get( 'wps_cart_points' ) );
								WC()->session->__unset( 'wps_cart_points' );
							}
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
		/*Get the quantitiy of the product*/
		if ( ! empty( $_REQUEST['quantity'] ) && isset( $_REQUEST['quantity'] ) ) {
			$wps_get_quantity = sanitize_text_field( wp_unslash( $_REQUEST['quantity'] ) );
		}
		if ( isset( $wps_get_quantity ) && $wps_get_quantity && null != $wps_get_quantity ) {
			$quantity = (int) $wps_get_quantity;
		} else {
			$quantity = 1;
		}

		$check_enable = get_post_meta( $product_id, 'wps_product_points_enable', 'no' );
		if ( 'yes' == $check_enable ) {
			/*Check is exists the variation id*/
			if ( isset( $variation_id ) && ! empty( $variation_id ) && $variation_id > 0 ) {
				$get_product_points          = get_post_meta( $variation_id, 'wps_wpr_variable_points', 1 );
				$item_meta['wps_wpm_points'] = (int) $get_product_points * (int) $quantity;
			} else {
				$get_product_points          = get_post_meta( $product_id, 'wps_points_product_value', 1 );
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
	 * This function is used to show item points in product discription page.   .
	 *
	 * @name wps_display_product_points
	 * @since 1.0.0
	 * @author WP Swings <webmaster@wpswings.com>
	 * @link https://www.wpswings.com/
	 */
	public function wps_display_product_points() {
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
		$check_enable = get_post_meta( $post->ID, 'wps_product_points_enable', 'no' );
		if ( 'yes' == $check_enable ) {
			if ( ! $product_is_variable ) {
				$get_product_points = get_post_meta( $post->ID, 'wps_points_product_value', 1 );
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

									$new_price = $reg_price - ( $reg_price * $values['Discount'] ) / 100;
									$price     = '<del>' . wc_price( $reg_price ) . $product_data->get_price_suffix() . '</del><ins>' . wc_price( $new_price ) . $product_data->get_price_suffix() . '</ins>';// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
							} elseif ( ! $this->check_exclude_sale_products( $product_data ) ) {
								$terms = get_the_terms( $product_id, 'product_cat' );
								if ( is_array( $terms ) && ! empty( $terms ) && ! $product_is_variable ) {
									foreach ( $terms as $term ) {

										$cat_id     = $term->term_id;
										$parent_cat = $term->parent;
										if ( in_array( $cat_id, $values['Prod_Categ'] ) || in_array( $parent_cat, $values['Prod_Categ'] ) ) {
											if ( ! empty( $reg_price ) ) {

												$new_price = $reg_price - ( $reg_price * $values['Discount'] ) / 100;
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

			if ( isset( $value['variation_id'] ) && ! empty( $value['variation_id'] ) ) {
				$variation_id     = $value['variation_id'];
				$variable_product = wc_get_product( $variation_id );
				$variable_price   = $variable_product->get_price();
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

										$new_price = $reg_price - ( $reg_price * $values['Discount'] ) / 100;
										// WOOCS - WooCommerce Currency Switcher Compatibility.
										$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
										if ( $woo_ver < '3.0.0' ) {
											$value['data']->price = $new_price;
										} else {
											$value['data']->set_price( $new_price );
										}
									} elseif ( $product_is_variable ) {

										$new_price = $variable_price - ( $variable_price * $values['Discount'] ) / 100;
										// WOOCS - WooCommerce Currency Switcher Compatibility.
										$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
										if ( $woo_ver < '3.0.0' ) {

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

										$cat_id     = $term->term_id;
										$parent_cat = $term->parent;
										if ( in_array( $cat_id, $values['Prod_Categ'] ) || in_array( $parent_cat, $values['Prod_Categ'] ) ) {
											if ( ! $product_is_variable ) {

												$new_price = $reg_price - ( $reg_price * $values['Discount'] ) / 100;
												// WOOCS - WooCommerce Currency Switcher Compatibility.
												$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
												if ( $woo_ver < '3.0.0' ) {
													$value['data']->price = $new_price;
												} else {
													$value['data']->set_price( $new_price );
												}
											} elseif ( $product_is_variable ) {

												$new_price = $variable_price - ( $variable_price * $values['Discount'] ) / 100;
												// WOOCS - WooCommerce Currency Switcher Compatibility.
												$new_price = apply_filters( 'wps_wpr_convert_base_price_diffrent_currency', $new_price );
												if ( $woo_ver < '3.0.0' ) {

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

										$get_product_points = get_post_meta( $cart[ $key ]['variation_id'], 'wps_wpr_variable_points', 1 );
									}
								} else {
									if ( isset( $cart[ $key ]['product_id'] ) && ! empty( $cart[ $key ]['product_id'] ) ) {
										$get_product_points = get_post_meta( $cart[ $key ]['product_id'], 'wps_points_product_value', 1 );
									}
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
		$cart_discount = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		if ( $cart_discount == $fee->object->name ) {
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
		// get shortcode setting values.
		$wps_wpr_other_settings                    = get_option( 'wps_wpr_other_settings', array() );
		$wps_wpr_other_settings                    = ! empty( $wps_wpr_other_settings ) && is_array( $wps_wpr_other_settings ) ? $wps_wpr_other_settings : array();
		$wps_wpr_checkout_page_apply_point_section = ! empty( $wps_wpr_other_settings['wps_wpr_checkout_page_apply_point_section'] ) ? $wps_wpr_other_settings['wps_wpr_checkout_page_apply_point_section'] : '';
		// check if shortcode is exist then return from here.
		if ( '1' === $wps_wpr_checkout_page_apply_point_section ) {
			$content = get_the_content();
			if ( ! empty( $content ) ) {
				$shortcode = '[WPS_CHECKOUT_PAGE_SECTION';
				$check     = strpos( $content, $shortcode );
				if ( true == $check ) {
					return;
				}
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

			$get_points         = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
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
			} else {
				if ( $get_min_redeem_req <= $get_points ) {
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
		$vars[] = 'points';
		$vars[] = 'view-log';
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

		$query_vars['points']   = $obj->get_endpoint_translation( 'points', isset( $wc_vars['points'] ) ? $wc_vars['points'] : 'points' );
		$query_vars['view-log'] = $obj->get_endpoint_translation( 'view-log', isset( $wc_vars['view-log'] ) ? $wc_vars['view-log'] : 'view-log' );
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

		if ( 'points' == $key ) {
			return 'points';
		}
		if ( 'view-log' == $key ) {
			return 'view-log';
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

		if ( ! empty( $cart_contents ) ) {
			foreach ( $cart_contents as $key => $value ) {

				$product       = wc_get_product( $cart_contents[ $key ]['product_id'] );
				$global_enable = get_option( 'wps_wpr_assign_products_points', true );
				if ( $product->get_type() == 'variable' ) {

					if ( isset( $cart_contents[ $key ]['variation_id'] ) && ! empty( $cart_contents[ $key ]['variation_id'] ) ) {

						$get_product_points = get_post_meta( $cart_contents[ $key ]['variation_id'], 'wps_wpr_variable_points', 1 );
						$check_enable       = get_post_meta( $cart_contents[ $key ]['product_id'], 'wps_product_points_enable', 'no' );

						$cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] = (int) $get_product_points * (int) ( $cart_contents[ $key ]['quantity'] );
						if ( ! is_bool( $global_enable ) && isset( $global_enable['wps_wpr_global_product_enable'] ) ) {
							if ( '0' == $global_enable['wps_wpr_global_product_enable'] && 'no' == $check_enable ) {

								unset( $cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] );
							}
						}
						if ( ! is_plugin_active( 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php' ) ) {

							unset( $cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] );
						}
					}
				} else {
					if ( isset( $cart_contents[ $key ]['product_id'] ) && ! empty( $cart_contents[ $key ]['product_id'] ) ) {

						$get_product_points = get_post_meta( $cart_contents[ $key ]['product_id'], 'wps_points_product_value', 1 );
						$cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] = (int) $get_product_points * (int) ( $cart_contents[ $key ]['quantity'] );
					}
					$check_enable = get_post_meta( $cart_contents[ $key ]['product_id'], 'wps_product_points_enable', 'no' );
					if ( ! is_bool( $global_enable ) && isset( $global_enable['wps_wpr_global_product_enable'] ) ) {
						if ( '0' == $global_enable['wps_wpr_global_product_enable'] && ( 'no' == $check_enable ) ) {

							unset( $cart_contents[ $key ]['product_meta']['meta_data']['wps_wpm_points'] );
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
				<p class="wps_wpr_heading"><?php echo esc_html__( 'Convert Points to Currency  Wallet Conversion', 'ultimate-woocommerce-points-and-rewards' ); ?></p>
				<fieldset class="wps_wpr_each_section">
					<p>
						<?php echo esc_html__( 'Points Conversion: ', 'ultimate-woocommerce-points-and-rewards' ); ?>
						<?php echo esc_html( $wps_points_par_value_wallet ) . esc_html__( 'points = ', 'ultimate-woocommerce-points-and-rewards' ) . wp_kses( wc_price( $wps_currency_par_value_wallet ), $this->wps_wpr_allowed_html() ); ?>
					</p>
					<form id="points_wallet" enctype="multipart/form-data" action="" method="post">
						<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
							<label for="wps_custom_wallet_text">
								<?php esc_html_e( 'Enter your points:', 'ultimate-woocommerce-points-and-rewards' ); ?>
							</label>
							<p id="wps_wpr_wallet_notification"></p>
							<input type="number" class="woocommerce-Input woocommerce-Input--number input-number" name="wps_custom_number" min="1" id="wps_custom_wallet_point_num" style="width: 160px;">

							<input type="button" name="wps_wpr_custom_wallet" id= "wps_wpr_custom_wallet" class="wps_wpr_custom_wallet button" value="<?php esc_html_e( 'Redeem to Wallet', 'ultimate-woocommerce-points-and-rewards' ); ?>" data-id="<?php echo esc_html( $user_id ); ?>">
						</p>
					</form>
				</fieldset>
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
					$response['message'] = 'Sorry ! Not Transfered';
			}
			if ( $get_points >= $points && ! empty( $points ) ) {

				$wps_points_par_value_wallet   = $this->wps_wpr_get_general_settings_num( 'wps_wpr_wallet_points_rate' );
				$wps_currency_par_value_wallet = $this->wps_wpr_get_general_settings_num( 'wps_wpr_wallet_price_rate' );
				$wps_wpr_wallet_roundoff       = $points * ( $wps_currency_par_value_wallet / $wps_points_par_value_wallet );
				$prev_wps_mpr_data             = get_user_meta( $user_id, 'wps_wallet', true );
				$total_data_wps_par            = $prev_wps_mpr_data + $wps_wpr_wallet_roundoff;

				$new_update_points   = $get_points - $points;
				$response['result']  = true;
				$response['message'] = 'successfully transfered';
				$points_log          = get_user_meta( $user_id, 'points_details', true );
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
		$cart_discount = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
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
		$cart_discount = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		$coupon_data   = $coupon->get_data();
		if ( ! empty( $coupon_data ) ) {
			if ( strtolower( $coupon_data['code'] ) === strtolower( $cart_discount ) ) {
				$coupon_html = $discount_amount_html . ' <a href="' . esc_url( add_query_arg( 'remove_coupon', urlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url() : wc_get_cart_url() ) ) . '" class="wps_remove_virtual_coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( '[Remove]', 'woocommerce' ) . '</a>';
			}
		}
		return $coupon_html;
	}

	/**
	 * Shortcode to show points log on WordPress pages.
	 *
	 * @return void
	 */
	public function wps_wpr_shortocde_to_show_points_log() {
		add_shortcode( 'SHOW_POINTS_LOG', array( $this, 'wps_wpr_create_points_log_shortocde' ) );
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
		if ( '1' === $wps_wpr_cart_page_apply_point_section ) {
			add_shortcode( 'WPS_CART_PAGE_SECTION', array( $this, 'wps_wpr_create_apply_point_shotcode' ) );
		}
		// Shortcode to show apply points section on checkout page.
		if ( '1' === $wps_wpr_checkout_page_apply_point_section ) {
			add_shortcode( 'WPS_CHECKOUT_PAGE_SECTION', array( $this, 'wps_wpr_create_checkout_page_shortcode' ) );
		}
	}

	/**
	 * This function is used to create shortcode for apply points section on cart page.
	 *
	 * @return object
	 */
	public function wps_wpr_create_apply_point_shotcode() {
		ob_start();

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
				$get_points         = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
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
				} else {
					if ( $get_min_redeem_req <= $get_points ) {
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
			$wps_currency = get_post_meta( $order_id, '_order_currency', true );
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

		if ( is_plugin_active( 'subscriptions-for-woocommerce/subscriptions-for-woocommerce.php' ) ) {
			if ( function_exists( 'wps_sfw_check_plugin_enable' ) && wps_sfw_check_plugin_enable() ) {

				// Renewal setting values.
				$wps_wpr_general_settings                 = get_option( 'wps_wpr_settings_gallery', array() );
				$wps_wpr_subscription__renewal_points     = ! empty( $wps_wpr_general_settings['wps_wpr_subscription__renewal_points'] ) ? $wps_wpr_general_settings['wps_wpr_subscription__renewal_points'] : 0;
				$wps_wpr_enable__renewal_message_settings = ! empty( $wps_wpr_general_settings['wps_wpr_enable__renewal_message_settings'] ) ? $wps_wpr_general_settings['wps_wpr_enable__renewal_message_settings'] : 0;
				$wps_wpr_subscription__renewal_message    = ! empty( $wps_wpr_general_settings['wps_wpr_subscription__renewal_message'] ) ? $wps_wpr_general_settings['wps_wpr_subscription__renewal_message'] : esc_html__( 'You will earn [Points] points when your subscription should be renewal.', 'points-and-rewards-for-woocommerce' );
				$wps_wpr_subscription__renewal_message    = str_replace( '[Points]', $wps_wpr_subscription__renewal_points, $wps_wpr_subscription__renewal_message );

				if ( '1' == $wps_wpr_enable__renewal_message_settings ) {
					?>
					<div class ="wps_ways_to_gain_points_section">
						<p class="wps_wpr_heading"><?php echo esc_html__( 'Subscription Renewal Points Message :', 'points-and-rewards-for-woocommerce' ); ?></p>
						<?php
						echo '<fieldset class="wps_wpr_each_section">' . wp_kses_post( $wps_wpr_subscription__renewal_message ) . '</fieldset>';
						?>
					</div>
					<?php
				}
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

		$user_id                              = $order->get_user_id();
		$wps_wpr_rewards_points_awarded_check = get_user_meta( $user_id, 'wps_wpr_rewards_points_awarded_check', true );
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

		// check order rewards setting enable or not.
		if ( 1 === $wps_wpr_enable_order_rewards_settings ) {

			// get particular user completed order.
			$customer_orders = get_posts(
				array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => $user_id,
					'post_type'   => wc_get_order_types(),
					'post_status' => array( 'wc-completed' ),
				)
			);

			// check user number of order.
			if ( ! empty( $customer_orders ) && ! is_null( $customer_orders ) ) {
				// check user reches order limit.
				if ( count( $customer_orders ) >= $wps_wpr_number_of_reward_order ) {

					$today_date                = date_i18n( 'Y-m-d h:i:sa' );
					$wps_order_rewards_details = get_user_meta( $user_id, 'points_details', true );
					$wps_order_rewards_details = ! empty( $wps_order_rewards_details ) && is_array( $wps_order_rewards_details ) ? $wps_order_rewards_details : array();
					$user_total_points         = get_user_meta( $user_id, 'wps_wpr_points', true );
					$user_total_points         = ! empty( $user_total_points ) && ! is_null( $user_total_points ) ? $user_total_points : 0;
					$updated_points            = (int) $user_total_points + $wps_wpr_number_of_rewards_points;

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

}
