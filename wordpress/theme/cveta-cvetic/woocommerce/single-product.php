<?php
/**
 * Proizvod: photo, name, price, description, quantity + add to cart, wishlist, perks, related products.
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$p     = wc_get_product( get_the_ID() );
	$cats  = wc_get_product_terms( $p->get_id(), 'product_cat', array( 'orderby' => 'term_order' ) );
	$main  = $cats ? $cats[0] : null;
	$tags  = wc_get_product_terms( $p->get_id(), 'product_tag' );
	$crumb = array( 'Asortiman' => wc_get_page_permalink( 'shop' ) );
	if ( $main ) {
		$crumb[ $main->name ] = get_term_link( $main );
	}
	$crumb[ cc_short_name( $p->get_name() ) ] = '';
	$related = wc_get_related_products( $p->get_id(), 4 );
	?>
	<section class="photo-hero soft"><div class="bg" style="background-image:url(<?php echo cc_img( 'design/hero-onama.jpg' ); ?>)" aria-hidden="true"></div><div class="container"><?php echo cc_crumbs( $crumb ); // phpcs:ignore ?></div></section>
	<section class="section" style="padding-top:1rem"><div class="container">
		<?php woocommerce_output_all_notices(); ?>
		<div class="product-page">
			<div class="product-gallery"><?php echo $p->get_image( 'woocommerce_single', array( 'alt' => $p->get_name() ) ); // phpcs:ignore ?></div>
			<div class="product-info">
				<?php if ( $main ) : ?><a class="eyebrow" href="<?php echo esc_url( get_term_link( $main ) ); ?>"><?php echo esc_html( $main->name ); ?></a><?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<span class="price"><?php echo esc_html( cc_money( $p->get_price() ) ); ?></span>
				<div class="desc"><?php the_content(); ?></div>
				<?php if ( $p->is_purchasable() && $p->is_in_stock() ) : ?>
					<form class="buy-row" method="post" action="<?php echo esc_url( $p->get_permalink() ); ?>" data-buy-form>
						<div class="qty"><button type="button" data-q="-1" aria-label="Manje">−</button><input type="number" name="quantity" min="1" max="99" value="1" aria-label="Količina" data-qty><button type="button" data-q="1" aria-label="Više">+</button></div>
						<button class="btn btn-primary" type="submit" name="add-to-cart" value="<?php echo (int) $p->get_id(); ?>"><?php echo cc_icon( 'cart' ); // phpcs:ignore ?>Dodaj u korpu</button>
						<button class="icon-btn wish-inline" type="button" data-wish="<?php echo (int) $p->get_id(); ?>" aria-label="Lista želja" aria-pressed="false" style="color:var(--pink-500)"><?php echo cc_icon( 'heart' ); // phpcs:ignore ?></button>
					</form>
				<?php else : ?>
					<p class="alert alert-err">Proizvod trenutno nije dostupan. Pozovite nas na <a href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo esc_html( cc_shop( 'phone' ) ); ?></a>.</p>
				<?php endif; ?>
				<div class="card perks">
					<div><?php echo cc_icon( 'truck' ); // phpcs:ignore ?>Besplatna dostava u Novom Sadu za porudžbine od 3.000 RSD</div>
					<div><?php echo cc_icon( 'gift' ); // phpcs:ignore ?>Poruka ili čestitka uz buket – samo je upišite pri naručivanju</div>
					<div><?php echo cc_icon( 'phone' ); // phpcs:ignore ?>Porudžbina i telefonom: <a href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo esc_html( cc_shop( 'phone' ) ); ?></a></div>
				</div>
				<ul class="meta-list">
					<li><strong>Kategorije:</strong>
						<?php echo $cats ? implode( ', ', array_map( fn( $c ) => '<a href="' . esc_url( get_term_link( $c ) ) . '">' . esc_html( $c->name ) . '</a>', $cats ) ) : '—'; // phpcs:ignore ?>
					</li>
					<?php if ( $tags ) : ?>
						<li><strong>Oznake:</strong><div class="chips" style="margin-top:.4rem"><?php foreach ( $tags as $t ) { echo '<span class="chip">' . esc_html( $t->name ) . '</span>'; } // phpcs:ignore ?></div></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div></section>
	<?php if ( $related ) : ?>
		<section class="section" style="padding-top:0"><div class="container">
			<div class="section-head"><h2>Možda će Vam se svideti</h2><a class="link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Ceo asortiman <?php echo cc_icon( 'arrow' ); // phpcs:ignore ?></a></div>
			<div class="grid"><?php foreach ( $related as $rid ) { echo cc_product_card( $rid, true ); } // phpcs:ignore ?></div>
		</div></section>
	<?php endif; ?>
	<?php
endwhile;
get_footer();
