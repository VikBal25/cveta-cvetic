<?php
/**
 * Template Name: Česta pitanja
 *
 * Questions are written in the page editor as "Heading" (pitanje) followed by paragraphs (odgovor).
 */
defined( 'ABSPATH' ) || exit;
get_header();

the_post();
$html  = apply_filters( 'the_content', get_the_content() );
$parts = preg_split( '#<h[23][^>]*>(.*?)</h[23]>#s', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
$items = array();
for ( $i = 1; $i < count( $parts ); $i += 2 ) {
	$items[] = array( wp_strip_all_tags( $parts[ $i ] ), trim( $parts[ $i + 1 ] ?? '' ) );
}
echo cc_photo_hero( get_the_title(), 'hero-faq.jpg', null, 'Sve što treba da znate o naručivanju, dostavi i plaćanju.' ); // phpcs:ignore
?>
<section class="section after-hero" style="padding-top:0"><div class="container"><div class="faq">
	<?php foreach ( $items as $i => $item ) : ?>
		<div class="faq-item glass<?php echo 0 === $i ? ' is-open' : ''; ?>">
			<button class="faq-q" type="button" aria-expanded="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-controls="faq-<?php echo (int) $i; ?>"><?php echo esc_html( $item[0] ); ?><span class="pm" aria-hidden="true"></span></button>
			<div class="answer" id="faq-<?php echo (int) $i; ?>" role="region"><div><?php echo wp_kses_post( $item[1] ); ?></div></div>
		</div>
	<?php endforeach; ?>
</div></div></section>
<?php
echo cc_cta(); // phpcs:ignore
get_footer();
