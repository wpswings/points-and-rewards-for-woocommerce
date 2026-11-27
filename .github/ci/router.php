<?php
/**
 * Router for PHP's built-in web server so WordPress pretty permalinks work in CI.
 *
 * @package points-and-rewards
 */

$wpr_root = rtrim( $_SERVER['DOCUMENT_ROOT'], '/' );
$wpr_path = urldecode( (string) parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

// Existing files (static assets and PHP entry points such as wp-login.php) are served as is.
if ( '/' !== $wpr_path && is_file( $wpr_root . $wpr_path ) ) {
	return false;
}

// Directories with an index.php (e.g. /wp-admin/).
if ( is_dir( $wpr_root . $wpr_path ) && is_file( $wpr_root . rtrim( $wpr_path, '/' ) . '/index.php' ) ) {
	$wpr_index                  = rtrim( $wpr_path, '/' ) . '/index.php';
	$_SERVER['SCRIPT_NAME']     = $wpr_index;
	$_SERVER['PHP_SELF']        = $wpr_index;
	$_SERVER['SCRIPT_FILENAME'] = $wpr_root . $wpr_index;
	chdir( dirname( $wpr_root . $wpr_index ) );
	require $wpr_root . $wpr_index;
	return true;
}

// Everything else goes through WordPress.
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $wpr_root . '/index.php';
chdir( $wpr_root );
require $wpr_root . '/index.php';
return true;
