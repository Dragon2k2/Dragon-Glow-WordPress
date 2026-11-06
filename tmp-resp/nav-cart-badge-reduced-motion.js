#!/usr/bin/env node
/** Verify the badge animation is disabled under prefers-reduced-motion. */
const path = require('path');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

(async () => {
  const browser = await chromium.launch();

  for (const reduced of [false, true]) {
    const page = await browser.newPage({
      viewport: { width: 390, height: 300 },
      reducedMotion: reduced ? 'reduce' : 'no-preference',
    });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(1400);

    const r = await page.evaluate(async () => {
      const badge = document.querySelector('#dg-mobile-menu-toggle .dg-cart-count');
      window.DGCartBadge.paint(5);
      await new Promise((res) => setTimeout(res, 80));
      return {
        hasClass: badge.classList.contains('is-pulse'),
        animationName: getComputedStyle(badge).animationName,
        animationDuration: getComputedStyle(badge).animationDuration,
      };
    });

    console.log(`reduced-motion=${reduced ? 'ON ' : 'OFF'} → class is-pulse=${r.hasClass}, animation=${r.animationName} (${r.animationDuration})`);
    if (reduced) {
      console.log(`  → ${r.animationName === 'none' ? '✓ animation bị tắt' : '✗ animation VẪN chạy'}`);
    } else {
      console.log(`  → ${r.animationName.includes('dgCartBadge') ? '✓ animation chạy' : '✗ animation không chạy'}`);
    }
    await page.close();
  }
  await browser.close();
})();
