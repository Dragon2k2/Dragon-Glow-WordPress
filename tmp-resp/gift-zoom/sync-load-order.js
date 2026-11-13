/**
 * Kiểm tra thứ tự load script + sự tồn tại của window.DGGiftValue
 * tại thời điểm gift-cards.js (ES module) chạy.
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/gift-cards/';

(async () => {
    const browser = await chromium.launch();
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();

    const order = [];
    await page.addInitScript(() => {
        window.__order = [];
        const obs = new MutationObserver(() => {
            document.querySelectorAll('script[src]').forEach((s) => {
                if (s.dataset.__logged) return;
                s.dataset.__logged = '1';
                window.__order.push(s.src.split('/').pop());
            });
        });
        obs.observe(document.documentElement, { childList: true, subtree: true });
    });

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);

    const result = await page.evaluate(() => ({
        hasModule: typeof window.DGGiftValue === 'object',
        hasGet: typeof (window.DGGiftValue || {}).get === 'function',
        hasSet: typeof (window.DGGiftValue || {}).set === 'function',
        current: (window.DGGiftValue || {}).get ? window.DGGiftValue.get() : null,
        openZoom: typeof window.dgOpenZoom === 'function',
        scripts: Array.from(document.querySelectorAll('script[src]'))
            .map((s) => s.src.split('/').pop())
            .filter((n) => n.includes('gift')),
    }));

    console.log(JSON.stringify(result, null, 2));
    await browser.close();
})();