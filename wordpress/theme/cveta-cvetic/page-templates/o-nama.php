<?php
/**
 * Template Name: O nama
 */
defined( 'ABSPATH' ) || exit;
get_header();
$img = function ( $name ) {
	$p = get_page_by_path( $name, OBJECT, 'product' );
	return $p ? get_the_post_thumbnail_url( $p, 'large' ) : '';
};
?>
<?php echo cc_photo_hero( 'Cveta Cvetić', 'hero-onama.jpg', 'O nama', 'Najkvalitetniji cvetni aranžmani – Cvećara Cveta Cvetić, Novi Sad' ); // phpcs:ignore ?>
<section class="section after-hero" style="padding-top:0"><div class="container">
	<div class="about-intro glass"><p><strong>Cveta Cvetić se bori za one prave vrednosti, za srdačnost, toplinu, pozitivu.</strong> Mi nudimo poklon, mi krasimo vaše bitne događaje, mi vodimo računa da vam svaki dan bude poseban.</p></div>
</div></section>
<section class="section" style="padding-top:0"><div class="container about-story">
	<div class="about-photos">
		<img class="p1" src="<?php echo esc_url( $img( 'buket-ruzicasti-san-romanticna-kombinacija-neznih-tonova' ) ); ?>" alt="Buket Ružičasti san" loading="lazy">
		<img class="p2" src="<?php echo esc_url( $img( 'buket-lavanda-elegancija-harmonican-spoj-boja-i-mirisa' ) ); ?>" alt="Buket Lavanda elegancija" loading="lazy">
		<span class="since">od<b>2016</b></span>
	</div>
	<div class="about-card glass">
		<h2>Neka svaki trenutak bude za pamćenje!</h2>
		<?php
		while ( have_posts() ) {
			the_post();
			the_content();
		}
		?>
	</div>
</div></section>
<section class="section" style="padding-top:0"><div class="container value-cards">
	<div class="value-card glass"><span class="ic"><?php echo cc_icon( 'pin' ); // phpcs:ignore ?></span><h3>Gde smo</h3>
		<p>Nalazimo se u mirnom delu grada, na adresi Ilije Birčanina 39, i svakodnevno osluškujemo želje naših mušterija. Bilo da vam treba poklon za rođendan, nežan buket za godišnjicu, cveće za venčanje ili samo znak pažnje bez posebnog povoda – tu smo da vam pomognemo da izaberete savršen cvetni izraz.</p></div>
	<div class="value-card glass"><span class="ic"><?php echo cc_icon( 'truck' ); // phpcs:ignore ?></span><h3>Cene i dostava</h3>
		<p>Naši buketi dostupni su već od 900 dinara, uz <strong>besplatnu dostavu u Novom Sadu za porudžbine od 3.000 dinara</strong>. Boje koje preovlađuju u našim aranžmanima su nežna puder roze, elegantna ljubičasta i čista bela – jer verujemo da svaka boja nosi svoju emociju.</p>
		<div class="swatches"><span class="swatch"><i style="background:#f6c9d3"></i>Puder roze</span><span class="swatch"><i style="background:#7b2cbf"></i>Ljubičasta</span><span class="swatch"><i style="background:#fff"></i>Bela</span></div></div>
	<div class="value-card glass"><span class="ic"><?php echo cc_icon( 'heart' ); // phpcs:ignore ?></span><h3>Naše vrednosti</h3>
		<p>Cveta Cvetić se zalaže za prave vrednosti: srdačnost, iskrenost, toplinu i vedrinu. Cveće je više od ukrasa – ono je poruka, poklon, podrška, radost. Mi se trudimo da svakim buketom unesemo tu poruku u vaš svet.</p></div>
</div></section>
<section class="section" style="padding-top:0"><div class="container"><div class="about-cta glass">
	<p>Naručite već danas cvetni aranžman, pogledajte <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">sve proizvode</a> ili budite slobodni i kontaktirajte nas putem <a href="<?php echo esc_attr( cc_shop( 'phoneHref' ) ); ?>">telefona</a> ili kontakt forme na stranici <a href="<?php echo esc_url( cc_page_url( 'kontakt' ) ); ?>">kontakt</a>.</p>
	<div class="actions"><a class="btn btn-primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Pogledajte asortiman <?php echo cc_icon( 'chevron' ); // phpcs:ignore ?></a><a class="btn btn-outline" href="<?php echo esc_url( cc_page_url( 'kontakt' ) ); ?>">Kontakt</a></div>
</div></div></section>
<section class="section" style="padding-top:0"><div class="container"><?php echo cc_benefits(); // phpcs:ignore ?></div></section>
<?php
get_footer();
