/**
 * WPS-8324: native "Redeem points" panel in the Cart and Checkout blocks.
 *
 * Preconditions (prepared by the test runner, restored afterwards):
 * - logged-in admin storage state (WP_STORAGE_STATE),
 * - points enabled with cart + checkout redemption on (100 points = 1),
 * - the logged-in user has 1500 points,
 * - a published simple product named "WPS-8324 E2E product" priced 20,
 * - Cart / Checkout pages use the WooCommerce blocks.
 */
const { test, expect } = require( '@playwright/test' );

const PRODUCT_NAME = 'WPS-8324 E2E product';

/**
 * Store API helper: returns the nonce needed for cart writes.
 *
 * @param {import('@playwright/test').APIRequestContext} request
 */
async function storeNonce( request ) {
	const res = await request.get( '/wp-json/wc/store/v1/cart' );
	expect( res.ok() ).toBeTruthy();
	return res.headers().nonce;
}

async function emptyCart( request ) {
	const nonce = await storeNonce( request );
	await request.delete( '/wp-json/wc/store/v1/cart/items', { headers: { Nonce: nonce } } );
}

test.describe.serial( 'Cart / Checkout block points redemption', () => {
	let productId;

	test.beforeAll( async ( { request } ) => {
		const res      = await request.get( '/wp-json/wc/store/v1/products?search=' + encodeURIComponent( PRODUCT_NAME ) );
		const products = await res.json();
		const product  = products.find( ( p ) => p.name === PRODUCT_NAME );
		expect( product, 'test product must exist' ).toBeTruthy();
		productId = product.id;
	} );

	test.beforeEach( async ( { page } ) => {
		await emptyCart( page.request );
		const nonce = await storeNonce( page.request );
		const add   = await page.request.post( '/wp-json/wc/store/v1/cart/add-item', {
			headers: { Nonce: nonce },
			data: { id: productId, quantity: 1 },
		} );
		expect( add.ok() ).toBeTruthy();
	} );

	test.afterAll( async ( { request } ) => {
		const nonce = await storeNonce( request );
		await request.post( '/wp-json/wc/store/v1/cart/extensions', {
			headers: { Nonce: nonce },
			data: { namespace: 'wps-wpr', data: { action: 'remove' } },
		} );
		await emptyCart( request );
	} );

	test( 'Store API cart exposes the points extension data', async ( { page } ) => {
		const res  = await page.request.get( '/wp-json/wc/store/v1/cart' );
		const cart = await res.json();
		const ext  = cart.extensions[ 'wps-wpr' ];
		expect( ext ).toBeTruthy();
		expect( ext.logged_in ).toBe( true );
		expect( ext.balance ).toBe( 1500 );
		expect( ext.max_points ).toBe( 1500 );
		expect( ext.applied_points ).toBe( 0 );
	} );

	test( 'cart block: apply and remove points', async ( { page } ) => {
		await page.goto( '/cart/' );
		const panel = page.locator( '.wps-wpr-block-redeem' ).first();
		await expect( panel ).toBeVisible();
		await expect( panel ).toHaveAttribute( 'data-context', 'woocommerce/cart' );
		await expect( panel.locator( '.wps-wpr-block-redeem__balance' ) ).toContainText( '1,500' );

		// The legacy injected link must not show alongside the native panel.
		await expect( page.locator( '#wps_wpr_button_to_add_points_section' ) ).toHaveCount( 0 );

		await panel.locator( '.wps-wpr-block-redeem__input' ).fill( '500' );
		await panel.locator( '.wps-wpr-block-redeem__apply' ).click();

		await expect( panel.locator( '.wps-wpr-block-redeem__applied' ) ).toContainText( '500 points applied' );
		await expect( page.locator( '.wc-block-components-totals-discount' ).first() ).toBeVisible();
		await expect( page.locator( '.wc-block-components-totals-discount' ).first() ).toContainText( '5' );

		// Persisted in the session: still applied after a reload.
		await page.reload();
		await expect( page.locator( '.wps-wpr-block-redeem__applied' ).first() ).toContainText( '500 points applied' );

		await page.locator( '.wps-wpr-block-redeem__remove' ).first().click();
		await expect( page.locator( '.wps-wpr-block-redeem__applied' ) ).toHaveCount( 0 );
		await expect( page.locator( '.wps-wpr-block-redeem__input' ).first() ).toBeVisible();
		await expect( page.locator( '.wc-block-components-totals-discount' ) ).toHaveCount( 0 );
	} );

	test( 'cart block: more points than the balance shows an error', async ( { page } ) => {
		await page.goto( '/cart/' );
		const panel = page.locator( '.wps-wpr-block-redeem' ).first();
		await panel.locator( '.wps-wpr-block-redeem__input' ).fill( '99999' );
		await panel.locator( '.wps-wpr-block-redeem__apply' ).click();
		await expect( panel.locator( '.wps-wpr-block-redeem__error' ) ).toContainText( /valid points/i );
		await expect( panel.locator( '.wps-wpr-block-redeem__applied' ) ).toHaveCount( 0 );
	} );

	test( 'cart block: removing the Cart Discount chip clears the points', async ( { page } ) => {
		await page.goto( '/cart/' );
		const panel = page.locator( '.wps-wpr-block-redeem' ).first();
		await panel.locator( '.wps-wpr-block-redeem__input' ).fill( '300' );
		await panel.locator( '.wps-wpr-block-redeem__apply' ).click();
		await expect( panel.locator( '.wps-wpr-block-redeem__applied' ) ).toContainText( '300 points applied' );

		await page.locator( '.wc-block-components-totals-discount .wc-block-components-chip__remove' ).first().click();
		await expect( page.locator( '.wps-wpr-block-redeem__applied' ) ).toHaveCount( 0 );

		// Not re-added on the next cart calculation.
		await page.reload();
		await expect( page.locator( '.wps-wpr-block-redeem__input' ).first() ).toBeVisible();
		await expect( page.locator( '.wc-block-components-totals-discount' ) ).toHaveCount( 0 );
	} );

	test( 'checkout block: panel renders and applies points', async ( { page } ) => {
		await page.goto( '/checkout/' );
		const panel = page.locator( '.wps-wpr-block-redeem' ).first();
		await expect( panel ).toBeVisible();
		await expect( panel ).toHaveAttribute( 'data-context', 'woocommerce/checkout' );

		await panel.locator( '.wps-wpr-block-redeem__input' ).fill( '200' );
		await panel.locator( '.wps-wpr-block-redeem__apply' ).click();
		await expect( panel.locator( '.wps-wpr-block-redeem__applied' ) ).toContainText( '200 points applied' );

		await panel.locator( '.wps-wpr-block-redeem__remove' ).click();
		await expect( panel.locator( '.wps-wpr-block-redeem__applied' ) ).toHaveCount( 0 );
	} );
} );
