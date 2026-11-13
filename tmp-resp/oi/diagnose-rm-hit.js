/**
 * Hit-test: element nào thực sự nhận click tại vị trí nút Close (×) và Cancel?
 * Nếu elementFromPoint trả về thứ khác → thứ đó đang che nút (overlay/header).
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);

    await page.evaluate(() => document.querySelector('[data-return-trigger]')?.click());
    await page.waitForTimeout(800);

    const hits = await page.evaluate(() => {
        const probe = (sel) => {
            const el = document.querySelector(sel);
            if (!el) return { sel, missing: true };
            const r = el.getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height / 2;
            const top = document.elementFromPoint(cx, cy);
            const cs = getComputedStyle(el);
            const chain = document.elementsFromPoint(cx, cy).slice(0, 5).map((n) => {
                const s = getComputedStyle(n);
                return `${n.tagName.toLowerCase()}.${(n.className || '').toString().split(' ').filter(Boolean).join('.')} [z:${s.zIndex} pe:${s.pointerEvents} pos:${s.position}]`;
            });
            return {
                sel,
                rect: { x: +r.x.toFixed(1), y: +r.y.toFixed(1), w: +r.width.toFixed(1), h: +r.height.toFixed(1) },
                center: { x: +cx.toFixed(1), y: +cy.toFixed(1) },
                topElement: top ? `${top.tagName.toLowerCase()}.${(top.className || '').toString().split(' ').filter(Boolean).join('.')}` : null,
                isSelfOrChild: top ? (top === el || el.contains(top)) : false,
                pointerEvents: cs.pointerEvents,
                visibility: cs.visibility,
                display: cs.display,
                opacity: cs.opacity,
                zIndex: cs.zIndex,
                stack: chain,
            };
        };
        return {
            close: probe('.dg-rm-close'),
            cancel: probe('.dg-rm-btn--secondary'),
            submit: probe('#dg-return-submit'),
            overlay: probe('.dg-rm-overlay'),
            dialogZ: getComputedStyle(document.querySelector('.dg-rm-dialog')).zIndex,
            headerZ: getComputedStyle(document.querySelector('.dg-rm-header')).zIndex,
        };
    });

    console.log(JSON.stringify(hits, null, 2));
    await browser.close();
})();
