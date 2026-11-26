/**
 * Shared helpers for the floating widget / rewards launcher e2e specs.
 */
const { expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=floating-widget';
const SAVE_BUTTON = '#wps_wpr_save_floating_widget_settings';

// The WP Swings onboarding modal opens about a second after an admin page loads (until it is
// submitted or skipped) and covers the section titles and the Save button. Block its script so
// the modal never opens; nothing is saved on the site.
async function blockOnboarding( page ) {
	await page.route( /wpswings-onboarding-admin(\.min)?\.js/, ( route ) => route.abort() );
}

async function openSettings( page ) {
	await blockOnboarding( page );
	await page.goto( SETTINGS_URL );
	await expect( page.locator( SAVE_BUTTON ) ).toBeAttached();
}

async function openSection( page, title, field ) {
	if ( ! ( await page.locator( field ).isVisible() ) ) {
		await page.locator( '.wps_wpr_general_sign_title', { hasText: title } ).click();
	}
	await expect( page.locator( field ) ).toBeVisible();
}

// Click Save and wait for the POST response and the reloaded page.
async function submitSettings( page ) {
	await Promise.all( [
		page.waitForResponse( ( r ) => 'POST' === r.request().method() && r.url().includes( 'page=wps-rwpr-setting' ) ),
		page.locator( SAVE_BUTTON ).click(),
	] );
	await page.waitForLoadState( 'load' );
}

// Current widget settings as the form would submit them (name/value pairs).
async function backupSettings( page ) {
	await openSettings( page );
	return page.evaluate( ( selector ) => {
		const form = document.querySelector( selector ).form;
		return Array.from( new FormData( form ).entries() ).filter( ( [ , value ] ) => 'string' === typeof value );
	}, SAVE_BUTTON );
}

// Submit a backup taken with backupSettings() again, with a fresh nonce.
async function restoreSettings( page, pairs ) {
	if ( ! pairs ) {
		return;
	}
	await openSettings( page );
	const nonce = await page.locator( 'input[name="wps_wpr_floating_widget_nonce"]' ).inputValue();
	const body = new URLSearchParams();
	for ( const [ name, value ] of pairs ) {
		body.append( name, 'wps_wpr_floating_widget_nonce' === name ? nonce : value );
	}
	body.append( 'wps_wpr_save_floating_widget_settings', 'Save Changes' );
	const response = await page.request.post( SETTINGS_URL, {
		data: body.toString(),
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
	} );
	expect( response.ok() ).toBe( true );
}

// Run a callback with an admin page outside a test (beforeAll / afterAll).
async function withAdminPage( browser, testInfo, callback ) {
	const context = await browser.newContext( testInfo.project.use );
	try {
		return await callback( await context.newPage() );
	} finally {
		await context.close();
	}
}

module.exports = { SETTINGS_URL, withAdminPage, blockOnboarding, openSettings, openSection, submitSettings, backupSettings, restoreSettings };
