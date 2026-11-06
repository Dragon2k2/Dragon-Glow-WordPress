/**
 * Measure the drawer badge alignment: is the pill flush with the right edge
 * of its row (and of the drawer)?
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file://' + path.resolve(__dirname, 'nav-badge.html').replace(/\\/g, '/');

(async () => {
	const browser = await chromium.launch();
	const out = [];

	for (const width of [320, 390, 768, 1023]) {
		const page = await browser.newPage({ viewport: { width, height: 900 } });
		await page.goto(FILE);
		await page.waitForTimeout(300);

		const data = await page.evaluate(() => {
			const rows = Array.from(document.querySelectorAll('.dg-mobile-menu__list a'));
			return rows.map(function (row) {
				const badge = row.querySelector('.dg-cart-count, .dg-wishlist-count');
				const rb = row.getBoundingClientRect();
				const bb = badge ? badge.getBoundingClientRect() : null;
				return {
					label: row.textContent.trim(),
					rowRight: Math.round(rb.right),
					rowLeft: Math.round(rb.left),
					badgeRight: bb ? Math.round(bb.right) : null,
					badgeLeft: bb ? Math.round(bb.left) : null,
					gapToRowRight: bb ? Math.round(rb.right - bb.right) : null,
					badgeText: badge ? badge.textContent.trim() : null,
					badgeW: bb ? Math.round(bb.width) : null,
					badgeH: bb ? Math.round(bb.height) : null,
				};
			});
		});

		out.push({ width, rows: data });
		await page.close();
	}

	await browser.close();
	console.log(JSON.stringify(out, null, 2));
})();
