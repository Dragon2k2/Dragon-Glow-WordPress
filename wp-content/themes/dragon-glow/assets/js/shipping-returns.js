/**
 * Dragon Glow — Shipping & Returns Motion Animations
 * ES Module — uses Motion (motion.dev) vanilla JS API.
 * Kết hợp Motion cho scroll-linked + CSS cho micro-interactions.
 *
 * @package Dragon_Glow
 */
import { animate, inView, scroll } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
    'use strict';

    /* ── Guard: respect prefers-reduced-motion ──────────────────────────────────── */
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── Scroll-linked progress bar ─────────────────────────────────────────────── */
    function initScrollProgress() {
        const fill = document.querySelector('.dg-sr-progress-fill');
        if (!fill) return;
        if (prefersReduced) {
            fill.style.transform = 'scaleX(1)';
            return;
        }
        scroll(
            animate(fill, { scaleX: [0, 1] }, { duration: 1, ease: 'none' }),
            { target: document.documentElement }
        );
    }

    /* ── Scroll reveal: stagger group children ─────────────────────────────────── */
    function initGroupReveal() {
        const groups = document.querySelectorAll('[data-sr-group]');
        groups.forEach(function (group) {
            const children = Array.from(group.querySelectorAll(':scope > [data-sr]'));
            if (!children.length) return;

            inView(group, function () {
                if (prefersReduced) {
                    children.forEach(function (c) { c.style.opacity = '1'; });
                    return;
                }
                animate(children, { opacity: [0, 1], y: [28, 0] }, {
                    duration: 0.7,
                    ease: [0.16, 1, 0.3, 1],
                    delay: children.length > 1 ? { each: 0.1 } : 0,
                });
            }, { amount: 0.1 });
        });
    }

    /* ── Orphan [data-sr] nodes (outside any group) ──────────────────────────── */
    function initOrphanReveal() {
        const all     = document.querySelectorAll('[data-sr]');
        const grouped = new Set(document.querySelectorAll('[data-sr-group] [data-sr]'));
        const orphans = Array.from(all).filter(function (el) { return !grouped.has(el); });
        orphans.forEach(function (el) {
            inView(el, function () {
                if (prefersReduced) { el.style.opacity = '1'; return; }
                animate(el, { opacity: [0, 1], y: [28, 0] }, {
                    duration: 0.65,
                    ease: [0.16, 1, 0.3, 1],
                });
            }, { amount: 0.15 });
        });
    }

    /* ── Trust badges stagger ─────────────────────────────────────────────────── */
    function initTrustBadges() {
        const badges = document.querySelectorAll('.dg-trust-badge');
        if (!badges.length) return;

        inView('.dg-sr-trust-inner', function () {
            if (prefersReduced) {
                badges.forEach(function (b) { b.style.opacity = '1'; });
                return;
            }
            animate(badges, { opacity: [0, 1], y: [20, 0] }, {
                duration: 0.6,
                ease: [0.16, 1, 0.3, 1],
                delay: { each: 0.08 },
            });
        }, { amount: 0.2 });
    }

    /* ── Packaging pillar stagger ─────────────────────────────────────────────── */
    function initPackagingPillars() {
        const pillars = document.querySelectorAll('.dg-pillar-item');
        if (!pillars.length) return;

        inView('.dg-packaging-grid', function () {
            if (prefersReduced) {
                pillars.forEach(function (p) { p.style.opacity = '1'; });
                return;
            }
            animate(pillars, { opacity: [0, 1], y: [20, 0] }, {
                duration: 0.65,
                ease: [0.16, 1, 0.3, 1],
                delay: { each: 0.12 },
            });
        }, { amount: 0.15 });
    }

    /* ── Coverage items stagger ───────────────────────────────────────────────── */
    function initCoverageItems() {
        const items = document.querySelectorAll('.dg-coverage-item');
        if (!items.length) return;

        inView('.dg-coverage-grid', function () {
            if (prefersReduced) {
                items.forEach(function (i) { i.style.opacity = '1'; });
                return;
            }
            animate(items, { opacity: [0, 1], x: [-12, 0] }, {
                duration: 0.6,
                ease: [0.16, 1, 0.3, 1],
                delay: { each: 0.1 },
            });
        }, { amount: 0.15 });
    }

    /* ── Count-up animation (stats tiles) ─────────────────────────────────────── */
    function initCountUp() {
        const counters = document.querySelectorAll('.dg-count[data-count-to]');
        if (!counters.length) return;

        counters.forEach(function (el) {
            const target = parseInt(el.dataset.countTo, 10);
            if (isNaN(target)) return;

            inView(el, function () {
                if (prefersReduced) {
                    el.textContent = target;
                    return;
                }
                animate({ from: 0, to: target }, function (v) {
                    el.textContent = Math.round(v);
                }, {
                    duration: 1.8,
                    ease: 'easeOut',
                });
            }, { amount: 0.5 });
        });
    }

    /* ── Magnetic effect (free shipping tile + CTA tile) ─────────────────────── */
    function initMagnetic() {
        const tiles = document.querySelectorAll('[data-magnetic]');
        tiles.forEach(function (tile) {
            tile.addEventListener('mousemove', function (e) {
                if (prefersReduced) return;
                const rect = tile.getBoundingClientRect();
                const cx = rect.left + rect.width / 2;
                const cy = rect.top + rect.height / 2;
                const dx = (e.clientX - cx) / (rect.width / 2);
                const dy = (e.clientY - cy) / (rect.height / 2);
                tile.style.transform = 'translate(' + (dx * 6) + 'px, ' + (dy * 6) + 'px)';
            });
            tile.addEventListener('mouseleave', function () {
                tile.style.transform = '';
            });
        });
    }

    /* ── FAQ mini accordion ───────────────────────────────────────────────────── */
    function initFAQ() {
        const items = document.querySelectorAll('.dg-sr-faq-item');
        if (!items.length) return;

        items.forEach(function (item) {
            const trigger = item.querySelector('.dg-sr-faq-trigger');
            if (!trigger) return;

            trigger.addEventListener('click', function () {
                const isOpen = item.classList.contains('is-open');

                // Close all
                items.forEach(function (i) { i.classList.remove('is-open'); });

                // Open clicked (if was closed)
                if (!isOpen) {
                    item.classList.add('is-open');
                    if (!prefersReduced) {
                        // Focus the panel for accessibility
                        const panel = item.querySelector('.dg-sr-faq-panel');
                        if (panel) {
                            animate(panel, { opacity: [0.7, 1] }, { duration: 0.3, ease: 'easeOut' });
                        }
                    }
                }

                // Update ARIA
                const expanded = !isOpen;
                trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            });

            // Keyboard support: Enter/Space to toggle
            trigger.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    trigger.click();
                }
            });
        });

        // FAQ stagger reveal
        if (!prefersReduced) {
            inView('.dg-sr-faq-list', function () {
                animate(items, { opacity: [0, 1], y: [16, 0] }, {
                    duration: 0.55,
                    ease: [0.16, 1, 0.3, 1],
                    delay: { each: 0.07 },
                });
            }, { amount: 0.05 });
        } else {
            items.forEach(function (i) { i.style.opacity = '1'; });
        }
    }

    /* ── Boot ──────────────────────────────────────────────────────────────────── */
    initScrollProgress();
    initGroupReveal();
    initOrphanReveal();
    initTrustBadges();
    initPackagingPillars();
    initCoverageItems();
    initCountUp();
    initMagnetic();
    initFAQ();

})();
