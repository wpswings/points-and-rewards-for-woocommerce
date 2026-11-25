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
	 * Earning-rate keys. The template's values are only written when the store
	 * has no earning rate yet or still has the default 1 = 1, so a rate the
	 * admin set is never replaced silently.
	 *
	 * @var array
	 */
	private static $rate_keys = array(
		'wps_wpr_coupon_conversion_points',
		'wps_wpr_coupon_conversion_price',
	);

	/**
	 * Notice toggles and the message key each one shows. When a template turns
	 * a notice on, its message is filled with the settings page default unless
	 * the store already has text saved.
	 *
	 * @var array option => array( toggle key => message key )
	 */
	private static $notice_messages = array(
		'wps_wpr_coupons_gallery' => array(
			'wps_wpr_per_currency_discount_notice' => 'wps_wpr_per_curr_earning_messages',
		),
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
	 * `wps_wpr_wizard_templates` filter. Template IDs are normalised with
	 * sanitize_key() (lowercase a-z, 0-9, "_" and "-"), so "Pro-VIP" becomes
	 * "pro-vip"; if two IDs normalise to the same key, the first one wins.
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

		// Same normalisation as the save path, so a pre-filled template is always found on save.
		$normalised = array();
		foreach ( $templates as $id => $template ) {
			$key = sanitize_key( (string) $id );
			if ( '' === $key || isset( $normalised[ $key ] ) || ! is_array( $template ) || empty( $template['title'] ) ) {
				continue;
			}
			$template['wizard']  = isset( $template['wizard'] ) && is_array( $template['wizard'] ) ? $template['wizard'] : array();
			$template['extra']   = isset( $template['extra'] ) && is_array( $template['extra'] ) ? $template['extra'] : array();
			$template['preview'] = ! empty( $template['preview'] ) && is_array( $template['preview'] ) ? $template['preview'] : self::wps_wpr_build_preview( $template, $currency );
			$normalised[ $key ]  = $template;
		}

		return $normalised;
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
			$amount     = ! empty( $coupon['wps_wpr_coupon_conversion_points'] ) ? $coupon['wps_wpr_coupon_conversion_points'] : 1;
			$points     = ! empty( $coupon['wps_wpr_coupon_conversion_price'] ) ? $coupon['wps_wpr_coupon_conversion_price'] : 1;
			$store_rate = self::wps_wpr_get_store_custom_rate();
			if ( null !== $store_rate ) {
				// The store's own rate is kept (see wps_wpr_apply_template_extras()).
				$amount = $store_rate['amount'];
				$points = $store_rate['points'];
				/* translators: 1: points, 2: currency symbol, 3: amount */
				$format = _n( 'Earn %1$s point per %2$s%3$s spent (your current rate is kept)', 'Earn %1$s points per %2$s%3$s spent (your current rate is kept)', (int) $points, 'points-and-rewards-for-woocommerce' );
			} else {
				/* translators: 1: points, 2: currency symbol, 3: amount */
				$format = _n( 'Earn %1$s point per %2$s%3$s spent', 'Earn %1$s points per %2$s%3$s spent', (int) $points, 'points-and-rewards-for-woocommerce' );
			}
			$preview[] = sprintf( $format, $points, $currency, $amount );
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
	 * The store's earning rate when the admin has set one other than the default.
	 *
	 * @return array|null array( 'amount' => currency amount, 'points' => points ), or null when unset or 1 = 1.
	 */
	public static function wps_wpr_get_store_custom_rate() {
		$coupon = get_option( 'wps_wpr_coupons_gallery', array() );
		$coupon = is_array( $coupon ) ? $coupon : array();
		$amount = isset( $coupon['wps_wpr_coupon_conversion_points'] ) ? (string) $coupon['wps_wpr_coupon_conversion_points'] : '';
		$points = isset( $coupon['wps_wpr_coupon_conversion_price'] ) ? (string) $coupon['wps_wpr_coupon_conversion_price'] : '';

		$is_default = function ( $value ) {
			return '' === $value || ( is_numeric( $value ) && 1.0 === (float) $value );
		};
		if ( $is_default( $amount ) && $is_default( $points ) ) {
			return null;
		}

		return array(
			'amount' => '' === $amount ? '1' : $amount,
			'points' => '' === $points ? '1' : $points,
		);
	}

	/**
	 * Default per-currency earning message, the same text the Coupon settings page uses.
	 *
	 * @return string
	 */
	public static function wps_wpr_default_earning_message() {
		return __( 'Place an order and earn reward points in return. Conversion rate: [CURRENCY] = [POINTS] point.', 'points-and-rewards-for-woocommerce' );
	}

	/**
	 * Apply a template's extra settings (the ones without a wizard step).
	 *
	 * Wizard field values are not touched here: they come from the submitted
	 * form so the admin's own changes win. An earning rate the store already
	 * set is kept, and a notice the template turns on always gets a message.
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
			$current   = get_option( $option, array() );
			$current   = is_array( $current ) ? $current : array();
			$keep_rate = 'wps_wpr_coupons_gallery' === $option && null !== self::wps_wpr_get_store_custom_rate();
			$messages  = isset( self::$notice_messages[ $option ] ) ? array_values( self::$notice_messages[ $option ] ) : array();
			foreach ( $values as $key => $value ) {
				$key = sanitize_key( $key );
				if ( $keep_rate && in_array( $key, self::$rate_keys, true ) ) {
					continue;
				}
				// Never replace message text the store already saved.
				if ( in_array( $key, $messages, true ) && isset( $current[ $key ] ) && '' !== trim( (string) $current[ $key ] ) ) {
					continue;
				}
				$current[ $key ] = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
			}
			if ( isset( self::$notice_messages[ $option ] ) ) {
				foreach ( self::$notice_messages[ $option ] as $toggle => $message ) {
					if ( ! empty( $current[ $toggle ] ) && ( ! isset( $current[ $message ] ) || '' === trim( (string) $current[ $message ] ) ) ) {
						$current[ $message ] = self::wps_wpr_default_earning_message();
					}
				}
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
