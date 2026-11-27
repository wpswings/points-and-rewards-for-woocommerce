<?php
/**
 * Tests for the persistent rewards launcher (tabbed mode of the floating widget).
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Thrown instead of dying at the end of an AJAX response.
 */
class WPS_WPR_Launcher_Test_Die extends Exception {}

/**
 * Rewards launcher tests.
 */
class RewardsLauncherTest extends TestCase {

	/**
	 * Option backups.
	 *
	 * @var array
	 */
	private $backup = array();

	/**
	 * Users created in a test.
	 *
	 * @var int[]
	 */
	private $users = array();

	/**
	 * Locale meridiem backup.
	 *
	 * @var array|null
	 */
	private $meridiem_backup = null;

	/**
	 * Main query backup.
	 *
	 * @var WP_Query|null
	 */
	private $query_backup = null;

	/**
	 * Rewards used by the negative balance tests.
	 *
	 * @return array
	 */
	public static function two_rewards() {
		return array(
			array( 'title' => '5 off', 'points' => 200 ),
			array( 'title' => '10% off', 'points' => 30 ),
		);
	}

	protected function setUp(): void {
		foreach ( array( WPS_WPR_Floating_Widget::OPTION, 'wps_wpr_settings_gallery' ) as $option ) {
			$this->backup[ $option ] = get_option( $option, null );
		}
		wp_set_current_user( 0 );
		WPS_WPR_Rewards_Launcher::wps_wpr_reset_catalog_cache();
	}

	protected function tearDown(): void {
		WPS_WPR_Rewards_Launcher::wps_wpr_reset_catalog_cache();
		if ( null !== $this->meridiem_backup ) {
			$GLOBALS['wp_locale']->meridiem = $this->meridiem_backup;
			$this->meridiem_backup          = null;
		}
		if ( null !== $this->query_backup ) {
			$GLOBALS['wp_query']     = $this->query_backup; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$GLOBALS['wp_the_query'] = $this->query_backup; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$this->query_backup      = null;
		}
		foreach ( $this->backup as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->users as $user_id ) {
			wp_delete_user( $user_id );
		}
		wp_set_current_user( 0 );
		remove_all_filters( 'wps_wpr_launcher_rewards' );
		remove_all_filters( 'wp_doing_ajax' );
		remove_all_filters( 'wp_die_ajax_handler' );
		remove_filter( 'doing_it_wrong_trigger_error', array( $this, 'skip_rest_send_json_notice' ), 10 );
		unset( $_REQUEST['nonce'], $_POST['nonce'] );
	}

	private function make_user( $points = 0, $log = array() ) {
		$user_id       = wp_insert_user(
			array(
				'user_login' => 'rltest_' . wp_generate_password( 8, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'rltest_' . wp_generate_password( 8, false ) . '@example.test',
				'first_name' => 'Rita',
				'role'       => 'customer',
			)
		);
		$this->users[] = $user_id;
		update_user_meta( $user_id, 'wps_wpr_points', $points );
		if ( $log ) {
			update_user_meta( $user_id, 'points_details', $log );
		}
		return $user_id;
	}

	private function launcher_settings( $overrides = array() ) {
		$input = array_merge(
			array(
				'wps_wpr_fw_enable'       => 'yes',
				'wps_wpr_fw_show_mobile'  => 'yes',
				'wps_wpr_fw_mode'         => 'launcher',
				'wps_wpr_fw_tab_home'     => 'yes',
				'wps_wpr_fw_tab_earn'     => 'yes',
				'wps_wpr_fw_tab_redeem'   => 'yes',
				'wps_wpr_fw_tab_activity' => 'yes',
				'wps_wpr_fw_tab_refer'    => 'yes',
			),
			$overrides
		);
		$settings = WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings( $input );
		update_option( WPS_WPR_Floating_Widget::OPTION, $settings );
		return $settings;
	}

	public function test_defaults_keep_compact_mode_and_all_tabs() {
		$defaults = WPS_WPR_Floating_Widget::wps_wpr_get_default_settings();
		$this->assertSame( 'compact', $defaults['wps_wpr_fw_mode'] );
		$this->assertSame( 'all', $defaults['wps_wpr_fw_targeting'] );
		foreach ( array_keys( WPS_WPR_Rewards_Launcher::wps_wpr_get_all_tabs() ) as $tab ) {
			$this->assertSame( 'yes', $defaults[ 'wps_wpr_fw_tab_' . $tab ] );
		}
	}

	public function test_sanitize_launcher_settings() {
		$clean = WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings(
			array(
				'wps_wpr_fw_mode'      => 'giant',
				'wps_wpr_fw_targeting' => 'everywhere',
				'wps_wpr_fw_page_ids'  => '12, abc, 34 34,-5, 0',
				'wps_wpr_fw_welcome'   => '<b>Hello</b> [NAME]',
				'wps_wpr_fw_tab_home'  => 'yes',
				'wps_wpr_fw_tab_earn'  => 'maybe',
			)
		);
		$this->assertSame( 'compact', $clean['wps_wpr_fw_mode'] );
		$this->assertSame( 'all', $clean['wps_wpr_fw_targeting'] );
		$this->assertSame( '12,34,5', $clean['wps_wpr_fw_page_ids'] );
		$this->assertSame( 'Hello [NAME]', $clean['wps_wpr_fw_welcome'] );
		$this->assertSame( 'yes', $clean['wps_wpr_fw_tab_home'] );
		$this->assertSame( 'no', $clean['wps_wpr_fw_tab_earn'] );
		$this->assertSame( 'no', $clean['wps_wpr_fw_tab_activity'] );

		$launcher = WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings(
			array(
				'wps_wpr_fw_mode'      => 'launcher',
				'wps_wpr_fw_targeting' => 'exclude',
			)
		);
		$this->assertSame( 'launcher', $launcher['wps_wpr_fw_mode'] );
		$this->assertSame( 'exclude', $launcher['wps_wpr_fw_targeting'] );
	}

	public function test_page_targeting_decisions() {
		$all     = array( 'wps_wpr_fw_targeting' => 'all', 'wps_wpr_fw_page_ids' => '5' );
		$include = array( 'wps_wpr_fw_targeting' => 'include', 'wps_wpr_fw_page_ids' => '5,9' );
		$exclude = array( 'wps_wpr_fw_targeting' => 'exclude', 'wps_wpr_fw_page_ids' => '5,9' );

		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 7, $all ) );
		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $all ) );

		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 9, $include ) );
		$this->assertFalse( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 7, $include ) );
		$this->assertFalse( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $include ), 'Archives are hidden in include mode.' );

		$this->assertFalse( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 5, $exclude ) );
		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 7, $exclude ) );
		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $exclude ) );

		$launcher = new WPS_WPR_Rewards_Launcher();
		$this->assertFalse( $launcher->wps_wpr_filter_display( false, $all ), 'An earlier "hide" decision is kept.' );
	}

	public function test_guest_tabs_skip_personal_tabs() {
		$settings = $this->launcher_settings();
		$this->assertSame( array( 'home', 'earn', 'redeem' ), array_keys( WPS_WPR_Rewards_Launcher::wps_wpr_get_enabled_tabs( $settings, 0 ) ) );
		$this->assertSame( array( 'home', 'earn', 'redeem', 'activity', 'refer' ), array_keys( WPS_WPR_Rewards_Launcher::wps_wpr_get_enabled_tabs( $settings, 5 ) ) );

		$none = $this->launcher_settings(
			array(
				'wps_wpr_fw_tab_home'     => 'no',
				'wps_wpr_fw_tab_earn'     => 'no',
				'wps_wpr_fw_tab_redeem'   => 'no',
				'wps_wpr_fw_tab_activity' => 'no',
				'wps_wpr_fw_tab_refer'    => 'no',
			)
		);
		$this->assertSame( array( 'home' ), array_keys( WPS_WPR_Rewards_Launcher::wps_wpr_get_enabled_tabs( $none, 5 ) ), 'Home is the fallback tab.' );
	}

	public function test_launcher_markup_renders_aria_tabs() {
		$user_id = $this->make_user( 250 );
		$this->launcher_settings( array( 'wps_wpr_fw_tab_redeem' => 'no' ) );

		$widget = new WPS_WPR_Floating_Widget();
		$html   = $widget->wps_wpr_get_markup( $user_id );

		$this->assertStringContainsString( 'wps-wpr-fw--launcher', $html );
		$this->assertStringContainsString( 'role="tablist"', $html );
		$this->assertSame( 4, substr_count( $html, 'role="tab"' ) );
		$this->assertSame( 4, substr_count( $html, 'role="tabpanel"' ) );
		$this->assertStringNotContainsString( 'data-wps-wpr-fw-tab="redeem"', $html );
		$this->assertSame( 1, substr_count( $html, 'aria-selected="true"' ) );
		$this->assertStringContainsString( 'Welcome back, Rita!', $html );
		$this->assertStringContainsString( '250', $html );
		$this->assertStringContainsString( 'data-wps-wpr-fw-activity', $html, 'Activity is a lazy-load placeholder.' );
		$this->assertSame( 1, substr_count( $html, 'wps-wpr-fw__launcher' ), 'Only one launcher button.' );
	}

	public function test_compact_mode_unchanged() {
		$user_id = $this->make_user( 10 );
		$this->launcher_settings( array( 'wps_wpr_fw_mode' => 'compact' ) );
		$html = ( new WPS_WPR_Floating_Widget() )->wps_wpr_get_markup( $user_id );
		$this->assertStringNotContainsString( 'role="tablist"', $html );
		$this->assertStringContainsString( 'wps-wpr-fw__section--balance', $html );
	}

	public function test_guest_markup_has_join_and_no_activity() {
		$this->launcher_settings();
		$html = ( new WPS_WPR_Floating_Widget() )->wps_wpr_get_markup( 0 );
		$this->assertStringContainsString( 'wps-wpr-fw__join', $html );
		$this->assertStringNotContainsString( 'data-wps-wpr-fw-activity', $html );
		$this->assertStringNotContainsString( '[NAME]', $html );
	}

	public function test_redeem_tab_lists_rewards_and_next_reward() {
		add_filter(
			'wps_wpr_launcher_rewards',
			static function () {
				return array(
					array( 'title' => 'Free mug', 'points' => 500 ),
					array( 'title' => 'Sticker', 'points' => 50 ),
				);
			}
		);
		$user_id = $this->make_user( 100 );
		$this->launcher_settings();
		$html = ( new WPS_WPR_Floating_Widget() )->wps_wpr_get_markup( $user_id );

		$this->assertStringContainsString( 'Free mug', $html );
		$this->assertStringContainsString( 'wps-wpr-fw__reward--available', $html );
		$this->assertStringContainsString( '400 more points to unlock Free mug', $html );
		$this->assertLessThan( strpos( $html, 'reward-title">Free mug' ), strpos( $html, 'reward-title">Sticker' ), 'Rewards are sorted by cost.' );
	}

	public function test_recent_activity_sorted_signed_and_limited() {
		$log = array(
			'registration'        => array(
				array( 'registration' => 50, 'date' => '2026-01-01 10:00:00am' ),
			),
			'cart_subtotal_point' => array(
				array( 'cart_subtotal_point' => 20, 'date' => '2026-03-01 10:00:00am' ),
			),
			'admin_points'        => array(
				array( 'admin_points' => 5, 'sign' => '-', 'date' => '2026-02-01 10:00:00am' ),
			),
			'broken'              => 'not-an-array',
		);
		for ( $i = 1; $i <= 12; $i++ ) {
			$log['points_on_order'][] = array( 'points_on_order' => $i, 'date' => '2025-06-' . sprintf( '%02d', $i ) . ' 10:00:00am' );
		}
		$user_id = $this->make_user( 0, $log );

		$entries = WPS_WPR_Rewards_Launcher::wps_wpr_get_recent_activity( $user_id );
		$this->assertCount( 10, $entries );
		$this->assertSame( 'cart_subtotal_point', $entries[0]['key'] );
		$this->assertSame( -20, $entries[0]['points'] );
		$this->assertSame( -5, $entries[1]['points'], 'Entries with a "-" sign are deductions.' );
		$this->assertSame( 50, $entries[2]['points'] );
		$this->assertSame( 12, $entries[3]['points'] );

		$html = WPS_WPR_Rewards_Launcher::wps_wpr_get_activity_html( $entries );
		$this->assertStringContainsString( 'wps-wpr-fw__activity-points--minus', $html );
		$this->assertStringContainsString( '+50', $html );
		$this->assertStringContainsString( 'No points activity yet.', WPS_WPR_Rewards_Launcher::wps_wpr_get_activity_html( array() ) );
	}

	/**
	 * Run the activity AJAX handler and return the decoded JSON.
	 *
	 * @param  string $nonce nonce value.
	 * @return array
	 */
	private function call_activity_ajax( $nonce ) {
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			static function () {
				return static function () {
					throw new WPS_WPR_Launcher_Test_Die();
				};
			}
		);
		$_REQUEST['nonce'] = $nonce;
		// BlocksTest defines REST_REQUEST for the rest of the run; wp_send_json() then
		// raises a "called incorrectly" notice (an error with WP_DEBUG on) that a real
		// admin-ajax.php request never sees.
		add_filter( 'doing_it_wrong_trigger_error', array( $this, 'skip_rest_send_json_notice' ), 10, 2 );

		ob_start();
		try {
			( new WPS_WPR_Rewards_Launcher() )->wps_wpr_ajax_activity();
		} catch ( WPS_WPR_Launcher_Test_Die $e ) {
			unset( $e );
		}
		$output = ob_get_clean();

		return json_decode( $output, true );
	}

	/**
	 * Silence only the wp_send_json() REST notice.
	 *
	 * @param  bool   $trigger  whether to trigger the notice.
	 * @param  string $function function name.
	 * @return bool
	 */
	public function skip_rest_send_json_notice( $trigger, $function ) {
		return 'wp_send_json' === $function ? false : $trigger;
	}

	public function test_activity_ajax_returns_only_current_users_entries() {
		$mine  = $this->make_user( 0, array( 'registration' => array( array( 'registration' => 77, 'date' => '2026-01-01 10:00:00am' ) ) ) );
		$other = $this->make_user( 0, array( 'registration' => array( array( 'registration' => 999, 'date' => '2026-01-01 10:00:00am' ) ) ) );

		wp_set_current_user( $mine );
		$_POST['user_id'] = $other;
		$json             = $this->call_activity_ajax( wp_create_nonce( WPS_WPR_Rewards_Launcher::NONCE_ACTION ) );
		unset( $_POST['user_id'] );

		$this->assertTrue( $json['success'] );
		$this->assertStringContainsString( '+77', $json['data']['html'] );
		$this->assertStringNotContainsString( '999', $json['data']['html'] );
	}

	public function test_activity_ajax_enforces_nonce_and_login() {
		$user_id = $this->make_user( 0 );
		wp_set_current_user( $user_id );
		$json = $this->call_activity_ajax( 'bad-nonce' );
		$this->assertFalse( $json['success'] );

		wp_set_current_user( 0 );
		$json = $this->call_activity_ajax( wp_create_nonce( WPS_WPR_Rewards_Launcher::NONCE_ACTION ) );
		$this->assertFalse( $json['success'] );
	}

	public function test_negative_balance_counts_as_zero_for_rewards() {
		add_filter( 'wps_wpr_launcher_rewards', array( __CLASS__, 'two_rewards' ) );
		$user_id = $this->make_user( -50 );
		$this->launcher_settings();
		$html = ( new WPS_WPR_Floating_Widget() )->wps_wpr_get_markup( $user_id );

		$this->assertStringContainsString( '30 more points to unlock 10% off', $html, 'The next reward is the cheapest one not yet affordable.' );
		$this->assertStringNotContainsString( 'to unlock 5 off', $html );
		$this->assertStringNotContainsString( 'wps-wpr-fw__reward--available', $html, 'Nothing is available with a negative balance.' );
		$this->assertStringContainsString( 'aria-valuenow="0"', $html );
		$this->assertStringContainsString( 'width:0%', $html );

		$next = WPS_WPR_Rewards_Launcher::wps_wpr_get_next_reward( -50, self::two_rewards() );
		$this->assertSame( 30, $next['points'], 'Unsorted rewards still give the cheapest one.' );
		$this->assertSame( 200, WPS_WPR_Rewards_Launcher::wps_wpr_get_next_reward( 30, self::two_rewards() )['points'] );
		$this->assertNull( WPS_WPR_Rewards_Launcher::wps_wpr_get_next_reward( 500, self::two_rewards() ) );
		$this->assertSame( 0, WPS_WPR_Rewards_Launcher::wps_wpr_get_spendable_balance( $user_id ) );
	}

	public function test_page_types_target_front_page_archives_and_search() {
		$clean = WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings(
			array( 'wps_wpr_fw_page_types' => array( 'front_page', 'search', 'bogus', '<b>' ) )
		);
		$this->assertSame( 'front_page,search', $clean['wps_wpr_fw_page_types'] );
		$this->assertSame( array( 'front_page', 'search' ), WPS_WPR_Rewards_Launcher::wps_wpr_parse_page_types( $clean['wps_wpr_fw_page_types'] ) );

		$include = array( 'wps_wpr_fw_targeting' => 'include', 'wps_wpr_fw_page_ids' => '5', 'wps_wpr_fw_page_types' => 'front_page,archive' );
		$exclude = array( 'wps_wpr_fw_targeting' => 'exclude', 'wps_wpr_fw_page_ids' => '5', 'wps_wpr_fw_page_types' => 'search' );

		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $include, array( 'front_page', 'blog' ) ) );
		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $include, array( 'archive' ) ) );
		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 5, $include, array() ), 'Page IDs keep working.' );
		$this->assertFalse( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $include, array( 'search' ) ) );
		$this->assertFalse( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $exclude, array( 'search' ) ) );
		$this->assertFalse( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 5, $exclude, array() ) );
		$this->assertTrue( WPS_WPR_Rewards_Launcher::wps_wpr_page_targeting_allows( 0, $exclude, array( 'archive' ) ) );

		// A real search request is detected through the conditional tags.
		$this->query_backup      = $GLOBALS['wp_query'];
		$GLOBALS['wp_query']     = new WP_Query( array( 's' => 'rltest-nothing-matches' ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_the_query'] = $GLOBALS['wp_query']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$this->assertContains( 'search', WPS_WPR_Rewards_Launcher::wps_wpr_get_current_page_types() );
		$launcher = new WPS_WPR_Rewards_Launcher();
		$this->assertFalse( $launcher->wps_wpr_filter_display( true, $exclude ), 'Search results are hidden when "Search results" is excluded.' );
		$this->assertTrue( $launcher->wps_wpr_filter_display( true, array_merge( $include, array( 'wps_wpr_fw_page_types' => 'search' ) ) ) );
	}

	public function test_activity_sorts_translated_meridiem_dates() {
		$this->meridiem_backup                       = $GLOBALS['wp_locale']->meridiem;
		$GLOBALS['wp_locale']->meridiem['am']        = 'vorm.';
		$GLOBALS['wp_locale']->meridiem['pm']        = 'nachm.';

		$this->assertSame( gmmktime( 14, 5, 6, 3, 1, 2026 ), WPS_WPR_Rewards_Launcher::wps_wpr_parse_log_date( '2026-03-01 02:05:06nachm.' ) );
		$this->assertSame( gmmktime( 0, 5, 6, 3, 1, 2026 ), WPS_WPR_Rewards_Launcher::wps_wpr_parse_log_date( '2026-03-01 12:05:06vorm.' ) );
		$this->assertSame( gmmktime( 14, 5, 6, 3, 1, 2026 ), WPS_WPR_Rewards_Launcher::wps_wpr_parse_log_date( '2026-03-01 02:05:06pm' ) );
		$this->assertSame( gmmktime( 0, 0, 0, 3, 1, 2026 ), WPS_WPR_Rewards_Launcher::wps_wpr_parse_log_date( '2026-03-01 02:05:06अपराह्न' ), 'Unknown meridiem keeps the day.' );
		$this->assertSame( gmmktime( 0, 0, 0, 3, 1, 2026 ), WPS_WPR_Rewards_Launcher::wps_wpr_parse_log_date( '2026-03-01' ) );
		$this->assertSame( 0, WPS_WPR_Rewards_Launcher::wps_wpr_parse_log_date( 'yesterday' ) );

		$log     = array(
			'registration'    => array( array( 'registration' => 3, 'date' => '2026-03-01 02:00:00nachm.' ) ),
			'comment'         => array( array( 'comment' => 2, 'date' => '2026-03-01 09:00:00vorm.' ) ),
			'points_on_order' => array(
				array( 'points_on_order' => 1, 'date' => '2026-02-01 11:00:00nachm.' ),
				array( 'points_on_order' => 4, 'date' => '2026-02-20 01:00:00अपराह्न' ),
				array( 'points_on_order' => 5, 'date' => '2026-02-20 01:00:00अपराह्न' ),
			),
		);
		$user_id = $this->make_user( 0, $log );
		$points  = wp_list_pluck( WPS_WPR_Rewards_Launcher::wps_wpr_get_recent_activity( $user_id ), 'points' );
		$this->assertSame( array( 3, 2, 5, 4, 1 ), $points, 'Newest first; same day falls back to the order the rows were logged.' );

		$html = WPS_WPR_Rewards_Launcher::wps_wpr_get_activity_html( WPS_WPR_Rewards_Launcher::wps_wpr_get_recent_activity( $user_id ) );
		$this->assertStringNotContainsString( 'nachm.', $html, 'Parsed dates are shown in the site date format.' );
	}

	public function test_guest_gets_guest_welcome() {
		$this->launcher_settings();
		$html = ( new WPS_WPR_Floating_Widget() )->wps_wpr_get_markup( 0 );
		$this->assertStringNotContainsString( 'Welcome back', $html );
		$this->assertStringContainsString( 'Join our rewards program', $html );

		$this->launcher_settings( array( 'wps_wpr_fw_welcome' => 'Hello there!' ) );
		$html = ( new WPS_WPR_Floating_Widget() )->wps_wpr_get_markup( 0 );
		$this->assertStringContainsString( 'Hello there!', $html, 'A welcome without [NAME] is shown to guests as written.' );
	}

	public function test_catalog_is_queried_once_per_request() {
		$calls = 0;
		add_filter(
			'wps_wpr_launcher_rewards',
			static function () use ( &$calls ) {
				++$calls;
				return array( array( 'title' => 'Sticker', 'points' => 500 ) );
			}
		);
		$user_id = $this->make_user( 100 );
		$this->launcher_settings();
		$html = ( new WPS_WPR_Floating_Widget() )->wps_wpr_get_markup( $user_id );
		$this->assertStringContainsString( '400 more points to unlock Sticker', $html );
		$this->assertStringContainsString( 'reward-title">Sticker', $html );
		$this->assertSame( 1, $calls, 'Home and Redeem share one catalog lookup.' );

		WPS_WPR_Rewards_Launcher::wps_wpr_reset_catalog_cache();
		WPS_WPR_Rewards_Launcher::wps_wpr_get_catalog_rewards();
		$this->assertSame( 2, $calls );
	}

	public function test_hooks_registered_when_plugin_enabled() {
		if ( ! has_action( 'wp_footer' ) || false === has_filter( 'wps_wpr_floating_widget_display' ) ) {
			$this->markTestSkipped( 'Points program is disabled on this site, so the public hooks are not registered.' );
		}
		$this->assertNotFalse( has_action( 'wp_ajax_' . WPS_WPR_Rewards_Launcher::AJAX_ACTION ) );
	}
}
