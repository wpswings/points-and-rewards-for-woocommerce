/**
 * WPS-8327 points migration e2e tests.
 *
 * Needs a logged-in admin (WP_STORAGE_STATE). The WPLoyalty tests expect a
 * seeded `{prefix}wlr_users` table whose first row holds the admin's email;
 * they are skipped when no WPLoyalty data is detected. The resume test also
 * needs at least 100 more rows with guest emails that match no user, so the
 * run spans two batches; it is skipped otherwise. Seed / clean up the table outside the test, and clear the
 * admin's `_wps_wpr_migrated_wployalty` meta before running (see the WPS-8327
 * notes in the PR).
 */
const { test, expect } = require( '@playwright/test' );

const TAB_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=import-export';

// The onboarding modal opens about a second after load and covers the page; block its script.
async function blockOnboarding( page ) {
	await page.route( /wpswings-onboarding-admin(\.min)?\.js/, ( route ) => route.abort() );
}

async function openMigration( page ) {
	await page.goto( TAB_URL );
	await page.waitForLoadState( 'load' );
	const mode = page.locator( '#wps_wpr_migration_mode' );
	if ( ! ( await mode.isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: 'Migrate From Another Plugin' } ).click();
	}
	await expect( mode ).toBeVisible();
}

// The admin's balance, read from a direct export request.
async function exportedPoints( page, email ) {
	await page.goto( TAB_URL );
	const nonce = await page.locator( 'input[name="wps_wpr_points_export_nonce"]' ).inputValue();
	const res = await page.request.post( TAB_URL, { form: { wps_wpr_export_points_csv: '1', wps_wpr_points_export_nonce: nonce } } );
	const line = ( await res.text() ).split( '\n' ).find( ( l ) => l.startsWith( email + ',' ) );
	return line ? parseInt( line.split( ',' )[ 1 ], 10 ) : 0;
}

test.describe.serial( 'Migrate from another plugin', () => {
	test.beforeEach( async ( { page } ) => {
		await blockOnboarding( page );
	} );

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

	// Review #1: with "Allow Re-import" ticked, a run whose batch response is lost is resumed
	// from the server-side offset (also after a reload) and applies the customer only once.
	test( 'interrupted re-import run resumes without double counting', async ( { page } ) => {
		await openMigration( page );
		const box = page.locator( '#wps_wpr_migration_source_wployalty' );
		test.skip( ! ( await box.locator( '.wps_wpr_migration_detected' ).isVisible() ), 'No WPLoyalty data seeded.' );

		await page.goto( '/wp-admin/profile.php' );
		const email = await page.locator( '#email' ).inputValue();
		const before = await exportedPoints( page, email );

		await openMigration( page );
		await box.locator( '.wps_wpr_migration_preview_btn' ).click();
		const sample = box.locator( '.wps_wpr_migration_sample tbody tr', { hasText: email } );
		await expect( sample ).toContainText( 'Found' );
		const points = parseInt( await sample.locator( 'td' ).nth( 1 ).innerText(), 10 );
		expect( points ).toBeGreaterThan( 0 );
		// Guests and deleted users are reported separately, not counted as customers.
		const unmatched = box.locator( '.wps_wpr_migration_unmatched' );
		const guests = ( await unmatched.count() ) ? parseInt( await unmatched.innerText(), 10 ) : 0;
		test.skip( guests < 100, 'Needs 100+ unmatched WPLoyalty rows so the run spans two batches.' );
		await expect( box.locator( '.wps_wpr_migration_totals' ) ).toContainText( '1 customers with a total of ' + points + ' points' );

		// The server applies the first batch, but the browser never sees the response.
		let runs = 0;
		await page.route( '**/admin-ajax.php', async ( route ) => {
			if ( ( route.request().postData() || '' ).includes( 'action=wps_wpr_migration_run' ) && 1 === ++runs ) {
				await route.fetch();
				return route.abort();
			}
			return route.continue();
		} );
		await page.selectOption( '#wps_wpr_migration_mode', 'add' );
		await page.check( '#wps_wpr_migration_allow_reimport' );
		page.once( 'dialog', ( dialog ) => dialog.accept() );
		await box.locator( '.wps_wpr_migration_run_btn' ).click();
		await expect( box.locator( '.wps_wpr_migration_resume .wps_wpr_migration_resume_btn' ) ).toBeVisible();
		await page.unroute( '**/admin-ajax.php' );
		expect( await exportedPoints( page, email ) ).toBe( before + points );

		// After a reload the unfinished run is offered; Resume does not apply the customer again.
		await openMigration( page );
		await expect( box.locator( '.wps_wpr_migration_resume' ) ).toBeVisible();
		await box.locator( '.wps_wpr_migration_resume_btn' ).click();
		await expect( box.locator( '.wps_wpr_migration_summary' ) ).toContainText( 'Migration finished: 1 customers updated' );
		await expect( box.locator( '.wps_wpr_migration_resume' ) ).toHaveCount( 0 );
		expect( await exportedPoints( page, email ) ).toBe( before + points );
	} );
} );
