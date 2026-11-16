const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');

(async () => {
    const browser = await chromium.launch({ headless: true, channel: 'chrome' });
    const page = await browser.newPage();
    await page.goto('https://dragonglow.freehosting.dev/cart/', { waitUntil: 'networkidle' });
    const markup = await page.locator('[data-dg-cart-checkout-with-selected]').count();
    // A separate browser session can have an empty cart. Use the verified live CTA
    // markup inside the same summary dimensions without changing server cart data.
    if (!markup) {
        await page.evaluate(() => {
            const panel = document.createElement('div');
            panel.style.cssText = 'width:calc(100% - 48px);max-width:448px;margin:auto;padding:20px';
            panel.innerHTML = '<div class="dg-cart-checkout-cta dg-cart-checkout-cta--selected"><button type="button" class="dg-shimmer-btn w-full py-5 rounded-2xl font-label-sm text-label-sm uppercase tracking-[0.18em] font-bold flex items-center justify-center gap-2 shadow-lg" data-dg-cart-checkout-with-selected><span class="material-symbols-outlined" style="font-size:20px">verified</span>Checkout with selected<span class="material-symbols-outlined" style="font-size:20px">arrow_forward</span></button></div>';
            document.body.append(panel);
        });
    }
    await page.evaluate(() => {
        const wrapper = document.querySelector('.dg-cart-checkout-cta--selected');
        wrapper.hidden = false;
    });
    const measure = () => page.locator('[data-dg-cart-checkout-with-selected]').evaluate(b => {
        const r = b.getBoundingClientRect(), s = getComputedStyle(b);
        const icons = [...b.querySelectorAll('.material-symbols-outlined')].map(x => x.getBoundingClientRect());
        return { width: r.width, height: r.height, padding: s.paddingInline, left: icons[0].left - r.left, right: r.right - icons[1].right, iconWidths: icons.map(x => x.width), font: s.fontSize, gap: s.gap };
    });
    const widths = [320, 375, 390, 640, 641, 768, 1024, 1280];
    const before = {};
    for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });
        before[width] = await measure();
    }
    const root = path.resolve(__dirname, '../wp-content/themes/dragon-glow');
    const tokens = fs.readFileSync(path.join(root, 'style.css'), 'utf8');
    const value = tokens.match(/--cart-checkout-mobile-padding-inline:\s*([^;]+);/)[1];
    await page.addStyleTag({ content: `:root{--cart-checkout-mobile-padding-inline:${value}}` });
    await page.addStyleTag({ path: path.join(root, 'assets/css/cart.css') });
    for (const width of widths) {
        await page.setViewportSize({ width, height: 900 });
        const after = await measure();
        const pass = width <= 640
            ? after.left >= 15.9 && after.right >= 15.9 && after.iconWidths.every(x => x >= 19.9) && after.height >= 44
            : JSON.stringify(before[width]) === JSON.stringify(after);
        console.log(JSON.stringify({ width, before: before[width], after, pass }));
        if (!pass) throw new Error(`CTA verification failed at ${width}px`);
    }
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.setViewportSize({ width: 320, height: 900 });
    console.log('Reduced motion:', await measure());
    await page.locator('[data-dg-cart-checkout-with-selected]').screenshot({ path: path.join(__dirname, 'cart-cta-mobile-320.png') });
    await browser.close();
})().catch(error => { console.error(error); process.exitCode = 1; });
