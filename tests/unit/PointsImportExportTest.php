<?php
/**
 * Tests for WPS_WPR_Points_Import_Export.
 *
 * @package points-and-rewards-for-woocommerce
 */

use PHPUnit\Framework\TestCase;

/**
 * Points CSV import / export tests.
 */
class PointsImportExportTest extends TestCase {

	/**
	 * Users created by a test.
	 *
	 * @var int[]
	 */
	private $users = array();

	/**
	 * Temp files created by a test.
	 *
	 * @var string[]
	 */
	private $files = array();

	/**
	 * Object under test.
	 *
	 * @var WPS_WPR_Points_Import_Export
	 */
	private $ie;

	protected function setUp(): void {
		$this->ie = new WPS_WPR_Points_Import_Export();
	}

	protected function tearDown(): void {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		foreach ( $this->users as $id ) {
			wp_delete_user( $id );
		}
		foreach ( $this->files as $f ) {
			if ( file_exists( $f ) ) {
				unlink( $f );
			}
		}
	}

	private function make_user( $points = 0 ) {
		$login = 'wpsie_' . wp_generate_password( 8, false );
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

	private function csv_file( $content ) {
		$f = tempnam( sys_get_temp_dir(), 'wpsie' );
		file_put_contents( $f, $content );
		$this->files[] = $f;
		return $f;
	}

	public function test_class_loaded_and_export_hook_registered() {
		$this->assertTrue( class_exists( 'WPS_WPR_Points_Import_Export' ) );
	}

	public function test_csv_safe_neutralises_formulas() {
		$this->assertSame( "'=SUM(A1)", WPS_WPR_Points_Import_Export::wps_wpr_csv_safe( '=SUM(A1)' ) );
		$this->assertSame( "'@cmd", WPS_WPR_Points_Import_Export::wps_wpr_csv_safe( '@cmd' ) );
		$this->assertSame( 'a@b.test', WPS_WPR_Points_Import_Export::wps_wpr_csv_safe( 'a@b.test' ) );
	}

	public function test_parse_points() {
		$this->assertSame( 1200, $this->ie->wps_wpr_parse_points( '1,200' ) );
		$this->assertSame( 15, $this->ie->wps_wpr_parse_points( '15.9' ) );
		$this->assertSame( 0, $this->ie->wps_wpr_parse_points( '0' ) );
		$this->assertNull( $this->ie->wps_wpr_parse_points( 'abc' ) );
		$this->assertNull( $this->ie->wps_wpr_parse_points( '-5' ) );
		$this->assertNull( $this->ie->wps_wpr_parse_points( '' ) );
	}

	public function test_resolve_column_by_alias_name_and_number() {
		$headers = array( 'Customer Email', 'Available_Points', 'Note' );
		$this->assertSame( 0, $this->ie->wps_wpr_resolve_column( $headers, '', WPS_WPR_Points_Import_Export::wps_wpr_email_aliases() ) );
		$this->assertSame( 1, $this->ie->wps_wpr_resolve_column( $headers, '', WPS_WPR_Points_Import_Export::wps_wpr_points_aliases() ) );
		$this->assertSame( 2, $this->ie->wps_wpr_resolve_column( $headers, 'note', array() ) );
		$this->assertSame( 1, $this->ie->wps_wpr_resolve_column( $headers, '2', array() ) );
		$this->assertSame( -1, $this->ie->wps_wpr_resolve_column( $headers, '9', array() ) );
		$this->assertSame( -1, $this->ie->wps_wpr_resolve_column( $headers, 'missing', array() ) );
	}

	public function test_read_csv_handles_bom_semicolons_and_blank_lines() {
		$f   = $this->csv_file( "\xEF\xBB\xBFemail;points\n\na@b.test;10\n" );
		$csv = $this->ie->wps_wpr_read_csv( $f );
		$this->assertSame( array( 'email', 'points' ), $csv['headers'] );
		$this->assertCount( 1, $csv['rows'] );
		$this->assertSame( array( 'a@b.test', '10' ), $csv['rows'][0] );
		$this->assertInstanceOf( WP_Error::class, $this->ie->wps_wpr_read_csv( '/nonexistent/file.csv' ) );
	}

	public function test_import_add_mode_with_competitor_headers_logs_points() {
		$u   = $this->make_user( 50 );
		$csv = array(
			'headers' => array( 'Customer Email', 'Points Balance', 'Reason' ),
			'rows'    => array(
				array( $u->user_email, '25', 'Migrated' ),
				array( 'nobody@example.test', '10', '' ),
				array( $u->user_email, 'x', '' ),
			),
		);
		$res = $this->ie->wps_wpr_import( $csv, array( 'mode' => 'add' ) );

		$this->assertSame( 1, $res['imported'] );
		$this->assertSame( 2, $res['skipped'] );
		$this->assertCount( 2, $res['errors'] );
		$this->assertSame( 75, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );

		$log  = get_user_meta( $u->ID, 'points_details', true );
		$last = end( $log['admin_points'] );
		$this->assertSame( 25, $last['admin_points'] );
		$this->assertSame( '+', $last['sign'] );
		$this->assertSame( 'Migrated', $last['reason'] );
	}

	public function test_import_override_and_subtract_by_user_id_column() {
		$a   = $this->make_user( 100 );
		$b   = $this->make_user( 30 );
		$csv = array(
			'headers' => array( 'ID', 'Total' ),
			'rows'    => array( array( (string) $a->ID, '40' ) ),
		);

		$res = $this->ie->wps_wpr_import(
			$csv,
			array(
				'mode'          => 'override',
				'user_column'   => '1',
				'points_column' => 'total',
			)
		);
		$this->assertSame( 1, $res['imported'] );
		$this->assertSame( 40, (int) get_user_meta( $a->ID, 'wps_wpr_points', true ) );
		$log  = get_user_meta( $a->ID, 'points_details', true );
		$last = end( $log['admin_points'] );
		$this->assertSame( 60, $last['admin_points'] );
		$this->assertSame( '-', $last['sign'] );

		// Subtract never goes below zero.
		$csv['rows'] = array( array( (string) $b->ID, '45' ) );
		$this->ie->wps_wpr_import(
			$csv,
			array(
				'mode'          => 'subtract',
				'user_column'   => 'ID',
				'points_column' => '2',
			)
		);
		$this->assertSame( 0, (int) get_user_meta( $b->ID, 'wps_wpr_points', true ) );
	}

	public function test_import_errors_when_columns_missing() {
		$csv = array(
			'headers' => array( 'foo', 'bar' ),
			'rows'    => array( array( 'a', 'b' ) ),
		);
		$this->assertInstanceOf( WP_Error::class, $this->ie->wps_wpr_import( $csv ) );
		$csv['headers'] = array( 'email', 'bar' );
		$res            = $this->ie->wps_wpr_import( $csv );
		$this->assertSame( 'wps_wpr_no_points_column', $res->get_error_code() );
	}

	public function test_export_round_trip() {
		$u = $this->make_user( 321 );
		$z = $this->make_user( 0 );

		$stream = fopen( 'php://memory', 'w+' );
		$this->ie->wps_wpr_write_export( $stream, true );
		rewind( $stream );
		$content = stream_get_contents( $stream );
		fclose( $stream );

		$this->assertStringStartsWith( '"User Email",Points,"User ID",Username', $content );
		$this->assertStringContainsString( $u->user_email . ',321,' . $u->ID, $content );
		$this->assertStringNotContainsString( $z->user_email, $content );

		// Re-import the export in replace mode after changing the balance.
		update_user_meta( $u->ID, 'wps_wpr_points', 5 );
		$csv = $this->ie->wps_wpr_read_csv( $this->csv_file( $content ) );
		$this->ie->wps_wpr_import( $csv, array( 'mode' => 'override' ) );
		$this->assertSame( 321, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );
	}

	public function test_export_includes_zero_balance_when_requested() {
		$z    = $this->make_user( 0 );
		$rows = array();
		$page = 1;
		do {
			$batch = $this->ie->wps_wpr_get_export_rows( false, $page++, 500 );
			$rows  = array_merge( $rows, $batch );
		} while ( 500 === count( $batch ) );
		$emails = wp_list_pluck( $rows, 0 );
		$this->assertContains( $z->user_email, $emails );
	}
}
