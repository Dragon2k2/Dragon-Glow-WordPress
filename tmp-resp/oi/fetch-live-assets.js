/**
 * Lấy JS/CSS thật đang chạy trên live (bypass anti-bot bằng browser thật)
 * rồi so sánh với file local để biết user đang gặp lỗi ở đâu.
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..', '..');
const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';
const OUT = path.join(__dirname, 'live');
fs.mkdirSync(OUT, { recursive: true });

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();

    /* chặn và lưu bản live */
    const captured = {};
    await page.route('**/assets/js/shipping-returns.js*', async (route) => {
        const res = await route.fetch();
        const body = await res.text();
        captured.js = body;
        fs.writeFileSync(path.join(OUT, 'live-shipping-returns.js'), body);
        await route.fulfill({ status: 200, contentType: 'text/javascript', body });
    });
    await page.route('**/assets/css/shipping-returns.css*', async (route) => {
        const res = await route.fetch();
        const body = await res.text();
        captured.css = body;
        fs.writeFileSync(path.join(OUT, 'live-shipping-returns.css'), body);
        await route.fulfill({ status: 200, contentType: 'text/css', body });
    });

    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(800);

    const localJs = fs.readFileSync(path.join(ROOT, 'wp-content/themes/dragon-glow/assets/js/shipping-returns.js'), 'utf8');
    const localCss = fs.readFileSync(path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css/shipping-returns.css'), 'utf8');

    const norm = (s) => s.replace(/\r\n/g, '\n').trim();
    console.log(JSON.stringify({
        liveJsBytes: captured.js ? captured.js.length : null,
        localJsBytes: localJs.length,
        jsIdentical: captured.js ? norm(captured.js) === norm(localJs) : null,
        liveHasConstEase: captured.js ? /const EASE/.test(captured.js) : null,
        liveCssBytes: captured.css ? captured.css.length : null,
        localCssBytes: localCss.length,
        cssIdentical: captured.css ? norm(captured.css) === norm(localCss) : null,
    }, null, 2));

    /* diff dòng */
    if (captured.js && norm(captured.js) !== norm(localJs)) {
        const a = norm(captured.js).split('\n');
        const b = norm(localJs).split('\n');
        console.log('--- live lines', a.length, ' local lines', b.length);
        for (let i = 0; i < Math.max(a.length, b.length); i++) {
            if (a[i] !== b[i]) console.log(`L${i + 1}\n  live : ${String(a[i]).trim()}\n  local: ${String(b[i]).trim()}`);
        }
    }

    await browser.close();
})();
