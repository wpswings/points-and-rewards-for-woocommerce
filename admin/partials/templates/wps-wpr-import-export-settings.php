<?php
/**
 * This is the setting template
 *
 * Points import / export.
 *
 * @link       https://wpswings.com/
 * @since      2.10.6
 *
 * @package    points-and-rewards-for-wooCommerce
 * @subpackage points-and-rewards-for-wooCommerce/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Points CSV import runs in AJAX batches (see admin/js/wps-wpr-points-import.js).
$wps_wpr_importer   = new WPS_WPR_Points_Import_Export();
$wps_wpr_import_job = $wps_wpr_importer->wps_wpr_get_import_job();
$wps_wpr_max_upload = wp_max_upload_size();
wp_enqueue_script( 'wps-wpr-points-import', WPS_RWPR_DIR_URL . 'admin/js/wps-wpr-points-import.js', array( 'jquery' ), (string) filemtime( WPS_RWPR_DIR_PATH . 'admin/js/wps-wpr-points-import.js' ), true );
wp_localize_script(
	'wps-wpr-points-import',
	'wpsWprPointsImport',
	array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( WPS_WPR_Points_Import_Export::IMPORT_NONCE ),
		'maxUpload' => (int) $wps_wpr_max_upload,
		'i18n'      => array(
			'noFile'         => __( 'Please choose a CSV file to import.', 'points-and-rewards-for-woocommerce' ),
			'notCsv'         => __( 'Please choose a CSV file.', 'points-and-rewards-for-woocommerce' ),
			/* translators: %s: maximum upload size */
			'tooLarge'       => sprintf( __( 'The file is larger than the maximum upload size (%s).', 'points-and-rewards-for-woocommerce' ), size_format( $wps_wpr_max_upload ) ),
			'uploading'      => __( 'Uploading and checking the file...', 'points-and-rewards-for-woocommerce' ),
			/* translators: 1: processed rows, 2: total rows */
			'progress'       => __( '%1$s of %2$s rows processed.', 'points-and-rewards-for-woocommerce' ),
			/* translators: 1: imported rows, 2: skipped rows */
			'done'           => __( 'Import finished: %1$s rows imported, %2$s rows skipped.', 'points-and-rewards-for-woocommerce' ),
			'uploadFailed'   => __( 'The request failed. Please try again.', 'points-and-rewards-for-woocommerce' ),
			'batchFailed'    => __( 'The import stopped before it finished. Resume it to apply the remaining rows; rows already applied are not applied again.', 'points-and-rewards-for-woocommerce' ),
			'resume'         => __( 'Resume import', 'points-and-rewards-for-woocommerce' ),
			'confirmDiscard' => __( 'Discard the unfinished import? Rows already applied stay applied.', 'points-and-rewards-for-woocommerce' ),
			'moreErrors'     => __( 'Only the first 50 messages are shown.', 'points-and-rewards-for-woocommerce' ),
			'leave'          => __( 'The import is still running. Leave the page?', 'points-and-rewards-for-woocommerce' ),
		),
	)
);

// Migration from other loyalty plugins (AJAX, batched).
wp_enqueue_script( 'wps-wpr-points-migration', WPS_RWPR_DIR_URL . 'admin/js/wps-wpr-points-migration.js', array( 'jquery' ), REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION, true );
wp_localize_script(
	'wps-wpr-points-migration',
	'wpsWprMigration',
	array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( WPS_WPR_Points_Migration::NONCE ),
		'i18n'    => array(
			'loading'   => __( 'Loading preview...', 'points-and-rewards-for-woocommerce' ),
			'failed'    => __( 'The request failed. Please try again.', 'points-and-rewards-for-woocommerce' ),
			/* translators: 1: customers, 2: points */
			'totals'    => __( '%1$s customers with a total of %2$s points found.', 'points-and-rewards-for-woocommerce' ),
			'customer'  => __( 'Customer', 'points-and-rewards-for-woocommerce' ),
			'points'    => __( 'Points', 'points-and-rewards-for-woocommerce' ),
			'match'     => __( 'Match', 'points-and-rewards-for-woocommerce' ),
			'matched'   => __( 'Found', 'points-and-rewards-for-woocommerce' ),
			'unmatched' => __( 'No matching customer', 'points-and-rewards-for-woocommerce' ),
			'confirm'   => __( 'Start the migration? Customer balances will be updated.', 'points-and-rewards-for-woocommerce' ),
			/* translators: %s: processed rows */
			'running'   => __( 'Migrating... %s customers processed.', 'points-and-rewards-for-woocommerce' ),
			/* translators: 1: updated, 2: skipped, 3: already migrated */
			'done'      => __( 'Migration finished: %1$s customers updated, %2$s skipped, %3$s already migrated.', 'points-and-rewards-for-woocommerce' ),
		),
	)
);
$wps_wpr_migration_sources = WPS_WPR_Points_Migration::wps_wpr_get_sources();
?>

<div class="wps_wpr_import_export_main_wrappers">
	<div id="wps_wpr_import_messages" aria-live="polite">
		<?php if ( $wps_wpr_import_job ) : ?>
			<div class="notice notice-warning wps_wpr_import_resume_notice">
				<p>
					<?php
					/* translators: 1: file name, 2: processed rows, 3: total rows */
					echo esc_html( sprintf( __( 'An import of "%1$s" did not finish: %2$d of %3$d rows processed. Resume it to apply the remaining rows; rows already applied are not applied again.', 'points-and-rewards-for-woocommerce' ), $wps_wpr_import_job['file'], $wps_wpr_import_job['offset'], $wps_wpr_import_job['total'] ) );
					?>
				</p>
				<p>
					<button type="button" class="button button-primary wps_wpr_import_resume_btn" data-import-id="<?php echo esc_attr( $wps_wpr_import_job['id'] ); ?>"><?php esc_html_e( 'Resume import', 'points-and-rewards-for-woocommerce' ); ?></button>
					<button type="button" class="button wps_wpr_import_discard_btn"><?php esc_html_e( 'Discard', 'points-and-rewards-for-woocommerce' ); ?></button>
				</p>
			</div>
		<?php endif; ?>
	</div>
	<div class="wps_wpr_import_progress" hidden>
		<progress id="wps_wpr_import_progress_bar" max="100" value="0"></progress>
		<span id="wps_wpr_import_progress_text"></span>
	</div>

	<main class="wps_wpr_main_import_export_wrapper">
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Export Points', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content">
				<input type="hidden" name="wps_wpr_points_export_nonce" value="<?php echo esc_attr( wp_create_nonce( WPS_WPR_Points_Import_Export::EXPORT_NONCE ) ); ?>">
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_export_only_with_points" class="wps_wpr_general_label"><?php esc_html_e( 'Only Users With Points', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" name="wps_wpr_export_only_with_points" id="wps_wpr_export_only_with_points" value="yes" checked>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Leave unchecked to export every user, including those with 0 points.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label class="wps_wpr_general_label"><?php esc_html_e( 'Download', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="submit" name="wps_wpr_export_points_csv" id="wps_wpr_export_points_csv" class="button" value="<?php esc_attr_e( 'Export Points CSV', 'points-and-rewards-for-woocommerce' ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Columns: User Email, Points, User ID, Username. The file can be imported back below.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
			</div>
		</section>

		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Import Points', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content wps_wpr_points_import_fields">
				<noscript><p class="wps_wpr_label_notice"><?php esc_html_e( 'JavaScript is required to import points.', 'points-and-rewards-for-woocommerce' ); ?></p></noscript>
				<p class="wps_wpr_label_notice"><?php esc_html_e( 'Import a CSV exported from this plugin or from another loyalty plugin. Columns are detected from common header names (email, user id, points, balance, reason). If detection fails, enter the header name or column number below.', 'points-and-rewards-for-woocommerce' ); ?></p>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_points_import_file" class="wps_wpr_general_label"><?php esc_html_e( 'CSV File', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="file" name="wps_wpr_points_import_file" id="wps_wpr_points_import_file" accept=".csv,text/csv">
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_import_mode" class="wps_wpr_general_label"><?php esc_html_e( 'Import Mode', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<select name="wps_wpr_import_mode" id="wps_wpr_import_mode">
							<option value="add"><?php esc_html_e( 'Add to current balance', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="override"><?php esc_html_e( 'Replace current balance', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="subtract"><?php esc_html_e( 'Subtract from current balance', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Use "Replace" when migrating balances from another plugin. "Add" and "Subtract" change balances again every time the same file is imported.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_import_user_column" class="wps_wpr_general_label"><?php esc_html_e( 'User Email / ID Column', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" name="wps_wpr_import_user_column" id="wps_wpr_import_user_column" class="input-text" placeholder="<?php esc_attr_e( 'Auto-detect', 'points-and-rewards-for-woocommerce' ); ?>">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Header name or column number (1 = first column). The column may hold emails or user IDs.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_import_points_column" class="wps_wpr_general_label"><?php esc_html_e( 'Points Column', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" name="wps_wpr_import_points_column" id="wps_wpr_import_points_column" class="input-text" placeholder="<?php esc_attr_e( 'Auto-detect', 'points-and-rewards-for-woocommerce' ); ?>">
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_import_reason_column" class="wps_wpr_general_label"><?php esc_html_e( 'Reason Column (optional)', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="text" name="wps_wpr_import_reason_column" id="wps_wpr_import_reason_column" class="input-text" placeholder="<?php esc_attr_e( 'Auto-detect', 'points-and-rewards-for-woocommerce' ); ?>">
					</div>
				</article>
			</div>
		</section>
		<section class="wps_wpr_general_row_wrap">
			<div class="wps_wpr_general_sign_title"><?php esc_html_e( 'Migrate From Another Plugin', 'points-and-rewards-for-woocommerce' ); ?></div>
			<div class="wps_wpr_section_content wps_wpr_migration_wrap">
				<p class="wps_wpr_label_notice"><?php esc_html_e( 'Copy customer points balances directly from another loyalty plugin\'s data on this site. Keep the other plugin\'s data in place until the migration finishes. Customers are matched by user ID or email.', 'points-and-rewards-for-woocommerce' ); ?></p>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_migration_mode" class="wps_wpr_general_label"><?php esc_html_e( 'Migration Mode', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<select id="wps_wpr_migration_mode">
							<option value="add"><?php esc_html_e( 'Add to current balance', 'points-and-rewards-for-woocommerce' ); ?></option>
							<option value="override"><?php esc_html_e( 'Replace current balance', 'points-and-rewards-for-woocommerce' ); ?></option>
						</select>
					</div>
				</article>
				<article class="wps_wpr_general_row">
					<label for="wps_wpr_migration_allow_reimport" class="wps_wpr_general_label"><?php esc_html_e( 'Allow Re-import', 'points-and-rewards-for-woocommerce' ); ?></label>
					<div class="wps_wpr_general_content">
						<input type="checkbox" id="wps_wpr_migration_allow_reimport" value="yes">
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'In "Add" mode, customers already migrated from a source are skipped so points are not counted twice. Tick to add them again.', 'points-and-rewards-for-woocommerce' ); ?></span>
					</div>
				</article>
				<?php foreach ( $wps_wpr_migration_sources as $wps_wpr_source ) : ?>
					<?php $wps_wpr_source_found = $wps_wpr_source->is_available(); ?>
					<article class="wps_wpr_general_row wps_wpr_migration_source" data-source="<?php echo esc_attr( $wps_wpr_source->get_id() ); ?>" id="wps_wpr_migration_source_<?php echo esc_attr( $wps_wpr_source->get_id() ); ?>">
						<label class="wps_wpr_general_label"><?php echo esc_html( $wps_wpr_source->get_label() ); ?></label>
						<div class="wps_wpr_general_content">
							<?php if ( $wps_wpr_source_found ) : ?>
								<span class="wps_wpr_migration_detected"><?php esc_html_e( 'Data detected', 'points-and-rewards-for-woocommerce' ); ?></span>
								<button type="button" class="button wps_wpr_migration_preview_btn"><?php esc_html_e( 'Preview', 'points-and-rewards-for-woocommerce' ); ?></button>
								<button type="button" class="button button-primary wps_wpr_migration_run_btn" disabled><?php esc_html_e( 'Migrate Points', 'points-and-rewards-for-woocommerce' ); ?></button>
								<div class="wps_wpr_migration_preview"></div>
								<div class="wps_wpr_migration_status" aria-live="polite"></div>
							<?php else : ?>
								<span class="wps_wpr_label_notice wps_wpr_migration_not_detected"><?php esc_html_e( 'Not detected on this site.', 'points-and-rewards-for-woocommerce' ); ?></span>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	</main>
	<input type="button" name="wps_wpr_import_points_csv" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_import_points_csv" value="<?php esc_attr_e( 'Import Points', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
