<?php
/**
 * Basic points expiry for the free plugin.
 *
 * Once a customer's balance reaches the threshold, an expiry date is set
 * (today + the configured period). One reminder email can be sent a number
 * of days before that date, and on the date the remaining balance expires.
 *
 * The data model is shared with the Pro plugin (option
 * `wps_wpr_points_expiration_settings`, user meta
 * `wps_wpr_points_expiration_date`, log key `expired_details`), so settings
 * and dates carry over when Pro is installed. When Pro runs its own
 * (advanced) expiry, this class stays idle so points never expire twice.
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
 * Points expiry handler.
 */
class WPS_WPR_Points_Expiry {

	/**
	 * Settings option (shared with Pro).
	 */
	const OPTION = 'wps_wpr_points_expiration_settings';

	/**
	 * Daily cron hook of the free expiry. Pro uses its own hook
	 * (PRO_CRON_HOOK), so the free plugin never schedules or clears Pro's event.
	 */
	const CRON_HOOK = 'wps_wpr_free_points_expiry_cron';

	/**
	 * Pro's daily expiry cron hook. Only read, to detect older Pro versions.
	 */
	const PRO_CRON_HOOK = 'wps_wpr_points_expiration_cron_schedule';

	/**
	 * Option set when an admin saves the free Points Expiration tab with
	 * expiry on. Until then the free expiry stays idle, even when an
	 * "enabled" value saved earlier by Pro is present in the shared option.
	 */
	const ACTIVATED_OPTION = 'wps_wpr_free_points_expiry_activated';

	/**
	 * Option set once the first free run after activation has moved past-due
	 * expiry dates forward (see wps_wpr_process_expiry()).
	 */
	const GRACE_DONE_OPTION = 'wps_wpr_free_points_expiry_grace_done';

	/**
	 * Grace period in days for past-due dates when no reminder window is set.
	 */
	const DEFAULT_GRACE_DAYS = 7;

	/**
	 * User meta holding the expiry date (Y-m-d, shared with Pro).
	 */
	const DATE_META = 'wps_wpr_points_expiration_date';

	/**
	 * User meta holding the expiry date a reminder was already sent for.
	 */
	const REMINDER_META = 'wps_wpr_points_expiry_reminder_sent';

	/**
	 * Points log key (shared with Pro).
	 */
	const LOG_KEY = 'expired_details';

	/**
	 * Users processed per query.
	 */
	const BATCH_SIZE = 50;

	/**
	 * Allowed period units.
	 *
	 * @return array unit => label.
	 */
	public static function wps_wpr_get_units() {
		return array(
			'days'   => __( 'Days', 'points-and-rewards-for-woocommerce' ),
			'weeks'  => __( 'Weeks', 'points-and-rewards-for-woocommerce' ),
			'months' => __( 'Months', 'points-and-rewards-for-woocommerce' ),
			'years'  => __( 'Years', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Default settings for the keys the free plugin manages.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_default_settings() {
		return array(
			'wps_wpr_points_expiration_enable'     => 0,
			'wps_wpr_points_exp_onmyaccount'       => 1,
			'wps_wpr_points_expiration_threshold'  => 1,
			'wps_wpr_points_expiration_time_num'   => 12,
			'wps_wpr_points_expiration_time_drop'  => array( 'months' ),
			'wps_wpr_enable_expire_notify_setting' => 0,
			'wps_wpr_points_expiration_email'      => 7,
			'wps_wpr_re_notification'              => __( 'Do not forget to redeem your [TOTALPOINT] points before they expire on [EXPIRYDATE].', 'points-and-rewards-for-woocommerce' ),
			'wps_wpr_expired_notification'         => __( 'Your points have expired. Keep shopping to earn more points!', 'points-and-rewards-for-woocommerce' ),
		);
	}

	/**
	 * Saved settings merged over the defaults.
	 *
	 * Keys saved by Pro (threshold message, validity upgrade, ...) are kept.
	 *
	 * @return array
	 */
	public static function wps_wpr_get_settings() {
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		return array_merge( self::wps_wpr_get_default_settings(), $saved );
	}

	/**
	 * Sanitize the free settings from a request and merge them into the saved option.
	 *
	 * @param  array $data raw (unslashed) request data.
	 * @return array settings to store.
	 */
	public static function wps_wpr_sanitize_settings( $data ) {
		$current = get_option( self::OPTION, array() );
		$current = is_array( $current ) ? $current : array();
		$unit    = isset( $data['wps_wpr_points_expiration_time_drop'] ) ? sanitize_key( $data['wps_wpr_points_expiration_time_drop'] ) : 'months';
		if ( ! array_key_exists( $unit, self::wps_wpr_get_units() ) ) {
			$unit = 'months';
		}

		$free = array(
			'wps_wpr_points_expiration_enable'     => ! empty( $data['wps_wpr_points_expiration_enable'] ) ? 1 : 0,
			'wps_wpr_points_exp_onmyaccount'       => ! empty( $data['wps_wpr_points_exp_onmyaccount'] ) ? 1 : 0,
			'wps_wpr_points_expiration_threshold'  => isset( $data['wps_wpr_points_expiration_threshold'] ) ? max( 1, absint( $data['wps_wpr_points_expiration_threshold'] ) ) : 1,
			'wps_wpr_points_expiration_time_num'   => isset( $data['wps_wpr_points_expiration_time_num'] ) ? max( 1, absint( $data['wps_wpr_points_expiration_time_num'] ) ) : 1,
			'wps_wpr_points_expiration_time_drop'  => array( $unit ),
			'wps_wpr_enable_expire_notify_setting' => ! empty( $data['wps_wpr_enable_expire_notify_setting'] ) ? 1 : 0,
			'wps_wpr_points_expiration_email'      => isset( $data['wps_wpr_points_expiration_email'] ) ? absint( $data['wps_wpr_points_expiration_email'] ) : 0,
			'wps_wpr_re_notification'              => isset( $data['wps_wpr_re_notification'] ) ? sanitize_textarea_field( $data['wps_wpr_re_notification'] ) : '',
			'wps_wpr_expired_notification'         => isset( $data['wps_wpr_expired_notification'] ) ? sanitize_textarea_field( $data['wps_wpr_expired_notification'] ) : '',
		);

		return array_merge( $current, $free );
	}

	/**
	 * Whether expiry is switched on.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_enabled() {
		$settings = self::wps_wpr_get_settings();
		return 1 === (int) $settings['wps_wpr_points_expiration_enable'];
	}

	/**
	 * Whether an admin has switched the free expiry on from the free tab.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_activated() {
		return (bool) get_option( self::ACTIVATED_OPTION, false );
	}

	/**
	 * Whether the shared option has expiry switched on without it being
	 * saved from the free tab (for example by Pro before it was deactivated).
	 *
	 * @return bool
	 */
	public static function wps_wpr_has_unreviewed_settings() {
		return self::wps_wpr_is_enabled() && ! self::wps_wpr_is_activated();
	}

	/**
	 * Record the admin's choice after the free tab is saved.
	 *
	 * Saving with expiry on activates the free expiry. The first run after
	 * that moves past-due dates forward. Saving with expiry off deactivates it.
	 *
	 * @return void
	 */
	public static function wps_wpr_update_activation() {
		if ( self::wps_wpr_is_enabled() ) {
			if ( ! self::wps_wpr_is_activated() ) {
				update_option( self::ACTIVATED_OPTION, time(), false );
				delete_option( self::GRACE_DONE_OPTION );
			}
		} else {
			delete_option( self::ACTIVATED_OPTION );
			delete_option( self::GRACE_DONE_OPTION );
		}
	}

	/**
	 * Whether the Pro plugin runs its own (advanced) points expiry.
	 *
	 * Newer Pro versions define WPS_WPR_PRO_HANDLES_POINTS_EXPIRY when they hook
	 * their expiry cron; older ones are detected by their cron callback.
	 *
	 * @return bool
	 */
	public static function wps_wpr_pro_handles_expiry() {
		$handled = defined( 'WPS_WPR_PRO_HANDLES_POINTS_EXPIRY' ) && WPS_WPR_PRO_HANDLES_POINTS_EXPIRY;

		if ( ! $handled ) {
			global $wp_filter;
			if ( isset( $wp_filter[ self::PRO_CRON_HOOK ] ) && is_object( $wp_filter[ self::PRO_CRON_HOOK ] ) ) {
				foreach ( $wp_filter[ self::PRO_CRON_HOOK ]->callbacks as $callbacks ) {
					foreach ( $callbacks as $callback ) {
						if ( is_array( $callback['function'] ) && isset( $callback['function'][1] ) && 'wps_wpr_check_daily_about_points_expiration' === $callback['function'][1] ) {
							$handled = true;
							break 2;
						}
					}
				}
			}
		}

		/**
		 * Filter whether points expiry is handled by the Pro plugin.
		 *
		 * @param bool $handled true when Pro handles expiry.
		 */
		return (bool) apply_filters( 'wps_wpr_pro_handles_points_expiry', $handled );
	}

	/**
	 * Whether the free expiry should run.
	 *
	 * @return bool
	 */
	public static function wps_wpr_is_active() {
		return self::wps_wpr_is_enabled() && self::wps_wpr_is_activated() && ! self::wps_wpr_pro_handles_expiry();
	}

	/**
	 * Expiry date for a start date and period.
	 *
	 * @param  string $from Y-m-d start date.
	 * @param  int    $num  period length.
	 * @param  string $unit days|weeks|months|years.
	 * @return string Y-m-d.
	 */
	public static function wps_wpr_calculate_expiry_date( $from, $num, $unit ) {
		$num  = max( 1, absint( $num ) );
		$unit = array_key_exists( $unit, self::wps_wpr_get_units() ) ? $unit : 'days';
		$date = new DateTime( $from );
		$date->modify( '+' . $num . ' ' . $unit );
		return $date->format( 'Y-m-d' );
	}

	/**
	 * Period unit from settings (Pro stores it as a one-item array).
	 *
	 * @param  array $settings settings.
	 * @return string
	 */
	public static function wps_wpr_get_unit( $settings ) {
		$unit = $settings['wps_wpr_points_expiration_time_drop'];
		$unit = is_array( $unit ) ? reset( $unit ) : $unit;
		return array_key_exists( (string) $unit, self::wps_wpr_get_units() ) ? (string) $unit : 'days';
	}

	/**
	 * Keep the free cron in line with the state on init.
	 *
	 * While Pro runs expiry the free cron is removed, and the past-due grace
	 * pass is armed again, so dates Pro left behind get a reminder before the
	 * free expiry acts on them.
	 *
	 * @return void
	 */
	public function wps_wpr_maybe_schedule_cron() {
		if ( self::wps_wpr_pro_handles_expiry() && false !== get_option( self::GRACE_DONE_OPTION, false ) ) {
			delete_option( self::GRACE_DONE_OPTION );
		}
		self::wps_wpr_sync_cron_schedule();
	}

	/**
	 * Schedule the free cron while the free expiry is active, clear it otherwise.
	 *
	 * Only the free plugin's own hook is touched, never Pro's.
	 *
	 * @return void
	 */
	public static function wps_wpr_sync_cron_schedule() {
		if ( self::wps_wpr_is_active() ) {
			if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
				wp_schedule_event( time(), 'daily', self::CRON_HOOK );
			}
		} elseif ( wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
	}

	/**
	 * Remove the free cron (plugin deactivation).
	 *
	 * @return void
	 */
	public static function wps_wpr_clear_cron() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Daily run: set expiry dates, send reminders and expire balances.
	 *
	 * @return array counts of what happened, keyed by result.
	 */
	public function wps_wpr_process_expiry() {
		$counts = array(
			'scheduled' => 0,
			'reminded'  => 0,
			'expired'   => 0,
		);
		if ( ! self::wps_wpr_is_active() ) {
			return $counts;
		}

		$settings = self::wps_wpr_get_settings();
		$today    = date_i18n( 'Y-m-d' );
		$last_id  = 0;
		// First run after activation: past-due dates get a reminder and a grace period instead of expiring.
		$grace = false === get_option( self::GRACE_DONE_OPTION, false );
		if ( $grace ) {
			$counts['grace'] = 0;
		}

		do {
			$user_ids = $this->wps_wpr_get_user_batch( $last_id );
			foreach ( $user_ids as $user_id ) {
				$result = $this->wps_wpr_process_user( $user_id, $settings, $today, $grace );
				if ( isset( $counts[ $result ] ) ) {
					++$counts[ $result ];
				}
				$last_id = max( $last_id, (int) $user_id );
			}
		} while ( count( $user_ids ) === self::BATCH_SIZE );

		if ( $grace ) {
			update_option( self::GRACE_DONE_OPTION, time(), false );
		}

		return $counts;
	}

	/**
	 * Next batch of users who have points or an expiry date, by ascending ID.
	 *
	 * Paging by ID keeps the batches stable while balances change.
	 *
	 * @param  int $after_id last processed user ID.
	 * @return int[]
	 */
	private function wps_wpr_get_user_batch( $after_id ) {
		$filter = function ( $query ) use ( $after_id ) {
			global $wpdb;
			$query->query_where .= $wpdb->prepare( " AND {$wpdb->users}.ID > %d", $after_id );
		};
		/**
		 * Filter the user query used by the daily expiry run.
		 *
		 * @param array $args get_users() arguments.
		 */
		$args = apply_filters(
			'wps_wpr_points_expiry_user_query_args',
			array(
				'fields'     => 'ID',
				'number'     => self::BATCH_SIZE,
				'orderby'    => 'ID',
				'order'      => 'ASC',
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => 'wps_wpr_points',
						'value'   => 0,
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => self::DATE_META,
						'compare' => 'EXISTS',
					),
				),
			)
		);
		$args['number'] = self::BATCH_SIZE;
		$args['fields'] = 'ID';

		add_action( 'pre_user_query', $filter );
		$user_ids = get_users( $args );
		remove_action( 'pre_user_query', $filter );
		return array_map( 'absint', $user_ids );
	}

	/**
	 * Process one customer.
	 *
	 * @param  int    $user_id  user id.
	 * @param  array  $settings settings.
	 * @param  string $today    Y-m-d.
	 * @param  bool   $grace    true on the first run after activation: a past-due
	 *                          date is moved forward and a reminder sent instead.
	 * @return string scheduled|reminded|expired|grace|none.
	 */
	public function wps_wpr_process_user( $user_id, $settings, $today, $grace = false ) {
		if ( function_exists( 'wps_wpr_restrict_user_fun' ) && wps_wpr_restrict_user_fun( $user_id ) ) {
			return 'none';
		}

		$points = (int) get_user_meta( $user_id, 'wps_wpr_points', true );
		$date   = (string) get_user_meta( $user_id, self::DATE_META, true );

		// Nothing left to expire (a negative balance is left alone). For an
		// empty balance, drop a stale date so the next earning starts fresh.
		if ( $points <= 0 ) {
			if ( 0 === $points && '' !== $date ) {
				delete_user_meta( $user_id, self::DATE_META );
				delete_user_meta( $user_id, self::REMINDER_META );
			}
			return 'none';
		}

		if ( '' === $date ) {
			if ( $points < max( 1, absint( $settings['wps_wpr_points_expiration_threshold'] ) ) ) {
				return 'none';
			}
			$date = self::wps_wpr_calculate_expiry_date( $today, $settings['wps_wpr_points_expiration_time_num'], self::wps_wpr_get_unit( $settings ) );
			update_user_meta( $user_id, self::DATE_META, $date );
			delete_user_meta( $user_id, self::REMINDER_META );
			return 'scheduled';
		}

		$reminder_days = absint( $settings['wps_wpr_points_expiration_email'] );

		if ( $today >= $date && $grace ) {
			// Date set before the free expiry was switched on (e.g. by Pro): never wipe it without notice.
			$date = gmdate( 'Y-m-d', strtotime( $today . ' +' . ( $reminder_days > 0 ? $reminder_days : self::DEFAULT_GRACE_DAYS ) . ' days' ) );
			update_user_meta( $user_id, self::DATE_META, $date );
			update_user_meta( $user_id, self::REMINDER_META, $date );
			$message = $settings['wps_wpr_re_notification'];
			$message = '' !== trim( (string) $message ) ? $message : self::wps_wpr_get_default_settings()['wps_wpr_re_notification'];
			$message = $this->wps_wpr_replace_placeholders( $message, $user_id, $points, $date );
			$this->wps_wpr_send_email( $user_id, __( 'Your points are expiring soon', 'points-and-rewards-for-woocommerce' ), $message );
			return 'grace';
		}

		if ( $today >= $date ) {
			$this->wps_wpr_expire_points( $user_id, $points, $today, $settings );
			return 'expired';
		}

		if ( 1 === (int) $settings['wps_wpr_enable_expire_notify_setting'] && $reminder_days > 0 ) {
			$remind_on = gmdate( 'Y-m-d', strtotime( $date . ' -' . $reminder_days . ' days' ) );
			if ( $today >= $remind_on && get_user_meta( $user_id, self::REMINDER_META, true ) !== $date ) {
				update_user_meta( $user_id, self::REMINDER_META, $date );
				$message = $this->wps_wpr_replace_placeholders( $settings['wps_wpr_re_notification'], $user_id, $points, $date );
				$this->wps_wpr_send_email( $user_id, __( 'Your points are expiring soon', 'points-and-rewards-for-woocommerce' ), $message );
				return 'reminded';
			}
		}

		return 'none';
	}

	/**
	 * Expire a customer's balance.
	 *
	 * @param  int    $user_id  user id.
	 * @param  int    $points   points to expire.
	 * @param  string $today    Y-m-d.
	 * @param  array  $settings settings.
	 * @return void
	 */
	public function wps_wpr_expire_points( $user_id, $points, $today, $settings ) {
		update_user_meta( $user_id, 'wps_wpr_points', 0 );
		delete_user_meta( $user_id, self::DATE_META );
		delete_user_meta( $user_id, self::REMINDER_META );

		$points_log = get_user_meta( $user_id, 'points_details', true );
		$points_log = ! empty( $points_log ) && is_array( $points_log ) ? $points_log : array();

		$points_log[ self::LOG_KEY ][] = array(
			self::LOG_KEY => $points,
			'date'        => $today,
		);
		update_user_meta( $user_id, 'points_details', $points_log );
		do_action( 'wps_wpr_update_points_log', $user_id );

		$sms = esc_html__( 'Oops! Some of your points have expired.', 'points-and-rewards-for-woocommerce' );
		if ( function_exists( 'wps_wpr_send_sms_org' ) ) {
			wps_wpr_send_sms_org( $user_id, $sms );
		}
		if ( function_exists( 'wps_wpr_send_messages_on_whatsapp' ) ) {
			wps_wpr_send_messages_on_whatsapp( $user_id, $sms );
		}

		if ( 1 === (int) $settings['wps_wpr_enable_expire_notify_setting'] ) {
			$message = $settings['wps_wpr_expired_notification'];
			$message = '' !== trim( (string) $message ) ? $message : __( 'Your points have expired. Keep shopping to earn more points!', 'points-and-rewards-for-woocommerce' );
			$message = $this->wps_wpr_replace_placeholders( $message, $user_id, $points, $today );
			$this->wps_wpr_send_email( $user_id, __( 'Your points have expired', 'points-and-rewards-for-woocommerce' ), $message );
		}

		/**
		 * Fires after a customer's points expire.
		 *
		 * @param int $user_id user id.
		 * @param int $points  expired points.
		 */
		do_action( 'wps_wpr_points_expired', $user_id, $points );
	}

	/**
	 * Replace message placeholders.
	 *
	 * @param  string $message message.
	 * @param  int    $user_id user id.
	 * @param  int    $points  points.
	 * @param  string $date    Y-m-d expiry date.
	 * @return string
	 */
	public function wps_wpr_replace_placeholders( $message, $user_id, $points, $date ) {
		$user = get_userdata( $user_id );
		return str_replace(
			array( '[TOTALPOINT]', '[EXPIRYDATE]', '[SITENAME]', '[USERNAME]' ),
			array(
				number_format_i18n( $points ),
				date_i18n( get_option( 'date_format' ), strtotime( $date ) ),
				get_bloginfo( 'name' ),
				$user ? $user->display_name : '',
			),
			(string) $message
		);
	}

	/**
	 * Send an email through the plugin's WooCommerce email.
	 *
	 * @param  int    $user_id user id.
	 * @param  string $subject subject.
	 * @param  string $message message.
	 * @return void
	 */
	public function wps_wpr_send_email( $user_id, $subject, $message ) {
		if ( ! function_exists( 'WC' ) || ! WC()->mailer() ) {
			return;
		}
		$emails = WC()->mailer()->get_emails();
		if ( empty( $emails['wps_wpr_email_notification'] ) ) {
			return;
		}
		$emails['wps_wpr_email_notification']->trigger( $user_id, wpautop( esc_html( $message ) ), $subject );
	}
}
