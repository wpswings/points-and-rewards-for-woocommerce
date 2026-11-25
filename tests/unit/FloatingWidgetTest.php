<?php
/**
 * Tests for the floating loyalty widget.
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Floating widget tests.
 */
class FloatingWidgetTest extends TestCase {

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

	protected function setUp(): void {
		foreach ( array( WPS_WPR_Floating_Widget::OPTION, 'wps_wpr_settings_gallery', 'wps_wpr_coupons_gallery', 'wps_wpr_membership_settings' ) as $option ) {
			$this->backup[ $option ] = get_option( $option, null );
		}
		wp_set_current_user( 0 );
	}

	protected function tearDown(): void {
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
		remove_all_filters( 'wps_wpr_floating_widget_sections' );
	}

	private function make_user( $points = 0 ) {
		$user_id       = wp_insert_user(
			array(
				'user_login' => 'fwtest_' . wp_generate_password( 8, false ),
				'user_pass'  => wp_generate_password(),
				'user_email' => 'fwtest_' . wp_generate_password( 8, false ) . '@example.test',
				'role'       => 'customer',
			)
		);
		$this->users[] = $user_id;
		update_user_meta( $user_id, 'wps_wpr_points', $points );
		return $user_id;
	}

	private function enable( $overrides = array() ) {
		update_option( WPS_WPR_Floating_Widget::OPTION, WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings( array_merge( array( 'wps_wpr_fw_enable' => 'yes', 'wps_wpr_fw_show_mobile' => 'yes' ), $overrides ) ) );
	}

	public function test_defaults_are_disabled() {
		delete_option( WPS_WPR_Floating_Widget::OPTION );
		$settings = WPS_WPR_Floating_Widget::wps_wpr_get_settings();
		$this->assertSame( 'no', $settings['wps_wpr_fw_enable'] );
		$this->assertSame( 'bottom-right', $settings['wps_wpr_fw_position'] );
		$this->assertSame( 'all', $settings['wps_wpr_fw_show_to'] );
		$widget = new WPS_WPR_Floating_Widget();
		$this->assertFalse( $widget->wps_wpr_should_display() );
	}

	public function test_sanitize_rejects_bad_values() {
		$clean = WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings(
			array(
				'wps_wpr_fw_enable'    => 'yes',
				'wps_wpr_fw_position'  => 'top-middle',
				'wps_wpr_fw_color'     => 'red;}body{display:none',
				'wps_wpr_fw_label'     => '<script>alert(1)</script>Points',
				'wps_wpr_fw_show_to'   => 'everyone',
				'wps_wpr_fw_hide_cart' => 'maybe',
				'wps_wpr_fw_offset'    => '9999',
			)
		);
		$this->assertSame( 'yes', $clean['wps_wpr_fw_enable'] );
		$this->assertSame( 'bottom-right', $clean['wps_wpr_fw_position'] );
		$this->assertSame( '#6c3fc5', $clean['wps_wpr_fw_color'] );
		$this->assertStringNotContainsString( '<script>', $clean['wps_wpr_fw_label'] );
		$this->assertSame( 'all', $clean['wps_wpr_fw_show_to'] );
		$this->assertSame( 'no', $clean['wps_wpr_fw_hide_cart'] );
		$this->assertSame( 400, $clean['wps_wpr_fw_offset'] );
		$this->assertSame( 'no', $clean['wps_wpr_fw_show_mobile'] );
	}

	public function test_sanitize_keeps_valid_values() {
		$clean = WPS_WPR_Floating_Widget::wps_wpr_sanitize_settings(
			array(
				'wps_wpr_fw_position' => 'bottom-left',
				'wps_wpr_fw_color'    => '#112233',
				'wps_wpr_fw_show_to'  => 'logged_in',
				'wps_wpr_fw_offset'   => '80',
			)
		);
		$this->assertSame( 'bottom-left', $clean['wps_wpr_fw_position'] );
		$this->assertSame( '#112233', $clean['wps_wpr_fw_color'] );
		$this->assertSame( 'logged_in', $clean['wps_wpr_fw_show_to'] );
		$this->assertSame( 80, $clean['wps_wpr_fw_offset'] );
	}

	public function test_display_rules() {
		$widget = new WPS_WPR_Floating_Widget();
		$this->enable();
		$this->assertTrue( $widget->wps_wpr_should_display(), 'enabled widget shows to guests' );

		$this->enable( array( 'wps_wpr_fw_show_to' => 'logged_in' ) );
		$this->assertFalse( $widget->wps_wpr_should_display(), 'logged-in only hides from guests' );
		wp_set_current_user( $this->make_user() );
		$this->assertTrue( $widget->wps_wpr_should_display(), 'logged-in only shows to customers' );

		update_user_meta( get_current_user_id(), 'wps_wpr_restrict_user', 'yes' );
		$this->assertFalse( $widget->wps_wpr_should_display(), 'restricted users never see it' );
	}

	public function test_logged_in_markup() {
		update_option(
			'wps_wpr_settings_gallery',
			array_merge(
				(array) $this->backup['wps_wpr_settings_gallery'],
				array(
					'wps_wpr_general_refer_enable' => 1,
					'wps_wpr_general_refer_value'  => 25,
				)
			)
		);
		update_option( 'wps_wpr_membership_settings', array( 'membership_roles' => array( 'Gold' => array() ) ) );
		$this->enable( array( 'wps_wpr_fw_position' => 'bottom-left', 'wps_wpr_fw_color' => '#123456' ) );

		$user_id = $this->make_user( 1234 );
		update_user_meta( $user_id, 'membership_level', 'Gold' );
		$widget = new WPS_WPR_Floating_Widget();
		$html   = $widget->wps_wpr_get_markup( $user_id );

		$this->assertStringContainsString( 'wps-wpr-fw--bottom-left', $html );
		$this->assertStringContainsString( '--wps-wpr-fw-color:#123456', $html );
		$this->assertStringContainsString( 'aria-expanded="false"', $html );
		$this->assertStringContainsString( number_format_i18n( 1234 ), $html );
		$this->assertStringContainsString( 'Level: Gold', $html );
		$this->assertStringContainsString( '?pkey=', $html );
		$this->assertStringContainsString( 'Refer a friend: 25 points', $html );
		$this->assertStringNotContainsString( 'Sign up / Log in', $html );
		$this->assertNotEmpty( get_user_meta( $user_id, 'wps_points_referral', true ) );
	}

	public function test_guest_markup() {
		update_option(
			'wps_wpr_settings_gallery',
			array_merge(
				(array) $this->backup['wps_wpr_settings_gallery'],
				array(
					'wps_wpr_general_signup'       => 1,
					'wps_wpr_general_signup_value' => 150,
				)
			)
		);
		$this->enable( array( 'wps_wpr_fw_show_mobile' => 'no' ) );
		$widget = new WPS_WPR_Floating_Widget();
		$html   = $widget->wps_wpr_get_markup( 0 );

		$this->assertStringContainsString( 'Join and earn 150 points', $html );
		$this->assertStringContainsString( 'Sign up / Log in', $html );
		$this->assertStringContainsString( 'wps-wpr-fw--hide-mobile', $html );
		$this->assertStringNotContainsString( 'pkey=', $html );
		$this->assertStringNotContainsString( 'data-wps-wpr-fw-balance', $html );
	}

	public function test_sections_filter() {
		$this->enable();
		add_filter(
			'wps_wpr_floating_widget_sections',
			static function ( $sections ) {
				$sections['custom'] = '<p class="custom-section">Custom</p>';
				unset( $sections['earn'] );
				return $sections;
			}
		);
		$widget = new WPS_WPR_Floating_Widget();
		$html   = $widget->wps_wpr_get_markup( 0 );
		$this->assertStringContainsString( 'wps-wpr-fw__section--custom', $html );
		$this->assertStringNotContainsString( 'wps-wpr-fw__section--earn', $html );
	}

	public function test_hooks_registered_when_program_enabled() {
		$general = get_option( 'wps_wpr_settings_gallery', array() );
		if ( empty( $general['wps_wpr_general_setting_enable'] ) ) {
			$this->markTestSkipped( 'Points program disabled on this site; public hooks are not registered.' );
		}
		$this->assertTrue( (bool) has_action( 'wp_footer' ) );
		global $wp_filter;
		$found = false;
		foreach ( $wp_filter['wp_footer']->callbacks as $callbacks ) {
			foreach ( $callbacks as $cb ) {
				if ( is_array( $cb['function'] ) && $cb['function'][0] instanceof WPS_WPR_Floating_Widget ) {
					$found = true;
				}
			}
		}
		$this->assertTrue( $found );
	}
}
