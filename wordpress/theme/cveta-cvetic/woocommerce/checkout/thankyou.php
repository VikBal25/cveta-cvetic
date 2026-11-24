<?php
/**
 * Hvala na porudžbini.
 *
 * @version 8.1.0
 * @var WC_Order|false $order
 */
defined( 'ABSPATH' ) || exit;
?>
<section class="photo-hero card-hero"><div class="bg" style="background-image:url(<?php echo cc_img( 'design/hero-onama.jpg' ); ?>)" aria-hidden="true"></div><div class="container">
	<div class="card prose glass cc-thanks" style="text-align:center">
	<?php if ( $order && ! $order->has_status( 'failed' ) ) : ?>
		<span class="eyebrow">Porudžbina je primljena</span>
		<h1>Hvala Vam! <span data-order-no>#<?php echo esc_html( $order->get_order_number() ); ?></span></h1>
		<p class="lead">Vaša porudžbina je uspešno poslata. Uskoro ćemo Vas kontaktirati telefonom ili e-mailom radi potvrde.</p>
		<div class="cc-thanks-summary" style="text-align:left">
			<div class="sum-row"><span>Ukupno</span><strong><?php echo esc_html( cc_money( $order->get_total() ) ); ?></strong></div>
			<div class="sum-row"><span>Plaćanje</span><strong><?php echo esc_html( $order->get_payment_method_title() ); ?></strong></div>
			<?php foreach ( cc_order_delivery_rows( $order ) as $k => $v ) : ?>
				<div class="sum-row"><span><?php echo esc_html( $k ); ?></span><strong><?php echo esc_html( $v ); ?></strong></div>
			<?php endforeach; ?>
		</div>
		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<p>Za hitne izmene pozovite nas na <a href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo esc_html( cc_shop( 'phone' ) ); ?></a>.</p>
	<?php elseif ( $order ) : ?>
		<span class="eyebrow">Plaćanje nije uspelo</span>
		<h1>Porudžbina nije završena</h1>
		<p class="lead">Pokušajte ponovo ili nas pozovite na <a href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo esc_html( cc_shop( 'phone' ) ); ?></a>.</p>
		<p><a class="btn btn-primary" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">Pokušaj ponovo</a></p>
	<?php else : ?>
		<span class="eyebrow">Porudžbina je primljena</span>
		<h1>Hvala Vam!</h1>
		<p class="lead">Uskoro ćemo Vas kontaktirati radi potvrde.</p>
	<?php endif; ?>
		<p><a class="btn btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Nastavi kupovinu</a> <a class="btn btn-outline" href="<?php echo esc_url( home_url( '/' ) ); ?>">Početna</a></p>
	</div>
</div></section>
