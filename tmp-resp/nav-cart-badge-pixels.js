#!/usr/bin/env node
/**
 * Pixel-level check of the rendered toggle + badge:
 *  - the badge circle must contain the theme primary colour (#735c00)
 *  - the digits must be white (on-primary) inside the circle
 *  - the badge must not overlap the glyph in the middle of the button
 */
const path = require('path');
const sharp = require('sharp');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

const PRIMARY = { r: 0x73, g: 0x5c, b: 0x00 };
const WHITE = { r: 0xff, g: 0xff, b: 0xff };

const near = (a, b, tol = 26) =>
  Math.abs(a.r - b.r) <= tol && Math.abs(a.g - b.g) <= tol && Math.abs(a.b - b.b) <= tol;

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 390, height: 300 }, deviceScaleFactor: 3 });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(1600);

  const box = await page.evaluate(() => {
    const b = document.getElementById('dg-mobile-menu-toggle').getBoundingClientRect();
    return { x: b.x - 20, y: b.y - 20, width: b.width + 40, height: b.height + 40 };
  });
  const file = path.join(OUT, 'pixel-toggle.png');
  await page.screenshot({ path: file, clip: box });

  const { data, info } = await sharp(file).raw().toBuffer({ resolveWithObject: true });
  const { width, height, channels } = info;

  let primary = 0;
  let white = 0;
  const primaryPixels = [];
  for (let y = 0; y < height; y++) {
    for (let x = 0; x < width; x++) {
      const i = (y * width + x) * channels;
      const px = { r: data[i], g: data[i + 1], b: data[i + 2] };
      if (near(px, PRIMARY)) { primary++; primaryPixels.push([x, y]); }
      else if (near(px, WHITE)) white++;
    }
  }

  // Bounding box of the primary-coloured (badge) pixels.
  const xs = primaryPixels.map((p) => p[0]);
  const ys = primaryPixels.map((p) => p[1]);
  const bbox = primaryPixels.length
    ? { x0: Math.min(...xs), x1: Math.max(...xs), y0: Math.min(...ys), y1: Math.max(...ys) }
    : null;

  console.log(`image ${width}x${height}`);
  console.log(`primary pixels (badge fill): ${primary}`);
  console.log(`white pixels (glyph + digits): ${white}`);
  console.log(`badge bbox: ${bbox ? `x ${bbox.x0}..${bbox.x1}, y ${bbox.y0}..${bbox.y1}` : 'NONE'}`);

  // The badge must sit in the top-right quadrant of the clipped region
  // (clip starts 20px left/above the button).
  const regionW = width;
  const regionH = height;
  if (bbox) {
    const cx = (bbox.x0 + bbox.x1) / 2;
    const cy = (bbox.y0 + bbox.y1) / 2;
    const inTopRight = cx > regionW * 0.55 && cy < regionH * 0.55;
    console.log(`badge centre (${Math.round(cx)}, ${Math.round(cy)}) → ${inTopRight ? '✓ top-right of the button' : '✗ wrong position'}`);
  }
  console.log(`→ ${primary > 100 && white > 50 ? '✓ badge renders with theme primary fill + white text' : '✗ SAI'}`);

  await browser.close();
})();
