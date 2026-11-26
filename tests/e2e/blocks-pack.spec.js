/**
 * E2E: Gutenberg blocks pack (WPS-8318).
 *
 * Needs a logged-in admin storage state (WP_STORAGE_STATE) and the points
 * program enabled (General > Enable Points and Rewards).
 */
const { test, expect } = require( '@playwright/test' );

const BLOCKS = [
	'wps-wpr/points-balance',
	'wps-wpr/points-log',
	'wps-wpr/referral-link',
	'wps-wpr/earn-points',
	'wps-wpr/membership-level',
	'wps-wpr/leaderboard',
];

/**
 * Open the block editor for a new page and wait until it is ready.
 *
 * @param {import('@playwright/test').Page} page
 */
async function openEditor( page ) {
	await page.goto( '/wp-admin/post-new.php?post_type=page' );
	await page.waitForFunction( () => window.wp && window.wp.data && window.wp.blocks && window.wp.data.select( 'core/editor' ) );
	// Dismiss the welcome guide / starter patterns modal if one opens.
	const modal = page.locator( '.components-modal__frame' );
	try {
		await modal.first().waitFor( { state: 'visible', timeout: 3000 } );
		await page.keyboard.press( 'Escape' );
		await modal.first().waitFor( { state: 'hidden', timeout: 5000 } );
	} catch ( e ) {
		// No modal.
	}
}

test.describe( 'Points and Rewards blocks pack', () => {
	let createdPageId = null;

	test.afterEach( async ( { page } ) => {
		if ( createdPageId ) {
			// wp.apiFetch is only loaded in wp-admin.
			await openEditor( page );
			await page.evaluate( async ( id ) => {
				await window.wp.apiFetch( { path: `/wp/v2/pages/${ id }?force=true`, method: 'DELETE' } );
			}, createdPageId );
			createdPageId = null;
		}
	} );

	test( 'all blocks are registered in the Points and Rewards category', async ( { page } ) => {
		await openEditor( page );

		const info = await page.evaluate( ( names ) => {
			const cats = window.wp.blocks.getCategories().map( ( c ) => c.slug );
			return {
				hasCategory: cats.includes( 'wps-points-and-rewards' ),
				blocks: names.map( ( n ) => {
					const type = window.wp.blocks.getBlockType( n );
					return type ? { name: n, category: type.category, title: type.title } : null;
				} ),
			};
		}, BLOCKS );

		expect( info.hasCategory ).toBe( true );
		for ( const block of info.blocks ) {
			expect( block ).not.toBeNull();
			expect( block.category ).toBe( 'wps-points-and-rewards' );
			expect( block.title.length ).toBeGreaterThan( 0 );
		}

		// The inserter search finds the blocks.
		await page.getByRole( 'button', { name: /Block Inserter|Toggle block inserter|Add block/i } ).first().click();
		await page.getByPlaceholder( /Search/i ).first().fill( 'Points Leaderboard' );
		await expect( page.getByRole( 'option', { name: /Points Leaderboard/i } ).first() ).toBeVisible();
	} );

	test( 'editor shows a server-side preview with settings', async ( { page } ) => {
		await openEditor( page );

		await page.evaluate( () => {
			const block = window.wp.blocks.createBlock( 'wps-wpr/points-balance', { label: 'E2E balance label' } );
			window.wp.data.dispatch( 'core/block-editor' ).insertBlocks( block );
			window.wp.data.dispatch( 'core/block-editor' ).selectBlock( block.clientId );
		} );

		const canvas = page.frameLocator( 'iframe[name="editor-canvas"]' );
		await expect( canvas.locator( '.wps-wpr-block-points-balance' ) ).toContainText( 'E2E balance label' );

		// Inspector controls from editor.js.
		await page.evaluate( () => window.wp.data.dispatch( 'core/edit-post' ).openGeneralSidebar( 'edit-post/block' ) );
		await expect( page.getByLabel( 'Show membership level' ) ).toBeVisible();
	} );

	test( 'published page renders the blocks on the front end', async ( { page } ) => {
		await openEditor( page );

		const content = [
			'<!-- wp:wps-wpr/points-balance {"label":"E2E Front Balance"} /-->',
			'<!-- wp:wps-wpr/leaderboard {"heading":"E2E Leaders","limit":3} /-->',
		].join( '\n' );

		const created = await page.evaluate( async ( body ) => {
			return window.wp.apiFetch( {
				path: '/wp/v2/pages',
				method: 'POST',
				data: { title: 'E2E blocks pack', status: 'publish', content: body },
			} );
		}, content );
		createdPageId = created.id;

		await page.goto( created.link );
		const balance = page.locator( '.wp-block-wps-wpr-points-balance' );
		await expect( balance ).toContainText( 'E2E Front Balance' );
		await expect( balance.locator( '.wps-wpr-block__value' ) ).toHaveText( /\d/ );

		// The leaderboard never prints email addresses.
		const leaderboard = page.locator( '.wp-block-wps-wpr-leaderboard' );
		if ( await leaderboard.count() ) {
			await expect( leaderboard ).not.toContainText( '@' );
		}
	} );
} );
