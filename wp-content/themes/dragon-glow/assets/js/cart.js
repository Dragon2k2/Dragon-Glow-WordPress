/**
 * Dragon Glow — Cart Page JS
 * AJAX remove, AJAX quantity update, empty state toggle, parallax blobs.
 *
 * Depends on: window.dgAjax.url, window.dgAjax.nonce (set by wp_localize_script)
 * Actions used:
 *   dg_ajax_remove_from_cart  → removes item, returns {success, message}
 *   dg_ajax_update_cart       → updates qty,  returns {success, fragments}
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── DOM ready ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        bindRemoveButtons();
        bindQtySteppers();
        initParallaxBlobs();
    }

    // ── Remove item ───────────────────────────────────────────────────────────

    function bindRemoveButtons() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.dg-remove-btn');
            if (!btn) return;
            removeItem(btn);
        });
    }

    /**
     * Fade out the row, call AJAX remove, then reload to refresh totals.
     * Reload is required so the order summary sidebar reflects the new totals.
     */
    function removeItem(btn) {
        var cartKey = btn.dataset.cartKey;
        if (!cartKey) return;

        var row = document.getElementById('dg-row-' + cartKey);
        if (!row) return;

        // Animate row out
        row.classList.add('is-removing');

        var formData = new FormData();
        formData.append('action',        'dg_ajax_remove_from_cart');
        formData.append('nonce',         getNonce());
        formData.append('cart_item_key', cartKey);

        fetch(getAjaxUrl(), {
            method: 'POST',
            body:   formData,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                // Wait for CSS transition (0.4s), then reload to update totals
                setTimeout(function () {
                    window.location.reload();
                }, 440);
            } else {
                // Revert animation on failure
                row.classList.remove('is-removing');
                console.warn('[Dragon Glow] Remove failed:', data.data && data.data.message);
            }
        })
        .catch(function () {
            row.classList.remove('is-removing');
        });
    }

    /**
     * After all rows removed, show empty state and hide cart grid.
     * Called after DOM manipulation if no reload is triggered.
     */
    function checkEmptyState() {
        var rows = document.querySelectorAll('#dg-cart-items tr.dg-cart-row:not(.is-removing)');
        if (rows.length === 0) {
            var cartView  = document.getElementById('dg-cart-view');
            var emptyView = document.getElementById('dg-empty-cart-view');
            if (cartView)  { cartView.style.display  = 'none'; }
            if (emptyView) { emptyView.classList.add('is-visible'); }
        }
    }

    // ── Quantity steppers ─────────────────────────────────────────────────────

    function bindQtySteppers() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.dg-qty-stepper-btn');
            if (!btn) return;

            var stepper = btn.closest('.dg-qty-stepper');
            if (!stepper) return;

            var cartKey = stepper.dataset.cartKey;
            var current = parseInt(stepper.dataset.qty, 10) || 1;
            var newQty  = btn.classList.contains('dg-qty-decrease') ? current - 1 : current + 1;

            if (newQty < 1) { return; }  // Use remove button for 0

            updateQty(stepper, cartKey, newQty);
        });
    }

    /**
     * Optimistically update displayed qty, then AJAX update.
     * On success: reload page so order summary totals are accurate.
     */
    function updateQty(stepper, cartKey, newQty) {
        // Optimistic UI: update display immediately
        stepper.dataset.qty = newQty;
        var display = stepper.querySelector('.dg-qty-value');
        if (display) { display.textContent = newQty; }

        // Disable stepper during request
        stepper.querySelectorAll('.dg-qty-stepper-btn').forEach(function (b) {
            b.disabled = true;
        });

        var formData = new FormData();
        formData.append('action',        'dg_ajax_update_cart');
        formData.append('nonce',         getNonce());
        formData.append('cart_item_key', cartKey);
        formData.append('quantity',      newQty);

        fetch(getAjaxUrl(), {
            method: 'POST',
            body:   formData,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                // Reload to refresh totals sidebar
                window.location.reload();
            } else {
                // Re-enable on failure (keep optimistic value for now)
                stepper.querySelectorAll('.dg-qty-stepper-btn').forEach(function (b) {
                    b.disabled = false;
                });
                console.warn('[Dragon Glow] Qty update failed');
            }
        })
        .catch(function () {
            stepper.querySelectorAll('.dg-qty-stepper-btn').forEach(function (b) {
                b.disabled = false;
            });
        });
    }

    // ── Parallax blobs ────────────────────────────────────────────────────────

    function initParallaxBlobs() {
        var blob = document.querySelector('.js-cart-blob');
        if (!blob || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

        window.addEventListener('mousemove', function (e) {
            var x = e.clientX / window.innerWidth;
            var y = e.clientY / window.innerHeight;
            blob.style.transform = 'translate(' + (x * 28) + 'px, ' + (y * 28) + 'px)';
        }, { passive: true });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    function getAjaxUrl() {
        return (window.dgAjax && window.dgAjax.url) ? window.dgAjax.url : '/wp-admin/admin-ajax.php';
    }

    function getNonce() {
        return (window.dgAjax && window.dgAjax.nonce) ? window.dgAjax.nonce : '';
    }

})();
