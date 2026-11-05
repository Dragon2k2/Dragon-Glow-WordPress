#!/usr/bin/env node
/**
 * Verify padding-top của .dg-sus-hero-inner ở 3 breakpoint.
 *   MOBILE  (<768px) → --sus-hero-pad-top-mobile (32px) — GIẢM
 *   >=768px         → calc(--sus-section-gap * 0.75) (90px) — KHÔNG ĐỔI
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/sus');

const MARKUP = `
<body style="margin:0; background:#fbf9f5;">
  <header style="height:72px; background:#241a00; color:#fff; display:flex; align-items:center; padding:0 24px; font:600 12px/1 sans-serif; letter-spacing:.12em;">HEADER</header>
  <main data-page="sustainability">
    <section class="dg-sus-hero">
      <div class="dg-sus-hero-inner">
        <div class="dg-sus-hero-image">
          <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='600' height='800'><rect width='600' height='800' fill='%234a6b3a'/><text x='300' y='400' font-size='40' fill='white' text-anchor='middle'>HERO IMAGE</text></svg>"
               alt="hero" loading="eager" fetchpriority="high" decoding="async">
        </div>
        <div class="dg-sus-hero-content">
          <p class="dg-sus-hero-badge">Sustainability</p>
          <h1 class="dg-sus-hero-headline">As radiant as your skin.</h1>
          <p class="dg-sus-hero-body">We take only what the earth renews.</p>
          <a href="#practices" class="dg-sus-btn">See our practice</a>
        </div>
      </div>
    </section>
  </main>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'sustainability.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head>${MARKUP}</html>`;

const CASES = [
  { name: 'MOBILE  390px', w: 390, h: 844, expect: 32 },
  { name: 'MOBILE  640px', w: 640, h: 900, expect: 32 },
  { name: 'TABLET 768px', w: 768, h: 1024, expect: 90 },
  { name: 'DESKTOP 1280px', w: 1280, h: 900, expect: 90 },
];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'sus-pad.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  for (const c of CASES) {
    const page = await browser.newPage({ viewport: { width: c.w, height: c.h } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(250);

    const r = await page.evaluate(() => {
      const inner = document.querySelector('.dg-sus-hero-inner');
      const cs = getComputedStyle(inner);
      const img = document.querySelector('.dg-sus-hero-image').getBoundingClientRect();
      return {
        padTop: cs.paddingTop,
        padBottom: cs.paddingBottom,
        padLeft: cs.paddingLeft,
        imgTop: Math.round(img.top),
      };
    });

    const pt = parseFloat(r.padTop);
    const pass = Math.abs(pt - c.expect) < 0.5;
    if (!pass) allPass = false;

    console.log('='.repeat(78));
    console.log(`${c.name}`);
    console.log('='.repeat(78));
    console.log(`  padding-top    : ${r.padTop}  (kỳ vọng ${c.expect}px) ${pass ? '✓' : '✗'}`);
    console.log(`  padding-bottom : ${r.padBottom}`);
    console.log(`  padding-left   : ${r.padLeft}`);
    console.log(`  ảnh bắt đầu từ y = ${r.imgTop}px (header cao 72px)`);

    await page.screenshot({ path: path.join(OUT, `pad-${c.w}.png`) });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(78));
  console.log(allPass ? '✓ TẤT CẢ ĐẠT YÊU CẦU' : '✗ KHÔNG ĐẠT');
  console.log('='.repeat(78));
})();
