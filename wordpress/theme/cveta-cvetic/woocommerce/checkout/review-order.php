<?php
/**
 * Order summary on the checkout (refreshed by WooCommerce via AJAX).
 *
 * @version 5.2.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-checkout-review-order-table">
	<ul class="mini-items">
		<?php
		foreach ( WC()->cart->get_cart() as $key => $item ) :
			$p = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
			if ( ! $p || ! $p->exists() || $item['quantity'] <= 0 ) {
				continue;
			}
			?>
			<li><?php echo $p->get_image( 'woocommerce_gallery_thumbnail', array( 'alt' => '' ) ); // phpcs:ignore ?><span class="n"><?php echo esc_html( cc_short_name( $p->get_name() ) ); ?> <small>× <?php echo (int) $item['quantity']; ?></small></span><strong><?php echo esc_html( cc_money( $item['line_total'] + $item['line_tax'] ) ); ?></strong></li>
		<?php endforeach; ?>
	</ul>
	<?php $cc_q = cc_delivery_quote(); ?>
	<div class="sum-row"><span>Dostava</span><strong data-ship<?php echo ( 0 === $cc_q['amount'] ) ? ' class="is-free"' : ''; ?>><?php echo esc_html( $cc_q['text'] ); ?></strong></div>
	<?php if ( $cc_q['missing'] > 0 ) : ?>
		<p class="free-hint">Još <strong><?php echo esc_html( cc_money( $cc_q['missing'] ) ); ?></strong> do besplatne dostave.</p>
	<?php endif; ?>
	<div class="sum-row total"><span>Ukupno</span><span><?php echo esc_html( cc_money( WC()->cart->get_total( 'edit' ) ) ); ?></span></div>
</div>
