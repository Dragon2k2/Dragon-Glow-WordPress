/**
 * SURVEY DESKTOP — Orders pagination.
 *
 * Câu hỏi: ở desktop (>=1280px) layout phân trang có vấn đề gì?
 *   - 12 trang: hàng 12 số + Prev/Next có vừa không? có lệch không?
 *   - 50 trang: hàng số tràn → wrap thành mấy dòng, có lệch lạch không?
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const OUT = __dirname;

const ACCOUNT_CSS = fs.readFileSync(path.join(THEME, 'assets/css/account.css'), 'utf8');
const WC_CSS = fs.readFileSync(path.join(THEME, 'assets/css/woocommerce.css'), 'utf8');
const STYLE_CSS = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');

/* Markup đúng như theme hiện tại (không có data-dg-page-hidden → hiện tất cả). */
function buildMarkup(total, current, withHiddenTags) {
    const base = 'https://dragonglow.freehosting.dev/my-account/orders';
    const visible = [];
    const pages = new Set([1, total]);
    for (let i = current - 1; i <= current + 1; i++) if (i > 1 && i < total) pages.add(i);
    const narrow = [...pages].sort((a, b) => a - b);

    let html = `<a class="woocommerce-button woocommerce-button--previous button" href="#">Previous</a>`;
    html += '<span class="woocommerce-pagination__numbers">';
    for (let i = 1; i <= total; i++) {
        const isCurrent = i === current;
        const isHidden = withHiddenTags && !narrow.includes(i);
        if (isHidden && narrow.includes(i - 1)) {
            html += '<span class="page-numbers dg-account-pagination__ellipsis" aria-hidden="true" data-dg-page-ellipsis>…</span>';
        }
        const hiddenAttr = isHidden ? ' data-dg-page-hidden' : '';
        if (isCurrent) html += `<span class="page-numbers current" aria-current="page"${hiddenAttr}>${i}</span>`;
        else html += `<a class="page-numbers" href="${base}/page/${i}/"${hiddenAttr}>${i}</a>`;
    }
    html += '</span>';
    html += `<a class="woocommerce-button woocommerce-button--next button" href="#">Next</a>`;
    return html;
}

function pageHtml(total, current) {
    return `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${STYLE_CSS}</style><style>${WC_CSS}</style><style>${ACCOUNT_CSS}</style>
</head><body class="dg-js">
<div class="dg-account"><div class="dg-account__wrap"><div class="dg-account__layout">
<div class="dg-account__sidebar"><div style="height:400px"></div></div>
<div class="dg-account__content">
<section class="dg-account-panel">
<div class="woocommerce-pagination woocommerce-Pagination">${buildMarkup(total, current, false)}</div>
</section>
</div></div></div></div></body></html>`;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });

    console.log('=== A. 12 TRANG (dữ liệu thật của site) ===');
    console.log('vw    | panelInner | numW | pagH | lines | prev/nums/next | fits');
    for (const w of [1280, 1366, 1440, 1600, 1920]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(120);

        const m = await page.evaluate(() => {
            const pag = document.querySelector('.woocommerce-pagination');
            const nums = pag.querySelector('.woocommerce-pagination__numbers');
            const panel = document.querySelector('.dg-account-panel');
            const prev = pag.querySelector('.woocommerce-button--previous');
            const next = pag.querySelector('.woocommerce-button--next');
            const pcs = getComputedStyle(panel);
            const pr = pag.getBoundingClientRect();

            const items = [prev, ...nums.querySelectorAll('.page-numbers'), next];
            const lineTops = [];
            for (const e of items) {
                const t = Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
                if (!lineTops.includes(t)) lineTops.push(t);
            }
            lineTops.sort((a, b) => a - b);
            const lineOf = e => {
                const t = Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
                return lineTops.indexOf(t);
            };

            const panelInner = panel.getBoundingClientRect().width - parseFloat(pcs.paddingLeft) * 2;
            let sum = 0;
            for (const e of items) sum += e.getBoundingClientRect().width;
            const needed = sum + 12 * 2;

            return {
                vw: window.innerWidth,
                panelInner: Math.round(panelInner),
                numW: Math.round(nums.getBoundingClientRect().width),
                pagH: Math.round(pr.height),
                lines: lineTops.length,
                prevLine: lineOf(prev), numsLine: lineOf(nums.querySelector('.page-numbers')), nextLine: lineOf(next),
                needed: Math.round(needed),
                fits: needed <= panelInner,
                hOverflow: document.documentElement.scrollWidth > window.innerWidth,
            };
        });
        console.log(
            String(m.vw).padEnd(5) + ' | ' + String(m.panelInner).padEnd(10) + ' | ' +
            String(m.numW).padEnd(4) + ' | ' + String(m.pagH).padEnd(4) + ' | ' +
            String(m.lines).padEnd(5) + ' | ' +
            (m.prevLine + '/' + m.numsLine + '/' + m.nextLine).padEnd(17) + ' | ' + m.fits
        );
        await ctx.close();
    }

    console.log('\n=== B. 50 TRANG (lịch sử đơn hàng dài) ===');
    console.log('vw    | panelInner | numW | pagH | lines | prev/nums/next | overflow');
    for (const w of [1280, 1440, 1920]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, 25), { waitUntil: 'load' });
        await page.waitForTimeout(120);

        const m = await page.evaluate(() => {
            const pag = document.querySelector('.woocommerce-pagination');
            const nums = pag.querySelector('.woocommerce-pagination__numbers');
            const panel = document.querySelector('.dg-account-panel');
            const prev = pag.querySelector('.woocommerce-button--previous');
            const next = pag.querySelector('.woocommerce-button--next');
            const pr = pag.getBoundingClientRect();

            const items = [prev, ...nums.querySelectorAll('.page-numbers'), next];
            const lineTops = [];
            for (const e of items) {
                const t = Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
                if (!lineTops.includes(t)) lineTops.push(t);
            }
            lineTops.sort((a, b) => a - b);
            const lineOf = e => {
                const t = Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
                return lineTops.indexOf(t);
            };
            const pcs = getComputedStyle(panel);
            const panelInner = panel.getBoundingClientRect().width - parseFloat(pcs.paddingLeft) * 2;
            const nr = nums.getBoundingClientRect();
            const pan = panel.getBoundingClientRect();

            return {
                vw: window.innerWidth,
                panelInner: Math.round(panelInner),
                numW: Math.round(nr.width),
                pagH: Math.round(pr.height),
                lines: lineTops.length,
                prevLine: lineOf(prev), numsLine: lineOf(nums.querySelector('.page-numbers')), nextLine: lineOf(next),
                numbersOverflow: nr.right > pan.right - parseFloat(pcs.paddingRight) + 1,
                hOverflow: document.documentElement.scrollWidth > window.innerWidth,
            };
        });
        console.log(
            String(m.vw).padEnd(5) + ' | ' + String(m.panelInner).padEnd(10) + ' | ' +
            String(m.numW).padEnd(4) + ' | ' + String(m.pagH).padEnd(4) + ' | ' +
            String(m.lines).padEnd(5) + ' | ' +
            (m.prevLine + '/' + m.numsLine + '/' + m.nextLine).padEnd(17) + ' | ' + m.numbersOverflow
        );

        const box = await page.evaluate(() => {
            const r = document.querySelector('.woocommerce-pagination').getBoundingClientRect();
            return { x: Math.max(0, r.x - 10), y: Math.max(0, r.y - 10), width: r.width + 20, height: r.height + 20 };
        });
        await page.screenshot({ path: path.join(OUT, `desktop-50p-${w}.png`), clip: box });
        await ctx.close();
    }

    /* Ảnh 12 trang ở 1440 */
    {
        const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(120);
        const box = await page.evaluate(() => {
            const r = document.querySelector('.woocommerce-pagination').getBoundingClientRect();
            return { x: Math.max(0, r.x - 20), y: Math.max(0, r.y - 20), width: r.width + 40, height: r.height + 40 };
        });
        await page.screenshot({ path: path.join(OUT, 'desktop-12p-1440.png'), clip: box });
        await ctx.close();
    }

    await browser.close();
})();
