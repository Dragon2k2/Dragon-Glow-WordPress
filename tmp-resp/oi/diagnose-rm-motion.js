/**
 * Chẩn đoán chính xác: nút Close (×) / Cancel của modal Return.
 *
 * Giả thuyết: `shipping-returns.js` dùng `EASE` ở dòng 180 & 199-200 nhưng
 * KHÔNG khai báo const EASE (khác các file khác trong theme) → ReferenceError
 * ném ra giữa chừng `close()` → `modal.setAttribute('hidden','')` không chạy
 * → modal không đóng được.
 *
 * Chạy 2 kịch bản: prefers-reduced-motion = no-preference (có animation)
 * và = reduce (không animation).
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

async function run(browser, reducedMotion, label) {
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e.message)));

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(600);

    const prefersReduced = await page.evaluate(() => matchMedia('(prefers-reduced-motion: reduce)').matches);

    /* mở modal */
    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(700);
    const opened = await page.evaluate(() => {
        const m = document.getElementById('dg-return-modal');
        return { hidden: m.hasAttribute('hidden'), display: getComputedStyle(m).display };
    });

    /* click Close (×) */
    await page.locator('.dg-rm-close').click();
    await page.waitForTimeout(900);
    const afterClose = await page.evaluate(() => {
        const m = document.getElementById('dg-return-modal');
        return {
            hidden: m.hasAttribute('hidden'),
            display: getComputedStyle(m).display,
            opacity: getComputedStyle(m.querySelector('.dg-rm-dialog')).opacity,
            dataOpen: m.hasAttribute('data-open'),
            bodyLock: document.body.classList.contains('dg-rm-open'),
        };
    });

    /* mở lại → click Cancel */
    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(700);
    await page.locator('.dg-rm-btn--secondary').click();
    await page.waitForTimeout(900);
    const afterCancel = await page.evaluate(() => {
        const m = document.getElementById('dg-return-modal');
        return {
            hidden: m.hasAttribute('hidden'),
            display: getComputedStyle(m).display,
            opacity: getComputedStyle(m.querySelector('.dg-rm-dialog')).opacity,
        };
    });

    await ctx.close();
    return { label, reducedMotionSetting: reducedMotion, prefersReducedInPage: prefersReduced, opened, afterClose, afterCancel, errors };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const a = await run(browser, 'no-preference', 'animation ON');
    const b = await run(browser, 'reduce', 'animation OFF');
    await browser.close();
    console.log(JSON.stringify([a, b], null, 2));
})();
