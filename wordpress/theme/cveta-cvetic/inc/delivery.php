<?php
/**
 * Delivery prices by zone. Change prices and free-delivery limits here.
 *   Novi Sad (left bank of the Danube): 500 RSD, free from 3.000 RSD.
 *   Petrovaradin and Sremska Kamenica (right bank): 700 RSD, free from 3.100 RSD.
 *   Surroundings: price agreed by phone (not added to the order).
 * Pickup in the shop is always free.
 */
defined( 'ABSPATH' ) || exit;

function cc_zones() {
	return array(
		'leva'    => array( 'label' => 'Novi Sad – leva obala Dunava', 'hint' => 'Centar, Liman, Grbavica, Detelinara, Novo naselje, Klisa…', 'price' => 500, 'free' => 3000 ),
		'desna'   => array( 'label' => 'Petrovaradin i Sremska Kamenica', 'hint' => 'Desna obala Dunava', 'price' => 700, 'free' => 3100 ),
		'okolina' => array( 'label' => 'Okolina Novog Sada', 'hint' => 'Veternik, Futog, Kać, Sremski Karlovci… – cenu dogovaramo telefonom', 'price' => null, 'free' => null ),
	);
}

/** The customer's current choice: [ 'delivery' => dostava|preuzimanje, 'zone' => key ]. */
function cc_delivery_choice() {
	// phpcs:disable WordPress.Security.NonceVerification -- read-only; WooCommerce verifies the checkout nonce.
	if ( isset( $_POST['cc_delivery'] ) ) {
		$d = wp_unslash( $_POST );
	} else {
		$d = array(
			'cc_delivery' => WC()->session ? WC()->session->get( 'cc_delivery' ) : '',
			'cc_zone'     => WC()->session ? WC()->session->get( 'cc_zone' ) : '',
		);
	}
	// phpcs:enable
	$zone = sanitize_key( $d['cc_zone'] ?? '' );
	return array(
		'delivery' => ( $d['cc_delivery'] ?? '' ) === 'preuzimanje' ? 'preuzimanje' : 'dostava',
		'zone'     => isset( cc_zones()[ $zone ] ) ? $zone : 'leva',
	);
}

// Remember the choice while WooCommerce refreshes the order summary.
add_action( 'woocommerce_checkout_update_order_review', function ( $post_data ) {
	parse_str( (string) $post_data, $d );
	if ( WC()->session ) {
		WC()->session->set( 'cc_delivery', ( $d['cc_delivery'] ?? '' ) === 'preuzimanje' ? 'preuzimanje' : 'dostava' );
		WC()->session->set( 'cc_zone', sanitize_key( $d['cc_zone'] ?? 'leva' ) );
	}
} );

/** Goods total incl. VAT (after coupons). */
function cc_cart_goods_total() {
	$cart = WC()->cart;
	return $cart ? (float) $cart->get_cart_contents_total() + (float) $cart->get_cart_contents_tax() : 0.0;
}

/**
 * Delivery for the current cart and choice.
 * Returns [ 'amount' => int|null (null = by agreement), 'text' => shown value, 'missing' => RSD to free delivery ].
 */
function cc_delivery_quote() {
	$c = cc_delivery_choice();
	if ( 'preuzimanje' === $c['delivery'] ) {
		return array( 'amount' => 0, 'text' => 'Preuzimanje u radnji', 'missing' => 0 );
	}
	$z = cc_zones()[ $c['zone'] ];
	if ( null === $z['price'] ) {
		return array( 'amount' => null, 'text' => 'Po dogovoru', 'missing' => 0 );
	}
	$goods = cc_cart_goods_total();
	if ( $goods >= $z['free'] ) {
		return array( 'amount' => 0, 'text' => 'Besplatno', 'missing' => 0 );
	}
	return array( 'amount' => $z['price'], 'text' => cc_money( $z['price'] ), 'missing' => (int) ceil( $z['free'] - $goods ) );
}

// The delivery price is added to the order total on the checkout only.
add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) && ! is_checkout() ) {
		return;
	}
	$q = cc_delivery_quote();
	if ( $q['amount'] > 0 ) {
		$cart->add_fee( 'Dostava', $q['amount'], false );
	}
} );

// A zone must be chosen for delivery.
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( 'dostava' === ( $_POST['cc_delivery'] ?? 'dostava' ) && ! isset( cc_zones()[ sanitize_key( wp_unslash( $_POST['cc_zone'] ?? '' ) ) ] ) ) {
		$errors->add( 'cc_zone', 'Izaberite <strong>zonu dostave</strong>.' );
	}
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order', function ( WC_Order $order ) {
	$c = cc_delivery_choice();
	if ( 'dostava' === $c['delivery'] ) {
		$order->update_meta_data( '_cc_zone', $c['zone'] );
	}
} );

/** Short text for product pages and the cart. */
function cc_delivery_terms_short() {
	return 'Novi Sad 500 RSD, besplatno od 3.000 RSD · Petrovaradin i S. Kamenica 700 RSD, besplatno od 3.100 RSD';
}
