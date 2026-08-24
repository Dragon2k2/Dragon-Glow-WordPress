<?php
/**
 * Dragon Glow — Asset Enqueue (loader / orchestrator).
 *
 * Single source of truth for asset loading. The actual enqueue logic lives in
 * per-concern modules under `inc/enqueue/` so each area is easy to locate:
 *
 *   - styles.php           All CSS enqueues (global + conditional).
 *   - scripts.php          All JS enqueues + localization (global + conditional).
 *   - tailwind-config.php  Inline Tailwind CDN config (design tokens).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Load enqueue modules.
require_once DG_DIR . '/inc/enqueue/tailwind-config.php';
require_once DG_DIR . '/inc/enqueue/styles.php';
require_once DG_DIR . '/inc/enqueue/scripts.php';

/**
 * Enqueue scripts and styles.
 *
 * Runs style enqueues first, then script enqueues + localization, preserving
 * the original single-function ordering.
 *
 * @return void
 */
function dg_enqueue_assets(): void {
    dg_enqueue_styles();
    dg_enqueue_scripts_assets();
}
add_action( 'wp_enqueue_scripts', 'dg_enqueue_assets' );

/**
 * Dequeue unnecessary styles.
 *
 * @return void
 */
function dg_dequeue_unnecessary(): void {
    // Remove WooCommerce block styles if not needed
    if ( dg_is_woocommerce_active() && ! is_checkout() && ! is_cart() ) {
        wp_dequeue_style( 'wc-block-style' );
    }
}
add_action( 'wp_enqueue_scripts', 'dg_dequeue_unnecessary', 20 );

/**
 * Preload critical assets.
 *
 * @return void
 */
function dg_preload_assets(): void {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'dg_preload_assets', 1 );