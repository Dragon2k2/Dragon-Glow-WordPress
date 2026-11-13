/**
 * Guard check: does the live gift-cards page also contain the product lightbox (#dg-zoom-modal)?
 */
const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.goto('https://dragonglow.freehosting.dev/gift-cards/', { waitUntil: 'networkidle', timeout: 60000 });
    const info = await page.evaluate(() => ({
        hasProductModal: !!document.getElementById('dg-zoom-modal'),
        hasGiftModal: !!document.getElementById('dg-gift-zoom-modal'),
        hasMainImage: !!document.getElementById('dg-main-image'),
        openZoomType: typeof window.dgOpenZoom,
    }));
    console.log(JSON.stringify(info, null, 2));
    await browser.close();
})();