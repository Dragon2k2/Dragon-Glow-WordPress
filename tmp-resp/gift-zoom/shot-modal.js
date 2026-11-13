/**
 * Screenshot the opened lightbox (mock, real theme CSS).
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.resolve(__dirname, 'mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(MOCK, { waitUntil: 'networkidle' });
    await page.locator('.dg-zoom-btn').click();
    await page.waitForTimeout(700);
    await page.screenshot({ path: path.join(__dirname, 'modal-open.png') });

    const closeBox = await page.locator('.dg-gift-zoom-modal__close').boundingBox();
    const closeStyle = await page.locator('.dg-gift-zoom-modal__close').evaluate((el) => {
        const cs = getComputedStyle(el);
        return { bg: cs.backgroundColor, color: cs.color, radius: cs.borderRadius, zIndex: cs.zIndex, position: cs.position };
    });
    console.log(JSON.stringify({ closeBox, closeStyle }, null, 2));
    await browser.close();
})();
