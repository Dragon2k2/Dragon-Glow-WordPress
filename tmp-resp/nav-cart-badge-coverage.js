#!/usr/bin/env node
/**
 * Pixel analysis of the badge over a real ☰ glyph.
 *
 * Question: how much of the hamburger's TOP BAR is covered by the badge?
 * The Material Symbols woff2 is not committed to the repo, so we swap in
 * "☰" with the same 24px metrics and measure the covered area directly.
 */
const path = require('path');
const sharp = require('sharp');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

const PRIMARY = { r: 0x73, g: 0x5c, b: 0x00 };

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 390, height: 300 }, deviceScaleFactor: 4 });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(1400);

  // Hide the badge → capture the pure glyph.
  await page.evaluate(() => {
    const g = document.querySelector('#dg-mobile-menu-toggle .material-symbols-outlined');
    g.textContent = '☰';
    g.style.fontFamily = 'serif';
    g.style.fontSize = '24px';
    g.style.lineHeight = '24px';
    g.style.display = 'inline-block';
    g.style.width = '24px';
    g.style.textAlign = 'center';
    g.style.color = '#735c00';
    document.querySelector('#dg-mobile-menu-toggle .dg-cart-count').style.visibility = 'hidden';
  });
  await page.waitForTimeout(300);

  const box = await page.evaluate(() => {
    const g = document.querySelector('#dg-mobile-menu-toggle .material-symbols-outlined');
    const r = g.getBoundingClientRect();
    return { x: r.x, y: r.y, width: r.width, height: r.height };
  });
  const glyphFile = path.join(OUT, 'glyph-only.png');
  await page.screenshot({ path: glyphFile, clip: box });

  // Show the badge again → capture the composited result.
  await page.evaluate(() => {
    document.querySelector('#dg-mobile-menu-toggle .dg-cart-count').style.visibility = 'visible';
  });
  await page.waitForTimeout(300);
  const compFile = path.join(OUT, 'glyph-badge.png');
  await page.screenshot({ path: compFile, clip: box });

  const read = async (f) => {
    const { data, info } = await sharp(f).raw().toBuffer({ resolveWithObject: true });
    return { data, w: info.width, h: info.height, c: info.channels };
  };

  const g1 = await read(glyphFile);
  const g2 = await read(compFile);

  let glyphPx = 0;
  let coveredPx = 0;
  let topBarPx = 0;
  let topBarCovered = 0;
  // The ☰ glyph's top bar occupies roughly the top third of its 24px box.
  const topBarLimit = Math.round(g1.h * 0.38);

  for (let y = 0; y < g1.h; y++) {
    for (let x = 0; x < g1.w; x++) {
      const i = (y * g1.w + x) * g1.c;
      const a = { r: g1.data[i], g: g1.data[i + 1], b: g1.data[i + 2] };
      const b = { r: g2.data[i], g: g2.data[i + 1], b: g2.data[i + 2] };
      const isGlyph = Math.abs(a.r - PRIMARY.r) < 40 && Math.abs(a.g - PRIMARY.g) < 40 && Math.abs(a.b - PRIMARY.b) < 40;
      // The badge is also primary — but it is a filled circle, so use the
      // badge's own mask instead. Simpler: a glyph pixel is "covered" when
      // the composited pixel is NOT the badge's ring colour.
      const stillGlyph = Math.abs(b.r - PRIMARY.r) < 40 && Math.abs(b.g - PRIMARY.g) < 40 && Math.abs(b.b - PRIMARY.b) < 40;
      if (isGlyph) {
        glyphPx++;
        if (!stillGlyph) coveredPx++;
      }
      if (y < topBarLimit) {
        // Only count glyph pixels in the top bar region.
        if (isGlyph) { topBarPx++; if (!stillGlyph) topBarCovered++; }
      }
    }
  }

  console.log(`glyph pixels: ${glyphPx}`);
  console.log(`covered by badge: ${coveredPx} (${((coveredPx / glyphPx) * 100).toFixed(1)}%)`);
  console.log(`top bar pixels: ${topBarPx}, covered: ${topBarCovered} (${topBarPx ? ((topBarCovered / topBarPx) * 100).toFixed(1) : '0'}%)`);
  console.log(`→ ${coveredPx / glyphPx > 0.12 ? '⚠ badge che hơn 12% glyph — cần chỉnh offset' : '✓ badge che ít glyph — chấp nhận được'}`);

  await browser.close();
})();
