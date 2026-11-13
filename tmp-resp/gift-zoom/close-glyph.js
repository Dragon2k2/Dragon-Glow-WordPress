/**
 * Inspect the close button glyph rendering in the gift-card lightbox (live).
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 }, deviceScaleFactor: 3 });
    const page = await ctx.newPage();
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(800);
    await page.locator('.dg-zoom-btn').click();
    await page.waitForTimeout(800);

    const info = await page.locator('.dg-gift-zoom-modal__close').evaluate((el) => {
        const span = el.querySelector('.material-symbols-outlined');
        const cs = getComputedStyle(el);
        const scs = span ? getComputedStyle(span) : null;
        return {
            btn: { fontFamily: cs.fontFamily, fontSize: cs.fontSize, color: cs.color, bg: cs.backgroundColor, w: cs.width, h: cs.height },
            span: scs ? {
                text: span.textContent,
                fontFamily: scs.fontFamily,
                fontSize: scs.fontSize,
                lineHeight: scs.lineHeight,
                display: scs.display,
                color: scs.color,
                width: Math.round(span.getBoundingClientRect().width),
                height: Math.round(span.getBoundingClientRect().height),
            } : null,
        };
    });

    const btnBox = await page.locator('.dg-gift-zoom-modal__close').boundingBox();
    await page.screenshot({
        path: path.join(__dirname, 'close-glyph.png'),
        clip: { x: btnBox.x - 12, y: btnBox.y - 12, width: btnBox.width + 24, height: btnBox.height + 24 },
    });

    console.log(JSON.stringify(info, null, 2));
    await browser.close();
})();
