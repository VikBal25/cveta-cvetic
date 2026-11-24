<?php
/**
 * Admin page "Brze cene": all products in one table – price, sale price, stock and "hit" on the home page.
 * Only changed rows are sent and saved. Available to administrators and shop managers.
 */
defined( 'ABSPATH' ) || exit;

const CC_PRICES_SLUG = 'cc-brze-cene';

add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=product',
		'Brze cene',
		'Brze cene',
		'edit_products',
		CC_PRICES_SLUG,
		'cc_prices_page',
		1
	);
} );

/** Shop managers land on "Brze cene" after logging in. */
add_filter( 'login_redirect', function ( $to, $requested, $user ) {
	if ( $user instanceof WP_User && in_array( 'shop_manager', (array) $user->roles, true ) && ( ! $requested || false !== strpos( $requested, 'wp-admin' ) && false === strpos( $requested, '.php?' ) ) ) {
		return admin_url( 'edit.php?post_type=product&page=' . CC_PRICES_SLUG );
	}
	return $to;
}, 20, 3 );
add_filter( 'woocommerce_login_redirect', function ( $to, $user ) {
	return ( $user instanceof WP_User && in_array( 'shop_manager', (array) $user->roles, true ) ) ? admin_url( 'edit.php?post_type=product&page=' . CC_PRICES_SLUG ) : $to;
}, 20, 2 );

/** Save. */
add_action( 'admin_post_cc_save_prices', function () {
	if ( ! current_user_can( 'edit_products' ) ) {
		wp_die( 'Nemate dozvolu.' );
	}
	check_admin_referer( 'cc_save_prices' );
	$rows   = isset( $_POST['p'] ) && is_array( $_POST['p'] ) ? wp_unslash( $_POST['p'] ) : array();
	$saved  = 0;
	$errors = array();
	$num    = function ( $v ) {
		$v = trim( str_replace( array( ' ', '.', ',' ), array( '', '', '.' ), (string) $v ) );
		return '' === $v ? '' : ( is_numeric( $v ) && $v >= 0 ? (string) round( (float) $v ) : false );
	};
	foreach ( $rows as $id => $r ) {
		$product = wc_get_product( absint( $id ) );
		if ( ! $product || ! current_user_can( 'edit_product', $product->get_id() ) ) {
			continue;
		}
		$name = $product->get_name();
		if ( $product->is_type( 'simple' ) && isset( $r['regular'] ) ) {
			$reg  = $num( $r['regular'] );
			$sale = $num( isset( $r['sale'] ) ? $r['sale'] : '' );
			if ( false === $reg || '' === $reg || false === $sale ) {
				$errors[] = "„{$name}“: cena mora biti broj.";
				continue;
			}
			if ( '' !== $sale && (float) $sale >= (float) $reg ) {
				$errors[] = "„{$name}“: akcijska cena mora biti manja od redovne.";
				continue;
			}
			$product->set_regular_price( $reg );
			$product->set_sale_price( $sale );
			if ( '' === $sale ) {
				$product->set_date_on_sale_from( null );
				$product->set_date_on_sale_to( null );
			}
		}
		if ( isset( $r['stock'] ) ) {
			$product->set_stock_status( 'outofstock' === $r['stock'] ? 'outofstock' : 'instock' );
		}
		$product->set_featured( ! empty( $r['hit'] ) );
		if ( isset( $r['visible'] ) ) {
			$want = ! empty( $r['visible'] ) ? 'publish' : 'draft';
			if ( in_array( $product->get_status(), array( 'publish', 'draft' ), true ) ) {
				$product->set_status( $want );
			}
		}
		$product->save();
		$saved++;
	}
	set_transient( 'cc_prices_notice_' . get_current_user_id(), array( 'saved' => $saved, 'errors' => $errors ), 60 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=product&page=' . CC_PRICES_SLUG ) );
	exit;
} );

function cc_prices_page() {
	$products = wc_get_products( array(
		'limit'   => -1,
		'status'  => array( 'publish', 'draft', 'private' ),
		'orderby' => 'title',
		'order'   => 'ASC',
	) );
	$cats   = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
	$notice = get_transient( 'cc_prices_notice_' . get_current_user_id() );
	delete_transient( 'cc_prices_notice_' . get_current_user_id() );
	?>
	<div class="wrap cc-prices">
		<h1>Brze cene <span class="cc-count"><?php echo count( $products ); ?> proizvoda</span></h1>
		<p class="cc-help">Promenite cenu, stanje ili oznaku pa kliknite <strong>Sačuvaj izmene</strong>. Promenjeni redovi su označeni žutom bojom. Cene su u dinarima, bez decimala.</p>

		<?php if ( $notice ) : ?>
			<?php if ( $notice['saved'] ) : ?>
				<div class="notice notice-success is-dismissible"><p>Sačuvano: <strong><?php echo (int) $notice['saved']; ?></strong> <?php echo 1 === (int) $notice['saved'] ? 'proizvod' : 'proizvoda'; ?>.</p></div>
			<?php endif; ?>
			<?php if ( $notice['errors'] ) : ?>
				<div class="notice notice-error"><p><?php echo implode( '<br>', array_map( 'esc_html', $notice['errors'] ) ); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="cc-tools">
			<input type="search" id="cc-q" placeholder="Pretraga po nazivu…" autocomplete="off">
			<select id="cc-cat">
				<option value="">Sve kategorije</option>
				<?php foreach ( $cats as $c ) : ?>
					<option value="<?php echo esc_attr( $c->slug ); ?>"><?php echo esc_html( $c->name ); ?> (<?php echo (int) $c->count; ?>)</option>
				<?php endforeach; ?>
			</select>
			<label class="cc-chk"><input type="checkbox" id="cc-only-changed"> Samo promenjeni</label>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="cc-form">
			<input type="hidden" name="action" value="cc_save_prices">
			<?php wp_nonce_field( 'cc_save_prices' ); ?>
			<table class="cc-table">
				<thead>
					<tr>
						<th class="c-img"></th>
						<th>Proizvod</th>
						<th class="c-num">Cena (RSD)</th>
						<th class="c-num">Akcijska cena</th>
						<th>Stanje</th>
						<th class="c-ico" title="Prikazan na naslovnoj u bloku „Najprodavanije“">Hit</th>
						<th class="c-ico" title="Vidljiv na sajtu">Na sajtu</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $products as $p ) :
					$id      = $p->get_id();
					$simple  = $p->is_type( 'simple' );
					$slugs   = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'slugs' ) );
					$catname = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'names' ) );
					$catname = array_values( array_diff( $catname, array( 'Svi proizvodi' ) ) );
					?>
					<tr data-name="<?php echo esc_attr( mb_strtolower( $p->get_name() ) ); ?>" data-cats="<?php echo esc_attr( ' ' . implode( ' ', is_wp_error( $slugs ) ? array() : $slugs ) . ' ' ); ?>">
						<td class="c-img"><?php echo $p->get_image( array( 48, 48 ) ); // phpcs:ignore ?></td>
						<td class="c-name">
							<a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>" title="<?php echo esc_attr( $p->get_name() ); ?>"><?php echo esc_html( cc_short_name( $p->get_name() ) ); ?></a>
							<span><?php echo esc_html( implode( ', ', array_slice( $catname, 0, 2 ) ) ); ?></span>
						</td>
						<?php if ( $simple ) : ?>
							<td class="c-num"><input type="text" inputmode="numeric" name="p[<?php echo $id; ?>][regular]" value="<?php echo esc_attr( $p->get_regular_price() ); ?>" required></td>
							<td class="c-num"><input type="text" inputmode="numeric" name="p[<?php echo $id; ?>][sale]" value="<?php echo esc_attr( $p->get_sale_price() ); ?>" placeholder="—"></td>
						<?php else : ?>
							<td class="c-num" colspan="2"><a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>">Cene po varijacijama →</a></td>
						<?php endif; ?>
						<td>
							<select name="p[<?php echo $id; ?>][stock]">
								<option value="instock" <?php selected( 'outofstock' !== $p->get_stock_status() ); ?>>Ima</option>
								<option value="outofstock" <?php selected( 'outofstock', $p->get_stock_status() ); ?>>Nema</option>
							</select>
						</td>
						<td class="c-ico"><input type="checkbox" name="p[<?php echo $id; ?>][hit]" value="1" <?php checked( $p->is_featured() ); ?>></td>
						<td class="c-ico">
							<input type="hidden" name="p[<?php echo $id; ?>][visible]" value="0">
							<input type="checkbox" name="p[<?php echo $id; ?>][visible]" value="1" <?php checked( 'publish', $p->get_status() ); ?>>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="cc-empty" hidden>Nema proizvoda za ovu pretragu.</p>

			<div class="cc-bar">
				<span id="cc-changed">Nema izmena</span>
				<button type="button" class="button" id="cc-reset" disabled>Poništi</button>
				<button type="submit" class="button button-primary button-hero" id="cc-save" disabled>Sačuvaj izmene</button>
			</div>
		</form>
	</div>

	<style>
	.cc-prices{max-width:1100px}
	.cc-prices h1{display:flex;align-items:baseline;gap:.6rem}
	.cc-count{font-size:13px;font-weight:400;color:#7a6d8a}
	.cc-help{color:#50445f;margin:.2rem 0 1rem}
	.cc-tools{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-bottom:.8rem}
	.cc-tools input[type=search]{min-width:260px;padding:6px 10px;border-radius:8px}
	.cc-tools select{border-radius:8px}
	.cc-chk{display:flex;gap:.35rem;align-items:center}
	.cc-table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border:1px solid #e4dcef;border-radius:12px}
	.cc-table th:first-child{border-top-left-radius:12px}.cc-table th:last-child{border-top-right-radius:12px}
	.cc-table th{background:#f6f1fc;color:#551b88;text-align:left;font-weight:600;padding:10px;border-bottom:1px solid #e4dcef;z-index:1}
	.cc-table td{padding:8px 10px;border-bottom:1px solid #f1ecf7;vertical-align:middle}
	.cc-table tr:last-child td{border-bottom:0}
	.cc-table tr.is-changed td{background:#fff8db}
	.cc-table tr.is-out .c-name a{color:#9a90a6}
	.c-img{width:52px}.c-img img{width:48px;height:48px;object-fit:cover;border-radius:8px;display:block}
	.c-name a{font-weight:600;color:#2d1846;text-decoration:none}.c-name a:hover{color:#7b2cbf}
	.c-name span{display:block;color:#8d82a0;font-size:12px;margin-top:2px}
	.c-num{width:130px}.c-num input{width:110px;text-align:right;font-variant-numeric:tabular-nums;border-radius:8px}
	.c-num input:invalid{border-color:#d63638}
	.c-ico{width:64px;text-align:center}
	.cc-bar{position:sticky;bottom:0;display:flex;gap:.6rem;align-items:center;justify-content:flex-end;padding:12px 0;margin-top:12px;background:linear-gradient(to top,#f0f0f1 70%,rgba(240,240,241,0))}
	#cc-changed{margin-right:auto;color:#50445f}
	#cc-changed.has{color:#551b88;font-weight:600}
	.cc-prices .button-primary{background:#7b2cbf;border-color:#6a22a8}
	.cc-prices .button-primary:hover{background:#6a22a8}
	@media (max-width:782px){.cc-table .c-img,.cc-table th:nth-child(4),.cc-table td:nth-child(4){display:none}.c-num{width:auto}.c-num input{width:90px}}
	</style>
	<script>
	(function(){
		var form=document.getElementById('cc-form'), rows=[].slice.call(form.querySelectorAll('tbody tr'));
		var q=document.getElementById('cc-q'), cat=document.getElementById('cc-cat'), only=document.getElementById('cc-only-changed');
		var save=document.getElementById('cc-save'), reset=document.getElementById('cc-reset'), info=document.getElementById('cc-changed'), empty=form.querySelector('.cc-empty');
		function val(el){return el.type==='checkbox'?el.checked:el.value;}
		rows.forEach(function(tr){
			tr._inputs=[].slice.call(tr.querySelectorAll('input:not([type=hidden]),select'));
			tr._inputs.forEach(function(el){el._orig=val(el);});
			var st=tr.querySelector('select'); tr.classList.toggle('is-out', st && st.value==='outofstock');
		});
		function changed(tr){return tr._inputs.some(function(el){return val(el)!==el._orig;});}
		function refresh(){
			var n=0;
			rows.forEach(function(tr){var c=changed(tr);tr.classList.toggle('is-changed',c);if(c)n++;var st=tr.querySelector('select');tr.classList.toggle('is-out',st&&st.value==='outofstock');});
			info.textContent=n?('Promenjeno: '+n+(n===1?' proizvod':' proizvoda')):'Nema izmena';
			info.classList.toggle('has',!!n); save.disabled=!n; reset.disabled=!n; filter();
		}
		function filter(){
			var s=q.value.trim().toLowerCase(), c=cat.value, shown=0;
			rows.forEach(function(tr){
				var ok=(!s||tr.dataset.name.indexOf(s)>-1)&&(!c||tr.dataset.cats.indexOf(' '+c+' ')>-1)&&(!only.checked||tr.classList.contains('is-changed'));
				tr.hidden=!ok; if(ok)shown++;
			});
			empty.hidden=!!shown;
		}
		form.addEventListener('input',refresh); form.addEventListener('change',refresh);
		q.addEventListener('input',filter); cat.addEventListener('change',filter); only.addEventListener('change',filter);
		reset.addEventListener('click',function(){rows.forEach(function(tr){tr._inputs.forEach(function(el){if(el.type==='checkbox')el.checked=el._orig;else el.value=el._orig;});});refresh();});
		form.addEventListener('submit',function(){
			// Send only changed products.
			rows.forEach(function(tr){if(!changed(tr)){tr.querySelectorAll('input,select').forEach(function(el){el.disabled=true;});}});
			save.disabled=true; save.textContent='Čuvam…';
		});
		window.addEventListener('beforeunload',function(e){if(!save.disabled){e.preventDefault();e.returnValue='';}});
	})();
	</script>
	<?php
}

/** Shop managers: keep only what they need in the admin menu. */
add_action( 'admin_menu', function () {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	foreach ( array( 'edit-comments.php', 'tools.php', 'themes.php', 'edit.php?post_type=page', 'woocommerce-marketing', 'wc-admin&path=/analytics/overview', 'wc-admin&path=/marketing', 'googlesitekit-dashboard', 'loco' ) as $slug ) {
		remove_menu_page( $slug );
	}
	// WooCommerce "Payments" entry and other settings-only top-level items.
	global $menu;
	foreach ( (array) $menu as $i => $item ) {
		$slug = isset( $item[2] ) ? (string) $item[2] : '';
		if ( false !== strpos( $slug, 'tab=checkout' ) || false !== strpos( $slug, 'wc-settings' ) || false !== strpos( $slug, 'payments' ) ) {
			unset( $menu[ $i ] );
		}
	}
}, 999 );
