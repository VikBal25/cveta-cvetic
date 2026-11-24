<?php
/**
 * Template Name: Mapa sajta
 */
defined( 'ABSPATH' ) || exit;
get_header();
echo cc_photo_hero( 'Mapa sajta', 'kontakt-bg.jpg' ); // phpcs:ignore
$li = fn( $url, $label ) => '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
?>
<section class="section" style="padding-top:0"><div class="container"><div class="card prose" style="max-width:none">
	<h2>Stranice</h2><ul>
	<?php
	// phpcs:disable
	echo $li( home_url( '/' ), 'Početna' );
	foreach ( get_pages( array( 'sort_column' => 'menu_order,post_title' ) ) as $pg ) {
		if ( in_array( (int) $pg->ID, array( (int) get_option( 'page_on_front' ), (int) wc_get_page_id( 'checkout' ), (int) wc_get_page_id( 'myaccount' ) ), true ) ) {
			continue;
		}
		echo $li( get_permalink( $pg ), $pg->post_title );
	}
	?>
	</ul>
	<h2>Kategorije</h2><ul>
	<?php
	foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) ) as $t ) {
		echo '<li><a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a> (' . (int) $t->count . ')</li>';
	}
	?>
	</ul>
	<h2>Proizvodi</h2><ul>
	<?php foreach ( wc_get_products( array( 'limit' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) { echo $li( $p->get_permalink(), $p->get_name() ); } ?>
	</ul>
	<h2>Aktuelnosti</h2><ul>
	<?php foreach ( get_posts( array( 'numberposts' => -1 ) ) as $post_item ) { echo $li( get_permalink( $post_item ), get_the_title( $post_item ) ); } // phpcs:enable ?>
	</ul>
</div></div></section>
<?php
get_footer();
