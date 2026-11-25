/**
 * E2E: floating loyalty widget.
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and the points
 * program enabled on the site.
 */
const { test, expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=floating-widget';

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

test.describe.serial( 'Floating loyalty widget', () => {
	test( 'admin can enable and configure the widget', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await expect( page.locator( '#wps_wpr_fw_enable' ) ).toBeAttached();

		const title = page.locator( '.wps_wpr_general_sign_title', { hasText: 'Floating Loyalty Widget' } );
		if ( ! ( await page.locator( '#wps_wpr_fw_enable' ).isVisible() ) ) {
			await title.click();
		}
		await page.locator( '#wps_wpr_fw_enable' ).check();
		await page.locator( '#wps_wpr_fw_position' ).selectOption( 'bottom-left' );
		await page.locator( '#wps_wpr_fw_label' ).fill( 'My Rewards' );
		await page.locator( '#wps_wpr_fw_show_to' ).selectOption( 'all' );
		await page.locator( '#wps_wpr_save_floating_widget_settings' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		await expect( page.locator( '#wps_wpr_fw_enable' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_fw_label' ) ).toHaveValue( 'My Rewards' );
	} );

	test( 'logged-in customer sees balance; Esc closes the panel', async ( { page } ) => {
		await page.goto( '/' );
		const launcher = page.locator( '#wps-wpr-floating-widget .wps-wpr-fw__launcher' );
		await expect( launcher ).toBeVisible();
		await expect( launcher ).toContainText( 'My Rewards' );
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toHaveClass( /wps-wpr-fw--bottom-left/ );

		await launcher.click();
		await expect( launcher ).toHaveAttribute( 'aria-expanded', 'true' );
		const panel = page.locator( '#wps-wpr-fw-panel' );
		await expect( panel ).toBeVisible();
		await expect( panel.locator( '[data-wps-wpr-fw-balance]' ) ).toHaveText( /\d/ );
		await expect( panel.locator( 'a.wps-wpr-fw__button' ) ).toHaveAttribute( 'href', /points/ );

		await page.keyboard.press( 'Escape' );
		await expect( panel ).toBeHidden();
		await expect( launcher ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( launcher ).toBeFocused();
	} );

	test( 'guest sees the join call to action', async ( { browser, baseURL } ) => {
		const context = await browser.newContext( { storageState: { cookies: [], origins: [] }, baseURL } );
		const page = await context.newPage();
		await page.goto( '/' );
		const launcher = page.locator( '#wps-wpr-floating-widget .wps-wpr-fw__launcher' );
		await expect( launcher ).toBeVisible();
		await launcher.click();
		const panel = page.locator( '#wps-wpr-fw-panel' );
		await expect( panel ).toBeVisible();
		await expect( panel ).toContainText( 'Sign up / Log in' );
		await expect( panel.locator( '[data-wps-wpr-fw-balance]' ) ).toHaveCount( 0 );
		await panel.locator( '[data-wps-wpr-fw-close]' ).click();
		await expect( panel ).toBeHidden();
		await context.close();
	} );

	test( 'admin can disable the widget again', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		if ( ! ( await page.locator( '#wps_wpr_fw_enable' ).isVisible() ) ) {
			await page.locator( '.wps_wpr_general_sign_title', { hasText: 'Floating Loyalty Widget' } ).click();
		}
		await page.locator( '#wps_wpr_fw_enable' ).uncheck();
		await page.locator( '#wps_wpr_save_floating_widget_settings' ).click();
		await page.waitForLoadState( 'domcontentloaded' );
		await page.goto( '/' );
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toHaveCount( 0 );
	} );
} );
