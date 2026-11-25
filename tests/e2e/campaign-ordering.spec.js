/**
 * E2E: campaign display ordering (WPS-8329).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE), the points
 * program enabled, and campaign settings with the birthday campaign plus
 * two social share tasks (Instagram profile, Twitter share) enabled.
 */
const { test, expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=wps-campaign-settings';

async function dismissOnboarding( page ) {
	// The WP Swings onboarding popup (and its loader) can open a moment after load and
	// cover the page. Hide it with CSS rather than closing it, so no onboarding state changes.
	await page.addStyleTag( { content: '.wps-wpr-second-onboarding-section,#wps_wpr_loader{display:none !important}' } );
}

async function openOrderSection( page ) {
	await page.goto( SETTINGS_URL );
	await dismissOnboarding( page );
	const list = page.locator( '#wps_wpr_campaign_order_list' );
	await expect( list ).toBeAttached();
	if ( ! ( await list.isVisible() ) ) {
		await page.locator( '#wps_wpr_campaign_order_wrap .wps_wpr_general_sign_title' ).click();
	}
	await expect( list ).toBeVisible();
	return list;
}

async function listKeys( list ) {
	return list.locator( '.wps_wpr_campaign_order_item' ).evaluateAll( ( els ) => els.map( ( el ) => el.dataset.key ) );
}

test.describe.serial( 'Campaign display order', () => {
	test( 'admin list shows default order, reorders and saves', async ( { page } ) => {
		const list = await openOrderSection( page );

		expect( await listKeys( list ) ).toEqual( [ 'birthday', 'comments', 'spin_wheel', 'first_order', 'quiz', 'insta_profile', 'share_twitter' ] );

		// Keyboard reordering (Alt + Arrow Up) renumbers the list.
		const twitterInput = page.locator( '#wps_wpr_campaign_priority_share_twitter' );
		for ( let i = 0; i < 6; i++ ) {
			await twitterInput.press( 'Alt+ArrowUp' );
		}
		expect( ( await listKeys( list ) )[ 0 ] ).toBe( 'share_twitter' );
		await expect( twitterInput ).toHaveValue( '1' );
		await expect( page.locator( '#wps_wpr_campaign_priority_birthday' ) ).toHaveValue( '2' );

		// Drag and drop: move the Instagram item above Birthday.
		const insta = list.locator( '[data-key="insta_profile"] .wps_wpr_campaign_order_handle' );
		const birthday = list.locator( '[data-key="birthday"]' );
		// Centre the list so the sticky "Save Changes" bar does not cover the handles.
		await page.evaluate( () => document.getElementById( 'wps_wpr_campaign_order_list' ).scrollIntoView( { block: 'center' } ) );
		const from = await insta.boundingBox();
		const to = await birthday.boundingBox();
		await page.mouse.move( from.x + from.width / 2, from.y + from.height / 2 );
		await page.mouse.down();
		await page.mouse.move( from.x + from.width / 2, from.y - 10, { steps: 5 } );
		await page.mouse.move( to.x + 20, to.y + to.height / 4, { steps: 20 } );
		await page.mouse.up();
		expect( ( await listKeys( list ) ).slice( 0, 3 ) ).toEqual( [ 'share_twitter', 'insta_profile', 'birthday' ] );
		await expect( page.locator( '#wps_wpr_campaign_priority_insta_profile' ) ).toHaveValue( '2' );

		await page.locator( '#wps_wpr_save_campaign_settings' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		const saved = await openOrderSection( page );
		expect( ( await listKeys( saved ) ).slice( 0, 3 ) ).toEqual( [ 'share_twitter', 'insta_profile', 'birthday' ] );
		await expect( page.locator( '#wps_wpr_campaign_priority_share_twitter' ) ).toHaveValue( '1' );
		await expect( page.locator( '#wps_wpr_campaign_priority_birthday' ) ).toHaveValue( '3' );
	} );

	test( 'order number field alone also reorders after save', async ( { page } ) => {
		await openOrderSection( page );
		await page.locator( '#wps_wpr_campaign_priority_birthday' ).fill( '0' );
		await page.locator( '#wps_wpr_save_campaign_settings' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		const list = await openOrderSection( page );
		expect( ( await listKeys( list ) ).slice( 0, 3 ) ).toEqual( [ 'birthday', 'share_twitter', 'insta_profile' ] );
	} );

	test( 'campaign popup renders earn items in the saved order', async ( { page } ) => {
		await page.goto( '/' );
		const headings = page.locator( '#wps_wpr_campaign_earn_wrap > .wps-wpr_campaign-h2' );
		await expect( headings.first() ).toBeAttached();
		const texts = ( await headings.allTextContents() ).map( ( t ) => t.replace( /\s+/g, ' ' ).trim() );

		const birthday = texts.findIndex( ( t ) => t.includes( 'Enter your birthday' ) );
		const twitter = texts.findIndex( ( t ) => t.includes( 'Share on Twitter (X)' ) );
		const insta = texts.findIndex( ( t ) => t.includes( 'Visit Instagram Profile' ) );

		expect( birthday ).toBeGreaterThanOrEqual( 0 );
		expect( birthday ).toBeLessThan( twitter );
		expect( twitter ).toBeLessThan( insta );
	} );
} );
