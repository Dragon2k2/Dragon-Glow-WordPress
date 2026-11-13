/**
 * Verification — outer image follows lightbox image on exit
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now();

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const out = {};

	const snap = (page) => page.evaluate(() => {
		const main = document.getElementById('dg-main-image');
		const m = document.querySelector('[data-dg-zoom-root]');
		return {
			mainSrc: main.getAttribute('src'),
			activeThumb: (() => {
				const t = document.querySelector('[data-dg-thumb].is-active');
				return t ? t.dataset.full : null;
			})(),
			counter: m.querySelector('[data-dg-zoom-counter]').textContent.trim(),
			zoomSrc: m.querySelector('[data-dg-zoom-image]').getAttribute('src'),
			isOpen: m.classList.contains('is-open')
		};
	});

	const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
	const errors = [];
	page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));
	await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

	out.initial = await snap(page);

	// Open → next → next → escape → outer image must match the last viewed.
	await page.click('#dg-main-image');
	await page.waitForTimeout(400);
	out.opened = await snap(page);

	await page.click('[data-dg-zoom-next]');
	await page.waitForTimeout(400);
	out.afterNext1 = await snap(page);

	await page.click('[data-dg-zoom-next]');
	await page.waitForTimeout(400);
	out.afterNext2 = await snap(page);

	// Close → outer image must equal the image viewed in the lightbox.
	await page.keyboard.press('Escape');
	await page.waitForTimeout(400);
	out.afterEscape = await snap(page);
	out.syncAfterExit = out.afterEscape.mainSrc === out.afterNext2.zoomSrc;
	out.thumbSyncAfterExit = out.afterEscape.activeThumb === out.afterNext2.zoomSrc;

	// Reopen → must open at the same image (not reset to first).
	await page.click('#dg-main-image');
	await page.waitForTimeout(400);
	out.reopened = await snap(page);
	out.reopenKeepsIndex = out.reopened.counter === out.afterNext2.counter;

	// Keyboard navigation also syncs.
	await page.keyboard.press('ArrowLeft');
	await page.waitForTimeout(400);
	out.afterArrowLeft = await snap(page);
	out.arrowSync = out.afterArrowLeft.mainSrc === out.afterArrowLeft.zoomSrc;

	// Prev at boundary wraps.
	await page.keyboard.press('ArrowLeft');
	await page.keyboard.press('ArrowLeft');
	await page.waitForTimeout(500);
	out.afterWrap = await snap(page);
	out.wrapSync = out.afterWrap.mainSrc === out.afterWrap.zoomSrc;

	await page.keyboard.press('Escape');
	await page.waitForTimeout(300);
	out.final = await snap(page);
	out.finalSync = out.final.mainSrc === out.afterWrap.zoomSrc;

	out.errors = errors;
	console.log(JSON.stringify(out, null, 2));
	await browser.close();
})();
