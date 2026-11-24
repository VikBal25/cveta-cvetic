<?php
// Router for PHP's built-in server: real files and folders are served as is, everything else goes to WordPress.
$root = $_SERVER['DOCUMENT_ROOT'];
$path = urldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
if ( $path !== '/' && file_exists( $root . $path ) ) {
	return false;
}
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
require $root . '/index.php';
