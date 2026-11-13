/**
 * Ảnh toàn cảnh section #traceable (kèm context trên/dưới) ở 3 breakpoint,
 * sau khi áp bản sửa: bỏ overlay ảnh + glass card.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-verify');
fs.mkdirSync(OUT, { recursive: true });

const CSS = require('fs')
    .readFileSync(path.join(__dirname, 'verify-traceable.js'), 'utf8')
    .split('const CSS = `')[1]
    .split('`;')[0];

const VIEWPORTS = [
    { name: '390', width: 390, height: 844 },
    { name: '768', width: 768, height: 1024 },
    { name: '1280', width: 1280, height: 900 },
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    for (const vp of VIEWPORTS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.addStyleTag({ content: CSS });
        await page.evaluate(() => {
            const ov = document.querySelector('.dg-oi-traceable-overlay');
            if (ov) ov.remove();
            const el = document.querySelector('#traceable');
            el.scrollIntoView({ block: 'center' });
        });
        await page.waitForTimeout(1000);
        await page.screenshot({ path: path.join(OUT, `ctx-${vp.name}.png`) });
        await ctx.close();
    }
    await browser.close();
    console.log('done');
})();
