/**
 * Dragon Glow — Product Page JS
 * Gallery image swap, thumbnail active state, tab switching, qty +/-.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Image Gallery Swap ────────────────────────────────────
    window.dgChangeImage = function (btn) {
        var fullSrc = btn.dataset.full;
        var mainImg = document.getElementById('dg-main-image');

        if (mainImg && fullSrc) {
            // Fade transition
            mainImg.style.opacity = '0';
            setTimeout(function () {
                mainImg.src = fullSrc;
                mainImg.style.opacity = '1';
            }, 150);
        }

        // Update thumbnail active states
        var thumbnails = document.querySelectorAll('.thumbnail-btn');
        thumbnails.forEach(function (thumb) {
            thumb.classList.remove('border-primary', 'ring-2', 'ring-primary-container/20', 'opacity-100');
            thumb.classList.add('border-outline-variant/30', 'opacity-60');
        });

        btn.classList.add('border-primary', 'ring-2', 'ring-primary-container/20', 'opacity-100');
        btn.classList.remove('border-outline-variant/30', 'opacity-60');
    };

    // ── Tab Switching ─────────────────────────────────────────
    window.dgSwitchTab = function (tabId) {
        // Hide all panes
        var panes = document.querySelectorAll('.dg-tab-pane');
        panes.forEach(function (pane) {
            pane.classList.add('hidden');
        });

        // Reset all tab buttons
        var buttons = document.querySelectorAll('.dg-tab-btn');
        buttons.forEach(function (btn) {
            btn.classList.remove('text-primary', 'font-bold', 'border-b-2', 'border-tertiary-container');
            btn.classList.add('text-on-surface-variant', 'font-medium');
            btn.setAttribute('aria-selected', 'false');
        });

        // Show selected pane
        var pane = document.getElementById('tab-' + tabId);
        if (pane) {
            pane.classList.remove('hidden');
        }

        // Activate selected button
        var btn = document.querySelector('[data-tab="' + tabId + '"]');
        if (btn) {
            btn.classList.add('text-primary', 'font-bold', 'border-b-2', 'border-tertiary-container');
            btn.classList.remove('text-on-surface-variant', 'font-medium');
            btn.setAttribute('aria-selected', 'true');
        }

        // Scroll to tabs on mobile
        var tabsSection = document.getElementById('product-tabs');
        if (tabsSection && window.innerWidth < 768) {
            tabsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ── Quantity +/- Buttons ──────────────────────────────────
    document.querySelectorAll('.quantity').forEach(function (container) {
        var input = container.querySelector('.qty');
        var minusBtn = container.querySelector('.minus');
        var plusBtn = container.querySelector('.plus');

        if (!input || !minusBtn || !plusBtn) return;

        minusBtn.addEventListener('click', function () {
            var val = parseInt(input.value) || 1;
            var min = parseInt(input.min) || 1;
            if (val > min) {
                input.value = val - 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        plusBtn.addEventListener('click', function () {
            var val = parseInt(input.value) || 0;
            var max = parseInt(input.max) || 999;
            if (val < max) {
                input.value = val + 1;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });

    // ── Star Rating in Review Form ───────────────────────────
    var reviewStars = document.getElementById('review-stars');
    if (reviewStars) {
        var ratingInput = document.getElementById('review-rating-value');
        var buttons = reviewStars.querySelectorAll('button');

        buttons.forEach(function (btn, index) {
            btn.addEventListener('mouseenter', function () {
                highlightStars(index + 1);
            });

            btn.addEventListener('click', function () {
                if (ratingInput) {
                    ratingInput.value = index + 1;
                }
                highlightStars(index + 1);
            });
        });

        reviewStars.addEventListener('mouseleave', function () {
            var current = parseInt(ratingInput ? ratingInput.value : 5);
            highlightStars(current);
        });

        function highlightStars(count) {
            buttons.forEach(function (btn, idx) {
                var icon = btn.querySelector('.material-symbols-outlined');
                if (idx < count) {
                    icon.style.fontVariationSettings = "'FILL' 1";
                    icon.classList.remove('text-outline-variant');
                    icon.classList.add('text-tertiary');
                } else {
                    icon.style.fontVariationSettings = "'FILL' 0";
                    icon.classList.add('text-outline-variant');
                    icon.classList.remove('text-tertiary');
                }
            });
        }
    }

    // ── Sticky Product Info ───────────────────────────────────
    var productInfo = document.getElementById('product-info');
    if (productInfo) {
        var lastScroll = 0;

        window.addEventListener('scroll', function () {
            var scroll = window.scrollY;

            if (scroll > 600 && scroll > lastScroll) {
                productInfo.classList.add('shadow-lg');
            } else {
                productInfo.classList.remove('shadow-lg');
            }

            lastScroll = scroll;
        }, { passive: true });
    }

    // ── Review Form Submit ───────────────────────────────────
    var reviewForm = document.getElementById('dg-review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(reviewForm);
            formData.append('action', 'dg_submit_review');
            formData.append('nonce', dgAjax.nonce);

            var submitBtn = reviewForm.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.textContent = '...';
            submitBtn.disabled = true;

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var msg = document.getElementById('dg-review-msg');
                if (msg) {
                    msg.textContent = data.data ? data.data.message : '';
                    msg.classList.remove('hidden');
                }
                if (data.success) {
                    reviewForm.reset();
                }
            })
            .finally(function () {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

})();
