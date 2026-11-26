/**
 * E2E: earn with or without tax / shipping (WPS-8328).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE).
 * Needs WP-CLI for the site: set WP_CLI to the command (default `wp`, run in
 * the site root). Saving the General tab rewrites the whole
 * `wps_wpr_settings_gallery` option, so the spec backs it up before it runs and
 * restores it afterwards, so it can be run again.
 */
const { test, expect } = require( '@playwright/test' );
const { execSync } = require( 'child_process' );

/**
 * Run a WP-CLI command and return its trimmed output.
 *
 * @param {string} args  arguments, already shell-quoted where needed.
 * @param {string} input optional stdin.
 * @return {string} output.
 */
function wp( args, input ) {
	return execSync( `${ process.env.WP_CLI || 'wp' } ${ args }`, {
		input,
		encoding: 'utf8',
		stdio: [ 'pipe', 'pipe', 'pipe' ],
	} ).trim();
}

/**
 * Run a WP-CLI command; return null instead of throwing when it fails
 * (for example when the option does not exist).
 *
 * @param {string} args arguments.
 * @return {string|null} output.
 */
function wpOrNull( args ) {
	try {
		return wp( args );
	} catch ( e ) {
		return null;
	}
}

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=general-setting';
const TAX = '#wps_wpr_points_amount_tax_mode';
const SHIPPING = '#wps_wpr_points_amount_exclude_shipping';

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

async function openSettings( page ) {
	await page.goto( SETTINGS_URL );
	await dismissOnboarding( page );
	if ( ! ( await page.locator( TAX ).isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: 'Earning Calculation' } ).click();
	}
	await expect( page.locator( TAX ) ).toBeVisible();
}

async function save( page ) {
	await page.locator( 'input[name="wps_wpr_save_general"]' ).click();
	await page.waitForLoadState( 'domcontentloaded' );
}

test.describe.serial( 'Earning calculation settings', () => {
	const backup = { settings: null };

	test.beforeAll( () => {
		backup.settings = wpOrNull( 'option get wps_wpr_settings_gallery --format=json' );
	} );

	test.afterAll( () => {
		if ( null === backup.settings ) {
			wpOrNull( 'option delete wps_wpr_settings_gallery' );
		} else {
			wp( 'option update wps_wpr_settings_gallery --format=json', backup.settings );
		}
	} );

	test( 'section shows tax and shipping options with including-tax default', async ( { page } ) => {
		await openSettings( page );
		await expect( page.locator( `${ TAX } option[value="incl"]` ) ).toHaveCount( 1 );
		await expect( page.locator( `${ TAX } option[value="excl"]` ) ).toHaveCount( 1 );
		await expect( page.locator( TAX ) ).toHaveValue( 'incl' );
		await expect( page.locator( SHIPPING ) ).not.toBeChecked();
	} );

	test( 'settings save and persist', async ( { page } ) => {
		await openSettings( page );
		await page.locator( TAX ).selectOption( 'excl' );
		await page.locator( SHIPPING ).check();
		await save( page );

		await openSettings( page );
		await expect( page.locator( TAX ) ).toHaveValue( 'excl' );
		await expect( page.locator( SHIPPING ) ).toBeChecked();

		// Back to the defaults.
		await page.locator( TAX ).selectOption( 'incl' );
		await page.locator( SHIPPING ).uncheck();
		await save( page );

		await openSettings( page );
		await expect( page.locator( TAX ) ).toHaveValue( 'incl' );
		await expect( page.locator( SHIPPING ) ).not.toBeChecked();
	} );
} );
