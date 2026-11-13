/**
 * Kiểm chứng Orders pagination ở mobile.
 *
 * Render markup thật (từ woocommerce/myaccount/orders.php) + CSS theme thật,
 * đo: tràn ngang, số pill hiển thị, hàng Prev/Next, touch target.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const OUT = __dirname;
fs.mkdirSync(OUT, { recursive: true });

const ACCOUNT_CSS = fs.readFileSync(path.join(THEME, 'assets/css/account.css'), 'utf8');
const WC_CSS = fs.readFileSync(path.join(THEME, 'assets/css/woocommerce.css'), 'utf8');
const STYLE_CSS = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');

/* Mirror of the markup produced by woocommerce/myaccount/orders.php. */
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
    html += '</span>';
    return html;
}

/* Mirror of dg_account_orders_page_window(). */
function windowOf(current, total) {
    total = Math.max(1, total);
    current = Math.min(Math.max(1, current), total);
    if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = new Set([1, total]);
    for (let i = current - 1; i <= current + 1; i++) {
        if (i > 1 && i < total) pages.add(i);
    }
    return [...pages].sort((a, b) => a - b);
}

function pageHtml(total, current) {
    return `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${STYLE_CSS}</style><style>${WC_CSS}</style><style>${ACCOUNT_CSS}</style>
</head><body class="dg-js">
<div class="dg-account"><div class="dg-account__wrap"><div class="dg-account__layout">
<div class="dg-account__sidebar"></div>
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

async function measure(page, total, current) {
    return page.evaluate(({ total, current }) => {
        const pag = document.querySelector('.woocommerce-pagination');
        const numbers = document.querySelector('.woocommerce-pagination__numbers');
        const pills = Array.from(numbers.querySelectorAll('.page-numbers'));
        const visiblePills = pills.filter(p => getComputedStyle(p).display !== 'none');
        const hiddenPills = pills.filter(p => getComputedStyle(p).display === 'none');

        const navRect = pag.getBoundingClientRect();
        const numRect = numbers.getBoundingClientRect();
        const prev = pag.querySelector('.woocommerce-button--previous');
        const next = pag.querySelector('.woocommerce-button--next');
        const prevRect = prev.getBoundingClientRect();
        const nextRect = next.getBoundingClientRect();

        const doc = document.documentElement;
        return {
            viewport: window.innerWidth,
            docScrollWidth: doc.scrollWidth,
            bodyScrollWidth: document.body.scrollWidth,
            horizontalOverflow: doc.scrollWidth > window.innerWidth,
            navRect: { left: navRect.left, right: navRect.right, width: navRect.width },
            numbersWidth: numRect.width,
            numbersOverflowsPanel: numRect.right > navRect.right + 1 || numRect.left < navRect.left - 1,
            visiblePillText: visiblePills.map(p => p.textContent.trim()),
            visiblePillCount: visiblePills.length,
            hiddenPillCount: hiddenPills.length,
            pagFlexDirection: getComputedStyle(pag).flexDirection,
            prevRect: { top: prevRect.top, height: prevRect.height, width: prevRect.width },
            nextRect: { top: nextRect.top, height: nextRect.height, width: nextRect.width },
            prevNextSameRow: Math.abs(prevRect.top - nextRect.top) < 2,
            prevHeight: prevRect.height,
            touchTargetOk: prevRect.height >= 44 && nextRect.height >= 44,
            numbersDisplay: getComputedStyle(numbers).display,
            numbersWrap: getComputedStyle(numbers).flexWrap,
        };
    }, { total, current });
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const out = {};
    const viewports = [320, 360, 390, 480, 639, 640, 768, 1024, 1280];

    for (const w of viewports) {
        const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
        const page = await ctx.newPage();
        const errors = [];
        page.on('pageerror', e => errors.push(String(e.message)));

        await page.setContent(pageHtml(12, 6), { waitUntil: 'load' });
        await page.waitForTimeout(150);
        const m = await measure(page, 12, 6);
        m.errors = errors;
        out[w] = m;

        await page.screenshot({
            path: path.join(OUT, `pag-${w}.png`),
            clip: await page.evaluate(() => {
                const r = document.querySelector('.woocommerce-pagination').getBoundingClientRect();
                return { x: Math.max(0, r.x - 8), y: Math.max(0, r.y - 8), width: r.width + 16, height: r.height + 16 };
            }),
        });
        await ctx.close();
    }

    await browser.close();
    console.log(JSON.stringify(out, null, 2));
})();
