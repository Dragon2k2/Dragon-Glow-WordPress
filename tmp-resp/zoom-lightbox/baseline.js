/**
 * Baseline (RED): does clicking the main product image open a lightbox?
 * Also records whether the existing zoom button works, so we know what
 * behaviour must not regress.
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(800);

    const snap = () => page.evaluate(() => {
        const modal = document.getElementById('dg-zoom-modal');
        return {
            modalExists: !!modal,
            modalHidden: modal ? modal.classList.contains('hidden') : null,
            modalDisplay: modal ? getComputedStyle(modal).display : null,
            zoomImgSrc: (document.getElementById('dg-zoom-image') || {}).getAttribute?.('src') || null,
            counter: (document.getElementById('dg-zoom-counter') || {}).textContent || null,
            mainSrc: (document.getElementById('dg-main-image') || {}).getAttribute?.('src') || null,
            thumbs: document.querySelectorAll('.dg-thumb-btn').length,
            bodyOverflow: document.body.style.overflow,
        };
    });

    const out = {};
    out.initial = await snap();

    // 1) Click the MAIN IMAGE (the requested behaviour).
    await page.locator('#dg-main-image').click({ timeout: 5000 }).catch((e) => { out.mainClickError = e.message; });
    await page.waitForTimeout(500);
    out.afterMainImageClick = await snap();

    // 2) Existing zoom button behaviour (must keep working).
    await page.locator('.dg-zoom-btn').click({ timeout: 5000 }).catch((e) => { out.zoomBtnError = e.message; });
    await page.waitForTimeout(500);
    out.afterZoomButtonClick = await snap();

    console.log(JSON.stringify({ out, errors }, null, 2));
    await browser.close();
})();
