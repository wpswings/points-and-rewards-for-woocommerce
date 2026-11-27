<?php
/**
 * CI helper: create or drop the test database without needing a mysql client.
 *
 * Usage: php db.php create|drop  (reads WPR_DB_HOST, WPR_DB_USER, WPR_DB_PASSWORD, WPR_DB_NAME).
 *
 * @package points-and-rewards
 */

$action = isset( $argv[1] ) ? $argv[1] : '';
$host   = (string) getenv( 'WPR_DB_HOST' );
$name   = (string) getenv( 'WPR_DB_NAME' );
if ( ! in_array( $action, array( 'create', 'drop' ), true ) || '' === $host || ! preg_match( '/^[A-Za-z0-9_]+$/', $name ) ) {
	fwrite( STDERR, "usage: WPR_DB_HOST=.. WPR_DB_NAME=.. php db.php create|drop\n" );
	exit( 2 );
}

$port   = null;
$socket = null;
if ( false !== strpos( $host, ':' ) ) {
	list( $host, $extra ) = explode( ':', $host, 2 );
	if ( ctype_digit( $extra ) ) {
		$port = (int) $extra;
	} else {
		$socket = $extra;
	}
}

mysqli_report( MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );
try {
	$db = new mysqli( $host, (string) getenv( 'WPR_DB_USER' ), (string) getenv( 'WPR_DB_PASSWORD' ), '', (int) $port, $socket );
	if ( 'create' === $action ) {
		$db->query( "CREATE DATABASE IF NOT EXISTS `{$name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );
	} else {
		$db->query( "DROP DATABASE IF EXISTS `{$name}`" );
	}
} catch ( Throwable $e ) {
	fwrite( STDERR, $e->getMessage() . "\n" );
	exit( 1 );
}
