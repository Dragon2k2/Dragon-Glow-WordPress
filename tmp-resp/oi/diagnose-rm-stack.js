/**
 * Bắt stack trace của mọi pageerror để xác định chính xác lỗi phát sinh ở đâu.
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();

    const errors = [];
    page.on('pageerror', (e) => errors.push({ message: e.message, stack: e.stack }));

    await page.addInitScript(() => {
        window.__errs = [];
        window.addEventListener('error', (ev) => {
            window.__errs.push({ msg: ev.message, stack: ev.error && ev.error.stack });
        });
    });

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1000);
    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(800);

    const inPage = await page.evaluate(() => window.__errs || []);

    console.log(JSON.stringify({ playwrightErrors: errors, inPageErrors: inPage }, null, 2));
    await browser.close();
})();
