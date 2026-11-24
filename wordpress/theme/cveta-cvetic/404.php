<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="photo-hero card-hero"><div class="bg" style="background-image:url(<?php echo cc_img( 'design/hero-blog.jpg' ); ?>)" aria-hidden="true"></div><div class="container"><div class="card prose glass" style="text-align:center">
	<span class="eyebrow">Greška 404</span><h1>Stranica nije pronađena</h1><p class="lead">Ova stranica ne postoji ili je premeštena.</p>
	<p><a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Početna</a> <a class="btn btn-outline" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Asortiman</a></p>
</div></div></section>
<?php
get_footer();
