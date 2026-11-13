/**
 * Hồi quy: so sánh pagination TRƯỚC (git HEAD) và SAU khi sửa.
 *
 * Mục tiêu: desktop/tablet (>=641px) phải IDENTICAL; mobile phải hết tràn ngang.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const OUT = __dirname;

const NEW_ACCOUNT = fs.readFileSync(path.join(THEME, 'assets/css/account.css'), 'utf8');
const NEW_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const WC_CSS = fs.readFileSync(path.join(THEME, 'assets/css/woocommerce.css'), 'utf8');

const OLD_ACCOUNT = fs.readFileSync(path.join(__dirname, 'baseline-account.css'), 'utf8');
const OLD_STYLE = fs.readFileSync(path.join(__dirname, 'baseline-style.css'), 'utf8');

function buildMarkup(total, current, withTags) {
    const base = 'https://dragonglow.freehosting.dev/my-account/orders';
    const visible = windowOf(current, total);
    let html = '<span class="woocommerce-pagination__numbers">';
    for (let i = 1; i <= total; i++) {
        const isCurrent = i === current;
        const isHidden = withTags && !visible.includes(i);
        if (withTags && isHidden && visible.includes(i - 1)) {
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
    html += '</span>';
    return html;
}

function windowOf(current, total) {
    total = Math.max(1, total);
    current = Math.min(Math.max(1, current), total);
    if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = new Set([1, total]);
    for (let i = current - 1; i <= current + 1; i++) if (i > 1 && i < total) pages.add(i);
    return [...pages].sort((a, b) => a - b);
}

function pageHtml(accountCss, styleCss, total, current, withTags) {
    return `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${styleCss}</style><style>${WC_CSS}</style><style>${accountCss}</style>
</head><body class="dg-js">
<div class="dg-account"><div class="dg-account__wrap"><div class="dg-account__layout">
<div class="dg-account__sidebar"></div>
<div class="dg-account__content">
<section class="dg-account-panel">
<div class="woocommerce-pagination woocommerce-Pagination">
<a class="woocommerce-button woocommerce-button--previous button" href="#">Previous</a>
${buildMarkup(total, current, withTags)}
<a class="woocommerce-button woocommerce-button--next button" href="#">Next</a>
</div>
</section>
</div></div></div></div></body></html>`;
}

async function snapshot(page) {
    return page.evaluate(() => {
        const pag = document.querySelector('.woocommerce-pagination');
        const numbers = document.querySelector('.woocommerce-pagination__numbers');
        const pills = Array.from(numbers.querySelectorAll('.page-numbers'));
        const vis = pills.filter(p => getComputedStyle(p).display !== 'none');
        const prev = pag.querySelector('.woocommerce-button--previous');
        const next = pag.querySelector('.woocommerce-button--next');
        const nr = numbers.getBoundingClientRect();
        const pr = prev.getBoundingClientRect();
        const nr2 = next.getBoundingClientRect();
        return {
            docScrollWidth: document.documentElement.scrollWidth,
            innerWidth: window.innerWidth,
            hOverflow: document.documentElement.scrollWidth > window.innerWidth,
            numbersWidth: Math.round(nr.width),
            navWidth: Math.round(pag.getBoundingClientRect().width),
            visiblePills: vis.map(p => p.textContent.trim()).join(','),
            visibleCount: vis.length,
            totalPills: pills.length,
            prevRect: { x: Math.round(pr.x), y: Math.round(pr.y), w: Math.round(pr.width), h: Math.round(pr.height) },
            nextRect: { x: Math.round(nr2.x), y: Math.round(nr2.y), w: Math.round(nr2.width), h: Math.round(nr2.height) },
            pillSize: vis.length ? { w: Math.round(vis[0].getBoundingClientRect().width), h: Math.round(vis[0].getBoundingClientRect().height), fs: getComputedStyle(vis[0]).fontSize } : null,
        };
    });
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = {};
    const viewports = [320, 375, 390, 480, 639, 640, 641, 768, 1024, 1280];

    for (const w of viewports) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();

        // BEFORE (HEAD): markup cũ (không tag), CSS cũ.
        await page.setContent(pageHtml(OLD_ACCOUNT, OLD_STYLE, 12, 6, false), { waitUntil: 'load' });
        await page.waitForTimeout(120);
        const before = await snapshot(page);
        await page.screenshot({ path: path.join(OUT, `before-${w}.png`) });

        // AFTER: markup mới (có tag), CSS mới.
        await page.setContent(pageHtml(NEW_ACCOUNT, NEW_STYLE, 12, 6, true), { waitUntil: 'load' });
        await page.waitForTimeout(120);
        const after = await snapshot(page);
        await page.screenshot({ path: path.join(OUT, `after-${w}.png`) });

        const desktopUnchanged = JSON.stringify({
            numbersWidth: before.numbersWidth, navWidth: before.navWidth,
            visiblePills: before.visiblePills, visibleCount: before.visibleCount,
            totalPills: before.totalPills, pillSize: before.pillSize,
            prevRect: before.prevRect, nextRect: before.nextRect,
        }) === JSON.stringify({
            numbersWidth: after.numbersWidth, navWidth: after.navWidth,
            visiblePills: after.visiblePills, visibleCount: after.visibleCount,
            totalPills: after.totalPills, pillSize: after.pillSize,
            prevRect: after.prevRect, nextRect: after.nextRect,
        });

        out[w] = {
            before: { hOverflow: before.hOverflow, docScrollWidth: before.docScrollWidth, visibleCount: before.visibleCount, numbersWidth: before.numbersWidth, visiblePills: before.visiblePills },
            after: { hOverflow: after.hOverflow, docScrollWidth: after.docScrollWidth, visibleCount: after.visibleCount, numbersWidth: after.numbersWidth, visiblePills: after.visiblePills },
            desktopUnchanged,
            rawBefore: before,
            rawAfter: after,
        };
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
