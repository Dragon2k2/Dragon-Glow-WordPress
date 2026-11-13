/**
 * Hồi quy: so sánh layout bảng đơn hàng giữa CSS baseline (git HEAD) và CSS
 * hiện tại ở tablet/desktop — phải giống hệt.
 * Chạy: node tmp-resp/account-pagination/regression-orders-desktop.js
 */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const DIR = __dirname;
const THEME = path.join(DIR, '../../wp-content/themes/dragon-glow');
const BASELINE = path.join(DIR, 'baseline-theme');

const MOCK = fs.readFileSync(path.join(DIR, 'orders-mobile-mock.html'), 'utf8');

(async () => {
	// Dựng bản baseline: copy theme + ghi đè CSS bằng bản từ git HEAD.
	fs.rmSync(BASELINE, { recursive: true, force: true });
	fs.cpSync(THEME, BASELINE, { recursive: true });
	fs.copyFileSync(path.join(DIR, 'baseline-account.css'), path.join(BASELINE, 'assets/css/account.css'));
	fs.copyFileSync(path.join(DIR, 'baseline-style.css'), path.join(BASELINE, 'style.css'));

	// Đặt mock HTML vào từng bản theme để link CSS tương đối hoạt động.
	fs.writeFileSync(path.join(THEME, 'tmp-orders-mock.html'), MOCK);
	fs.writeFileSync(path.join(BASELINE, 'tmp-orders-mock.html'), MOCK);

	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage();
	let fail = 0;

	for (const w of [641, 768, 834, 1024, 1279, 1280, 1440, 1920]) {
		const snap = async (root) => {
			await page.setViewportSize({ width: w, height: 900 });
			await page.goto('file:///' + path.join(root, 'tmp-orders-mock.html').replace(/\\/g, '/'), { waitUntil: 'load' });
			await page.waitForTimeout(200);
			return page.evaluate(() => {
				const t = document.querySelector('.woocommerce-orders-table');
				const row = t.querySelector('tbody tr');
				const sig = (c) => {
					const b = c.getBoundingClientRect();
					const cs = getComputedStyle(c);
					return `${c.tagName}:${cs.display}:${cs.textAlign}:${cs.fontWeight}:${cs.color}:${Math.round(b.x)},${Math.round(b.y)},${Math.round(b.width)},${Math.round(b.height)}`;
				};
				return {
					thead: getComputedStyle(t.querySelector('thead')).display,
					row: sig(row),
					cells: [].map.call(row.children, sig),
					totalText: row.querySelector('.woocommerce-orders-table__cell-order-total').textContent.trim().replace(/\s+/g, ' '),
					btn: (() => { const b = row.querySelector('.woocommerce-button').getBoundingClientRect(); return `${Math.round(b.width)}x${Math.round(b.height)}`; })(),
				};
			});
		};

		const base = await snap(BASELINE);
		const cur = await snap(THEME);

		const diffs = [];
		if (base.thead !== cur.thead) diffs.push(`thead ${base.thead}→${cur.thead}`);
		if (base.row !== cur.row) diffs.push(`row ${base.row} → ${cur.row}`);
		base.cells.forEach((c, i) => { if (c !== cur.cells[i]) diffs.push(`cell${i} ${c} → ${cur.cells[i]}`); });
		if (base.btn !== cur.btn) diffs.push(`btn ${base.btn} → ${cur.btn}`);
		if (base.totalText !== cur.totalText) diffs.push(`totalText "${base.totalText}" → "${cur.totalText}"`);

		if (diffs.length) fail++;
		console.log(`${diffs.length ? 'FAIL' : 'PASS'} ${String(w).padStart(4)}px ${diffs.join(' | ')}`);
	}

	fs.rmSync(BASELINE, { recursive: true, force: true });
	fs.rmSync(path.join(THEME, 'tmp-orders-mock.html'), { force: true });
	console.log('\n' + (fail === 0 ? 'NO REGRESSION' : fail + ' REGRESSIONS'));
	await browser.close();
	process.exit(fail === 0 ? 0 : 1);
})();
