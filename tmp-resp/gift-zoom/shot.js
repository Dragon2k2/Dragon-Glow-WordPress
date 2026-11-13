/**
 * Screenshot the gift card preview with the zoom button (mock, real theme CSS).
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.resolve(__dirname, 'mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    for (const width of [390, 1280]) {
        const ctx = await browser.newContext({ viewport: { width, height: 900 } });
        const page = await ctx.newPage();
        await page.goto(MOCK, { waitUntil: 'networkidle' });
        await page.waitForTimeout(300);
        await page.locator('.dg-gift-card-preview').screenshot({
            path: path.join(__dirname, `preview-${width}.png`),
        });
        await ctx.close();
    }
    await browser.close();
    console.log('done');
})();
