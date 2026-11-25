/**
 * E2E: setup wizard program templates (WPS-8316).
 *
 * Needs a logged-in admin (WP_STORAGE_STATE). The last test saves the wizard,
 * so run it on a test site or back up the plugin settings first.
 */
const { test, expect } = require( '@playwright/test' );

const WIZARD_URL = '/wp-admin/admin.php?page=wps-wpr-setup-wizard';

async function openWizard( page ) {
	await page.goto( WIZARD_URL );
	await expect( page ).not.toHaveURL( /wp-login/ );
	await expect( page.locator( '#wps-wpr-program-templates' ) ).toBeVisible();
}

async function next( page ) {
	await page.locator( '.wps-wpr-btn-next' ).click();
}

test.describe( 'Setup wizard templates', () => {
	test( 'shows template cards with a preview of their settings', async ( { page } ) => {
		await openWizard( page );

		const cards = page.locator( '.wps-wpr-program-template' );
		await expect( cards ).toHaveCount( 4 );
		await expect( cards.filter( { hasText: 'Starter Loyalty' } ) ).toBeVisible();
		await expect( cards.filter( { hasText: 'Referral Growth' } ) ).toBeVisible();
		await expect( cards.filter( { hasText: 'Big Spender / VIP' } ) ).toBeVisible();
		await expect( cards.filter( { hasText: 'Engagement Booster' } ) ).toBeVisible();

		const vip = page.locator( '.wps-wpr-program-template[data-template="vip"]' );
		await expect( vip.locator( '.wps-wpr-program-template-preview li' ).first() ).toContainText( 'Earn 2 points per' );
	} );

	test( 'applying a template pre-fills the steps and "start from scratch" restores them', async ( { page } ) => {
		await openWizard( page );

		const originalRedeem = await page.locator( '#redemption_points' ).inputValue();
		const originalReferral = await page.locator( '#referee_points' ).inputValue();

		await page.locator( '.wps-wpr-apply-template[data-template="vip"]' ).click();
		await expect( page.locator( '#wps_wpr_program_template' ) ).toHaveValue( 'vip' );
		await expect( page.locator( '.wps-wpr-program-template[data-template="vip"]' ) ).toHaveClass( /wps-wpr-program-template-selected/ );
		await expect( page.locator( 'input[name="step0[plugin_enable]"]' ) ).toBeChecked();

		await next( page );
		await expect( page.locator( '#redemption_points' ) ).toBeVisible();
		await expect( page.locator( '#redemption_points' ) ).toHaveValue( '50' );
		await next( page );
		await expect( page.locator( '#referee_points' ) ).toHaveValue( '100' );
		await expect( page.locator( 'input[name="step2[referral_enable]"]' ) ).toBeChecked();
		await next( page );
		await expect( page.locator( 'input[name="step3[point_tab_template]"][value="temp_four"]' ) ).toBeChecked();
		await next( page );
		await expect( page.locator( 'input[name="step4[signup_enable]"]' ) ).not.toBeChecked();

		// Back to step 0 and drop the template.
		await page.locator( '.wps-wpr-progress-step[data-step="0"]' ).click();
		await page.locator( '.wps-wpr-clear-template' ).click();
		await expect( page.locator( '#wps_wpr_program_template' ) ).toHaveValue( '' );
		await expect( page.locator( '.wps-wpr-program-template-selected' ) ).toHaveCount( 0 );
		await expect( page.locator( '#redemption_points' ) ).toHaveValue( originalRedeem );
		await expect( page.locator( '#referee_points' ) ).toHaveValue( originalReferral );
	} );

	test( 'switching templates keeps the stored points of features the new template turns off', async ( { page } ) => {
		await openWizard( page );

		const originalReferral = await page.locator( '#referee_points' ).inputValue();

		await page.locator( '.wps-wpr-apply-template[data-template="vip"]' ).click();
		await expect( page.locator( '#referee_points' ) ).toHaveValue( '100' );

		// Starter turns referral off and leaves its points alone.
		await page.locator( '.wps-wpr-apply-template[data-template="starter"]' ).click();
		await expect( page.locator( '#wps_wpr_program_template' ) ).toHaveValue( 'starter' );
		await expect( page.locator( 'input[name="step2[referral_enable]"]' ) ).not.toBeChecked();
		await expect( page.locator( '#referee_points' ) ).toHaveValue( originalReferral );
	} );

	test( 'completing the wizard with an edited template saves the edits', async ( { page } ) => {
		await openWizard( page );

		await page.locator( '.wps-wpr-apply-template[data-template="referral"]' ).click();
		await next( page ); // Redemption.
		await next( page ); // Referral.
		await page.locator( '#referee_points' ).fill( '250' );
		await next( page ); // Layout.
		await next( page ); // Signup.
		await next( page ); // Complete.

		const saved = page.waitForResponse( ( res ) => res.url().includes( 'admin-ajax.php' ) && res.request().method() === 'POST' );
		await page.locator( '.wps-wpr-btn-finish' ).click();
		const body = await ( await saved ).json();
		expect( body.success ).toBe( true );

		// The wizard reads back the saved settings.
		await openWizard( page );
		await expect( page.locator( '#referee_points' ) ).toHaveValue( '250' );
		await expect( page.locator( '#signup_points' ) ).toHaveValue( '100' );
		await expect( page.locator( 'input[name="step3[point_tab_template]"][value="temp_two"]' ) ).toBeChecked();

		// Per-currency earning (no wizard step) was applied from the template.
		await page.goto( '/wp-admin/admin.php?page=wps-rwpr-setting&tab=coupon-setting' );
		await expect( page.locator( '#wps_wpr_coupon_conversion_enable' ) ).toBeChecked();
	} );
} );
