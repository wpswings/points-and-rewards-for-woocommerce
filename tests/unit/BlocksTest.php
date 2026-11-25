<?php
/**
 * Tests for the Gutenberg blocks pack.
 *
 * @package points-and-rewards-for-wooCommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Class BlocksTest.
 */
class BlocksTest extends TestCase {

	/**
	 * Saved options, restored after each test.
	 *
	 * @var array
	 */
	private $saved_options = array();

	/**
	 * Users created by the test.
	 *
	 * @var int[]
	 */
	private $user_ids = array();

	/**
	 * Block instance.
	 *
	 * @var WPS_WPR_Blocks
	 */
	private $blocks;

	/**
	 * Set up.
	 */
	protected function setUp(): void {
		foreach ( array( 'wps_wpr_settings_gallery', 'wps_wpr_coupons_gallery', 'wps_wpr_leaderboard_cache_version' ) as $option ) {
			$this->saved_options[ $option ] = get_option( $option, null );
		}
		$this->blocks = new WPS_WPR_Blocks();
		wp_set_current_user( 0 );
	}

	/**
	 * Tear down.
	 */
	protected function tearDown(): void {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->user_ids as $user_id ) {
			wp_delete_user( $user_id );
		}
		foreach ( $this->saved_options as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
		// Drop leaderboard transients so restoring the cache version cannot serve stale rows.
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_wps\\_wpr\\_lb\\_%' OR option_name LIKE '\\_transient\\_timeout\\_wps\\_wpr\\_lb\\_%'" ); // phpcs:ignore WordPress.DB
		wp_cache_flush();
		wp_set_current_user( 0 );
	}

	/**
	 * Merge values into the general settings option.
	 *
	 * @param array $values values.
	 */
	private function set_general( $values ) {
		$settings = get_option( 'wps_wpr_settings_gallery', array() );
		$settings = is_array( $settings ) ? $settings : array();
		update_option( 'wps_wpr_settings_gallery', array_merge( $settings, $values ) );
	}

	/**
	 * Create a customer.
	 *
	 * @param  array $data user fields.
	 * @param  int   $points points balance.
	 * @return int
	 */
	private function make_user( $data = array(), $points = 0 ) {
		$login   = 'wpsblk_' . wp_generate_password( 8, false );
		$user_id = wp_insert_user(
			array_merge(
				array(
					'user_login' => $login,
					'user_email' => $login . '@example.test',
					'user_pass'  => wp_generate_password(),
					'role'       => 'customer',
				),
				$data
			)
		);
		$this->assertIsInt( $user_id );
		$this->user_ids[] = $user_id;
		if ( $points ) {
			update_user_meta( $user_id, 'wps_wpr_points', $points );
		}
		return $user_id;
	}

	/**
	 * All six blocks are registered in the plugin category with a render callback.
	 */
	public function test_blocks_are_registered() {
		$registry = WP_Block_Type_Registry::get_instance();
		foreach ( array_keys( WPS_WPR_Blocks::wps_wpr_get_blocks() ) as $slug ) {
			$block = $registry->get_registered( 'wps-wpr/' . $slug );
			$this->assertNotNull( $block, $slug . ' is registered' );
			$this->assertSame( WPS_WPR_Blocks::CATEGORY, $block->category );
			$this->assertTrue( $block->is_dynamic(), $slug . ' is dynamic' );
			$this->assertContains( WPS_WPR_Blocks::EDITOR_HANDLE, $block->editor_script_handles );
		}
	}

	/**
	 * The block category is added once.
	 */
	public function test_block_category_added_once() {
		$categories = $this->blocks->wps_wpr_block_category( array( array( 'slug' => 'text', 'title' => 'Text' ) ) );
		$this->assertSame( WPS_WPR_Blocks::CATEGORY, $categories[0]['slug'] );
		$again = $this->blocks->wps_wpr_block_category( $categories );
		$this->assertCount( 2, $again );
	}

	/**
	 * Nothing renders on the front end when the program is off.
	 */
	public function test_disabled_program_renders_nothing() {
		$this->set_general( array( 'wps_wpr_general_setting_enable' => 0 ) );
		$user_id = $this->make_user( array(), 100 );
		wp_set_current_user( $user_id );
		$this->assertSame( '', $this->blocks->wps_wpr_render_points_balance( array() ) );
		$this->assertSame( '', $this->blocks->wps_wpr_render_leaderboard( array() ) );
	}

	/**
	 * Points balance for a logged-in customer and a guest.
	 */
	public function test_points_balance() {
		$this->set_general( array( 'wps_wpr_general_setting_enable' => 1 ) );
		$user_id = $this->make_user( array(), 1250 );
		update_user_meta( $user_id, 'membership_level', 'Gold' );
		wp_set_current_user( $user_id );

		$html = $this->blocks->wps_wpr_render_points_balance(
			array(
				'label'     => 'Balance:',
				'showLevel' => true,
			)
		);
		$this->assertStringContainsString( 'Balance:', $html );
		$this->assertStringContainsString( number_format_i18n( 1250 ), $html );
		$this->assertStringContainsString( 'Gold', $html );

		wp_set_current_user( 0 );
		$guest = $this->blocks->wps_wpr_render_points_balance( array( 'showLoginPrompt' => true ) );
		$this->assertStringContainsString( 'wps-wpr-block__login', $guest );
		$this->assertSame( '', $this->blocks->wps_wpr_render_points_balance( array( 'showLoginPrompt' => false ) ) );
	}

	/**
	 * Rendering through do_blocks uses the block wrapper attributes.
	 */
	public function test_render_through_block_parser() {
		$this->set_general( array( 'wps_wpr_general_setting_enable' => 1 ) );
		$user_id = $this->make_user( array(), 42 );
		wp_set_current_user( $user_id );

		$html = do_blocks( '<!-- wp:wps-wpr/points-balance {"label":"Pts"} /-->' );
		$this->assertStringContainsString( 'wp-block-wps-wpr-points-balance', $html );
		$this->assertStringContainsString( 'Pts', $html );
		$this->assertStringContainsString( '42', $html );
	}

	/**
	 * Ways to earn reflects the enabled rules.
	 */
	public function test_earn_points_items() {
		$this->set_general(
			array(
				'wps_wpr_general_setting_enable' => 1,
				'wps_wpr_general_signup'         => 1,
				'wps_wpr_general_signup_value'   => 25,
				'wps_wpr_general_refer_enable'   => 0,
			)
		);
		update_option(
			'wps_wpr_coupons_gallery',
			array(
				'wps_wpr_coupon_conversion_enable' => 1,
				'wps_wpr_coupon_conversion_points' => 10,
				'wps_wpr_coupon_conversion_price'  => 3,
			)
		);

		$items = WPS_WPR_Blocks::wps_wpr_get_earn_items();
		$keys  = wp_list_pluck( $items, 'key' );
		$this->assertSame( array( 'signup', 'per_currency' ), $keys );
		$this->assertSame( 25, $items[0]['points'] );
		$this->assertSame( 3, $items[1]['points'] );

		$html = $this->blocks->wps_wpr_render_earn_points( array( 'heading' => 'Earn more' ) );
		$this->assertStringContainsString( 'Earn more', $html );
		$this->assertStringContainsString( 'Create an account', $html );
		$this->assertStringNotContainsString( 'Refer a friend', $html );
	}

	/**
	 * The referral link block needs referral enabled and shows the user's key.
	 */
	public function test_referral_link() {
		$user_id = $this->make_user();
		wp_set_current_user( $user_id );

		$this->set_general(
			array(
				'wps_wpr_general_setting_enable' => 1,
				'wps_wpr_general_refer_enable'   => 0,
			)
		);
		$this->assertSame( '', $this->blocks->wps_wpr_render_referral_link( array() ) );

		$this->set_general(
			array(
				'wps_wpr_general_refer_enable' => 1,
				'wps_wpr_general_refer_value'  => 40,
			)
		);
		$html = $this->blocks->wps_wpr_render_referral_link( array() );
		$key  = get_user_meta( $user_id, 'wps_points_referral', true );
		$this->assertNotEmpty( $key );
		$this->assertStringContainsString( 'pkey=' . $key, $html );
		$this->assertStringContainsString( 'wps-wpr-block__copy-btn', $html );

		// The key is stable across renders.
		$this->blocks->wps_wpr_render_referral_link( array() );
		$this->assertSame( $key, get_user_meta( $user_id, 'wps_points_referral', true ) );
	}

	/**
	 * Membership level block.
	 */
	public function test_membership_level() {
		$this->set_general( array( 'wps_wpr_general_setting_enable' => 1 ) );
		$user_id = $this->make_user();
		wp_set_current_user( $user_id );

		$this->assertSame( '', $this->blocks->wps_wpr_render_membership_level( array() ) );
		$this->assertStringContainsString( 'No level yet', $this->blocks->wps_wpr_render_membership_level( array( 'noLevelText' => 'No level yet' ) ) );

		update_user_meta( $user_id, 'membership_level', 'Silver' );
		$this->assertStringContainsString( 'Silver', $this->blocks->wps_wpr_render_membership_level( array() ) );
	}

	/**
	 * Leaderboard names never expose emails.
	 */
	public function test_leaderboard_name_is_safe() {
		$with_names = new WP_User( $this->make_user( array( 'first_name' => 'Jane', 'last_name' => 'Doe' ) ) );
		$this->assertSame( 'Jane D.', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $with_names, 'first_initial', 1 ) );

		$email_login = 'wpsblk_' . wp_generate_password( 6, false ) . '@example.test';
		$email_user  = new WP_User(
			$this->make_user(
				array(
					'user_login'   => $email_login,
					'user_email'   => $email_login,
					'display_name' => $email_login,
				)
			)
		);
		$this->assertSame( 'Customer #2', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $email_user, 'first_initial', 2 ) );
		$this->assertSame( 'Customer #2', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $email_user, 'display_name', 2 ) );
		$this->assertSame( 'Customer #3', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $with_names, 'anonymous', 3 ) );
	}

	/**
	 * Leaderboard ordering, limit and restricted users.
	 */
	public function test_leaderboard_order_and_restrictions() {
		$this->set_general( array( 'wps_wpr_general_setting_enable' => 1 ) );

		$top        = $this->make_user( array( 'first_name' => 'Topper' ), 9000001 );
		$second     = $this->make_user( array( 'first_name' => 'Runner' ), 9000000 );
		$restricted = $this->make_user( array( 'first_name' => 'Hidden' ), 9000002 );
		update_user_meta( $restricted, 'wps_wpr_restrict_user', 'yes' );

		$rows = WPS_WPR_Blocks::wps_wpr_get_leaderboard( 2 );
		$ids  = array_map(
			function ( $row ) {
				return $row['user']->ID;
			},
			$rows
		);
		$this->assertSame( array( $top, $second ), $ids );

		$html = $this->blocks->wps_wpr_render_leaderboard(
			array(
				'limit'      => 2,
				'showPoints' => false,
			)
		);
		$this->assertStringContainsString( 'Topper', $html );
		$this->assertStringNotContainsString( 'Hidden', $html );
		$this->assertStringNotContainsString( '@example.test', $html );
		$this->assertStringNotContainsString( number_format_i18n( 9000001 ), $html );

		// Limit is clamped.
		$this->assertLessThanOrEqual( WPS_WPR_Blocks::LEADERBOARD_MAX, count( WPS_WPR_Blocks::wps_wpr_get_leaderboard( 500 ) ) );
	}

	/**
	 * A user without a first name, or whose display name is the login, is never
	 * shown by login (WPS-8318 review).
	 */
	public function test_leaderboard_name_never_shows_login() {
		$login   = 'wpsblk_dave' . wp_generate_password( 4, false );
		$no_name = new WP_User(
			$this->make_user(
				array(
					'user_login'   => $login,
					'user_email'   => 'john.smith' . wp_generate_password( 4, false ) . '@example.test',
					'display_name' => $login,
				)
			)
		);
		$this->assertSame( $login, $no_name->display_name );
		$this->assertSame( 'Customer #1', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $no_name, 'first_initial', 1 ) );
		$this->assertSame( 'Customer #1', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $no_name, 'display_name', 1 ) );

		// Display name equal to the login in another case, or to the email local part.
		$no_name->display_name = strtoupper( $login );
		$this->assertSame( 'Customer #2', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $no_name, 'display_name', 2 ) );
		$no_name->display_name = strstr( $no_name->user_email, '@', true );
		$this->assertSame( 'Customer #3', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $no_name, 'display_name', 3 ) );

		// A real display name is still shown in display_name mode.
		$no_name->display_name = 'Dave the Saver';
		$this->assertSame( 'Dave the Saver', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $no_name, 'display_name', 4 ) );

		// First name only, no last name.
		$first_only = new WP_User( $this->make_user( array( 'first_name' => 'Mira' ) ) );
		$this->assertSame( 'Mira', WPS_WPR_Blocks::wps_wpr_leaderboard_name( $first_only, 'first_initial', 5 ) );
	}

	/**
	 * Administrators and shop managers are left off the leaderboard by default,
	 * and the role list is filterable.
	 */
	public function test_leaderboard_excludes_store_managers() {
		$this->set_general( array( 'wps_wpr_general_setting_enable' => 1 ) );

		$admin    = $this->make_user( array( 'role' => 'administrator', 'first_name' => 'Admin' ), 9500002 );
		$manager  = $this->make_user( array( 'role' => 'shop_manager', 'first_name' => 'Manager' ), 9500001 );
		$customer = $this->make_user( array( 'first_name' => 'Cust' ), 9500000 );

		$roles = WPS_WPR_Blocks::wps_wpr_leaderboard_excluded_roles();
		$this->assertContains( 'administrator', $roles );
		$this->assertContains( 'shop_manager', $roles );
		$this->assertNotContains( 'customer', $roles );

		$ids = $this->leaderboard_ids( 1 );
		$this->assertSame( array( $customer ), $ids );

		$no_exclusions = function () {
			return array();
		};
		add_filter( 'wps_wpr_block_leaderboard_excluded_roles', $no_exclusions );
		$ids = $this->leaderboard_ids( 3 );
		remove_filter( 'wps_wpr_block_leaderboard_excluded_roles', $no_exclusions );
		$this->assertSame( array( $admin, $manager, $customer ), $ids );
	}

	/**
	 * The leaderboard query is cached and the cache is cleared when points change.
	 */
	public function test_leaderboard_is_cached_and_invalidated() {
		global $wpdb;
		$first  = $this->make_user( array( 'first_name' => 'First' ), 9600001 );
		$second = $this->make_user( array( 'first_name' => 'Second' ), 9600000 );

		$this->assertSame( array( $first, $second ), $this->leaderboard_ids( 2 ) );

		// A change that bypasses the meta API is not seen, even after the
		// per-request object cache is cleared: the result is in a transient.
		$wpdb->update( $wpdb->usermeta, array( 'meta_value' => '9600005' ), array( 'user_id' => $second, 'meta_key' => 'wps_wpr_points' ) ); // phpcs:ignore WordPress.DB
		wp_cache_flush();
		$this->assertSame( array( $first, $second ), $this->leaderboard_ids( 2 ) );

		// Updating points through the meta API invalidates the cache.
		wp_cache_delete( $second, 'user_meta' );
		update_user_meta( $second, 'wps_wpr_points', 9600010 );
		$this->assertSame( array( $second, $first ), $this->leaderboard_ids( 2 ) );

		// Restricting a user also invalidates it.
		update_user_meta( $second, 'wps_wpr_restrict_user', 'yes' );
		$this->assertSame( $first, $this->leaderboard_ids( 1 )[0] );
	}

	/**
	 * Non-positive "ways to earn" items and malformed leaderboard rows are skipped.
	 */
	public function test_filtered_items_are_validated() {
		$this->set_general(
			array(
				'wps_wpr_general_setting_enable' => 1,
				'wps_wpr_general_signup'         => 1,
				'wps_wpr_general_signup_value'   => 25,
			)
		);
		$earn = function ( $items ) {
			$items[] = array( 'key' => 'bad', 'label' => 'Negative rule', 'points' => -3 );
			return $items;
		};
		add_filter( 'wps_wpr_block_earn_points_items', $earn );
		$html = $this->blocks->wps_wpr_render_earn_points( array() );
		remove_filter( 'wps_wpr_block_earn_points_items', $earn );
		$this->assertStringContainsString( 'Create an account', $html );
		$this->assertStringNotContainsString( 'Negative rule', $html );

		$user_id = $this->make_user( array( 'first_name' => 'Valid' ), 9700000 );
		$rows    = function () use ( $user_id ) {
			return array( array( 'user' => 5, 'points' => 1 ), 'junk', array( 'user' => new WP_User( $user_id ), 'points' => 10 ) );
		};
		add_filter( 'wps_wpr_block_leaderboard_rows', $rows );
		$html = $this->blocks->wps_wpr_render_leaderboard( array() );
		remove_filter( 'wps_wpr_block_leaderboard_rows', $rows );
		$this->assertStringContainsString( 'Valid', $html );
		$this->assertSame( 1, substr_count( $html, '<li ' ) );
	}

	/**
	 * Editor notices only show in the block renderer, not in other REST
	 * responses such as GET /wp/v2/pages/<id>.
	 *
	 * Defines REST_REQUEST for the rest of the run, so it is the last test.
	 * The other tests do not set a rest_route, so they are unaffected.
	 */
	public function test_editor_preview_only_in_block_renderer() {
		$this->set_general( array( 'wps_wpr_general_setting_enable' => 0 ) );
		$editor = $this->make_user( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor );
		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}

		$GLOBALS['wp']->query_vars['rest_route'] = '/wp/v2/pages/1';
		$this->assertFalse( WPS_WPR_Blocks::wps_wpr_is_editor_preview() );
		$this->assertSame( '', $this->blocks->wps_wpr_render_points_balance( array() ) );

		$GLOBALS['wp']->query_vars['rest_route'] = '/wp/v2/block-renderer/wps-wpr/points-balance';
		$this->assertTrue( WPS_WPR_Blocks::wps_wpr_is_editor_preview() );
		$this->assertStringContainsString( 'wps-wpr-block__notice', $this->blocks->wps_wpr_render_points_balance( array() ) );
		unset( $GLOBALS['wp']->query_vars['rest_route'] );
	}

	/**
	 * Leaderboard user IDs.
	 *
	 * @param  int $limit limit.
	 * @return int[]
	 */
	private function leaderboard_ids( $limit ) {
		return array_map(
			function ( $row ) {
				return $row['user']->ID;
			},
			WPS_WPR_Blocks::wps_wpr_get_leaderboard( $limit )
		);
	}
}
