/**
 * Dragon Glow — Account Register Page: Page Load Animations
 *
 * Simulates a single hover animation on brand logo and title when the
 * register page loads. Applies `.is-animate` class to trigger one-time
 * CSS transition, then removes it to allow hover interactions to work freely.
 *
 * @package Dragon_Glow
 */

(() => {
	'use strict';

	/**
	 * Play one-time on-load animation for logo + title
	 */
	function initOnPageLoad() {
		const brandLogo = document.querySelector('.dg-account-auth__brand-logo');
		const brandTitle = document.querySelector('.dg-account-auth__brand-title');

		// Ensure both elements exist
		if (!brandLogo || !brandTitle) return;

		// Apply .is-animate class with slight delay for better perception
		// Logo animates first (50ms)
		setTimeout(() => {
			brandLogo.classList.add('is-animate');
		}, 50);

		// Title animates 20ms later for staggered effect
		setTimeout(() => {
			brandTitle.classList.add('is-animate');
		}, 70);

		// Remove animation class after transition completes (600ms + buffer)
		// This allows hover to work freely without interference
		setTimeout(() => {
			brandLogo.classList.remove('is-animate');
			brandTitle.classList.remove('is-animate');
		}, 700);
	}

	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initOnPageLoad);
	} else {
		setTimeout(initOnPageLoad, 0);
	}
})();
