/**
 * Chrome dùng overlay scrollbar (không chiếm layout) → phải cuộn bằng wheel
 * rồi chụp NGAY để scrollbar hiện, mới đo được màu/độ rộng thật.
 *
 * So sánh 3 biến thể trên modal Return:
 *   A — token-based (bản hiện tại): width: var(--sr-scrollbar-size)
 *   B — literal 8px + màu literal (giống careers.css)
 *   C — token-based width + literal màu
 * và so với modal Apply (careers.css).
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

const THUMB = '212,194,194';
const TRACK = '250,249,246';
const HOVER = '130,116,115';

async function scrollAndShoot(page, dialogSel, tag) {
    const box = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, w: r.width, h: r.height };
    }, dialogSel);

    /* đưa chuột vào giữa dialog để hover scrollbar + wheel */
    await page.mouse.move(box.x + box.w - 6, box.y + box.h / 2);
    await page.mouse.wheel(0, 200);
    await page.waitForTimeout(60);

    const file = path.join(OUT, `wheel-${tag}.png`);
    await page.screenshot({ path: file, clip: { x: box.x + box.w - 24, y: box.y + 40, width: 24, height: 240 } });

    const raw = await sharp(file).raw().toBuffer({ resolveWithObject: true });
    const y = Math.floor(raw.info.height / 2);
    const cols = [];
    for (let x = 0; x < raw.info.width; x++) {
        const i = (y * raw.info.width + x) * raw.info.channels;
        cols.push(`${raw.data[i]},${raw.data[i + 1]},${raw.data[i + 2]}`);
    }
    return { cols, file };
}

function classify(cols) {
    const thumb = [], track = [], hover = [];
    cols.forEach((c, x) => {
        if (c === THUMB) thumb.push(x);
        else if (c === TRACK) track.push(x);
        else if (c === HOVER) hover.push(x);
    });
    return { thumb, track, hover };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = {};

    /* ── Apply (baseline, careers.css) ── */
    {
        const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const page = await ctx.newPage();
        await page.route('**/assets/css/careers.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: CA_CSS }));
        await page.route('**/assets/css/style.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));
        await page.goto('https://dragonglow.freehosting.dev/careers/', { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(700);
        await page.evaluate(() => document.querySelector('[data-apply-trigger]')?.click());
        await page.waitForTimeout(1200);
        const s = await scrollAndShoot(page, '.dg-apply-dialog', 'apply');
        out.apply = { cols: s.cols, ...classify(s.cols) };
        await ctx.close();
    }

    /* ── Return: 3 biến thể ── */
    const variants = {
        A_token: '',
        B_literal: `.dg-rm-dialog::-webkit-scrollbar { width: 8px; }
          .dg-rm-dialog::-webkit-scrollbar-track { background: #faf9f6; border-radius: 4px; }
          .dg-rm-dialog::-webkit-scrollbar-thumb { background: #d4c2c2; border-radius: 4px; }`,
        C_tokenWidth_literalColor: `.dg-rm-dialog::-webkit-scrollbar-track { background: #faf9f6; border-radius: 4px; }
          .dg-rm-dialog::-webkit-scrollbar-thumb { background: #d4c2c2; border-radius: 4px; }`,
    };

    for (const [name, extraCss] of Object.entries(variants)) {
        const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const page = await ctx.newPage();
        await page.route('**/assets/css/shipping-returns.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_CSS }));
        await page.route('**/assets/css/style.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));
        await page.goto('https://dragonglow.freehosting.dev/shipping-returns/', { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(700);
        await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
        await page.waitForTimeout(1200);
        if (extraCss) await page.addStyleTag({ content: extraCss });
        await page.waitForTimeout(300);
        const s = await scrollAndShoot(page, '.dg-rm-dialog', name);
        out[name] = { cols: s.cols, ...classify(s.cols) };
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
