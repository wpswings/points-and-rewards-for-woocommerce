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
 * membership percentage bonus) reads its amount from wps_wpr_get_order_amount_for_points().
 * wps_wpr_get_cart_amount_for_points() / ::wps_wpr_get_cart_amount() are a public API for
 * cart-side estimates; the cart points messages (WPS-8329, PR #286) use the static method.
 *
 * When an order earns points, the basis used (tax mode, exclude shipping, amount) is stored
 * on the order, so cancel / refund deductions use the basis the order was awarded on even if
 * the settings change later. Orders awarded before this setting existed have no stored basis
 * and use the order total including tax and shipping, which is how they were awarded.
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
	 * Order meta: basis the order's points were awarded on (tax_mode, exclude_shipping, amount).
	 */
	const BASIS_META = '_wps_wpr_points_amount_basis';

	/**
	 * Orders that had no money-based points awarded when their status started to change.
	 *
	 * @var array
	 */
	private $pending_award = array();

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_filter( 'wps_wpr_general_settings', array( $this, 'wps_wpr_add_settings' ), 16, 1 );
		add_filter( 'wps_wpr_general_settings_save_option', array( $this, 'wps_wpr_save_settings' ), 16, 1 );
		add_action( 'wps_wpr_additional_general_settings', array( $this, 'wps_wpr_render_field' ), 10, 2 );
		// Around the award handlers (free at 10, Pro at 11; re-complete flags are cleared at 1).
		add_action( 'woocommerce_order_status_changed', array( $this, 'wps_wpr_note_unawarded_order' ), 2, 1 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'wps_wpr_store_order_basis_after_award' ), 98, 1 );
	}

	/**
	 * Current settings as a basis array.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_current_basis() {
		return array(
			'tax_mode'         => self::wps_wpr_get_tax_mode(),
			'exclude_shipping' => self::wps_wpr_exclude_shipping() ? 1 : 0,
		);
	}

	/**
	 * Basis used before this setting existed: order total including tax and shipping.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_legacy_basis() {
		return array(
			'tax_mode'         => 'incl',
			'exclude_shipping' => 0,
		);
	}

	/**
	 * Basis stored on the order when its points were awarded, or null when none was stored
	 * (orders awarded before this setting existed).
	 *
	 * @param WC_Order|int $order order or order id.
	 * @return array|null
	 */
	public static function wps_wpr_get_stored_basis( $order ) {
		$order_id = is_numeric( $order ) ? absint( $order ) : ( is_a( $order, 'WC_Abstract_Order' ) ? $order->get_id() : 0 );
		if ( empty( $order_id ) ) {
			return null;
		}
		$basis = wps_wpr_hpos_get_meta_data( $order_id, self::BASIS_META, true );
		if ( ! is_array( $basis ) || ! isset( $basis['tax_mode'], $basis['amount'] ) ) {
			return null;
		}
		return array(
			'tax_mode'         => 'excl' === $basis['tax_mode'] ? 'excl' : 'incl',
			'exclude_shipping' => empty( $basis['exclude_shipping'] ) ? 0 : 1,
			'amount'           => (float) $basis['amount'],
		);
	}

	/**
	 * Whether money-based points (per currency spent or order total) were awarded for the order.
	 *
	 * @param int $order_id order id.
	 * @return bool
	 */
	private static function wps_wpr_order_has_amount_points( $order_id ) {
		return 'set' === wps_wpr_hpos_get_meta_data( $order_id, "$order_id#item_conversion_id", true )
			|| 'yes' === wps_wpr_hpos_get_meta_data( $order_id, "$order_id#points_assignedon_order_total", true );
	}

	/**
	 * Before the award handlers: remember orders that have no money-based points yet.
	 *
	 * @param int $order_id order id.
	 * @return void
	 */
	public function wps_wpr_note_unawarded_order( $order_id ) {
		$order_id = absint( $order_id );
		if ( $order_id && ! self::wps_wpr_order_has_amount_points( $order_id ) ) {
			$this->pending_award[ $order_id ] = true;
		}
	}

	/**
	 * After the award handlers: when the order has just earned money-based points, store the
	 * basis they were calculated on. Orders already awarded (including ones awarded before this
	 * setting existed) are left untouched.
	 *
	 * @param int $order_id order id.
	 * @return void
	 */
	public function wps_wpr_store_order_basis_after_award( $order_id ) {
		$order_id = absint( $order_id );
		if ( empty( $this->pending_award[ $order_id ] ) ) {
			return;
		}
		unset( $this->pending_award[ $order_id ] );
		if ( ! self::wps_wpr_order_has_amount_points( $order_id ) ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$basis           = self::wps_wpr_get_current_basis();
		$basis['amount'] = self::wps_wpr_get_order_amount( $order );
		wps_wpr_hpos_update_meta_data( $order_id, self::BASIS_META, $basis );
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
	 * @param array $basis        optional basis (tax_mode, exclude_shipping); defaults to the current settings.
	 * @return float
	 */
	public static function wps_wpr_calculate_amount( $total, $total_tax, $shipping, $shipping_tax, $basis = null ) {
		$basis  = is_array( $basis ) ? $basis : self::wps_wpr_get_current_basis();
		$amount = (float) $total;
		$excl   = isset( $basis['tax_mode'] ) && 'excl' === $basis['tax_mode'];

		if ( ! empty( $basis['exclude_shipping'] ) ) {
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
	 * Amount the order's points were awarded on, for cancel / refund recalculation. Uses the
	 * basis stored at award time; orders without one (awarded before this setting existed)
	 * use the order total including tax and shipping, as they were awarded.
	 *
	 * @param WC_Order|int $order order or order id.
	 * @return float
	 */
	public static function wps_wpr_get_awarded_order_amount( $order ) {
		$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return 0;
		}
		$basis = self::wps_wpr_get_stored_basis( $order );
		return null === $basis ? (float) $order->get_total() : (float) $basis['amount'];
	}

	/**
	 * Part of a refund on the basis its order was awarded on: refunded line totals, plus their
	 * tax only when tax was included, plus refunded shipping (and its tax when tax was included)
	 * only when shipping was included. A refund amount not tied to refunded lines is converted
	 * in proportion to the order. With the legacy / default basis this is the refund amount.
	 *
	 * @param WC_Order_Refund $refund refund.
	 * @param WC_Order        $order  parent order.
	 * @return float
	 */
	public static function wps_wpr_get_refund_amount( $refund, $order ) {
		if ( ! is_a( $refund, 'WC_Order_Refund' ) || ! is_a( $order, 'WC_Order' ) ) {
			return 0;
		}
		$refund_amount = abs( (float) $refund->get_amount() );
		$order_total   = (float) $order->get_total();
		$stored        = self::wps_wpr_get_stored_basis( $order );
		$basis         = null === $stored ? self::wps_wpr_get_legacy_basis() : $stored;
		$order_amount  = self::wps_wpr_get_awarded_order_amount( $order );
		$order_ratio   = $order_total > 0 ? $order_amount / $order_total : 0;

		$lines_total = 0;
		$lines_tax   = 0;
		foreach ( $refund->get_items( array( 'line_item', 'fee' ) ) as $item ) {
			$lines_total += abs( (float) $item->get_total() );
			$lines_tax   += abs( (float) $item->get_total_tax() );
		}
		$shipping     = 0;
		$shipping_tax = 0;
		foreach ( $refund->get_items( 'shipping' ) as $item ) {
			$shipping     += abs( (float) $item->get_total() );
			$shipping_tax += abs( (float) $item->get_total_tax() );
		}

		$itemized = $lines_total + $lines_tax + $shipping + $shipping_tax;
		if ( $itemized <= 0 ) {
			return $refund_amount * $order_ratio;
		}

		$amount = self::wps_wpr_calculate_amount( $itemized, $lines_tax + $shipping_tax, $shipping, $shipping_tax, $basis );
		if ( $refund_amount < $itemized ) {
			// Less was refunded than the refunded lines are worth: scale down.
			$amount *= $refund_amount / $itemized;
		} else {
			// Extra amount not tied to a line.
			$amount += ( $refund_amount - $itemized ) * $order_ratio;
		}
		return (float) $amount;
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

if ( ! function_exists( 'wps_wpr_get_awarded_order_amount_for_points' ) ) {
	/**
	 * Amount an order's points were awarded on (stored basis, or the order total for orders
	 * awarded before the Earning Calculation settings existed). Use it when taking points back.
	 *
	 * @param WC_Order|int $order order or order id.
	 * @return float
	 */
	function wps_wpr_get_awarded_order_amount_for_points( $order ) {
		return WPS_WPR_Points_Amount_Settings::wps_wpr_get_awarded_order_amount( $order );
	}
}

if ( ! function_exists( 'wps_wpr_get_refund_amount_for_points' ) ) {
	/**
	 * Part of a refund on the basis its order's points were awarded on.
	 *
	 * @param WC_Order_Refund $refund refund.
	 * @param WC_Order        $order  parent order.
	 * @return float
	 */
	function wps_wpr_get_refund_amount_for_points( $refund, $order ) {
		return WPS_WPR_Points_Amount_Settings::wps_wpr_get_refund_amount( $refund, $order );
	}
}

/*
 * Public API for cart-side estimates. Not called by the free plugin itself; the cart points
 * messages (WPS-8329, PR #286) use WPS_WPR_Points_Amount_Settings::wps_wpr_get_cart_amount().
 */
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
