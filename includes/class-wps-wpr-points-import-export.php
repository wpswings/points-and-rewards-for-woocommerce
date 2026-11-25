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
	 * @return array|WP_Error array( 'headers' => array, 'rows' => array ).
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
			'headers' => $headers,
			'rows'    => $rows,
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
	 * Import a parsed CSV.
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
	 * }
	 * @return array|WP_Error summary: imported, skipped, errors (row messages).
	 */
	public function wps_wpr_import( $csv, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'mode'           => 'add',
				'user_column'    => '',
				'points_column'  => '',
				'reason_column'  => '',
				'default_reason' => __( 'Points imported from CSV', 'points-and-rewards-for-woocommerce' ),
			)
		);
		$mode = in_array( $args['mode'], array( 'add', 'subtract', 'override' ), true ) ? $args['mode'] : 'add';

		$headers = isset( $csv['headers'] ) ? (array) $csv['headers'] : array();
		$rows    = isset( $csv['rows'] ) ? (array) $csv['rows'] : array();

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

		$summary = array(
			'imported' => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		foreach ( $rows as $index => $row ) {
			$line       = $index + 2; // Header is line 1.
			$identifier = isset( $row[ $user_col ] ) ? trim( (string) $row[ $user_col ] ) : '';
			$raw_points = isset( $row[ $points_col ] ) ? trim( (string) $row[ $points_col ] ) : '';
			$reason     = ( -1 !== $reason_col && isset( $row[ $reason_col ] ) && '' !== trim( (string) $row[ $reason_col ] ) ) ? sanitize_text_field( $row[ $reason_col ] ) : $args['default_reason'];

			$user = $this->wps_wpr_find_user( $identifier );
			if ( ! $user ) {
				++$summary['skipped'];
				/* translators: 1: line number, 2: email or user id */
				$summary['errors'][] = sprintf( __( 'Line %1$d: no user found for "%2$s".', 'points-and-rewards-for-woocommerce' ), $line, $identifier );
				continue;
			}

			$points = $this->wps_wpr_parse_points( $raw_points );
			if ( null === $points ) {
				++$summary['skipped'];
				/* translators: 1: line number, 2: points value */
				$summary['errors'][] = sprintf( __( 'Line %1$d: "%2$s" is not a valid points value.', 'points-and-rewards-for-woocommerce' ), $line, $raw_points );
				continue;
			}

			if ( $this->wps_wpr_apply_points( $user->ID, $points, $mode, $reason ) ) {
				++$summary['imported'];
			} else {
				++$summary['skipped'];
			}
		}

		do_action( 'wps_wpr_points_import_completed', $summary, $args );
		return $summary;
	}

	/**
	 * Find a user by email or ID.
	 *
	 * @param  string $identifier email or numeric user ID.
	 * @return WP_User|false
	 */
	public function wps_wpr_find_user( $identifier ) {
		$identifier = trim( (string) $identifier );
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
	 * Parse a points cell. Accepts thousands separators and decimals (rounded down).
	 *
	 * @param  string $value raw cell.
	 * @return int|null points (>= 0) or null when invalid.
	 */
	public function wps_wpr_parse_points( $value ) {
		$value = str_replace( array( ',', ' ' ), '', trim( (string) $value ) );
		if ( '' === $value || ! is_numeric( $value ) ) {
			return null;
		}
		$points = (int) floor( (float) $value );
		return $points < 0 ? null : $points;
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
