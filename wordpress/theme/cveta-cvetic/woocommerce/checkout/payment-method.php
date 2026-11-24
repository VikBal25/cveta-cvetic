<?php
/**
 * One payment method as a radio card.
 *
 * @version 3.5.0
 */
defined( 'ABSPATH' ) || exit;
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
	<label class="radio-card" for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
		<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>">
		<span><strong><?php echo wp_kses_post( $gateway->get_title() ); ?></strong><?php if ( $gateway->get_description() ) : ?><small><?php echo wp_kses_post( wp_strip_all_tags( $gateway->get_description() ) ); ?></small><?php endif; ?></span>
	</label>
	<?php if ( $gateway->has_fields() && 'cod' !== $gateway->id && 'bacs' !== $gateway->id && 'cheque' !== $gateway->id ) : ?>
		<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>"><?php $gateway->payment_fields(); ?></div>
	<?php endif; ?>
</li>
