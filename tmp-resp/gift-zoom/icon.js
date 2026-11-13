/**
 * Measure the zoom icon rendering (font-size, family) inside the gift preview.
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.resolve(__dirname, 'mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(MOCK, { waitUntil: 'networkidle' });

    const icon = await page.locator('.dg-zoom-btn .material-symbols-outlined').evaluate((el) => {
        const cs = getComputedStyle(el);
        return {
            fontSize: cs.fontSize,
            fontFamily: cs.fontFamily,
            fontWeight: cs.fontWeight,
            lineHeight: cs.lineHeight,
            color: cs.color,
            display: cs.display,
        };
    });
    const btn = await page.locator('.dg-zoom-btn').evaluate((el) => {
        const cs = getComputedStyle(el);
        return {
            bg: cs.backgroundColor,
            color: cs.color,
            backdropFilter: cs.backdropFilter || cs.webkitBackdropFilter,
            borderRadius: cs.borderRadius,
        };
    });
    console.log(JSON.stringify({ icon, btn }, null, 2));
    await browser.close();
})();
