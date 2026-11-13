/**
 * Đo chính xác số DÒNG text thật của nhãn trong nút (dùng Range.getClientRects),
 * không suy ra từ chiều cao nút (padding làm sai số).
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const CSS_FILE = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/careers.css');
const URL = 'https://dragonglow.freehosting.dev/careers/';
const css = fs.readFileSync(CSS_FILE, 'utf8');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = [];

    for (const w of [320, 360, 390, 639]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 800 } });
        const page = await ctx.newPage();
        await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.evaluate(() => document.querySelectorAll('link[href*="careers.css"]').forEach((l) => l.remove()));
        await page.addStyleTag({ content: css });
        await page.evaluate(() => document.querySelector('[data-apply-trigger]')?.click());
        await page.waitForTimeout(700);

        const m = await page.evaluate(() => {
            const textLines = (el) => {
                const r = document.createRange();
                r.selectNodeContents(el);
                const rects = [...r.getClientRects()].filter((x) => x.width > 0 && x.height > 0);
                /* gom theo top → số dòng */
                const tops = [];
                rects.forEach((x) => {
                    if (!tops.some((t) => Math.abs(t - x.top) < 2)) tops.push(x.top);
                });
                return tops.length;
            };
            const c = document.querySelector('.dg-apply-actions .dg-careers-btn--secondary');
            const s = document.querySelector('#dg-apply-submit');
            const cs = getComputedStyle(s);
            const cb = c.getBoundingClientRect();
            const sb = s.getBoundingClientRect();
            /* đo độ rộng text chưa wrap bằng cách clone vào span inline-block */
            const probe = document.createElement('span');
            probe.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;';
            probe.style.font = cs.font;
            probe.style.letterSpacing = cs.letterSpacing;
            probe.style.textTransform = cs.textTransform;
            probe.textContent = s.textContent.trim();
            document.body.appendChild(probe);
            const textW = probe.getBoundingClientRect().width;
            probe.remove();

            return {
                cancelLines: textLines(c),
                submitLines: textLines(s),
                submitTextWidth: +textW.toFixed(1),
                submitBoxWidth: +sb.width.toFixed(1),
                submitContentWidth: +(sb.width - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight)).toFixed(1),
                fontSize: cs.fontSize,
                letterSpacing: cs.letterSpacing,
                textTransform: cs.textTransform,
                cancelH: +cb.height.toFixed(1),
                submitH: +sb.height.toFixed(1),
            };
        });
        out.push({ w, ...m });
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
