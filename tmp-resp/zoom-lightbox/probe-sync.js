const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const page = await browser.newPage();
    await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'domcontentloaded' });
    const res = await page.evaluate(async () => {
        const r = await fetch('/wp-content/themes/dragon-glow/dg-sync-probe.txt?probe=3');
        return { status: r.status, body: (await r.text()).slice(0, 200) };
    });
    console.log(JSON.stringify(res, null, 2));
    await browser.close();
})();
