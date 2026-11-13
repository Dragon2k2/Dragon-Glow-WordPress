/**
 * Live verification of the gift-card zoom button + lightbox on the real site.
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    const out = [];

    for (const width of [390, 768, 1280]) {
        const ctx = await browser.newContext({ viewport: { width, height: 900 } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(800);

        const btn = page.locator('.dg-gift-card-preview .dg-zoom-btn');
        const visible = await btn.isVisible();
        const box = await btn.boundingBox();
        const style = await btn.evaluate((el) => {
            const cs = getComputedStyle(el);
            return { position: cs.position, top: cs.top, right: cs.right, zIndex: cs.zIndex, w: cs.width, h: cs.height, bg: cs.backgroundColor };
        });

        await btn.click();
        await page.waitForTimeout(800);

        const modal = await page.locator('#dg-gift-zoom-modal').evaluate((el) => ({
            display: getComputedStyle(el).display,
            isOpen: el.classList.contains('is-open'),
            ariaHidden: el.getAttribute('aria-hidden'),
        }));
        const zoomImg = await page.locator('#dg-gift-zoom-image').evaluate((el) => ({
            src: el.getAttribute('src'),
            naturalW: el.naturalWidth,
            naturalH: el.naturalHeight,
            boxW: Math.round(el.getBoundingClientRect().width),
            boxH: Math.round(el.getBoundingClientRect().height),
            opacity: getComputedStyle(el).opacity,
        }));
        const bodyOverflow = await page.evaluate(() => document.body.style.overflow);

        await page.screenshot({ path: path.join(__dirname, `live-modal-${width}.png`) });

        await page.keyboard.press('Escape');
        await page.waitForTimeout(300);
        const closed = await page.locator('#dg-gift-zoom-modal').evaluate((el) => getComputedStyle(el).display);
        const overflowAfter = await page.evaluate(() => document.body.style.overflow);

        out.push({ width, visible, box, style, modal, zoomImg, bodyOverflow, closed, overflowAfter, errors });
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
