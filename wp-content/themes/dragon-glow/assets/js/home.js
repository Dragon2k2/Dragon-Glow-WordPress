/**
 * Dragon Glow — Home Page Motion Animations
 * ES Module — Motion (motion.dev) vanilla API. KHÔNG React.
 * 
 * Gồm:
 *   - Hero image hover: spring scale + shadow glow + overlay tint
 * 
 * Tôn trọng prefers-reduced-motion: skip animation khi user yêu cầu.
 *
 * @package Dragon_Glow
 */

'use strict';

import { animate } from 'https://cdn.jsdelivr.net/npm/motion@11/+esm';

(function () {
	/* ── Guard: respect prefers-reduced-motion ─────────────── */
	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	
	/* ── Easing for smooth, sophisticated feel ─────────────── */
	const EASE_OUT = 'easeOut';
	const MOTION_DURATION = 0.5; // seconds

	/**
	 * Initialize hero image hover animations
	 * Triggers on mouseenter/mouseleave of .dg-home-hero__product-frame
	 */
	function initHeroImageHover() {
		const productFrame = document.querySelector('.dg-home-hero__product-frame');
		if (!productFrame) return;

		const image = productFrame.querySelector('[data-hero-img]');
		const overlay = productFrame; // ::after pseudo-element on frame
		if (!image) return;

		/**
		 * On mouseenter: scale up image + intensify shadow + add overlay tint
		 */
		productFrame.addEventListener('mouseenter', () => {
			if (prefersReduced) {
				// Instant state: add hover class, no animation
				productFrame.classList.add('is-hover');
				return;
			}

			// Animate image scale with spring physics
			animate(
				image,
				{ 
					scale: [1, parseFloat(getComputedStyle(productFrame).getPropertyValue('--hero-image-hover-scale') || 1.05)]
				},
				{
					type: 'spring',
					stiffness: 300,
					damping: 20,
					duration: MOTION_DURATION
				}
			);

			// Animate overlay opacity (tint) — use Motion for smooth easing
			animate(
				overlay,
				{ 
					// Use CSS custom property via setProperty
					'--overlay-opacity': [0, 0.08]
				},
				{
					duration: MOTION_DURATION,
					ease: EASE_OUT
				}
			).then(() => {
				// After animation, add class for shadow (CSS rule handles it)
				productFrame.classList.add('is-hover');
			});
		});

		/**
		 * On mouseleave: scale back down + reduce shadow + remove overlay
		 */
		productFrame.addEventListener('mouseleave', () => {
			if (prefersReduced) {
				// Instant state: remove hover class
				productFrame.classList.remove('is-hover');
				return;
			}

			// Reset image scale
			animate(
				image,
				{ 
					scale: [parseFloat(getComputedStyle(productFrame).getPropertyValue('--hero-image-hover-scale') || 1.05), 1]
				},
				{
					type: 'spring',
					stiffness: 300,
					damping: 20,
					duration: MOTION_DURATION
				}
			);

			// Reset overlay opacity
			animate(
				overlay,
				{ 
					'--overlay-opacity': [0.08, 0]
				},
				{
					duration: MOTION_DURATION,
					ease: EASE_OUT
				}
			).then(() => {
				// After animation, remove hover class
				productFrame.classList.remove('is-hover');
			});
		});
	}

	/* ── Initialize on DOM ready ─────────────── */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initHeroImageHover);
	} else {
		initHeroImageHover();
	}
})();
