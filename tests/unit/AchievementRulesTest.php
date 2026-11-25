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
	 * @param int    $days_ago creation date offset.
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
}
