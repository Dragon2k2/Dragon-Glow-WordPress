/**
 * Bổ sung: focus restore bằng click THẬT vào trigger (không phải .click() JS),
 * focus trap, và nhánh prefers-reduced-motion.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const JS = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/js/shipping-returns.js');
const CSS = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/shipping-returns.css');
const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';
const js = fs.readFileSync(JS, 'utf8');
const css = fs.readFileSync(CSS, 'utf8');

async function run(browser, reducedMotion, label) {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e.message)));

    await page.route('**/assets/js/shipping-returns.js*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/javascript', body: js }));
    await page.route('**/assets/css/shipping-returns.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: css }));

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(800);

    const out = { label, errors };

    /* click THẬT trigger → mở → đóng bằng ESC → focus phải về trigger */
    await page.locator('[data-return-trigger]').click();
    await page.waitForTimeout(800);
    out.focusInModal = await page.evaluate(() => {
        const a = document.activeElement;
        return a ? `${a.tagName.toLowerCase()}#${a.id || ''}.${(a.className || '').toString().split(' ').filter(Boolean).join('.')}` : null;
    });
    await page.keyboard.press('Escape');
    await page.waitForTimeout(900);
    out.focusAfterEsc = await page.evaluate(() => {
        const a = document.activeElement;
        return a ? `${a.tagName.toLowerCase()}#${a.id || ''}.${String(a.className || '').split(' ').filter(Boolean).join('.')}` : null;
    });

    /* click THẬT trigger → mở → click Close → focus về trigger */
    await page.locator('[data-return-trigger]').click();
    await page.waitForTimeout(800);
    await page.locator('.dg-rm-close').click();
    await page.waitForTimeout(900);
    out.focusAfterCloseBtn = await page.evaluate(() => {
        const a = document.activeElement;
        return a ? `${a.tagName.toLowerCase()}#${a.id || ''}.${String(a.className || '').split(' ').filter(Boolean).join('.')}` : null;
    });
    out.closedAfterCloseBtn = await page.evaluate(() => document.getElementById('dg-return-modal').hasAttribute('hidden'));

    /* focus trap: Tab 12 lần không thoát khỏi dialog */
    await page.locator('[data-return-trigger]').click();
    await page.waitForTimeout(800);
    const trap = [];
    for (let i = 0; i < 12; i++) {
        await page.keyboard.press('Tab');
        trap.push(await page.evaluate(() => {
            const a = document.activeElement;
            return a ? `${a.tagName.toLowerCase()}#${a.id || ''}.${String(a.className || '').split(' ').filter(Boolean).join('.')}` : null;
        }));
    }
    out.trapStaysInside = trap.every((t) => t && !/^body/i.test(t));
    out.trapSample = trap.slice(0, 4);
    await page.keyboard.press('Escape');
    await page.waitForTimeout(800);

    await ctx.close();
    return out;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const a = await run(browser, 'no-preference', 'animation ON');
    const b = await run(browser, 'reduce', 'animation OFF');
    await browser.close();
    console.log(JSON.stringify([a, b], null, 2));
})();
