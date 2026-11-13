/**
 * Đo pixel thật của thanh cuộn (track + thumb) ở cả 2 modal để so sánh màu.
 * Dùng sharp để đọc PNG crop.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const OUT = path.join(__dirname, 'rm-scrollbar');
fs.mkdirSync(OUT, { recursive: true });

const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const CA_CSS = fs.readFileSync(path.join(THEME, 'assets/css/careers.css'), 'utf8');

const SR_URL = 'https://dragonglow.freehosting.dev/shipping-returns/';
const CA_URL = 'https://dragonglow.freehosting.dev/careers/';

/* đọc màu tại 1 điểm trong ảnh PNG */
async function px(file, x, y) {
    const { data, info } = await sharp(file).raw().toBuffer({ resolveWithObject: true });
    const idx = (y * info.width + x) * info.channels;
    return { r: data[idx], g: data[idx + 1], b: data[idx + 2], a: data[idx + 3] };
}

async function shoot(browser, url, cssRoute, openSel, dialogSel, tag) {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();
    await page.route(cssRoute[0], (r) => r.fulfill({ status: 200, contentType: 'text/css', body: cssRoute[1] }));
    await page.route('**/assets/css/style.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

    await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate((s) => document.querySelector(s)?.click(), openSel);
    await page.waitForTimeout(900);

    const info = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const r = el.getBoundingClientRect();
        return { x: r.x, y: r.y, w: r.width, h: r.height };
    }, dialogSel);

    const file = path.join(OUT, `${tag}-scrollbar.png`);
    /* crop 12px sát mép phải của dialog, toàn chiều cao */
    await page.screenshot({
        path: file,
        clip: { x: info.x + info.w - 12, y: info.y, width: 12, height: Math.min(info.h, 500) },
    });

    const meta = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const cs = getComputedStyle(el);
        return {
            scrollbarWidth: cs.scrollbarWidth,
            gutterPx: el.offsetWidth - el.clientWidth,
            scrollHeight: el.scrollHeight,
            clientHeight: el.clientHeight,
        };
    }, dialogSel);

    await ctx.close();
    return { file, info, meta };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });

    const rm = await shoot(browser, SR_URL, ['**/assets/css/shipping-returns.css*', SR_CSS],
        '[data-return-trigger]', '.dg-rm-dialog', 'return');
    const ca = await shoot(browser, CA_URL, ['**/assets/css/careers.css*', CA_CSS],
        '[data-apply-trigger]', '.dg-apply-dialog', 'apply');

    await browser.close();

    const out = { return: rm.meta, apply: ca.meta };

    /* quét cột pixel ở mép phải để tìm track & thumb */
    for (const [tag, shot] of [['return', rm], ['apply', ca]]) {
        const buf = await sharp(shot.file).raw().toBuffer({ resolveWithObject: true });
        const { data, info } = { data: buf.data, info: buf.width ? { width: buf.info.width, height: buf.info.height, channels: buf.info.channels } : buf.info };
        const cols = [];
        for (let x = 0; x < info.width; x++) {
            /* lấy màu ở giữa chiều cao crop */
            const y = Math.floor(info.height / 2);
            const i = (y * info.width + x) * info.channels;
            cols.push({ x, rgb: [data[i], data[i + 1], data[i + 2]] });
        }
        out[tag].scrollbarColumns = cols;
    }

    console.log(JSON.stringify(out, null, 2));
})();
