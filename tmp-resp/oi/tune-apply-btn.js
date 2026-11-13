/**
 * Tìm bộ thông số mobile để nhãn nút luôn nằm 1 DÒNG ở 320px
 * (nút phụ "Cancel" ngắn, nút chính "Send application" dài).
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const CSS_FILE = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/careers.css');
const URL = 'https://dragonglow.freehosting.dev/careers/';
const base = fs.readFileSync(CSS_FILE, 'utf8');

const VARIANTS = [
    { name: 'current', fs: '0.75rem', ls: null, pad: '0.75rem 0.875rem' },
    { name: 'ls-0.04', fs: '0.75rem', ls: '0.04em', pad: '0.75rem 0.875rem' },
    { name: 'ls-0.04-pad-0.625', fs: '0.75rem', ls: '0.04em', pad: '0.75rem 0.625rem' },
    { name: 'fs-0.6875-ls-0.04', fs: '0.6875rem', ls: '0.04em', pad: '0.75rem 0.625rem' },
    { name: 'fs-0.6875-ls-0.04-pad-0.5', fs: '0.6875rem', ls: '0.04em', pad: '0.75rem 0.5rem' },
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = [];

    for (const v of VARIANTS) {
        const css = base.replace(
            /\.dg-apply-actions \.dg-careers-btn \{([\s\S]*?)\}/,
            (m, body) => `.dg-apply-actions .dg-careers-btn {${body}}`
        );
        /* override biến thể vào khối mobile */
        const override = `
@media (max-width: 639px) {
    .dg-apply-actions .dg-careers-btn {
        font-size: ${v.fs};
        letter-spacing: ${v.ls || 'inherit'};
        padding: ${v.pad};
    }
}`;
        for (const w of [320, 360, 390]) {
            const ctx = await browser.newContext({ viewport: { width: w, height: 800 } });
            const page = await ctx.newPage();
            await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
            await page.evaluate(() => document.querySelectorAll('link[href*="careers.css"]').forEach((l) => l.remove()));
            await page.addStyleTag({ content: css });
            await page.addStyleTag({ content: override });
            await page.evaluate(() => document.querySelector('[data-apply-trigger]')?.click());
            await page.waitForTimeout(700);

            const m = await page.evaluate(() => {
                const c = document.querySelector('.dg-apply-actions .dg-careers-btn--secondary');
                const s = document.querySelector('#dg-apply-submit');
                if (!c || !s) return null;
                const cb = c.getBoundingClientRect();
                const sb = s.getBoundingClientRect();
                /* số dòng text: so chiều cao nút với chiều cao 1 dòng */
                const lh = parseFloat(getComputedStyle(s).lineHeight) || 15;
                const lines = Math.round((sb.height - parseFloat(getComputedStyle(s).paddingTop) * 2) / lh);
                return {
                    sameRow: Math.abs(cb.top - sb.top) < 2,
                    cancelW: +cb.width.toFixed(1),
                    submitW: +sb.width.toFixed(1),
                    submitH: +sb.height.toFixed(1),
                    cancelH: +cb.height.toFixed(1),
                    lines,
                    oneLine: lines <= 1,
                    equalHeight: Math.abs(cb.height - sb.height) < 1,
                    clipped: s.scrollWidth > s.clientWidth + 1 || c.scrollWidth > c.clientWidth + 1,
                    overflowX: document.documentElement.scrollWidth > window.innerWidth,
                };
            });
            out.push({ variant: v.name, w, ...m });
            await ctx.close();
        }
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
