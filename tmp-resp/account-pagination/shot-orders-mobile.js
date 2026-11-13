/**
 * Chụp ảnh mock bảng đơn hàng ở nhiều viewport để soi bằng mắt.
 * Chạy: node tmp-resp/account-pagination/shot-orders-mobile.js
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file:///' + path.join(__dirname, 'orders-mobile-mock.html').replace(/\\/g, '/');
const OUT = __dirname;
const TAG = process.argv[2] || 'before';

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage({ deviceScaleFactor: 2 });

	for (const w of [320, 390, 640, 768]) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(FILE, { waitUntil: 'load' });
		await page.waitForTimeout(250);
		await page.locator('.woocommerce-orders-table').screenshot({ path: path.join(OUT, `orders-${TAG}-${w}.png`) });
		console.log(`shot ${TAG}-${w}.png`);
	}

	await browser.close();
})();
