const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
    const root = path.resolve(__dirname, '../..');
    const theme = path.join(root, 'wp-content/themes/dragon-glow');
    const home = fs.readFileSync(path.join(theme, 'assets/css/home.css'), 'utf8');
    const tokens = fs.readFileSync(path.join(theme, 'style.css'), 'utf8');
    const baselineHome = home.replace(/    \/\* Preserve the full-size desktop frame height while retaining the narrower column\. \*\/\r?\n    \.dg-home-hero \.dg-home-hero__product-frame \{\r?\n        height: var\(--hero-frame-height-laptop\);\r?\n    \}\r?\n\r?\n/, '');
    assert.notEqual(baselineHome, home);
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const page = await browser.newPage({ reducedMotion: 'reduce' });
    await page.route('**/dragon-glow/assets/css/home.css*', route => route.fulfill({ contentType: 'text/css', body: baselineHome }));
    await page.route('**/dragon-glow/style.css*', route => route.fulfill({ contentType: 'text/css', body: tokens }));
    await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'networkidle', timeout: 60000 });
    await page.locator('.dg-home-hero__product-frame').waitFor();
    await page.evaluate(() => document.fonts.ready);
    const widths = [390, 640, 768, 1023, 1024, 1025, 1280, 1440];
    async function measure(width) {
        await page.setViewportSize({ width, height: 1000 });
        await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
        return page.evaluate(() => {
            const frame = document.querySelector('.dg-home-hero__product-frame');
            const image = frame.querySelector('img');
            const r = frame.getBoundingClientRect();
            const ir = image.getBoundingClientRect();
            const c = getComputedStyle(frame);
            const info = frame.querySelector('.dg-home-hero__info-card').getBoundingClientRect();
            return { width: innerWidth, w: r.width, h: r.height, x: r.x, imageH: ir.height, imageW: ir.width, fit: getComputedStyle(image).objectFit, infoInside: info.bottom <= r.bottom && info.top >= r.top, overflow: Math.max(0, document.documentElement.scrollWidth - innerWidth), padding: parseFloat(c.paddingTop) + parseFloat(c.paddingBottom) };
        });
    }
    const before = {};
    for (const width of widths) before[width] = await measure(width);
    await page.addStyleTag({ content: home });
    const results = [];
    for (const width of widths) {
        const after = await measure(width);
        if (width === 1024) {
            assert(before[width].h < 650);
            assert.equal(after.h, 650);
            assert.equal(after.w, before[width].w);
            assert.equal(after.imageH, after.h - after.padding);
            assert(after.infoInside);
            assert.equal(after.fit, 'cover');
        } else {
            assert.equal(after.h, before[width].h, `Height changed at ${width}`);
            assert.equal(after.w, before[width].w, `Width changed at ${width}`);
        }
        assert(after.overflow <= before[width].overflow, `New overflow at ${width}`);
        results.push({ width, beforeHeight: before[width].h, afterHeight: after.h, frameWidth: after.w, overflow: after.overflow });
    }
    await page.setViewportSize({ width: 1024, height: 1000 });
    await page.locator('.dg-home-hero').screenshot({ path: path.join(__dirname, 'after-1024.png') });
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    assert.equal((await measure(1024)).h, 650);
    console.log(JSON.stringify({ status: 'PASS', results, normalAndReducedMotion: 'PASS' }, null, 2));
    await browser.close();
})().catch(error => { console.error(error); process.exit(1); });
