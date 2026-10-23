/**
 * Dragon Glow — Account Register Page: Brand Shine Autoplay
 *
 * Plays the crest-header shimmer sweep once on page load, reusing the exact
 * same CSS transition as hover (`.is-shine-once` in register.css mirrors the
 * `:hover` / `:has()` rules). Works at every breakpoint because the swept
 * property (background-position) is breakpoint-independent.
 *
 * The crest header carries no [data-sr] reveal, so the sweep plays shortly
 * after DOM ready. Respects `prefers-reduced-motion`: no autoplay, hover
 * still works.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	var SHINE_CLASS = 'is-shine-once';
	var HOLD_MS = 700;
	var START_DELAY = 350;

	var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/**
	 * Add the shine class, then release it so hover keeps working.
	 *
	 * @param {HTMLElement} header Crest header element.
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
	 * Play the one-time shine sweep on the crest header.
	 *
	 * @return void
	 */
	function initBrandShine() {
		var header = document.querySelector('.dg-account-auth__crest-header');
		if (!header || prefersReduced) {
			return;
		}

		window.requestAnimationFrame(function () {
			window.setTimeout(function () {
				playShine(header);
			}, START_DELAY);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBrandShine);
	} else {
		initBrandShine();
	}
})();
