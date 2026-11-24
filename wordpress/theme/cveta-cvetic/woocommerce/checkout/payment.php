<?php
/**
 * Payment methods as radio cards (the confirm button lives in the order summary, see form-checkout.php).
 *
 * @version 9.8.0
 */
defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="alert alert-err">Trenutno nema dostupnih načina plaćanja. Pozovite nas na ' . esc_html( cc_shop( 'phone' ) ) . '.</li>';
			}
			?>
		</ul>
	<?php endif; ?>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
