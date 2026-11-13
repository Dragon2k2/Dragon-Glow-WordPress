/**
 * Test: Chrome có reserve layout cho ::-webkit-scrollbar custom không?
 * Tạo div thuần trong trang live, đo offsetWidth - clientWidth.
 * Biến thể: không style / width 8px literal / width var() / scrollbar-width: thin.
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shipping-returns/';

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(600);

    const result = await page.evaluate(() => {
        const host = document.createElement('div');
        host.style.cssText = 'position:fixed;top:0;left:0;width:400px;';
        document.body.appendChild(host);

        const measure = (label, css) => {
            let styleEl = document.getElementById('sb-test-style');
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = 'sb-test-style';
                document.head.appendChild(styleEl);
            }
            styleEl.textContent = css;

            const box = document.createElement('div');
            box.className = 'sb';
            box.style.cssText = 'width:300px;height:100px;overflow-y:scroll;';
            box.innerHTML = '<div style="height:500px">x</div>';
            host.appendChild(box);
            const gutter = box.offsetWidth - box.clientWidth;
            host.removeChild(box);
            return { label, gutter };
        };

        const out = [];
        out.push(measure('no-style', ''));
        out.push(measure('webkit-8px', '.sb::-webkit-scrollbar{width:8px;} .sb::-webkit-scrollbar-thumb{background:#d4c2c2;}'));
        out.push(measure('webkit-20px', '.sb::-webkit-scrollbar{width:20px;} .sb::-webkit-scrollbar-thumb{background:#d4c2c2;}'));
        out.push(measure('std-thin', '.sb{scrollbar-width:thin;}'));
        out.push(measure('std-auto', '.sb{scrollbar-width:auto;}'));
        out.push(measure('webkit+std-thin', '.sb{scrollbar-width:thin;} .sb::-webkit-scrollbar{width:20px;}'));

        host.remove();
        return out;
    });

    console.log(JSON.stringify(result, null, 2));
    await browser.close();
})();
