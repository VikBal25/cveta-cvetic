<?php
/**
 * Serbian (Latin) wording for the WooCommerce texts that still show on the public site
 * (notices, cart totals, checkout errors). The admin keeps its own language.
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'gettext_woocommerce', function ( $translation, $text ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $translation;
	}
	static $map = array(
		'Subtotal'                       => 'Međuzbir',
		'Total'                          => 'Ukupno',
		'Product'                        => 'Proizvod',
		'Price'                          => 'Cena',
		'Quantity'                       => 'Količina',
		'Update cart'                    => 'Ažuriraj korpu',
		'Cart updated.'                  => 'Korpa je ažurirana.',
		'Proceed to checkout'            => 'Nastavi na naručivanje',
		'Your cart is currently empty.'  => 'Vaša korpa je prazna.',
		'Return to shop'                 => 'Pogledajte asortiman',
		'Undo?'                          => 'Vrati?',
		'%s removed.'                    => '%s je uklonjen iz korpe.',
		'Place order'                    => 'Potvrdi porudžbinu',
		'Billing details'                => 'Vaši podaci',
		'Your order'                     => 'Vaša porudžbina',
		'Order notes'                    => 'Napomena uz porudžbinu',
		'(optional)'                     => '(nije obavezno)',
		'optional'                       => 'nije obavezno',
		'required'                       => 'obavezno',
		'%s is a required field.'        => '%s – obavezno polje.',
		'Invalid billing email address'  => 'E-mail adresa nije ispravna.',
		'%s is not a valid phone number.' => '%s nije ispravan broj telefona.',
		'Please read and accept the terms and conditions to proceed with your order.' => 'Potrebna je Vaša saglasnost sa uslovima korišćenja i prodaje.',
		'Sorry, your session has expired.' => 'Sesija je istekla.',
		'Return to homepage'             => 'Početna',
		'Thank you. Your order has been received.' => 'Hvala Vam! Vaša porudžbina je primljena.',
		'Order number:'                  => 'Broj porudžbine:',
		'Date:'                          => 'Datum:',
		'Email:'                         => 'E-mail:',
		'Total:'                         => 'Ukupno:',
		'Payment method:'                => 'Način plaćanja:',
		'Order details'                  => 'Detalji porudžbine',
		'Our bank details'               => 'Podaci za uplatu',
		'Billing address'                => 'Podaci kupca',
		'No products in the cart.'       => 'Korpa je prazna.',
		'View cart'                      => 'Pogledaj korpu',
		'Add to cart'                    => 'Dodaj u korpu',
		'Read more'                      => 'Pogledaj',
		'Sale!'                          => 'Akcija!',
		'Search results: &ldquo;%s&rdquo;' => 'Rezultati pretrage: „%s“',
		'Showing the single result'      => 'Prikaz jednog rezultata',
		'Showing all %d result'          => 'Prikaz svih %d rezultata',
		'Showing all %d results'         => 'Prikaz svih %d rezultata',
		'No products were found matching your selection.' => 'Nema proizvoda za izabrani filter.',
		'Shipping'                       => 'Dostava',
		'Free!'                          => 'Besplatno',
		'Coupon:'                        => 'Kupon:',
		'Have a coupon?'                 => 'Imate kupon?',
		'There are no payment methods available. This may be an error on our side. Please contact us if you need any help placing your order.' => 'Trenutno nema dostupnih načina plaćanja. Pozovite nas da završimo porudžbinu.',
		'Please enter a valid email address.' => 'Unesite ispravnu e-mail adresu.',
		'Error processing checkout. Please try again.' => 'Greška pri slanju porudžbine. Pokušajte ponovo.',
		'Your order was cancelled.'      => 'Porudžbina je otkazana.',
		'Invalid payment method.'        => 'Izaberite način plaćanja.',
		'terms and conditions'           => 'uslove korišćenja i prodaje',
		'privacy policy'                 => 'pravila privatnosti',
		'Order received'                 => 'Hvala na porudžbini',
	);
	return $map[ $text ] ?? $translation;
}, 10, 2 );

add_filter( 'ngettext_woocommerce', function ( $translation, $single, $plural, $number ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $translation;
	}
	if ( 'Showing %1$d&ndash;%2$d of %3$d result' === $single ) {
		return 'Prikaz %1$d–%2$d od %3$d rezultata';
	}
	if ( 'Showing all %d result' === $single ) {
		return 1 === (int) $number ? 'Prikaz jednog rezultata' : 'Prikaz svih %d rezultata';
	}
	return $translation;
}, 10, 4 );

add_filter( 'ngettext_with_context_woocommerce', function ( $translation, $single, $plural, $number, $context ) {
	if ( 'Showing %1$d&ndash;%2$d of %3$d result' === $single ) {
		return 'Prikaz %1$d–%2$d od %3$d rezultata';
	}
	return $translation;
}, 10, 5 );

// Checkout validation: "Billing Ime i prezime" → "Ime i prezime" (the shop has only one address).
add_filter( 'gettext_with_context_woocommerce', function ( $translation, $text, $context ) {
	if ( 'Billing %s' === $text || 'Shipping %s' === $text ) {
		return '%s';
	}
	return $translation;
}, 10, 3 );

// Labels are rendered as "Ime i prezime *" (no extra "(optional)" text).
add_filter( 'woocommerce_form_field', function ( $field ) {
	return preg_replace( '#\s*<span class="optional">.*?</span>#', '', $field );
} );
