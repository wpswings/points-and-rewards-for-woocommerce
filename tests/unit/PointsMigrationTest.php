<?php
/**
 * Tests for WPS_WPR_Points_Migration and its sources.
 *
 * Source tables and meta keys are redirected to test-only names through the
 * plugin's filters, so real competitor data on the site is never touched.
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Points migration tests.
 */
class PointsMigrationTest extends TestCase {

	/**
	 * Users created by a test.
	 *
	 * @var int[]
	 */
	private $users = array();

	/**
	 * Tables created by a test.
	 *
	 * @var string[]
	 */
	private $tables = array();

	/**
	 * Object under test.
	 *
	 * @var WPS_WPR_Points_Migration
	 */
	private $migration;

	/**
	 * Unique suffix for test tables / meta keys.
	 *
	 * @var string
	 */
	private $suffix;

	protected function setUp(): void {
		$this->migration = new WPS_WPR_Points_Migration();
		$this->suffix    = strtolower( wp_generate_password( 6, false ) );

		add_filter( 'wps_wpr_migration_table_wployalty', array( $this, 'wlr_table' ) );
		add_filter( 'wps_wpr_migration_table_wc_points_rewards', array( $this, 'wcpr_table' ) );
		add_filter( 'wps_wpr_migration_meta_key_yith', array( $this, 'yith_key' ) );
		add_filter( 'wps_wpr_migration_meta_key_mycred', array( $this, 'mycred_key' ) );
	}

	protected function tearDown(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->users as $id ) {
			wp_delete_user( $id );
		}
		foreach ( $this->tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore
		}
		remove_all_filters( 'wps_wpr_migration_table_wployalty' );
		remove_all_filters( 'wps_wpr_migration_table_wc_points_rewards' );
		remove_all_filters( 'wps_wpr_migration_meta_key_yith' );
		remove_all_filters( 'wps_wpr_migration_meta_key_mycred' );
		remove_all_filters( 'wps_wpr_migration_sources' );
	}

	public function wlr_table() {
		return 'wpstest_wlr_users_' . $this->suffix;
	}

	public function wcpr_table() {
		return 'wpstest_wcpr_' . $this->suffix;
	}

	public function yith_key() {
		return '_wpstest_ywpar_' . $this->suffix;
	}

	public function mycred_key() {
		return '_wpstest_mycred_' . $this->suffix;
	}

	private function make_user( $points = 0 ) {
		$login = 'wpsmig_' . strtolower( wp_generate_password( 8, false ) );
		$id    = wp_insert_user(
			array(
				'user_login' => $login,
				'user_email' => $login . '@example.test',
				'user_pass'  => wp_generate_password(),
			)
		);
		$this->users[] = $id;
		if ( $points ) {
			update_user_meta( $id, 'wps_wpr_points', $points );
		}
		return get_user_by( 'id', $id );
	}

	private function create_wlr_table( $rows ) {
		global $wpdb;
		$table          = $wpdb->prefix . $this->wlr_table();
		$this->tables[] = $table;
		$wpdb->query( "CREATE TABLE `{$table}` ( id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_email VARCHAR(180) NOT NULL, points BIGINT NOT NULL DEFAULT 0, used_total_points BIGINT NOT NULL DEFAULT 0, earn_total_point BIGINT NOT NULL DEFAULT 0, PRIMARY KEY (id) )" ); // phpcs:ignore
		foreach ( $rows as $email => $points ) {
			$wpdb->insert( $table, array( 'user_email' => $email, 'points' => $points, 'earn_total_point' => $points ) );
		}
	}

	private function create_wcpr_table( $rows ) {
		global $wpdb;
		$table          = $wpdb->prefix . $this->wcpr_table();
		$this->tables[] = $table;
		$wpdb->query( "CREATE TABLE `{$table}` ( id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_id BIGINT NOT NULL, points BIGINT NOT NULL, points_balance BIGINT NOT NULL, order_id BIGINT NULL, date DATETIME NOT NULL, PRIMARY KEY (id) )" ); // phpcs:ignore
		foreach ( $rows as $row ) {
			$wpdb->insert(
				$table,
				array(
					'user_id'        => $row[0],
					'points'         => $row[1],
					'points_balance' => $row[1],
					'date'           => current_time( 'mysql' ),
				)
			);
		}
	}

	private function points( $user ) {
		clean_user_cache( $user->ID );
		wp_cache_delete( $user->ID, 'user_meta' );
		return (int) get_user_meta( $user->ID, 'wps_wpr_points', true );
	}

	public function test_registry_has_default_sources_and_is_filterable() {
		$ids = array_keys( WPS_WPR_Points_Migration::wps_wpr_get_sources() );
		$this->assertSame( array( 'wployalty', 'wc_points_rewards', 'yith', 'mycred' ), $ids );

		add_filter(
			'wps_wpr_migration_sources',
			function ( $sources ) {
				$sources[] = new stdClass(); // Ignored: does not implement the interface.
				return $sources;
			}
		);
		$this->assertCount( 4, WPS_WPR_Points_Migration::wps_wpr_get_sources() );
	}

	public function test_missing_sources_are_not_available_and_preview_errors() {
		foreach ( WPS_WPR_Points_Migration::wps_wpr_get_sources() as $source ) {
			$this->assertFalse( $source->is_available(), $source->get_id() . ' should not be detected' );
		}
		$this->assertInstanceOf( WP_Error::class, $this->migration->wps_wpr_preview( 'wployalty' ) );
		$this->assertInstanceOf( WP_Error::class, $this->migration->wps_wpr_run_batch( 'wployalty', 0 ) );
		$this->assertInstanceOf( WP_Error::class, $this->migration->wps_wpr_preview( 'nope' ) );
	}

	public function test_wployalty_preview_and_add_migration_is_idempotent() {
		$a = $this->make_user( 10 );
		$b = $this->make_user();
		$this->create_wlr_table(
			array(
				$a->user_email           => 150,
				$b->user_email           => 40,
				'ghost@example.invalid' => 25,
				'zero@example.invalid'  => 0,
			)
		);

		$preview = $this->migration->wps_wpr_preview( 'wployalty' );
		$this->assertIsArray( $preview );
		$this->assertSame( 3, $preview['customers'] );
		$this->assertSame( 215, $preview['points'] );
		$this->assertCount( 3, $preview['sample'] );
		$this->assertTrue( $preview['sample'][0]['matched'] );
		$this->assertFalse( $preview['sample'][2]['matched'] );

		$result = $this->migration->wps_wpr_run_batch( 'wployalty', 0, array( 'mode' => 'add' ) );
		$this->assertSame( 2, $result['imported'] );
		$this->assertSame( 1, $result['skipped'] );
		$this->assertTrue( $result['done'] );
		$this->assertSame( 160, $this->points( $a ) );
		$this->assertSame( 40, $this->points( $b ) );

		// Log entry with the source name.
		$log = get_user_meta( $a->ID, 'points_details', true );
		$this->assertSame( 'Migrated from WPLoyalty', end( $log['admin_points'] )['reason'] );

		// Second run in add mode does not double count.
		$again = $this->migration->wps_wpr_run_batch( 'wployalty', 0, array( 'mode' => 'add' ) );
		$this->assertSame( 0, $again['imported'] );
		$this->assertSame( 2, $again['already'] );
		$this->assertSame( 160, $this->points( $a ) );

		// Unless re-import is allowed.
		$this->migration->wps_wpr_run_batch( 'wployalty', 0, array( 'mode' => 'add', 'allow_reimport' => true ) );
		$this->assertSame( 310, $this->points( $a ) );
	}

	public function test_replace_mode_overrides_balance() {
		$a = $this->make_user( 999 );
		$this->create_wlr_table( array( $a->user_email => 75 ) );

		$this->migration->wps_wpr_run_batch( 'wployalty', 0, array( 'mode' => 'override' ) );
		$this->assertSame( 75, $this->points( $a ) );

		// Replace is safe to repeat.
		$this->migration->wps_wpr_run_batch( 'wployalty', 0, array( 'mode' => 'override' ) );
		$this->assertSame( 75, $this->points( $a ) );
	}

	public function test_batches_page_through_all_rows() {
		$rows  = array();
		$users = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$u          = $this->make_user();
			$users[]    = $u;
			$rows[ $u->user_email ] = 10 + $i;
		}
		$this->create_wlr_table( $rows );

		$offset   = 0;
		$imported = 0;
		$calls    = 0;
		do {
			$r         = $this->migration->wps_wpr_run_batch( 'wployalty', $offset, array( 'batch' => 2 ) );
			$offset    = $r['next_offset'];
			$imported += $r['imported'];
			++$calls;
		} while ( ! $r['done'] && $calls < 10 );

		$this->assertSame( 5, $imported );
		$this->assertSame( 3, $calls );
		$this->assertSame( 14, $this->points( $users[4] ) );
	}

	public function test_wc_points_rewards_sums_balances_per_user() {
		$a = $this->make_user();
		$b = $this->make_user( 5 );
		$this->create_wcpr_table(
			array(
				array( $a->ID, 30 ),
				array( $a->ID, 20 ),
				array( $b->ID, 7 ),
				array( 99999999, 50 ), // No such user.
			)
		);

		$preview = $this->migration->wps_wpr_preview( 'wc_points_rewards' );
		$this->assertSame( 3, $preview['customers'] );
		$this->assertSame( 107, $preview['points'] );

		$r = $this->migration->wps_wpr_run_batch( 'wc_points_rewards', 0 );
		$this->assertSame( 2, $r['imported'] );
		$this->assertSame( 1, $r['skipped'] );
		$this->assertSame( 50, $this->points( $a ) );
		$this->assertSame( 12, $this->points( $b ) );
	}

	public function test_meta_sources_yith_and_mycred() {
		$a = $this->make_user();
		$b = $this->make_user( 1 );
		update_user_meta( $a->ID, $this->yith_key(), '120' );
		update_user_meta( $b->ID, $this->mycred_key(), '33.75' );

		$yith = WPS_WPR_Points_Migration::wps_wpr_get_source( 'yith' );
		$this->assertTrue( $yith->is_available() );
		$this->assertSame( 1, $yith->count_rows() );
		$this->assertSame( 120, $yith->get_total_points() );

		$this->migration->wps_wpr_run_batch( 'yith', 0 );
		$this->assertSame( 120, $this->points( $a ) );

		$preview = $this->migration->wps_wpr_preview( 'mycred' );
		$this->assertSame( 33, $preview['points'] );
		$this->migration->wps_wpr_run_batch( 'mycred', 0 );
		$this->assertSame( 34, $this->points( $b ) );

		// Markers are per source.
		$this->assertNotEmpty( get_user_meta( $a->ID, '_wps_wpr_migrated_yith', true ) );
		$this->assertEmpty( get_user_meta( $a->ID, '_wps_wpr_migrated_mycred', true ) );
	}
}
