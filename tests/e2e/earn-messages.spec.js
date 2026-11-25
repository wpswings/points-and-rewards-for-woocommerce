/**
 * E2E: earn-points messages on shop, product and cart pages (WPS-8323).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and this site setup
 * (back up the options first and restore them afterwards):
 * - points program enabled (wps_wpr_settings_gallery[wps_wpr_general_setting_enable] = 1)
 * - per-currency earning on at 2 points per 1 currency unit (wps_wpr_coupons_gallery)
 * - a simple product "wps-em-e2e-simple" at 25 (=> 50 points)
 * - a variable product "wps-em-e2e-variable" with sizes S at 10 and L at 30 (=> 20 - 60 points)
 * - the store visible to guests (WooCommerce "coming soon" mode off)
 * The test saves the Earn Messages tab (option wps_wpr_earn_message_settings).
 */
const { test, expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=earn-messages-settings';

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

test.describe.serial( 'Earn-points messages', () => {
	test( 'admin enables the messages and the settings persist', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await expect( page.locator( '.wps_wpr_general_sign_title', { hasText: 'Earn-Points Messages' } ) ).toBeVisible();

		for ( const id of [ '#wps_wpr_em_enable_product', '#wps_wpr_em_enable_shop', '#wps_wpr_em_enable_cart', '#wps_wpr_em_show_guests' ] ) {
			await page.locator( id ).check();
		}
		await page.locator( '#wps_wpr_em_product_text' ).fill( 'Earn [POINTS] points on this item' );
		await page.locator( '#wps_wpr_save_earn_messages_settings' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await expect( page.locator( '#wps_wpr_em_enable_product' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_em_enable_shop' ) ).toBeChecked();
		await expect( page.locator( '#wps_wpr_em_enable_cart' ) ).toBeChecked();
	} );

	test( 'simple product page shows the points earned', async ( { page } ) => {
		await page.goto( '/product/wps-em-e2e-simple/' );
		const message = page.locator( '.wps_wpr_earn_message_product' );
		await expect( message ).toHaveText( 'Earn 50 points on this item' );
		// The older badge is replaced, so only one points message shows.
		await expect( page.locator( '.wps_wpr_product_point' ) ).toHaveCount( 0 );
	} );

	test( 'variable product shows a range that follows the selected variation', async ( { page } ) => {
		await page.goto( '/product/wps-em-e2e-variable/' );
		const points = page.locator( '.wps_wpr_earn_message_product .wps_wpr_earn_message_points' );
		await expect( points ).toHaveText( '20 - 60' );

		await page.locator( 'select#size, select[name="attribute_size"]' ).first().selectOption( 'L' );
		await expect( points ).toHaveText( '60' );

		await page.locator( 'select#size, select[name="attribute_size"]' ).first().selectOption( 'S' );
		await expect( points ).toHaveText( '20' );

		await page.locator( '.reset_variations' ).click();
		await expect( points ).toHaveText( '20 - 60' );
	} );

	test( 'shop listing shows the message on product cards', async ( { page } ) => {
		await page.goto( '/?s=WPS+EM+E2E&post_type=product' );
		const card = page.locator( 'li.product, .wc-block-grid__product, .product' ).filter( { hasText: 'WPS EM E2E Simple' } ).first();
		await expect( card.locator( '.wps_wpr_earn_message_shop' ) ).toHaveText( 'Earn 50 points' );
	} );

	test( 'guests see the product message too', async ( { browser, baseURL } ) => {
		const context = await browser.newContext( { baseURL, storageState: { cookies: [], origins: [] } } );
		const page = await context.newPage();
		await page.goto( '/product/wps-em-e2e-simple/' );
		await expect( page.locator( '.wps_wpr_earn_message_product' ) ).toHaveText( 'Earn 50 points on this item' );
		await context.close();
	} );

	test( 'block cart shows the points the order earns and refreshes on quantity change', async ( { browser, baseURL } ) => {
		// A guest context keeps the admin's saved cart untouched.
		const context = await browser.newContext( { baseURL, storageState: { cookies: [], origins: [] } } );
		const page = await context.newPage();
		await page.goto( '/product/wps-em-e2e-simple/' );
		await page.locator( 'form.cart button[name="add-to-cart"], form.cart .single_add_to_cart_button' ).first().click();
		await page.waitForLoadState( 'domcontentloaded' );

		await page.goto( '/cart/' );
		const notice = page.locator( '.wps_wpr_earn_message_cart_block' );
		await expect( notice ).toBeVisible();
		await expect( notice ).toHaveText( /Points you will earn: \d+ points/ );
		const before = parseInt( ( await notice.locator( '.wps_wpr_earn_message_points' ).innerText() ).replace( /\D/g, '' ), 10 );
		expect( before ).toBeGreaterThanOrEqual( 50 );

		await page.locator( '.wc-block-components-quantity-selector__button--plus' ).first().click();
		await expect.poll( async () => parseInt( ( await notice.locator( '.wps_wpr_earn_message_points' ).innerText() ).replace( /\D/g, '' ), 10 ) ).toBe( before + 50 );
		await context.close();
	} );
} );
