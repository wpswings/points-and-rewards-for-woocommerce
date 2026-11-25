<?php
/**
 * Display / priority ordering for campaigns and rewards.
 *
 * Admins drag campaigns into the order they want (or type an order number)
 * on the Campaigning tab. The campaign modal then renders its earn items in
 * that order. The generic helpers below can sort any keyed list, so other
 * reward lists (e.g. a reward catalog) can adopt the same ordering.
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

if ( ! function_exists( 'wps_wpr_sort_items_by_order' ) ) {
	/**
	 * Sort a keyed list by admin-defined order numbers.
	 *
	 * Lower numbers come first. Items without an order number keep their
	 * original relative position and go after the numbered ones. Ties keep
	 * their original relative position too, so the sort is stable.
	 *
	 * @param array $items      items keyed by id, in their default order.
	 * @param array $priorities order numbers keyed by the same ids.
	 * @return array sorted items, keys preserved.
	 */
	function wps_wpr_sort_items_by_order( $items, $priorities = array() ) {
		$items      = is_array( $items ) ? $items : array();
		$priorities = is_array( $priorities ) ? $priorities : array();

		$rank     = array();
		$position = 0;
		foreach ( $items as $key => $item ) {
			$has_order    = isset( $priorities[ $key ] ) && '' !== $priorities[ $key ] && is_numeric( $priorities[ $key ] );
			$rank[ $key ] = array( $has_order ? (int) $priorities[ $key ] : PHP_INT_MAX, $position++ );
		}

		uksort(
			$items,
			function ( $a, $b ) use ( $rank ) {
				if ( $rank[ $a ][0] === $rank[ $b ][0] ) {
					return $rank[ $a ][1] <=> $rank[ $b ][1];
				}
				return $rank[ $a ][0] <=> $rank[ $b ][0];
			}
		);

		/**
		 * Filter a list after it has been sorted by order number.
		 *
		 * @param array $items      sorted items.
		 * @param array $priorities order numbers used.
		 */
		return apply_filters( 'wps_wpr_sort_items_by_order', $items, $priorities );
	}
}

if ( ! function_exists( 'wps_wpr_sort_items_by_field' ) ) {
	/**
	 * Sort a list of array items by an order field stored on each item.
	 *
	 * A plain (0-indexed) list is returned re-indexed; a keyed list keeps
	 * its keys.
	 *
	 * @param array  $items items.
	 * @param string $field item field that holds the order number.
	 * @return array
	 */
	function wps_wpr_sort_items_by_field( $items, $field = 'order' ) {
		if ( ! is_array( $items ) || empty( $items ) ) {
			return is_array( $items ) ? $items : array();
		}

		$priorities = array();
		foreach ( $items as $key => $item ) {
			if ( is_array( $item ) && isset( $item[ $field ] ) ) {
				$priorities[ $key ] = $item[ $field ];
			} elseif ( is_object( $item ) && isset( $item->{$field} ) ) {
				$priorities[ $key ] = $item->{$field};
			}
		}

		$is_list = array_keys( $items ) === range( 0, count( $items ) - 1 );
		$sorted  = wps_wpr_sort_items_by_order( $items, $priorities );

		return $is_list ? array_values( $sorted ) : $sorted;
	}
}

/**
 * Campaign ordering.
 */
class WPS_WPR_Campaign_Ordering {

	/**
	 * Key inside the `wps_wpr_campaign_settings` option that stores order numbers.
	 */
	const SETTING_KEY = 'wps_wpr_campaign_priority';

	/**
	 * Largest order number accepted.
	 */
	const MAX_ORDER = 9999;

	/**
	 * Built-in campaign items in their default (legacy) display order.
	 *
	 * Social share tasks follow these, in the order they are configured.
	 *
	 * @return array key => label.
	 */
	public static function wps_wpr_get_core_campaigns() {
		$campaigns = array(
			'birthday'    => __( 'Birthday Points', 'points-and-rewards-for-woocommerce' ),
			'comments'    => __( 'Comments Points', 'points-and-rewards-for-woocommerce' ),
			'spin_wheel'  => __( 'Spin Wheel', 'points-and-rewards-for-woocommerce' ),
			'first_order' => __( 'First Order Points', 'points-and-rewards-for-woocommerce' ),
			'quiz'        => __( 'Quiz Contest', 'points-and-rewards-for-woocommerce' ),
		);

		/**
		 * Filter the built-in campaign items that can be ordered.
		 *
		 * @param array $campaigns key => label.
		 */
		return apply_filters( 'wps_wpr_orderable_core_campaigns', $campaigns );
	}

	/**
	 * Campaign settings option as an array.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_campaign_settings() {
		$settings = get_option( 'wps_wpr_campaign_settings', array() );
		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Saved order numbers.
	 *
	 * @param array|null $settings campaign settings (read from the option when null).
	 * @return array key => int.
	 */
	public static function wps_wpr_get_priorities( $settings = null ) {
		$settings = is_array( $settings ) ? $settings : self::wps_wpr_get_campaign_settings();
		$saved    = ! empty( $settings[ self::SETTING_KEY ] ) && is_array( $settings[ self::SETTING_KEY ] ) ? $settings[ self::SETTING_KEY ] : array();
		return self::wps_wpr_sanitize_priorities( $saved );
	}

	/**
	 * Sanitize posted order numbers.
	 *
	 * @param mixed $raw raw value, key => number.
	 * @return array key => int, empty values dropped.
	 */
	public static function wps_wpr_sanitize_priorities( $raw ) {
		$clean = array();
		if ( ! is_array( $raw ) ) {
			return $clean;
		}

		foreach ( $raw as $key => $value ) {
			$key   = sanitize_key( $key );
			$value = is_scalar( $value ) ? trim( (string) $value ) : '';
			if ( '' === $key || '' === $value || ! is_numeric( $value ) ) {
				continue;
			}
			$clean[ $key ] = min( self::MAX_ORDER, absint( $value ) );
		}

		return $clean;
	}

	/**
	 * All orderable campaign items for the admin list, sorted by saved order.
	 *
	 * @param array $social_types social share type labels (key => label).
	 * @param array $settings     campaign settings.
	 * @return array key => label.
	 */
	public static function wps_wpr_get_admin_items( $social_types = array(), $settings = null ) {
		$settings = is_array( $settings ) ? $settings : self::wps_wpr_get_campaign_settings();
		$items    = self::wps_wpr_get_core_campaigns();

		$configured = ! empty( $settings['wps_wpr_social_share_campaign_label'] ) && is_array( $settings['wps_wpr_social_share_campaign_label'] ) ? $settings['wps_wpr_social_share_campaign_label'] : array();
		foreach ( $configured as $social_key ) {
			$social_key = sanitize_key( $social_key );
			if ( '' === $social_key || isset( $items[ $social_key ] ) ) {
				continue;
			}
			$items[ $social_key ] = isset( $social_types[ $social_key ] ) ? $social_types[ $social_key ] : $social_key;
		}

		return wps_wpr_sort_items_by_order( $items, self::wps_wpr_get_priorities( $settings ) );
	}

	/**
	 * Sort rendered campaign blocks by the saved order.
	 *
	 * @param array $blocks key => html, in the default order.
	 * @return array
	 */
	public static function wps_wpr_sort_campaign_blocks( $blocks ) {
		$blocks = is_array( $blocks ) ? $blocks : array();
		$blocks = array_filter(
			$blocks,
			function ( $html ) {
				return '' !== trim( (string) $html );
			}
		);
		return wps_wpr_sort_items_by_order( $blocks, self::wps_wpr_get_priorities() );
	}

	/**
	 * Sort reward lists that carry an `order` field (e.g. reward catalog rewards).
	 *
	 * Lists without order numbers come back unchanged.
	 *
	 * @param array $rewards rewards.
	 * @return array
	 */
	public static function wps_wpr_sort_rewards( $rewards ) {
		return wps_wpr_sort_items_by_field( $rewards, 'order' );
	}

	/**
	 * Enqueue the sortable list script on the Campaigning tab.
	 *
	 * @return void
	 */
	public function wps_wpr_enqueue_admin_assets() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'wps-rwpr-setting' !== $page || 'wps-campaign-settings' !== $tab ) {
			return;
		}

		wp_enqueue_script(
			'wps-wpr-campaign-ordering',
			WPS_RWPR_DIR_URL . 'admin/js/wps-wpr-campaign-ordering.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION,
			true
		);
	}
}
