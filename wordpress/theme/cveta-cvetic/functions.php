<?php
/**
 * Cveta Cvetić theme.
 */
defined( 'ABSPATH' ) || exit;

define( 'CC_VER', '1.0.0' );

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/woo.php';
require_once __DIR__ . '/inc/checkout.php';
require_once __DIR__ . '/inc/delivery.php';
require_once __DIR__ . '/inc/contact.php';
require_once __DIR__ . '/inc/strings.php';
require_once __DIR__ . '/inc/redirects.php';
require_once __DIR__ . '/inc/admin-prices.php';
require_once __DIR__ . '/inc/panel.php';
require_once __DIR__ . '/inc/emails.php';
require_once __DIR__ . '/inc/sitemap.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 900,
	) );
} );

/** Cache-busting version: changes whenever a CSS/JS file changes. */
function cc_asset_ver( $rel ) {
	$file = get_theme_file_path( $rel );
	return file_exists( $file ) ? (string) filemtime( $file ) : CC_VER;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'cc-fonts', 'https://fonts.googleapis.com/css2?family=Gilda+Display&family=Libre+Baskerville:ital,wght@1,400;1,700&family=Nunito+Sans:wght@400;600;700;800&family=Outfit:wght@400;500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'cc-site', get_theme_file_uri( 'assets/css/site.css' ), array(), cc_asset_ver( 'assets/css/site.css' ) );
	wp_enqueue_style( 'cc-woo', get_theme_file_uri( 'assets/css/woo.css' ), array( 'cc-site' ), cc_asset_ver( 'assets/css/woo.css' ) );
	wp_enqueue_script( 'cc-theme', get_theme_file_uri( 'assets/js/theme.js' ), array(), cc_asset_ver( 'assets/js/theme.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script( 'cc-theme', 'CC_DATA', array(
		'ajax'     => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
		'store'    => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
		'cartUrl'  => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
		'wishUrl'  => cc_page_url( 'lista-zelja' ),
		'privUrl'  => cc_page_url( 'privatnost' ),
		'shopUrl'  => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'phone'    => cc_shop( 'phone' ),
		'phoneHref' => cc_shop( 'phoneHref' ),
		'icons'    => array(
			'check'  => cc_icon( 'check' ),
			'heart'  => cc_icon( 'heart' ),
			'basket' => cc_icon( 'basket' ),
		),
	) );
	// Block styles are not used by the theme templates.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}, 20 );

add_action( 'wp_head', function () {
	echo '<meta name="theme-color" content="#f9d3e1">' . "\n";
	echo '<link rel="icon" href="' . cc_img( 'favicon.png' ) . '">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 2 );

add_filter( 'document_title_separator', fn() => '–' );

/** "data-page" on <body>, used by the JS (same names as the static site). */
function cc_page_key() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_shop() || is_product_taxonomy() ) {
			return 'shop';
		}
		if ( is_product() ) {
			return 'product';
		}
		if ( is_cart() ) {
			return 'cart';
		}
		if ( is_checkout() ) {
			return is_order_received_page() ? 'thanks' : 'checkout';
		}
	}
	if ( is_page_template( 'page-templates/kontakt.php' ) ) {
		return 'contact';
	}
	if ( is_page_template( 'page-templates/cesta-pitanja.php' ) ) {
		return 'faq';
	}
	if ( is_page_template( 'page-templates/lista-zelja.php' ) ) {
		return 'lista-zelja';
	}
	if ( is_home() ) {
		return 'blog';
	}
	if ( is_singular( 'post' ) ) {
		return 'post';
	}
	return is_404() ? 'notfound' : 'page';
}

add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more', fn() => '…' );

/** Main menu items (falls back to the fixed list when no WordPress menu is assigned). */
function cc_nav_items() {
	$items = array(
		array( home_url( '/' ), 'Početna', is_front_page() ),
		array( cc_page_url( 'o-nama' ), 'O nama', is_page( 'o-nama' ) ),
		array( wc_get_page_permalink( 'shop' ), 'Asortiman', is_woocommerce(), true ),
		array( get_permalink( get_option( 'page_for_posts' ) ), 'Aktuelnosti', is_home() || is_singular( 'post' ) ),
		array( cc_page_url( 'cesta-pitanja' ), 'Česta pitanja', is_page( 'cesta-pitanja' ) ),
		array( cc_page_url( 'kontakt' ), 'Kontakt', is_page( 'kontakt' ) ),
	);
	return $items;
}
