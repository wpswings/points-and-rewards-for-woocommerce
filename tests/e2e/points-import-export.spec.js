/**
 * WPS-8315 Points import / export e2e tests.
 *
 * Needs a logged-in admin: set WP_STORAGE_STATE (see playwright.config.js).
 */
const { test, expect } = require( '@playwright/test' );
const fs = require( 'fs' );

const TAB_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=import-export';

// Open the tab, close the onboarding popup if shown, and expand the Import section.
async function openTab( page ) {
	await page.goto( TAB_URL );
	const close = page.locator( '.wps-on-boarding-close-btn a' );
	if ( await close.isVisible() ) {
		await close.click();
	}
	const importMode = page.locator( '#wps_wpr_import_mode' );
	if ( ! ( await importMode.isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: 'Import Points' } ).click();
	}
	await expect( importMode ).toBeVisible();
}

async function adminEmail( page ) {
	await page.goto( '/wp-admin/profile.php' );
	return page.locator( '#email' ).inputValue();
}

async function importCsv( page, csv, mode ) {
	await openTab( page );
	await page.setInputFiles( '#wps_wpr_points_import_file', {
		name: 'points.csv',
		mimeType: 'text/csv',
		buffer: Buffer.from( csv ),
	} );
	await page.selectOption( '#wps_wpr_import_mode', mode );
	await Promise.all( [ page.waitForLoadState( 'load' ), page.click( '#wps_wpr_import_points_csv' ) ] );
}

test.describe( 'Points Import / Export tab', () => {
	test( 'tab renders export and import sections', async ( { page } ) => {
		await openTab( page );
		await expect( page ).not.toHaveURL( /wp-login/ );
		await expect( page.locator( '#wps_wpr_export_points_csv' ) ).toBeVisible();
		await expect( page.locator( '#wps_wpr_points_import_file' ) ).toBeAttached();
		await expect( page.locator( '#wps_wpr_import_mode' ) ).toBeVisible();
	} );

	test( 'import with competitor headers, export, then revert', async ( { page } ) => {
		const email = await adminEmail( page );

		// Competitor-style headers are auto-detected.
		await importCsv( page, `Customer Email,Points Balance,Note\n${ email },7,e2e import\nnobody@example.invalid,3,\n`, 'add' );
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 users updated, 1 rows skipped' );
		await expect( page.locator( '.wps_wpr_import_errors' ) ).toContainText( 'nobody@example.invalid' );

		// Export contains the admin with a positive balance.
		await openTab( page );
		const [ download ] = await Promise.all( [
			page.waitForEvent( 'download' ),
			page.click( '#wps_wpr_export_points_csv' ),
		] );
		const content = fs.readFileSync( await download.path(), 'utf8' );
		expect( content.split( '\n' )[ 0 ] ).toContain( 'User Email' );
		expect( content ).toContain( email );

		// Revert the change.
		await importCsv( page, `email,points\n${ email },7\n`, 'subtract' );
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 users updated' );
	} );

	test( 'rejects a non-CSV file and unknown columns', async ( { page } ) => {
		await openTab( page );
		await page.setInputFiles( '#wps_wpr_points_import_file', {
			name: 'points.txt',
			mimeType: 'text/plain',
			buffer: Buffer.from( 'email,points\n' ),
		} );
		await Promise.all( [ page.waitForLoadState( 'load' ), page.click( '#wps_wpr_import_points_csv' ) ] );
		await expect( page.locator( '.wps_wpr_import_notice.notice-error' ) ).toContainText( 'Please choose a CSV file' );

		await importCsv( page, 'foo,bar\na,b\n', 'add' );
		await expect( page.locator( '.wps_wpr_import_notice.notice-error' ) ).toContainText( 'user email / user ID column' );
	} );
} );
