<?php
/**
 * Order e-mails in the shop style: small logo, violet title bar, compact tables,
 * shop contacts in the footer. Colours and footer are fixed here, so the old
 * WooCommerce e-mail settings (big favicon logo, "Built with…" footer) no longer apply.
 */
defined( 'ABSPATH' ) || exit;

const CC_EMAIL_COLORS = array(
	'woocommerce_email_base_color'            => '#7b2cbf',
	'woocommerce_email_background_color'      => '#f8eef4',
	'woocommerce_email_body_background_color' => '#ffffff',
	'woocommerce_email_text_color'            => '#4d3a5e',
	'woocommerce_email_footer_text_color'     => '#8a7896',
);
foreach ( CC_EMAIL_COLORS as $cc_opt => $cc_val ) {
	add_filter( 'pre_option_' . $cc_opt, fn() => $cc_val );
}
add_filter( 'pre_option_woocommerce_email_header_image', fn() => '' );

// Footer: shop name, address, phone, website (replaces the old agency credit).
add_filter( 'woocommerce_email_footer_text', function () {
	$site = home_url( '/' );
	return '<strong style="color:#551b88">' . esc_html( cc_shop( 'name' ) ) . '</strong><br>'
		. esc_html( cc_shop( 'address' ) ) . '<br>'
		. '<a href="' . esc_attr( cc_shop( 'phoneHref' ) ) . '" style="color:#7b2cbf;text-decoration:none">' . esc_html( cc_shop( 'phone' ) ) . '</a>'
		. ' · <a href="' . esc_url( $site ) . '" style="color:#7b2cbf;text-decoration:none">' . esc_html( wp_parse_url( $site, PHP_URL_HOST ) ) . '</a>';
}, 20 );

add_filter( 'woocommerce_email_styles', function ( $css ) {
	return $css . '
	body, #outer_wrapper, #wrapper, #inner_wrapper { background-color:#f8eef4 !important; }
	#wrapper { padding:24px 12px !important; margin:0 auto !important; max-width:600px !important; box-sizing:border-box !important; }
	#cc_email_logo { padding:6px 0 16px !important; text-align:center !important; background:transparent !important; }
	#template_container { background:#ffffff !important; border:0 !important; border-radius:16px !important; box-shadow:0 6px 24px rgba(106,34,168,.08) !important; overflow:hidden !important; }
	#template_header { background:#7b2cbf !important; color:#ffffff !important; border-radius:16px 16px 0 0 !important; border:0 !important; }
	#header_wrapper { padding:18px 26px !important; display:block !important; }
	#template_header h1, #header_wrapper h1 { color:#ffffff !important; font-size:21px !important; line-height:1.3 !important; font-weight:700 !important; margin:0 !important; text-align:left !important; text-shadow:none !important; letter-spacing:0 !important; }
	#body_content { background:#ffffff !important; border-radius:0 0 16px 16px !important; }
	#body_content table td { padding-left:0; padding-right:0; }
	#body_content_inner_cell { padding:22px 26px 8px !important; }
	#body_content_inner { color:#4d3a5e !important; font-size:14px !important; line-height:1.55 !important; }
	#body_content_inner p { margin:0 0 12px !important; }
	#body_content_inner h2, #body_content_inner h3, .email-order-detail-heading { color:#551b88 !important; font-size:16px !important; line-height:1.35 !important; margin:20px 0 8px !important; font-weight:700 !important; }
	#body_content_inner h2 span, .email-order-detail-heading span { color:#8a7896 !important; font-size:12px !important; font-weight:400 !important; }
	.td, #body_content_inner table.td th, #body_content_inner table.td td { border-color:#f0e2f2 !important; color:#4d3a5e !important; padding:8px 10px !important; font-size:14px !important; }
	table.td { border:1px solid #f0e2f2 !important; border-radius:10px !important; border-collapse:separate !important; overflow:hidden !important; }
	table.td thead th { background:#faf4fb !important; color:#551b88 !important; font-size:13px !important; }
	table.td tfoot th, table.td tfoot td { font-weight:600 !important; }
	.order-totals-total th, .order-totals-total td { color:#551b88 !important; font-size:16px !important; }
	.cc-delivery th, .cc-delivery td { border-bottom:1px solid #f0e2f2 !important; padding:8px 10px !important; font-size:14px !important; vertical-align:top !important; }
	.cc-delivery th { color:#8a7896 !important; font-weight:600 !important; }
	.address { border:1px solid #f0e2f2 !important; border-radius:10px !important; padding:10px 12px !important; color:#4d3a5e !important; font-style:normal !important; }
	a, .link { color:#7b2cbf !important; }
	#template_footer #credit, #credit, #credit p { color:#8a7896 !important; font-size:12px !important; line-height:1.6 !important; text-align:center !important; padding:14px 16px 0 !important; border:0 !important; }
	#template_footer, #template_footer td { border:0 !important; background:transparent !important; }
	.order_item td, .order_item th { padding-top:10px !important; padding-bottom:10px !important; vertical-align:middle !important; }
	.order_item img { border-radius:8px !important; }
	.order-item-data td, .order-item-data th { padding:0 10px 0 0 !important; border:0 !important; }
	.email-order-item-thumbnail { width:56px !important; }
	@media screen and (max-width:600px) {
		#wrapper { padding:12px 6px !important; }
		#header_wrapper { padding:14px 16px !important; }
		#template_header h1 { font-size:18px !important; }
		#body_content_inner_cell { padding:16px 12px 6px !important; }
		.td, #body_content_inner table.td th, #body_content_inner table.td td { padding:7px 6px !important; font-size:13px !important; }
		.email-order-item-thumbnail { display:none !important; }
	}';
} );

// No "Get the WooCommerce app" promo in the shop's new-order e-mail.
add_action( 'woocommerce_email', function ( $mailer ) {
	if ( isset( $mailer->emails['WC_Email_New_Order'] ) ) {
		remove_action( 'woocommerce_email_footer', array( $mailer->emails['WC_Email_New_Order'], 'mobile_messaging' ), 9 );
	}
}, 20 );

// Texts like "contact us at {store_email}" show the shop's real inbox, not the technical sender address.
add_filter( 'woocommerce_email_format_string', function ( $string, $email ) {
	$from = is_object( $email ) && method_exists( $email, 'get_from_address' ) ? $email->get_from_address() : '';
	return $from ? str_replace( $from, cc_shop( 'email' ), $string ) : $string;
}, 10, 2 );
