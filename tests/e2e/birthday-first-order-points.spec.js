/**
 * WPS-8317 Birthday and first-order points in the free plugin.
 *
 * Needs a logged-in admin: set WP_STORAGE_STATE (see playwright.config.js).
 * Needs WP-CLI for the site: set WP_CLI to the command (default `wp`, run in
 * the site root). The spec backs up the `wps_wpr_settings_gallery` option and
 * the admin's `_my_bday`, first name and last name before it runs, clears the
 * admin's date of birth so every run starts the same way, and restores all of
 * them afterwards, so it can be run again.
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
 * (for example when an option or meta key does not exist).
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
	const backup = { userId: 0, settings: null, meta: {} };
	const USER_META = [ '_my_bday', 'first_name', 'last_name' ];

	test.beforeAll( async ( { browser } ) => {
		// The logged-in admin's user id, from the profile screen.
		const page = await browser.newPage();
		await page.goto( '/wp-admin/profile.php' );
		backup.userId = parseInt( await page.locator( '#user_id' ).inputValue(), 10 );
		await page.close();
		expect( backup.userId ).toBeGreaterThan( 0 );

		backup.settings = wpOrNull( 'option get wps_wpr_settings_gallery --format=json' );
		// null when the meta key does not exist.
		const rows = JSON.parse( wp( `user meta list ${ backup.userId } --keys=${ USER_META.join( ',' ) } --format=json` ) || '[]' );
		for ( const key of USER_META ) {
			const row          = rows.find( ( r ) => r.meta_key === key );
			backup.meta[ key ] = row ? String( row.meta_value ) : null;
		}
		// Start without a saved date of birth, so the field can be set.
		wpOrNull( `user meta delete ${ backup.userId } _my_bday` );
	} );

	test.afterAll( () => {
		if ( null === backup.settings ) {
			wpOrNull( 'option delete wps_wpr_settings_gallery' );
		} else {
			wp( 'option update wps_wpr_settings_gallery --format=json', backup.settings );
		}
		if ( ! backup.userId ) {
			return;
		}
		for ( const key of USER_META ) {
			const value = backup.meta[ key ];
			if ( null === value ) {
				wpOrNull( `user meta delete ${ backup.userId } ${ key }` );
			} else {
				// Single-quoted for the shell (WP-CLI rejects an empty value on stdin).
				wp( `user meta update ${ backup.userId } ${ key } '${ value.replace( /'/g, "'\\''" ) }'` );
			}
		}
	} );

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

		// beforeAll cleared any saved date, so the field is editable.
		await expect( field ).toBeEnabled();
		// WooCommerce requires a first and last name to save account details (restored in afterAll).
		for ( const name of [ '#account_first_name', '#account_last_name' ] ) {
			if ( '' === ( await page.locator( name ).inputValue() ) ) {
				await page.fill( name, 'E2E' );
			}
		}
		await field.fill( '1990-04-12' );
		await Promise.all( [ page.waitForLoadState( 'load' ), page.click( 'button[name="save_account_details"]' ) ] );
		await page.goto( '/my-account/edit-account/' );

		await expect( field ).toBeDisabled();
		await expect( field ).toHaveValue( '1990-04-12' );
		await expect( page.locator( '.wps_wpr_birthday_notice' ) ).toContainText( 'can no longer be changed' );
	} );

	test( 'Free vs Pro lists birthday and first order points as free', async ( { page } ) => {
		// The Free vs Pro tab is only shown while Pro is not active.
		test.skip( null !== wpOrNull( 'plugin is-active ultimate-woocommerce-points-and-rewards' ), 'Pro is active.' );
		await page.goto( '/wp-admin/admin.php?page=wps-rwpr-setting&tab=free-vs-pro' );
		await closeOnboarding( page );
		const row = page.locator( 'tr', { hasText: 'Birthday & First Order Points' } );
		await expect( row ).toBeVisible();
		await expect( row.locator( 'td' ).nth( 1 ).locator( '.wps-free-vs-pro__icon--yes' ) ).toBeVisible();
	} );
} );
