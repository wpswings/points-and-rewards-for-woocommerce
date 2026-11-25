<?php
/**
 * Native Cart / Checkout block support for points redemption.
 *
 * Extends the WooCommerce Store API cart endpoint with the customer's points
 * data, registers an update callback to apply / remove points, and registers
 * a block integration that renders the "Redeem points" panel inside the
 * Cart and Checkout blocks. Redemption itself reuses the same validation and
 * session logic as the classic cart (see
 * Points_Rewards_For_WooCommerce_Public::wps_wpr_apply_points_to_cart()).
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart / Checkout block points redemption.
 */
class WPS_WPR_Blocks_Redeem {

	/**
	 * Store API extension namespace.
	 */
	const STORE_API_NAMESPACE = 'wps-wpr';

	/**
	 * Block integration name (script data is exposed as "<name>_data").
	 */
	const INTEGRATION_NAME = 'wps-wpr-redeem-points';

	/**
	 * Public class instance holding the shared redemption logic.
	 *
	 * @var Points_Rewards_For_WooCommerce_Public
	 */
	private $public;

	/**
	 * Whether the Store API data has been registered in this request.
	 *
	 * @var bool
	 */
	private $store_api_registered = false;

	/**
	 * Constructor.
	 *
	 * @param Points_Rewards_For_WooCommerce_Public $plugin_public public class instance.
	 */
	public function __construct( $plugin_public ) {
		$this->public = $plugin_public;
	}

	/**
	 * Whether the native block redemption UI is enabled.
	 *
	 * Filterable so sites can fall back to the legacy injected markup.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_native_enabled() {
		return (bool) apply_filters( 'wps_wpr_native_block_redeem_enabled', true );
	}

	/**
	 * Read a numeric general setting.
	 *
	 * @param string $key setting key.
	 * @return int
	 */
	private function wps_wpr_setting( $key ) {
		$settings = get_option( 'wps_wpr_settings_gallery', array() );
		return ( is_array( $settings ) && ! empty( $settings[ $key ] ) ) ? (int) $settings[ $key ] : 0;
	}

	/**
	 * Register the Store API endpoint data and update callback.
	 *
	 * Hooked on woocommerce_blocks_loaded.
	 *
	 * @return void
	 */
	public function wps_wpr_register_store_api() {
		if ( $this->store_api_registered || ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			return;
		}
		$this->store_api_registered = true;

		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema::IDENTIFIER,
				'namespace'       => self::STORE_API_NAMESPACE,
				'data_callback'   => array( $this, 'wps_wpr_cart_data' ),
				'schema_callback' => array( $this, 'wps_wpr_cart_schema' ),
				'schema_type'     => ARRAY_A,
			)
		);

		if ( function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
			woocommerce_store_api_register_update_callback(
				array(
					'namespace' => self::STORE_API_NAMESPACE,
					'callback'  => array( $this, 'wps_wpr_update_callback' ),
				)
			);
		}
	}

	/**
	 * Schema of the data added to the cart endpoint.
	 *
	 * @return array
	 */
	public function wps_wpr_cart_schema() {
		$int  = array(
			'type'     => 'integer',
			'context'  => array( 'view', 'edit' ),
			'readonly' => true,
		);
		$bool = array(
			'type'     => 'boolean',
			'context'  => array( 'view', 'edit' ),
			'readonly' => true,
		);
		$str  = array(
			'type'     => 'string',
			'context'  => array( 'view', 'edit' ),
			'readonly' => true,
		);

		return array(
			'enabled_cart'     => $bool + array( 'description' => __( 'Points redemption is enabled on the cart.', 'points-and-rewards-for-woocommerce' ) ),
			'enabled_checkout' => $bool + array( 'description' => __( 'Points redemption is enabled on the checkout.', 'points-and-rewards-for-woocommerce' ) ),
			'logged_in'        => $bool + array( 'description' => __( 'The customer is logged in.', 'points-and-rewards-for-woocommerce' ) ),
			'can_redeem'       => $bool + array( 'description' => __( 'The customer is allowed to redeem points.', 'points-and-rewards-for-woocommerce' ) ),
			'balance'          => $int + array( 'description' => __( 'Customer points balance.', 'points-and-rewards-for-woocommerce' ) ),
			'available'        => $int + array( 'description' => __( 'Points available to redeem on this cart.', 'points-and-rewards-for-woocommerce' ) ),
			'min_points'       => $int + array( 'description' => __( 'Minimum points balance required to redeem.', 'points-and-rewards-for-woocommerce' ) ),
			'max_points'       => $int + array( 'description' => __( 'Maximum points usable on this cart.', 'points-and-rewards-for-woocommerce' ) ),
			'applied_points'   => $int + array( 'description' => __( 'Points currently applied to the cart.', 'points-and-rewards-for-woocommerce' ) ),
			'applied_amount'   => $str + array( 'description' => __( 'Discount from the applied points, formatted.', 'points-and-rewards-for-woocommerce' ) ),
			'points_rate'      => $int + array( 'description' => __( 'Points side of the redemption conversion rate.', 'points-and-rewards-for-woocommerce' ) ),
			'price_rate'       => $int + array( 'description' => __( 'Currency side of the redemption conversion rate.', 'points-and-rewards-for-woocommerce' ) ),
			'rate_text'        => $str + array( 'description' => __( 'Conversion rate, formatted.', 'points-and-rewards-for-woocommerce' ) ),
		);
	}

	/**
	 * Format a money amount as plain text.
	 *
	 * @param float $amount amount.
	 * @return string
	 */
	private function wps_wpr_plain_price( $amount ) {
		return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, get_bloginfo( 'charset' ) );
	}

	/**
	 * Data added to the cart endpoint.
	 *
	 * @return array
	 */
	public function wps_wpr_cart_data() {
		$points_rate = $this->wps_wpr_setting( 'wps_wpr_cart_points_rate' );
		$points_rate = $points_rate > 0 ? $points_rate : 1;
		$price_rate  = $this->wps_wpr_setting( 'wps_wpr_cart_price_rate' );
		$price_rate  = $price_rate > 0 ? $price_rate : 1;
		$user_id     = get_current_user_id();

		$data = array(
			'enabled_cart'     => 1 === $this->wps_wpr_setting( 'wps_wpr_custom_points_on_cart' ),
			'enabled_checkout' => 1 === $this->wps_wpr_setting( 'wps_wpr_apply_points_checkout' ),
			'logged_in'        => $user_id > 0,
			'can_redeem'       => false,
			'balance'          => 0,
			'available'        => 0,
			'min_points'       => $this->wps_wpr_setting( 'wps_wpr_apply_points_value' ),
			'max_points'       => 0,
			'applied_points'   => 0,
			'applied_amount'   => '',
			'points_rate'      => $points_rate,
			'price_rate'       => $price_rate,
			/* translators: 1: number of points, 2: money amount. */
			'rate_text'        => sprintf( __( '%1$s points = %2$s', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $points_rate ), $this->wps_wpr_plain_price( $price_rate ) ),
		);

		if ( ! $user_id ) {
			return $data;
		}

		$data['can_redeem'] = ! wps_wpr_restrict_user_fun( $user_id ) && ! apply_filters( 'wps_wpr_allowed_user_roles_points_features', false );
		$data['balance']    = max( 0, (int) get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$data['available']  = max( 0, (int) $this->public->wps_wpr_get_available_redeem_points( $user_id ) );

		$subtotal = ( isset( WC()->cart ) && WC()->cart ) ? (float) WC()->cart->get_subtotal() : 0;
		$to_cover = $subtotal > 0 ? (int) ceil( round( $subtotal * $points_rate / $price_rate, 4 ) ) : 0;

		$data['max_points'] = min( $data['available'], $to_cover );

		$applied_amount = ( isset( WC()->session ) && WC()->session ) ? (float) WC()->session->get( 'wps_cart_points', 0 ) : 0;
		if ( $applied_amount > 0 ) {
			$data['applied_points'] = (int) ceil( round( $applied_amount * $points_rate / $price_rate, 4 ) );
			$data['applied_amount'] = $this->wps_wpr_plain_price( $applied_amount );
		}

		return $data;
	}

	/**
	 * Store API update callback: apply or remove points.
	 *
	 * Called through extensionCartUpdate( { namespace: 'wps-wpr', data: { action, points } } ).
	 *
	 * @param array $data request data.
	 * @return void
	 * @throws \Automattic\WooCommerce\StoreApi\Exceptions\RouteException When the request is rejected.
	 */
	public function wps_wpr_update_callback( $data ) {
		$data   = is_array( $data ) ? $data : array();
		$action = isset( $data['action'] ) ? sanitize_key( $data['action'] ) : '';

		if ( 'remove' === $action ) {
			$this->public->wps_wpr_remove_points_from_cart();
			return;
		}

		if ( 'apply' !== $action ) {
			$this->wps_wpr_throw( __( 'Unknown points action.', 'points-and-rewards-for-woocommerce' ) );
		}

		if ( 1 !== $this->wps_wpr_setting( 'wps_wpr_custom_points_on_cart' ) && 1 !== $this->wps_wpr_setting( 'wps_wpr_apply_points_checkout' ) ) {
			$this->wps_wpr_throw( __( 'Points redemption is not available.', 'points-and-rewards-for-woocommerce' ) );
		}

		$points   = isset( $data['points'] ) && is_scalar( $data['points'] ) ? sanitize_text_field( (string) $data['points'] ) : '';
		$response = $this->public->wps_wpr_apply_points_to_cart( $points );
		if ( empty( $response['result'] ) ) {
			$message = ! empty( $response['message'] ) ? $response['message'] : __( 'Can not redeem!', 'points-and-rewards-for-woocommerce' );
			$this->wps_wpr_throw( wp_strip_all_tags( $message ) );
		}
	}

	/**
	 * Reject a Store API request with a message shown to the customer.
	 *
	 * @param string $message message.
	 * @return void
	 * @throws \Automattic\WooCommerce\StoreApi\Exceptions\RouteException Always.
	 * @throws Exception When the Store API exception class is unavailable.
	 */
	private function wps_wpr_throw( $message ) {
		if ( class_exists( '\Automattic\WooCommerce\StoreApi\Exceptions\RouteException' ) ) {
			throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'wps_wpr_redeem_points_error', $message, 400 ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}
		throw new Exception( $message ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
	}

	/**
	 * Whether the current request is a Store API request.
	 *
	 * @return bool
	 */
	private function wps_wpr_is_store_api_request() {
		if ( function_exists( 'WC' ) && is_callable( array( WC(), 'is_store_api_request' ) ) ) {
			return (bool) WC()->is_store_api_request();
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return defined( 'REST_REQUEST' ) && REST_REQUEST && false !== strpos( $uri, '/wc/store' );
	}

	/**
	 * Clear the redeemed points when the customer removes the "Cart Discount"
	 * coupon chip in the Cart / Checkout block.
	 *
	 * Without this the discount would be re-added on the next cart calculation.
	 *
	 * @param string $coupon_code removed coupon code.
	 * @return void
	 */
	public function wps_wpr_coupon_removed( $coupon_code ) {
		if ( ! $this->wps_wpr_is_store_api_request() ) {
			return;
		}
		$cart_discount = esc_html__( 'Cart Discount', 'points-and-rewards-for-woocommerce' );
		if ( strtolower( (string) $coupon_code ) !== strtolower( $cart_discount ) ) {
			return;
		}
		if ( isset( WC()->session ) && WC()->session ) {
			WC()->session->__unset( 'wps_cart_points' );
			WC()->session->__unset( 'wps_wpr_tax_before_coupon' );
		}
	}

	/**
	 * Register the block integration with the Cart or Checkout block.
	 *
	 * @param object $integration_registry Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry.
	 * @return void
	 */
	public function wps_wpr_register_block_integration( $integration_registry ) {
		if ( ! self::wps_wpr_is_native_enabled() || ! interface_exists( '\Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface' ) ) {
			return;
		}
		require_once plugin_dir_path( __FILE__ ) . 'class-wps-wpr-blocks-redeem-integration.php';
		if ( is_object( $integration_registry ) && method_exists( $integration_registry, 'is_registered' ) && $integration_registry->is_registered( self::INTEGRATION_NAME ) ) {
			return;
		}
		$integration_registry->register( new WPS_WPR_Blocks_Redeem_Integration() );
	}
}
