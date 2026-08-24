/**
 * Dragon Glow — Sustainability
 * ES Module — Motion vanilla API (motion.dev). KHÔNG React.
 *
 * Gồm: reveal-on-scroll cho [data-sr], hero image parallax nhẹ.
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */
import { animate, inView, scroll } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
	'use strict';

	const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const root = document.querySelector('[data-page="sustainability"]');
	if (!root) {
		return;
	}

	const EASE = [0.2, 0, 0, 1];

	initReveal();
	initHeroParallax();

	/* ── Reveal: stagger các phần tử data-sr ──────────────────────────────── */
	function initReveal() {
		root.querySelectorAll('[data-sr]').forEach(function (el) {
			if (reduce) {
				el.style.opacity = '1';
				return;
			}
			inView(el, function () {
				const delay = parseInt(el.getAttribute('data-sr-delay') || '0', 10) / 1000;
				animate(el, { opacity: [0, 1], y: [20, 0] }, {
					duration: 0.8,
					ease: EASE,
					delay: delay
				});
			}, { amount: 0.15 });
		});
	}

	/* ── Hero parallax: ảnh hero dịch nhẹ theo scroll ─────────────────────── */
	function initHeroParallax() {
		if (reduce) {
			return;
		}
		const heroWrap = root.querySelector('.dg-sus-hero-image');
		const heroImg = heroWrap && heroWrap.querySelector('img');
		if (!heroImg) {
			return;
		}
		inView(heroImg, function () {
			scroll(
				animate(heroImg, { y: [-40, 40] }, { duration: 1, ease: 'linear' }),
				{ target: heroWrap, offset: ['start end', 'end start'] }
			);
		}, { amount: 0 });
	}
})();