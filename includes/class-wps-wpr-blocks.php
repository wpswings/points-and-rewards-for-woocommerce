<?php
/**
 * Native Gutenberg blocks pack.
 *
 * Registers server-side rendered (dynamic) blocks for the points program:
 * points balance, points log, referral link, ways to earn, membership level
 * and a points leaderboard. Each block lives in blocks/<name>/block.json.
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
 * Class WPS_WPR_Blocks.
 */
class WPS_WPR_Blocks {

	/**
	 * Block category slug.
	 */
	const CATEGORY = 'wps-points-and-rewards';

	/**
	 * Editor script handle.
	 */
	const EDITOR_HANDLE = 'wps-wpr-blocks-editor';

	/**
	 * Front-end style handle.
	 */
	const STYLE_HANDLE = 'wps-wpr-blocks';

	/**
	 * Front-end script handle.
	 */
	const VIEW_HANDLE = 'wps-wpr-blocks-view';

	/**
	 * Highest leaderboard size allowed.
	 */
	const LEADERBOARD_MAX = 50;

	/**
	 * Seconds a leaderboard query is cached.
	 */
	const LEADERBOARD_CACHE_TTL = 600;

	/**
	 * Option holding the leaderboard cache version (bumped to invalidate).
	 */
	const LEADERBOARD_CACHE_VERSION = 'wps_wpr_leaderboard_cache_version';

	/**
	 * Block folder names mapped to their render methods.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_blocks() {
		return array(
			'points-balance'   => 'wps_wpr_render_points_balance',
			'points-log'       => 'wps_wpr_render_points_log',
			'referral-link'    => 'wps_wpr_render_referral_link',
			'earn-points'      => 'wps_wpr_render_earn_points',
			'membership-level' => 'wps_wpr_render_membership_level',
			'leaderboard'      => 'wps_wpr_render_leaderboard',
		);
	}

	/**
	 * Register assets and blocks.
	 *
	 * @return void
	 */
	public function wps_wpr_register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$asset_file = WPS_RWPR_DIR_PATH . 'blocks/editor.asset.php';
		$asset      = file_exists( $asset_file ) ? include $asset_file : array(
			'dependencies' => array(),
			'version'      => REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION,
		);

		wp_register_script( self::EDITOR_HANDLE, WPS_RWPR_DIR_URL . 'blocks/editor.js', $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( self::EDITOR_HANDLE, 'points-and-rewards-for-woocommerce', WPS_RWPR_DIR_PATH . 'languages' );
		wp_register_style( self::STYLE_HANDLE, WPS_RWPR_DIR_URL . 'blocks/blocks.css', array(), $asset['version'] );
		wp_register_script( self::VIEW_HANDLE, WPS_RWPR_DIR_URL . 'blocks/view.js', array(), $asset['version'], true );

		foreach ( self::wps_wpr_get_blocks() as $folder => $callback ) {
			register_block_type(
				WPS_RWPR_DIR_PATH . 'blocks/' . $folder,
				array( 'render_callback' => array( $this, $callback ) )
			);
		}
	}

	/**
	 * Add the "Points and Rewards" block category.
	 *
	 * @param  array $categories block categories.
	 * @return array
	 */
	public function wps_wpr_block_category( $categories ) {
		foreach ( (array) $categories as $category ) {
			if ( isset( $category['slug'] ) && self::CATEGORY === $category['slug'] ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			array(
				'slug'  => self::CATEGORY,
				'title' => __( 'Points and Rewards', 'points-and-rewards-for-woocommerce' ),
				'icon'  => 'awards',
			)
		);
		return $categories;
	}

	/**
	 * Whether the points program is switched on.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_program_enabled() {
		$general_settings = get_option( 'wps_wpr_settings_gallery', array() );
		return is_array( $general_settings ) && ! empty( $general_settings['wps_wpr_general_setting_enable'] ) && 1 === (int) $general_settings['wps_wpr_general_setting_enable'];
	}

	/**
	 * Whether the current request is the editor preview (block renderer REST call).
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_editor_preview() {
		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST || ! current_user_can( 'edit_posts' ) ) {
			return false;
		}
		$route = isset( $GLOBALS['wp'] ) && $GLOBALS['wp'] instanceof WP && isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';
		return 0 === strpos( $route, '/wp/v2/block-renderer/' );
	}

	/**
	 * Wrap block markup with the block wrapper attributes.
	 *
	 * @param  string $block_class block specific class.
	 * @param  string $content     inner html (already escaped).
	 * @return string
	 */
	private function wps_wpr_wrap( $block_class, $content ) {
		wp_enqueue_style( self::STYLE_HANDLE );

		$classes = 'wps-wpr-block ' . $block_class;
		$attrs   = function_exists( 'get_block_wrapper_attributes' ) && class_exists( 'WP_Block_Supports' ) && ! empty( WP_Block_Supports::$block_to_render ) ? get_block_wrapper_attributes( array( 'class' => $classes ) ) : 'class="' . esc_attr( $classes ) . '"';

		return '<div ' . $attrs . '>' . $content . '</div>';
	}

	/**
	 * Notice shown only in the editor when a block has nothing to show.
	 *
	 * @param  string $block_class block class.
	 * @param  string $message     message.
	 * @return string
	 */
	private function wps_wpr_editor_notice( $block_class, $message ) {
		if ( ! self::wps_wpr_is_editor_preview() ) {
			return '';
		}
		return $this->wps_wpr_wrap( $block_class, '<p class="wps-wpr-block__notice">' . esc_html( $message ) . '</p>' );
	}

	/**
	 * Login prompt for guests.
	 *
	 * @param  string $block_class block class.
	 * @param  array  $attributes  block attributes.
	 * @return string
	 */
	private function wps_wpr_guest_output( $block_class, $attributes ) {
		if ( empty( $attributes['showLoginPrompt'] ) ) {
			return '';
		}

		$text = ! empty( $attributes['loginText'] ) ? $attributes['loginText'] : __( 'Log in to see your reward points.', 'points-and-rewards-for-woocommerce' );
		$url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();

		return $this->wps_wpr_wrap(
			$block_class,
			'<p class="wps-wpr-block__login"><a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a></p>'
		);
	}

	/**
	 * Render: points balance.
	 *
	 * @param  array $attributes block attributes.
	 * @return string
	 */
	public function wps_wpr_render_points_balance( $attributes ) {
		$class = 'wps-wpr-block-points-balance';
		if ( ! self::wps_wpr_is_program_enabled() ) {
			return $this->wps_wpr_editor_notice( $class, __( 'Enable the points program to show this block.', 'points-and-rewards-for-woocommerce' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $this->wps_wpr_guest_output( $class, $attributes );
		}

		$label  = isset( $attributes['label'] ) && '' !== $attributes['label'] ? $attributes['label'] : __( 'Your Points', 'points-and-rewards-for-woocommerce' );
		$points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );

		$html  = '<span class="wps-wpr-block__label">' . esc_html( $label ) . '</span> ';
		$html .= '<span class="wps-wpr-block__value">' . esc_html( number_format_i18n( $points ) ) . '</span>';

		if ( ! empty( $attributes['showLevel'] ) ) {
			$level = get_user_meta( $user_id, 'membership_level', true );
			if ( ! empty( $level ) ) {
				$html .= ' <span class="wps-wpr-block__level">' . esc_html( $level ) . '</span>';
			}
		}

		return $this->wps_wpr_wrap( $class, $html );
	}

	/**
	 * Render: points log (reuses the [SHOW_POINTS_LOG] template).
	 *
	 * @param  array $attributes block attributes.
	 * @return string
	 */
	public function wps_wpr_render_points_log( $attributes ) {
		$class = 'wps-wpr-block-points-log';
		if ( ! self::wps_wpr_is_program_enabled() ) {
			return $this->wps_wpr_editor_notice( $class, __( 'Enable the points program to show this block.', 'points-and-rewards-for-woocommerce' ) );
		}

		if ( ! get_current_user_id() ) {
			return $this->wps_wpr_guest_output( $class, $attributes );
		}

		if ( ! class_exists( 'Points_Rewards_For_WooCommerce_Public' ) ) {
			return '';
		}

		$public_obj = new Points_Rewards_For_WooCommerce_Public( 'points-and-rewards-for-woocommerce', REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION );
		return $this->wps_wpr_wrap( $class, $public_obj->wps_wpr_create_points_log_shortocde() );
	}

	/**
	 * Get (and create if missing) the user's referral link.
	 *
	 * @param  int $user_id user id.
	 * @return string
	 */
	public static function wps_wpr_get_referral_link( $user_id ) {
		$referral_key = get_user_meta( $user_id, 'wps_points_referral', true );
		if ( empty( $referral_key ) && function_exists( 'wps_wpr_create_referral_code' ) ) {
			$referral_key = wps_wpr_create_referral_code();
			update_user_meta( $user_id, 'wps_points_referral', $referral_key );
			if ( '' === get_user_meta( $user_id, 'wps_points_referral_invite', true ) ) {
				update_user_meta( $user_id, 'wps_points_referral_invite', 0 );
			}
		}

		$general_settings = get_option( 'wps_wpr_settings_gallery', array() );
		$referral_page    = is_array( $general_settings ) && ! empty( $general_settings['wps_wpr_referral_page'] ) ? (array) $general_settings['wps_wpr_referral_page'] : array();
		$page_url         = ! empty( $referral_page ) ? get_page_link( $referral_page[0] ) : site_url();
		$page_url         = apply_filters( 'wps_wpr_referral_link_url', $page_url );

		return add_query_arg( 'pkey', rawurlencode( $referral_key ), $page_url );
	}

	/**
	 * Render: referral link with copy button.
	 *
	 * @param  array $attributes block attributes.
	 * @return string
	 */
	public function wps_wpr_render_referral_link( $attributes ) {
		$class            = 'wps-wpr-block-referral-link';
		$general_settings = get_option( 'wps_wpr_settings_gallery', array() );
		$referral_enabled = is_array( $general_settings ) && ! empty( $general_settings['wps_wpr_general_refer_enable'] );

		if ( ! self::wps_wpr_is_program_enabled() || ! $referral_enabled ) {
			return $this->wps_wpr_editor_notice( $class, __( 'Enable the points program and referral points to show this block.', 'points-and-rewards-for-woocommerce' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $this->wps_wpr_guest_output( $class, $attributes );
		}

		wp_enqueue_script( self::VIEW_HANDLE );

		$heading = isset( $attributes['heading'] ) && '' !== $attributes['heading'] ? $attributes['heading'] : __( 'Refer a friend', 'points-and-rewards-for-woocommerce' );
		$link    = self::wps_wpr_get_referral_link( $user_id );
		$points  = ! empty( $general_settings['wps_wpr_general_refer_value'] ) ? absint( $general_settings['wps_wpr_general_refer_value'] ) : 0;

		$html = '<p class="wps-wpr-block__heading">' . esc_html( $heading ) . '</p>';
		if ( $points > 0 ) {
			/* translators: %s: referral points */
			$html .= '<p class="wps-wpr-block__desc">' . esc_html( sprintf( __( 'Earn %s points for every friend who signs up with your link.', 'points-and-rewards-for-woocommerce' ), number_format_i18n( $points ) ) ) . '</p>';
		}
		$html .= '<div class="wps-wpr-block__copy">';
		$html .= '<input type="text" readonly class="wps-wpr-block__link" value="' . esc_url( $link ) . '" aria-label="' . esc_attr__( 'Your referral link', 'points-and-rewards-for-woocommerce' ) . '">';
		$html .= '<button type="button" class="wps-wpr-block__copy-btn" data-copied="' . esc_attr__( 'Copied!', 'points-and-rewards-for-woocommerce' ) . '">' . esc_html__( 'Copy', 'points-and-rewards-for-woocommerce' ) . '</button>';
		$html .= '</div>';

		return $this->wps_wpr_wrap( $class, $html );
	}

	/**
	 * Build the "ways to earn" list from the current settings.
	 *
	 * @return array list of array( 'key', 'label', 'points' ).
	 */
	public static function wps_wpr_get_earn_items() {
		$general = get_option( 'wps_wpr_settings_gallery', array() );
		$general = is_array( $general ) ? $general : array();
		$coupon  = get_option( 'wps_wpr_coupons_gallery', array() );
		$coupon  = is_array( $coupon ) ? $coupon : array();
		$items   = array();

		if ( ! empty( $general['wps_wpr_general_signup'] ) && ! empty( $general['wps_wpr_general_signup_value'] ) ) {
			$items[] = array(
				'key'    => 'signup',
				'label'  => __( 'Create an account', 'points-and-rewards-for-woocommerce' ),
				'points' => absint( $general['wps_wpr_general_signup_value'] ),
			);
		}

		if ( ! empty( $general['wps_wpr_general_refer_enable'] ) && ! empty( $general['wps_wpr_general_refer_value'] ) ) {
			$items[] = array(
				'key'    => 'referral',
				'label'  => __( 'Refer a friend', 'points-and-rewards-for-woocommerce' ),
				'points' => absint( $general['wps_wpr_general_refer_value'] ),
			);
		}

		if ( ! empty( $coupon['wps_wpr_coupon_conversion_enable'] ) ) {
			$amount = ! empty( $coupon['wps_wpr_coupon_conversion_points'] ) ? (float) $coupon['wps_wpr_coupon_conversion_points'] : 1;
			$points = ! empty( $coupon['wps_wpr_coupon_conversion_price'] ) ? absint( $coupon['wps_wpr_coupon_conversion_price'] ) : 1;
			$price  = function_exists( 'wc_price' ) ? wp_strip_all_tags( html_entity_decode( wc_price( $amount ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ) : $amount;
			$items[] = array(
				'key'    => 'per_currency',
				/* translators: %s: formatted amount */
				'label'  => sprintf( __( 'Every %s you spend', 'points-and-rewards-for-woocommerce' ), $price ),
				'points' => $points,
			);
		}

		/**
		 * Filter the "ways to earn" items shown by the Ways to Earn block.
		 *
		 * @param array $items list of array( 'key', 'label', 'points' ).
		 */
		return (array) apply_filters( 'wps_wpr_block_earn_points_items', $items );
	}

	/**
	 * Render: ways to earn points.
	 *
	 * @param  array $attributes block attributes.
	 * @return string
	 */
	public function wps_wpr_render_earn_points( $attributes ) {
		$class = 'wps-wpr-block-earn-points';
		if ( ! self::wps_wpr_is_program_enabled() ) {
			return $this->wps_wpr_editor_notice( $class, __( 'Enable the points program to show this block.', 'points-and-rewards-for-woocommerce' ) );
		}

		$items = array_filter(
			self::wps_wpr_get_earn_items(),
			function ( $item ) {
				return is_array( $item ) && ! empty( $item['label'] ) && isset( $item['points'] ) && (int) $item['points'] > 0;
			}
		);
		if ( empty( $items ) ) {
			return $this->wps_wpr_editor_notice( $class, __( 'No earning rules are enabled yet.', 'points-and-rewards-for-woocommerce' ) );
		}

		$heading = isset( $attributes['heading'] ) && '' !== $attributes['heading'] ? $attributes['heading'] : __( 'Ways to earn points', 'points-and-rewards-for-woocommerce' );

		$html = '<p class="wps-wpr-block__heading">' . esc_html( $heading ) . '</p><ul class="wps-wpr-block__list">';
		foreach ( $items as $item ) {
			$points = (int) $item['points'];
			$html  .= '<li class="wps-wpr-block__item wps-wpr-earn-' . esc_attr( sanitize_html_class( isset( $item['key'] ) ? $item['key'] : '' ) ) . '">';
			$html  .= '<span class="wps-wpr-block__item-label">' . esc_html( $item['label'] ) . '</span>';
			/* translators: %s: points */
			$html .= ' <span class="wps-wpr-block__item-points">' . esc_html( sprintf( _n( '%s point', '%s points', $points, 'points-and-rewards-for-woocommerce' ), number_format_i18n( $points ) ) ) . '</span>';
			$html .= '</li>';
		}
		$html .= '</ul>';

		return $this->wps_wpr_wrap( $class, $html );
	}

	/**
	 * Render: membership level.
	 *
	 * @param  array $attributes block attributes.
	 * @return string
	 */
	public function wps_wpr_render_membership_level( $attributes ) {
		$class = 'wps-wpr-block-membership-level';
		if ( ! self::wps_wpr_is_program_enabled() ) {
			return $this->wps_wpr_editor_notice( $class, __( 'Enable the points program to show this block.', 'points-and-rewards-for-woocommerce' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $this->wps_wpr_guest_output( $class, $attributes );
		}

		$level = get_user_meta( $user_id, 'membership_level', true );
		if ( empty( $level ) ) {
			$text = isset( $attributes['noLevelText'] ) ? $attributes['noLevelText'] : '';
			if ( '' === $text ) {
				return $this->wps_wpr_editor_notice( $class, __( 'You have no membership level yet. Customers without a level see nothing here.', 'points-and-rewards-for-woocommerce' ) );
			}
			return $this->wps_wpr_wrap( $class, '<p class="wps-wpr-block__desc">' . esc_html( $text ) . '</p>' );
		}

		$label = isset( $attributes['label'] ) && '' !== $attributes['label'] ? $attributes['label'] : __( 'Your membership level', 'points-and-rewards-for-woocommerce' );
		$html  = '<span class="wps-wpr-block__label">' . esc_html( $label ) . '</span> <span class="wps-wpr-block__level">' . esc_html( $level ) . '</span>';

		return $this->wps_wpr_wrap( $class, $html );
	}

	/**
	 * Whether a name looks like the user's login or email, so it must not be shown.
	 *
	 * @param  string  $name name.
	 * @param  WP_User $user user.
	 * @return bool
	 */
	public static function wps_wpr_name_reveals_account( $name, $user ) {
		$name = strtolower( trim( (string) $name ) );
		if ( '' === $name || false !== strpos( $name, '@' ) || is_email( $name ) ) {
			return true;
		}

		$email = strtolower( (string) $user->user_email );
		$local = false !== strpos( $email, '@' ) ? substr( $email, 0, strpos( $email, '@' ) ) : $email;

		foreach ( array( (string) $user->user_login, (string) $user->user_nicename, $local ) as $private ) {
			if ( '' !== $private && strtolower( $private ) === $name ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Name shown on the leaderboard. Never returns an email address or login.
	 *
	 * The default "first_initial" format shows "First L." and falls back to
	 * "Customer #N" when no first name is set. It never falls back to the
	 * display name, which WordPress and WooCommerce default to the login.
	 *
	 * @param  WP_User $user   user.
	 * @param  string  $format first_initial|display_name|anonymous.
	 * @param  int     $rank   rank (for anonymous names).
	 * @return string
	 */
	public static function wps_wpr_leaderboard_name( $user, $format, $rank ) {
		/* translators: %d: leaderboard rank */
		$anonymous = sprintf( __( 'Customer #%d', 'points-and-rewards-for-woocommerce' ), $rank );
		if ( 'anonymous' === $format || ! $user instanceof WP_User ) {
			return $anonymous;
		}

		if ( 'display_name' === $format ) {
			$name = trim( (string) $user->display_name );
		} else {
			$first = trim( (string) $user->first_name );
			$last  = trim( (string) $user->last_name );
			if ( '' === $first || self::wps_wpr_name_reveals_account( $first, $user ) ) {
				return $anonymous;
			}
			$name = $first . ( '' !== $last ? ' ' . mb_substr( $last, 0, 1 ) . '.' : '' );
		}

		if ( self::wps_wpr_name_reveals_account( $name, $user ) ) {
			return $anonymous;
		}

		return $name;
	}

	/**
	 * Roles kept off the leaderboard: by default every role that can manage
	 * the store or the site (manage_woocommerce or manage_options).
	 *
	 * @return string[]
	 */
	public static function wps_wpr_leaderboard_excluded_roles() {
		$roles = array();
		$wp_roles = wp_roles();
		foreach ( $wp_roles->roles as $role => $data ) {
			$caps = isset( $data['capabilities'] ) ? (array) $data['capabilities'] : array();
			if ( ! empty( $caps['manage_woocommerce'] ) || ! empty( $caps['manage_options'] ) ) {
				$roles[] = $role;
			}
		}

		/**
		 * Filter the roles that never appear on the points leaderboard.
		 *
		 * @param string[] $roles role slugs.
		 */
		$roles = (array) apply_filters( 'wps_wpr_block_leaderboard_excluded_roles', $roles );
		$roles = array_values( array_unique( array_filter( array_map( 'sanitize_key', $roles ) ) ) );
		sort( $roles );
		return $roles;
	}

	/**
	 * Cache key for a leaderboard query.
	 *
	 * @param  int      $limit limit.
	 * @param  string[] $roles excluded roles.
	 * @return string
	 */
	private static function wps_wpr_leaderboard_cache_key( $limit, $roles ) {
		$version = (int) get_option( self::LEADERBOARD_CACHE_VERSION, 0 );
		return 'wps_wpr_lb_' . md5( $limit . '|' . implode( ',', $roles ) . '|' . $version . '|' . get_current_blog_id() );
	}

	/**
	 * Invalidate cached leaderboards when a user's points or restriction change.
	 *
	 * Hooked to added_user_meta, updated_user_meta and deleted_user_meta.
	 * Cached rows also expire after LEADERBOARD_CACHE_TTL, which covers
	 * other changes such as a role change.
	 *
	 * @param  int|array $meta_id  meta id(s).
	 * @param  int       $user_id  user id.
	 * @param  string    $meta_key meta key.
	 * @return void
	 */
	public static function wps_wpr_flush_leaderboard_cache( $meta_id, $user_id, $meta_key ) {
		if ( in_array( $meta_key, array( 'wps_wpr_points', 'wps_wpr_restrict_user' ), true ) ) {
			update_option( self::LEADERBOARD_CACHE_VERSION, (int) get_option( self::LEADERBOARD_CACHE_VERSION, 0 ) + 1, false );
		}
	}

	/**
	 * Top users by points balance, excluding users restricted from points
	 * and store/site managers.
	 *
	 * One SQL query, cached in a transient for LEADERBOARD_CACHE_TTL and
	 * invalidated when any user's points or restriction meta changes.
	 *
	 * @param  int $limit number of users.
	 * @return array list of array( 'user' => WP_User, 'points' => int ).
	 */
	public static function wps_wpr_get_leaderboard( $limit ) {
		global $wpdb;

		$limit     = min( self::LEADERBOARD_MAX, max( 1, absint( $limit ) ) );
		$roles     = self::wps_wpr_leaderboard_excluded_roles();
		$cache_key = self::wps_wpr_leaderboard_cache_key( $limit, $roles );
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			$results = $cached;
		} else {
			$role_sql = '';
			$args     = array( 'wps_wpr_restrict_user', $wpdb->get_blog_prefix() . 'capabilities', 'wps_wpr_points' );
			if ( ! empty( $roles ) ) {
				$likes = array();
				foreach ( $roles as $role ) {
					$likes[] = 'caps.meta_value LIKE %s';
					$args[]  = '%' . $wpdb->esc_like( '"' . $role . '"' ) . '%';
				}
				$role_sql = ' AND ( caps.meta_value IS NULL OR NOT ( ' . implode( ' OR ', $likes ) . ' ) )';
			}
			$args[] = $limit;

			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery
			$sql = "SELECT pts.user_id AS user_id, CAST( pts.meta_value AS SIGNED ) AS points
				FROM {$wpdb->usermeta} pts
				INNER JOIN {$wpdb->users} u ON u.ID = pts.user_id
				LEFT JOIN {$wpdb->usermeta} restr ON restr.user_id = pts.user_id AND restr.meta_key = %s
				LEFT JOIN {$wpdb->usermeta} caps ON caps.user_id = pts.user_id AND caps.meta_key = %s
				WHERE pts.meta_key = %s
				AND CAST( pts.meta_value AS SIGNED ) > 0
				AND ( restr.meta_value IS NULL OR restr.meta_value <> 'yes' ){$role_sql}
				ORDER BY points DESC, pts.user_id ASC
				LIMIT %d";

			$results = $wpdb->get_results( $wpdb->prepare( $sql, $args ), ARRAY_A );
			// phpcs:enable
			$results = is_array( $results ) ? $results : array();
			set_transient( $cache_key, $results, self::LEADERBOARD_CACHE_TTL );
		}

		$ids = array_map( 'absint', wp_list_pluck( $results, 'user_id' ) );
		if ( ! empty( $ids ) ) {
			cache_users( $ids );
		}

		$rows = array();
		foreach ( $results as $result ) {
			$user = get_userdata( (int) $result['user_id'] );
			if ( $user ) {
				$rows[] = array(
					'user'   => $user,
					'points' => (int) $result['points'],
				);
			}
		}

		return (array) apply_filters( 'wps_wpr_block_leaderboard_rows', $rows, $limit );
	}

	/**
	 * Render: points leaderboard.
	 *
	 * @param  array $attributes block attributes.
	 * @return string
	 */
	public function wps_wpr_render_leaderboard( $attributes ) {
		$class = 'wps-wpr-block-leaderboard';
		if ( ! self::wps_wpr_is_program_enabled() ) {
			return $this->wps_wpr_editor_notice( $class, __( 'Enable the points program to show this block.', 'points-and-rewards-for-woocommerce' ) );
		}

		$limit       = isset( $attributes['limit'] ) ? absint( $attributes['limit'] ) : 5;
		$format      = isset( $attributes['nameFormat'] ) && in_array( $attributes['nameFormat'], array( 'first_initial', 'display_name', 'anonymous' ), true ) ? $attributes['nameFormat'] : 'first_initial';
		$show_points = ! isset( $attributes['showPoints'] ) || ! empty( $attributes['showPoints'] );
		$rows        = self::wps_wpr_get_leaderboard( $limit );

		if ( empty( $rows ) ) {
			return $this->wps_wpr_editor_notice( $class, __( 'No customers have points yet.', 'points-and-rewards-for-woocommerce' ) );
		}

		$heading = isset( $attributes['heading'] ) && '' !== $attributes['heading'] ? $attributes['heading'] : __( 'Top members', 'points-and-rewards-for-woocommerce' );
		$current = get_current_user_id();

		$html = '<p class="wps-wpr-block__heading">' . esc_html( $heading ) . '</p><ol class="wps-wpr-block__list wps-wpr-block__leaderboard">';
		$rank = 0;
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || empty( $row['user'] ) || ! $row['user'] instanceof WP_User ) {
				continue;
			}
			++$rank;
			$is_me    = $current && $row['user']->ID === $current;
			$html    .= '<li class="wps-wpr-block__item' . ( $is_me ? ' is-current-user' : '' ) . '">';
			$html    .= '<span class="wps-wpr-block__rank">' . esc_html( $rank ) . '</span> ';
			$html    .= '<span class="wps-wpr-block__item-label">' . esc_html( self::wps_wpr_leaderboard_name( $row['user'], $format, $rank ) ) . '</span>';
			if ( $show_points ) {
				$html .= ' <span class="wps-wpr-block__item-points">' . esc_html( number_format_i18n( isset( $row['points'] ) ? (int) $row['points'] : 0 ) ) . '</span>';
			}
			$html .= '</li>';
		}
		$html .= '</ol>';

		return $this->wps_wpr_wrap( $class, $html );
	}
}
