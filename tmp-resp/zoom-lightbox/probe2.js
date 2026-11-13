const { chromium } = require('playwright');
(async () => {
	const b = await chromium.launch({ channel: 'chrome' });
	const p = await b.newPage();
	const urls = [
		'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now(),
		'https://dragonglow.freehosting.dev/gift-cards/?v=' + Date.now()
	];
	for (const u of urls) {
		await p.goto(u, { waitUntil: 'domcontentloaded', timeout: 60000 });
		const r = await p.evaluate(() => ({
			url: location.href,
			trigger: document.documentElement.innerHTML.includes('data-dg-zoom-trigger'),
			root: !!document.querySelector('[data-dg-zoom-root]'),
			inline: /onclick="[^"]*zoom/i.test(document.documentElement.innerHTML),
			modal: !!document.getElementById('dg-zoom-modal'),
			lib: typeof window.DGZoomLightbox
		}));
		console.log(JSON.stringify(r));
	}
	await b.close();
})();
