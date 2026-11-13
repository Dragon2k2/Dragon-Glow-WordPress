/**
 * Verify cuối: so sánh scrollbar 2 modal + đảm bảo hành vi modal Return còn đúng.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_JS = fs.readFileSync(path.join(THEME, 'assets/js/shipping-returns.js'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const CA_CSS = fs.readFileSync(path.join(THEME, 'assets/css/careers.css'), 'utf8');

const VPS = [
    { name: '320', w: 320, h: 640 },
    { name: '390', w: 390, h: 844 },
    { name: '768', w: 768, h: 1024 },
    { name: '1280', w: 1280, h: 900 },
];

function sb(page, sel) {
    return page.evaluate((s) => {
        const el = document.querySelector(s);
        const bar = getComputedStyle(el, '::-webkit-scrollbar');
        const thumb = getComputedStyle(el, '::-webkit-scrollbar-thumb');
        const track = getComputedStyle(el, '::-webkit-scrollbar-track');
        return {
            width: bar.width,
            thumb: thumb.backgroundColor,
            thumbRadius: thumb.borderRadius,
            track: track.backgroundColor,
            trackRadius: track.borderRadius,
            transition: thumb.transition,
        };
    }, sel);
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = {};

    /* baseline Apply */
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
        await page.waitForTimeout(1000);
        out.applyBaseline = await sb(page, '.dg-apply-dialog');
        await ctx.close();
    }

    /* Return ở nhiều viewport */
    out.return = [];
    for (const vp of VPS) {
        const ctx = await browser.newContext({ viewport: { width: vp.w, height: vp.h } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.route('**/assets/css/shipping-returns.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_CSS }));
        await page.route('**/assets/js/shipping-returns.js*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/javascript', body: SR_JS }));
        await page.route('**/assets/css/style.css*', (r) =>
            r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

        await page.goto('https://dragonglow.freehosting.dev/shipping-returns/', { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(700);
        await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
        await page.waitForTimeout(900);

        const r = { vp: vp.name, scrollbar: await sb(page, '.dg-rm-dialog') };

        /* scroll hoạt động */
        r.maxScrollTop = await page.evaluate(() => {
            const el = document.querySelector('.dg-rm-dialog');
            el.scrollTop = 9999;
            const v = el.scrollTop;
            el.scrollTop = 0;
            return v;
        });

        /* close hoạt động */
        await page.locator('.dg-rm-close').click({ timeout: 8000 });
        await page.waitForTimeout(800);
        r.closed = await page.evaluate(() => document.getElementById('dg-return-modal').hasAttribute('hidden'));

        r.errors = errors;
        out.return.push(r);
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
