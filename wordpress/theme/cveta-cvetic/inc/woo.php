<?php
/**
 * WooCommerce: prices in RSD, shop sorting, header cart counter, layout clean-up.
 */
defined( 'ABSPATH' ) || exit;

// The theme styles everything itself.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'woocommerce-inline' );
}, 30 );

// Prices: "4.500 RSD".
add_filter( 'woocommerce_currency_symbol', function ( $symbol, $currency ) {
	return 'RSD' === $currency ? 'RSD' : $symbol;
}, 10, 2 );
add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

// Header cart counter is refreshed after AJAX add-to-cart / cart updates.
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$n = WC()->cart->get_cart_contents_count();
	$fragments['span.cc-cart-count'] = '<span class="count cc-cart-count" data-n="' . (int) $n . '">' . ( $n ? (int) $n : '' ) . '</span>';
	return $fragments;
} );

// Shop: 12 per page, same sort options as the static site.
add_filter( 'loop_shop_per_page', fn() => 12 );
add_filter( 'woocommerce_catalog_orderby', function () {
	return array(
		'menu_order' => 'Najpopularnije',
		'date'       => 'Najnovije',
		'price'      => 'Cena: od niže ka višoj',
		'price-desc' => 'Cena: od više ka nižoj',
		'title'      => 'Naziv A–Š',
	);
} );
add_filter( 'woocommerce_get_catalog_ordering_args', function ( $args, $orderby ) {
	if ( 'title' === $orderby ) {
		$args['orderby'] = 'title';
		$args['order']   = 'ASC';
	}
	return $args;
}, 10, 2 );

// No WooCommerce sidebar / wrappers / breadcrumbs: the theme templates render the layout.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_filter( 'woocommerce_show_page_title', '__return_false' );

// Cross-sells under the cart are not part of the design.
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

// After adding to cart without JS, return to the same page.
add_filter( 'woocommerce_add_to_cart_redirect', function ( $url ) {
	return $url ?: wp_get_referer();
} );

/** Product search from the header/drawer only searches products and uses the shop template. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_search() && 'product' === $q->get( 'post_type' ) ) {
		$q->set( 'posts_per_page', 12 );
	}
} );

/** Shorter notices text, e.g. after adding to cart. */
add_filter( 'wc_add_to_cart_message_html', function ( $message, $products ) {
	$names = array();
	foreach ( (array) $products as $id => $qty ) {
		$names[] = '„' . cc_short_name( get_the_title( $id ) ) . '“';
	}
	return sprintf( '%s %s u korpu. <a href="%s" class="button wc-forward">Pogledaj korpu</a>', implode( ', ', $names ), count( $names ) > 1 ? 'su dodati' : 'je dodat', esc_url( wc_get_cart_url() ) );
}, 10, 2 );
