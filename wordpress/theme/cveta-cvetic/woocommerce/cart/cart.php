<?php
/**
 * Korpa: product rows with quantity and remove, order summary.
 * Keeps the WooCommerce form names so the standard cart scripts (AJAX update/remove) work.
 *
 * @version 10.1.0
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
$total = WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax();
?>
<div class="cart-layout">
	<form class="woocommerce-cart-form card cart-items" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>
		<?php
		foreach ( WC()->cart->get_cart() as $key => $item ) :
			$p = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
			if ( ! $p || ! $p->exists() || $item['quantity'] <= 0 ) {
				continue;
			}
			$link = $p->is_visible() ? $p->get_permalink( $item ) : '';
			?>
			<div class="cart-row cart_item woocommerce-cart-form__cart-item">
				<a href="<?php echo esc_url( $link ); ?>"><?php echo $p->get_image( 'woocommerce_gallery_thumbnail', array( 'alt' => '' ) ); // phpcs:ignore ?></a>
				<div class="info"><a class="name" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( cc_short_name( $p->get_name() ) ); ?></a><div class="unit"><?php echo esc_html( cc_money( $p->get_price() ) ); ?> / kom</div></div>
				<div class="qty" data-cart-qty>
					<button type="button" data-step="-1" aria-label="Manje">−</button>
					<input type="number" class="qty-input" name="cart[<?php echo esc_attr( $key ); ?>][qty]" value="<?php echo (int) $item['quantity']; ?>" min="0" max="99" step="1" aria-label="Količina" inputmode="numeric">
					<button type="button" data-step="1" aria-label="Više">+</button>
				</div>
				<div class="line-total"><?php echo esc_html( cc_money( $item['line_total'] + $item['line_tax'] ) ); ?></div>
				<div class="product-remove"><a href="<?php echo esc_url( wc_get_cart_remove_url( $key ) ); ?>" class="remove" aria-label="Ukloni" data-product_id="<?php echo (int) $p->get_id(); ?>"><?php echo cc_icon( 'trash' ); // phpcs:ignore ?></a></div>
			</div>
		<?php endforeach; ?>
		<?php do_action( 'woocommerce_cart_contents' ); ?>
		<button type="submit" class="sr-only" name="update_cart" value="1">Ažuriraj korpu</button>
		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	</form>
	<div class="card summary cart_totals">
		<h3>Pregled porudžbine</h3>
		<div class="sum-row"><span>Međuzbir</span><strong><?php echo esc_html( cc_money( $total ) ); ?></strong></div>
		<?php $cc_goods = cc_cart_goods_total(); ?>
		<div class="sum-row"><span>Dostava (Novi Sad)</span><?php echo $cc_goods >= 3000 ? '<strong style="color:var(--mint-ink)">Besplatno</strong>' : '<strong>' . esc_html( cc_money( 500 ) ) . '</strong>'; // phpcs:ignore ?></div>
		<?php if ( $cc_goods < 3000 ) : ?>
			<p class="free-hint">Još <strong><?php echo esc_html( cc_money( ceil( 3000 - $cc_goods ) ) ); ?></strong> do besplatne dostave u Novom Sadu.</p>
		<?php endif; ?>
		<p class="form-note" style="margin:.25rem 0 0">Petrovaradin i S. Kamenica: 700 RSD, besplatno od 3.100 RSD. Okolina – po dogovoru. Preuzimanje u cvećari je besplatno.</p>
		<div class="sum-row total"><span>Ukupno</span><span><?php echo esc_html( cc_money( WC()->cart->get_total( 'edit' ) ) ); ?></span></div>
		<a class="btn btn-primary btn-block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>" style="margin-top:1rem">Nastavi na naručivanje <?php echo cc_icon( 'arrow' ); // phpcs:ignore ?></a>
		<a class="btn btn-outline btn-block" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" style="margin-top:.6rem">Nastavi kupovinu</a>
		<p class="form-note" style="margin-top:1rem">Cene su sa uračunatim PDV-om. Plaćanje pouzećem, uplatom na račun ili karticom u radnji.</p>
	</div>
</div>
<?php do_action( 'woocommerce_after_cart' ); ?>
