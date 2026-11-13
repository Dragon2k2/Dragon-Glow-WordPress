/**
 * Kiểm chứng sau khi sửa: inject JS + CSS local lên site live, rồi test
 * modal Return ở 390/768/1280 px.
 *
 * Kiểm tra:
 *  - click nút Close (×) → modal đóng
 *  - click nút Cancel → modal đóng
 *  - ESC → đóng
 *  - click overlay → đóng
 *  - focus được vào modal khi mở, trả lại focus khi đóng
 *  - body scroll lock được thả
 *  - không có pageerror
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const JS = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/js/shipping-returns.js');
const CSS = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/shipping-returns.css');
const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';
const OUT = path.join(__dirname, 'rm-fix');
fs.mkdirSync(OUT, { recursive: true });

const js = fs.readFileSync(JS, 'utf8');
const css = fs.readFileSync(CSS, 'utf8');

const VPS = [
    { name: '320', width: 320, height: 640 },
    { name: '390', width: 390, height: 844 },
    { name: '768', width: 768, height: 1024 },
    { name: '1280', width: 1280, height: 900 },
];

const modalState = () => {
    const m = document.getElementById('dg-return-modal');
    return {
        hidden: m.hasAttribute('hidden'),
        dataOpen: m.hasAttribute('data-open'),
        bodyLock: document.body.classList.contains('dg-rm-open'),
        display: getComputedStyle(m).display,
    };
};

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const results = [];

    for (const vp of VPS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.route('**/assets/js/shipping-returns.js*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/javascript', body: js }));
        await page.route('**/assets/css/shipping-returns.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: css }));

        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(800);

        const r = { vp: vp.name };

        /* helper: mở modal */
        const open = async () => {
            await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
            await page.waitForTimeout(800);
            return page.evaluate(modalState);
        };

        r.opened = await open();

        /* hit-test nút Close: có bị header chặn không? */
        r.closeHit = await page.evaluate(() => {
            const el = document.querySelector('.dg-rm-close');
            const rect = el.getBoundingClientRect();
            const top = document.elementFromPoint(rect.left + rect.width / 2, rect.top + rect.height / 2);
            return {
                topElement: top ? `${top.tagName.toLowerCase()}.${(top.className || '').toString().split(' ').filter(Boolean).join('.')}` : null,
                clickable: !!top && (top === el || el.contains(top)),
            };
        });

        /* click Close */
        try {
            await page.locator('.dg-rm-close').click({ timeout: 8000 });
            r.closeClick = 'ok';
        } catch (e) {
            r.closeClick = 'FAIL: ' + String(e.message).split('\n')[0];
        }
        await page.waitForTimeout(900);
        r.afterClose = await page.evaluate(modalState);

        /* click Cancel */
        await open();
        try {
            await page.locator('.dg-rm-btn--secondary').click({ timeout: 8000 });
            r.cancelClick = 'ok';
        } catch (e) {
            r.cancelClick = 'FAIL: ' + String(e.message).split('\n')[0];
        }
        await page.waitForTimeout(900);
        r.afterCancel = await page.evaluate(modalState);

        /* ESC */
        await open();
        await page.keyboard.press('Escape');
        await page.waitForTimeout(900);
        r.afterEsc = await page.evaluate(modalState);

        /* click overlay */
        await open();
        await page.locator('.dg-rm-overlay').click({ position: { x: 5, y: 5 }, timeout: 8000 });
        await page.waitForTimeout(900);
        r.afterOverlay = await page.evaluate(modalState);

        /* focus management */
        await open();
        r.focusOnOpen = await page.evaluate(() => {
            const a = document.activeElement;
            return a ? `${a.tagName.toLowerCase()}#${a.id || ''}.${(a.className || '').toString().split(' ').filter(Boolean).join('.')}` : null;
        });
        await page.keyboard.press('Escape');
        await page.waitForTimeout(900);
        r.focusAfterClose = await page.evaluate(() => {
            const a = document.activeElement;
            return a ? `${a.tagName.toLowerCase()}#${a.id || ''}.${(a.className || '').toString().split(' ').filter(Boolean).join('.')}` : null;
        });

        r.errors = errors;
        await page.screenshot({ path: path.join(OUT, `after-${vp.name}.png`) });
        results.push(r);
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(results, null, 2));
})();
