/**
 * Verification — shared image lightbox (single product page)
 */
const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/?v=' + Date.now();

(async () => {
	const browser = await chromium.launch({ channel: 'chrome' });
	const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });

	const errors = [];
	page.on('pageerror', (e) => errors.push('PAGEERROR: ' + e.message));
	page.on('console', (m) => { if (m.type() === 'error') errors.push('CONSOLE: ' + m.text()); });

	await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });

	const out = {};

	out.lib = await page.evaluate(() => typeof window.DGZoomLightbox);
	out.modalExists = await page.evaluate(() => !!document.querySelector('[data-dg-zoom-root]'));
	out.mainImgAttrs = await page.evaluate(() => {
		const el = document.getElementById('dg-main-image');
		if (!el) return null;
		return {
			role: el.getAttribute('role'),
			tabindex: el.getAttribute('tabindex'),
			trigger: el.hasAttribute('data-dg-zoom-trigger'),
			label: el.getAttribute('aria-label'),
			cursor: getComputedStyle(el).cursor
		};
	});
	out.zoomBtnTrigger = await page.evaluate(() => {
		const el = document.querySelector('.dg-zoom-btn');
		return el ? { trigger: el.hasAttribute('data-dg-zoom-trigger'), onclick: el.getAttribute('onclick') } : null;
	});
	out.inlineHandlers = await page.evaluate(() => {
		const all = document.querySelectorAll('[onclick]');
		return Array.from(all).map((e) => e.getAttribute('onclick')).filter((v) => /zoom/i.test(v));
	});
	out.thumbCount = await page.evaluate(() => document.querySelectorAll('[data-dg-thumb]').length);

	// ── Click main image → lightbox opens ──
	await page.click('#dg-main-image');
	await page.waitForTimeout(500);
	out.afterMainClick = await page.evaluate(() => {
		const m = document.querySelector('[data-dg-zoom-root]');
		const img = m.querySelector('[data-dg-zoom-image]');
		return {
			isOpen: m.classList.contains('is-open'),
			ariaHidden: m.getAttribute('aria-hidden'),
			display: getComputedStyle(m).display,
			imgSrc: img.getAttribute('src'),
			counter: m.querySelector('[data-dg-zoom-counter]').textContent.trim(),
			prevHidden: m.querySelector('[data-dg-zoom-prev]').hidden,
			bodyOverflow: document.body.style.overflow,
			activeEl: document.activeElement.className
		};
	});
	await page.screenshot({ path: 'tmp-resp/zoom-lightbox/shot-main-click.png' });

	// ── Zoom button click → opens ──
	await page.keyboard.press('Escape');
	await page.waitForTimeout(300);
	await page.click('.dg-zoom-btn');
	await page.waitForTimeout(400);
	out.afterBtnClick = await page.evaluate(() => {
		const m = document.querySelector('[data-dg-zoom-root]');
		return { isOpen: m.classList.contains('is-open'), counter: m.querySelector('[data-dg-zoom-counter]').textContent.trim() };
	});

	// ── Arrow navigation ──
	await page.keyboard.press('ArrowRight');
	await page.waitForTimeout(300);
	out.afterNext = await page.evaluate(() => {
		const m = document.querySelector('[data-dg-zoom-root]');
		return {
			counter: m.querySelector('[data-dg-zoom-counter]').textContent.trim(),
			imgSrc: m.querySelector('[data-dg-zoom-image]').getAttribute('src')
		};
	});

	// ── Escape closes + focus restore ──
	await page.keyboard.press('Escape');
	await page.waitForTimeout(300);
	out.afterEscape = await page.evaluate(() => {
		const m = document.querySelector('[data-dg-zoom-root]');
		return {
			isOpen: m.classList.contains('is-open'),
			ariaHidden: m.getAttribute('aria-hidden'),
			bodyOverflow: document.body.style.overflow,
			activeId: document.activeElement.id
		};
	});

	// ── Thumbnail swap then open → index must match current image ──
	await page.click('[data-dg-thumb]:nth-of-type(1)').catch(() => {});
	await page.evaluate(() => {
		const t = document.querySelectorAll('[data-dg-thumb]');
		if (t.length > 2) t[2].click();
	});
	await page.waitForTimeout(600);
	out.thumbSwap = await page.evaluate(() => {
		const main = document.getElementById('dg-main-image');
		return { src: main.getAttribute('src'), active: document.querySelectorAll('[data-dg-thumb].is-active').length };
	});
	await page.click('#dg-main-image');
	await page.waitForTimeout(400);
	out.openAfterSwap = await page.evaluate(() => {
		const m = document.querySelector('[data-dg-zoom-root]');
		return {
			counter: m.querySelector('[data-dg-zoom-counter]').textContent.trim(),
			imgSrc: m.querySelector('[data-dg-zoom-image]').getAttribute('src'),
			mainSrc: document.getElementById('dg-main-image').getAttribute('src')
		};
	});

	// ── Keyboard activation on main image (Enter) ──
	await page.keyboard.press('Escape');
	await page.waitForTimeout(250);
	await page.evaluate(() => document.getElementById('dg-main-image').focus());
	await page.keyboard.press('Enter');
	await page.waitForTimeout(350);
	out.keyboardOpen = await page.evaluate(() => document.querySelector('[data-dg-zoom-root]').classList.contains('is-open'));
	await page.keyboard.press('Escape');

	out.errors = errors;
	console.log(JSON.stringify(out, null, 2));
	await browser.close();
})();
