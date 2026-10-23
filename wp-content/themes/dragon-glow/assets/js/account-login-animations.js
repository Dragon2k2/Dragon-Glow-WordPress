/**
 * Dragon Glow — Account Login Page: Brand Shine Autoplay
 *
 * Plays the brand-header shimmer sweep once on page load, reusing the exact
 * same CSS transition as hover (`.is-shine-once` in login.css mirrors the
 * `:hover` / `:has()` rules). Works at every breakpoint because the swept
 * property (background-position) is breakpoint-independent.
 *
 * Waits for the [data-sr] reveal (account.js adds `.is-visible`) so the
 * sweep runs while the header is actually visible. Falls back to a timed
 * play when the reveal class never arrives (e.g. no IntersectionObserver).
 *
 * Respects `prefers-reduced-motion`: no autoplay, hover still works.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	var SHINE_CLASS = 'is-shine-once';
	var VISIBLE_CLASS = 'is-visible';
	var HOLD_MS = 700;
	var AFTER_VISIBLE_DELAY = 150;
	var FALLBACK_MS = 2000;

	var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/**
	 * Add the shine class, then release it so hover keeps working.
	 *
	 * @param {HTMLElement} header Brand header element.
	 * @return void
	 */
	function playShine(header) {
		if (header.classList.contains(SHINE_CLASS)) {
			return;
		}
		header.classList.add(SHINE_CLASS);
		window.setTimeout(function () {
			header.classList.remove(SHINE_CLASS);
		}, HOLD_MS);
	}

	/**
	 * Play the one-time shine sweep on the brand header.
	 *
	 * @return void
	 */
	function initBrandShine() {
		var header = document.querySelector('.dg-login__brand-header');
		if (!header || prefersReduced) {
			return;
		}

		// Already revealed — sweep on the next frame.
		if (header.classList.contains(VISIBLE_CLASS)) {
			window.requestAnimationFrame(function () {
				window.setTimeout(function () {
					playShine(header);
				}, AFTER_VISIBLE_DELAY);
			});
			return;
		}

		// Not revealed yet — wait for account.js to add `.is-visible`.
		var played = false;
		var observer = null;

		function playOnce() {
			if (played) {
				return;
			}
			played = true;
			if (observer) {
				observer.disconnect();
			}
			window.requestAnimationFrame(function () {
				window.setTimeout(function () {
					playShine(header);
				}, AFTER_VISIBLE_DELAY);
			});
		}

		if ('MutationObserver' in window) {
			observer = new MutationObserver(function () {
				if (header.classList.contains(VISIBLE_CLASS)) {
					playOnce();
				}
			});
			observer.observe(header, { attributes: true, attributeFilter: ['class'] });
		}

		// Fallback: play anyway so the sweep is never skipped silently.
		window.setTimeout(playOnce, FALLBACK_MS);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBrandShine);
	} else {
		initBrandShine();
	}
})();
