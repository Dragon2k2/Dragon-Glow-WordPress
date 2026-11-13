/**
 * Verification — shared image lightbox: Gift Cards regression
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/?v=' + Date.now();

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const out = {};
	const errors = [];

	for (const w of [390, 1280]) {
		const page = await browser.newPage({ viewport: { width: w, height: 900 } });
		page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));
		await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

		// Click gift card image → opens.
		await page.click('.dg-gift-card-image');
		await page.waitForTimeout(500);
		out['open_' + w] = await page.evaluate(() => {
			const m = document.querySelector('[data-dg-zoom-root]');
			const img = m.querySelector('[data-dg-zoom-image]');
			const prev = m.querySelector('[data-dg-zoom-prev]');
			return {
				isOpen: m.classList.contains('is-open'),
				counter: m.querySelector('[data-dg-zoom-counter]').textContent.trim(),
				imgSrc: img.getAttribute('src'),
				previewSrc: document.querySelector('.dg-gift-card-image').getAttribute('src'),
				prevVisible: !prev.hidden,
				prevRect: (() => { const b = prev.getBoundingClientRect(); return { w: Math.round(b.width), h: Math.round(b.height) }; })()
			};
		});

		// Next → syncs denomination state.
		await page.click('[data-dg-zoom-next]');
		await page.waitForTimeout(400);
		out['next_' + w] = await page.evaluate(() => {
			const m = document.querySelector('[data-dg-zoom-root]');
			const chip = document.querySelector('.dg-gift-value.is-active');
			const hidden = document.querySelector('input[name="value"]');
			return {
				counter: m.querySelector('[data-dg-zoom-counter]').textContent.trim(),
				imgSrc: m.querySelector('[data-dg-zoom-image]').getAttribute('src'),
				chipText: chip ? chip.textContent.trim() : null,
				hiddenValue: hidden ? hidden.value : null,
				previewSrc: document.querySelector('.dg-gift-card-image').getAttribute('src')
			};
		});

		// Close → state kept (no revert).
		await page.keyboard.press('Escape');
		await page.waitForTimeout(300);
		out['close_' + w] = await page.evaluate(() => {
			const m = document.querySelector('[data-dg-zoom-root]');
			const hidden = document.querySelector('input[name="value"]');
			return {
				isOpen: m.classList.contains('is-open'),
				hiddenValue: hidden ? hidden.value : null,
				previewSrc: document.querySelector('.dg-gift-card-image').getAttribute('src'),
				chipText: (() => { const c = document.querySelector('.dg-gift-value.is-active'); return c ? c.textContent.trim() : null; })()
			};
		});

		// Reopen → same index.
		await page.click('.dg-gift-card-image');
		await page.waitForTimeout(400);
		out['reopen_' + w] = await page.evaluate(() => document.querySelector('[data-dg-zoom-counter]').textContent.trim());

		await page.screenshot({ path: `tmp-resp/zoom-lightbox/gift-${w}.png` });
		await page.close();
	}

	out.errors = errors;
	console.log(JSON.stringify(out, null, 2));
	await browser.close();
})();
