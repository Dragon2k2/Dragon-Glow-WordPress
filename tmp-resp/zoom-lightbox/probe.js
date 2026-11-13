const { chromium } = require('playwright');
(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
	await page.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/', { waitUntil: 'domcontentloaded', timeout: 60000 });
	const html = await page.content();
	console.log('HAS_ZOOM_ROOT:', html.includes('data-dg-zoom-root'));
	console.log('HAS_DG_ZOOM_MODAL:', html.includes('dg-zoom-modal'));
	console.log('HAS_TRIGGER:', html.includes('data-dg-zoom-trigger'));
	console.log('HAS_THUMB_ATTR:', html.includes('data-dg-thumb'));
	console.log('HAS_INLINE_ZOOM:', /onclick="[^"]*zoom/i.test(html));
	console.log('HAS_DG_ZOOM_IMG:', html.includes('dg-zoom-image'));
	console.log('---TAIL---');
	console.log(html.slice(-3000));
	await browser.close();
})();
