<?php
/**
 * Points import / export.
 *
 * Exports every customer's points balance as CSV and imports balances from a
 * CSV file. The importer maps columns by header name (or column number), so it
 * accepts this plugin's own export as well as CSV files exported from other
 * loyalty plugins.
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
 * Points CSV import and export.
 */
class WPS_WPR_Points_Import_Export {

	/**
	 * Nonce action for the export request.
	 */
	const EXPORT_NONCE = 'wps-wpr-points-export';

	/**
	 * Nonce action for the import request.
	 */
	const IMPORT_NONCE = 'wps-wpr-points-import';

	/**
	 * Points log key used for imported points.
	 */
	const LOG_KEY = 'admin_points';

	/**
	 * Users fetched per query while exporting.
	 */
	const EXPORT_BATCH = 500;

	/**
	 * Rows applied per AJAX import request.
	 */
	const IMPORT_BATCH = 200;

	/**
	 * Rows stored per option while an import job is pending.
	 */
	const IMPORT_PAGE = 1000;

	/**
	 * Option holding the pending import job (progress, options, totals).
	 */
	const JOB_OPTION = 'wps_wpr_points_import_job';

	/**
	 * Prefix of the options holding the pending job's rows.
	 */
	const ROWS_OPTION = 'wps_wpr_points_import_rows_';

	/**
	 * Option used as a lock so two requests never run the same job at once.
	 */
	const LOCK_OPTION = 'wps_wpr_points_import_lock';

	/**
	 * Default largest points value accepted from a CSV cell.
	 */
	const MAX_POINTS = 1000000000;

	/**
	 * Header names recognised as the user email column (lower case).
	 *
	 * @return array
	 */
	public static function wps_wpr_email_aliases() {
		return apply_filters(
			'wps_wpr_import_email_column_aliases',
			array( 'user email', 'user_email', 'email', 'e-mail', 'email address', 'customer email', 'customer_email', 'billing email', 'billing_email' )
		);
	}

	/**
	 * Header names recognised as the user ID column (lower case).
	 *
	 * @return array
	 */
	public static function wps_wpr_user_id_aliases() {
		return apply_filters(
			'wps_wpr_import_user_id_column_aliases',
			array( 'user id', 'user_id', 'userid', 'customer id', 'customer_id', 'wp user id', 'wp_user_id' )
		);
	}

	/**
	 * Header names recognised as the points column (lower case).
	 *
	 * @return array
	 */
	public static function wps_wpr_points_aliases() {
		return apply_filters(
			'wps_wpr_import_points_column_aliases',
			array( 'points', 'point', 'user points', 'total points', 'points balance', 'point balance', 'balance', 'available points', 'current points', 'points_balance', 'total_points', 'available_points', 'reward points' )
		);
	}

	/**
	 * Header names recognised as the reason column (lower case).
	 *
	 * @return array
	 */
	public static function wps_wpr_reason_aliases() {
		return apply_filters( 'wps_wpr_import_reason_column_aliases', array( 'reason', 'note', 'notes', 'comment', 'description' ) );
	}

	/**
	 * Stream the points CSV when the export button is submitted.
	 *
	 * Hooked on admin_init so headers can be sent before any admin output.
	 *
	 * @return void
	 */
	public function wps_wpr_maybe_export_points() {
		if ( ! isset( $_POST['wps_wpr_export_points_csv'], $_POST['wps_wpr_points_export_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wpr_points_export_nonce'] ) ), self::EXPORT_NONCE ) ) {
			wp_die( esc_html__( 'Security check failed.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied', 'points-and-rewards-for-woocommerce' ) );
		}

		$only_with_points = ! empty( $_POST['wps_wpr_export_only_with_points'] );
		$filename         = 'points-export-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$this->wps_wpr_write_export( $output, $only_with_points );
		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Write the export CSV (header + one row per user) to a stream.
	 *
	 * @param  resource $output           writable stream.
	 * @param  bool     $only_with_points only include users with a positive balance.
	 * @return int      number of user rows written.
	 */
	public function wps_wpr_write_export( $output, $only_with_points = true ) {
		fputcsv( $output, $this->wps_wpr_export_headers() );

		$written = 0;
		$page    = 1;
		do {
			$rows = $this->wps_wpr_get_export_rows( $only_with_points, $page, self::EXPORT_BATCH );
			foreach ( $rows as $row ) {
				fputcsv( $output, $row );
				++$written;
			}
			++$page;
		} while ( count( $rows ) === self::EXPORT_BATCH );

		return $written;
	}

	/**
	 * Export column headers.
	 *
	 * @return array
	 */
	public function wps_wpr_export_headers() {
		return apply_filters(
			'wps_wpr_points_export_headers',
			array(
				__( 'User Email', 'points-and-rewards-for-woocommerce' ),
				__( 'Points', 'points-and-rewards-for-woocommerce' ),
				__( 'User ID', 'points-and-rewards-for-woocommerce' ),
				__( 'Username', 'points-and-rewards-for-woocommerce' ),
			)
		);
	}

	/**
	 * One page of export rows.
	 *
	 * @param  bool $only_with_points only include users with a positive balance.
	 * @param  int  $page             page number (1-based).
	 * @param  int  $per_page         users per page.
	 * @return array rows of array( email, points, user id, username ).
	 */
	public function wps_wpr_get_export_rows( $only_with_points = true, $page = 1, $per_page = self::EXPORT_BATCH ) {
		$args = array(
			'number'  => $per_page,
			'paged'   => max( 1, (int) $page ),
			'orderby' => 'ID',
			'order'   => 'ASC',
			'fields'  => array( 'ID', 'user_email', 'user_login' ),
		);
		if ( $only_with_points ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'wps_wpr_points',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			);
		}

		$rows = array();
		foreach ( get_users( $args ) as $user ) {
			$points = (int) get_user_meta( $user->ID, 'wps_wpr_points', true );
			$row    = array(
				self::wps_wpr_csv_safe( $user->user_email ),
				$points,
				(int) $user->ID,
				self::wps_wpr_csv_safe( $user->user_login ),
			);
			$rows[] = apply_filters( 'wps_wpr_points_export_row', $row, $user );
		}
		return $rows;
	}

	/**
	 * Neutralise values that spreadsheet apps would run as formulas.
	 *
	 * @param  string $value cell value.
	 * @return string
	 */
	public static function wps_wpr_csv_safe( $value ) {
		$value = (string) $value;
		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			$value = "'" . $value;
		}
		return $value;
	}

	/**
	 * Read a CSV file into headers and rows.
	 *
	 * @param  string $path file path.
	 * @return array|WP_Error array( 'headers' => array, 'rows' => array, 'delimiter' => string ).
	 */
	public function wps_wpr_read_csv( $path ) {
		if ( empty( $path ) || ! is_readable( $path ) ) {
			return new WP_Error( 'wps_wpr_csv_unreadable', __( 'The CSV file could not be read.', 'points-and-rewards-for-woocommerce' ) );
		}

		$handle = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $handle ) {
			return new WP_Error( 'wps_wpr_csv_unreadable', __( 'The CSV file could not be read.', 'points-and-rewards-for-woocommerce' ) );
		}

		$first = fgets( $handle );
		if ( false === $first ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			return new WP_Error( 'wps_wpr_csv_empty', __( 'The CSV file is empty.', 'points-and-rewards-for-woocommerce' ) );
		}

		// Detect the delimiter from the header line (some exports use ; or tabs).
		$delimiter = ',';
		$best      = 0;
		foreach ( array( ',', ';', "\t" ) as $candidate ) {
			$count = substr_count( $first, $candidate );
			if ( $count > $best ) {
				$best      = $count;
				$delimiter = $candidate;
			}
		}
		rewind( $handle );

		$headers = fgetcsv( $handle, 0, $delimiter, '"', '\\' );
		$headers = is_array( $headers ) ? $headers : array();
		if ( ! empty( $headers[0] ) ) {
			// Strip a UTF-8 BOM from the first header.
			$headers[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $headers[0] );
		}
		$headers = array_map( 'trim', array_map( 'strval', $headers ) );

		$rows = array();
		while ( false !== ( $row = fgetcsv( $handle, 0, $delimiter, '"', '\\' ) ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			// Skip blank lines (fgetcsv returns array( null )).
			if ( ! is_array( $row ) || ( 1 === count( $row ) && ( null === $row[0] || '' === trim( (string) $row[0] ) ) ) ) {
				continue;
			}
			$rows[] = $row;
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return array(
			'headers'   => $headers,
			'rows'      => $rows,
			'delimiter' => $delimiter,
		);
	}

	/**
	 * Resolve a column index.
	 *
	 * The admin can enter a header name or a 1-based column number. When left
	 * empty the column is detected from the known header aliases.
	 *
	 * @param  array  $headers  CSV headers.
	 * @param  string $override admin-entered header name or column number.
	 * @param  array  $aliases  header names to auto-detect.
	 * @return int    zero-based index, or -1 when not found.
	 */
	public function wps_wpr_resolve_column( $headers, $override, $aliases ) {
		$normalized = array_map( array( __CLASS__, 'wps_wpr_normalize_header' ), $headers );
		$override   = trim( (string) $override );

		if ( '' !== $override ) {
			if ( ctype_digit( $override ) ) {
				$index = (int) $override - 1;
				return ( $index >= 0 && $index < count( $headers ) ) ? $index : -1;
			}
			$found = array_search( self::wps_wpr_normalize_header( $override ), $normalized, true );
			return false === $found ? -1 : (int) $found;
		}

		foreach ( $aliases as $alias ) {
			$found = array_search( self::wps_wpr_normalize_header( $alias ), $normalized, true );
			if ( false !== $found ) {
				return (int) $found;
			}
		}
		return -1;
	}

	/**
	 * Normalise a header for comparison (lower case, single spaces, "_" and "-" as spaces).
	 *
	 * @param  string $header header text.
	 * @return string
	 */
	public static function wps_wpr_normalize_header( $header ) {
		$header = strtolower( trim( (string) $header ) );
		$header = str_replace( array( '_', '-' ), ' ', $header );
		return preg_replace( '/\s+/', ' ', $header );
	}

	/**
	 * Fill in the default import options.
	 *
	 * @param  array $args import options (see wps_wpr_import()).
	 * @return array
	 */
	public function wps_wpr_import_args( $args ) {
		$args         = wp_parse_args(
			$args,
			array(
				'mode'           => 'add',
				'user_column'    => '',
				'points_column'  => '',
				'reason_column'  => '',
				'default_reason' => __( 'Points imported from CSV', 'points-and-rewards-for-woocommerce' ),
				'decimal'        => '.',
			)
		);
		$args['mode']    = in_array( $args['mode'], array( 'add', 'subtract', 'override' ), true ) ? $args['mode'] : 'add';
		$args['decimal'] = ',' === $args['decimal'] ? ',' : '.';
		return $args;
	}

	/**
	 * Decimal separator for a CSV: files separated by ";" use the European "1.234,5" format.
	 *
	 * @param  array $csv parsed CSV from wps_wpr_read_csv().
	 * @return string "," or ".".
	 */
	public static function wps_wpr_csv_decimal( $csv ) {
		return ( isset( $csv['delimiter'] ) && ';' === $csv['delimiter'] ) ? ',' : '.';
	}

	/**
	 * Resolve the user, points and reason columns.
	 *
	 * @param  array $headers CSV headers.
	 * @param  array $args    import options.
	 * @return array|WP_Error array( user, points, reason ) zero-based indexes (reason may be -1).
	 */
	public function wps_wpr_resolve_import_columns( $headers, $args ) {
		// The user column may hold an email or a user ID.
		$user_col = $this->wps_wpr_resolve_column( $headers, $args['user_column'], self::wps_wpr_email_aliases() );
		if ( -1 === $user_col && '' === trim( (string) $args['user_column'] ) ) {
			$user_col = $this->wps_wpr_resolve_column( $headers, '', self::wps_wpr_user_id_aliases() );
		}
		$points_col = $this->wps_wpr_resolve_column( $headers, $args['points_column'], self::wps_wpr_points_aliases() );
		$reason_col = $this->wps_wpr_resolve_column( $headers, $args['reason_column'], self::wps_wpr_reason_aliases() );

		if ( -1 === $user_col ) {
			return new WP_Error( 'wps_wpr_no_user_column', __( 'Could not find the user email / user ID column. Enter its header name or column number.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( -1 === $points_col ) {
			return new WP_Error( 'wps_wpr_no_points_column', __( 'Could not find the points column. Enter its header name or column number.', 'points-and-rewards-for-woocommerce' ) );
		}
		return array(
			'user'   => $user_col,
			'points' => $points_col,
			'reason' => $reason_col,
		);
	}

	/**
	 * Reduce a CSV row to array( identifier, points cell, reason ) using the resolved columns.
	 *
	 * @param  array $row  CSV row.
	 * @param  array $cols resolved columns.
	 * @return array
	 */
	public function wps_wpr_compact_row( $row, $cols ) {
		$reason = ( -1 !== $cols['reason'] && isset( $row[ $cols['reason'] ] ) ) ? sanitize_text_field( $row[ $cols['reason'] ] ) : '';
		return array(
			isset( $row[ $cols['user'] ] ) ? trim( (string) $row[ $cols['user'] ] ) : '',
			isset( $row[ $cols['points'] ] ) ? trim( (string) $row[ $cols['points'] ] ) : '',
			trim( $reason ),
		);
	}

	/**
	 * Import a parsed CSV in one go.
	 *
	 * The admin screen uses the batched job (wps_wpr_start_import_job() and
	 * wps_wpr_run_import_batch()) instead, so large files never run in a
	 * single request. This method is kept for code that imports small files.
	 *
	 * @param  array $csv  array( 'headers' => array, 'rows' => array ) from wps_wpr_read_csv().
	 * @param  array $args {
	 *     Import options.
	 *
	 *     @type string $mode           add|subtract|override (default add).
	 *     @type string $user_column    header name / column number of the email or user ID column.
	 *     @type string $points_column  header name / column number of the points column.
	 *     @type string $reason_column  header name / column number of the reason column.
	 *     @type string $default_reason reason used when a row has none.
	 *     @type string $decimal        decimal separator ("." or ","). Defaults from the CSV delimiter.
	 * }
	 * @return array|WP_Error summary: imported, skipped, errors (row messages).
	 */
	public function wps_wpr_import( $csv, $args = array() ) {
		if ( ! isset( $args['decimal'] ) ) {
			$args['decimal'] = self::wps_wpr_csv_decimal( $csv );
		}
		$args    = $this->wps_wpr_import_args( $args );
		$headers = isset( $csv['headers'] ) ? (array) $csv['headers'] : array();
		$rows    = isset( $csv['rows'] ) ? (array) $csv['rows'] : array();

		$cols = $this->wps_wpr_resolve_import_columns( $headers, $args );
		if ( is_wp_error( $cols ) ) {
			return $cols;
		}

		$summary = array(
			'imported' => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		foreach ( array_values( $rows ) as $index => $row ) {
			$row    = $this->wps_wpr_compact_row( $row, $cols );
			$result = $this->wps_wpr_import_row( $row, $index + 2, $args ); // Header is line 1.
			if ( $result['imported'] ) {
				++$summary['imported'];
			} else {
				++$summary['skipped'];
				$summary['errors'][] = $result['error'];
			}
		}

		do_action( 'wps_wpr_points_import_completed', $summary, $args );
		return $summary;
	}

	/**
	 * Apply one compact row.
	 *
	 * @param  array $row  array( identifier, points cell, reason ) from wps_wpr_compact_row().
	 * @param  int   $line CSV line number, for messages.
	 * @param  array $args import options (mode, default_reason, decimal).
	 * @return array array( 'imported' => bool, 'error' => string ).
	 */
	public function wps_wpr_import_row( $row, $line, $args ) {
		$identifier = isset( $row[0] ) ? (string) $row[0] : '';
		$raw_points = isset( $row[1] ) ? (string) $row[1] : '';
		$reason     = ! empty( $row[2] ) ? (string) $row[2] : $args['default_reason'];

		$user = $this->wps_wpr_find_user( $identifier );
		if ( ! $user ) {
			return array(
				'imported' => false,
				/* translators: 1: line number, 2: email or user id */
				'error'    => sprintf( __( 'Line %1$d: no user found for "%2$s".', 'points-and-rewards-for-woocommerce' ), $line, $identifier ),
			);
		}

		$points = $this->wps_wpr_parse_points( $raw_points, $args['decimal'] );
		if ( null === $points ) {
			return array(
				'imported' => false,
				/* translators: 1: line number, 2: points value */
				'error'    => sprintf( __( 'Line %1$d: "%2$s" is not a valid points value.', 'points-and-rewards-for-woocommerce' ), $line, $raw_points ),
			);
		}

		if ( ! $this->wps_wpr_apply_points( $user->ID, $points, $args['mode'], $reason ) ) {
			return array(
				'imported' => false,
				/* translators: 1: line number, 2: email or user id */
				'error'    => sprintf( __( 'Line %1$d: the balance of "%2$s" did not change.', 'points-and-rewards-for-woocommerce' ), $line, $identifier ),
			);
		}

		return array(
			'imported' => true,
			'error'    => '',
		);
	}

	/**
	 * Check an uploaded CSV file ($_FILES entry) and return its temp path.
	 *
	 * @param  array $file array( 'error' => int, 'tmp_name' => string, 'name' => string ).
	 * @return string|WP_Error temp path.
	 */
	public function wps_wpr_check_upload( $file ) {
		$error = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

		if ( UPLOAD_ERR_INI_SIZE === $error || UPLOAD_ERR_FORM_SIZE === $error ) {
			return new WP_Error(
				'wps_wpr_csv_too_large',
				/* translators: %s: maximum upload size */
				sprintf( __( 'The file is larger than the maximum upload size (%s).', 'points-and-rewards-for-woocommerce' ), size_format( wp_max_upload_size() ) )
			);
		}
		if ( UPLOAD_ERR_NO_FILE === $error ) {
			return new WP_Error( 'wps_wpr_csv_missing', __( 'Please choose a CSV file to import.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( UPLOAD_ERR_OK !== $error ) {
			/* translators: %d: PHP upload error code */
			return new WP_Error( 'wps_wpr_csv_upload_failed', sprintf( __( 'The file could not be uploaded (error code %d).', 'points-and-rewards-for-woocommerce' ), $error ) );
		}

		$tmp  = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		if ( '' === $tmp || ! is_uploaded_file( $tmp ) ) {
			return new WP_Error( 'wps_wpr_csv_missing', __( 'Please choose a CSV file to import.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( 'csv' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			return new WP_Error( 'wps_wpr_csv_type', __( 'Please choose a CSV file.', 'points-and-rewards-for-woocommerce' ) );
		}
		return $tmp;
	}

	/**
	 * The pending import job, if any.
	 *
	 * @return array|null
	 */
	public function wps_wpr_get_import_job() {
		$job = get_option( self::JOB_OPTION );
		return ( is_array( $job ) && ! empty( $job['id'] ) ) ? $job : null;
	}

	/**
	 * Store a parsed CSV as an import job, to be applied in batches.
	 *
	 * Any previous unfinished job is discarded.
	 *
	 * @param  array  $csv       parsed CSV from wps_wpr_read_csv().
	 * @param  array  $args      import options (see wps_wpr_import()).
	 * @param  string $file_name uploaded file name, for display.
	 * @return array|WP_Error the job.
	 */
	public function wps_wpr_start_import_job( $csv, $args = array(), $file_name = '' ) {
		if ( ! isset( $args['decimal'] ) ) {
			$args['decimal'] = self::wps_wpr_csv_decimal( $csv );
		}
		$args    = $this->wps_wpr_import_args( $args );
		$headers = isset( $csv['headers'] ) ? (array) $csv['headers'] : array();
		$rows    = isset( $csv['rows'] ) ? (array) $csv['rows'] : array();

		$cols = $this->wps_wpr_resolve_import_columns( $headers, $args );
		if ( is_wp_error( $cols ) ) {
			return $cols;
		}
		if ( empty( $rows ) ) {
			return new WP_Error( 'wps_wpr_csv_no_rows', __( 'The CSV file has no rows to import.', 'points-and-rewards-for-woocommerce' ) );
		}

		$this->wps_wpr_discard_import_job();

		$job = array(
			'id'             => strtolower( wp_generate_password( 16, false ) ),
			'file'           => sanitize_file_name( $file_name ),
			'total'          => count( $rows ),
			'offset'         => 0,
			'imported'       => 0,
			'skipped'        => 0,
			'errors'         => array(),
			'mode'           => $args['mode'],
			'decimal'        => $args['decimal'],
			'default_reason' => $args['default_reason'],
			'user_id'        => get_current_user_id(),
			'started'        => time(),
		);
		// Save the job first so a failure while saving rows can still be discarded.
		update_option( self::JOB_OPTION, $job, false );

		$page = 0;
		foreach ( array_chunk( array_values( $rows ), self::IMPORT_PAGE ) as $chunk ) {
			$compact = array();
			foreach ( $chunk as $row ) {
				$compact[] = $this->wps_wpr_compact_row( $row, $cols );
			}
			update_option( self::ROWS_OPTION . $page, $compact, false );
			++$page;
		}

		return $job;
	}

	/**
	 * Apply the next batch of rows of the pending job.
	 *
	 * Progress is saved after every row, so an interrupted request resumes at
	 * the next row instead of applying rows a second time.
	 *
	 * @param  string $import_id job id.
	 * @param  int    $limit     rows to apply (0 = default batch size).
	 * @return array|WP_Error progress: processed, total, imported, skipped, errors (this batch), done.
	 */
	public function wps_wpr_run_import_batch( $import_id, $limit = 0 ) {
		$job = $this->wps_wpr_get_import_job();
		if ( ! $job || (string) $import_id !== $job['id'] ) {
			return new WP_Error( 'wps_wpr_import_not_found', __( 'This import is no longer available. Please upload the file again.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( ! $this->wps_wpr_acquire_import_lock() ) {
			return new WP_Error( 'wps_wpr_import_busy', __( 'This import is already running in another window.', 'points-and-rewards-for-woocommerce' ) );
		}

		$limit      = $limit > 0 ? (int) $limit : (int) apply_filters( 'wps_wpr_points_import_batch_size', self::IMPORT_BATCH );
		$limit      = max( 1, $limit );
		$end        = min( (int) $job['total'], (int) $job['offset'] + $limit );
		$started    = microtime( true );
		$new_errors = array();
		$page_no    = -1;
		$page       = array();

		try {
			while ( $job['offset'] < $end ) {
				$index = (int) $job['offset'];
				if ( intdiv( $index, self::IMPORT_PAGE ) !== $page_no ) {
					$page_no = intdiv( $index, self::IMPORT_PAGE );
					$page    = get_option( self::ROWS_OPTION . $page_no, array() );
				}
				$row    = isset( $page[ $index % self::IMPORT_PAGE ] ) ? $page[ $index % self::IMPORT_PAGE ] : array( '', '', '' );
				$result = $this->wps_wpr_import_row( $row, $index + 2, $job );

				if ( $result['imported'] ) {
					++$job['imported'];
				} else {
					++$job['skipped'];
					$new_errors[] = $result['error'];
					if ( count( $job['errors'] ) < 50 ) {
						$job['errors'][] = $result['error'];
					}
				}
				$job['offset'] = $index + 1;
				update_option( self::JOB_OPTION, $job, false );

				// Stay well inside max_execution_time; the next request continues.
				if ( microtime( true ) - $started > 20 ) {
					break;
				}
			}
		} finally {
			$this->wps_wpr_release_import_lock();
		}

		$done     = $job['offset'] >= $job['total'];
		$progress = array(
			'import_id' => $job['id'],
			'processed' => (int) $job['offset'],
			'total'     => (int) $job['total'],
			'imported'  => (int) $job['imported'],
			'skipped'   => (int) $job['skipped'],
			'errors'    => $new_errors,
			'done'      => $done,
		);

		if ( $done ) {
			$this->wps_wpr_discard_import_job();
			do_action(
				'wps_wpr_points_import_completed',
				array(
					'imported' => (int) $job['imported'],
					'skipped'  => (int) $job['skipped'],
					'errors'   => $job['errors'],
				),
				array(
					'mode'           => $job['mode'],
					'default_reason' => $job['default_reason'],
				)
			);
		}
		return $progress;
	}

	/**
	 * Delete the pending import job and its rows.
	 *
	 * @return void
	 */
	public function wps_wpr_discard_import_job() {
		$job = get_option( self::JOB_OPTION );
		if ( is_array( $job ) && ! empty( $job['total'] ) ) {
			$pages = (int) ceil( $job['total'] / self::IMPORT_PAGE );
			for ( $i = 0; $i < $pages; $i++ ) {
				delete_option( self::ROWS_OPTION . $i );
			}
		}
		delete_option( self::JOB_OPTION );
	}

	/**
	 * Take the import lock. A lock older than a minute is treated as left over
	 * from a request that died and is taken over.
	 *
	 * @return bool
	 */
	private function wps_wpr_acquire_import_lock() {
		global $wpdb;
		$now = time();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- atomic lock row; must bypass the options cache.
		$taken = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')", self::LOCK_OPTION, $now ) );
		if ( $taken ) {
			return true;
		}
		$since = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", self::LOCK_OPTION ) );
		if ( null !== $since && $now - (int) $since < 60 ) {
			return false;
		}
		$taken = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", $now, self::LOCK_OPTION, (string) $since ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery
		return (bool) $taken;
	}

	/**
	 * Release the import lock.
	 *
	 * @return void
	 */
	private function wps_wpr_release_import_lock() {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => self::LOCK_OPTION ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Nonce and capability check for the import AJAX actions.
	 *
	 * @return void
	 */
	private function wps_wpr_import_ajax_guard() {
		if ( ! check_ajax_referer( self::IMPORT_NONCE, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'points-and-rewards-for-woocommerce' ) ), 403 );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access denied', 'points-and-rewards-for-woocommerce' ) ), 403 );
		}
	}

	/**
	 * AJAX: upload and parse a CSV, and store it as an import job.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_import_start() {
		// A request over post_max_size arrives with empty $_POST and $_FILES.
		if ( empty( $_POST ) && empty( $_FILES ) && ! empty( $_SERVER['CONTENT_LENGTH'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wp_send_json_error(
				array(
					/* translators: %s: maximum upload size */
					'message' => sprintf( __( 'The file is larger than the maximum upload size (%s).', 'points-and-rewards-for-woocommerce' ), size_format( wp_max_upload_size() ) ),
				)
			);
		}
		$this->wps_wpr_import_ajax_guard();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in wps_wpr_import_ajax_guard().
		$field = 'wps_wpr_points_import_file';
		$path  = $this->wps_wpr_check_upload(
			array(
				'error'    => isset( $_FILES[ $field ]['error'] ) ? absint( $_FILES[ $field ]['error'] ) : UPLOAD_ERR_NO_FILE,
				'tmp_name' => isset( $_FILES[ $field ]['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES[ $field ]['tmp_name'] ) ) : '',
				'name'     => isset( $_FILES[ $field ]['name'] ) ? sanitize_file_name( wp_unslash( $_FILES[ $field ]['name'] ) ) : '',
			)
		);
		if ( is_wp_error( $path ) ) {
			wp_send_json_error( array( 'message' => $path->get_error_message() ) );
		}

		$csv = $this->wps_wpr_read_csv( $path );
		if ( is_wp_error( $csv ) ) {
			wp_send_json_error( array( 'message' => $csv->get_error_message() ) );
		}

		$job = $this->wps_wpr_start_import_job(
			$csv,
			array(
				'mode'          => ! empty( $_POST['wps_wpr_import_mode'] ) ? sanitize_key( wp_unslash( $_POST['wps_wpr_import_mode'] ) ) : 'add',
				'user_column'   => ! empty( $_POST['wps_wpr_import_user_column'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_import_user_column'] ) ) : '',
				'points_column' => ! empty( $_POST['wps_wpr_import_points_column'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_import_points_column'] ) ) : '',
				'reason_column' => ! empty( $_POST['wps_wpr_import_reason_column'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_import_reason_column'] ) ) : '',
			),
			isset( $_FILES[ $field ]['name'] ) ? sanitize_file_name( wp_unslash( $_FILES[ $field ]['name'] ) ) : ''
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		if ( is_wp_error( $job ) ) {
			wp_send_json_error( array( 'message' => $job->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'import_id' => $job['id'],
				'total'     => $job['total'],
			)
		);
	}

	/**
	 * AJAX: apply the next batch of the pending import job.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_import_batch() {
		$this->wps_wpr_import_ajax_guard();
		$import_id = isset( $_POST['import_id'] ) ? sanitize_text_field( wp_unslash( $_POST['import_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$progress  = $this->wps_wpr_run_import_batch( $import_id );
		if ( is_wp_error( $progress ) ) {
			wp_send_json_error(
				array(
					'code'    => $progress->get_error_code(),
					'message' => $progress->get_error_message(),
				)
			);
		}
		wp_send_json_success( $progress );
	}

	/**
	 * AJAX: discard the pending import job.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_import_discard() {
		$this->wps_wpr_import_ajax_guard();
		$this->wps_wpr_discard_import_job();
		wp_send_json_success();
	}

	/**
	 * Find a user by email or ID.
	 *
	 * @param  string $identifier email or numeric user ID.
	 * @return WP_User|false
	 */
	public function wps_wpr_find_user( $identifier ) {
		$identifier = trim( (string) $identifier );
		// Undo the formula guard added by wps_wpr_csv_safe() so this plugin's export imports back.
		if ( strlen( $identifier ) > 1 && "'" === $identifier[0] && in_array( $identifier[1], array( '=', '+', '-', '@' ), true ) ) {
			$identifier = substr( $identifier, 1 );
		}
		if ( '' === $identifier ) {
			return false;
		}
		if ( is_email( $identifier ) ) {
			return get_user_by( 'email', $identifier );
		}
		if ( ctype_digit( $identifier ) ) {
			return get_user_by( 'id', (int) $identifier );
		}
		return false;
	}

	/**
	 * Parse a points cell.
	 *
	 * Thousands separators must group digits in threes. Decimals are rounded
	 * down. Values above the maximum (filter wps_wpr_import_max_points) are
	 * rejected so huge numbers cannot overflow.
	 *
	 * @param  string $value   raw cell.
	 * @param  string $decimal decimal separator: "." (1,234.5) or "," (1.234,5).
	 * @return int|null points (>= 0) or null when invalid.
	 */
	public function wps_wpr_parse_points( $value, $decimal = '.' ) {
		$value     = str_replace( array( ' ', "\xC2\xA0" ), '', trim( (string) $value ) );
		$decimal   = ',' === $decimal ? ',' : '.';
		$thousands = ',' === $decimal ? '.' : ',';
		if ( '' === $value ) {
			return null;
		}

		if ( false !== strpos( $value, $thousands ) ) {
			$dec_pos  = strpos( $value, $decimal );
			$int_part = false === $dec_pos ? $value : substr( $value, 0, $dec_pos );
			if ( ! preg_match( '/^\d{1,3}(' . preg_quote( $thousands, '/' ) . '\d{3})+$/', $int_part ) ) {
				return null; // Ambiguous, e.g. "1,5" in a comma-decimal-point file.
			}
			$value = str_replace( $thousands, '', $value );
		}
		if ( ',' === $decimal ) {
			$value = str_replace( ',', '.', $value );
		}
		if ( ! is_numeric( $value ) ) {
			return null;
		}

		$number = (float) $value;
		$max    = min( PHP_INT_MAX, (int) apply_filters( 'wps_wpr_import_max_points', self::MAX_POINTS ) );
		if ( $number < 0 || $number > $max ) {
			return null;
		}
		return (int) floor( $number );
	}

	/**
	 * Apply points to a user and log the change.
	 *
	 * @param  int    $user_id user id.
	 * @param  int    $points  points from the CSV.
	 * @param  string $mode    add|subtract|override.
	 * @param  string $reason  log reason.
	 * @return bool   true when the balance changed.
	 */
	public function wps_wpr_apply_points( $user_id, $points, $mode, $reason ) {
		$current = (int) get_user_meta( $user_id, 'wps_wpr_points', true );

		if ( 'subtract' === $mode ) {
			$new = max( 0, $current - $points );
		} elseif ( 'override' === $mode ) {
			$new = max( 0, $points );
		} else {
			$new = $current + $points;
		}

		$change = $new - $current;
		if ( 0 === $change ) {
			return false;
		}

		update_user_meta( $user_id, 'wps_wpr_points', $new );

		$log = get_user_meta( $user_id, 'points_details', true );
		$log = ! empty( $log ) && is_array( $log ) ? $log : array();

		$log[ self::LOG_KEY ][] = array(
			self::LOG_KEY => abs( $change ),
			'date'        => date_i18n( 'Y-m-d h:i:sa' ),
			'sign'        => $change > 0 ? '+' : '-',
			'reason'      => $reason,
		);
		update_user_meta( $user_id, 'points_details', $log );
		do_action( 'wps_wpr_update_points_log', $user_id );
		do_action( 'wps_wpr_points_imported_for_user', $user_id, $change, $new, $mode );

		return true;
	}
}
