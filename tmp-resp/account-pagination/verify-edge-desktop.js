/**
 * Kiểm chứng NGƯỠNG ranh giới desktop: 13 trang (full list) vs 14 trang (phải collapse).
 * 1280px là viewport desktop hẹp nhất → cột nội dung hẹp nhất.
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
const FULL_LIMIT = 13;

function pageWindow(current, total, radius) {
    total = Math.max(1, total);
    current = Math.min(Math.max(1, current), total);
    radius = Math.max(0, radius);
    if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [];
    if (current <= radius + 1) {
        const runEnd = Math.min(total, current + radius);
        for (let i = 1; i <= runEnd; i++) pages.push(i);
        pages.push(total);
    } else if (current >= total - radius) {
        pages.push(1);
        const runStart = Math.max(2, current - radius);
        for (let i = runStart; i <= total; i++) pages.push(i);
    } else {
        pages.push(1);
        for (let i = current - radius; i <= current + radius; i++) pages.push(i);
        pages.push(total);
    }
    return [...new Set(pages)].sort((a, b) => a - b);
}

function buildMarkup(total, current) {
    const compact = pageWindow(current, total, 1);
    let wide = pageWindow(current, total, 2);
    if (total <= FULL_LIMIT) wide = Array.from({ length: total }, (_, i) => i + 1);

    let html = '';
    if (current > 1) html += `<a class="woocommerce-button woocommerce-button--previous button" href="#">Previous</a>`;
    html += '<span class="woocommerce-pagination__numbers">';
    for (let i = 1; i <= total; i++) {
        const isCurrent = i === current;
        const inCompact = compact.includes(i);
        const inWide = wide.includes(i);
        if (!inCompact && compact.includes(i - 1)) {
            html += '<span class="page-numbers dg-account-pagination__ellipsis" aria-hidden="true" data-dg-page-ellipsis="compact">…</span>';
        }
        if (!inWide && wide.includes(i - 1)) {
            html += '<span class="page-numbers dg-account-pagination__ellipsis" aria-hidden="true" data-dg-page-ellipsis="wide">…</span>';
        }
        let attrs = '';
        if (!inCompact) attrs += ' data-dg-page-out-compact';
        if (!inWide) attrs += ' data-dg-page-out-wide';
        if (isCurrent) html += `<span class="page-numbers current" aria-current="page"${attrs}>${i}</span>`;
        else html += `<a class="page-numbers" href="#"${attrs}>${i}</a>`;
    }
    html += '</span>';
    if (current < total) html += `<a class="woocommerce-button woocommerce-button--next button" href="#">Next</a>`;
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
<div class="woocommerce-pagination woocommerce-Pagination">${buildMarkup(total, current)}</div>
</section>
</div></div></div></div></body></html>`;
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    let fail = 0;
    const check = (label, ok, detail) => {
        if (!ok) { fail++; console.log('  FAIL ' + label + (detail ? ' — ' + detail : '')); }
        else console.log('  PASS ' + label);
    };

    console.log('=== NGƯỠNG 13 vs 14 trang @ 1280px (viewport desktop hẹp nhất) ===');
    for (const [total, expectFull] of [[12, true], [13, true], [14, false], [20, false]]) {
        const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(total, 7), { waitUntil: 'load' });
        await page.waitForTimeout(120);
        const m = await page.evaluate(() => {
            const pag = document.querySelector('.woocommerce-pagination');
            const nums = pag.querySelector('.woocommerce-pagination__numbers');
            const panel = document.querySelector('.dg-account-panel');
            const pr = pag.getBoundingClientRect();
            const pills = [...nums.querySelectorAll('.page-numbers')];
            const vis = pills.filter(p => getComputedStyle(p).display !== 'none');
            const T = e => Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
            const tops = [];
            for (const e of vis) { const t = T(e); if (!tops.includes(t)) tops.push(t); }
            const pan = panel.getBoundingClientRect();
            const nr = nums.getBoundingClientRect();
            const pcs = getComputedStyle(panel);
            return {
                text: vis.map(p => p.textContent.trim()).join(' '),
                count: vis.length,
                lines: tops.length,
                panelInner: Math.round(pan.width - parseFloat(pcs.paddingLeft) * 2),
                numsWidth: Math.round(nr.width),
                overflow: nr.right > pan.right - parseFloat(pcs.paddingRight) + 1,
                hOverflow: document.documentElement.scrollWidth > window.innerWidth,
            };
        });
        console.log(`  ${total} trang → "${m.text}" (${m.count} hiển thị, lines=${m.lines}, cột=${m.panelInner}px, dải số=${m.numsWidth}px, tràn=${m.overflow})`);
        check(`${total} trang: 1 hàng`, m.lines === 1, `lines=${m.lines}`);
        check(`${total} trang: không tràn ngang`, !m.hOverflow && !m.overflow);
        if (expectFull) check(`${total} trang: hiện đủ`, m.count === total && !m.text.includes('…'), m.text);
        else check(`${total} trang: đã collapse`, m.text.includes('…'), m.text);
        await ctx.close();
    }

    console.log('\n=== Ảnh ngưỡng ===');
    for (const [w, total, cur, name] of [[1280, 13, 7, 'edge-13p-1280'], [1280, 14, 7, 'edge-14p-1280'], [1440, 14, 7, 'edge-14p-1440']]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(total, cur), { waitUntil: 'load' });
        await page.waitForTimeout(120);
        const box = await page.evaluate(() => {
            const r = document.querySelector('.woocommerce-pagination').getBoundingClientRect();
            return { x: Math.max(0, r.x - 20), y: Math.max(0, r.y - 20), width: r.width + 40, height: r.height + 40 };
        });
        await page.screenshot({ path: path.join(OUT, name + '.png'), clip: box });
        await ctx.close();
    }

    await browser.close();
    console.log('\n' + (fail === 0 ? 'ALL PASS' : fail + ' FAILURES'));
    process.exit(fail === 0 ? 0 : 1);
})();
