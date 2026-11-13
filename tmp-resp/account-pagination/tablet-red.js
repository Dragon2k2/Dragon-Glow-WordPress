/**
 * RED — Đo layout phân trang ở dải tablet (641–1199px) với layout thật.
 *
 * Mục tiêu: chứng minh hiện trạng bị vỡ ở tablet:
 *   - Hàng số trang không vừa → Prev/Next bị đẩy xuống dòng lệch lạch.
 *   - Ở 1024–1112px: hàng số TRÀN khỏi panel (sidebar 280px chiếm chỗ).
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
    const widths = [641, 700, 768, 834, 900, 1023, 1024, 1080, 1112, 1200, 1280, 1440];
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
            const prev = pag.querySelector('.woocommerce-button--previous');
            const next = pag.querySelector('.woocommerce-button--next');

            const R = e => {
                if (!e) return null;
                const r = e.getBoundingClientRect();
                return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) };
            };

            const nr = nums.getBoundingClientRect();
            const pr = prev.getBoundingClientRect();
            const xr = next.getBoundingClientRect();
            const pan = panel.getBoundingClientRect();
            const pcs = getComputedStyle(panel);

            // Số "dòng" thực tế của cả khối pagination: gom theo đường baseline.
            const all = [prev, ...vis, next];
            const lineTops = new Set(all.map(e => Math.round(e.getBoundingClientRect().top)));
            const lines = [...lineTops].sort((a, b) => a - b);

            // Dải hàng nào chứa Previous / numbers / Next
            const lineOf = e => lines.findIndex(t => Math.abs(t - Math.round(e.getBoundingClientRect().top)) < 2);

            return {
                vw: window.innerWidth,
                panelInner: Math.round(pan.width - parseFloat(pcs.paddingLeft) * 2),
                numbersW: Math.round(nr.width),
                numbersOverflow: nr.right > pan.right - parseFloat(pcs.paddingRight) + 1,
                docOverflow: document.documentElement.scrollWidth > window.innerWidth,
                visibleCount: vis.length,
                visibleText: vis.map(p => p.textContent.trim()).join(' '),
                prev: R(prev), next: R(next),
                prevLine: lineOf(prev), numsLine: lineOf(vis[0]), nextLine: lineOf(next),
                totalLines: lines.length,
                prev: R(prev),
            };
        });
        rows.push(m);
        await ctx.close();
    }

    await browser.close();

    console.log('vw    | panelInner | numW | overflow | prevLine/numsLine/nextLine | lines | visible');
    console.log('------+------------+------+----------+--------------------------+-------+--------');
    for (const m of rows) {
        console.log(
            String(m.vw).padEnd(5) + ' | ' +
            String(m.panelInner).padEnd(10) + ' | ' +
            String(m.numbersW).padEnd(4) + ' | ' +
            (m.numbersOverflow ? 'OVERFLOW' : '  ok   ') + ' | ' +
            (m.prevLine + '/' + m.numsLine + '/' + m.nextLine).padEnd(24) + ' | ' +
            String(m.totalLines).padEnd(5) + ' | ' +
            m.visibleText
        );
    }
})();
