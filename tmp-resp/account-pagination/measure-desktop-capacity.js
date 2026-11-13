/**
 * Đo sức chứa thật của hàng phân trang ở desktop.
 *
 * Mục tiêu: biết chính xác
 *   - bề rộng nút Previous / Next
 *   - bề rộng pill 1 chữ số vs 2 chữ số
 *   - số trang tối đa vẫn nằm 1 hàng trong cột nội dung hẹp nhất (776px @1280px)
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
    for (let i = 1; i <= total; i++) {
        nums += `<a class="page-numbers" href="${base}/page/${i}/">${i}</a>`;
    }
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

    const out = {};

    /* 1. Bề rộng từng thành phần */
    await page.setContent(pageHtml(12), { waitUntil: 'load' });
    await page.waitForTimeout(150);
    out.parts = await page.evaluate(() => {
        const panel = document.querySelector('.dg-account-panel');
        const pcs = getComputedStyle(panel);
        const prev = document.querySelector('.woocommerce-button--previous');
        const next = document.querySelector('.woocommerce-button--next');
        const nums = document.querySelector('.woocommerce-pagination__numbers');
        const pills = [...nums.querySelectorAll('.page-numbers')];
        const oneDigit = pills.find(p => p.textContent.trim().length === 1);
        const twoDigit = pills.find(p => p.textContent.trim().length === 2);
        const cs = getComputedStyle(nums);
        return {
            panelInner: Math.round(panel.getBoundingClientRect().width - parseFloat(pcs.paddingLeft) * 2),
            prevW: Math.round(prev.getBoundingClientRect().width),
            nextW: Math.round(next.getBoundingClientRect().width),
            oneDigitW: Math.round(oneDigit.getBoundingClientRect().width),
            twoDigitW: Math.round(twoDigit.getBoundingClientRect().width),
            numsGap: cs.columnGap,
            pagGap: getComputedStyle(document.querySelector('.woocommerce-pagination')).columnGap,
        };
    });

    console.log('=== SỨC CHỨA HÀNG (desktop, viewport 1280px) ===');
    for (const [k, v] of Object.entries(out.parts)) console.log('  ' + k.padEnd(12) + ': ' + v);

    /* 2. Bao nhiêu trang vẫn nằm 1 hàng? Thử nhiều tổng số trang. */
    console.log('\n=== TỐI ĐA SỐ TRANG TRÊN 1 HÀNG ===');
    const rows = [];
    for (const total of [5, 8, 10, 12, 14, 16, 18, 20, 24, 30, 40, 50]) {
        await page.setContent(pageHtml(total), { waitUntil: 'load' });
        await page.waitForTimeout(120);
        const m = await page.evaluate(() => {
            const pag = document.querySelector('.woocommerce-pagination');
            const nums = pag.querySelector('.woocommerce-pagination__numbers');
            const panel = document.querySelector('.dg-account-panel');
            const pcs = getComputedStyle(panel);
            const pr = pag.getBoundingClientRect();

            const tops = new Set();
            for (const e of nums.querySelectorAll('.page-numbers')) {
                const t = Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
                tops.add(t);
            }
            const nr = nums.getBoundingClientRect();
            const pan = panel.getBoundingClientRect();
            return {
                lines: tops.size,
                numW: Math.round(nr.width),
                overflow: nr.right > pan.right - parseFloat(pcs.paddingRight) + 1,
                panelInner: Math.round(pan.width - parseFloat(pcs.paddingLeft) * 2),
            };
        });
        rows.push({ total, ...m });
        console.log(
            '  ' + String(total).padStart(3) + ' trang → numW=' + String(m.numW).padEnd(5) +
            ' lines=' + m.lines + (m.overflow ? '  OVERFLOW!' : '')
        );
    }

    /* 3. Bề rộng của WINDOW desktop (first, current±R, last + ellipsis) */
    console.log('\n=== BỀ RỘNG WINDOW DESKTOP (radius R) ===');
    const p = out.parts;
    const pill1 = p.oneDigitW, pill2 = p.twoDigitW;
    const ellipsisW = 26; // min-width 22 + padding 0 2px
    for (const R of [1, 2, 3, 4, 5, 6]) {
        // Trường hợp xấu nhất: current ở giữa, cả first và last đều 2 chữ số.
        const items = 2 + (2 * R + 1) + 2; // first + window + last + 2 ellipsis
        const pills = 2 * pill2 + (2 * R + 1) * pill2; // xấu nhất: toàn 2 chữ số
        const w = pills + 2 * ellipsisW + p.prevW + p.nextW + 2 * p.pagGap;
        console.log(
            '  R=' + R + ' → items=' + items + '  width≈' + Math.round(w) +
            (w <= p.panelInner ? '  FITS' : '  OVERFLOW')
        );
    }
    console.log('  panelInner = ' + p.panelInner);

    await browser.close();
})();
