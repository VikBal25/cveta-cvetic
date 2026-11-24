<?php
/**
 * Regular pages (Uslovi, Privatnost, Naručivanje-plaćanje-dostava…) and the WooCommerce
 * Korpa / Naručivanje pages: photo title + page content.
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	if ( function_exists( 'is_cart' ) && is_cart() ) :
		echo cc_photo_hero( 'Korpa', 'hero-faq.jpg', null, 'Pregledajte izabrane proizvode i nastavite na naručivanje.' ); // phpcs:ignore
		echo '<section class="section after-hero" style="padding-top:0"><div class="container">';
		the_content();
		echo '</div></section>';
	elseif ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) :
		echo cc_photo_hero( 'Naručivanje', 'hero-faq.jpg', 'Završi kupovinu', 'Još samo nekoliko podataka i Vaše cveće je na putu.' ); // phpcs:ignore
		echo '<section class="section" style="padding-top:0"><div class="container">';
		the_content();
		echo '</div></section>';
	elseif ( function_exists( 'is_checkout' ) && is_order_received_page() ) :
		the_content(); // thankyou.php renders its own hero card
	else :
		$img = get_post_meta( get_the_ID(), '_cc_hero', true ) ?: 'hero-faq.jpg';
		echo cc_photo_hero( get_the_title(), $img, null, (string) get_post_meta( get_the_ID(), '_cc_lead', true ) ); // phpcs:ignore
		?>
		<section class="section after-hero" style="padding-top:0"><div class="container"><div class="card prose"><?php the_content(); ?></div></div></section>
		<?php
		if ( get_post_meta( get_the_ID(), '_cc_cta', true ) ) {
			echo cc_cta(); // phpcs:ignore
		}
	endif;
endwhile;

get_footer();
