/**
 * Regression: mở lightbox rồi đóng ngay (không chuyển ảnh) → state bên ngoài
 * KHÔNG đổi. Và chip vẫn hoạt động sau khi lightbox đã dùng.
 */
const { chromium } = require('playwright');
const path = require('path');

const MOCK = 'file:///' + path.join(__dirname, 'sync-mock.html').replace(/\\/g, '/');

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));

    await page.goto(MOCK);
    await page.waitForTimeout(800);

    const snap = () => page.evaluate(() => ({
        previewSrc: document.querySelector('.dg-gift-card-image').getAttribute('src'),
        activeChip: document.querySelector('.dg-gift-value.is-active').getAttribute('data-amount'),
        hiddenValue: document.querySelector('input[name="value"]').value,
        submitLabel: document.querySelector('.dg-gift-submit-value').textContent.trim(),
    }));

    const out = {};
    out.initial = await snap();

    // Mở rồi đóng ngay → state giữ nguyên $100
    await page.locator('.dg-zoom-btn').click();
    await page.waitForTimeout(400);
    out.openNoNav = await snap();
    await page.keyboard.press('Escape');
    await page.waitForTimeout(300);
    out.closedNoNav = await snap();

    // Chip vẫn hoạt động sau khi lightbox đã dùng → $50
    await page.locator('.dg-gift-value[data-amount="50"]').click();
    await page.waitForTimeout(300);
    out.chip50 = await snap();

    // Mở lightbox, next ×1 → $100, đóng → state $100
    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(400);
    await page.locator('.dg-gift-zoom-modal__nav--next').click();
    await page.waitForTimeout(400);
    await page.keyboard.press('Escape');
    await page.waitForTimeout(300);
    out.afterCloseAt100 = await snap();

    // Click backdrop (target = modal) → đóng
    await page.locator('.dg-gift-card-image').click();
    await page.waitForTimeout(400);
    await page.locator('#dg-gift-zoom-modal').click({ position: { x: 5, y: 5 } });
    await page.waitForTimeout(300);
    out.closedByBackdrop = await snap();

    console.log(JSON.stringify({ out, errors }, null, 2));
    await browser.close();
})();
