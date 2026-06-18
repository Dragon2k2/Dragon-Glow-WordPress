/**
 * Dragon Glow — Cart Page JS
 * Remove item, empty state check, quantity update.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Remove from Cart (AJAX) ───────────────────────────────
    document.addEventListener('click', function (e) {
        var removeBtn = e.target.closest('.product-remove a');

        if (!removeBtn || !removeBtn.classList.contains('remove')) return;

        // Let WooCommerce handle the removal for now
        // This is just for any custom animations
        var row = removeBtn.closest('tr');
        if (row) {
            row.style.opacity = '0.5';
        }
    });

    // ── Update Cart on Quantity Change ─────────────────────────
    var qtyInputs = document.querySelectorAll('.woocommerce .qty');
    qtyInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            // Find and submit the cart form
            var form = document.querySelector('.woocommerce-cart-form');
            if (form) {
                // Trigger WooCommerce's update event
                input.name = 'cart[' + input.closest('tr').dataset.productId + '][qty]';
            }
        });
    });

    // ── Coupon Animation ───────────────────────────────────────
    var couponForm = document.querySelector('.woocommerce .coupon');
    if (couponForm) {
        var couponInput = couponForm.querySelector('input[name="coupon_code"]');
        var couponBtn = couponForm.querySelector('button[name="apply_coupon"]');

        if (couponInput && couponBtn) {
            couponBtn.addEventListener('click', function () {
                if (couponInput.value.trim()) {
                    couponBtn.textContent = '...';
                }
            });
        }
    }

    // ── Empty Cart State Check ────────────────────────────────
    function checkEmptyCart() {
        var cartTable = document.querySelector('.woocommerce-cart-form');
        var cartContents = cartTable ? cartTable.querySelector('tbody') : null;

        if (cartContents && cartContents.children.length <= 1) {
            // Only headers remain
            var emptyMsg = document.querySelector('.cart-empty');
            if (emptyMsg) {
                emptyMsg.style.display = 'block';
            }
        }
    }

    // Run on load
    checkEmptyCart();

    // ── Free Shipping Progress ────────────────────────────────
    function updateShippingProgress() {
        var progressBar = document.querySelector('.shipping-progress-bar');
        if (!progressBar) return;

        // Get subtotal from WooCommerce
        var subtotalEl = document.querySelector('.cart-subtotal td');
        if (subtotalEl) {
            var subtotalText = subtotalEl.textContent.replace(/[^0-9.]/g, '');
            var subtotal = parseFloat(subtotalText) || 0;
            var threshold = 75;
            var percentage = Math.min(100, (subtotal / threshold) * 100);

            progressBar.style.width = percentage + '%';
        }
    }

    updateShippingProgress();

    // ── Cart Totals Sticky (mobile) ───────────────────────────
    var cartTotals = document.querySelector('.cart_totals');
    if (cartTotals && window.innerWidth < 1024) {
        cartTotals.classList.add('sticky', 'bottom-0', 'z-10', 'bg-surface', 'shadow-lg');
    }

})();
