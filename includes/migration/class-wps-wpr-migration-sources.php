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
		$table = $wpdb->prefix . apply_filters( 'wps_wpr_migration_table_' . $this->get_id(), $this->table_suffix() );
		// Only plain identifier characters, so the name is safe inside backticks.
		return (string) preg_replace( '/[^A-Za-z0-9_]/', '', (string) $table );
	}

	/**
	 * Matched / unmatched totals for the preview, from a "user_key, balance" subquery.
	 *
	 * @param  string $subquery SQL returning one row per source customer with columns `k` (user id or email) and `b` (balance > 0).
	 * @param  string $join     how `s.k` maps to the users table: 'id' or 'email'.
	 * @return array customers, points, records, record_points.
	 */
	protected function match_summary( $subquery, $join ) {
		global $wpdb;
		$on = 'email' === $join ? 'CAST(LOWER(u.user_email) AS BINARY) = CAST(LOWER(s.k) AS BINARY)' : 'u.ID = s.k';
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$all     = $wpdb->get_row( "SELECT COUNT(*) AS c, COALESCE(SUM(FLOOR(s.b)),0) AS p FROM ( {$subquery} ) s", ARRAY_A );
		$matched = $wpdb->get_row( "SELECT COUNT(*) AS c, COALESCE(SUM(FLOOR(s.b)),0) AS p FROM ( {$subquery} ) s INNER JOIN {$wpdb->users} u ON {$on}", ARRAY_A );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array(
			'customers'     => (int) ( $matched ? $matched['c'] : 0 ),
			'points'        => (int) ( $matched ? $matched['p'] : 0 ),
			'records'       => (int) ( $all ? $all['c'] : 0 ),
			'record_points' => (int) ( $all ? $all['p'] : 0 ),
		);
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
		$table = $this->get_table();
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
	 * Whether banned WPLoyalty customers are migrated too.
	 *
	 * @var bool
	 */
	private $include_banned = false;

	/**
	 * Include (or skip, the default) customers WPLoyalty has banned.
	 *
	 * @param bool $include include banned customers.
	 * @return void
	 */
	public function set_include_banned( $include ) {
		$this->include_banned = (bool) $include;
	}

	/**
	 * WHERE clause: positive balance, and not banned unless banned customers are included.
	 *
	 * @return string
	 */
	protected function where_clause() {
		$where = 'points > 0';
		/**
		 * Filter whether banned WPLoyalty customers are migrated.
		 *
		 * @param bool $include include banned customers (default false, or the "Include banned customers" option).
		 */
		if ( ! apply_filters( 'wps_wpr_migration_wployalty_include_banned', $this->include_banned ) && $this->has_column( 'is_banned_user' ) ) {
			$where .= ' AND ( is_banned_user = 0 OR is_banned_user IS NULL )';
		}
		return $where;
	}

	/**
	 * Customers with a balance.
	 *
	 * @return int
	 */
	public function count_rows() {
		global $wpdb;
		$table = $this->get_table();
		$where = $this->where_clause();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE {$where}" );
	}

	/**
	 * Total balance.
	 *
	 * @return int
	 */
	public function get_total_points() {
		global $wpdb;
		$table = $this->get_table();
		$where = $this->where_clause();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COALESCE(SUM(FLOOR(points)),0) FROM `{$table}` WHERE {$where}" );
	}

	/**
	 * Matched / unmatched totals (customers are matched by email).
	 *
	 * @return array
	 */
	public function get_match_summary() {
		$table = $this->get_table();
		$where = $this->where_clause();
		return $this->match_summary( "SELECT user_email AS k, points AS b FROM `{$table}` WHERE {$where}", 'email' );
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
		$table = $this->get_table();
		$where = $this->where_clause();
		$order = $this->has_column( 'id' ) ? 'id' : 'user_email';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_email, points FROM `{$table}` WHERE {$where} ORDER BY {$order} ASC LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );
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
		$table = $this->get_table();
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
		$table = $this->get_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COALESCE(SUM(b),0) FROM ( SELECT SUM(points_balance) b FROM `{$table}` GROUP BY user_id HAVING b > 0 ) t" );
	}

	/**
	 * Matched / unmatched totals (customers are matched by user ID).
	 *
	 * @return array
	 */
	public function get_match_summary() {
		$table = $this->get_table();
		return $this->match_summary( "SELECT user_id AS k, SUM(points_balance) AS b FROM `{$table}` GROUP BY user_id HAVING b > 0", 'id' );
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
		$table = $this->get_table();
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
	 * Base meta key, as used on a single site.
	 *
	 * @return string
	 */
	abstract protected function default_meta_key();

	/**
	 * The meta key for a site context. Sources whose plugin stores per-blog keys on
	 * multisite override this.
	 *
	 * @param  array $context multisite (bool), blog_id (int), main_site (bool), blog_prefix (string), central (bool).
	 * @return string
	 */
	public function resolve_meta_key( array $context ) {
		unset( $context );
		return $this->default_meta_key();
	}

	/**
	 * The current site context used to resolve the meta key.
	 *
	 * @return array
	 */
	public function get_site_context() {
		global $wpdb;
		return array(
			'multisite'   => is_multisite(),
			'blog_id'     => get_current_blog_id(),
			'main_site'   => is_main_site(),
			'blog_prefix' => $wpdb->get_blog_prefix(),
			'central'     => false,
		);
	}

	/**
	 * Meta key (filterable).
	 *
	 * @return string
	 */
	public function get_meta_key() {
		$context = $this->get_site_context();
		/**
		 * Filter the user meta key a migration source reads the balance from, e.g. for a
		 * custom myCred point type or a different multisite layout.
		 *
		 * @param string $key     meta key resolved for the current site.
		 * @param array  $context site context (multisite, blog_id, main_site, blog_prefix, central).
		 */
		return (string) apply_filters( 'wps_wpr_migration_meta_key_' . $this->get_id(), $this->resolve_meta_key( $context ), $context );
	}

	/**
	 * One row per user with a positive balance: columns user_id and b. Duplicate meta
	 * rows for the same user count once (the highest value), never summed.
	 *
	 * @return string prepared SQL.
	 */
	private function balances_sql() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.SlowDBQuery
		return $wpdb->prepare( "SELECT user_id, MAX(CAST(meta_value AS DECIMAL(20,4))) AS b FROM {$wpdb->usermeta} WHERE meta_key = %s GROUP BY user_id HAVING b > 0", $this->get_meta_key() );
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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ( ' . $this->balances_sql() . ' ) t' );
	}

	/**
	 * Total balance.
	 *
	 * @return int
	 */
	public function get_total_points() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( 'SELECT COALESCE(SUM(FLOOR(b)),0) FROM ( ' . $this->balances_sql() . ' ) t' );
	}

	/**
	 * Matched / unmatched totals: only meta of existing users can be migrated.
	 *
	 * @return array
	 */
	public function get_match_summary() {
		global $wpdb;
		$sql = $this->balances_sql();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$all     = $wpdb->get_row( "SELECT COUNT(*) AS c, COALESCE(SUM(FLOOR(t.b)),0) AS p FROM ( {$sql} ) t", ARRAY_A );
		$matched = $wpdb->get_row( "SELECT COUNT(*) AS c, COALESCE(SUM(FLOOR(t.b)),0) AS p FROM ( {$sql} ) t INNER JOIN {$wpdb->users} u ON u.ID = t.user_id", ARRAY_A );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array(
			'customers'     => (int) ( $matched ? $matched['c'] : 0 ),
			'points'        => (int) ( $matched ? $matched['p'] : 0 ),
			'records'       => (int) ( $all ? $all['c'] : 0 ),
			'record_points' => (int) ( $all ? $all['p'] : 0 ),
		);
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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $wpdb->prepare( 'SELECT user_id, b FROM ( ' . $this->balances_sql() . ' ) t ORDER BY user_id ASC LIMIT %d OFFSET %d', $limit, $offset ), ARRAY_A );
		$rows    = array();
		foreach ( (array) $results as $row ) {
			$user   = get_user_by( 'id', (int) $row['user_id'] );
			$rows[] = array(
				'user_id' => (int) $row['user_id'],
				'email'   => $user ? $user->user_email : '',
				'points'  => (int) floor( (float) $row['b'] ),
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

	/**
	 * On multisite YITH stores the balance per blog, prefixed with the blog's table prefix.
	 *
	 * @param  array $context site context.
	 * @return string
	 */
	public function resolve_meta_key( array $context ) {
		if ( ! empty( $context['multisite'] ) && ! empty( $context['blog_prefix'] ) ) {
			return $context['blog_prefix'] . $this->default_meta_key();
		}
		return $this->default_meta_key();
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

	/**
	 * Like myCred's mycred_get_meta_key(): on a multisite subsite without a central
	 * ledger the key gets a "_{blog_id}" suffix; the main site uses the plain key.
	 *
	 * @param  array $context site context.
	 * @return string
	 */
	public function resolve_meta_key( array $context ) {
		$key = $this->default_meta_key();
		if ( ! empty( $context['multisite'] ) && empty( $context['main_site'] ) && empty( $context['central'] ) && ! empty( $context['blog_id'] ) ) {
			$key .= '_' . (int) $context['blog_id'];
		}
		return $key;
	}

	/**
	 * Site context, including whether myCred keeps one central ledger for the network.
	 *
	 * @return array
	 */
	public function get_site_context() {
		$context = parent::get_site_context();
		if ( $context['multisite'] ) {
			if ( function_exists( 'mycred_centralize_log' ) ) {
				$context['central'] = (bool) mycred_centralize_log();
			} else {
				$network            = get_site_option( 'mycred_network', array() );
				$context['central'] = is_array( $network ) && ! empty( $network['central'] );
			}
		}
		return $context;
	}
}
