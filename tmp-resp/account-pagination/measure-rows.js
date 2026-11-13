/**
 * Đo chính xác: bao nhiêu trang vẫn nằm trên MỘT hàng duy nhất ở desktop?
 *
 * Metric: gom prev + numbers + next theo hàng (top tương đối) → nếu
 * toàn bộ nằm cùng 1 hàng thì cột nội dung đủ chỗ cho hàng đầy đủ.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');

const ACCOUNT_CSS = fs.readFileSync(path.join(THEME, 'assets/css/account.css'), 'utf8');
const WC_CSS = fs.readFileSync(path.join(THEME, 'assets/css/woocommerce.css'), 'utf8');
const STYLE_CSS = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');

function pageHtml(total) {
    const base = 'https://dragonglow.freehosting.dev/my-account/orders';
    let nums = '';
    for (let i = 1; i <= total; i++) nums += `<a class="page-numbers" href="${base}/page/${i}/">${i}</a>`;
    return `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${STYLE_CSS}</style><style>${WC_CSS}</style><style>${ACCOUNT_CSS}</style>
</head><body class="dg-js">
<div class="dg-account"><div class="dg-account__wrap"><div class="dg-account__layout">
<div class="dg-account__sidebar"><div style="height:400px"></div></div>
<div class="dg-account__content">
<section class="dg-account-panel">
<div class="woocommerce-pagination woocommerce-Pagination">
<a class="woocommerce-button woocommerce-button--previous button" href="#">Previous</a>
<span class="woocommerce-pagination__numbers">${nums}</span>
<a class="woocommerce-button woocommerce-button--next button" href="#">Next</a>
</div>
</section>
</div></div></div></div></body></html>`;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();

    console.log('vw=1280 (panel hẹp nhất ở desktop)');
    console.log('total | pagH | rows(prev+nums+next) | numRows | prevRow/numsRow/nextRow');
    for (const total of [10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 25, 30, 50]) {
        await page.setContent(pageHtml(total), { waitUntil: 'load' });
        await page.waitForTimeout(120);
        const m = await page.evaluate(() => {
            const pag = document.querySelector('.woocommerce-pagination');
            const nums = pag.querySelector('.woocommerce-pagination__numbers');
            const prev = pag.querySelector('.woocommerce-button--previous');
            const next = pag.querySelector('.woocommerce-button--next');
            const pr = pag.getBoundingClientRect();

            const T = e => Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
            const allTops = [];
            for (const e of [prev, next, ...nums.querySelectorAll('.page-numbers')]) {
                const t = T(e);
                if (!allTops.includes(t)) allTops.push(t);
            }
            allTops.sort((a, b) => a - b);

            const numTops = [];
            for (const e of nums.querySelectorAll('.page-numbers')) {
                const t = T(e);
                if (!numTops.includes(t)) numTops.push(t);
            }

            const idx = t => allTops.indexOf(t);
            return {
                pagH: Math.round(pr.height),
                rows: allTops.length,
                numRows: numTops.length,
                prevRow: idx(T(prev)), numsRow: idx(T(nums.querySelector('.page-numbers'))), nextRow: idx(T(next)),
            };
        });
        console.log(
            String(total).padStart(5) + ' | ' + String(m.pagH).padEnd(4) + ' | ' +
            String(m.rows).padEnd(17) + ' | ' + String(m.numRows).padEnd(7) + ' | ' +
            m.prevRow + '/' + m.numsRow + '/' + m.nextRow
        );
    }

    await browser.close();
})();
