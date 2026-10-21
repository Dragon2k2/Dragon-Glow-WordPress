/**
 * Dragon Glow — Account Login Page: Page Load Animations
 *
 * Simulates a single hover animation on brand logo and title when the
 * login page loads. Applies `.is-animate` class to trigger one-time
 * CSS transition, then removes it to allow hover interactions to work freely.
 *
 * @package Dragon_Glow
 */

(() => {
	'use strict';

	// Store the scroll position before disabling restoration
	const savedScrollPosition = window.scrollY || window.pageYOffset;

	// Disable browser's automatic scroll restoration on page load
	window.history.scrollRestoration = 'manual';

	/**
	 * Play one-time on-load animation for logo + title
	 */
	function initOnPageLoad() {
		// Set scroll-behavior to auto to prevent smooth scroll during page load
		document.documentElement.style.scrollBehavior = 'auto';
		document.body.style.scrollBehavior = 'auto';

		const brandLogo = document.querySelector('.dg-login__brand-logo');
		const brandTitle = document.querySelector('.dg-account-auth__brand-title');

		if (!brandLogo || !brandTitle) {
			return;
		}

		// Apply .is-animate class with slight delay for better perception
		setTimeout(() => {
			brandLogo.classList.add('is-animate');
		}, 50);

		// Title animates 20ms later for staggered effect
		setTimeout(() => {
			brandTitle.classList.add('is-animate');
		}, 70);

		// Remove animation class after transition completes and restore scroll position
		setTimeout(() => {
			brandLogo.classList.remove('is-animate');
			brandTitle.classList.remove('is-animate');

			// Restore the saved scroll position
			window.scrollTo(0, savedScrollPosition);

			// Re-enable scroll restoration for future navigations
			window.history.scrollRestoration = 'auto';
		}, 700);
	}

	// Run as early as possible
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initOnPageLoad);
	} else {
		initOnPageLoad();
	}
})();
