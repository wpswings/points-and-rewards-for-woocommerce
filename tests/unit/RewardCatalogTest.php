<?php
/**
 * Reward catalog tests.
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests for WPS_WPR_Reward_Catalog.
 */
class RewardCatalogTest extends TestCase {

	/**
	 * Settings backup.
	 *
	 * @var mixed
	 */
	private $backup;

	/**
	 * Created ids.
	 *
	 * @var array
	 */
	private $users = array();

	/**
	 * Created products.
	 *
	 * @var array
	 */
	private $products = array();

	protected function setUp(): void {
		$this->backup = get_option( WPS_WPR_Reward_Catalog::OPTION, null );
	}

	protected function tearDown(): void {
		if ( null === $this->backup ) {
			delete_option( WPS_WPR_Reward_Catalog::OPTION );
		} else {
			update_option( WPS_WPR_Reward_Catalog::OPTION, $this->backup );
		}
		foreach ( $this->users as $user_id ) {
			foreach ( WPS_WPR_Reward_Catalog::wps_wpr_get_user_redemptions( $user_id ) as $redemption ) {
				wp_delete_post( $redemption['coupon_id'], true );
			}
			delete_option( 'wps_wpr_reward_lock_' . $user_id );
			$GLOBALS['wpdb']->delete( $GLOBALS['wpdb']->options, array( 'option_name' => 'wps_wpr_reward_lock_' . $user_id ) );
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $user_id );
		}
		foreach ( $this->products as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$product->delete( true );
			}
		}
	}

	private function make_user( $points ) {
		$user_id       = wp_insert_user(
			array(
				'user_login' => 'rc_test_' . wp_generate_password( 8, false ),
				'user_email' => 'rc_' . wp_generate_password( 8, false ) . '@example.test',
				'user_pass'  => wp_generate_password(),
				'role'       => 'customer',
			)
		);
		$this->users[] = $user_id;
		update_user_meta( $user_id, 'wps_wpr_points', $points );
		return $user_id;
	}

	private function make_product() {
		$product = new WC_Product_Simple();
		$product->set_name( 'RC Test T-shirt' );
		$product->set_regular_price( '20' );
		$product->set_status( 'publish' );
		$id               = $product->save();
		$this->products[] = $id;
		return $id;
	}

	private function save_rewards( array $rewards ) {
		return WPS_WPR_Reward_Catalog::wps_wpr_save_settings( 'yes', $rewards );
	}

	private function reward_id( $settings, $title ) {
		foreach ( $settings['rewards'] as $id => $reward ) {
			if ( $reward['title'] === $title ) {
				return $id;
			}
		}
		return '';
	}

	public function test_sanitize_drops_empty_rows_and_clamps_values() {
		$settings = $this->save_rewards(
			array(
				array( 'title' => '' ),
				array(
					'title'  => 'Big percent',
					'points' => '100',
					'type'   => 'percent',
					'amount' => '250',
					'active' => 'yes',
				),
				array(
					'title'  => 'Bad type',
					'points' => '-5',
					'type'   => 'evil',
					'amount' => '5',
				),
			)
		);
		$this->assertCount( 2, $settings['rewards'] );
		$percent = $settings['rewards'][ $this->reward_id( $settings, 'Big percent' ) ];
		$this->assertSame( 100.0, $percent['amount'] );
		$bad = $settings['rewards'][ $this->reward_id( $settings, 'Bad type' ) ];
		$this->assertSame( 'fixed_cart', $bad['type'] );
		$this->assertSame( 0, $bad['points'] );
		$this->assertSame( 'no', $bad['active'] );
		$this->assertCount( 1, WPS_WPR_Reward_Catalog::wps_wpr_get_save_errors() );
	}

	public function test_negative_or_zero_values_are_rejected_not_sign_flipped() {
		$settings = $this->save_rewards(
			array(
				array( 'title' => 'Neg cost', 'points' => '-50', 'type' => 'fixed_cart', 'amount' => '5', 'active' => 'yes' ),
				array( 'title' => 'Zero cost', 'points' => '0', 'type' => 'fixed_cart', 'amount' => '5', 'active' => 'yes' ),
				array( 'title' => 'Neg limit', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '5', 'user_limit' => '-1', 'active' => 'yes' ),
				array( 'title' => 'Neg expiry', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '5', 'expiry_days' => '-3', 'active' => 'yes' ),
				array( 'title' => 'Fine', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '5', 'user_limit' => '2', 'expiry_days' => '3', 'active' => 'yes' ),
			)
		);
		$errors = WPS_WPR_Reward_Catalog::wps_wpr_get_save_errors();
		$this->assertCount( 4, $errors );
		$this->assertStringContainsString( 'Neg cost', $errors[0] );
		$this->assertStringContainsString( 'at least 1', $errors[0] );
		$this->assertStringContainsString( 'cannot be negative', $errors[2] );
		$this->assertStringContainsString( 'cannot be negative', $errors[3] );

		foreach ( array( 'Neg cost', 'Zero cost', 'Neg limit', 'Neg expiry' ) as $title ) {
			$reward = $settings['rewards'][ $this->reward_id( $settings, $title ) ];
			$this->assertSame( 'no', $reward['active'], $title );
		}
		$this->assertSame( 0, $settings['rewards'][ $this->reward_id( $settings, 'Neg cost' ) ]['points'] );
		$this->assertSame( 0, $settings['rewards'][ $this->reward_id( $settings, 'Neg limit' ) ]['user_limit'] );
		$this->assertSame( 0, $settings['rewards'][ $this->reward_id( $settings, 'Neg expiry' ) ]['expiry_days'] );

		$fine = $settings['rewards'][ $this->reward_id( $settings, 'Fine' ) ];
		$this->assertSame( array( 'yes', 10, 2, 3 ), array( $fine['active'], $fine['points'], $fine['user_limit'], $fine['expiry_days'] ) );
		$this->assertSame( array( 'Fine' ), array_values( wp_list_pluck( WPS_WPR_Reward_Catalog::wps_wpr_get_available_rewards(), 'title' ) ) );
	}

	public function test_variable_parent_cannot_be_a_free_product_reward() {
		$parent = new WC_Product_Variable();
		$parent->set_name( 'RC Test Hoodie' );
		$parent->set_status( 'publish' );
		$parent_id        = $parent->save();
		$this->products[] = $parent_id;

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent_id );
		$variation->set_regular_price( '30' );
		$variation->set_status( 'publish' );
		$variation_id     = $variation->save();
		$this->products[] = $variation_id;

		$settings = $this->save_rewards(
			array(
				array( 'title' => 'Hoodie', 'points' => '10', 'type' => 'free_product', 'product_id' => (string) $parent_id, 'active' => 'yes' ),
				array( 'title' => 'Hoodie M', 'points' => '10', 'type' => 'free_product', 'product_id' => (string) $variation_id, 'active' => 'yes' ),
			)
		);
		$errors = WPS_WPR_Reward_Catalog::wps_wpr_get_save_errors();
		$this->assertCount( 1, $errors );
		$this->assertStringContainsString( 'Hoodie', $errors[0] );
		$this->assertSame( 'no', $settings['rewards'][ $this->reward_id( $settings, 'Hoodie' ) ]['active'] );

		// Even when forced active (e.g. saved before this check existed) the parent is not offered or redeemable.
		$forced                                        = $settings;
		$hoodie_id                                     = $this->reward_id( $settings, 'Hoodie' );
		$forced['rewards'][ $hoodie_id ]['active']     = 'yes';
		update_option( WPS_WPR_Reward_Catalog::OPTION, $forced );
		$this->assertSame( array( 'Hoodie M' ), array_values( wp_list_pluck( WPS_WPR_Reward_Catalog::wps_wpr_get_available_rewards(), 'title' ) ) );
		$user_id = $this->make_user( 100 );
		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $hoodie_id ), 'wps_wpr_reward' );
		$this->assertIsArray( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Hoodie M' ) ) );
	}

	public function test_trashed_coupon_is_unavailable_and_has_no_apply_link() {
		$settings = $this->save_rewards(
			array( array( 'title' => 'Trash me', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '1', 'active' => 'yes' ) )
		);
		$user_id = $this->make_user( 50 );
		$result  = WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Trash me' ) );
		$this->assertStringContainsString( WPS_WPR_Reward_Catalog::APPLY_ARG, WPS_WPR_Reward_Catalog::wps_wpr_get_catalog_html( $user_id ) );

		wp_trash_post( $result['coupon_id'] );
		$this->assertSame( 'deleted', WPS_WPR_Reward_Catalog::wps_wpr_get_redemption_status( $result ) );
		$html = WPS_WPR_Reward_Catalog::wps_wpr_get_catalog_html( $user_id );
		$this->assertStringContainsString( 'Unavailable', $html );
		$this->assertStringNotContainsString( WPS_WPR_Reward_Catalog::APPLY_ARG, $html );
	}

	public function test_inactive_or_misconfigured_rewards_are_hidden() {
		$settings  = $this->save_rewards(
			array(
				array( 'title' => 'Off', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '5' ),
				array( 'title' => 'No amount', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '0', 'active' => 'yes' ),
				array( 'title' => 'No product', 'points' => '10', 'type' => 'free_product', 'active' => 'yes' ),
				array( 'title' => 'Ship', 'points' => '10', 'type' => 'free_shipping', 'active' => 'yes' ),
			)
		);
		$available = WPS_WPR_Reward_Catalog::wps_wpr_get_available_rewards();
		$this->assertSame( array( 'Ship' ), array_values( wp_list_pluck( $available, 'title' ) ) );
		$this->assertNotEmpty( $settings );
	}

	public function test_fixed_cart_redemption_creates_coupon_deducts_points_and_logs() {
		$settings = $this->save_rewards(
			array(
				array( 'title' => 'Ten off', 'points' => '300', 'type' => 'fixed_cart', 'amount' => '10', 'min_amount' => '50', 'expiry_days' => '7', 'active' => 'yes' ),
			)
		);
		$user_id  = $this->make_user( 500 );
		$result   = WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Ten off' ) );

		$this->assertIsArray( $result );
		$this->assertSame( 200, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
		$this->assertSame( 200, $result['balance'] );

		$coupon = new WC_Coupon( $result['coupon_id'] );
		$this->assertSame( 'fixed_cart', $coupon->get_discount_type() );
		$this->assertEquals( 10, $coupon->get_amount() );
		$this->assertEquals( 50, $coupon->get_minimum_amount() );
		$this->assertSame( 1, $coupon->get_usage_limit() );
		$this->assertSame( array( strtolower( get_userdata( $user_id )->user_email ) ), array_map( 'strtolower', $coupon->get_email_restrictions() ) );
		$this->assertNotNull( $coupon->get_date_expires() );
		$this->assertGreaterThan( time() + 6 * DAY_IN_SECONDS, $coupon->get_date_expires()->getTimestamp() );

		$log = get_user_meta( $user_id, 'points_details', true );
		$this->assertSame( 300, $log['reward_catalog_redeemed'][0]['reward_catalog_redeemed'] );
		$this->assertSame( $coupon->get_code(), $log['reward_catalog_redeemed'][0]['coupon'] );
		$this->assertSame( 'available', WPS_WPR_Reward_Catalog::wps_wpr_get_redemption_status( $result ) );
	}

	public function test_percent_free_product_and_free_shipping_coupons() {
		$product_id = $this->make_product();
		$settings   = $this->save_rewards(
			array(
				array( 'title' => 'Pct', 'points' => '10', 'type' => 'percent', 'amount' => '15', 'active' => 'yes' ),
				array( 'title' => 'Shirt', 'points' => '10', 'type' => 'free_product', 'product_id' => (string) $product_id, 'active' => 'yes' ),
				array( 'title' => 'Ship', 'points' => '10', 'type' => 'free_shipping', 'active' => 'yes' ),
			)
		);
		$user_id = $this->make_user( 100 );

		$pct = new WC_Coupon( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Pct' ) )['coupon_id'] );
		$this->assertSame( 'percent', $pct->get_discount_type() );
		$this->assertEquals( 15, $pct->get_amount() );

		$shirt = new WC_Coupon( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Shirt' ) )['coupon_id'] );
		$this->assertSame( 'percent', $shirt->get_discount_type() );
		$this->assertEquals( 100, $shirt->get_amount() );
		$this->assertSame( array( $product_id ), $shirt->get_product_ids() );
		$this->assertSame( 1, $shirt->get_limit_usage_to_x_items() );

		$ship = new WC_Coupon( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Ship' ) )['coupon_id'] );
		$this->assertTrue( $ship->get_free_shipping() );
		$this->assertEquals( 0, $ship->get_amount() );

		$this->assertSame( 70, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
		$this->assertCount( 3, WPS_WPR_Reward_Catalog::wps_wpr_get_user_redemptions( $user_id ) );
	}

	public function test_insufficient_points_is_rejected_without_side_effects() {
		$settings = $this->save_rewards(
			array( array( 'title' => 'Pricey', 'points' => '1000', 'type' => 'fixed_cart', 'amount' => '50', 'active' => 'yes' ) )
		);
		$user_id  = $this->make_user( 999 );
		$result   = WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Pricey' ) );

		$this->assertWPError( $result, 'wps_wpr_points' );
		$this->assertSame( 999, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
		$this->assertEmpty( WPS_WPR_Reward_Catalog::wps_wpr_get_user_redemptions( $user_id ) );
		$this->assertEmpty( get_user_meta( $user_id, 'points_details', true ) );
	}

	public function test_per_user_limit() {
		$settings  = $this->save_rewards(
			array( array( 'title' => 'Once', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '1', 'user_limit' => '1', 'active' => 'yes' ) )
		);
		$reward_id = $this->reward_id( $settings, 'Once' );
		$user_id   = $this->make_user( 100 );

		$this->assertIsArray( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ) );
		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ), 'wps_wpr_limit' );
		$this->assertSame( 90, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
	}

	public function test_disabled_catalog_restricted_user_and_unknown_reward() {
		$settings  = $this->save_rewards(
			array( array( 'title' => 'Any', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '1', 'active' => 'yes' ) )
		);
		$reward_id = $this->reward_id( $settings, 'Any' );
		$user_id   = $this->make_user( 100 );

		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, 'nope' ), 'wps_wpr_reward' );

		update_user_meta( $user_id, 'wps_wpr_restrict_user', 'yes' );
		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ), 'wps_wpr_restricted' );
		delete_user_meta( $user_id, 'wps_wpr_restrict_user' );

		WPS_WPR_Reward_Catalog::wps_wpr_save_settings( 'no', $settings['rewards'] );
		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ), 'wps_wpr_disabled' );
		$this->assertSame( 100, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
	}

	public function test_lock_blocks_concurrent_redemption() {
		$settings = $this->save_rewards(
			array( array( 'title' => 'Lock', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '1', 'active' => 'yes' ) )
		);
		$user_id  = $this->make_user( 100 );
		add_option( 'wps_wpr_reward_lock_' . $user_id, time(), '', 'no' );
		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Lock' ) ), 'wps_wpr_busy' );
		$this->assertSame( 100, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
	}

	/**
	 * Insert a lock row directly, as another request would.
	 *
	 * @param int    $user_id user id.
	 * @param string $value   lock value.
	 */
	private function insert_lock_row( $user_id, $value ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->options,
			array(
				'option_name'  => 'wps_wpr_reward_lock_' . $user_id,
				'option_value' => $value,
				'autoload'     => 'no',
			)
		);
	}

	private function lock_row( $user_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'wps_wpr_reward_lock_' . $user_id ) );
	}

	public function test_lock_held_by_another_request_with_a_different_value_blocks() {
		$settings  = $this->save_rewards(
			array( array( 'title' => 'Lock2', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '1', 'active' => 'yes' ) )
		);
		$reward_id = $this->reward_id( $settings, 'Lock2' );
		$user_id   = $this->make_user( 100 );

		// This request has already looked the lock up and cached "not there" (as a
		// racing request would), then another request takes it 5 seconds ago with a
		// different value. add_option() trusted that cache and overwrote the lock.
		$this->assertFalse( get_option( 'wps_wpr_reward_lock_' . $user_id ) );
		$this->insert_lock_row( $user_id, ( time() - 5 ) . ':otherrequest' );
		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ), 'wps_wpr_busy' );
		$this->assertSame( 100, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
		$this->assertEmpty( WPS_WPR_Reward_Catalog::wps_wpr_get_user_redemptions( $user_id ) );
		// The other request's lock is left alone.
		$this->assertSame( ( time() - 5 ) . ':otherrequest', $this->lock_row( $user_id ) );
	}

	public function test_stale_lock_is_recovered_and_lock_is_released_after_redeem() {
		$settings  = $this->save_rewards(
			array( array( 'title' => 'Lock3', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '1', 'active' => 'yes' ) )
		);
		$reward_id = $this->reward_id( $settings, 'Lock3' );
		$user_id   = $this->make_user( 100 );

		$this->insert_lock_row( $user_id, ( time() - 120 ) . ':deadrequest' );
		$this->assertIsArray( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ) );
		$this->assertNull( $this->lock_row( $user_id ) );

		// A failed redeem releases the lock too.
		update_user_meta( $user_id, 'wps_wpr_points', 1 );
		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ), 'wps_wpr_points' );
		$this->assertNull( $this->lock_row( $user_id ) );
	}

	public function test_balance_is_read_from_the_database_inside_the_lock() {
		global $wpdb;
		$settings  = $this->save_rewards(
			array( array( 'title' => 'Fresh', 'points' => '100', 'type' => 'fixed_cart', 'amount' => '1', 'active' => 'yes' ) )
		);
		$reward_id = $this->reward_id( $settings, 'Fresh' );
		$user_id   = $this->make_user( 100 );

		// Prime the meta cache with 100, then another request spends the points behind its back.
		$this->assertSame( 100, absint( get_user_meta( $user_id, 'wps_wpr_points', true ) ) );
		$wpdb->update( $wpdb->usermeta, array( 'meta_value' => '0' ), array( 'user_id' => $user_id, 'meta_key' => 'wps_wpr_points' ) );

		$this->assertWPError( WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $reward_id ), 'wps_wpr_points' );
		$this->assertEmpty( WPS_WPR_Reward_Catalog::wps_wpr_get_user_redemptions( $user_id ) );
	}

	public function test_custom_reward_type_via_filter() {
		$add_type = static function ( $types ) {
			$types['store_credit'] = 'Store credit';
			return $types;
		};
		$build    = static function ( $coupon ) {
			$coupon->set_discount_type( 'fixed_cart' );
			$coupon->set_amount( 7 );
		};
		add_filter( 'wps_wpr_reward_catalog_types', $add_type );
		add_action( 'wps_wpr_reward_catalog_build_coupon_store_credit', $build );

		$settings = $this->save_rewards(
			array( array( 'title' => 'Credit', 'points' => '5', 'type' => 'store_credit', 'active' => 'yes' ) )
		);
		$user_id  = $this->make_user( 10 );
		$result   = WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Credit' ) );

		remove_filter( 'wps_wpr_reward_catalog_types', $add_type );
		remove_action( 'wps_wpr_reward_catalog_build_coupon_store_credit', $build );

		$this->assertIsArray( $result );
		$this->assertEquals( 7, ( new WC_Coupon( $result['coupon_id'] ) )->get_amount() );
	}

	public function test_catalog_html_reflects_balance_and_redemptions() {
		$settings = $this->save_rewards(
			array(
				array( 'title' => 'Cheap', 'points' => '10', 'type' => 'fixed_cart', 'amount' => '1', 'active' => 'yes' ),
				array( 'title' => 'Dear', 'points' => '5000', 'type' => 'fixed_cart', 'amount' => '100', 'active' => 'yes' ),
			)
		);
		$user_id  = $this->make_user( 50 );
		$result   = WPS_WPR_Reward_Catalog::wps_wpr_redeem( $user_id, $this->reward_id( $settings, 'Cheap' ) );
		$html     = WPS_WPR_Reward_Catalog::wps_wpr_get_catalog_html( $user_id );

		$this->assertStringContainsString( 'Cheap', $html );
		$this->assertStringContainsString( 'Not enough points', $html );
		$this->assertStringContainsString( $result['code'], $html );
		$this->assertStringContainsString( WPS_WPR_Reward_Catalog::APPLY_ARG, $html );
	}

	/**
	 * Assert a WP_Error with a code.
	 *
	 * @param mixed  $value value.
	 * @param string $code  expected code.
	 */
	private function assertWPError( $value, $code ) {
		$this->assertInstanceOf( WP_Error::class, $value );
		$this->assertSame( $code, $value->get_error_code() );
	}
}
