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

/**
 * Print a "data island" — an inline JSON blob in <head> exposing the
 * server's current cart count.
 *
 * Enterprise rationale (the "Zero-Delay Cart Badge" pattern)
 * ────────────────────────────────────────────────────────────
 * WC pages (especially `is_order_received_page()`) render the global
 * header via `template-parts/global/header-nav.php`, which calls
 * `dg_render_cart_count_badge( $cart_count )`. At that exact millisecond
 * the cart session is mid-transition (just cleared by WC's
 * `woocommerce_checkout_order_processed` hook), so the rendered badge
 * shows `0 / hidden` and only corrects itself ~2 s later when
 * `DGCart.refreshCount()` POSTs back to the server.
 *
 * The fix is to expose the server-side count as soon as PHP has it —
 * BEFORE any enqueued JS loads — by printing it as a tiny JSON object in
 * `<head>` at priority 1 (the earliest hook in the document). The header
 * template reads `window.__DG_CART__` synchronously while rendering, so
 * the badge's text + `hidden` class reflect the **server-truth** count
 * in the very first byte the browser parses.
 *
 * Why a separate hook (and not `wp_localize_script`)?
 *   • `wp_localize_script` injects its `<script>` *after* the enqueued
 *     `dg-main` script tag — which itself sits in `<footer>`. By then
 *     the header has already been painted with the wrong count.
 *   • Printing the island directly in `<head>` at priority 1 runs
 *     before any `wp_head()` enqueue, before CSS, before fonts — the
 *     earliest possible moment a script can execute in the document.
 *
 * Scope:
 *   • Output only on WC contexts where the cart state is meaningful
 *     (otherwise the cost of the script tag is wasted bytes).
 *   • Honor `dg_is_woocommerce_active()` — when WC is off, the badge is
 *     never rendered and the data island would only confuse readers
 *     searching the source.
 *
 * @return void
 */
function dg_print_cart_data_island(): void {
    // Only meaningful when WC owns the cart and we're on a page that
    // actually renders the global header.
    if ( ! dg_is_woocommerce_active() ) {
        return;
    }

    // Restrict to WC contexts where the header cart icon shows.
    // Mirrors the condition set in scripts.php for cart-related enqueues.
    $dg_is_wc_context = is_cart()
        || is_checkout()
        || is_order_received_page()
        || is_shop()
        || is_product()
        || is_product_taxonomy()
        || is_account_page();
    if ( ! $dg_is_wc_context ) {
        return;
    }

    // wp_json_encode is safe for inline scripts when escaped with
    // esc_js() — which is exactly what WordPress ships for that purpose.
    $dg_payload = array(
        'count'    => (int) dg_get_cart_item_count(),
        'ts'       => time(),         // stamp so consumers can detect stale data
        'endpoint' => admin_url( 'admin-ajax.php' ),
    );

    $dg_json = wp_json_encode( $dg_payload );
    if ( false === $dg_json ) {
        // Encoding failed — bail rather than emit a broken `{}` that
        // would silently coerce to a valid-looking but meaningless value.
        return;
    }

    printf(
        "<script>window.__DG_CART__=%s;</script>\n", // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON string built via wp_json_encode.
        $dg_json
    );
}
add_action( 'wp_head', 'dg_print_cart_data_island', 6 );

/**
 * Tell the browser we'll need admin-ajax.php on WC pages, so the very first
 * `DGCart.refreshCount()` POST doesn't pay a DNS + TLS handshake cost.
 *
 * Preconnect is cheap (a single HTTP request to /wp-admin/admin-ajax.php
 * without sending a body) and cuts ~100–300 ms off the first AJAX round
 * trip on slow connections — enough to push the badge update from
 * "noticeable 2–3 s" into "imperceptible" territory.
 *
 * Scoped to WC pages only — everywhere else the data island is absent
 * and so is the postback need.
 *
 * @return void
 */
function dg_preconnect_admin_ajax_on_wc(): void {
    if ( ! dg_is_woocommerce_active() ) {
        return;
    }
    $dg_is_wc_context = is_cart()
        || is_checkout()
        || is_order_received_page()
        || is_shop()
        || is_product()
        || is_product_taxonomy()
        || is_account_page();
    if ( ! $dg_is_wc_context ) {
        return;
    }
    echo '<link rel="preconnect" href="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '">' . "\n";
}
add_action( 'wp_head', 'dg_preconnect_admin_ajax_on_wc', 6 );