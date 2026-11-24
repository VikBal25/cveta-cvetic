<?php
/**
 * One-time setup of the local WordPress from the static site data.
 * Run: tools/wp/wp.sh eval-file ../tools/wp/setup.php
 * Safe to run again: existing products, posts and pages are updated, not duplicated.
 */
if ( ! defined( 'WP_CLI' ) ) {
	exit;
}
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$root = dirname( __DIR__, 2 ); // project folder
$pub  = $root . '/public';
$json = fn( $name ) => json_decode( file_get_contents( "$pub/data/$name.json" ), true );

/* ---------- Theme and general settings ---------- */
switch_theme( 'cveta-cvetic' );
update_option( 'blogname', 'Cveta Cvetić' );
update_option( 'posts_per_page', 12 );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );

$wc = array(
	'woocommerce_default_country'              => 'RS',
	'woocommerce_store_address'                => 'Ilije Birčanina 39',
	'woocommerce_store_city'                   => 'Novi Sad',
	'woocommerce_store_postcode'               => '21000',
	'woocommerce_currency'                     => 'RSD',
	'woocommerce_currency_pos'                 => 'right_space',
	'woocommerce_price_thousand_sep'           => '.',
	'woocommerce_price_decimal_sep'            => ',',
	'woocommerce_price_num_decimals'           => '0',
	'woocommerce_ship_to_countries'            => 'disabled',
	'woocommerce_calc_taxes'                   => 'no',
	'woocommerce_prices_include_tax'           => 'yes',
	'woocommerce_enable_coupons'               => 'no',
	'woocommerce_enable_guest_checkout'        => 'yes',
	'woocommerce_enable_checkout_login_reminder' => 'no',
	'woocommerce_enable_signup_and_login_from_checkout' => 'no',
	'woocommerce_enable_myaccount_registration' => 'no',
	'woocommerce_enable_reviews'               => 'no',
	'woocommerce_manage_stock'                 => 'no',
	'woocommerce_coming_soon'                  => 'no',
	'woocommerce_store_pages_only'             => 'no',
	'woocommerce_cart_redirect_after_add'      => 'no',
	'woocommerce_enable_ajax_add_to_cart'      => 'yes',
	'woocommerce_checkout_terms_and_conditions_checkbox_text' => 'Prihvatam [terms] i [privacy_policy].',
	'woocommerce_checkout_privacy_policy_text' => '',
	'woocommerce_registration_privacy_policy_text' => '',
	'woocommerce_email_from_name'              => 'Cveta Cvetić',
	'woocommerce_task_list_hidden'             => 'yes',
	'woocommerce_show_marketplace_suggestions' => 'no',
	'woocommerce_allow_tracking'               => 'no',
	'woocommerce_analytics_enabled'            => 'no',
	'woocommerce_feature_order_attribution_enabled' => 'no',
);
foreach ( $wc as $k => $v ) {
	update_option( $k, $v );
}
update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true, 'completed' => true ) );

// Payment methods (same three as the static site).
update_option( 'woocommerce_cod_settings', array(
	'enabled' => 'yes', 'title' => 'Plaćanje pouzećem', 'description' => 'Gotovinom prilikom dostave ili preuzimanja.',
	'instructions' => 'Porudžbinu plaćate gotovinom prilikom dostave ili preuzimanja.', 'enable_for_methods' => array(), 'enable_for_virtual' => 'yes',
) );
update_option( 'woocommerce_bacs_settings', array(
	'enabled' => 'yes', 'title' => 'Uplata na račun', 'description' => 'Instrukcije za uplatu šaljemo e-mailom nakon potvrde porudžbine.',
	'instructions' => 'Instrukcije za uplatu šaljemo e-mailom nakon što potvrdimo porudžbinu.', 'account_details' => '',
) );
update_option( 'woocommerce_cheque_settings', array(
	'enabled' => 'yes', 'title' => 'Karticom u cvećari', 'description' => 'Pri preuzimanju u radnji.',
	'instructions' => 'Porudžbinu plaćate karticom prilikom preuzimanja u cvećari.',
) );
update_option( 'woocommerce_gateway_order', array( 'cod' => 0, 'bacs' => 1, 'cheque' => 2 ) );

/* ---------- Helpers ---------- */
function cc_setup_attach( $file, $parent = 0, $title = '' ) {
	global $wpdb;
	if ( ! file_exists( $file ) ) {
		return 0;
	}
	$name     = basename( $file );
	$existing = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_cc_source' AND meta_value = %s LIMIT 1", $name ) );
	if ( $existing ) {
		return (int) $existing;
	}
	$tmp = wp_tempnam( $name );
	copy( $file, $tmp );
	$id = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), $parent, $title );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( "$name: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_cc_source', $name );
	return (int) $id;
}

function cc_setup_page( $slug, $title, $content = '', $template = '', $meta = array(), $order = 0 ) {
	$page = get_page_by_path( $slug );
	$data = array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title,
		'post_content' => $content, 'menu_order' => $order, 'comment_status' => 'closed',
	);
	$id = $page ? wp_update_post( array_merge( $data, array( 'ID' => $page->ID ) ) ) : wp_insert_post( $data );
	update_post_meta( $id, '_wp_page_template', $template ?: 'default' );
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	return $id;
}

/** Links of the static site (kontakt.html, asortiman.html?kat=…) and of the old cvetacvetic.rs → WordPress URLs. */
function cc_setup_links( $html ) {
	$html = preg_replace_callback( '#href="(https?://cvetacvetic\.rs)?/?product-category/(?:https?:/+cvetacvetic\.rs/product-category/)?(?:suze/)?([a-z0-9-]+)/?"#', fn( $m ) => 'href="' . home_url( '/product-category/' . $m[2] . '/' ) . '"', $html );
	$html = preg_replace( '#href="https?://cvetacvetic\.rs/shop/?"#', 'href="' . home_url( '/shop/' ) . '"', $html );
	$html = preg_replace( '#href="https?://cvetacvetic\.rs/([a-z0-9-]+)/?"#', 'href="' . home_url( '/$1/' ) . '"', $html );
	$html = preg_replace( '#href="https?://cvetacvetic\.rs/?"#', 'href="' . home_url( '/' ) . '"', $html );
	$html = preg_replace_callback( '#href="([a-z0-9-]+)\.html(?:\?kat=([a-z0-9-]+))?"#', function ( $m ) {
		if ( ! empty( $m[2] ) ) {
			return 'href="' . home_url( '/product-category/' . $m[2] . '/' ) . '"';
		}
		$map = array( 'index' => '/', 'asortiman' => '/shop/', 'aktuelnosti' => '/aktuelnosti/' );
		return 'href="' . home_url( $map[ $m[1] ] ?? '/' . $m[1] . '/' ) . '"';
	}, $html );
	return $html;
}

/* ---------- Product categories ---------- */
$cat_names = array(
	'buketi' => 'Buketi', 'mini-buketi' => 'Mini buketi', 'cvetni-aranzmani' => 'Cvetni aranžmani', 'vecna-ruza' => 'Večna ruža',
	'poklon-set' => 'Poklon set', 'saksijsko-cvece' => 'Saksijsko cveće', 'helijum-baloni' => 'Helijum baloni', 'kad-je-tesko' => 'Kad je teško',
	'bidermajeri' => 'Bidermajeri', 'dodaci' => 'Dodaci', 'specijalne-ponude' => 'Specijalne ponude',
);
$cat_ids = array();
$order   = 0;
foreach ( $cat_names as $slug => $name ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	$t = $t ? array( 'term_id' => $t->term_id ) : wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
	$cat_ids[ $slug ] = (int) $t['term_id'];
	update_term_meta( $cat_ids[ $slug ], 'order', $order++ );
}
// "Uncategorized" → "Ostalo" (WooCommerce needs a default category).
$default = (int) get_option( 'default_product_cat' );
if ( $default ) {
	wp_update_term( $default, 'product_cat', array( 'name' => 'Ostalo', 'slug' => 'ostalo' ) );
}
WP_CLI::log( 'Kategorije: ' . count( $cat_ids ) );

/* ---------- Products ---------- */
$featured = array( 'Puder roze san', 'Ružičasti san', 'Lavanda elegancija', 'Ljubičasti osmeh', 'Luksuzna orhideja', 'Pastelna elegancija', 'Ljubičasta elegancija', 'Nežna pažnja' );
$rank     = function ( $p ) use ( $featured ) {
	foreach ( $featured as $i => $n ) {
		if ( false !== strpos( $p['name'], $n ) ) {
			return $i;
		}
	}
	return array_intersect( $p['cats'], array( 'poklon-set', 'dodaci' ) ) ? 200 : 100;
};
$n = 0;
foreach ( $json( 'products' ) as $p ) {
	$existing = get_page_by_path( $p['slug'], OBJECT, 'product' );
	$product  = $existing ? wc_get_product( $existing->ID ) : new WC_Product_Simple();
	$product->set_name( $p['name'] );
	$product->set_slug( $p['slug'] );
	$product->set_status( 'publish' );
	$product->set_regular_price( (string) $p['price'] );
	$product->set_description( cc_setup_links( $p['description'] ?: '<p>' . esc_html( $p['short'] ) . '</p>' ) );
	$product->set_short_description( $p['short'] );
	$product->set_category_ids( array_values( array_filter( array_map( fn( $c ) => $cat_ids[ $c ] ?? 0, $p['cats'] ) ) ) ?: array( $default ) );
	$product->set_menu_order( $rank( $p ) );
	$product->set_featured( $rank( $p ) < 100 );
	$product->set_date_created( $p['date'] );
	$product->set_reviews_allowed( false );
	$id = $product->save();
	wp_set_object_terms( $id, $p['tags'], 'product_tag' );
	if ( ! $product->get_image_id() ) {
		$img = cc_setup_attach( $pub . '/' . $p['image'], $id, $p['name'] );
		if ( $img ) {
			$product->set_image_id( $img );
			$product->save();
		}
	}
	$n++;
}
WP_CLI::log( "Proizvodi: $n" );

// Category pictures (used in the shop strip when the theme has no design picture).
foreach ( $cat_ids as $slug => $tid ) {
	if ( ! get_term_meta( $tid, 'thumbnail_id', true ) ) {
		$first = wc_get_products( array( 'category' => array( $slug ), 'limit' => 1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
		if ( $first && $first[0]->get_image_id() ) {
			update_term_meta( $tid, 'thumbnail_id', $first[0]->get_image_id() );
		}
	}
}

/* ---------- Blog posts (Aktuelnosti) ---------- */
foreach ( array( 'hello-world' ) as $slug ) {
	$hello = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $hello ) {
		wp_delete_post( $hello->ID, true );
	}
}
$sample = get_page_by_path( 'sample-page' );
if ( $sample ) {
	wp_delete_post( $sample->ID, true );
}
wp_update_term( (int) get_option( 'default_category' ), 'category', array( 'name' => 'Aktuelnosti', 'slug' => 'aktuelnosti-kategorija' ) );
$n = 0;
foreach ( $json( 'posts' ) as $post ) {
	$existing = get_page_by_path( $post['slug'], OBJECT, 'post' );
	$data     = array(
		'post_type' => 'post', 'post_status' => 'publish', 'post_name' => $post['slug'], 'post_title' => $post['title'],
		'post_content' => cc_setup_links( $post['content'] ), 'post_excerpt' => $post['excerpt'],
		'post_date' => $post['date'] . ' 10:00:00', 'comment_status' => 'closed',
	);
	$id = $existing ? wp_update_post( array_merge( $data, array( 'ID' => $existing->ID ) ) ) : wp_insert_post( $data );
	if ( ! empty( $post['image'] ) && ! has_post_thumbnail( $id ) ) {
		$img = cc_setup_attach( $pub . '/' . $post['image'], $id, $post['title'] );
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
	}
	$n++;
}
WP_CLI::log( "Članci: $n" );

/* ---------- Pages ---------- */
$legal = fn( $name ) => cc_setup_links( file_get_contents( "$root/tools/legal/$name.html" ) );

$faq = array(
	array( 'Gde se nalazi Cvećara Cveta Cvetić?', 'Naša cvećara se nalazi u Novom Sadu, na adresi Ilije Birčanina 39. Tačnu lokaciju i uputstvo kako da nas pronađete možete videti na <a href="kontakt.html">mapi na stranici Kontakt</a> ili nas pozovite direktno za detaljne informacije.' ),
	array( 'Da li radite dostavu cveća?', 'Da, vršimo brzu i diskretnu dostavu cveća na teritoriji Novog Sada i okoline. Moguće je i zakazivanje dostave za tačno određeno vreme.' ),
	array( 'Kako mogu da naručim cveće?', 'Cveće možete naručiti direktno na sajtu, putem telefona, Viber-a, Instagrama, naše Facebook stranice ili dolaskom u cvećaru.' ),
	array( 'Da li mogu platiti online?', 'Omogućavamo plaćanje putem bankovnog transfera (uplata na račun), kao i plaćanje karticom u prodavnici. Za dostave je moguće i plaćanje pouzećem.' ),
	array( 'Primate li porudžbine za venčanja i proslave?', 'Naravno! Specijalizovani smo za dekoracije venčanja, rođendana, slava i drugih proslava. Moguće je zakazivanje konsultacija za detaljan dogovor.' ),
	array( 'Koje vrste cveća imate u ponudi?', 'Uvek imamo sveže rezano cveće kao što su ruže, ljiljani, gerberi, orhideje, margarete i sezonsko cveće. Takođe, nudimo saksijsko cveće i dekorativne aranžmane.' ),
	array( 'Da li pravite personalizovane aranžmane?', 'Da, svaki buket i aranžman možemo napraviti u skladu sa Vašim željama – birate boje, vrstu cveća, ambalažu i poruku.' ),
	array( 'Koliko unapred treba da poručim cveće za veće događaje?', 'Za venčanja i veće događaje preporučujemo da nas kontaktirate barem 5–7 dana ranije, kako bismo sve isplanirali na vreme.' ),
	array( 'Mogu li dodati poruku ili čestitku uz buket?', 'Naravno! Uz svaki buket možete dodati personalizovanu poruku, čestitku ili karticu – besplatno ili uz malu doplatu, u zavisnosti od dizajna. Tekst poruke upišite pri naručivanju.' ),
);
$faq_html = '';
foreach ( $faq as $qa ) {
	$faq_html .= "<!-- wp:heading {\"level\":3} -->\n<h3>{$qa[0]}</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>{$qa[1]}</p>\n<!-- /wp:paragraph -->\n\n";
}

$dostava = <<<HTML
<h2>Naručivanje</h2>
<p>Proizvod izaberite u <a href="asortiman.html">asortimanu</a>, dodajte ga u korpu i popunite formu za naručivanje. Nakon slanja porudžbine kontaktiraćemo Vas radi potvrde. Cveće možete naručiti i putem telefona <a href="tel:+381644124337">064/41-24-337</a>, Viber-a, Instagrama, naše Facebook stranice ili direktno dolaskom u cvećaru.</p>
<p>Uz svaki buket možete dodati personalizovanu poruku ili čestitku – tekst upišite u polje „Poruka na čestitki“ prilikom naručivanja.</p>
<h2>Plaćanje</h2>
<ul>
<li><strong>Pouzećem</strong> – gotovinom prilikom dostave ili preuzimanja.</li>
<li><strong>Uplatom na račun</strong> – instrukcije za uplatu šaljemo e-mailom nakon potvrde porudžbine.</li>
<li><strong>Karticom u cvećari</strong> – prilikom preuzimanja u radnji.</li>
</ul>
<p>Sve cene su iskazane u dinarima (RSD) sa uračunatim PDV-om.</p>
<h2>Dostava</h2>
<p>Vršimo brzu i diskretnu dostavu cveća na teritoriji Novog Sada i okoline. Dostava na teritoriji Novog Sada je <strong>besplatna</strong>, a moguće je i zakazivanje dostave za tačno određeno vreme. Dostavu van Novog Sada dogovaramo pojedinačno.</p>
<p>Porudžbinu možete preuzeti i lično u cvećari na adresi Ilije Birčanina 39, Novi Sad.</p>
<h2>Venčanja i veći događaji</h2>
<p>Za venčanja i veće događaje preporučujemo da nas kontaktirate barem 5–7 dana ranije, kako bismo sve isplanirali na vreme.</p>
HTML;

$about = '<p>Cvećara Cveta Cvetić iz Novog Sada nastala je iz iskrene ljubavi prema cveću i želje da se svakodnevnim gestovima unese toplina, radost i pažnja u tuđe živote. Mi ne prodajemo samo cveće – mi stvaramo uspomene, ulepšavamo važne trenutke i prenosimo emocije kroz pažljivo birane cvetne aranžmane.</p>';

$home     = cc_setup_page( 'pocetna', 'Početna', '', '', array(), 0 );
$onama    = cc_setup_page( 'o-nama', 'O nama', $about, 'page-templates/o-nama.php', array(), 1 );
$blog     = cc_setup_page( 'aktuelnosti', 'Aktuelnosti', '', '', array(), 3 );
$faq_id   = cc_setup_page( 'cesta-pitanja', 'Česta pitanja', cc_setup_links( $faq_html ), 'page-templates/cesta-pitanja.php', array(), 4 );
$kontakt  = cc_setup_page( 'kontakt', 'Kontakt', '', 'page-templates/kontakt.php', array(), 5 );
cc_setup_page( 'dostava', 'Naručivanje, plaćanje i dostava', cc_setup_links( $dostava ), '', array( '_cc_hero' => 'hero-blog.jpg', '_cc_cta' => '1' ), 6 );
cc_setup_page( 'lista-zelja', 'Lista želja', '', 'page-templates/lista-zelja.php', array(), 7 );
$uslovi   = cc_setup_page( 'uslovi', 'Uslovi korišćenja i prodaje', $legal( 'uslovi' ), '', array( '_cc_hero' => 'hero-faq.jpg' ), 8 );
$privacy  = cc_setup_page( 'privatnost', 'Pravila privatnosti', $legal( 'privatnost' ), '', array( '_cc_hero' => 'hero-onama.jpg' ), 9 );
cc_setup_page( 'mapa-sajta', 'Mapa sajta', '', 'page-templates/mapa-sajta.php', array(), 10 );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', $blog );
update_option( 'wp_page_for_privacy_policy', $privacy );
update_option( 'woocommerce_terms_page_id', $uslovi );

// WooCommerce pages: classic shortcodes (the theme templates style them), Serbian titles and URLs.
$wc_pages = array(
	'shop'      => array( 'shop', 'Asortiman', '', 2 ),
	'cart'      => array( 'korpa', 'Korpa', '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->', 11 ),
	'checkout'  => array( 'porucivanje', 'Naručivanje', '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->', 12 ),
	'myaccount' => array( 'moj-nalog', 'Moj nalog', '<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->', 13 ),
);
foreach ( $wc_pages as $key => $cfg ) {
	$id = wc_get_page_id( $key );
	if ( $id > 0 && get_post( $id ) ) {
		wp_update_post( array( 'ID' => $id, 'post_name' => $cfg[0], 'post_title' => $cfg[1], 'post_content' => $cfg[2], 'menu_order' => $cfg[3], 'post_status' => 'publish' ) );
	} else {
		$id = cc_setup_page( $cfg[0], $cfg[1], $cfg[2], '', array(), $cfg[3] );
		update_option( "woocommerce_{$key}_page_id", $id );
	}
}
// Old refund/returns draft page from WooCommerce is not needed.
$refund = get_page_by_path( 'refund_returns' );
if ( $refund ) {
	wp_delete_post( $refund->ID, true );
}

flush_rewrite_rules( true );
WP_CLI::success( 'Sajt je podešen.' );
