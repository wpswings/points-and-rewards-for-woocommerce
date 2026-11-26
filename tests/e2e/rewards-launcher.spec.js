/**
 * E2E: persistent rewards launcher (tabbed mode of the floating widget).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and the points
 * program enabled on the site.
 */
const { test, expect } = require( '@playwright/test' );

const { blockOnboarding, openSettings, openSection, submitSettings, backupSettings, restoreSettings, withAdminPage } = require( './floating-widget-helpers' );

async function saveSettings( page, changes ) {
	await openSettings( page );
	await openSection( page, 'Floating Loyalty Widget', '#wps_wpr_fw_enable' );
	await page.locator( '#wps_wpr_fw_enable' ).check();
	await page.locator( '#wps_wpr_fw_show_mobile' ).check();
	await openSection( page, 'Rewards Launcher', '#wps_wpr_fw_mode' );
	await openSection( page, 'Page Targeting', '#wps_wpr_fw_targeting' );
	await changes( page );
	await submitSettings( page );
}

test.describe.serial( 'Rewards launcher', () => {
	let myAccountId = 0;
	let backup = null;

	test.beforeAll( async ( { browser }, testInfo ) => {
		backup = await withAdminPage( browser, testInfo, ( page ) => backupSettings( page ) );
	} );

	test.afterAll( async ( { browser }, testInfo ) => {
		await withAdminPage( browser, testInfo, ( page ) => restoreSettings( page, backup ) );
	} );

	test.beforeEach( async ( { page } ) => {
		await blockOnboarding( page );
	} );

	test( 'admin switches the widget to launcher mode', async ( { page } ) => {
		await saveSettings( page, async ( p ) => {
			await p.locator( '#wps_wpr_fw_mode' ).selectOption( 'launcher' );
			for ( const tab of [ 'home', 'earn', 'redeem', 'activity', 'refer' ] ) {
				await p.locator( `#wps_wpr_fw_tab_${ tab }` ).check();
			}
			await p.locator( '#wps_wpr_fw_welcome' ).fill( 'Hi [NAME], welcome!' );
			await p.locator( '#wps_wpr_fw_targeting' ).selectOption( 'all' );
			await p.locator( '#wps_wpr_fw_page_ids' ).fill( '' );
			for ( const box of await p.locator( 'input[name="wps_wpr_fw_page_types[]"]' ).all() ) {
				await box.uncheck();
			}
		} );

		await openSection( page, 'Rewards Launcher', '#wps_wpr_fw_mode' );
		await expect( page.locator( '#wps_wpr_fw_mode' ) ).toHaveValue( 'launcher' );
		await expect( page.locator( '#wps_wpr_fw_welcome' ) ).toHaveValue( 'Hi [NAME], welcome!' );
	} );

	test( 'launcher opens with tabs; keyboard switches tabs; activity loads', async ( { page } ) => {
		await page.goto( '/' );
		const root = page.locator( '#wps-wpr-floating-widget' );
		await expect( root ).toHaveClass( /wps-wpr-fw--launcher/ );
		await expect( page.locator( '.wps-wpr-fw__launcher' ) ).toHaveCount( 1 );

		await root.locator( '.wps-wpr-fw__launcher' ).click();
		const tablist = root.locator( '[role="tablist"]' );
		await expect( tablist ).toBeVisible();
		await expect( root.locator( '#wps-wpr-fw-tabpanel-home' ) ).toBeVisible();
		await expect( root.locator( '.wps-wpr-fw__welcome' ) ).toContainText( 'welcome!' );

		const home = root.locator( '#wps-wpr-fw-tab-home' );
		await expect( home ).toHaveAttribute( 'aria-selected', 'true' );
		await home.focus();
		await page.keyboard.press( 'ArrowRight' );
		const earn = root.locator( '#wps-wpr-fw-tab-earn' );
		await expect( earn ).toHaveAttribute( 'aria-selected', 'true' );
		await expect( earn ).toBeFocused();
		await expect( root.locator( '#wps-wpr-fw-tabpanel-earn' ) ).toBeVisible();
		await expect( root.locator( '#wps-wpr-fw-tabpanel-home' ) ).toBeHidden();

		await page.keyboard.press( 'End' );
		await expect( root.locator( '#wps-wpr-fw-tab-refer' ) ).toBeFocused();
		await page.keyboard.press( 'Home' );
		await expect( home ).toBeFocused();

		const response = page.waitForResponse( ( r ) => r.url().includes( 'admin-ajax.php' ) && 'POST' === r.request().method() );
		await root.locator( '#wps-wpr-fw-tab-activity' ).click();
		const json = await ( await response ).json();
		expect( json.success ).toBe( true );
		const activity = root.locator( '[data-wps-wpr-fw-activity]' );
		await expect( activity ).not.toContainText( 'Loading' );
		await expect( activity.locator( '.wps-wpr-fw__activity-list, .wps-wpr-fw__empty' ) ).toHaveCount( 1 );

		await page.keyboard.press( 'Escape' );
		await expect( root.locator( '#wps-wpr-fw-panel' ) ).toBeHidden();
	} );

	test( 'launcher is hidden while the campaign popup is open', async ( { page } ) => {
		// Make sure a campaign modal element exists before the widget script initialises
		// (on a real site it is rendered server-side when a campaign is enabled).
		await page.addInitScript( () => {
			document.addEventListener( 'DOMContentLoaded', () => {
				if ( ! document.getElementById( 'wps-wpr-campaign-modal' ) ) {
					const modal = document.createElement( 'div' );
					modal.id = 'wps-wpr-campaign-modal';
					document.body.appendChild( modal );
				}
			} );
		} );
		await page.goto( '/' );
		const root = page.locator( '#wps-wpr-floating-widget' );
		await expect( root ).toBeVisible();
		await root.locator( '.wps-wpr-fw__launcher' ).click();
		await expect( root.locator( '#wps-wpr-fw-panel' ) ).toBeVisible();

		// Same class the campaign script toggles.
		await page.evaluate( () => document.getElementById( 'wps-wpr-campaign-modal' ).classList.add( 'wps-wpr-campaign-modal--active' ) );
		await expect( root ).toBeHidden();
		await expect( root.locator( '.wps-wpr-fw__launcher' ) ).toHaveAttribute( 'aria-expanded', 'false' );

		await page.evaluate( () => document.getElementById( 'wps-wpr-campaign-modal' ).classList.remove( 'wps-wpr-campaign-modal--active' ) );
		await expect( root ).toBeVisible();
	} );

	test( 'page exclusion hides the launcher on that page only', async ( { page } ) => {
		await page.goto( '/my-account/' );
		const bodyClass = ( await page.locator( 'body' ).getAttribute( 'class' ) ) || '';
		const match = bodyClass.match( /page-id-(\d+)/ );
		expect( match ).not.toBeNull();
		myAccountId = match[ 1 ];
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toBeVisible();

		await saveSettings( page, async ( p ) => {
			await p.locator( '#wps_wpr_fw_targeting' ).selectOption( 'exclude' );
			await p.locator( '#wps_wpr_fw_page_ids' ).fill( myAccountId );
		} );

		await page.goto( '/my-account/' );
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toHaveCount( 0 );
		await page.goto( '/' );
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toBeVisible();
	} );

	test( 'page types target search results alongside page IDs', async ( { page } ) => {
		await saveSettings( page, async ( p ) => {
			await p.locator( '#wps_wpr_fw_targeting' ).selectOption( 'include' );
			await p.locator( '#wps_wpr_fw_page_ids' ).fill( myAccountId );
			await p.locator( '#wps_wpr_fw_page_type_search' ).check();
		} );
		await openSection( page, 'Page Targeting', '#wps_wpr_fw_targeting' );
		await expect( page.locator( '#wps_wpr_fw_page_type_search' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_fw_page_type_front_page' ) ).not.toBeChecked();

		await page.goto( '/?s=rewards' );
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toBeVisible();
		await page.goto( '/my-account/' );
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toBeVisible();
		await page.goto( '/' );
		await expect( page.locator( '#wps-wpr-floating-widget' ) ).toHaveCount( 0 );
	} );
} );
