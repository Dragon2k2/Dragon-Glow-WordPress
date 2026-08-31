/**
 * Dragon Glow — Cart Feedback
 * AJAX quick-add button feedback (.wc-add-to-cart-btn) and header cart-count
 * refresh.
 *
 * Depends on window.DGCart (lib/cart-api.js) and dgAjax (localized on dg-main).
 * Exposes window.DGUpdateCartCount for other modules (e.g. cart-api.js).
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── AJAX: WooCommerce Quick Add to Cart ────────────────────
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.wc-add-to-cart-btn');
        if (!btn) return;

        var productId = btn.dataset.productId;
        var productType = btn.dataset.productType;

        // Variable products → redirect to product page
        if (productType && productType !== 'simple') {
            var productCard = btn.closest('[class*="product-card"]');
            var link = productCard ? productCard.querySelector('a[href]') : null;
            if (link) {
                window.location.href = link.href;
            }
            return;
        }

        e.preventDefault();
        var originalText = btn.textContent.trim();
        btn.textContent = '✓';
        btn.classList.add('opacity-70');

        window.DGCart.add({
            productId: parseInt(productId, 10) || 0,
            slug:      btn.dataset.productSlug || '',
            size:      '',
            quantity:  1,
        })
        .then(function (data) {
            if (data.error && data.data && data.data.redirect) {
                window.location.href = data.data.redirect;
                return;
            }

            if (data.success) {
                // Update cart count
                window.DGCart.refreshCount();

                // Show success feedback
                btn.textContent = '✓ Added!';
                btn.classList.remove('opacity-70');
                btn.classList.add('bg-green-500', 'text-white');

                setTimeout(function () {
                    btn.textContent = originalText;
                    btn.classList.remove('bg-green-500', 'text-white');
                }, 2000);
            } else {
                btn.textContent = originalText;
                btn.classList.remove('opacity-70');
            }
        })
        .catch(function () {
            btn.textContent = originalText;
            btn.classList.remove('opacity-70');
        });
    });

    // ── Update Cart Count ─────────────────────────────────────
    /**
     * Refresh the cart fragment displayed in the header (cart icon + count).
     * Exposed on window so other modules (e.g. quick-add-to-cart.js) can call it
     * after adding an item without needing to duplicate the AJAX call.
     *
     * @return {void}
     */
    function updateCartCount() {
        var formData = new FormData();
        formData.append('action', 'dg_ajax_get_cart_count');
        formData.append('nonce', dgAjax.nonce);

        fetch(dgAjax.url, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                var count = data.data.count;
                document.querySelectorAll('.dg-cart-count').forEach(function (el) {
                    el.textContent = count;
                    el.classList.toggle('hidden', count === 0);
                });
            })
            .catch(function (err) {
                console.error('Cart count update error:', err);
            });
    }
    window.DGUpdateCartCount = updateCartCount;

})();