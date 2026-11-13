/**
 * Kiểm chứng site LIVE: hosting có anti-bot challenge (AES) nên raw fetch bị
 * chặn. Script này để browser chạy challenge (set cookie __test), reload,
 * rồi đọc DOM thật + đo layout ở nhiều viewport.
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/my-account/orders/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await ctx.newPage();

    await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(3000);

    // Challenge thường reload trang sau khi set cookie.
    if (!await page.evaluate(() => !!document.querySelector('.woocommerce-pagination'))) {
        await page.waitForTimeout(3000);
        await page.reload({ waitUntil: 'domcontentloaded' }).catch(() => {});
        await page.waitForTimeout(2500);
    }

    const info = await page.evaluate(() => ({
        url: location.href,
        title: document.title,
        hasPag: !!document.querySelector('.woocommerce-pagination'),
        bodyStart: document.body.innerText.slice(0, 200).replace(/\s+/g, ' '),
    }));
    console.log('info:', JSON.stringify(info, null, 1));

    if (!info.hasPag) {
        await browser.close();
        return;
    }

    for (const w of [390, 834, 1280, 1440, 1920]) {
        await page.setViewportSize({ width: w, height: 900 });
        await page.waitForTimeout(600);

        const m = await page.evaluate(() => {
            const pag = document.querySelector('.woocommerce-pagination');
            const nums = pag.querySelector('.woocommerce-pagination__numbers');
            const panel = document.querySelector('.dg-account-panel');
            const prev = pag.querySelector('.woocommerce-button--previous');
            const next = pag.querySelector('.woocommerce-button--next');
            const pcs = getComputedStyle(panel);
            const pr = pag.getBoundingClientRect();

            const pills = Array.from(nums.querySelectorAll('.page-numbers'));
            const vis = pills.filter(p => getComputedStyle(p).display !== 'none');
            const T = e => Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
            const items = [prev, ...vis, next].filter(Boolean);
            const tops = Array.from(new Set(items.map(T))).sort((a, b) => a - b);

            const nr = nums.getBoundingClientRect();
            const pan = panel.getBoundingClientRect();

            return {
                vw: window.innerWidth,
                sw: document.documentElement.scrollWidth,
                hOverflow: document.documentElement.scrollWidth > window.innerWidth,
                visible: vis.map(p => p.textContent.trim()).join(' '),
                lines: tops.length,
                numbersOverflow: nr.right > pan.right - parseFloat(pcs.paddingRight) + 1,
                pagWrap: getComputedStyle(pag).flexWrap,
                prevH: prev ? Math.round(prev.getBoundingClientRect().height) : null,
                nextH: next ? Math.round(next.getBoundingClientRect().height) : null,
                totalPills: pills.length,
                outCompact: pills.filter(p => p.hasAttribute('data-dg-page-out-compact')).length,
                outWide: pills.filter(p => p.hasAttribute('data-dg-page-out-wide')).length,
                css: Array.from(document.querySelectorAll('link[rel=stylesheet]')).map(l => l.href).filter(h => h.includes('account.css'))[0] || '',
            };
        });

        console.log(
            `${String(m.vw).padEnd(5)} | ovf=${m.hOverflow ? 'YES' : 'no '} | lines=${m.lines} | wrap=${m.pagWrap.padEnd(6)} | panelOF=${m.numbersOverflow ? 'YES' : 'no '} | btnH=${m.prevH}/${m.nextH} | pills=${m.totalPills} outC=${m.outCompact} outW=${m.outWide} | "${m.visible}"`
        );

        const box = await page.evaluate(() => {
            const r = document.querySelector('.woocommerce-pagination').getBoundingClientRect();
            return { x: Math.max(0, r.x - 20), y: Math.max(0, r.y - 20), width: r.width + 40, height: r.height + 40 };
        });
        await page.screenshot({ path: `tmp-resp/account-pagination/live-${w}.png`, clip: box });
    }

    console.log('\nCSS:', await page.evaluate(() => (Array.from(document.querySelectorAll('link[rel=stylesheet]')).map(l => l.href).filter(h => h.includes('account.css'))[0] || 'n/a')));
    console.log('\nMARKUP:', await page.evaluate(() => document.querySelector('.woocommerce-pagination__numbers').innerHTML.replace(/\s+/g, ' ').slice(0, 800)));

    await browser.close();
})();
