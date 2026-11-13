const { chromium } = require('playwright');
(async () => {
	const b = await chromium.launch({ channel: 'chrome' });
	const p = await b.newPage();
	const bad = [];
	p.on('response', (r) => { if (r.status() >= 400) bad.push(r.status() + ' ' + r.url()); });
	await p.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now(), { waitUntil: 'networkidle', timeout: 60000 });
	await p.waitForTimeout(1500);
	console.log(bad.join('\n') || 'no 4xx');
	await b.close();
})();
