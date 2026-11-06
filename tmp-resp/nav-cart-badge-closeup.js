#!/usr/bin/env node
/** Close-up screenshot (region-based) of the menu toggle + badge. */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

(async () => {
  const browser = await chromium.launch();
  for (const w of [320, 390, 768, 1023]) {
    const page = await browser.newPage({ viewport: { width: w, height: 400 }, deviceScaleFactor: 3 });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(1500);
    const box = await page.evaluate(() => {
      const b = document.getElementById('dg-mobile-menu-toggle').getBoundingClientRect();
      return { x: b.x - 24, y: b.y - 24, width: b.width + 48, height: b.height + 48 };
    });
    await page.screenshot({ path: path.join(OUT, `toggle-region-${w}.png`), clip: box });
    await page.evaluate(() => window.DGCartBadge.paint(7));
    await page.waitForTimeout(150);
    await page.screenshot({ path: path.join(OUT, `toggle-region-${w}-pulse.png`), clip: box });
    await page.close();
  }
  await browser.close();
  console.log('done');
})();
