<?php
/**
 * Floating loyalty widget.
 *
 * A bubble button fixed to a corner of every storefront page that opens a
 * compact rewards panel: points balance, membership level, ways to earn,
 * referral link and a link to the account points page. Guests see a
 * join-and-earn call to action instead.
 *
 * The panel is built from sections that can be added, removed or reordered
 * with the `wps_wpr_floating_widget_sections` filter.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Floating loyalty widget.
 */
class WPS_WPR_Floating_Widget {

	/**
	 * Option that stores the widget settings.
	 */
	const OPTION = 'wps_wpr_floating_widget_settings';

	/**
	 * Script / style handle.
	 */
	const HANDLE = 'wps-wpr-floating-widget';

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_default_settings() {
		return array(
			'wps_wpr_fw_enable'        => 'no',
			'wps_wpr_fw_position'      => 'bottom-right',
			'wps_wpr_fw_color'         => '#6c3fc5',
			'wps_wpr_fw_label'         => __( 'Rewards', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_fw_icon'          => '★',
			'wps_wpr_fw_show_to'       => 'all',
			'wps_wpr_fw_hide_cart'     => 'no',
			'wps_wpr_fw_hide_checkout' => 'yes',
			'wps_wpr_fw_show_mobile'   => 'yes',
			'wps_wpr_fw_offset'        => 24,
			'wps_wpr_fw_mode'          => 'compact',
			'wps_wpr_fw_tab_home'      => 'yes',
			'wps_wpr_fw_tab_earn'      => 'yes',
			'wps_wpr_fw_tab_redeem'    => 'yes',
			'wps_wpr_fw_tab_activity'  => 'yes',
			'wps_wpr_fw_tab_refer'     => 'yes',
			'wps_wpr_fw_targeting'     => 'all',
			'wps_wpr_fw_page_ids'      => '',
			/* translators: [NAME] is replaced with the customer's first name. */
			'wps_wpr_fw_welcome'       => __( 'Welcome back, [NAME]!', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Saved settings merged over the defaults.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_settings() {
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		return wp_parse_args( $saved, self::wps_wpr_get_default_settings() );
	}

	/**
	 * Sanitize raw (already unslashed) settings input.
	 *
	 * @param  array $input raw input.
	 * @return array
	 */
	public static function wps_wpr_sanitize_settings( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::wps_wpr_get_default_settings();
		$yes_no   = static function ( $key ) use ( $input ) {
			return ( ! empty( $input[ $key ] ) && 'yes' === $input[ $key ] ) ? 'yes' : 'no';
		};

		$position = isset( $input['wps_wpr_fw_position'] ) ? sanitize_key( $input['wps_wpr_fw_position'] ) : '';
		$show_to  = isset( $input['wps_wpr_fw_show_to'] ) ? sanitize_key( $input['wps_wpr_fw_show_to'] ) : '';
		$color    = isset( $input['wps_wpr_fw_color'] ) ? sanitize_hex_color( $input['wps_wpr_fw_color'] ) : '';
		$label    = isset( $input['wps_wpr_fw_label'] ) ? sanitize_text_field( $input['wps_wpr_fw_label'] ) : '';
		$icon     = isset( $input['wps_wpr_fw_icon'] ) ? sanitize_text_field( $input['wps_wpr_fw_icon'] ) : '';
		$offset   = isset( $input['wps_wpr_fw_offset'] ) ? absint( $input['wps_wpr_fw_offset'] ) : $defaults['wps_wpr_fw_offset'];
		$mode     = isset( $input['wps_wpr_fw_mode'] ) ? sanitize_key( $input['wps_wpr_fw_mode'] ) : '';
		$target   = isset( $input['wps_wpr_fw_targeting'] ) ? sanitize_key( $input['wps_wpr_fw_targeting'] ) : '';
		$page_ids = isset( $input['wps_wpr_fw_page_ids'] ) && class_exists( 'WPS_WPR_Rewards_Launcher' ) ? WPS_WPR_Rewards_Launcher::wps_wpr_parse_page_ids( $input['wps_wpr_fw_page_ids'] ) : array();
		$welcome  = isset( $input['wps_wpr_fw_welcome'] ) ? sanitize_text_field( $input['wps_wpr_fw_welcome'] ) : $defaults['wps_wpr_fw_welcome'];

		return array(
			'wps_wpr_fw_enable'        => $yes_no( 'wps_wpr_fw_enable' ),
			'wps_wpr_fw_position'      => in_array( $position, array( 'bottom-right', 'bottom-left' ), true ) ? $position : $defaults['wps_wpr_fw_position'],
			'wps_wpr_fw_color'         => $color ? $color : $defaults['wps_wpr_fw_color'],
			'wps_wpr_fw_label'         => '' !== $label ? mb_substr( $label, 0, 40 ) : $defaults['wps_wpr_fw_label'],
			'wps_wpr_fw_icon'          => mb_substr( $icon, 0, 4 ),
			'wps_wpr_fw_show_to'       => in_array( $show_to, array( 'all', 'logged_in' ), true ) ? $show_to : $defaults['wps_wpr_fw_show_to'],
			'wps_wpr_fw_hide_cart'     => $yes_no( 'wps_wpr_fw_hide_cart' ),
			'wps_wpr_fw_hide_checkout' => $yes_no( 'wps_wpr_fw_hide_checkout' ),
			'wps_wpr_fw_show_mobile'   => $yes_no( 'wps_wpr_fw_show_mobile' ),
			'wps_wpr_fw_offset'        => min( 400, $offset ),
			'wps_wpr_fw_mode'          => in_array( $mode, array( 'compact', 'launcher' ), true ) ? $mode : $defaults['wps_wpr_fw_mode'],
			'wps_wpr_fw_tab_home'      => $yes_no( 'wps_wpr_fw_tab_home' ),
			'wps_wpr_fw_tab_earn'      => $yes_no( 'wps_wpr_fw_tab_earn' ),
			'wps_wpr_fw_tab_redeem'    => $yes_no( 'wps_wpr_fw_tab_redeem' ),
			'wps_wpr_fw_tab_activity'  => $yes_no( 'wps_wpr_fw_tab_activity' ),
			'wps_wpr_fw_tab_refer'     => $yes_no( 'wps_wpr_fw_tab_refer' ),
			'wps_wpr_fw_targeting'     => in_array( $target, array( 'all', 'include', 'exclude' ), true ) ? $target : $defaults['wps_wpr_fw_targeting'],
			'wps_wpr_fw_page_ids'      => implode( ',', $page_ids ),
			'wps_wpr_fw_welcome'       => mb_substr( $welcome, 0, 120 ),
		);
	}

	/**
	 * Whether the widget should be shown on the current request.
	 *
	 * @return bool
	 */
	public function wps_wpr_should_display() {
		$settings = self::wps_wpr_get_settings();
		$display  = 'yes' === $settings['wps_wpr_fw_enable'];

		if ( $display && ( is_admin() || ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) || wp_doing_ajax() ) ) {
			$display = false;
		}
		if ( $display && 'logged_in' === $settings['wps_wpr_fw_show_to'] && ! is_user_logged_in() ) {
			$display = false;
		}
		if ( $display && is_user_logged_in() && function_exists( 'wps_wpr_restrict_user_fun' ) && wps_wpr_restrict_user_fun() ) {
			$display = false;
		}
		if ( $display && 'yes' === $settings['wps_wpr_fw_hide_cart'] && function_exists( 'is_cart' ) && is_cart() ) {
			$display = false;
		}
		if ( $display && 'yes' === $settings['wps_wpr_fw_hide_checkout'] && function_exists( 'is_checkout' ) && is_checkout() ) {
			$display = false;
		}

		/**
		 * Filter whether the floating loyalty widget is displayed.
		 *
		 * @param bool  $display  display flag.
		 * @param array $settings widget settings.
		 */
		return (bool) apply_filters( 'wps_wpr_floating_widget_display', $display, $settings );
	}

	/**
	 * Enqueue the widget assets (only when it will be displayed).
	 *
	 * @return void
	 */
	public function wps_wpr_enqueue_assets() {
		if ( ! $this->wps_wpr_should_display() ) {
			return;
		}
		$version = defined( 'REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION' ) ? REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION : '1.0.0';
		wp_enqueue_style( self::HANDLE, WPS_RWPR_DIR_URL . 'public/css/wps-wpr-floating-widget.css', array(), $version );
		wp_enqueue_script( self::HANDLE, WPS_RWPR_DIR_URL . 'public/js/wps-wpr-floating-widget.js', array(), $version, true );
		$data = array(
			'copied' => __( 'Copied!', 'points-and-rewards-for-woocommerce' ),
			'copy'   => __( 'Copy', 'points-and-rewards-for-woocommerce' ),
		);
		if ( $this->wps_wpr_is_launcher_mode() ) {
			$data = array_merge( $data, WPS_WPR_Rewards_Launcher::wps_wpr_get_script_data() );
		}
		wp_localize_script( self::HANDLE, 'wpsWprFloatingWidget', $data );
	}

	/**
	 * Whether the widget runs as the full rewards launcher (tabbed panel).
	 *
	 * @param  array|null $settings widget settings (defaults to the saved ones).
	 * @return bool
	 */
	public function wps_wpr_is_launcher_mode( $settings = null ) {
		$settings = is_array( $settings ) ? $settings : self::wps_wpr_get_settings();
		return 'launcher' === $settings['wps_wpr_fw_mode'] && class_exists( 'WPS_WPR_Rewards_Launcher' );
	}

	/**
	 * Output the widget in the footer.
	 *
	 * @return void
	 */
	public function wps_wpr_render() {
		if ( ! $this->wps_wpr_should_display() ) {
			return;
		}
		echo $this->wps_wpr_get_markup( get_current_user_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped while building.
	}

	/**
	 * General points settings value.
	 *
	 * @param  string $key setting key.
	 * @return mixed
	 */
	protected function wps_wpr_general_setting( $key ) {
		$settings = get_option( 'wps_wpr_settings_gallery', array() );
		return ( is_array( $settings ) && isset( $settings[ $key ] ) ) ? $settings[ $key ] : '';
	}

	/**
	 * Ways to earn points, built from the live settings.
	 *
	 * @return string[] plain-text lines.
	 */
	public function wps_wpr_get_earn_items() {
		$items = array();

		$signup_points = absint( $this->wps_wpr_general_setting( 'wps_wpr_general_signup_value' ) );
		if ( $this->wps_wpr_general_setting( 'wps_wpr_general_signup' ) && $signup_points > 0 ) {
			/* translators: %s: points */
			$items['signup'] = sprintf( __( 'Sign up: %s points', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $signup_points ) );
		}

		$refer_points = absint( $this->wps_wpr_general_setting( 'wps_wpr_general_refer_value' ) );
		if ( $this->wps_wpr_general_setting( 'wps_wpr_general_refer_enable' ) && $refer_points > 0 ) {
			/* translators: %s: points */
			$items['referral'] = sprintf( __( 'Refer a friend: %s points', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $refer_points ) );
		}

		$coupon = get_option( 'wps_wpr_coupons_gallery', array() );
		$coupon = is_array( $coupon ) ? $coupon : array();
		if ( ! empty( $coupon['wps_wpr_coupon_conversion_enable'] ) ) {
			// Points earned = order total * conversion_price / conversion_points (see public order points logic).
			$points_per = ! empty( $coupon['wps_wpr_coupon_conversion_price'] ) ? (float) $coupon['wps_wpr_coupon_conversion_price'] : 1;
			$per_amount = ! empty( $coupon['wps_wpr_coupon_conversion_points'] ) ? (float) $coupon['wps_wpr_coupon_conversion_points'] : 1;
			$items['purchase'] = sprintf(
				/* translators: 1: points, 2: amount */
				__( 'Shop: %1$s points for every %2$s spent', 'points-and-rewards-for-woocommerce' ),
				number_format_i18n( $points_per ),
				wp_strip_all_tags( wc_price( $per_amount ) )
			);
		}

		/**
		 * Filter the "ways to earn" lines shown in the floating widget.
		 *
		 * @param string[] $items lines keyed by source.
		 */
		return (array) apply_filters( 'wps_wpr_floating_widget_earn_items', $items );
	}

	/**
	 * Referral link for a user (creates the referral key if missing).
	 *
	 * @param  int $user_id user id.
	 * @return string
	 */
	public function wps_wpr_get_referral_link( $user_id ) {
		if ( ! $this->wps_wpr_general_setting( 'wps_wpr_general_refer_enable' ) ) {
			return '';
		}
		$key = get_user_meta( $user_id, 'wps_points_referral', true );
		if ( empty( $key ) && function_exists( 'wps_wpr_create_referral_code' ) ) {
			$key = wps_wpr_create_referral_code();
			update_user_meta( $user_id, 'wps_points_referral', $key );
		}
		if ( empty( $key ) ) {
			return '';
		}
		$page = $this->wps_wpr_general_setting( 'wps_wpr_referral_page' );
		$base = ( ! empty( $page ) && is_array( $page ) ) ? get_page_link( $page[0] ) : site_url();
		$base = apply_filters( 'wps_wpr_referral_link_url', $base );
		return $base . '?pkey=' . rawurlencode( $key );
	}

	/**
	 * Membership level name for a user.
	 *
	 * @param  int $user_id user id.
	 * @return string
	 */
	public function wps_wpr_get_membership_level( $user_id ) {
		$level = get_user_meta( $user_id, 'membership_level', true );
		if ( empty( $level ) ) {
			return '';
		}
		$settings = get_option( 'wps_wpr_membership_settings', array() );
		$roles    = ( is_array( $settings ) && ! empty( $settings['membership_roles'] ) && is_array( $settings['membership_roles'] ) ) ? $settings['membership_roles'] : array();
		return isset( $roles[ $level ] ) ? (string) $level : '';
	}

	/**
	 * Panel sections for a user.
	 *
	 * @param  int $user_id user id (0 for guests).
	 * @return array section key => HTML.
	 */
	public function wps_wpr_get_sections( $user_id ) {
		$sections = array();

		if ( $user_id ) {
			$points    = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
			$level     = $this->wps_wpr_get_membership_level( $user_id );
			$balance   = '<div class="wps-wpr-fw__balance"><span class="wps-wpr-fw__balance-label">' . esc_html__( 'Your points', 'points-and-rewards-for-woocommerce' ) . '</span>';
			$balance  .= '<strong class="wps-wpr-fw__balance-value" data-wps-wpr-fw-balance>' . esc_html( number_format_i18n( $points ) ) . '</strong>';
			if ( '' !== $level ) {
				/* translators: %s: membership level */
				$balance .= '<span class="wps-wpr-fw__level">' . esc_html( sprintf( __( 'Level: %s', 'points-and-rewards-for-woocommerce' ), $level ) ) . '</span>';
			}
			$balance            .= '</div>';
			$sections['balance'] = $balance;
		} else {
			$signup_points = absint( $this->wps_wpr_general_setting( 'wps_wpr_general_signup_value' ) );
			$heading       = ( $this->wps_wpr_general_setting( 'wps_wpr_general_signup' ) && $signup_points > 0 )
				/* translators: %s: points */
				? sprintf( __( 'Join and earn %s points', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $signup_points ) )
				: __( 'Join our rewards program', 'points-and-rewards-for-woocommerce' );
			$account_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();

			$sections['join'] = '<div class="wps-wpr-fw__join"><strong class="wps-wpr-fw__join-title">' . esc_html( $heading ) . '</strong>'
				. '<p>' . esc_html__( 'Create an account to start earning points on every purchase.', 'points-and-rewards-for-woocommerce' ) . '</p>'
				. '<a class="wps-wpr-fw__button" href="' . esc_url( $account_url ) . '">' . esc_html__( 'Sign up / Log in', 'points-and-rewards-for-woocommerce' ) . '</a></div>';
		}

		$earn = $this->wps_wpr_get_earn_items();
		if ( ! empty( $earn ) ) {
			$list = '';
			foreach ( $earn as $line ) {
				$list .= '<li>' . esc_html( $line ) . '</li>';
			}
			$sections['earn'] = '<div class="wps-wpr-fw__earn"><span class="wps-wpr-fw__section-title">' . esc_html__( 'Ways to earn', 'points-and-rewards-for-woocommerce' ) . '</span><ul>' . $list . '</ul></div>';
		}

		if ( $user_id ) {
			$referral = $this->wps_wpr_get_referral_link( $user_id );
			if ( '' !== $referral ) {
				$sections['referral'] = '<div class="wps-wpr-fw__referral"><span class="wps-wpr-fw__section-title">' . esc_html__( 'Your referral link', 'points-and-rewards-for-woocommerce' ) . '</span>'
					. '<div class="wps-wpr-fw__referral-row"><input type="text" readonly class="wps-wpr-fw__referral-input" value="' . esc_attr( $referral ) . '" aria-label="' . esc_attr__( 'Your referral link', 'points-and-rewards-for-woocommerce' ) . '">'
					. '<button type="button" class="wps-wpr-fw__copy" data-wps-wpr-fw-copy>' . esc_html__( 'Copy', 'points-and-rewards-for-woocommerce' ) . '</button></div></div>';
			}

			$points_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'points' ) : '';
			if ( $points_url ) {
				$sections['account'] = '<a class="wps-wpr-fw__button" href="' . esc_url( $points_url ) . '">' . esc_html__( 'View points & history', 'points-and-rewards-for-woocommerce' ) . '</a>';
			}
		}

		/**
		 * Filter the floating widget panel sections.
		 *
		 * Each value is escaped HTML; keys are section ids (balance, join, earn,
		 * referral, account). Add, remove or reorder entries to customise the panel.
		 *
		 * @param array $sections section key => HTML.
		 * @param int   $user_id  current user id (0 for guests).
		 */
		return (array) apply_filters( 'wps_wpr_floating_widget_sections', $sections, $user_id );
	}

	/**
	 * Full widget markup.
	 *
	 * @param  int $user_id user id (0 for guests).
	 * @return string
	 */
	public function wps_wpr_get_markup( $user_id ) {
		$settings = self::wps_wpr_get_settings();
		$sections = $this->wps_wpr_get_sections( absint( $user_id ) );

		$classes = array( 'wps-wpr-fw', 'wps-wpr-fw--' . $settings['wps_wpr_fw_position'] );
		if ( 'yes' !== $settings['wps_wpr_fw_show_mobile'] ) {
			$classes[] = 'wps-wpr-fw--hide-mobile';
		}
		$style = sprintf( '--wps-wpr-fw-color:%s;--wps-wpr-fw-offset:%dpx;', $settings['wps_wpr_fw_color'], absint( $settings['wps_wpr_fw_offset'] ) );

		$body = '';
		if ( $this->wps_wpr_is_launcher_mode( $settings ) ) {
			$classes[] = 'wps-wpr-fw--launcher';
			$launcher  = new WPS_WPR_Rewards_Launcher( $this );
			$body      = $launcher->wps_wpr_get_panel_body( absint( $user_id ), $settings, $sections );
		} else {
			foreach ( $sections as $key => $html ) {
				$body .= '<div class="wps-wpr-fw__section wps-wpr-fw__section--' . esc_attr( sanitize_html_class( $key ) ) . '">' . $html . '</div>';
			}
		}

		$icon  = '' !== $settings['wps_wpr_fw_icon'] ? '<span class="wps-wpr-fw__icon" aria-hidden="true">' . esc_html( $settings['wps_wpr_fw_icon'] ) . '</span>' : '';
		$title = $user_id ? __( 'Your rewards', 'points-and-rewards-for-woocommerce' ) : __( 'Rewards', 'points-and-rewards-for-woocommerce' );

		$html  = '<div id="wps-wpr-floating-widget" class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">';
		$html .= '<div class="wps-wpr-fw__panel" id="wps-wpr-fw-panel" role="dialog" aria-modal="false" aria-labelledby="wps-wpr-fw-title" hidden tabindex="-1">';
		$html .= '<div class="wps-wpr-fw__header"><span class="wps-wpr-fw__title" id="wps-wpr-fw-title">' . esc_html( $title ) . '</span>';
		$html .= '<button type="button" class="wps-wpr-fw__close" data-wps-wpr-fw-close aria-label="' . esc_attr__( 'Close rewards panel', 'points-and-rewards-for-woocommerce' ) . '">&times;</button></div>';
		$html .= '<div class="wps-wpr-fw__body">' . $body . '</div></div>';
		$html .= '<button type="button" class="wps-wpr-fw__launcher" aria-expanded="false" aria-controls="wps-wpr-fw-panel">' . $icon . '<span class="wps-wpr-fw__label">' . esc_html( $settings['wps_wpr_fw_label'] ) . '</span></button>';
		$html .= '</div>';

		/**
		 * Filter the complete floating widget markup.
		 *
		 * @param string $html     markup.
		 * @param int    $user_id  user id.
		 * @param array  $settings widget settings.
		 */
		return (string) apply_filters( 'wps_wpr_floating_widget_markup', $html, $user_id, $settings );
	}
}
