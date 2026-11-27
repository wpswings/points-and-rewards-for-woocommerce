// CI helper: log in as the admin once and save the session for the e2e specs (WP_STORAGE_STATE).
// Usage: node login.js <output.json>   (reads WP_BASE_URL, WP_USERNAME, WP_PASSWORD).
const { chromium } = require( '@playwright/test' );

( async () => {
	const out = process.argv[ 2 ];
	const browser = await chromium.launch( process.env.PW_CHROMIUM_PATH ? { executablePath: process.env.PW_CHROMIUM_PATH } : {} );
	const page = await browser.newPage();
	await page.goto( process.env.WP_BASE_URL + '/wp-login.php' );
	await page.fill( '#user_login', process.env.WP_USERNAME );
	await page.fill( '#user_pass', process.env.WP_PASSWORD );
	await page.click( '#wp-submit' );
	await page.waitForURL( /wp-admin/, { timeout: 60000 } );
	await page.context().storageState( { path: out } );
	await browser.close();
	console.log( 'Saved admin session to ' + out );
} )().catch( ( e ) => {
	console.error( e );
	process.exit( 1 );
} );
