<?php
/**
 * A/B Testing functionality for Campaign Modal
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Points_And_Rewards_For_Woocommerce
 * @subpackage Points_And_Rewards_For_Woocommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A/B Testing Class
 *
 * Handles variant assignment, impression/conversion tracking, and statistics
 */
class WPS_WPR_AB_Testing {

	/**
	 * Get active A/B test
	 *
	 * @return object|null Active test object or null
	 */
	public function get_active_test() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wps_wpr_popup_ab_tests';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			return null;
		}

		$current_time = current_time( 'mysql' );

		// Get active test (status = 'active' and within date range if scheduled)
		$test = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name
				WHERE status = %s
				AND (start_date IS NULL OR start_date <= %s)
				AND (end_date IS NULL OR end_date >= %s)
				LIMIT 1",
				'active',
				$current_time,
				$current_time
			)
		);

		return $test;
	}

	/**
	 * Get user's assigned variant (A or B)
	 *
	 * @param int $test_id Test ID.
	 * @return string 'A' or 'B'
	 */
	public function get_user_variant( $test_id ) {
		// Check for forced variant via URL parameter
		if ( isset( $_GET['wps_wpr_force_variant'] ) ) {
			$forced = strtoupper( sanitize_text_field( wp_unslash( $_GET['wps_wpr_force_variant'] ) ) );
			if ( in_array( $forced, array( 'A', 'B' ), true ) ) {
				return $forced;
			}
		}

		// Check cookie for existing assignment
		$cookie_name = 'wps_wpr_ab_test_' . $test_id;
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			$variant = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
			if ( in_array( $variant, array( 'A', 'B' ), true ) ) {
				return $variant;
			}
		}

		// Assign random variant (50/50 split)
		$variant = ( wp_rand( 0, 1 ) === 0 ) ? 'A' : 'B';

		// Store in cookie for 30 days
		setcookie( $cookie_name, $variant, time() + ( 30 * DAY_IN_SECONDS ), '/' );

		return $variant;
	}

	/**
	 * Get variant configuration
	 *
	 * @param int    $test_id Test ID.
	 * @param string $variant Variant (A or B).
	 * @return array Variant configuration
	 */
	public function get_variant_config( $test_id, $variant ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wps_wpr_popup_ab_tests';

		$test = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d",
				$test_id
			)
		);

		if ( ! $test ) {
			return array();
		}

		$column = ( 'A' === $variant ) ? 'variant_a_config' : 'variant_b_config';
		$config = json_decode( $test->$column, true );

		// Ensure config has required keys with defaults
		$config = wp_parse_args(
			$config,
			array(
				'heading'    => '',
				'image_url'  => '',
				'color_one'  => ( 'A' === $variant ) ? '#6A040F' : '#03045E',
				'color_two'  => ( 'A' === $variant ) ? '#DC2F02' : '#0077B6',
			)
		);

		return $config;
	}

	/**
	 * Track impression or conversion event
	 *
	 * @param int    $test_id Test ID.
	 * @param string $variant Variant (A or B).
	 * @param string $event_type Event type ('impression' or 'conversion').
	 * @return bool Success
	 */
	public function track_event( $test_id, $variant, $event_type = 'impression' ) {
		global $wpdb;
		$events_table = $wpdb->prefix . 'wps_wpr_popup_ab_events';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$events_table'" ) != $events_table ) {
			return false;
		}

		// For impressions, only track once per session
		if ( 'impression' === $event_type ) {
			$session_key = 'wps_wpr_ab_impression_' . $test_id . '_' . $variant;
			if ( isset( $_COOKIE[ $session_key ] ) ) {
				return true; // Already tracked
			}
			// Set cookie to prevent duplicate impressions (session only)
			setcookie( $session_key, '1', 0, '/' );
		}

		$user_id = get_current_user_id();
		$user_id = ( $user_id > 0 ) ? $user_id : null;

		// Get user IP (privacy-safe)
		$user_ip = $this->get_user_ip();

		// Insert event
		$result = $wpdb->insert(
			$events_table,
			array(
				'test_id'    => $test_id,
				'variant'    => $variant,
				'event_type' => $event_type,
				'user_id'    => $user_id,
				'user_ip'    => $user_ip,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Get user IP address (privacy-safe)
	 *
	 * @return string Hashed IP address
	 */
	private function get_user_ip() {
		$ip = '';

		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// Hash IP for privacy
		return hash( 'sha256', $ip . AUTH_SALT );
	}

	/**
	 * Get test statistics
	 *
	 * @param int $test_id Test ID.
	 * @return array Statistics for both variants
	 */
	public function get_test_statistics( $test_id ) {
		global $wpdb;
		$events_table = $wpdb->prefix . 'wps_wpr_popup_ab_events';

		// Get impressions and conversions for Variant A
		$variant_a_impressions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $events_table
				WHERE test_id = %d AND variant = 'A' AND event_type = 'impression'",
				$test_id
			)
		);

		$variant_a_conversions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $events_table
				WHERE test_id = %d AND variant = 'A' AND event_type = 'conversion'",
				$test_id
			)
		);

		// Get impressions and conversions for Variant B
		$variant_b_impressions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $events_table
				WHERE test_id = %d AND variant = 'B' AND event_type = 'impression'",
				$test_id
			)
		);

		$variant_b_conversions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $events_table
				WHERE test_id = %d AND variant = 'B' AND event_type = 'conversion'",
				$test_id
			)
		);

		// Calculate conversion rates
		$variant_a_rate = ( $variant_a_impressions > 0 ) ? ( $variant_a_conversions / $variant_a_impressions ) * 100 : 0;
		$variant_b_rate = ( $variant_b_impressions > 0 ) ? ( $variant_b_conversions / $variant_b_impressions ) * 100 : 0;

		return array(
			'variant_a' => array(
				'impressions'     => intval( $variant_a_impressions ),
				'conversions'     => intval( $variant_a_conversions ),
				'conversion_rate' => round( $variant_a_rate, 2 ),
			),
			'variant_b' => array(
				'impressions'     => intval( $variant_b_impressions ),
				'conversions'     => intval( $variant_b_conversions ),
				'conversion_rate' => round( $variant_b_rate, 2 ),
			),
		);
	}
}
