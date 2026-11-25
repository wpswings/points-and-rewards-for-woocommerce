/**
 * E2E: achievement / bonus rules.
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and the points
 * program enabled. The spec creates its own rule and deletes it at the end.
 */
const { test, expect } = require( '@playwright/test' );

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
	test( 'admin creates a rule with conditions', async ( { page } ) => {
		await page.goto( SETTINGS_URL );
		await dismissOnboarding( page );
		await expect( page.locator( '.wps_wpr_ach_limit_notice' ) ).toBeVisible();

		await page.locator( '.wps_wpr_ach_add_rule' ).click();
		const rule = page.locator( '.wps_wpr_ach_rule' ).last();
		await rule.locator( '.wps_wpr_ach_name' ).fill( RULE_NAME );
		await rule.locator( '.wps_wpr_ach_goal_type' ).selectOption( 'spend' );
		await rule.locator( '.wps_wpr_ach_goal_value' ).fill( '400' );
		await rule.locator( '.wps_wpr_ach_window' ).fill( '30' );
		await rule.locator( '.wps_wpr_ach_points' ).fill( '40' );

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
