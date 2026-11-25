<?php
/**
 * Points migration sources.
 *
 * Each source reads customer points balances straight from another loyalty
 * plugin's database tables or user meta. The table and meta layouts below
 * follow the public structure of each plugin; the keys are filterable so a
 * site with a different version can adjust them.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/includes/migration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contract every migration source implements.
 */
interface WPS_WPR_Migration_Source {

	/**
	 * Unique source id (used in markers and requests).
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Human readable source name.
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Whether the source data exists on this site.
	 *
	 * @return bool
	 */
	public function is_available();

	/**
	 * Number of customers with a positive balance.
	 *
	 * @return int
	 */
	public function count_rows();

	/**
	 * Sum of all positive balances.
	 *
	 * @return int
	 */
	public function get_total_points();

	/**
	 * Customer balances, ordered stably for paging.
	 *
	 * @param  int $offset rows to skip.
	 * @param  int $limit  rows to return.
	 * @return array list of array( 'user_id' => int, 'email' => string, 'points' => int ).
	 */
	public function get_rows( $offset, $limit );
}

/**
 * Shared helpers for table based sources.
 */
abstract class WPS_WPR_Migration_Table_Source implements WPS_WPR_Migration_Source {

	/**
	 * Unprefixed table name.
	 *
	 * @return string
	 */
	abstract protected function table_suffix();

	/**
	 * Full table name.
	 *
	 * @return string
	 */
	public function get_table() {
		global $wpdb;
		return $wpdb->prefix . apply_filters( 'wps_wpr_migration_table_' . $this->get_id(), $this->table_suffix() );
	}

	/**
	 * Whether the table exists.
	 *
	 * @return bool
	 */
	public function is_available() {
		global $wpdb;
		$table = $this->get_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
	}

	/**
	 * Whether the table has a column.
	 *
	 * @param  string $column column name.
	 * @return bool
	 */
	protected function has_column( $column ) {
		global $wpdb;
		$table = esc_sql( $this->get_table() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (bool) $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", $column ) );
	}
}

/**
 * WPLoyalty (Loyalty Program by Flycart): one row per customer in {prefix}wlr_users,
 * current balance in the `points` column.
 */
class WPS_WPR_Migration_Source_WPLoyalty extends WPS_WPR_Migration_Table_Source {

	/**
	 * Source id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'wployalty';
	}

	/**
	 * Source label.
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'WPLoyalty', 'points-and-rewards-for-woocommerce' );
	}

	/**
	 * Table suffix.
	 *
	 * @return string
	 */
	protected function table_suffix() {
		return 'wlr_users';
	}

	/**
	 * Available only when the table has the columns we read.
	 *
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && $this->has_column( 'user_email' ) && $this->has_column( 'points' );
	}

	/**
	 * Customers with a balance.
	 *
	 * @return int
	 */
	public function count_rows() {
		global $wpdb;
		$table = esc_sql( $this->get_table() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE points > 0" );
	}

	/**
	 * Total balance.
	 *
	 * @return int
	 */
	public function get_total_points() {
		global $wpdb;
		$table = esc_sql( $this->get_table() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COALESCE(SUM(points),0) FROM `{$table}` WHERE points > 0" );
	}

	/**
	 * Rows.
	 *
	 * @param  int $offset offset.
	 * @param  int $limit  limit.
	 * @return array
	 */
	public function get_rows( $offset, $limit ) {
		global $wpdb;
		$table = esc_sql( $this->get_table() );
		$order = $this->has_column( 'id' ) ? 'id' : 'user_email';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_email, points FROM `{$table}` WHERE points > 0 ORDER BY {$order} ASC LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );
		$rows    = array();
		foreach ( (array) $results as $row ) {
			$rows[] = array(
				'user_id' => 0,
				'email'   => (string) $row['user_email'],
				'points'  => (int) floor( (float) $row['points'] ),
			);
		}
		return $rows;
	}
}

/**
 * WooCommerce Points and Rewards (official extension): point batches in
 * {prefix}wc_points_rewards_user_points; the balance is SUM(points_balance) per user.
 */
class WPS_WPR_Migration_Source_WC_Points_Rewards extends WPS_WPR_Migration_Table_Source {

	/**
	 * Source id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'wc_points_rewards';
	}

	/**
	 * Source label.
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'WooCommerce Points and Rewards', 'points-and-rewards-for-woocommerce' );
	}

	/**
	 * Table suffix.
	 *
	 * @return string
	 */
	protected function table_suffix() {
		return 'wc_points_rewards_user_points';
	}

	/**
	 * Available only when the table has the columns we read.
	 *
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && $this->has_column( 'user_id' ) && $this->has_column( 'points_balance' );
	}

	/**
	 * Customers with a balance.
	 *
	 * @return int
	 */
	public function count_rows() {
		global $wpdb;
		$table = esc_sql( $this->get_table() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM ( SELECT user_id FROM `{$table}` GROUP BY user_id HAVING SUM(points_balance) > 0 ) t" );
	}

	/**
	 * Total balance.
	 *
	 * @return int
	 */
	public function get_total_points() {
		global $wpdb;
		$table = esc_sql( $this->get_table() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COALESCE(SUM(b),0) FROM ( SELECT SUM(points_balance) b FROM `{$table}` GROUP BY user_id HAVING b > 0 ) t" );
	}

	/**
	 * Rows.
	 *
	 * @param  int $offset offset.
	 * @param  int $limit  limit.
	 * @return array
	 */
	public function get_rows( $offset, $limit ) {
		global $wpdb;
		$table = esc_sql( $this->get_table() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, SUM(points_balance) AS balance FROM `{$table}` GROUP BY user_id HAVING balance > 0 ORDER BY user_id ASC LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );
		$rows    = array();
		foreach ( (array) $results as $row ) {
			$user   = get_user_by( 'id', (int) $row['user_id'] );
			$rows[] = array(
				'user_id' => (int) $row['user_id'],
				'email'   => $user ? $user->user_email : '',
				'points'  => (int) floor( (float) $row['balance'] ),
			);
		}
		return $rows;
	}
}

/**
 * Shared logic for sources that keep the balance in a user meta key.
 */
abstract class WPS_WPR_Migration_Meta_Source implements WPS_WPR_Migration_Source {

	/**
	 * Default meta key.
	 *
	 * @return string
	 */
	abstract protected function default_meta_key();

	/**
	 * Meta key (filterable).
	 *
	 * @return string
	 */
	public function get_meta_key() {
		return (string) apply_filters( 'wps_wpr_migration_meta_key_' . $this->get_id(), $this->default_meta_key() );
	}

	/**
	 * SQL clause for a positive numeric meta value.
	 *
	 * @return string
	 */
	private function positive_clause() {
		return 'CAST(meta_value AS DECIMAL(20,4)) > 0';
	}

	/**
	 * Available when any user has the meta key.
	 *
	 * @return bool
	 */
	public function is_available() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.SlowDBQuery
		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT umeta_id FROM {$wpdb->usermeta} WHERE meta_key = %s LIMIT 1", $this->get_meta_key() ) );
	}

	/**
	 * Customers with a balance.
	 *
	 * @return int
	 */
	public function count_rows() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.SlowDBQuery
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = %s AND " . $this->positive_clause(), $this->get_meta_key() ) );
	}

	/**
	 * Total balance.
	 *
	 * @return int
	 */
	public function get_total_points() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.SlowDBQuery
		$sum = $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(FLOOR(CAST(meta_value AS DECIMAL(20,4)))),0) FROM {$wpdb->usermeta} WHERE meta_key = %s AND " . $this->positive_clause(), $this->get_meta_key() ) );
		return (int) $sum;
	}

	/**
	 * Rows.
	 *
	 * @param  int $offset offset.
	 * @param  int $limit  limit.
	 * @return array
	 */
	public function get_rows( $offset, $limit ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.SlowDBQuery
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s AND " . $this->positive_clause() . ' ORDER BY user_id ASC LIMIT %d OFFSET %d', $this->get_meta_key(), $limit, $offset ), ARRAY_A );
		$rows    = array();
		foreach ( (array) $results as $row ) {
			$user   = get_user_by( 'id', (int) $row['user_id'] );
			$rows[] = array(
				'user_id' => (int) $row['user_id'],
				'email'   => $user ? $user->user_email : '',
				'points'  => (int) floor( (float) $row['meta_value'] ),
			);
		}
		return $rows;
	}
}

/**
 * YITH WooCommerce Points and Rewards: balance in the `_ywpar_user_total_points` user meta.
 */
class WPS_WPR_Migration_Source_YITH extends WPS_WPR_Migration_Meta_Source {

	/**
	 * Source id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'yith';
	}

	/**
	 * Source label.
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'YITH WooCommerce Points and Rewards', 'points-and-rewards-for-woocommerce' );
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function default_meta_key() {
		return '_ywpar_user_total_points';
	}
}

/**
 * MyCred: default point type balance in the `mycred_default` user meta.
 */
class WPS_WPR_Migration_Source_MyCred extends WPS_WPR_Migration_Meta_Source {

	/**
	 * Source id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'mycred';
	}

	/**
	 * Source label.
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'myCred', 'points-and-rewards-for-woocommerce' );
	}

	/**
	 * Meta key.
	 *
	 * @return string
	 */
	protected function default_meta_key() {
		return 'mycred_default';
	}
}
