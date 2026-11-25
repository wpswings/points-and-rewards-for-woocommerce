<?php
/**
 * Tests for the setup wizard program templates (WPS-8316).
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Wizard templates tests.
 */
class WizardTemplatesTest extends TestCase {

	/**
	 * Options the tests change, restored afterwards.
	 *
	 * @var array
	 */
	private $options = array(
		'wps_wpr_settings_gallery',
		'wps_wpr_coupons_gallery',
		'wps_wpr_other_settings',
		'wps_wpr_wizard_template',
	);

	/**
	 * Saved option values.
	 *
	 * @var array
	 */
	private $backup = array();

	/**
	 * Back up options.
	 */
	protected function setUp(): void {
		foreach ( $this->options as $option ) {
			$this->backup[ $option ] = get_option( $option, '__missing__' );
		}
	}

	/**
	 * Restore options and filters.
	 */
	protected function tearDown(): void {
		remove_all_filters( 'wps_wpr_wizard_templates' );
		foreach ( $this->backup as $option => $value ) {
			if ( '__missing__' === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
	}

	/**
	 * Call the wizard's private save routine.
	 *
	 * @param array $data wizard data.
	 * @return bool
	 */
	private function process_wizard_data( $data ) {
		$wizard = new WPS_WPR_Setup_Wizard( 'points-and-rewards-for-woocommerce', '2.10.6' );
		$method = new ReflectionMethod( $wizard, 'process_wizard_data' );
		$method->setAccessible( true );
		return $method->invoke( $wizard, $data );
	}

	public function test_default_templates_are_complete() {
		$templates = WPS_WPR_Wizard_Templates::wps_wpr_get_templates();

		$this->assertSame( array( 'starter', 'referral', 'vip', 'engagement' ), array_keys( $templates ) );
		foreach ( $templates as $template ) {
			$this->assertNotEmpty( $template['title'] );
			$this->assertNotEmpty( $template['description'] );
			$this->assertNotEmpty( $template['preview'] );
			foreach ( array( 'step0', 'step1', 'step2', 'step3', 'step4' ) as $step ) {
				$this->assertArrayHasKey( $step, $template['wizard'] );
			}
		}
	}

	public function test_preview_describes_template_values() {
		$starter = WPS_WPR_Wizard_Templates::wps_wpr_get_template( 'starter' );
		$preview = implode( ' | ', $starter['preview'] );

		$this->assertStringContainsString( 'Earn 1 point(s) per', $preview );
		$this->assertStringContainsString( 'Redeem 100 points', $preview );
		$this->assertStringContainsString( '50 sign-up points', $preview );
		$this->assertStringContainsString( 'Referral program off', $preview );

		$vip = WPS_WPR_Wizard_Templates::wps_wpr_get_template( 'vip' );
		$this->assertStringContainsString( 'Earn 2 point(s) per', implode( ' | ', $vip['preview'] ) );
	}

	public function test_filter_can_add_templates_and_invalid_ones_are_dropped() {
		add_filter(
			'wps_wpr_wizard_templates',
			function ( $templates ) {
				$templates['pro_tiers'] = array(
					'title'  => 'Pro Tiers',
					'wizard' => array( 'step4' => array( 'signup_enable' => '1', 'signup_points' => '10' ) ),
				);
				$templates['broken']    = array( 'description' => 'no title' );
				$templates['also_bad']  = 'string';
				return $templates;
			}
		);

		$templates = WPS_WPR_Wizard_Templates::wps_wpr_get_templates();

		$this->assertArrayHasKey( 'pro_tiers', $templates );
		$this->assertSame( array(), $templates['pro_tiers']['extra'] );
		$this->assertNotEmpty( $templates['pro_tiers']['preview'] );
		$this->assertArrayNotHasKey( 'broken', $templates );
		$this->assertArrayNotHasKey( 'also_bad', $templates );
	}

	public function test_apply_extras_writes_settings_and_keeps_other_keys() {
		update_option(
			'wps_wpr_coupons_gallery',
			array(
				'wps_wpr_coupon_conversion_enable' => '',
				'wps_wpr_per_curr_earning_messages' => 'keep me',
			)
		);

		$this->assertTrue( WPS_WPR_Wizard_Templates::wps_wpr_apply_template_extras( 'vip' ) );

		$coupon = get_option( 'wps_wpr_coupons_gallery' );
		$this->assertSame( '1', $coupon['wps_wpr_coupon_conversion_enable'] );
		$this->assertSame( '1', $coupon['wps_wpr_coupon_conversion_points'] );
		$this->assertSame( '2', $coupon['wps_wpr_coupon_conversion_price'] );
		$this->assertSame( 'keep me', $coupon['wps_wpr_per_curr_earning_messages'] );
		$this->assertSame( 'vip', get_option( 'wps_wpr_wizard_template' ) );
	}

	public function test_unknown_template_changes_nothing() {
		update_option( 'wps_wpr_coupons_gallery', array( 'wps_wpr_coupon_conversion_price' => '7' ) );
		delete_option( 'wps_wpr_wizard_template' );

		$this->assertFalse( WPS_WPR_Wizard_Templates::wps_wpr_apply_template_extras( 'does-not-exist' ) );
		$this->assertSame( array( 'wps_wpr_coupon_conversion_price' => '7' ), get_option( 'wps_wpr_coupons_gallery' ) );
		$this->assertFalse( get_option( 'wps_wpr_wizard_template' ) );
	}

	public function test_extras_cannot_write_unlisted_options() {
		add_filter(
			'wps_wpr_wizard_templates',
			function ( $templates ) {
				$templates['evil'] = array(
					'title' => 'Evil',
					'extra' => array(
						'users_can_register'       => array( 'x' => 1 ),
						'wps_wpr_settings_gallery' => array( 'wps_wpr_general_signup_value' => '<b>5</b>' ),
					),
				);
				return $templates;
			}
		);
		$before = get_option( 'users_can_register' );

		WPS_WPR_Wizard_Templates::wps_wpr_apply_template_extras( 'evil' );

		$this->assertSame( $before, get_option( 'users_can_register' ) );
		$general = get_option( 'wps_wpr_settings_gallery' );
		$this->assertSame( '5', $general['wps_wpr_general_signup_value'] );
	}

	public function test_wizard_save_keeps_admin_edits_and_applies_template_extras() {
		update_option( 'wps_wpr_coupons_gallery', array() );

		// The admin picked "referral" and then changed the referral points to 300.
		$template                              = WPS_WPR_Wizard_Templates::wps_wpr_get_template( 'referral' );
		$data                                  = $template['wizard'];
		$data['step0']['program_template']     = 'referral';
		$data['step2']['referee_points']       = '300';

		$this->assertTrue( $this->process_wizard_data( $data ) );

		$general = get_option( 'wps_wpr_settings_gallery' );
		$this->assertSame( 1, $general['wps_wpr_general_refer_enable'] );
		$this->assertSame( 300, $general['wps_wpr_general_refer_value'] );
		$this->assertSame( 100, $general['wps_wpr_general_signup_value'] );
		$this->assertSame( 100, $general['wps_wpr_cart_points_rate'] );

		$other = get_option( 'wps_wpr_other_settings' );
		$this->assertSame( 'temp_two', $other['wps_wpr_choose_account_page_temp'] );

		$coupon = get_option( 'wps_wpr_coupons_gallery' );
		$this->assertSame( '1', $coupon['wps_wpr_coupon_conversion_enable'] );
		$this->assertSame( 'referral', get_option( 'wps_wpr_wizard_template' ) );
	}

	public function test_wizard_save_without_template_does_not_touch_extras() {
		update_option( 'wps_wpr_coupons_gallery', array( 'wps_wpr_coupon_conversion_price' => '9' ) );
		delete_option( 'wps_wpr_wizard_template' );

		$this->process_wizard_data(
			array(
				'step0' => array(
					'plugin_enable'    => '1',
					'program_template' => '',
				),
			)
		);

		$this->assertSame( array( 'wps_wpr_coupon_conversion_price' => '9' ), get_option( 'wps_wpr_coupons_gallery' ) );
		$this->assertFalse( get_option( 'wps_wpr_wizard_template' ) );
	}
}
