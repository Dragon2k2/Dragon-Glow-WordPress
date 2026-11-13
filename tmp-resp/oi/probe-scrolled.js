/**
 * Probe chính xác: cuộn dialog rồi chụp mép phải, dump mọi cột pixel.
 * Tìm thanh cuộn (màu #d4c2c2 = 212,194,194) trong cả 2 modal.
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

    /* cuộn giữa dialog để thanh cuộn hiện */
    await page.evaluate((s) => { document.querySelector(s).scrollTop = 120; }, dialogSel);
    await page.waitForTimeout(400);

    const info = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, w: r.width, h: r.height };
    }, dialogSel);

    const file = path.join(OUT, `scrolled-${tag}.png`);
    await page.screenshot({ path: file, clip: { x: info.x + info.w - 24, y: info.y, width: 24, height: 300 } });

    const raw = await sharp(file).raw().toBuffer({ resolveWithObject: true });
    const y = 150;
    const cols = [];
    for (let x = 0; x < raw.info.width; x++) {
        const i = (y * raw.info.width + x) * raw.info.channels;
        cols.push({ x, rgb: [raw.data[i], raw.data[i + 1], raw.data[i + 2]] });
    }

    /* tìm pixel trùng màu thumb #d4c2c2 */
    const thumbHits = cols.filter((c) => c.rgb[0] === 212 && c.rgb[1] === 194 && c.rgb[2] === 194);
    const trackHits = cols.filter((c) => c.rgb[0] === 250 && c.rgb[1] === 249 && c.rgb[2] === 246);

    await ctx.close();
    return { tag, cols, thumbHits, trackHits };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const rm = await probe(browser, 'https://dragonglow.freehosting.dev/shipping-returns/',
        ['**/assets/css/shipping-returns.css*', SR_CSS], '[data-return-trigger]', '.dg-rm-dialog', 'return');
    const ca = await probe(browser, 'https://dragonglow.freehosting.dev/careers/',
        ['**/assets/css/careers.css*', CA_CSS], '[data-apply-trigger]', '.dg-apply-dialog', 'apply');
    await browser.close();
    console.log(JSON.stringify({ rm, ca }, null, 2));
})();
