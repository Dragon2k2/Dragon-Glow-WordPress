/**
 * Dragon Glow — Product Page JS
 * Gallery image swap (cache-aware, instant), thumbnail active state,
 * tab switching, qty +/-, star rating, sticky bar, review form.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Image Gallery Preloader ─────────────────────────────────
    // Collect all data-full URLs from thumbnails; skip the first
    // thumbnail because its image is already loaded on the page.
    (function preloadGalleryImages() {
        var thumbs = document.querySelectorAll('.thumbnail-btn');
        if (!thumbs.length) return;

        var toPreload = [];
        for (var i = 1; i < thumbs.length; i++) {
            var src = thumbs[i].dataset.full;
            if (src) toPreload.push(src);
        }

        if (!toPreload.length) return;

        // Defer to after window.load so the critical rendering path
        // is never blocked.  Falls back to an immediate loop for
        // browsers without requestIdleCallback.
        var preload = function () {
            for (var j = 0; j < toPreload.length; j++) {
                (new Image()).src = toPreload[j];
            }
        };

        if (typeof requestIdleCallback !== 'undefined') {
            requestIdleCallback(preload, { timeout: 2000 });
        } else if (document.readyState === 'complete') {
            setTimeout(preload, 0);
        } else {
            window.addEventListener('load', function () {
                setTimeout(preload, 0);
            });
        }
    }());

    // ── Image Gallery Swap ─────────────────────────────────────
    // Strategy:
    //  1. Guard: ignore re-clicks on the same thumbnail.
    //  2. If the new image is already in browser cache (Image.complete)
    //     the swap is near-instantaneous; no setTimeout is used.
    //  3. If not cached yet, wait for the Image load event before
    //     fading in — this eliminates the blank / flicker window.
    //  4. If the user clicks a different thumbnail while a transition
    //     is still running, the pending transition is cancelled and a
    //     fresh one starts immediately.
    window.dgChangeImage = function (btn) {
        var fullSrc   = btn.dataset.full;
        var mainImg   = document.getElementById('dg-main-image');
        if (!mainImg || !fullSrc) return;

        // Guard: clicking the already-active thumbnail does nothing.
        if (btn.classList.contains('is-active')) return;

        // Cancel any transition already in progress so fast
        // sequential clicks never leave the image at opacity 0.
        if (mainImg._dgSwapTimer) {
            clearTimeout(mainImg._dgSwapTimer);
            mainImg._dgSwapTimer = null;
        }
        if (mainImg._dgSwapLoadHandler) {
            mainImg.removeEventListener('load', mainImg._dgSwapLoadHandler);
            mainImg._dgSwapLoadHandler = null;
        }

        // ── Update thumbnail active states (immediate, no animation) ──
        var thumbnails = document.querySelectorAll('.thumbnail-btn');
        for (var i = 0; i < thumbnails.length; i++) {
            var thumb = thumbnails[i];
            thumb.classList.remove(
                'is-active', 'border-primary', 'ring-2',
                'ring-primary-container/20', 'opacity-100'
            );
            thumb.classList.add('border-outline-variant/30', 'opacity-60');
        }
        btn.classList.add(
            'is-active', 'border-primary', 'ring-2',
            'ring-primary-container/20', 'opacity-100'
        );
        btn.classList.remove('border-outline-variant/30', 'opacity-60');

        // ── Swap the main image ───────────────────────────────────────
        var preloader = new Image();
        preloader.src = fullSrc;

        if (preloader.complete && preloader.naturalWidth > 0) {
            // Already in cache — swap + fade in immediately.
            mainImg.style.opacity = '0';
            mainImg.src = fullSrc;
            mainImg.style.opacity = '1';
        } else {
            // Not cached yet — fade to 0, wait for load, then fade in.
            mainImg.style.opacity = '0';
            mainImg._dgSwapLoadHandler = function () {
                mainImg.src = fullSrc;
                mainImg.style.opacity = '1';
                mainImg._dgSwapLoadHandler = null;
            };
            mainImg.addEventListener('load', mainImg._dgSwapLoadHandler);
        }
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
