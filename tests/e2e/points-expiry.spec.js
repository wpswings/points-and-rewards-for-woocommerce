/**
 * E2E: basic points expiry.
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and the points
 * program enabled. The My Account test also needs the admin user to have a
 * points balance and an expiry date (user meta wps_wpr_points_expiration_date);
 * pass that date as WPS_E2E_EXPIRY_DATE (Y-m-d), otherwise the test is skipped.
 */
const { test, expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=points-expiration';

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

test.describe.serial( 'Points expiry', () => {
	test( 'Points Expiration tab is listed and settings save', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await expect( page.locator( '#wps_wpr_points_expiration_enable' ) ).toBeAttached();
		// With Pro active the tab shows Pro's own expiry form (covered by Pro's points-expiry-pro spec).
		test.skip( 0 === ( await page.locator( '#wps_wpr_save_points_expiry_settings' ).count() ), 'Pro replaces the basic expiry form' );

		await openSection( page, 'Points Expiration', '#wps_wpr_points_expiration_enable' );
		await page.locator( '#wps_wpr_points_expiration_enable' ).check();
		await page.locator( '#wps_wpr_points_expiration_threshold' ).fill( '5' );
		await page.locator( '#wps_wpr_points_expiration_time_num' ).fill( '6' );
		await page.locator( '#wps_wpr_points_expiration_time_drop' ).selectOption( 'months' );
		await page.locator( '#wps_wpr_points_exp_onmyaccount' ).check();

		await openSection( page, 'Expiry Emails', '#wps_wpr_enable_expire_notify_setting' );
		await page.locator( '#wps_wpr_enable_expire_notify_setting' ).check();
		await page.locator( '#wps_wpr_points_expiration_email' ).fill( '10' );
		await page.locator( '#wps_wpr_re_notification' ).fill( 'Use your [TOTALPOINT] points before [EXPIRYDATE]!' );

		await page.locator( '#wps_wpr_save_points_expiry_settings' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		await expect( page.locator( '#wps_wpr_points_expiration_enable' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_points_expiration_threshold' ) ).toHaveValue( '5' );
		await expect( page.locator( '#wps_wpr_points_expiration_time_num' ) ).toHaveValue( '6' );
		await expect( page.locator( '#wps_wpr_points_expiration_time_drop' ) ).toHaveValue( 'months' );
		await expect( page.locator( '#wps_wpr_points_exp_onmyaccount' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_enable_expire_notify_setting' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_points_expiration_email' ) ).toHaveValue( '10' );
		await expect( page.locator( '#wps_wpr_re_notification' ) ).toHaveValue( 'Use your [TOTALPOINT] points before [EXPIRYDATE]!' );
	} );

	test( 'Free vs Pro lists points expiry as free', async ( { page } ) => {
		await page.goto( '/wp-admin/admin.php?page=wps-rwpr-setting&tab=free-vs-pro' );
		await dismissOnboarding( page );
		// The Free vs Pro tab only exists while Pro is inactive.
		test.skip( 0 === ( await page.locator( '.wps-free-vs-pro__wrapper' ).count() ), 'Free vs Pro tab is hidden while Pro is active' );
		await expect( page.getByText( 'Expire unused points after a set period', { exact: false } ) ).toBeAttached();
		await expect( page.getByText( 'Advanced Points Expiry', { exact: true } ) ).toBeAttached();
	} );

	test( 'My Account shows the expiry date', async ( { page } ) => {
		const expiry = process.env.WPS_E2E_EXPIRY_DATE;
		test.skip( ! expiry, 'Set WPS_E2E_EXPIRY_DATE after giving the admin a balance and an expiry date.' );

		await page.goto( '/my-account/points/' );
		const notice = page.locator( '.wps_wpr_points_expiry' ).first();
		await expect( notice ).toBeVisible();
		await expect( notice ).toContainText( 'Expiring On' );
		// The year of the expiry date is shown whatever the site date format.
		await expect( notice ).toContainText( expiry.slice( 0, 4 ) );
	} );
} );
