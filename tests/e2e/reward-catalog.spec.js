/**
 * E2E: reward catalog.
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE), the points
 * program enabled, and the admin user holding at least 50 points.
 */
const { test, expect } = require( '@playwright/test' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=reward-catalog';
const POINTS_URL = '/my-account/points/';
const REWARD_TITLE = 'E2E Ten Off';

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

test.describe.serial( 'Reward catalog', () => {
	test( 'admin creates a reward', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		const enable = page.locator( '#wps_wpr_reward_catalog_enable' );
		await expect( enable ).toBeAttached();
		if ( ! ( await enable.isVisible() ) ) {
			await page.locator( '.wps_wpr_general_sign_title', { hasText: 'Reward Catalog' } ).click();
		}
		await enable.check();

		await page.locator( '#wps-wpr-rc-add' ).click();
		const row = page.locator( '#wps-wpr-rc-table tbody tr.wps-wpr-rc-row' ).last();
		await row.locator( '.wps-wpr-rc-title' ).fill( REWARD_TITLE );
		await row.locator( '.wps-wpr-rc-description' ).fill( 'Ten off your next order' );
		await row.locator( '.wps-wpr-rc-points' ).fill( '50' );
		await row.locator( '.wps-wpr-rc-type' ).selectOption( 'fixed_cart' );
		await expect( row.locator( '.wps-wpr-rc-amount' ) ).toBeVisible();
		await expect( row.locator( '.wps-wpr-rc-product-wrap' ) ).toBeHidden();
		await row.locator( '.wps-wpr-rc-amount' ).fill( '10' );
		await row.locator( '.wps-wpr-rc-active' ).check();

		await page.locator( '#wps_wpr_save_reward_catalog' ).click();
		await page.waitForLoadState( 'domcontentloaded' );

		await expect( page.locator( '.notice-success' ).first() ).toContainText( 'Settings saved' );
		await expect( page.locator( '#wps_wpr_reward_catalog_enable' ) ).toBeChecked();
		await expect( page.locator( '#wps-wpr-rc-table .wps-wpr-rc-title' ) ).toHaveValue( REWARD_TITLE );
		await expect( page.locator( '#wps-wpr-rc-table .wps-wpr-rc-points' ) ).toHaveValue( '50' );
	} );

	test( 'customer redeems the reward and gets a coupon', async ( { page } ) => {
		await page.goto( POINTS_URL );
		const catalog = page.locator( '#wps-wpr-reward-catalog' );
		await expect( catalog ).toBeVisible();

		const card = catalog.locator( '.wps-wpr-reward-card', { hasText: REWARD_TITLE } );
		await expect( card ).toContainText( '50 points' );

		page.once( 'dialog', ( dialog ) => dialog.accept() );
		await card.locator( '.wps-wpr-reward-card__redeem' ).click();

		const notice = catalog.locator( '.wps-wpr-reward-catalog__notice' );
		await expect( notice ).toContainText( /RWD-[A-Z0-9]{8}/ );
		await expect( notice.locator( 'a.wps-wpr-reward-catalog__apply' ) ).toHaveAttribute( 'href', /wps_wpr_apply_reward=rwd-/i );

		const code = ( await notice.textContent() ).match( /RWD-[A-Z0-9]{8}/ )[ 0 ];

		await page.reload();
		const coupons = page.locator( '.wps-wpr-reward-catalog__coupons' );
		await expect( coupons ).toContainText( code );
		await expect( coupons ).toContainText( REWARD_TITLE );
		await expect( coupons.locator( 'a', { hasText: 'Apply to cart' } ) ).toBeVisible();
	} );

	test( 'apply to cart link applies the reward coupon', async ( { page } ) => {
		await page.goto( '/shop/' );
		const productLink = page.locator( 'a.woocommerce-loop-product__link, .wc-block-components-product-image a, li.product a' ).first();
		test.skip( ! ( await productLink.count() ), 'No product on the shop page.' );
		await page.goto( await productLink.getAttribute( 'href' ) );
		const addButton = page.locator( 'form.cart button[name="add-to-cart"], form.cart .single_add_to_cart_button' ).first();
		test.skip( ! ( await addButton.count() ), 'First product cannot be added from its page.' );
		await addButton.click();
		await page.waitForLoadState( 'domcontentloaded' );

		await page.goto( POINTS_URL );
		const coupons = page.locator( '.wps-wpr-reward-catalog__coupons' );
		const code = ( await coupons.locator( 'code' ).first().textContent() ).trim();
		await coupons.locator( 'a', { hasText: 'Apply to cart' } ).first().click();
		await page.waitForLoadState( 'domcontentloaded' );

		await expect( page ).toHaveURL( /cart/ );
		await expect( page.locator( 'body' ) ).toContainText( 'Coupon code applied successfully' );
		await expect( page.getByText( 'Your cart is currently empty' ) ).toBeHidden();
		expect( code ).toMatch( /^RWD-/ );
	} );
} );
