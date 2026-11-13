const { chromium } = require('playwright');
const fs = require('fs');

const FILES = [
    'assets/js/product.js',
    'assets/js/gift-cards-zoom.js',
    'assets/css/single-product.css',
    'assets/css/product.css',
    'inc/enqueue/scripts.php',
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const page = await browser.newPage();
    await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'domcontentloaded' });

    for (const f of FILES) {
        const live = await page.evaluate(async (f) => {
            const r = await fetch('/wp-content/themes/dragon-glow/' + f + '?probe=2');
            return await r.text();
        }, f);
        let local = '';
        try { local = fs.readFileSync('wp-content/themes/dragon-glow/' + f, 'utf8'); } catch (e) { }
        const out = `tmp-resp/zoom-lightbox/live-${f.replace(/[\/\\]/g, '_')}`;
        fs.writeFileSync(out, live);
        console.log(`${f}: live=${live.length} local=${local.length} equal=${live.trim() === local.trim()}`);
    }

    await browser.close();
})();
