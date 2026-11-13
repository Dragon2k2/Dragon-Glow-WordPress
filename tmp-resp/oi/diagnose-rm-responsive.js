/**
 * Kiểm tra modal Return ở nhiều viewport:
 *  - hit-test (element nào thật sự nhận click)
 *  - click thật → modal có đóng không
 *  - bắt lỗi pageerror
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';
const OUT = path.join(__dirname, 'rm-modal');
require('fs').mkdirSync(OUT, { recursive: true });

const VPS = [
    { name: '390', width: 390, height: 844 },
    { name: '768', width: 768, height: 1024 },
    { name: '1280', width: 1280, height: 900 },
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = [];

    for (const vp of VPS) {
        const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(String(e.message)));

        await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
        await page.waitForTimeout(700);

        await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
        await page.waitForTimeout(800);

        const probe = async (sel) => page.evaluate((s) => {
            const el = document.querySelector(s);
            if (!el) return { missing: true };
            const r = el.getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height / 2;
            const top = document.elementFromPoint(cx, cy);
            return {
                rect: { x: +r.x.toFixed(1), y: +r.y.toFixed(1), w: +r.width.toFixed(1), h: +r.height.toFixed(1) },
                inViewport: r.top >= 0 && r.bottom <= window.innerHeight && r.left >= 0 && r.right <= window.innerWidth,
                topElement: top ? `${top.tagName.toLowerCase()}.${(top.className || '').toString().split(' ').filter(Boolean).join('.')}` : null,
                isSelfOrChild: top ? (top === el || el.contains(top)) : false,
                dialogScrollTop: document.querySelector('.dg-rm-dialog').scrollTop,
                dialogScrollHeight: document.querySelector('.dg-rm-dialog').scrollHeight,
                dialogClientHeight: document.querySelector('.dg-rm-dialog').clientHeight,
            };
        }, sel);

        const closeProbe = await probe('.dg-rm-close');
        const cancelProbe = await probe('.dg-rm-btn--secondary');

        await page.screenshot({ path: path.join(OUT, `modal-${vp.name}.png`) });

        /* click Close thật */
        await page.locator('.dg-rm-close').click();
        await page.waitForTimeout(900);
        const afterClose = await page.evaluate(() => {
            const m = document.getElementById('dg-return-modal');
            return { hidden: m.hasAttribute('hidden'), display: getComputedStyle(m).display };
        });

        /* mở lại → click Cancel */
        await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
        await page.waitForTimeout(700);
        await page.locator('.dg-rm-btn--secondary').click();
        await page.waitForTimeout(900);
        const afterCancel = await page.evaluate(() => {
            const m = document.getElementById('dg-return-modal');
            return { hidden: m.hasAttribute('hidden'), display: getComputedStyle(m).display };
        });

        out.push({ vp: vp.name, closeProbe, cancelProbe, afterClose, afterCancel, errors });
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
