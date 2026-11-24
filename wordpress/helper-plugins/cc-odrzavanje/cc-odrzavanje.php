<?php
/**
 * Plugin Name: Cveta Cvetić – Održavanje
 * Description: Dok je plugin aktivan, posetioci vide poruku „Sajt se ažurira“ (HTTP 503). Prijavljeni administratori vide sajt normalno. Deaktivirajte plugin da biste otvorili sajt.
 * Version: 1.0
 * Requires PHP: 7.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', function () {
	if ( current_user_can( 'manage_options' ) || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	status_header( 503 );
	header( 'Retry-After: 3600' );
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	?><!doctype html>
<html lang="sr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Cveta Cvetić – sajt se ažurira</title>
<style>
body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:linear-gradient(160deg,#f6efff,#ffe9f1);color:#3b1d5e;text-align:center;padding:24px;box-sizing:border-box}
.card{max-width:520px;background:rgba(255,255,255,.85);border-radius:24px;padding:40px 28px;box-shadow:0 20px 60px rgba(111,38,180,.15)}
h1{font-size:1.6rem;margin:0 0 .6rem}
p{line-height:1.55;margin:.4rem 0}
a.btn{display:inline-block;margin-top:1.2rem;padding:.9rem 1.6rem;border-radius:14px;background:linear-gradient(180deg,#8a3fd0,#6f26b4);color:#fff;text-decoration:none;font-weight:700}
</style>
</head>
<body>
<div class="card">
<h1>🌸 Sajt se trenutno ažurira</h1>
<p>Uskoro se vraćamo sa novim izgledom. Hvala na strpljenju!</p>
<p>Porudžbine primamo telefonom i putem Vibera:</p>
<a class="btn" href="tel:+381644124337">064/41-24-337</a>
<p style="margin-top:1.2rem">Ilije Birčanina 39, Novi Sad</p>
</div>
</body>
</html><?php
	exit;
}, 0 );
