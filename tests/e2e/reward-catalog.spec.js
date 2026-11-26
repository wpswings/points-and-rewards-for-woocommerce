/**
 * E2E: reward catalog.
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and WP-CLI for the
 * site: set WP_CLI to the command (default `wp`, run in the site root).
 *
 * Before it runs, the spec backs up and then changes what it needs: it turns
 * the points program on, turns WooCommerce "Coming soon" mode off, gives the
 * admin exactly 50 points, and creates a simple product and a customer. After
 * it runs it restores the options, the admin's points, redemptions, points log
 * and cart, and deletes the product, the customer and every reward coupon it
 * created, so it can be run again.
 */
const { test, expect } = require( '@playwright/test' );
const { execFileSync } = require( 'child_process' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=reward-catalog';
const POINTS_URL = '/my-account/points/';
const REWARD_TITLE = 'E2E Ten Off';
const RACE_TITLE = 'E2E Race Reward';
const RACE_PASS = 'E2e-Race-' + Date.now() + '!x';

/**
 * Run PHP through `wp eval-file -` and return its trimmed output.
 *
 * @param {string} php  PHP code (without the opening tag).
 * @param {Object} data passed to the code as $data (decoded JSON).
 * @return {string} output.
 */
function php( php, data = {} ) {
	const cmd = ( process.env.WP_CLI || 'wp' ).split( ' ' );
	const arg = Buffer.from( JSON.stringify( data ) ).toString( 'base64' );
	return execFileSync( cmd[ 0 ], [ ...cmd.slice( 1 ), 'eval-file', '-', arg ], {
		input: `<?php $data = json_decode( base64_decode( $args[0] ), true );\n${ php }`,
		encoding: 'utf8',
		stdio: [ 'pipe', 'pipe', 'pipe' ],
	} ).trim();
}

const PHP_COUPONS_FOR = `
function e2e_rc_coupons( $user_ids ) {
	global $wpdb;
	$user_ids = array_map( 'intval', (array) $user_ids );
	if ( ! $user_ids ) { return array(); }
	return array_map( 'intval', $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wps_wpr_reward_user' AND meta_value IN (" . implode( ',', $user_ids ) . ')' ) );
}`;

const PHP_BACKUP = `${ PHP_COUPONS_FOR }
global $wpdb;
$u    = (int) $data['admin'];
$meta = array();
foreach ( array( 'wps_wpr_points', 'wps_wpr_reward_redemptions', 'points_details', '_woocommerce_persistent_cart_' . get_current_blog_id() ) as $key ) {
	$meta[ $key ] = metadata_exists( 'user', $u, $key ) ? get_user_meta( $u, $key, true ) : null;
}
echo base64_encode( serialize( array(
	'options' => array(
		'wps_wpr_settings_gallery' => get_option( 'wps_wpr_settings_gallery', null ),
		'woocommerce_coming_soon'  => get_option( 'woocommerce_coming_soon', null ),
		'wps_wpr_reward_catalog'   => get_option( 'wps_wpr_reward_catalog', null ),
	),
	'meta'    => $meta,
	'session' => $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM {$wpdb->prefix}woocommerce_sessions WHERE session_key = %s", (string) $u ) ),
	'coupons' => e2e_rc_coupons( $u ),
) ) );`;

const PHP_SETUP = `
$u       = (int) $data['admin'];
$gallery = get_option( 'wps_wpr_settings_gallery', array() );
$gallery = is_array( $gallery ) ? $gallery : array();
$gallery['wps_wpr_general_setting_enable'] = 1;
update_option( 'wps_wpr_settings_gallery', $gallery );
update_option( 'woocommerce_coming_soon', 'no' );
update_user_meta( $u, 'wps_wpr_points', 50 );

$product = new WC_Product_Simple();
$product->set_name( 'E2E Reward Catalog Product' );
$product->set_regular_price( '40' );
$product->set_status( 'publish' );
$product_id = $product->save();

$login   = 'rc_e2e_race_' . wp_generate_password( 6, false, false );
$race_id = wp_insert_user( array( 'user_login' => $login, 'user_pass' => $data['pass'], 'user_email' => $login . '@example.test', 'role' => 'customer' ) );
echo wp_json_encode( array( 'product' => $product_id, 'race' => $race_id, 'login' => $login ) );`;

const PHP_RESTORE = `${ PHP_COUPONS_FOR }
global $wpdb;
require_once ABSPATH . 'wp-admin/includes/user.php';
$u      = (int) $data['admin'];
$backup = unserialize( base64_decode( $data['backup'] ) );
$users  = array_filter( array( $u, (int) $data['race'] ) );

foreach ( array_diff( e2e_rc_coupons( $users ), $backup['coupons'] ) as $coupon_id ) {
	wp_delete_post( $coupon_id, true );
}
if ( $data['race'] ) {
	wp_delete_user( (int) $data['race'] );
	$wpdb->delete( $wpdb->prefix . 'woocommerce_sessions', array( 'session_key' => (string) (int) $data['race'] ) );
}
if ( $data['product'] && ( $product = wc_get_product( (int) $data['product'] ) ) ) {
	$product->delete( true );
}
foreach ( $users as $id ) {
	$wpdb->delete( $wpdb->options, array( 'option_name' => 'wps_wpr_reward_lock_' . $id ) );
}
foreach ( $backup['options'] as $key => $value ) {
	null === $value ? delete_option( $key ) : update_option( $key, $value );
}
foreach ( $backup['meta'] as $key => $value ) {
	null === $value ? delete_user_meta( $u, $key ) : update_user_meta( $u, $key, $value );
}
$table = $wpdb->prefix . 'woocommerce_sessions';
if ( null === $backup['session'] ) {
	$wpdb->delete( $table, array( 'session_key' => (string) $u ) );
} else {
	$wpdb->update( $table, array( 'session_value' => $backup['session'] ), array( 'session_key' => (string) $u ) );
}
echo 'restored';`;

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

test.describe.serial( 'Reward catalog', () => {
	const state = { admin: 0, backup: null, product: 0, race: 0, login: '' };

	test.beforeAll( async ( { browser } ) => {
		const page = await browser.newPage();
		await page.goto( '/wp-admin/profile.php' );
		state.admin = parseInt( await page.locator( '#user_id' ).inputValue(), 10 );
		await page.close();
		expect( state.admin ).toBeGreaterThan( 0 );

		state.backup = php( PHP_BACKUP, { admin: state.admin } );
		expect( state.backup.length ).toBeGreaterThan( 0 );
		Object.assign( state, JSON.parse( php( PHP_SETUP, { admin: state.admin, pass: RACE_PASS } ) ) );
	} );

	test.afterAll( () => {
		if ( state.backup ) {
			php( PHP_RESTORE, { admin: state.admin, backup: state.backup, race: state.race, product: state.product } );
		}
	} );

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
		await expect( page.locator( '.wps-wpr-rc-error' ) ).toHaveCount( 0 );
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
		await page.goto( `/?add-to-cart=${ state.product }` );
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

	test( 'parallel redeems spend the points only once', async ( { browser } ) => {
		const rewardId = php(
			`$s = WPS_WPR_Reward_Catalog::wps_wpr_get_settings();
			$rows = array_values( $s['rewards'] );
			$rows[] = array( 'title' => $data['title'], 'points' => '100', 'type' => 'fixed_cart', 'amount' => '5', 'active' => 'yes' );
			$s = WPS_WPR_Reward_Catalog::wps_wpr_save_settings( 'yes', $rows );
			foreach ( $s['rewards'] as $id => $r ) { if ( $r['title'] === $data['title'] ) { echo $id; } }`,
			{ title: RACE_TITLE }
		);
		expect( rewardId ).toMatch( /^rwd/ );

		// Log the customer in with a fresh context.
		const context = await browser.newContext( { storageState: { cookies: [], origins: [] } } );
		const page = await context.newPage();
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', state.login );
		await page.fill( '#user_pass', RACE_PASS );
		await Promise.all( [ page.waitForLoadState( 'load' ), page.click( '#wp-submit' ) ] );
		await page.goto( POINTS_URL );
		await expect( page.locator( '#wps-wpr-reward-catalog' ) ).toBeVisible();
		const nonce = await page.evaluate( () => window.wpsWprRewardCatalog.nonce );

		for ( let round = 1; round <= 3; round++ ) {
			php( `update_user_meta( (int) $data['race'], 'wps_wpr_points', 100 );`, { race: state.race } );

			const responses = await Promise.all(
				Array.from( { length: 5 }, () =>
					context.request.post( '/wp-admin/admin-ajax.php', {
						form: { action: 'wps_wpr_redeem_reward', nonce, reward_id: rewardId },
					} )
				)
			);
			const bodies = await Promise.all( responses.map( ( r ) => r.json() ) );
			const successes = bodies.filter( ( b ) => b && b.success ).length;

			const after = JSON.parse(
				php(
					`${ PHP_COUPONS_FOR }
					$u = (int) $data['race'];
					wp_cache_delete( $u, 'user_meta' );
					$log = get_user_meta( $u, 'points_details', true );
					echo wp_json_encode( array(
						'points'      => (int) get_user_meta( $u, 'wps_wpr_points', true ),
						'redemptions' => count( WPS_WPR_Reward_Catalog::wps_wpr_get_user_redemptions( $u ) ),
						'log'         => isset( $log['reward_catalog_redeemed'] ) ? count( $log['reward_catalog_redeemed'] ) : 0,
						'coupons'     => count( e2e_rc_coupons( $u ) ),
					) );`,
					{ race: state.race }
				)
			);

			expect( successes, `round ${ round } successes` ).toBe( 1 );
			expect( after, `round ${ round } state` ).toEqual( { points: 0, redemptions: round, log: round, coupons: round } );
		}
		await context.close();
	} );
} );
