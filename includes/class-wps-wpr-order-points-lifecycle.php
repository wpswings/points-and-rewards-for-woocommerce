<?php
/**
 * Order points lifecycle: re-complete after cancel / refund, partial refunds,
 * Order Rewards reversal and product points for admin / REST created orders.
 *
 * The award and reversal logic itself lives in the public classes of the free and
 * Pro plugins. This class only keeps their per-order flags consistent so that:
 * - an order moved back from cancelled / refunded to a live status earns again and
 *   pays again for the points it redeemed (once per round trip);
 * - partial refunds take back a proportional share of the earned points, and a later
 *   full cancel / refund never takes back more than was awarded;
 * - Order Rewards points are taken back when the order that triggered them is cancelled;
 * - line items added outside the cart (admin / REST) get their product points.
 *
 * @link       https://wpswings.com/
 * @since      2.10.5
 *
 * @package    points-and-rewards-for-woocommerce
 * @subpackage points-and-rewards-for-woocommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order points lifecycle class.
 */
class WPS_WPR_Order_Points_Lifecycle {

	/**
	 * Order meta: redeemed points given back on cancel / refund, keyed by the flag that
	 * marked the give-back. Used to take them again when the order is re-completed.
	 */
	const RETURNED_REDEEM_META = 'wps_wpr_returned_redeem_points';

	/**
	 * Order meta: partial refund tracking.
	 */
	const PARTIAL_REFUND_META = 'wps_wpr_partial_refund_points';

	/**
	 * Order meta: Order Rewards points awarded when this order reached the order limit.
	 */
	const ORDER_REWARDS_META = 'wps_wpr_order_rewards_awarded_points';

	/**
	 * Order meta: number of orders counted for the Order Rewards award.
	 */
	const ORDER_REWARDS_COUNT_META = 'wps_wpr_order_rewards_order_count';

	/**
	 * Order meta: Order Rewards points of this order were taken back.
	 */
	const ORDER_REWARDS_REVERSED_META = 'wps_wpr_order_rewards_reversed';

	/**
	 * Log key: points taken back for a partial refund.
	 */
	const PARTIAL_LOG_KEY = 'partial_refund_points_deducted';

	/**
	 * Log key: partial refund deduction given back because the full order was reversed.
	 */
	const PARTIAL_RESTORE_LOG_KEY = 'partial_refund_points_restored';

	/**
	 * Log key: Order Rewards points taken back on cancel / refund.
	 */
	const ORDER_REWARDS_REVERSAL_LOG_KEY = 'order_rewards_points_reversed';

	/**
	 * Register hooks owned by this class.
	 */
	public function __construct() {
		// Before the award handlers (priority 10 / 11): clear cancel flags and take redeemed points again.
		add_action( 'woocommerce_order_status_changed', array( $this, 'wps_wpr_restore_reversed_order_points' ), 1, 3 );
		// Before the award handlers: product points for line items created outside the cart.
		add_action( 'woocommerce_order_status_changed', array( $this, 'wps_wpr_add_missing_item_points' ), 5, 3 );
		// After the cancel handlers.
		add_action( 'woocommerce_order_status_changed', array( $this, 'wps_wpr_reverse_order_rewards_points' ), 25, 3 );
		// After every full reversal handler.
		add_action( 'woocommerce_order_status_changed', array( $this, 'wps_wpr_settle_partial_refunds_on_reversal' ), 99, 3 );
		add_action( 'woocommerce_order_partially_refunded', array( $this, 'wps_wpr_deduct_partial_refund_points' ), 10, 2 );
	}

	/**
	 * Remember redeemed points that were given back to the customer on cancel / refund,
	 * so they can be taken again if the order is re-completed.
	 *
	 * @param int    $order_id order id.
	 * @param string $flag_key order meta key that marks the give-back as done.
	 * @param int    $points   points given back.
	 * @param string $log_type points log type to use when the points are taken again.
	 * @return void
	 */
	public static function wps_wpr_track_returned_redeem_points( $order_id, $flag_key, $points, $log_type ) {
		$points = (int) round( (float) $points );
		$order  = wc_get_order( $order_id );
		if ( ! $order || $points <= 0 ) {
			return;
		}

		$returned = $order->get_meta( self::RETURNED_REDEEM_META );
		$returned = is_array( $returned ) ? $returned : array();
		$previous = isset( $returned[ $flag_key ]['points'] ) ? (int) $returned[ $flag_key ]['points'] : 0;

		$returned[ $flag_key ] = array(
			'points' => $previous + $points,
			'type'   => $log_type,
		);
		$order->update_meta_data( self::RETURNED_REDEEM_META, $returned );
		$order->save();
	}

	/**
	 * Order moved from cancelled / refunded back to a live status: clear the reversal flags
	 * (and the matching award flags) so the award handlers award again, and take back the
	 * redeemed points that were returned on cancel / refund.
	 *
	 * Only flags of points that were actually reversed are cleared, so toggling the status
	 * repeatedly never awards or deducts twice.
	 *
	 * @param int    $order_id   order id.
	 * @param string $old_status old status.
	 * @param string $new_status new status.
	 * @return void
	 */
	public function wps_wpr_restore_reversed_order_points( $order_id, $old_status, $new_status ) {
		if ( ! in_array( $old_status, array( 'cancelled', 'refunded' ), true ) ) {
			return;
		}

		$live_statuses = array( 'processing', 'on-hold', 'completed', wps_wpr_get_points_award_status() );
		if ( ! in_array( $new_status, $live_statuses, true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$user_id = absint( $order->get_user_id() );
		if ( empty( $user_id ) ) {
			return;
		}

		$restored = false;

		// Per currency spent points.
		$currency_flags = array( "$order_id#cancel_per_currency_spend_points", "$order_id#refund_per_currency_spend_points" );
		if ( $this->wps_wpr_order_has_flag( $order, $currency_flags, 'yes' ) ) {
			$this->wps_wpr_delete_order_meta( $order, array_merge( $currency_flags, array( "$order_id#item_conversion_id" ) ) );
			$restored = true;
		}

		// Product (assigned) points, per line item.
		foreach ( $order->get_items() as $item_id => $item ) {
			$item_flags = array( "$order_id#$item_id#cancel_points", "$order_id#$item_id#refund_points" );
			if ( ! $this->wps_wpr_order_has_flag( $order, $item_flags, 'yes' ) ) {
				continue;
			}

			$this->wps_wpr_delete_order_meta( $order, $item_flags );
			foreach ( $item->get_meta_data() as $item_meta ) {
				if ( 'Points' === $item_meta->key ) {
					$order->delete_meta_data( "$order_id#{$item_meta->id}#set" );
				}
			}
			$restored = true;
		}

		// Order total range points ( the single-quoted keys are the pre-2.10.5 literal keys ).
		$order_total_flags = array(
			"$order_id#wps_cancel_order_total_points",
			'$order_id#wps_cancel_order_total_points',
			"$order_id#wps_point_on_order_total",
			'$order_id#wps_point_on_order_total',
		);
		if ( $this->wps_wpr_order_has_flag( $order, $order_total_flags, 'yes' ) ) {
			$this->wps_wpr_delete_order_meta( $order, array_merge( $order_total_flags, array( "$order_id#points_assignedon_order_total", 'wps_wpr_order_total_awarded_points' ) ) );
			$restored = true;
		}

		// Referral purchase points of the referrer.
		$referral_flags = array( 'cancelled_refer_purchase_point', 'refunded_refer_purchase_point' );
		if ( $this->wps_wpr_order_has_flag( $order, $referral_flags, 'done' ) ) {
			$this->wps_wpr_delete_order_meta( $order, array_merge( $referral_flags, array( "$order_id#refral_conversion_id" ) ) );
			$restored = true;
		}

		if ( $restored ) {
			// Pro award idempotency token: the per-type flags above still guard every award.
			$order->delete_meta_data( '_wps_par_points_awarded' );
		}

		// Redeemed points that were returned on cancel / refund.
		$returned = $order->get_meta( self::RETURNED_REDEEM_META );
		$returned = is_array( $returned ) ? $returned : array();
		if ( 'done' === $order->get_meta( 'refunded_points_by_cart' ) && ! isset( $returned['refunded_points_by_cart'] ) ) {
			// Cancelled before this give-back was tracked.
			$returned['refunded_points_by_cart'] = array(
				'points' => absint( $order->get_meta( 'wps_cart_discount#points' ) ),
				'type'   => 'cart_subtotal_point',
			);
		}

		$deduct_by_type = array();
		foreach ( $returned as $flag_key => $entry ) {
			$points = isset( $entry['points'] ) ? absint( $entry['points'] ) : 0;
			$type   = ! empty( $entry['type'] ) ? $entry['type'] : 'cart_subtotal_point';
			$order->delete_meta_data( $flag_key );
			if ( $points > 0 ) {
				$deduct_by_type[ $type ] = ( isset( $deduct_by_type[ $type ] ) ? $deduct_by_type[ $type ] : 0 ) + $points;
			}
		}
		$order->delete_meta_data( self::RETURNED_REDEEM_META );

		if ( ! $restored && empty( $returned ) ) {
			return;
		}
		$order->save();

		$total_deduct = array_sum( $deduct_by_type );
		if ( $total_deduct > 0 ) {
			$user_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
			$new_points  = $user_points - $total_deduct;
			update_user_meta( $user_id, 'wps_wpr_points', $new_points );

			$points_log = get_user_meta( $user_id, 'points_details', true );
			$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();
			$today_date = date_i18n( 'Y-m-d h:i:sa' );
			foreach ( $deduct_by_type as $type => $points ) {
				$points_log[ $type ][] = array(
					$type      => $points,
					'date'     => $today_date,
					'order_id' => $order_id,
				);
			}
			update_user_meta( $user_id, 'points_details', $points_log );
			do_action( 'wps_wpr_update_points_log', $user_id );

			$order->add_order_note(
				/* translators: 1: points, 2: new status */
				sprintf( esc_html__( '%1$s redeemed points deducted again because the order was moved back to %2$s.', 'points-and-rewards-for-woocommerce' ), $total_deduct, wc_get_order_status_name( $new_status ) )
			);
		}

		// The award status mapping caches its decision per request; flags changed, so evaluate again.
		if ( function_exists( 'wps_wpr_reset_award_order_status_decision' ) ) {
			wps_wpr_reset_award_order_status_decision( $order_id );
		}
	}

	/**
	 * Line items created outside the cart (admin / REST) have no "Points" item meta, so the
	 * award handlers give them no product points. Add the meta from the product settings
	 * (same rule as the cart: product points enabled, variation or product points x quantity).
	 *
	 * @param int    $order_id   order id.
	 * @param string $old_status old status.
	 * @param string $new_status new status.
	 * @return void
	 */
	public function wps_wpr_add_missing_item_points( $order_id, $old_status, $new_status ) {
		if ( $old_status === $new_status || ! in_array( $new_status, array( 'completed', wps_wpr_get_points_award_status() ), true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || empty( $order->get_user_id() ) ) {
			return;
		}

		// Allow sites to keep product points for cart orders only.
		if ( ! apply_filters( 'wps_wpr_award_product_points_without_cart_meta', true, $order ) ) {
			return;
		}

		foreach ( $order->get_items() as $item_id => $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}

			$has_points_meta = false;
			foreach ( $item->get_meta_data() as $item_meta ) {
				if ( 'Points' === $item_meta->key ) {
					$has_points_meta = true;
					break;
				}
			}
			// Do not add points to items already reversed / handled.
			if ( $has_points_meta || 'yes' === $order->get_meta( "$order_id#$item_id#cancel_points" ) || 'yes' === $order->get_meta( "$order_id#$item_id#refund_points" ) ) {
				continue;
			}

			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$quantity     = (int) $item->get_quantity();
			if ( empty( $product_id ) || $quantity <= 0 ) {
				continue;
			}

			if ( 'yes' !== wps_wpr_hpos_get_meta_data( $product_id, 'wps_product_points_enable', true ) ) {
				continue;
			}

			if ( ! empty( $variation_id ) ) {
				$product_points = (int) wps_wpr_hpos_get_meta_data( $variation_id, 'wps_wpr_variable_points', true );
			} else {
				$product_points = (int) wps_wpr_hpos_get_meta_data( $product_id, 'wps_points_product_value', true );
			}

			$item_points = (int) apply_filters( 'wps_wpr_order_item_fallback_points', $product_points * $quantity, $item, $order );
			if ( $item_points <= 0 ) {
				continue;
			}

			$item->add_meta_data( 'Points', $item_points, true );
			$item->save();
		}
	}

	/**
	 * Take back the Order Rewards points when the order that triggered them is cancelled or
	 * refunded, and let the customer qualify again.
	 *
	 * Badge points are not handled here: badges are awarded from the customer's accumulated
	 * points with no per-order record, so they cannot be traced back to a single order.
	 *
	 * @param int    $order_id   order id.
	 * @param string $old_status old status.
	 * @param string $new_status new status.
	 * @return void
	 */
	public function wps_wpr_reverse_order_rewards_points( $order_id, $old_status, $new_status ) {
		if ( $old_status === $new_status || ! in_array( $new_status, array( 'cancelled', 'refunded' ), true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$awarded = absint( $order->get_meta( self::ORDER_REWARDS_META ) );
		if ( $awarded <= 0 || $order->get_meta( self::ORDER_REWARDS_REVERSED_META ) ) {
			return;
		}

		$user_id = absint( $order->get_user_id() );
		if ( empty( $user_id ) ) {
			return;
		}

		$user_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		$new_points  = $user_points - $awarded;
		update_user_meta( $user_id, 'wps_wpr_points', $new_points );

		// The order no longer counts, so the customer can qualify for the reward again.
		delete_user_meta( $user_id, 'wps_wpr_rewards_points_awarded_check' );
		$counted = absint( $order->get_meta( self::ORDER_REWARDS_COUNT_META ) );
		if ( $counted > 0 ) {
			$orders_count = absint( get_user_meta( $user_id, 'wps_wpr_orders_count', true ) );
			update_user_meta( $user_id, 'wps_wpr_orders_count', max( 0, $orders_count - $counted ) );
		}

		$order->update_meta_data( self::ORDER_REWARDS_REVERSED_META, $awarded );
		$order->save();

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ self::ORDER_REWARDS_REVERSAL_LOG_KEY ][] = array(
			self::ORDER_REWARDS_REVERSAL_LOG_KEY => $awarded,
			'date'                               => date_i18n( 'Y-m-d h:i:sa' ),
			'order_id'                           => $order_id,
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		$order->add_order_note(
			/* translators: %s: deducted points */
			sprintf( esc_html__( '%s Order Rewards points deducted because the order was cancelled or refunded.', 'points-and-rewards-for-woocommerce' ), $awarded )
		);
	}

	/**
	 * Partial refund: take back a share of the per currency and product points earned by the
	 * order. Totals are tracked on the order so repeated partial refunds never take back more
	 * than was awarded.
	 *
	 * @param int $order_id  order id.
	 * @param int $refund_id refund id.
	 * @return void
	 */
	public function wps_wpr_deduct_partial_refund_points( $order_id, $refund_id ) {
		$order  = wc_get_order( $order_id );
		$refund = wc_get_order( $refund_id );
		if ( ! $order || ! $refund || ! $refund instanceof WC_Order_Refund ) {
			return;
		}

		$user_id = absint( $order->get_user_id() );
		if ( empty( $user_id ) ) {
			return;
		}

		$tracked = $this->wps_wpr_get_partial_refund_tracking( $order );
		if ( in_array( (int) $refund_id, $tracked['refund_ids'], true ) ) {
			return;
		}

		// Cumulative refunded amount and quantities, tracked here to avoid stale order caches.
		$tracked['refund_ids'][]     = (int) $refund_id;
		$tracked['refunded_amount'] += (float) $refund->get_amount();
		foreach ( $refund->get_items() as $refund_item ) {
			$refunded_item_id = absint( $refund_item->get_meta( '_refunded_item_id' ) );
			if ( $refunded_item_id ) {
				$tracked['item_qty'][ $refunded_item_id ] = ( isset( $tracked['item_qty'][ $refunded_item_id ] ) ? $tracked['item_qty'][ $refunded_item_id ] : 0 ) + abs( (float) $refund_item->get_quantity() );
			}
		}

		$deduct      = 0;
		$order_total = (float) $order->get_total();

		// Per currency spent points: share of the refunded amount.
		$currency_reversed = $this->wps_wpr_order_has_flag( $order, array( "$order_id#cancel_per_currency_spend_points", "$order_id#refund_per_currency_spend_points" ), 'yes' );
		if ( $order_total > 0 && 'set' === $order->get_meta( "$order_id#item_conversion_id" ) && ! $currency_reversed ) {
			$awarded = $this->wps_wpr_get_awarded_currency_points( $user_id, $order_id );
			if ( $awarded > 0 ) {
				$ratio  = min( 1, $tracked['refunded_amount'] / $order_total );
				$target = min( $awarded, (int) round( $awarded * $ratio ) );
				$delta  = $target - (int) $tracked['currency'];
				if ( $delta > 0 ) {
					$deduct             += $delta;
					$tracked['currency'] = $target;
				}
			}
		}

		// Product points: share of the refunded quantity of each line item.
		foreach ( $order->get_items() as $item_id => $item ) {
			if ( empty( $tracked['item_qty'][ $item_id ] ) || $this->wps_wpr_order_has_flag( $order, array( "$order_id#$item_id#cancel_points", "$order_id#$item_id#refund_points" ), 'yes' ) ) {
				continue;
			}

			$quantity = (float) $item->get_quantity();
			if ( $quantity <= 0 ) {
				continue;
			}

			$item_points = 0;
			foreach ( $item->get_meta_data() as $item_meta ) {
				if ( 'Points' === $item_meta->key && 'set' === $order->get_meta( "$order_id#{$item_meta->id}#set" ) ) {
					$item_points += (int) $item_meta->value;
				}
			}
			if ( $item_points <= 0 ) {
				continue;
			}

			$ratio    = min( 1, $tracked['item_qty'][ $item_id ] / $quantity );
			$target   = min( $item_points, (int) round( $item_points * $ratio ) );
			$previous = isset( $tracked['items'][ $item_id ] ) ? (int) $tracked['items'][ $item_id ] : 0;
			if ( $target > $previous ) {
				$deduct                       += $target - $previous;
				$tracked['items'][ $item_id ] = $target;
			}
		}

		$order->update_meta_data( self::PARTIAL_REFUND_META, $tracked );
		$order->save();

		if ( $deduct <= 0 ) {
			return;
		}

		$user_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		update_user_meta( $user_id, 'wps_wpr_points', $user_points - $deduct );

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ self::PARTIAL_LOG_KEY ][] = array(
			self::PARTIAL_LOG_KEY => $deduct,
			'date'                => date_i18n( 'Y-m-d h:i:sa' ),
			'order_id'            => $order_id,
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		$order->add_order_note(
			/* translators: %s: deducted points */
			sprintf( esc_html__( '%s earned points deducted for the partial refund.', 'points-and-rewards-for-woocommerce' ), $deduct )
		);
	}

	/**
	 * After a full cancel / refund the reversal handlers take back all earned points of the
	 * order. Give back what partial refunds already took, so the total never exceeds the award.
	 *
	 * @param int    $order_id   order id.
	 * @param string $old_status old status.
	 * @param string $new_status new status.
	 * @return void
	 */
	public function wps_wpr_settle_partial_refunds_on_reversal( $order_id, $old_status, $new_status ) {
		if ( $old_status === $new_status || ! in_array( $new_status, array( 'cancelled', 'refunded' ), true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->get_meta( self::PARTIAL_REFUND_META ) ) {
			return;
		}

		$user_id = absint( $order->get_user_id() );
		if ( empty( $user_id ) ) {
			return;
		}

		$tracked = $this->wps_wpr_get_partial_refund_tracking( $order );
		$credit  = 0;

		if ( $tracked['currency'] > 0 && $this->wps_wpr_order_has_flag( $order, array( "$order_id#cancel_per_currency_spend_points", "$order_id#refund_per_currency_spend_points" ), 'yes' ) ) {
			$credit             += (int) $tracked['currency'];
			$tracked['currency'] = 0;
		}

		foreach ( $tracked['items'] as $item_id => $points ) {
			if ( $points > 0 && $this->wps_wpr_order_has_flag( $order, array( "$order_id#$item_id#cancel_points", "$order_id#$item_id#refund_points" ), 'yes' ) ) {
				$credit                      += (int) $points;
				$tracked['items'][ $item_id ] = 0;
			}
		}

		if ( $credit <= 0 ) {
			return;
		}

		$order->update_meta_data( self::PARTIAL_REFUND_META, $tracked );
		$order->save();

		$user_points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		update_user_meta( $user_id, 'wps_wpr_points', $user_points + $credit );

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ self::PARTIAL_RESTORE_LOG_KEY ][] = array(
			self::PARTIAL_RESTORE_LOG_KEY => $credit,
			'date'                        => date_i18n( 'Y-m-d h:i:sa' ),
			'order_id'                    => $order_id,
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		$order->add_order_note(
			/* translators: %s: points */
			sprintf( esc_html__( '%s points taken for earlier partial refunds were added back, because the full order points were deducted.', 'points-and-rewards-for-woocommerce' ), $credit )
		);
	}

	/**
	 * Partial refund tracking with defaults.
	 *
	 * @param WC_Order $order order.
	 * @return array
	 */
	private function wps_wpr_get_partial_refund_tracking( $order ) {
		$tracked = $order->get_meta( self::PARTIAL_REFUND_META );
		$tracked = is_array( $tracked ) ? $tracked : array();
		return wp_parse_args(
			$tracked,
			array(
				'refund_ids'      => array(),
				'refunded_amount' => 0,
				'item_qty'        => array(),
				'currency'        => 0,
				'items'           => array(),
			)
		);
	}

	/**
	 * Per currency spent points awarded for an order, read from the user's points log.
	 *
	 * @param int $user_id  user id.
	 * @param int $order_id order id.
	 * @return int
	 */
	private function wps_wpr_get_awarded_currency_points( $user_id, $order_id ) {
		$points_log = get_user_meta( $user_id, 'points_details', true );
		$awarded    = 0;
		if ( ! empty( $points_log['pro_conversion_points'] ) && is_array( $points_log['pro_conversion_points'] ) ) {
			foreach ( $points_log['pro_conversion_points'] as $entry ) {
				if ( ! empty( $entry['refered_order_id'] ) && (int) $entry['refered_order_id'] === (int) $order_id ) {
					$awarded = (int) $entry['pro_conversion_points'];
				}
			}
		}
		return $awarded;
	}

	/**
	 * Whether any of the given order meta keys holds the given value.
	 *
	 * @param WC_Order $order order.
	 * @param array    $keys  meta keys.
	 * @param string   $value expected value.
	 * @return bool
	 */
	private function wps_wpr_order_has_flag( $order, $keys, $value ) {
		foreach ( $keys as $key ) {
			if ( $value === $order->get_meta( $key ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Delete order meta keys (saved by the caller).
	 *
	 * @param WC_Order $order order.
	 * @param array    $keys  meta keys.
	 * @return void
	 */
	private function wps_wpr_delete_order_meta( $order, $keys ) {
		foreach ( $keys as $key ) {
			$order->delete_meta_data( $key );
		}
	}
}
