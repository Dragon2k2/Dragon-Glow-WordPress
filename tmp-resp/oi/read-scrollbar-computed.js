/**
 * Đọc computed style của pseudo-element scrollbar trên chính 2 dialog thật,
 * và so sánh từng thuộc tính.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const CA_CSS = fs.readFileSync(path.join(THEME, 'assets/css/careers.css'), 'utf8');

async function read(browser, url, cssRoute, openSel, dialogSel, tag) {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();
    await page.route(cssRoute[0], (r) => r.fulfill({ status: 200, contentType: 'text/css', body: cssRoute[1] }));
    await page.route('**/assets/css/style.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));
    await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate((s) => document.querySelector(s)?.click(), openSel);
    await page.waitForTimeout(1000);

    const res = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const bar = getComputedStyle(el, '::-webkit-scrollbar');
        const thumb = getComputedStyle(el, '::-webkit-scrollbar-thumb');
        const track = getComputedStyle(el, '::-webkit-scrollbar-track');
        const thumbHover = getComputedStyle(el, '::-webkit-scrollbar-thumb:hover');
        const cs = getComputedStyle(el);
        return {
            barWidth: bar.width,
            thumbBg: thumb.backgroundColor,
            thumbRadius: thumb.borderRadius,
            thumbTransition: thumb.transition,
            trackBg: track.backgroundColor,
            trackRadius: track.borderRadius,
            thumbHoverBg: thumbHover.backgroundColor,
            scrollbarWidth: cs.scrollbarWidth,
            scrollbarColor: cs.scrollbarColor,
        };
    }, dialogSel);

    await ctx.close();
    return { tag, res };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const rm = await read(browser, 'https://dragonglow.freehosting.dev/shipping-returns/',
        ['**/assets/css/shipping-returns.css*', SR_CSS], '[data-return-trigger]', '.dg-rm-dialog', 'return');
    const ca = await read(browser, 'https://dragonglow.freehosting.dev/careers/',
        ['**/assets/css/careers.css*', CA_CSS], '[data-apply-trigger]', '.dg-apply-dialog', 'apply');
    await browser.close();
    console.log(JSON.stringify({ rm, ca }, null, 2));
})();
