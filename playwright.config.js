// Playwright config for the plugin e2e tests.
// WP_BASE_URL     - site URL (default http://localhost:10043)
// WP_STORAGE_STATE - optional path to a logged-in admin storageState JSON
const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	testDir: './tests/e2e',
	timeout: 60000,
	expect: { timeout: 10000 },
	fullyParallel: false,
	workers: 1,
	reporter: [ [ 'list' ] ],
	outputDir: process.env.PW_OUTPUT_DIR || 'test-results',
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:10043',
		storageState: process.env.WP_STORAGE_STATE || undefined,
		screenshot: 'only-on-failure',
		trace: 'retain-on-failure',
		viewport: { width: 1280, height: 720 },
		ignoreHTTPSErrors: true,
		headless: true,
		launchOptions: process.env.PW_CHROMIUM_PATH ? { executablePath: process.env.PW_CHROMIUM_PATH } : {},
	},
	projects: [ { name: 'chromium', use: { browserName: 'chromium' } } ],
} );
