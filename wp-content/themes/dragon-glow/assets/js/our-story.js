/**
 * Dragon Glow — Our Story JS
 * Reveal-on-scroll observer (uses class `visible`) + smooth scroll for anchor
 * links. Newsletter form is handled by the shared `dg-newsletter` lib
 * (registered globally); only page-specific behavior lives here.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

        // ── Reveal on scroll ──────────────────────────────────────
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px' });

            document.querySelectorAll('.reveal-on-scroll').forEach(function (el) {
                observer.observe(el);
            });
        } else {
            // Fallback: reveal everything immediately.
            document.querySelectorAll('.reveal-on-scroll').forEach(function (el) {
                el.classList.add('visible');
            });
        }

        // Respect reduced motion: skip smooth scroll.
        if (prefersReduced) return;

        // ── Smooth scroll for anchor links (#philosophy etc.) ────
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var targetId = this.getAttribute('href');
                if (!targetId || targetId === '#') return;
                var target = document.querySelector(targetId);
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    });
})();
