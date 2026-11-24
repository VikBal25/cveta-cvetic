<?php
/**
 * Fallback list (archives, search outside the shop).
 */
defined( 'ABSPATH' ) || exit;
get_header();
$title = is_search() ? 'Pretraga: „' . get_search_query() . '“' : wp_strip_all_tags( get_the_archive_title() );
echo cc_photo_hero( $title ?: 'Aktuelnosti', 'hero-blog.jpg' ); // phpcs:ignore
?>
<section class="section after-hero" style="padding-top:0"><div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid-3"><?php while ( have_posts() ) { the_post(); echo cc_post_card( get_post() ); } // phpcs:ignore ?></div>
		<?php
		$links = paginate_links( array( 'type' => 'array', 'prev_next' => false ) );
		if ( $links ) {
			echo '<nav class="pagination" aria-label="Strane">' . implode( '', $links ) . '</nav>'; // phpcs:ignore
		}
		?>
	<?php else : ?>
		<div class="empty card"><h3>Nema rezultata</h3><a class="btn btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Asortiman</a></div>
	<?php endif; ?>
</div></section>
<?php
get_footer();
