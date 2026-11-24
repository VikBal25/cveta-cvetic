<?php
/**
 * 301 redirects from the old site's page addresses to the new ones.
 * Runs only when WordPress found nothing at the requested address (404),
 * so it never shadows a real page, product or post.
 */
defined( 'ABSPATH' ) || exit;

function cc_old_paths() {
	return array(
		'cart'         => 'korpa',
		'checkout'     => 'porucivanje',
		'my-account'   => 'moj-nalog',
		'wishlist'     => 'lista-zelja',
		'wishlist-2'   => 'lista-zelja',
		'terms-of-use' => 'uslovi',
		'privacy-policy' => 'privatnost',
		'cveta-cvetic' => 'o-nama',
		'home-6'       => '',
		'404-2'        => '',
	);
}

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path  = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$parts = explode( '/', $path, 2 );
	$map   = cc_old_paths();
	if ( '' === $parts[0] || ! array_key_exists( $parts[0], $map ) ) {
		return;
	}
	// Keep the rest of the path (e.g. checkout/order-received/123/) and the query string.
	$target = $map[ $parts[0] ];
	$rest   = isset( $parts[1] ) && '' !== $target ? $parts[1] : '';
	$url    = home_url( '/' . ( '' === $target ? '' : trailingslashit( $target ) . ( $rest ? trailingslashit( $rest ) : '' ) ) );
	$query  = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY );
	if ( $query ) {
		$url .= '?' . $query;
	}
	wp_safe_redirect( $url, 301 );
	exit;
}, 1 );
