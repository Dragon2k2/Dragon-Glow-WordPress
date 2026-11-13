/**
 * Verification — reduced motion + tab trap + counter aria
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now();

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const out = {};

	// ── Reduced motion ──
	{
		const page = await browser.newPage({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
		await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
		await page.click('#dg-main-image');
		await page.waitForTimeout(400);
		out.reducedMotion = await page.evaluate(() => {
			const img = document.querySelector('[data-dg-zoom-image]');
			const cs = getComputedStyle(img);
			return {
				isOpen: document.querySelector('[data-dg-zoom-root]').classList.contains('is-open'),
				transition: cs.transitionDuration,
				transform: cs.transform,
				opacity: cs.opacity,
				visible: img.classList.contains('is-visible')
			};
		});
		await page.close();
	}

	// ── Tab trap: focus cycles inside modal ──
	{
		const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
		await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
		await page.click('#dg-main-image');
		await page.waitForTimeout(400);
		const seq = [];
		for (let i = 0; i < 5; i++) {
			await page.keyboard.press('Tab');
			seq.push(await page.evaluate(() => {
				const a = document.activeElement;
				return { cls: a.className, inModal: !!document.querySelector('[data-dg-zoom-root]').contains(a) };
			}));
		}
		out.tabSequence = seq;
		out.allInsideModal = seq.every((s) => s.inModal);
		await page.close();
	}

	// ── Counter aria-live + image alt ──
	{
		const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
		await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
		await page.click('#dg-main-image');
		await page.waitForTimeout(400);
		out.a11y = await page.evaluate(() => {
			const m = document.querySelector('[data-dg-zoom-root]');
			const img = m.querySelector('[data-dg-zoom-image]');
			return {
				role: m.getAttribute('role'),
				ariaModal: m.getAttribute('aria-modal'),
				ariaHidden: m.getAttribute('aria-hidden'),
				ariaLabel: m.getAttribute('aria-label'),
				counterAriaLive: m.querySelector('[data-dg-zoom-counter]').getAttribute('aria-live'),
				imgAlt: img.getAttribute('alt'),
				closeLabel: m.querySelector('[data-dg-zoom-close]').getAttribute('aria-label')
			};
		});
		await page.close();
	}

	console.log(JSON.stringify(out, null, 2));
	await browser.close();
})();
