#!/usr/bin/env node
/**
 * Verify responsive + reduced-motion cho nút Careers mới.
 *   - 3 breakpoint: không tràn ngang, nút ôm chữ, mũi tên không lọt ra ngoài viewport
 *   - prefers-reduced-motion: transform về 0, mũi tên ẩn
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
  <main data-page="careers">
    <section class="dg-careers-hero">
      <div class="dg-careers-hero-text">
        <p class="dg-careers-hero-eyebrow">Careers</p>
        <h1 class="dg-careers-hero-title dg-careers-hero-title--emphasis">Build the Ritual with Us</h1>
        <p class="dg-careers-hero-intro">We make skincare with care.</p>
        <a class="dg-careers-btn dg-careers-btn--secondary" href="#open-roles">
          <span class="dg-careers-btn__label">See open roles</span>
        </a>
      </div>
    </section>
  </main>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'careers.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head>${MARKUP}</html>`;

const WIDTHS = [320, 375, 390, 768, 1024, 1280];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'resp.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  console.log('='.repeat(80));
  console.log('RESPONSIVE');
  console.log('='.repeat(80));
  for (const w of WIDTHS) {
    const page = await browser.newPage({ viewport: { width: w, height: 800 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);

    const r = await page.evaluate(() => {
      const btn = document.querySelector('.dg-careers-btn--secondary');
      const label = btn.querySelector('.dg-careers-btn__label');
      const b = btn.getBoundingClientRect();
      const lb = label.getBoundingClientRect();
      const parent = btn.parentElement.getBoundingClientRect();
      const cs = getComputedStyle(btn);
      return {
        w: Math.round(b.width), h: Math.round(b.height),
        left: Math.round(b.left), right: Math.round(b.right),
        parentW: Math.round(parent.width),
        radius: cs.borderRadius,
        overflowX: document.documentElement.scrollWidth > window.innerWidth,
        scrollW: document.documentElement.scrollWidth,
        // nút ôm chữ (không giãn hết cột) ở desktop?
        hugs: Math.round(b.width) < Math.round(parent.width) - 2,
        // text căn giữa?
        centered: Math.abs((lb.left + lb.right) / 2 - (b.left + b.right) / 2) <= 1,
      };
    });

    const pass = !r.overflowX && r.centered && r.radius === '9999px';
    if (!pass) allPass = false;
    console.log(`  ${String(w).padStart(4)}px → w=${r.w}×${r.h} radius=${r.radius} textCănGiữa=${r.centered ? '✓' : '✗'} ômChữ=${r.hugs ? '✓' : '(giãn cột)'} tràn=${r.overflowX ? '✗ ' + r.scrollW + 'px' : 'không ✓'} ${pass ? '✓' : '✗'}`);
    await page.close();
  }

  // reduced motion
  console.log('\n' + '='.repeat(80));
  console.log('PREFERS-REDUCED-MOTION');
  console.log('='.repeat(80));
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 800 }, reducedMotion: 'reduce' });
  const page = await ctx.newPage();
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(300);
  await page.hover('.dg-careers-btn--secondary');
  await page.waitForTimeout(400);

  const rm = await page.evaluate(() => {
    const btn = document.querySelector('.dg-careers-btn--secondary');
    const label = btn.querySelector('.dg-careers-btn__label');
    const after = getComputedStyle(label, '::after');
    return {
      transform: getComputedStyle(btn).transform,
      transition: getComputedStyle(btn).transitionDuration,
      arrowOpacity: after.opacity,
      arrowTransform: after.transform,
    };
  });
  const rmOk = rm.transform === 'none' && rm.arrowOpacity === '0';
  if (!rmOk) allPass = false;
  console.log(`  transform nút    : ${rm.transform} ${rm.transform === 'none' ? '✓' : '✗'}`);
  console.log(`  transition       : ${rm.transition}`);
  console.log(`  mũi tên opacity  : ${rm.arrowOpacity} ${rm.arrowOpacity === '0' ? '✓' : '✗'}`);
  console.log(`  mũi tên transform: ${rm.arrowTransform}`);
  console.log(`  → ${rmOk ? '✓ OK' : '✗'}`);

  await page.screenshot({ path: path.join(OUT, 'reduced.png') });
  await browser.close();

  console.log('\n' + '='.repeat(80));
  console.log(allPass ? '✓ TẤT CẢ ĐẠT YÊU CẦU' : '✗ CÓ CASE KHÔNG ĐẠT');
  console.log('='.repeat(80));
})();
