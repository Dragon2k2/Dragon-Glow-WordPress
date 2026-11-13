/**
 * Probe trực tiếp trạng thái LIVE (không inject gì):
 * - overlay có đang làm tối ảnh không?
 * - pixel màu nền thực tế tại vùng chữ?
 */
const { chromium } = require('playwright');
const sharp = require('sharp');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/our-ingredients/';
const OUT = path.join(__dirname, 'traceable-contrast');

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.evaluate(() => document.querySelector('#traceable').scrollIntoView({ block: 'center' }));
    await page.waitForTimeout(900);

    const info = await page.evaluate(() => {
        const ov = document.querySelector('.dg-oi-traceable-overlay');
        const ovs = ov ? getComputedStyle(ov) : null;
        const bgDiv = document.querySelector('.dg-oi-traceable-bg');
        const bgs = getComputedStyle(bgDiv);
        const h = document.querySelector('.dg-oi-traceable-headline');
        const r = h.getBoundingClientRect();
        const sec = document.querySelector('#traceable').getBoundingClientRect();
        return {
            overlayPresent: !!ov,
            overlayBg: ovs ? ovs.backgroundColor : null,
            overlayBlend: ovs ? ovs.mixBlendMode : null,
            overlayZ: ovs ? ovs.zIndex : null,
            bgDivZ: bgs.zIndex,
            bgDivIsolation: bgs.isolation,
            headlineRectRel: { x: r.x - sec.x, y: r.y - sec.y, w: r.width, h: r.height },
            headlineColor: getComputedStyle(h).color,
        };
    });

    const shot = path.join(OUT, 'live-raw-1280.png');
    await page.locator('#traceable').screenshot({ path: shot });

    /* ẩn chữ rồi chụp lại để lấy nền thuần */
    await page.evaluate(() => {
        document.querySelectorAll('.dg-oi-traceable-headline,.dg-oi-traceable-body,.dg-oi-traceable-country,.dg-oi-traceable-footnote').forEach((el) => (el.style.visibility = 'hidden'));
    });
    await page.waitForTimeout(200);
    const shot2 = path.join(OUT, 'live-notxt-1280.png');
    await page.locator('#traceable').screenshot({ path: shot2 });

    const { data, info: im } = await sharp(shot2).raw().toBuffer({ resolveWithObject: true });
    const px = (x, y) => {
        const i = (y * im.width + x) * im.channels;
        return [data[i], data[i + 1], data[i + 2]];
    };
    info.bgSamples = {
        'headlineArea (100,120)': px(100, 120),
        'leftEdge (20,120)': px(20, 120),
        'farRight (1200,120)': px(1200, 120),
        'belowCard (100,560)': px(100, 560),
    };

    await browser.close();
    console.log(JSON.stringify(info, null, 2));
})();
