/**
 * WPS-8317 Birthday and first-order points in the free plugin.
 *
 * Needs a logged-in admin: set WP_STORAGE_STATE (see playwright.config.js).
 * The tests save General settings and the admin's date of birth, so back up
 * the `wps_wpr_settings_gallery` option and the admin's `_my_bday` user meta
 * before running them on a site you care about.
 */
const { test, expect } = require( '@playwright/test' );

const GENERAL_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=general-setting';

async function closeOnboarding( page ) {
	const close = page.locator( '.wps-on-boarding-close-btn a' );
	if ( await close.isVisible() ) {
		await close.click();
	}
}

// Expand a General tab section by its title.
async function openSection( page, title, fieldSelector ) {
	const field = page.locator( fieldSelector );
	if ( ! ( await field.isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: new RegExp( `^\\s*${ title }\\s*$` ) } ).click();
	}
	await expect( field ).toBeVisible();
}

test.describe.serial( 'Birthday and first-order points (free)', () => {
	test( 'General tab has First Order and Birthday sections and saves them', async ( { page } ) => {
		await page.goto( GENERAL_URL );
		await expect( page ).not.toHaveURL( /wp-login/ );
		await closeOnboarding( page );

		await openSection( page, 'Enable', '#wps_wpr_general_setting_enable' );
		await page.locator( '#wps_wpr_general_setting_enable' ).check();

		await openSection( page, 'First Order Points', '#wps_wpr_general_first_value' );
		await page.locator( '#wps_wpr_general_setting_enablee' ).check();
		await page.fill( '#wps_wpr_general_first_value', '30' );
		await page.selectOption( 'select[name="wps_wpr_first_order_points_type"]', 'percent' );

		await openSection( page, 'Birthday Points', '#wps_wpr_general_birthday_value' );
		await page.locator( '#wps_wpr_general_setting_birthday_enablee' ).check();
		await page.fill( '#wps_wpr_general_birthday_value', '45' );

		await Promise.all( [ page.waitForLoadState( 'load' ), page.click( 'input[name="wps_wpr_save_general"]' ) ] );

		await page.goto( GENERAL_URL );
		await closeOnboarding( page );
		await expect( page.locator( '#wps_wpr_general_setting_enablee' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_general_first_value' ) ).toHaveValue( '30' );
		await expect( page.locator( 'select[name="wps_wpr_first_order_points_type"]' ) ).toHaveValue( 'percent' );
		await expect( page.locator( '#wps_wpr_general_setting_birthday_enablee' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_general_birthday_value' ) ).toHaveValue( '45' );
	} );

	test( 'Notification tab has First Order and Birthday emails', async ( { page } ) => {
		await page.goto( '/wp-admin/admin.php?page=wps-rwpr-setting&tab=points-notification' );
		await closeOnboarding( page );
		await expect( page.locator( '.wps_wpr_general_sign_title', { hasText: 'First Order Points Notification' } ) ).toBeVisible();
		await expect( page.locator( '.wps_wpr_general_sign_title', { hasText: 'Birthday Points Notification' } ) ).toBeVisible();
		await expect( page.locator( '#wps_wpr_point_on_bday_subject' ) ).toBeAttached();
	} );

	test( 'customer can set a date of birth once on Account details', async ( { page } ) => {
		await page.goto( '/my-account/edit-account/' );
		const field = page.locator( '#account_bday' );
		await expect( field ).toBeVisible();
		await expect( page.locator( '.wps_wpr_birthday_notice' ) ).toContainText( '45 points' );

		// An admin may already have a saved date from an earlier run.
		if ( await field.isEnabled() ) {
			// WooCommerce requires a first and last name to save account details.
			for ( const name of [ '#account_first_name', '#account_last_name' ] ) {
				if ( '' === ( await page.locator( name ).inputValue() ) ) {
					await page.fill( name, 'E2E' );
				}
			}
			await field.fill( '1990-04-12' );
			await Promise.all( [ page.waitForLoadState( 'load' ), page.click( 'button[name="save_account_details"]' ) ] );
			await page.goto( '/my-account/edit-account/' );
		}

		await expect( field ).toBeDisabled();
		await expect( field ).not.toHaveValue( '' );
		await expect( page.locator( '.wps_wpr_birthday_notice' ) ).toContainText( 'can no longer be changed' );
	} );

	test( 'Free vs Pro lists birthday and first order points as free', async ( { page } ) => {
		await page.goto( '/wp-admin/admin.php?page=wps-rwpr-setting&tab=free-vs-pro' );
		await closeOnboarding( page );
		const row = page.locator( 'tr', { hasText: 'Birthday & First Order Points' } );
		await expect( row ).toBeVisible();
		await expect( row.locator( 'td' ).nth( 1 ).locator( '.wps-free-vs-pro__icon--yes' ) ).toBeVisible();
	} );
} );
