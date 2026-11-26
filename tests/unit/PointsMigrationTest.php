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

	/**
	 * Run state options backed up in setUp.
	 *
	 * @var array
	 */
	private $run_backup = array();

	protected function setUp(): void {
		$this->migration = new WPS_WPR_Points_Migration();
		$this->suffix    = strtolower( wp_generate_password( 6, false ) );
		foreach ( array( 'wployalty', 'wc_points_rewards', 'yith', 'mycred' ) as $id ) {
			$this->run_backup[ $id ] = get_option( WPS_WPR_Points_Migration::RUN_OPTION_PREFIX . $id, null );
			delete_option( WPS_WPR_Points_Migration::RUN_OPTION_PREFIX . $id );
		}

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
		remove_all_filters( 'wps_wpr_migration_wployalty_include_banned' );
		foreach ( $this->run_backup as $id => $value ) {
			delete_option( WPS_WPR_Points_Migration::RUN_OPTION_PREFIX . $id );
			if ( null !== $value ) {
				update_option( WPS_WPR_Points_Migration::RUN_OPTION_PREFIX . $id, $value, false );
			}
		}
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

	private function create_wlr_table( $rows, $banned = array() ) {
		global $wpdb;
		$table          = $wpdb->prefix . $this->wlr_table();
		$this->tables[] = $table;
		$wpdb->query( "CREATE TABLE `{$table}` ( id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_email VARCHAR(180) NOT NULL, points BIGINT NOT NULL DEFAULT 0, used_total_points BIGINT NOT NULL DEFAULT 0, earn_total_point BIGINT NOT NULL DEFAULT 0, is_banned_user SMALLINT NULL DEFAULT 0, PRIMARY KEY (id) )" ); // phpcs:ignore
		foreach ( $rows as $email => $points ) {
			$wpdb->insert( $table, array( 'user_email' => $email, 'points' => $points, 'earn_total_point' => $points, 'is_banned_user' => in_array( $email, $banned, true ) ? 1 : 0 ) );
		}
	}

	// Drive a run to completion with the given batch size; returns the final batch result.
	private function finish_run( $id, $run_id, $batch ) {
		$calls = 0;
		do {
			$r = $this->migration->wps_wpr_continue_run( $id, $run_id, $batch );
			++$calls;
		} while ( is_array( $r ) && ! $r['done'] && $calls < 20 );
		return $r;
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
		$this->assertSame( 2, $preview['customers'] );
		$this->assertSame( 190, $preview['points'] );
		$this->assertSame( 1, $preview['unmatched'] );
		$this->assertSame( 25, $preview['unmatched_points'] );
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

		// Only existing users are counted; the deleted user's batch is reported as unmatched.
		$preview = $this->migration->wps_wpr_preview( 'wc_points_rewards' );
		$this->assertSame( 2, $preview['customers'] );
		$this->assertSame( 57, $preview['points'] );
		$this->assertSame( 1, $preview['unmatched'] );
		$this->assertSame( 50, $preview['unmatched_points'] );

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

	// Review #1: an interrupted run with re-import ticked is resumed from the saved offset and
	// never applies a customer twice within the run, even if a batch is retried.
	public function test_interrupted_reimport_run_never_applies_a_customer_twice() {
		$users = array();
		$rows  = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$u                      = $this->make_user( 100 );
			$users[]                = $u;
			$rows[ $u->user_email ] = 10;
		}
		$this->create_wlr_table( $rows );

		$run = $this->migration->wps_wpr_start_run( 'wployalty', array( 'mode' => 'add', 'allow_reimport' => true ) );
		$this->assertIsArray( $run );
		$this->assertSame( 5, $run['total'] );

		// Two batches of 2, then the browser goes away.
		$this->migration->wps_wpr_continue_run( 'wployalty', $run['run_id'], 2 );
		$this->migration->wps_wpr_continue_run( 'wployalty', $run['run_id'], 2 );
		$saved = WPS_WPR_Points_Migration::wps_wpr_get_run( 'wployalty' );
		$this->assertSame( 4, $saved['offset'] );
		$this->assertSame( 4, $saved['imported'] );

		// A retried request for an already processed offset in the same run is a no-op.
		$retry = $this->migration->wps_wpr_run_batch( 'wployalty', 0, array( 'mode' => 'add', 'allow_reimport' => true, 'run_id' => $run['run_id'], 'batch' => 4 ) );
		$this->assertSame( 0, $retry['imported'] );
		$this->assertSame( 4, $retry['already'] );
		$this->assertSame( 110, $this->points( $users[0] ) );

		// Starting a new run while one is unfinished is refused (the UI offers Resume / Start over).
		$this->assertInstanceOf( WP_Error::class, $this->migration->wps_wpr_start_run( 'wployalty', array( 'allow_reimport' => true ) ) );
		$preview = $this->migration->wps_wpr_preview( 'wployalty' );
		$this->assertSame( $run['run_id'], $preview['run']['run_id'] );

		// Resume finishes the remaining customer only.
		$last = $this->finish_run( 'wployalty', $run['run_id'], 2 );
		$this->assertTrue( $last['done'] );
		$this->assertSame( 5, $last['run']['imported'] );
		foreach ( $users as $u ) {
			$this->assertSame( 110, $this->points( $u ) );
			$this->assertEmpty( get_user_meta( $u->ID, WPS_WPR_Points_Migration::RUN_MARKER_PREFIX . 'wployalty', true ) );
		}
		$this->assertNull( WPS_WPR_Points_Migration::wps_wpr_get_run( 'wployalty' ) );

		// A finished run cannot be continued.
		$this->assertInstanceOf( WP_Error::class, $this->migration->wps_wpr_continue_run( 'wployalty', $run['run_id'] ) );
	}

	public function test_start_over_discards_the_unfinished_run() {
		$a = $this->make_user();
		$b = $this->make_user();
		$this->create_wlr_table( array( $a->user_email => 5, $b->user_email => 6 ) );

		$run = $this->migration->wps_wpr_start_run( 'wployalty', array( 'mode' => 'add' ) );
		$this->migration->wps_wpr_continue_run( 'wployalty', $run['run_id'], 1 );

		$new = $this->migration->wps_wpr_start_run( 'wployalty', array( 'mode' => 'add' ), true );
		$this->assertNotSame( $run['run_id'], $new['run_id'] );
		$this->assertSame( 0, $new['offset'] );
		$this->assertInstanceOf( WP_Error::class, $this->migration->wps_wpr_continue_run( 'wployalty', $run['run_id'] ) );

		// Without re-import the per-source marker still protects the customer done by the old run.
		$last = $this->finish_run( 'wployalty', $new['run_id'], 1 );
		$this->assertSame( 1, $last['run']['imported'] );
		$this->assertSame( 1, $last['run']['already'] );
		$this->assertSame( 5, $this->points( $a ) );
		$this->assertSame( 6, $this->points( $b ) );
	}

	// Review #2: duplicate meta rows for one user count once, in the preview and the migration.
	public function test_duplicate_meta_rows_count_once() {
		$a = $this->make_user();
		add_user_meta( $a->ID, $this->mycred_key(), '5' );
		add_user_meta( $a->ID, $this->mycred_key(), '5' );

		$preview = $this->migration->wps_wpr_preview( 'mycred' );
		$this->assertSame( 1, $preview['customers'] );
		$this->assertSame( 5, $preview['points'] );

		$r = $this->migration->wps_wpr_run_batch( 'mycred', 0, array( 'allow_reimport' => true ) );
		$this->assertSame( 1, $r['processed'] );
		$this->assertSame( 5, $this->points( $a ) );
	}

	// Review #4: meta of deleted users is reported as unmatched, not as migratable customers.
	public function test_preview_separates_orphan_meta() {
		global $wpdb;
		$a = $this->make_user();
		update_user_meta( $a->ID, $this->mycred_key(), '22' );
		$wpdb->insert( $wpdb->usermeta, array( 'user_id' => 99999998, 'meta_key' => $this->mycred_key(), 'meta_value' => '77' ) ); // phpcs:ignore

		try {
			$preview = $this->migration->wps_wpr_preview( 'mycred' );
			$this->assertSame( 1, $preview['customers'] );
			$this->assertSame( 22, $preview['points'] );
			$this->assertSame( 1, $preview['unmatched'] );
			$this->assertSame( 77, $preview['unmatched_points'] );
		} finally {
			$wpdb->delete( $wpdb->usermeta, array( 'user_id' => 99999998, 'meta_key' => $this->mycred_key() ) ); // phpcs:ignore
		}
	}

	// Review #3: multisite meta keys.
	public function test_meta_key_resolver_for_multisite() {
		remove_all_filters( 'wps_wpr_migration_meta_key_mycred' );
		remove_all_filters( 'wps_wpr_migration_meta_key_yith' );
		$mycred = new WPS_WPR_Migration_Source_MyCred();
		$yith   = new WPS_WPR_Migration_Source_YITH();

		$single = array( 'multisite' => false, 'blog_id' => 1, 'main_site' => true, 'blog_prefix' => 'wp_', 'central' => false );
		$main   = array( 'multisite' => true, 'blog_id' => 1, 'main_site' => true, 'blog_prefix' => 'wp_', 'central' => false );
		$sub    = array( 'multisite' => true, 'blog_id' => 3, 'main_site' => false, 'blog_prefix' => 'wp_3_', 'central' => false );

		$this->assertSame( 'mycred_default', $mycred->resolve_meta_key( $single ) );
		$this->assertSame( 'mycred_default', $mycred->resolve_meta_key( $main ) );
		$this->assertSame( 'mycred_default_3', $mycred->resolve_meta_key( $sub ) );
		$this->assertSame( 'mycred_default', $mycred->resolve_meta_key( array_merge( $sub, array( 'central' => true ) ) ) );

		$this->assertSame( '_ywpar_user_total_points', $yith->resolve_meta_key( $single ) );
		$this->assertSame( 'wp__ywpar_user_total_points', $yith->resolve_meta_key( $main ) );
		$this->assertSame( 'wp_3__ywpar_user_total_points', $yith->resolve_meta_key( $sub ) );

		// This test site is a single site: the plain keys are used.
		$this->assertSame( is_multisite() ? $mycred->resolve_meta_key( $mycred->get_site_context() ) : 'mycred_default', $mycred->get_meta_key() );

		// The key stays filterable and the filter receives the site context.
		$seen = null;
		add_filter(
			'wps_wpr_migration_meta_key_mycred',
			function ( $key, $context ) use ( &$seen ) {
				$seen = $context;
				return 'mycred_gold';
			},
			10,
			2
		);
		$this->assertSame( 'mycred_gold', $mycred->get_meta_key() );
		$this->assertArrayHasKey( 'blog_id', $seen );
	}

	// Nit #5: same capability as the 8315 import (manage_options only).
	public function test_capability_is_manage_options() {
		$previous = get_current_user_id();
		$manager  = $this->make_user();
		$manager->set_role( 'shop_manager' );
		$admin = $this->make_user();
		$admin->set_role( 'administrator' );
		try {
			wp_set_current_user( $manager->ID );
			$this->assertFalse( WPS_WPR_Points_Migration::wps_wpr_can_migrate() );
			wp_set_current_user( $admin->ID );
			$this->assertTrue( WPS_WPR_Points_Migration::wps_wpr_can_migrate() );
		} finally {
			wp_set_current_user( $previous );
		}
	}

	// Nit #6: the migrated marker is written only when the balance really changed.
	public function test_marker_written_only_after_successful_apply() {
		$a = $this->make_user( 75 );
		$this->create_wlr_table( array( $a->user_email => 75 ) );
		$r = $this->migration->wps_wpr_run_batch( 'wployalty', 0, array( 'mode' => 'override' ) );
		$this->assertSame( 0, $r['imported'] );
		$this->assertEmpty( get_user_meta( $a->ID, '_wps_wpr_migrated_wployalty', true ) );
	}

	// Nit #7: a filtered table name is reduced to identifier characters.
	public function test_table_name_is_sanitised() {
		global $wpdb;
		remove_all_filters( 'wps_wpr_migration_table_wployalty' );
		add_filter(
			'wps_wpr_migration_table_wployalty',
			function () {
				return 'wlr`users; DROP TABLE x';
			}
		);
		$source = new WPS_WPR_Migration_Source_WPLoyalty();
		$this->assertSame( $wpdb->prefix . 'wlrusersDROPTABLEx', $source->get_table() );
		$this->assertFalse( $source->is_available() );
	}

	// Nit #8: banned WPLoyalty customers are skipped by default and can be included.
	public function test_banned_wployalty_customers_are_skipped_by_default() {
		$a = $this->make_user();
		$b = $this->make_user();
		$this->create_wlr_table( array( $a->user_email => 10, $b->user_email => 20 ), array( $b->user_email ) );

		$preview = $this->migration->wps_wpr_preview( 'wployalty' );
		$this->assertSame( 1, $preview['customers'] );
		$this->assertSame( 10, $preview['points'] );
		$this->assertSame( 2, $this->migration->wps_wpr_preview( 'wployalty', array( 'include_banned' => true ) )['customers'] );

		$this->migration->wps_wpr_run_batch( 'wployalty', 0 );
		$this->assertSame( 10, $this->points( $a ) );
		$this->assertSame( 0, $this->points( $b ) );

		$run  = $this->migration->wps_wpr_start_run( 'wployalty', array( 'include_banned' => true ) );
		$last = $this->finish_run( 'wployalty', $run['run_id'], 100 );
		$this->assertSame( 1, $last['run']['imported'] );
		$this->assertSame( 20, $this->points( $b ) );
	}
}
