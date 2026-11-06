#!/usr/bin/env node
/**
 * Check whether the `hidden` class actually hides the badge.
 *
 * Tailwind's `.hidden` (display:none) is (0,1,0); the theme rule
 * `.dg-nav-toggle .dg-cart-count { display: inline-flex }` is (0,2,0) —
 * so the theme rule WINS and the badge would stay visible at count 0.
 */
const path = require('path');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 390, height: 300 } });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(1500);

  const r = await page.evaluate(async () => {
    const badge = document.querySelector('#dg-mobile-menu-toggle .dg-cart-count');
    const read = () => {
      const cs = getComputedStyle(badge);
      const rect = badge.getBoundingClientRect();
      return {
        hasHiddenClass: badge.classList.contains('hidden'),
        display: cs.display,
        visibility: cs.visibility,
        w: Math.round(rect.width),
        h: Math.round(rect.height),
      };
    };
    const before = read();
    window.DGCartBadge.paint(0);
    await new Promise((res) => setTimeout(res, 80));
    const after = read();
    window.DGCartBadge.paint(5);
    await new Promise((res) => setTimeout(res, 80));
    const shown = read();
    return { before, after, shown };
  });

  console.log('count=3 :', JSON.stringify(r.before));
  console.log('count=0 :', JSON.stringify(r.after), r.after.w > 0 ? '✗ VẪN HIỆN — hidden bị đè' : '✓ ẩn đúng');
  console.log('count=5 :', JSON.stringify(r.shown));

  await browser.close();
})();
