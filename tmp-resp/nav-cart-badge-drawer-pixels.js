#!/usr/bin/env node
/**
 * Precise pixel verification of the drawer "Cart" badge.
 *
 * Crops exactly the badge rect (from getBoundingClientRect) and checks:
 *   - the pill fill is the theme primary (#735c00)
 *   - the digit inside is white
 *   - the pill is fully inside the anchor row (not clipped)
 */
const path = require('path');
const sharp = require('sharp');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');
const PRIMARY = { r: 0x73, g: 0x5c, b: 0x00 };
const near = (a, b, tol = 30) =>
  Math.abs(a.r - b.r) <= tol && Math.abs(a.g - b.g) <= tol && Math.abs(a.b - b.b) <= tol;

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 390, height: 700 }, deviceScaleFactor: 4 });
  await page.goto('file:///' + path.join(OUT, 'drawer.html').replace(/\\/g, '/'));
  await page.waitForTimeout(1600);

  const geo = await page.evaluate(() => {
    const link = document.querySelector('.dg-mobile-menu-item--cart a');
    const badge = link.querySelector('.dg-cart-count');
    const lr = link.getBoundingClientRect();
    const br = badge.getBoundingClientRect();
    return {
      badge: { x: br.x, y: br.y, w: br.width, h: br.height },
      link: { x: lr.x, y: lr.y, w: lr.width, h: lr.height },
    };
  });

  const file = path.join(OUT, 'drawer-badge-crop.png');
  await page.screenshot({
    path: file,
    clip: { x: geo.badge.x - 4, y: geo.badge.y - 4, width: geo.badge.w + 8, height: geo.badge.h + 8 },
  });

  const { data, info } = await sharp(file).raw().toBuffer({ resolveWithObject: true });
  let primary = 0, white = 0;
  for (let y = 0; y < info.height; y++) {
    for (let x = 0; x < info.width; x++) {
      const i = (y * info.width + x) * info.channels;
      const px = { r: data[i], g: data[i + 1], b: data[i + 2] };
      if (near(px, PRIMARY)) primary++;
      else if (near(px, { r: 255, g: 255, b: 255 })) white++;
    }
  }

  console.log(`badge rect (CSS): x=${geo.badge.x.toFixed(1)} y=${geo.badge.y.toFixed(1)} ${geo.badge.w}x${geo.badge.h}`);
  console.log(`link rect  (CSS): x=${geo.link.x.toFixed(1)} w=${geo.link.w}`);
  console.log(`crop ${info.width}x${info.height} @4x`);
  console.log(`primary px: ${primary}, white px: ${white}`);

  const inside = geo.badge.x >= geo.link.x && (geo.badge.x + geo.badge.w) <= (geo.link.x + geo.link.w);
  const rightGap = geo.link.x + geo.link.w - (geo.badge.x + geo.badge.w);
  console.log(`→ badge nằm trong row: ${inside ? '✓' : '✗'}`);
  console.log(`→ cách mép phải row: ${rightGap.toFixed(1)}px ${rightGap >= 0 && rightGap <= 25 ? '✓ (sát mép, theo padding)' : '✗'}`);
  console.log(`→ fill primary + chữ trắng: ${primary > 200 && white > 20 ? '✓' : '✗ (primary=' + primary + ' white=' + white + ')'}`);

  await browser.close();
})();
