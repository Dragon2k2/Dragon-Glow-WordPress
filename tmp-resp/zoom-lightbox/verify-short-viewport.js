const { chromium } = require('playwright');
(async () => {
	const b = await chromium.launch({ channel: 'chrome' });
	for (const [w, h] of [[1280, 560], [1280, 600], [1440, 700], [390, 600], [768, 500]]) {
		const p = await b.newPage({ viewport: { width: w, height: h } });
		await p.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now(), { waitUntil: 'networkidle', timeout: 60000 });
		await p.click('#dg-main-image');
		await p.waitForTimeout(400);
		const r = await p.evaluate(() => {
			const m = document.querySelector('[data-dg-zoom-root]');
			const img = m.querySelector('[data-dg-zoom-image]');
			const c = m.querySelector('[data-dg-zoom-counter]');
			const ri = img.getBoundingClientRect(), rc = c.getBoundingClientRect();
			return {
				gap: Math.round(rc.top - ri.bottom),
				imgBottom: Math.round(ri.bottom),
				counterTop: Math.round(rc.top),
				counterBottom: Math.round(rc.bottom),
				overflowsBottom: rc.bottom > window.innerHeight,
				imgOverflows: ri.bottom > window.innerHeight
			};
		});
		console.log(`${w}x${h}`, JSON.stringify(r));
		await p.close();
	}
	await b.close();
})();
