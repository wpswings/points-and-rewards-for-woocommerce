/**
 * E2E: achievement / bonus rules.
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and WP-CLI for the
 * site: set WP_CLI to the command (default `wp`, run in the site root).
 *
 * Before it runs, the spec backs up the points settings and the saved
 * achievement rules (into the option wps_wpr_ach_e2e_backup, so a crashed run
 * is restored by the next one), turns the points program on and clears the
 * rules. Afterwards it restores both, so it can be run again.
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

const PHP_RESTORE = `
$backup = get_option( 'wps_wpr_ach_e2e_backup', null );
if ( is_array( $backup ) ) {
	foreach ( $backup as $name => $value ) {
		if ( '__none__' === $value ) {
			delete_option( $name );
		} else {
			update_option( $name, $value );
		}
	}
	delete_option( 'wps_wpr_ach_e2e_backup' );
}
echo 'restored';`;

const PHP_SETUP = `
$backup = array();
foreach ( array( 'wps_wpr_settings_gallery', 'wps_wpr_achievement_rules' ) as $name ) {
	$backup[ $name ] = get_option( $name, '__none__' );
}
add_option( 'wps_wpr_ach_e2e_backup', $backup, '', 'no' );
$gallery = get_option( 'wps_wpr_settings_gallery', array() );
$gallery = is_array( $gallery ) ? $gallery : array();
$gallery['wps_wpr_general_setting_enable'] = 1;
update_option( 'wps_wpr_settings_gallery', $gallery );
delete_option( 'wps_wpr_achievement_rules' );
echo 'ready';`;

const SETTINGS_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=achievement-rules';
const POINTS_URL = '/my-account/points/';
const RULE_NAME = 'E2E Big Spender';

async function dismissOnboarding( page ) {
	const close = page.locator( '.wps-wpr-on-boarding-close-btn a, .wps-wpr-onboarding-section .wps-close-btn, .swal2-close' ).first();
	if ( await close.isVisible().catch( () => false ) ) {
		await close.click().catch( () => {} );
	}
}

function ruleSection( page ) {
	return page.locator( '.wps_wpr_ach_rule' ).filter( { has: page.locator( `.wps_wpr_ach_name[value="${ RULE_NAME }"]` ) } );
}

async function openSection( section ) {
	const content = section.locator( '.wps_wpr_section_content' );
	if ( ! ( await content.isVisible() ) ) {
		await section.locator( '.wps_wpr_general_sign_title' ).click();
	}
	await expect( content ).toBeVisible();
}

test.describe.serial( 'Achievement rules', () => {
	test.beforeAll( () => {
		php( PHP_RESTORE );
		expect( php( PHP_SETUP ) ).toBe( 'ready' );
	} );

	test.afterAll( () => {
		expect( php( PHP_RESTORE ) ).toBe( 'restored' );
	} );

	test( 'admin creates a rule with conditions', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		// The free plugin shows a rule-limit notice; a licensed Pro lifts the limit (limit 0) and hides it.
		const limit = Number( php( 'echo WPS_WPR_Achievement_Rules::wps_wpr_rules_limit();' ) );
		await expect( page.locator( '.wps_wpr_ach_limit_notice' ) ).toHaveCount( limit > 0 ? 1 : 0 );

		await page.locator( '.wps_wpr_ach_add_rule' ).click();
		const rule = page.locator( '.wps_wpr_ach_rule' ).last();
		await rule.locator( '.wps_wpr_ach_name' ).fill( RULE_NAME );
		await rule.locator( '.wps_wpr_ach_goal_type' ).selectOption( 'spend' );
		await rule.locator( '.wps_wpr_ach_goal_value' ).fill( '400' );
		await rule.locator( '.wps_wpr_ach_window' ).fill( '30' );
		await rule.locator( '.wps_wpr_ach_points' ).fill( '40' );
		await expect( rule ).toContainText( 'at most once per period' );

		// Condition: role is administrator. Number types switch the operators.
		await rule.locator( '.wps_wpr_ach_add_condition' ).click();
		const condition = rule.locator( '.wps_wpr_ach_condition' ).last();
		await condition.locator( '.wps_wpr_ach_cond_type' ).selectOption( 'order_count' );
		await expect( condition.locator( '.wps_wpr_ach_cond_value' ) ).toHaveAttribute( 'type', 'number' );
		await expect( condition.locator( '.wps_wpr_ach_cond_operator' ) ).toHaveValue( '>=' );
		await condition.locator( '.wps_wpr_ach_cond_type' ).selectOption( 'user_role' );
		await expect( condition.locator( '.wps_wpr_ach_cond_operator' ) ).toHaveValue( 'in' );
		await condition.locator( '.wps_wpr_ach_cond_value' ).fill( 'administrator' );

		await page.locator( '#wps_wpr_save_achievement_rules' ).click();
		await page.waitForLoadState( 'domcontentloaded' );
		await expect( page.locator( '.notice-success' ).first() ).toContainText( 'Settings saved' );

		const saved = ruleSection( page );
		await expect( saved ).toHaveCount( 1 );
		await openSection( saved );
		await expect( saved.locator( '.wps_wpr_ach_goal_value' ) ).toHaveValue( '400' );
		await expect( saved.locator( '.wps_wpr_ach_points' ) ).toHaveValue( '40' );
		await expect( saved.locator( '.wps_wpr_ach_active' ) ).toBeChecked();
		await expect( saved.locator( '.wps_wpr_ach_cond_value' ) ).toHaveValue( 'administrator' );
	} );

	test( 'My Account shows the achievement with a progress bar', async ( { page } ) => {
		await page.goto( POINTS_URL );
		const card = page.locator( '.wps_wpr_achievement', { hasText: RULE_NAME } );
		await expect( card ).toBeVisible();
		await expect( card ).toContainText( '+40 points' );
		await expect( card ).toContainText( /Spend .*400.* within 30 days/ );
		await expect( card.locator( '.wps_wpr_achievement_bar' ) ).toHaveAttribute( 'role', 'progressbar' );
		await expect( card.locator( '.wps_wpr_achievement_meta' ) ).toContainText( '/' );
	} );

	test( 'admin deletes the rule', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		const saved = ruleSection( page );
		await openSection( saved );
		await saved.locator( '.wps_wpr_ach_remove_rule' ).click();
		await page.locator( '#wps_wpr_save_achievement_rules' ).click();
		await page.waitForLoadState( 'domcontentloaded' );
		await expect( ruleSection( page ) ).toHaveCount( 0 );

		await page.goto( POINTS_URL );
		await expect( page.locator( '.wps_wpr_achievement', { hasText: RULE_NAME } ) ).toHaveCount( 0 );
	} );
} );
