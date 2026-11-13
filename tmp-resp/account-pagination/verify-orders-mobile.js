/**
 * Kiểm chứng card đơn hàng ở mobile + hồi quy tablet/desktop.
 * Chạy: node tmp-resp/account-pagination/verify-orders-mobile.js
 */
const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file:///' + path.join(__dirname, 'orders-mobile-mock.html').replace(/\\/g, '/');

const VIEWPORTS = [
	{ w: 320, mobile: true },
	{ w: 375, mobile: true },
	{ w: 390, mobile: true },
	{ w: 414, mobile: true },
	{ w: 480, mobile: true },
	{ w: 600, mobile: true },
	{ w: 639, mobile: true },
	{ w: 640, mobile: true },
	{ w: 641, mobile: false },
	{ w: 768, mobile: false },
	{ w: 1024, mobile: false },
	{ w: 1280, mobile: false },
	{ w: 1440, mobile: false },
];

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage();
	let fail = 0;

	for (const vp of VIEWPORTS) {
		await page.setViewportSize({ width: vp.w, height: 900 });
		await page.goto(FILE, { waitUntil: 'load' });
		await page.waitForTimeout(200);

		const r = await page.evaluate(() => {
			const table = document.querySelector('.woocommerce-orders-table');
			const row = table.querySelector('tbody tr');
			const cells = [...row.children];
			const rects = cells.map((c) => c.getBoundingClientRect());

			// Mobile: mọi cell cùng x, cùng chiều rộng, xếp dọc.
			const stacked = rects.every((b) => Math.abs(b.x - rects[0].x) <= 1);
			const sameW = rects.every((b) => Math.abs(b.width - rects[0].width) <= 1);
			const vertical = rects.every((b, i) => i === 0 || b.y >= rects[i - 1].y + rects[i - 1].height - 1);

			const order = cells[0];
			const orderCs = getComputedStyle(order);
			const dateCs = getComputedStyle(cells[1]);
			const actions = cells[cells.length - 1];
			const btn = actions.querySelector('.woocommerce-button');

			return {
				thead: getComputedStyle(table.querySelector('thead')).display,
				tableDisplay: getComputedStyle(table).display,
				thDisplay: orderCs.display,
				tdDisplay: getComputedStyle(cells[1]).display,
				thBefore: getComputedStyle(order, '::before').content,
				tdBefore: getComputedStyle(cells[1], '::before').content,
				dateFontWeight: dateCs.fontWeight,
				dateColor: dateCs.color,
				orderFontWeight: orderCs.fontWeight,
				stacked, sameW, vertical,
				rowH: Math.round(row.getBoundingClientRect().height),
				btnW: btn ? Math.round(btn.getBoundingClientRect().width) : 0,
				btnH: btn ? Math.round(btn.getBoundingClientRect().height) : 0,
				docOverflow: Math.round(document.documentElement.scrollWidth - document.documentElement.clientWidth),
				tableW: Math.round(table.getBoundingClientRect().width),
			};
		});

		const isMobile = vp.w <= 640;
		const problems = [];

		if (r.docOverflow > 0) problems.push('tràn ngang ' + r.docOverflow);
		if (isMobile) {
			// thead được ẩn bằng kỹ thuật visually-hidden nên vẫn còn trong
			// cây trợ năng (display khác "none").
			if (r.thead === 'none') problems.push('thead bị xoá khỏi cây trợ năng');
			if (r.thDisplay !== 'flex') problems.push('th chưa flex: ' + r.thDisplay);
			if (r.tdDisplay !== 'flex') problems.push('td chưa flex');
			if (r.thBefore !== 'none') problems.push('th còn label');
			if (r.tdBefore === 'none' || r.tdBefore === 'normal') problems.push('td mất label');
			if (!r.stacked) problems.push('cell lệch ngang');
			if (!r.sameW) problems.push('cell lệch rộng');
			if (!r.vertical) problems.push('cell không xếp dọc');
			if (r.dateFontWeight === '600') problems.push('ô Date bị nhấn mạnh như cột đầu');
			if (r.btnH < 44) problems.push('nút View ' + r.btnH + 'px < 44px');
		} else {
			// Tablet (641-768px) giữ nguyên hành vi trước khi sửa:
			// thead ẩn, `<th>` còn table-cell, `<td>` đã là flex.
			// Desktop (>=1024px) giữ nguyên bảng đầy đủ.
			if (vp.w <= 768) {
				if (r.thead !== 'none') problems.push('tablet: thead bị ẩn');
				if (r.thDisplay !== 'table-cell') problems.push('tablet: th đổi layout: ' + r.thDisplay);
				if (r.tdDisplay !== 'flex') problems.push('tablet: td đổi layout: ' + r.tdDisplay);
				if (r.rowH !== 309) problems.push('tablet: rowH ' + r.rowH + ' ≠ 309');
			} else {
				if (r.thead === 'none') problems.push('desktop: thead bị ẩn');
				if (r.thDisplay !== 'table-cell') problems.push('desktop: th đổi layout: ' + r.thDisplay);
				if (r.tdDisplay !== 'table-cell') problems.push('desktop: td đổi layout: ' + r.tdDisplay);
			}
		}

		if (problems.length) fail++;
		console.log(`${problems.length ? 'FAIL' : 'PASS'} ${String(vp.w).padStart(4)}px | thead=${r.thead.padEnd(6)} th=${r.thDisplay.padEnd(11)} td=${r.tdDisplay.padEnd(11)} before(th)=${r.thBefore.padEnd(8)} before(td)=${String(r.tdBefore).padEnd(11)} stacked=${r.stacked ? 'y' : 'n'} v=${r.vertical ? 'y' : 'n'} btn=${r.btnW}×${r.btnH} rowH=${r.rowH} ovf=${r.docOverflow} ${problems.join('; ')}`);
	}

	console.log('\n' + (fail === 0 ? 'ALL PASS' : fail + ' FAILURES'));
	await browser.close();
	process.exit(fail === 0 ? 0 : 1);
})();
