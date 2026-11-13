/**
 * Inject file JS LOCAL (đúng code trên disk) lên site live rồi test close/cancel.
 * Mục đích: xác nhận lỗi `EASE is not defined` làm modal không đóng được.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const JS = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/js/shipping-returns.js');
const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';
const src = fs.readFileSync(JS, 'utf8');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e.message)));

    /* chặn script module gốc, thay bằng bản local */
    await page.route('**/assets/js/shipping-returns.js*', (route) =>
        route.fulfill({ status: 200, contentType: 'text/javascript', body: src })
    );

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(900);

    const hasEase = /const EASE/.test(src);

    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(700);
    const opened = await page.evaluate(() => {
        const m = document.getElementById('dg-return-modal');
        return { hidden: m.hasAttribute('hidden'), display: getComputedStyle(m).display };
    });

    await page.locator('.dg-rm-close').click();
    await page.waitForTimeout(1000);
    const afterClose = await page.evaluate(() => {
        const m = document.getElementById('dg-return-modal');
        return {
            hidden: m.hasAttribute('hidden'),
            display: getComputedStyle(m).display,
            dataOpen: m.hasAttribute('data-open'),
            bodyLock: document.body.classList.contains('dg-rm-open'),
            dialogOpacity: getComputedStyle(m.querySelector('.dg-rm-dialog')).opacity,
        };
    });

    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(700);
    await page.locator('.dg-rm-btn--secondary').click();
    await page.waitForTimeout(1000);
    const afterCancel = await page.evaluate(() => {
        const m = document.getElementById('dg-return-modal');
        return {
            hidden: m.hasAttribute('hidden'),
            display: getComputedStyle(m).display,
            dataOpen: m.hasAttribute('data-open'),
        };
    });

    console.log(JSON.stringify({ localJsHasConstEase: hasEase, opened, afterClose, afterCancel, errors }, null, 2));
    await browser.close();
})();
