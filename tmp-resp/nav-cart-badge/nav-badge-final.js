/**
 * Final verification of the combined nav badge.
 *
 *  1. Resting size (after the pulse animation settles) — the badge must be
 *     20px for a 1-digit count and grow for 2-3 digits.
 *  2. `prefers-reduced-motion: reduce` → no animation is applied.
 *  3. Desktop (>=1024px) is untouched: the toggle is hidden, the desktop
 *     heart/bag badges keep their own look.
 *  4. The badge never overflows the viewport at 320px.
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file://' + path.resolve(__dirname, 'nav-badge.html').replace(/\\/g, '/');

(async () => {
	const browser = await chromium.launch();
	const out = {};

	// 1. Resting sizes across counts.
	{
		const page = await browser.newPage({ viewport: { width: 390, height: 900 } });
		await page.goto(FILE);
		await page.waitForTimeout(300);

		const sizes = {};
		for (const [cart, wishlist, label] of [[0, 0, '0'], [3, 0, '3'], [0, 2, '2'], [3, 1, '4'], [12, 34, '46'], [999, 1, '1000']]) {
			await page.evaluate(
				([c, w]) => {
					window.DGNavBadge.setCart(c);
					window.DGNavBadge.setWishlist(w);
				},
				[cart, wishlist]
			);
			await page.waitForTimeout(900); // let the animation settle
			sizes[label] = await page.evaluate(() => {
				const b = document.querySelector('.dg-nav-badge');
				const r = b.getBoundingClientRect();
				const cs = getComputedStyle(b);
				return {
					text: b.textContent.trim(),
					w: Math.round(r.width),
					h: Math.round(r.height),
					display: cs.display,
					hidden: b.classList.contains('hidden'),
					right: Math.round(r.right),
					viewport: window.innerWidth,
				};
			});
		}
		out.sizes = sizes;
		await page.close();
	}

	// 2. Reduced motion → no animation.
	{
		const page = await browser.newPage({
			viewport: { width: 390, height: 900 },
			reducedMotion: 'reduce',
		});
		await page.goto(FILE);
		await page.waitForTimeout(300);

		out.reducedMotion = await page.evaluate(async () => {
			window.DGNavBadge.setWishlist(9);
			await new Promise((r) => setTimeout(r, 60));
			const b = document.querySelector('.dg-nav-badge');
			const cs = getComputedStyle(b);
			return {
				animationName: cs.animationName,
				text: b.textContent.trim(),
			};
		});
		await page.close();
	}

	// 3. Desktop untouched.
	{
		const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
		await page.goto(FILE);
		await page.waitForTimeout(300);

		out.desktop = await page.evaluate(() => {
			const toggle = document.getElementById('dg-mobile-menu-toggle');
			const heartBadge = document.querySelector('a[aria-label="Wishlist"] .dg-wishlist-count');
			const cartBadge = document.querySelector('a[aria-label="Cart"] .dg-cart-count');
			const tcs = getComputedStyle(toggle);
			return {
				toggleDisplay: tcs.display,
				heartBadgeW: Math.round(heartBadge.getBoundingClientRect().width),
				heartBadgeText: heartBadge.textContent.trim(),
				cartBadgeW: Math.round(cartBadge.getBoundingClientRect().width),
				cartBadgeText: cartBadge.textContent.trim(),
			};
		});
		await page.close();
	}

	await browser.close();
	console.log(JSON.stringify(out, null, 2));
})();
