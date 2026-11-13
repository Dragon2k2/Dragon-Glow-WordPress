const { chromium } = require('playwright');
(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage();
	await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'domcontentloaded', timeout: 60000 });
	const checks = [
		'/wp-content/themes/dragon-glow/assets/js/lib/zoom-lightbox.js',
		'/wp-content/themes/dragon-glow/assets/css/zoom-lightbox.css',
		'/wp-content/themes/dragon-glow/assets/js/product.js',
		'/wp-content/themes/dragon-glow/template-parts/global/zoom-modal.php'
	];
	for (const c of checks) {
		const r = await page.evaluate(async (u) => {
			try {
				const res = await fetch(u + '?probe=' + Date.now(), { cache: 'no-store' });
				const t = await res.text();
				return { status: res.status, len: t.length, head: t.slice(0, 120) };
			} catch (e) { return { err: String(e) }; }
		}, c);
		console.log(c, JSON.stringify(r));
	}
	await browser.close();
})();
