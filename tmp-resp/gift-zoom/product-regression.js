/**
 * Regression check: single product page zoom still uses product.js lightbox.
 */
const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message));

    await page.goto('https://dragonglow.freehosting.dev/product/blushbloome-petal-blush/', { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(600);

    const info = await page.evaluate(() => ({
        hasProductModal: !!document.getElementById('dg-zoom-modal'),
        hasGiftModal: !!document.getElementById('dg-gift-zoom-modal'),
        hasZoomBtn: !!document.querySelector('.dg-zoom-btn'),
    }));

    const btn = page.locator('.dg-zoom-btn').first();
    await btn.click();
    await page.waitForTimeout(700);
    const opened = await page.locator('#dg-zoom-modal').evaluate((el) => ({
        display: getComputedStyle(el).display,
        imgSrc: (document.getElementById('dg-zoom-image') || {}).src || null,
    }));

    console.log(JSON.stringify({ info, opened, errors }, null, 2));
    await browser.close();
})();