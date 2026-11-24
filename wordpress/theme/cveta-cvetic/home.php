<?php
/**
 * Aktuelnosti (blog list).
 */
defined( 'ABSPATH' ) || exit;
get_header();
echo cc_photo_hero( 'Aktuelnosti', 'hero-blog.jpg', null, 'Dostava cveća po delovima Novog Sada, saveti i novosti iz naše cvećare.' ); // phpcs:ignore
?>
<section class="section after-hero" style="padding-top:0"><div class="container">
	<div class="grid grid-3">
		<?php
		while ( have_posts() ) {
			the_post();
			echo cc_post_card( get_post() ); // phpcs:ignore
		}
		?>
	</div>
	<?php
	$links = paginate_links( array( 'type' => 'array', 'prev_next' => false ) );
	if ( $links ) {
		echo '<nav class="pagination" aria-label="Strane">' . implode( '', $links ) . '</nav>'; // phpcs:ignore
	}
	?>
</div></section>
<?php
get_footer();
