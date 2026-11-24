<?php
/**
 * Asortiman (mockup 04): title + "Specijalne ponude" card, category strip, search, sorting, grid, pages.
 * Used for the shop page, product categories and product search.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$current = is_product_category() ? get_queried_object() : null;
$title   = $current ? $current->name : ( is_search() ? 'Pretraga: „' . get_search_query() . '“' : 'Asortiman' );
$labels  = array();
foreach ( cc_shop_cats() as $c ) {
	$labels[ $c[0] ] = $c[1];
}
if ( $current && isset( $labels[ $current->slug ] ) ) {
	$title = $labels[ $current->slug ];
}
$shop_url = wc_get_page_permalink( 'shop' );
$orderby  = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : 'menu_order'; // phpcs:ignore
?>
<section class="page-hero">
	<?php echo cc_petals(); // phpcs:ignore ?>
	<div class="container shop-hero">
		<div>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php echo cc_crumbs( $current || is_search() ? array( 'Asortiman' => $shop_url, $title => '' ) : array( 'Asortiman' => '' ) ); // phpcs:ignore ?>
			<p class="lead"><?php echo $current && $current->description ? esc_html( $current->description ) : 'Buketi, aranžmani i pokloni za svaku priliku'; ?></p>
		</div>
		<a class="special-card" href="<?php echo esc_url( cc_cat_url( 'specijalne-ponude' ) ); ?>">
			<div class="txt"><h2>Specijalne<br>ponude</h2><span class="pct">%</span></div>
			<img src="<?php echo cc_img( 'design/special-ponude.jpg' ); ?>" alt="Buket roze ruža" width="378" height="284">
		</a>
	</div>
</section>
<section class="section" style="padding-top:0"><div class="container"><div class="shop-panel">
	<div class="cat-strip">
		<button class="cat-arrow prev" type="button" data-cat-scroll="-1" aria-label="Prethodne kategorije"><?php echo cc_icon( 'chevron' ); // phpcs:ignore ?></button>
		<div class="cat-track" data-cats>
			<?php
			foreach ( cc_shop_cats() as $c ) :
				$term = get_term_by( 'slug', $c[0], 'product_cat' );
				if ( ! $term ) {
					continue;
				}
				$img = $c[2] ? cc_img( 'design/' . $c[2] ) : '';
				if ( ! $img ) {
					$thumb = get_term_meta( $term->term_id, 'thumbnail_id', true );
					$img   = $thumb ? wp_get_attachment_image_url( $thumb, 'woocommerce_thumbnail' ) : cc_img( 'design/cat-buketi.jpg' );
				}
				$active = $current && $current->slug === $c[0];
				$href   = $active ? $shop_url : get_term_link( $term );
				?>
				<a class="cat-card<?php echo $active ? ' active' : ''; ?>" href="<?php echo esc_url( $href ); ?>"><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy"><span><?php echo esc_html( $c[1] ); ?></span></a>
			<?php endforeach; ?>
		</div>
		<button class="cat-arrow next" type="button" data-cat-scroll="1" aria-label="Sledeće kategorije"><?php echo cc_icon( 'chevron' ); // phpcs:ignore ?></button>
	</div>
	<div class="shop-toolbar">
		<span><?php woocommerce_result_count(); ?></span>
		<div class="tools">
			<form role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="post_type" value="product">
				<input class="input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Pretraga…" aria-label="Pretraga proizvoda">
			</form>
			<form method="get" data-sort-form>
				<select class="input" name="orderby" aria-label="Sortiranje" onchange="this.form.submit()">
					<?php foreach ( apply_filters( 'woocommerce_catalog_orderby', array() ) as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $orderby, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
			</form>
		</div>
	</div>
	<?php woocommerce_output_all_notices(); ?>
	<?php if ( woocommerce_product_loop() && have_posts() ) : ?>
		<div class="grid">
			<?php
			while ( have_posts() ) {
				the_post();
				echo cc_product_card( get_the_ID(), true, true ); // phpcs:ignore
			}
			?>
		</div>
		<?php
		global $wp_query;
		$links = paginate_links( array(
			'total'     => $wp_query->max_num_pages,
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'type'      => 'array',
			'prev_next' => false,
		) );
		if ( $links ) {
			echo '<nav class="pagination" aria-label="Strane">' . implode( '', $links ) . '</nav>'; // phpcs:ignore
		}
		?>
	<?php else : ?>
		<div class="empty">
			<h3>Ovu ponudu pripremamo po porudžbini</h3>
			<p>Pozovite nas na <a href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo esc_html( cc_shop( 'phone' ) ); ?></a> – napravićemo poklon po Vašoj želji.</p>
			<a class="btn btn-outline" href="<?php echo esc_url( $shop_url ); ?>">Prikaži sve proizvode</a>
		</div>
	<?php endif; ?>
</div></div></section>
<?php
echo cc_cta(); // phpcs:ignore
get_footer();
