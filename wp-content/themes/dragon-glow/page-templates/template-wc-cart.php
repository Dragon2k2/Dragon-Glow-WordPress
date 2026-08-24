<?php
/**
 * Template Name: WooCommerce Cart — Dragon Glow
 *
 * Direct WooCommerce cart page using wc_get_template().
 * This template bypasses the [woocommerce_cart] shortcode and directly
 * renders the theme's custom cart layout (woocommerce/cart/cart.php).
 *
 * Setup: Create a WordPress page, set this as the template,
 * and assign it in WooCommerce > Settings > Advanced > Cart page.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Ensure cart fragments are loaded (required for AJAX cart count updates)
wc_load_cart();

// Render the custom WooCommerce cart template
wc_get_template( 'cart/cart.php' );

get_footer();
