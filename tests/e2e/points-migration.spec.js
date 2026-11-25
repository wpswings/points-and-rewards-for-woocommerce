/**
 * WPS-8327 points migration e2e tests.
 *
 * Needs a logged-in admin (WP_STORAGE_STATE). The WPLoyalty test expects a
 * seeded `{prefix}wlr_users` table holding the admin's email; it is skipped
 * when no WPLoyalty data is detected. Seed / clean up the table outside the
 * test (see the WPS-8327 notes in the PR).
 */
const { test, expect } = require( '@playwright/test' );

const TAB_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=import-export';

async function openMigration( page ) {
	await page.goto( TAB_URL );
	const close = page.locator( '.wps-on-boarding-close-btn a' );
	if ( await close.isVisible() ) {
		await close.click();
	}
	const mode = page.locator( '#wps_wpr_migration_mode' );
	if ( ! ( await mode.isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: 'Migrate From Another Plugin' } ).click();
	}
	await expect( mode ).toBeVisible();
}

test.describe( 'Migrate from another plugin', () => {
	test( 'section lists every source with its detection state', async ( { page } ) => {
		await openMigration( page );
		for ( const id of [ 'wployalty', 'wc_points_rewards', 'yith', 'mycred' ] ) {
			await expect( page.locator( `#wps_wpr_migration_source_${ id }` ) ).toBeVisible();
		}
		await expect( page.locator( '#wps_wpr_migration_source_mycred .wps_wpr_migration_not_detected' ) ).toBeVisible();
	} );

	test( 'WPLoyalty preview and migration', async ( { page } ) => {
		await openMigration( page );
		const box = page.locator( '#wps_wpr_migration_source_wployalty' );
		test.skip( ! ( await box.locator( '.wps_wpr_migration_detected' ).isVisible() ), 'No WPLoyalty data seeded.' );

		const run = box.locator( '.wps_wpr_migration_run_btn' );
		await expect( run ).toBeDisabled();

		await box.locator( '.wps_wpr_migration_preview_btn' ).click();
		await expect( box.locator( '.wps_wpr_migration_totals' ) ).toContainText( 'customers with a total of' );
		await expect( box.locator( '.wps_wpr_migration_sample tbody tr' ).first() ).toContainText( 'Found' );
		await expect( run ).toBeEnabled();

		await page.selectOption( '#wps_wpr_migration_mode', 'add' );
		page.once( 'dialog', ( dialog ) => dialog.accept() );
		await run.click();
		await expect( box.locator( '.wps_wpr_migration_summary' ) ).toContainText( 'Migration finished: 1 customers updated' );

		// Running again in add mode skips the already migrated customer.
		page.once( 'dialog', ( dialog ) => dialog.accept() );
		await run.click();
		await expect( box.locator( '.wps_wpr_migration_summary' ) ).toContainText( '0 customers updated' );
		await expect( box.locator( '.wps_wpr_migration_summary' ) ).toContainText( '1 already migrated' );
	} );
} );
