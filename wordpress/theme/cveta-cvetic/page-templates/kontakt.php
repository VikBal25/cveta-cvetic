<?php
/**
 * Template Name: Kontakt
 */
defined( 'ABSPATH' ) || exit;
get_header();

// phpcs:disable WordPress.Security.NonceVerification
$sent  = isset( $_GET['poslato'] );
$error = isset( $_GET['greska'] ) ? sanitize_key( $_GET['greska'] ) : '';
// phpcs:enable
$errors = array(
	'polja'  => 'Popunite ime, ispravan e-mail i poruku.',
	'limit'  => 'Previše poruka. Pokušajte ponovo za nekoliko minuta.',
	'sesija' => 'Sesija je istekla. Pošaljite poruku ponovo.',
);
$collage = wc_get_products( array( 'featured' => true, 'limit' => 9, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$tall    = array( 0, 1, 5 );
?>
<section class="contact-top">
	<div class="bg" aria-hidden="true"></div>
	<?php echo cc_petals(); // phpcs:ignore ?>
	<div class="container">
		<div class="contact-hero"><h1>Kontakt</h1><?php echo cc_crumbs( array( 'Kontakt' => '' ) ); // phpcs:ignore ?><p class="lead">Budite slobodni kontaktirajte nas u bilo kom trenutku!</p></div>
		<div class="contact-cards">
			<div class="contact-card glass"><span class="ic"><?php echo cc_icon( 'phone' ); // phpcs:ignore ?></span><div><h3>Pozovite nas</h3><a class="big" href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>"><?php echo esc_html( cc_shop( 'phone' ) ); ?></a></div></div>
			<div class="contact-card glass"><span class="ic"><?php echo cc_icon( 'mail' ); // phpcs:ignore ?></span><div><h3>Pošaljite e-mail</h3><a class="small" href="mailto:<?php echo esc_attr( cc_shop( 'email' ) ); ?>"><?php echo esc_html( cc_shop( 'email' ) ); ?></a></div></div>
			<div class="contact-card glass"><span class="ic"><?php echo cc_icon( 'pin' ); // phpcs:ignore ?></span><div><h3>Dođite kod nas</h3><p class="mid">Ilije Birčanina 39,<br>Novi Sad 21000</p></div></div>
		</div>
	</div>
</section>
<section class="section contact-body" style="padding-top:0"><div class="container">
	<div class="contact-grid">
		<form class="glass-form glass" id="forma" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-contact-form novalidate>
			<h2>Pošaljite poruku</h2>
			<?php if ( $sent ) : ?><div class="alert alert-ok">Hvala! Vaša poruka je poslata. Javićemo Vam se u najkraćem roku.</div><?php endif; ?>
			<?php if ( $error && isset( $errors[ $error ] ) ) : ?><div class="alert alert-err"><?php echo esc_html( $errors[ $error ] ); ?></div><?php endif; ?>
			<input type="hidden" name="action" value="cc_contact">
			<?php wp_nonce_field( 'cc_contact', 'cc_nonce' ); ?>
			<div class="field"><label for="cname">Ime i prezime *</label><input class="input" id="cname" name="name" required maxlength="120" autocomplete="name"><span class="err">Unesite ime i prezime.</span></div>
			<div class="field"><label for="cemail">Email *</label><input class="input" id="cemail" name="email" type="email" required maxlength="160" autocomplete="email"><span class="err">Unesite ispravnu e-mail adresu.</span></div>
			<div class="field"><label for="cmsg">Poruka</label><textarea class="input" id="cmsg" name="message" required maxlength="2000"></textarea><span class="err">Napišite poruku.</span></div>
			<input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
			<button class="btn btn-primary btn-block" type="submit">Pošaljite formu</button>
		</form>
		<a class="map-card" href="<?php echo esc_url( cc_shop( 'maps' ) ); ?>" target="_blank" rel="noopener" aria-label="Otvori lokaciju u Google mapama">
			<img src="<?php echo cc_img( 'design/kontakt-map.jpg' ); ?>" alt="Mapa – Cvećara Cveta Cvetić" width="680" height="662">
			<span class="map-btn"><?php echo cc_icon( 'external' ); // phpcs:ignore ?>Otvori u Google mapama</span>
		</a>
	</div>
	<div class="collage glass">
		<?php foreach ( $collage as $i => $p ) : ?>
			<a class="<?php echo in_array( $i, $tall, true ) ? 'tall' : ''; ?>" href="<?php echo esc_url( $p->get_permalink() ); ?>" aria-label="<?php echo esc_attr( cc_short_name( $p->get_name() ) ); ?>"><?php echo wp_get_attachment_image( $p->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?></a>
		<?php endforeach; ?>
	</div>
</div></section>
<?php
get_footer();
