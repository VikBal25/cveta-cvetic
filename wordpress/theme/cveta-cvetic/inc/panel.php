<?php
/**
 * Shop panel: a simple "back office" inside the site for the owner.
 *
 * - Lives on a secret address (option cc_panel_slug, e.g. /panel-x7k2m9qa/). Any other address gives the normal 404.
 * - Only users who can edit products (administrator, shop manager) get in; everyone else sees a login form there.
 * - Products, orders and messages are read and written through WooCommerce/WordPress, so the admin stays in sync.
 */
defined( 'ABSPATH' ) || exit;

function cc_panel_slug() {
	$slug = get_option( 'cc_panel_slug' );
	if ( ! $slug ) {
		$slug = 'panel-' . strtolower( wp_generate_password( 8, false, false ) );
		update_option( 'cc_panel_slug', $slug, false );
	}
	return $slug;
}

function cc_panel_url() {
	return home_url( '/' . cc_panel_slug() . '/' );
}

function cc_panel_can() {
	return current_user_can( 'edit_products' );
}

/* ---------- Routing ---------- */
add_action( 'parse_request', function ( $wp ) {
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $path !== cc_panel_slug() ) {
		return;
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	header( 'Referrer-Policy: same-origin', true );

	$error = '';
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['cc_panel_login'] ) ) {
		$error = cc_panel_login();
	}
	if ( isset( $_GET['odjava'] ) && is_user_logged_in() ) {
		check_admin_referer( 'cc_panel_logout' );
		wp_logout();
		wp_safe_redirect( cc_panel_url() );
		exit;
	}
	if ( cc_panel_can() ) {
		cc_panel_render_app();
	} else {
		cc_panel_render_login( $error );
	}
	exit;
}, 0 );

function cc_panel_login() {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'cc_panel_login' ) ) {
		return 'Sesija je istekla, pokušajte ponovo.';
	}
	$key  = 'cc_panel_login_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );
	$hits = (int) get_transient( $key );
	if ( $hits >= 8 ) {
		return 'Previše pokušaja. Sačekajte 15 minuta.';
	}
	$user = wp_signon( array(
		'user_login'    => sanitize_text_field( wp_unslash( $_POST['log'] ?? '' ) ),
		'user_password' => (string) wp_unslash( $_POST['pwd'] ?? '' ),
		'remember'      => true,
	), is_ssl() );
	if ( is_wp_error( $user ) ) {
		set_transient( $key, $hits + 1, 15 * MINUTE_IN_SECONDS );
		return 'Pogrešno korisničko ime ili lozinka.';
	}
	if ( ! user_can( $user, 'edit_products' ) ) {
		wp_logout();
		return 'Ovaj nalog nema pristup.';
	}
	delete_transient( $key );
	wp_safe_redirect( cc_panel_url() );
	exit;
}

/* Shop managers go straight to the panel after logging in anywhere. */
add_filter( 'login_redirect', function ( $to, $requested, $user ) {
	if ( $user instanceof WP_User && in_array( 'shop_manager', (array) $user->roles, true ) ) {
		return cc_panel_url();
	}
	return $to;
}, 30, 3 );
add_filter( 'woocommerce_login_redirect', function ( $to, $user ) {
	return ( $user instanceof WP_User && in_array( 'shop_manager', (array) $user->roles, true ) ) ? cc_panel_url() : $to;
}, 30, 2 );

/* Link to the panel in the admin menu and admin bar (only for people who can use it). */
add_action( 'admin_menu', function () {
	if ( cc_panel_can() ) {
		global $menu;
		$menu['2.5'] = array( 'Panel prodavnice', 'edit_products', cc_panel_url(), '', 'menu-top', 'cc-panel-link', 'dashicons-store' );
	}
}, 5 );
add_action( 'admin_bar_menu', function ( $bar ) {
	if ( cc_panel_can() ) {
		$bar->add_node( array( 'id' => 'cc-panel', 'title' => '🌸 Panel prodavnice', 'href' => cc_panel_url() ) );
	}
}, 80 );

/* Backups (Duplicator) are for administrators only. */
add_action( 'admin_menu', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		remove_menu_page( 'duplicator-pro' );
		remove_menu_page( 'duplicator' );
	}
}, 999 );
add_action( 'admin_init', function () {
	$page = isset( $_GET['page'] ) ? (string) $_GET['page'] : '';
	if ( 0 === strpos( $page, 'duplicator' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Nemate dozvolu.', 403 );
	}
} );

/* ---------- REST API ---------- */
add_action( 'rest_api_init', function () {
	$ns   = 'cc-panel/v1';
	$perm = function () {
		return cc_panel_can();
	};
	register_rest_route( $ns, '/bootstrap', array( 'methods' => 'GET', 'callback' => 'cc_panel_api_bootstrap', 'permission_callback' => $perm ) );
	register_rest_route( $ns, '/products', array(
		array( 'methods' => 'GET', 'callback' => 'cc_panel_api_products', 'permission_callback' => $perm ),
		array( 'methods' => 'POST', 'callback' => 'cc_panel_api_save_product', 'permission_callback' => $perm ),
	) );
	register_rest_route( $ns, '/products/(?P<id>\d+)', array(
		array( 'methods' => 'GET', 'callback' => 'cc_panel_api_product', 'permission_callback' => $perm ),
		array( 'methods' => 'POST', 'callback' => 'cc_panel_api_save_product', 'permission_callback' => $perm ),
		array( 'methods' => 'DELETE', 'callback' => 'cc_panel_api_delete_product', 'permission_callback' => $perm ),
	) );
	register_rest_route( $ns, '/media', array( 'methods' => 'POST', 'callback' => 'cc_panel_api_upload', 'permission_callback' => function () {
		return cc_panel_can() && current_user_can( 'upload_files' );
	} ) );
	register_rest_route( $ns, '/orders', array( 'methods' => 'GET', 'callback' => 'cc_panel_api_orders', 'permission_callback' => $perm ) );
	register_rest_route( $ns, '/orders/(?P<id>\d+)', array( 'methods' => 'POST', 'callback' => 'cc_panel_api_order_status', 'permission_callback' => $perm ) );
	register_rest_route( $ns, '/messages', array( 'methods' => 'GET', 'callback' => 'cc_panel_api_messages', 'permission_callback' => $perm ) );
} );

function cc_panel_all_cat_id() {
	$t = get_term_by( 'slug', 'svi-proizvodi', 'product_cat' );
	return $t ? (int) $t->term_id : 0;
}

function cc_panel_categories() {
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name' ) );
	$all   = cc_panel_all_cat_id();
	$out   = array();
	foreach ( (array) $terms as $t ) {
		if ( (int) $t->term_id === $all || in_array( $t->slug, array( 'uncategorized', 'ostalo', 'ostali' ), true ) ) {
			continue;
		}
		$out[] = array( 'id' => (int) $t->term_id, 'name' => html_entity_decode( $t->name ), 'count' => (int) $t->count );
	}
	return $out;
}

function cc_panel_img( $id, $size = 'woocommerce_thumbnail' ) {
	if ( ! $id ) {
		return '';
	}
	$src = wp_get_attachment_image_src( $id, $size );
	return $src ? $src[0] : '';
}

function cc_panel_product_brief( WC_Product $p ) {
	$all  = cc_panel_all_cat_id();
	$cats = array_values( array_diff( $p->get_category_ids(), array( $all ) ) );
	return array(
		'id'       => $p->get_id(),
		'name'     => html_entity_decode( $p->get_name() ),
		'short'    => function_exists( 'cc_short_name' ) ? cc_short_name( $p->get_name() ) : $p->get_name(),
		'simple'   => $p->is_type( 'simple' ),
		'regular'  => $p->get_regular_price(),
		'sale'     => $p->get_sale_price(),
		'instock'  => 'outofstock' !== $p->get_stock_status(),
		'featured' => $p->is_featured(),
		'visible'  => 'publish' === $p->get_status(),
		'cats'     => array_map( 'intval', $cats ),
		'thumb'    => cc_panel_img( $p->get_image_id() ),
		'url'      => get_permalink( $p->get_id() ),
		'modified' => $p->get_date_modified() ? $p->get_date_modified()->getTimestamp() : 0,
	);
}

function cc_panel_api_bootstrap() {
	$u = wp_get_current_user();
	return array(
		'user'       => $u->display_name,
		'categories' => cc_panel_categories(),
		'newOrders'  => count( wc_get_orders( array( 'status' => array( 'on-hold', 'processing', 'pending' ), 'limit' => 99, 'return' => 'ids' ) ) ),
	);
}

function cc_panel_api_products() {
	$list = wc_get_products( array( 'limit' => -1, 'status' => array( 'publish', 'draft', 'private' ), 'orderby' => 'date', 'order' => 'DESC' ) );
	return array_map( 'cc_panel_product_brief', $list );
}

function cc_panel_api_product( WP_REST_Request $r ) {
	$p = wc_get_product( (int) $r['id'] );
	if ( ! $p ) {
		return new WP_Error( 'cc_404', 'Proizvod nije pronađen.', array( 'status' => 404 ) );
	}
	$data                = cc_panel_product_brief( $p );
	$data['description'] = $p->get_description();
	$data['excerpt']     = wp_strip_all_tags( $p->get_short_description() );
	$data['image']       = array( 'id' => (int) $p->get_image_id(), 'url' => cc_panel_img( $p->get_image_id(), 'medium_large' ) );
	$data['gallery']     = array_map( function ( $id ) {
		return array( 'id' => (int) $id, 'url' => cc_panel_img( $id ) );
	}, $p->get_gallery_image_ids() );
	return $data;
}

function cc_panel_num( $v ) {
	$v = trim( str_replace( array( ' ', '.', 'RSD', 'rsd' ), '', (string) $v ) );
	$v = str_replace( ',', '.', $v );
	if ( '' === $v ) {
		return '';
	}
	return ( is_numeric( $v ) && $v >= 0 ) ? (string) round( (float) $v ) : false;
}

function cc_panel_api_save_product( WP_REST_Request $r ) {
	$id = (int) $r['id'];
	$p  = $id ? wc_get_product( $id ) : new WC_Product_Simple();
	if ( ! $p ) {
		return new WP_Error( 'cc_404', 'Proizvod nije pronađen.', array( 'status' => 404 ) );
	}
	if ( $id && ! current_user_can( 'edit_product', $id ) ) {
		return new WP_Error( 'cc_403', 'Nemate dozvolu.', array( 'status' => 403 ) );
	}
	$d    = (array) $r->get_json_params();
	$name = isset( $d['name'] ) ? trim( sanitize_text_field( $d['name'] ) ) : $p->get_name();
	if ( '' === $name ) {
		return new WP_Error( 'cc_400', 'Upišite naziv proizvoda.', array( 'status' => 400 ) );
	}
	$p->set_name( $name );

	if ( $p->is_type( 'simple' ) && array_key_exists( 'regular', $d ) ) {
		$reg  = cc_panel_num( $d['regular'] );
		$sale = cc_panel_num( isset( $d['sale'] ) ? $d['sale'] : '' );
		if ( false === $reg || '' === $reg ) {
			return new WP_Error( 'cc_400', 'Upišite cenu (samo broj, npr. 4500).', array( 'status' => 400 ) );
		}
		if ( false === $sale ) {
			return new WP_Error( 'cc_400', 'Akcijska cena mora biti broj.', array( 'status' => 400 ) );
		}
		if ( '' !== $sale && (float) $sale >= (float) $reg ) {
			return new WP_Error( 'cc_400', 'Akcijska cena mora biti manja od redovne.', array( 'status' => 400 ) );
		}
		$p->set_regular_price( $reg );
		$p->set_sale_price( $sale );
		if ( '' === $sale ) {
			$p->set_date_on_sale_from( null );
			$p->set_date_on_sale_to( null );
		}
	}
	if ( array_key_exists( 'description', $d ) ) {
		$allowed = array( 'p' => array(), 'br' => array(), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'ul' => array(), 'ol' => array(), 'li' => array() );
		$html    = wp_kses( (string) $d['description'], $allowed );
		$html    = preg_replace( '#<b>(.*?)</b>#s', '<strong>$1</strong>', $html );
		$html    = preg_replace( '#<i>(.*?)</i>#s', '<em>$1</em>', $html );
		$p->set_description( trim( $html ) );
	}
	if ( array_key_exists( 'excerpt', $d ) ) {
		$p->set_short_description( sanitize_textarea_field( $d['excerpt'] ) );
	}
	if ( isset( $d['cats'] ) && is_array( $d['cats'] ) ) {
		$cats = array_values( array_filter( array_map( 'intval', $d['cats'] ) ) );
		$all  = cc_panel_all_cat_id();
		if ( $all ) {
			$cats[] = $all;
		}
		$p->set_category_ids( array_unique( $cats ) );
	}
	if ( isset( $d['instock'] ) ) {
		$p->set_manage_stock( false );
		$p->set_stock_status( $d['instock'] ? 'instock' : 'outofstock' );
	}
	if ( isset( $d['featured'] ) ) {
		$p->set_featured( (bool) $d['featured'] );
	}
	if ( isset( $d['visible'] ) ) {
		$p->set_status( $d['visible'] ? 'publish' : 'draft' );
	} elseif ( ! $id ) {
		$p->set_status( 'publish' );
	}
	if ( isset( $d['image'] ) ) {
		$img = (int) $d['image'];
		$p->set_image_id( $img && wp_attachment_is_image( $img ) ? $img : '' );
	}
	if ( isset( $d['gallery'] ) && is_array( $d['gallery'] ) ) {
		$p->set_gallery_image_ids( array_values( array_filter( array_map( 'intval', $d['gallery'] ), 'wp_attachment_is_image' ) ) );
	}
	if ( ! $id ) {
		$p->set_catalog_visibility( 'visible' );
		$p->set_reviews_allowed( false );
	}
	$new_id = $p->save();
	// Attach uploaded images to the product so they show up under it in the media library.
	foreach ( array_merge( array( $p->get_image_id() ), $p->get_gallery_image_ids() ) as $att ) {
		if ( $att && ! wp_get_post_parent_id( $att ) ) {
			wp_update_post( array( 'ID' => (int) $att, 'post_parent' => $new_id ) );
		}
	}
	return cc_panel_api_product( cc_panel_req( $new_id ) );
}

function cc_panel_req( $id ) {
	$req = new WP_REST_Request( 'GET' );
	$req->set_url_params( array( 'id' => $id ) );
	return $req;
}

function cc_panel_api_delete_product( WP_REST_Request $r ) {
	$id = (int) $r['id'];
	$p  = wc_get_product( $id );
	if ( ! $p || ! current_user_can( 'delete_product', $id ) ) {
		return new WP_Error( 'cc_403', 'Proizvod ne može da se obriše.', array( 'status' => 403 ) );
	}
	wp_trash_post( $id );
	return array( 'deleted' => $id );
}

function cc_panel_api_upload( WP_REST_Request $r ) {
	$files = $r->get_file_params();
	if ( empty( $files['file'] ) ) {
		return new WP_Error( 'cc_400', 'Fajl nije poslat.', array( 'status' => 400 ) );
	}
	$check = wp_check_filetype_and_ext( $files['file']['tmp_name'], $files['file']['name'] );
	if ( empty( $check['type'] ) || 0 !== strpos( $check['type'], 'image/' ) ) {
		return new WP_Error( 'cc_400', 'Dozvoljene su samo slike (JPG, PNG, WEBP).', array( 'status' => 400 ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$title = sanitize_text_field( (string) $r->get_param( 'title' ) );
	$id    = media_handle_sideload( $files['file'], 0, $title ? $title : null );
	if ( is_wp_error( $id ) ) {
		return new WP_Error( 'cc_500', 'Slika nije sačuvana: ' . $id->get_error_message(), array( 'status' => 500 ) );
	}
	if ( $title ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $title );
	}
	return array( 'id' => $id, 'url' => cc_panel_img( $id, 'medium_large' ), 'thumb' => cc_panel_img( $id ) );
}

function cc_panel_status_labels() {
	return array(
		'pending'    => 'Čeka uplatu',
		'on-hold'    => 'Nova',
		'processing' => 'U pripremi',
		'completed'  => 'Isporučeno',
		'cancelled'  => 'Otkazano',
		'refunded'   => 'Vraćen novac',
		'failed'     => 'Neuspelo',
	);
}

function cc_panel_api_orders( WP_REST_Request $r ) {
	$labels = cc_panel_status_labels();
	$orders = wc_get_orders( array( 'limit' => 60, 'orderby' => 'date', 'order' => 'DESC', 'status' => array_keys( $labels ) ) );
	$out    = array();
	foreach ( $orders as $o ) {
		if ( ! $o instanceof WC_Order ) {
			continue;
		}
		$items = array();
		foreach ( $o->get_items() as $it ) {
			$items[] = array( 'name' => html_entity_decode( $it->get_name() ), 'qty' => (int) $it->get_quantity() );
		}
		$out[] = array(
			'id'       => $o->get_id(),
			'number'   => $o->get_order_number(),
			'date'     => $o->get_date_created() ? wp_date( 'j.n.Y. H:i', $o->get_date_created()->getTimestamp() ) : '',
			'status'   => $o->get_status(),
			'label'    => isset( $labels[ $o->get_status() ] ) ? $labels[ $o->get_status() ] : $o->get_status(),
			'total'    => html_entity_decode( wp_strip_all_tags( wc_price( $o->get_total() ) ) ),
			'name'     => trim( $o->get_billing_first_name() . ' ' . $o->get_billing_last_name() ),
			'phone'    => $o->get_billing_phone(),
			'email'    => $o->get_billing_email(),
			'payment'  => $o->get_payment_method_title(),
			'note'     => $o->get_customer_note(),
			'items'    => $items,
			'delivery' => function_exists( 'cc_order_delivery_rows' ) ? cc_order_delivery_rows( $o ) : array(),
			'admin'    => $o->get_edit_order_url(),
		);
	}
	return $out;
}

function cc_panel_api_order_status( WP_REST_Request $r ) {
	$o      = wc_get_order( (int) $r['id'] );
	$status = sanitize_key( (string) $r->get_param( 'status' ) );
	if ( ! $o || ! in_array( $status, array( 'on-hold', 'processing', 'completed', 'cancelled' ), true ) ) {
		return new WP_Error( 'cc_400', 'Neispravan zahtev.', array( 'status' => 400 ) );
	}
	$o->update_status( $status, 'Promenjeno u panelu prodavnice.', true );
	$labels = cc_panel_status_labels();
	return array( 'id' => $o->get_id(), 'status' => $status, 'label' => $labels[ $status ] );
}

function cc_panel_api_messages() {
	$posts = get_posts( array( 'post_type' => 'cc_message', 'post_status' => array( 'private', 'publish' ), 'numberposts' => 40 ) );
	return array_map( function ( $m ) {
		return array(
			'id'      => $m->ID,
			'title'   => html_entity_decode( $m->post_title ),
			'text'    => $m->post_content,
			'date'    => wp_date( 'j.n.Y. H:i', strtotime( $m->post_date_gmt . ' UTC' ) ),
		);
	}, $posts );
}

/* ---------- Pages ---------- */
function cc_panel_head( $title ) {
	$css = get_template_directory_uri() . '/assets/panel/panel.css?v=' . filemtime( get_template_directory() . '/assets/panel/panel.css' );
	?><!doctype html>
<html lang="sr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#7b2cbf">
<title><?php echo esc_html( $title ); ?></title>
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/img/brand/emblem.png' ); ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( $css ); ?>">
</head>
	<?php
}

function cc_panel_render_login( $error ) {
	status_header( 200 );
	cc_panel_head( 'Prijava – Cveta Cvetić' );
	?>
<body class="cp-login">
<main class="cp-login-card">
	<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/brand/emblem.png' ); ?>" alt="" width="84" height="84">
	<h1>Panel prodavnice</h1>
	<?php if ( $error ) : ?><p class="cp-alert"><?php echo esc_html( $error ); ?></p><?php endif; ?>
	<form method="post" autocomplete="on">
		<?php wp_nonce_field( 'cc_panel_login' ); ?>
		<input type="hidden" name="cc_panel_login" value="1">
		<label>Korisničko ime ili e-mail<input type="text" name="log" autocomplete="username" required autofocus></label>
		<label>Lozinka<input type="password" name="pwd" autocomplete="current-password" required></label>
		<button class="cp-btn cp-btn-primary" type="submit">Prijavi se</button>
	</form>
	<a class="cp-muted" href="<?php echo esc_url( wp_lostpassword_url( cc_panel_url() ) ); ?>">Zaboravili ste lozinku?</a>
</main>
</body>
</html>
	<?php
}

function cc_panel_render_app() {
	status_header( 200 );
	$js     = get_template_directory_uri() . '/assets/panel/panel.js?v=' . filemtime( get_template_directory() . '/assets/panel/panel.js' );
	$config = array(
		'api'    => esc_url_raw( rest_url( 'cc-panel/v1/' ) ),
		'nonce'  => wp_create_nonce( 'wp_rest' ),
		'site'   => home_url( '/' ),
		'admin'  => admin_url( 'edit.php?post_type=product' ),
		'logout' => wp_nonce_url( add_query_arg( 'odjava', '1', cc_panel_url() ), 'cc_panel_logout' ),
		'logo'   => get_template_directory_uri() . '/assets/img/brand/emblem.png',
	);
	cc_panel_head( 'Panel prodavnice – Cveta Cvetić' );
	?>
<body class="cp">
<header class="cp-top">
	<a class="cp-brand" href="#proizvodi"><img src="<?php echo esc_url( $config['logo'] ); ?>" alt="" width="40" height="40"><span>Panel prodavnice</span></a>
	<nav class="cp-tabs" aria-label="Sekcije">
		<a href="#proizvodi" data-tab="proizvodi">Proizvodi</a>
		<a href="#narudzbe" data-tab="narudzbe">Narudžbe <b class="cp-badge" id="cp-new-orders" hidden></b></a>
		<a href="#poruke" data-tab="poruke">Poruke</a>
	</nav>
	<div class="cp-top-actions">
		<a class="cp-link" href="<?php echo esc_url( $config['site'] ); ?>" target="_blank" rel="noopener">Sajt ↗</a>
		<a class="cp-link" href="<?php echo esc_url( $config['logout'] ); ?>">Odjava</a>
	</div>
</header>
<main id="cp-main" class="cp-main" aria-live="polite"><p class="cp-loading">Učitavanje…</p></main>
<div id="cp-sheet" class="cp-sheet" hidden></div>
<div id="cp-toast" class="cp-toast" role="status" hidden></div>
<script>window.CP = <?php echo wp_json_encode( $config ); ?>;</script>
<script src="<?php echo esc_url( $js ); ?>"></script>
</body>
</html>
	<?php
}
