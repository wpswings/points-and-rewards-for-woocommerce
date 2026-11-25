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
		return current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Preview a source.
	 *
	 * @param  string $id source id.
	 * @return array|WP_Error
	 */
	public function wps_wpr_preview( $id ) {
		$source = self::wps_wpr_get_source( $id );
		if ( ! $source ) {
			return new WP_Error( 'wps_wpr_unknown_source', __( 'Unknown migration source.', 'points-and-rewards-for-woocommerce' ) );
		}
		if ( ! $source->is_available() ) {
			/* translators: %s: source plugin name */
			return new WP_Error( 'wps_wpr_source_missing', sprintf( __( 'No %s data was found on this site.', 'points-and-rewards-for-woocommerce' ), $source->get_label() ) );
		}

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

		return array(
			'source'    => $source->get_id(),
			'label'     => $source->get_label(),
			'customers' => $source->count_rows(),
			'points'    => $source->get_total_points(),
			'sample'    => $sample,
		);
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
	 * @param  array  $args mode (add|override), allow_reimport (bool), batch (int).
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
			)
		);
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

			// In add mode a customer is only migrated once per source, unless re-import is allowed.
			if ( 'add' === $mode && ! $args['allow_reimport'] && get_user_meta( $user->ID, $marker, true ) ) {
				++$result['already'];
				continue;
			}

			$points = max( 0, (int) $row['points'] );
			if ( $importer->wps_wpr_apply_points( $user->ID, $points, $mode, $reason ) ) {
				++$result['imported'];
			} else {
				++$result['skipped'];
			}
			update_user_meta( $user->ID, $marker, time() );
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
	 * AJAX: preview a source.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_preview() {
		$id     = $this->wps_wpr_verify_request();
		$result = $this->wps_wpr_preview( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( $result );
	}

	/**
	 * AJAX: migrate one batch.
	 *
	 * @return void
	 */
	public function wps_wpr_ajax_run() {
		$id = $this->wps_wpr_verify_request();
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in wps_wpr_verify_request().
		$offset = isset( $_POST['offset'] ) ? absint( wp_unslash( $_POST['offset'] ) ) : 0;
		$mode   = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'add';
		$again  = ! empty( $_POST['allow_reimport'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['allow_reimport'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$result = $this->wps_wpr_run_batch(
			$id,
			$offset,
			array(
				'mode'           => $mode,
				'allow_reimport' => $again,
			)
		);
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( $result );
	}
}
