/**
 * Dragon Glow — Thank You / Order Received Page JS
 *
 * Single responsibility: paint the header cart-count badge with the live
 * server-truth count the **moment this script executes**, so the user
 * never sees a blank "shopping_bag" icon flickering into a numbered badge.
 *
 * Two-stage render (the "Zero-Delay Cart Badge" pattern)
 * ──────────────────────────────────────────────────────
 * Stage 1 — IN-HEAD DATA ISLAND (printed by `dg_print_cart_data_island()`
 *           in `inc/enqueue.php`, hooked at `wp_head` priority 1):
 *
 *     <script>window.__DG_CART__={"count":2,"ts":1700000000,...}</script>
 *
 *   Runs **before** any CSS, font, or enqueued JS. The first HTML byte
 *   the browser parses already exposes the server's cart count. Reading
 *   that value synchronously and painting it into the existing
 *   `.dg-cart-count` element costs zero network round-trips.
 *
 * Stage 2 — ASYNC CONFIRM (defensive):
 *
 *   We still POST to `?action=dg_ajax_get_cart_count` after first paint
 *   to reconcile against any async session mutation (e.g. background
 *   Buy-Now restore, subscription plugin clearing, mobile-app
 *   concurrent cart edits). If the network response agrees with our
 *   painted value we leave it; otherwise we overwrite. The user never
 *   perceives this round-trip because the badge already shows the right
 *   number on the first frame.
 *
 * Why not just rely on `DGCart.refreshCount()` (the previous version)?
 *   That waits for DOMContentLoaded + script parse + fetch round-trip
 *   (~500–1000 ms on a warm page; 2–3 s on shared hosting with a cold
 *   connection). During that window the user sees the icon without a
 *   number — exactly the bug this file is here to eliminate.
 *
 * Why this script is loaded on Thank You specifically?
 *   The Thank You page is the only WC endpoint where the cart state is
 *   always mid-transition (WC just cleared the session). It's also the
 *   single point of friction users complain about. Everywhere else the
 *   server-rendered count is final and no client-side paint is needed.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    /**
     * Paint a fresh value (or hide) on every `.dg-cart-count` badge.
     *
     * Mirrors the contract used by `DGCart.refreshCount()` in
     * `lib/cart-api.js` — same selectors, same `hidden` toggle rules — so
     * the two paths are interchangeable at runtime.
     *
     * @param {number} count Non-negative integer.
     * @return {void}
     */
    function paintBadge(count) {
        var total = parseInt(count, 10);
        if (!isFinite(total) || total < 0) {
            total = 0;
        }
        var nodes = document.querySelectorAll('.dg-cart-count');
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].textContent = String(total);
            // Toggle the Tailwind `hidden` utility so the badge appears or
            // disappears based on count, matching the PHP-side renderer.
            if (total === 0) {
                nodes[i].classList.add('hidden');
            } else {
                nodes[i].classList.remove('hidden');
            }
        }
    }

    /**
     * Stage 1: read the in-head data island and paint immediately.
     *
     * Critical: this MUST run synchronously, before any deferred work.
     * Because the IIFE itself executes inside an enqueued footer script
     * we still pay the script-parse cost — but we avoid the entire
     * fetch round-trip, which is where the 2–3 s waiting lived.
     *
     * @return {boolean} true when we painted from the island, false when
     *                   no island was present and we stayed out of the way.
     */
    function paintFromDataIsland() {
        var island = window.__DG_CART__;
        if (!island || typeof island.count !== 'number') {
            return false;
        }
        paintBadge(island.count);
        return true;
    }

    /**
     * Stage 2: async confirm against the server.
     *
     * Best-effort — silently swallows errors so the user never sees a
     * toast about a failed cart-count refresh. The data island has
     * already painted a defensible value; this is purely reconciliation.
     *
     * @return {void}
     */
    function confirmFromServer() {
        if (!window.DGCart || typeof window.DGCart.refreshCount !== 'function') {
            return;
        }
        // refreshCount() handles its own error path; we don't need to
        // attach a .catch() because it already .catch()-es internally.
        window.DGCart.refreshCount();
    }

    /**
     * Orchestrate the two stages.
     *
     * Order is non-negotiable:
     *   1. Paint from island — synchronous, zero network.
     *   2. Schedule confirm from server — microtask, no UX impact.
     */
    function init() {
        paintFromDataIsland();

        // Defer to the next macrotask so the browser gets a paint frame
        // with the island value before we kick off any network work.
        if (typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(confirmFromServer, { timeout: 1500 });
        } else {
            window.setTimeout(confirmFromServer, 0);
        }
    }

    // Execute at the earliest moment after parsing. The script tag is
    // already deferred/loaded at the end of the body, so DOMContentLoaded
    // is essentially "now" — but we still guard for inline-async placement.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();

