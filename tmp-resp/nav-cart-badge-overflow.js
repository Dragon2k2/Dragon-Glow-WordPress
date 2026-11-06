#!/usr/bin/env node
/** Check that a 2- and 3-digit count does not clip inside the badge pill. */
const path = require('path');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 390, height: 300 }, deviceScaleFactor: 3 });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(1500);

  for (const n of [1, 9, 10, 99, 100, 1234]) {
    const r = await page.evaluate((count) => {
      window.DGCartBadge.paint(count);
      const badge = document.querySelector('#dg-mobile-menu-toggle .dg-cart-count');
      const b = badge.getBoundingClientRect();
      // The digit text must fit inside the pill's content box.
      const range = document.createRange();
      range.selectNodeContents(badge);
      const text = range.getBoundingClientRect();
      return {
        w: Math.round(b.width),
        h: Math.round(b.height),
        textW: Math.round(text.width),
        textRight: Math.round(text.right),
        pillRight: Math.round(b.right),
        overflows: text.right > b.right - 1 || text.width > b.width - 4,
      };
    }, n);
    console.log(`count=${String(n).padStart(4)} → pill ${r.w}x${r.h}px, text ${r.textW}px, overflow=${r.overflows ? '✗ CẮT' : '✓ vừa'}`);
  }

  await browser.close();
})();
