/**
 * Scenario: Motion CDN bị chặn → ES module gift-cards.js bị skip toàn bộ.
 * Kiểm tra chip mệnh giá + lightbox vẫn hoạt động (nhờ gift-value.js
 * classic script local, không phụ thuộc CDN).
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();

    const errors = [];
    page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

    // Chặn toàn bộ jsDelivr → Motion ES module không load được.
    await ctx.route('**://cdn.jsdelivr.net/**', (route) => route.abort());

    await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(2500);

    const snap = () => page.evaluate(() => ({
        hasGiftValue: typeof window.DGGiftValue === 'object',
        previewSrc: (document.querySelector('.dg-gift-card-image') || {}).getAttribute?.('src') || null,
        activeChip: (document.querySelector('.dg-gift-value.is-active') || {}).getAttribute?.('data-amount') || null,
        hiddenValue: (document.querySelector('input[name="value"]') || {}).value || null,
        submitLabel: (document.querySelector('.dg-gift-submit-value') || {}).textContent?.trim() || null,
        counter: (document.getElementById('dg-gift-zoom-counter') || {}).textContent || null,
    }));

    const out = {};
    out.initial = await snap();

    // Chip mệnh giá vẫn hoạt động?
    await page.locator('.dg-gift-value[data-amount="250"]').click();
    await page.waitForTimeout(400);
    out.afterChip250 = await snap();

    // Lightbox vẫn hoạt động + đồng bộ?
    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(500);
    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(500);
    out.afterNext = await snap();

    await page.keyboard.press('Escape');
    await page.waitForTimeout(400);
    out.afterClose = await snap();

    console.log(JSON.stringify({ out, errors }, null, 2));
    await browser.close();
})();