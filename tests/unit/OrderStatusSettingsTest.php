<?php
/**
 * Tests for the configurable award / reversal order statuses (WPS-8321).
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Class OrderStatusSettingsTest.
 */
class OrderStatusSettingsTest extends TestCase {

	/**
	 * Option backups.
	 *
	 * @var array
	 */
	private $backup = array();

	/**
	 * Created objects to clean up.
	 *
	 * @var array
	 */
	private $users = array();

	/**
	 * Created orders.
	 *
	 * @var array
	 */
	private $orders = array();

	/**
	 * Created products.
	 *
	 * @var array
	 */
	private $products = array();

	/**
	 * Hooks added by this test.
	 *
	 * @var array
	 */
	private static $added_hooks = array();

	/**
	 * Make sure the order points handlers run even when the points program is
	 * switched off on the test site (they are only registered when it is on).
	 */
	public static function setUpBeforeClass(): void {
		if ( ! self::has_method_hook( 'woocommerce_order_status_changed', 'wps_wpr_woocommerce_order_status_changed' ) ) {
			$public = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION );
			add_action( 'woocommerce_order_status_changed', array( $public, 'wps_wpr_woocommerce_order_status_changed' ), 10, 3 );
			self::$added_hooks[] = array( 'woocommerce_order_status_changed', array( $public, 'wps_wpr_woocommerce_order_status_changed' ), 10 );
		}
		if ( ! self::has_method_hook( 'woocommerce_order_status_changed', 'wps_wpr_restore_reversed_order_points' ) ) {
			$lifecycle = new WPS_WPR_Order_Points_Lifecycle();
			foreach ( array( 'wps_wpr_restore_reversed_order_points' => 1, 'wps_wpr_add_missing_item_points' => 5, 'wps_wpr_reverse_order_rewards_points' => 25, 'wps_wpr_settle_partial_refunds_on_reversal' => 99 ) as $method => $priority ) {
				self::$added_hooks[] = array( 'woocommerce_order_status_changed', array( $lifecycle, $method ), $priority );
			}
			self::$added_hooks[] = array( 'woocommerce_order_partially_refunded', array( $lifecycle, 'wps_wpr_deduct_partial_refund_points' ), 10 );
		}
	}

	/**
	 * Remove the hooks added in setUpBeforeClass().
	 */
	public static function tearDownAfterClass(): void {
		foreach ( self::$added_hooks as $hook ) {
			remove_action( $hook[0], $hook[1], $hook[2] );
		}
		self::$added_hooks = array();
	}

	/**
	 * Whether a method with this name is hooked.
	 *
	 * @param string $hook   hook.
	 * @param string $method method name.
	 * @return bool
	 */
	private static function has_method_hook( $hook, $method ) {
		global $wp_filter;
		if ( empty( $wp_filter[ $hook ] ) ) {
			return false;
		}
		foreach ( $wp_filter[ $hook ]->callbacks as $callbacks ) {
			foreach ( $callbacks as $cb ) {
				if ( is_array( $cb['function'] ) && isset( $cb['function'][1] ) && $method === $cb['function'][1] ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Back up options and set a 1 point per 1 currency earning rate.
	 */
	protected function setUp(): void {
		foreach ( array( 'wps_wpr_settings_gallery', 'wps_wpr_coupons_gallery' ) as $option ) {
			$this->backup[ $option ] = get_option( $option, null );
		}
		$general = get_option( 'wps_wpr_settings_gallery', array() );
		$general = is_array( $general ) ? $general : array();
		unset( $general['wps_wpr_points_award_order_status'], $general['wps_wpr_points_reversal_order_statuses'] );
		$general['wps_wpr_general_setting_enable'] = 1;
		update_option( 'wps_wpr_settings_gallery', $general );
		update_option(
			'wps_wpr_coupons_gallery',
			array(
				'wps_wpr_coupon_conversion_enable' => 1,
				'wps_wpr_coupon_conversion_price'  => 1,
				'wps_wpr_coupon_conversion_points' => 1,
			)
		);
		$this->reset_public_option_cache();
		$GLOBALS['wps_wpr_award_status_decisions'] = array();
	}

	/**
	 * Restore options and delete created data.
	 */
	protected function tearDown(): void {
		foreach ( $this->orders as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$order->delete( true );
			}
		}
		foreach ( $this->products as $product_id ) {
			wp_delete_post( $product_id, true );
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->users as $user_id ) {
			wp_delete_user( $user_id );
		}
		foreach ( $this->backup as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
		$_POST = array();
		$this->reset_public_option_cache();
	}

	/**
	 * Clear the public class's per-request option cache.
	 */
	private function reset_public_option_cache() {
		$prop = new ReflectionProperty( 'Points_Rewards_For_WooCommerce_Public', 'wps_wpr_option_cache' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
	}

	/**
	 * Save the two settings directly.
	 *
	 * @param string $award    award status.
	 * @param array  $reversal extra reversal statuses.
	 */
	private function save_statuses( $award, $reversal = array() ) {
		$general = get_option( 'wps_wpr_settings_gallery', array() );
		$general['wps_wpr_points_award_order_status']      = $award;
		$general['wps_wpr_points_reversal_order_statuses'] = $reversal;
		update_option( 'wps_wpr_settings_gallery', $general );
	}

	/**
	 * Create a customer with a pending order worth 50.
	 *
	 * @return array array( user id, order id ).
	 */
	private function create_order() {
		$user_id       = wp_insert_user(
			array(
				'user_login' => 'wps8321_' . wp_generate_password( 8, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'wps8321_' . wp_generate_password( 8, false ) . '@example.test',
				'role'       => 'customer',
			)
		);
		$this->users[] = $user_id;

		$product = new WC_Product_Simple();
		$product->set_name( 'WPS-8321 test product' );
		$product->set_regular_price( '50' );
		$product->save();
		$this->products[] = $product->get_id();

		$order = wc_create_order( array( 'customer_id' => $user_id ) );
		$order->add_product( $product, 1 );
		$order->calculate_totals( false );
		$order->save();
		$this->orders[] = $order->get_id();

		return array( $user_id, $order->get_id() );
	}

	/**
	 * Move the order to a status (fires woocommerce_order_status_changed).
	 *
	 * @param int    $order_id order id.
	 * @param string $status   status.
	 */
	private function set_status( $order_id, $status ) {
		$order = wc_get_order( $order_id );
		$order->update_status( $status );
	}

	/**
	 * Points balance.
	 *
	 * @param int $user_id user id.
	 * @return int
	 */
	private function points( $user_id ) {
		return (int) get_user_meta( $user_id, 'wps_wpr_points', true );
	}

	public function test_defaults_are_unchanged() {
		$this->assertSame( 'completed', wps_wpr_get_points_award_status() );
		$this->assertSame( array( 'cancelled', 'refunded' ), wps_wpr_get_points_reversal_statuses() );
		$this->assertTrue( wps_wpr_is_points_reversal_status( 'wc-cancelled' ) );
		$this->assertFalse( wps_wpr_is_points_reversal_status( 'failed' ) );
	}

	public function test_saved_award_status_is_used() {
		$this->save_statuses( 'processing' );
		$this->assertSame( 'processing', wps_wpr_get_points_award_status() );
	}

	public function test_reversal_statuses_never_include_award_or_live_statuses() {
		$this->save_statuses( 'processing', array( 'failed', 'processing', 'completed', 'pending' ) );
		$this->assertSame( array( 'cancelled', 'refunded', 'failed' ), wps_wpr_get_points_reversal_statuses() );
		$this->assertSame( array( 'failed' ), wps_wpr_get_extra_points_reversal_statuses() );
	}

	public function test_save_sanitizes_posted_values() {
		$settings = new WPS_WPR_Order_Status_Settings();

		$_POST = array(
			'wps_wpr_points_award_order_status'      => 'failed',
			'wps_wpr_points_reversal_order_statuses' => array( 'failed', 'bogus', 'completed', 'failed' ),
		);
		$saved = $settings->wps_wpr_save_settings( array() );
		$this->assertSame( 'completed', $saved['wps_wpr_points_award_order_status'] );
		$this->assertSame( array( 'failed' ), $saved['wps_wpr_points_reversal_order_statuses'] );

		$_POST = array(
			'wps_wpr_points_award_order_status'      => 'on-hold',
			'wps_wpr_points_reversal_order_statuses' => array( 'on-hold' ),
		);
		$saved = $settings->wps_wpr_save_settings( array() );
		$this->assertSame( 'completed', $saved['wps_wpr_points_award_order_status'], 'On hold can never award points.' );
		$this->assertSame( array( 'on-hold' ), $saved['wps_wpr_points_reversal_order_statuses'] );
	}

	public function test_award_status_options_exclude_unpaid_statuses() {
		$options = WPS_WPR_Order_Status_Settings::wps_wpr_award_status_options();
		$this->assertSame( 'completed', array_key_first( $options ) );
		$this->assertArrayHasKey( 'processing', $options );
		foreach ( array( 'pending', 'failed', 'cancelled', 'refunded', 'on-hold' ) as $status ) {
			$this->assertArrayNotHasKey( $status, $options );
		}
	}

	public function test_points_awarded_on_completed_by_default() {
		list( $user_id, $order_id ) = $this->create_order();
		$this->set_status( $order_id, 'processing' );
		$this->assertSame( 0, $this->points( $user_id ) );
		$this->set_status( $order_id, 'completed' );
		$this->assertSame( 50, $this->points( $user_id ) );
	}

	public function test_points_awarded_on_processing_and_not_twice() {
		$this->save_statuses( 'processing' );
		list( $user_id, $order_id ) = $this->create_order();

		$this->set_status( $order_id, 'processing' );
		$this->assertSame( 50, $this->points( $user_id ) );

		$this->set_status( $order_id, 'completed' );
		$this->assertSame( 50, $this->points( $user_id ), 'Completing an already awarded order must not award again.' );
	}

	public function test_cancelled_still_reverses_points() {
		$this->save_statuses( 'processing' );
		list( $user_id, $order_id ) = $this->create_order();
		$this->set_status( $order_id, 'processing' );
		$this->assertSame( 50, $this->points( $user_id ) );
		$this->set_status( $order_id, 'cancelled' );
		$this->assertSame( 0, $this->points( $user_id ) );
	}

	public function test_failed_does_not_reverse_unless_configured() {
		list( $user_id, $order_id ) = $this->create_order();
		$this->set_status( $order_id, 'completed' );
		$this->assertSame( 50, $this->points( $user_id ) );
		$this->set_status( $order_id, 'failed' );
		$this->assertSame( 50, $this->points( $user_id ) );
	}

	public function test_configured_status_reverses_and_reaward_works() {
		$this->save_statuses( 'completed', array( 'failed' ) );
		list( $user_id, $order_id ) = $this->create_order();

		$this->set_status( $order_id, 'completed' );
		$this->assertSame( 50, $this->points( $user_id ) );

		$this->set_status( $order_id, 'failed' );
		$this->assertSame( 0, $this->points( $user_id ), 'A configured reversal status takes the points back.' );

		$this->set_status( $order_id, 'completed' );
		$this->assertSame( 50, $this->points( $user_id ), 'Moving back to the award status awards again.' );
	}

	public function test_configured_status_with_custom_award_status() {
		$this->save_statuses( 'processing', array( 'failed' ) );
		list( $user_id, $order_id ) = $this->create_order();

		$this->set_status( $order_id, 'processing' );
		$this->assertSame( 50, $this->points( $user_id ) );
		$this->set_status( $order_id, 'failed' );
		$this->assertSame( 0, $this->points( $user_id ) );
		$this->set_status( $order_id, 'failed' );
		$this->assertSame( 0, $this->points( $user_id ), 'No second deduction.' );
	}

	public function test_configured_status_on_unawarded_order_does_nothing() {
		$this->save_statuses( 'completed', array( 'failed' ) );
		list( $user_id, $order_id ) = $this->create_order();
		update_user_meta( $user_id, 'wps_wpr_points', 20 );

		$this->set_status( $order_id, 'failed' );
		$this->assertSame( 20, $this->points( $user_id ) );
	}
}
