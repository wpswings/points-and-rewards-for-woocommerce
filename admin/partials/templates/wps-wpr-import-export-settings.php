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

$wps_wpr_import_summary = null;
$wps_wpr_import_error   = '';

// Import points from CSV.
if ( isset( $_POST['wps_wpr_import_points_csv'], $_POST['wps_wpr_points_import_nonce'] ) && current_user_can( 'manage_options' ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wpr_points_import_nonce'] ) ), WPS_WPR_Points_Import_Export::IMPORT_NONCE ) ) {

		$wps_wpr_tmp_file  = ! empty( $_FILES['wps_wpr_points_import_file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['wps_wpr_points_import_file']['tmp_name'] ) ) : '';
		$wps_wpr_file_name = ! empty( $_FILES['wps_wpr_points_import_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['wps_wpr_points_import_file']['name'] ) ) : '';

		if ( empty( $wps_wpr_tmp_file ) || ! is_uploaded_file( $wps_wpr_tmp_file ) ) {
			$wps_wpr_import_error = __( 'Please choose a CSV file to import.', 'points-and-rewards-for-woocommerce' );
		} elseif ( 'csv' !== strtolower( pathinfo( $wps_wpr_file_name, PATHINFO_EXTENSION ) ) ) {
			$wps_wpr_import_error = __( 'Please choose a CSV file.', 'points-and-rewards-for-woocommerce' );
		} else {
			$wps_wpr_importer = new WPS_WPR_Points_Import_Export();
			$wps_wpr_csv      = $wps_wpr_importer->wps_wpr_read_csv( $wps_wpr_tmp_file );
			if ( is_wp_error( $wps_wpr_csv ) ) {
				$wps_wpr_import_error = $wps_wpr_csv->get_error_message();
			} else {
				$wps_wpr_result = $wps_wpr_importer->wps_wpr_import(
					$wps_wpr_csv,
					array(
						'mode'          => ! empty( $_POST['wps_wpr_import_mode'] ) ? sanitize_key( wp_unslash( $_POST['wps_wpr_import_mode'] ) ) : 'add',
						'user_column'   => ! empty( $_POST['wps_wpr_import_user_column'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_import_user_column'] ) ) : '',
						'points_column' => ! empty( $_POST['wps_wpr_import_points_column'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_import_points_column'] ) ) : '',
						'reason_column' => ! empty( $_POST['wps_wpr_import_reason_column'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wpr_import_reason_column'] ) ) : '',
					)
				);
				if ( is_wp_error( $wps_wpr_result ) ) {
					$wps_wpr_import_error = $wps_wpr_result->get_error_message();
				} else {
					$wps_wpr_import_summary = $wps_wpr_result;
				}
			}
		}
	} else {
		$wps_wpr_import_error = __( 'Security check failed.', 'points-and-rewards-for-woocommerce' );
	}
}
?>

<div class="wps_wpr_import_export_main_wrappers">
	<?php if ( ! empty( $wps_wpr_import_error ) ) : ?>
		<div class="notice notice-error is-dismissible wps_wpr_import_notice"><p><?php echo esc_html( $wps_wpr_import_error ); ?></p></div>
	<?php elseif ( is_array( $wps_wpr_import_summary ) ) : ?>
		<div class="notice notice-success is-dismissible wps_wpr_import_notice">
			<p id="wps_wpr_import_result">
				<?php
				/* translators: 1: imported rows, 2: skipped rows */
				echo esc_html( sprintf( __( 'Import finished: %1$d users updated, %2$d rows skipped.', 'points-and-rewards-for-woocommerce' ), $wps_wpr_import_summary['imported'], $wps_wpr_import_summary['skipped'] ) );
				?>
			</p>
			<?php if ( ! empty( $wps_wpr_import_summary['errors'] ) ) : ?>
				<ul class="wps_wpr_import_errors">
					<?php foreach ( array_slice( $wps_wpr_import_summary['errors'], 0, 50 ) as $wps_wpr_import_row_error ) : ?>
						<li><?php echo esc_html( $wps_wpr_import_row_error ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

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
			<div class="wps_wpr_section_content">
				<input type="hidden" name="wps_wpr_points_import_nonce" value="<?php echo esc_attr( wp_create_nonce( WPS_WPR_Points_Import_Export::IMPORT_NONCE ) ); ?>">
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
						<span class="wps_wpr_label_notice"><?php esc_html_e( 'Use "Replace" when migrating balances from another plugin.', 'points-and-rewards-for-woocommerce' ); ?></span>
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
	</main>
	<input type="submit" name="wps_wpr_import_points_csv" class="button-primary woocommerce-save-button wps_wpr_save_changes" id="wps_wpr_import_points_csv" value="<?php esc_attr_e( 'Import Points', 'points-and-rewards-for-woocommerce' ); ?>">
</div>
