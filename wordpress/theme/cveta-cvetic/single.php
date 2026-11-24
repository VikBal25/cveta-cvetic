<?php
/**
 * Članak (blog post).
 */
defined( 'ABSPATH' ) || exit;
get_header();
$blog = get_permalink( get_option( 'page_for_posts' ) );
while ( have_posts() ) :
	the_post();
	?>
	<section class="photo-hero post-hero"><div class="bg" style="background-image:url(<?php echo cc_img( 'design/hero-blog.jpg' ); ?>)" aria-hidden="true"></div><div class="container"><h1><?php the_title(); ?></h1>
		<?php echo cc_crumbs( array( 'Aktuelnosti' => $blog, get_the_title() => '' ) ); // phpcs:ignore ?></div></section>
	<section class="section" style="padding-top:0"><div class="container">
		<article class="card prose"><time><?php echo esc_html( cc_date( get_post() ) ); ?></time><?php the_post_thumbnail( 'large', array( 'alt' => '' ) ); ?><?php the_content(); ?>
			<p style="margin-top:2rem"><a class="btn btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Pogledajte asortiman</a></p></article>
	</div></section>
	<section class="section" style="padding-top:0"><div class="container"><div class="section-head"><h2>Još iz aktuelnosti</h2></div><div class="grid grid-3">
		<?php foreach ( get_posts( array( 'numberposts' => 3, 'exclude' => array( get_the_ID() ) ) ) as $other ) { echo cc_post_card( $other ); } // phpcs:ignore ?>
	</div></div></section>
	<?php
endwhile;
get_footer();
