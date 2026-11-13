/**
 * Kiểm chứng với Chrome cưỡng chế classic (non-overlay) scrollbar:
 *   --disable-features=OverlayScrollbar,FluentOverlayScrollbar,FluentScrollbar
 * Đo pixel thật ở mép phải dialog + so sánh 2 modal.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const CA_CSS = fs.readFileSync(path.join(THEME, 'assets/css/careers.css'), 'utf8');
const OUT = path.join(__dirname, 'rm-scrollbar');
fs.mkdirSync(OUT, { recursive: true });

const ARGS = ['--disable-features=OverlayScrollbar,FluentOverlayScrollbar,FluentScrollbar',
    '--force-device-scale-factor=1'];

async function probe(browser, url, cssRoute, openSel, dialogSel, tag) {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();
    await page.route(cssRoute[0], (r) => r.fulfill({ status: 200, contentType: 'text/css', body: cssRoute[1] }));
    await page.route('**/assets/css/style.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

    await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate((s) => document.querySelector(s)?.click(), openSel);
    await page.waitForTimeout(1200);
    await page.evaluate((s) => { document.querySelector(s).scrollTop = 150; }, dialogSel);
    await page.waitForTimeout(500);

    const info = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const r = el.getBoundingClientRect();
        return {
            x: r.x, y: r.y, w: r.width, h: r.height,
            gutterPx: el.offsetWidth - el.clientWidth,
            scrollHeight: el.scrollHeight,
            clientHeight: el.clientHeight,
        };
    }, dialogSel);

    const file = path.join(OUT, `classic-${tag}.png`);
    await page.screenshot({ path: file, clip: { x: info.x + info.w - 24, y: info.y, width: 24, height: 260 } });

    const raw = await sharp(file).raw().toBuffer({ resolveWithObject: true });
    const rows = [];
    for (const y of [20, 130, 240]) {
        const cols = [];
        for (let x = 0; x < raw.info.width; x++) {
            const i = (y * raw.info.width + x) * raw.info.channels;
            cols.push(`${raw.data[i]},${raw.data[i + 1]},${raw.data[i + 2]}`);
        }
        rows.push({ y, cols });
    }

    await ctx.close();
    return { tag, info, rows };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome', args: ARGS });
    const rm = await probe(browser, 'https://dragonglow.freehosting.dev/shipping-returns/',
        ['**/assets/css/shipping-returns.css*', SR_CSS], '[data-return-trigger]', '.dg-rm-dialog', 'return');
    const ca = await probe(browser, 'https://dragonglow.freehosting.dev/careers/',
        ['**/assets/css/careers.css*', CA_CSS], '[data-apply-trigger]', '.dg-apply-dialog', 'apply');
    await browser.close();
    console.log(JSON.stringify({ rm, ca }, null, 2));
})();
