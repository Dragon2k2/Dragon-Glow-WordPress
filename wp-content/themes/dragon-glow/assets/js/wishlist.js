/**
 * Dragon Glow — Wishlist Page JS
 * Remove from wishlist, empty state check.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Remove from Wishlist (AJAX) ──────────────────────────
    document.addEventListener('click', function (e) {
        var removeBtn = e.target.closest('.dg-remove-wishlist');
        if (!removeBtn) return;

        e.preventDefault();

        var productId = removeBtn.dataset.productId;
        var card = removeBtn.closest('[data-product-id]');

        // Visual feedback
        removeBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">refresh</span>';

        var formData = new FormData();
        formData.append('action', 'dg_toggle_wishlist');
        formData.append('product_id', productId);
        formData.append('nonce', dgAjax.nonce);

        fetch(dgAjax.url, {
            method: 'POST',
            body: formData
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                // Animate removal
                if (card) {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    card.style.transition = 'all 0.3s ease';

                    setTimeout(function () {
                        card.remove();

                        // Check if wishlist is now empty
                        checkEmptyWishlist();
                    }, 300);
                }

                // Update count
                var countEl = document.querySelector('.wishlist-count');
                if (countEl && data.data.count !== undefined) {
                    countEl.textContent = data.data.count;
                }
            } else {
                // Reset button
                removeBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">close</span>';
            }
        })
        .catch(function (err) {
            console.error('Remove wishlist error:', err);
            removeBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">close</span>';
        });
    });

    // ── Quick Add to Cart from Wishlist ──────────────────────
    document.querySelectorAll('.dg-wishlist-grid .wc-add-to-cart-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var productId = this.dataset.productId;
            var productType = this.dataset.productType;

            // Variable products - redirect to product page
            if (productType && productType !== 'simple') {
                // Find the product link
                var card = this.closest('[data-product-id]');
                var link = card ? card.querySelector('a[href^="/product"]') : null;
                if (link) {
                    window.location.href = link.href;
                }
                return;
            }

            e.preventDefault();

            var originalText = this.textContent;
            this.textContent = '...';
            this.disabled = true;

            var formData = new FormData();
            formData.append('action', 'dg_ajax_add_to_cart');
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('nonce', dgAjax.nonce);

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    this.textContent = '✓ Added!';
                    this.classList.add('bg-green-500', 'text-white');

                    setTimeout(function () {
                        this.textContent = originalText;
                        this.classList.remove('bg-green-500', 'text-white');
                        this.disabled = false;
                    }.bind(this), 2000);
                } else {
                    this.textContent = originalText;
                    this.disabled = false;
                }
            }.bind(this))
            .catch(function (err) {
                console.error('Add to cart error:', err);
                this.textContent = originalText;
                this.disabled = false;
            }.bind(this));
        });
    });

    // ── Check Empty State ────────────────────────────────────
    function checkEmptyWishlist() {
        var grid = document.getElementById('dg-wishlist-grid');
        var items = grid ? grid.querySelectorAll('[data-product-id]') : [];

        if (items.length === 0) {
            showEmptyState();
        }
    }

    function showEmptyState() {
        var container = document.querySelector('main > div');
        if (!container) return;

        container.innerHTML = `
            <div class="text-center py-24">
                <div class="w-48 h-48 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-8">
                    <span class="material-symbols-outlined text-primary" style="font-size: 96px;">favorite</span>
                </div>

                <h2 class="font-headline text-headline-md text-primary mb-4">
                    ${dg_i18n.yourWishlistEmpty || 'Your wishlist is empty'}
                </h2>

                <p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
                    ${dg_i18n.emptyWishlistDesc || 'Save your favorite products here so you can easily find them later. Start exploring our collection!'}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="${dg_shop_url || '/shop/'}" class="btn-primary">
                        ${dg_i18n.shopNow || 'Shop Now'}
                    </a>
                    <a href="${dg_home_url || '/'}" class="btn-ghost">
                        ${dg_i18n.backToHome || 'Back to Home'}
                    </a>
                </div>
            </div>
        `;
    }

    // ── Initialize ───────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Add empty state text translations if needed
        if (typeof dgAjax !== 'undefined') {
            window.dg_i18n = {
                yourWishlistEmpty: 'Your wishlist is empty',
                emptyWishlistDesc: 'Save your favorite products here so you can easily find them later. Start exploring our collection!',
                shopNow: 'Shop Now',
                backToHome: 'Back to Home'
            };
        }

        // Add shop and home URLs for empty state
        window.dg_shop_url = document.body.dataset.shopUrl || '/shop/';
        window.dg_home_url = document.body.dataset.homeUrl || '/';
    });

})();
