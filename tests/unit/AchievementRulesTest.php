<?php
/**
 * Tests for the condition engine and achievement / bonus rules.
 *
 * @package points-and-rewards-for-wooCommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Achievement rules tests.
 */
class AchievementRulesTest extends TestCase {

	/**
	 * Users created by a test.
	 *
	 * @var int[]
	 */
	private $users = array();

	/**
	 * Orders created by a test.
	 *
	 * @var int[]
	 */
	private $orders = array();

	/**
	 * Saved rules option, restored after each test.
	 *
	 * @var mixed
	 */
	private $saved_rules;

	/**
	 * Back up the rules option.
	 */
	protected function setUp(): void {
		$this->saved_rules = get_option( WPS_WPR_Achievement_Rules::OPTION, null );
		delete_option( WPS_WPR_Achievement_Rules::OPTION );
		// Test the free plugin's default limit even when Pro is active.
		remove_all_filters( 'wps_wpr_achievement_rules_limit' );
	}

	/**
	 * Remove test data and restore the option.
	 */
	protected function tearDown(): void {
		foreach ( $this->orders as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				foreach ( $order->get_refunds() as $refund ) {
					$refund->delete( true );
				}
				$order->delete( true );
			}
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->users as $user_id ) {
			wp_delete_user( $user_id );
		}
		if ( null === $this->saved_rules ) {
			delete_option( WPS_WPR_Achievement_Rules::OPTION );
		} else {
			update_option( WPS_WPR_Achievement_Rules::OPTION, $this->saved_rules );
		}
		remove_all_filters( 'wps_wpr_achievement_rules_limit' );
		WPS_WPR_Condition_Engine::wps_wpr_flush_cache();
		$this->users  = array();
		$this->orders = array();
	}

	/**
	 * Create a customer.
	 *
	 * @param array $meta user meta.
	 * @param string $role role.
	 * @return int
	 */
	private function make_user( $meta = array(), $role = 'customer' ) {
		$user_id = wp_insert_user(
			array(
				'user_login' => 'wpr_ach_' . wp_generate_password( 8, false, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'wpr_ach_' . wp_generate_password( 8, false, false ) . '@example.test',
				'role'       => $role,
			)
		);
		foreach ( $meta as $key => $value ) {
			update_user_meta( $user_id, $key, $value );
		}
		$this->users[] = $user_id;
		return $user_id;
	}

	/**
	 * Create an order.
	 *
	 * @param int    $user_id  customer.
	 * @param float  $total    order total.
	 * @param string $status   status.
	 * @param int    $days_ago creation (and paid / completed) date offset.
	 * @return WC_Order
	 */
	private function make_order( $user_id, $total, $status = 'completed', $days_ago = 0 ) {
		$order = wc_create_order( array( 'customer_id' => $user_id ) );
		$order->set_billing_country( 'GB' );
		$order->set_total( $total );
		if ( $days_ago ) {
			$order->set_date_created( time() - $days_ago * DAY_IN_SECONDS );
		}
		$order->set_status( $status );
		$order->save();
		if ( $days_ago && $order->get_date_paid() ) {
			$order->set_date_paid( time() - $days_ago * DAY_IN_SECONDS );
		}
		if ( $days_ago && $order->get_date_completed() ) {
			$order->set_date_completed( time() - $days_ago * DAY_IN_SECONDS );
		}
		$order->save();
		$this->orders[] = $order->get_id();
		return wc_get_order( $order->get_id() );
	}

	/**
	 * Build a rule.
	 *
	 * @param array $overrides values.
	 * @return array
	 */
	private function rule( $overrides = array() ) {
		return wp_parse_args(
			$overrides,
			array(
				'id'          => 'rule_test1',
				'name'        => 'Spend 500 in 30 days',
				'active'      => 'yes',
				'goal_type'   => 'spend',
				'goal_value'  => 500,
				'window_days' => 30,
				'points'      => 75,
				'repeat'      => 'once',
				'match'       => 'all',
				'conditions'  => array(),
			)
		);
	}

	/**
	 * Evaluate one condition for a context.
	 *
	 * @param string $type     type.
	 * @param string $operator operator.
	 * @param string $value    value.
	 * @param array  $context  context.
	 * @return bool
	 */
	private function check( $type, $operator, $value, $context ) {
		return WPS_WPR_Condition_Engine::wps_wpr_evaluate(
			array(
				array(
					'type'     => $type,
					'operator' => $operator,
					'value'    => $value,
				),
			),
			'all',
			$context
		);
	}

	/**
	 * Each built-in condition type matches and fails as expected.
	 */
	public function test_condition_types_true_and_false() {
		$user_id = $this->make_user(
			array(
				'billing_country'  => 'IN',
				'locale'           => 'fr_FR',
				'membership_level' => 'Gold',
			)
		);
		$this->make_order( $user_id, 120 );
		$order = $this->make_order( $user_id, 80, 'processing' );
		$ctx   = array( 'user_id' => $user_id );

		$this->assertTrue( $this->check( 'user_role', 'in', 'customer, subscriber', $ctx ) );
		$this->assertFalse( $this->check( 'user_role', 'in', 'administrator', $ctx ) );
		$this->assertTrue( $this->check( 'user_role', 'not_in', 'administrator', $ctx ) );

		$this->assertTrue( $this->check( 'billing_country', 'in', 'IN,US', $ctx ) );
		$this->assertFalse( $this->check( 'billing_country', 'in', 'US', $ctx ) );
		// With an order, the order's billing country wins.
		$this->assertTrue( $this->check( 'billing_country', 'in', 'GB', array( 'user_id' => $user_id, 'order' => $order ) ) );

		$this->assertTrue( $this->check( 'order_count', '>=', '2', $ctx ) );
		$this->assertFalse( $this->check( 'order_count', '>=', '3', $ctx ) );
		$this->assertTrue( $this->check( 'order_count', '=', '2', $ctx ) );

		$this->assertTrue( $this->check( 'lifetime_spend', '>=', '200', $ctx ) );
		$this->assertFalse( $this->check( 'lifetime_spend', '>', '200', $ctx ) );
		$this->assertTrue( $this->check( 'lifetime_spend', '<=', '200', $ctx ) );

		$this->assertTrue( $this->check( 'language', 'in', 'fr', $ctx ) );
		$this->assertTrue( $this->check( 'language', 'in', 'fr_FR', $ctx ) );
		$this->assertFalse( $this->check( 'language', 'in', 'de, en_US', $ctx ) );

		$this->assertTrue( $this->check( 'membership', 'in', 'gold', $ctx ) );
		$this->assertFalse( $this->check( 'membership', 'in', 'Silver', $ctx ) );

		$this->assertTrue( $this->check( 'order_total', '>=', '80', array( 'user_id' => $user_id, 'order' => $order ) ) );
		$this->assertFalse( $this->check( 'order_total', '>', '80', array( 'user_id' => $user_id, 'order' => $order ) ) );
		// Order conditions fail without an order, and are skipped in preview mode.
		$this->assertFalse( $this->check( 'order_total', '>=', '1', $ctx ) );
		$this->assertTrue( $this->check( 'order_total', '>=', '1', array( 'user_id' => $user_id, 'skip_order_conditions' => true ) ) );

		$this->assertFalse( $this->check( 'unknown_type', 'in', 'x', $ctx ) );
	}

	/**
	 * AND / OR matching and the extension filter.
	 */
	public function test_and_or_matching_and_custom_type() {
		$user_id    = $this->make_user( array( 'billing_country' => 'US' ) );
		$ctx        = array( 'user_id' => $user_id );
		$conditions = array(
			array( 'type' => 'billing_country', 'operator' => 'in', 'value' => 'US' ),
			array( 'type' => 'user_role', 'operator' => 'in', 'value' => 'administrator' ),
		);
		$this->assertFalse( WPS_WPR_Condition_Engine::wps_wpr_evaluate( $conditions, 'all', $ctx ) );
		$this->assertTrue( WPS_WPR_Condition_Engine::wps_wpr_evaluate( $conditions, 'any', $ctx ) );
		$this->assertTrue( WPS_WPR_Condition_Engine::wps_wpr_evaluate( array(), 'all', $ctx ) );

		$add_type = function ( $types ) {
			$types['vip_flag'] = array(
				'label'    => 'VIP',
				'kind'     => 'list',
				'resolver' => function ( $context ) {
					return get_user_meta( $context['user_id'], 'vip', true ) ? 'yes' : 'no';
				},
			);
			return $types;
		};
		add_filter( 'wps_wpr_condition_types', $add_type );
		update_user_meta( $user_id, 'vip', 1 );
		$this->assertTrue( $this->check( 'vip_flag', 'in', 'yes', $ctx ) );
		remove_filter( 'wps_wpr_condition_types', $add_type );
	}

	/**
	 * Spending the goal within the window awards the bonus exactly once.
	 */
	public function test_spend_goal_awards_once_and_not_on_status_bounce() {
		$user_id = $this->make_user();
		$this->make_order( $user_id, 300 );
		$order = $this->make_order( $user_id, 250 );
		update_option( WPS_WPR_Achievement_Rules::OPTION, array( $this->rule() ) );

		$rules  = new WPS_WPR_Achievement_Rules();
		$before = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$rules->wps_wpr_check_achievements( $order->get_id(), 'processing', 'completed' );
		$after = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$this->assertSame( 75, $after - $before );

		$log = get_user_meta( $user_id, 'points_details', true );
		$this->assertCount( 1, $log[ WPS_WPR_Achievement_Rules::LOG_KEY ] );
		$this->assertSame( 75, $log[ WPS_WPR_Achievement_Rules::LOG_KEY ][0][ WPS_WPR_Achievement_Rules::LOG_KEY ] );
		$this->assertSame( 'Spend 500 in 30 days', $log[ WPS_WPR_Achievement_Rules::LOG_KEY ][0]['rule_name'] );
		$this->assertEquals( 75, wc_get_order( $order->get_id() )->get_meta( WPS_WPR_Achievement_Rules::ORDER_META_PREFIX . 'rule_test1' ) );

		// Status bounce on the same order, and a new order: still only once.
		$rules->wps_wpr_check_achievements( $order->get_id(), 'processing', 'completed' );
		$third = $this->make_order( $user_id, 600 );
		$rules->wps_wpr_check_achievements( $third->get_id(), 'processing', 'completed' );
		$this->assertSame( $after, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
	}

	/**
	 * Below the goal nothing is awarded; non-award transitions are ignored.
	 */
	public function test_goal_not_met_and_wrong_status() {
		$user_id = $this->make_user();
		$order   = $this->make_order( $user_id, 499 );
		update_option( WPS_WPR_Achievement_Rules::OPTION, array( $this->rule() ) );
		$rules = new WPS_WPR_Achievement_Rules();

		$rules->wps_wpr_check_achievements( $order->get_id(), 'processing', 'completed' );
		$this->assertSame( array(), WPS_WPR_Achievement_Rules::wps_wpr_get_user_awards( $user_id ) );

		// A transition that is not the award status is ignored even when the goal is met.
		update_option( WPS_WPR_Achievement_Rules::OPTION, array( $this->rule( array( 'goal_value' => 100 ) ) ) );
		$rules->wps_wpr_check_achievements( $order->get_id(), 'completed', 'cancelled' );
		$this->assertSame( array(), WPS_WPR_Achievement_Rules::wps_wpr_get_user_awards( $user_id ) );
	}

	/**
	 * Orders outside the window do not count.
	 */
	public function test_window_excludes_old_orders() {
		$user_id = $this->make_user();
		$this->make_order( $user_id, 450, 'completed', 40 );
		$order = $this->make_order( $user_id, 100 );
		$rule  = $this->rule();

		$progress = WPS_WPR_Achievement_Rules::wps_wpr_get_progress( $rule, $user_id );
		$this->assertEquals( 100, $progress['current'] );
		$this->assertFalse( $progress['met'] );

		$rule['window_days'] = 60;
		$progress            = WPS_WPR_Achievement_Rules::wps_wpr_get_progress( $rule, $user_id );
		$this->assertEquals( 550, $progress['current'] );
		$this->assertTrue( $progress['met'] );

		// Processing orders don't count while points are awarded on completed.
		$this->make_order( $user_id, 1000, 'processing' );
		$this->assertEquals( 550, WPS_WPR_Achievement_Rules::wps_wpr_get_progress( $rule, $user_id )['current'] );
		unset( $order );
	}

	/**
	 * Order-count goal with conditions, and a repeatable rule that restarts after an award.
	 */
	public function test_order_goal_conditions_and_repeat() {
		$user_id = $this->make_user( array( 'billing_country' => 'US' ) );
		$rule    = $this->rule(
			array(
				'goal_type'  => 'orders',
				'goal_value' => 2,
				'points'     => 20,
				'repeat'     => 'repeat',
				'conditions' => array(
					array( 'type' => 'order_total', 'operator' => '>=', 'value' => '50' ),
				),
			)
		);
		update_option( WPS_WPR_Achievement_Rules::OPTION, array( $rule ) );
		$rules = new WPS_WPR_Achievement_Rules();

		$this->make_order( $user_id, 60 );
		$small = $this->make_order( $user_id, 10 );
		// Second order meets the goal but fails the order-total condition.
		$rules->wps_wpr_check_achievements( $small->get_id(), 'processing', 'completed' );
		$this->assertSame( array(), WPS_WPR_Achievement_Rules::wps_wpr_get_user_awards( $user_id ) );

		$big = $this->make_order( $user_id, 70 );
		$rules->wps_wpr_check_achievements( $big->get_id(), 'processing', 'completed' );
		$awards = WPS_WPR_Achievement_Rules::wps_wpr_get_user_awards( $user_id );
		$this->assertCount( 1, $awards['rule_test1'] );

		// Progress restarts after the award; orders created before it no longer count.
		sleep( 1 );
		$progress = WPS_WPR_Achievement_Rules::wps_wpr_get_progress( $rule, $user_id );
		$this->assertEquals( 0, $progress['current'] );
	}

	/**
	 * Free allows one active rule; the filter lifts the limit.
	 */
	public function test_active_rule_limit() {
		$raw = array(
			array( 'name' => 'A', 'active' => 'yes', 'goal_value' => '100', 'window_days' => '30', 'points' => '10', 'goal_type' => 'spend' ),
			array( 'name' => 'B', 'active' => 'yes', 'goal_value' => '3', 'window_days' => '30', 'points' => '10', 'goal_type' => 'orders' ),
			array( 'name' => '', 'active' => 'yes' ),
		);

		$result = WPS_WPR_Achievement_Rules::wps_wpr_sanitize_rules( $raw );
		$this->assertCount( 2, $result['rules'] );
		$this->assertSame( 1, $result['deactivated'] );
		$this->assertSame( 'yes', $result['rules'][0]['active'] );
		$this->assertSame( 'no', $result['rules'][1]['active'] );
		$this->assertMatchesRegularExpression( '/^rule_[a-z0-9]{8}$/', $result['rules'][0]['id'] );

		add_filter( 'wps_wpr_achievement_rules_limit', '__return_zero' );
		$result = WPS_WPR_Achievement_Rules::wps_wpr_sanitize_rules( $raw );
		$this->assertSame( 0, $result['deactivated'] );
		update_option( WPS_WPR_Achievement_Rules::OPTION, $result['rules'] );
		$this->assertCount( 2, WPS_WPR_Achievement_Rules::wps_wpr_get_active_rules() );

		remove_all_filters( 'wps_wpr_achievement_rules_limit' );
		$this->assertCount( 1, WPS_WPR_Achievement_Rules::wps_wpr_get_active_rules() );
	}

	/**
	 * Sanitizing drops unknown condition types and empty list values.
	 */
	public function test_sanitize_conditions() {
		$result = WPS_WPR_Achievement_Rules::wps_wpr_sanitize_rules(
			array(
				array(
					'name'       => 'C',
					'active'     => 'yes',
					'goal_value' => '10',
					'points'     => '5',
					'window_days' => '0',
					'match'      => 'any',
					'conditions' => array(
						array( 'type' => 'billing_country', 'operator' => 'in', 'value' => 'US, GB' ),
						array( 'type' => 'bogus', 'operator' => 'in', 'value' => 'x' ),
						array( 'type' => 'user_role', 'operator' => 'in', 'value' => '' ),
						array( 'type' => 'order_count', 'operator' => 'bad', 'value' => '3' ),
					),
				),
			)
		);
		$rule = $result['rules'][0];
		$this->assertSame( 'any', $rule['match'] );
		$this->assertSame( 1, $rule['window_days'] );
		$this->assertCount( 2, $rule['conditions'] );
		$this->assertSame( '>=', $rule['conditions'][1]['operator'] );
	}

	/**
	 * My Account markup shows progress for qualifying rules only.
	 */
	public function test_account_markup() {
		$user_id = $this->make_user( array( 'billing_country' => 'US' ) );
		$this->make_order( $user_id, 250 );
		update_option(
			WPS_WPR_Achievement_Rules::OPTION,
			array(
				$this->rule(
					array(
						'conditions' => array(
							array( 'type' => 'billing_country', 'operator' => 'in', 'value' => 'US' ),
							array( 'type' => 'order_total', 'operator' => '>=', 'value' => '1000' ),
						),
					)
				),
			)
		);

		$html = WPS_WPR_Achievement_Rules::wps_wpr_get_account_achievements_html( $user_id );
		$this->assertStringContainsString( 'Spend 500 in 30 days', $html );
		$this->assertStringContainsString( 'aria-valuenow="50"', $html );
		$this->assertStringContainsString( '+75 points', $html );

		update_user_meta( $user_id, 'billing_country', 'DE' );
		$this->assertSame( '', WPS_WPR_Achievement_Rules::wps_wpr_get_account_achievements_html( $user_id ) );
	}

	/**
	 * Move a user's award times and orders' counted times back in time.
	 *
	 * @param int   $user_id   user.
	 * @param int   $seconds   seconds to move back.
	 * @param int[] $order_ids orders whose counted time is moved too.
	 */
	private function shift_back( $user_id, $seconds, $order_ids = array() ) {
		$awards = WPS_WPR_Achievement_Rules::wps_wpr_get_user_awards( $user_id );
		foreach ( $awards as $rule_id => $list ) {
			foreach ( $list as $i => $award ) {
				$awards[ $rule_id ][ $i ]['time'] = $award['time'] - $seconds;
			}
		}
		update_user_meta( $user_id, WPS_WPR_Achievement_Rules::AWARDS_META, $awards );
		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			$order->update_meta_data( WPS_WPR_Achievement_Rules::COUNTED_META, (int) $order->get_meta( WPS_WPR_Achievement_Rules::COUNTED_META ) - $seconds );
			$order->save_meta_data();
		}
	}

	/**
	 * Complete an order through the achievement hook.
	 *
	 * @param WPS_WPR_Achievement_Rules $rules rules object.
	 * @param WC_Order                  $order order.
	 */
	private function complete( $rules, $order ) {
		$order = wc_get_order( $order->get_id() );
		$order->set_status( 'completed' );
		$order->save();
		$rules->wps_wpr_check_achievements( $order->get_id(), 'processing', 'completed' );
	}

	/**
	 * Total awards of a rule for a user.
	 *
	 * @param int $user_id user.
	 * @return int
	 */
	private function award_count( $user_id ) {
		$awards = WPS_WPR_Achievement_Rules::wps_wpr_get_user_awards( $user_id );
		return isset( $awards['rule_test1'] ) ? count( $awards['rule_test1'] ) : 0;
	}

	/**
	 * A repeatable rule awards at most once per period, and again in the next period.
	 */
	public function test_repeatable_awards_once_per_period() {
		$user_id = $this->make_user();
		update_option(
			WPS_WPR_Achievement_Rules::OPTION,
			array( $this->rule( array( 'goal_value' => 100, 'points' => 10, 'repeat' => 'repeat' ) ) )
		);
		$rules = new WPS_WPR_Achievement_Rules();

		$first = $this->make_order( $user_id, 100, 'processing' );
		$this->complete( $rules, $first );
		$this->assertSame( 1, $this->award_count( $user_id ) );

		// 50 + 50 on the same day: the goal is reached again, but not in a new period.
		$second = $this->make_order( $user_id, 50, 'processing' );
		$this->complete( $rules, $second );
		$third = $this->make_order( $user_id, 50, 'processing' );
		$this->complete( $rules, $third );
		$this->assertSame( 1, $this->award_count( $user_id ) );
		$this->assertSame( 10, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );

		// 10 days later: still inside the period, no award.
		$this->shift_back( $user_id, 10 * DAY_IN_SECONDS, array( $first->get_id() ) );
		$fourth = $this->make_order( $user_id, 1, 'processing' );
		$this->complete( $rules, $fourth );
		$this->assertSame( 1, $this->award_count( $user_id ) );

		// The period has passed since the award: the 50 + 50 (+1) reached after it award again.
		$this->shift_back( $user_id, 21 * DAY_IN_SECONDS, array( $first->get_id() ) );
		$html = WPS_WPR_Achievement_Rules::wps_wpr_get_account_achievements_html( $user_id );
		$this->assertStringNotContainsString( 'You can earn it again', $html );
		$fifth = $this->make_order( $user_id, 1, 'processing' );
		$this->complete( $rules, $fifth );
		$this->assertSame( 2, $this->award_count( $user_id ) );
		$this->assertSame( 20, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );

		// Right after the second award My Account shows when it can be earned again.
		$html = WPS_WPR_Achievement_Rules::wps_wpr_get_account_achievements_html( $user_id );
		$this->assertStringContainsString( 'You can earn it again from', $html );
	}

	/**
	 * Window membership uses the date the order reached the award status, not its creation date.
	 */
	public function test_order_completed_after_award_counts() {
		$user_id = $this->make_user();
		update_option(
			WPS_WPR_Achievement_Rules::OPTION,
			array( $this->rule( array( 'goal_value' => 100, 'points' => 10, 'repeat' => 'repeat' ) ) )
		);
		$rules = new WPS_WPR_Achievement_Rules();

		// Two orders placed together; A completes first and earns the award.
		$a = $this->make_order( $user_id, 100, 'processing', 2 );
		$b = $this->make_order( $user_id, 100, 'processing', 2 );
		$this->complete( $rules, $a );
		$this->assertSame( 1, $this->award_count( $user_id ) );

		// The period passes; B was placed before the award (and before the window) but completes now.
		$this->shift_back( $user_id, 31 * DAY_IN_SECONDS, array( $a->get_id() ) );
		$b->set_date_created( time() - 33 * DAY_IN_SECONDS );
		$b->save();
		$this->complete( $rules, $b );
		$this->assertSame( 2, $this->award_count( $user_id ) );
	}

	/**
	 * Spend progress and lifetime spend are net of refunds, using aggregate queries.
	 */
	public function test_spend_is_net_of_refunds() {
		$user_id = $this->make_user();
		$this->make_order( $user_id, 120 );
		$order = $this->make_order( $user_id, 200 );
		wc_create_refund(
			array(
				'order_id' => $order->get_id(),
				'amount'   => 50,
			)
		);
		$this->make_order( $user_id, 999, 'cancelled' );

		$this->assertEquals( 270, WPS_WPR_Achievement_Rules::wps_wpr_get_progress( $this->rule(), $user_id )['current'] );
		WPS_WPR_Condition_Engine::wps_wpr_flush_cache();
		$this->assertEquals( 270, WPS_WPR_Condition_Engine::wps_wpr_resolve_lifetime_spend( array( 'user_id' => $user_id ) ) );
		$this->assertEquals( 2, WPS_WPR_Achievement_Rules::wps_wpr_get_progress( $this->rule( array( 'goal_type' => 'orders' ) ), $user_id )['current'] );
		$this->assertSame( 0.0, WPS_WPR_Condition_Engine::wps_wpr_sum_net_totals( array() ) );
	}

	/**
	 * The rendered progress bar keeps its ARIA values.
	 */
	public function test_rendered_progress_bar_keeps_aria() {
		$user_id = $this->make_user();
		$this->make_order( $user_id, 125 );
		update_option( WPS_WPR_Achievement_Rules::OPTION, array( $this->rule() ) );

		ob_start();
		( new WPS_WPR_Achievement_Rules() )->wps_wpr_render_account_achievements( $user_id );
		$html = ob_get_clean();
		$this->assertStringContainsString( 'role="progressbar"', $html );
		$this->assertStringContainsString( 'aria-valuenow="25"', $html );
		$this->assertStringContainsString( 'aria-valuemin="0"', $html );
		$this->assertStringContainsString( 'aria-valuemax="100"', $html );
	}

	/**
	 * Active rules beyond the free limit are shown as "Inactive (limit)" and don't run.
	 */
	public function test_rules_beyond_limit_shown_inactive() {
		$first  = $this->rule( array( 'id' => 'rule_p1', 'name' => 'P1', 'goal_value' => 100 ) );
		$second = $this->rule( array( 'id' => 'rule_p2', 'name' => 'P2', 'goal_value' => 100 ) );
		update_option( WPS_WPR_Achievement_Rules::OPTION, array( $first, $second ) );

		$this->assertSame( array( 'rule_p2' ), WPS_WPR_Achievement_Rules::wps_wpr_get_limited_rule_ids() );
		$this->assertSame( array( 'rule_p1' ), wp_list_pluck( WPS_WPR_Achievement_Rules::wps_wpr_get_active_rules(), 'id' ) );

		// Runtime matches: only P1 awards.
		$user_id = $this->make_user();
		$order   = $this->make_order( $user_id, 150 );
		( new WPS_WPR_Achievement_Rules() )->wps_wpr_check_achievements( $order->get_id(), 'processing', 'completed' );
		$this->assertSame( array( 'rule_p1' ), array_keys( WPS_WPR_Achievement_Rules::wps_wpr_get_user_awards( $user_id ) ) );

		ob_start();
		include WPS_RWPR_DIR_PATH . 'admin/partials/templates/wps-wpr-achievement-rules-settings.php';
		$html = ob_get_clean();
		$this->assertSame( 1, substr_count( $html, 'wps_wpr_ach_limited_badge' ) );
		$this->assertStringContainsString( 'Inactive (limit)', $html );
		$this->assertStringContainsString( 'wps_wpr_ach_limited_notice', $html );
		$this->assertStringContainsString( 'at most once per period', $html );

		// With the limit lifted nothing is limited.
		add_filter( 'wps_wpr_achievement_rules_limit', '__return_zero' );
		$this->assertSame( array(), WPS_WPR_Achievement_Rules::wps_wpr_get_limited_rule_ids() );
	}

	/**
	 * In "any" mode, a skipped order condition keeps the rule visible on My Account.
	 */
	public function test_any_rule_with_skipped_order_condition_is_shown() {
		$user_id    = $this->make_user( array( 'membership_level' => 'Gold' ) );
		$conditions = array(
			array( 'type' => 'membership', 'operator' => 'in', 'value' => 'Silver' ),
			array( 'type' => 'order_total', 'operator' => '>=', 'value' => '1' ),
		);
		$preview = array( 'user_id' => $user_id, 'skip_order_conditions' => true );
		$this->assertTrue( WPS_WPR_Condition_Engine::wps_wpr_evaluate( $conditions, 'any', $preview ) );
		$this->assertFalse( WPS_WPR_Condition_Engine::wps_wpr_evaluate( $conditions, 'all', $preview ) );
		// Without preview mode the order condition fails as before.
		$this->assertFalse( WPS_WPR_Condition_Engine::wps_wpr_evaluate( $conditions, 'any', array( 'user_id' => $user_id ) ) );

		update_option( WPS_WPR_Achievement_Rules::OPTION, array( $this->rule( array( 'match' => 'any', 'conditions' => $conditions ) ) ) );
		$this->assertStringContainsString( 'Spend 500 in 30 days', WPS_WPR_Achievement_Rules::wps_wpr_get_account_achievements_html( $user_id ) );
	}

	/**
	 * Invalid numbers in number conditions are dropped instead of saved as 0.
	 */
	public function test_invalid_number_condition_dropped() {
		$result = WPS_WPR_Achievement_Rules::wps_wpr_sanitize_rules(
			array(
				array(
					'name'       => 'N',
					'goal_value' => '10',
					'points'     => '5',
					'conditions' => array(
						array( 'type' => 'order_total', 'operator' => '>=', 'value' => 'abc' ),
						array( 'type' => 'order_total', 'operator' => '>=', 'value' => '12abc' ),
						array( 'type' => 'order_total', 'operator' => '>=', 'value' => '' ),
						array( 'type' => 'order_count', 'operator' => '>=', 'value' => '3' ),
						array( 'type' => 'lifetime_spend', 'operator' => '>=', 'value' => '0' ),
					),
				),
			)
		);
		$conditions = $result['rules'][0]['conditions'];
		$this->assertCount( 2, $conditions );
		$this->assertSame( 'order_count', $conditions[0]['type'] );
		$this->assertSame( '3', $conditions[0]['value'] );
		$this->assertSame( '0', $conditions[1]['value'] );
	}

	/**
	 * The customer's own locale wins over the current request language.
	 */
	public function test_language_prefers_customer_locale() {
		$user_id = $this->make_user( array( 'locale' => 'fr_FR' ) );
		$wpml    = function () {
			return 'de';
		};
		add_filter( 'wpml_current_language', $wpml );
		$this->assertTrue( $this->check( 'language', 'in', 'fr', array( 'user_id' => $user_id ) ) );
		$this->assertFalse( $this->check( 'language', 'in', 'de', array( 'user_id' => $user_id ) ) );
		remove_filter( 'wpml_current_language', $wpml );
	}
}
