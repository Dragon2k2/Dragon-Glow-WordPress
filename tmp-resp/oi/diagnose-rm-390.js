/**
 * Kiểm tra nút Cancel ở mobile (390px) — dùng dispatchEvent để không bị
 * Playwright chặn, đồng thời ghi lại element thật sự nằm trên tại điểm click.
 */
const { chromium } = require('playwright');
const path = require('path');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e.message)));

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(800);

    const probe = await page.evaluate(() => {
        const out = {};
        const inspect = (sel, key) => {
            const el = document.querySelector(sel);
            const r = el.getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height / 2;
            const stack = document.elementsFromPoint(cx, cy).slice(0, 4).map((n) => {
                const s = getComputedStyle(n);
                return `${n.tagName.toLowerCase()}.${(n.className || '').toString().split(' ').filter(Boolean).join('.')} [pos:${s.position} z:${s.zIndex}]`;
            });
            out[key] = {
                rect: { x: +r.x.toFixed(1), y: +r.y.toFixed(1), w: +r.width.toFixed(1), h: +r.height.toFixed(1) },
                center: { x: +cx.toFixed(1), y: +cy.toFixed(1) },
                stack,
                topIsSelf: stack[0] && (stack[0].includes('dg-rm-close') || stack[0].includes('dg-rm-btn')),
            };
        };
        inspect('.dg-rm-close', 'close');
        inspect('.dg-rm-btn--secondary', 'cancel');

        const d = document.querySelector('.dg-rm-dialog');
        out.dialog = {
            padding: getComputedStyle(d).padding,
            scrollTop: d.scrollTop,
            scrollHeight: d.scrollHeight,
            clientHeight: d.clientHeight,
            rect: (({ x, y, width, height }) => ({ x: +x.toFixed(1), y: +y.toFixed(1), width: +width.toFixed(1), height: +height.toFixed(1) }))(d.getBoundingClientRect()),
        };
        const h = document.querySelector('.dg-rm-header');
        out.header = {
            rect: (({ x, y, width, height }) => ({ x: +x.toFixed(1), y: +y.toFixed(1), width: +width.toFixed(1), height: +height.toFixed(1) }))(h.getBoundingClientRect()),
            position: getComputedStyle(h).position,
            zIndex: getComputedStyle(h).zIndex,
        };
        return out;
    });

    console.log(JSON.stringify({ probe, errors }, null, 2));
    await browser.close();
})();
