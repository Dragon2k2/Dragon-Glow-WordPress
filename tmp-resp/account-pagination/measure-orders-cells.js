/**
 * Đo vị trí từng phần tử trong card đơn hàng ở mobile.
 * Chạy: node tmp-resp/account-pagination/measure-orders-cells.js
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file:///' + path.join(__dirname, 'orders-mobile-mock.html').replace(/\\/g, '/');

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage({ deviceScaleFactor: 1 });
	await page.setViewportSize({ width: 390, height: 900 });
	await page.goto(FILE, { waitUntil: 'load' });
	await page.waitForTimeout(250);

	const r = await page.evaluate(() => {
		const row = document.querySelector('tbody tr');
		const out = [];
		[...row.children].forEach((c) => {
			const b = c.getBoundingClientRect();
			out.push({
				tag: c.tagName.toLowerCase(),
				cls: c.className.match(/__cell-([a-z-]+)/)?.[1] || '',
				title: c.getAttribute('data-title'),
				x: Math.round(b.x), y: Math.round(b.y), w: Math.round(b.width), h: Math.round(b.height),
				children: [...c.children].map((k) => {
					const kb = k.getBoundingClientRect();
					return k.tagName.toLowerCase() + '.' + (k.className || '').split(' ')[0] + ` @(${Math.round(kb.x)},${Math.round(kb.y)}) ${Math.round(kb.width)}×${Math.round(kb.height)}`;
				}),
				text: c.textContent.trim().replace(/\s+/g, ' '),
			});
		});
		const badges = [...document.querySelectorAll('.order-status')].map((s) => {
			const b = s.getBoundingClientRect();
			return `@(${Math.round(b.x)},${Math.round(b.y)}) ${Math.round(b.width)}×${Math.round(b.height)} "${s.textContent.trim()}"`;
		});
		return { out, badges };
	});

	console.log('--- cells ---');
	for (const c of r.out) {
		console.log(`${c.tag} ${c.cls.padEnd(14)} title=${String(c.title).padEnd(8)} @(${c.x},${c.y}) ${c.w}×${c.h}`);
		for (const k of c.children) console.log(`      ↳ ${k}`);
	}
	console.log('--- badges ---');
	r.badges.forEach((b) => console.log('  ' + b));

	await browser.close();
})();
