#!/usr/bin/env node
/**
 * Kiểm tra mũi tên "→" có nằm BÊN TRONG pill (không lọt ra ngoài mép phải nút)
 * ở trạng thái rest VÀ hover, ở mọi breakpoint.
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
    <section class="dg-oi-hero"><div class="dg-oi-hero-inner">
      <div class="dg-oi-hero-media"><div style="height:100%;background:#ccc"></div></div>
      <div class="dg-oi-hero-content">
        <a href="#traceable" class="dg-oi-btn" id="oi"><span class="dg-oi-btn__label">See the sources</span></a>
      </div>
    </div></section>
  </main>
  <main data-page="careers">
    <section class="dg-careers-hero"><div class="dg-careers-hero-media"><div style="height:100%;background:#ccc"></div></div>
      <div class="dg-careers-hero-text">
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

function PROBE(sel) {
  const btn = document.querySelector(sel);
  const label = btn.querySelector('[class*="__label"]');
  const b = btn.getBoundingClientRect();
  const lb = label.getBoundingClientRect();
  const after = getComputedStyle(label, '::after');
  // vị trí mũi tên = left:100% của label + margin-left
  const arrowLeft = lb.right + parseFloat(after.marginLeft || 0);
  const arrowW = parseFloat(after.fontSize) * 0.6; // xấp xỉ bề rộng glyph "→"
  const arrowRight = arrowLeft + arrowW;
  return {
    btnLeft: Math.round(b.left), btnRight: Math.round(b.right),
    labelLeft: Math.round(lb.left), labelRight: Math.round(lb.right),
    arrowLeft: Math.round(arrowLeft), arrowRight: Math.round(arrowLeft + arrowW),
    inside: arrowRight <= b.right,
    gapToBtnEdge: Math.round(b.right - (arrowLeft + arrowW)),
    opacity: after.opacity,
  };
}

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'arrow.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  for (const w of [390, 768, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);

    console.log('='.repeat(80));
    console.log(`WIDTH ${w}px`);
    console.log('='.repeat(80));

    for (const [name, sel] of [['OI     ', '#oi'], ['CAREERS', '#car']]) {
      const rest = await page.evaluate(PROBE, sel);
      await page.hover(sel);
      await page.waitForTimeout(500);
      const hov = await page.evaluate(PROBE, sel);
      await page.mouse.move(0, 0);
      await page.waitForTimeout(300);

      console.log(`  ${name} REST  : nút [${rest.btnLeft}→${rest.btnRight}] text [${rest.labelLeft}→${rest.labelRight}] mũi tên [${rest.arrowLeft}→${rest.arrowRight}]`);
      console.log(`              arrow trong pill: ${rest.inside ? '✓' : '✗'} (còn ${rest.gapToBtnEdge}px tới mép phải nút)`);
      console.log(`  ${name} HOVER : mũi tên opacity=${hov.opacity} trong pill: ${hov.inside ? '✓' : '✗'} (còn ${hov.gapToBtnEdge}px)`);
      if (!rest.inside || !hov.inside) allPass = false;
    }
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(80));
  console.log(allPass ? '✓ MŨI TÊN LUÔN NẰM TRONG PILL' : '✗ MŨI TÊN LỌT RA NGOÀI');
  console.log('='.repeat(80));
})();
