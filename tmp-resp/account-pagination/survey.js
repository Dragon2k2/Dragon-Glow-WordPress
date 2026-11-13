/**
 * Đo layout phân trang Orders ở dải rộng (mobile → desktop) với layout thật
 * (sidebar 280px ở >=1024px, wrap padding theo theme).
 *
 * Mục tiêu: tìm chính xác các viewport mà hàng số trang TRÀN khỏi panel.
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

function buildMarkup(total, current) {
    const base = 'https://dragonglow.freehosting.dev/my-account/orders';
    const visible = windowOf(current, total);
    let html = '<span class="woocommerce-pagination__numbers">';
    for (let i = 1; i <= total; i++) {
        const isCurrent = i === current;
        const isHidden = !visible.includes(i);
        if (isHidden && visible.includes(i - 1)) {
            html += '<span class="page-numbers dg-account-pagination__ellipsis" aria-hidden="true" data-dg-page-ellipsis>…</span>';
        }
        const hiddenAttr = isHidden ? ' data-dg-page-hidden' : '';
        if (isCurrent) {
            html += `<span class="page-numbers current" aria-current="page"${hiddenAttr}>${i}</span>`;
        } else {
            const url = i === 1 ? base : `${base}/page/${i}/`;
            html += `<a class="page-numbers" href="${url}"${hiddenAttr}>${i}</a>`;
        }
    }
    return html + '</span>';
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
    const widths = [320, 360, 390, 414, 480, 600, 640, 641, 700, 768, 834, 900, 1023, 1024, 1080, 1112, 1280, 1440];
    const rows = [];

    for (const w of widths) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(120);

        const m = await page.evaluate(() => {
            const pag = document.querySelector('.woocommerce-pagination');
            const nums = document.querySelector('.woocommerce-pagination__numbers');
            const panel = document.querySelector('.dg-account-panel');
            const pills = Array.from(nums.querySelectorAll('.page-numbers'));
            const vis = pills.filter(p => getComputedStyle(p).display !== 'none');

            const nr = nums.getBoundingClientRect();
            const pr = pag.getBoundingClientRect();
            const pan = panel.getBoundingClientRect();
            const pcs = getComputedStyle(panel);

            const tops = new Set(vis.map(p => Math.round(p.getBoundingClientRect().top)));
            const rows = tops.size;

            let contentW = 0;
            for (const p of vis) contentW += p.getBoundingClientRect().width;
            const gaps = Math.max(0, vis.length - 1) * parseFloat(getComputedStyle(nums).columnGap || 8);

            return {
                innerWidth: window.innerWidth,
                docScrollWidth: document.documentElement.scrollWidth,
                hOverflow: document.documentElement.scrollWidth > window.innerWidth,
                pagWidth: Math.round(pr.width),
                panelInner: Math.round(pan.width - parseFloat(pcs.paddingLeft) * 2),
                numbersWidth: Math.round(nr.width),
                numbersRight: Math.round(nr.right),
                panelRight: Math.round(pan.right),
                numbersOverflowPanel: nr.right > pan.right - parseFloat(pcs.paddingRight) + 1,
                visibleCount: vis.length,
                visibleText: vis.map(p => p.textContent.trim()).join(' '),
                pillRows: rows,
                numbersHeight: Math.round(nr.height),
                contentW: Math.round(contentW),
                gaps: Math.round(gaps),
                pagDisplay: getComputedStyle(pag).display,
                cols: getComputedStyle(pag).gridTemplateColumns,
            };
        });

        rows.push(m);
        await ctx.close();
    }

    await browser.close();

    console.log('vw   | ovf | pagW | panelInner | numW | rows | visible');
    console.log('-----+-----+------+------------+------+------+--------');
    for (const m of rows) {
        console.log(
            String(m.innerWidth).padEnd(4) + ' | ' +
            (m.hOverflow ? 'YES ' : 'no  ') + ' | ' +
            String(m.pagWidth).padEnd(4) + ' | ' +
            String(m.panelInner).padEnd(10) + ' | ' +
            String(m.numbersWidth).padEnd(4) + ' | ' +
            String(m.pillRows).padEnd(4) + ' | ' +
            m.visibleText
        );
    }
    const bad = rows.filter(m => m.hOverflow || m.numbersOverflowPanel);
    console.log('\nTRAN: ' + (bad.length ? bad.map(m => m.innerWidth).join(', ') : 'khong co'));
    console.log('\nJSON:\n' + JSON.stringify(rows, null, 1));
})();
