/**
 * Kiểm chứng: scrollbar của modal Return (.dg-rm-dialog) phải giống modal Apply (.dg-apply-dialog).
 *
 * Inject CSS local lên site live (Careers + Shipping & Returns), đo:
 *  - kích thước thanh cuộn (webkit) — clientWidth vs offsetWidth
 *  - màu track / thumb (đọc pseudo-element ::-webkit-scrollbar-* không được,
 *    nên dùng cách tính từ computed style + chụp pixel thật)
 *  - scrollbar-width / scrollbar-color (Firefox props, đọc được computed)
 *  - scroll hoạt động (scrollTop đổi)
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const OUT = path.join(__dirname, 'rm-scrollbar');
fs.mkdirSync(OUT, { recursive: true });

const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const CA_CSS = fs.readFileSync(path.join(THEME, 'assets/css/careers.css'), 'utf8');

const SR_URL = 'https://dragonglow.freehosting.dev/shipping-returns/';
const CA_URL = 'https://dragonglow.freehosting.dev/careers/';

async function measure(page, sel) {
    return page.evaluate((s) => {
        const el = document.querySelector(s);
        if (!el) return { missing: true };
        const cs = getComputedStyle(el);
        return {
            scrollbarWidth: cs.scrollbarWidth,
            scrollbarColor: cs.scrollbarColor,
            overflowY: cs.overflowY,
            scrollHeight: el.scrollHeight,
            clientHeight: el.clientHeight,
            offsetWidth: el.offsetWidth,
            clientWidth: el.clientWidth,
            /* bề rộng thanh cuộn = offsetWidth - clientWidth (đã trừ border) */
            gutterPx: el.offsetWidth - el.clientWidth,
            scrollable: el.scrollHeight > el.clientHeight,
        };
    }, sel);
}

async function scrollAndRead(page, sel) {
    return page.evaluate((s) => {
        const el = document.querySelector(s);
        if (!el) return null;
        el.scrollTop = 9999;
        const max = el.scrollTop;
        el.scrollTop = 0;
        return max;
    }, sel);
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = {};

    /* ── Shipping & Returns (Return modal) ── */
    {
        const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.route('**/assets/css/shipping-returns.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_CSS }));
        await page.route('**/assets/css/style.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

        await page.goto(SR_URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(700);
        await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
        await page.waitForTimeout(800);

        out.returnModal = await measure(page, '.dg-rm-dialog');
        out.returnModal.maxScrollTop = await scrollAndRead(page, '.dg-rm-dialog');
        out.returnModal.errors = errors;

        await page.screenshot({ path: path.join(OUT, 'return-modal-390.png') });

        /* crop vùng thanh cuộn để so pixel thật */
        const box = await page.evaluate(() => {
            const el = document.querySelector('.dg-rm-dialog');
            const r = el.getBoundingClientRect();
            return { x: r.x, y: r.y, width: r.width, height: r.height };
        });
        await page.screenshot({
            path: path.join(OUT, 'return-scrollbar-crop.png'),
            clip: { x: box.x + box.width - 14, y: box.y, width: 14, height: Math.min(box.height, 400) },
        });
        await ctx.close();
    }

    /* ── Careers (Apply modal) ── */
    {
        const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.route('**/assets/css/careers.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: CA_CSS }));
        await page.route('**/assets/css/style.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

        await page.goto(CA_URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(700);

        const opened = await page.evaluate(() => {
            const t = document.querySelector('[data-apply-trigger]');
            if (!t) return false;
            t.click();
            return true;
        });
        await page.waitForTimeout(900);

        out.applyModal = await measure(page, '.dg-apply-dialog');
        out.applyModal.opened = opened;
        out.applyModal.maxScrollTop = await scrollAndRead(page, '.dg-apply-dialog');
        out.applyModal.errors = errors;

        await page.screenshot({ path: path.join(OUT, 'apply-modal-390.png') });

        const box = await page.evaluate(() => {
            const el = document.querySelector('.dg-apply-dialog');
            if (!el) return null;
            const r = el.getBoundingClientRect();
            return { x: r.x, y: r.y, width: r.width, height: r.height };
        });
        if (box) {
            await page.screenshot({
                path: path.join(OUT, 'apply-scrollbar-crop.png'),
                clip: { x: box.x + box.width - 14, y: box.y, width: 14, height: Math.min(box.height, 400) },
            });
        }
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
