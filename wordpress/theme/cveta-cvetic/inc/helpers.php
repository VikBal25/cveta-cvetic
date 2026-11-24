<?php
/**
 * Shared data and small render helpers: shop contacts, icons, product cards, page heroes.
 */
defined( 'ABSPATH' ) || exit;

/** Shop contacts. Change them here (they are used in the header, footer, contact page and checkout). */
function cc_shop( $key = null ) {
	$shop = array(
		'name'      => 'Cvećara Cveta Cvetić',
		'phone'     => '064/41-24-337',
		'phoneHref' => 'tel:+381644124337',
		'email'     => 'tomatasovac@gmail.com',
		'address'   => 'Ilije Birčanina 39, Novi Sad 21000',
		'facebook'  => 'https://www.facebook.com/flowershop.cvetacvetic/',
		'instagram' => 'https://www.instagram.com/cveta_cvetic_/',
		'maps'      => 'https://www.google.com/maps/search/?api=1&query=Cve%C4%87ara+Cveta+Cveti%C4%87+Ilije+Bir%C4%8Danina+39+Novi+Sad',
		'pib'       => '113733973',
		'mb'        => '65201909',
	);
	return $key ? ( $shop[ $key ] ?? '' ) : $shop;
}

function cc_img( $file ) {
	return esc_url( get_theme_file_uri( 'assets/img/' . $file ) );
}

/** Inline SVG icons (same set as the static site). */
function cc_icon( $name, $class = 'icon' ) {
	static $icons = array(
		'cart'     => '<path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M6 6 5 3H2"/>',
		'heart'    => '<path d="M12 20s-7-4.4-9.2-8.6C1.2 8.2 3.1 4.5 6.8 4.5c2.1 0 3.6 1.2 5.2 3 1.6-1.8 3.1-3 5.2-3 3.7 0 5.6 3.7 4 6.9C19 15.6 12 20 12 20z"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'phone'    => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'truck'    => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
		'shield'   => '<path d="M12 3 5 6v5c0 4.5 3 8.4 7 10 4-1.6 7-5.5 7-10V6z"/><path d="m9 12 2 2 4-4"/>',
		'gift'     => '<rect x="3" y="8" width="18" height="5" rx="1"/><path d="M5 13v8h14v-8M12 8v13M12 8S10.5 3 8 4.5 9 8 12 8zM12 8s1.5-5 4-3.5S15 8 12 8z"/>',
		'trash'    => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
		'check'    => '<path d="m5 12 5 5 9-10"/>',
		'fb'       => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v7h4v-7h3l1-4h-4V8z"/>',
		'ig'       => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1"/>',
		'basket'   => '<path d="M3 10h18l-2 10H5z"/><path d="M8 10l4-6 4 6M9 14v3M12 14v3M15 14v3"/>',
		'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
		'chevron'  => '<path d="m9 6 6 6-6 6"/>',
		'flower2'  => '<circle cx="12" cy="7.5" r="2"/><path d="M12 5.5C11 3 13 3 12 5.5zM10 7.5c-2.5-1-2.5 1 0 0zM14 7.5c2.5-1 2.5 1 0 0zM12 9.5C11 12 13 12 12 9.5z"/><path d="M12 7.5c-1.6-3.2 1.6-3.2 0 0z"/><circle cx="12" cy="7.5" r="4.5"/><path d="M12 12v9M12 17c-2-3-5-3-6-2 1 2 4 3 6 2zM12 17c2-3 5-3 6-2-1 2-4 3-6 2z"/>',
	);
	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $icons[ $name ] ?? '' ) . '</svg>';
}

/** Card title: the quoted part of the name, e.g. Buket „Nežna pažnja“ – … → Nežna pažnja. */
function cc_short_name( $name ) {
	if ( preg_match( '/„([^“”"]+)[“”"]/u', $name, $m ) ) {
		$s = $m[1];
	} else {
		$s = explode( ' – ', $name )[0];
	}
	return trim( str_replace( array( '„', '“', '”', '"' ), '', $s ) );
}

function cc_money( $amount ) {
	return number_format( (float) $amount, 0, ',', '.' ) . ' RSD';
}

function cc_logo() {
	$leaf = '<svg class="leaf" viewBox="0 0 20 26" aria-hidden="true"><path d="M3 24C3 13 8 5 18 2c-1 9-6 15-15 22z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M4 23c3-6 7-11 12-17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
	return '<img class="emblem" src="' . cc_img( 'brand/emblem.png' ) . '" alt="" width="512" height="512">'
		. '<span class="wordmark"><span class="wm-line">CVETA</span><span class="wm-line">CVETI<span class="wm-c">C' . $leaf . '</span></span><span class="wm-tag">Flower &amp; Gift shop</span></span>';
}

/** Category strip on the shop page (same order and pictures as the static site). */
function cc_shop_cats() {
	return array(
		array( 'buketi', 'Buketi', 'cat-buketi.jpg' ),
		array( 'mini-buketi', 'Mini buketi', 'cat-mini-buketi.jpg' ),
		array( 'cvetni-aranzmani', 'Cvetni aranžmani', 'cat-cvetni-aranzmani.jpg' ),
		array( 'vecna-ruza', 'Večna ruža', 'cat-vecna-ruza.jpg' ),
		array( 'poklon-set', 'Poklon set', 'cat-poklon-set.jpg' ),
		array( 'saksijsko-cvece', 'Saksijsko cveće', 'cat-saksijsko-cvece.jpg' ),
		array( 'helijum-baloni', 'Helijum baloni', 'cat-helijum-baloni.jpg' ),
		array( 'kad-je-tesko', 'Kad je teško', 'cat-kad-je-tesko.jpg' ),
		array( 'bidermajeri', 'Bidermajeri', '' ),
		array( 'dodaci', 'Dodaci', '' ),
		array( 'specijalne-ponude', 'Specijalne ponude', 'special-ponude.jpg' ),
	);
}

function cc_cat_url( $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		return get_term_link( $term );
	}
	return wc_get_page_permalink( 'shop' );
}

function cc_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/** Badge on shop cards: "Sa medom" for teddy-bear products, "Novo" for the two newest. */
function cc_badge_for( WC_Product $p ) {
	static $newest = null;
	if ( null === $newest ) {
		$newest = wc_get_products( array( 'limit' => 2, 'orderby' => 'date', 'order' => 'DESC', 'return' => 'ids' ) );
	}
	if ( preg_match( '/\bmed(a|om|e)?\b|meda/iu', $p->get_name() ) ) {
		return array( 'Sa medom', 'right' );
	}
	if ( in_array( $p->get_id(), $newest, true ) ) {
		return array( 'Novo', 'pink' );
	}
	return null;
}

/**
 * Product card. $v2 = shop card (mockup 04), otherwise home card (mockup 01).
 */
function cc_product_card( $product, $v2 = false, $with_badge = false ) {
	$p = wc_get_product( $product );
	if ( ! $p ) {
		return '';
	}
	$id    = $p->get_id();
	$url   = esc_url( $p->get_permalink() );
	$title = esc_html( cc_short_name( $p->get_name() ) );
	$img   = wp_get_attachment_image( $p->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => cc_short_name( $p->get_name() ), 'loading' => 'lazy' ) );
	if ( ! $img ) {
		$img = wc_placeholder_img( 'woocommerce_thumbnail' );
	}
	$badge = '';
	if ( $with_badge && ( $b = cc_badge_for( $p ) ) ) {
		$badge = '<span class="tag-badge ' . esc_attr( $b[1] ) . '">' . esc_html( $b[0] ) . '</span>';
	}
	$price = '<span class="price">' . cc_money( $p->get_price() ) . '</span>';
	$buy   = $p->is_purchasable() && $p->is_in_stock()
		? '<button class="btn btn-primary btn-cart" type="button" data-add="' . $id . '">' . cc_icon( $v2 ? 'basket' : 'cart' ) . 'Dodaj u korpu</button>'
		: '<a class="btn btn-outline btn-cart" href="' . $url . '">Pogledaj</a>';
	$heart = function ( $cls ) use ( $id ) {
		return '<button class="' . $cls . '" type="button" data-wish="' . $id . '" aria-label="Dodaj u listu želja" aria-pressed="false">' . cc_icon( 'heart' ) . '</button>';
	};
	$media = '<a class="media" href="' . $url . '">' . $badge . $img . '</a>';

	if ( $v2 ) {
		return '<article class="product-card v2">' . $media . '<div class="body"><div class="name-row"><a class="title" href="' . $url . '">' . $title . '</a>' . $heart( 'wish-inline' ) . '</div>' . $price . $buy . '</div></article>';
	}
	return '<article class="product-card">' . $heart( 'wish' ) . $media . '<div class="body"><a class="title" href="' . $url . '">' . $title . '</a>' . $price . $buy . '</div></article>';
}

function cc_crumbs( $items ) {
	$out = '<nav class="crumbs" aria-label="Putanja"><a href="' . esc_url( home_url( '/' ) ) . '">Početna</a>';
	foreach ( $items as $label => $url ) {
		$out .= '<span>/</span>' . ( $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>' : esc_html( $label ) );
	}
	return $out . '</nav>';
}

/** Big page title over a pink photo that runs under the glass header. */
function cc_photo_hero( $title, $img, $crumb = null, $lead = '' ) {
	$crumbs = cc_crumbs( array( ( $crumb ?: $title ) => '' ) );
	$lead   = $lead ? '<p class="lead">' . esc_html( $lead ) . '</p>' : '';
	return '<section class="photo-hero"><div class="bg" style="background-image:url(' . cc_img( 'design/' . $img ) . ')" aria-hidden="true"></div><div class="container"><h1>' . esc_html( $title ) . '</h1>' . $crumbs . $lead . '</div></section>';
}

function cc_petals() {
	$out = '';
	foreach ( array( array( 3, 88, 20, .7 ), array( 32, 47, -30, .9 ), array( 34, 64, 40, .7 ), array( 40, 76, 10, 1.1 ), array( 88, 14, -20, 1.2 ), array( 94, 52, 30, .9 ), array( 90, 86, -10, 1.3 ), array( 60, 90, 60, .8 ) ) as $p ) {
		$out .= sprintf( '<i class="petal" style="left:%s%%;top:%s%%;transform:rotate(%sdeg) scale(%s)"></i>', $p[0], $p[1], $p[2], $p[3] );
	}
	return $out;
}

function cc_benefits() {
	return '<div class="benefits">'
		. '<div class="benefit"><span class="ic">' . cc_icon( 'flower2' ) . '</span><strong>Sveže i<br>pažljivo birano</strong></div>'
		. '<div class="benefit"><span class="ic">' . cc_icon( 'truck' ) . '</span><strong>Brza dostava<br>u Novom Sadu</strong></div>'
		. '<div class="benefit"><span class="ic">' . cc_icon( 'shield' ) . '</span><strong>Sigurna<br>kupovina</strong></div>'
		. '<div class="benefit"><span class="ic heart">' . cc_icon( 'heart' ) . '</span><strong>Poklanjamo<br>emocije</strong></div></div>';
}

function cc_cta() {
	return '<section class="section" style="padding-top:0"><div class="container"><div class="cta-wrap">'
		. '<img class="rose" src="' . cc_img( 'design/cta-rose.jpg' ) . '" alt="" aria-hidden="true">'
		. '<img class="petals-img" src="' . cc_img( 'design/cta-petals.jpg' ) . '" alt="" aria-hidden="true">'
		. '<div class="cta"><div class="txt"><span class="ic">' . cc_icon( 'phone' ) . '</span><div><span>Niste sigurni šta da izaberete?</span><strong>Pozovite nas: <a href="' . esc_attr( cc_shop( 'phoneHref' ) ) . '">' . esc_html( cc_shop( 'phone' ) ) . '</a></strong></div></div>'
		. '<a class="btn btn-primary" href="' . esc_url( cc_page_url( 'kontakt' ) ) . '">' . cc_icon( 'mail' ) . 'Pošaljite upit</a></div>'
		. '</div></div></section>';
}

function cc_post_card( $post ) {
	$img = get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) );
	return '<a class="post-card" href="' . esc_url( get_permalink( $post ) ) . '">' . $img
		. '<div class="body"><time>' . esc_html( cc_date( $post ) ) . '</time><h3>' . esc_html( get_the_title( $post ) ) . '</h3><p>' . esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ) . '</p></div></a>';
}

/** Date in Serbian Latin, e.g. 19. avgust 2025. */
function cc_date( $post ) {
	$months = array( 'januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar' );
	$t      = get_post_time( 'U', false, $post );
	return gmdate( 'j', $t ) . '. ' . $months[ (int) gmdate( 'n', $t ) - 1 ] . ' ' . gmdate( 'Y', $t ) . '.';
}
