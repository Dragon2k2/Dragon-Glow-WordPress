/**
 * Verification — shared image lightbox: focus restore + a11y + responsive
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now();

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const out = {};

	// ── Focus restore ──
	{
		const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
		await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

		// Click main image (an <img> — not focusable by click).
		await page.click('#dg-main-image');
		await page.waitForTimeout(400);
		out.focusAfterOpen = await page.evaluate(() => document.activeElement.className);
		await page.keyboard.press('Escape');
		await page.waitForTimeout(300);
		out.focusRestoredToMainImg = await page.evaluate(() => document.activeElement.id);

		// Click zoom button.
		await page.click('.dg-zoom-btn');
		await page.waitForTimeout(400);
		await page.keyboard.press('Escape');
		await page.waitForTimeout(300);
		out.focusRestoredToBtn = await page.evaluate(() => document.activeElement.className.includes('dg-zoom-btn'));

		// Focus trap: Tab from last focusable should wrap to first.
		await page.click('#dg-main-image');
		await page.waitForTimeout(400);
		out.trapFirst = await page.evaluate(() => document.activeElement.className);
		await page.close();
	}

	// ── Responsive: mobile nav placement ──
	for (const w of [390, 768, 1280]) {
		const page = await browser.newPage({ viewport: { width: w, height: 900 } });
		await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
		await page.click('#dg-main-image');
		await page.waitForTimeout(500);
		out['nav_' + w] = await page.evaluate(() => {
			const m = document.querySelector('[data-dg-zoom-root]');
			const prev = m.querySelector('[data-dg-zoom-prev]');
			const next = m.querySelector('[data-dg-zoom-next]');
			const counter = m.querySelector('[data-dg-zoom-counter]');
			const img = m.querySelector('[data-dg-zoom-image]');
			const r = (el) => { const b = el.getBoundingClientRect(); return { x: Math.round(b.x), y: Math.round(b.y), w: Math.round(b.width), h: Math.round(b.height) }; };
			return {
				modalDisplay: getComputedStyle(m).display,
				prev: r(prev), next: r(next), counter: r(counter),
				img: r(img),
				viewport: { w: window.innerWidth, h: window.innerHeight }
			};
		});
		await page.screenshot({ path: `tmp-resp/zoom-lightbox/shot-${w}.png` });
		await page.close();
	}

	console.log(JSON.stringify(out, null, 2));
	await browser.close();
})();
