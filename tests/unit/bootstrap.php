<?php
/**
 * PHPUnit bootstrap.
 *
 * Loads the WordPress install this plugin lives in, so tests run against real
 * WordPress and WooCommerce. Point WP_LOAD_PATH at another wp-load.php to
 * run them elsewhere. Tests must clean up any data they create.
 *
 * @package points-and-rewards
 */

$wps_wpr_wp_load = getenv( 'WP_LOAD_PATH' ) ? getenv( 'WP_LOAD_PATH' ) : dirname( __DIR__, 5 ) . '/wp-load.php';

if ( ! file_exists( $wps_wpr_wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found at {$wps_wpr_wp_load}. Set WP_LOAD_PATH.\n" );
	exit( 1 );
}

if ( ! defined( 'WP_USE_THEMES' ) ) {
	define( 'WP_USE_THEMES', false );
}
$_SERVER['HTTP_HOST']   = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : 'localhost';
$_SERVER['REQUEST_URI'] = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';

require_once $wps_wpr_wp_load;
