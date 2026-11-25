<?php
/**
 * Reward catalog.
 *
 * Admins define rewards (fixed discount, percentage discount, free product,
 * free shipping) that customers redeem for points from My Account. Each
 * redemption creates a single-use WooCommerce coupon locked to the
 * customer's email.
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
 * Reward catalog feature.
 */
class WPS_WPR_Reward_Catalog {

	/**
	 * Settings option.
	 */
	const OPTION = 'wps_wpr_reward_catalog';

	/**
	 * Points log key.
	 */
	const LOG_KEY = 'reward_catalog_redeemed';

	/**
	 * User meta holding the customer's redemptions.
	 */
	const REDEMPTIONS_META = 'wps_wpr_reward_redemptions';

	/**
	 * Ajax action / nonce action for redeeming.
	 */
	const AJAX_ACTION = 'wps_wpr_redeem_reward';

	/**
	 * Query arg used by the "apply to cart" link.
	 */
	const APPLY_ARG = 'wps_wpr_apply_reward';

	/**
	 * Reward types.
	 *
	 * @return array type => label.
	 */
	public static function wps_wpr_get_reward_types() {
		return apply_filters(
			'wps_wpr_reward_catalog_types',
			array(
				'fixed_cart'    => __( 'Fixed cart discount', 'points-and-rewards-for-woocommerce' ),
				'percent'       => __( 'Percentage discount', 'points-and-rewards-for-woocommerce' ),
				'free_product'  => __( 'Free product', 'points-and-rewards-for-woocommerce' ),
				'free_shipping' => __( 'Free shipping', 'points-and-rewards-for-woocommerce' ),
			)
		);
	}

	/**
	 * Default values for one reward.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_reward_defaults() {
		return array(
			'id'          => '',
			'title'       => '',
			'description' => '',
			'points'      => 0,
			'type'        => 'fixed_cart',
			'amount'      => 0,
			'product_id'  => 0,
			'min_amount'  => 0,
			'expiry_days' => 0,
			'user_limit'  => 0,
			'active'      => 'yes',
		);
	}

	/**
	 * Saved settings.
	 *
	 * @return array { enable: yes|no, rewards: array }.
	 */
	public static function wps_wpr_get_settings() {
		$settings = get_option( self::OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();
		$settings = wp_parse_args(
			$settings,
			array(
				'enable'  => 'no',
				'rewards' => array(),
			)
		);

		$rewards = array();
		foreach ( (array) $settings['rewards'] as $reward ) {
			if ( is_array( $reward ) && ! empty( $reward['id'] ) ) {
				$rewards[ $reward['id'] ] = wp_parse_args( $reward, self::wps_wpr_get_reward_defaults() );
			}
		}
		$settings['rewards'] = $rewards;
		return $settings;
	}

	/**
	 * Whether the catalog is switched on.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_enabled() {
		$settings = self::wps_wpr_get_settings();
		return 'yes' === $settings['enable'];
	}

	/**
	 * Sanitize one submitted reward. Returns null for an empty / invalid row.
	 *
	 * @param  array $raw raw reward data.
	 * @return array|null
	 */
	public static function wps_wpr_sanitize_reward( $raw ) {
		if ( ! is_array( $raw ) ) {
			return null;
		}

		$reward          = self::wps_wpr_get_reward_defaults();
		$reward['title'] = isset( $raw['title'] ) ? sanitize_text_field( $raw['title'] ) : '';
		if ( '' === $reward['title'] ) {
			return null;
		}

		$types                 = self::wps_wpr_get_reward_types();
		$reward['id']          = ! empty( $raw['id'] ) ? sanitize_key( $raw['id'] ) : 'rwd' . strtolower( wp_generate_password( 10, false ) );
		$reward['description'] = isset( $raw['description'] ) ? sanitize_textarea_field( $raw['description'] ) : '';
		$reward['points']      = isset( $raw['points'] ) ? absint( $raw['points'] ) : 0;
		$reward['type']        = isset( $raw['type'] ) && isset( $types[ $raw['type'] ] ) ? sanitize_key( $raw['type'] ) : 'fixed_cart';
		$reward['amount']      = isset( $raw['amount'] ) ? max( 0, (float) wc_format_decimal( $raw['amount'] ) ) : 0;
		$reward['product_id']  = isset( $raw['product_id'] ) ? absint( $raw['product_id'] ) : 0;
		$reward['min_amount']  = isset( $raw['min_amount'] ) ? max( 0, (float) wc_format_decimal( $raw['min_amount'] ) ) : 0;
		$reward['expiry_days'] = isset( $raw['expiry_days'] ) ? absint( $raw['expiry_days'] ) : 0;
		$reward['user_limit']  = isset( $raw['user_limit'] ) ? absint( $raw['user_limit'] ) : 0;
		$reward['active']      = ! empty( $raw['active'] ) && 'yes' === $raw['active'] ? 'yes' : 'no';

		if ( 'percent' === $reward['type'] ) {
			$reward['amount'] = (float) min( 100, $reward['amount'] );
		}
		if ( in_array( $reward['type'], array( 'free_product', 'free_shipping' ), true ) ) {
			$reward['amount'] = 0;
		}
		if ( 'free_product' !== $reward['type'] ) {
			$reward['product_id'] = 0;
		}

		return apply_filters( 'wps_wpr_reward_catalog_sanitize_reward', $reward, $raw );
	}

	/**
	 * Save settings from a submitted rewards array.
	 *
	 * @param  string $enable  yes|no.
	 * @param  array  $rewards raw rewards rows.
	 * @return array saved settings.
	 */
	public static function wps_wpr_save_settings( $enable, $rewards ) {
		$clean = array();
		foreach ( (array) $rewards as $raw ) {
			$reward = self::wps_wpr_sanitize_reward( $raw );
			if ( $reward ) {
				$clean[ $reward['id'] ] = $reward;
			}
		}
		$settings = array(
			'enable'  => 'yes' === $enable ? 'yes' : 'no',
			'rewards' => $clean,
		);
		update_option( self::OPTION, $settings );
		return $settings;
	}

	/**
	 * Is the reward usable: active, priced and correctly configured.
	 *
	 * @param  array $reward reward.
	 * @return bool
	 */
	public static function wps_wpr_is_reward_available( $reward ) {
		if ( empty( $reward['id'] ) || 'yes' !== $reward['active'] || absint( $reward['points'] ) <= 0 ) {
			return false;
		}
		switch ( $reward['type'] ) {
			case 'fixed_cart':
			case 'percent':
				$valid = (float) $reward['amount'] > 0;
				break;
			case 'free_product':
				$product = wc_get_product( absint( $reward['product_id'] ) );
				$valid   = $product && $product->is_purchasable();
				break;
			default:
				$valid = true;
		}
		return (bool) apply_filters( 'wps_wpr_reward_catalog_reward_available', $valid, $reward );
	}

	/**
	 * Rewards the customer can see.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_available_rewards() {
		$settings = self::wps_wpr_get_settings();
		$rewards  = array_filter( $settings['rewards'], array( __CLASS__, 'wps_wpr_is_reward_available' ) );
		return apply_filters( 'wps_wpr_reward_catalog_available_rewards', $rewards );
	}

	/**
	 * Customer's redemptions.
	 *
	 * @param  int $user_id user id.
	 * @return array
	 */
	public static function wps_wpr_get_user_redemptions( $user_id ) {
		$redemptions = get_user_meta( $user_id, self::REDEMPTIONS_META, true );
		return is_array( $redemptions ) ? $redemptions : array();
	}

	/**
	 * How many times the customer has redeemed a reward.
	 *
	 * @param  int    $user_id   user id.
	 * @param  string $reward_id reward id.
	 * @return int
	 */
	public static function wps_wpr_count_user_redemptions( $user_id, $reward_id ) {
		$count = 0;
		foreach ( self::wps_wpr_get_user_redemptions( $user_id ) as $redemption ) {
			if ( isset( $redemption['reward_id'] ) && $redemption['reward_id'] === $reward_id ) {
				++$count;
			}
		}
		return $count;
	}

	/**
	 * Generate an unused coupon code.
	 *
	 * @return string
	 */
	public static function wps_wpr_generate_coupon_code() {
		do {
			$code = apply_filters( 'wps_wpr_reward_catalog_coupon_code', 'RWD-' . strtoupper( wp_generate_password( 8, false ) ) );
		} while ( wc_get_coupon_id_by_code( $code ) );
		return $code;
	}

	/**
	 * Build (and save) the coupon for a reward.
	 *
	 * @param  array   $reward reward.
	 * @param  WP_User $user   customer.
	 * @return WC_Coupon|WP_Error
	 */
	public static function wps_wpr_create_reward_coupon( $reward, $user ) {
		$coupon = new WC_Coupon();
		$coupon->set_code( self::wps_wpr_generate_coupon_code() );
		/* translators: %s: reward title */
		$coupon->set_description( sprintf( __( 'Points reward: %s', 'points-and-rewards-for-woocommerce' ), $reward['title'] ) );
		$coupon->set_usage_limit( 1 );
		$coupon->set_usage_limit_per_user( 1 );
		$coupon->set_individual_use( false );
		$coupon->set_email_restrictions( array( $user->user_email ) );

		if ( (float) $reward['min_amount'] > 0 ) {
			$coupon->set_minimum_amount( wc_format_decimal( $reward['min_amount'] ) );
		}
		if ( absint( $reward['expiry_days'] ) > 0 ) {
			$coupon->set_date_expires( time() + absint( $reward['expiry_days'] ) * DAY_IN_SECONDS );
		}

		switch ( $reward['type'] ) {
			case 'fixed_cart':
				$coupon->set_discount_type( 'fixed_cart' );
				$coupon->set_amount( wc_format_decimal( $reward['amount'] ) );
				break;
			case 'percent':
				$coupon->set_discount_type( 'percent' );
				$coupon->set_amount( wc_format_decimal( $reward['amount'] ) );
				break;
			case 'free_product':
				$coupon->set_discount_type( 'percent' );
				$coupon->set_amount( 100 );
				$coupon->set_product_ids( array( absint( $reward['product_id'] ) ) );
				$coupon->set_limit_usage_to_x_items( 1 );
				break;
			case 'free_shipping':
				$coupon->set_discount_type( 'fixed_cart' );
				$coupon->set_amount( 0 );
				$coupon->set_free_shipping( true );
				break;
			default:
				/**
				 * Configure a coupon for a custom reward type (registered via wps_wpr_reward_catalog_types).
				 *
				 * @param WC_Coupon $coupon coupon.
				 * @param array     $reward reward.
				 */
				do_action( 'wps_wpr_reward_catalog_build_coupon_' . $reward['type'], $coupon, $reward );
		}

		$coupon->update_meta_data( '_wps_wpr_reward_id', $reward['id'] );
		$coupon->update_meta_data( '_wps_wpr_reward_user', $user->ID );

		$coupon = apply_filters( 'wps_wpr_reward_catalog_coupon', $coupon, $reward, $user );
		if ( ! $coupon instanceof WC_Coupon ) {
			return new WP_Error( 'wps_wpr_coupon', __( 'Could not create the reward coupon.', 'points-and-rewards-for-woocommerce' ) );
		}

		$coupon_id = $coupon->save();
		if ( ! $coupon_id ) {
			return new WP_Error( 'wps_wpr_coupon', __( 'Could not create the reward coupon.', 'points-and-rewards-for-woocommerce' ) );
		}
		return $coupon;
	}

	/**
	 * Take a short per-user lock so two requests cannot spend the same points.
	 *
	 * @param  int $user_id user id.
	 * @return bool
	 */
	private static function wps_wpr_acquire_lock( $user_id ) {
		$key = 'wps_wpr_reward_lock_' . $user_id;
		if ( add_option( $key, time(), '', 'no' ) ) {
			return true;
		}
		// Recover from a stale lock.
		if ( (int) get_option( $key ) < time() - 30 ) {
			delete_option( $key );
			return add_option( $key, time(), '', 'no' );
		}
		return false;
	}

	/**
	 * Release the per-user lock.
	 *
	 * @param int $user_id user id.
	 */
	private static function wps_wpr_release_lock( $user_id ) {
		delete_option( 'wps_wpr_reward_lock_' . $user_id );
	}

	/**
	 * Redeem a reward for a customer.
	 *
	 * @param  int    $user_id   user id.
	 * @param  string $reward_id reward id.
	 * @return array|WP_Error redemption record.
	 */
	public static function wps_wpr_redeem( $user_id, $reward_id ) {
		$user_id = absint( $user_id );
		$user    = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error( 'wps_wpr_login', __( 'Please log in to redeem rewards.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( ! self::wps_wpr_is_enabled() ) {
			return new WP_Error( 'wps_wpr_disabled', __( 'The reward catalog is not available.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( function_exists( 'wps_wpr_restrict_user_fun' ) && wps_wpr_restrict_user_fun( $user_id ) ) {
			return new WP_Error( 'wps_wpr_restricted', __( 'Your account cannot redeem points.', 'points-and-rewards-for-woocommerce' ) );
		}

		$rewards = self::wps_wpr_get_available_rewards();
		if ( empty( $rewards[ $reward_id ] ) ) {
			return new WP_Error( 'wps_wpr_reward', __( 'This reward is not available.', 'points-and-rewards-for-woocommerce' ) );
		}
		$reward = $rewards[ $reward_id ];
		$cost   = absint( $reward['points'] );

		if ( ! self::wps_wpr_acquire_lock( $user_id ) ) {
			return new WP_Error( 'wps_wpr_busy', __( 'Another request is in progress. Please try again.', 'points-and-rewards-for-woocommerce' ) );
		}

		$limit = absint( $reward['user_limit'] );
		if ( $limit > 0 && self::wps_wpr_count_user_redemptions( $user_id, $reward_id ) >= $limit ) {
			self::wps_wpr_release_lock( $user_id );
			return new WP_Error( 'wps_wpr_limit', __( 'You have reached the redemption limit for this reward.', 'points-and-rewards-for-woocommerce' ) );
		}

		wp_cache_delete( $user_id, 'user_meta' );
		$balance = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
		if ( $balance < $cost ) {
			self::wps_wpr_release_lock( $user_id );
			return new WP_Error( 'wps_wpr_points', __( 'You do not have enough points for this reward.', 'points-and-rewards-for-woocommerce' ) );
		}

		$coupon = self::wps_wpr_create_reward_coupon( $reward, $user );
		if ( is_wp_error( $coupon ) ) {
			self::wps_wpr_release_lock( $user_id );
			return $coupon;
		}

		$remaining = $balance - $cost;
		update_user_meta( $user_id, 'wps_wpr_points', $remaining );

		$redemption = array(
			'reward_id' => $reward['id'],
			'title'     => $reward['title'],
			'type'      => $reward['type'],
			'points'    => $cost,
			'coupon_id' => $coupon->get_id(),
			'code'      => $coupon->get_code(),
			'date'      => time(),
		);
		$redemptions   = self::wps_wpr_get_user_redemptions( $user_id );
		$redemptions[] = $redemption;
		update_user_meta( $user_id, self::REDEMPTIONS_META, $redemptions );

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ self::LOG_KEY ][] = array(
			self::LOG_KEY => $cost,
			'date'        => date_i18n( 'Y-m-d h:i:sa' ),
			'reward'      => $reward['title'],
			'coupon'      => $coupon->get_code(),
		);
		update_user_meta( $user_id, 'points_details', $points_log );

		self::wps_wpr_release_lock( $user_id );

		do_action( 'wps_wpr_update_points_log', $user_id );
		do_action( 'wps_wpr_reward_catalog_redeemed', $user_id, $reward, $coupon, $remaining );

		$redemption['balance'] = $remaining;
		return $redemption;
	}

	/**
	 * Coupon code as shown to customers. WooCommerce stores codes in lower
	 * case and matches them case-insensitively, so upper case is just easier to read.
	 *
	 * @param  string $code coupon code.
	 * @return string
	 */
	public static function wps_wpr_display_code( $code ) {
		return wc_strtoupper( (string) $code );
	}

	/**
	 * Link that applies a reward coupon (and adds the free product) to the cart.
	 *
	 * @param  array $redemption redemption record.
	 * @return string
	 */
	public static function wps_wpr_get_apply_url( $redemption ) {
		$args = array(
			self::APPLY_ARG => rawurlencode( $redemption['code'] ),
			'_wpnonce'      => wp_create_nonce( self::APPLY_ARG ),
		);
		return add_query_arg( $args, wc_get_cart_url() );
	}

	/**
	 * Is the redemption's coupon still usable.
	 *
	 * @param  array $redemption redemption record.
	 * @return string available|used|expired|deleted.
	 */
	public static function wps_wpr_get_redemption_status( $redemption ) {
		$coupon = new WC_Coupon( absint( $redemption['coupon_id'] ) );
		if ( ! $coupon->get_id() ) {
			return 'deleted';
		}
		if ( $coupon->get_usage_limit() && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
			return 'used';
		}
		$expires = $coupon->get_date_expires();
		if ( $expires && $expires->getTimestamp() < time() ) {
			return 'expired';
		}
		return 'available';
	}

	/**
	 * Ajax: redeem a reward.
	 */
	public function wps_wpr_ajax_redeem_reward() {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to redeem rewards.', 'points-and-rewards-for-woocommerce' ) ) );
		}

		$reward_id = isset( $_POST['reward_id'] ) ? sanitize_key( wp_unslash( $_POST['reward_id'] ) ) : '';
		$result    = self::wps_wpr_redeem( $user_id, $reward_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				/* translators: %s: coupon code */
				'message'   => sprintf( __( 'Reward redeemed! Your coupon code is %s', 'points-and-rewards-for-woocommerce' ), self::wps_wpr_display_code( $result['code'] ) ),
				'code'      => self::wps_wpr_display_code( $result['code'] ),
				'apply_url' => self::wps_wpr_get_apply_url( $result ),
				'balance'   => $result['balance'],
			)
		);
	}

	/**
	 * Handle the "apply to cart" link.
	 */
	public function wps_wpr_maybe_apply_reward_coupon() {
		if ( empty( $_GET[ self::APPLY_ARG ] ) || ! is_user_logged_in() || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::APPLY_ARG ) ) {
			return;
		}

		$code   = wc_format_coupon_code( sanitize_text_field( wp_unslash( $_GET[ self::APPLY_ARG ] ) ) );
		$coupon = new WC_Coupon( $code );
		if ( ! $coupon->get_id() || absint( $coupon->get_meta( '_wps_wpr_reward_user' ) ) !== get_current_user_id() ) {
			return;
		}

		// A free product reward needs its product in the cart first.
		$product_ids = $coupon->get_product_ids();
		if ( $coupon->get_meta( '_wps_wpr_reward_id' ) && 1 === count( $product_ids ) && 100 === (int) $coupon->get_amount() ) {
			$product_id = absint( $product_ids[0] );
			$in_cart    = false;
			foreach ( WC()->cart->get_cart() as $item ) {
				if ( absint( $item['product_id'] ) === $product_id || absint( $item['variation_id'] ) === $product_id ) {
					$in_cart = true;
					break;
				}
			}
			if ( ! $in_cart ) {
				WC()->cart->add_to_cart( $product_id );
			}
		}

		if ( ! WC()->cart->has_discount( $code ) ) {
			WC()->cart->apply_coupon( $code );
		}

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	/**
	 * Enqueue the customer script on the My Account page.
	 */
	public function wps_wpr_enqueue_scripts() {
		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_user_logged_in() || ! self::wps_wpr_is_enabled() ) {
			return;
		}
		wp_enqueue_style( 'wps-wpr-reward-catalog', WPS_RWPR_DIR_URL . 'public/css/wps-wpr-reward-catalog.css', array(), REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION );
		wp_enqueue_script( 'wps-wpr-reward-catalog', WPS_RWPR_DIR_URL . 'public/js/wps-wpr-reward-catalog.js', array(), REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION, true );
		wp_localize_script(
			'wps-wpr-reward-catalog',
			'wpsWprRewardCatalog',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::AJAX_ACTION ),
				'action'  => self::AJAX_ACTION,
				'i18n'    => array(
					'confirm' => __( 'Redeem this reward for %s points?', 'points-and-rewards-for-woocommerce' ),
					'apply'   => __( 'Apply to cart', 'points-and-rewards-for-woocommerce' ),
					'error'   => __( 'Something went wrong. Please try again.', 'points-and-rewards-for-woocommerce' ),
				),
			)
		);
	}

	/**
	 * Render the catalog in the My Account points tab.
	 *
	 * @param int $user_id user id.
	 */
	public function wps_wpr_render_catalog( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! self::wps_wpr_is_enabled() ) {
			return;
		}
		echo self::wps_wpr_get_catalog_html( $user_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
	}

	/**
	 * Catalog markup.
	 *
	 * @param  int $user_id user id.
	 * @return string
	 */
	public static function wps_wpr_get_catalog_html( $user_id ) {
		$rewards     = self::wps_wpr_get_available_rewards();
		$redemptions = array_reverse( self::wps_wpr_get_user_redemptions( $user_id ) );
		$balance     = absint( get_user_meta( $user_id, 'wps_wpr_points', true ) );
		$types       = self::wps_wpr_get_reward_types();
		$restricted  = function_exists( 'wps_wpr_restrict_user_fun' ) && wps_wpr_restrict_user_fun( $user_id );

		ob_start();
		?>
		<div class="wps-wpr-reward-catalog" id="wps-wpr-reward-catalog">
			<h3 class="wps-wpr-reward-catalog__title"><?php esc_html_e( 'Rewards', 'points-and-rewards-for-woocommerce' ); ?></h3>
			<div class="wps-wpr-reward-catalog__notice" role="status" aria-live="polite"></div>
			<?php if ( empty( $rewards ) ) : ?>
				<p><?php esc_html_e( 'No rewards are available right now.', 'points-and-rewards-for-woocommerce' ); ?></p>
			<?php else : ?>
				<ul class="wps-wpr-reward-catalog__list">
					<?php
					foreach ( $rewards as $reward ) :
						$limit     = absint( $reward['user_limit'] );
						$used      = self::wps_wpr_count_user_redemptions( $user_id, $reward['id'] );
						$limit_hit = $limit > 0 && $used >= $limit;
						$disabled  = $restricted || $limit_hit || $balance < absint( $reward['points'] );
						?>
						<li class="wps-wpr-reward-card" data-reward="<?php echo esc_attr( $reward['id'] ); ?>">
							<span class="wps-wpr-reward-card__type"><?php echo esc_html( isset( $types[ $reward['type'] ] ) ? $types[ $reward['type'] ] : $reward['type'] ); ?></span>
							<strong class="wps-wpr-reward-card__name"><?php echo esc_html( $reward['title'] ); ?></strong>
							<?php if ( '' !== $reward['description'] ) : ?>
								<span class="wps-wpr-reward-card__desc"><?php echo esc_html( $reward['description'] ); ?></span>
							<?php endif; ?>
							<span class="wps-wpr-reward-card__cost">
								<?php
								/* translators: %s: points */
								echo esc_html( sprintf( _n( '%s point', '%s points', absint( $reward['points'] ), 'points-and-rewards-for-woocommerce' ), number_format_i18n( absint( $reward['points'] ) ) ) );
								?>
							</span>
							<button type="button" class="button wps-wpr-reward-card__redeem" data-reward="<?php echo esc_attr( $reward['id'] ); ?>" data-points="<?php echo esc_attr( absint( $reward['points'] ) ); ?>" <?php disabled( $disabled ); ?>>
								<?php
								if ( $limit_hit ) {
									esc_html_e( 'Limit reached', 'points-and-rewards-for-woocommerce' );
								} elseif ( $balance < absint( $reward['points'] ) ) {
									esc_html_e( 'Not enough points', 'points-and-rewards-for-woocommerce' );
								} else {
									esc_html_e( 'Redeem', 'points-and-rewards-for-woocommerce' );
								}
								?>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( ! empty( $redemptions ) ) : ?>
				<h4 class="wps-wpr-reward-catalog__subtitle"><?php esc_html_e( 'Your reward coupons', 'points-and-rewards-for-woocommerce' ); ?></h4>
				<table class="shop_table shop_table_responsive wps-wpr-reward-catalog__coupons">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Reward', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Coupon code', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Status', 'points-and-rewards-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$labels = array(
							'available' => __( 'Available', 'points-and-rewards-for-woocommerce' ),
							'used'      => __( 'Used', 'points-and-rewards-for-woocommerce' ),
							'expired'   => __( 'Expired', 'points-and-rewards-for-woocommerce' ),
							'deleted'   => __( 'Unavailable', 'points-and-rewards-for-woocommerce' ),
						);
						foreach ( $redemptions as $redemption ) :
							$status = self::wps_wpr_get_redemption_status( $redemption );
							?>
							<tr>
								<td data-title="<?php esc_attr_e( 'Reward', 'points-and-rewards-for-woocommerce' ); ?>"><?php echo esc_html( $redemption['title'] ); ?></td>
								<td data-title="<?php esc_attr_e( 'Coupon code', 'points-and-rewards-for-woocommerce' ); ?>"><code><?php echo esc_html( self::wps_wpr_display_code( $redemption['code'] ) ); ?></code></td>
								<td data-title="<?php esc_attr_e( 'Points', 'points-and-rewards-for-woocommerce' ); ?>"><?php echo esc_html( '-' . absint( $redemption['points'] ) ); ?></td>
								<td data-title="<?php esc_attr_e( 'Status', 'points-and-rewards-for-woocommerce' ); ?>">
									<?php if ( 'available' === $status ) : ?>
										<a href="<?php echo esc_url( self::wps_wpr_get_apply_url( $redemption ) ); ?>"><?php esc_html_e( 'Apply to cart', 'points-and-rewards-for-woocommerce' ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $labels[ $status ] ); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Enqueue the admin assets on the reward catalog tab.
	 *
	 * @param string $hook admin page hook.
	 */
	public function wps_wpr_admin_enqueue_scripts( $hook ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab check.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'reward-catalog' !== $tab || false === strpos( (string) $hook, 'wps-rwpr-setting' ) ) {
			return;
		}
		wp_enqueue_script( 'wc-enhanced-select' );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_script( 'wps-wpr-reward-catalog-admin', WPS_RWPR_DIR_URL . 'admin/js/wps-wpr-reward-catalog-admin.js', array( 'jquery', 'wc-enhanced-select' ), REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION, true );
	}
}
