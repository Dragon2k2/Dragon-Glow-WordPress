/**
 * Dragon Glow — Main JS
 * Core UI: scroll reveal, parallax hero, blob parallax, carousel, mobile menu,
 * mobile filter toggle, accordion.
 *
 * Cart feedback (quick-add, cart count, wishlist) lives in lib/cart-feedback.js.
 * Newsletter forms live in lib/newsletter.js.
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