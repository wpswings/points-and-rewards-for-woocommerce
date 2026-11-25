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

	/**
	 * Import job / lock options as they were before the test.
	 *
	 * @var array
	 */
	private $saved_options = array();

	protected function setUp(): void {
		$this->ie = new WPS_WPR_Points_Import_Export();
		foreach ( array( WPS_WPR_Points_Import_Export::JOB_OPTION, WPS_WPR_Points_Import_Export::LOCK_OPTION ) as $name ) {
			$this->saved_options[ $name ] = get_option( $name, null );
		}
		$job = $this->saved_options[ WPS_WPR_Points_Import_Export::JOB_OPTION ];
		if ( is_array( $job ) && ! empty( $job['total'] ) ) {
			for ( $i = 0; $i < ceil( $job['total'] / WPS_WPR_Points_Import_Export::IMPORT_PAGE ); $i++ ) {
				$this->saved_options[ WPS_WPR_Points_Import_Export::ROWS_OPTION . $i ] = get_option( WPS_WPR_Points_Import_Export::ROWS_OPTION . $i, null );
			}
		}
	}

	protected function tearDown(): void {
		global $wpdb;
		// Remove anything a test left behind, then put back the site's own job (if any).
		$this->ie->wps_wpr_discard_import_job();
		$wpdb->delete( $wpdb->options, array( 'option_name' => WPS_WPR_Points_Import_Export::LOCK_OPTION ) );
		wp_cache_delete( WPS_WPR_Points_Import_Export::LOCK_OPTION, 'options' );
		foreach ( $this->saved_options as $name => $value ) {
			if ( null !== $value ) {
				update_option( $name, $value, false );
			}
		}
		$this->saved_options = array();

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

	private function make_user( $points = 0, $email_prefix = '' ) {
		$login = 'wpsie_' . wp_generate_password( 8, false );
		$id    = wp_insert_user(
			array(
				'user_login' => $login,
				'user_email' => $email_prefix . $login . '@example.test',
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
		foreach ( array( 'start', 'batch', 'discard' ) as $action ) {
			$this->assertTrue( has_action( 'wp_ajax_wps_wpr_points_import_' . $action ), $action );
		}
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

	/**
	 * Run a pending job to the end, collecting the per-batch error messages.
	 */
	private function run_job( $id, $limit = 0 ) {
		$errors = array();
		do {
			$progress = $this->ie->wps_wpr_run_import_batch( $id, $limit );
			$this->assertIsArray( $progress );
			$errors = array_merge( $errors, $progress['errors'] );
		} while ( ! $progress['done'] );
		$progress['errors'] = $errors;
		return $progress;
	}

	// Review #1: the admin import runs in resumable batches and never applies a row twice.
	public function test_batched_import_resumes_without_reapplying_rows() {
		$u   = $this->make_user( 10 );
		$v   = $this->make_user( 0 );
		$csv = array(
			'headers' => array( 'email', 'points' ),
			'rows'    => array(
				array( $u->user_email, '5' ),
				array( $v->user_email, '3' ),
				array( $u->user_email, '5' ),
				array( 'nobody@example.test', '1' ),
				array( $v->user_email, '3' ),
			),
		);
		$job = $this->ie->wps_wpr_start_import_job( $csv, array( 'mode' => 'add' ), 'points.csv' );
		$this->assertSame( 5, $job['total'] );
		$this->assertSame( 0, $job['offset'] );

		$first = $this->ie->wps_wpr_run_import_batch( $job['id'], 2 );
		$this->assertSame( 2, $first['processed'] );
		$this->assertFalse( $first['done'] );
		$this->assertSame( 15, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );

		// A new request (e.g. after a timeout) continues at row 3; a wrong id is refused.
		$other = new WPS_WPR_Points_Import_Export();
		$this->assertSame( 'wps_wpr_import_not_found', $other->wps_wpr_run_import_batch( 'nope' )->get_error_code() );
		$this->assertSame( 2, $other->wps_wpr_get_import_job()['offset'] );
		$last = $this->run_job( $job['id'], 2 );

		$this->assertTrue( $last['done'] );
		$this->assertSame( 5, $last['processed'] );
		$this->assertSame( 4, $last['imported'] );
		$this->assertSame( 1, $last['skipped'] );
		$this->assertStringContainsString( 'nobody@example.test', $last['errors'][0] );
		$this->assertSame( 20, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );
		$this->assertSame( 6, (int) get_user_meta( $v->ID, 'wps_wpr_points', true ) );

		// The finished job and its rows are removed.
		$this->assertNull( $this->ie->wps_wpr_get_import_job() );
		$this->assertFalse( get_option( WPS_WPR_Points_Import_Export::ROWS_OPTION . '0' ) );
	}

	public function test_batched_import_spans_row_pages() {
		$u    = $this->make_user( 0 );
		$rows = array_fill( 0, 1204, array( 'nobody@example.test', '1' ) );
		$rows[1100] = array( $u->user_email, '9' );
		$job  = $this->ie->wps_wpr_start_import_job(
			array(
				'headers' => array( 'email', 'points' ),
				'rows'    => $rows,
			),
			array( 'mode' => 'override' )
		);
		$this->assertCount( 1000, get_option( WPS_WPR_Points_Import_Export::ROWS_OPTION . '0' ) );
		$this->assertCount( 204, get_option( WPS_WPR_Points_Import_Export::ROWS_OPTION . '1' ) );

		$last = $this->run_job( $job['id'] );
		$this->assertSame( 1, $last['imported'] );
		$this->assertSame( 1203, $last['skipped'] );
		$this->assertSame( 9, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );
		$this->assertFalse( get_option( WPS_WPR_Points_Import_Export::ROWS_OPTION . '1' ) );
	}

	public function test_batched_import_lock_and_discard() {
		global $wpdb;
		$u   = $this->make_user( 0 );
		$job = $this->ie->wps_wpr_start_import_job(
			array(
				'headers' => array( 'email', 'points' ),
				'rows'    => array( array( $u->user_email, '4' ) ),
			)
		);

		// Another request holds the lock: the batch is refused and nothing is applied.
		$wpdb->insert( $wpdb->options, array( 'option_name' => WPS_WPR_Points_Import_Export::LOCK_OPTION, 'option_value' => (string) time(), 'autoload' => 'no' ) );
		$this->assertSame( 'wps_wpr_import_busy', $this->ie->wps_wpr_run_import_batch( $job['id'] )->get_error_code() );
		$this->assertSame( 0, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );

		// A stale lock (left by a request that died) is taken over.
		$wpdb->update( $wpdb->options, array( 'option_value' => (string) ( time() - 300 ) ), array( 'option_name' => WPS_WPR_Points_Import_Export::LOCK_OPTION ) );
		$this->assertTrue( $this->ie->wps_wpr_run_import_batch( $job['id'] )['done'] );
		$this->assertSame( 4, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );
		$this->assertNull( $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", WPS_WPR_Points_Import_Export::LOCK_OPTION ) ) );

		// Discard removes a pending job; starting needs rows and columns.
		$job = $this->ie->wps_wpr_start_import_job( array( 'headers' => array( 'email', 'points' ), 'rows' => array( array( $u->user_email, '1' ) ) ) );
		$this->ie->wps_wpr_discard_import_job();
		$this->assertNull( $this->ie->wps_wpr_get_import_job() );
		$this->assertFalse( get_option( WPS_WPR_Points_Import_Export::ROWS_OPTION . '0' ) );
		$this->assertSame( 'wps_wpr_csv_no_rows', $this->ie->wps_wpr_start_import_job( array( 'headers' => array( 'email', 'points' ), 'rows' => array() ) )->get_error_code() );
		$this->assertSame( 'wps_wpr_no_points_column', $this->ie->wps_wpr_start_import_job( array( 'headers' => array( 'email' ), 'rows' => array( array( 'a' ) ) ) )->get_error_code() );
	}

	// Review #2: emails the export prefixes with ' import back.
	public function test_export_round_trip_with_guarded_emails() {
		$a = $this->make_user( 11, '-' );
		$b = $this->make_user( 22, '+' );

		$stream = fopen( 'php://memory', 'w+' );
		$this->ie->wps_wpr_write_export( $stream, true );
		rewind( $stream );
		$content = stream_get_contents( $stream );
		fclose( $stream );
		$this->assertStringContainsString( "'" . $a->user_email, $content );

		update_user_meta( $a->ID, 'wps_wpr_points', 0 );
		update_user_meta( $b->ID, 'wps_wpr_points', 0 );
		$this->ie->wps_wpr_import( $this->ie->wps_wpr_read_csv( $this->csv_file( $content ) ), array( 'mode' => 'override' ) );
		$this->assertSame( 11, (int) get_user_meta( $a->ID, 'wps_wpr_points', true ) );
		$this->assertSame( 22, (int) get_user_meta( $b->ID, 'wps_wpr_points', true ) );

		$this->assertSame( $a->ID, $this->ie->wps_wpr_find_user( "'" . $a->user_email )->ID );
		$this->assertFalse( $this->ie->wps_wpr_find_user( "'" ) );
	}

	// Review #4: locale formats, ambiguous values and overflow.
	public function test_parse_points_locale_and_limits() {
		$this->assertSame( 1, $this->ie->wps_wpr_parse_points( '1.234' ) );
		$this->assertSame( 1234567, $this->ie->wps_wpr_parse_points( '1,234,567.9' ) );
		$this->assertNull( $this->ie->wps_wpr_parse_points( '1,5' ) );
		$this->assertNull( $this->ie->wps_wpr_parse_points( '12,34' ) );
		$this->assertSame( 1000, $this->ie->wps_wpr_parse_points( '1 000' ) );
		$this->assertSame( 1000, $this->ie->wps_wpr_parse_points( '1e3' ) );

		$this->assertSame( 1234, $this->ie->wps_wpr_parse_points( '1.234', ',' ) );
		$this->assertSame( 1, $this->ie->wps_wpr_parse_points( '1,5', ',' ) );
		$this->assertSame( 1234567, $this->ie->wps_wpr_parse_points( '1.234.567,89', ',' ) );
		$this->assertNull( $this->ie->wps_wpr_parse_points( '1.5', ',' ) );

		$this->assertNull( $this->ie->wps_wpr_parse_points( '99999999999999999999' ) );
		$this->assertNull( $this->ie->wps_wpr_parse_points( '1000000001' ) );
		$this->assertSame( 1000000000, $this->ie->wps_wpr_parse_points( '1000000000' ) );
	}

	public function test_semicolon_csv_uses_comma_decimals() {
		$u   = $this->make_user( 0 );
		$csv = $this->ie->wps_wpr_read_csv( $this->csv_file( "email;points\n{$u->user_email};1.250\n" ) );
		$this->assertSame( ';', $csv['delimiter'] );
		$res = $this->ie->wps_wpr_import( $csv, array( 'mode' => 'override' ) );
		$this->assertSame( 1, $res['imported'] );
		$this->assertSame( 1250, (int) get_user_meta( $u->ID, 'wps_wpr_points', true ) );
	}

	// Review #5: every skipped row has a message, and upload errors are reported.
	public function test_unchanged_rows_have_a_message() {
		$u   = $this->make_user( 0 );
		$res = $this->ie->wps_wpr_import(
			array(
				'headers' => array( 'email', 'points' ),
				'rows'    => array( array( $u->user_email, '5' ) ),
			),
			array( 'mode' => 'subtract' )
		);
		$this->assertSame( 1, $res['skipped'] );
		$this->assertCount( 1, $res['errors'] );
		$this->assertStringContainsString( 'did not change', $res['errors'][0] );
	}

	public function test_check_upload_reports_php_upload_errors() {
		$this->assertSame( 'wps_wpr_csv_too_large', $this->ie->wps_wpr_check_upload( array( 'error' => UPLOAD_ERR_INI_SIZE ) )->get_error_code() );
		$this->assertSame( 'wps_wpr_csv_too_large', $this->ie->wps_wpr_check_upload( array( 'error' => UPLOAD_ERR_FORM_SIZE ) )->get_error_code() );
		$this->assertSame( 'wps_wpr_csv_missing', $this->ie->wps_wpr_check_upload( array( 'error' => UPLOAD_ERR_NO_FILE ) )->get_error_code() );
		$this->assertSame( 'wps_wpr_csv_upload_failed', $this->ie->wps_wpr_check_upload( array( 'error' => UPLOAD_ERR_PARTIAL ) )->get_error_code() );
		// A path that was not uploaded through PHP is refused.
		$this->assertSame(
			'wps_wpr_csv_missing',
			$this->ie->wps_wpr_check_upload(
				array(
					'error'    => UPLOAD_ERR_OK,
					'tmp_name' => $this->csv_file( "email,points\n" ),
					'name'     => 'points.csv',
				)
			)->get_error_code()
		);
	}
}
