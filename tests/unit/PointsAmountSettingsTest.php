<?php
/**
 * Tests for the "earn with or without tax / shipping" settings (WPS-8328).
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Class PointsAmountSettingsTest.
 */
class PointsAmountSettingsTest extends TestCase {

	/**
	 * Option backups (name => value or null when missing).
	 *
	 * @var array
	 */
	private $backup = array();

	/**
	 * Created tax rate id.
	 *
	 * @var int
	 */
	private $tax_rate_id = 0;

	/**
	 * Created ids to clean up.
	 *
	 * @var array
	 */
	private $orders   = array();
	private $products = array();
	private $users    = array();

	/**
	 * Hooks added by hook_award_handlers(), removed in tearDown.
	 *
	 * @var array
	 */
	private $added_hooks = array();

	/**
	 * Back up options, switch taxes on and add a 10% rate (also on shipping).
	 */
	protected function setUp(): void {
		foreach ( array( 'wps_wpr_settings_gallery', 'wps_wpr_coupons_gallery', 'wps_wpr_order_total_settings', 'woocommerce_calc_taxes', 'woocommerce_prices_include_tax', 'woocommerce_tax_based_on' ) as $name ) {
			$this->backup[ $name ] = get_option( $name, null );
		}
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_tax_based_on', 'billing' );

		$this->tax_rate_id = WC_Tax::_insert_tax_rate(
			array(
				'tax_rate_country'  => '',
				'tax_rate_state'    => '',
				'tax_rate'          => '10.0000',
				'tax_rate_name'     => 'WPS-8328 test tax',
				'tax_rate_priority' => '1',
				'tax_rate_compound' => '0',
				'tax_rate_shipping' => '1',
				'tax_rate_order'    => '1',
				'tax_rate_class'    => '',
			)
		);
		WC_Cache_Helper::invalidate_cache_group( 'taxes' );
		$this->reset_public_option_cache();
	}

	/**
	 * Restore options and delete created data.
	 */
	protected function tearDown(): void {
		foreach ( $this->added_hooks as $hook ) {
			remove_action( $hook[0], $hook[1], $hook[2] );
		}
		$this->added_hooks = array();
		foreach ( $this->orders as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				foreach ( $order->get_refunds() as $refund ) {
					$refund->delete( true );
				}
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
		if ( $this->tax_rate_id ) {
			WC_Tax::_delete_tax_rate( $this->tax_rate_id );
		}
		foreach ( $this->backup as $name => $value ) {
			if ( null === $value ) {
				delete_option( $name );
			} else {
				update_option( $name, $value );
			}
		}
		WC_Cache_Helper::invalidate_cache_group( 'taxes' );
		$this->reset_public_option_cache();
		$_POST = array();
	}

	/**
	 * Clear the public class's static option cache so new settings are read.
	 */
	private function reset_public_option_cache() {
		if ( ! class_exists( 'Points_Rewards_For_WooCommerce_Public' ) ) {
			return;
		}
		$prop = new ReflectionProperty( 'Points_Rewards_For_WooCommerce_Public', 'wps_wpr_option_cache' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
	}

	/**
	 * Save the earning calculation settings into the General tab option.
	 *
	 * @param string $mode    incl or excl.
	 * @param int    $exclude 1 to exclude shipping.
	 */
	private function set_mode( $mode, $exclude = 0 ) {
		$settings = get_option( 'wps_wpr_settings_gallery', array() );
		$settings = is_array( $settings ) ? $settings : array();
		$settings[ WPS_WPR_Points_Amount_Settings::TAX_KEY ]      = $mode;
		$settings[ WPS_WPR_Points_Amount_Settings::SHIPPING_KEY ] = $exclude;
		update_option( 'wps_wpr_settings_gallery', $settings );
	}

	/**
	 * Order of one 100.00 product plus 10.00 shipping, with 10% tax on both.
	 * Total = 100 + 10 + 10 (item tax) + 1 (shipping tax) = 121.
	 *
	 * @param int $user_id customer.
	 * @return WC_Order
	 */
	private function create_taxed_order( $user_id = 0 ) {
		$product = new WC_Product_Simple();
		$product->set_name( 'WPS-8328 test product' );
		$product->set_regular_price( '100' );
		$product->set_tax_status( 'taxable' );
		$product->save();
		$this->products[] = $product->get_id();

		$order = wc_create_order( array( 'customer_id' => $user_id ) );
		$order->add_product( $product, 1 );
		$order->set_billing_country( 'US' );
		$order->set_billing_email( 'wps8328@example.test' );

		$shipping = new WC_Order_Item_Shipping();
		$shipping->set_method_title( 'Flat rate' );
		$shipping->set_method_id( 'flat_rate' );
		$shipping->set_total( '10' );
		$order->add_item( $shipping );

		$order->calculate_totals( true );
		$order->save();
		$this->orders[] = $order->get_id();
		return $order;
	}

	public function test_order_totals_are_set_up_as_expected() {
		$order = $this->create_taxed_order();
		$this->assertEqualsWithDelta( 121.0, (float) $order->get_total(), 0.001 );
		$this->assertEqualsWithDelta( 11.0, (float) $order->get_total_tax(), 0.001 );
		$this->assertEqualsWithDelta( 10.0, (float) $order->get_shipping_total(), 0.001 );
		$this->assertEqualsWithDelta( 1.0, (float) $order->get_shipping_tax(), 0.001 );
	}

	public function test_default_is_order_total_including_tax_and_shipping() {
		$settings = get_option( 'wps_wpr_settings_gallery', array() );
		$settings = is_array( $settings ) ? $settings : array();
		unset( $settings[ WPS_WPR_Points_Amount_Settings::TAX_KEY ], $settings[ WPS_WPR_Points_Amount_Settings::SHIPPING_KEY ] );
		update_option( 'wps_wpr_settings_gallery', $settings );

		$this->assertSame( 'incl', WPS_WPR_Points_Amount_Settings::wps_wpr_get_tax_mode() );
		$this->assertFalse( WPS_WPR_Points_Amount_Settings::wps_wpr_exclude_shipping() );

		$order = $this->create_taxed_order();
		// Same as the order total used before this setting existed.
		$this->assertEqualsWithDelta( (float) $order->get_total(), wps_wpr_get_order_amount_for_points( $order ), 0.001 );
		$this->assertEqualsWithDelta( 121.0, wps_wpr_get_order_amount_for_points( $order->get_id() ), 0.001 );
	}

	public function test_excluding_tax() {
		$this->set_mode( 'excl' );
		$order = $this->create_taxed_order();
		$this->assertEqualsWithDelta( 110.0, wps_wpr_get_order_amount_for_points( $order ), 0.001 );
	}

	public function test_including_tax_excluding_shipping() {
		$this->set_mode( 'incl', 1 );
		$order = $this->create_taxed_order();
		// Shipping and its tax are removed; item tax stays.
		$this->assertEqualsWithDelta( 110.0, wps_wpr_get_order_amount_for_points( $order ), 0.001 );
	}

	public function test_excluding_tax_and_shipping() {
		$this->set_mode( 'excl', 1 );
		$order = $this->create_taxed_order();
		$this->assertEqualsWithDelta( 100.0, wps_wpr_get_order_amount_for_points( $order ), 0.001 );
	}

	public function test_calculate_amount_never_negative_and_filterable() {
		$this->set_mode( 'excl', 1 );
		$this->assertSame( 0.0, (float) WPS_WPR_Points_Amount_Settings::wps_wpr_calculate_amount( 5, 10, 10, 1 ) );

		$filter = function () {
			return 'incl';
		};
		add_filter( 'wps_wpr_points_amount_tax_mode', $filter );
		$this->assertSame( 'incl', WPS_WPR_Points_Amount_Settings::wps_wpr_get_tax_mode() );
		remove_filter( 'wps_wpr_points_amount_tax_mode', $filter );
	}

	public function test_invalid_order_returns_zero() {
		$this->assertSame( 0.0, (float) wps_wpr_get_order_amount_for_points( 0 ) );
		$this->assertSame( 0.0, (float) wps_wpr_get_cart_amount_for_points( new stdClass() ) );
	}

	public function test_save_settings_sanitizes_input() {
		$obj = new WPS_WPR_Points_Amount_Settings();

		$_POST  = array( WPS_WPR_Points_Amount_Settings::TAX_KEY => 'excl', WPS_WPR_Points_Amount_Settings::SHIPPING_KEY => '1' );
		$result = $obj->wps_wpr_save_settings( array( 'other' => 1 ) );
		$this->assertSame( 'excl', $result[ WPS_WPR_Points_Amount_Settings::TAX_KEY ] );
		$this->assertSame( 1, $result[ WPS_WPR_Points_Amount_Settings::SHIPPING_KEY ] );
		$this->assertSame( 1, $result['other'] );

		$_POST  = array( WPS_WPR_Points_Amount_Settings::TAX_KEY => 'bogus' );
		$result = $obj->wps_wpr_save_settings( array() );
		$this->assertSame( 'incl', $result[ WPS_WPR_Points_Amount_Settings::TAX_KEY ] );
		$this->assertSame( 0, $result[ WPS_WPR_Points_Amount_Settings::SHIPPING_KEY ] );
	}

	public function test_settings_section_is_added_to_general_tab() {
		$fields = apply_filters( 'wps_wpr_general_settings', array() );
		$ids    = array();
		foreach ( $fields as $field ) {
			if ( is_array( $field ) && ! empty( $field['id'] ) ) {
				$ids[] = $field['id'];
			}
		}
		$this->assertContains( WPS_WPR_Points_Amount_Settings::TAX_KEY, $ids );
		$this->assertContains( WPS_WPR_Points_Amount_Settings::SHIPPING_KEY, $ids );
	}

	/**
	 * Per currency points awarded on order completion follow the setting.
	 *
	 * @param string $mode     tax mode.
	 * @param int    $exclude  exclude shipping.
	 * @param int    $expected expected points at 1 point per 1.00.
	 * @dataProvider per_currency_cases
	 */
	public function test_per_currency_points_follow_setting( $mode, $exclude, $expected ) {
		$this->set_mode( $mode, $exclude );
		update_option(
			'wps_wpr_coupons_gallery',
			array(
				'wps_wpr_coupon_conversion_enable' => 1,
				'wps_wpr_coupon_conversion_points' => 1,
				'wps_wpr_coupon_conversion_price'  => 1,
			)
		);
		$this->reset_public_option_cache();

		$user_id       = wp_insert_user(
			array(
				'user_login' => 'wps8328_' . wp_generate_password( 6, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'wps8328_' . wp_generate_password( 6, false ) . '@example.test',
				'role'       => 'customer',
			)
		);
		$this->users[] = $user_id;
		$order         = $this->create_taxed_order( $user_id );

		$public = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION );
		$public->wps_wpr_woocommerce_order_status_changed( $order->get_id(), 'processing', 'completed' );

		$this->assertSame( $expected, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
	}

	/**
	 * Cases for test_per_currency_points_follow_setting.
	 *
	 * @return array
	 */
	public static function per_currency_cases() {
		return array(
			'including tax (default)'    => array( 'incl', 0, 121 ),
			'excluding tax'              => array( 'excl', 0, 110 ),
			'including tax, no shipping' => array( 'incl', 1, 110 ),
			'excluding tax, no shipping' => array( 'excl', 1, 100 ),
		);
	}
	/**
	 * Hook the award / reversal handlers as the plugin does when it is enabled (the site may
	 * have the plugin switched off, in which case they were not hooked at load time).
	 */
	private function hook_award_handlers() {
		$settings = get_option( 'wps_wpr_settings_gallery', array() );
		$settings = is_array( $settings ) ? $settings : array();
		$settings['wps_wpr_general_setting_enable'] = 1;
		update_option( 'wps_wpr_settings_gallery', $settings );

		$public    = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION );
		$lifecycle = new WPS_WPR_Order_Points_Lifecycle();
		// The lifecycle constructor hooked itself; remember those hooks for removal.
		$this->added_hooks[] = array( 'woocommerce_order_status_changed', array( $lifecycle, 'wps_wpr_restore_reversed_order_points' ), 1 );
		$this->added_hooks[] = array( 'woocommerce_order_status_changed', array( $lifecycle, 'wps_wpr_add_missing_item_points' ), 5 );
		$this->added_hooks[] = array( 'woocommerce_order_status_changed', array( $lifecycle, 'wps_wpr_reverse_order_rewards_points' ), 25 );
		$this->added_hooks[] = array( 'woocommerce_order_status_changed', array( $lifecycle, 'wps_wpr_settle_partial_refunds_on_reversal' ), 99 );
		$this->added_hooks[] = array( 'woocommerce_order_partially_refunded', array( $lifecycle, 'wps_wpr_deduct_partial_refund_points' ), 10 );

		$callback = array( $public, 'wps_wpr_woocommerce_order_status_changed' );
		if ( ! has_action( 'woocommerce_order_status_changed', $callback ) ) {
			add_action( 'woocommerce_order_status_changed', $callback, 10, 3 );
			$this->added_hooks[] = array( 'woocommerce_order_status_changed', $callback, 10 );
		}
	}

	/**
	 * Per currency 1 point per 1.00 and order total ranges that reveal the basis:
	 * under 110 => 3, 110 to 115 => 5, 115.01 and above => 9.
	 */
	private function enable_amount_rules() {
		update_option(
			'wps_wpr_coupons_gallery',
			array(
				'wps_wpr_coupon_conversion_enable' => 1,
				'wps_wpr_coupon_conversion_points' => 1,
				'wps_wpr_coupon_conversion_price'  => 1,
			)
		);
		update_option(
			'wps_wpr_order_total_settings',
			array(
				'wps_wpr_thankyouorder_enable'       => 1,
				'wps_wpr_thankyouorder_minimum'      => array( '1', '110', '115.01' ),
				'wps_wpr_thankyouorder_maximum'      => array( '109.99', '115', '' ),
				'wps_wpr_thankyouorder_current_type' => array( '3', '5', '9' ),
			)
		);
		$this->reset_public_option_cache();
	}

	/**
	 * New customer.
	 *
	 * @return int
	 */
	private function create_customer() {
		$user_id       = wp_insert_user(
			array(
				'user_login' => 'wps8328_' . wp_generate_password( 6, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'wps8328_' . wp_generate_password( 6, false ) . '@example.test',
				'role'       => 'customer',
			)
		);
		$this->users[] = $user_id;
		return $user_id;
	}

	/**
	 * Current points of a user.
	 *
	 * @param int $user_id user.
	 * @return int
	 */
	private function points( $user_id ) {
		wp_cache_delete( $user_id, 'user_meta' );
		return (int) get_user_meta( $user_id, 'wps_wpr_points', true );
	}

	/**
	 * Per currency points logged for the order.
	 *
	 * @param int $user_id user.
	 * @return int
	 */
	private function per_currency_logged( $user_id ) {
		wp_cache_delete( $user_id, 'user_meta' );
		$log = get_user_meta( $user_id, 'points_details', true );
		return isset( $log['pro_conversion_points'][0]['pro_conversion_points'] ) ? (int) $log['pro_conversion_points'][0]['pro_conversion_points'] : 0;
	}

	/**
	 * Complete the taxed order through the real status hooks.
	 *
	 * @param int $user_id customer.
	 * @return WC_Order
	 */
	private function award_order( $user_id ) {
		$this->hook_award_handlers();
		$order = $this->create_taxed_order( $user_id );
		$order->update_status( 'processing' );
		$order = wc_get_order( $order->get_id() );
		$order->update_status( 'completed' );
		return wc_get_order( $order->get_id() );
	}

	/**
	 * Partially refund the item line ("item") or the shipping line ("ship") in full, with tax.
	 *
	 * @param WC_Order $order order.
	 * @param string   $kind  item or ship.
	 * @return WC_Order_Refund
	 */
	private function refund_line( $order, $kind ) {
		$order  = wc_get_order( $order->get_id() );
		$lines  = array();
		$amount = 0;
		foreach ( $order->get_items( array( 'line_item', 'shipping' ) ) as $item_id => $item ) {
			$is_ship = $item->is_type( 'shipping' );
			if ( ( 'ship' === $kind ) !== $is_ship ) {
				continue;
			}
			$total           = (float) $item->get_total();
			$tax             = (float) $item->get_total_tax();
			$lines[ $item_id ] = array(
				'qty'          => $is_ship ? 0 : $item->get_quantity(),
				'refund_total' => $total,
				'refund_tax'   => array( $this->tax_rate_id => $tax ),
			);
			$amount         += $total + $tax;
		}
		$refund = wc_create_refund(
			array(
				'order_id'       => $order->get_id(),
				'amount'         => wc_format_decimal( $amount, 2 ),
				'line_items'     => $lines,
				'refund_payment' => false,
				'restock_items'  => false,
			)
		);
		$this->assertNotWPError( $refund );
		return $refund;
	}

	/**
	 * WP_Error guard.
	 *
	 * @param mixed $value value.
	 */
	private function assertNotWPError( $value ) {
		$this->assertFalse( is_wp_error( $value ), is_wp_error( $value ) ? $value->get_error_message() : '' );
	}

	/**
	 * A partial refund takes back per currency points on the basis the order was awarded on.
	 *
	 * @param string $mode     tax mode.
	 * @param int    $exclude  exclude shipping.
	 * @param string $kind     refunded line: item or ship.
	 * @param int    $awarded  expected per currency points awarded.
	 * @param int    $deducted expected points taken back by the refund.
	 * @dataProvider partial_refund_cases
	 */
	public function test_partial_refund_deduction_follows_award_basis( $mode, $exclude, $kind, $awarded, $deducted ) {
		$this->set_mode( $mode, $exclude );
		update_option(
			'wps_wpr_coupons_gallery',
			array(
				'wps_wpr_coupon_conversion_enable' => 1,
				'wps_wpr_coupon_conversion_points' => 1,
				'wps_wpr_coupon_conversion_price'  => 1,
			)
		);
		$this->reset_public_option_cache();
		$user_id = $this->create_customer();
		$order   = $this->award_order( $user_id );
		$this->assertSame( $awarded, $this->per_currency_logged( $user_id ) );
		$before = $this->points( $user_id );

		$this->refund_line( $order, $kind );
		$this->assertSame( $deducted, $before - $this->points( $user_id ) );
	}

	/**
	 * Cases for test_partial_refund_deduction_follows_award_basis.
	 * Order: item 100 + tax 10, shipping 10 + tax 1 = 121.
	 *
	 * @return array
	 */
	public static function partial_refund_cases() {
		return array(
			'default, item refund (as before)'     => array( 'incl', 0, 'item', 121, 110 ),
			'default, shipping refund (as before)' => array( 'incl', 0, 'ship', 121, 11 ),
			'exclude shipping, item refund'        => array( 'incl', 1, 'item', 110, 110 ),
			'exclude shipping, shipping refund'    => array( 'incl', 1, 'ship', 110, 0 ),
			'excl tax and shipping, item refund'   => array( 'excl', 1, 'item', 100, 100 ),
			'excl tax and shipping, ship refund'   => array( 'excl', 1, 'ship', 100, 0 ),
			'excl tax, item refund'                => array( 'excl', 0, 'item', 110, 100 ),
		);
	}

	public function test_award_stores_basis_on_order() {
		$this->set_mode( 'excl', 1 );
		$this->enable_amount_rules();
		$order = $this->award_order( $this->create_customer() );

		$basis = WPS_WPR_Points_Amount_Settings::wps_wpr_get_stored_basis( $order );
		$this->assertSame( 'excl', $basis['tax_mode'] );
		$this->assertSame( 1, $basis['exclude_shipping'] );
		$this->assertEqualsWithDelta( 100.0, $basis['amount'], 0.001 );
		$this->assertEqualsWithDelta( 100.0, wps_wpr_get_awarded_order_amount_for_points( $order ), 0.001 );
	}

	public function test_refund_after_setting_change_uses_stored_basis() {
		$this->set_mode( 'incl', 0 );
		$this->enable_amount_rules();
		$user_id = $this->create_customer();
		$order   = $this->award_order( $user_id );
		// 121 per currency + 9 order total points.
		$this->assertSame( 130, $this->points( $user_id ) );

		// Settings change after the award.
		$this->set_mode( 'excl', 1 );
		$this->reset_public_option_cache();

		// Shipping was part of the award basis, so a shipping refund still takes back its share.
		$this->refund_line( $order, 'ship' );
		$this->assertSame( 119, $this->points( $user_id ) );

		// The order total fallback recalculates on the award basis (121 => 9, not 110 => 5).
		wps_wpr_hpos_update_meta_data( $order->get_id(), 'wps_wpr_order_total_awarded_points', 0 );
		$order = wc_get_order( $order->get_id() );
		$order->update_status( 'cancelled' );
		$this->assertSame( 0, $this->points( $user_id ) );
	}

	public function test_legacy_order_without_basis_uses_order_total() {
		$this->set_mode( 'incl', 0 );
		$this->enable_amount_rules();
		$user_id = $this->create_customer();
		$order   = $this->award_order( $user_id );
		$this->assertSame( 130, $this->points( $user_id ) );

		// An order awarded before the basis was stored.
		wps_wpr_hpos_update_meta_data( $order->get_id(), WPS_WPR_Points_Amount_Settings::BASIS_META, '' );
		wps_wpr_hpos_update_meta_data( $order->get_id(), 'wps_wpr_order_total_awarded_points', 0 );
		$this->assertNull( WPS_WPR_Points_Amount_Settings::wps_wpr_get_stored_basis( $order->get_id() ) );

		$this->set_mode( 'excl', 1 );
		$this->reset_public_option_cache();

		$this->refund_line( $order, 'ship' );
		$this->assertSame( 119, $this->points( $user_id ) );
		$this->assertNull( WPS_WPR_Points_Amount_Settings::wps_wpr_get_stored_basis( $order->get_id() ) );

		$order = wc_get_order( $order->get_id() );
		$order->update_status( 'cancelled' );
		$this->assertSame( 0, $this->points( $user_id ) );
	}
}
