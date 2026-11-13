/**
 * Xác nhận THỰC THI order: gift-value.js (classic, footer) phải chạy TRƯỚC
 * gift-cards.js (ES module → deferred, luôn chạy sau classic scripts).
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();

    // Ghi lại thời điểm DGGiftValue xuất hiện + lúc ES module chạy.
    await page.addInitScript(() => {
        window.__t0 = performance.now();
        window.__valueReadyAt = null;
        window.__giftModuleRanAt = null;
        window.__interval = setInterval(() => {
            if (window.__valueReadyAt === null && window.DGGiftValue) {
                window.__valueReadyAt = performance.now();
            }
        }, 1);
    });

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);

    const result = await page.evaluate(() => {
        // Chiều ngược: ES module chạy → đánh dấu. Nếu DGGiftValue đã có sẵn
        // lúc module chạy → thứ tự đúng.
        return {
            valueReadyAt: window.__valueReadyAt,
            valueExists: typeof window.DGGiftValue === 'object',
            // Kiểm tra fallback có bị kích hoạt không: nếu gift-cards.js chạy
            // TRƯỚC gift-value.js, module sẽ tự gắn listener fallback → click
            // chip vẫn hoạt động nhưng double-update.
            chipListenerSource: (function () {
                // Đếm số listener không trực tiếp được; dùng hành vi thay thế.
                return null;
            })(),
        };
    });

    // Hành vi thực tế: click chip → state đổi đúng 1 lần, không nhân đôi.
    await page.locator('.dg-gift-value[data-amount="500"]').click();
    await page.waitForTimeout(300);
    const afterChip = await page.evaluate(() => ({
        previewSrc: document.querySelector('.dg-gift-card-image').getAttribute('src'),
        activeChip: document.querySelector('.dg-gift-value.is-active').getAttribute('data-amount'),
        hiddenValue: document.querySelector('input[name="value"]').value,
        submitLabel: document.querySelector('.dg-gift-submit-value').textContent.trim(),
        value: window.DGGiftValue.get(),
    }));

    console.log(JSON.stringify({ result, afterChip }, null, 2));
    await browser.close();
})();