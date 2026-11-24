<?php
/**
 * Plugin Name: CC – zamena stare mape sajta (jednokratno)
 * Description: Pravi kopiju starog sitemap.xml i robots.txt u wp-content/uploads/cc-backup-2026-11-24/, briše stari sitemap.xml (tada /sitemap.xml vodi na /wp-sitemap.xml) i u robots.txt dodaje adresu nove mape. Posle aktivacije se može obrisati.
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;

register_activation_hook( __FILE__, function () {
	$root   = rtrim( ABSPATH, '/' ) . '/';
	$up     = wp_upload_dir();
	$backup = trailingslashit( $up['basedir'] ) . 'cc-backup-2026-11-24/';
	wp_mkdir_p( $backup );
	$log = array();

	foreach ( array( 'sitemap.xml', 'robots.txt' ) as $f ) {
		if ( file_exists( $root . $f ) && ! file_exists( $backup . $f ) ) {
			$log[ $f . ' backup' ] = copy( $root . $f, $backup . $f ) && filesize( $backup . $f ) === filesize( $root . $f );
		}
	}

	// Remove the old static sitemap only when its copy is safe.
	if ( file_exists( $root . 'sitemap.xml' ) && file_exists( $backup . 'sitemap.xml' ) && filesize( $backup . 'sitemap.xml' ) === filesize( $root . 'sitemap.xml' ) ) {
		$log['sitemap.xml removed'] = unlink( $root . 'sitemap.xml' );
	}

	if ( file_exists( $backup . 'robots.txt' ) || ! file_exists( $root . 'robots.txt' ) ) {
		$robots = "User-agent: *\nCrawl-Delay: 20\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";
		$log['robots.txt written'] = false !== file_put_contents( $root . 'robots.txt', $robots );
	}

	update_option( 'cc_sitemap_fix_log', $log, false );
} );

add_action( 'admin_notices', function () {
	$log = get_option( 'cc_sitemap_fix_log' );
	if ( ! $log || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>CC mapa sajta:</strong> ';
	foreach ( $log as $k => $v ) {
		echo esc_html( $k . ': ' . ( $v ? 'OK' : 'GREŠKA' ) ) . ' &nbsp; ';
	}
	echo '</p></div>';
} );

register_deactivation_hook( __FILE__, function () {
	delete_option( 'cc_sitemap_fix_log' );
} );
