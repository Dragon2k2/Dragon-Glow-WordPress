/**
 * Probe: does the live site serve the CURRENT local theme files?
 * Compares a marker string that only exists in the latest local edits.
 */
const { chromium } = require('playwright');

const BASE = 'https://dragonglow.freehosting.dev/wp-content/themes/dragon-glow';

const FILES = [
    'assets/css/gift-cards.css',
    'assets/js/gift-cards-zoom.js',
    'assets/js/lib/gift-value.js',
    'assets/js/product.js',
    'style.css',
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext();
    const page = await ctx.newPage();

    for (const f of FILES) {
        try {
            const res = await page.goto(`${BASE}/${f}`, { timeout: 30000 });
            const body = await res.text();
            console.log(`\n=== ${f} — status ${res.status()} len ${body.length}`);
            if (f.endsWith('gift-cards.css')) {
                console.log('has .dg-gift-zoom-modal:', body.includes('.dg-gift-zoom-modal'));
                console.log('has --gift-zoom-backdrop:', body.includes('--gift-zoom-backdrop'));
            }
            if (f.endsWith('gift-cards-zoom.js')) {
                console.log('has DGGiftValue.set:', body.includes('DGGiftValue.set'));
                console.log('has gift-value.js (sync):', body.includes('DGGiftValue'));
            }
            if (f.endsWith('gift-value.js')) {
                console.log('EXISTS on live (new file!):', body.length > 100);
            }
            if (f.endsWith('style.css')) {
                const m = body.match(/--gift-zoom-backdrop:[^;]+;/);
                console.log('gift-zoom token:', m ? m[0] : 'none');
            }
            if (f.endsWith('product.js')) {
                console.log('has dgOpenZoom:', body.includes('dgOpenZoom'));
            }
        } catch (e) {
            console.log(`\n=== ${f} ERROR: ${e.message}`);
        }
    }

    await browser.close();
})();
