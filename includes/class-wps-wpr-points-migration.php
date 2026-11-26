<?php
/**
 * Points migration from other loyalty plugins.
 *
 * Reads balances directly from another plugin's data (see
 * includes/migration/class-wps-wpr-migration-sources.php) and applies them
 * with the CSV importer's apply logic, in AJAX batches.
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

require_once __DIR__ . '/migration/class-wps-wpr-migration-sources.php';

/**
 * Migration controller.
 */
class WPS_WPR_Points_Migration {

	/**
	 * Nonce action for migration requests.
	 */
	const NONCE = 'wps-wpr-points-migration';

	/**
	 * Rows processed per AJAX request.
	 */
	const BATCH_SIZE = 100;

	/**
	 * Prefix of the per-source user marker meta.
	 */
	const MARKER_PREFIX = '_wps_wpr_migrated_';

	/**
	 * Prefix of the option holding a source's unfinished migration run.
	 */
	const RUN_OPTION_PREFIX = 'wps_wpr_migration_run_';

	/**
	 * Prefix of the user meta holding the id of the run that last processed the customer.
	 */
	const RUN_MARKER_PREFIX = '_wps_wpr_migration_run_';

	/**
	 * Registered sources, keyed by id.
	 *
	 * @return WPS_WPR_Migration_Source[]
	 */
	public static function wps_wpr_get_sources() {
		$sources = array(
			new WPS_WPR_Migration_Source_WPLoyalty(),
			new WPS_WPR_Migration_Source_WC_Points_Rewards(),
			new WPS_WPR_Migration_Source_YITH(),
			new WPS_WPR_Migration_Source_MyCred(),
		);

		/**
		 * Filter the migration sources. Add objects implementing WPS_WPR_Migration_Source.
		 *
		 * @param WPS_WPR_Migration_Source[] $sources sources.
		 */
		$sources = apply_filters( 'wps_wpr_migration_sources', $sources );

		$keyed = array();
		foreach ( (array) $sources as $source ) {
			if ( $source instanceof WPS_WPR_Migration_Source ) {
				$keyed[ sanitize_key( $source->get_id() ) ] = $source;
			}
		}
		return $keyed;
	}

	/**
	 * Get one source.
	 *
	 * @param  string $id source id.
	 * @return WPS_WPR_Migration_Source|null
	 */
	public static function wps_wpr_get_source( $id ) {
		$sources = self::wps_wpr_get_sources();
		$id      = sanitize_key( $id );
		return isset( $sources[ $id ] ) ? $sources[ $id ] : null;
	}

	/**
	 * Whether the current user may migrate.
	 *
	 * @return bool
	 */
	public static function wps_wpr_can_migrate() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Preview a source.
	 *
	 * @param  string $id source id.
	 * @return array|WP_Error
	 */
	public function wps_wpr_preview( $id, $args = array() ) {
		$source = self::wps_wpr_get_source( $id );
		if ( ! $source ) {
			return new WP_Error( 'wps_wpr_unknown_source', __( 'Unknown migration source.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( ! $source->is_available() ) {
			/* translators: %s: source plugin name */
			return new WP_Error( 'wps_wpr_source_missing', sprintf( __( 'No %s data was found on this site.', 'points-and-rewards-for-woocommerce' ), $source->get_label() ) );
		}

		$this->wps_wpr_configure_source( $source, $args );

		$sample = array();
		foreach ( $source->get_rows( 0, 10 ) as $row ) {
			$user     = $this->wps_wpr_match_user( $row );
			$sample[] = array(
				'email'   => $row['email'],
				'user_id' => $user ? $user->ID : 0,
				'points'  => (int) $row['points'],
				'matched' => (bool) $user,
			);
		}

		// Count only records that map to an existing customer; the rest are reported separately.
		if ( method_exists( $source, 'get_match_summary' ) ) {
			$summary = $source->get_match_summary();
		} else {
			$records = $source->count_rows();
			$points  = $source->get_total_points();
			$summary = array(
				'customers'     => $records,
				'points'        => $points,
				'records'       => $records,
				'record_points' => $points,
			);
		}

		return array(
			'source'           => $source->get_id(),
			'label'            => $source->get_label(),
			'customers'        => (int) $summary['customers'],
			'points'           => (int) $summary['points'],
			'unmatched'        => max( 0, (int) $summary['records'] - (int) $summary['customers'] ),
			'unmatched_points' => max( 0, (int) $summary['record_points'] - (int) $summary['points'] ),
			'sample'           => $sample,
			'run'              => self::wps_wpr_get_run( $source->get_id() ),
		);
	}

	/**
	 * Apply per-request source options (e.g. include banned WPLoyalty customers).
	 *
	 * @param  WPS_WPR_Migration_Source $source source.
	 * @param  array                    $args   include_banned (bool).
	 * @return void
	 */
	private function wps_wpr_configure_source( $source, $args ) {
		if ( method_exists( $source, 'set_include_banned' ) ) {
			$source->set_include_banned( ! empty( $args['include_banned'] ) );
		}
	}

	/**
	 * The unfinished migration run of a source, if any.
	 *
	 * @param  string $id source id.
	 * @return array|null run_id, source, mode, allow_reimport, include_banned, offset, started, total, processed, imported, skipped, already.
	 */
	public static function wps_wpr_get_run( $id ) {
		$run = get_option( self::RUN_OPTION_PREFIX . sanitize_key( $id ) );
		return is_array( $run ) && ! empty( $run['run_id'] ) ? $run : null;
	}

	/**
	 * Start a migration run. The run state (id, mode, offset and totals) is kept in an
	 * option so an interrupted run can be resumed from where it stopped.
	 *
	 * @param  string $id      source id.
	 * @param  array  $args    mode, allow_reimport, include_banned.
	 * @param  bool   $restart discard an unfinished run instead of refusing to start.
	 * @return array|WP_Error the new run.
	 */
	public function wps_wpr_start_run( $id, $args = array(), $restart = false ) {
		$source = self::wps_wpr_get_source( $id );
		if ( ! $source ) {
			return new WP_Error( 'wps_wpr_unknown_source', __( 'Unknown migration source.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( ! $source->is_available() ) {
			/* translators: %s: source plugin name */
			return new WP_Error( 'wps_wpr_source_missing', sprintf( __( 'No %s data was found on this site.', 'points-and-rewards-for-woocommerce' ), $source->get_label() ) );
		}
		$existing = self::wps_wpr_get_run( $source->get_id() );
		if ( $existing && ! $restart ) {
			return new WP_Error( 'wps_wpr_run_exists', __( 'An unfinished migration exists for this source. Resume it or start over.', 'points-and-rewards-for-woocommerce' ), array( 'run' => $existing ) );
		}
		if ( $existing ) {
			$this->wps_wpr_discard_run( $source->get_id() );
		}

		$this->wps_wpr_configure_source( $source, $args );
		$run = array(
			'run_id'         => wp_generate_uuid4(),
			'source'         => $source->get_id(),
			'mode'           => isset( $args['mode'] ) && 'override' === $args['mode'] ? 'override' : 'add',
			'allow_reimport' => ! empty( $args['allow_reimport'] ),
			'include_banned' => ! empty( $args['include_banned'] ),
			'offset'         => 0,
			'started'        => time(),
			'total'          => $source->count_rows(),
			'processed'      => 0,
			'imported'       => 0,
			'skipped'        => 0,
			'already'        => 0,
		);
		update_option( self::RUN_OPTION_PREFIX . $source->get_id(), $run, false );
		return $run;
	}

	/**
	 * Forget a source's run state and its per-run customer markers.
	 *
	 * @param  string $id source id.
	 * @return void
	 */
	public function wps_wpr_discard_run( $id ) {
		$id = sanitize_key( $id );
		delete_option( self::RUN_OPTION_PREFIX . $id );
		delete_metadata( 'user', 0, self::RUN_MARKER_PREFIX . $id, '', true );
	}

	/**
	 * Process the next batch of a run, from the offset saved on the server.
	 *
	 * @param  string $id     source id.
	 * @param  string $run_id run id returned by wps_wpr_start_run().
	 * @param  int    $batch  rows per batch.
	 * @return array|WP_Error batch result plus the updated `run`.
	 */
	public function wps_wpr_continue_run( $id, $run_id, $batch = self::BATCH_SIZE ) {
		$run = self::wps_wpr_get_run( $id );
		if ( ! $run || ! hash_equals( (string) $run['run_id'], (string) $run_id ) ) {
			return new WP_Error( 'wps_wpr_run_missing', __( 'This migration is no longer running. Reload the page and try again.', 'points-and-rewards-for-woocommerce' ) );
		}

		$result = $this->wps_wpr_run_batch(
			$id,
			$run['offset'],
			array(
				'mode'           => $run['mode'],
				'allow_reimport' => $run['allow_reimport'],
				'include_banned' => $run['include_banned'],
				'batch'          => $batch,
				'run_id'         => $run['run_id'],
			)
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$run['offset'] = $result['next_offset'];
		foreach ( array( 'processed', 'imported', 'skipped', 'already' ) as $key ) {
			$run[ $key ] += $result[ $key ];
		}
		if ( $result['done'] ) {
			$this->wps_wpr_discard_run( $id );
		} else {
			update_option( self::RUN_OPTION_PREFIX . sanitize_key( $id ), $run, false );
		}
		$result['run'] = $run;
		return $result;
	}

	/**
	 * Match a source row to a WordPress user: user ID first, then email.
	 *
	 * @param  array $row source row.
	 * @return WP_User|false
	 */
	public function wps_wpr_match_user( $row ) {
		if ( ! empty( $row['user_id'] ) ) {
			$user = get_user_by( 'id', (int) $row['user_id'] );
			if ( $user ) {
				return $user;
			}
		}
		if ( ! empty( $row['email'] ) && is_email( $row['email'] ) ) {
			return get_user_by( 'email', $row['email'] );
		}
		return false;
	}

	/**
	 * Migrate one batch.
	 *
	 * @param  string $id   source id.
	 * @param  int    $offset rows already processed.
	 * @param  array  $args mode (add|override), allow_reimport (bool), batch (int), include_banned (bool),
	 *                      run_id (string: customers already processed in this run are never applied again).
	 * @return array|WP_Error
	 */
	public function wps_wpr_run_batch( $id, $offset, $args = array() ) {
		$source = self::wps_wpr_get_source( $id );
		if ( ! $source ) {
			return new WP_Error( 'wps_wpr_unknown_source', __( 'Unknown migration source.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( ! $source->is_available() ) {
			/* translators: %s: source plugin name */
			return new WP_Error( 'wps_wpr_source_missing', sprintf( __( 'No %s data was found on this site.', 'points-and-rewards-for-woocommerce' ), $source->get_label() ) );
		}

		$args = wp_parse_args(
			$args,
			array(
				'mode'           => 'add',
				'allow_reimport' => false,
				'batch'          => self::BATCH_SIZE,
				'include_banned' => false,
				'run_id'         => '',
			)
		);
		$this->wps_wpr_configure_source( $source, $args );
		$run_id     = (string) $args['run_id'];
		$run_marker = self::RUN_MARKER_PREFIX . $source->get_id();
		$mode   = 'override' === $args['mode'] ? 'override' : 'add';
		$batch  = max( 1, (int) $args['batch'] );
		$offset = max( 0, (int) $offset );
		$marker = self::MARKER_PREFIX . $source->get_id();

		/* translators: %s: source plugin name */
		$reason   = sprintf( __( 'Migrated from %s', 'points-and-rewards-for-woocommerce' ), $source->get_label() );
		$importer = new WPS_WPR_Points_Import_Export();
		$rows     = $source->get_rows( $offset, $batch );

		$result = array(
			'processed'   => count( $rows ),
			'imported'    => 0,
			'skipped'     => 0,
			'already'     => 0,
			'errors'      => array(),
			'next_offset' => $offset + count( $rows ),
			'done'        => count( $rows ) < $batch,
		);

		foreach ( $rows as $row ) {
			$user = $this->wps_wpr_match_user( $row );
			if ( ! $user ) {
				++$result['skipped'];
				/* translators: %s: email or user id */
				$result['errors'][] = sprintf( __( 'No matching customer for %s.', 'points-and-rewards-for-woocommerce' ), ! empty( $row['email'] ) ? $row['email'] : '#' . (int) $row['user_id'] );
				continue;
			}

			// Within one run a customer is applied at most once, even with re-import allowed
			// (e.g. when an interrupted batch is resumed).
			if ( '' !== $run_id && get_user_meta( $user->ID, $run_marker, true ) === $run_id ) {
				++$result['already'];
				continue;
			}

			// In add mode a customer is only migrated once per source, unless re-import is allowed.
			if ( 'add' === $mode && ! $args['allow_reimport'] && get_user_meta( $user->ID, $marker, true ) ) {
				++$result['already'];
				continue;
			}

			$points = max( 0, (int) $row['points'] );
			if ( $importer->wps_wpr_apply_points( $user->ID, $points, $mode, $reason ) ) {
				++$result['imported'];
				// Mark the customer only once the balance has really changed.
				update_user_meta( $user->ID, $marker, time() );
			} else {
				++$result['skipped'];
			}
			if ( '' !== $run_id ) {
				update_user_meta( $user->ID, $run_marker, $run_id );
			}
		}

		if ( $result['done'] ) {
			do_action( 'wps_wpr_points_migration_completed', $source->get_id(), $mode );
		}

		return $result;
	}

	/**
	 * Verify an AJAX request and return the source id.
	 *
	 * @return string
	 */
	private function wps_wpr_verify_request() {
		check_ajax_referer( self::NONCE, 'nonce' );
		if ( ! self::wps_wpr_can_migrate() ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to migrate points.', 'points-and-rewards-for-woocommerce' ) ), 403 );
		}
		return isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';
	}

	/**
	 * Request flag: include banned WPLoyalty customers.
	 *
	 * @return bool
	 */
	private function wps_wpr_request_include_banned() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in wps_wpr_verify_request().
		return ! empty( $_POST['include_banned'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['include_banned'] ) );
	}

	/**
	 * AJAX: preview a source.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_preview() {
		$id     = $this->wps_wpr_verify_request();
		$result = $this->wps_wpr_preview( $id, array( 'include_banned' => $this->wps_wpr_request_include_banned() ) );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( $result );
	}

	/**
	 * AJAX: start (or start over) a migration run.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_start() {
		$id = $this->wps_wpr_verify_request();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in wps_wpr_verify_request().
		$mode    = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'add';
		$again   = ! empty( $_POST['allow_reimport'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['allow_reimport'] ) );
		$restart = ! empty( $_POST['restart'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['restart'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$run = $this->wps_wpr_start_run(
			$id,
			array(
				'mode'           => $mode,
				'allow_reimport' => $again,
				'include_banned' => $this->wps_wpr_request_include_banned(),
			),
			$restart
		);
		if ( is_wp_error( $run ) ) {
			$data = (array) $run->get_error_data();
			wp_send_json_error(
				array(
					'message' => $run->get_error_message(),
					'code'    => $run->get_error_code(),
					'run'     => isset( $data['run'] ) ? $data['run'] : null,
				)
			);
		}
		wp_send_json_success( array( 'run' => $run ) );
	}

	/**
	 * AJAX: migrate the next batch of a run.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_run() {
		$id = $this->wps_wpr_verify_request();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in wps_wpr_verify_request().
		$run_id = isset( $_POST['run_id'] ) ? sanitize_text_field( wp_unslash( $_POST['run_id'] ) ) : '';

		$result = $this->wps_wpr_continue_run( $id, $run_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( $result );
	}

	/**
	 * AJAX: discard an unfinished run.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_discard() {
		$id = $this->wps_wpr_verify_request();
		$this->wps_wpr_discard_run( $id );
		wp_send_json_success();
	}
}
