/**
 * Chụp vùng mép phải của cả 2 modal (rộng 30px) để so sánh trực quan.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const CA_CSS = fs.readFileSync(path.join(THEME, 'assets/css/careers.css'), 'utf8');
const OUT = path.join(__dirname, 'rm-scrollbar');
fs.mkdirSync(OUT, { recursive: true });

async function shoot(browser, url, cssRoute, openSel, dialogSel, tag) {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2 });
    const page = await ctx.newPage();
    await page.route(cssRoute[0], (r) => r.fulfill({ status: 200, contentType: 'text/css', body: cssRoute[1] }));
    await page.route('**/assets/css/style.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

    await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate((s) => document.querySelector(s)?.click(), openSel);
    await page.waitForTimeout(1200);

    /* ẩn animation transform để ảnh ổn định */
    await page.evaluate((s) => {
        const el = document.querySelector(s);
        el.style.transform = 'none';
    }, dialogSel);
    await page.waitForTimeout(300);

    const info = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, w: r.width, h: r.height, scrollHeight: el.scrollHeight, clientHeight: el.clientHeight };
    }, dialogSel);

    await page.screenshot({
        path: path.join(OUT, `visual-${tag}.png`),
        clip: { x: info.x + info.w - 40, y: info.y, width: 40, height: 260 },
    });
    await ctx.close();
    return info;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const rm = await shoot(browser, 'https://dragonglow.freehosting.dev/shipping-returns/',
        ['**/assets/css/shipping-returns.css*', SR_CSS], '[data-return-trigger]', '.dg-rm-dialog', 'return');
    const ca = await shoot(browser, 'https://dragonglow.freehosting.dev/careers/',
        ['**/assets/css/careers.css*', CA_CSS], '[data-apply-trigger]', '.dg-apply-dialog', 'apply');
    await browser.close();
    console.log(JSON.stringify({ rm, ca }, null, 2));
})();
