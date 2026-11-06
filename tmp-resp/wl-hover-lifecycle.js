#!/usr/bin/env node
/**
 * Verify vòng đời trạng thái chip:
 *  1. Chip A active → hover A: không đổi style ✓
 *  2. JS chuyển `is-active` sang chip B
 *     → hover A (giờ inactive): PHẢI có hover style ✓
 *     → hover B (giờ active): KHÔNG đổi style ✓
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();

  for (const w of [390, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(400);

    const snap = (sel) => page.evaluate((s) => {
      const cs = getComputedStyle(document.querySelector(s));
      const b = getComputedStyle(document.querySelector(s + ' .dg-wishlist-toolbar__count'));
      return { bg: cs.backgroundColor, color: cs.color, badgeBg: b.backgroundColor };
    }, sel);

    console.log(`\n=== ${w}px ===`);

    // 1. Chip A active, hover A
    await page.hover('.dg-wishlist-toolbar__seg.is-active');
    await page.waitForTimeout(300);
    const aHoverActive = await snap('.dg-wishlist-toolbar__seg.is-active');
    console.log(`1) hover chip ACTIVE  → bg=${aHoverActive.bg} color=${aHoverActive.color}`);
    console.log(`   → ${aHoverActive.bg === 'rgb(115, 92, 0)' ? '✓ giữ nền vàng active' : '✗ mất nền active'}`);

    // 2. Chuyển is-active sang chip thứ 2 (mô phỏng JS)
    await page.evaluate(() => {
      const segs = document.querySelectorAll('.dg-wishlist-toolbar__seg');
      segs[0].classList.remove('is-active');
      segs[1].classList.add('is-active');
      segs[0].setAttribute('aria-selected', 'false');
      segs[1].setAttribute('aria-selected', 'true');
    });
    await page.waitForTimeout(100);

    // 2a. Hover chip A (giờ inactive) → phải có hover
    await page.hover('.dg-wishlist-toolbar__seg:not(.is-active)');
    await page.waitForTimeout(300);
    const aHoverInactive = await snap('.dg-wishlist-toolbar__seg:not(.is-active)');
    console.log(`2) hover chip ĐÃ TỪNG active (giờ inactive) → bg=${aHoverInactive.bg} color=${aHoverInactive.color}`);
    console.log(`   → ${aHoverInactive.bg !== 'rgba(0, 0, 0, 0)' ? '✓ có hover style trở lại' : '✗ mất hover'}`);

    // 2b. Hover chip B (giờ active) → không đổi
    await page.hover('.dg-wishlist-toolbar__seg.is-active');
    await page.waitForTimeout(300);
    const bHover = await snap('.dg-wishlist-toolbar__seg.is-active');
    console.log(`3) hover chip MỚI active → bg=${bHover.bg} color=${bHover.color}`);
    console.log(`   → ${bHover.bg === 'rgb(115, 92, 0)' ? '✓ giữ nền vàng active' : '✗ mất nền active'}`);

    // 3. aria-selected đồng bộ
    const aria = await page.evaluate(() => Array.from(document.querySelectorAll('.dg-wishlist-toolbar__seg')).map((s) => s.getAttribute('aria-selected')));
    console.log(`4) aria-selected sau khi chuyển: ${JSON.stringify(aria)}`);

    await page.close();
  }
  await browser.close();
})();
