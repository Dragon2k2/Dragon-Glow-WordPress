/**
 * Kiểm chứng cuối: nút Cancel + Send application cùng 1 HÀNG NGANG ở mobile.
 * Chụp ảnh tên mới (FINAL-*) để không bị cache ảnh cũ.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const CSS_FILE = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/careers.css');
const URL = 'https://dragonglow.freehosting.dev/careers/';
const OUT = path.join(__dirname, 'apply-actions');
fs.mkdirSync(OUT, { recursive: true });
const css = fs.readFileSync(CSS_FILE, 'utf8');

const VPS = [
    { name: '320', width: 320, height: 700, mobile: true },
    { name: '360', width: 360, height: 800, mobile: true },
    { name: '390', width: 390, height: 844, mobile: true },
    { name: '639', width: 639, height: 844, mobile: true },
    { name: '640', width: 640, height: 900, mobile: false },
    { name: '768', width: 768, height: 1024, mobile: false },
    { name: '1280', width: 1280, height: 900, mobile: false },
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = [];

    for (const vp of VPS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.evaluate(() => document.querySelectorAll('link[href*="careers.css"]').forEach((l) => l.remove()));
        await page.addStyleTag({ content: css });
        await page.evaluate(() => document.querySelector('[data-apply-trigger]')?.click());
        await page.waitForTimeout(800);

        const m = await page.evaluate(() => {
            const box = document.querySelector('.dg-apply-actions');
            const c = document.querySelector('.dg-apply-actions .dg-careers-btn--secondary');
            const s = document.querySelector('#dg-apply-submit');
            const cs = getComputedStyle(box);
            const cb = c.getBoundingClientRect();
            const sb = s.getBoundingClientRect();
            const scs = getComputedStyle(s);
            const lh = parseFloat(scs.lineHeight) || 15;
            const lines = Math.round((sb.height - parseFloat(scs.paddingTop) * 2) / lh);
            const dialog = document.querySelector('.dg-apply-dialog').getBoundingClientRect();
            return {
                flexDirection: cs.flexDirection,
                justifyContent: cs.justifyContent,
                sameRow: Math.abs(cb.top - sb.top) < 2,
                cancel: { w: +cb.width.toFixed(1), h: +cb.height.toFixed(1) },
                submit: { w: +sb.width.toFixed(1), h: +sb.height.toFixed(1) },
                equalHeight: Math.abs(cb.height - sb.height) < 1,
                submitLines: lines,
                gapPx: +(sb.left - cb.right).toFixed(1),
                insideDialog: cb.right <= dialog.right + 0.5 && sb.right <= dialog.right + 0.5,
                overflowX: document.documentElement.scrollWidth > window.innerWidth,
                clipped: s.scrollWidth > s.clientWidth + 1 || c.scrollWidth > c.clientWidth + 1,
                touchTargetOk: cb.height >= 44 && sb.height >= 44,
            };
        });

        await page.locator('.dg-apply-actions').screenshot({ path: path.join(OUT, `FINAL-${vp.name}.png`) });
        out.push({ vp: vp.name, isMobile: vp.mobile, ...m, errors });
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
