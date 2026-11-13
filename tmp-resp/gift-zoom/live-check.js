/**
 * Check whether the live Gift Cards page already reflects local changes.
 */
const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    try {
        await page.goto('https://dragonglow.freehosting.dev/gift-cards/', { waitUntil: 'domcontentloaded', timeout: 30000 });
    } catch (e) {
        console.log('NAV ERROR: ' + e.message);
        await browser.close();
        return;
    }
    const info = await page.evaluate(() => ({
        title: document.title,
        url: location.href,
        hasPreview: !!document.querySelector('.dg-gift-card-preview'),
        hasZoomBtn: !!document.querySelector('.dg-gift-card-preview .dg-zoom-btn'),
        hasModal: !!document.getElementById('dg-gift-zoom-modal'),
        zoomScript: !!document.querySelector('script[src*="gift-cards-zoom"]'),
    }));
    console.log(JSON.stringify(info, null, 2));
    await browser.close();
})();
