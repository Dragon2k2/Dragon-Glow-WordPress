/**
 * Chrome có resolve var() trong ::-webkit-scrollbar pseudo-element không?
 * Dùng getComputedStyle(el, '::-webkit-scrollbar') để đọc giá trị thực.
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(600);

    const result = await page.evaluate(() => {
        const style = document.createElement('style');
        style.textContent = `
          :root { --sb-w: 8px; --sb-c: #d4c2c2; }
          .t-var::-webkit-scrollbar { width: var(--sb-w); }
          .t-var::-webkit-scrollbar-thumb { background: var(--sb-c); }
          .t-lit::-webkit-scrollbar { width: 8px; }
          .t-lit::-webkit-scrollbar-thumb { background: #d4c2c2; }
        `;
        document.head.appendChild(style);

        const read = (cls) => {
            const el = document.createElement('div');
            el.className = cls;
            el.style.cssText = 'width:200px;height:80px;overflow-y:scroll;';
            el.innerHTML = '<div style="height:400px">x</div>';
            document.body.appendChild(el);
            const bar = getComputedStyle(el, '::-webkit-scrollbar');
            const thumb = getComputedStyle(el, '::-webkit-scrollbar-thumb');
            const track = getComputedStyle(el, '::-webkit-scrollbar-track');
            const res = {
                barWidth: bar.width,
                barBg: bar.backgroundColor,
                thumbBg: thumb.backgroundColor,
                trackBg: track.backgroundColor,
            };
            el.remove();
            return res;
        };

        return { varBased: read('t-var'), literal: read('t-lit') };
    });

    console.log(JSON.stringify(result, null, 2));
    await browser.close();
})();
