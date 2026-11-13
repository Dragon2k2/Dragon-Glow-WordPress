/**
 * Liệt kê rule ::-webkit-scrollbar đang tồn tại trong CSSOM của mỗi trang,
 * và kiểm tra rule cho .dg-rm-dialog có được áp không.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');

const SR_CSS = fs.readFileSync(path.join(THEME, 'assets/css/shipping-returns.css'), 'utf8');
const SR_STYLE = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
const CA_CSS = fs.readFileSync(path.join(THEME, 'assets/css/careers.css'), 'utf8');

async function inspect(browser, url, cssRoute, openSel, dialogSel, tag) {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const page = await ctx.newPage();
    await page.route(cssRoute[0], (r) => r.fulfill({ status: 200, contentType: 'text/css', body: cssRoute[1] }));
    await page.route('**/assets/css/style.css*', (r) =>
        r.fulfill({ status: 200, contentType: 'text/css', body: SR_STYLE }));

    await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(700);
    await page.evaluate((s) => document.querySelector(s)?.click(), openSel);
    await page.waitForTimeout(1000);

    const out = await page.evaluate((s) => {
        const rules = [];
        for (const sheet of document.styleSheets) {
            let list;
            try { list = sheet.cssRules; } catch (e) { continue; }
            for (const r of list) {
                if (r.selectorText && /webkit-scrollbar/.test(r.selectorText)) {
                    rules.push({
                        sheet: (sheet.href || 'inline').split('/').pop(),
                        selector: r.selectorText,
                        style: r.style.cssText,
                    });
                }
            }
        }
        const el = document.querySelector(s);
        const cs = getComputedStyle(el);
        return {
            rules,
            dialogOverflowY: cs.overflowY,
            dialogScrollbarWidth: cs.scrollbarWidth,
            dialogScrollbarGutter: cs.scrollbarGutter,
            dialogScrollbarColor: cs.scrollbarColor,
            hasScrollbar: el.scrollHeight > el.clientHeight,
            scrollbarBoxPx: el.offsetWidth - el.clientWidth,
            borderWidth: cs.borderRightWidth + ' / ' + cs.borderLeftWidth,
            /* thử ép scrollbar hiện: đổi overflow sang scroll rồi đo lại */
            forced: (() => {
                const prev = el.style.overflowY;
                el.style.overflowY = 'scroll';
                const w = el.offsetWidth - el.clientWidth;
                el.style.overflowY = prev;
                return w;
            })(),
        };
    }, dialogSel);

    await ctx.close();
    return { tag, ...out };
}

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const rm = await inspect(browser, 'https://dragonglow.freehosting.dev/shipping-returns/',
        ['**/assets/css/shipping-returns.css*', SR_CSS], '[data-return-trigger]', '.dg-rm-dialog', 'return');
    const ca = await inspect(browser, 'https://dragonglow.freehosting.dev/careers/',
        ['**/assets/css/careers.css*', CA_CSS], '[data-apply-trigger]', '.dg-apply-dialog', 'apply');
    await browser.close();
    console.log(JSON.stringify({ rm, ca }, null, 2));
})();
