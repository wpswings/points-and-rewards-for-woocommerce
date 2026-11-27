/**
 * E2E: setup wizard program templates (WPS-8316).
 *
 * Needs a logged-in admin (WP_STORAGE_STATE) and WP-CLI for the site: set
 * WP_CLI to the command (default `wp`, run in the site root).
 *
 * The last test saves the wizard. Before the tests run, the spec backs up every
 * option the wizard writes (into wps_wpr_wizard_e2e_backup, so a crashed run is
 * restored by the next one) and puts them back afterwards.
 */
const { test, expect } = require( '@playwright/test' );
const { execFileSync } = require( 'child_process' );

/**
 * Run PHP through `wp eval-file -` and return its trimmed output.
 *
 * @param {string} code PHP code (without the opening tag).
 * @return {string} output.
 */
function php( code ) {
	const cmd = ( process.env.WP_CLI || 'wp' ).split( ' ' );
	return execFileSync( cmd[ 0 ], [ ...cmd.slice( 1 ), 'eval-file', '-' ], {
		input: `<?php\n${ code }`,
		encoding: 'utf8',
		stdio: [ 'pipe', 'pipe', 'pipe' ],
	} ).trim();
}

const WIZARD_OPTIONS = "array( 'wps_wpr_settings_gallery', 'wps_wpr_other_settings', 'wps_wpr_coupons_gallery', 'wps_wpr_wizard_template', 'wps_wpr_wizard_completed', 'wps_wpr_wizard_skipped' )";

const PHP_RESTORE = `
$backup = get_option( 'wps_wpr_wizard_e2e_backup', null );
if ( is_array( $backup ) ) {
	foreach ( $backup as $name => $value ) {
		if ( '__none__' === $value ) {
			delete_option( $name );
		} else {
			update_option( $name, $value );
		}
	}
	delete_option( 'wps_wpr_wizard_e2e_backup' );
}
echo 'restored';`;

const PHP_BACKUP = `
$backup = array();
foreach ( ${ WIZARD_OPTIONS } as $name ) {
	$backup[ $name ] = get_option( $name, '__none__' );
}
add_option( 'wps_wpr_wizard_e2e_backup', $backup, '', 'no' );
echo 'saved';`;

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
	test.beforeAll( () => {
		php( PHP_RESTORE );
		expect( php( PHP_BACKUP ) ).toBe( 'saved' );
	} );

	test.afterAll( () => {
		expect( php( PHP_RESTORE ) ).toBe( 'restored' );
	} );

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
