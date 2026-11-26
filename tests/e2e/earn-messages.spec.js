/**
 * E2E: earn-points messages on shop, product and cart pages (WPS-8323).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and WP-CLI for the
 * site: set WP_CLI to the command (default `wp`, run in the site root).
 *
 * Before it runs, the spec backs up (into the option wps_wpr_em_e2e_backup, so a
 * crashed run is restored by the next one) and then changes what it needs: the
 * points program on, per-currency earning at 2 points per 1 currency unit,
 * WooCommerce "Coming soon" mode off and the Earn Messages settings cleared. It
 * creates a simple product "WPS EM E2E Simple" at 25 (50 points) and a variable
 * product "WPS EM E2E Variable" with sizes S at 10 and L at 30 (20 - 60 points).
 * Afterwards it restores the options and deletes the products and the guest carts
 * that hold them, so it can be run again from a clean site.
 */
const { test, expect } = require( '@playwright/test' );
const { execFileSync } = require( 'child_process' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=earn-messages-settings';

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

const PHP_CLEANUP = `
global $wpdb;
$ids = get_option( 'wps_wpr_em_e2e_products', array() );
foreach ( array( 'wps-em-e2e-simple', 'wps-em-e2e-variable' ) as $slug ) {
	$found = get_page_by_path( $slug, OBJECT, 'product' );
	if ( $found ) {
		$ids[] = $found->ID;
	}
}
foreach ( array_unique( array_map( 'intval', (array) $ids ) ) as $id ) {
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_sessions WHERE session_value LIKE %s", '%' . $wpdb->esc_like( '"product_id";i:' . $id . ';' ) . '%' ) );
	$product = wc_get_product( $id );
	if ( $product ) {
		foreach ( $product->get_children() as $child ) {
			wp_delete_post( $child, true );
		}
		$product->delete( true );
	}
}
delete_option( 'wps_wpr_em_e2e_products' );
$backup = get_option( 'wps_wpr_em_e2e_backup', null );
if ( is_array( $backup ) ) {
	foreach ( $backup as $name => $value ) {
		if ( '__none__' === $value ) {
			delete_option( $name );
		} else {
			update_option( $name, $value );
		}
	}
	delete_option( 'wps_wpr_em_e2e_backup' );
}
echo 'clean';`;

const PHP_SETUP = `
$backup = array();
foreach ( array( 'wps_wpr_settings_gallery', 'wps_wpr_coupons_gallery', 'wps_wpr_earn_message_settings', 'woocommerce_coming_soon' ) as $name ) {
	$backup[ $name ] = get_option( $name, '__none__' );
}
add_option( 'wps_wpr_em_e2e_backup', $backup, '', 'no' );

$gallery = get_option( 'wps_wpr_settings_gallery', array() );
$gallery = is_array( $gallery ) ? $gallery : array();
$gallery['wps_wpr_general_setting_enable'] = 1;
update_option( 'wps_wpr_settings_gallery', $gallery );
$coupons = get_option( 'wps_wpr_coupons_gallery', array() );
$coupons = is_array( $coupons ) ? $coupons : array();
$coupons['wps_wpr_coupon_conversion_enable'] = 1;
$coupons['wps_wpr_coupon_conversion_price']  = 2;
$coupons['wps_wpr_coupon_conversion_points'] = 1;
update_option( 'wps_wpr_coupons_gallery', $coupons );
update_option( 'woocommerce_coming_soon', 'no' );
delete_option( 'wps_wpr_earn_message_settings' );

$ids    = array();
$simple = new WC_Product_Simple();
$simple->set_name( 'WPS EM E2E Simple' );
$simple->set_slug( 'wps-em-e2e-simple' );
$simple->set_regular_price( '25' );
$simple->set_tax_status( 'none' );
$simple->set_status( 'publish' );
$ids[] = $simple->save();

$attribute = new WC_Product_Attribute();
$attribute->set_name( 'Size' );
$attribute->set_options( array( 'S', 'L' ) );
$attribute->set_visible( true );
$attribute->set_variation( true );
$variable = new WC_Product_Variable();
$variable->set_name( 'WPS EM E2E Variable' );
$variable->set_slug( 'wps-em-e2e-variable' );
$variable->set_attributes( array( $attribute ) );
$variable->set_tax_status( 'none' );
$variable->set_status( 'publish' );
$ids[] = $variable->save();
foreach ( array( 'S' => '10', 'L' => '30' ) as $size => $price ) {
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $variable->get_id() );
	$variation->set_attributes( array( 'size' => $size ) );
	$variation->set_regular_price( $price );
	$variation->set_status( 'publish' );
	$variation->save();
}
WC_Product_Variable::sync( $variable->get_id() );
update_option( 'wps_wpr_em_e2e_products', $ids, false );
echo 'ready';`;

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

test.describe.serial( 'Earn-points messages', () => {
	test.beforeAll( () => {
		// Restore anything a crashed earlier run left behind, then set up from scratch.
		php( PHP_CLEANUP );
		expect( php( PHP_SETUP ) ).toBe( 'ready' );
	} );

	test.afterAll( () => {
		php( PHP_CLEANUP );
	} );

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
