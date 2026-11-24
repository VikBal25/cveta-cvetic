<?php
/**
 * Empty cart.
 *
 * @version 7.0.1
 */
defined( 'ABSPATH' ) || exit;
do_action( 'woocommerce_cart_is_empty' );
?>
<div class="empty card">
	<h3>Vaša korpa je prazna</h3>
	<p>Izaberite buket koji govori više od reči.</p>
	<a class="btn btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Pogledajte asortiman</a>
</div>
