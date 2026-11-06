#!/usr/bin/env node
/**
 * Verify the badge never clips the menu glyph.
 *
 * The self-hosted Material Symbols woff2 is not committed to the repo, so
 * the real glyph does not render in the static harness. We substitute an
 * equivalent glyph (☰, 24px, same box as the real icon) so the overlap
 * between the badge and the icon can be measured for real.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

(async () => {
  const browser = await chromium.launch();

  for (const w of [320, 390, 768, 1023]) {
    const page = await browser.newPage({ viewport: { width: w, height: 300 }, deviceScaleFactor: 3 });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(1400);

    const r = await page.evaluate(() => {
      const toggle = document.getElementById('dg-mobile-menu-toggle');
      const glyph = toggle.querySelector('.material-symbols-outlined');
      const badge = toggle.querySelector('.dg-cart-count');

      // Swap the missing webfont for a real glyph of the same metrics.
      glyph.textContent = '☰';
      glyph.style.fontFamily = 'serif';
      glyph.style.fontSize = '24px';
      glyph.style.lineHeight = '24px';
      glyph.style.display = 'inline-block';
      glyph.style.width = '24px';
      glyph.style.textAlign = 'center';

      const g = glyph.getBoundingClientRect();
      const b = badge.getBoundingClientRect();
      const t = toggle.getBoundingClientRect();

      const overlapX = Math.min(g.right, b.right) - Math.max(g.left, b.left);
      const overlapY = Math.min(g.bottom, b.bottom) - Math.max(g.top, b.top);
      const overlaps = overlapX > 0 && overlapY > 0;

      return {
        glyph: { l: Math.round(g.left), r: Math.round(g.right), t: Math.round(g.top), b: Math.round(g.bottom) },
        badge: { l: Math.round(b.left), r: Math.round(b.right), t: Math.round(b.top), b: Math.round(b.bottom) },
        toggleRight: Math.round(t.right),
        viewportW: window.innerWidth,
        overlapX: Math.round(overlapX),
        overlapY: Math.round(overlapY),
        overlaps,
        badgeInsideViewport: b.right <= window.innerWidth,
      };
    });

    console.log(`\n--- ${w}px ---`);
    console.log(`  glyph  x ${r.glyph.l}..${r.glyph.r}  y ${r.glyph.t}..${r.glyph.b}`);
    console.log(`  badge  x ${r.badge.l}..${r.badge.r}  y ${r.badge.t}..${r.badge.b}`);
    console.log(`  overlap: ${r.overlapX}x${r.overlapY}px → ${r.overlaps ? '✗ ĐÈ LÊN GLYPH' : '✓ không đè'}`);
    console.log(`  badge trong viewport: ${r.badgeInsideViewport ? '✓' : '✗ tràn ra ngoài (right=' + r.badge.r + ' > ' + r.viewportW + ')'}`);

    await page.screenshot({ path: path.join(OUT, `glyph-${w}.png`), clip: { x: r.badge.l - 30, y: r.glyph.t - 20, width: 90, height: 70 } });
    await page.close();
  }
  await browser.close();
})();
