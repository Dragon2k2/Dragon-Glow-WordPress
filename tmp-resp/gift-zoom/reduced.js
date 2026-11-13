/**
 * Verify reduced-motion behaviour of the gift-card lightbox.
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.resolve(__dirname, 'mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({
        viewport: { width: 1280, height: 900 },
        reducedMotion: 'reduce',
    });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message));
    await page.goto(MOCK, { waitUntil: 'networkidle' });

    await page.locator('.dg-zoom-btn').click();
    await page.waitForTimeout(120);

    const state = await page.evaluate(() => {
        const img = document.getElementById('dg-gift-zoom-image');
        const cs = getComputedStyle(img);
        return {
            modalDisplay: getComputedStyle(document.getElementById('dg-gift-zoom-modal')).display,
            imgOpacity: cs.opacity,
            imgTransform: cs.transform,
            transitionDuration: cs.transitionDuration,
            hasVisible: img.classList.contains('is-visible'),
        };
    });
    console.log(JSON.stringify({ state, errors }, null, 2));
    await browser.close();
})();
