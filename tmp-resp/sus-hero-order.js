#!/usr/bin/env node
/**
 * Verify hero order của trang Sustainability ở 3 breakpoint.
 * Mục tiêu: MOBILE (<768px) ảnh ở TRÊN, nội dung ở DƯỚI.
 *           >=768px giữ nguyên: ảnh cột trái, nội dung cột phải.
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

const BREAKPOINTS = [
  { name: 'MOBILE  390px', w: 390, h: 844, expect: 'image-first' },
  { name: 'TABLET 768px', w: 768, h: 1024, expect: 'image-left' },
  { name: 'DESKTOP 1280px', w: 1280, h: 900, expect: 'image-left' },
];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'sus.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  for (const bp of BREAKPOINTS) {
    const page = await browser.newPage({ viewport: { width: bp.w, height: bp.h } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);

    const r = await page.evaluate(() => {
      const img = document.querySelector('.dg-sus-hero-image');
      const con = document.querySelector('.dg-sus-hero-content');
      const ir = img.getBoundingClientRect();
      const cr = con.getBoundingClientRect();
      const inner = getComputedStyle(document.querySelector('.dg-sus-hero-inner'));
      return {
        imgTop: Math.round(ir.top + window.scrollY),
        imgLeft: Math.round(ir.left),
        imgW: Math.round(ir.width),
        imgH: Math.round(ir.height),
        conTop: Math.round(cr.top + window.scrollY),
        conLeft: Math.round(cr.left),
        conW: Math.round(cr.width),
        imgOrder: getComputedStyle(img).order,
        conOrder: getComputedStyle(con).order,
        cols: inner.gridTemplateColumns,
      };
    });

    const stacked = r.imgTop < r.conTop;
    const sideBySide = r.conLeft > r.imgLeft + r.imgW - 2;
    let verdict, pass;
    if (bp.expect === 'image-first') {
      pass = stacked;
      verdict = stacked ? 'ảnh TRÊN, nội dung DƯỚI ✓' : 'ảnh chưa lên trên ✗';
    } else {
      pass = sideBySide;
      verdict = sideBySide ? 'ảnh CỘT TRÁI, nội dung CỘT PHẢI ✓' : 'không còn 2 cột ✗';
    }
    if (!pass) allPass = false;

    console.log('='.repeat(78));
    console.log(`${bp.name}  (${bp.w}×${bp.h})`);
    console.log('='.repeat(78));
    console.log(`  grid-template-columns : ${r.cols}`);
    console.log(`  IMAGE    top=${r.imgTop} left=${r.imgLeft} ${r.imgW}×${r.imgH}  order=${r.imgOrder}`);
    console.log(`  CONTENT  top=${r.conTop} left=${r.conLeft} ${r.conW}px      order=${r.conOrder}`);
    console.log(`  → ${verdict}`);

    await page.screenshot({ path: path.join(OUT, `hero-${bp.w}.png`), fullPage: false });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(78));
  console.log(allPass ? '✓ TẤT CẢ BREAKPOINT ĐẠT YÊU CẦU' : '✗ CÓ BREAKPOINT KHÔNG ĐẠT');
  console.log('='.repeat(78));
})();
