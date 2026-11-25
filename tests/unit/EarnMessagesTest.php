<?php
/**
 * Tests for the earn-points messages.
 *
 * @package points-and-rewards-for-wooCommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Earn messages tests.
 */
class EarnMessagesTest extends TestCase {

	/**
	 * Option backups.
	 *
	 * @var array
	 */
	private $backup = array();

	/**
	 * Created post ids.
	 *
	 * @var int[]
	 */
	private $posts = array();

	/**
	 * Created user ids.
	 *
	 * @var int[]
	 */
	private $users = array();

	/**
	 * Back up options and set a known earning rate: 2 points per 1 currency unit.
	 */
	protected function setUp(): void {
		foreach ( array( 'wps_wpr_coupons_gallery', WPS_WPR_Earn_Messages::OPTION, 'wps_wpr_membership_settings' ) as $option ) {
			$this->backup[ $option ] = get_option( $option, null );
		}
		update_option(
			'wps_wpr_coupons_gallery',
			array(
				'wps_wpr_coupon_conversion_enable' => 1,
				'wps_wpr_coupon_conversion_price'  => 2,
				'wps_wpr_coupon_conversion_points' => 1,
			)
		);
		delete_option( WPS_WPR_Earn_Messages::OPTION );
		wp_set_current_user( 0 );
	}

	/**
	 * Restore options and delete created data.
	 */
	protected function tearDown(): void {
		foreach ( $this->backup as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
		foreach ( array_reverse( $this->posts ) as $post_id ) {
			$product = wc_get_product( $post_id );
			if ( $product ) {
				$product->delete( true );
			}
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->users as $user_id ) {
			wp_delete_user( $user_id );
		}
		wp_set_current_user( 0 );
	}

	/**
	 * Create a simple product.
	 *
	 * @param float $price price.
	 * @return WC_Product_Simple
	 */
	private function make_simple( $price ) {
		$product = new WC_Product_Simple();
		$product->set_name( 'WPS earn msg simple' );
		$product->set_regular_price( (string) $price );
		$product->set_tax_status( 'none' );
		$product->save();
		$this->posts[] = $product->get_id();
		return $product;
	}

	/**
	 * Create a variable product with variations at the given prices.
	 *
	 * @param array $prices prices.
	 * @return WC_Product_Variable
	 */
	private function make_variable( $prices ) {
		$attribute = new WC_Product_Attribute();
		$attribute->set_name( 'Size' );
		$attribute->set_options( array_map( 'strval', array_keys( $prices ) ) );
		$attribute->set_visible( true );
		$attribute->set_variation( true );

		$product = new WC_Product_Variable();
		$product->set_name( 'WPS earn msg variable' );
		$product->set_attributes( array( $attribute ) );
		$product->set_tax_status( 'none' );
		$product->save();
		$this->posts[] = $product->get_id();

		foreach ( $prices as $option => $price ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product->get_id() );
			$variation->set_attributes( array( 'size' => (string) $option ) );
			$variation->set_regular_price( (string) $price );
			$variation->set_tax_status( 'none' );
			$variation->save();
			$this->posts[] = $variation->get_id();
		}
		return wc_get_product( $product->get_id() );
	}

	public function test_simple_product_uses_per_currency_rate() {
		$product = $this->make_simple( 25 );
		$this->assertSame( 50, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $product, 0 ) );
	}

	public function test_assigned_points_are_added() {
		$product = $this->make_simple( 10 );
		update_post_meta( $product->get_id(), 'wps_product_points_enable', 'yes' );
		update_post_meta( $product->get_id(), 'wps_points_product_value', 7 );
		$this->assertSame( 27, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $product, 0 ) );
	}

	public function test_no_points_when_conversion_disabled() {
		update_option( 'wps_wpr_coupons_gallery', array( 'wps_wpr_coupon_conversion_enable' => 0 ) );
		$product = $this->make_simple( 25 );
		$this->assertSame( 0, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $product, 0 ) );
	}

	public function test_membership_rate_overrides_global_rate() {
		$user_id       = wp_insert_user(
			array(
				'user_login' => 'wps_em_' . wp_generate_password( 6, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'wps_em_' . wp_generate_password( 6, false ) . '@example.test',
			)
		);
		$this->users[] = $user_id;
		update_user_meta( $user_id, 'membership_level', 'Gold' );
		update_option(
			'wps_wpr_membership_settings',
			array(
				'membership_roles' => array(
					'Gold' => array(
						'wps_wpr_enable_mem_wise_per_curr' => '1',
						'wps_wpr_membership_wise_price'    => 1,
						'wps_wpr_membership_wise_points'   => 5,
					),
				),
			)
		);
		$product = $this->make_simple( 10 );
		$this->assertSame( 50, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $product, $user_id ) );
		$this->assertSame( 20, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $product, 0 ) );
	}

	public function test_variable_product_range_and_variation_points() {
		$product = $this->make_variable(
			array(
				'S' => 10,
				'L' => 30,
			)
		);
		$this->assertSame( array( 20, 60 ), WPS_WPR_Earn_Messages::wps_wpr_get_points_range( $product, 0 ) );
		$this->assertSame( '20 - 60', WPS_WPR_Earn_Messages::wps_wpr_format_range( array( 20, 60 ) ) );
		$this->assertSame( '20', WPS_WPR_Earn_Messages::wps_wpr_format_range( array( 20, 20 ) ) );

		update_option( WPS_WPR_Earn_Messages::OPTION, array( 'wps_wpr_em_enable_product' => 'yes' ) );
		$children  = $product->get_children();
		$variation = wc_get_product( $children[1] );
		$data      = ( new WPS_WPR_Earn_Messages() )->wps_wpr_add_variation_points( array(), $product, $variation );
		$this->assertSame( '60', $data['wps_wpr_earn_points'] );
	}

	public function test_message_replaces_placeholder_and_escapes() {
		$html = WPS_WPR_Earn_Messages::wps_wpr_build_message( 'Earn [POINTS] pts <b>now</b>', '50', 'product' );
		$this->assertStringContainsString( '<span class="wps_wpr_earn_message_points">50</span>', $html );
		$this->assertStringContainsString( '&lt;b&gt;now&lt;/b&gt;', $html );
		$this->assertStringContainsString( 'wps_wpr_earn_message_product', $html );
	}

	public function test_sanitize_settings() {
		$clean = WPS_WPR_Earn_Messages::wps_wpr_sanitize_settings(
			array(
				'wps_wpr_em_enable_shop'  => 'yes',
				'wps_wpr_em_enable_cart'  => 'maybe',
				'wps_wpr_em_shop_text'    => '  ',
				'wps_wpr_em_text_color'   => 'red',
				'wps_wpr_em_product_text' => 'Get [POINTS]<script>x</script>',
			)
		);
		$defaults = WPS_WPR_Earn_Messages::wps_wpr_get_default_settings();
		$this->assertSame( 'yes', $clean['wps_wpr_em_enable_shop'] );
		$this->assertSame( 'no', $clean['wps_wpr_em_enable_cart'] );
		$this->assertSame( 'no', $clean['wps_wpr_em_enable_product'] );
		$this->assertSame( $defaults['wps_wpr_em_shop_text'], $clean['wps_wpr_em_shop_text'] );
		$this->assertSame( $defaults['wps_wpr_em_text_color'], $clean['wps_wpr_em_text_color'] );
		$this->assertStringNotContainsString( '<script>', $clean['wps_wpr_em_product_text'] );
	}

	public function test_disabled_locations_render_nothing() {
		global $product;
		$product = $this->make_simple( 25 );
		$em      = new WPS_WPR_Earn_Messages();

		ob_start();
		$em->wps_wpr_render_product_message();
		$em->wps_wpr_render_shop_message();
		$this->assertSame( '', ob_get_clean() );
		$this->assertTrue( $em->wps_wpr_hide_legacy_badge( true ) );

		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_product' => 'yes',
				'wps_wpr_em_enable_shop'    => 'yes',
			)
		);
		ob_start();
		$em->wps_wpr_render_product_message();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Earn <span class="wps_wpr_earn_message_points">50</span> points on this item', $output );
		$this->assertFalse( $em->wps_wpr_hide_legacy_badge( true ) );
	}

	public function test_guests_hidden_when_option_off() {
		global $product;
		$product = $this->make_simple( 25 );
		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_product' => 'yes',
				'wps_wpr_em_show_guests'    => 'no',
			)
		);
		ob_start();
		( new WPS_WPR_Earn_Messages() )->wps_wpr_render_product_message();
		$this->assertSame( '', ob_get_clean() );
	}

	public function test_cart_points_sum_assigned_and_per_currency() {
		$product = $this->make_simple( 10 );
		update_post_meta( $product->get_id(), 'wps_product_points_enable', 'yes' );
		update_post_meta( $product->get_id(), 'wps_points_product_value', 3 );

		$cart = $this->getMockBuilder( WC_Cart::class )->disableOriginalConstructor()->onlyMethods( array( 'get_cart', 'get_total' ) )->getMock();
		$cart->method( 'get_cart' )->willReturn(
			array(
				'k' => array(
					'data'     => wc_get_product( $product->get_id() ),
					'quantity' => 2,
				),
			)
		);
		$cart->method( 'get_total' )->willReturn( 20.0 );
		// 2 x 3 assigned + 20 * 2 per-currency.
		$this->assertSame( 46, WPS_WPR_Earn_Messages::wps_wpr_get_cart_points( $cart, 0 ) );
	}

	public function test_block_cart_notice() {
		$em    = new WPS_WPR_Earn_Messages();
		$block = array( 'blockName' => 'woocommerce/cart' );

		$this->assertSame( '<p>x</p>', $em->wps_wpr_render_cart_block_notice( '<p>x</p>', array( 'blockName' => 'core/paragraph' ) ) );
		$this->assertSame( '<div>cart</div>', $em->wps_wpr_render_cart_block_notice( '<div>cart</div>', $block ) );

		update_option( WPS_WPR_Earn_Messages::OPTION, array( 'wps_wpr_em_enable_cart' => 'yes' ) );
		if ( ! WC()->cart ) {
			wc_load_cart();
		}
		WC()->cart->empty_cart();
		$product = $this->make_simple( 15 );
		WC()->cart->add_to_cart( $product->get_id(), 1 );
		WC()->cart->calculate_totals();

		$html = $em->wps_wpr_render_cart_block_notice( '<div>cart</div>', $block );
		WC()->cart->empty_cart();

		$this->assertStringContainsString( 'wps_wpr_earn_message_cart_block', $html );
		$this->assertStringContainsString( 'Points you will earn: <span class="wps_wpr_earn_message_points">30 points</span>', $html );
		$this->assertStringEndsWith( '<div>cart</div>', $html );
	}
}
