/**
 * Responsive check for gallery lightbox nav buttons + counter.
 */
const { chromium } = require('playwright');
const path = require('path');

(async () => {
    const browser = await chromium.launch();
    const out = [];

    for (const width of [390, 768, 1280]) {
        const ctx = await browser.newContext({ viewport: { width, height: 900 } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

        await page.goto('file:///' + path.join(__dirname, 'gallery-mock.html').replace(/\\/g, '/'));
        await page.waitForTimeout(1000);

        await page.locator('.dg-gift-card-image').click();
        await page.waitForTimeout(600);

        const m = await page.evaluate(() => {
            const g = (s) => {
                const el = document.querySelector(s);
                if (!el) return null;
                const r = el.getBoundingClientRect();
                const cs = getComputedStyle(el);
                return {
                    x: Math.round(r.x), y: Math.round(r.y),
                    w: Math.round(r.width), h: Math.round(r.height),
                    display: cs.display, zIndex: cs.zIndex,
                };
            };
            const img = document.getElementById('dg-gift-zoom-image');
            const r = img.getBoundingClientRect();
            return {
                prev: g('.dg-gift-zoom-modal__nav--prev'),
                next: g('.dg-gift-zoom-modal__nav--next'),
                counter: g('.dg-gift-zoom-modal__counter'),
                close: g('.dg-gift-zoom-modal__close'),
                img: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) },
                counterText: document.getElementById('dg-gift-zoom-counter').textContent,
            };
        });

        await page.screenshot({ path: path.join(__dirname, `gallery-modal-${width}.png`) });
        out.push({ width, ...m, errors });
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
