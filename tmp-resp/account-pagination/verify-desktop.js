/**
 * VERIFY DESKTOP — Orders pagination.
 *
 * Kiểm chứng cơ chế 2 window:
 *   - compact window (current ±1)  → dùng ở < 1280px
 *   - wide window   (current ±2)  → dùng ở >= 1280px
 *   - desktop list đầy đủ khi tổng trang <= 13 (vừa 1 hàng ở 776px)
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

/* Port nguyên văn logic PHP sang JS. */
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
    const base = 'https://dragonglow.freehosting.dev/my-account/orders';
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
        else html += `<a class="page-numbers" href="${base}/page/${i}/"${attrs}>${i}</a>`;
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

async function probe(page) {
    return page.evaluate(() => {
        const pag = document.querySelector('.woocommerce-pagination');
        const nums = pag.querySelector('.woocommerce-pagination__numbers');
        const panel = document.querySelector('.dg-account-panel');
        const prev = pag.querySelector('.woocommerce-button--previous');
        const next = pag.querySelector('.woocommerce-button--next');
        const pcs = getComputedStyle(panel);
        const pr = pag.getBoundingClientRect();

        const pills = [...nums.querySelectorAll('.page-numbers')];
        const vis = pills.filter(p => getComputedStyle(p).display !== 'none');
        const ell = pills.filter(p => p.classList.contains('dg-account-pagination__ellipsis'));

        const T = e => Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
        const tops = [];
        for (const e of [prev, ...vis, next].filter(Boolean)) {
            const t = T(e);
            if (!tops.includes(t)) tops.push(t);
        }
        tops.sort((a, b) => a - b);
        const lineOf = e => (e ? tops.indexOf(T(e)) : -1);

        const nr = nums.getBoundingClientRect();
        const pan = panel.getBoundingClientRect();

        return {
            vw: window.innerWidth,
            docScrollWidth: document.documentElement.scrollWidth,
            hOverflow: document.documentElement.scrollWidth > window.innerWidth,
            panelInner: Math.round(pan.width - parseFloat(pcs.paddingLeft) * 2),
            numbersOverflow: nr.right > pan.right - parseFloat(pcs.paddingRight) + 1,
            visibleText: vis.map(p => p.textContent.trim()).join(' '),
            visibleCount: vis.length,
            ellipsisVisible: ell.filter(e => getComputedStyle(e).display !== 'none').length,
            lines: tops.length,
            prevLine: lineOf(prev), numsLine: lineOf(vis[0]), nextLine: lineOf(next),
            pagWrap: getComputedStyle(pag).flexWrap,
            currentHasAria: !!pag.querySelector('.page-numbers.current[aria-current="page"]'),
            ellipsisAriaHidden: ell.every(e => e.getAttribute('aria-hidden') === 'true'),
        };
    });
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    let fail = 0;
    const check = (label, ok, detail) => {
        if (!ok) { fail++; console.log('  FAIL ' + label + (detail ? ' — ' + detail : '')); }
    };

    /* ── DESKTOP: 12 trang (dữ liệu thật) → phải hiện đủ 12 số ── */
    console.log('=== DESKTOP 12 trang (current=6) — phải giữ nguyên 12 số ===');
    for (const w of [1280, 1366, 1440, 1600, 1920]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        console.log(`  ${w}px → "${m.visibleText}" lines=${m.lines} ellipsis=${m.ellipsisVisible}`);
        check(`${w}px all 12 pages`, m.visibleText === '1 2 3 4 5 6 7 8 9 10 11 12', m.visibleText);
        check(`${w}px single line`, m.lines === 1, `lines=${m.lines}`);
        check(`${w}px no ellipsis`, m.ellipsisVisible === 0, `${m.ellipsisVisible}`);
        check(`${w}px no h-overflow`, !m.hOverflow);
        await ctx.close();
    }

    /* ── DESKTOP: 50 trang → window rộng, 1 hàng ── */
    console.log('\n=== DESKTOP 50 trang (current=25) — window rộng ===');
    for (const w of [1280, 1440, 1920]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, 25), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        console.log(`  ${w}px → "${m.visibleText}" lines=${m.lines} ellipsis=${m.ellipsisVisible}`);
        check(`50p ${w}px single line`, m.lines === 1, `lines=${m.lines}`);
        check(`50p ${w}px no h-overflow`, !m.hOverflow);
        check(`50p ${w}px inside panel`, !m.numbersOverflow);
        check(`50p ${w}px wide window`, m.visibleText === '1 … 23 24 25 26 27 … 50', m.visibleText);
        await ctx.close();
    }

    /* ── DESKTOP: các mốc trang đầu/cuối ── */
    console.log('\n=== DESKTOP 50 trang — trang đầu / cuối ===');
    for (const [label, cur, expect] of [
        ['first', 1, '1 2 3 … 50'],
        ['last', 50, '1 … 48 49 50'],
        ['second', 2, '1 2 3 4 … 50'],
    ]) {
        const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, cur), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        console.log(`  ${label} → "${m.visibleText}" lines=${m.lines}`);
        check(`${label} window`, m.visibleText === expect, m.visibleText);
        check(`${label} single line`, m.lines === 1, `lines=${m.lines}`);
        await ctx.close();
    }

    /* ── HỒI QUY: mobile + tablet không đổi ── */
    console.log('\n=== HỒI QUY mobile/tablet 12 trang (current=6) ===');
    for (const w of [320, 390, 640, 641, 834, 1024, 1279]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        console.log(`  ${w}px → "${m.visibleText}" lines=${m.lines}`);
        check(`${w}px compact window`, m.visibleText === '1 … 5 6 7 … 12', m.visibleText);
        check(`${w}px no h-overflow`, !m.hOverflow);
        check(`${w}px inside panel`, !m.numbersOverflow);
        await ctx.close();
    }

    console.log('\n=== HỒI QUY mobile 50 trang ===');
    for (const w of [320, 390, 834]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, 25), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        console.log(`  ${w}px → "${m.visibleText}"`);
        check(`50p ${w}px no h-overflow`, !m.hOverflow);
        check(`50p ${w}px inside panel`, !m.numbersOverflow);
        await ctx.close();
    }

    /* ── A11Y + reduced motion ── */
    console.log('\n=== A11Y / REDUCED MOTION ===');
    {
        const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, 25), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        check('aria-current present', m.currentHasAria);
        check('ellipsis aria-hidden', m.ellipsisAriaHidden);
        await ctx.close();
    }
    {
        const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, reducedMotion: 'reduce' });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, 25), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const d = await page.evaluate(() => getComputedStyle(document.querySelector('.woocommerce-pagination .page-numbers')).transitionDuration);
        check('reduced-motion neutralised', parseFloat(d) < 0.01 || d === '0s', d);
        await ctx.close();
    }

    /* ── Ảnh ── */
    for (const [w, total, cur, name] of [[1440, 12, 6, 'shot-desktop-12p'], [1440, 50, 25, 'shot-desktop-50p'], [834, 12, 6, 'shot-tablet-12p']]) {
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
