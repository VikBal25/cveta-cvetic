<?php
/**
 * Plugin Name: Cveta Cvetić – Jednokratno podešavanje
 * Description: Pri aktivaciji jednom podesi stranice i prodavnicu za temu Cveta Cvetić (stranice, naslovna, korpa, naručivanje, opcije WooCommerce-a). Posle toga se može deaktivirati i obrisati.
 * Version: 1.0
 * Requires PHP: 7.0
 */
defined( 'ABSPATH' ) || exit;

register_activation_hook( __FILE__, 'cc_live_setup' );

add_action( 'admin_notices', function () {
	$log = get_option( 'cc_live_setup_log' );
	if ( $log && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-success"><p><strong>Cveta Cvetić podešavanje:</strong></p><pre id="cc-setup-log" style="white-space:pre-wrap">' . esc_html( $log ) . '</pre></div>';
	}
} );

function cc_live_links( $html ) {
	$html = preg_replace_callback( '#href="([a-z0-9-]+)\.html(?:\?kat=([a-z0-9-]+))?"#', function ( $m ) {
		if ( ! empty( $m[2] ) ) {
			return 'href="' . home_url( '/product-category/' . $m[2] . '/' ) . '"';
		}
		$map = array( 'index' => '/', 'asortiman' => '/shop/', 'aktuelnosti' => '/aktuelnosti/' );
		return 'href="' . home_url( isset( $map[ $m[1] ] ) ? $map[ $m[1] ] : '/' . $m[1] . '/' ) . '"';
	}, $html );
	$html = preg_replace( '#href="https?://cvetacvetic\.rs/?"#', 'href="' . home_url( '/' ) . '"', $html );
	return $html;
}

function cc_live_page( $slug, $title, $content, $template = '', $meta = array(), $order = 0, &$log = array() ) {
	$page = get_page_by_path( $slug );
	$data = array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title,
		'post_content' => $content, 'menu_order' => $order, 'comment_status' => 'closed',
	);
	if ( null === $content && $page ) {
		unset( $data['post_content'] );
	} elseif ( null === $content ) {
		$data['post_content'] = '';
	}
	$id = $page ? wp_update_post( array_merge( $data, array( 'ID' => $page->ID ) ), true ) : wp_insert_post( $data, true );
	if ( is_wp_error( $id ) ) {
		$log[] = "GREŠKA stranica $slug: " . $id->get_error_message();
		return 0;
	}
	update_post_meta( $id, '_wp_page_template', $template ? $template : 'default' );
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	$log[] = ( $page ? 'Ažurirana' : 'Napravljena' ) . " stranica /$slug/ (#$id)";
	return $id;
}

function cc_live_setup() {
	$log = array();
	$dir = __DIR__;

	// Store options (prices, checkout, e-mails). Existing products, orders and customers are not touched.
	$wc = array(
		'woocommerce_currency_pos'                 => 'right_space',
		'woocommerce_price_thousand_sep'           => '.',
		'woocommerce_price_decimal_sep'            => ',',
		'woocommerce_price_num_decimals'           => '0',
		'woocommerce_ship_to_countries'            => 'disabled',
		'woocommerce_enable_coupons'               => 'no',
		'woocommerce_enable_guest_checkout'        => 'yes',
		'woocommerce_enable_checkout_login_reminder' => 'no',
		'woocommerce_enable_signup_and_login_from_checkout' => 'no',
		'woocommerce_enable_reviews'               => 'no',
		'woocommerce_manage_stock'                 => 'no',
		'woocommerce_coming_soon'                  => 'no',
		'woocommerce_cart_redirect_after_add'      => 'no',
		'woocommerce_enable_ajax_add_to_cart'      => 'yes',
		'woocommerce_checkout_terms_and_conditions_checkbox_text' => 'Prihvatam [terms] i [privacy_policy].',
		'woocommerce_checkout_privacy_policy_text' => '',
		'woocommerce_email_from_name'              => 'Cveta Cvetić',
		'woocommerce_email_from_address'           => 'info@cvetacvetic.rs', // technical sender only; replies go to the reply-to address
		'woocommerce_task_list_hidden'             => 'yes',
		'woocommerce_show_marketplace_suggestions' => 'no',
		'woocommerce_allow_tracking'               => 'no',
	);
	foreach ( $wc as $k => $v ) {
		update_option( $k, $v );
	}
	update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true, 'completed' => true ) );
	update_option( 'default_comment_status', 'closed' );
	$log[] = 'Opcije prodavnice podešene (cene u RSD bez decimala, bez dostave preko WooCommerce zona, poručivanje bez naloga).';

	// Cash on delivery must not depend on shipping methods (shipping is handled by the theme's delivery fields).
	$cod = get_option( 'woocommerce_cod_settings', array() );
	$cod = is_array( $cod ) ? $cod : array();
	$cod['enable_for_methods'] = array();
	$cod['enable_for_virtual'] = 'yes';
	update_option( 'woocommerce_cod_settings', $cod );

	// Pages.
	$faq = array(
		array( 'Gde se nalazi Cvećara Cveta Cvetić?', 'Naša cvećara se nalazi u Novom Sadu, na adresi Ilije Birčanina 39. Tačnu lokaciju i uputstvo kako da nas pronađete možete videti na <a href="kontakt.html">mapi na stranici Kontakt</a> ili nas pozovite direktno za detaljne informacije.' ),
		array( 'Da li radite dostavu cveća?', 'Da, vršimo brzu i diskretnu dostavu cveća na teritoriji Novog Sada i okoline. Moguće je i zakazivanje dostave za tačno određeno vreme. Dostava u Novom Sadu je 500 RSD (besplatno za porudžbine od 3.000 RSD), u Petrovaradinu i Sremskoj Kamenici 700 RSD (besplatno od 3.100 RSD), a za okolinu cenu dogovaramo telefonom.' ),
		array( 'Kako mogu da naručim cveće?', 'Cveće možete naručiti direktno na sajtu, putem telefona, Viber-a, Instagrama, naše Facebook stranice ili dolaskom u cvećaru.' ),
		array( 'Kako mogu da platim?', 'Porudžbinu možete platiti opštom uplatnicom (e-banking, banka ili menjačnica), pouzećem prilikom dostave ili u našoj cvećari prilikom preuzimanja.' ),
		array( 'Primate li porudžbine za venčanja i proslave?', 'Naravno! Specijalizovani smo za dekoracije venčanja, rođendana, slava i drugih proslava. Moguće je zakazivanje konsultacija za detaljan dogovor.' ),
		array( 'Koje vrste cveća imate u ponudi?', 'Uvek imamo sveže rezano cveće kao što su ruže, ljiljani, gerberi, orhideje, margarete i sezonsko cveće. Takođe, nudimo saksijsko cveće i dekorativne aranžmane.' ),
		array( 'Da li pravite personalizovane aranžmane?', 'Da, svaki buket i aranžman možemo napraviti u skladu sa Vašim željama – birate boje, vrstu cveća, ambalažu i poruku.' ),
		array( 'Koliko unapred treba da poručim cveće za veće događaje?', 'Za venčanja i veće događaje preporučujemo da nas kontaktirate barem 5–7 dana ranije, kako bismo sve isplanirali na vreme.' ),
		array( 'Mogu li dodati poruku ili čestitku uz buket?', 'Naravno! Uz svaki buket možete dodati personalizovanu poruku ili čestitku. Tekst poruke upišite pri naručivanju.' ),
	);
	$faq_html = '';
	foreach ( $faq as $qa ) {
		$faq_html .= "<!-- wp:heading {\"level\":3} -->\n<h3>{$qa[0]}</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>{$qa[1]}</p>\n<!-- /wp:paragraph -->\n\n";
	}

	$dostava = '<h2>Naručivanje</h2>
<p>Proizvod izaberite u <a href="asortiman.html">asortimanu</a>, dodajte ga u korpu i popunite formu za naručivanje. Nakon slanja porudžbine kontaktiraćemo Vas radi potvrde. Cveće možete naručiti i putem telefona <a href="tel:+381644124337">064/41-24-337</a>, Viber-a, Instagrama, naše Facebook stranice ili direktno dolaskom u cvećaru.</p>
<p>Uz svaki buket možete dodati personalizovanu poruku ili čestitku – tekst upišite u polje „Poruka na čestitki“ prilikom naručivanja.</p>
<h2>Plaćanje</h2>
<ul>
<li><strong>Opštom uplatnicom</strong> – uplata putem e-bankinga, banke ili menjačnice; podaci za uplatu se prikazuju nakon porudžbine i šalju e-mailom.</li>
<li><strong>Pouzećem</strong> – gotovinom prilikom dostave.</li>
<li><strong>U našoj cvećari</strong> – prilikom preuzimanja u radnji.</li>
</ul>
<p>Sve cene su iskazane u dinarima (RSD) sa uračunatim PDV-om.</p>
<h2>Dostava</h2>
<p>Vršimo brzu i diskretnu dostavu cveća, a moguće je i zakazivanje dostave za tačno određeno vreme.</p><ul><li><strong>Novi Sad (leva obala Dunava)</strong> – 500 RSD, <strong>besplatno za porudžbine od 3.000 RSD</strong>.</li><li><strong>Petrovaradin i Sremska Kamenica</strong> – 700 RSD, <strong>besplatno od 3.100 RSD</strong>.</li><li><strong>Okolina Novog Sada</strong> (Veternik, Futog, Kać, Sremski Karlovci…) – cenu i uslove dogovaramo telefonom.</li></ul><p>Cena dostave se računa automatski pri naručivanju, kada izaberete zonu dostave.</p>
<p>Porudžbinu možete preuzeti i lično u cvećari na adresi Ilije Birčanina 39, Novi Sad.</p>
<h2>Venčanja i veći događaji</h2>
<p>Za venčanja i veće događaje preporučujemo da nas kontaktirate barem 5–7 dana ranije, kako bismo sve isplanirali na vreme.</p>';

	$about = '<p>Cvećara Cveta Cvetić iz Novog Sada nastala je iz iskrene ljubavi prema cveću i želje da se svakodnevnim gestovima unese toplina, radost i pažnja u tuđe živote. Mi ne prodajemo samo cveće – mi stvaramo uspomene, ulepšavamo važne trenutke i prenosimo emocije kroz pažljivo birane cvetne aranžmane.</p>';

	// Old pages replaced by new ones become drafts (content stays in the admin); their addresses redirect via the theme.
	foreach ( array( 'privacy-policy', 'terms-of-use', 'cveta-cvetic', 'wishlist', 'wishlist-2', 'home-6', '404-2' ) as $old ) {
		$p = get_page_by_path( $old );
		if ( $p && 'publish' === $p->post_status ) {
			wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'draft' ) );
			$log[] = "Stara stranica /$old/ prebačena u nacrt (#{$p->ID})";
		}
	}

	$home    = cc_live_page( 'pocetna', 'Početna', '', '', array(), 0, $log );
	cc_live_page( 'o-nama', 'O nama', $about, 'page-templates/o-nama.php', array(), 1, $log );
	$blog    = cc_live_page( 'aktuelnosti', 'Aktuelnosti', '', '', array(), 3, $log );
	cc_live_page( 'cesta-pitanja', 'Česta pitanja', cc_live_links( $faq_html ), 'page-templates/cesta-pitanja.php', array(), 4, $log );
	cc_live_page( 'kontakt', 'Kontakt', null, 'page-templates/kontakt.php', array(), 5, $log );
	cc_live_page( 'dostava', 'Naručivanje, plaćanje i dostava', cc_live_links( $dostava ), '', array( '_cc_hero' => 'hero-blog.jpg', '_cc_cta' => '1' ), 6, $log );
	cc_live_page( 'lista-zelja', 'Lista želja', '', 'page-templates/lista-zelja.php', array(), 7, $log );
	$uslovi  = cc_live_page( 'uslovi', 'Uslovi korišćenja i prodaje', cc_live_links( (string) file_get_contents( "$dir/uslovi.html" ) ), '', array( '_cc_hero' => 'hero-faq.jpg' ), 8, $log );
	$privacy = cc_live_page( 'privatnost', 'Pravila privatnosti', cc_live_links( (string) file_get_contents( "$dir/privatnost.html" ) ), '', array( '_cc_hero' => 'hero-onama.jpg' ), 9, $log );
	cc_live_page( 'mapa-sajta', 'Mapa sajta', null, 'page-templates/mapa-sajta.php', array(), 10, $log );

	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
	}
	if ( $blog ) {
		update_option( 'page_for_posts', $blog );
	}
	if ( $privacy ) {
		update_option( 'wp_page_for_privacy_policy', $privacy );
	}
	if ( $uslovi ) {
		update_option( 'woocommerce_terms_page_id', $uslovi );
	}

	// WooCommerce pages: classic shortcodes, Serbian titles and addresses.
	$wc_pages = array(
		'shop'      => array( 'shop', 'Asortiman', null, 2 ),
		'cart'      => array( 'korpa', 'Korpa', '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->', 11 ),
		'checkout'  => array( 'porucivanje', 'Naručivanje', '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->', 12 ),
		'myaccount' => array( 'moj-nalog', 'Moj nalog', '<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->', 13 ),
	);
	foreach ( $wc_pages as $key => $cfg ) {
		$id = (int) get_option( "woocommerce_{$key}_page_id" );
		if ( $id > 0 && get_post( $id ) ) {
			$upd = array( 'ID' => $id, 'post_name' => $cfg[0], 'post_title' => $cfg[1], 'menu_order' => $cfg[3], 'post_status' => 'publish' );
			if ( null !== $cfg[2] ) {
				$upd['post_content'] = $cfg[2];
			}
			wp_update_post( $upd );
			update_post_meta( $id, '_wp_page_template', 'default' );
			$log[] = "WooCommerce stranica $key → /{$cfg[0]}/ (#$id)";
		} else {
			$id = cc_live_page( $cfg[0], $cfg[1], (string) $cfg[2], '', array(), $cfg[3], $log );
			update_option( "woocommerce_{$key}_page_id", $id );
		}
	}

	flush_rewrite_rules( true );
	$log[] = 'Gotovo: ' . current_time( 'mysql' );
	update_option( 'cc_live_setup_log', implode( "\n", $log ), false );
}
