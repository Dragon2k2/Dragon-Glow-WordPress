/**
 * Verify: chuyển ảnh trong lightbox → đồng bộ toàn bộ state mệnh giá bên ngoài.
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.join(__dirname, 'sync-mock.html').replace(/\\/g, '/');

function readState(page) {
    return page.evaluate(() => ({
        previewSrc: (document.querySelector('.dg-gift-card-image') || {}).getAttribute?.('src') || null,
        activeChip: (document.querySelector('.dg-gift-value.is-active') || {}).getAttribute?.('data-amount') || null,
        hiddenValue: (document.querySelector('input[name="value"]') || {}).value || null,
        submitLabel: (document.querySelector('.dg-gift-submit-value') || {}).textContent?.trim() || null,
        counter: (document.getElementById('dg-gift-zoom-counter') || {}).textContent || null,
        zoomSrc: (document.getElementById('dg-gift-zoom-image') || {}).getAttribute?.('src') || null,
        modalOpen: document.getElementById('dg-gift-zoom-modal').classList.contains('is-open'),
    }));
}

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

    await page.goto(MOCK);
    await page.waitForTimeout(800);

    const out = {};

    // Baseline — state khởi tạo
    out.initial = await readState(page);

    // Click chip $250 → state đổi
    await page.locator('.dg-gift-value[data-amount="250"]').click();
    await page.waitForTimeout(300);
    out.afterChip250 = await readState(page);

    // Mở lightbox từ ảnh → index phải là 250 (index 2)
    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(500);
    out.open = await readState(page);

    // Chuyển next → $500
    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(400);
    out.afterNext = await readState(page);

    // Đóng lightbox → state bên ngoài phải khớp $500
    await page.keyboard.press('Escape');
    await page.waitForTimeout(400);
    out.afterClose = await readState(page);

    // Mở lại → index phải là 500
    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(400);
    out.reopen = await readState(page);

    // Prev → $250
    await page.locator('.dg-gift-zoom-modal__nav--prev').click();
    await page.waitForTimeout(400);
    out.afterPrev = await readState(page);

    console.log(JSON.stringify({ out, errors }, null, 2));
    await browser.close();
})();
