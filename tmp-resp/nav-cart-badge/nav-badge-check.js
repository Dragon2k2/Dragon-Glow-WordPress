/**
 * Verify the combined nav badge (.dg-nav-badge) on the mobile/tablet menu
 * toggle + the wishlist badge in the drawer.
 *
 * Checks:
 *   1. Initial state: badge shows cart(3) + wishlist(1) = 4.
 *   2. Wishlist toggle (optimistic +1) → badge becomes 5.
 *   3. Cart change (+2) → badge becomes 7.
 *   4. Both back to 0 → badge hidden (display:none), not just `.hidden`.
 *   5. aria-label rebuilt on the toggle and on the drawer "Wishlist" link.
 *   6. Drawer wishlist badge sits at the trailing edge (margin-left:auto).
 *   7. Desktop (>=1024px) is untouched by the new rules.
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file://' + path.resolve(__dirname, 'nav-badge.html').replace(/\\/g, '/');
const WIDTHS = [320, 390, 768, 1023, 1024, 1280];

(async () => {
	const browser = await chromium.launch();
	const results = [];

	for (const width of WIDTHS) {
		const page = await browser.newPage({ viewport: { width, height: 800 } });
		await page.goto(FILE);
		await page.waitForTimeout(400);

		const isMobile = width < 1024;

		const read = () =>
			page.evaluate(() => {
				const badge = document.querySelector('.dg-nav-badge');
				const toggle = document.getElementById('dg-mobile-menu-toggle');
				const cs = badge ? getComputedStyle(badge) : null;
				const wlLink = document.querySelector('.dg-mobile-menu-item--wishlist a');
				const wlBadge = document.querySelector('.dg-mobile-menu-item--wishlist .dg-wishlist-count');
				const wlCs = wlBadge ? getComputedStyle(wlBadge) : null;
				return {
					badgeText: badge ? badge.textContent.trim() : null,
					badgeHiddenClass: badge ? badge.classList.contains('hidden') : null,
					badgeDisplay: cs ? cs.display : null,
					badgeW: badge ? badge.getBoundingClientRect().width : null,
					badgeH: badge ? badge.getBoundingClientRect().height : null,
					toggleAria: toggle ? toggle.getAttribute('aria-label') : null,
					wlLinkAria: wlLink ? wlLink.getAttribute('aria-label') : null,
					wlBadgeText: wlBadge ? wlBadge.textContent.trim() : null,
					wlBadgeDisplay: wlCs ? wlCs.display : null,
					wlBadgeMarginLeft: wlCs ? wlCs.marginLeft : null,
					wlBadgePos: wlCs ? wlCs.position : null,
				};
			});

		const initial = await read();

		// 2. Wishlist +1 (simulates clicking a .dg-wishlist-toggle).
		await page.evaluate(() => {
			if (window.DGNavBadge) window.DGNavBadge.setWishlist(2);
		});
		await page.waitForTimeout(120);
		const afterWishlist = await read();

		// 3. Cart +2 (simulates adding to the bag).
		await page.evaluate(() => {
			if (window.DGCartBadge) window.DGCartBadge.paint(5);
		});
		await page.waitForTimeout(120);
		const afterCart = await read();

		// 4. Both to zero → badge must be display:none.
		await page.evaluate(() => {
			if (window.DGCartBadge) window.DGCartBadge.paint(0);
			if (window.DGNavBadge) window.DGNavBadge.setWishlist(0);
		});
		await page.waitForTimeout(120);
		const afterZero = await read();

		// 5. Drawer wishlist badge paints from the wishlist module path.
		await page.evaluate(() => {
			document.querySelectorAll('.dg-wishlist-count').forEach(function (el) {
				el.textContent = '7';
				el.classList.remove('hidden');
			});
		});

		results.push({ width, isMobile, initial, afterWishlist, afterCart, afterZero });

		if (isMobile) {
			await page.screenshot({ path: `nav-badge-${width}.png` });
		}

		await page.close();
	}

	await browser.close();
	console.log(JSON.stringify(results, null, 2));
})();
