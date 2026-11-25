<?php
/**
 * Ready-made loyalty program templates for the setup wizard.
 *
 * A template pre-fills the wizard fields (which the admin can still change in
 * the later steps) and, on save, applies extra settings that the wizard has no
 * step for, such as per-currency earning.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wizard program templates.
 */
class WPS_WPR_Wizard_Templates {

	/**
	 * Option that remembers the last template applied.
	 */
	const APPLIED_OPTION = 'wps_wpr_wizard_template';

	/**
	 * Settings options a template's "extra" block may write to.
	 *
	 * @var array
	 */
	private static $allowed_options = array(
		'wps_wpr_settings_gallery',
		'wps_wpr_coupons_gallery',
		'wps_wpr_other_settings',
	);

	/**
	 * Get all templates.
	 *
	 * Each template:
	 * - title, description: shown on the card.
	 * - wizard: values keyed by wizard step and field name (step0..step4). They
	 *   pre-fill the form; the admin can edit them before finishing.
	 * - extra: option name => array( setting key => value ), applied on save.
	 * - preview: human-readable list of what the template sets.
	 *
	 * Pro (or a site) can add or change templates with the
	 * `wps_wpr_wizard_templates` filter.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_templates() {
		$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ) : '$';

		$templates = array(
			'starter'    => array(
				'title'       => __( 'Starter Loyalty', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'A simple earn-and-redeem program. Good for stores launching their first loyalty program.', 'points-and-rewards-for-woocommerce' ),
				'wizard'      => array(
					'step0' => array( 'plugin_enable' => '1' ),
					'step1' => array(
						'redemption_cart_enable'     => '1',
						'redemption_checkout_enable' => '1',
						'redemption_points'          => '100',
						'redemption_value'           => '1',
					),
					'step2' => array(
						'referral_enable' => '0',
						'referee_points'  => '0',
					),
					'step3' => array(
						'point_tab_template'     => 'temp_one',
						'show_points_on_product' => '1',
					),
					'step4' => array(
						'signup_enable' => '1',
						'signup_points' => '50',
					),
				),
				'extra'       => array(
					'wps_wpr_coupons_gallery' => array(
						'wps_wpr_coupon_conversion_enable' => 1,
						'wps_wpr_coupon_conversion_points' => 1,
						'wps_wpr_coupon_conversion_price'  => 1,
					),
				),
			),
			'referral'   => array(
				'title'       => __( 'Referral Growth', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Big rewards for bringing in friends, plus a welcome bonus for new sign-ups.', 'points-and-rewards-for-woocommerce' ),
				'wizard'      => array(
					'step0' => array( 'plugin_enable' => '1' ),
					'step1' => array(
						'redemption_cart_enable'     => '1',
						'redemption_checkout_enable' => '1',
						'redemption_points'          => '100',
						'redemption_value'           => '1',
					),
					'step2' => array(
						'referral_enable' => '1',
						'referee_points'  => '200',
					),
					'step3' => array(
						'point_tab_template'     => 'temp_two',
						'show_points_on_product' => '1',
					),
					'step4' => array(
						'signup_enable' => '1',
						'signup_points' => '100',
					),
				),
				'extra'       => array(
					'wps_wpr_coupons_gallery' => array(
						'wps_wpr_coupon_conversion_enable' => 1,
						'wps_wpr_coupon_conversion_points' => 1,
						'wps_wpr_coupon_conversion_price'  => 1,
					),
				),
			),
			'vip'        => array(
				'title'       => __( 'Big Spender / VIP', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Double points on every purchase and richer redemption to reward high-value customers.', 'points-and-rewards-for-woocommerce' ),
				'wizard'      => array(
					'step0' => array( 'plugin_enable' => '1' ),
					'step1' => array(
						'redemption_cart_enable'     => '1',
						'redemption_checkout_enable' => '1',
						'redemption_points'          => '50',
						'redemption_value'           => '1',
					),
					'step2' => array(
						'referral_enable' => '1',
						'referee_points'  => '100',
					),
					'step3' => array(
						'point_tab_template'     => 'temp_four',
						'show_points_on_product' => '1',
					),
					'step4' => array(
						'signup_enable' => '0',
						'signup_points' => '0',
					),
				),
				'extra'       => array(
					'wps_wpr_coupons_gallery' => array(
						'wps_wpr_coupon_conversion_enable' => 1,
						'wps_wpr_coupon_conversion_points' => 1,
						'wps_wpr_coupon_conversion_price'  => 2,
					),
				),
			),
			'engagement' => array(
				'title'       => __( 'Engagement Booster', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Rewards sign-ups, referrals and purchases, with points shown everywhere to keep customers coming back.', 'points-and-rewards-for-woocommerce' ),
				'wizard'      => array(
					'step0' => array( 'plugin_enable' => '1' ),
					'step1' => array(
						'redemption_cart_enable'     => '1',
						'redemption_checkout_enable' => '1',
						'redemption_points'          => '100',
						'redemption_value'           => '1',
					),
					'step2' => array(
						'referral_enable' => '1',
						'referee_points'  => '150',
					),
					'step3' => array(
						'point_tab_template'     => 'temp_three',
						'show_points_on_product' => '1',
					),
					'step4' => array(
						'signup_enable' => '1',
						'signup_points' => '100',
					),
				),
				'extra'       => array(
					'wps_wpr_coupons_gallery' => array(
						'wps_wpr_coupon_conversion_enable'     => 1,
						'wps_wpr_coupon_conversion_points'     => 1,
						'wps_wpr_coupon_conversion_price'      => 1,
						'wps_wpr_per_currency_discount_notice' => 1,
					),
				),
			),
		);

		$templates = apply_filters( 'wps_wpr_wizard_templates', $templates );
		$templates = is_array( $templates ) ? $templates : array();

		foreach ( $templates as $id => $template ) {
			if ( ! is_array( $template ) || empty( $template['title'] ) ) {
				unset( $templates[ $id ] );
				continue;
			}
			$templates[ $id ]['wizard']  = isset( $template['wizard'] ) && is_array( $template['wizard'] ) ? $template['wizard'] : array();
			$templates[ $id ]['extra']   = isset( $template['extra'] ) && is_array( $template['extra'] ) ? $template['extra'] : array();
			$templates[ $id ]['preview'] = ! empty( $template['preview'] ) && is_array( $template['preview'] ) ? $template['preview'] : self::wps_wpr_build_preview( $templates[ $id ], $currency );
		}

		return $templates;
	}

	/**
	 * Get one template.
	 *
	 * @param string $id template id.
	 * @return array|null
	 */
	public static function wps_wpr_get_template( $id ) {
		$id        = sanitize_key( (string) $id );
		$templates = self::wps_wpr_get_templates();
		return ( '' !== $id && isset( $templates[ $id ] ) ) ? $templates[ $id ] : null;
	}

	/**
	 * Build the preview lines for a template from its values.
	 *
	 * @param array  $template template.
	 * @param string $currency currency symbol.
	 * @return array
	 */
	public static function wps_wpr_build_preview( $template, $currency = '$' ) {
		$wizard  = $template['wizard'];
		$extra   = $template['extra'];
		$preview = array();

		$coupon = isset( $extra['wps_wpr_coupons_gallery'] ) ? $extra['wps_wpr_coupons_gallery'] : array();
		if ( ! empty( $coupon['wps_wpr_coupon_conversion_enable'] ) ) {
			$amount    = ! empty( $coupon['wps_wpr_coupon_conversion_points'] ) ? $coupon['wps_wpr_coupon_conversion_points'] : 1;
			$points    = ! empty( $coupon['wps_wpr_coupon_conversion_price'] ) ? $coupon['wps_wpr_coupon_conversion_price'] : 1;
			$preview[] = sprintf(
				/* translators: 1: points, 2: currency symbol, 3: amount */
				__( 'Earn %1$s point(s) per %2$s%3$s spent', 'points-and-rewards-for-woocommerce' ),
				$points,
				$currency,
				$amount
			);
		}

		$step1 = isset( $wizard['step1'] ) ? $wizard['step1'] : array();
		if ( ( isset( $step1['redemption_cart_enable'] ) && '1' === (string) $step1['redemption_cart_enable'] ) || ( isset( $step1['redemption_checkout_enable'] ) && '1' === (string) $step1['redemption_checkout_enable'] ) ) {
			$preview[] = sprintf(
				/* translators: 1: points, 2: currency symbol, 3: amount */
				__( 'Redeem %1$s points = %2$s%3$s off', 'points-and-rewards-for-woocommerce' ),
				isset( $step1['redemption_points'] ) ? $step1['redemption_points'] : 100,
				$currency,
				isset( $step1['redemption_value'] ) ? $step1['redemption_value'] : 1
			);
		}

		if ( ! empty( $wizard['step4']['signup_enable'] ) && '1' === (string) $wizard['step4']['signup_enable'] ) {
			/* translators: %s: points */
			$preview[] = sprintf( __( '%s sign-up points', 'points-and-rewards-for-woocommerce' ), $wizard['step4']['signup_points'] );
		} else {
			$preview[] = __( 'No sign-up points', 'points-and-rewards-for-woocommerce' );
		}

		if ( ! empty( $wizard['step2']['referral_enable'] ) && '1' === (string) $wizard['step2']['referral_enable'] ) {
			/* translators: %s: points */
			$preview[] = sprintf( __( '%s points per referral', 'points-and-rewards-for-woocommerce' ), $wizard['step2']['referee_points'] );
		} else {
			$preview[] = __( 'Referral program off', 'points-and-rewards-for-woocommerce' );
		}

		return $preview;
	}

	/**
	 * Apply a template's extra settings (the ones without a wizard step).
	 *
	 * Wizard field values are not touched here: they come from the submitted
	 * form so the admin's own changes win.
	 *
	 * @param string $id template id.
	 * @return bool True when the template exists and was applied.
	 */
	public static function wps_wpr_apply_template_extras( $id ) {
		$template = self::wps_wpr_get_template( $id );
		if ( null === $template ) {
			return false;
		}

		foreach ( $template['extra'] as $option => $values ) {
			if ( ! in_array( $option, self::$allowed_options, true ) || ! is_array( $values ) ) {
				continue;
			}
			$current = get_option( $option, array() );
			$current = is_array( $current ) ? $current : array();
			foreach ( $values as $key => $value ) {
				$current[ sanitize_key( $key ) ] = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
			}
			update_option( $option, $current );
		}

		update_option( self::APPLIED_OPTION, sanitize_key( $id ) );

		/**
		 * Fires after a wizard template's settings are applied.
		 *
		 * @param string $id       template id.
		 * @param array  $template template definition.
		 */
		do_action( 'wps_wpr_wizard_template_applied', sanitize_key( $id ), $template );

		return true;
	}
}
