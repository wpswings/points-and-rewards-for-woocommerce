/**
 * E2E: campaign display ordering (WPS-8329).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and WP-CLI for the
 * site: set WP_CLI to the command (default `wp`, run in the site root).
 *
 * Before it runs, the spec backs up and then seeds what it needs: the points
 * program on, WooCommerce "Coming soon" mode off, and campaign settings with
 * the birthday campaign plus two social share tasks (Instagram profile,
 * Twitter share). After it runs it restores those options exactly, so it can
 * be run again.
 */
const { test, expect } = require( '@playwright/test' );
const { execFileSync } = require( 'child_process' );

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=wps-campaign-settings';

/**
 * Run PHP through `wp eval-file -` and return its trimmed output.
 *
 * @param {string} code PHP code (without the opening tag).
 * @param {Object} data passed to the code as $data (decoded JSON).
 * @return {string} output.
 */
function php( code, data = {} ) {
	const cmd = ( process.env.WP_CLI || 'wp' ).split( ' ' );
	const arg = Buffer.from( JSON.stringify( data ) ).toString( 'base64' );
	return execFileSync( cmd[ 0 ], [ ...cmd.slice( 1 ), 'eval-file', '-', arg ], {
		input: `<?php $data = json_decode( base64_decode( $args[0] ), true );\n${ code }`,
		encoding: 'utf8',
		stdio: [ 'pipe', 'pipe', 'pipe' ],
	} ).trim();
}

const OPTIONS = [ 'wps_wpr_settings_gallery', 'wps_wpr_campaign_settings', 'woocommerce_coming_soon' ];

const PHP_BACKUP = `
$backup = array();
foreach ( $data['options'] as $name ) {
	$backup[ $name ] = get_option( $name, null );
}
echo base64_encode( serialize( $backup ) );`;

const PHP_RESTORE = `
foreach ( unserialize( base64_decode( $data['backup'] ) ) as $name => $value ) {
	if ( null === $value ) {
		delete_option( $name );
	} else {
		update_option( $name, $value );
	}
}
echo 'restored';`;

const PHP_SEED = `
$gallery = get_option( 'wps_wpr_settings_gallery', array() );
$gallery = is_array( $gallery ) ? $gallery : array();
$gallery['wps_wpr_general_setting_enable'] = 1;
update_option( 'wps_wpr_settings_gallery', $gallery );
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'wps_wpr_campaign_settings', array_merge( array(
	'wps_wpr_enable_campaign_settings'    => 'yes',
	'wps_wpr_enable_birthday_campaign'    => 'yes',
	'wps_wpr_select_page_for_campaign'    => array(),
	'wps_wpr_social_share_campaign_label' => array( 'insta_profile', 'share_twitter' ),
	'wps_wpr_social_share_url'            => array( 'https://example.com/insta', 'https://example.com/x' ),
	'wps_wpr_social_share_points'         => array( '5', '7' ),
	'wps_wpr_enter_campaign_heading'      => 'Points and Rewards Program',
	'wps_wpr_campaign_color_one'          => '#a13a93',
	'wps_wpr_campaign_color_two'          => '#ffbb21',
), $data['extra'] ) );
echo 'seeded';`;

const PHP_CAMPAIGN = `echo wp_json_encode( get_option( 'wps_wpr_campaign_settings', array() ) );`;

function seed( extra = {} ) {
	expect( php( PHP_SEED, { extra } ) ).toBe( 'seeded' );
}

let backup = null;

test.beforeAll( () => {
	backup = php( PHP_BACKUP, { options: OPTIONS } );
	seed();
} );

test.afterAll( () => {
	if ( backup ) {
		php( PHP_RESTORE, { backup } );
	}
} );

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
		await expect( twitterInput ).toBeFocused();
		await expect( page.locator( '#wps_wpr_campaign_order_live' ) ).toHaveAttribute( 'aria-live', 'polite' );
		await expect( page.locator( '#wps_wpr_campaign_order_live' ) ).toHaveText( /moved to position 1 of 7/ );
		await expect( page.locator( '#wps_wpr_campaign_order_keyboard_help' ) ).toContainText( 'Alt + Up Arrow' );
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

	test( 'legacy blank quiz row is cleaned up so the tab can be saved', async ( { page } ) => {
		// v2.10.6 stored one blank quiz row when the tab was saved without a quiz.
		const quiz = {
			wps_wpr_enable_quiz_contest_campaign: 'yes',
			wps_wpr_quiz_question: [ '', 'E2E question one?', 'E2E question two?' ],
			wps_wpr_quiz_option_one: [ '', 'A1', 'B1' ],
			wps_wpr_quiz_option_two: [ '', 'A2', 'B2' ],
			wps_wpr_quiz_option_three: [ '', 'A3', 'B3' ],
			wps_wpr_quiz_option_four: [ '', 'A4', 'B4' ],
			wps_wpr_quiz_answer: [ '', 'A1', 'B3' ],
			wps_wpr_quiz_rewards_points: [ '', '10', '20' ],
		};
		seed( quiz );

		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		const questions = page.locator( 'input[name="wps_wpr_quiz_question[]"]' );
		await expect( questions ).toHaveCount( 2 );
		await expect( questions.nth( 0 ) ).toHaveValue( 'E2E question one?' );
		expect( await page.locator( '#wps_wpr_save_campaign_settings' ).evaluate( ( el ) => el.form.checkValidity() ) ).toBe( true );

		await Promise.all( [ page.waitForNavigation(), page.locator( '#wps_wpr_save_campaign_settings' ).click() ] );

		const saved = JSON.parse( php( PHP_CAMPAIGN ) );
		expect( saved.wps_wpr_quiz_question ).toEqual( [ 'E2E question one?', 'E2E question two?' ] );
		expect( saved.wps_wpr_quiz_option_one ).toEqual( [ 'A1', 'B1' ] );
		expect( saved.wps_wpr_quiz_option_four ).toEqual( [ 'A4', 'B4' ] );
		expect( saved.wps_wpr_quiz_answer ).toEqual( [ 'A1', 'B3' ] );
		expect( saved.wps_wpr_quiz_rewards_points ).toEqual( [ '10', '20' ] );
	} );
} );
