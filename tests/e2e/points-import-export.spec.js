/**
 * WPS-8315 Points import / export e2e tests.
 *
 * Needs a logged-in admin: set WP_STORAGE_STATE (see playwright.config.js).
 */
const { test, expect } = require( '@playwright/test' );
const fs = require( 'fs' );

const TAB_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=import-export';

// The WP Swings onboarding modal opens about a second after an admin page loads (until it is
// submitted or skipped). It used to appear after the one-off close check below had already run,
// and then covered the Export button or the result notice, which made this spec flaky. Block
// its script so the modal never opens; nothing is saved on the site.
async function blockOnboarding( page ) {
	await page.route( /wpswings-onboarding-admin(\.min)?\.js/, ( route ) => route.abort() );
}

// Open the tab and expand the Import section.
async function openTab( page ) {
	await page.goto( TAB_URL );
	await page.waitForLoadState( 'load' );
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

// The admin's balance, read from an export of every user (posted directly, so no UI timing is involved).
async function exportedPoints( page, email ) {
	await page.goto( TAB_URL );
	const nonce = await page.locator( 'input[name="wps_wpr_points_export_nonce"]' ).inputValue();
	const res = await page.request.post( TAB_URL, { form: { wps_wpr_export_points_csv: '1', wps_wpr_points_export_nonce: nonce } } );
	const line = ( await res.text() ).split( '\n' ).find( ( l ) => l.startsWith( email + ',' ) );
	return parseInt( line.split( ',' )[ 1 ], 10 );
}

async function importCsv( page, csv, mode ) {
	await openTab( page );
	await page.setInputFiles( '#wps_wpr_points_import_file', {
		name: 'points.csv',
		mimeType: 'text/csv',
		buffer: Buffer.from( csv ),
	} );
	await page.selectOption( '#wps_wpr_import_mode', mode );
	await page.click( '#wps_wpr_import_points_csv' );
	// The import runs over AJAX; wait for the result or an error notice.
	await expect( page.locator( '#wps_wpr_import_result, .wps_wpr_import_notice.notice-error' ).first() ).toBeVisible( { timeout: 30000 } );
}

test.describe( 'Points Import / Export tab', () => {
	test.beforeEach( async ( { page } ) => {
		await blockOnboarding( page );
	} );

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
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 rows imported, 1 rows skipped' );
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
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 rows imported' );
	} );

	test( 'rejects a non-CSV file and unknown columns', async ( { page } ) => {
		await openTab( page );
		await page.setInputFiles( '#wps_wpr_points_import_file', {
			name: 'points.txt',
			mimeType: 'text/plain',
			buffer: Buffer.from( 'email,points\n' ),
		} );
		await page.click( '#wps_wpr_import_points_csv' );
		await expect( page.locator( '.wps_wpr_import_notice.notice-error' ) ).toContainText( 'Please choose a CSV file' );

		await importCsv( page, 'foo,bar\na,b\n', 'add' );
		await expect( page.locator( '.wps_wpr_import_notice.notice-error' ) ).toContainText( 'user email / user ID column' );
	} );

	// Review #3: Enter in a column field starts the import, not the export download.
	test( 'Enter in a column field imports instead of exporting', async ( { page } ) => {
		const email = await adminEmail( page );
		await openTab( page );
		await page.setInputFiles( '#wps_wpr_points_import_file', {
			name: 'points.csv',
			mimeType: 'text/csv',
			buffer: Buffer.from( `email,points\n${ email },2\n` ),
		} );
		await page.selectOption( '#wps_wpr_import_mode', 'add' );
		let downloaded = false;
		page.on( 'download', () => {
			downloaded = true;
		} );
		await page.fill( '#wps_wpr_import_points_column', 'points' );
		await page.press( '#wps_wpr_import_points_column', 'Enter' );
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 rows imported' );
		expect( downloaded ).toBe( false );

		await importCsv( page, `email,points\n${ email },2\n`, 'subtract' );
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 rows imported' );
	} );

	// Review #1: rows are applied in batches; an interrupted import resumes without re-applying rows.
	test( 'interrupted batched import resumes without applying rows twice', async ( { page } ) => {
		const email = await adminEmail( page );
		const filler = Array.from( { length: 450 }, ( v, i ) => `nobody${ i }@example.invalid,1` ).join( '\n' );
		const csv = `email,points\n${ email },5\n${ filler }\n`;
		const before = await exportedPoints( page, email );

		// Let the first batch through, then fail the next batch request.
		let batches = 0;
		await page.route( '**/admin-ajax.php', async ( route ) => {
			const body = route.request().postData() || '';
			if ( body.includes( 'wps_wpr_points_import_batch' ) && ++batches === 2 ) {
				return route.abort();
			}
			return route.continue();
		} );

		await importCsv( page, csv, 'add' );
		const resume = page.locator( '.wps_wpr_import_notice.notice-error .wps_wpr_import_resume_btn' );
		await expect( resume ).toBeVisible();
		await expect( page.locator( '#wps_wpr_import_progress_text' ) ).toContainText( '200 of 451 rows processed' );

		// After a reload the unfinished import is offered for resuming.
		await page.unroute( '**/admin-ajax.php' );
		await openTab( page );
		await expect( page.locator( '.wps_wpr_import_resume_notice' ) ).toContainText( '200 of 451 rows processed' );
		await page.locator( '.wps_wpr_import_resume_notice .wps_wpr_import_resume_btn' ).click();
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 rows imported, 450 rows skipped' );
		await expect( page.locator( '.wps_wpr_import_resume_notice' ) ).toHaveCount( 0 );
		expect( await exportedPoints( page, email ) ).toBe( before + 5 );

		// Revert.
		await importCsv( page, `email,points\n${ email },5\n`, 'subtract' );
		await expect( page.locator( '#wps_wpr_import_result' ) ).toContainText( '1 rows imported' );
		expect( await exportedPoints( page, email ) ).toBe( before );
	} );
} );
