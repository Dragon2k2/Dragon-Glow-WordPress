const { chromium } = require('playwright');
(async () => {
	const b = await chromium.launch({ channel: 'chrome' });
	const p = await b.newPage({ viewport: { width: 390, height: 844 } });
	await p.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now(), { waitUntil: 'networkidle', timeout: 60000 });
	await p.click('#dg-main-image');
	await p.waitForTimeout(500);
	console.log(JSON.stringify(await p.evaluate(() => {
		const m = document.querySelector('[data-dg-zoom-root]');
		const r = (sel) => { const e = m.querySelector(sel); const b = e.getBoundingClientRect(); const cs = getComputedStyle(e); return { sel, top: Math.round(b.top), bottom: Math.round(b.bottom), left: Math.round(b.left), right: Math.round(b.right), w: Math.round(b.width), h: Math.round(b.height), display: cs.display, position: cs.position, gridRow: cs.gridRow, gridCol: cs.gridColumn }; };
		return [r('[data-dg-zoom-prev]'), r('[data-dg-zoom-counter]'), r('[data-dg-zoom-next]'), r('.dg-zoom-modal__stage')];
	}), null, 2));
	await b.close();
})();
