<?php defined( 'ABSPATH' ) || exit; ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr( cc_page_key() ); ?>">
<?php wp_body_open(); ?>
<?php
$cats = array_filter( cc_shop_cats(), fn( $c ) => (bool) get_term_by( 'slug', $c[0], 'product_cat' ) );
$sub  = '';
foreach ( $cats as $c ) {
	$active = is_product_category( $c[0] ) ? ' class="active"' : '';
	$sub   .= '<a href="' . esc_url( cc_cat_url( $c[0] ) ) . '"' . $active . '>' . esc_html( $c[1] ) . '</a>';
}
$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<div class="frame">
	<header class="header"><div class="container">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Cveta Cvetić – Flower &amp; Gift shop, početna"><?php echo cc_logo(); // phpcs:ignore ?></a>
		<nav class="nav" aria-label="Glavni meni"><ul>
			<?php foreach ( cc_nav_items() as $item ) : ?>
				<?php $a = '<a href="' . esc_url( $item[0] ) . '" class="' . ( $item[2] ? 'active' : '' ) . '">' . esc_html( $item[1] ) . '</a>'; ?>
				<?php if ( ! empty( $item[3] ) ) : ?>
					<li class="has-sub"><?php echo $a; // phpcs:ignore ?><div class="sub"><?php echo $sub; // phpcs:ignore ?><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Svi proizvodi</a></div></li>
				<?php else : ?>
					<li><?php echo $a; // phpcs:ignore ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul></nav>
		<div class="header-actions">
			<a class="icon-btn" href="<?php echo esc_url( cc_page_url( 'lista-zelja' ) ); ?>" aria-label="Lista želja" title="Lista želja"><?php echo cc_icon( 'heart' ); // phpcs:ignore ?><span class="count" data-wish-count></span></a>
			<a class="icon-btn" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="Korpa" title="Korpa"><?php echo cc_icon( 'cart' ); // phpcs:ignore ?><span class="count cc-cart-count" data-n="<?php echo (int) $count; ?>"><?php echo $count ? (int) $count : ''; ?></span></a>
			<button class="icon-btn menu-toggle" type="button" data-menu-open aria-label="Otvori meni"><?php echo cc_icon( 'menu' ); // phpcs:ignore ?></button>
		</div>
	</div></header>
	<main id="main">
