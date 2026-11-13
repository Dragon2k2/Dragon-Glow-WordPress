const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const page = await browser.newPage();
    await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'domcontentloaded' });
    const live = await page.evaluate(async () => {
        const r = await fetch('/wp-content/themes/dragon-glow/assets/js/lib/gift-value.js?probe=1');
        return await r.text();
    });
    const local = fs.readFileSync('wp-content/themes/dragon-glow/assets/js/lib/gift-value.js', 'utf8');
    fs.writeFileSync('tmp-resp/zoom-lightbox/live-gift-value.js', live);
    console.log('live len', live.length, 'local len', local.length, 'equal:', live.trim() === local.trim());
    await browser.close();
})();
