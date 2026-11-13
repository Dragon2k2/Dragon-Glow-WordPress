/**
 * VERIFY CUỐI — Orders pagination.
 *
 * Checklist:
 *  1. Mobile (<=640): không tràn ngang, Prev/Next cùng hàng, touch target >= 44px
 *  2. Tablet (641-1279): 1 hàng duy nhất, không tràn, hiển thị window "1 … 5 6 7 … 12"
 *  3. Desktop (>=1280): 12 số đầy đủ, 1 hàng, không tràn
 *  4. A11y: aria-current, aria-hidden ellipsis, focus-visible
 *  5. Reduced motion: không có transition
 *  6. Trang đầu/cuối: nút Prev/Next vắng mặt → nút còn lại chiếm full row
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

/* Port nguyên văn logic PHP sang JS (giữ đồng bộ với
   inc/woocommerce/account/orders.php). */
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
    if (current > 1) {
        html += `<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="${base}/page/${current - 1}/">Previous</a>`;
    }
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

        let hiddenAttr = '';
        if (!inCompact) hiddenAttr += ' data-dg-page-out-compact';
        if (!inWide) hiddenAttr += ' data-dg-page-out-wide';

        if (isCurrent) {
            html += `<span class="page-numbers current" aria-current="page"${hiddenAttr}>${i}</span>`;
        } else {
            const url = i === 1 ? base : `${base}/page/${i}/`;
            html += `<a class="page-numbers" href="${url}"${hiddenAttr}>${i}</a>`;
        }
    }
    html += '</span>';
    if (current < total) {
        html += `<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="${base}/page/${current + 1}/">Next</a>`;
    }
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

        const R = e => {
            if (!e) return null;
            const r = e.getBoundingClientRect();
            return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) };
        };

        // Dòng thật: so sánh vị trí giữa các item (dùng top tương đối, làm tròn 2px).
        const items = [prev, ...vis, next].filter(Boolean);
        const lineTops = [];
        for (const e of items) {
            const t = Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
            if (!lineTops.includes(t)) lineTops.push(t);
        }
        lineTops.sort((a, b) => a - b);

        const lineOf = e => {
            if (!e) return -1;
            const t = Math.round((e.getBoundingClientRect().top - pr.top) / 4) * 4;
            return lineTops.indexOf(t);
        };

        const nr = nums.getBoundingClientRect();

        return {
            vw: window.innerWidth,
            docScrollWidth: document.documentElement.scrollWidth,
            hOverflow: document.documentElement.scrollWidth > window.innerWidth,
            panelInner: Math.round(panel.getBoundingClientRect().width - parseFloat(pcs.paddingLeft) * 2),
            numbersOverflow: nr.right > panel.getBoundingClientRect().right - parseFloat(pcs.paddingRight) + 1,
            visibleText: vis.map(p => p.textContent.trim()).join(' '),
            visibleCount: vis.length,
            ellipsisCount: ell.length,
            ellipsisVisible: ell.filter(e => getComputedStyle(e).display !== 'none').length,
            lines: lineTops.length,
            prevLine: lineOf(prev), numsLine: lineOf(vis[0]), nextLine: lineOf(next),
            prev: R(prev), next: R(next),
            pagDisplay: getComputedStyle(pag).display,
            pagWrap: getComputedStyle(pag).flexWrap,
            currentHasAria: !!pag.querySelector('.page-numbers.current[aria-current="page"]'),
            ellipsisAriaHidden: ell.every(e => e.getAttribute('aria-hidden') === 'true'),
            hiddenCount: pills.filter(p => p.hasAttribute('data-dg-page-hidden')).length,
        };
    });
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = {};
    let fail = 0;
    const check = (label, ok, detail) => {
        if (!ok) { fail++; console.log('  FAIL ' + label + (detail ? ' — ' + detail : '')); }
        else console.log('  PASS ' + label);
    };

    /* ── 1. MOBILE (320-640) ── */
    console.log('\n=== MOBILE (current=6, total=12) ===');
    for (const w of [320, 360, 390, 414, 480, 600, 640]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        out[`m${w}`] = m;

        check(`${w}px no h-overflow`, !m.hOverflow, `scrollWidth=${m.docScrollWidth}`);
        check(`${w}px numbers inside panel`, !m.numbersOverflow);
        check(`${w}px prev+next same row`, m.prevLine === m.nextLine && m.prevLine !== -1, `${m.prevLine}/${m.nextLine}`);
        check(`${w}px touch target >=44`, m.prev && m.next && m.prev.h >= 44 && m.next.h >= 44, `${m.prev?.h}/${m.next?.h}`);
        check(`${w}px window = "1 … 5 6 7 … 12"`, m.visibleText === '1 … 5 6 7 … 12', m.visibleText);
        check(`${w}px aria-current present`, m.currentHasAria);
        check(`${w}px ellipsis aria-hidden`, m.ellipsisAriaHidden);
        const box = await page.evaluate(() => {
            const r = document.querySelector('.woocommerce-pagination').getBoundingClientRect();
            return { x: Math.max(0, r.x - 10), y: Math.max(0, r.y - 10), width: r.width + 20, height: r.height + 20 };
        });
        await page.screenshot({ path: path.join(OUT, `final-mobile-${w}.png`), clip: box });
        await ctx.close();
    }

    /* ── 2. TABLET (641-1279) ── */
    console.log('\n=== TABLET (current=6, total=12) ===');
    for (const w of [641, 768, 834, 900, 1023, 1024, 1080, 1112, 1200, 1279]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        out[`t${w}`] = m;

        check(`${w}px single line`, m.lines === 1, `lines=${m.lines} prev/nums/next=${m.prevLine}/${m.numsLine}/${m.nextLine}`);
        check(`${w}px no h-overflow`, !m.hOverflow, `scrollWidth=${m.docScrollWidth}`);
        check(`${w}px numbers inside panel`, !m.numbersOverflow);
        check(`${w}px window = "1 … 5 6 7 … 12"`, m.visibleText === '1 … 5 6 7 … 12', m.visibleText);
        check(`${w}px nowrap`, m.pagWrap === 'nowrap', m.pagWrap);
        const box = await page.evaluate(() => {
            const r = document.querySelector('.woocommerce-pagination').getBoundingClientRect();
            return { x: Math.max(0, r.x - 10), y: Math.max(0, r.y - 10), width: r.width + 20, height: r.height + 20 };
        });
        await page.screenshot({ path: path.join(OUT, `final-tablet-${w}.png`), clip: box });
        await ctx.close();
    }

    /* ── 3. DESKTOP (>=1280) — 12 trang vẫn đủ, 50 trang dùng window rộng ── */
    console.log('\n=== DESKTOP (current=6, total=12) ===');
    for (const w of [1280, 1440, 1920]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        out[`d${w}`] = m;

        check(`${w}px all 12 pages visible`, m.visibleCount === 12 && m.visibleText === '1 2 3 4 5 6 7 8 9 10 11 12', m.visibleText);
        check(`${w}px single line`, m.lines === 1, `lines=${m.lines}`);
        check(`${w}px no h-overflow`, !m.hOverflow);
        check(`${w}px ellipsis hidden`, m.ellipsisVisible === 0);
        await ctx.close();
    }

    console.log('\n=== DESKTOP (current=25, total=50) — window rộng ===');
    for (const w of [1280, 1440, 1920]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, 25), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        out[`d50-${w}`] = m;

        check(`${w}px wide window`, m.visibleText === '1 … 23 24 25 26 27 … 50', m.visibleText);
        check(`${w}px single line`, m.lines === 1, `lines=${m.lines}`);
        check(`${w}px no h-overflow`, !m.hOverflow);
        check(`${w}px inside panel`, !m.numbersOverflow);
        await ctx.close();
    }

    /* ── 4. TRANG ĐẦU / CUỐI ── */
    console.log('\n=== EDGE PAGES ===');
    for (const [label, cur] of [['first', 1], ['last', 12]]) {
        for (const w of [390, 834, 1440]) {
            const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
            const page = await ctx.newPage();
            await page.setContent(pageHtml(12, cur), { waitUntil: 'load' });
            await page.waitForTimeout(100);
            const m = await probe(page);
            const onlyOne = (cur === 1 && !m.prev && m.next) || (cur === 12 && m.prev && !m.next);
            check(`${label} @${w}px exactly one nav button`, onlyOne, `prev=${!!m.prev} next=${!!m.next}`);
            check(`${label} @${w}px no h-overflow`, !m.hOverflow);
            await ctx.close();
        }
    }

    /* ── 5. REDUCED MOTION ── */
    console.log('\n=== REDUCED MOTION ===');
    {
        const ctx = await browser.newContext({ viewport: { width: 390, height: 900 }, reducedMotion: 'reduce' });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await page.evaluate(() => {
            const p = document.querySelector('.woocommerce-pagination .page-numbers');
            const cs = getComputedStyle(p);
            return { dur: cs.transitionDuration, vis: cs.visibility, opacity: cs.opacity };
        });
        check('reduced-motion transition neutralised', parseFloat(m.dur) < 0.01 || m.dur === '0s', m.dur);
        await ctx.close();
    }

    /* ── 6. NHIỀU TRANG (edge case: 50 trang) ── */
    console.log('\n=== 50 PAGES (edge case) ===');
    for (const w of [390, 834, 1440]) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        await page.setContent(pageHtml(50, 25), { waitUntil: 'load' });
        await page.waitForTimeout(100);
        const m = await probe(page);
        check(`50p @${w}px no h-overflow`, !m.hOverflow, `scrollWidth=${m.docScrollWidth}`);
        check(`50p @${w}px numbers inside panel`, !m.numbersOverflow);
        await ctx.close();
    }

    await browser.close();
    console.log('\n' + (fail === 0 ? 'ALL PASS' : fail + ' FAILURES'));
    fs.writeFileSync(path.join(OUT, 'final-result.json'), JSON.stringify(out, null, 2));
    process.exit(fail === 0 ? 0 : 1);
})();
