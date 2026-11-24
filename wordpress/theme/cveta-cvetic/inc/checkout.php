<?php
/**
 * Checkout: the same fields as the static site (delivery or pickup, date and time, another recipient,
 * card message), saved on the order and shown in the admin, the e-mails and the thank-you page.
 */
defined( 'ABSPATH' ) || exit;

const CC_TIMES = array( '09–12h', '12–15h', '15–18h', '18–20h', 'Svejedno' );

/** Billing fields: name, phone, e-mail, address and part of town only. */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	$b = $fields['billing'];
	$fields['billing'] = array(
		'billing_first_name' => array_merge( $b['billing_first_name'], array( 'label' => 'Ime i prezime', 'required' => true, 'class' => array( 'field' ), 'priority' => 10, 'autocomplete' => 'name' ) ),
		'billing_phone'      => array_merge( $b['billing_phone'], array( 'label' => 'Telefon', 'required' => true, 'class' => array( 'field' ), 'priority' => 20, 'placeholder' => '06x xxx xxxx' ) ),
		'billing_email'      => array_merge( $b['billing_email'], array( 'label' => 'E-mail', 'required' => true, 'class' => array( 'field' ), 'priority' => 30 ) ),
		'billing_address_1'  => array_merge( $b['billing_address_1'], array( 'label' => 'Adresa dostave', 'required' => false, 'class' => array( 'field' ), 'priority' => 40, 'placeholder' => 'Ulica i broj, sprat, stan' ) ),
		'billing_city'       => array_merge( $b['billing_city'], array( 'label' => 'Deo grada / mesto', 'required' => false, 'class' => array( 'field' ), 'priority' => 50, 'placeholder' => 'npr. Liman, Detelinara, Petrovaradin' ) ),
	);
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = 'Napomena uz porudžbinu';
		$fields['order']['order_comments']['placeholder'] = '';
		$fields['order']['order_comments']['class']       = array( 'field' );
	}
	return $fields;
}, 20 );

// Address fields: no country / postcode validation for this local shop.
add_filter( 'woocommerce_billing_fields', function ( $fields ) {
	foreach ( array( 'billing_country', 'billing_state', 'billing_postcode', 'billing_last_name', 'billing_company', 'billing_address_2' ) as $k ) {
		unset( $fields[ $k ] );
	}
	return $fields;
}, 20 );

// The shop sells only in Serbia: the country field is hidden, so the country is always Serbia.
// Without this WooCommerce refuses guest orders ("…without a billing country").
add_filter( 'woocommerce_checkout_posted_data', function ( $data ) {
	$data['billing_country'] = 'RS';
	if ( array_key_exists( 'shipping_country', $data ) && '' === (string) $data['shipping_country'] ) {
		$data['shipping_country'] = 'RS';
	}
	return $data;
} );
add_filter( 'default_checkout_billing_country', function () {
	return 'RS';
} );
add_action( 'woocommerce_checkout_update_order_review', function () {
	if ( WC()->customer && ! WC()->customer->get_billing_country() ) {
		WC()->customer->set_billing_country( 'RS' );
	}
} );

// Inputs get the theme class.
add_filter( 'woocommerce_form_field_args', function ( $args ) {
	$args['input_class'][] = 'input';
	return $args;
} );

/** Posted custom values (sanitized). */
function cc_checkout_posted() {
	$p = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce verifies the checkout nonce.
	$v = array(
		'delivery' => ( $p['cc_delivery'] ?? '' ) === 'preuzimanje' ? 'preuzimanje' : 'dostava',
		'other'    => ! empty( $p['cc_other'] ),
		'rname'    => sanitize_text_field( $p['cc_rname'] ?? '' ),
		'rphone'   => sanitize_text_field( $p['cc_rphone'] ?? '' ),
		'date'     => sanitize_text_field( $p['cc_date'] ?? '' ),
		'time'     => sanitize_text_field( $p['cc_time'] ?? '' ),
		'card'     => sanitize_textarea_field( mb_substr( (string) ( $p['cc_card'] ?? '' ), 0, 300 ) ),
	);
	if ( ! in_array( $v['time'], CC_TIMES, true ) ) {
		$v['time'] = 'Svejedno';
	}
	return $v;
}

add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	$v = cc_checkout_posted();
	if ( 'dostava' === $v['delivery'] && '' === trim( (string) ( $data['billing_address_1'] ?? '' ) ) ) {
		$errors->add( 'cc_address', '<strong>Adresa dostave</strong> je obavezna.' );
	}
	$today = wp_date( 'Y-m-d' );
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v['date'] ) || $v['date'] < $today ) {
		$errors->add( 'cc_date', 'Izaberite <strong>datum</strong> (danas ili kasnije).' );
	}
	if ( ! empty( $data['billing_phone'] ) && ! preg_match( '/^[0-9+ \/()-]{6,}$/', $data['billing_phone'] ) ) {
		$errors->add( 'cc_phone', '<strong>Telefon</strong> nije ispravan.' );
	}
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order', function ( WC_Order $order ) {
	$v = cc_checkout_posted();
	$order->update_meta_data( '_cc_delivery', $v['delivery'] );
	$order->update_meta_data( '_cc_date', $v['date'] );
	$order->update_meta_data( '_cc_time', $v['time'] );
	$order->update_meta_data( '_cc_card', $v['card'] );
	if ( 'dostava' === $v['delivery'] && $v['other'] ) {
		$order->update_meta_data( '_cc_rname', $v['rname'] );
		$order->update_meta_data( '_cc_rphone', $v['rphone'] );
	}
	if ( 'preuzimanje' === $v['delivery'] ) {
		$order->set_billing_address_1( '' );
		$order->set_billing_city( '' );
	}
	$order->set_billing_country( 'RS' );
} );

/** Rows describing delivery for an order: [label => value]. */
function cc_order_delivery_rows( WC_Order $order ) {
	$method = $order->get_meta( '_cc_delivery' );
	if ( ! $method ) {
		return array();
	}
	$date = $order->get_meta( '_cc_date' );
	$rows = array(
		'Način' => 'preuzimanje' === $method ? 'Preuzimanje u cvećari' : 'Dostava na adresu',
	);
	if ( 'dostava' === $method ) {
		$zone = $order->get_meta( '_cc_zone' );
		if ( $zone && function_exists( 'cc_zones' ) && isset( cc_zones()[ $zone ] ) ) {
			$rows['Zona'] = cc_zones()[ $zone ]['label'];
		}
		$rows['Adresa'] = trim( $order->get_billing_address_1() . ( $order->get_billing_city() ? ', ' . $order->get_billing_city() : '' ) );
	}
	$rows['Datum'] = $date ? wp_date( 'j.n.Y.', strtotime( $date . ' 12:00' ) ) : '';
	$rows[ 'preuzimanje' === $method ? 'Vreme preuzimanja' : 'Vreme dostave' ] = $order->get_meta( '_cc_time' );
	if ( $order->get_meta( '_cc_rname' ) || $order->get_meta( '_cc_rphone' ) ) {
		$rows['Primalac'] = trim( $order->get_meta( '_cc_rname' ) . ' ' . $order->get_meta( '_cc_rphone' ) );
	}
	if ( $order->get_meta( '_cc_card' ) ) {
		$rows['Poruka na čestitki'] = $order->get_meta( '_cc_card' );
	}
	return array_filter( $rows );
}

function cc_order_delivery_html( WC_Order $order, $plain = false ) {
	$rows = cc_order_delivery_rows( $order );
	if ( ! $rows ) {
		return '';
	}
	if ( $plain ) {
		$out = "\nDOSTAVA\n";
		foreach ( $rows as $k => $v ) {
			$out .= $k . ': ' . $v . "\n";
		}
		return $out;
	}
	$out = '<h3>Dostava</h3><table class="cc-delivery td" cellspacing="0" cellpadding="8" style="width:100%;margin-bottom:20px">';
	foreach ( $rows as $k => $v ) {
		$out .= '<tr><th style="text-align:left;width:38%">' . esc_html( $k ) . '</th><td style="text-align:left">' . nl2br( esc_html( $v ) ) . '</td></tr>';
	}
	return $out . '</table>';
}

// Admin order screen.
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	echo '<div style="clear:both;padding-top:8px">' . cc_order_delivery_html( $order ) . '</div>'; // phpcs:ignore
} );

// E-mails to the shop and the customer.
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text ) {
	echo cc_order_delivery_html( $order, $plain_text ); // phpcs:ignore
}, 10, 3 );

// The terms page content is not expanded inside the checkout.
remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );

// Button text.
add_filter( 'woocommerce_order_button_text', fn() => 'Potvrdi porudžbinu' );
