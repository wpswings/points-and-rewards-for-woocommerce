<?php
/**
 * Persistent rewards launcher.
 *
 * Turns the floating loyalty widget (see WPS_WPR_Floating_Widget) into a
 * chat-style launcher: a larger slide-up panel with tabs for Home, Earn,
 * Redeem, Activity and Refer. It reuses the widget's single bubble button, so
 * there is never a second launcher on the page, and it stays separate from
 * the campaign popup (the launcher hides itself while that popup is open).
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
 * Rewards launcher (full mode of the floating widget).
 */
class WPS_WPR_Rewards_Launcher {

	/**
	 * AJAX action that returns the activity tab.
	 */
	const AJAX_ACTION = 'wps_wpr_launcher_activity';

	/**
	 * Nonce action for the activity request.
	 */
	const NONCE_ACTION = 'wps-wpr-launcher-activity';

	/**
	 * Number of activity entries shown.
	 */
	const ACTIVITY_LIMIT = 10;

	/**
	 * Floating widget instance used for shared data (balance, earn items, referral link).
	 *
	 * @var WPS_WPR_Floating_Widget
	 */
	protected $widget;

	/**
	 * Constructor.
	 *
	 * @param WPS_WPR_Floating_Widget|null $widget floating widget instance.
	 */
	public function __construct( $widget = null ) {
		$this->widget = $widget instanceof WPS_WPR_Floating_Widget ? $widget : new WPS_WPR_Floating_Widget();
	}

	/**
	 * Available tabs (key => label), in display order.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_all_tabs() {
		return array(
			'home'     => __( 'Home', 'points-and-rewards-for-woocommerce' ),
			'earn'     => __( 'Earn', 'points-and-rewards-for-woocommerce' ),
			'redeem'   => __( 'Redeem', 'points-and-rewards-for-woocommerce' ),
			'activity' => __( 'Activity', 'points-and-rewards-for-woocommerce' ),
			'refer'    => __( 'Refer', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Tabs enabled in the settings for a user.
	 *
	 * Activity and Refer are personal, so guests never get them.
	 *
	 * @param  array $settings widget settings.
	 * @param  int   $user_id  user id (0 for guests).
	 * @return array key => label.
	 */
	public static function wps_wpr_get_enabled_tabs( $settings, $user_id ) {
		$tabs = array();
		foreach ( self::wps_wpr_get_all_tabs() as $key => $label ) {
			$setting = 'wps_wpr_fw_tab_' . $key;
			if ( isset( $settings[ $setting ] ) && 'yes' !== $settings[ $setting ] ) {
				continue;
			}
			if ( ! $user_id && in_array( $key, array( 'activity', 'refer' ), true ) ) {
				continue;
			}
			$tabs[ $key ] = $label;
		}

		// Home is the fallback so the panel is never empty.
		if ( empty( $tabs ) ) {
			$tabs['home'] = __( 'Home', 'points-and-rewards-for-woocommerce' );
		}

		/**
		 * Filter the launcher tabs.
		 *
		 * @param array $tabs     key => label.
		 * @param int   $user_id  user id (0 for guests).
		 * @param array $settings widget settings.
		 */
		return (array) apply_filters( 'wps_wpr_launcher_tabs', $tabs, $user_id, $settings );
	}

	/**
	 * Parse a list of page IDs ("12, 34 56") into unique positive integers.
	 *
	 * @param  string|array $ids raw ids.
	 * @return int[]
	 */
	public static function wps_wpr_parse_page_ids( $ids ) {
		if ( is_array( $ids ) ) {
			$ids = implode( ',', $ids );
		}
		$parts = preg_split( '/[\s,]+/', (string) $ids, -1, PREG_SPLIT_NO_EMPTY );
		$ids   = array_filter( array_map( 'absint', (array) $parts ) );
		return array_values( array_unique( $ids ) );
	}

	/**
	 * Whether page targeting allows the widget for a queried object.
	 *
	 * @param  int   $object_id queried object id (0 for archives, search, etc).
	 * @param  array $settings  widget settings.
	 * @return bool
	 */
	public static function wps_wpr_page_targeting_allows( $object_id, $settings ) {
		$mode = isset( $settings['wps_wpr_fw_targeting'] ) ? $settings['wps_wpr_fw_targeting'] : 'all';
		$ids  = self::wps_wpr_parse_page_ids( isset( $settings['wps_wpr_fw_page_ids'] ) ? $settings['wps_wpr_fw_page_ids'] : '' );

		if ( 'include' === $mode ) {
			return in_array( absint( $object_id ), $ids, true );
		}
		if ( 'exclude' === $mode ) {
			return ! in_array( absint( $object_id ), $ids, true );
		}
		return true;
	}

	/**
	 * Queried object id, mapping the WooCommerce shop archive to the shop page.
	 *
	 * @return int
	 */
	public static function wps_wpr_get_current_object_id() {
		if ( function_exists( 'is_shop' ) && is_shop() && function_exists( 'wc_get_page_id' ) ) {
			return absint( wc_get_page_id( 'shop' ) );
		}
		if ( is_singular() || is_page() ) {
			return absint( get_queried_object_id() );
		}
		return 0;
	}

	/**
	 * Apply page targeting to the widget display decision.
	 *
	 * Hooked on `wps_wpr_floating_widget_display`.
	 *
	 * @param  bool  $display  display flag.
	 * @param  array $settings widget settings.
	 * @return bool
	 */
	public function wps_wpr_filter_display( $display, $settings ) {
		if ( ! $display ) {
			return false;
		}
		return self::wps_wpr_page_targeting_allows( self::wps_wpr_get_current_object_id(), $settings );
	}

	/**
	 * Log keys whose amounts are deductions.
	 *
	 * @return string[]
	 */
	public static function wps_wpr_get_deduction_keys() {
		$keys = array(
			'Coupon_details',
			'refund_points_on_order',
			'refund_payment_points_details',
			'refund_subscription__renewal_points',
			'cancel_points_on_order_total',
			'membership',
			'pur_by_points',
			'deduction_of_points',
			'deduction_currency_spent',
			'cart_subtotal_point',
			'refund_member_assign_rewards_points',
			'membership_level_points_refunded',
			'abandoned_cart_points_reversed',
			'partial_refund_points_deducted',
			'order_rewards_points_reversed',
			'api_membership_logs',
			'expired_details',
			'deduct_currency_pnt_cancel',
			'deduct_bcz_cancel',
			'pur_pro_pnt_only',
			'Sender_point_details',
			'points_reset_by_admin',
			'points_deduct_wallet',
			'reset_users_points_logs',
			'reward_catalog_redeemed',
		);

		/**
		 * Filter the points log keys treated as deductions in the launcher activity.
		 *
		 * @param string[] $keys log keys.
		 */
		return (array) apply_filters( 'wps_wpr_launcher_deduction_keys', $keys );
	}

	/**
	 * Human label for a points log key.
	 *
	 * @param  string $key log key.
	 * @return string
	 */
	public static function wps_wpr_get_activity_label( $key ) {
		$labels = array(
			'registration'               => __( 'Sign-up bonus', 'points-and-rewards-for-woocommerce' ),
			'points_on_order'            => __( 'Order points', 'points-and-rewards-for-woocommerce' ),
			'order__rewards_points'      => __( 'Order rewards', 'points-and-rewards-for-woocommerce' ),
			'product_details'            => __( 'Product purchase points', 'points-and-rewards-for-woocommerce' ),
			'reference_details'          => __( 'Referral points', 'points-and-rewards-for-woocommerce' ),
			'ref_product_detail'         => __( 'Referral purchase points', 'points-and-rewards-for-woocommerce' ),
			'comment'                    => __( 'Review points', 'points-and-rewards-for-woocommerce' ),
			'admin_points'               => __( 'Adjusted by the store', 'points-and-rewards-for-woocommerce' ),
			'cart_subtotal_point'        => __( 'Points redeemed on an order', 'points-and-rewards-for-woocommerce' ),
			'expired_details'            => __( 'Points expired', 'points-and-rewards-for-woocommerce' ),
			'abandoned_cart_points'      => __( 'Cart recovery bonus', 'points-and-rewards-for-woocommerce' ),
			'reward_catalog_redeemed'    => __( 'Reward redeemed', 'points-and-rewards-for-woocommerce' ),
			'social_share_points_log'    => __( 'Social share points', 'points-and-rewards-for-woocommerce' ),
			'quiz_points_log'            => __( 'Quiz points', 'points-and-rewards-for-woocommerce' ),
			'game_claim_points'          => __( 'Game points', 'points-and-rewards-for-woocommerce' ),
			'user_badges_rewards_points' => __( 'Badge reward', 'points-and-rewards-for-woocommerce' ),
		);

		if ( isset( $labels[ $key ] ) ) {
			$label = $labels[ $key ];
		} else {
			$label = ucfirst( trim( preg_replace( '/\s+/', ' ', str_replace( '_', ' ', (string) $key ) ) ) );
		}

		/**
		 * Filter the label shown for a points log key in the launcher activity.
		 *
		 * @param string $label label.
		 * @param string $key   log key.
		 */
		return (string) apply_filters( 'wps_wpr_launcher_activity_label', $label, $key );
	}

	/**
	 * Most recent points log entries for a user, newest first.
	 *
	 * @param  int $user_id user id.
	 * @param  int $limit   max entries.
	 * @return array[] each { key, label, points (signed int), date (string), timestamp }.
	 */
	public static function wps_wpr_get_recent_activity( $user_id, $limit = self::ACTIVITY_LIMIT ) {
		$log = get_user_meta( absint( $user_id ), 'points_details', true );
		if ( empty( $log ) || ! is_array( $log ) ) {
			return array();
		}

		$deductions = self::wps_wpr_get_deduction_keys();
		$entries    = array();
		$sequence   = 0;

		foreach ( $log as $key => $rows ) {
			if ( ! is_array( $rows ) ) {
				continue;
			}
			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) || ! isset( $row[ $key ] ) || ! is_numeric( $row[ $key ] ) ) {
					continue;
				}
				$amount = abs( (float) $row[ $key ] );
				if ( $amount <= 0 ) {
					continue;
				}
				$negative = in_array( $key, $deductions, true ) || ( isset( $row['sign'] ) && '-' === $row['sign'] );
				$date     = isset( $row['date'] ) ? (string) $row['date'] : '';
				$time     = '' !== $date ? strtotime( $date ) : false;

				$entries[] = array(
					'key'       => (string) $key,
					'label'     => self::wps_wpr_get_activity_label( $key ),
					'points'    => (int) round( $negative ? -$amount : $amount ),
					'date'      => $date,
					'timestamp' => false === $time ? 0 : $time,
					'sequence'  => $sequence++,
				);
			}
		}

		usort(
			$entries,
			static function ( $a, $b ) {
				if ( $a['timestamp'] === $b['timestamp'] ) {
					return $b['sequence'] - $a['sequence'];
				}
				return $b['timestamp'] - $a['timestamp'];
			}
		);

		$entries = array_slice( $entries, 0, max( 1, absint( $limit ) ) );
		foreach ( $entries as &$entry ) {
			unset( $entry['sequence'] );
		}
		unset( $entry );

		/**
		 * Filter the launcher activity entries.
		 *
		 * @param array $entries entries.
		 * @param int   $user_id user id.
		 */
		return (array) apply_filters( 'wps_wpr_launcher_activity', $entries, $user_id );
	}

	/**
	 * Activity list HTML.
	 *
	 * @param  array $entries entries from wps_wpr_get_recent_activity().
	 * @return string
	 */
	public static function wps_wpr_get_activity_html( $entries ) {
		if ( empty( $entries ) ) {
			return '<p class="wps-wpr-fw__empty">' . esc_html__( 'No points activity yet.', 'points-and-rewards-for-woocommerce' ) . '</p>';
		}

		$html = '<ul class="wps-wpr-fw__activity-list">';
		foreach ( $entries as $entry ) {
			$points = (int) $entry['points'];
			$class  = $points < 0 ? 'wps-wpr-fw__activity-points--minus' : 'wps-wpr-fw__activity-points--plus';
			$sign   = $points < 0 ? '-' : '+';
			$date   = $entry['timestamp'] ? date_i18n( get_option( 'date_format' ), $entry['timestamp'] ) : $entry['date'];

			$html .= '<li class="wps-wpr-fw__activity-item">'
				. '<span class="wps-wpr-fw__activity-text"><span class="wps-wpr-fw__activity-label">' . esc_html( $entry['label'] ) . '</span>'
				. '<span class="wps-wpr-fw__activity-date">' . esc_html( $date ) . '</span></span>'
				. '<span class="wps-wpr-fw__activity-points ' . esc_attr( $class ) . '">' . esc_html( $sign . number_format_i18n( abs( $points ) ) ) . '</span>'
				. '</li>';
		}
		$html .= '</ul>';

		return $html;
	}

	/**
	 * AJAX: activity tab for the current user only.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_activity() {
		if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Your session has expired. Please reload the page.', 'points-and-rewards-for-woocommerce' ) ), 403 );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to see your points activity.', 'points-and-rewards-for-woocommerce' ) ), 401 );
		}

		$entries = self::wps_wpr_get_recent_activity( $user_id );
		wp_send_json_success(
			array(
				'html'  => self::wps_wpr_get_activity_html( $entries ),
				'count' => count( $entries ),
			)
		);
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
	 * Rewards from the reward catalog (WPS-8322), when that feature is available.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_catalog_rewards() {
		$rewards = array();
		if ( class_exists( 'WPS_WPR_Reward_Catalog' )
			&& method_exists( 'WPS_WPR_Reward_Catalog', 'wps_wpr_is_enabled' )
			&& method_exists( 'WPS_WPR_Reward_Catalog', 'wps_wpr_get_available_rewards' )
			&& WPS_WPR_Reward_Catalog::wps_wpr_is_enabled() ) {
			$rewards = (array) WPS_WPR_Reward_Catalog::wps_wpr_get_available_rewards();
		}

		/**
		 * Filter the rewards listed in the launcher Redeem tab.
		 *
		 * Each reward needs at least `title` and `points`.
		 *
		 * @param array $rewards rewards.
		 */
		$rewards = (array) apply_filters( 'wps_wpr_launcher_rewards', $rewards );

		uasort(
			$rewards,
			static function ( $a, $b ) {
				return absint( isset( $a['points'] ) ? $a['points'] : 0 ) - absint( isset( $b['points'] ) ? $b['points'] : 0 );
			}
		);

		return $rewards;
	}

	/**
	 * The cheapest reward the user cannot afford yet.
	 *
	 * @param  int   $balance points balance.
	 * @param  array $rewards rewards.
	 * @return array|null
	 */
	public static function wps_wpr_get_next_reward( $balance, $rewards ) {
		foreach ( $rewards as $reward ) {
			if ( isset( $reward['points'] ) && absint( $reward['points'] ) > absint( $balance ) ) {
				return $reward;
			}
		}
		return null;
	}

	/**
	 * Welcome line with [NAME] replaced.
	 *
	 * @param  array $settings widget settings.
	 * @param  int   $user_id  user id.
	 * @return string plain text.
	 */
	public function wps_wpr_get_welcome( $settings, $user_id ) {
		$text = isset( $settings['wps_wpr_fw_welcome'] ) ? (string) $settings['wps_wpr_fw_welcome'] : '';
		$name = '';
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			$name = $user ? ( $user->first_name ? $user->first_name : $user->display_name ) : '';
		}
		if ( ! $user_id || '' === $name ) {
			$text = trim( preg_replace( '/,?\s*\[NAME\]/', '', $text ) );
		}
		return trim( str_replace( '[NAME]', $name, $text ) );
	}

	/**
	 * Tab panel HTML for one tab.
	 *
	 * @param  string $tab      tab key.
	 * @param  int    $user_id  user id (0 for guests).
	 * @param  array  $settings widget settings.
	 * @param  array  $sections compact widget sections (balance, join, earn, referral, account).
	 * @return string
	 */
	public function wps_wpr_get_tab_content( $tab, $user_id, $settings, $sections ) {
		$html = '';

		switch ( $tab ) {
			case 'home':
				$welcome = $this->wps_wpr_get_welcome( $settings, $user_id );
				if ( '' !== $welcome ) {
					$html .= '<p class="wps-wpr-fw__welcome">' . esc_html( $welcome ) . '</p>';
				}
				if ( $user_id ) {
					$html   .= isset( $sections['balance'] ) ? $sections['balance'] : '';
					$balance = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
					$next    = self::wps_wpr_get_next_reward( $balance, self::wps_wpr_get_catalog_rewards() );
					if ( $next ) {
						$needed = absint( $next['points'] ) - $balance;
						$pct    = absint( $next['points'] ) > 0 ? min( 100, (int) floor( $balance * 100 / absint( $next['points'] ) ) ) : 0;
						$html  .= '<div class="wps-wpr-fw__next">'
							/* translators: 1: points still needed, 2: reward title */
							. '<span>' . esc_html( sprintf( __( '%1$s more points to unlock %2$s', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $needed ), isset( $next['title'] ) ? $next['title'] : '' ) ) . '</span>'
							. '<span class="wps-wpr-fw__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . esc_attr( $pct ) . '"><span style="width:' . esc_attr( $pct ) . '%"></span></span>'
							. '</div>';
					}
					$html .= isset( $sections['account'] ) ? $sections['account'] : '';
				} else {
					$html .= isset( $sections['join'] ) ? $sections['join'] : '';
				}
				break;

			case 'earn':
				$html .= isset( $sections['earn'] ) ? $sections['earn'] : '<p class="wps-wpr-fw__empty">' . esc_html__( 'Ways to earn will appear here soon.', 'points-and-rewards-for-woocommerce' ) . '</p>';
				break;

			case 'redeem':
				$html .= $this->wps_wpr_get_redeem_html( $user_id );
				break;

			case 'activity':
				$html .= '<div class="wps-wpr-fw__activity" data-wps-wpr-fw-activity aria-live="polite"><p class="wps-wpr-fw__loading">' . esc_html__( 'Loading your activity…', 'points-and-rewards-for-woocommerce' ) . '</p></div>';
				break;

			case 'refer':
				if ( isset( $sections['referral'] ) ) {
					$html .= $sections['referral'];
					$link  = $this->widget->wps_wpr_get_referral_link( $user_id );
					$html .= '<div class="wps-wpr-fw__share">'
						. '<a class="wps-wpr-fw__share-link" target="_blank" rel="noopener noreferrer" href="' . esc_url( 'https://wa.me/?text=' . rawurlencode( $link ) ) . '">' . esc_html__( 'WhatsApp', 'points-and-rewards-for-woocommerce' ) . '</a>'
						. '<a class="wps-wpr-fw__share-link" target="_blank" rel="noopener noreferrer" href="' . esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $link ) ) . '">' . esc_html__( 'Facebook', 'points-and-rewards-for-woocommerce' ) . '</a>'
						. '<a class="wps-wpr-fw__share-link" href="' . esc_url( 'mailto:?body=' . rawurlencode( $link ) ) . '">' . esc_html__( 'Email', 'points-and-rewards-for-woocommerce' ) . '</a>'
						. '</div>';
				} else {
					$html .= '<p class="wps-wpr-fw__empty">' . esc_html__( 'The referral program is not active right now.', 'points-and-rewards-for-woocommerce' ) . '</p>';
				}
				break;
		}

		/**
		 * Filter one launcher tab's HTML.
		 *
		 * @param string $html     escaped HTML.
		 * @param string $tab      tab key.
		 * @param int    $user_id  user id (0 for guests).
		 * @param array  $settings widget settings.
		 */
		return (string) apply_filters( 'wps_wpr_launcher_tab_content', $html, $tab, $user_id, $settings );
	}

	/**
	 * Redeem tab HTML.
	 *
	 * @param  int $user_id user id (0 for guests).
	 * @return string
	 */
	protected function wps_wpr_get_redeem_html( $user_id ) {
		$rewards    = self::wps_wpr_get_catalog_rewards();
		$balance    = $user_id ? absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) : 0;
		$points_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'points' ) : '';
		$html       = '';

		if ( ! empty( $rewards ) ) {
			$html .= '<ul class="wps-wpr-fw__rewards">';
			foreach ( $rewards as $reward ) {
				$cost      = absint( isset( $reward['points'] ) ? $reward['points'] : 0 );
				$available = $user_id && $balance >= $cost;
				$html     .= '<li class="wps-wpr-fw__reward' . ( $available ? ' wps-wpr-fw__reward--available' : '' ) . '">'
					. '<span class="wps-wpr-fw__reward-title">' . esc_html( isset( $reward['title'] ) ? $reward['title'] : '' ) . '</span>'
					/* translators: %s: points */
					. '<span class="wps-wpr-fw__reward-cost">' . esc_html( sprintf( __( '%s points', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $cost ) ) ) . '</span>'
					. '</li>';
			}
			$html .= '</ul>';
			if ( $user_id && $points_url ) {
				$html .= '<a class="wps-wpr-fw__button" href="' . esc_url( $points_url ) . '">' . esc_html__( 'Redeem a reward', 'points-and-rewards-for-woocommerce' ) . '</a>';
			}
		} elseif ( $this->wps_wpr_general_setting( 'wps_wpr_custom_points_on_cart' ) ) {
			$points_rate = (float) $this->wps_wpr_general_setting( 'wps_wpr_cart_points_rate' );
			$price_rate  = (float) $this->wps_wpr_general_setting( 'wps_wpr_cart_price_rate' );
			$points_rate = $points_rate > 0 ? $points_rate : 1;
			$price_rate  = $price_rate > 0 ? $price_rate : 1;
			$html       .= '<p class="wps-wpr-fw__redeem-rate">'
				. esc_html(
					sprintf(
						/* translators: 1: points, 2: amount */
						__( 'Use your points on the cart page: %1$s points = %2$s off.', 'points-and-rewards-for-woocommerce' ),
						number_format_i18n( $points_rate ),
						wp_strip_all_tags( wc_price( $price_rate ) )
					)
				)
				. '</p>';
			if ( function_exists( 'wc_get_cart_url' ) ) {
				$html .= '<a class="wps-wpr-fw__button" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'Go to cart', 'points-and-rewards-for-woocommerce' ) . '</a>';
			}
		} else {
			$html .= '<p class="wps-wpr-fw__empty">' . esc_html__( 'Rewards will appear here soon.', 'points-and-rewards-for-woocommerce' ) . '</p>';
		}

		if ( ! $user_id && ( ! empty( $rewards ) || $this->wps_wpr_general_setting( 'wps_wpr_custom_points_on_cart' ) ) ) {
			$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
			$html       .= '<p class="wps-wpr-fw__hint"><a href="' . esc_url( $account_url ) . '">' . esc_html__( 'Log in to redeem your points', 'points-and-rewards-for-woocommerce' ) . '</a></p>';
		}

		return $html;
	}

	/**
	 * Launcher panel markup (tabs + panels).
	 *
	 * @param  int   $user_id  user id (0 for guests).
	 * @param  array $settings widget settings.
	 * @param  array $sections compact widget sections.
	 * @return string
	 */
	public function wps_wpr_get_panel_body( $user_id, $settings, $sections ) {
		$tabs  = self::wps_wpr_get_enabled_tabs( $settings, $user_id );
		$first = true;

		$tablist = '<div class="wps-wpr-fw__tabs" role="tablist" aria-label="' . esc_attr__( 'Rewards sections', 'points-and-rewards-for-woocommerce' ) . '">';
		$panels  = '';
		foreach ( $tabs as $key => $label ) {
			$key      = sanitize_key( $key );
			$tab_id   = 'wps-wpr-fw-tab-' . $key;
			$panel_id = 'wps-wpr-fw-tabpanel-' . $key;

			$tablist .= '<button type="button" role="tab" class="wps-wpr-fw__tab" id="' . esc_attr( $tab_id ) . '" data-wps-wpr-fw-tab="' . esc_attr( $key ) . '"'
				. ' aria-controls="' . esc_attr( $panel_id ) . '" aria-selected="' . ( $first ? 'true' : 'false' ) . '" tabindex="' . ( $first ? '0' : '-1' ) . '">'
				. esc_html( $label ) . '</button>';

			$panels .= '<div class="wps-wpr-fw__tabpanel" role="tabpanel" id="' . esc_attr( $panel_id ) . '" aria-labelledby="' . esc_attr( $tab_id ) . '" tabindex="0"' . ( $first ? '' : ' hidden' ) . '>'
				. $this->wps_wpr_get_tab_content( $key, $user_id, $settings, $sections )
				. '</div>';

			$first = false;
		}
		$tablist .= '</div>';

		return $tablist . '<div class="wps-wpr-fw__tabpanels">' . $panels . '</div>';
	}

	/**
	 * Script data needed in launcher mode.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_script_data() {
		return array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'activityNonce' => is_user_logged_in() ? wp_create_nonce( self::NONCE_ACTION ) : '',
			'activityError' => __( 'Could not load your activity. Please try again.', 'points-and-rewards-for-woocommerce' ),
		);
	}
}
