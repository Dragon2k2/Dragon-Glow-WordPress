/**
 * Verification — counter spacing vs image bottom edge
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now();

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const out = {};

	for (const [w, h] of [[390, 844], [640, 900], [768, 1024], [1280, 900], [1440, 1200]]) {
		const page = await browser.newPage({ viewport: { width: w, height: h } });
		await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
		await page.click('#dg-main-image');
		await page.waitForTimeout(500);
		out[`${w}x${h}`] = await page.evaluate(() => {
			const m = document.querySelector('[data-dg-zoom-root]');
			const img = m.querySelector('[data-dg-zoom-image]');
			const counter = m.querySelector('[data-dg-zoom-counter]');
			const prev = m.querySelector('[data-dg-zoom-prev]');
			const next = m.querySelector('[data-dg-zoom-next]');
			const r = (el) => { const b = el.getBoundingClientRect(); return { top: Math.round(b.top), bottom: Math.round(b.bottom), left: Math.round(b.left), right: Math.round(b.right), w: Math.round(b.width), h: Math.round(b.height) }; };
			const ri = r(img), rc = r(counter), rp = r(prev), rn = r(next);
			return {
				img: ri,
				counter: rc,
				gapImageToCounter: rc.top - ri.bottom,
				counterToViewportBottom: window.innerHeight - rc.bottom,
				navRowAligned: Math.abs(rp.top - rc.top) < 2 && Math.abs(rn.top - rc.top) < 2,
				navOverlapCounter: rp.right > rc.left || rn.left < rc.right,
				imgInsideViewport: ri.bottom <= window.innerHeight && ri.top >= 0,
				modalDisplay: getComputedStyle(m).display
			};
		});
		await page.screenshot({ path: `tmp-resp/zoom-lightbox/spacing-${w}.png` });
		await page.close();
	}

	console.log(JSON.stringify(out, null, 2));
	await browser.close();
})();
