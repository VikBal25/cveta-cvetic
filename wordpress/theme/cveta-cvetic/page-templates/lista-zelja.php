<?php
/**
 * Template Name: Lista želja
 *
 * The list is kept in the visitor's browser; theme.js loads the products from the WooCommerce Store API.
 */
defined( 'ABSPATH' ) || exit;
get_header();
echo cc_photo_hero( 'Lista želja', 'hero-onama.jpg', null, 'Proizvodi koje ste označili srcem.' ); // phpcs:ignore
?>
<section class="section after-hero" style="padding-top:0"><div class="container"><div class="grid" data-wish-grid></div></div></section>
<?php
get_footer();
