#!/usr/bin/env node
/**
 * Đo nút .dg-oi-btn (hero Our Ingredients) làm REFERENCE:
 * width, padding, radius, font, letter-spacing, mũi tên.
 * So với nút Careers hero hiện tại để biết cần sửa gì.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/btn');

const MARKUP = `
<body style="margin:0; background:#fbf9f5;">
  <main data-page="our-ingredients">
    <section class="dg-oi-hero">
      <div class="dg-oi-hero-inner">
        <div class="dg-oi-hero-content">
          <p class="dg-oi-hero-accent"></p>
          <h1 class="dg-oi-hero-title">Ingredients</h1>
          <p class="dg-oi-hero-body">We name what we use.</p>
          <a href="#traceable" class="dg-oi-btn" id="oi">
            <span class="dg-oi-btn__label">See the sources</span>
          </a>
        </div>
      </div>
    </section>
  </main>
  <main data-page="careers">
    <section class="dg-careers-hero">
      <div class="dg-careers-hero-text">
        <a class="dg-careers-btn dg-careers-btn--secondary" href="#open-roles" id="car">See open roles</a>
      </div>
    </section>
  </main>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'our-ingredients.css', 'careers.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head>${MARKUP}</html>`;

function READ(sel) {
  const el = document.querySelector(sel);
  const cs = getComputedStyle(el);
  const b = el.getBoundingClientRect();
  const parent = el.parentElement.getBoundingClientRect();
  return {
    w: Math.round(b.width), h: Math.round(b.height),
    parentW: Math.round(parent.width),
    display: cs.display, alignSelf: cs.alignSelf,
    padding: cs.padding, radius: cs.borderRadius,
    border: cs.borderTopWidth + ' ' + cs.borderTopStyle + ' ' + cs.borderTopColor,
    bg: cs.backgroundImage, color: cs.color,
    shadow: cs.boxShadow,
    font: cs.fontSize + '/' + cs.fontWeight, ls: cs.letterSpacing,
    transform: cs.transform, transition: cs.transitionProperty,
  };
}

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'measure.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(400);

  const oi = await page.evaluate(READ, '#oi');
  const car = await page.evaluate(READ, '#car');
  console.log('='.repeat(80));
  console.log('REFERENCE: .dg-oi-btn (Our Ingredients hero)');
  console.log('='.repeat(80));
  for (const [k, v] of Object.entries(oi)) console.log(`  ${k.padEnd(12)}: ${v}`);

  console.log('\n' + '='.repeat(80));
  console.log('CURRENT: .dg-careers-btn--secondary (Careers hero)');
  console.log('='.repeat(80));
  for (const [k, v] of Object.entries(car)) console.log(`  ${k.padEnd(12)}: ${v}`);

  console.log('\n' + '='.repeat(80));
  console.log('KHOẢNG CÁCH CẦN SỬA');
  console.log('='.repeat(80));
  const diff = Object.keys(oi).filter((k) => String(oi[k]) !== String(car[k]));
  diff.forEach((k) => console.log(`  ${k.padEnd(12)}: OI="${oi[k]}"  →  CAREERS="${car[k]}"`));

  await browser.close();
})();
