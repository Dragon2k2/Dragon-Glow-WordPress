#!/usr/bin/env node
/**
 * Verify hover của .dg-oi-btn:
 *  - đọc computed background gradient trước / sau hover
 *  - tính contrast thực tế của text với 2 đầu gradient
 *  - chụp screenshot để mắt kiểm
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/oi');

const MARKUP = `
<body style="background:#fbf9f5; padding:60px;">
  <div data-page="our-ingredients">
    <a href="#traceable" class="dg-oi-btn" id="btn">
      <span class="dg-oi-btn__label">See the sources</span>
    </a>
  </div>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'our-ingredients.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head>${MARKUP}</html>`;

function srgbToLin(c) { c /= 255; return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); }
function lum(rgb) {
  return 0.2126 * srgbToLin(rgb[0]) + 0.7152 * srgbToLin(rgb[1]) + 0.0722 * srgbToLin(rgb[2]);
}
function ratio(a, b) {
  const l1 = lum(a), l2 = lum(b);
  const [hi, lo] = l1 > l2 ? [l1, l2] : [l2, l1];
  return (hi + 0.05) / (lo + 0.05);
}
function parseRgb(s) {
  const m = s.match(/rgba?\(([^)]+)\)/);
  if (!m) return null;
  const p = m[1].split(',').map((x) => parseFloat(x));
  return [p[0], p[1], p[2]];
}

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'oi.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 600 } });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(400);

  const read = async () => page.evaluate(() => {
    const el = document.querySelector('#btn');
    const label = el.querySelector('.dg-oi-btn__label');
    const cs = getComputedStyle(el);
    const ls = getComputedStyle(label);
    return {
      background: cs.backgroundImage,
      boxShadow: cs.boxShadow,
      transform: cs.transform,
      color: cs.color,
      fontSize: cs.fontSize,
      fontWeight: cs.fontWeight,
      labelText: label.textContent.trim(),
      arrowOpacity: getComputedStyle(label, '::after').opacity,
      arrowTransform: getComputedStyle(label, '::after').transform,
      borderColor: cs.borderTopColor,
    };
  });

  const base = await read();
  await page.screenshot({ path: path.join(OUT, '01-rest.png'), clip: { x: 40, y: 40, width: 340, height: 110 } });

  await page.hover('#btn');
  await page.waitForTimeout(600);
  const hov = await read();
  await page.screenshot({ path: path.join(OUT, '02-hover.png'), clip: { x: 40, y: 40, width: 340, height: 110 } });

  // Tính contrast từ gradient thực tế
  const extract = (bg) => [...bg.matchAll(/rgba?\((\d+),\s*(\d+),\s*(\d+)/g)].map((m) => [+m[1], +m[2], +m[3]]);
  const ink = parseRgb(base.color);

  console.log('='.repeat(78));
  console.log('TRẠNG THÁI REST');
  console.log('='.repeat(78));
  console.log(`  background : ${base.background}`);
  console.log(`  color      : ${base.color}`);
  console.log(`  font       : ${base.fontSize} / ${base.fontWeight}`);
  console.log(`  border     : ${base.borderColor}`);
  console.log(`  arrow      : opacity ${base.arrowOpacity}, transform ${base.arrowTransform}`);
  console.log(`  transform  : ${base.transform}`);

  console.log('\n' + '='.repeat(78));
  console.log('TRẠNG THÁI HOVER');
  console.log('='.repeat(78));
  console.log(`  background : ${hov.background}`);
  console.log(`  box-shadow : ${hov.boxShadow}`);
  console.log(`  transform  : ${hov.transform}`);
  console.log(`  arrow      : opacity ${hov.arrowOpacity}, transform ${hov.arrowTransform}`);

  console.log('\n' + '='.repeat(78));
  console.log('CONTRAST THỰC TẾ (text 12px bold → cần AA 4.5:1, AAA 7:1)');
  console.log('='.repeat(78));
  for (const [label, st] of [['REST ', base], ['HOVER', hov]]) {
    const stops = extract(st.background);
    if (!stops.length) { console.log(`  ${label}: không đọc được gradient`); continue; }
    const ratios = stops.map((s) => ratio(s, ink));
    const min = Math.min(...ratios);
    const tag = min >= 7 ? 'AAA ✓' : min >= 4.5 ? 'AA ✓' : 'FAIL ✗';
    console.log(`  ${label}: ${stops.map((s) => `rgb(${s.join(',')})`).join(' → ')}`);
    console.log(`         contrast ${ratios.map((r) => r.toFixed(2)).join(' → ')}:1   min ${min.toFixed(2)}:1  ${tag}`);
  }

  // So sánh độ sáng gradient hover vs rest
  const bs = extract(base.background), hs = extract(hov.background);
  if (bs.length && hs.length) {
    const avg = (a) => a.reduce((s, c) => s + lum(c), 0) / a.length;
    const d = (avg(hs) - avg(bs)) * 100;
    console.log('\n' + '='.repeat(78));
    console.log('HƯỚNG LUMINANCE');
    console.log('='.repeat(78));
    console.log(`  ΔL hover - rest = ${d >= 0 ? '+' : ''}${d.toFixed(2)}  →  ${d > 0 ? 'SÁNG HƠN ✓' : 'tối hơn ✗'}`);
  }

  await browser.close();
  console.log(`\nScreenshot: ${path.join(OUT, '01-rest.png')} | ${path.join(OUT, '02-hover.png')}`);
})();
