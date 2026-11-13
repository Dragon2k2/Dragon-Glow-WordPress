/**
 * Đo layout bảng đơn hàng trong mock HTML ở nhiều viewport.
 * Chạy: node tmp-resp/account-pagination/measure-orders-mobile.js
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file:///' + path.join(__dirname, 'orders-mobile-mock.html').replace(/\\/g, '/');
const VIEWPORTS = [320, 375, 390, 414, 480, 640, 768, 1024, 1280, 1440];

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage();
	const out = [];

	for (const w of VIEWPORTS) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(FILE, { waitUntil: 'load' });
		await page.waitForTimeout(250);

		const r = await page.evaluate(() => {
			const table = document.querySelector('.woocommerce-orders-table');
			const row = table.querySelector('tbody tr');
			const th = row.querySelector('th');
			const tds = [...row.querySelectorAll('td')];
			const cells = [th, ...tds].map((c) => {
				const b = c.getBoundingClientRect();
				const cs = getComputedStyle(c);
				return {
					tag: c.tagName.toLowerCase(),
					cls: c.className.replace('woocommerce-orders-table__cell ', '').replace('woocommerce-orders-table__cell', 'cell'),
					title: c.getAttribute('data-title'),
					display: cs.display,
					textAlign: cs.textAlign,
					before: getComputedStyle(c, '::before').content,
					x: Math.round(b.x),
					y: Math.round(b.y),
					w: Math.round(b.width),
					h: Math.round(b.height),
				};
			});
			const tr = row.getBoundingClientRect();
			return {
				tableW: Math.round(table.getBoundingClientRect().width),
				rowW: Math.round(tr.width),
				rowH: Math.round(tr.height),
				theadDisplay: getComputedStyle(table.querySelector('thead')).display,
				docOverflow: Math.round(document.documentElement.scrollWidth - document.documentElement.clientWidth),
				cells,
			};
		});

		out.push({ w, ...r });
		console.log(`\n=== ${w}px | table ${r.tableW}px | row ${r.rowW}×${r.rowH} | thead:${r.theadDisplay} | docOverflow:${r.docOverflow} ===`);
		for (const c of r.cells) {
			console.log(`   ${c.tag.padEnd(3)} ${(c.title || '-').padEnd(8)} ${c.display.padEnd(10)} align=${c.textAlign.padEnd(6)} before=${JSON.stringify(c.before).padEnd(14)} @(${c.x},${c.y}) ${c.w}×${c.h}`);
		}
	}

	await browser.close();
})();
