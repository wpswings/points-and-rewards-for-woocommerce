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
	 * Filters added by a test: array( hook, callback ).
	 *
	 * @var array
	 */
	private $filters = array();

	/**
	 * Tax rate created by a test.
	 *
	 * @var int
	 */
	private $tax_rate_id = 0;

	/**
	 * Add a filter that tearDown removes.
	 *
	 * @param string   $hook     hook.
	 * @param callable $callback callback.
	 * @return void
	 */
	private function add_test_filter( $hook, $callback ) {
		add_filter( $hook, $callback, 10, 5 );
		$this->filters[] = array( $hook, $callback );
	}

	/**
	 * Make the free plugin see pro as active, without loading pro.
	 *
	 * @return void
	 */
	private function fake_pro_active() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$this->add_test_filter(
			'option_active_plugins',
			function ( $plugins ) {
				$plugins   = is_array( $plugins ) ? $plugins : array();
				$plugins[] = 'ultimate-woocommerce-points-and-rewards/ultimate-woocommerce-points-and-rewards.php';
				return $plugins;
			}
		);
	}

	/**
	 * Turn taxes on with a 10% standard rate, prices entered and shown excluding tax.
	 *
	 * @return void
	 */
	private function enable_tax() {
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_tax_display_shop', 'excl' );
		update_option( 'woocommerce_tax_based_on', 'base' );
		$this->tax_rate_id = WC_Tax::_insert_tax_rate(
			array(
				'tax_rate_country'  => '',
				'tax_rate_state'    => '',
				'tax_rate'          => '10.0000',
				'tax_rate_name'     => 'WPS-8323 test tax',
				'tax_rate_priority' => '1',
				'tax_rate_compound' => '0',
				'tax_rate_shipping' => '1',
				'tax_rate_order'    => '1',
				'tax_rate_class'    => '',
			)
		);
		WC_Cache_Helper::invalidate_cache_group( 'taxes' );
	}

	/**
	 * Back up options and set a known earning rate: 2 points per 1 currency unit.
	 */
	protected function setUp(): void {
		foreach ( array( 'wps_wpr_coupons_gallery', WPS_WPR_Earn_Messages::OPTION, 'wps_wpr_membership_settings', 'wps_wpr_other_settings', 'wps_wpr_assign_products_points', 'woocommerce_calc_taxes', 'woocommerce_prices_include_tax', 'woocommerce_tax_display_shop', 'woocommerce_tax_based_on' ) as $option ) {
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
		foreach ( $this->filters as $filter ) {
			remove_filter( $filter[0], $filter[1], 10 );
		}
		if ( $this->tax_rate_id ) {
			WC_Tax::_delete_tax_rate( $this->tax_rate_id );
			$this->tax_rate_id = 0;
		}
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
	 * @param float  $price      price.
	 * @param string $tax_status tax status.
	 * @return WC_Product_Simple
	 */
	private function make_simple( $price, $tax_status = 'none' ) {
		$product = new WC_Product_Simple();
		$product->set_name( 'WPS earn msg simple' );
		$product->set_regular_price( (string) $price );
		$product->set_tax_status( $tax_status );
		$product->save();
		$this->posts[] = $product->get_id();
		return $product;
	}

	/**
	 * Create a variable product with variations at the given prices.
	 *
	 * @param array  $prices     prices.
	 * @param string $tax_status tax status.
	 * @return WC_Product_Variable
	 */
	private function make_variable( $prices, $tax_status = 'none' ) {
		$attribute = new WC_Product_Attribute();
		$attribute->set_name( 'Size' );
		$attribute->set_options( array_map( 'strval', array_keys( $prices ) ) );
		$attribute->set_visible( true );
		$attribute->set_variation( true );

		$product = new WC_Product_Variable();
		$product->set_name( 'WPS earn msg variable' );
		$product->set_attributes( array( $attribute ) );
		$product->set_tax_status( $tax_status );
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

		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_product' => 'yes',
				'wps_wpr_em_show_guests'    => 'yes',
			)
		);
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
				'wps_wpr_em_show_guests'    => 'yes',
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

		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_cart' => 'yes',
				'wps_wpr_em_show_guests' => 'yes',
			)
		);
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

	/**
	 * Per-currency points an order earns, worked out the way the award does (on the order total).
	 *
	 * @param WC_Product $product product.
	 * @param int        $qty     quantity.
	 * @return int
	 */
	private function award_for_order_of( $product, $qty = 1 ) {
		$order = wc_create_order();
		$order->add_product( $product, $qty );
		$order->calculate_totals();
		$total = (float) $order->get_total();
		$order->delete( true );
		return (int) round( $total * 2 / 1 );
	}

	public function test_tax_basis_matches_order_award_when_prices_shown_excluding_tax() {
		$this->enable_tax();
		$simple   = $this->make_simple( 25, 'taxable' );
		$variable = $this->make_variable(
			array(
				'S' => 10,
				'L' => 30,
			),
			'taxable'
		);
		$children = $variable->get_children();
		$small    = wc_get_product( $children[0] );
		$large    = wc_get_product( $children[1] );

		// 25 + 10% tax = 27.50 earns 55, although the shop shows 25.
		$this->assertSame( 55, $this->award_for_order_of( $simple ) );
		$this->assertSame( 55, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $simple, 0 ) );
		$this->assertSame( $this->award_for_order_of( $small ), WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $small, 0 ) );
		$this->assertSame(
			array( $this->award_for_order_of( $small ), $this->award_for_order_of( $large ) ),
			WPS_WPR_Earn_Messages::wps_wpr_get_points_range( $variable, 0 )
		);
	}

	public function test_excluding_tax_mode_of_points_amount_setting_is_respected() {
		if ( ! class_exists( 'WPS_WPR_Points_Amount_Settings' ) ) {
			// Stand-in for the WPS-8328 settings class: only the tax mode, read through its filter.
			eval( 'class WPS_WPR_Points_Amount_Settings { public static function wps_wpr_get_tax_mode() { return "excl" === apply_filters( "wps_wpr_points_amount_tax_mode", "incl" ) ? "excl" : "incl"; } }' ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
		}
		$this->enable_tax();
		$simple   = $this->make_simple( 25, 'taxable' );
		$variable = $this->make_variable(
			array(
				'S' => 10,
				'L' => 30,
			),
			'taxable'
		);
		$this->assertSame( 55, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $simple, 0 ) );
		$this->add_test_filter(
			'wps_wpr_points_amount_tax_mode',
			function () {
				return 'excl';
			}
		);
		$this->assertSame( 50, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $simple, 0 ) );
		$this->assertSame( array( 20, 60 ), WPS_WPR_Earn_Messages::wps_wpr_get_points_range( $variable, 0 ) );
	}

	public function test_variable_range_fast_path_matches_per_variation_points() {
		$variable = $this->make_variable(
			array(
				'S' => 10,
				'M' => 20,
				'L' => 30,
			)
		);
		update_post_meta( $variable->get_id(), 'wps_product_points_enable', 'yes' );
		$expected = array();
		foreach ( $variable->get_children() as $index => $variation_id ) {
			update_post_meta( $variation_id, 'wps_wpr_variable_points', 3 + $index );
			$expected[] = WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( wc_get_product( $variation_id ), 0 );
		}
		$this->assertSame( array( 23, 65 ), array( min( $expected ), max( $expected ) ) );
		$this->assertSame( array( 23, 65 ), WPS_WPR_Earn_Messages::wps_wpr_get_points_range( wc_get_product( $variable->get_id() ), 0 ) );
	}

	/**
	 * Render a 30-card product loop and return the number of queries it ran.
	 *
	 * @param int[] $ids product ids.
	 * @return int
	 */
	private function count_loop_queries( $ids ) {
		global $wpdb, $wp_query;
		wp_cache_flush();
		$before_query = $wp_query;
		$start        = $wpdb->num_queries;
		$wp_query     = new WP_Query( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			array(
				'post_type'      => 'product',
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $ids ),
			)
		);
		ob_start();
		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		$html = ob_get_clean();
		wp_reset_postdata();
		$count    = $wpdb->num_queries - $start;
		$wp_query = $before_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$this->last_loop_html = $html;
		return $count;
	}

	/**
	 * HTML of the last rendered loop.
	 *
	 * @var string
	 */
	private $last_loop_html = '';

	public function test_shop_loop_adds_few_queries_for_variable_products() {
		$ids = array();
		for ( $i = 0; $i < 12; $i++ ) {
			$variable = $this->make_variable(
				array(
					'S' => 10 + $i,
					'M' => 20 + $i,
					'L' => 30 + $i,
				)
			);
			update_post_meta( $variable->get_id(), 'wps_product_points_enable', 'yes' );
			foreach ( $variable->get_children() as $variation_id ) {
				update_post_meta( $variation_id, 'wps_wpr_variable_points', 2 );
			}
			$ids[] = $variable->get_id();
		}
		for ( $i = 0; $i < 18; $i++ ) {
			$ids[] = $this->make_simple( 5 + $i )->get_id();
		}

		$em = new WPS_WPR_Earn_Messages();
		add_action( 'woocommerce_after_shop_loop_item_title', array( $em, 'wps_wpr_render_shop_message' ), 20 );
		$this->filters[] = array( 'woocommerce_after_shop_loop_item_title', array( $em, 'wps_wpr_render_shop_message' ) );

		$off_settings = array( 'wps_wpr_em_show_guests' => 'yes' );
		$on_settings  = array(
			'wps_wpr_em_enable_shop' => 'yes',
			'wps_wpr_em_show_guests' => 'yes',
		);

		// Warm WooCommerce's own caches (variation price transients) for both states first.
		update_option( WPS_WPR_Earn_Messages::OPTION, $off_settings );
		$this->count_loop_queries( $ids );
		update_option( WPS_WPR_Earn_Messages::OPTION, $on_settings );
		$this->count_loop_queries( $ids );

		update_option( WPS_WPR_Earn_Messages::OPTION, $off_settings );
		$off = $this->count_loop_queries( $ids );
		update_option( WPS_WPR_Earn_Messages::OPTION, $on_settings );
		$on   = $this->count_loop_queries( $ids );
		$html = $this->last_loop_html;

		$this->assertSame( 30, substr_count( $html, 'wps_wpr_earn_message_shop' ) );
		// Variation 1: (10 + 0) * 2 + 2 = 22, last product's L: (30 + 11) * 2 + 2 = 84.
		$this->assertStringContainsString( '>22 - 62<', $html );
		$this->assertStringContainsString( '>44 - 84<', $html );
		$this->assertLessThan( 10, $on - $off, "Shop loop queries: off {$off}, on {$on}" );
	}

	public function test_legacy_badge_hidden_only_when_new_message_shows() {
		global $product;
		$product = $this->make_simple( 25 );
		$em      = new WPS_WPR_Earn_Messages();
		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_product' => 'yes',
				'wps_wpr_em_show_guests'    => 'no',
			)
		);
		// Guests do not see the new message, so they keep the old badge.
		$this->assertTrue( $em->wps_wpr_hide_legacy_badge( true ) );

		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_product' => 'yes',
				'wps_wpr_em_show_guests'    => 'yes',
			)
		);
		$this->assertFalse( ( new WPS_WPR_Earn_Messages() )->wps_wpr_hide_legacy_badge( true ) );

		// A product that earns nothing shows no new message, so the badge stays.
		update_option( 'wps_wpr_coupons_gallery', array( 'wps_wpr_coupon_conversion_enable' => 0 ) );
		$this->assertTrue( ( new WPS_WPR_Earn_Messages() )->wps_wpr_hide_legacy_badge( true ) );
	}

	public function test_cart_message_only_suppressed_when_pro_shows_its_own() {
		$this->fake_pro_active();
		$this->add_test_filter( 'wps_wpr_show_total_earning_points', '__return_null' );
		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_cart' => 'yes',
				'wps_wpr_em_show_guests' => 'yes',
			)
		);
		if ( ! WC()->cart ) {
			wc_load_cart();
		}
		$em = new WPS_WPR_Earn_Messages();
		$this->assertTrue( WPS_WPR_Earn_Messages::wps_wpr_is_pro_active() );

		// Pro active with its cart / checkout notices off (the default): free shows its message.
		update_option( 'wps_wpr_other_settings', array( 'wps_wpr_cart_page_total_earning_points' => 0 ) );
		$this->assertTrue( (bool) $em->wps_wpr_should_show_cart_message( 'cart' ) );
		$this->assertTrue( (bool) $em->wps_wpr_should_show_cart_message( 'checkout' ) );

		// Pro's cart notice on: free steps aside on the cart only.
		update_option( 'wps_wpr_other_settings', array( 'wps_wpr_cart_page_total_earning_points' => 1 ) );
		$this->assertFalse( (bool) $em->wps_wpr_should_show_cart_message( 'cart' ) );
		$this->assertTrue( (bool) $em->wps_wpr_should_show_cart_message( 'checkout' ) );

		update_option( 'wps_wpr_other_settings', array( 'wps_wpr_checkout_page_total_earning_points' => 1 ) );
		$this->assertTrue( (bool) $em->wps_wpr_should_show_cart_message( 'cart' ) );
		$this->assertFalse( (bool) $em->wps_wpr_should_show_cart_message( 'checkout' ) );
	}

	public function test_pro_shop_option_only_suppresses_where_pro_renders() {
		global $product;
		$this->fake_pro_active();
		update_option( 'wps_wpr_assign_products_points', array( 'wps_wpr_show_assign_points_on_shop_page' => '1' ) );
		update_option(
			WPS_WPR_Earn_Messages::OPTION,
			array(
				'wps_wpr_em_enable_shop' => 'yes',
				'wps_wpr_em_show_guests' => 'yes',
			)
		);
		$product = $this->make_simple( 25 );
		update_post_meta( $product->get_id(), 'wps_product_points_enable', 'yes' );
		update_post_meta( $product->get_id(), 'wps_points_product_value', 7 );

		// Not the shop or a category page (e.g. search results, related products): pro prints nothing, free shows.
		$em = new WPS_WPR_Earn_Messages();
		$this->assertFalse( $em->wps_wpr_pro_shows_loop_points( $product ) );
		ob_start();
		$em->wps_wpr_render_shop_message();
		$this->assertStringContainsString( '>57<', ob_get_clean() );
	}

	public function test_points_only_products_earn_nothing_with_pro() {
		$this->fake_pro_active();
		$product = $this->make_simple( 25 );
		$this->assertSame( 50, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( $product, 0 ) );
		update_post_meta( $product->get_id(), 'wps_product_purchase_points_only', 'yes' );
		$this->assertSame( 0, WPS_WPR_Earn_Messages::wps_wpr_get_points_for_product( wc_get_product( $product->get_id() ), 0 ) );
	}

	public function test_guests_hidden_by_default() {
		$defaults = WPS_WPR_Earn_Messages::wps_wpr_get_default_settings();
		$this->assertSame( 'no', $defaults['wps_wpr_em_show_guests'] );
	}
}
