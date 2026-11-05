#!/usr/bin/env node
/**
 * Verify CHIỀU RỘNG nút hero 2 trang với markup + layout thật.
 * Nút phải giãn hết content box của cột (fill), y hệt nhau về HÀNH VI.
 * Đo ở 3 breakpoint.
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
        <div class="dg-oi-hero-media"><div style="height:100%;background:#ccc"></div></div>
        <div class="dg-oi-hero-content">
          <p class="dg-oi-hero-accent"></p>
          <h1 class="dg-oi-hero-title">Ingredients</h1>
          <p class="dg-oi-hero-body">We name what we use.<br>We show where it comes from.</p>
          <a href="#traceable" class="dg-oi-btn" id="oi">
            <span class="dg-oi-btn__label">See the sources</span>
          </a>
        </div>
      </div>
    </section>
  </main>
  <hr style="margin:40px 0;border:0;border-top:1px solid #ddd">
  <main data-page="careers">
    <section class="dg-careers-hero">
      <div class="dg-careers-hero-media"><div style="height:100%;background:#ccc"></div></div>
      <div class="dg-careers-hero-text">
        <p class="dg-careers-hero-eyebrow">Careers</p>
        <h1 class="dg-careers-hero-title dg-careers-hero-title--emphasis">Build the Ritual with Us</h1>
        <p class="dg-careers-hero-intro">We make skincare with care.</p>
        <a class="dg-careers-btn dg-careers-btn--secondary" href="#open-roles" id="car">
          <span class="dg-careers-btn__label">See open roles</span>
        </a>
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

function MEASURE(sel) {
  const btn = document.querySelector(sel);
  const content = btn.parentElement;
  const cs = getComputedStyle(content);
  const b = btn.getBoundingClientRect();
  const c = content.getBoundingClientRect();
  const ccs = getComputedStyle(content);
  // content box = width - padding trái - padding phải
  const padL = parseFloat(ccs.paddingLeft), padR = parseFloat(ccs.paddingRight);
  const contentBox = c.width - padL - padR;
  return {
    btnW: Math.round(b.width),
    contentBox: Math.round(contentBox),
    fillRatio: +(b.width / contentBox).toFixed(3),
    contentW: Math.round(c.width),
    padL: Math.round(padL), padR: Math.round(padR),
    contentDisplay: cs.display,
  };
}

const CASES = [
  { name: 'MOBILE  390px', w: 390 },
  { name: 'TABLET 768px', w: 768 },
  { name: 'DESKTOP 1280px', w: 1280 },
];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'width.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  for (const c of CASES) {
    const page = await browser.newPage({ viewport: { width: c.w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);

    const oi = await page.evaluate(MEASURE, '#oi');
    const car = await page.evaluate(MEASURE, '#car');

    console.log('='.repeat(80));
    console.log(c.name);
    console.log('='.repeat(80));
    console.log(`  OI      : cột ${oi.contentW}px (pad ${oi.padL}/${oi.padR}) → content box ${oi.contentBox}px | nút ${oi.btnW}px | fill ${(oi.fillRatio * 100).toFixed(1)}%`);
    console.log(`  CAREERS : cột ${car.contentW}px (pad ${car.padL}/${car.padR}) → content box ${car.contentBox}px | nút ${car.btnW}px | fill ${(car.fillRatio * 100).toFixed(1)}%`);

    const bothFill = oi.fillRatio >= 0.99 && car.fillRatio >= 0.99;
    const sameBehavior = Math.abs(oi.fillRatio - car.fillRatio) < 0.02;
    const pass = bothFill && sameBehavior;
    if (!pass) allPass = false;
    console.log(`  → cả 2 giãn hết cột: ${bothFill ? '✓' : '✗'} | cùng hành vi: ${sameBehavior ? '✓' : '✗'}  ${pass ? '✓ PASS' : '✗ FAIL'}`);

    await page.screenshot({ path: path.join(OUT, `w-${c.w}.png`), fullPage: true });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(80));
  console.log(allPass ? '✓ CHIỀU RỘNG KHỚP Ở MỌI BREAKPOINT' : '✗ KHÔNG KHỚP');
  console.log('='.repeat(80));
})();
