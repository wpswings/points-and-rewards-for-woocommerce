/**
 * E2E: configurable award / reversal order statuses (WPS-8321).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE). The test saves the
 * General settings tab, so back up the `wps_wpr_settings_gallery` option first
 * and restore it afterwards.
 */
const { test, expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=general-setting';

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

async function openSection( page, title, field ) {
	if ( ! ( await page.locator( field ).isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: title } ).click();
	}
	await expect( page.locator( field ) ).toBeVisible();
}

test.describe.serial( 'Order status settings', () => {
	test( 'section lists award and reversal statuses', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
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
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await openSection( page, 'Order Status Settings', '#wps_wpr_points_award_order_status' );

		await page.locator( '#wps_wpr_points_award_order_status' ).selectOption( 'processing' );
		await page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ).check();
		await page.locator( 'input[name="wps_wpr_save_general"]' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await openSection( page, 'Order Status Settings', '#wps_wpr_points_award_order_status' );
		await expect( page.locator( '#wps_wpr_points_award_order_status' ) ).toHaveValue( 'processing' );
		await expect( page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ) ).toBeChecked();

		// Back to the defaults.
		await page.locator( '#wps_wpr_points_award_order_status' ).selectOption( 'completed' );
		await page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ).uncheck();
		await page.locator( 'input[name="wps_wpr_save_general"]' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await openSection( page, 'Order Status Settings', '#wps_wpr_points_award_order_status' );
		await expect( page.locator( '#wps_wpr_points_award_order_status' ) ).toHaveValue( 'completed' );
		await expect( page.locator( '#wps_wpr_points_reversal_order_statuses input[value="failed"]' ) ).not.toBeChecked();
	} );
} );
