/**
 * Fetch file .php từ TRONG browser (đã vượt AES challenge) rồi dò nội dung mới.
 */
const { chromium } = require('playwright');
const path = require('path');

const BASE = 'https://dragonglow.freehosting.dev/wp-content/themes/dragon-glow/';
const STORAGE = path.join(__dirname, 'storage.json');

const TARGETS = [
    {
        label: 'woocommerce/myaccount/orders.php',
        file: 'woocommerce/myaccount/orders.php',
        needles: [
            'data-dg-page-out-compact',
            'data-dg-page-out-wide',
            'data-dg-page-ellipsis="compact"',
            'data-dg-page-ellipsis="wide"',
            'dg_account_orders_page_window',
            'dg_account_orders_pagination_full_limit',
        ],
    },
    {
        label: 'inc/woocommerce/account/orders.php',
        file: 'inc/woocommerce/account/orders.php',
        needles: [
            'int $radius = 1',
            'dg_account_orders_pagination_full_limit',
            'dg_account_orders_page_window',
        ],
    },
    {
        label: 'functions.php (DG_VERSION)',
        file: 'functions.php',
        needles: ["'1.16.27'"],
    },
];

(async () => {
    const browser = await chromium.launch({ channel: 'chrome' });
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, storageState: STORAGE });
    const page = await ctx.newPage();

    await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(4000);

    let fail = 0;
    for (const t of TARGETS) {
        const url = BASE + t.file + '?ver=1.16.27';
        const body = await page.evaluate(async (u) => {
            const r = await fetch(u, { credentials: 'include' });
            return await r.text();
        }, url);
        const challenged = body.includes('aes.js') || body.length < 1500;
        console.log(`\n${t.label}  [${body.length} bytes${challenged ? ' — CHALLENGE' : ''}]`);
        for (const n of t.needles) {
            const ok = body.includes(n);
            if (!ok) fail++;
            console.log('   ' + (ok ? 'OK  ' : 'MISS') + ' ' + n);
        }
    }

    await browser.close();
    console.log('\n' + (fail === 0 ? 'DEPLOY OK' : fail + ' MISSING'));
    process.exit(fail === 0 ? 0 : 1);
})();
