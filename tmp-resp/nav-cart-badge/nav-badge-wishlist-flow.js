/**
 * End-to-end check of the wishlist flow:
 *   click .dg-wishlist-toggle
 *     → drawer "Wishlist" badge updates (1 → 2)
 *     → combined badge on the menu toggle updates (4 → 5)
 *     → aria-labels rebuilt on both hosts
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file://' + path.resolve(__dirname, 'nav-badge.html').replace(/\\/g, '/');

(async () => {
	const browser = await chromium.launch();
	const out = [];

	for (const width of [320, 390, 768, 1023, 1024]) {
		const page = await browser.newPage({ viewport: { width, height: 900 } });
		await page.goto(FILE);
		await page.waitForTimeout(400);

		const read = () =>
			page.evaluate(() => {
				const navBadge = document.querySelector('.dg-nav-badge');
				const toggle = document.getElementById('dg-mobile-menu-toggle');
				const wlBadge = document.querySelector('.dg-mobile-menu-item--wishlist .dg-wishlist-count');
				const wlLink = document.querySelector('.dg-mobile-menu-item--wishlist a');
				const heart = document.querySelector('.dg-wishlist-toggle');
				return {
					navBadgeText: navBadge ? navBadge.textContent.trim() : null,
					navBadgeHidden: navBadge ? navBadge.classList.contains('hidden') : null,
					toggleAria: toggle ? toggle.getAttribute('aria-label') : null,
					wlBadgeText: wlBadge ? wlBadge.textContent.trim() : null,
					wlBadgeHidden: wlBadge ? wlBadge.classList.contains('hidden') : null,
					wlLinkAria: wlLink ? wlLink.getAttribute('aria-label') : null,
					heartActive: heart ? heart.classList.contains('is-active') : null,
				};
			});

		const before = await read();

		await page.click('.dg-wishlist-toggle');
		await page.waitForTimeout(120);
		const optimistic = await read();

		// Wait past the 350ms debounce + the stubbed AJAX round-trip.
		await page.waitForTimeout(900);
		const confirmed = await read();

		out.push({ width, before, optimistic, confirmed });
		await page.close();
	}

	await browser.close();
	console.log(JSON.stringify(out, null, 2));
})();
