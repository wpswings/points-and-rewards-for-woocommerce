<?php
/**
 * Earn-points messages on shop, product, cart and checkout pages.
 *
 * Shows customers how many points they will earn ("Earn 50 points on this item")
 * using the plugin's real earning rules: product-assigned points plus
 * per-currency points (including membership-wise per-currency rates).
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
 * Earn-points messages.
 */
class WPS_WPR_Earn_Messages {

	/**
	 * Settings option name.
	 */
	const OPTION = 'wps_wpr_earn_message_settings';

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_default_settings() {
		return array(
			'wps_wpr_em_enable_product' => 'no',
			'wps_wpr_em_enable_shop'    => 'no',
			'wps_wpr_em_enable_cart'    => 'no',
			'wps_wpr_em_show_guests'    => 'yes',
			'wps_wpr_em_product_text'   => __( 'Earn [POINTS] points on this item', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_em_shop_text'      => __( 'Earn [POINTS] points', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_em_cart_text'      => __( 'Points you will earn', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_em_text_color'     => '#2e7d32',
		);
	}

	/**
	 * Saved settings merged with defaults.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_settings() {
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		return wp_parse_args( $saved, self::wps_wpr_get_default_settings() );
	}

	/**
	 * Sanitize raw (unslashed) settings input.
	 *
	 * @param  array $input raw input.
	 * @return array
	 */
	public static function wps_wpr_sanitize_settings( $input ) {
		$defaults = self::wps_wpr_get_default_settings();
		$output   = array();
		foreach ( array( 'wps_wpr_em_enable_product', 'wps_wpr_em_enable_shop', 'wps_wpr_em_enable_cart', 'wps_wpr_em_show_guests' ) as $key ) {
			$output[ $key ] = ( ! empty( $input[ $key ] ) && 'yes' === $input[ $key ] ) ? 'yes' : 'no';
		}
		foreach ( array( 'wps_wpr_em_product_text', 'wps_wpr_em_shop_text', 'wps_wpr_em_cart_text' ) as $key ) {
			$value          = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
			$output[ $key ] = '' !== trim( $value ) ? $value : $defaults[ $key ];
		}
		$color                            = isset( $input['wps_wpr_em_text_color'] ) ? sanitize_hex_color( $input['wps_wpr_em_text_color'] ) : '';
		$output['wps_wpr_em_text_color'] = ! empty( $color ) ? $color : $defaults['wps_wpr_em_text_color'];
		return $output;
	}

	/**
	 * Is a location enabled.
	 *
	 * @param  string $location product|shop|cart.
	 * @return bool
	 */
	public static function wps_wpr_is_location_enabled( $location ) {
		$settings = self::wps_wpr_get_settings();
		$key      = 'wps_wpr_em_enable_' . $location;
		return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function wps_wpr_register_hooks() {
		add_filter( 'wps_wpr_show_legacy_product_points_badge', array( $this, 'wps_wpr_hide_legacy_badge' ) );
		add_action( 'woocommerce_single_product_summary', array( $this, 'wps_wpr_render_product_message' ), 8 );
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'wps_wpr_render_shop_message' ), 20 );
		add_filter( 'woocommerce_available_variation', array( $this, 'wps_wpr_add_variation_points' ), 10, 3 );
		add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'wps_wpr_render_cart_row' ) );
		add_action( 'woocommerce_review_order_after_order_total', array( $this, 'wps_wpr_render_cart_row' ) );
		add_filter( 'render_block', array( $this, 'wps_wpr_render_cart_block_notice' ), 10, 2 );
		add_action( 'wp_ajax_wps_wpr_earn_message_cart_points', array( $this, 'wps_wpr_ajax_cart_points' ) );
		add_action( 'wp_ajax_nopriv_wps_wpr_earn_message_cart_points', array( $this, 'wps_wpr_ajax_cart_points' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wps_wpr_enqueue_scripts' ) );
	}

	/**
	 * Hide the older product points badge when the new product message is on,
	 * so the product page never shows two points messages.
	 *
	 * @param  bool $show show badge.
	 * @return bool
	 */
	public function wps_wpr_hide_legacy_badge( $show ) {
		return self::wps_wpr_is_location_enabled( 'product' ) ? false : $show;
	}

	/**
	 * Whether messages may be shown to the current visitor.
	 *
	 * @return bool
	 */
	public function wps_wpr_can_show_to_current_user() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			$settings = self::wps_wpr_get_settings();
			return 'yes' === $settings['wps_wpr_em_show_guests'];
		}
		if ( function_exists( 'wps_wpr_restrict_user_fun' ) && wps_wpr_restrict_user_fun( $user_id ) ) {
			return false;
		}
		if ( apply_filters( 'wps_wpr_allowed_user_roles_points_features', false ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Per-currency earning rate for a user: array( points, price ) or null when off.
	 *
	 * Mirrors the order earning logic: the membership-wise per-currency rate wins
	 * when it is enabled for the user's level, otherwise the global rate is used.
	 *
	 * @param  int $user_id user id (0 for guests).
	 * @return array|null
	 */
	public static function wps_wpr_get_per_currency_rate( $user_id ) {
		$coupon_settings = get_option( 'wps_wpr_coupons_gallery', array() );
		$coupon_settings = is_array( $coupon_settings ) ? $coupon_settings : array();

		if ( $user_id ) {
			$membership_level = get_user_meta( $user_id, 'membership_level', true );
			$membership       = get_option( 'wps_wpr_membership_settings', array() );
			$roles            = ( is_array( $membership ) && isset( $membership['membership_roles'] ) && is_array( $membership['membership_roles'] ) ) ? $membership['membership_roles'] : array();
			if ( ! empty( $membership_level ) && isset( $roles[ $membership_level ] ) && is_array( $roles[ $membership_level ] ) ) {
				$values = $roles[ $membership_level ];
				if ( isset( $values['wps_wpr_enable_mem_wise_per_curr'] ) && '1' === $values['wps_wpr_enable_mem_wise_per_curr'] ) {
					$price  = isset( $values['wps_wpr_membership_wise_price'] ) ? (float) $values['wps_wpr_membership_wise_price'] : 0;
					$points = isset( $values['wps_wpr_membership_wise_points'] ) ? (float) $values['wps_wpr_membership_wise_points'] : 0;
					if ( $price > 0 && $points > 0 ) {
						return array( $points, $price );
					}
				}
			}
		}

		if ( empty( $coupon_settings['wps_wpr_coupon_conversion_enable'] ) ) {
			return null;
		}

		// The plugin stores "points per X" in the *_price key and the currency amount in the *_points key.
		$points = ! empty( $coupon_settings['wps_wpr_coupon_conversion_price'] ) ? (float) $coupon_settings['wps_wpr_coupon_conversion_price'] : 1;
		$price  = ! empty( $coupon_settings['wps_wpr_coupon_conversion_points'] ) ? (float) $coupon_settings['wps_wpr_coupon_conversion_points'] : 1;
		return array( $points, $price );
	}

	/**
	 * Points earned on an amount through per-currency earning.
	 *
	 * @param  float $amount  amount.
	 * @param  int   $user_id user id.
	 * @return int
	 */
	public static function wps_wpr_get_per_currency_points( $amount, $user_id ) {
		$amount = (float) $amount;
		$rate   = self::wps_wpr_get_per_currency_rate( $user_id );
		if ( $amount <= 0 || null === $rate ) {
			return 0;
		}
		list( $points, $price ) = $rate;
		$calculated             = round( ( $amount * $points ) / $price );
		$calculated             = apply_filters( 'wps_round_down_cart_total_value', $calculated, $amount, $points, $price );
		return max( 0, (int) $calculated );
	}

	/**
	 * Product-assigned points for a simple product or a single variation.
	 *
	 * @param  WC_Product $product product or variation.
	 * @return int
	 */
	public static function wps_wpr_get_assigned_points( $product ) {
		$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		if ( 'yes' !== get_post_meta( $parent_id, 'wps_product_points_enable', true ) ) {
			return 0;
		}
		if ( $product->is_type( 'variation' ) ) {
			return absint( get_post_meta( $product->get_id(), 'wps_wpr_variable_points', true ) );
		}
		return absint( get_post_meta( $product->get_id(), 'wps_points_product_value', true ) );
	}

	/**
	 * Points a customer earns for one unit of a simple product or a variation.
	 *
	 * @param  WC_Product $product product or variation.
	 * @param  int|null   $user_id user id; defaults to the current user.
	 * @return int
	 */
	public static function wps_wpr_get_points_for_product( $product, $user_id = null ) {
		if ( ! $product instanceof WC_Product ) {
			return 0;
		}
		$user_id = null === $user_id ? get_current_user_id() : absint( $user_id );
		$price   = (float) apply_filters( 'wps_wpr_earn_message_product_price', wc_get_price_to_display( $product ), $product );
		$points  = self::wps_wpr_get_assigned_points( $product ) + self::wps_wpr_get_per_currency_points( $price, $user_id );
		return (int) apply_filters( 'wps_wpr_earn_message_product_points', $points, $product, $user_id );
	}

	/**
	 * Points range for any product: array( min, max ). Variable products use their visible variations.
	 *
	 * @param  WC_Product $product product.
	 * @param  int|null   $user_id user id.
	 * @return array
	 */
	public static function wps_wpr_get_points_range( $product, $user_id = null ) {
		if ( ! $product instanceof WC_Product ) {
			return array( 0, 0 );
		}
		if ( $product->is_type( 'variable' ) ) {
			$points = array();
			foreach ( $product->get_visible_children() as $variation_id ) {
				$variation = wc_get_product( $variation_id );
				if ( $variation && $variation->is_purchasable() ) {
					$points[] = self::wps_wpr_get_points_for_product( $variation, $user_id );
				}
			}
			return empty( $points ) ? array( 0, 0 ) : array( min( $points ), max( $points ) );
		}
		$points = self::wps_wpr_get_points_for_product( $product, $user_id );
		return array( $points, $points );
	}

	/**
	 * Format a range as text ("50" or "40 - 60").
	 *
	 * @param  array $range array( min, max ).
	 * @return string
	 */
	public static function wps_wpr_format_range( $range ) {
		list( $min, $max ) = $range;
		if ( $min === $max ) {
			return number_format_i18n( $min );
		}
		/* translators: 1: minimum points, 2: maximum points */
		return sprintf( __( '%1$s - %2$s', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $min ), number_format_i18n( $max ) );
	}

	/**
	 * Build the message HTML.
	 *
	 * @param  string $template   template with [POINTS].
	 * @param  string $points     points text.
	 * @param  string $location   product|shop.
	 * @param  array  $data_attrs extra data attributes.
	 * @return string
	 */
	public static function wps_wpr_build_message( $template, $points, $location, $data_attrs = array() ) {
		$settings = self::wps_wpr_get_settings();
		$parts    = explode( '[POINTS]', $template );
		$html     = '';
		foreach ( $parts as $index => $part ) {
			if ( $index > 0 ) {
				$html .= '<span class="wps_wpr_earn_message_points">' . esc_html( $points ) . '</span>';
			}
			$html .= esc_html( $part );
		}
		$attrs = '';
		foreach ( $data_attrs as $name => $value ) {
			$attrs .= ' data-' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}
		return '<div class="wps_wpr_earn_message wps_wpr_earn_message_' . esc_attr( $location ) . '" style="color:' . esc_attr( $settings['wps_wpr_em_text_color'] ) . '"' . $attrs . '>' . $html . '</div>';
	}

	/**
	 * Product page message.
	 *
	 * @return void
	 */
	public function wps_wpr_render_product_message() {
		if ( ! self::wps_wpr_is_location_enabled( 'product' ) || ! $this->wps_wpr_can_show_to_current_user() ) {
			return;
		}
		global $product;
		$product = $product instanceof WC_Product ? $product : wc_get_product( get_the_ID() );
		if ( ! $product ) {
			return;
		}
		$range = self::wps_wpr_get_points_range( $product );
		if ( $range[1] <= 0 ) {
			return;
		}
		$settings = self::wps_wpr_get_settings();
		$text     = self::wps_wpr_format_range( $range );
		echo wp_kses_post(
			self::wps_wpr_build_message(
				$settings['wps_wpr_em_product_text'],
				$text,
				'product',
				array( 'default-points' => $text )
			)
		);
	}

	/**
	 * Shop / category loop message.
	 *
	 * @return void
	 */
	public function wps_wpr_render_shop_message() {
		if ( ! self::wps_wpr_is_location_enabled( 'shop' ) || ! $this->wps_wpr_can_show_to_current_user() ) {
			return;
		}
		// Pro already prints assigned points in the loop when its own option is on; avoid a second message.
		if ( $this->wps_wpr_pro_shows_loop_points() ) {
			return;
		}
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$range = self::wps_wpr_get_points_range( $product );
		if ( $range[1] <= 0 ) {
			return;
		}
		$settings = self::wps_wpr_get_settings();
		echo wp_kses_post( self::wps_wpr_build_message( $settings['wps_wpr_em_shop_text'], self::wps_wpr_format_range( $range ), 'shop' ) );
	}

	/**
	 * Whether the pro plugin prints product points on the shop loop itself.
	 *
	 * @return bool
	 */
	public function wps_wpr_pro_shows_loop_points() {
		if ( ! ( function_exists( 'wps_wpr_is_active' ) && wps_wpr_is_active() ) ) {
			return false;
		}
		$pro_settings = get_option( 'wps_wpr_assign_products_points', array() );
		return is_array( $pro_settings ) && isset( $pro_settings['wps_wpr_show_assign_points_on_shop_page'] ) && '1' === (string) $pro_settings['wps_wpr_show_assign_points_on_shop_page'];
	}

	/**
	 * Add earn points to the variation data used by the product page script.
	 *
	 * @param  array                $data      variation data.
	 * @param  WC_Product           $product   parent product.
	 * @param  WC_Product_Variation $variation variation.
	 * @return array
	 */
	public function wps_wpr_add_variation_points( $data, $product, $variation ) {
		if ( self::wps_wpr_is_location_enabled( 'product' ) && $this->wps_wpr_can_show_to_current_user() ) {
			$data['wps_wpr_earn_points'] = number_format_i18n( self::wps_wpr_get_points_for_product( $variation ) );
		}
		return $data;
	}

	/**
	 * Points the current cart would earn.
	 *
	 * @param  WC_Cart  $cart    cart.
	 * @param  int|null $user_id user id.
	 * @return int
	 */
	public static function wps_wpr_get_cart_points( $cart, $user_id = null ) {
		if ( ! $cart instanceof WC_Cart ) {
			return 0;
		}
		$user_id  = null === $user_id ? get_current_user_id() : absint( $user_id );
		$assigned = 0;
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
				continue;
			}
			$assigned += self::wps_wpr_get_assigned_points( $item['data'] ) * absint( $item['quantity'] );
		}
		$total  = (float) apply_filters( 'wps_wpr_earn_message_cart_total', (float) $cart->get_total( 'edit' ), $cart );
		$points = $assigned + self::wps_wpr_get_per_currency_points( $total, $user_id );
		return (int) apply_filters( 'wps_wpr_earn_message_cart_points', $points, $cart, $user_id );
	}

	/**
	 * Whether the cart / checkout message should show for the current visitor.
	 *
	 * @return bool
	 */
	public function wps_wpr_should_show_cart_message() {
		if ( ! self::wps_wpr_is_location_enabled( 'cart' ) || ! $this->wps_wpr_can_show_to_current_user() ) {
			return false;
		}
		// Pro shows its own "total earning points" notice on the cart.
		if ( has_action( 'wps_wpr_show_total_earning_points' ) ) {
			return false;
		}
		return function_exists( 'WC' ) && WC()->cart;
	}

	/**
	 * Points text for the cart ("50 points").
	 *
	 * @param  int $points points.
	 * @return string
	 */
	public static function wps_wpr_format_points( $points ) {
		/* translators: %s: points */
		return sprintf( _n( '%s point', '%s points', $points, 'points-and-rewards-for-woocommerce' ), number_format_i18n( $points ) );
	}

	/**
	 * Notice above the block-based Cart and Checkout. The script keeps it in sync as the cart changes.
	 *
	 * @param  string $block_content block html.
	 * @param  array  $block         block data.
	 * @return string
	 */
	public function wps_wpr_render_cart_block_notice( $block_content, $block ) {
		if ( empty( $block['blockName'] ) || ! in_array( $block['blockName'], array( 'woocommerce/cart', 'woocommerce/checkout' ), true ) ) {
			return $block_content;
		}
		if ( is_admin() || ! $this->wps_wpr_should_show_cart_message() ) {
			return $block_content;
		}
		$settings = self::wps_wpr_get_settings();
		$points   = self::wps_wpr_get_cart_points( WC()->cart );
		$notice   = '<div class="wps_wpr_earn_message wps_wpr_earn_message_cart_block" style="color:' . esc_attr( $settings['wps_wpr_em_text_color'] ) . ';' . ( $points > 0 ? '' : 'display:none;' ) . '">'
			. esc_html( $settings['wps_wpr_em_cart_text'] ) . ': <span class="wps_wpr_earn_message_points">' . esc_html( self::wps_wpr_format_points( $points ) ) . '</span></div>';
		return $notice . $block_content;
	}

	/**
	 * AJAX: points the current visitor's cart would earn (read only).
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_cart_points() {
		if ( function_exists( 'wc_load_cart' ) && function_exists( 'WC' ) && ! WC()->cart ) {
			wc_load_cart();
		}
		if ( ! $this->wps_wpr_should_show_cart_message() ) {
			wp_send_json_success( array( 'points' => 0, 'text' => '' ) );
		}
		WC()->cart->calculate_totals();
		$points = self::wps_wpr_get_cart_points( WC()->cart );
		wp_send_json_success(
			array(
				'points' => $points,
				'text'   => self::wps_wpr_format_points( $points ),
			)
		);
	}

	/**
	 * Cart / checkout totals row (classic shortcode cart and checkout).
	 *
	 * @return void
	 */
	public function wps_wpr_render_cart_row() {
		if ( ! $this->wps_wpr_should_show_cart_message() ) {
			return;
		}
		$points = self::wps_wpr_get_cart_points( WC()->cart );
		if ( $points <= 0 ) {
			return;
		}
		$settings = self::wps_wpr_get_settings();
		?>
		<tr class="wps_wpr_earn_message_cart">
			<th><?php echo esc_html( $settings['wps_wpr_em_cart_text'] ); ?></th>
			<td data-title="<?php echo esc_attr( $settings['wps_wpr_em_cart_text'] ); ?>" style="color:<?php echo esc_attr( $settings['wps_wpr_em_text_color'] ); ?>">
				<?php echo esc_html( self::wps_wpr_format_points( $points ) ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Enqueue the script on product pages (variation updates) and block cart / checkout (live totals).
	 *
	 * @return void
	 */
	public function wps_wpr_enqueue_scripts() {
		if ( ! function_exists( 'is_product' ) ) {
			return;
		}
		$on_product = is_product() && self::wps_wpr_is_location_enabled( 'product' );
		$on_cart    = ( is_cart() || is_checkout() ) && self::wps_wpr_is_location_enabled( 'cart' );
		if ( ! $on_product && ! $on_cart ) {
			return;
		}
		wp_enqueue_script(
			'wps-wpr-earn-messages',
			WPS_RWPR_DIR_URL . 'public/js/wps-wpr-earn-messages.js',
			array( 'jquery' ),
			defined( 'REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION' ) ? REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION : '1.0.0',
			true
		);
		wp_localize_script(
			'wps-wpr-earn-messages',
			'wps_wpr_earn_messages',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'action'   => 'wps_wpr_earn_message_cart_points',
			)
		);
	}
}
