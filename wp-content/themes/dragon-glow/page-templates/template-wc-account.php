<?php
/**
 * Template Name: WC My Account — Dragon Glow
 * Description: Custom template for the WooCommerce My Account page.
 * Bypasses `index.php`'s wrapper / `<article>` / `<h1>` so the layout is the
 * Luminous Ethereal account shell produced by `dg_render_wc_account()`.
 *
 * Routing: `dg_use_wc_account_template()` in `inc/woocommerce/account.php`
 * swaps this template in for any request on the WC My Account endpoint, so
 * the page-template assignment on the WC account page itself is irrelevant.
 *
 * Chrome policy: signed-out visitors see the "Heritage Atelier" full-bleed
 * auth gate (see `dg_render_account_signed_out()`); site nav and footer
 * columns are hidden via the `dg-hide-site-chrome` body class added by
 * `dg_auth_portal_body_class()` so the auth screen is distraction-free.
 * This still calls `get_header()`/`get_footer()` so `wp_head()`/`wp_footer()`
 * fire normally — without them, enqueued CSS/JS would never reach the page.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

dg_render_wc_account();

get_footer();
