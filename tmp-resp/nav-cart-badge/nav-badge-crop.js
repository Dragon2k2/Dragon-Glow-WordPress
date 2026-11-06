/**
 * Crop screenshots of the menu toggle + the drawer rows so the badge
 * position can be inspected visually.
 */
const { chromium } = require('playwright');
const path = require('path');
const sharp = require('sharp');

const FILE = 'file://' + path.resolve(__dirname, 'nav-badge.html').replace(/\\/g, '/');
const WIDTHS = [320, 390, 768, 1023];

(async () => {
	const browser = await chromium.launch();

	for (const width of WIDTHS) {
		const page = await browser.newPage({ viewport: { width, height: 900 } });
		await page.goto(FILE);
		await page.waitForTimeout(400);

		// Toggle region.
		const toggle = await page.$('#dg-mobile-menu-toggle');
		const box = await toggle.boundingBox();
		await page.screenshot({
			path: `crop-toggle-${width}.png`,
			clip: {
				x: Math.max(0, box.x - 14),
				y: Math.max(0, box.y - 14),
				width: box.width + 28,
				height: box.height + 28,
			},
		});

		// Drawer rows region.
		const drawer = await page.$('.dg-mobile-menu__list');
		const dbox = await drawer.boundingBox();
		await page.screenshot({
			path: `crop-drawer-${width}.png`,
			clip: { x: dbox.x, y: dbox.y, width: dbox.width, height: dbox.height },
		});

		await page.close();
	}

	await browser.close();

	// Upscale the crops so the 20px badge is readable.
	for (const width of WIDTHS) {
		for (const kind of ['toggle', 'drawer']) {
			const src = `crop-${kind}-${width}.png`;
			const dst = `crop-${kind}-${width}-zoom.png`;
			const meta = await sharp(src).metadata();
			await sharp(src)
				.resize(meta.width * 3, meta.height * 3, { kernel: 'nearest' })
				.png()
				.toFile(dst);
		}
	}

	console.log('done');
})();
