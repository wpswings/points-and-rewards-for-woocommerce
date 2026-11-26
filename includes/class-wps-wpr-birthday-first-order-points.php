<?php
/**
 * Birthday points and first-order points.
 *
 * These two earning rules used to be Pro-only. They now live in the free
 * plugin and use the same option keys, user meta and log keys the Pro plugin
 * used, so existing data keeps working:
 *
 * - Settings (in `wps_wpr_settings_gallery`): `wps_wpr_general_setting_enablee`,
 *   `wps_wpr_general_first_value`, `wps_wpr_first_order_points_type`,
 *   `wps_wpr_general_setting_birthday_enablee`, `wps_wpr_general_birthday_value`.
 * - Email settings (in `wps_wpr_notificatin_array`):
 *   `wps_wpr_point_on_first_order_point_setting_enable`, `wps_wpr_point_on_first_order_subject`,
 *   `wps_wpr_point_on_first_order_desc`, `wps_wpr_point_on_birthday_setting_enable`,
 *   `wps_wpr_point_on_bday_subject`, `wps_wpr_point_on_bday_order_desc`.
 * - User meta: `_my_bday`, `points_on_first_order`, `wps_wpr_birthday_points_get`,
 *   `wps_wpr_birthday_points_year`.
 * - Points log keys: `points_on_first_order`, `points_on_birthday`.
 *
 * When an older Pro version (one that does not define
 * WPS_WPR_PRO_DEFERS_BIRTHDAY_FIRST_ORDER) is active, Pro keeps handling both
 * rules and this class stays idle, so points are never awarded twice.
 *
 * Differences from the Pro copy:
 * - First-order points are taken back when that order is cancelled or refunded,
 *   and a later order can then earn them.
 * - Birthdays are matched in the site timezone (Pro used UTC), and a 29 February
 *   birthday counts on 28 February in common years.
 * - Only a strict Y-m-d date of birth earns points; a malformed date saved by an
 *   older version earns nothing until an admin corrects it.
 * - Guest orders placed before the account was created are not counted, as in Pro.
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
 * Birthday and first-order points.
 */
class WPS_WPR_Birthday_First_Order_Points {

	/**
	 * Hourly cron hook that awards birthday points.
	 */
	const CRON_HOOK = 'wps_wpr_birthday_points_check';

	/**
	 * General settings option.
	 */
	const SETTINGS_OPTION = 'wps_wpr_settings_gallery';

	/**
	 * Notification settings option.
	 */
	const NOTIFICATION_OPTION = 'wps_wpr_notificatin_array';

	/**
	 * Users processed per batch by the birthday cron.
	 */
	const BATCH_SIZE = 200;

	/**
	 * Whether the free plugin handles these rules (false while an older Pro does).
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_handled_here() {
		return function_exists( 'wps_wpr_free_handles_birthday_first_order' ) && wps_wpr_free_handles_birthday_first_order();
	}

	/**
	 * Read a general setting.
	 *
	 * @param  string $key     setting key.
	 * @param  mixed  $default default value.
	 * @return mixed
	 */
	public static function wps_wpr_get_setting( $key, $default = '' ) {
		$settings = get_option( self::SETTINGS_OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();
		return isset( $settings[ $key ] ) && '' !== $settings[ $key ] ? $settings[ $key ] : $default;
	}

	/**
	 * Is the main plugin switch on?
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_plugin_enabled() {
		return 1 === (int) self::wps_wpr_get_setting( 'wps_wpr_general_setting_enable', 0 );
	}

	/**
	 * Is first-order points enabled?
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_first_order_enabled() {
		return self::wps_wpr_is_plugin_enabled() && 1 === (int) self::wps_wpr_get_setting( 'wps_wpr_general_setting_enablee', 0 );
	}

	/**
	 * Is birthday points enabled?
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_birthday_enabled() {
		return self::wps_wpr_is_plugin_enabled() && 1 === (int) self::wps_wpr_get_setting( 'wps_wpr_general_setting_birthday_enablee', 0 );
	}

	/**
	 * Is the user excluded from earning points?
	 *
	 * @param  int $user_id user id.
	 * @return bool
	 */
	public static function wps_wpr_is_user_restricted( $user_id ) {
		return function_exists( 'wps_wpr_restrict_user_fun' ) && wps_wpr_restrict_user_fun( $user_id );
	}

	/* ------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------ */

	/**
	 * Add the First Order and Birthday sections to the General settings tab.
	 *
	 * @param  array $settings general settings.
	 * @return array
	 */
	public function wps_wpr_add_general_settings( $settings ) {
		if ( ! self::wps_wpr_is_handled_here() ) {
			return $settings;
		}

		$settings[] = array(
			'title' => __( 'First Order Points', 'points-and-rewards-for-woocommerce' ),
			'type'  => 'title',
		);
		$settings[] = array(
			'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'checkbox',
			'desc'     => __( 'Give points on a customer\'s first order', 'points-and-rewards-for-woocommerce' ),
			'id'       => 'wps_wpr_general_setting_enablee',
			'desc_tip' => __( 'Award points once, when a customer\'s first paid order reaches the points award status.', 'points-and-rewards-for-woocommerce' ),
			'default'  => 0,
		);
		$settings[] = array(
			'title'             => __( 'First Order Points', 'points-and-rewards-for-woocommerce' ),
			'type'              => 'number',
			'default'           => 1,
			'id'                => 'wps_wpr_general_first_value',
			'custom_attributes' => array( 'min' => '"1"' ),
			'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
			'desc_tip'          => __( 'Points (or percentage of the order total) a new customer gets for their first order.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'title'            => __( 'Select Points Type', 'points-and-rewards-for-woocommerce' ),
			'id'               => 'wps_wpr_first_order_points_type',
			'class'            => 'wps_wgm_new_woo_ver_style_select',
			'type'             => 'singleSelectDropDownWithKeyvalue',
			'desc_tip'         => __( 'Fixed: the value above is the number of points. Percentage: points are that percentage of the order total.', 'points-and-rewards-for-woocommerce' ),
			'custom_attribute' => array(
				array(
					'id'   => 'fixed',
					'name' => __( 'Fixed', 'points-and-rewards-for-woocommerce' ),
				),
				array(
					'id'   => 'percent',
					'name' => __( 'Percentage', 'points-and-rewards-for-woocommerce' ),
				),
			),
		);
		$settings[] = array(
			'type' => 'sectionend',
		);
		$settings[] = array(
			'title' => __( 'Birthday Points', 'points-and-rewards-for-woocommerce' ),
			'type'  => 'title',
		);
		$settings[] = array(
			'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'checkbox',
			'desc'     => __( 'Give points on the customer\'s birthday', 'points-and-rewards-for-woocommerce' ),
			'id'       => 'wps_wpr_general_setting_birthday_enablee',
			'desc_tip' => __( 'Customers add their date of birth under My Account > Account details. Points are awarded once a year on that date.', 'points-and-rewards-for-woocommerce' ),
			'default'  => 0,
		);
		$settings[] = array(
			'title'             => __( 'Birthday Points', 'points-and-rewards-for-woocommerce' ),
			'type'              => 'number',
			'default'           => 1,
			'id'                => 'wps_wpr_general_birthday_value',
			'custom_attributes' => array( 'min' => '"1"' ),
			'class'             => 'input-text wps_wpr_new_woo_ver_style_text',
			'desc_tip'          => __( 'Points customers get on their birthday.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'type' => 'sectionend',
		);

		return $settings;
	}

	/**
	 * Save the First Order and Birthday settings.
	 *
	 * Runs on the `wps_wpr_general_settings_save_option` filter, after the
	 * General tab nonce has been verified.
	 *
	 * @param  array $settings settings being saved.
	 * @return array
	 */
	public function wps_wpr_save_general_settings( $settings ) {
		if ( ! self::wps_wpr_is_handled_here() || ! is_array( $settings ) ) {
			return $settings;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified by the General settings template before this filter runs.
		$type = ! empty( $_POST['wps_wpr_first_order_points_type'] ) ? sanitize_key( wp_unslash( $_POST['wps_wpr_first_order_points_type'] ) ) : 'fixed';

		$settings['wps_wpr_general_setting_enablee']          = ! empty( $_POST['wps_wpr_general_setting_enablee'] ) ? 1 : 0;
		$settings['wps_wpr_general_first_value']              = ! empty( $_POST['wps_wpr_general_first_value'] ) ? max( 1, absint( wp_unslash( $_POST['wps_wpr_general_first_value'] ) ) ) : 1;
		$settings['wps_wpr_first_order_points_type']          = in_array( $type, array( 'fixed', 'percent' ), true ) ? $type : 'fixed';
		$settings['wps_wpr_general_setting_birthday_enablee'] = ! empty( $_POST['wps_wpr_general_setting_birthday_enablee'] ) ? 1 : 0;
		$settings['wps_wpr_general_birthday_value']           = ! empty( $_POST['wps_wpr_general_birthday_value'] ) ? max( 1, absint( wp_unslash( $_POST['wps_wpr_general_birthday_value'] ) ) ) : 1;
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $settings;
	}

	/**
	 * Add the First Order and Birthday email sections to the Notification tab.
	 *
	 * @param  array $settings notification settings.
	 * @return array
	 */
	public function wps_wpr_add_notification_settings( $settings ) {
		if ( ! self::wps_wpr_is_handled_here() ) {
			return $settings;
		}

		$settings[] = array(
			'title' => __( 'First Order Points Notification', 'points-and-rewards-for-woocommerce' ),
			'type'  => 'title',
		);
		$settings[] = array(
			'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'checkbox',
			'id'       => 'wps_wpr_point_on_first_order_point_setting_enable',
			'class'    => 'input-text',
			'desc_tip' => __( 'Email the customer when they get first order points.', 'points-and-rewards-for-woocommerce' ),
			'desc'     => __( 'Enable first order points email', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'text',
			'id'       => 'wps_wpr_point_on_first_order_subject',
			'class'    => 'input-text wps_wpr_new_woo_ver_style_text',
			'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
			'default'  => __( 'Points Added', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'textarea_email',
			'id'       => 'wps_wpr_point_on_first_order_desc',
			'class'    => 'input-text',
			'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
			'default'  => __( 'Your [FIRSTORDERPOINT] Points have been added. Now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
			'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[FIRSTORDERPOINT]' . __( ' for the points added, ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' for the total points and ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' for the customer name.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'type' => 'sectionend',
		);
		$settings[] = array(
			'title' => __( 'Birthday Points Notification', 'points-and-rewards-for-woocommerce' ),
			'type'  => 'title',
		);
		$settings[] = array(
			'title'    => __( 'Enable', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'checkbox',
			'id'       => 'wps_wpr_point_on_birthday_setting_enable',
			'class'    => 'input-text',
			'desc_tip' => __( 'Email the customer when they get birthday points.', 'points-and-rewards-for-woocommerce' ),
			'desc'     => __( 'Enable birthday points email', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'title'    => __( 'Email Subject', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'text',
			'id'       => 'wps_wpr_point_on_bday_subject',
			'class'    => 'input-text wps_wpr_new_woo_ver_style_text',
			'desc_tip' => __( 'Input subject for the email.', 'points-and-rewards-for-woocommerce' ),
			'default'  => __( 'Happy Birthday! Points Added', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'title'    => __( 'Email Description', 'points-and-rewards-for-woocommerce' ),
			'type'     => 'textarea_email',
			'id'       => 'wps_wpr_point_on_bday_order_desc',
			'class'    => 'input-text',
			'desc_tip' => __( 'Enter the Email Description for the user.', 'points-and-rewards-for-woocommerce' ),
			'default'  => __( 'Happy Birthday [USERNAME]! [BIRTHDAYPOINT] Points have been added. Now your Total Points are [TOTALPOINTS].', 'points-and-rewards-for-woocommerce' ),
			'desc'     => __( 'Use ', 'points-and-rewards-for-woocommerce' ) . '[BIRTHDAYPOINT]' . __( ' for the points added, ', 'points-and-rewards-for-woocommerce' ) . '[TOTALPOINTS]' . __( ' for the total points and ', 'points-and-rewards-for-woocommerce' ) . '[USERNAME]' . __( ' for the customer name.', 'points-and-rewards-for-woocommerce' ),
		);
		$settings[] = array(
			'type' => 'sectionend',
		);

		return $settings;
	}

	/* ------------------------------------------------------------------
	 * Points helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Add points to a user and write a points log entry.
	 *
	 * @param  int    $user_id user id.
	 * @param  int    $points  points to add.
	 * @param  string $log_key points log key.
	 * @param  array  $extra   extra log fields.
	 * @return int    new balance.
	 */
	public static function wps_wpr_add_points( $user_id, $points, $log_key, $extra = array() ) {
		$balance = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		$balance = $balance + $points;
		update_user_meta( $user_id, 'wps_wpr_points', $balance );

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ $log_key ][] = array_merge(
			array(
				$log_key => $points,
				'date'   => date_i18n( 'Y-m-d h:i:sa' ),
			),
			$extra
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		return $balance;
	}

	/**
	 * Send the SMS / WhatsApp message (when those integrations are configured).
	 *
	 * @param  int    $user_id user id.
	 * @param  string $message message.
	 * @return void
	 */
	private function wps_wpr_send_messages( $user_id, $message ) {
		if ( function_exists( 'wps_wpr_send_sms_org' ) ) {
			wps_wpr_send_sms_org( $user_id, $message );
		}
		if ( function_exists( 'wps_wpr_send_messages_on_whatsapp' ) ) {
			wps_wpr_send_messages_on_whatsapp( $user_id, $message );
		}
	}

	/**
	 * Send a points email when it is enabled in the Notification settings.
	 *
	 * @param  int    $user_id      user id.
	 * @param  string $enable_key   per-email enable key.
	 * @param  string $subject_key  subject key.
	 * @param  string $desc_key     description key.
	 * @param  array  $replacements shortcode => value.
	 * @return bool   whether the email was sent.
	 */
	private function wps_wpr_send_email( $user_id, $enable_key, $subject_key, $desc_key, $replacements ) {
		$notifications = get_option( self::NOTIFICATION_OPTION, array() );
		if ( ! is_array( $notifications ) || empty( $notifications[ $enable_key ] ) || empty( $notifications['wps_wpr_notification_setting_enable'] ) ) {
			return false;
		}

		$user = get_user_by( 'ID', $user_id );
		if ( ! $user || ! function_exists( 'WC' ) ) {
			return false;
		}

		$subject     = ! empty( $notifications[ $subject_key ] ) ? $notifications[ $subject_key ] : __( 'Points Added', 'points-and-rewards-for-woocommerce' );
		$description = ! empty( $notifications[ $desc_key ] ) ? $notifications[ $desc_key ] : '';
		$name        = ! empty( $user->first_name ) ? $user->first_name : $user->display_name;

		$replacements['[USERNAME]'] = $name;
		// Older Pro templates used [Total Points].
		if ( isset( $replacements['[TOTALPOINTS]'] ) ) {
			$replacements['[Total Points]'] = $replacements['[TOTALPOINTS]'];
		}
		$description = str_replace( array_keys( $replacements ), array_values( $replacements ), $description );

		$emails = WC()->mailer()->get_emails();
		if ( empty( $emails['wps_wpr_email_notification'] ) ) {
			return false;
		}
		$emails['wps_wpr_email_notification']->trigger( $user_id, $description, $subject );
		return true;
	}

	/* ------------------------------------------------------------------
	 * First-order points
	 * ------------------------------------------------------------------ */

	/**
	 * Calculate first-order points for an order.
	 *
	 * @param  WC_Order $order   order.
	 * @param  int      $user_id user id.
	 * @return int
	 */
	public static function wps_wpr_calculate_first_order_points( $order, $user_id ) {
		$value = absint( self::wps_wpr_get_setting( 'wps_wpr_general_first_value', 0 ) );
		$type  = self::wps_wpr_get_setting( 'wps_wpr_first_order_points_type', 'fixed' );

		if ( 'percent' === $type ) {
			$total  = max( 0, (float) $order->get_total() - (float) $order->get_total_refunded() );
			$points = (int) round( ( $total * $value ) / 100 );
		} else {
			$points = $value;
		}

		// Birthday multiplier (Pro).
		$points = (int) apply_filters( 'wps_birthday_multiplier_points', $points, $user_id );

		return (int) apply_filters( 'wps_wpr_first_order_points', max( 0, $points ), $order, $user_id );
	}

	/**
	 * Does this order earn the customer's first-order points?
	 *
	 * Same rule Pro used: the first of the customer's orders to reach the points
	 * award status (see wps_wpr_get_points_award_status()) earns the points, once
	 * per customer. The `points_on_first_order` user meta records that the points
	 * were given; other orders that are still processing or on hold do not block
	 * the award, so orders completed out of sequence still earn it.
	 *
	 * @param  WC_Order $order   order.
	 * @param  int      $user_id user id.
	 * @return bool
	 */
	public static function wps_wpr_is_first_order( $order, $user_id ) {
		$is_first = '' === (string) get_user_meta( $user_id, 'points_on_first_order', true );
		return (bool) apply_filters( 'wps_wpr_is_first_order', $is_first, $order, $user_id );
	}

	/**
	 * Award first-order points when the order reaches the points award status.
	 *
	 * @param  int    $order_id   order id.
	 * @param  string $old_status old status.
	 * @param  string $new_status new status.
	 * @return int    points awarded (0 when nothing was awarded).
	 */
	public function wps_wpr_award_first_order_points( $order_id, $old_status, $new_status ) {
		if ( ! self::wps_wpr_is_handled_here() || ! self::wps_wpr_is_first_order_enabled() ) {
			return 0;
		}

		if ( function_exists( 'wps_wpr_map_award_order_statuses' ) ) {
			list( $old_status, $new_status ) = wps_wpr_map_award_order_statuses( $order_id, $old_status, $new_status );
		}
		if ( $old_status === $new_status || 'completed' !== $new_status ) {
			return 0;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_meta( 'wps_wpr_first_order_points_awarded' ) ) {
			return 0;
		}

		$user_id = absint( $order->get_user_id() );
		if ( ! $user_id || self::wps_wpr_is_user_restricted( $user_id ) ) {
			return 0;
		}

		// Once per customer (same meta Pro used).
		if ( ! self::wps_wpr_is_first_order( $order, $user_id ) ) {
			return 0;
		}

		$points = self::wps_wpr_calculate_first_order_points( $order, $user_id );
		if ( $points <= 0 ) {
			return 0;
		}

		$balance = self::wps_wpr_add_points( $user_id, $points, 'points_on_first_order', array( 'order_id' => $order_id ) );
		update_user_meta( $user_id, 'points_on_first_order', $points );

		$order->update_meta_data( 'wps_wpr_first_order_points_awarded', $points );
		$order->save();
		$order->add_order_note(
			/* translators: %s: points */
			sprintf( esc_html__( '%s first order points awarded to the customer.', 'points-and-rewards-for-woocommerce' ), $points )
		);

		/* translators: %s: total points */
		$this->wps_wpr_send_messages( $user_id, sprintf( esc_html__( 'You have received first order points as a reward. Your total points balance is now %s', 'points-and-rewards-for-woocommerce' ), $balance ) );
		$this->wps_wpr_send_email(
			$user_id,
			'wps_wpr_point_on_first_order_point_setting_enable',
			'wps_wpr_point_on_first_order_subject',
			'wps_wpr_point_on_first_order_desc',
			array(
				'[FIRSTORDERPOINT]' => $points,
				'[TOTALPOINTS]'     => $balance,
			)
		);

		do_action( 'wps_wpr_first_order_points_awarded', $user_id, $points, $order_id );

		return $points;
	}

	/**
	 * Take back first-order points when the order that earned them is cancelled or
	 * refunded, and let a later order earn them again.
	 *
	 * Only the points the customer still has are taken, so the balance never goes
	 * below 0.
	 *
	 * @param  int    $order_id   order id.
	 * @param  string $old_status old status.
	 * @param  string $new_status new status.
	 * @return int    points taken back (0 when nothing was taken).
	 */
	public function wps_wpr_reverse_first_order_points( $order_id, $old_status, $new_status ) {
		if ( ! self::wps_wpr_is_handled_here() || $old_status === $new_status || ! in_array( $new_status, array( 'cancelled', 'refunded' ), true ) ) {
			return 0;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return 0;
		}

		$awarded = absint( $order->get_meta( 'wps_wpr_first_order_points_awarded' ) );
		$user_id = absint( $order->get_user_id() );
		if ( $awarded <= 0 || ! $user_id ) {
			return 0;
		}

		$balance = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		$deduct  = min( $awarded, max( 0, $balance ) );
		if ( $deduct > 0 ) {
			update_user_meta( $user_id, 'wps_wpr_points', $balance - $deduct );
		}

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log['first_order_points_reversed'][] = array(
			'first_order_points_reversed' => $deduct,
			'date'                        => date_i18n( 'Y-m-d h:i:sa' ),
			'order_id'                    => $order_id,
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		// The order no longer counts, so a later order can earn the first-order points.
		delete_user_meta( $user_id, 'points_on_first_order' );
		$order->delete_meta_data( 'wps_wpr_first_order_points_awarded' );
		$order->save();
		$order->add_order_note(
			/* translators: %s: points */
			sprintf( esc_html__( '%s first order points deducted because the order was cancelled or refunded.', 'points-and-rewards-for-woocommerce' ), $deduct )
		);

		do_action( 'wps_wpr_first_order_points_reversed', $user_id, $deduct, $order_id );

		return $deduct;
	}

	/* ------------------------------------------------------------------
	 * Birthday points
	 * ------------------------------------------------------------------ */

	/**
	 * Schedule (or unschedule) the daily birthday cron.
	 *
	 * @return void
	 */
	public function wps_wpr_maybe_schedule_cron() {
		$needed = self::wps_wpr_is_handled_here() && self::wps_wpr_is_birthday_enabled();
		$next   = wp_next_scheduled( self::CRON_HOOK );

		if ( $needed && ! $next ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'hourly', self::CRON_HOOK );
		} elseif ( ! $needed && $next ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
	}

	/**
	 * Validate a date of birth (Y-m-d, in the past).
	 *
	 * @param  string $date date string.
	 * @return bool
	 */
	public static function wps_wpr_is_valid_birthday( $date ) {
		if ( ! is_string( $date ) || ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $parts ) ) {
			return false;
		}
		if ( ! checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ) {
			return false;
		}
		return (int) $parts[1] >= 1900 && $date < wp_date( 'Y-m-d' );
	}

	/**
	 * Month-day strings that count as "today" for birthdays.
	 *
	 * On 28 February of a non-leap year, 29 February birthdays count too.
	 *
	 * @param  int|null $timestamp timestamp (defaults to now).
	 * @return array
	 */
	public static function wps_wpr_birthday_month_days( $timestamp = null ) {
		$timestamp = null === $timestamp ? time() : $timestamp;
		$days      = array( wp_date( 'm-d', $timestamp ) );
		if ( '02-28' === $days[0] && ! (int) wp_date( 'L', $timestamp ) ) {
			$days[] = '02-29';
		}
		return $days;
	}

	/**
	 * Award birthday points to one user, at most once per year.
	 *
	 * @param  int      $user_id   user id.
	 * @param  int|null $timestamp "today" (defaults to now).
	 * @return int      points awarded (0 when nothing was awarded).
	 */
	public function wps_wpr_award_birthday_points( $user_id, $timestamp = null ) {
		if ( ! self::wps_wpr_is_handled_here() || ! self::wps_wpr_is_birthday_enabled() ) {
			return 0;
		}

		$timestamp = null === $timestamp ? time() : $timestamp;
		$birthday  = (string) get_user_meta( $user_id, '_my_bday', true );
		if ( ! self::wps_wpr_is_valid_birthday( $birthday ) || ! in_array( substr( $birthday, 5 ), self::wps_wpr_birthday_month_days( $timestamp ), true ) ) {
			return 0;
		}

		$year = wp_date( 'Y', $timestamp );
		// Same meta Pro used, so a year is never awarded twice.
		if ( (string) get_user_meta( $user_id, 'wps_wpr_birthday_points_get', true ) === $year ) {
			return 0;
		}

		if ( self::wps_wpr_is_user_restricted( $user_id ) ) {
			return 0;
		}

		$points = (int) apply_filters( 'wps_wpr_birthday_points', absint( self::wps_wpr_get_setting( 'wps_wpr_general_birthday_value', 0 ) ), $user_id );
		if ( $points <= 0 ) {
			return 0;
		}

		update_user_meta( $user_id, 'wps_wpr_birthday_points_get', $year );
		$years   = get_user_meta( $user_id, 'wps_wpr_birthday_points_year', true );
		$years   = is_array( $years ) ? $years : array();
		$years[] = $year;
		update_user_meta( $user_id, 'wps_wpr_birthday_points_year', array_values( array_unique( $years ) ) );

		$balance = self::wps_wpr_add_points( $user_id, $points, 'points_on_birthday' );

		/* translators: 1: birthday points, 2: total points */
		$this->wps_wpr_send_messages( $user_id, sprintf( esc_html__( "Happy Birthday! To celebrate your special day, you've received %1\$s reward points. Your total points balance is now %2\$s", 'points-and-rewards-for-woocommerce' ), $points, $balance ) );
		$this->wps_wpr_send_email(
			$user_id,
			'wps_wpr_point_on_birthday_setting_enable',
			'wps_wpr_point_on_bday_subject',
			'wps_wpr_point_on_bday_order_desc',
			array(
				'[BIRTHDAYPOINT]' => $points,
				'[TOTALPOINTS]'   => $balance,
			)
		);

		do_action( 'wps_wpr_birthday_points_awarded', $user_id, $points );

		return $points;
	}

	/**
	 * Cron: award birthday points to everyone whose birthday is today.
	 *
	 * Runs hourly; the per-year marker makes repeat runs harmless.
	 *
	 * @param  int|null $timestamp "today" (defaults to now).
	 * @return int      number of users awarded.
	 */
	public function wps_wpr_process_birthdays( $timestamp = null ) {
		if ( ! self::wps_wpr_is_handled_here() || ! self::wps_wpr_is_birthday_enabled() ) {
			return 0;
		}

		$timestamp  = null === $timestamp ? time() : $timestamp;
		$meta_query = array( 'relation' => 'OR' );
		foreach ( self::wps_wpr_birthday_month_days( $timestamp ) as $month_day ) {
			$meta_query[] = array(
				'key'     => '_my_bday',
				'value'   => '-' . $month_day,
				'compare' => 'LIKE',
			);
		}

		$awarded = 0;
		$paged   = 1;
		do {
			$user_ids = get_users(
				array(
					'fields'     => 'ID',
					'number'     => self::BATCH_SIZE,
					'paged'      => $paged,
					'meta_query' => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				)
			);
			foreach ( $user_ids as $user_id ) {
				if ( $this->wps_wpr_award_birthday_points( (int) $user_id, $timestamp ) > 0 ) {
					++$awarded;
				}
			}
			++$paged;
		} while ( count( $user_ids ) === self::BATCH_SIZE );

		return $awarded;
	}

	/**
	 * Date of birth field on My Account > Account details.
	 *
	 * @return void
	 */
	public function wps_wpr_account_birthday_field() {
		if ( ! self::wps_wpr_is_handled_here() || ! self::wps_wpr_is_birthday_enabled() ) {
			return;
		}

		$birthday = (string) get_user_meta( get_current_user_id(), '_my_bday', true );
		$points   = absint( self::wps_wpr_get_setting( 'wps_wpr_general_birthday_value', 0 ) );
		?>
		<fieldset class="wps_wpr_birthday_fieldset">
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="account_bday"><?php esc_html_e( 'Date of birth', 'points-and-rewards-for-woocommerce' ); ?></label>
				<input type="date" class="woocommerce-Input woocommerce-Input--text input-text" name="account_bday" id="account_bday" max="<?php echo esc_attr( wp_date( 'Y-m-d', strtotime( '-1 day' ) ) ); ?>" value="<?php echo esc_attr( $birthday ); ?>" <?php disabled( '' !== $birthday ); ?> />
				<span class="wps_wpr_birthday_notice">
					<?php
					if ( '' !== $birthday ) {
						esc_html_e( 'Your date of birth is saved and can no longer be changed.', 'points-and-rewards-for-woocommerce' );
					} else {
						/* translators: %s: birthday points */
						echo esc_html( sprintf( __( 'Add your date of birth to get %s points on your birthday. It cannot be changed once saved.', 'points-and-rewards-for-woocommerce' ), $points ) );
					}
					?>
				</span>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Save the date of birth from My Account > Account details.
	 *
	 * The date can only be set once, so customers cannot move it to earn again.
	 *
	 * @param  int $user_id user id.
	 * @return bool whether a date was saved.
	 */
	public function wps_wpr_save_account_birthday( $user_id ) {
		if ( ! self::wps_wpr_is_handled_here() || ! self::wps_wpr_is_birthday_enabled() ) {
			return false;
		}

		$nonce = isset( $_REQUEST['save-account-details-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['save-account-details-nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'save_account_details' ) ) {
			return false;
		}

		$user_id  = absint( $user_id );
		$birthday = isset( $_POST['account_bday'] ) ? sanitize_text_field( wp_unslash( $_POST['account_bday'] ) ) : '';
		if ( ! $user_id || get_current_user_id() !== $user_id || '' !== (string) get_user_meta( $user_id, '_my_bday', true ) ) {
			return false;
		}
		if ( ! self::wps_wpr_is_valid_birthday( $birthday ) ) {
			return false;
		}

		update_user_meta( $user_id, '_my_bday', $birthday );
		return true;
	}

	/* ------------------------------------------------------------------
	 * Points log
	 * ------------------------------------------------------------------ */

	/**
	 * Log sections handled by this class.
	 *
	 * @return array log key => title.
	 */
	private function wps_wpr_log_sections() {
		return array(
			'points_on_first_order' => __( 'Points earned for placing your first order', 'points-and-rewards-for-woocommerce' ),
			'points_on_birthday'    => __( 'Points Earned on Birthday', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Show first-order and birthday entries in the customer's points log.
	 *
	 * @param  array $point_log points log.
	 * @return void
	 */
	public function wps_wpr_public_points_log( $point_log ) {
		if ( ! self::wps_wpr_is_handled_here() || ! is_array( $point_log ) ) {
			return;
		}

		foreach ( $this->wps_wpr_log_sections() as $log_key => $title ) {
			if ( empty( $point_log[ $log_key ] ) || ! is_array( $point_log[ $log_key ] ) ) {
				continue;
			}
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php echo esc_html( $title ); ?><a class="wps_wpr_open_toggle" href="javascript:;"></a></p>
				<div class="wps_wpr_points_view">
					<table class="wps_wpr_common_table">
						<thead>
							<tr>
								<th class="wps-wpr-view-log-Date"><span class="wps_wpr_nobr"><?php esc_html_e( 'Date', 'points-and-rewards-for-woocommerce' ); ?></span></th>
								<th class="wps-wpr-view-log-Status"><span class="wps_wpr_nobr"><?php esc_html_e( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span></th>
							</tr>
						</thead>
						<?php foreach ( $point_log[ $log_key ] as $value ) : ?>
							<tr>
								<td><?php echo esc_html( function_exists( 'wps_wpr_set_the_wordpress_date_format' ) ? wps_wpr_set_the_wordpress_date_format( $value['date'] ) : $value['date'] ); ?></td>
								<td><?php echo '+' . esc_html( isset( $value[ $log_key ] ) ? $value[ $log_key ] : 0 ); ?></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Show first-order and birthday entries in the admin points log.
	 *
	 * @param  array $point_log points log.
	 * @return void
	 */
	public function wps_wpr_admin_points_log( $point_log ) {
		if ( ! self::wps_wpr_is_handled_here() || ! is_array( $point_log ) ) {
			return;
		}

		foreach ( $this->wps_wpr_log_sections() as $log_key => $title ) {
			if ( empty( $point_log[ $log_key ] ) || ! is_array( $point_log[ $log_key ] ) ) {
				continue;
			}
			?>
			<div class="wps_wpr_slide_toggle">
				<p class="wps_wpr_view_log_notice wps_wpr_common_slider"><?php echo esc_html( $title ); ?>
					<a class="wps_wpr_open_toggle" href="javascript:;"></a>
				</p>
				<table class="form-table mwp_wpr_settings wps_wpr_points_view wps_wpr_common_table">
					<thead>
						<tr valign="top">
							<th scope="row" class="wps_wpr_head_titledesc"><span class="wps_wpr_nobr"><?php esc_html_e( 'Date & Time', 'points-and-rewards-for-woocommerce' ); ?></span></th>
							<th scope="row" class="wps_wpr_head_titledesc"><span class="wps_wpr_nobr"><?php esc_html_e( 'Point Status', 'points-and-rewards-for-woocommerce' ); ?></span></th>
						</tr>
					</thead>
					<?php foreach ( $point_log[ $log_key ] as $value ) : ?>
						<tr valign="top">
							<td class="forminp forminp-text"><?php echo esc_html( $value['date'] ); ?></td>
							<td class="forminp forminp-text"><?php echo '+' . esc_html( isset( $value[ $log_key ] ) ? $value[ $log_key ] : 0 ); ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
			<?php
		}
	}
}
