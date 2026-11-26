<?php
/**
 * Editor script dependencies for the blocks pack (no build step needed).
 *
 * @package points-and-rewards-for-wooCommerce
 */

return array(
	'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
	'version'      => (string) filemtime( __DIR__ . '/editor.js' ),
);
