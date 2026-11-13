/**
 * Kiểm chứng bản DEPLOY live (có anti-bot AES challenge).
 * Gửi cookie __test để vượt challenge, rồi tải file và dò nội dung mới.
 */
const https = require('https');
const path = require('path');

const BASE = 'https://dragonglow.freehosting.dev/wp-content/themes/dragon-glow/';
const COOKIE = '__test=' + require(path.join(__dirname, 'storage.json')).cookies.find(c => c.name === '__test').value;

const TARGETS = [
    {
        label: 'assets/css/account.css',
        file: 'assets/css/account.css',
        needles: [
            '@media (min-width: 1280px)',
            '[data-dg-page-out-compact]',
            '[data-dg-page-out-wide]',
            "ellipsis='compact']",
            "ellipsis='wide']",
            'flex-wrap: nowrap',
        ],
    },
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

function get(url) {
    return new Promise((resolve, reject) => {
        const req = https.get(url, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0 Safari/537.36',
                'Cookie': COOKIE,
            },
        }, (r) => {
            let d = '';
            r.on('data', (c) => (d += c));
            r.on('end', () => resolve({ status: r.statusCode, body: d }));
        });
        req.on('error', reject);
        req.setTimeout(30000, () => req.destroy(new Error('timeout')));
    });
}

(async () => {
    let fail = 0;
    for (const t of TARGETS) {
        const url = BASE + t.file + '?ver=1.16.27';
        try {
            const r = await get(url);
            const antiBot = r.body.includes('aes.js') || r.body.length < 1500;
            console.log(`\n${t.label}  [HTTP ${r.status}, ${r.body.length} bytes${antiBot ? ' — CÓ THỬ THÁCH ANTI-BOT' : ''}]`);
            for (const n of t.needles) {
                const ok = r.body.includes(n);
                if (!ok) fail++;
                console.log('   ' + (ok ? 'OK  ' : 'MISS') + ' ' + n);
            }
        } catch (e) {
            fail++;
            console.log(`\n${t.label}  ERROR: ${e.message}`);
        }
    }
    console.log('\n' + (fail === 0 ? 'DEPLOY OK' : fail + ' MISSING'));
    process.exit(fail === 0 ? 0 : 1);
})();
