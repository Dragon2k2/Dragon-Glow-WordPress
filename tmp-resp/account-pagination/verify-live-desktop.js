/**
 * Kiểm chứng phân trang My Account Orders trên site live ở nhiều viewport.
 * Chạy: node tmp-resp/account-pagination/verify-live-desktop.js
 */
const { chromium } = require('playwright');
const path = require('path');

const STORAGE = path.join(__dirname, 'storage.json');
const BASE = 'https://dragonglow.freehosting.dev';

const VIEWPORTS = [
	{ name: 'mobile-320', width: 320, height: 800 },
	{ name: 'mobile-390', width: 390, height: 844 },
	{ name: 'mobile-640', width: 640, height: 900 },
	{ name: 'tablet-768', width: 768, height: 900 },
	{ name: 'tablet-1024', width: 1024, height: 900 },
	{ name: 'tablet-1279', width: 1279, height: 900 },
	{ name: 'desktop-1280', width: 1280, height: 900 },
	{ name: 'desktop-1440', width: 1440, height: 900 },
	{ name: 'desktop-1920', width: 1920, height: 1000 },
];

const PAGES = [1, 2, 6, 11, 12];

async function readPagination(page) {
	return await page.evaluate(() => {
		const nums = document.querySelector('.woocommerce-pagination__numbers');
		if (!nums) return null;
		const visible = [];
		nums.childNodes.forEach((n) => {
			if (n.nodeType === 3) return;
			if (getComputedStyle(n).display === 'none') return;
			visible.push((n.textContent || '').trim());
		});
		const p = document.querySelector('.woocommerce-pagination');
		const pr = p.getBoundingClientRect();
		const nr = nums.getBoundingClientRect();
		const panel = p.closest('.dg-account-panel') || document.querySelector('.woocommerce-MyAccount');
		const pr2 = panel.getBoundingClientRect();
		const cur = nums.querySelector('[aria-current="page"]');
		return {
			visible: visible.join(' '),
			pagH: Math.round(pr.height),
			numsW: Math.round(nr.width),
			overflow: Math.round(nr.right - pr2.right),
			docOverflow: Math.round(document.documentElement.scrollWidth - document.documentElement.clientWidth),
			prev: !!p.querySelector('.woocommerce-button--previous'),
			next: !!p.querySelector('.woocommerce-button--next'),
			ariaCurrent: cur ? (cur.textContent || '').trim() : null,
		};
	});
}

/** Số phần tử hiển thị kỳ vọng theo window + vị trí trang. */
function expectedCount(isDesktop, pg) {
	if (isDesktop) {
		if (pg <= 3) return 5;    // 1 2 3 … 12
		if (pg >= 10) return 5;   // 1 … 9 10 11 12
		return 8;                // 1 … 4 5 6 7 8 … 12
	}
	if (pg <= 2) return 4;       // 1 2 … 12
	if (pg >= 11) return 4;      // 1 … 11 12
	return 5;                   // 1 … 5 6 7 … 12
}

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const ctx = await browser.newContext({ storageState: STORAGE, viewport: { width: 1440, height: 900 } });
	const page = await ctx.newPage();
	let fail = 0;

	for (const vp of VIEWPORTS) {
		await page.setViewportSize({ width: vp.width, height: vp.height });
		for (const pg of PAGES) {
			const url = `${BASE}/my-account/orders/${pg > 1 ? 'page/' + pg + '/' : ''}`;
			await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
			await page.waitForTimeout(700);
			const r = await readPagination(page);
			if (!r) {
				console.log(`FAIL ${vp.name.padEnd(14)} p${pg} — không có phân trang (chưa đăng nhập?)`);
				fail++;
				continue;
			}

			const isDesktop = vp.width >= 1280;
			const hasEllipsis = r.visible.includes('…');
			const count = r.visible.split(' ').filter(Boolean).length;
			const exp = expectedCount(isDesktop, pg);

			const problems = [];
			if (!hasEllipsis) problems.push('thiếu ellipsis');
			if (count !== exp) problems.push(`số phần tử ${count} ≠ ${exp}`);
			if (r.overflow > 0) problems.push('tràn panel ' + r.overflow + 'px');
			if (r.docOverflow > 0) problems.push('tràn ngang ' + r.docOverflow + 'px');
			if (r.pagH > 60) problems.push('cao bất thường ' + r.pagH + 'px');
			if (r.ariaCurrent !== String(pg)) problems.push('aria-current sai: ' + r.ariaCurrent);
			if (pg > 1 && !r.prev) problems.push('thiếu nút Previous');
			if (pg < 12 && !r.next) problems.push('thiếu nút Next');

			if (problems.length) fail++;
			console.log(`${problems.length ? 'FAIL' : 'PASS'} ${vp.name.padEnd(14)} p${String(pg).padStart(2)} → [${r.visible}] count=${count} h=${r.pagH} w=${r.numsW} ${problems.join('; ')}`);
		}
	}

	console.log('\n' + (fail === 0 ? 'ALL PASS' : fail + ' FAILURES'));
	await browser.close();
	process.exit(fail === 0 ? 0 : 1);
})();
