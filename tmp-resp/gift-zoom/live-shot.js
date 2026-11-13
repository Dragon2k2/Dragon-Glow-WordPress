/**
 * Live screenshot of the gift card preview area (zoom button) + modal close button.
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    for (const width of [390, 1280]) {
        const ctx = await browser.newContext({ viewport: { width, height: 900 }, deviceScaleFactor: 2 });
        const page = await ctx.newPage();
        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(900);

        const preview = page.locator('.dg-gift-card-preview');
        await preview.scrollIntoViewIfNeeded();
        await page.waitForTimeout(400);
        await preview.screenshot({ path: path.join(__dirname, `live-preview-${width}.png`) });

        // Modal close button area
        await page.locator('.dg-zoom-btn').click();
        await page.waitForTimeout(800);
        await page.screenshot({
            path: path.join(__dirname, `live-modal-close-${width}.png`),
            clip: { x: Math.max(0, width - 110), y: 0, width: 110, height: 110 },
        });
        await ctx.close();
    }
    await browser.close();
    console.log('done');
})();
