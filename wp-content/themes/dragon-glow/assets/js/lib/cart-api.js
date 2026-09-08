/**
 * Dragon Glow — Cart API Module
 *
 * Single source of truth for all cart AJAX operations.
 * Every module that adds/removes/clears cart items should use this
 * instead of rolling its own FormData + fetch logic.
 *
 * Exposes a single global: window.DGCart
 *
 * Methods
 *   add({productId, slug, size, quantity})       → Promise
 *   addMany({productIds})                         → Promise
 *   remove({productId, slug})                     → Promise
 *   removeMany({cartItemKeys})                    → Promise
 *   clear()                                       → Promise
 *   getIdentifiers()                              → Promise<{productIds, slugs}>
 *   refreshCount()                                → Promise<void>
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
     * Pass `variationId` + `variationAttributes` to re-add an exact
     * configuration (used by the cart-page undo flow after a remove).
     *
     * @param {Object} opts
     * @param {number} [opts.productId]  Numeric product ID (0 for mock-only products).
     * @param {string} [opts.slug]      Mock product slug.
     * @param {string} [opts.size]      Selected size label.
     * @param {number} [opts.variationId] Variation ID (0 for simple products).
     * @param {Object} [opts.variationAttributes] Attribute slug → value map.
     * @param {number} [opts.quantity=1] Quantity to add.
     * @return {Promise<Object>}  Resolves to the AJAX response data on success,
     *                            rejects on network error.
     */
    function add(opts) {
        opts = opts || {};
        var variationAttrs = (opts.variationAttributes && typeof opts.variationAttributes === 'object')
            ? JSON.stringify(opts.variationAttributes)
            : '';
        return post('dg_ajax_add_to_cart', {
            product_id:           parseInt(opts.productId, 10) || 0,
            slug:                 opts.slug || '',
            size:                 opts.size || '',
            variation_id:         parseInt(opts.variationId, 10) || 0,
            variation_attributes: variationAttrs,
            quantity:             parseInt(opts.quantity, 10) || 1,
        });
    }

    /**
     * Add multiple simple wishlist products to the cart in one request.
     *
     * The wishlist endpoint validates ownership and eligibility server-side,
     * then returns per-item outcomes for partial-success feedback.
     *
     * @param {Object}   opts
     * @param {number[]} [opts.productIds] Wishlist product IDs.
     * @return {Promise<Object>}
     */
    function addMany(opts) {
        opts = opts || {};
        var productIds = Array.isArray(opts.productIds)
            ? opts.productIds.map(function (id) { return parseInt(id, 10) || 0; }).filter(function (id) { return id > 0; })
            : [];

        return post('dg_wishlist_add_to_cart', {
            product_ids: productIds.join(','),
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
     * Bulk-remove a list of cart-item keys.
     *
     * `cartItemKeys` are the WC `cart_item_key` values (the hash strings
     * rendered into `data-cart-key` on each row). Sending them as a
     * comma-separated string keeps the request FormData-safe; the server
     * rejects any key that isn't present in the current cart, so spoofing
     * a stale key is harmless — the response only counts real removals.
     *
     * @param {Object}   opts
     * @param {string[]} [opts.cartItemKeys] WC cart-item keys.
     * @return {Promise<Object>}
     */
    function removeMany(opts) {
        opts = opts || {};
        var keys = Array.isArray(opts.cartItemKeys)
            ? opts.cartItemKeys.filter(function (k) { return typeof k === 'string' && k.length > 0; })
            : [];

        return post('dg_ajax_remove_cart_items', {
            cart_item_keys: keys.join(','),
        });
    }

    /**
     * Empty the entire cart.
     *
     * @return {Promise<Object>}
     */
    function clear() {
        return post('dg_ajax_clear_cart', {});
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
        addMany:       addMany,
        remove:        remove,
        removeMany:    removeMany,
        clear:         clear,
        getIdentifiers: getIdentifiers,
        refreshCount:  refreshCount,
    };

})();
