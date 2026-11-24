<?php
/**
 * Početna (mockup 01): hero, category bento, best sellers (featured products), benefits.
 * Best sellers = products marked with the star (Featured) in Proizvodi.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$tile = function ( $slug, $html, $pos, $extra = '' ) {
	$img = array(
		'buketi'          => 'tile-buketi.jpg',
		'vecna-ruza'      => 'tile-vecna-ruza.jpg',
		'poklon-set'      => 'tile-poklon-set.jpg',
		'mini-buketi'     => 'tile-mini-buketi.jpg',
		'saksijsko-cvece' => 'tile-saksijsko.jpg',
		'helijum-baloni'  => 'tile-helijum.jpg',
	)[ $slug ];
	return '<a class="tile ' . $extra . '" href="' . esc_url( cc_cat_url( $slug ) ) . '" style="background-image:url(' . cc_img( 'design/' . $img ) . ');background-position:' . $pos . '"><h3>' . $html . '</h3><span class="go">' . cc_icon( 'chevron' ) . '</span></a>';
};
$featured = wc_get_products( array( 'featured' => true, 'limit' => 8, 'orderby' => 'menu_order', 'order' => 'ASC', 'status' => 'publish' ) );
if ( ! $featured ) {
	$featured = wc_get_products( array( 'limit' => 8, 'orderby' => 'menu_order', 'order' => 'ASC', 'status' => 'publish' ) );
}
?>
<section class="hero">
	<div class="hero-media"><img src="<?php echo cc_img( 'design/home-hero.jpg' ); ?>" alt="Nežan buket roze ruža, lisijantusa i gipsofile" width="770" height="573"></div>
	<?php echo cc_petals(); // phpcs:ignore ?>
	<div class="container"><div class="hero-text">
		<h1>Cveće koje<br>govori više<br>od reči</h1>
		<p class="lead">Vaš omiljeni izbor cvetnih aranžmana u Novom Sadu</p>
		<div class="hero-actions">
			<a class="btn btn-primary btn-hero" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Poruči buket <?php echo cc_icon( 'chevron' ); // phpcs:ignore ?></a>
			<span class="badge-pill">Od 2016</span>
		</div>
	</div></div>
</section>
<section class="section" style="padding-top:0"><div class="container"><div class="bento">
	<?php
	// phpcs:disable
	echo $tile( 'buketi', 'Buketi', 'right bottom', 'tile-big' );
	echo $tile( 'vecna-ruza', 'Večna<br>ruža', 'right center' );
	echo $tile( 'poklon-set', 'Poklon<br>set', 'right center' );
	echo $tile( 'mini-buketi', 'Mini buketi<br>već od<span class="price-big">800 RSD</span>', 'center bottom', 'tile-violet' );
	echo $tile( 'saksijsko-cvece', 'Saksijsko<br>cveće', 'right center' );
	echo $tile( 'helijum-baloni', 'Helijum<br>baloni', 'right center' );
	// phpcs:enable
	?>
</div></div></section>
<section class="section" style="padding-top:.5rem"><div class="container">
	<div class="section-head"><h2>Najprodavanije</h2><a class="link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Pogledaj sve <span class="go-sm"><?php echo cc_icon( 'chevron' ); // phpcs:ignore ?></span></a></div>
	<div class="grid">
		<?php foreach ( $featured as $p ) { echo cc_product_card( $p ); } // phpcs:ignore ?>
	</div>
</div></section>
<section class="section" style="padding-top:0"><div class="container"><?php echo cc_benefits(); // phpcs:ignore ?></div></section>
<?php
get_footer();
