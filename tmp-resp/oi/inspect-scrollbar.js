/**
 * Điều tra sâu: token resolve, pseudo-element rules có áp dụng không,
 * và quét dải pixel cột dọc cạnh mép phải ở nhiều vị trí y.
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

async function shoot(browser, url, cssRoute, openSel, dialogSel, tag) {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();
    await page.route(cssRoute[0], (r) => r.fulfill({ status: 200, contentType: 'text/css', body: cssRoute[1] }));
    await page.route('**/assets/css/style.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

    await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate((s) => document.querySelector(s)?.click(), openSel);
    await page.waitForTimeout(1200);

    const meta = await page.evaluate((s) => {
        const el = document.querySelector(s);
        const cs = getComputedStyle(el);
        const r = el.getBoundingClientRect();
        return {
            rect: { x: r.x, y: r.y, w: r.width, h: r.height },
            scrollbarWidth: cs.scrollbarWidth,
            scrollbarColor: cs.scrollbarColor,
            gutterPx: el.offsetWidth - el.clientWidth,
            borderRight: cs.borderRightWidth,
            paddingRight: cs.paddingRight,
            tokens: {
                srThumb: cs.getPropertyValue('--sr-scrollbar-thumb').trim(),
                srTrack: cs.getPropertyValue('--sr-scrollbar-track').trim(),
                srSize: cs.getPropertyValue('--sr-scrollbar-size').trim(),
                aOutline: cs.getPropertyValue('--a-outline').trim(),
                aSurfaceLow: cs.getPropertyValue('--a-surface-low').trim(),
            },
            /* stylesheet nào đang load */
            sheets: [...document.styleSheets].map((s) => (s.href || 'inline').split('/').pop()),
        };
    }, dialogSel);

    const file = path.join(OUT, `${tag}-strip.png`);
    await page.screenshot({
        path: file,
        clip: { x: meta.rect.x + meta.rect.w - 20, y: meta.rect.y, width: 20, height: Math.min(meta.rect.h, 500) },
    });

    /* quét màu ở 3 y khác nhau, cột x=11..19 */
    const raw = await sharp(file).raw().toBuffer({ resolveWithObject: true });
    const rows = [];
    for (const y of [5, Math.floor(raw.info.height / 2), raw.info.height - 5]) {
        const cols = [];
        for (let x = 0; x < raw.info.width; x++) {
            const i = (y * raw.info.width + x) * raw.info.channels;
            cols.push(`${raw.data[i]},${raw.data[i + 1]},${raw.data[i + 2]}`);
        }
        rows.push({ y, cols });
    }

    await ctx.close();
    return { meta, file, rows };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const rm = await shoot(browser, SR_URL, ['**/assets/css/shipping-returns.css*', SR_CSS],
        '[data-return-trigger]', '.dg-rm-dialog', 'return');
    const ca = await shoot(browser, CA_URL, ['**/assets/css/careers.css*', CA_CSS],
        '[data-apply-trigger]', '.dg-apply-dialog', 'apply');
    await browser.close();
    console.log(JSON.stringify({ return: rm, apply: ca }, null, 2));
})();
