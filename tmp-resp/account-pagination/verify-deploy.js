/**
 * Kiểm chứng bản deploy trên site live: file CSS/PHP mới đã lên hay chưa.
 */
const https = require('https');
const fs = require('fs');
const path = require('path');

const BASE = 'https://dragonglow.freehosting.dev/wp-content/themes/dragon-glow/';

const TARGETS = [
    {
        label: 'account.css',
        file: 'assets/css/account.css',
        needles: [
            '@media (min-width: 1280px)',
            '[data-dg-page-out-compact]',
            '[data-dg-page-out-wide]',
            "ellipsis='compact']",
            "ellipsis='wide']",
        ],
    },
    {
        label: 'woocommerce/myaccount/orders.php',
        file: 'woocommerce/myaccount/orders.php',
        needles: [
            'data-dg-page-out-compact',
            'data-dg-page-out-wide',
            "data-dg-page-ellipsis=\"compact\"",
            "data-dg-page-ellipsis=\"wide\"",
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
];

function get(url) {
    return new Promise((resolve, reject) => {
        https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (r) => {
            let d = '';
            r.on('data', (c) => (d += c));
            r.on('end', () => resolve({ status: r.statusCode, body: d }));
        }).on('error', reject);
    });
}

(async () => {
    let fail = 0;
    for (const t of TARGETS) {
        const url = BASE + t.file + '?ver=1.16.27';
        try {
            const r = await get(url);
            const results = t.needles.map((n) => {
                const ok = r.body.includes(n);
                if (!ok) fail++;
                return (ok ? 'OK  ' : 'MISS') + ' ' + n;
            });
            console.log(`\n${t.label}  [HTTP ${r.status}, ${r.body.length} bytes]`);
            results.forEach((x) => console.log('   ' + x));
        } catch (e) {
            fail++;
            console.log(`\n${t.label}  ERROR: ${e.message}`);
        }
    }
    console.log('\n' + (fail === 0 ? 'DEPLOY OK' : fail + ' MISSING'));
})();
