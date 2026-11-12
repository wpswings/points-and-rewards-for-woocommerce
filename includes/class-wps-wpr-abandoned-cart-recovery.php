<?php
/**
 * Abandoned cart recovery with points incentive.
 *
 * Tracks carts of logged-in customers, emails a bonus points offer when a cart
 * is left idle, and awards the bonus when the customer completes an order
 * before the offer expires.
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
 * Abandoned cart recovery class.
 */
class WPS_WPR_Abandoned_Cart_Recovery {

	/**
	 * Cron hook name.
	 */
	const CRON_HOOK = 'wps_wpr_abandoned_cart_check';

	/**
	 * Settings option name.
	 */
	const OPTION = 'wps_wpr_abandoned_cart_settings';

	/**
	 * Log key used in points_details user meta.
	 */
	const LOG_KEY = 'abandoned_cart_points';

	/**
	 * Max users processed per cron run.
	 */
	const BATCH_SIZE = 50;

	/**
	 * Log key used in points_details user meta when a bonus is reversed.
	 */
	const REVERSAL_LOG_KEY = 'abandoned_cart_points_reversed';

	/**
	 * Register hooks owned by this class.
	 */
	public function __construct() {
		// Take the bonus back when the recovered order is cancelled or refunded.
		add_action( 'woocommerce_order_status_changed', array( $this, 'wps_wpr_reverse_recovery_points' ), 25, 3 );
	}

	/**
	 * Number of days a customer must wait between two recovery bonuses.
	 *
	 * @return int
	 */
	public function wps_wpr_get_bonus_cooldown_days() {
		return absint( apply_filters( 'wps_wpr_abandoned_cart_bonus_cooldown_days', 30 ) );
	}

	/**
	 * Check whether the user received a recovery bonus within the cooldown.
	 *
	 * @param  int $user_id user id.
	 * @return bool
	 */
	public function wps_wpr_is_in_bonus_cooldown( $user_id ) {
		$last_awarded = (int) get_user_meta( $user_id, 'wps_wpr_ac_last_awarded', true );
		$cooldown     = $this->wps_wpr_get_bonus_cooldown_days();
		return $last_awarded > 0 && $cooldown > 0 && ( time() - $last_awarded ) < ( $cooldown * DAY_IN_SECONDS );
	}

	/**
	 * Get default settings.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_default_settings() {
		return array(
			'wps_wpr_ac_enable'          => 'no',
			'wps_wpr_ac_abandon_hours'   => 1,
			'wps_wpr_ac_bonus_points'    => 50,
			'wps_wpr_ac_validity_days'   => 3,
			'wps_wpr_ac_min_cart_total'  => 0,
			'wps_wpr_ac_email_subject'   => __( 'You left something in your cart - earn bonus points!', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_ac_email_content'   => __( 'Hi [USERNAME],<br><br>You left items worth [CARTTOTAL] in your cart. Complete your purchase before [EXPIRYDATE] and earn <strong>[POINTS] bonus points</strong>!<br><br><a href="[CARTLINK]">Return to your cart</a>', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Get saved settings merged with defaults.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_settings() {
		$settings = get_option( self::OPTION, array() );
		$settings = ! empty( $settings ) && is_array( $settings ) ? $settings : array();
		return wp_parse_args( $settings, self::wps_wpr_get_default_settings() );
	}

	/**
	 * Check whether the feature is enabled.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_enabled() {
		$settings = self::wps_wpr_get_settings();
		return 'yes' === $settings['wps_wpr_ac_enable'];
	}

	/**
	 * Schedule or unschedule the cron based on the setting.
	 *
	 * @return void
	 */
	public static function wps_wpr_sync_cron_schedule() {
		$next_run = wp_next_scheduled( self::CRON_HOOK );
		if ( self::wps_wpr_is_enabled() ) {
			if ( ! $next_run ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::CRON_HOOK );
			}
		} elseif ( $next_run ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
	}

	/**
	 * Check whether the user is restricted from points features.
	 *
	 * @param  int $user_id user id.
	 * @return bool
	 */
	public function wps_wpr_is_user_restricted( $user_id ) {
		return 'yes' === get_user_meta( $user_id, 'wps_wpr_restrict_user', true );
	}

	/**
	 * Get the user's active (unexpired) incentive.
	 *
	 * @param  int $user_id user id.
	 * @return array empty when there is no active incentive.
	 */
	public function wps_wpr_get_active_incentive( $user_id ) {
		$incentive = get_user_meta( $user_id, 'wps_wpr_ac_incentive', true );
		if ( empty( $incentive ) || ! is_array( $incentive ) || empty( $incentive['expires'] ) ) {
			return array();
		}
		if ( (int) $incentive['expires'] < time() ) {
			delete_user_meta( $user_id, 'wps_wpr_ac_incentive' );
			return array();
		}
		return $incentive;
	}

	/**
	 * Clear cart tracking data for a user.
	 *
	 * @param  int $user_id user id.
	 * @return void
	 */
	public function wps_wpr_clear_tracking( $user_id ) {
		delete_user_meta( $user_id, 'wps_wpr_ac_cart_hash' );
		delete_user_meta( $user_id, 'wps_wpr_ac_last_activity' );
		delete_user_meta( $user_id, 'wps_wpr_ac_cart_total' );
		delete_user_meta( $user_id, 'wps_wpr_ac_status' );
	}

	/**
	 * Keep the cron schedule in sync with the setting.
	 *
	 * @return void
	 */
	public function wps_wpr_maybe_schedule_cron() {
		self::wps_wpr_sync_cron_schedule();
	}

	/**
	 * Track cart changes of logged-in customers.
	 *
	 * @return void
	 */
	public function wps_wpr_track_cart_activity() {
		if ( ! self::wps_wpr_is_enabled() || ! is_user_logged_in() || ! function_exists( 'WC' ) || empty( WC()->cart ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( $this->wps_wpr_is_user_restricted( $user_id ) || WC()->cart->is_empty() ) {
			return;
		}

		// Only write when the cart contents actually changed.
		$cart_hash = WC()->cart->get_cart_hash();
		if ( get_user_meta( $user_id, 'wps_wpr_ac_cart_hash', true ) === $cart_hash ) {
			return;
		}

		update_user_meta( $user_id, 'wps_wpr_ac_cart_hash', $cart_hash );
		update_user_meta( $user_id, 'wps_wpr_ac_last_activity', time() );
		update_user_meta( $user_id, 'wps_wpr_ac_cart_total', (float) WC()->cart->get_total( 'edit' ) );

		// Don't send a new offer while one is still pending.
		$status = ! empty( $this->wps_wpr_get_active_incentive( $user_id ) ) ? 'reminded' : 'tracking';
		update_user_meta( $user_id, 'wps_wpr_ac_status', $status );
	}

	/**
	 * Clear tracking when the cart (including the persistent cart) is emptied.
	 *
	 * @param  bool $clear_persistent_cart whether the persistent cart was cleared too.
	 * @return void
	 */
	public function wps_wpr_cart_emptied( $clear_persistent_cart = true ) {
		// Logout empties only the session cart; the saved cart is still there.
		if ( ! $clear_persistent_cart || ! is_user_logged_in() ) {
			return;
		}
		$this->wps_wpr_clear_tracking( get_current_user_id() );
	}

	/**
	 * Stop tracking once the customer places an order.
	 *
	 * @param  int|WC_Order $order order id or order object.
	 * @return void
	 */
	public function wps_wpr_order_placed( $order ) {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $order );
		if ( ! $order ) {
			return;
		}
		$user_id = absint( $order->get_user_id() );
		if ( $user_id ) {
			$this->wps_wpr_clear_tracking( $user_id );
		}
	}

	/**
	 * Cron callback: find abandoned carts and send the points offer.
	 *
	 * @return void
	 */
	public function wps_wpr_process_abandoned_carts() {
		if ( ! self::wps_wpr_is_enabled() ) {
			return;
		}

		$settings      = self::wps_wpr_get_settings();
		$abandon_hours = max( 1, absint( $settings['wps_wpr_ac_abandon_hours'] ) );
		$bonus_points  = absint( $settings['wps_wpr_ac_bonus_points'] );
		$min_total     = (float) $settings['wps_wpr_ac_min_cart_total'];
		if ( $bonus_points <= 0 ) {
			return;
		}

		$user_ids = get_users(
			array(
				'fields'     => 'ID',
				'number'     => self::BATCH_SIZE,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'   => 'wps_wpr_ac_status',
						'value' => 'tracking',
					),
					array(
						'key'     => 'wps_wpr_ac_last_activity',
						'value'   => time() - ( $abandon_hours * HOUR_IN_SECONDS ),
						'compare' => '<=',
						'type'    => 'NUMERIC',
					),
				),
			)
		);

		foreach ( $user_ids as $user_id ) {
			$user_id = absint( $user_id );

			if ( $this->wps_wpr_is_user_restricted( $user_id ) ) {
				$this->wps_wpr_clear_tracking( $user_id );
				continue;
			}

			// The cart may have been emptied from another device or session.
			$persistent_cart = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
			if ( empty( $persistent_cart['cart'] ) ) {
				$this->wps_wpr_clear_tracking( $user_id );
				continue;
			}

			$cart_total = (float) get_user_meta( $user_id, 'wps_wpr_ac_cart_total', true );
			if ( $cart_total < $min_total ) {
				// Re-evaluated when the cart changes again.
				update_user_meta( $user_id, 'wps_wpr_ac_status', 'skipped' );
				continue;
			}

			if ( ! empty( $this->wps_wpr_get_active_incentive( $user_id ) ) ) {
				update_user_meta( $user_id, 'wps_wpr_ac_status', 'reminded' );
				continue;
			}

			// No new offer while the customer is still in the bonus cooldown.
			if ( $this->wps_wpr_is_in_bonus_cooldown( $user_id ) ) {
				update_user_meta( $user_id, 'wps_wpr_ac_status', 'skipped' );
				continue;
			}

			// Remember which products were in the abandoned cart.
			$product_ids = array();
			foreach ( $persistent_cart['cart'] as $cart_item ) {
				if ( ! empty( $cart_item['product_id'] ) ) {
					$product_ids[] = absint( $cart_item['product_id'] );
				}
			}
			$settings['wps_wpr_ac_cart_product_ids'] = array_values( array_unique( $product_ids ) );

			$this->wps_wpr_send_incentive( $user_id, $bonus_points, $cart_total, $settings );
		}
	}

	/**
	 * Create the incentive for a user and email it.
	 *
	 * @param  int   $user_id      user id.
	 * @param  int   $bonus_points bonus points offered.
	 * @param  float $cart_total   abandoned cart total.
	 * @param  array $settings     feature settings.
	 * @return void
	 */
	public function wps_wpr_send_incentive( $user_id, $bonus_points, $cart_total, $settings ) {
		$validity_days = max( 1, absint( $settings['wps_wpr_ac_validity_days'] ) );
		$sent          = time();
		$expires       = $sent + ( $validity_days * DAY_IN_SECONDS );

		update_user_meta(
			$user_id,
			'wps_wpr_ac_incentive',
			array(
				'points'      => $bonus_points,
				'sent'        => $sent,
				'expires'     => $expires,
				'min_total'   => (float) $settings['wps_wpr_ac_min_cart_total'],
				'product_ids' => ! empty( $settings['wps_wpr_ac_cart_product_ids'] ) ? array_map( 'absint', (array) $settings['wps_wpr_ac_cart_product_ids'] ) : array(),
			)
		);
		update_user_meta( $user_id, 'wps_wpr_ac_status', 'reminded' );

		$user          = get_userdata( $user_id );
		$email_content = str_replace(
			array( '[USERNAME]', '[POINTS]', '[CARTTOTAL]', '[CARTLINK]', '[EXPIRYDATE]' ),
			array(
				$user ? esc_html( $user->display_name ) : '',
				$bonus_points,
				wp_strip_all_tags( wc_price( $cart_total ) ),
				esc_url( wc_get_cart_url() ),
				date_i18n( get_option( 'date_format' ), $expires ),
			),
			$settings['wps_wpr_ac_email_content']
		);
		$email_subject = str_replace( '[POINTS]', $bonus_points, $settings['wps_wpr_ac_email_subject'] );

		$mailer = WC()->mailer()->emails;
		if ( ! empty( $mailer['wps_wpr_email_notification'] ) ) {
			$mailer['wps_wpr_email_notification']->trigger( $user_id, $email_content, $email_subject );
		}

		do_action( 'wps_wpr_abandoned_cart_incentive_sent', $user_id, $bonus_points, $expires );
	}

	/**
	 * Show the pending offer on the cart and checkout pages.
	 *
	 * @return void
	 */
	public function wps_wpr_show_incentive_notice() {
		if ( ! self::wps_wpr_is_enabled() || ! is_user_logged_in() ) {
			return;
		}
		$incentive = $this->wps_wpr_get_active_incentive( get_current_user_id() );
		if ( empty( $incentive ) ) {
			return;
		}
		wc_print_notice(
			sprintf(
				/* translators: 1: bonus points, 2: expiry date */
				esc_html__( 'Complete your order before %2$s to earn %1$s bonus points!', 'points-and-rewards-for-woocommerce' ),
				absint( $incentive['points'] ),
				esc_html( date_i18n( get_option( 'date_format' ), $incentive['expires'] ) )
			),
			'notice'
		);
	}

	/**
	 * Award the bonus when an order placed within the offer window is completed.
	 *
	 * @param  int    $order_id   order id.
	 * @param  string $old_status old status.
	 * @param  string $new_status new status.
	 * @return void
	 */
	public function wps_wpr_award_recovery_points( $order_id, $old_status, $new_status ) {
		// Award points at the configured order status (see wps_wpr_get_points_award_status()).
		list( $old_status, $new_status ) = wps_wpr_map_award_order_statuses( $order_id, $old_status, $new_status );
		$award_statuses = apply_filters( 'wps_wpr_abandoned_cart_award_statuses', array( 'completed' ) );
		if ( $old_status === $new_status || ! in_array( $new_status, $award_statuses, true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_meta( 'wps_wpr_ac_points_awarded' ) ) {
			return;
		}

		$user_id = absint( $order->get_user_id() );
		if ( ! $user_id || $this->wps_wpr_is_user_restricted( $user_id ) ) {
			return;
		}

		$incentive = get_user_meta( $user_id, 'wps_wpr_ac_incentive', true );
		if ( empty( $incentive ) || ! is_array( $incentive ) ) {
			return;
		}

		// The order must be placed after the offer was sent and before it expired.
		$order_created = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0;
		if ( $order_created < (int) $incentive['sent'] || $order_created > (int) $incentive['expires'] ) {
			return;
		}

		$bonus_points = absint( $incentive['points'] );
		if ( $bonus_points <= 0 ) {
			return;
		}

		// One bonus per cooldown period.
		if ( $this->wps_wpr_is_in_bonus_cooldown( $user_id ) ) {
			return;
		}

		if ( ! $this->wps_wpr_order_qualifies( $order, $incentive ) ) {
			return;
		}

		$user_points    = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$updated_points = $user_points + $bonus_points;
		update_user_meta( $user_id, 'wps_wpr_points', $updated_points );
		update_user_meta( $user_id, 'wps_wpr_ac_last_awarded', time() );
		delete_user_meta( $user_id, 'wps_wpr_ac_incentive' );
		$this->wps_wpr_clear_tracking( $user_id );

		$order->update_meta_data( 'wps_wpr_ac_points_awarded', $bonus_points );
		$order->save();
		$order->add_order_note(
			/* translators: %s: bonus points */
			sprintf( esc_html__( '%s abandoned cart recovery bonus points awarded to the customer.', 'points-and-rewards-for-woocommerce' ), $bonus_points )
		);

		$this->wps_wpr_create_recovery_points_log( $user_id, $bonus_points, $order_id );

		/* translators: 1: bonus points, 2: total points */
		$message = sprintf( esc_html__( 'You earned %1$s bonus points for completing your order. Your total points balance is now %2$s', 'points-and-rewards-for-woocommerce' ), $bonus_points, $updated_points );
		if ( function_exists( 'wps_wpr_send_sms_org' ) ) {
			wps_wpr_send_sms_org( $user_id, $message );
		}
		if ( function_exists( 'wps_wpr_send_messages_on_whatsapp' ) ) {
			wps_wpr_send_messages_on_whatsapp( $user_id, $message );
		}

		do_action( 'wps_wpr_abandoned_cart_points_awarded', $user_id, $bonus_points, $order_id );
	}

	/**
	 * Check that the order really recovers the abandoned cart.
	 *
	 * The order total (net of refunds) must meet the same minimum used when the
	 * offer was sent, and the order must contain the abandoned cart's products.
	 *
	 * @param  WC_Order $order     order object.
	 * @param  array    $incentive stored incentive.
	 * @return bool
	 */
	public function wps_wpr_order_qualifies( $order, $incentive ) {
		$settings    = self::wps_wpr_get_settings();
		$min_total   = isset( $incentive['min_total'] ) ? (float) $incentive['min_total'] : (float) $settings['wps_wpr_ac_min_cart_total'];
		$order_total = (float) $order->get_total() - (float) $order->get_total_refunded();
		$qualifies   = $order_total > 0 && $order_total >= $min_total;

		if ( $qualifies && ! empty( $incentive['product_ids'] ) && is_array( $incentive['product_ids'] ) ) {
			$order_product_ids = array();
			foreach ( $order->get_items() as $item ) {
				if ( is_callable( array( $item, 'get_product_id' ) ) ) {
					$order_product_ids[] = absint( $item->get_product_id() );
				}
			}
			$missing   = array_diff( array_map( 'absint', $incentive['product_ids'] ), $order_product_ids );
			$qualifies = empty( $missing );
		}

		return (bool) apply_filters( 'wps_wpr_abandoned_cart_order_qualifies', $qualifies, $order, $incentive );
	}

	/**
	 * Reverse the recovery bonus when its order is cancelled or refunded.
	 *
	 * @param  int    $order_id   order id.
	 * @param  string $old_status old status.
	 * @param  string $new_status new status.
	 * @return void
	 */
	public function wps_wpr_reverse_recovery_points( $order_id, $old_status, $new_status ) {
		if ( $old_status === $new_status || ! in_array( $new_status, array( 'cancelled', 'refunded' ), true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$awarded = absint( $order->get_meta( 'wps_wpr_ac_points_awarded' ) );
		if ( $awarded <= 0 || $order->get_meta( 'wps_wpr_ac_points_reversed' ) ) {
			return;
		}

		$user_id = absint( $order->get_user_id() );
		if ( ! $user_id ) {
			return;
		}

		// Balances are stored unsigned, so never go below zero.
		$user_points = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$deducted    = min( $awarded, $user_points );
		update_user_meta( $user_id, 'wps_wpr_points', $user_points - $deducted );

		$order->update_meta_data( 'wps_wpr_ac_points_reversed', $deducted );
		$order->save();
		$order->add_order_note(
			/* translators: %s: deducted points */
			sprintf( esc_html__( '%s abandoned cart recovery bonus points deducted because the order was cancelled or refunded.', 'points-and-rewards-for-woocommerce' ), $deducted )
		);

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ self::REVERSAL_LOG_KEY ][] = array(
			self::REVERSAL_LOG_KEY => $deducted,
			'date'                 => date_i18n( 'Y-m-d h:i:sa' ),
			'order_id'             => $order_id,
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		do_action( 'wps_wpr_abandoned_cart_points_reversed', $user_id, $deducted, $order_id );
	}

	/**
	 * Add the bonus to the user's points log.
	 *
	 * @param  int $user_id      user id.
	 * @param  int $bonus_points awarded points.
	 * @param  int $order_id     order id.
	 * @return void
	 */
	public function wps_wpr_create_recovery_points_log( $user_id, $bonus_points, $order_id ) {
		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ self::LOG_KEY ][] = array(
			self::LOG_KEY => $bonus_points,
			'date'        => date_i18n( 'Y-m-d h:i:sa' ),
			'order_id'    => $order_id,
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );
	}
}
