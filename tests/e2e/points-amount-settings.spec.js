/**
 * E2E: earn with or without tax / shipping (WPS-8328).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE). The test saves the
 * General settings tab, so back up the `wps_wpr_settings_gallery` option first
 * and restore it afterwards.
 */
const { test, expect } = require( '@playwright/test' );

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
