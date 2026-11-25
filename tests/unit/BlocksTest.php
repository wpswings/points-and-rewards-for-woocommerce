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
		foreach ( array( 'wps_wpr_settings_gallery', 'wps_wpr_coupons_gallery' ) as $option ) {
			$this->saved_options[ $option ] = get_option( $option, null );
		}
		$this->blocks = new WPS_WPR_Blocks();
		wp_set_current_user( 0 );
	}

	/**
	 * Tear down.
	 */
	protected function tearDown(): void {
		foreach ( $this->saved_options as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->user_ids as $user_id ) {
			wp_delete_user( $user_id );
		}
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
}
