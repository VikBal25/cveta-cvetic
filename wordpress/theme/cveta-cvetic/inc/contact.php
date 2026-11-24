<?php
/**
 * Contact form: messages are stored in the admin ("Poruke") and sent by e-mail to the shop.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'cc_message', array(
		'labels'          => array(
			'name'          => 'Poruke',
			'singular_name' => 'Poruka',
			'menu_name'     => 'Poruke sa sajta',
			'all_items'     => 'Sve poruke',
			'edit_item'     => 'Poruka',
		),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 56,
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

add_action( 'admin_post_nopriv_cc_contact', 'cc_handle_contact' );
add_action( 'admin_post_cc_contact', 'cc_handle_contact' );

function cc_handle_contact() {
	$back = wp_get_referer() ?: cc_page_url( 'kontakt' );
	$back = remove_query_arg( array( 'poslato', 'greska' ), $back );
	$p    = wp_unslash( $_POST );

	if ( ! isset( $p['cc_nonce'] ) || ! wp_verify_nonce( $p['cc_nonce'], 'cc_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'greska', 'sesija', $back ) . '#forma' );
		exit;
	}
	if ( ! empty( $p['website'] ) ) { // honeypot
		wp_safe_redirect( add_query_arg( 'poslato', '1', $back ) . '#forma' );
		exit;
	}
	// 10 messages per 10 minutes per IP.
	$key  = 'cc_contact_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );
	$hits = (int) get_transient( $key );
	if ( $hits >= 10 ) {
		wp_safe_redirect( add_query_arg( 'greska', 'limit', $back ) . '#forma' );
		exit;
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$name    = sanitize_text_field( mb_substr( (string) ( $p['name'] ?? '' ), 0, 120 ) );
	$email   = sanitize_email( $p['email'] ?? '' );
	$message = sanitize_textarea_field( mb_substr( (string) ( $p['message'] ?? '' ), 0, 2000 ) );
	if ( ! $name || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( add_query_arg( 'greska', 'polja', $back ) . '#forma' );
		exit;
	}

	wp_insert_post( array(
		'post_type'    => 'cc_message',
		'post_status'  => 'private',
		'post_title'   => $name . ' – ' . $email,
		'post_content' => $message,
	) );
	wp_mail(
		cc_shop( 'email' ),
		'Nova poruka sa sajta – ' . $name,
		"Ime: $name\nE-mail: $email\n\n$message",
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);
	wp_safe_redirect( add_query_arg( 'poslato', '1', $back ) . '#forma' );
	exit;
}
