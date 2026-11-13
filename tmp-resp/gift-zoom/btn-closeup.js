/**
 * Close-up of the zoom button inside the preview (top-right corner).
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.resolve(__dirname, 'mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    for (const width of [390, 1280]) {
        const ctx = await browser.newContext({ viewport: { width, height: 900 }, deviceScaleFactor: 2 });
        const page = await ctx.newPage();
        await page.goto(MOCK, { waitUntil: 'networkidle' });
        await page.waitForTimeout(200);

        const btnBox = await page.locator('.dg-zoom-btn').boundingBox();
        await page.screenshot({
            path: path.join(__dirname, `btn-closeup-${width}.png`),
            clip: {
                x: Math.max(0, btnBox.x - 24),
                y: Math.max(0, btnBox.y - 24),
                width: btnBox.width + 48,
                height: btnBox.height + 48,
            },
        });
        await ctx.close();
    }
    await browser.close();
    console.log('done');
})();
