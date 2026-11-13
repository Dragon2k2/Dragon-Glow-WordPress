/**
 * Kiểm tra giả thuyết: Chrome có resolve var() trong ::-webkit-scrollbar width không?
 * Test 3 biến thể bằng addStyleTag runtime trên modal Return.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const OUT = path.join(__dirname, 'rm-scrollbar');
fs.mkdirSync(OUT, { recursive: true });

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

async function sample(page, tag) {
    const info = await page.evaluate(() => {
        const el = document.querySelector('.dg-rm-dialog');
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, w: r.width, h: r.height };
    });
    const file = path.join(OUT, `probe-${tag}.png`);
    await page.screenshot({ path: file, clip: { x: info.x + info.w - 12, y: info.y, width: 12, height: 200 } });
    const raw = await sharp(file).raw().toBuffer({ resolveWithObject: true });
    const y = Math.floor(raw.info.height / 2);
    const cols = [];
    for (let x = 0; x < raw.info.width; x++) {
        const i = (y * raw.info.width + x) * raw.info.channels;
        cols.push(`${raw.data[i]},${raw.data[i + 1]},${raw.data[i + 2]}`);
    }
    return cols;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();

    await page.route('**/assets/css/shipping-returns.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_CSS }));
    await page.route('**/assets/css/style.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(1000);

    const out = {};

    out.baseline = await sample(page, 'baseline');

    /* biến thể A: width literal 8px (như careers.css) */
    await page.addStyleTag({ content: `
      .dg-rm-dialog::-webkit-scrollbar { width: 8px; }
      .dg-rm-dialog::-webkit-scrollbar-track { background: #faf9f6; border-radius: 4px; }
      .dg-rm-dialog::-webkit-scrollbar-thumb { background: #d4c2c2; border-radius: 4px; }
    `});
    await page.waitForTimeout(400);
    out.literal8px = await sample(page, 'literal');

    /* biến thể B: width literal, màu literal */
    await page.addStyleTag({ content: `
      .dg-rm-dialog::-webkit-scrollbar { width: 10px; }
      .dg-rm-dialog::-webkit-scrollbar-track { background: #ff00ff; }
      .dg-rm-dialog::-webkit-scrollbar-thumb { background: #00ff00; }
    `});
    await page.waitForTimeout(400);
    out.literal10px = await sample(page, 'literal10');

    console.log(JSON.stringify(out, null, 2));
    await browser.close();
})();
