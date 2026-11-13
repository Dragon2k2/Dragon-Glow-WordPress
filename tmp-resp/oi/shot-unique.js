/**
 * Chụp lại với tên file duy nhất + in vị trí từng dòng text (Range rects)
 * để đối chiếu ảnh với DOM.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const CSS_FILE = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/careers.css');
const URL = 'https://dragonglow.freehosting.dev/careers/';
const css = fs.readFileSync(CSS_FILE, 'utf8');
const stamp = Date.now();

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    for (const w of [320, 390]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 800 } });
        const page = await ctx.newPage();
        await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.evaluate(() => document.querySelectorAll('link[href*="careers.css"]').forEach((l) => l.remove()));
        await page.addStyleTag({ content: css });
        await page.evaluate(() => document.querySelector('[data-apply-trigger]')?.click());
        await page.waitForTimeout(800);

        const rects = await page.evaluate(() => {
            const s = document.querySelector('#dg-apply-submit');
            const r = document.createRange();
            r.selectNodeContents(s);
            return [...r.getClientRects()].map((x) => ({ x: +x.x.toFixed(1), y: +x.y.toFixed(1), w: +x.width.toFixed(1), h: +x.height.toFixed(1) }));
        });
        console.log(w, JSON.stringify(rects));

        const el = page.locator('.dg-apply-actions');
        await el.screenshot({ path: path.join(__dirname, 'apply-actions', `SHOT-${w}-${stamp}.png`) });
        await ctx.close();
    }
    await browser.close();
})();
