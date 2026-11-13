/**
 * Verify gift-card zoom button + lightbox on the local mock page.
 * Run: node tmp-resp/gift-zoom/verify.js
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.resolve(__dirname, 'mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    const results = [];

    for (const width of [390, 768, 1280]) {
        const ctx = await browser.newContext({ viewport: { width, height: 900 } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
        page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

        await page.goto(MOCK, { waitUntil: 'networkidle' });

        const btn = page.locator('.dg-gift-card-preview .dg-zoom-btn');
        const btnVisible = await btn.isVisible();
        const btnBox = await btn.boundingBox();
        const btnStyle = await btn.evaluate((el) => {
            const cs = getComputedStyle(el);
            return { position: cs.position, top: cs.top, right: cs.right, zIndex: cs.zIndex, w: cs.width, h: cs.height };
        });
        const imgSrc = await page.locator('.dg-gift-card-image').getAttribute('src');

        // Click zoom
        await btn.click();
        await page.waitForTimeout(500);
        const modalOpen = await page.locator('#dg-gift-zoom-modal').evaluate((el) => ({
            display: getComputedStyle(el).display,
            isOpen: el.classList.contains('is-open'),
            ariaHidden: el.getAttribute('aria-hidden'),
        }));
        const zoomImg = await page.locator('#dg-gift-zoom-image').evaluate((el) => ({
            src: el.getAttribute('src'),
            opacity: getComputedStyle(el).opacity,
            hasVisible: el.classList.contains('is-visible'),
        }));
        const bodyOverflow = await page.evaluate(() => document.body.style.overflow);
        const focused = await page.evaluate(() => document.activeElement && document.activeElement.className);

        await page.screenshot({ path: path.join(__dirname, `zoom-open-${width}.png`) });

        // Escape closes
        await page.keyboard.press('Escape');
        await page.waitForTimeout(200);
        const modalClosed = await page.locator('#dg-gift-zoom-modal').evaluate((el) => ({
            display: getComputedStyle(el).display,
            isOpen: el.classList.contains('is-open'),
        }));
        const bodyOverflowAfter = await page.evaluate(() => document.body.style.overflow);

        results.push({
            width, btnVisible, btnBox, btnStyle, imgSrc,
            modalOpen, zoomImg, bodyOverflow, focused,
            modalClosed, bodyOverflowAfter, errors,
        });

        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(results, null, 2));
})();
