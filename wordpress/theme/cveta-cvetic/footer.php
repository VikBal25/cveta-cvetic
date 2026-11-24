<?php defined( 'ABSPATH' ) || exit; ?>
	</main>
	<footer class="footer">
		<img class="deco l" src="<?php echo cc_img( 'design/footer-left.jpg' ); ?>" alt="" aria-hidden="true">
		<img class="deco r" src="<?php echo cc_img( 'design/footer-right.jpg' ); ?>" alt="" aria-hidden="true">
		<div class="container inner">
			<div class="socials">
				<a href="<?php echo esc_url( cc_shop( 'facebook' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook"><?php echo cc_icon( 'fb' ); // phpcs:ignore ?></a>
				<span class="sep" aria-hidden="true"></span>
				<a href="<?php echo esc_url( cc_shop( 'instagram' ) ); ?>" target="_blank" rel="noopener" aria-label="Instagram"><?php echo cc_icon( 'ig' ); // phpcs:ignore ?></a>
			</div>
			<nav class="footer-links" aria-label="Informacije">
				<a href="<?php echo esc_url( cc_page_url( 'o-nama' ) ); ?>">O nama</a>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Asortiman</a>
				<a href="<?php echo esc_url( cc_page_url( 'dostava' ) ); ?>">Naručivanje, plaćanje i dostava</a>
				<a href="<?php echo esc_url( cc_page_url( 'cesta-pitanja' ) ); ?>">Česta pitanja</a>
				<a href="<?php echo esc_url( cc_page_url( 'kontakt' ) ); ?>">Kontakt</a>
				<a href="<?php echo esc_url( cc_page_url( 'lista-zelja' ) ); ?>">Lista želja</a>
				<a href="<?php echo esc_url( cc_page_url( 'uslovi' ) ); ?>">Uslovi korišćenja i prodaje</a>
				<a href="<?php echo esc_url( cc_page_url( 'privatnost' ) ); ?>">Pravila privatnosti</a>
				<a href="<?php echo esc_url( cc_page_url( 'mapa-sajta' ) ); ?>">Mapa sajta</a>
			</nav>
			<div class="footer-meta">PIB: <?php echo esc_html( cc_shop( 'pib' ) ); ?> &nbsp;·&nbsp; Matični broj: <?php echo esc_html( cc_shop( 'mb' ) ); ?></div>
			<div class="footer-small">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Cvećara Cveta Cvetić – <?php echo esc_html( cc_shop( 'address' ) ); ?> – <a href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo esc_html( cc_shop( 'phone' ) ); ?></a></div>
		</div>
	</footer>
</div>
<div class="drawer" data-drawer aria-hidden="true">
	<div class="drawer-backdrop" data-menu-close></div>
	<div class="drawer-panel" role="dialog" aria-label="Meni">
		<div style="display:flex;justify-content:space-between;align-items:center">
			<span class="logo logo-sm"><?php echo cc_logo(); // phpcs:ignore ?></span>
			<button class="icon-btn" type="button" data-menu-close aria-label="Zatvori meni" style="width:48px;height:48px"><?php echo cc_icon( 'close' ); // phpcs:ignore ?></button>
		</div>
		<form action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search" style="margin-top:1rem"><input type="hidden" name="post_type" value="product"><input class="input" type="search" name="s" placeholder="Pretraga proizvoda…" aria-label="Pretraga proizvoda"></form>
		<ul>
			<?php foreach ( cc_nav_items() as $item ) : ?>
				<li><a href="<?php echo esc_url( $item[0] ); ?>"><?php echo esc_html( $item[1] ); ?></a></li>
				<?php if ( ! empty( $item[3] ) ) : ?>
					<li class="sub-list">
					<?php foreach ( cc_shop_cats() as $c ) : ?>
						<?php if ( get_term_by( 'slug', $c[0], 'product_cat' ) ) : ?><a href="<?php echo esc_url( cc_cat_url( $c[0] ) ); ?>"><?php echo esc_html( $c[1] ); ?></a><?php endif; ?>
					<?php endforeach; ?>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
			<li><a href="<?php echo esc_url( cc_page_url( 'lista-zelja' ) ); ?>">Lista želja</a></li>
			<li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Korpa</a></li>
		</ul>
		<a class="btn btn-primary btn-block" href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo cc_icon( 'phone' ); // phpcs:ignore ?><?php echo esc_html( cc_shop( 'phone' ) ); ?></a>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
