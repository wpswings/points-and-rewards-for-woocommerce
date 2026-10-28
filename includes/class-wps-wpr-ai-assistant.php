<?php
/**
 * AI settings assistant.
 *
 * Chat panel in the plugin settings that answers merchant questions and can
 * propose settings changes. It uses the WordPress AI Client (configured in
 * Settings > Connectors) and exposes the plugin settings to the model through
 * the Abilities API. Changes proposed by the model are only saved after the
 * admin confirms them.
 *
 * @link       https://wpswings.com/
 * @since      2.10.5
 *
 * @package    points-and-rewards-for-woocommerce
 * @subpackage points-and-rewards-for-woocommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WordPress\AiClient\Messages\DTO\Message;
use WordPress\AiClient\Messages\DTO\MessagePart;
use WordPress\AiClient\Messages\DTO\ModelMessage;
use WordPress\AiClient\Messages\DTO\UserMessage;

/**
 * AI settings assistant class.
 */
class WPS_WPR_AI_Assistant {

	/**
	 * Ability category slug.
	 */
	const CATEGORY = 'points-and-rewards';

	/**
	 * Ability to read settings.
	 */
	const ABILITY_GET = 'wps-wpr/get-settings';

	/**
	 * Ability to propose a settings change.
	 */
	const ABILITY_PROPOSE = 'wps-wpr/propose-settings-change';

	/**
	 * Ajax nonce action.
	 */
	const NONCE = 'wps-wpr-ai-assistant';

	/**
	 * Max model/tool round trips per chat message.
	 */
	const MAX_STEPS = 5;

	/**
	 * Max previous chat turns sent back to the model.
	 */
	const MAX_HISTORY = 12;

	/**
	 * Option that stores the assistant settings.
	 */
	const OPTION = 'wps_wpr_ai_assistant_settings';

	/**
	 * Get saved assistant settings merged with defaults.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_settings() {
		$settings = get_option( self::OPTION, array() );
		return wp_parse_args(
			is_array( $settings ) ? $settings : array(),
			array(
				'wps_wpr_ai_enable'   => 'yes',
				'wps_wpr_ai_can_edit' => 'yes',
			)
		);
	}

	/**
	 * Check whether the admin allows the assistant to propose setting changes.
	 *
	 * @return bool
	 */
	public static function wps_wpr_can_edit() {
		$settings = self::wps_wpr_get_settings();
		return 'yes' === $settings['wps_wpr_ai_can_edit'];
	}

	/**
	 * Check whether WordPress provides the AI Client and Abilities API.
	 *
	 * @return bool
	 */
	public static function wps_wpr_has_ai_api() {
		return function_exists( 'wp_ai_client_prompt' ) && function_exists( 'wp_register_ability' ) && class_exists( 'WP_AI_Client_Ability_Function_Resolver' );
	}

	/**
	 * Check whether an AI provider is configured and can generate text.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_ai_available() {
		if ( ! self::wps_wpr_has_ai_api() || ! wp_supports_ai() ) {
			return false;
		}
		return (bool) wp_ai_client_prompt( 'ping' )->is_supported_for_text_generation();
	}

	/**
	 * Settings the assistant can read and change.
	 *
	 * Each entry maps a setting id to its option, type and the values the
	 * plugin's own settings forms store, so changes match a manual save.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_settings_catalog() {
		$general = array(
			'option' => 'wps_wpr_settings_gallery',
			'tab'    => __( 'General', 'points-and-rewards-for-woocommerce' ),
			'on'     => 1,
			'off'    => '',
		);
		$coupon  = array(
			'option' => 'wps_wpr_coupons_gallery',
			'tab'    => __( 'Per Currency Points Settings', 'points-and-rewards-for-woocommerce' ),
			'on'     => 1,
			'off'    => 0,
		);
		$other   = array(
			'option' => 'wps_wpr_other_settings',
			'tab'    => __( 'Advance Settings', 'points-and-rewards-for-woocommerce' ),
			'on'     => 1,
			'off'    => 0,
		);
		$cart    = array(
			'option' => 'wps_wpr_abandoned_cart_settings',
			'tab'    => __( 'Abandoned Cart', 'points-and-rewards-for-woocommerce' ),
			'on'     => 'yes',
			'off'    => 'no',
		);

		$catalog = array(
			'wps_wpr_general_setting_enable'        => $general + array(
				'type'  => 'bool',
				'label' => __( 'Enable Points and Rewards', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Master switch. When off, no points features run on the store.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_general_signup'                => $general + array(
				'type'  => 'bool',
				'label' => __( 'Signup Points', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Award points to new customers when they register.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_general_signup_value'          => $general + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Signup Points Value', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Points a new customer gets after signup.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_general_refer_enable'          => $general + array(
				'type'  => 'bool',
				'label' => __( 'Referral Points', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Award points when a customer invites another customer with their referral link.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_general_refer_value'           => $general + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Referral Points Value', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Points the referrer gets for a successful referral.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_general_social_media_enable'   => $general + array(
				'type'  => 'bool',
				'label' => __( 'Social Sharing', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Show social share buttons for the referral link.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_custom_points_on_cart'         => $general + array(
				'type'  => 'bool',
				'label' => __( 'Redemption Over Cart Sub-total', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Let customers redeem points as a discount on the cart.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_cart_points_rate'              => $general + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Redemption Rate: Points', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Number of points that equal the currency amount in "Redemption Rate: Amount".', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_cart_price_rate'               => $general + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Redemption Rate: Amount', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Currency discount given for "Redemption Rate: Points" points.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_apply_points_checkout'         => $general + array(
				'type'  => 'bool',
				'label' => __( 'Apply Points on Checkout', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Also show the apply points field on the checkout page.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_show_redeem_notice'            => $general + array(
				'type'  => 'bool',
				'label' => __( 'Show Redeem Notice on Cart Page', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Show a notice on the cart page telling customers they can redeem points.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_points_redemption_messages'    => $general + array(
				'type'  => 'text',
				'label' => __( 'Points Redemption Message', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Text of the redeem notice on the cart page.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_enable_order_rewards_settings' => $general + array(
				'type'  => 'bool',
				'label' => __( 'Order Rewards', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Award bonus points after a customer places a given number of orders.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_number_of_reward_order'        => $general + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Order Rewards: Number of Orders', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Orders needed to receive the order reward.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_number_of_rewards_points'      => $general + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Order Rewards: Points', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Reward value, as fixed points or a percentage depending on "Order Rewards: Points Type".', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_order_rewards_points_type'     => $general + array(
				'type'    => 'enum',
				'options' => array( 'fixed', 'percent' ),
				'label'   => __( 'Order Rewards: Points Type', 'points-and-rewards-for-woocommerce' ),
				'desc'    => __( 'fixed = fixed points, percent = percentage of the order total.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_points_tab_text'               => $general + array(
				'type'  => 'text',
				'label' => __( 'Points Tab Text', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Label of the points tab in My Account.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_coupon_conversion_enable'      => $coupon + array(
				'type'  => 'bool',
				'label' => __( 'Per Currency Points Conversion', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Customers earn points for every currency unit spent on an order.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_coupon_conversion_points'      => $coupon + array(
				'type'  => 'number',
				'min'   => 0.01,
				'label' => __( 'Earning Rate: Amount Spent', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Currency amount spent that earns "Earning Rate: Points" points.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_coupon_conversion_price'       => $coupon + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Earning Rate: Points', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Points earned for every "Earning Rate: Amount Spent".', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_per_currency_discount_notice'  => $coupon + array(
				'type'  => 'bool',
				'label' => __( 'Show Per Currency Earning Notice', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Show the earning message on the cart page.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_per_curr_earning_messages'     => $coupon + array(
				'type'  => 'text',
				'label' => __( 'Per Currency Earning Message', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Cart page message. Supports [POINTS] and [CURRENCY].', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_cart_page_apply_point_section' => $other + array(
				'type'  => 'bool',
				'label' => __( 'Show Apply Points Section on Cart Page', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Show the [WPS_CART_PAGE_SECTION] apply points section on the cart page.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_checkout_page_apply_point_section' => $other + array(
				'type'  => 'bool',
				'label' => __( 'Show Apply Points Section on Checkout Page', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Show the [WPS_CHECKOUT_PAGE_SECTION] apply points section on the checkout page.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_enable_guest_user_rewards_points' => $other + array(
				'type'  => 'bool',
				'label' => __( 'Guest User Points', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Let guests earn points that are assigned when they create an account.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_guest_user_rewards_points'     => $other + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Guest User Points Value', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Points a guest earns.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_membership_setting_enable'     => array(
				'option' => 'wps_wpr_membership_settings',
				'tab'    => __( 'Membership', 'points-and-rewards-for-woocommerce' ),
				'on'     => 1,
				'off'    => 0,
				'type'   => 'bool',
				'label'  => __( 'Enable Membership', 'points-and-rewards-for-woocommerce' ),
				'desc'   => __( 'Turn membership levels on or off. Levels themselves are created in the Membership tab.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_notification_setting_enable'   => array(
				'option' => 'wps_wpr_notificatin_array',
				'tab'    => __( 'Points Notification', 'points-and-rewards-for-woocommerce' ),
				'on'     => 1,
				'off'    => 0,
				'type'   => 'bool',
				'label'  => __( 'Enable Points Notifications', 'points-and-rewards-for-woocommerce' ),
				'desc'   => __( 'Send emails to customers when their points change.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_enable_gamification_settings'  => array(
				'option' => 'wps_wpr_save_gami_setting',
				'tab'    => __( 'Gamification', 'points-and-rewards-for-woocommerce' ),
				'on'     => 'yes',
				'off'    => '',
				'type'   => 'bool',
				'label'  => __( 'Enable Gamification (Spin Wheel)', 'points-and-rewards-for-woocommerce' ),
				'desc'   => __( 'Show the spin wheel game. Segments are configured in the Gamification tab.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_enable_user_badges_settings'   => array(
				'option' => 'wps_wpr_user_badges_setting',
				'tab'    => __( 'Badges', 'points-and-rewards-for-woocommerce' ),
				'on'     => 'yes',
				'off'    => 'no',
				'type'   => 'bool',
				'label'  => __( 'Enable Badges', 'points-and-rewards-for-woocommerce' ),
				'desc'   => __( 'Award badges and bonus points when customers reach point thresholds. Badges are configured in the Badges tab.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_ac_enable'                     => $cart + array(
				'type'  => 'bool',
				'label' => __( 'Abandoned Cart Recovery', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Email customers who abandon their cart a bonus points offer.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_ac_abandon_hours'              => $cart + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Abandoned Cart: Hours Before Email', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Hours of cart inactivity before the offer email is sent.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_ac_bonus_points'               => $cart + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Abandoned Cart: Bonus Points', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Points awarded when the customer completes the order in time.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_ac_validity_days'              => $cart + array(
				'type'  => 'int',
				'min'   => 1,
				'label' => __( 'Abandoned Cart: Offer Validity (Days)', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Days the customer has to complete the order.', 'points-and-rewards-for-woocommerce' ),
			),
			'wps_wpr_ac_min_cart_total'             => $cart + array(
				'type'  => 'number',
				'min'   => 0,
				'label' => __( 'Abandoned Cart: Minimum Cart Total', 'points-and-rewards-for-woocommerce' ),
				'desc'  => __( 'Only send the offer for carts worth at least this amount.', 'points-and-rewards-for-woocommerce' ),
			),
		);

		return apply_filters( 'wps_wpr_ai_assistant_settings_catalog', $catalog );
	}

	/**
	 * Get the current value of a catalog setting, normalised for the model.
	 *
	 * @param  string $id   setting id.
	 * @param  array  $spec catalog entry.
	 * @return mixed
	 */
	public function wps_wpr_get_setting_value( $id, $spec ) {
		$option = get_option( $spec['option'], array() );
		if ( 'wps_wpr_abandoned_cart_settings' === $spec['option'] && class_exists( 'WPS_WPR_Abandoned_Cart_Recovery' ) ) {
			$option = WPS_WPR_Abandoned_Cart_Recovery::wps_wpr_get_settings();
		}
		$value = is_array( $option ) && isset( $option[ $id ] ) ? $option[ $id ] : null;

		switch ( $spec['type'] ) {
			case 'bool':
				return ! empty( $value ) && 'no' !== $value;
			case 'int':
				return null === $value || '' === $value ? null : (int) $value;
			case 'number':
				return null === $value || '' === $value ? null : (float) $value;
			default:
				return null === $value ? '' : (string) $value;
		}
	}

	/**
	 * Validate a proposed value and convert it to the stored format.
	 *
	 * @param  array $spec  catalog entry.
	 * @param  mixed $value proposed value.
	 * @return mixed|WP_Error stored value.
	 */
	public function wps_wpr_sanitize_setting_value( $spec, $value ) {
		switch ( $spec['type'] ) {
			case 'bool':
				$bool = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
				if ( null === $bool ) {
					return new WP_Error( 'invalid_value', __( 'Expected true or false.', 'points-and-rewards-for-woocommerce' ) );
				}
				return $bool ? $spec['on'] : $spec['off'];
			case 'int':
			case 'number':
				if ( ! is_numeric( $value ) ) {
					return new WP_Error( 'invalid_value', __( 'Expected a number.', 'points-and-rewards-for-woocommerce' ) );
				}
				$number = 'int' === $spec['type'] ? (int) $value : (float) $value;
				if ( isset( $spec['min'] ) && $number < $spec['min'] ) {
					/* translators: %s: minimum value */
					return new WP_Error( 'invalid_value', sprintf( __( 'Value must be at least %s.', 'points-and-rewards-for-woocommerce' ), $spec['min'] ) );
				}
				return $number;
			case 'enum':
				if ( ! in_array( (string) $value, $spec['options'], true ) ) {
					/* translators: %s: allowed values */
					return new WP_Error( 'invalid_value', sprintf( __( 'Allowed values: %s.', 'points-and-rewards-for-woocommerce' ), implode( ', ', $spec['options'] ) ) );
				}
				return (string) $value;
			default:
				return sanitize_text_field( (string) $value );
		}
	}

	/**
	 * Register the ability category.
	 *
	 * @return void
	 */
	public function wps_wpr_register_ability_category() {
		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Points and Rewards', 'points-and-rewards-for-woocommerce' ),
				'description' => __( 'Read and manage Points and Rewards for WooCommerce settings.', 'points-and-rewards-for-woocommerce' ),
			)
		);
	}

	/**
	 * Register the abilities used by the assistant.
	 *
	 * @return void
	 */
	public function wps_wpr_register_abilities() {
		$setting_ids = array_keys( self::wps_wpr_get_settings_catalog() );
		$permission  = function () {
			return current_user_can( 'manage_options' );
		};

		wp_register_ability(
			self::ABILITY_GET,
			array(
				'label'               => __( 'Get Points and Rewards settings', 'points-and-rewards-for-woocommerce' ),
				'description'         => 'Returns the current values of Points and Rewards for WooCommerce settings with their label, settings tab, description and type. Call this before answering questions about the store configuration or proposing changes. Pass "ids" to fetch specific settings, or omit it to fetch all.',
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'ids' => array(
							'type'        => 'array',
							'description' => 'Setting ids to return. Omit to return all settings.',
							'items'       => array(
								'type' => 'string',
								'enum' => $setting_ids,
							),
						),
					),
					'additionalProperties' => false,
				),
				'execute_callback'    => array( $this, 'wps_wpr_ability_get_settings' ),
				'permission_callback' => $permission,
				'meta'                => array(
					'annotations' => array(
						'readonly'   => true,
						'idempotent' => true,
					),
				),
			)
		);

		wp_register_ability(
			self::ABILITY_PROPOSE,
			array(
				'label'               => __( 'Propose Points and Rewards settings change', 'points-and-rewards-for-woocommerce' ),
				'description'         => 'Proposes changes to Points and Rewards for WooCommerce settings. Nothing is saved: the admin sees the proposal with Apply and Cancel buttons and must click Apply. Use the exact ids from get-settings. Boolean settings take true/false, numeric settings take numbers.',
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'changes' => array(
							'type'     => 'array',
							'minItems' => 1,
							'items'    => array(
								'type'                 => 'object',
								'properties'           => array(
									'id'    => array(
										'type' => 'string',
										'enum' => $setting_ids,
									),
									'value' => array(
										'type' => array( 'boolean', 'number', 'string' ),
									),
								),
								'required'             => array( 'id', 'value' ),
								'additionalProperties' => false,
							),
						),
						'summary' => array(
							'type'        => 'string',
							'description' => 'One short sentence describing the change for the admin.',
						),
					),
					'required'             => array( 'changes', 'summary' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => array( $this, 'wps_wpr_ability_propose_change' ),
				'permission_callback' => $permission,
				'meta'                => array(
					'annotations' => array(
						'readonly'    => true,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Ability callback: return current settings.
	 *
	 * @param  array|null $input ability input.
	 * @return array
	 */
	public function wps_wpr_ability_get_settings( $input = null ) {
		$catalog = self::wps_wpr_get_settings_catalog();
		$ids     = ! empty( $input['ids'] ) && is_array( $input['ids'] ) ? $input['ids'] : array_keys( $catalog );

		$settings = array();
		foreach ( $ids as $id ) {
			if ( empty( $catalog[ $id ] ) ) {
				continue;
			}
			$spec       = $catalog[ $id ];
			$setting    = array(
				'id'          => $id,
				'label'       => $spec['label'],
				'tab'         => $spec['tab'],
				'description' => $spec['desc'],
				'type'        => 'enum' === $spec['type'] ? 'one of: ' . implode( ', ', $spec['options'] ) : $spec['type'],
				'value'       => $this->wps_wpr_get_setting_value( $id, $spec ),
			);
			$settings[] = $setting;
		}

		return array(
			'currency' => get_woocommerce_currency(),
			'settings' => $settings,
		);
	}

	/**
	 * Ability callback: validate a change and store it until the admin confirms.
	 *
	 * @param  array|null $input ability input.
	 * @return array|WP_Error
	 */
	public function wps_wpr_ability_propose_change( $input = null ) {
		if ( ! self::wps_wpr_can_edit() ) {
			return new WP_Error( 'editing_disabled', __( 'Setting changes by the AI Assistant are turned off.', 'points-and-rewards-for-woocommerce' ) );
		}
		$catalog = self::wps_wpr_get_settings_catalog();
		$changes = ! empty( $input['changes'] ) && is_array( $input['changes'] ) ? $input['changes'] : array();
		if ( empty( $changes ) ) {
			return new WP_Error( 'no_changes', __( 'No changes were given.', 'points-and-rewards-for-woocommerce' ) );
		}

		$validated = array();
		foreach ( $changes as $change ) {
			$id = isset( $change['id'] ) ? (string) $change['id'] : '';
			if ( empty( $catalog[ $id ] ) ) {
				/* translators: %s: setting id */
				return new WP_Error( 'unknown_setting', sprintf( __( 'Unknown setting "%s".', 'points-and-rewards-for-woocommerce' ), $id ) );
			}
			$spec   = $catalog[ $id ];
			$stored = $this->wps_wpr_sanitize_setting_value( $spec, isset( $change['value'] ) ? $change['value'] : null );
			if ( is_wp_error( $stored ) ) {
				return new WP_Error( 'invalid_value', $spec['label'] . ': ' . $stored->get_error_message() );
			}
			$validated[] = array(
				'id'     => $id,
				'label'  => $spec['label'],
				'tab'    => $spec['tab'],
				'from'   => $this->wps_wpr_get_setting_value( $id, $spec ),
				'to'     => 'bool' === $spec['type'] ? $stored === $spec['on'] : $stored,
				'stored' => $stored,
			);
		}

		$proposal_id = wp_generate_uuid4();
		set_transient(
			$this->wps_wpr_proposal_key( $proposal_id ),
			array(
				'summary' => isset( $input['summary'] ) ? sanitize_text_field( $input['summary'] ) : '',
				'changes' => $validated,
			),
			HOUR_IN_SECONDS
		);

		return array(
			'proposal_id' => $proposal_id,
			'status'      => 'awaiting_admin_confirmation',
			'changes'     => array_map(
				function ( $change ) {
					unset( $change['stored'] );
					return $change;
				},
				$validated
			),
		);
	}

	/**
	 * Transient key for a proposal, scoped to the current user.
	 *
	 * @param  string $proposal_id proposal id.
	 * @return string
	 */
	public function wps_wpr_proposal_key( $proposal_id ) {
		return 'wps_wpr_ai_proposal_' . get_current_user_id() . '_' . md5( $proposal_id );
	}

	/**
	 * System instruction for the model.
	 *
	 * @return string
	 */
	public function wps_wpr_get_system_instruction() {
		$is_pro = function_exists( 'wps_wpr_is_active' ) && wps_wpr_is_active();

		return implode(
			"\n",
			array(
				'You are the in-dashboard assistant for the "Points and Rewards for WooCommerce" plugin. You help the store admin understand and configure the loyalty program.',
				'Store currency: ' . get_woocommerce_currency() . '. Pro version active: ' . ( $is_pro ? 'yes' : 'no' ) . '.',
				'',
				'Plugin settings tabs:',
				'- General: master switch, signup and referral points, social sharing, cart redemption and redemption rate, order rewards, points tab texts.',
				'- Per Currency Points Settings: points earned per currency spent on orders.',
				'- Points Table: view and adjust each customer\'s points and logs.',
				'- Points Notification: email templates for points events.',
				'- Membership: membership levels with required points and discounts.',
				'- Assign Product Points: points earned for buying specific products.',
				'- Advance Settings: apply-points sections, restrictions, My Account template, payment method and guest rewards.',
				'- Order Total Points: points by order total ranges.',
				'- Gamification: spin wheel game. Badges: badge levels by points. SMS / Whatsapp Integration. Campaigning: referral and promotional campaigns. Abandoned Cart: bonus points offer for abandoned carts.',
				'',
				'Rules:',
				'1. Call the get-settings tool before describing the current configuration. Never guess current values.',
				self::wps_wpr_can_edit() ? '2. To change settings, call the propose-settings-change tool. It does not save anything: tell the admin to review the proposal card and click Apply. Never say a change was saved.' : '2. You cannot change settings: the admin turned this off in the AI Assistant tab. Explain which tab and setting to change by hand instead.',
				'3. You can only change the settings returned by get-settings. For anything else (membership levels, product points, badge levels, email templates, campaigns), explain where in the plugin to configure it step by step.',
				'4. If the plugin cannot do what the admin asks (for example time-based multipliers such as double points on weekends), say so plainly, then suggest the closest available option' . ( $is_pro ? '' : ' or mention that the Pro version may offer it' ) . '.',
				'5. Be concise and practical. Use short paragraphs or bullet points. Refer to settings by their label and tab, not by id.',
				'6. Only answer questions about this plugin, WooCommerce loyalty programs and related store setup.',
			)
		);
	}

	/**
	 * Build model messages from the chat history sent by the browser.
	 *
	 * @param  array  $history previous turns as role/text pairs.
	 * @param  string $message new admin message.
	 * @return Message[]
	 */
	public function wps_wpr_build_messages( $history, $message ) {
		$messages = array();
		$history  = array_slice( is_array( $history ) ? $history : array(), -self::MAX_HISTORY );
		foreach ( $history as $turn ) {
			$text = isset( $turn['text'] ) ? sanitize_textarea_field( $turn['text'] ) : '';
			if ( '' === $text ) {
				continue;
			}
			$part       = array( new MessagePart( $text ) );
			$messages[] = ( isset( $turn['role'] ) && 'assistant' === $turn['role'] ) ? new ModelMessage( $part ) : new UserMessage( $part );
		}
		$messages[] = new UserMessage( array( new MessagePart( $message ) ) );
		return $messages;
	}

	/**
	 * Run the model with tool calls until it returns a text answer.
	 *
	 * @param  Message[] $messages conversation.
	 * @return array|WP_Error reply text and proposals.
	 */
	public function wps_wpr_run_conversation( $messages ) {
		$abilities = self::wps_wpr_can_edit() ? array( self::ABILITY_GET, self::ABILITY_PROPOSE ) : array( self::ABILITY_GET );
		$resolver  = new WP_AI_Client_Ability_Function_Resolver( ...$abilities );
		$proposals = array();

		for ( $step = 0; $step < self::MAX_STEPS; $step++ ) {
			$result = wp_ai_client_prompt( $messages )
				->using_system_instruction( $this->wps_wpr_get_system_instruction() )
				->using_abilities( ...$abilities )
				->using_temperature( 0.2 )
				->generate_text_result();

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$reply      = $result->toMessage();
			$messages[] = $reply;

			if ( ! $resolver->has_ability_calls( $reply ) ) {
				return array(
					'reply'     => $this->wps_wpr_get_message_text( $reply ),
					'proposals' => $proposals,
				);
			}

			$responses  = $resolver->execute_abilities( $reply );
			$messages[] = $responses;
			$proposals  = array_merge( $proposals, $this->wps_wpr_collect_proposals( $responses ) );
		}

		return array(
			'reply'     => __( 'Sorry, I could not finish that request. Please try rephrasing it.', 'points-and-rewards-for-woocommerce' ),
			'proposals' => $proposals,
		);
	}

	/**
	 * Get the visible text of a model message.
	 *
	 * @param  Message $message model message.
	 * @return string
	 */
	public function wps_wpr_get_message_text( $message ) {
		$text = array();
		foreach ( $message->getParts() as $part ) {
			if ( $part->getChannel()->isContent() && null !== $part->getText() ) {
				$text[] = $part->getText();
			}
		}
		return trim( implode( "\n\n", $text ) );
	}

	/**
	 * Collect proposals created by the propose ability in a tool response message.
	 *
	 * @param  Message $responses tool response message.
	 * @return array
	 */
	public function wps_wpr_collect_proposals( $responses ) {
		$proposals = array();
		foreach ( $responses->getParts() as $part ) {
			if ( ! $part->getType()->isFunctionResponse() ) {
				continue;
			}
			$response = $part->getFunctionResponse()->getResponse();
			if ( is_array( $response ) && ! empty( $response['proposal_id'] ) ) {
				$proposal = get_transient( $this->wps_wpr_proposal_key( $response['proposal_id'] ) );
				if ( $proposal ) {
					$proposals[] = array(
						'id'      => $response['proposal_id'],
						'summary' => $proposal['summary'],
						'changes' => $response['changes'],
					);
				}
			}
		}
		return $proposals;
	}

	/**
	 * Verify an ajax request from the assistant panel.
	 *
	 * @return void
	 */
	public function wps_wpr_verify_ajax_request() {
		check_ajax_referer( self::NONCE, 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'points-and-rewards-for-woocommerce' ) ), 403 );
		}
	}

	/**
	 * Ajax: send a chat message to the model.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_chat() {
		$this->wps_wpr_verify_ajax_request();

		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON; each turn is sanitized in wps_wpr_build_messages().
		$history = isset( $_POST['history'] ) ? json_decode( wp_unslash( $_POST['history'] ), true ) : array();
		if ( '' === $message ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a message.', 'points-and-rewards-for-woocommerce' ) ) );
		}
		$settings = self::wps_wpr_get_settings();
		if ( 'yes' !== $settings['wps_wpr_ai_enable'] ) {
			wp_send_json_error( array( 'message' => __( 'The AI Assistant is disabled. Enable it in the AI Assistant tab.', 'points-and-rewards-for-woocommerce' ) ) );
		}
		if ( ! self::wps_wpr_is_ai_available() ) {
			wp_send_json_error( array( 'message' => __( 'No AI provider is configured. Connect one in Settings > Connectors.', 'points-and-rewards-for-woocommerce' ) ) );
		}

		$result = $this->wps_wpr_run_conversation( $this->wps_wpr_build_messages( $history, $message ) );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( $result );
	}

	/**
	 * Ajax: apply or discard a proposal the admin confirmed in the chat.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_resolve_proposal() {
		$this->wps_wpr_verify_ajax_request();

		$proposal_id = isset( $_POST['proposal_id'] ) ? sanitize_text_field( wp_unslash( $_POST['proposal_id'] ) ) : '';
		$decision    = isset( $_POST['decision'] ) ? sanitize_text_field( wp_unslash( $_POST['decision'] ) ) : '';
		$key         = $this->wps_wpr_proposal_key( $proposal_id );
		$proposal    = get_transient( $key );
		if ( empty( $proposal['changes'] ) ) {
			wp_send_json_error( array( 'message' => __( 'This proposal has expired. Ask the assistant again.', 'points-and-rewards-for-woocommerce' ) ) );
		}
		delete_transient( $key );

		if ( 'apply' === $decision && ! self::wps_wpr_can_edit() ) {
			wp_send_json_error( array( 'message' => __( 'Setting changes by the AI Assistant are turned off.', 'points-and-rewards-for-woocommerce' ) ) );
		}
		if ( 'apply' !== $decision ) {
			wp_send_json_success( array( 'message' => __( 'Change discarded.', 'points-and-rewards-for-woocommerce' ) ) );
		}

		$this->wps_wpr_apply_changes( $proposal['changes'] );
		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'points-and-rewards-for-woocommerce' ) ) );
	}

	/**
	 * Save validated changes to their options.
	 *
	 * @param  array $changes validated changes.
	 * @return void
	 */
	public function wps_wpr_apply_changes( $changes ) {
		$options = array();
		$catalog = self::wps_wpr_get_settings_catalog();
		foreach ( $changes as $change ) {
			if ( empty( $catalog[ $change['id'] ] ) ) {
				continue;
			}
			$option = $catalog[ $change['id'] ]['option'];
			if ( ! isset( $options[ $option ] ) ) {
				$current            = get_option( $option, array() );
				$options[ $option ] = is_array( $current ) ? $current : array();
			}
			$options[ $option ][ $change['id'] ] = $change['stored'];
		}

		foreach ( $options as $option => $value ) {
			update_option( $option, $value );
		}

		if ( isset( $options['wps_wpr_abandoned_cart_settings'] ) && class_exists( 'WPS_WPR_Abandoned_Cart_Recovery' ) ) {
			WPS_WPR_Abandoned_Cart_Recovery::wps_wpr_sync_cron_schedule();
		}

		do_action( 'wps_wpr_ai_assistant_settings_applied', $changes );
	}

	/**
	 * Enqueue the chat panel assets on the assistant tab.
	 *
	 * @return void
	 */
	public function wps_wpr_enqueue_assets() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only page check.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab check.
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'wps-rwpr-setting' !== $page || 'ai-assistant' !== $tab ) {
			return;
		}

		wp_enqueue_style( 'wps-wpr-ai-assistant', WPS_RWPR_DIR_URL . 'admin/css/wps-wpr-ai-assistant.css', array(), REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION );
		wp_enqueue_script( 'wps-wpr-ai-assistant', WPS_RWPR_DIR_URL . 'admin/js/wps-wpr-ai-assistant.js', array( 'jquery' ), REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION, true );
		wp_localize_script(
			'wps-wpr-ai-assistant',
			'wps_wpr_ai',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE ),
				'i18n'    => array(
					'thinking' => __( 'Thinking…', 'points-and-rewards-for-woocommerce' ),
					'error'    => __( 'Something went wrong. Please try again.', 'points-and-rewards-for-woocommerce' ),
					'apply'    => __( 'Apply', 'points-and-rewards-for-woocommerce' ),
					'cancel'   => __( 'Cancel', 'points-and-rewards-for-woocommerce' ),
					'proposed' => __( 'Proposed change', 'points-and-rewards-for-woocommerce' ),
					'on'       => __( 'On', 'points-and-rewards-for-woocommerce' ),
					'off'      => __( 'Off', 'points-and-rewards-for-woocommerce' ),
					'empty'    => __( '(empty)', 'points-and-rewards-for-woocommerce' ),
					'applied'  => __( 'Applied', 'points-and-rewards-for-woocommerce' ),
					'canceled' => __( 'Canceled', 'points-and-rewards-for-woocommerce' ),
				),
			)
		);
	}
}
