<?php
/**
 * Achievement / bonus rules.
 *
 * Admins define goals such as "spend 500 within 30 days" or "place 3 orders
 * within 60 days". When an order reaches the points award status, every
 * active rule is checked for the customer and the bonus is awarded once per
 * rule (or once per goal cycle for repeatable rules). Rules can be targeted
 * with the condition engine (WPS_WPR_Condition_Engine).
 *
 * The free plugin evaluates one active rule; the limit is filterable with
 * `wps_wpr_achievement_rules_limit` (0 = unlimited), which the Pro plugin uses.
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
 * Achievement rules.
 */
class WPS_WPR_Achievement_Rules {

	/**
	 * Option storing the rules.
	 */
	const OPTION = 'wps_wpr_achievement_rules';

	/**
	 * Points log key.
	 */
	const LOG_KEY = 'achievement_bonus_points';

	/**
	 * User meta storing awards per rule.
	 */
	const AWARDS_META = 'wps_wpr_achievement_awards';

	/**
	 * Order meta prefix marking a rule awarded for that order.
	 */
	const ORDER_META_PREFIX = 'wps_wpr_achievement_awarded_';

	/**
	 * Goal types.
	 *
	 * @return array
	 */
	public static function wps_wpr_goal_types() {
		return (array) apply_filters(
			'wps_wpr_achievement_goal_types',
			array(
				'spend'  => __( 'Spend at least (amount)', 'points-and-rewards-for-woocommerce' ),
				'orders' => __( 'Place at least (orders)', 'points-and-rewards-for-woocommerce' ),
			)
		);
	}

	/**
	 * Maximum number of active rules evaluated. 0 means unlimited.
	 *
	 * @return int
	 */
	public static function wps_wpr_rules_limit() {
		return absint( apply_filters( 'wps_wpr_achievement_rules_limit', 1 ) );
	}

	/**
	 * Default values of one rule.
	 *
	 * @return array
	 */
	public static function wps_wpr_rule_defaults() {
		return array(
			'id'          => '',
			'name'        => '',
			'active'      => 'yes',
			'goal_type'   => 'spend',
			'goal_value'  => 0,
			'window_days' => 30,
			'points'      => 0,
			'repeat'      => 'once',
			'match'       => 'all',
			'conditions'  => array(),
		);
	}

	/**
	 * All saved rules.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_rules() {
		$rules = get_option( self::OPTION, array() );
		$rules = is_array( $rules ) ? $rules : array();
		$clean = array();
		foreach ( $rules as $rule ) {
			if ( is_array( $rule ) && ! empty( $rule['id'] ) ) {
				$clean[] = wp_parse_args( $rule, self::wps_wpr_rule_defaults() );
			}
		}
		return $clean;
	}

	/**
	 * Active rules, capped at the rule limit.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_active_rules() {
		$active = array_values(
			array_filter(
				self::wps_wpr_get_rules(),
				function ( $rule ) {
					return 'yes' === $rule['active'] && $rule['goal_value'] > 0 && $rule['points'] > 0 && $rule['window_days'] > 0;
				}
			)
		);
		$limit = self::wps_wpr_rules_limit();
		return $limit > 0 ? array_slice( $active, 0, $limit ) : $active;
	}

	/**
	 * Sanitize rules posted from the settings form.
	 *
	 * Rules beyond the active limit are saved as inactive.
	 *
	 * @param array $raw raw posted rules (already unslashed).
	 * @return array array( 'rules' => array, 'deactivated' => int ).
	 */
	public static function wps_wpr_sanitize_rules( $raw ) {
		$raw         = is_array( $raw ) ? $raw : array();
		$types       = WPS_WPR_Condition_Engine::wps_wpr_get_condition_types();
		$goal_types  = self::wps_wpr_goal_types();
		$limit       = self::wps_wpr_rules_limit();
		$active      = 0;
		$deactivated = 0;
		$rules       = array();
		$seen_ids    = array();

		foreach ( $raw as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$name = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
			if ( '' === $name ) {
				continue;
			}

			$id = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
			if ( '' === $id || isset( $seen_ids[ $id ] ) ) {
				$id = 'rule_' . strtolower( wp_generate_password( 8, false, false ) );
			}
			$seen_ids[ $id ] = true;

			$goal_type = isset( $item['goal_type'], $goal_types[ $item['goal_type'] ] ) ? $item['goal_type'] : 'spend';
			$is_active = ! empty( $item['active'] ) && 'yes' === $item['active'];
			if ( $is_active ) {
				++$active;
				if ( $limit > 0 && $active > $limit ) {
					$is_active = false;
					++$deactivated;
				}
			}

			$conditions = array();
			if ( ! empty( $item['conditions'] ) && is_array( $item['conditions'] ) ) {
				foreach ( $item['conditions'] as $condition ) {
					if ( ! is_array( $condition ) || empty( $condition['type'] ) || ! isset( $types[ $condition['type'] ] ) ) {
						continue;
					}
					$kind      = $types[ $condition['type'] ]['kind'];
					$operators = 'number' === $kind ? WPS_WPR_Condition_Engine::wps_wpr_number_operators() : WPS_WPR_Condition_Engine::wps_wpr_list_operators();
					$operator  = isset( $condition['operator'], $operators[ $condition['operator'] ] ) ? $condition['operator'] : key( $operators );
					$value     = isset( $condition['value'] ) ? sanitize_text_field( $condition['value'] ) : '';
					if ( 'number' === $kind ) {
						$value = (string) (float) wc_format_decimal( $value );
					} elseif ( '' === trim( $value ) ) {
						continue;
					}
					$conditions[] = array(
						'type'     => sanitize_key( $condition['type'] ),
						'operator' => $operator,
						'value'    => $value,
					);
				}
			}

			$rules[] = array(
				'id'          => $id,
				'name'        => $name,
				'active'      => $is_active ? 'yes' : 'no',
				'goal_type'   => $goal_type,
				'goal_value'  => isset( $item['goal_value'] ) ? max( 0, (float) wc_format_decimal( sanitize_text_field( $item['goal_value'] ) ) ) : 0,
				'window_days' => isset( $item['window_days'] ) ? max( 1, absint( $item['window_days'] ) ) : 30,
				'points'      => isset( $item['points'] ) ? absint( $item['points'] ) : 0,
				'repeat'      => isset( $item['repeat'] ) && 'repeat' === $item['repeat'] ? 'repeat' : 'once',
				'match'       => isset( $item['match'] ) && 'any' === $item['match'] ? 'any' : 'all',
				'conditions'  => $conditions,
			);
		}

		return array(
			'rules'       => $rules,
			'deactivated' => $deactivated,
		);
	}

	/**
	 * Order statuses whose orders count toward goals.
	 *
	 * @return array
	 */
	public static function wps_wpr_counted_statuses() {
		$statuses = array( 'completed' );
		if ( function_exists( 'wps_wpr_get_points_award_status' ) ) {
			$statuses[] = wps_wpr_get_points_award_status();
		}
		return array_values( array_unique( (array) apply_filters( 'wps_wpr_achievement_counted_statuses', $statuses ) ) );
	}

	/**
	 * Awards recorded for a user.
	 *
	 * @param int $user_id user id.
	 * @return array rule id => list of array( 'time', 'order_id', 'points' ).
	 */
	public static function wps_wpr_get_user_awards( $user_id ) {
		$awards = get_user_meta( $user_id, self::AWARDS_META, true );
		return is_array( $awards ) ? $awards : array();
	}

	/**
	 * Timestamp of the last award of a rule for a user (0 if never).
	 *
	 * @param int    $user_id user id.
	 * @param string $rule_id rule id.
	 * @return int
	 */
	public static function wps_wpr_last_award_time( $user_id, $rule_id ) {
		$awards = self::wps_wpr_get_user_awards( $user_id );
		if ( empty( $awards[ $rule_id ] ) || ! is_array( $awards[ $rule_id ] ) ) {
			return 0;
		}
		$last = end( $awards[ $rule_id ] );
		return isset( $last['time'] ) ? (int) $last['time'] : 0;
	}

	/**
	 * Progress of a user toward a rule's goal.
	 *
	 * Counts orders created inside the rule window. For repeatable rules the
	 * window also starts after the last award, so the same orders never earn
	 * the bonus twice.
	 *
	 * @param array    $rule    rule.
	 * @param int      $user_id user id.
	 * @param int|null $now     current timestamp (for tests).
	 * @return array array( 'current' => float, 'goal' => float, 'met' => bool ).
	 */
	public static function wps_wpr_get_progress( $rule, $user_id, $now = null ) {
		$now   = null === $now ? time() : (int) $now;
		$start = $now - ( absint( $rule['window_days'] ) * DAY_IN_SECONDS );
		if ( 'repeat' === $rule['repeat'] ) {
			$start = max( $start, self::wps_wpr_last_award_time( $user_id, $rule['id'] ) + 1 );
		}

		$orders = wc_get_orders(
			array(
				'customer_id'  => absint( $user_id ),
				'status'       => self::wps_wpr_counted_statuses(),
				'date_created' => '>=' . $start,
				'limit'        => -1,
				'type'         => 'shop_order',
			)
		);

		$current = 0.0;
		foreach ( $orders as $order ) {
			if ( 'orders' === $rule['goal_type'] ) {
				++$current;
			} else {
				$current += max( 0, (float) $order->get_total() - (float) $order->get_total_refunded() );
			}
		}

		$current = (float) apply_filters( 'wps_wpr_achievement_progress', $current, $rule, $user_id, $orders );
		$goal    = (float) $rule['goal_value'];

		return array(
			'current' => $current,
			'goal'    => $goal,
			'met'     => $goal > 0 && $current >= $goal,
		);
	}

	/**
	 * Handle an order status change and award bonuses.
	 *
	 * @param int    $order_id   order id.
	 * @param string $old_status old status.
	 * @param string $new_status new status.
	 * @return void
	 */
	public function wps_wpr_check_achievements( $order_id, $old_status, $new_status ) {
		if ( function_exists( 'wps_wpr_map_award_order_statuses' ) ) {
			list( $old_status, $new_status ) = wps_wpr_map_award_order_statuses( $order_id, $old_status, $new_status );
		}
		if ( $old_status === $new_status || 'completed' !== $new_status ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return;
		}
		$user_id = absint( $order->get_user_id() );
		if ( ! $user_id || ( function_exists( 'wps_wpr_restrict_user_fun' ) && wps_wpr_restrict_user_fun( $user_id ) ) ) {
			return;
		}

		foreach ( self::wps_wpr_get_active_rules() as $rule ) {
			$this->wps_wpr_maybe_award_rule( $rule, $user_id, $order );
		}
	}

	/**
	 * Award one rule if the user qualifies.
	 *
	 * @param array    $rule    rule.
	 * @param int      $user_id user id.
	 * @param WC_Order $order   triggering order.
	 * @return bool true when the bonus was awarded.
	 */
	public function wps_wpr_maybe_award_rule( $rule, $user_id, $order ) {
		// The same order never triggers the same rule twice (status bounces).
		if ( $order->get_meta( self::ORDER_META_PREFIX . $rule['id'] ) ) {
			return false;
		}
		if ( 'once' === $rule['repeat'] && self::wps_wpr_last_award_time( $user_id, $rule['id'] ) ) {
			return false;
		}

		$context = array(
			'user_id' => $user_id,
			'order'   => $order,
		);
		if ( ! WPS_WPR_Condition_Engine::wps_wpr_evaluate( $rule['conditions'], $rule['match'], $context ) ) {
			return false;
		}

		$progress = self::wps_wpr_get_progress( $rule, $user_id );
		if ( ! $progress['met'] ) {
			return false;
		}

		$points = absint( apply_filters( 'wps_wpr_achievement_bonus_points', $rule['points'], $rule, $user_id, $order ) );
		if ( $points <= 0 ) {
			return false;
		}

		$order->update_meta_data( self::ORDER_META_PREFIX . $rule['id'], $points );
		$order->save_meta_data();

		$balance = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) + $points;
		update_user_meta( $user_id, 'wps_wpr_points', $balance );

		$awards                    = self::wps_wpr_get_user_awards( $user_id );
		$awards[ $rule['id'] ]   = isset( $awards[ $rule['id'] ] ) && is_array( $awards[ $rule['id'] ] ) ? $awards[ $rule['id'] ] : array();
		$awards[ $rule['id'] ][] = array(
			'time'     => time(),
			'order_id' => $order->get_id(),
			'points'   => $points,
		);
		update_user_meta( $user_id, self::AWARDS_META, $awards );

		$points_log                    = get_user_meta( $user_id, 'points_details', true );
		$points_log                    = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();
		$points_log[ self::LOG_KEY ][] = array(
			self::LOG_KEY => $points,
			'date'        => date_i18n( 'Y-m-d h:i:sa' ),
			'order_id'    => $order->get_id(),
			'rule_name'   => $rule['name'],
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		$order->add_order_note(
			/* translators: 1: points, 2: rule name */
			sprintf( esc_html__( '%1$s achievement bonus points awarded to the customer for "%2$s".', 'points-and-rewards-for-woocommerce' ), $points, $rule['name'] )
		);

		do_action( 'wps_wpr_achievement_awarded', $user_id, $points, $rule, $order );
		return true;
	}

	/**
	 * Goal description shown to customers.
	 *
	 * @param array $rule rule.
	 * @return string
	 */
	public static function wps_wpr_goal_text( $rule ) {
		if ( 'orders' === $rule['goal_type'] ) {
			/* translators: 1: number of orders, 2: days */
			return sprintf( _n( 'Place %1$s order within %2$s days', 'Place %1$s orders within %2$s days', (int) $rule['goal_value'], 'points-and-rewards-for-woocommerce' ), number_format_i18n( $rule['goal_value'] ), number_format_i18n( $rule['window_days'] ) );
		}
		/* translators: 1: amount, 2: days */
		return sprintf( __( 'Spend %1$s within %2$s days', 'points-and-rewards-for-woocommerce' ), wp_strip_all_tags( wc_price( $rule['goal_value'] ) ), number_format_i18n( $rule['window_days'] ) );
	}

	/**
	 * Show achievements with progress on the My Account points page.
	 *
	 * @param int $user_id user id.
	 * @return void
	 */
	public function wps_wpr_render_account_achievements( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return;
		}
		$html = self::wps_wpr_get_account_achievements_html( $user_id );
		if ( '' === $html ) {
			return;
		}
		echo '<style>' . self::wps_wpr_account_css() . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static CSS.
		echo wp_kses_post( $html );
	}

	/**
	 * CSS of the My Account achievements section.
	 *
	 * @return string
	 */
	public static function wps_wpr_account_css() {
		return '.wps_wpr_achievements{margin:20px 0}'
			. '.wps_wpr_achievement{border:1px solid #e2e2e2;border-radius:6px;padding:12px 14px;margin-bottom:10px}'
			. '.wps_wpr_achievement_head{display:flex;justify-content:space-between;gap:10px;font-weight:600}'
			. '.wps_wpr_achievement_goal{font-size:.9em;opacity:.8;margin:4px 0 8px}'
			. '.wps_wpr_achievement_bar{background:#eee;border-radius:10px;height:10px;overflow:hidden}'
			. '.wps_wpr_achievement_bar span{display:block;height:100%;background:#2e7d32}'
			. '.wps_wpr_achievement_meta{font-size:.85em;margin-top:6px}';
	}

	/**
	 * Markup of the My Account achievements section.
	 *
	 * @param int $user_id user id.
	 * @return string empty when no rule applies.
	 */
	public static function wps_wpr_get_account_achievements_html( $user_id ) {
		$items = array();
		foreach ( self::wps_wpr_get_active_rules() as $rule ) {
			$context = array(
				'user_id'               => $user_id,
				'skip_order_conditions' => true,
			);
			if ( ! WPS_WPR_Condition_Engine::wps_wpr_evaluate( $rule['conditions'], $rule['match'], $context ) ) {
				continue;
			}
			$earned   = 'once' === $rule['repeat'] && self::wps_wpr_last_award_time( $user_id, $rule['id'] );
			$progress = self::wps_wpr_get_progress( $rule, $user_id );
			$percent  = $earned ? 100 : ( $progress['goal'] > 0 ? min( 100, (int) floor( $progress['current'] / $progress['goal'] * 100 ) ) : 0 );
			$items[]  = array(
				'rule'     => $rule,
				'earned'   => $earned,
				'progress' => $progress,
				'percent'  => $percent,
			);
		}

		if ( empty( $items ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="wps_wpr_achievements">
			<h3 class="wps_wpr_heading"><?php esc_html_e( 'Achievements', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<?php foreach ( $items as $item ) : ?>
				<?php
				$rule     = $item['rule'];
				$progress = $item['progress'];
				if ( 'orders' === $rule['goal_type'] ) {
					$progress_text = number_format_i18n( min( $progress['current'], $progress['goal'] ) ) . ' / ' . number_format_i18n( $progress['goal'] );
				} else {
					$progress_text = wp_strip_all_tags( wc_price( min( $progress['current'], $progress['goal'] ) ) ) . ' / ' . wp_strip_all_tags( wc_price( $progress['goal'] ) );
				}
				?>
				<div class="wps_wpr_achievement" data-rule="<?php echo esc_attr( $rule['id'] ); ?>">
					<div class="wps_wpr_achievement_head">
						<span class="wps_wpr_achievement_name"><?php echo esc_html( $rule['name'] ); ?></span>
						<span class="wps_wpr_achievement_points">
							<?php
							/* translators: %s: bonus points */
							echo esc_html( sprintf( __( '+%s points', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $rule['points'] ) ) );
							?>
						</span>
					</div>
					<div class="wps_wpr_achievement_goal"><?php echo esc_html( self::wps_wpr_goal_text( $rule ) ); ?></div>
					<div class="wps_wpr_achievement_bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $item['percent'] ); ?>"><span style="width:<?php echo esc_attr( $item['percent'] ); ?>%"></span></div>
					<div class="wps_wpr_achievement_meta">
						<?php
						if ( $item['earned'] ) {
							esc_html_e( 'Earned', 'points-and-rewards-for-woocommerce' );
						} else {
							echo esc_html( $progress_text );
						}
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
