<?php
/**
 * Amount used to calculate earned points: with or without tax and shipping.
 *
 * Adds an "Earning Calculation" section to the General settings tab:
 * - "Calculate Earned Points On": the order total including tax (default, the
 *   behaviour before this setting existed) or excluding tax.
 * - "Exclude Shipping": leave the shipping charge out of the amount (off by default).
 *
 * Every money-based earning rule (per currency spent, order total ranges and the
 * membership percentage bonus) reads its amount from wps_wpr_get_order_amount_for_points(),
 * and cart-side estimates can use wps_wpr_get_cart_amount_for_points().
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
 * Class WPS_WPR_Points_Amount_Settings.
 */
class WPS_WPR_Points_Amount_Settings {

	/**
	 * Settings option (the General tab option).
	 */
	const OPTION = 'wps_wpr_settings_gallery';

	/**
	 * Tax mode key: "incl" or "excl".
	 */
	const TAX_KEY = 'wps_wpr_points_amount_tax_mode';

	/**
	 * Exclude shipping key: 1 or 0.
	 */
	const SHIPPING_KEY = 'wps_wpr_points_amount_exclude_shipping';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_add_settings' ), 16, 1 );
		add_filter( 'wps_wpr_general_settings_save_option', array( $this, 'wps_wpr_save_settings' ), 16, 1 );
		add_action( 'wps_wpr_additional_general_settings', array( $this, 'wps_wpr_render_field' ), 10, 2 );
	}

	/**
	 * Saved tax mode. Defaults to "incl", which matches the order total used before.
	 *
	 * @return string "incl" or "excl".
	 */
	public static function wps_wpr_get_tax_mode() {
		$settings = get_option( self::OPTION, array() );
		$mode     = is_array( $settings ) && ! empty( $settings[ self::TAX_KEY ] ) ? (string) $settings[ self::TAX_KEY ] : 'incl';
		$mode     = (string) apply_filters( 'wps_wpr_points_amount_tax_mode', $mode );
		return 'excl' === $mode ? 'excl' : 'incl';
	}

	/**
	 * Whether shipping is left out of the amount. Defaults to false (shipping included, as before).
	 *
	 * @return bool
	 */
	public static function wps_wpr_exclude_shipping() {
		$settings = get_option( self::OPTION, array() );
		$exclude  = is_array( $settings ) && ! empty( $settings[ self::SHIPPING_KEY ] );
		return (bool) apply_filters( 'wps_wpr_points_amount_exclude_shipping', $exclude );
	}

	/**
	 * Work out the amount from a total and its tax / shipping parts.
	 *
	 * @param float $total        total including tax and shipping.
	 * @param float $total_tax    all tax in the total (including shipping tax).
	 * @param float $shipping     shipping excluding tax.
	 * @param float $shipping_tax tax on shipping.
	 * @return float
	 */
	public static function wps_wpr_calculate_amount( $total, $total_tax, $shipping, $shipping_tax ) {
		$amount = (float) $total;
		$excl   = 'excl' === self::wps_wpr_get_tax_mode();

		if ( self::wps_wpr_exclude_shipping() ) {
			$amount -= (float) $shipping;
			if ( ! $excl ) {
				$amount -= (float) $shipping_tax;
			}
		}
		if ( $excl ) {
			// Includes the shipping tax, so this is right whether or not shipping was removed above.
			$amount -= (float) $total_tax;
		}

		return max( 0, round( $amount, wc_get_price_decimals() ) );
	}

	/**
	 * Amount of an order that earns points.
	 *
	 * @param WC_Order|int $order order or order id.
	 * @return float
	 */
	public static function wps_wpr_get_order_amount( $order ) {
		$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return 0;
		}

		$amount = self::wps_wpr_calculate_amount(
			$order->get_total(),
			$order->get_total_tax(),
			$order->get_shipping_total(),
			$order->get_shipping_tax()
		);
		return (float) apply_filters( 'wps_wpr_order_amount_for_points', $amount, $order );
	}

	/**
	 * Amount of a cart that will earn points.
	 *
	 * @param WC_Cart|null $cart cart, defaults to the current cart.
	 * @return float
	 */
	public static function wps_wpr_get_cart_amount( $cart = null ) {
		if ( null === $cart && function_exists( 'WC' ) && isset( WC()->cart ) ) {
			$cart = WC()->cart;
		}
		if ( ! $cart || ! is_a( $cart, 'WC_Cart' ) ) {
			return 0;
		}

		$amount = self::wps_wpr_calculate_amount(
			$cart->get_total( 'edit' ),
			$cart->get_total_tax(),
			$cart->get_shipping_total(),
			$cart->get_shipping_tax()
		);
		return (float) apply_filters( 'wps_wpr_cart_amount_for_points', $amount, $cart );
	}

	/**
	 * Add the section to the General settings tab.
	 *
	 * @param array $settings settings fields.
	 * @return array
	 */
	public function wps_wpr_add_settings( $settings ) {
		$settings[] = array(
			'title' => __( 'Earning Calculation', 'points-and-rewards-for-woocommerce' ),
			'type'  => 'title',
		);
		$settings[] = array(
			'title'    => __( 'Calculate Earned Points On', 'points-and-rewards-for-woocommerce' ),
			'id'       => self::TAX_KEY,
			'type'     => 'wps_wpr_points_tax_mode_select',
			'desc_tip' => __( 'Choose whether points earned from spending (per currency spent, order total and membership percentage points) are calculated on the order total including tax or excluding tax.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'title'    => __( 'Exclude Shipping', 'points-and-rewards-for-woocommerce' ),
			'id'       => self::SHIPPING_KEY,
			'type'     => 'wps_wpr_points_shipping_checkbox',
			'desc_tip' => __( 'Leave the shipping charge out of the amount used to calculate earned points.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'type' => 'sectionend',
		);
		return $settings;
	}

	/**
	 * Save the section. The General tab verifies the nonce before this filter runs.
	 *
	 * @param array $general_settings_array settings to save.
	 * @return array
	 */
	public function wps_wpr_save_settings( $general_settings_array ) {
		if ( ! is_array( $general_settings_array ) ) {
			return $general_settings_array;
		}
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$mode    = ! empty( $_POST[ self::TAX_KEY ] ) ? sanitize_key( wp_unslash( $_POST[ self::TAX_KEY ] ) ) : 'incl';
		$exclude = ! empty( $_POST[ self::SHIPPING_KEY ] ) ? 1 : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$general_settings_array[ self::TAX_KEY ]      = 'excl' === $mode ? 'excl' : 'incl';
		$general_settings_array[ self::SHIPPING_KEY ] = $exclude;
		return $general_settings_array;
	}

	/**
	 * Render the custom field types of this section.
	 *
	 * @param array $value            field.
	 * @param array $general_settings saved settings.
	 * @return void
	 */
	public function wps_wpr_render_field( $value, $general_settings ) {
		if ( empty( $value['type'] ) || empty( $value['id'] ) ) {
			return;
		}
		$general_settings = is_array( $general_settings ) ? $general_settings : array();

		if ( 'wps_wpr_points_tax_mode_select' === $value['type'] ) {
			$saved = ! empty( $general_settings[ self::TAX_KEY ] ) && 'excl' === $general_settings[ self::TAX_KEY ] ? 'excl' : 'incl';
			?>
			<select name="<?php echo esc_attr( self::TAX_KEY ); ?>" id="<?php echo esc_attr( self::TAX_KEY ); ?>" class="wps_wgm_new_woo_ver_style_select">
				<option value="incl" <?php selected( $saved, 'incl' ); ?>><?php esc_html_e( 'Order total including tax', 'points-and-rewards-for-woocommerce' ); ?></option>
				<option value="excl" <?php selected( $saved, 'excl' ); ?>><?php esc_html_e( 'Order total excluding tax', 'points-and-rewards-for-woocommerce' ); ?></option>
			</select>
			<?php
			return;
		}

		if ( 'wps_wpr_points_shipping_checkbox' === $value['type'] ) {
			?>
			<label for="<?php echo esc_attr( self::SHIPPING_KEY ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( self::SHIPPING_KEY ); ?>" id="<?php echo esc_attr( self::SHIPPING_KEY ); ?>" value="1" <?php checked( ! empty( $general_settings[ self::SHIPPING_KEY ] ) ); ?>>
				<?php esc_html_e( 'Do not count shipping when calculating earned points.', 'points-and-rewards-for-woocommerce' ); ?>
			</label>
			<?php
		}
	}
}

if ( ! function_exists( 'wps_wpr_get_order_amount_for_points' ) ) {
	/**
	 * Amount of an order that earns points, following the Earning Calculation settings.
	 *
	 * @param WC_Order|int $order order or order id.
	 * @return float
	 */
	function wps_wpr_get_order_amount_for_points( $order ) {
		return WPS_WPR_Points_Amount_Settings::wps_wpr_get_order_amount( $order );
	}
}

if ( ! function_exists( 'wps_wpr_get_cart_amount_for_points' ) ) {
	/**
	 * Amount of a cart that will earn points, following the Earning Calculation settings.
	 *
	 * @param WC_Cart|null $cart cart, defaults to the current cart.
	 * @return float
	 */
	function wps_wpr_get_cart_amount_for_points( $cart = null ) {
		return WPS_WPR_Points_Amount_Settings::wps_wpr_get_cart_amount( $cart );
	}
}
