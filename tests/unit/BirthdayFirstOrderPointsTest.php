<?php
/**
 * Tests for birthday points and first-order points in the free plugin.
 *
 * @package points-and-rewards-for-wooCommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Birthday / first-order points tests.
 */
class BirthdayFirstOrderPointsTest extends TestCase {

	/**
	 * Saved option values, restored after each test.
	 *
	 * @var array
	 */
	private $backup = array();

	/**
	 * Users created by a test.
	 *
	 * @var int[]
	 */
	private $user_ids = array();

	/**
	 * Orders created by a test.
	 *
	 * @var int[]
	 */
	private $order_ids = array();

	/**
	 * Class under test.
	 *
	 * @var WPS_WPR_Birthday_First_Order_Points
	 */
	private $feature;

	protected function setUp(): void {
		foreach ( array( 'wps_wpr_settings_gallery', 'wps_wpr_notificatin_array' ) as $option ) {
			$this->backup[ $option ] = get_option( $option, null );
		}

		// Plugin on, both rules on, emails off.
		$settings = get_option( 'wps_wpr_settings_gallery', array() );
		$settings = is_array( $settings ) ? $settings : array();
		update_option(
			'wps_wpr_settings_gallery',
			array_merge(
				$settings,
				array(
					'wps_wpr_general_setting_enable'           => 1,
					'wps_wpr_general_setting_enablee'          => 1,
					'wps_wpr_general_first_value'              => 25,
					'wps_wpr_first_order_points_type'          => 'fixed',
					'wps_wpr_general_setting_birthday_enablee' => 1,
					'wps_wpr_general_birthday_value'           => 40,
				)
			)
		);
		update_option( 'wps_wpr_notificatin_array', array( 'wps_wpr_notification_setting_enable' => '' ) );

		$this->feature = new WPS_WPR_Birthday_First_Order_Points();
	}

	protected function tearDown(): void {
		foreach ( $this->order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$order->delete( true );
			}
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->user_ids as $user_id ) {
			wp_delete_user( $user_id );
		}
		foreach ( $this->backup as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
		remove_all_filters( 'wps_wpr_free_handles_birthday_first_order' );
		wp_set_current_user( 0 );
		$_POST    = array();
		$_REQUEST = array();
	}

	/**
	 * Create a customer.
	 *
	 * @return int
	 */
	private function make_user() {
		$user_id          = wp_insert_user(
			array(
				'user_login' => 'wpr_bfo_' . wp_generate_password( 8, false ),
				'user_email' => 'wpr_bfo_' . wp_generate_password( 8, false ) . '@example.test',
				'user_pass'  => wp_generate_password(),
				'role'       => 'customer',
			)
		);
		$this->user_ids[] = $user_id;
		return $user_id;
	}

	/**
	 * Create an order for a customer without firing status hooks.
	 *
	 * @param  int    $user_id user id.
	 * @param  float  $total   order total.
	 * @param  string $status  status.
	 * @return WC_Order
	 */
	private function make_order( $user_id, $total = 100, $status = 'pending' ) {
		$order = new WC_Order();
		$order->set_customer_id( $user_id );
		$order->set_total( $total );
		$order->set_status( $status );
		$order->save();
		$this->order_ids[] = $order->get_id();
		return $order;
	}

	public function test_free_handles_rules_when_pro_is_inactive() {
		$this->assertTrue( function_exists( 'wps_wpr_free_handles_birthday_first_order' ) );
		$this->assertTrue( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_handled_here() );

		add_filter( 'wps_wpr_free_handles_birthday_first_order', '__return_false' );
		$this->assertFalse( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_handled_here() );
	}

	public function test_general_settings_sections_are_added() {
		$ids = array_column( $this->feature->wps_wpr_add_general_settings( array() ), 'id' );
		foreach ( array( 'wps_wpr_general_setting_enablee', 'wps_wpr_general_first_value', 'wps_wpr_first_order_points_type', 'wps_wpr_general_setting_birthday_enablee', 'wps_wpr_general_birthday_value' ) as $id ) {
			$this->assertContains( $id, $ids );
		}

		add_filter( 'wps_wpr_free_handles_birthday_first_order', '__return_false' );
		$this->assertSame( array(), $this->feature->wps_wpr_add_general_settings( array() ), 'No settings while an older Pro handles the rules.' );
	}

	public function test_notification_sections_are_added() {
		$ids = array_column( $this->feature->wps_wpr_add_notification_settings( array() ), 'id' );
		foreach ( array( 'wps_wpr_point_on_first_order_point_setting_enable', 'wps_wpr_point_on_first_order_subject', 'wps_wpr_point_on_first_order_desc', 'wps_wpr_point_on_birthday_setting_enable', 'wps_wpr_point_on_bday_subject', 'wps_wpr_point_on_bday_order_desc' ) as $id ) {
			$this->assertContains( $id, $ids );
		}
	}

	public function test_save_sanitizes_values() {
		$_POST = array(
			'wps_wpr_general_setting_enablee'          => 'on',
			'wps_wpr_general_first_value'              => '-7',
			'wps_wpr_first_order_points_type'          => 'bogus',
			'wps_wpr_general_birthday_value'           => '15',
		);
		$saved = $this->feature->wps_wpr_save_general_settings( array( 'existing' => 'kept' ) );

		$this->assertSame( 'kept', $saved['existing'] );
		$this->assertSame( 1, $saved['wps_wpr_general_setting_enablee'] );
		$this->assertSame( 7, $saved['wps_wpr_general_first_value'] );
		$this->assertSame( 'fixed', $saved['wps_wpr_first_order_points_type'] );
		$this->assertSame( 0, $saved['wps_wpr_general_setting_birthday_enablee'] );
		$this->assertSame( 15, $saved['wps_wpr_general_birthday_value'] );

		$_POST['wps_wpr_first_order_points_type'] = 'percent';
		$this->assertSame( 'percent', $this->feature->wps_wpr_save_general_settings( array() )['wps_wpr_first_order_points_type'] );
	}

	public function test_first_order_points_awarded_once() {
		$user_id = $this->make_user();
		$order   = $this->make_order( $user_id );

		$this->assertSame( 25, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'processing', 'completed' ) );
		$this->assertSame( 25, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$this->assertSame( 25, (int) get_user_meta( $user_id, 'points_on_first_order', true ) );
		$this->assertSame( 25, (int) wc_get_order( $order->get_id() )->get_meta( 'wps_wpr_first_order_points_awarded' ) );

		$log = get_user_meta( $user_id, 'points_details', true );
		$this->assertCount( 1, $log['points_on_first_order'] );
		$this->assertSame( 25, $log['points_on_first_order'][0]['points_on_first_order'] );
		$this->assertSame( $order->get_id(), $log['points_on_first_order'][0]['order_id'] );

		// Same order again, and a second order: nothing more.
		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'processing', 'completed' ) );
		$second = $this->make_order( $user_id );
		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $second->get_id(), 'processing', 'completed' ) );
		$this->assertSame( 25, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
	}

	public function test_first_order_percentage() {
		$settings = get_option( 'wps_wpr_settings_gallery' );
		$settings['wps_wpr_first_order_points_type'] = 'percent';
		$settings['wps_wpr_general_first_value']     = 10;
		update_option( 'wps_wpr_settings_gallery', $settings );

		$user_id = $this->make_user();
		$order   = $this->make_order( $user_id, 255 );

		$this->assertSame( 26, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'processing', 'completed' ) );
	}

	public function test_no_first_order_points_when_customer_already_has_a_paid_order() {
		$user_id = $this->make_user();
		$this->make_order( $user_id, 50, 'completed' );
		$order = $this->make_order( $user_id );

		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'processing', 'completed' ) );
	}

	public function test_no_first_order_points_when_disabled_restricted_guest_or_not_completed() {
		$user_id = $this->make_user();
		$order   = $this->make_order( $user_id );

		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'pending', 'processing' ), 'Only on the award status.' );

		update_user_meta( $user_id, 'wps_wpr_restrict_user', 'yes' );
		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'processing', 'completed' ), 'Restricted user.' );
		delete_user_meta( $user_id, 'wps_wpr_restrict_user' );

		$guest = $this->make_order( 0 );
		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $guest->get_id(), 'processing', 'completed' ), 'Guest order.' );

		$settings = get_option( 'wps_wpr_settings_gallery' );
		$settings['wps_wpr_general_setting_enablee'] = 0;
		update_option( 'wps_wpr_settings_gallery', $settings );
		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'processing', 'completed' ), 'Disabled.' );

		add_filter( 'wps_wpr_free_handles_birthday_first_order', '__return_false' );
		$settings['wps_wpr_general_setting_enablee'] = 1;
		update_option( 'wps_wpr_settings_gallery', $settings );
		$this->assertSame( 0, $this->feature->wps_wpr_award_first_order_points( $order->get_id(), 'processing', 'completed' ), 'Older Pro handles it.' );
	}

	public function test_birthday_validation() {
		$this->assertTrue( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_valid_birthday( '1990-05-17' ) );
		$this->assertTrue( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_valid_birthday( '2000-02-29' ) );
		$this->assertFalse( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_valid_birthday( '1990-02-30' ) );
		$this->assertFalse( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_valid_birthday( '17/05/1990' ) );
		$this->assertFalse( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_valid_birthday( wp_date( 'Y-m-d', strtotime( '+1 day' ) ) ) );
		$this->assertFalse( WPS_WPR_Birthday_First_Order_Points::wps_wpr_is_valid_birthday( '1850-01-01' ) );
	}

	public function test_leap_day_birthdays_count_on_28_february_in_common_years() {
		$common = strtotime( '2027-02-28 12:00:00' );
		$leap   = strtotime( '2028-02-28 12:00:00' );
		$this->assertSame( array( '02-28', '02-29' ), WPS_WPR_Birthday_First_Order_Points::wps_wpr_birthday_month_days( $common ) );
		$this->assertSame( array( '02-28' ), WPS_WPR_Birthday_First_Order_Points::wps_wpr_birthday_month_days( $leap ) );
	}

	public function test_birthday_points_awarded_once_per_year() {
		$user_id = $this->make_user();
		$today   = strtotime( '2027-06-15 10:00:00' );
		update_user_meta( $user_id, '_my_bday', '1991-06-15' );

		$this->assertSame( 40, $this->feature->wps_wpr_award_birthday_points( $user_id, $today ) );
		$this->assertSame( 0, $this->feature->wps_wpr_award_birthday_points( $user_id, $today ), 'Not twice in one year.' );
		$this->assertSame( 40, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$this->assertSame( '2027', get_user_meta( $user_id, 'wps_wpr_birthday_points_get', true ) );
		$this->assertSame( array( '2027' ), get_user_meta( $user_id, 'wps_wpr_birthday_points_year', true ) );

		$log = get_user_meta( $user_id, 'points_details', true );
		$this->assertSame( 40, $log['points_on_birthday'][0]['points_on_birthday'] );

		$this->assertSame( 40, $this->feature->wps_wpr_award_birthday_points( $user_id, strtotime( '2028-06-15 10:00:00' ) ), 'Next year again.' );
		$this->assertSame( 0, $this->feature->wps_wpr_award_birthday_points( $user_id, strtotime( '2029-06-16 10:00:00' ) ), 'Not on another day.' );
	}

	public function test_process_birthdays_only_awards_matching_users() {
		$today    = strtotime( '2027-09-03 10:00:00' );
		$birthday = $this->make_user();
		$other    = $this->make_user();
		$none     = $this->make_user();
		update_user_meta( $birthday, '_my_bday', '1985-09-03' );
		update_user_meta( $other, '_my_bday', '1985-09-04' );

		$this->assertSame( 1, $this->feature->wps_wpr_process_birthdays( $today ) );
		$this->assertSame( 40, (int) get_user_meta( $birthday, 'wps_wpr_points', true ) );
		$this->assertSame( 0, (int) get_user_meta( $other, 'wps_wpr_points', true ) );
		$this->assertSame( 0, (int) get_user_meta( $none, 'wps_wpr_points', true ) );
		$this->assertSame( 0, $this->feature->wps_wpr_process_birthdays( $today ), 'Repeat runs award nothing.' );
	}

	public function test_birthday_disabled_awards_nothing() {
		$settings = get_option( 'wps_wpr_settings_gallery' );
		$settings['wps_wpr_general_setting_birthday_enablee'] = 0;
		update_option( 'wps_wpr_settings_gallery', $settings );

		$user_id = $this->make_user();
		update_user_meta( $user_id, '_my_bday', '1991-06-15' );
		$this->assertSame( 0, $this->feature->wps_wpr_award_birthday_points( $user_id, strtotime( '2027-06-15 10:00:00' ) ) );
	}

	public function test_account_birthday_saved_once_with_valid_nonce() {
		$user_id = $this->make_user();
		wp_set_current_user( $user_id );

		$_POST['account_bday'] = '1993-11-02';
		$this->assertFalse( $this->feature->wps_wpr_save_account_birthday( $user_id ), 'Needs a nonce.' );

		$_REQUEST['save-account-details-nonce'] = wp_create_nonce( 'save_account_details' );
		$this->assertTrue( $this->feature->wps_wpr_save_account_birthday( $user_id ) );
		$this->assertSame( '1993-11-02', get_user_meta( $user_id, '_my_bday', true ) );

		$_POST['account_bday'] = '1993-12-25';
		$this->assertFalse( $this->feature->wps_wpr_save_account_birthday( $user_id ), 'Cannot be changed once saved.' );
		$this->assertSame( '1993-11-02', get_user_meta( $user_id, '_my_bday', true ) );

		$other = $this->make_user();
		$this->assertFalse( $this->feature->wps_wpr_save_account_birthday( $other ), 'Only for the current user.' );
	}

	public function test_account_birthday_rejects_invalid_dates() {
		$user_id = $this->make_user();
		wp_set_current_user( $user_id );
		$_REQUEST['save-account-details-nonce'] = wp_create_nonce( 'save_account_details' );

		$_POST['account_bday'] = wp_date( 'Y-m-d', strtotime( '+3 days' ) );
		$this->assertFalse( $this->feature->wps_wpr_save_account_birthday( $user_id ) );
		$_POST['account_bday'] = 'not-a-date';
		$this->assertFalse( $this->feature->wps_wpr_save_account_birthday( $user_id ) );
		$this->assertSame( '', get_user_meta( $user_id, '_my_bday', true ) );
	}

	public function test_log_renderers_output_entries() {
		$log = array(
			'points_on_first_order' => array(
				array(
					'points_on_first_order' => 25,
					'date'                  => '2027-01-01 10:00:00am',
				),
			),
			'points_on_birthday'    => array(
				array(
					'points_on_birthday' => 40,
					'date'               => '2027-06-15 10:00:00am',
				),
			),
		);

		ob_start();
		$this->feature->wps_wpr_public_points_log( $log );
		$public = ob_get_clean();
		$this->assertStringContainsString( '+25', $public );
		$this->assertStringContainsString( '+40', $public );

		ob_start();
		$this->feature->wps_wpr_admin_points_log( $log );
		$admin = ob_get_clean();
		$this->assertStringContainsString( 'Points Earned on Birthday', $admin );
		$this->assertStringContainsString( '+25', $admin );
	}

	public function test_cron_is_scheduled_only_when_birthday_points_enabled() {
		wp_clear_scheduled_hook( WPS_WPR_Birthday_First_Order_Points::CRON_HOOK );
		$this->feature->wps_wpr_maybe_schedule_cron();
		$this->assertNotFalse( wp_next_scheduled( WPS_WPR_Birthday_First_Order_Points::CRON_HOOK ) );

		$settings = get_option( 'wps_wpr_settings_gallery' );
		$settings['wps_wpr_general_setting_birthday_enablee'] = 0;
		update_option( 'wps_wpr_settings_gallery', $settings );
		$this->feature->wps_wpr_maybe_schedule_cron();
		$this->assertFalse( wp_next_scheduled( WPS_WPR_Birthday_First_Order_Points::CRON_HOOK ) );
	}
}
