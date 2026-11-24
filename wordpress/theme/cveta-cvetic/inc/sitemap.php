<?php
/**
 * WordPress sitemap (/wp-sitemap.xml) for Google: only real content.
 * - no author list (it would show the admin user names);
 * - no cart, checkout, account or wishlist pages.
 * /sitemap.xml redirects here (WordPress core does this once the old static file is gone).
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $post_type ) {
	if ( 'page' !== $post_type ) {
		return $args;
	}
	$skip = array();
	if ( function_exists( 'wc_get_page_id' ) ) {
		foreach ( array( 'cart', 'checkout', 'myaccount' ) as $p ) {
			$skip[] = wc_get_page_id( $p );
		}
	}
	$wishlist = get_page_by_path( 'lista-zelja' );
	if ( $wishlist ) {
		$skip[] = $wishlist->ID;
	}
	$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), array_filter( $skip, fn( $id ) => $id > 0 ) );
	return $args;
}, 10, 2 );
