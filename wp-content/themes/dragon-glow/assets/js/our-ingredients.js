/**
 * Dragon Glow — Our Ingredients
 * ES Module — Motion vanilla API (motion.dev). KHÔNG React.
 *
 * Gồm: reveal animations, scroll-triggered fade+translate,
 * ingredient tile hover scale (CSS-driven), image parallax on scroll.
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */
import { animate, inView, stagger } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
	'use strict';

	const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const root = document.querySelector('[data-page="our-ingredients"]');
	if (!root) {
		return;
	}

	const EASE = [0.2, 0, 0, 1];

	initReveal();
	initSmoothScroll();
	initParallax();

	/* ── Reveal: stagger các phần tử data-sr ──────────────────────────────── */
	function initReveal() {
		// Individual [data-sr] elements
		root.querySelectorAll('[data-sr]').forEach(function (el) {
			if (reduce) {
				el.style.opacity = '1';
				return;
			}
			inView(el, function () {
				const delay = parseInt(el.getAttribute('data-sr-delay') || '0', 10) / 1000;
				animate(el, { opacity: [0, 1], y: [22, 0] }, {
					duration: 0.65,
					ease: EASE,
					delay: delay
				});
			}, { amount: 0.15 });
		});
	}

	/* ── Smooth scroll: nút "See the sources" → #traceable ───────────────── */
	function initSmoothScroll() {
		const btn = root.querySelector('a[href="#traceable"]');
		if (!btn) {
			return;
		}
		btn.addEventListener('click', function (e) {
			const target = document.getElementById('traceable');
			if (!target) {
				return;
			}
			e.preventDefault();
			if (reduce) {
				target.scrollIntoView();
				target.focus({ preventScroll: true });
				return;
			}
			const top = target.getBoundingClientRect().top + window.scrollY - 80;
			animate(window.scrollY, top, {
				duration: 0.8,
				ease: EASE,
				onUpdate: function (v) { window.scrollTo(0, v); },
				onComplete: function () { target.focus({ preventScroll: true }); }
			});
		});
	}

	/* ── Parallax: ảnh hero + traceable dịch chuyển nhẹ theo scroll ─────── */
	function initParallax() {
		if (reduce) {
			return;
		}
		const heroImg = root.querySelector('.dg-oi-hero-image img');
		if (heroImg) {
			inView(heroImg, function () {
				import("https://cdn.jsdelivr.net/npm/motion@11/+esm").then(function (motion) {
					motion.scroll(
						motion.animate(heroImg, { y: [-60, 60] }, { duration: 1, ease: 'linear' }),
						{ target: heroImg.closest('.dg-oi-hero-image'), offset: ['start end', 'end start'] }
					);
				});
			}, { amount: 0 });
		}
	}
})();
