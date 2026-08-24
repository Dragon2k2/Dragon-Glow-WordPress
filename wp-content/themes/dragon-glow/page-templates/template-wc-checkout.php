<?php
/**
 * Template Name: WC Checkout — Dragon Glow
 *
 * Custom template for the WooCommerce checkout page.
 * Bypasses `index.php`'s wrapper/article/the_title so the layout is the
 * canonical WC checkout shell used by `dg_render_wc_checkout()`.
 *
 * Routing: `dg_use_wc_checkout_template()` in `inc/woocommerce.php` swaps
 * this template in for any request on the WC checkout endpoint, so the
 * page-template assignment on the WC checkout page (ID 104) is irrelevant.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

dg_render_wc_checkout();

get_footer();