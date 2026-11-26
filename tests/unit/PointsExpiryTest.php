<?php
/**
 * Tests for the basic points expiry.
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Points expiry tests.
 */
class PointsExpiryTest extends TestCase {

	/**
	 * Users created by a test.
	 *
	 * @var int[]
	 */
	private $users = array();

	/**
	 * Saved option value before the test.
	 *
	 * @var mixed
	 */
	private $saved_option;

	/**
	 * Saved activation / grace options before the test.
	 *
	 * @var array
	 */
	private $saved_flags = array();

	/**
	 * Next run of the free and Pro cron hooks before the test.
	 *
	 * @var array
	 */
	private $saved_crons = array();

	/**
	 * Emails captured during a test.
	 *
	 * @var array
	 */
	private $mails = array();

	/**
	 * Expiry handler.
	 *
	 * @var WPS_WPR_Points_Expiry
	 */
	private $expiry;

	/**
	 * Set up.
	 */
	protected function setUp(): void {
		$this->saved_option = get_option( WPS_WPR_Points_Expiry::OPTION, null );
		foreach ( array( WPS_WPR_Points_Expiry::ACTIVATED_OPTION, WPS_WPR_Points_Expiry::GRACE_DONE_OPTION ) as $flag ) {
			$this->saved_flags[ $flag ] = get_option( $flag, null );
		}
		foreach ( array( WPS_WPR_Points_Expiry::CRON_HOOK, WPS_WPR_Points_Expiry::PRO_CRON_HOOK ) as $hook ) {
			$this->saved_crons[ $hook ] = wp_next_scheduled( $hook );
		}
		$this->expiry       = new WPS_WPR_Points_Expiry();
		$this->mails        = array();
		add_filter( 'pre_wp_mail', array( $this, 'capture_mail' ), 10, 2 );
		// Exercise the free expiry even when Pro is active; tests that need
		// Pro to handle expiry add a later filter.
		add_filter( 'wps_wpr_pro_handles_points_expiry', '__return_false', 5 );
	}

	/**
	 * Tear down.
	 */
	protected function tearDown(): void {
		remove_filter( 'pre_wp_mail', array( $this, 'capture_mail' ), 10 );
		remove_all_filters( 'wps_wpr_pro_handles_points_expiry' );
		remove_all_filters( 'wps_wpr_points_expiry_user_query_args' );
		remove_all_actions( 'wps_wpr_points_expired' );
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->users as $user_id ) {
			wp_delete_user( $user_id );
		}
		if ( null === $this->saved_option ) {
			delete_option( WPS_WPR_Points_Expiry::OPTION );
		} else {
			update_option( WPS_WPR_Points_Expiry::OPTION, $this->saved_option );
		}
		foreach ( $this->saved_flags as $flag => $value ) {
			if ( null === $value ) {
				delete_option( $flag );
			} else {
				update_option( $flag, $value, false );
			}
		}
		foreach ( $this->saved_crons as $hook => $next ) {
			wp_clear_scheduled_hook( $hook );
			if ( $next ) {
				wp_schedule_event( $next, 'daily', $hook );
			}
		}
	}

	/**
	 * Capture an email instead of sending it.
	 *
	 * @param  null|bool $short_circuit short circuit.
	 * @param  array     $atts          mail args.
	 * @return bool
	 */
	public function capture_mail( $short_circuit, $atts ) {
		$this->mails[] = $atts;
		return true;
	}

	/**
	 * Create a customer with a balance.
	 *
	 * @param  int $points balance.
	 * @return int
	 */
	private function make_user( $points ) {
		$user_id = wp_insert_user(
			array(
				'user_login' => 'wps_expiry_' . wp_generate_password( 8, false ),
				'user_email' => 'wps_expiry_' . wp_generate_password( 8, false ) . '@example.test',
				'user_pass'  => wp_generate_password(),
				'role'       => 'customer',
			)
		);
		$this->users[] = $user_id;
		update_user_meta( $user_id, 'wps_wpr_points', $points );
		return $user_id;
	}

	/**
	 * Enable expiry with the given overrides.
	 *
	 * @param  array $overrides settings.
	 * @return array settings.
	 */
	private function enable( $overrides = array() ) {
		$settings = array_merge(
			WPS_WPR_Points_Expiry::wps_wpr_get_default_settings(),
			array(
				'wps_wpr_points_expiration_enable'     => 1,
				'wps_wpr_points_expiration_threshold'  => 10,
				'wps_wpr_points_expiration_time_num'   => 30,
				'wps_wpr_points_expiration_time_drop'  => array( 'days' ),
				'wps_wpr_enable_expire_notify_setting' => 1,
				'wps_wpr_points_expiration_email'      => 7,
			),
			$overrides
		);
		update_option( WPS_WPR_Points_Expiry::OPTION, $settings );
		// As if the admin saved the free tab and the first run already happened.
		update_option( WPS_WPR_Points_Expiry::ACTIVATED_OPTION, time(), false );
		update_option( WPS_WPR_Points_Expiry::GRACE_DONE_OPTION, time(), false );
		return WPS_WPR_Points_Expiry::wps_wpr_get_settings();
	}

	/**
	 * Limit the daily run to the given users.
	 *
	 * @param int[] $ids user IDs.
	 * @return void
	 */
	private function limit_run_to( $ids ) {
		add_filter(
			'wps_wpr_points_expiry_user_query_args',
			function ( $args ) use ( $ids ) {
				$args['include'] = $ids;
				return $args;
			}
		);
	}

	/**
	 * Expiry dates per unit.
	 */
	public function test_calculate_expiry_date() {
		$this->assertSame( '2026-12-01', WPS_WPR_Points_Expiry::wps_wpr_calculate_expiry_date( '2026-11-01', 30, 'days' ) );
		$this->assertSame( '2026-11-15', WPS_WPR_Points_Expiry::wps_wpr_calculate_expiry_date( '2026-11-01', 2, 'weeks' ) );
		$this->assertSame( '2027-05-01', WPS_WPR_Points_Expiry::wps_wpr_calculate_expiry_date( '2026-11-01', 6, 'months' ) );
		$this->assertSame( '2027-11-01', WPS_WPR_Points_Expiry::wps_wpr_calculate_expiry_date( '2026-11-01', 1, 'years' ) );
		// Unknown unit falls back to days, and the period is at least 1.
		$this->assertSame( '2026-11-02', WPS_WPR_Points_Expiry::wps_wpr_calculate_expiry_date( '2026-11-01', 0, 'decades' ) );
	}

	/**
	 * Sanitizing clamps values and keeps keys saved by Pro.
	 */
	public function test_sanitize_settings_keeps_pro_keys() {
		update_option( WPS_WPR_Points_Expiry::OPTION, array( 'wps_wpr_threshold_notif' => 'Pro message', 'wps_wpr_enable_points_upgrade' => 1 ) );
		$clean = WPS_WPR_Points_Expiry::wps_wpr_sanitize_settings(
			array(
				'wps_wpr_points_expiration_enable'    => 'yes',
				'wps_wpr_points_expiration_threshold' => '-5',
				'wps_wpr_points_expiration_time_num'  => '0',
				'wps_wpr_points_expiration_time_drop' => 'fortnights',
				'wps_wpr_points_expiration_email'     => '3',
				'wps_wpr_re_notification'             => '<b>Hi</b> [TOTALPOINT]',
			)
		);
		$this->assertSame( 1, $clean['wps_wpr_points_expiration_enable'] );
		$this->assertSame( 0, $clean['wps_wpr_points_exp_onmyaccount'] );
		$this->assertSame( 5, $clean['wps_wpr_points_expiration_threshold'] );
		$this->assertSame( 1, $clean['wps_wpr_points_expiration_time_num'] );
		$this->assertSame( array( 'months' ), $clean['wps_wpr_points_expiration_time_drop'] );
		$this->assertSame( 3, $clean['wps_wpr_points_expiration_email'] );
		$this->assertSame( 'Hi [TOTALPOINT]', $clean['wps_wpr_re_notification'] );
		$this->assertSame( 'Pro message', $clean['wps_wpr_threshold_notif'] );
		$this->assertSame( 1, $clean['wps_wpr_enable_points_upgrade'] );
	}

	/**
	 * Reaching the threshold sets the expiry date; below it does nothing.
	 */
	public function test_expiry_date_set_at_threshold() {
		$settings = $this->enable();
		$rich     = $this->make_user( 50 );
		$poor     = $this->make_user( 5 );

		$this->assertSame( 'scheduled', $this->expiry->wps_wpr_process_user( $rich, $settings, '2026-11-25' ) );
		$this->assertSame( '2026-12-25', get_user_meta( $rich, WPS_WPR_Points_Expiry::DATE_META, true ) );

		$this->assertSame( 'none', $this->expiry->wps_wpr_process_user( $poor, $settings, '2026-11-25' ) );
		$this->assertSame( '', get_user_meta( $poor, WPS_WPR_Points_Expiry::DATE_META, true ) );
	}

	/**
	 * On the expiry date the whole balance expires and is logged.
	 */
	public function test_points_expire_and_are_logged() {
		$settings = $this->enable();
		$user_id  = $this->make_user( 120 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2026-11-20' );

		$fired = array();
		add_action(
			'wps_wpr_points_expired',
			function ( $uid, $points ) use ( &$fired ) {
				$fired[] = array( $uid, $points );
			},
			10,
			2
		);

		$this->assertSame( 'expired', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-25' ) );
		$this->assertSame( 0, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$this->assertSame( '', get_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, true ) );

		$log = get_user_meta( $user_id, 'points_details', true );
		$this->assertCount( 1, $log['expired_details'] );
		$this->assertSame( 120, $log['expired_details'][0]['expired_details'] );
		$this->assertSame( '2026-11-25', $log['expired_details'][0]['date'] );
		$this->assertSame( array( array( $user_id, 120 ) ), $fired );
		$this->assertCount( 1, $this->mails, 'Expired email sent.' );
	}

	/**
	 * One reminder is sent inside the reminder window, and only once.
	 */
	public function test_reminder_sent_once() {
		$settings = $this->enable();
		$user_id  = $this->make_user( 80 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2026-11-30' );

		// Outside the 7-day window.
		$this->assertSame( 'none', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-20' ) );
		$this->assertCount( 0, $this->mails );

		// Inside the window: one reminder.
		$this->assertSame( 'reminded', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-25' ) );
		$this->assertSame( 'none', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-26' ) );
		$this->assertCount( 1, $this->mails );
		$this->assertStringContainsString( '80', $this->mails[0]['message'] );
		$this->assertSame( 80, (int) get_user_meta( $user_id, 'wps_wpr_points', true ), 'Reminder does not touch the balance.' );
	}

	/**
	 * No emails when notifications are off.
	 */
	public function test_no_email_when_notifications_off() {
		$settings = $this->enable( array( 'wps_wpr_enable_expire_notify_setting' => 0 ) );
		$user_id  = $this->make_user( 80 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2026-11-28' );

		$this->assertSame( 'none', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-25' ) );
		$this->assertSame( 'expired', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-28' ) );
		$this->assertCount( 0, $this->mails );
	}

	/**
	 * A zero balance drops a stale expiry date.
	 */
	public function test_zero_balance_clears_stale_date() {
		$settings = $this->enable();
		$user_id  = $this->make_user( 0 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2026-11-01' );

		$this->assertSame( 'none', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-25' ) );
		$this->assertSame( '', get_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, true ) );
		$this->assertSame( '', get_user_meta( $user_id, 'points_details', true ) );
	}

	/**
	 * Users restricted from points are skipped.
	 */
	public function test_restricted_user_skipped() {
		$settings = $this->enable();
		$user_id  = $this->make_user( 50 );
		update_user_meta( $user_id, 'wps_wpr_restrict_user', 'yes' );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2026-11-01' );

		$this->assertSame( 'none', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-25' ) );
		$this->assertSame( 50, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
	}

	/**
	 * The daily run processes matching users in batches.
	 */
	public function test_daily_run_expires_matching_users() {
		$this->enable();
		$past   = $this->make_user( 40 );
		$future = $this->make_user( 40 );
		update_user_meta( $past, WPS_WPR_Points_Expiry::DATE_META, '2000-01-01' );
		update_user_meta( $future, WPS_WPR_Points_Expiry::DATE_META, '2999-01-01' );

		// Limit the run to this test's users.
		$ids = array( $past, $future );
		add_filter(
			'wps_wpr_points_expiry_user_query_args',
			function ( $args ) use ( $ids ) {
				$args['include'] = $ids;
				return $args;
			}
		);

		$counts = $this->expiry->wps_wpr_process_expiry();
		$this->assertSame( 1, $counts['expired'] );
		$this->assertSame( 0, (int) get_user_meta( $past, 'wps_wpr_points', true ) );
		$this->assertSame( 40, (int) get_user_meta( $future, 'wps_wpr_points', true ) );
	}

	/**
	 * Nothing runs when expiry is disabled.
	 */
	public function test_daily_run_idle_when_disabled() {
		$this->enable( array( 'wps_wpr_points_expiration_enable' => 0 ) );
		$user_id = $this->make_user( 40 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2000-01-01' );

		$counts = $this->expiry->wps_wpr_process_expiry();
		$this->assertSame( 0, array_sum( $counts ) );
		$this->assertSame( 40, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
	}

	/**
	 * The free expiry stays idle when Pro handles expiry.
	 */
	public function test_daily_run_idle_when_pro_handles_expiry() {
		$this->enable();
		$user_id = $this->make_user( 40 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2000-01-01' );

		add_filter( 'wps_wpr_pro_handles_points_expiry', '__return_true' );
		$this->assertFalse( WPS_WPR_Points_Expiry::wps_wpr_is_active() );
		$counts = $this->expiry->wps_wpr_process_expiry();
		$this->assertSame( 0, array_sum( $counts ) );
		$this->assertSame( 40, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
	}

	/**
	 * An older Pro is detected by its cron callback.
	 */
	public function test_pro_detected_by_cron_callback() {
		if ( defined( 'WPS_WPR_PRO_HANDLES_POINTS_EXPIRY' ) ) {
			$this->markTestSkipped( 'Pro is active and already handles expiry.' );
		}
		remove_all_filters( 'wps_wpr_pro_handles_points_expiry' );
		$this->assertFalse( WPS_WPR_Points_Expiry::wps_wpr_pro_handles_expiry() );

		$fake = new class() {
			/**
			 * Stand-in for the Pro cron callback.
			 */
			public function wps_wpr_check_daily_about_points_expiration() {}
		};
		add_action( WPS_WPR_Points_Expiry::PRO_CRON_HOOK, array( $fake, 'wps_wpr_check_daily_about_points_expiration' ) );
		$this->assertTrue( WPS_WPR_Points_Expiry::wps_wpr_pro_handles_expiry() );
		remove_action( WPS_WPR_Points_Expiry::PRO_CRON_HOOK, array( $fake, 'wps_wpr_check_daily_about_points_expiration' ) );
	}

	/**
	 * Saving keeps the free cron in line with the setting.
	 */
	public function test_cron_synced_with_setting() {
		$this->enable();
		WPS_WPR_Points_Expiry::wps_wpr_sync_cron_schedule();
		$this->assertNotFalse( wp_next_scheduled( WPS_WPR_Points_Expiry::CRON_HOOK ) );

		$this->enable( array( 'wps_wpr_points_expiration_enable' => 0 ) );
		WPS_WPR_Points_Expiry::wps_wpr_sync_cron_schedule();
		$this->assertFalse( wp_next_scheduled( WPS_WPR_Points_Expiry::CRON_HOOK ) );
	}

	/**
	 * The free plugin never schedules or clears Pro's expiry cron.
	 */
	public function test_free_never_touches_pro_cron() {
		wp_clear_scheduled_hook( WPS_WPR_Points_Expiry::PRO_CRON_HOOK );
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', WPS_WPR_Points_Expiry::PRO_CRON_HOOK );
		$pro_next = wp_next_scheduled( WPS_WPR_Points_Expiry::PRO_CRON_HOOK );

		// Expiry off, Pro "not handling" (e.g. licence lapsed): the free save must leave Pro's event alone.
		$this->enable( array( 'wps_wpr_points_expiration_enable' => 0 ) );
		WPS_WPR_Points_Expiry::wps_wpr_update_activation();
		WPS_WPR_Points_Expiry::wps_wpr_sync_cron_schedule();
		$this->assertSame( $pro_next, wp_next_scheduled( WPS_WPR_Points_Expiry::PRO_CRON_HOOK ) );

		// Pro handling expiry: the free cron is removed, Pro's stays.
		$this->enable();
		WPS_WPR_Points_Expiry::wps_wpr_sync_cron_schedule();
		$this->assertNotFalse( wp_next_scheduled( WPS_WPR_Points_Expiry::CRON_HOOK ) );
		add_filter( 'wps_wpr_pro_handles_points_expiry', '__return_true' );
		$this->expiry->wps_wpr_maybe_schedule_cron();
		$this->assertFalse( wp_next_scheduled( WPS_WPR_Points_Expiry::CRON_HOOK ) );
		$this->assertSame( $pro_next, wp_next_scheduled( WPS_WPR_Points_Expiry::PRO_CRON_HOOK ) );

		// Deactivating free clears only the free cron.
		wp_schedule_event( time(), 'daily', WPS_WPR_Points_Expiry::CRON_HOOK );
		wps_wpr_clear_points_expiry_cron();
		$this->assertFalse( wp_next_scheduled( WPS_WPR_Points_Expiry::CRON_HOOK ) );
		$this->assertSame( $pro_next, wp_next_scheduled( WPS_WPR_Points_Expiry::PRO_CRON_HOOK ) );
	}

	/**
	 * Settings saved earlier by Pro (enabled, past dates) do nothing until the
	 * free tab is saved. The first run after saving sends a reminder and moves
	 * the past date forward instead of expiring the balance.
	 */
	public function test_pro_settings_idle_until_free_saved_then_reminder_first() {
		// Pro's leftovers: enabled option, a past date, no free activation.
		update_option(
			WPS_WPR_Points_Expiry::OPTION,
			array(
				'wps_wpr_points_expiration_enable'     => 1,
				'wps_wpr_points_expiration_threshold'  => 10,
				'wps_wpr_points_expiration_time_num'   => 1,
				'wps_wpr_points_expiration_time_drop'  => array( 'months' ),
				'wps_wpr_enable_expire_notify_setting' => 0,
				'wps_wpr_points_expiration_email'      => 5,
				'wps_wpr_threshold_notif'              => 'Pro message',
			)
		);
		delete_option( WPS_WPR_Points_Expiry::ACTIVATED_OPTION );
		delete_option( WPS_WPR_Points_Expiry::GRACE_DONE_OPTION );
		$user_id = $this->make_user( 100 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2000-01-01' );
		$this->limit_run_to( array( $user_id ) );

		$this->assertTrue( WPS_WPR_Points_Expiry::wps_wpr_has_unreviewed_settings() );
		$this->assertFalse( WPS_WPR_Points_Expiry::wps_wpr_is_active() );
		WPS_WPR_Points_Expiry::wps_wpr_sync_cron_schedule();
		$this->assertFalse( wp_next_scheduled( WPS_WPR_Points_Expiry::CRON_HOOK ), 'No free cron before the admin saves.' );
		$this->assertSame( 0, array_sum( $this->expiry->wps_wpr_process_expiry() ) );
		$this->assertSame( 100, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$this->assertCount( 0, $this->mails );

		// Admin reviews and saves the free tab with expiry on.
		WPS_WPR_Points_Expiry::wps_wpr_update_activation();
		$this->assertFalse( WPS_WPR_Points_Expiry::wps_wpr_has_unreviewed_settings() );
		$this->assertTrue( WPS_WPR_Points_Expiry::wps_wpr_is_active() );

		$today  = date_i18n( 'Y-m-d' );
		$counts = $this->expiry->wps_wpr_process_expiry();
		$this->assertSame( 1, $counts['grace'] );
		$this->assertSame( 0, $counts['expired'] );
		$this->assertSame( 100, (int) get_user_meta( $user_id, 'wps_wpr_points', true ), 'Not wiped on the first run.' );
		$new_date = gmdate( 'Y-m-d', strtotime( $today . ' +5 days' ) );
		$this->assertSame( $new_date, get_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, true ) );
		$this->assertCount( 1, $this->mails, 'Reminder sent even with expiry emails off.' );
		$this->assertStringContainsString( '100', $this->mails[0]['message'] );

		// Next run: no second reminder, no expiry before the new date.
		$counts = $this->expiry->wps_wpr_process_expiry();
		$this->assertArrayNotHasKey( 'grace', $counts );
		$this->assertSame( 0, array_sum( $counts ) );
		$this->assertCount( 1, $this->mails );

		// On the new date the balance expires as usual.
		$settings = WPS_WPR_Points_Expiry::wps_wpr_get_settings();
		$this->assertSame( 'expired', $this->expiry->wps_wpr_process_user( $user_id, $settings, $new_date ) );
		$this->assertSame( 0, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
	}

	/**
	 * Saving with expiry off deactivates it; Pro taking over re-arms the grace pass.
	 */
	public function test_activation_flags() {
		$this->enable( array( 'wps_wpr_points_expiration_enable' => 0 ) );
		WPS_WPR_Points_Expiry::wps_wpr_update_activation();
		$this->assertFalse( WPS_WPR_Points_Expiry::wps_wpr_is_activated() );
		$this->assertFalse( get_option( WPS_WPR_Points_Expiry::GRACE_DONE_OPTION, false ) );

		$this->enable();
		add_filter( 'wps_wpr_pro_handles_points_expiry', '__return_true' );
		$this->expiry->wps_wpr_maybe_schedule_cron();
		$this->assertFalse( get_option( WPS_WPR_Points_Expiry::GRACE_DONE_OPTION, false ) );
		$this->assertTrue( WPS_WPR_Points_Expiry::wps_wpr_is_activated() );
	}

	/**
	 * A negative balance is skipped: no "expired" log, no reset to 0.
	 */
	public function test_negative_balance_skipped() {
		$settings = $this->enable();
		$user_id  = $this->make_user( -50 );
		update_user_meta( $user_id, WPS_WPR_Points_Expiry::DATE_META, '2026-11-01' );

		$this->assertSame( 'none', $this->expiry->wps_wpr_process_user( $user_id, $settings, '2026-11-25' ) );
		$this->assertSame( -50, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$this->assertSame( '', get_user_meta( $user_id, 'points_details', true ) );
		$this->assertCount( 0, $this->mails );
	}

	/**
	 * Placeholders are replaced.
	 */
	public function test_placeholders_replaced() {
		$user_id = $this->make_user( 10 );
		$text    = $this->expiry->wps_wpr_replace_placeholders( '[USERNAME] has [TOTALPOINT] points until [EXPIRYDATE] at [SITENAME]', $user_id, 1500, '2026-12-25' );
		$this->assertStringContainsString( number_format_i18n( 1500 ), $text );
		$this->assertStringContainsString( date_i18n( get_option( 'date_format' ), strtotime( '2026-12-25' ) ), $text );
		$this->assertStringContainsString( get_bloginfo( 'name' ), $text );
		$this->assertStringNotContainsString( '[', $text );
	}
}
