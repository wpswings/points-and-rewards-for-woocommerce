<?php
/**
 * Reusable condition engine.
 *
 * Evaluates a list of conditions (user role, billing country, order count,
 * lifetime spend, language, membership level, order total) against a context
 * made of a user and, optionally, an order. Other features can use it to
 * target rules at specific customers. New condition types can be added with
 * the `wps_wpr_condition_types` filter.
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
 * Condition engine.
 */
class WPS_WPR_Condition_Engine {

	/**
	 * Lifetime spend per user for the current request.
	 *
	 * @var array
	 */
	private static $lifetime_spend_cache = array();

	/**
	 * Operators available for list conditions.
	 *
	 * @return array
	 */
	public static function wps_wpr_list_operators() {
		return array(
			'in'     => __( 'is any of', 'points-and-rewards-for-woocommerce' ),
			'not_in' => __( 'is none of', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Operators available for number conditions.
	 *
	 * @return array
	 */
	public static function wps_wpr_number_operators() {
		return array(
			'>=' => __( 'at least', 'points-and-rewards-for-woocommerce' ),
			'<=' => __( 'at most', 'points-and-rewards-for-woocommerce' ),
			'>'  => __( 'more than', 'points-and-rewards-for-woocommerce' ),
			'<'  => __( 'less than', 'points-and-rewards-for-woocommerce' ),
			'='  => __( 'exactly', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Registered condition types.
	 *
	 * Each type has a label, a kind ("list" or "number"), a resolver that
	 * returns the actual value for a context, an optional `needs_order` flag
	 * and an optional `compare` callable for custom list matching.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_condition_types() {
		$types = array(
			'user_role'      => array(
				'label'    => __( 'User role', 'points-and-rewards-for-woocommerce' ),
				'kind'     => 'list',
				'hint'     => __( 'Role slugs, e.g. customer, subscriber', 'points-and-rewards-for-woocommerce' ),
				'resolver' => array( __CLASS__, 'wps_wpr_resolve_user_role' ),
			),
			'billing_country' => array(
				'label'    => __( 'Billing country', 'points-and-rewards-for-woocommerce' ),
				'kind'     => 'list',
				'hint'     => __( 'Two-letter country codes, e.g. US, GB, IN', 'points-and-rewards-for-woocommerce' ),
				'resolver' => array( __CLASS__, 'wps_wpr_resolve_billing_country' ),
			),
			'order_count'    => array(
				'label'    => __( 'Lifetime paid orders', 'points-and-rewards-for-woocommerce' ),
				'kind'     => 'number',
				'resolver' => array( __CLASS__, 'wps_wpr_resolve_order_count' ),
			),
			'lifetime_spend' => array(
				'label'    => __( 'Lifetime spend', 'points-and-rewards-for-woocommerce' ),
				'kind'     => 'number',
				'resolver' => array( __CLASS__, 'wps_wpr_resolve_lifetime_spend' ),
			),
			'language'       => array(
				'label'    => __( 'Language', 'points-and-rewards-for-woocommerce' ),
				'kind'     => 'list',
				'hint'     => __( 'Locales or language codes, e.g. en_US, fr, de_DE', 'points-and-rewards-for-woocommerce' ),
				'resolver' => array( __CLASS__, 'wps_wpr_resolve_language' ),
				'compare'  => array( __CLASS__, 'wps_wpr_compare_language' ),
			),
			'membership'     => array(
				'label'    => __( 'Membership level', 'points-and-rewards-for-woocommerce' ),
				'kind'     => 'list',
				'hint'     => __( 'Membership level names', 'points-and-rewards-for-woocommerce' ),
				'resolver' => array( __CLASS__, 'wps_wpr_resolve_membership' ),
			),
			'order_total'    => array(
				'label'       => __( 'Order total', 'points-and-rewards-for-woocommerce' ),
				'kind'        => 'number',
				'needs_order' => true,
				'resolver'    => array( __CLASS__, 'wps_wpr_resolve_order_total' ),
			),
		);

		return (array) apply_filters( 'wps_wpr_condition_types', $types );
	}

	/**
	 * Evaluate a set of conditions.
	 *
	 * @param array  $conditions list of array( 'type', 'operator', 'value' ).
	 * @param string $match      "all" (AND) or "any" (OR).
	 * @param array  $context    array( 'user_id' => int, 'order' => WC_Order|null, 'skip_order_conditions' => bool ).
	 * @return bool  true when there are no conditions.
	 */
	public static function wps_wpr_evaluate( $conditions, $match = 'all', $context = array() ) {
		$conditions = is_array( $conditions ) ? array_values( array_filter( $conditions, 'is_array' ) ) : array();
		if ( empty( $conditions ) ) {
			return true;
		}

		$context = wp_parse_args(
			$context,
			array(
				'user_id'               => 0,
				'order'                 => null,
				'skip_order_conditions' => false,
			)
		);

		$results = array();
		$skipped = false;
		foreach ( $conditions as $condition ) {
			$result = self::wps_wpr_evaluate_condition( $condition, $context );
			if ( null === $result ) {
				$skipped = true; // Skipped (e.g. order condition outside an order).
				continue;
			}
			$results[] = $result;
		}

		if ( empty( $results ) ) {
			return true;
		}

		if ( 'any' === $match ) {
			// A skipped condition could still pass on a future order, so "any" may pass.
			$passed = $skipped || in_array( true, $results, true );
		} else {
			$passed = ! in_array( false, $results, true );
		}

		return (bool) apply_filters( 'wps_wpr_conditions_passed', $passed, $conditions, $match, $context );
	}

	/**
	 * Evaluate one condition.
	 *
	 * @param array $condition condition.
	 * @param array $context   context.
	 * @return bool|null null when the condition is skipped.
	 */
	public static function wps_wpr_evaluate_condition( $condition, $context ) {
		$types = self::wps_wpr_get_condition_types();
		$type  = isset( $condition['type'] ) ? $condition['type'] : '';
		if ( ! isset( $types[ $type ] ) || ! is_callable( $types[ $type ]['resolver'] ) ) {
			return false; // Unknown conditions never pass.
		}

		$definition = $types[ $type ];
		if ( ! empty( $definition['needs_order'] ) && empty( $context['order'] ) ) {
			return ! empty( $context['skip_order_conditions'] ) ? null : false;
		}

		$operator = isset( $condition['operator'] ) ? (string) $condition['operator'] : '';
		$expected = isset( $condition['value'] ) ? $condition['value'] : '';
		$actual   = call_user_func( $definition['resolver'], $context );

		if ( 'number' === $definition['kind'] ) {
			$result = self::wps_wpr_compare_numbers( (float) $actual, $operator, (float) $expected );
		} else {
			$expected = self::wps_wpr_to_list( $expected );
			$actual   = self::wps_wpr_to_list( $actual );
			if ( ! empty( $definition['compare'] ) && is_callable( $definition['compare'] ) ) {
				$matches = (bool) call_user_func( $definition['compare'], $actual, $expected );
			} else {
				$matches = (bool) array_intersect( array_map( 'strtolower', $actual ), array_map( 'strtolower', $expected ) );
			}
			$result = 'not_in' === $operator ? ! $matches : $matches;
		}

		return (bool) apply_filters( 'wps_wpr_evaluate_condition', $result, $condition, $context, $actual );
	}

	/**
	 * Compare two numbers.
	 *
	 * @param float  $actual   actual value.
	 * @param string $operator operator.
	 * @param float  $expected expected value.
	 * @return bool
	 */
	public static function wps_wpr_compare_numbers( $actual, $operator, $expected ) {
		switch ( $operator ) {
			case '<=':
				return $actual <= $expected;
			case '>':
				return $actual > $expected;
			case '<':
				return $actual < $expected;
			case '=':
				return abs( $actual - $expected ) < 0.00001;
			case '>=':
			default:
				return $actual >= $expected;
		}
	}

	/**
	 * Turn a comma separated string or array into a clean list.
	 *
	 * @param mixed $value value.
	 * @return array
	 */
	public static function wps_wpr_to_list( $value ) {
		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}
		$value = is_array( $value ) ? $value : array( $value );
		$value = array_map( 'trim', array_map( 'strval', $value ) );
		return array_values( array_filter( $value, 'strlen' ) );
	}

	/**
	 * Order statuses that count as paid for lifetime stats.
	 *
	 * @return array
	 */
	public static function wps_wpr_paid_statuses() {
		$statuses = function_exists( 'wc_get_is_paid_statuses' ) ? wc_get_is_paid_statuses() : array( 'processing', 'completed' );
		if ( function_exists( 'wps_wpr_get_points_award_status' ) ) {
			$statuses[] = wps_wpr_get_points_award_status();
		}
		return array_values( array_unique( $statuses ) );
	}

	/**
	 * User roles.
	 *
	 * @param array $context context.
	 * @return array
	 */
	public static function wps_wpr_resolve_user_role( $context ) {
		$user = get_userdata( absint( $context['user_id'] ) );
		return $user ? (array) $user->roles : array();
	}

	/**
	 * Billing country: order first, then the customer's saved address.
	 *
	 * @param array $context context.
	 * @return string
	 */
	public static function wps_wpr_resolve_billing_country( $context ) {
		if ( ! empty( $context['order'] ) && is_a( $context['order'], 'WC_Order' ) && $context['order']->get_billing_country() ) {
			return $context['order']->get_billing_country();
		}
		return (string) get_user_meta( absint( $context['user_id'] ), 'billing_country', true );
	}

	/**
	 * Lifetime paid order count.
	 *
	 * @param array $context context.
	 * @return int
	 */
	public static function wps_wpr_resolve_order_count( $context ) {
		$user_id = absint( $context['user_id'] );
		if ( ! $user_id ) {
			return 0;
		}
		$ids = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'status'      => self::wps_wpr_paid_statuses(),
				'limit'       => -1,
				'return'      => 'ids',
				'type'        => 'shop_order',
			)
		);
		return count( $ids );
	}

	/**
	 * Lifetime spend (net of refunds) on paid orders.
	 *
	 * Uses order IDs plus one aggregate query instead of loading every order.
	 * WooCommerce's own customer total spent is not used because it is gross
	 * of refunds, ignores a custom points award status and is cached in user
	 * meta. The value is memoized per user for the current request.
	 *
	 * @param array $context context.
	 * @return float
	 */
	public static function wps_wpr_resolve_lifetime_spend( $context ) {
		$user_id = absint( $context['user_id'] );
		if ( ! $user_id ) {
			return 0.0;
		}
		if ( isset( self::$lifetime_spend_cache[ $user_id ] ) ) {
			return self::$lifetime_spend_cache[ $user_id ];
		}
		$ids = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'status'      => self::wps_wpr_paid_statuses(),
				'limit'       => -1,
				'return'      => 'ids',
				'type'        => 'shop_order',
			)
		);
		self::$lifetime_spend_cache[ $user_id ] = self::wps_wpr_sum_net_totals( $ids );
		return self::$lifetime_spend_cache[ $user_id ];
	}

	/**
	 * Clear the per-request caches (call when orders change).
	 *
	 * @return void
	 */
	public static function wps_wpr_flush_cache() {
		self::$lifetime_spend_cache = array();
	}

	/**
	 * Sum of order totals net of refunds, without loading order objects.
	 *
	 * Works with both HPOS and posts storage. Refund orders store a negative
	 * total, so adding them gives the net amount.
	 *
	 * @param int[] $order_ids order ids.
	 * @return float
	 */
	public static function wps_wpr_sum_net_totals( $order_ids ) {
		global $wpdb;
		$order_ids = array_values( array_filter( array_map( 'absint', (array) $order_ids ) ) );
		if ( empty( $order_ids ) ) {
			return 0.0;
		}

		$hpos  = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
		$total = 0.0;
		foreach ( array_chunk( $order_ids, 500 ) as $chunk ) {
			$in = implode( ',', $chunk ); // Integers only (absint above).
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
			if ( $hpos ) {
				$table  = $wpdb->prefix . 'wc_orders';
				$total += (float) $wpdb->get_var( "SELECT SUM(total_amount) FROM {$table} WHERE id IN ({$in})" );
				$total += (float) $wpdb->get_var( "SELECT SUM(total_amount) FROM {$table} WHERE type = 'shop_order_refund' AND parent_order_id IN ({$in})" );
			} else {
				$total += (float) $wpdb->get_var( "SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = '_order_total' AND post_id IN ({$in})" );
				$total += (float) $wpdb->get_var( "SELECT SUM(pm.meta_value) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_order_total' WHERE p.post_type = 'shop_order_refund' AND p.post_parent IN ({$in})" );
			}
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		}
		return max( 0.0, $total );
	}

	/**
	 * Language of the customer: order language (WPML), the user's own locale,
	 * then (on the storefront only) the current WPML / Polylang language, and
	 * finally the site locale.
	 *
	 * The request language is not used in wp-admin, cron, REST or webhooks,
	 * where it is the admin's language rather than the customer's.
	 *
	 * @param array $context context.
	 * @return string
	 */
	public static function wps_wpr_resolve_language( $context ) {
		if ( ! empty( $context['order'] ) && is_a( $context['order'], 'WC_Order' ) ) {
			$order_language = $context['order']->get_meta( 'wpml_language' );
			if ( $order_language ) {
				return (string) $order_language;
			}
		}

		$user_id = absint( $context['user_id'] );
		if ( $user_id ) {
			$user_locale = get_user_meta( $user_id, 'locale', true );
			if ( $user_locale ) {
				return (string) $user_locale;
			}
		}

		$language   = '';
		$storefront = ! is_admin() && ! wp_doing_cron() && ! wp_doing_ajax() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) && ! ( defined( 'WP_CLI' ) && WP_CLI );
		if ( $storefront ) {
			$language = apply_filters( 'wpml_current_language', null );
			if ( empty( $language ) && function_exists( 'pll_current_language' ) ) {
				$language = pll_current_language( 'locale' );
			}
		}
		if ( empty( $language ) ) {
			$language = $user_id ? get_user_locale( $user_id ) : determine_locale();
		}
		return (string) $language;
	}

	/**
	 * Match languages: "fr" matches "fr_FR" and the other way round.
	 *
	 * @param array $actual   actual languages.
	 * @param array $expected expected languages.
	 * @return bool
	 */
	public static function wps_wpr_compare_language( $actual, $expected ) {
		foreach ( $actual as $language ) {
			$language = strtolower( str_replace( '-', '_', $language ) );
			$short    = strtok( $language, '_' );
			foreach ( $expected as $wanted ) {
				$wanted = strtolower( str_replace( '-', '_', $wanted ) );
				if ( $wanted === $language || $wanted === $short || strtok( $wanted, '_' ) === $language ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Membership level name.
	 *
	 * @param array $context context.
	 * @return string
	 */
	public static function wps_wpr_resolve_membership( $context ) {
		return (string) get_user_meta( absint( $context['user_id'] ), 'membership_level', true );
	}

	/**
	 * Order total (net of refunds).
	 *
	 * @param array $context context.
	 * @return float
	 */
	public static function wps_wpr_resolve_order_total( $context ) {
		$order = $context['order'];
		return (float) $order->get_total() - (float) $order->get_total_refunded();
	}
}
