/**
 * Live verification: lightbox ↔ page state sync on the real site.
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1200);

    const snap = () => page.evaluate(() => ({
        previewSrc: (document.querySelector('.dg-gift-card-image') || {}).getAttribute?.('src') || null,
        activeChip: (document.querySelector('.dg-gift-value.is-active') || {}).getAttribute?.('data-amount') || null,
        hiddenValue: (document.querySelector('input[name="value"]') || {}).value || null,
        submitLabel: (document.querySelector('.dg-gift-submit-value') || {}).textContent?.trim() || null,
        counter: (document.getElementById('dg-gift-zoom-counter') || {}).textContent || null,
        open: document.getElementById('dg-gift-zoom-modal').classList.contains('is-open'),
    }));

    const out = {};
    out.initial = await snap();

    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(600);
    out.open = await snap();

    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(500);
    out.afterNext = await snap();

    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);
    out.afterClose = await snap();

    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(500);
    out.reopen = await snap();

    console.log(JSON.stringify({ out, errors }, null, 2));
    await browser.close();
})();