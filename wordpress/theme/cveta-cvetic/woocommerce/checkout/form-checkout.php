<?php
/**
 * Naručivanje: 1. Vaši podaci, 2. Dostava (adresa ili preuzimanje, datum, vreme, primalac, čestitka), 3. Plaćanje.
 * Order summary + confirm button on the right.
 *
 * @version 9.4.0
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

$fields = $checkout->get_checkout_fields();
$field  = function ( $group, $key ) use ( $fields, $checkout ) {
	if ( isset( $fields[ $group ][ $key ] ) ) {
		woocommerce_form_field( $key, $fields[ $group ][ $key ], $checkout->get_value( $key ) );
	}
};
$today = wp_date( 'Y-m-d' );
// phpcs:disable WordPress.Security.NonceVerification
$posted = array(
	'delivery' => ( $_POST['cc_delivery'] ?? '' ) === 'preuzimanje' ? 'preuzimanje' : 'dostava',
	'date'     => sanitize_text_field( wp_unslash( $_POST['cc_date'] ?? $today ) ),
	'zone'     => cc_delivery_choice()['zone'],
);
// phpcs:enable
?>
<div class="steps"><span>1. Korpa</span><span class="on">2. Podaci i dostava</span><span>3. Potvrda</span></div>
<form name="checkout" method="post" class="checkout woocommerce-checkout cart-layout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" novalidate>
	<div>
		<div class="card checkout-block" id="customer_details">
			<h3><span class="num">1</span>Vaši podaci</h3>
			<div class="woocommerce-billing-fields">
				<div class="row-2"><?php $field( 'billing', 'billing_first_name' ); ?><?php $field( 'billing', 'billing_phone' ); ?></div>
				<?php $field( 'billing', 'billing_email' ); ?>
			</div>
		</div>

		<div class="card checkout-block">
			<h3><span class="num">2</span>Dostava</h3>
			<label class="radio-card"><input type="radio" name="cc_delivery" value="dostava" <?php checked( $posted['delivery'], 'dostava' ); ?>><span><strong>Dostava na adresu</strong><small>Novi Sad 500 RSD, besplatno od 3.000 RSD. Petrovaradin i S. Kamenica 700 RSD, besplatno od 3.100 RSD.</small></span></label>
			<label class="radio-card"><input type="radio" name="cc_delivery" value="preuzimanje" <?php checked( $posted['delivery'], 'preuzimanje' ); ?>><span><strong>Preuzimanje u cvećari</strong><small><?php echo esc_html( cc_shop( 'address' ) ); ?></small></span></label>
			<div data-delivery-only style="margin-top:1rem">
				<p class="zone-title">Gde dostavljamo?</p>
				<div class="zone-list">
					<?php foreach ( cc_zones() as $zk => $z ) : ?>
						<label class="radio-card zone-card"><input type="radio" name="cc_zone" value="<?php echo esc_attr( $zk ); ?>" <?php checked( $posted['zone'], $zk ); ?>><span><strong><?php echo esc_html( $z['label'] ); ?></strong><small><?php echo esc_html( $z['hint'] ); ?></small></span><em class="zone-price"><?php echo null === $z['price'] ? 'po dogovoru' : esc_html( cc_money( $z['price'] ) ) . '<br><b>besplatno od ' . esc_html( number_format( $z['free'], 0, ',', '.' ) ) . '</b>'; // phpcs:ignore ?></em></label>
					<?php endforeach; ?>
				</div>
				<?php $field( 'billing', 'billing_address_1' ); ?>
				<?php $field( 'billing', 'billing_city' ); ?>
				<label class="check" style="display:block;margin-bottom:1rem"><input type="checkbox" name="cc_other" value="1" data-other-recipient> Cveće šaljem drugoj osobi</label>
				<div class="row-2" data-recipient hidden>
					<p class="form-row field"><label for="cc_rname">Ime primaoca</label><input class="input" id="cc_rname" name="cc_rname" maxlength="120"></p>
					<p class="form-row field"><label for="cc_rphone">Telefon primaoca</label><input class="input" id="cc_rphone" name="cc_rphone" type="tel" maxlength="40"></p>
				</div>
			</div>
			<div class="row-2">
				<p class="form-row field validate-required"><label for="cc_date">Datum <abbr class="required" title="obavezno">*</abbr></label><input class="input" id="cc_date" name="cc_date" type="date" min="<?php echo esc_attr( $today ); ?>" value="<?php echo esc_attr( $posted['date'] ); ?>" required></p>
				<p class="form-row field"><label for="cc_time" data-time-label>Vreme dostave</label>
					<select class="input" id="cc_time" name="cc_time"><?php foreach ( CC_TIMES as $t ) { echo '<option>' . esc_html( $t ) . '</option>'; } // phpcs:ignore ?></select></p>
			</div>
			<p class="form-row field"><label for="cc_card">Poruka na čestitki</label><textarea class="input" id="cc_card" name="cc_card" maxlength="300" placeholder="Npr. Srećan rođendan! Volim te."></textarea></p>
			<?php $field( 'order', 'order_comments' ); ?>
		</div>

		<div class="card checkout-block">
			<h3><span class="num">3</span>Plaćanje</h3>
			<?php woocommerce_checkout_payment(); ?>
		</div>
	</div>

	<aside class="card summary" id="order_review">
		<h3>Vaša porudžbina</h3>
		<?php woocommerce_order_review(); ?>
		<div class="place-order">
			<?php wc_get_template( 'checkout/terms.php' ); ?>
			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
			<button type="submit" class="btn btn-primary btn-block" name="woocommerce_checkout_place_order" id="place_order" value="1" style="margin-top:1rem"><?php echo cc_icon( 'check' ); // phpcs:ignore ?>Potvrdi porudžbinu</button>
			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>
		<p class="form-note" style="margin-top:1rem">Nakon slanja Vas kontaktiramo radi potvrde. Cene su sa PDV-om.</p>
	</aside>
</form>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
