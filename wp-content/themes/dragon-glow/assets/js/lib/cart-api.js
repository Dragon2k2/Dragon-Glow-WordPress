/**
 * Dragon Glow — Cart API Module
 *
 * Single source of truth for all cart AJAX operations.
 * Every module that adds/removes cart items should use this instead of
 * rolling its own FormData + fetch logic.
 *
 * Exposes a single global: window.DGCart
 *
 * Methods
 *   add({productId, slug, size, quantity}) → Promise
 *   remove({productId, slug})              → Promise
 *   getIdentifiers()                       → Promise<{productIds, slugs}>
 *   refreshCount()                          → Promise<void>
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Helpers ────────────────────────────────────────────────────────────────

    function getAjaxUrl() {
        return (window.dgAjax && window.dgAjax.url)
            ? window.dgAjax.url
            : '/wp-admin/admin-ajax.php';
    }

    function getNonce() {
        return (window.dgAjax && window.dgAjax.nonce)
            ? window.dgAjax.nonce
            : '';
    }

    /**
     * Build a FormData payload common to all cart actions.
     *
     * @param {string} action
     * @param {Object} extras  Additional key/value pairs to append.
     * @return {FormData}
     */
    function buildFormData(action, extras) {
        var fd = new FormData();
        fd.append('action', action);
        fd.append('nonce', getNonce());
        if (extras) {
            Object.keys(extras).forEach(function (key) {
                if (extras[key] !== undefined && extras[key] !== null) {
                    fd.append(key, extras[key]);
                }
            });
        }
        return fd;
    }

    /**
     * Fire a POST request and return the parsed JSON.
     *
     * @param {string}   action
     * @param {Object}   extras  Additional FormData fields.
     * @return {Promise<Object>}
     */
    function post(action, extras) {
        return fetch(getAjaxUrl(), {
            method:      'POST',
            body:        buildFormData(action, extras),
            credentials: 'same-origin',
        })
        .then(function (r) { return r.json(); });
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Add a product to the cart.
     *
     * Supports both WooCommerce products (productId > 0) and mock products
     * (productId === 0, slug required).  Slug and size are always sent so
     * the backend can disambiguate.
     *
     * @param {Object} opts
     * @param {number} [opts.productId]  Numeric product ID (0 for mock-only products).
     * @param {string} [opts.slug]      Mock product slug.
     * @param {string} [opts.size]      Selected size label.
     * @param {number} [opts.quantity=1] Quantity to add.
     * @return {Promise<Object>}  Resolves to the AJAX response data on success,
     *                            rejects on network error.
     */
    function add(opts) {
        opts = opts || {};
        return post('dg_ajax_add_to_cart', {
            product_id: parseInt(opts.productId, 10) || 0,
            slug:       opts.slug || '',
            size:       opts.size || '',
            quantity:   parseInt(opts.quantity, 10) || 1,
        });
    }

    /**
     * Remove a product from the cart.
     *
     * Accepts productId (for WooCommerce products) OR slug (for mock products).
     * Both are sent so the backend can handle either gracefully.
     *
     * @param {Object} opts
     * @param {number} [opts.productId]  Numeric product ID (0 if not a WC product).
     * @param {string} [opts.slug]      Mock product slug.
     * @return {Promise<Object>}
     */
    function remove(opts) {
        opts = opts || {};
        return post('dg_ajax_remove_product_from_cart', {
            product_id: parseInt(opts.productId, 10) || 0,
            slug:       opts.slug || '',
        });
    }

    /**
     * Fetch cart identifiers — the stable "keys" used to determine whether a
     * product button should show an "Added" state.
     *
     * Returns {productIds: number[], slugs: string[]}
     *   - productIds: numeric WC product IDs in the cart.
     *   - slugs:       mock product slugs in the cart.
     *
     * @return {Promise<Object>}
     */
    function getIdentifiers() {
        return post('dg_ajax_get_cart_identifiers', {});
    }

    /**
     * Refresh the cart-count badge in the header.
     * Calls the existing window.DGUpdateCartCount if available.
     *
     * @return {Promise<void>}
     */
    function refreshCount() {
        return post('dg_ajax_get_cart_count', {}).then(function (data) {
            if (!data.success) return;
            var count = data.data && data.data.count;
            document.querySelectorAll('.dg-cart-count').forEach(function (el) {
                el.textContent = count;
                el.classList.toggle('hidden', count === 0);
            });
        }).catch(function () {
            // Silently ignore network errors for badge refresh.
        });
    }

    // ── Expose on window ──────────────────────────────────────────────────────

    window.DGCart = {
        add:           add,
        remove:        remove,
        getIdentifiers: getIdentifiers,
        refreshCount:  refreshCount,
    };

})();
