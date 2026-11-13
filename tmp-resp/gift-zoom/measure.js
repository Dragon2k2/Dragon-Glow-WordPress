/**
 * Measure the zoom image box vs natural size in the mock.
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.resolve(__dirname, 'mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const failed = [];
    page.on('requestfailed', (r) => failed.push(r.url()));
    page.on('response', (r) => { if (r.status() >= 400) failed.push(r.status() + ' ' + r.url()); });

    await page.goto(MOCK, { waitUntil: 'networkidle' });
    await page.locator('.dg-zoom-btn').click();
    await page.waitForTimeout(600);

    const info = await page.locator('#dg-gift-zoom-image').evaluate((el) => ({
        naturalW: el.naturalWidth,
        naturalH: el.naturalHeight,
        boxW: Math.round(el.getBoundingClientRect().width),
        boxH: Math.round(el.getBoundingClientRect().height),
        computedW: getComputedStyle(el).width,
        computedH: getComputedStyle(el).height,
        objectFit: getComputedStyle(el).objectFit,
        complete: el.complete,
    }));
    const stage = await page.locator('.dg-gift-zoom-modal__stage').evaluate((el) => ({
        boxW: Math.round(el.getBoundingClientRect().width),
        boxH: Math.round(el.getBoundingClientRect().height),
        maxH: getComputedStyle(el).maxHeight,
    }));

    console.log(JSON.stringify({ info, stage, failed }, null, 2));
    await browser.close();
})();
