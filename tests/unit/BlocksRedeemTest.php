<?php
/**
 * Tests for native Cart / Checkout block points redemption.
 *
 * @package points-and-rewards-for-wooCommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * WPS_WPR_Blocks_Redeem tests.
 */
class BlocksRedeemTest extends TestCase {

	/**
	 * Backed-up general settings.
	 *
	 * @var mixed
	 */
	private static $settings_backup;

	/**
	 * Test product id.
	 *
	 * @var int
	 */
	private static $product_id;

	/**
	 * Test customer id.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Public class instance.
	 *
	 * @var Points_Rewards_For_WooCommerce_Public
	 */
	private $public;

	/**
	 * Class under test.
	 *
	 * @var WPS_WPR_Blocks_Redeem
	 */
	private $redeem;

	/**
	 * Hooks added by this test class (removed afterwards).
	 *
	 * @var array
	 */
	private static $added_hooks = array();

	public static function setUpBeforeClass(): void {
		self::$settings_backup = get_option( 'wps_wpr_settings_gallery', null );

		$product = new WC_Product_Simple();
		$product->set_name( 'WPS-8324 block redeem product' );
		$product->set_regular_price( '20' );
		$product->set_status( 'publish' );
		self::$product_id = $product->save();

		// The plugin may be disabled on the test site, in which case its cart hooks are not registered.
		$public = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', '2.10.6' );
		if ( ! has_action( 'woocommerce_cart_calculate_fees' ) || ! self::has_plugin_callback( 'woocommerce_get_shop_coupon_data', 'wps_wpr_validate_virtual_coupon_for_points' ) ) {
			add_action( 'woocommerce_cart_calculate_fees', array( $public, 'wps_wpr_woocommerce_cart_custom_points' ), PHP_INT_MAX );
			add_filter( 'woocommerce_get_shop_coupon_data', array( $public, 'wps_wpr_validate_virtual_coupon_for_points' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_before_calculate_totals', array( $public, 'wps_wpr_woocommerce_cart_custom_points' ), PHP_INT_MAX );
			self::$added_hooks = array(
				array( 'woocommerce_before_calculate_totals', array( $public, 'wps_wpr_woocommerce_cart_custom_points' ), PHP_INT_MAX ),
				array( 'woocommerce_cart_calculate_fees', array( $public, 'wps_wpr_woocommerce_cart_custom_points' ), PHP_INT_MAX ),
				array( 'woocommerce_get_shop_coupon_data', array( $public, 'wps_wpr_validate_virtual_coupon_for_points' ), PHP_INT_MAX ),
			);
		}
	}

	public static function tearDownAfterClass(): void {
		foreach ( self::$added_hooks as $hook ) {
			remove_filter( $hook[0], $hook[1], $hook[2] );
		}
		if ( null === self::$settings_backup ) {
			delete_option( 'wps_wpr_settings_gallery' );
		} else {
			update_option( 'wps_wpr_settings_gallery', self::$settings_backup );
		}
		wp_delete_post( self::$product_id, true );
	}

	/**
	 * Whether a plugin method is attached to a hook.
	 *
	 * @param string $hook   hook.
	 * @param string $method method name.
	 * @return bool
	 */
	private static function has_plugin_callback( $hook, $method ) {
		global $wp_filter;
		if ( empty( $wp_filter[ $hook ] ) ) {
			return false;
		}
		foreach ( $wp_filter[ $hook ]->callbacks as $callbacks ) {
			foreach ( $callbacks as $cb ) {
				if ( is_array( $cb['function'] ) && $method === $cb['function'][1] ) {
					return true;
				}
			}
		}
		return false;
	}

	protected function setUp(): void {
		$this->set_settings();

		$this->user_id = wp_insert_user(
			array(
				'user_login' => 'wps8324_' . wp_generate_password( 6, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'wps8324_' . wp_generate_password( 6, false ) . '@example.test',
				'role'       => 'customer',
			)
		);
		update_user_meta( $this->user_id, 'wps_wpr_points', 1500 );
		wp_set_current_user( $this->user_id );

		wc_load_cart();
		// CLI has no session cookie; mark the session as started like a real browser session.
		$session_ref = new ReflectionProperty( WC()->session, '_has_cookie' );
		$session_ref->setAccessible( true );
		$session_ref->setValue( WC()->session, true );
		WC()->cart->empty_cart();
		WC()->session->__unset( 'wps_cart_points' );
		WC()->cart->add_to_cart( self::$product_id, 1 );
		WC()->cart->calculate_totals();

		$this->public = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', '2.10.6' );
		$this->redeem = new WPS_WPR_Blocks_Redeem( $this->public );
	}

	protected function tearDown(): void {
		if ( WC()->session ) {
			WC()->session->__unset( 'wps_cart_points' );
			WC()->session->__unset( 'wps_wpr_tax_before_coupon' );
		}
		if ( WC()->cart ) {
			WC()->cart->remove_coupons();
			WC()->cart->empty_cart();
		}
		$_SERVER['REQUEST_URI'] = '/';
		wp_set_current_user( 0 );
		require_once ABSPATH . 'wp-admin/includes/user.php';
		wp_delete_user( $this->user_id );
	}

	/**
	 * Save general settings and reset the public class option cache.
	 *
	 * @param array $overrides setting overrides.
	 */
	private function set_settings( $overrides = array() ) {
		$settings = is_array( self::$settings_backup ) ? self::$settings_backup : array();
		$settings = array_merge(
			$settings,
			array(
				'wps_wpr_general_setting_enable' => 1,
				'wps_wpr_custom_points_on_cart'  => 1,
				'wps_wpr_apply_points_checkout'  => 1,
				'wps_wpr_cart_points_rate'       => 100,
				'wps_wpr_cart_price_rate'        => 1,
				'wps_wpr_apply_points_value'     => 0,
			),
			$overrides
		);
		update_option( 'wps_wpr_settings_gallery', $settings );

		$ref = new ReflectionClass( 'Points_Rewards_For_WooCommerce_Public' );
		if ( $ref->hasProperty( 'wps_wpr_option_cache' ) ) {
			$ref->setStaticPropertyValue( 'wps_wpr_option_cache', array() );
		}
	}

	/**
	 * Run the update callback and return the exception message (or '' on success).
	 *
	 * @param array $data callback data.
	 * @return string
	 */
	private function run_update( $data ) {
		try {
			$this->redeem->wps_wpr_update_callback( $data );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
		return '';
	}

	public function test_schema_describes_every_data_key() {
		$schema = $this->redeem->wps_wpr_cart_schema();
		$data   = $this->redeem->wps_wpr_cart_data();
		$this->assertSame( array_keys( $schema ), array_keys( $data ) );
		foreach ( $schema as $prop ) {
			$this->assertTrue( $prop['readonly'] );
			$this->assertContains( $prop['type'], array( 'integer', 'boolean', 'string' ) );
		}
	}

	public function test_cart_data_for_customer_with_points() {
		$data = $this->redeem->wps_wpr_cart_data();
		$this->assertTrue( $data['enabled_cart'] );
		$this->assertTrue( $data['enabled_checkout'] );
		$this->assertTrue( $data['logged_in'] );
		$this->assertTrue( $data['can_redeem'] );
		$this->assertSame( 1500, $data['balance'] );
		$this->assertSame( 1500, $data['available'] );
		// Subtotal 20 x 100 points per 1 = 2000 points to cover; capped by the balance.
		$this->assertSame( 1500, $data['max_points'] );
		$this->assertSame( 0, $data['applied_points'] );
		$this->assertSame( 100, $data['points_rate'] );
		$this->assertStringContainsString( '100', $data['rate_text'] );
	}

	public function test_max_points_capped_by_subtotal() {
		update_user_meta( $this->user_id, 'wps_wpr_points', 5000 );
		$data = $this->redeem->wps_wpr_cart_data();
		$this->assertSame( 2000, $data['max_points'] );
	}

	public function test_guest_gets_no_balance() {
		wp_set_current_user( 0 );
		$data = $this->redeem->wps_wpr_cart_data();
		$this->assertFalse( $data['logged_in'] );
		$this->assertFalse( $data['can_redeem'] );
		$this->assertSame( 0, $data['balance'] );
	}

	public function test_restricted_user_cannot_redeem() {
		update_user_meta( $this->user_id, 'wps_wpr_restrict_user', 'yes' );
		$data = $this->redeem->wps_wpr_cart_data();
		$this->assertFalse( $data['can_redeem'] );
		$this->assertNotSame( '', $this->run_update( array( 'action' => 'apply', 'points' => '100' ) ) );
		$this->assertEmpty( WC()->session->get( 'wps_cart_points' ) );
	}

	public function test_apply_points_sets_discount_and_cart_coupon() {
		$this->assertSame( '', $this->run_update( array( 'action' => 'apply', 'points' => '500' ) ) );
		$this->assertEquals( 5.0, (float) WC()->session->get( 'wps_cart_points' ) );

		$data = $this->redeem->wps_wpr_cart_data();
		$this->assertSame( 500, $data['applied_points'] );
		$this->assertStringContainsString( '5', $data['applied_amount'] );

		WC()->cart->calculate_totals();
		$codes = array_map( 'strtolower', WC()->cart->get_applied_coupons() );
		$this->assertContains( strtolower( __( 'Cart Discount', 'points-and-rewards-for-woocommerce' ) ), $codes );
		$this->assertEquals( 5.0, (float) WC()->cart->get_discount_total() );
	}

	public function test_apply_more_than_balance_is_rejected() {
		$message = $this->run_update( array( 'action' => 'apply', 'points' => '1501' ) );
		$this->assertStringContainsString( 'valid points', $message );
		$this->assertEmpty( WC()->session->get( 'wps_cart_points' ) );
	}

	public function test_invalid_points_are_rejected() {
		foreach ( array( 'abc', '-5', '10.5', '0', '' ) as $bad ) {
			$this->assertNotSame( '', $this->run_update( array( 'action' => 'apply', 'points' => $bad ) ), "'{$bad}' should be rejected" );
		}
		$this->assertEmpty( WC()->session->get( 'wps_cart_points' ) );
	}

	public function test_minimum_points_rule_enforced() {
		$this->set_settings( array( 'wps_wpr_apply_points_value' => 2000 ) );
		$this->assertNotSame( '', $this->run_update( array( 'action' => 'apply', 'points' => '100' ) ) );
		$this->assertSame( 2000, $this->redeem->wps_wpr_cart_data()['min_points'] );
	}

	public function test_apply_rejected_when_redemption_disabled() {
		$this->set_settings(
			array(
				'wps_wpr_custom_points_on_cart' => 0,
				'wps_wpr_apply_points_checkout' => 0,
			)
		);
		$this->assertNotSame( '', $this->run_update( array( 'action' => 'apply', 'points' => '100' ) ) );
		$this->assertEmpty( WC()->session->get( 'wps_cart_points' ) );
	}

	public function test_unknown_action_rejected() {
		$this->assertNotSame( '', $this->run_update( array( 'action' => 'steal' ) ) );
	}

	public function test_remove_points_clears_discount() {
		$this->run_update( array( 'action' => 'apply', 'points' => '300' ) );
		WC()->cart->calculate_totals();
		$this->assertSame( '', $this->run_update( array( 'action' => 'remove' ) ) );
		$this->assertEmpty( WC()->session->get( 'wps_cart_points' ) );
		WC()->cart->calculate_totals();
		$this->assertEquals( 0.0, (float) WC()->cart->get_discount_total() );
		$this->assertSame( 0, $this->redeem->wps_wpr_cart_data()['applied_points'] );
	}

	public function test_removing_coupon_chip_in_store_api_clears_points() {
		$this->run_update( array( 'action' => 'apply', 'points' => '300' ) );
		$label = __( 'Cart Discount', 'points-and-rewards-for-woocommerce' );

		// Outside the Store API the hook does nothing (classic cart handles it itself).
		$_SERVER['REQUEST_URI'] = '/cart/';
		$this->redeem->wps_wpr_coupon_removed( $label );
		$this->assertNotEmpty( WC()->session->get( 'wps_cart_points' ) );

		// Another coupon removed in the Store API keeps the points.
		$_SERVER['REQUEST_URI'] = '/' . rest_get_url_prefix() . '/wc/store/v1/cart/remove-coupon';
		$this->redeem->wps_wpr_coupon_removed( 'summer10' );
		$this->assertNotEmpty( WC()->session->get( 'wps_cart_points' ) );

		$this->redeem->wps_wpr_coupon_removed( $label );
		$this->assertEmpty( WC()->session->get( 'wps_cart_points' ) );
	}

	public function test_store_api_cart_response_contains_extension_data() {
		$response = rest_do_request( new WP_REST_Request( 'GET', '/wc/store/v1/cart' ) );
		$data     = json_decode( wp_json_encode( $response->get_data() ), true );
		if ( empty( $data['extensions']['wps-wpr'] ) ) {
			// Plugin disabled on this site at load time: register the extension now.
			$this->redeem->wps_wpr_register_store_api();
			$response = rest_do_request( new WP_REST_Request( 'GET', '/wc/store/v1/cart' ) );
			$data     = json_decode( wp_json_encode( $response->get_data() ), true );
		}
		$this->assertSame( 200, $response->get_status() );
		$this->assertArrayHasKey( 'wps-wpr', $data['extensions'] );
		$ext = $data['extensions']['wps-wpr'];
		$this->assertArrayHasKey( 'balance', $ext );
		$this->assertArrayHasKey( 'max_points', $ext );
	}

	public function test_legacy_ajax_helpers_still_share_logic() {
		$response = $this->public->wps_wpr_apply_points_to_cart( '200' );
		$this->assertTrue( $response['result'] );
		$this->assertEquals( 2.0, (float) WC()->session->get( 'wps_cart_points' ) );
		$response = $this->public->wps_wpr_remove_points_from_cart();
		$this->assertTrue( $response['result'] );
		$this->assertEmpty( WC()->session->get( 'wps_cart_points' ) );
	}
}
