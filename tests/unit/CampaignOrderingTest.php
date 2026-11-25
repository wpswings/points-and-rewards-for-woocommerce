<?php
/**
 * Tests for campaign and reward display ordering (WPS-8329).
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Campaign ordering tests.
 */
class CampaignOrderingTest extends TestCase {

	/**
	 * Campaign settings before the test.
	 *
	 * @var mixed
	 */
	private $campaign_backup;

	/**
	 * Back up the campaign settings.
	 */
	protected function setUp(): void {
		$this->campaign_backup = get_option( 'wps_wpr_campaign_settings', null );
	}

	/**
	 * Restore the campaign settings.
	 */
	protected function tearDown(): void {
		if ( null === $this->campaign_backup ) {
			delete_option( 'wps_wpr_campaign_settings' );
		} else {
			update_option( 'wps_wpr_campaign_settings', $this->campaign_backup );
		}
	}

	public function test_classes_and_helpers_loaded() {
		$this->assertTrue( class_exists( 'WPS_WPR_Campaign_Ordering' ) );
		$this->assertTrue( function_exists( 'wps_wpr_sort_items_by_order' ) );
		$this->assertTrue( function_exists( 'wps_wpr_sort_items_by_field' ) );
	}

	public function test_no_order_numbers_keeps_default_order() {
		$items = array(
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
		);
		$this->assertSame( array( 'a', 'b', 'c' ), array_keys( wps_wpr_sort_items_by_order( $items, array() ) ) );
	}

	public function test_sorts_by_number_and_unnumbered_keep_relative_order_last() {
		$items = array(
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
			'd' => 'D',
			'e' => 'E',
		);
		$sorted = wps_wpr_sort_items_by_order(
			$items,
			array(
				'd' => 1,
				'b' => 2,
			)
		);
		$this->assertSame( array( 'd', 'b', 'a', 'c', 'e' ), array_keys( $sorted ) );
		$this->assertSame( 'D', reset( $sorted ), 'Values stay attached to their keys.' );
	}

	public function test_ties_are_stable_and_blank_values_ignored() {
		$items  = array(
			'x' => 1,
			'y' => 2,
			'z' => 3,
		);
		$sorted = wps_wpr_sort_items_by_order(
			$items,
			array(
				'z' => 5,
				'y' => 5,
				'x' => '',
			)
		);
		$this->assertSame( array( 'y', 'z', 'x' ), array_keys( $sorted ) );
	}

	public function test_sort_filter_can_change_result() {
		$callback = function ( $items ) {
			return array_reverse( $items, true );
		};
		add_filter( 'wps_wpr_sort_items_by_order', $callback );
		$sorted = wps_wpr_sort_items_by_order( array( 'a' => 1, 'b' => 2 ), array() );
		remove_filter( 'wps_wpr_sort_items_by_order', $callback );

		$this->assertSame( array( 'b', 'a' ), array_keys( $sorted ) );
	}

	public function test_sort_by_field_reindexes_lists_and_keeps_unordered_lists() {
		$rewards = array(
			array( 'id' => 'r1' ),
			array(
				'id'    => 'r2',
				'order' => 1,
			),
			array( 'id' => 'r3' ),
		);
		$sorted  = wps_wpr_sort_items_by_field( $rewards, 'order' );
		$this->assertSame( array( 0, 1, 2 ), array_keys( $sorted ) );
		$this->assertSame( array( 'r2', 'r1', 'r3' ), wp_list_pluck( $sorted, 'id' ) );

		$plain = array( array( 'id' => 'a' ), array( 'id' => 'b' ) );
		$this->assertSame( $plain, WPS_WPR_Campaign_Ordering::wps_wpr_sort_rewards( $plain ), 'Rewards without order numbers are unchanged.' );
		$this->assertSame( array(), wps_wpr_sort_items_by_field( array() ) );
	}

	public function test_reward_catalog_filter_is_hooked() {
		$this->assertNotFalse( has_filter( 'wps_wpr_reward_catalog_available_rewards', array( 'WPS_WPR_Campaign_Ordering', 'wps_wpr_sort_rewards' ) ) );

		$rewards = apply_filters(
			'wps_wpr_reward_catalog_available_rewards',
			array(
				array(
					'id'    => 'late',
					'order' => 9,
				),
				array(
					'id'    => 'early',
					'order' => 2,
				),
			)
		);
		$this->assertSame( array( 'early', 'late' ), wp_list_pluck( $rewards, 'id' ) );
	}

	public function test_sanitize_priorities() {
		$clean = WPS_WPR_Campaign_Ordering::wps_wpr_sanitize_priorities(
			array(
				'birthday'        => '3',
				'Spin_Wheel'      => '1',
				'quiz'            => '',
				'insta_profile'   => 'abc',
				'share_twitter'   => '-4',
				'first_order'     => '120000',
				'<script>bad</sc' => '2',
				'comments'        => array( 1 ),
			)
		);
		$this->assertSame(
			array(
				'birthday'      => 3,
				'spin_wheel'    => 1,
				'share_twitter' => 4,
				'first_order'   => WPS_WPR_Campaign_Ordering::MAX_ORDER,
				'scriptbadsc'   => 2,
			),
			$clean
		);
		$this->assertSame( array(), WPS_WPR_Campaign_Ordering::wps_wpr_sanitize_priorities( 'nope' ) );
	}

	public function test_legacy_settings_without_priorities_keep_default_order() {
		update_option(
			'wps_wpr_campaign_settings',
			array(
				'wps_wpr_enable_campaign_settings'    => 'yes',
				'wps_wpr_social_share_campaign_label' => array( 'share_twitter', 'insta_profile' ),
			)
		);

		$this->assertSame( array(), WPS_WPR_Campaign_Ordering::wps_wpr_get_priorities() );

		$items = WPS_WPR_Campaign_Ordering::wps_wpr_get_admin_items(
			array(
				'share_twitter' => 'Share on Twitter (X)',
				'insta_profile' => 'Visit Instagram Profile',
			)
		);
		$this->assertSame( array( 'birthday', 'comments', 'spin_wheel', 'first_order', 'quiz', 'share_twitter', 'insta_profile' ), array_keys( $items ) );
		$this->assertSame( 'Share on Twitter (X)', $items['share_twitter'] );

		$blocks = array(
			'birthday'      => '<div>Birthday</div>',
			'comments'      => '',
			'spin_wheel'    => "  \n ",
			'quiz'          => '<div>Quiz</div>',
			'insta_profile' => '<div>Insta</div>',
		);
		$this->assertSame( array( 'birthday', 'quiz', 'insta_profile' ), array_keys( WPS_WPR_Campaign_Ordering::wps_wpr_sort_campaign_blocks( $blocks ) ), 'Empty blocks are dropped and order is unchanged.' );
	}

	public function test_saved_order_applies_to_admin_list_and_blocks() {
		update_option(
			'wps_wpr_campaign_settings',
			array(
				'wps_wpr_social_share_campaign_label' => array( 'insta_profile' ),
				WPS_WPR_Campaign_Ordering::SETTING_KEY => array(
					'insta_profile' => 1,
					'quiz'          => 2,
					'birthday'      => 3,
				),
			)
		);

		$items = WPS_WPR_Campaign_Ordering::wps_wpr_get_admin_items( array() );
		$this->assertSame( array( 'insta_profile', 'quiz', 'birthday', 'comments', 'spin_wheel', 'first_order' ), array_keys( $items ) );
		$this->assertSame( 'insta_profile', $items['insta_profile'], 'Unknown social labels fall back to their key.' );

		$blocks = WPS_WPR_Campaign_Ordering::wps_wpr_sort_campaign_blocks(
			array(
				'birthday'      => 'B',
				'spin_wheel'    => 'S',
				'quiz'          => 'Q',
				'insta_profile' => 'I',
			)
		);
		$this->assertSame( 'IQBS', implode( '', $blocks ) );
	}

	public function test_campaign_modal_renders_in_saved_order() {
		if ( ! class_exists( 'Points_Rewards_For_WooCommerce_Public' ) ) {
			$this->markTestSkipped( 'Public class not loaded.' );
		}

		update_option(
			'wps_wpr_campaign_settings',
			array(
				'wps_wpr_enable_campaign_settings'    => 'yes',
				'wps_wpr_enable_birthday_campaign'    => 'yes',
				'wps_wpr_select_page_for_campaign'    => array(),
				'wps_wpr_social_share_campaign_label' => array( 'insta_profile', 'share_twitter' ),
				'wps_wpr_social_share_url'            => array( 'https://example.com/insta', 'https://example.com/x' ),
				'wps_wpr_social_share_points'         => array( '5', '7' ),
				WPS_WPR_Campaign_Ordering::SETTING_KEY => array(
					'share_twitter' => 1,
					'birthday'      => 2,
				),
			)
		);

		$public = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION );
		ob_start();
		$public->wps_wpr_show_campaign_modal();
		$html = ob_get_clean();

		$twitter  = strpos( $html, 'Share on Twitter (X)' );
		$birthday = strpos( $html, 'Enter your birthday' );
		$insta    = strpos( $html, 'Visit Instagram Profile' );

		$this->assertNotFalse( $twitter, 'Twitter campaign rendered.' );
		$this->assertNotFalse( $birthday, 'Birthday campaign rendered.' );
		$this->assertNotFalse( $insta, 'Instagram campaign rendered.' );
		$this->assertLessThan( $birthday, $twitter, 'Order 1 comes before order 2.' );
		$this->assertLessThan( $insta, $birthday, 'Numbered campaigns come before unnumbered ones.' );
	}
}
