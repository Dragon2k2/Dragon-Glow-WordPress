/**
 * Kiểm chứng: .dg-apply-actions — 2 nút cùng MỘT HÀNG NGANG ở mobile.
 *
 * CSS lấy trực tiếp từ file thật trên disk (assets/css/careers.css): gỡ link
 * stylesheet cũ trên live rồi inject toàn bộ file mới → đo đúng code vừa sửa.
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
    { name: '390', width: 390, height: 844, mobile: true },
    { name: '639', width: 639, height: 844, mobile: true },
    { name: '640', width: 640, height: 900, mobile: false },
    { name: '768', width: 768, height: 1024, mobile: false },
    { name: '1280', width: 1280, height: 900, mobile: false },
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const report = { cssBytes: css.length, viewports: [] };

    for (const vp of VPS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });

        /* gỡ stylesheet careers.css cũ, inject bản trên disk */
        await page.evaluate(() => {
            document.querySelectorAll('link[href*="careers.css"]').forEach((l) => l.remove());
        });
        await page.addStyleTag({ content: css });

        /* mở modal */
        await page.evaluate(() => {
            const t = document.querySelector('[data-apply-trigger]');
            if (t) t.click();
        });
        await page.waitForTimeout(800);

        const info = await page.evaluate(() => {
            const modal = document.getElementById('dg-apply-modal');
            const box = document.querySelector('.dg-apply-actions');
            const cancel = document.querySelector('.dg-apply-actions .dg-careers-btn--secondary');
            const submit = document.querySelector('#dg-apply-submit');
            if (!box || !cancel || !submit) return { missing: true, modalFound: !!modal };

            const cs = getComputedStyle(box);
            const c = cancel.getBoundingClientRect();
            const s = submit.getBoundingClientRect();
            const ccs = getComputedStyle(cancel);
            const scs = getComputedStyle(submit);
            const dialog = document.querySelector('.dg-apply-dialog');
            const dr = dialog ? dialog.getBoundingClientRect() : null;

            return {
                modalOpen: modal && !modal.hasAttribute('hidden'),
                flexDirection: cs.flexDirection,
                gap: cs.gap,
                justifyContent: cs.justifyContent,
                sameRow: Math.abs(c.top - s.top) < 2,
                cancelRect: { x: +c.x.toFixed(1), y: +c.y.toFixed(1), w: +c.width.toFixed(1), h: +c.height.toFixed(1) },
                submitRect: { x: +s.x.toFixed(1), y: +s.y.toFixed(1), w: +s.width.toFixed(1), h: +s.height.toFixed(1) },
                gapPx: +(s.left - c.right).toFixed(1),
                noOverlap: c.right <= s.left + 0.5,
                cancelFontSize: ccs.fontSize,
                submitFontSize: scs.fontSize,
                cancelMinHeight: ccs.minHeight,
                submitMinHeight: scs.minHeight,
                overflowX: document.documentElement.scrollWidth > window.innerWidth,
                scrollWidth: document.documentElement.scrollWidth,
                innerWidth: window.innerWidth,
                insideDialog: dr ? (c.right <= dr.right + 0.5 && s.right <= dr.right + 0.5) : null,
                textClipped: {
                    cancel: cancel.scrollWidth > cancel.clientWidth + 1,
                    submit: submit.scrollWidth > submit.clientWidth + 1,
                },
                touchTargetOk: c.height >= 44 && s.height >= 44,
            };
        });

        const el = page.locator('.dg-apply-actions');
        if (await el.count()) {
            await el.screenshot({ path: path.join(OUT, `actions-${vp.name}.png`) });
        }

        report.viewports.push({ viewport: vp.name, expectSameRow: vp.mobile, ...info, errors });
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(report, null, 2));
})();
