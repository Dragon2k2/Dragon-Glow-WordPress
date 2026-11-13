/**
 * Verification — regression: no zoom-related console errors on other pages
 */
const { chromium } = require('playwright');

const PAGES = [
	'https://dragonglow.freehosting.dev/?v=' + Date.now(),
	'https://dragonglow.freehosting.dev/shop/?v=' + Date.now(),
	'https://dragonglow.freehosting.dev/gift-cards/?v=' + Date.now(),
	'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now()
];

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	for (const url of PAGES) {
		const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
		const errs = [];
		page.on('pageerror', (e) => errs.push('PAGEERROR: ' + e.message));
		page.on('console', (m) => {
			const t = m.text();
			if (m.type() === 'error' && !t.includes('material-symbols-outlined.woff2')) errs.push('CONSOLE: ' + t);
		});
		await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
		await page.waitForTimeout(800);
		const info = await page.evaluate(() => ({
			lib: typeof window.DGZoomLightbox,
			modals: document.querySelectorAll('[data-dg-zoom-root]').length,
			triggers: document.querySelectorAll('[data-dg-zoom-trigger]').length,
			inlineZoom: (document.documentElement.innerHTML.match(/onclick="[^"]*zoom/gi) || []).length
		}));
		console.log(url.split('?')[0].replace('https://dragonglow.freehosting.dev', '') || '/', JSON.stringify(info), errs.length ? 'ERRORS: ' + errs.join(' | ') : 'clean');
		await page.close();
	}
	await browser.close();
})();
