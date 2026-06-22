/**
 * Dragon Glow — Main JS
 * Scroll reveal, parallax hero, blob parallax, carousel, mobile menu, AJAX cart feedback.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Scroll Reveal ──────────────────────────────────────────
    function revealOnScroll() {
        const reveals = document.querySelectorAll('.reveal');
        reveals.forEach(function (el) {
            const top = el.getBoundingClientRect().top;
            if (top < window.innerHeight - 100) {
                el.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', revealOnScroll, { passive: true });
    window.addEventListener('load', revealOnScroll);

    // ── Hero Parallax ──────────────────────────────────────────
    let ticking = false;
    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                document.documentElement.style.setProperty('--scroll-y', window.scrollY * 0.3 + 'px');
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });

    // ── Blob Parallax on Mouse ──────────────────────────────────
    document.addEventListener('mousemove', function (e) {
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;
        const blobs = document.querySelectorAll('.ethereal-blob');
        blobs.forEach(function (blob, i) {
            const speed = (i + 1) * 18;
            blob.style.transform = 'translate(' + (x * speed) + 'px, ' + (y * speed) + 'px)';
        });
    });

    // ── Carousel (Best Sellers) ────────────────────────────────
    var carousel = document.getElementById('dg-carousel');
    var prevBtn = document.getElementById('dg-prev-btn');
    var nextBtn = document.getElementById('dg-next-btn');

    if (carousel && nextBtn && prevBtn) {
        nextBtn.addEventListener('click', function () {
            carousel.scrollBy({ left: 320, behavior: 'smooth' });
        });
        prevBtn.addEventListener('click', function () {
            carousel.scrollBy({ left: -320, behavior: 'smooth' });
        });
    }

    // ── Mobile Nav Toggle ──────────────────────────────────────
    var menuToggle = document.getElementById('dg-mobile-menu-toggle');
    var mobileMenu = document.getElementById('dg-mobile-menu');

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
            var isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isExpanded);
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (e) {
            if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

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

    // ── AJAX: Wishlist Toggle ──────────────────────────────────
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.dg-wishlist-btn');
        if (!btn) return;
        e.preventDefault();

        var productId = btn.dataset.productId;
        var icon = btn.querySelector('.material-symbols-outlined');

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
                var added = data.data.added;
                if (icon) {
                    icon.style.fontVariationSettings = "'FILL' " + (added ? '1' : '0');
                }

                // Update button text if applicable
                if (btn.dataset.wishlistText) {
                    btn.textContent = added ? btn.dataset.wishlistSavedText : btn.dataset.wishlistText;
                }
            } else if (data.data && data.data.redirect) {
                window.location.href = data.data.redirect;
            }
        })
        .catch(function (err) {
            console.error('Wishlist error:', err);
        });
    });

    // ── Newsletter (footer) ────────────────────────────────────
    var subscribeBtn = document.getElementById('footer-subscribe-btn');
    if (subscribeBtn) {
        subscribeBtn.addEventListener('click', function () {
            var emailInput = document.getElementById('footer-email');
            var email = emailInput ? emailInput.value.trim() : '';
            if (!email) return;

            var formData = new FormData();
            formData.append('action', 'dg_newsletter');
            formData.append('email', email);
            formData.append('nonce', dgAjax.nonce);

            subscribeBtn.textContent = '...';

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    subscribeBtn.textContent = '✓';
                    if (emailInput) emailInput.value = '';
                    var msg = document.getElementById('footer-newsletter-msg');
                    if (msg) {
                        msg.textContent = data.data.message;
                        msg.classList.remove('hidden');
                        msg.classList.add('text-tertiary');
                    }
                } else {
                    subscribeBtn.textContent = '!';
                    var msg = document.getElementById('footer-newsletter-msg');
                    if (msg) {
                        msg.textContent = data.data.message;
                        msg.classList.remove('hidden');
                        msg.classList.add('text-error');
                    }
                }
            })
            .catch(function () {
                subscribeBtn.textContent = 'Join';
            });
        });
    }

    // ── Newsletter Form (dedicated) ───────────────────────────
    var newsletterForm = document.getElementById('dg-newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var emailInput = newsletterForm.querySelector('input[type="email"]');
            var email = emailInput ? emailInput.value.trim() : '';
            var btn = newsletterForm.querySelector('button[type="submit"]');

            if (!email) return;

            var originalText = btn.textContent;
            btn.textContent = '...';
            btn.disabled = true;

            var formData = new FormData();
            formData.append('action', 'dg_newsletter');
            formData.append('email', email);
            formData.append('nonce', dgAjax.nonce);

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var msg = document.getElementById('dg-newsletter-msg');
                if (msg) {
                    msg.textContent = data.data ? data.data.message : '';
                    msg.classList.remove('hidden', 'text-error', 'text-tertiary');
                    msg.classList.add(data.success ? 'text-tertiary' : 'text-error');
                }
                if (data.success) {
                    newsletterForm.reset();
                }
            })
            .finally(function () {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
    }

    // ── Filter Toggle (mobile) ────────────────────────────────
    var filterToggle = document.getElementById('dg-mobile-filter-toggle');
    var filterPanel = document.getElementById('dg-mobile-filter-panel');

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function () {
            filterPanel.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });

        var overlay = document.getElementById('dg-filter-overlay');
        var closeBtn = document.getElementById('dg-close-filter');

        if (overlay) {
            overlay.addEventListener('click', function () {
                filterPanel.classList.add('hidden');
                document.body.style.overflow = '';
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                filterPanel.classList.add('hidden');
                document.body.style.overflow = '';
            });
        }
    }

    // ── Accordion (FAQ) ────────────────────────────────────────
    document.querySelectorAll('.dg-accordion-item').forEach(function (item) {
        var trigger = item.querySelector('.dg-accordion-trigger');
        if (trigger) {
            trigger.addEventListener('click', function () {
                var isOpen = item.classList.contains('active');
                // Close all
                document.querySelectorAll('.dg-accordion-item').forEach(function (i) {
                    i.classList.remove('active');
                });
                // Open clicked (if was closed)
                if (!isOpen) {
                    item.classList.add('active');
                }
            });
        }
    });

    // Open first accordion by default on contact page
    var firstAccordion = document.querySelector('.dg-accordion-item');
    if (firstAccordion && !firstAccordion.classList.contains('active')) {
        // Only auto-open on contact page
        if (window.location.pathname.indexOf('contact') > -1) {
            firstAccordion.classList.add('active');
        }
    }

})();
