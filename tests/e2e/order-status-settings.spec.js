/**
 * E2E: configurable award / reversal order statuses (WPS-8321).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE). The test saves the
 * General settings tab, so back up the `wps_wpr_settings_gallery` option first
 * and restore it afterwards.
 */
const { test, expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=general-setting';

// The WP Swings onboarding modal opens 1 second after the page loads (until it is submitted or
// skipped) and covers the section titles, so a click that lands after that second times out.
// Block its script so the modal never opens; nothing is saved on the site.
async function blockOnboarding( page ) {
	await page.route( /wpswings-onboarding-admin(\.min)?\.js/, ( route ) => route.abort() );
}

async function openSettings( page ) {
	await page.goto( SETTINGS_URL );
	await expect( page.locator( '.wps_wpr_general_sign_title', { hasText: 'Order Status Settings' } ) ).toBeVisible();
}

// Submit the General tab and wait for the POST response and the reloaded page.
async function saveSettings( page ) {
	await Promise.all( [
		page.waitForResponse( ( response ) => 'POST' === response.request().method() && response.url().includes( 'page=wps-rwpr-setting' ) ),
		page.locator( 'input[name="wps_wpr_save_general"]' ).click(),
	] );
	await page.waitForLoadState( 'load' );
}

async function openSection( page, title, field ) {
	if ( ! ( await page.locator( field ).isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: title } ).click();
	}
	await expect( page.locator( field ) ).toBeVisible();
}

test.describe.serial( 'Order status settings', () => {
	test.beforeEach( async ( { page } ) => {
		await blockOnboarding( page );
	} );

	test( 'section lists award and reversal statuses', async ( { page } ) => {
		await openSettings( page );
		await openSection( page, 'Order Status Settings', '#wps_wpr_points_award_order_status' );

		const award = page.locator( '#wps_wpr_points_award_order_status' );
		await expect( award.locator( 'option[value="completed"]' ) ).toHaveCount( 1 );
		await expect( award.locator( 'option[value="processing"]' ) ).toHaveCount( 1 );
		await expect( award.locator( 'option[value="cancelled"]' ) ).toHaveCount( 0 );
		await expect( award.locator( 'option[value="on-hold"]' ) ).toHaveCount( 0 );

		const reversal = page.locator( '#wps_wpr_points_reversal_order_statuses' );
		await expect( reversal.locator( 'input[type="checkbox"][disabled]' ) ).toHaveCount( 2 );
		await expect( reversal.locator( 'input[value="failed"]' ) ).toHaveCount( 1 );
		await expect( reversal.locator( 'input[value="completed"]' ) ).toHaveCount( 0 );
	} );

	test( 'settings save and persist', async ( { page } ) => {
		await openSettings( page );
		await openSection( page, 'Order Status Settings', '#wps_wpr_points_award_order_status' );

		await page.locator( '#wps_wpr_points_award_order_status' ).selectOption( 'processing' );
		await page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ).check();
		await saveSettings( page );

		await openSettings( page );
		await openSection( page, 'Order Status Settings', '#wps_wpr_points_award_order_status' );
		await expect( page.locator( '#wps_wpr_points_award_order_status' ) ).toHaveValue( 'processing' );
		await expect( page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ) ).toBeChecked();

		// Back to the defaults.
		await page.locator( '#wps_wpr_points_award_order_status' ).selectOption( 'completed' );
		await page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ).uncheck();
		await saveSettings( page );

		await openSettings( page );
		await openSection( page, 'Order Status Settings', '#wps_wpr_points_award_order_status' );
		await expect( page.locator( '#wps_wpr_points_award_order_status' ) ).toHaveValue( 'completed' );
		await expect( page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ) ).not.toBeChecked();
	} );
} );
