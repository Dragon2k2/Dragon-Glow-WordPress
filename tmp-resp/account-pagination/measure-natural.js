/**
 * Đo bề rộng tự nhiên của hàng phân trang ĐẦY ĐỦ (12 số) ở dải rộng,
 * để chọn breakpoint chính xác cho compact window.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');

const ACCOUNT_CSS = fs.readFileSync(path.join(THEME, 'assets/css/account.css'), 'utf8');
const WC_CSS = fs.readFileSync(path.join(THEME, 'assets/css/woocommerce.css'), 'utf8');
const STYLE_CSS = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');

function windowOf(current, total) {
    total = Math.max(1, total);
    current = Math.min(Math.max(1, current), total);
    if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = new Set([1, total]);
    for (let i = current - 1; i <= current + 1; i++) if (i > 1 && i < total) pages.add(i);
    return [...pages].sort((a, b) => a - b);
}

/* Markup KHÔNG có data-dg-page-hidden → ép hiện tất cả (như desktop). */
function buildMarkup(total, current) {
    const base = 'https://dragonglow.freehosting.dev/my-account/orders';
    let html = '<span class="woocommerce-pagination__numbers">';
    for (let i = 1; i <= total; i++) {
        if (i === current) html += `<span class="page-numbers current" aria-current="page">${i}</span>`;
        else html += `<a class="page-numbers" href="${base}/page/${i}/">${i}</a>`;
    }
    return html + '</span>';
}

function pageHtml(total, current) {
    return `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${STYLE_CSS}</style><style>${WC_CSS}</style><style>${ACCOUNT_CSS}</style>
<style>
/* Tắt compact window để đo bề rộng tự nhiên của hàng đầy đủ. */
.dg-account-panel .woocommerce-pagination__numbers [data-dg-page-hidden]{display:inline-flex!important;}
.dg-account-panel .woocommerce-pagination__numbers .dg-account-pagination__ellipsis{display:none!important;}
</style>
</head><body class="dg-js">
<div class="dg-account"><div class="dg-account__wrap"><div class="dg-account__layout">
<div class="dg-account__sidebar"><div style="height:400px"></div></div>
<div class="dg-account__content">
<section class="dg-account-panel">
<div class="woocommerce-pagination woocommerce-Pagination">
<a class="woocommerce-button woocommerce-button--previous button" href="#">Previous</a>
${buildMarkup(total, current)}
<a class="woocommerce-button woocommerce-button--next button" href="#">Next</a>
</div>
</section>
</div></div></div></div></body></html>`;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const widths = [641, 768, 834, 900, 1023, 1024, 1080, 1112, 1152, 1200, 1240, 1280, 1366, 1440];
    const rows = [];

    for (const w of widths) {
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

            const panelInner = panel.getBoundingClientRect().width - parseFloat(pcs.paddingLeft) * 2;

            // Bề rộng tự nhiên của hàng: tổng chiều rộng nội dung các item
            // (không tính gap) — dùng để biết cần bao nhiêu px.
            let sum = 0;
            for (const e of [prev, ...nums.querySelectorAll('.page-numbers'), next]) {
                sum += e.getBoundingClientRect().width;
            }
            const gap = parseFloat(getComputedStyle(pag).columnGap || 12);
            const needed = sum + gap * 2; // 2 khoảng gap giữa 3 item

            // Số dòng thật (dùng offsetTop tương đối, tránh lệch subpixel).
            const tops = new Set([prev, ...nums.querySelectorAll('.page-numbers'), next]
                .map(e => Math.round(e.getBoundingClientRect().top - pag.getBoundingClientRect().top)));
            const lines = tops.size;

            return {
                vw: window.innerWidth,
                panelInner: Math.round(panelInner),
                needed: Math.round(needed),
                fits: needed <= panelInner,
                lines,
                numW: Math.round(nums.getBoundingClientRect().width),
            };
        });
        rows.push(m);
        await ctx.close();
    }

    await browser.close();

    console.log('vw    | panelInner | needed | fits | lines');
    console.log('------+------------+--------+------+------');
    for (const m of rows) {
        console.log(
            String(m.vw).padEnd(5) + ' | ' +
            String(m.panelInner).padEnd(10) + ' | ' +
            String(m.needed).padEnd(6) + ' | ' +
            (m.fits ? 'YES' : 'no ') + '  | ' +
            m.lines
        );
    }
})();
