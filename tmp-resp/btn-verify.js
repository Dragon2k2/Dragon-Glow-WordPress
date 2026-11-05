#!/usr/bin/env node
/**
 * Verify nút Careers hero (.dg-careers-btn--secondary) giống .dg-oi-btn:
 *   - style (bg, color, border, radius, font, ls, padding, shadow, transition)
 *   - mũi tên "→" neo đúng mép phải TEXT, trượt ra khi hover
 *   - hover: gradient sáng hơn + nâng -2px
 *   - text luôn căn giữa trong nút (trước/sau hover)
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

function srgbToLin(c) { c /= 255; return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); }
function lum(rgb) { return 0.2126 * srgbToLin(rgb[0]) + 0.7152 * srgbToLin(rgb[1]) + 0.0722 * srgbToLin(rgb[2]); }
function ratio(a, b) { const l1 = lum(a), l2 = lum(b); const [hi, lo] = l1 > l2 ? [l1, l2] : [l2, l1]; return (hi + 0.05) / (lo + 0.05); }
function parseRgb(s) { const m = s.match(/rgba?\(([^)]+)\)/); if (!m) return null; const p = m[1].split(',').map(parseFloat); return [p[0], p[1], p[2]]; }

function SNAP(sel) {
  const el = document.querySelector(sel);
  const label = el.querySelector('[class*="__label"]');
  const cs = getComputedStyle(el);
  const ls = getComputedStyle(label);
  const after = getComputedStyle(label, '::after');
  const b = el.getBoundingClientRect();
  const lb = label.getBoundingClientRect();
  return {
    bg: cs.backgroundImage, color: cs.color,
    border: cs.borderTopWidth + ' ' + cs.borderTopStyle + ' ' + cs.borderTopColor,
    radius: cs.borderRadius, padding: cs.padding,
    shadow: cs.boxShadow, font: cs.fontSize + '/' + cs.fontWeight,
    ls: cs.letterSpacing, transform: cs.transform,
    transition: cs.transitionProperty,
    h: Math.round(b.height),
    labelW: Math.round(lb.width),
    textCenter: Math.round((lb.left + lb.right) / 2),
    btnCenter: Math.round((b.left + b.right) / 2),
    arrow: { content: after.content, opacity: after.opacity, transform: after.transform, left: after.left, marginLeft: after.marginLeft, fontSize: after.fontSize },
  };
}

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'verify.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(400);

  const oi = await page.evaluate(SNAP, '#oi');
  const car = await page.evaluate(SNAP, '#car');

  console.log('='.repeat(80));
  console.log('STYLE — so sánh OI vs CAREERS');
  console.log('='.repeat(80));
  const keys = ['bg', 'color', 'border', 'radius', 'padding', 'shadow', 'font', 'ls', 'transition', 'h'];
  let allSame = true;
  for (const k of keys) {
    const same = String(oi[k]) === String(car[k]);
    if (!same) allSame = false;
    console.log(`  ${k.padEnd(11)} ${same ? '✓' : '✗'}`);
    if (!same) console.log(`      oi : ${oi[k]}\n      car: ${car[k]}`);
  }

  console.log('\n' + '='.repeat(80));
  console.log('MŨI TÊN "→" (rest)');
  console.log('='.repeat(80));
  console.log(`  OI     : content=${oi.arrow.content} opacity=${oi.arrow.opacity} transform=${oi.arrow.transform} left=${oi.arrow.left} ml=${oi.arrow.marginLeft} fs=${oi.arrow.fontSize}`);
  console.log(`  CAREERS: content=${car.arrow.content} opacity=${car.arrow.opacity} transform=${car.arrow.transform} left=${car.arrow.left} ml=${car.arrow.marginLeft} fs=${car.arrow.fontSize}`);
  // So sánh thuộc tính TƯƠNG ĐỐI của mũi tên (không so `left` px —
  // giá trị px khác nhau do độ dài text 2 nút khác nhau).
  const arrowSame = oi.arrow.content === car.arrow.content
    && oi.arrow.opacity === car.arrow.opacity
    && oi.arrow.transform === car.arrow.transform
    && oi.arrow.marginLeft === car.arrow.marginLeft
    && oi.arrow.fontSize === car.arrow.fontSize;
  console.log(`  → mũi tên khớp (content/opacity/transform/margin/font-size): ${arrowSame ? '✓' : '✗'}`);
  console.log(`     (left là px tuyệt đối theo mép phải text → khác nhau do text dài khác nhau: oi=${oi.arrow.left} car=${car.arrow.left})`);

  console.log('\n' + '='.repeat(80));
  console.log('CĂN GIỮA TEXT trong nút');
  console.log('='.repeat(80));
  const dOi = Math.abs(oi.textCenter - oi.btnCenter);
  const dCar = Math.abs(car.textCenter - car.btnCenter);
  console.log(`  OI      : lệch ${dOi}px ${dOi <= 1 ? '✓' : '✗'}`);
  console.log(`  CAREERS : lệch ${dCar}px ${dCar <= 1 ? '✓' : '✗'}`);

  // hover
  await page.hover('#car');
  await page.waitForTimeout(600);
  const carHov = await page.evaluate(SNAP, '#car');
  console.log('\n' + '='.repeat(80));
  console.log('HOVER — Careers');
  console.log('='.repeat(80));
  console.log(`  bg        : ${carHov.bg}`);
  console.log(`  shadow    : ${carHov.shadow}`);
  console.log(`  transform : ${carHov.transform}`);
  console.log(`  arrow     : opacity=${carHov.arrow.opacity} transform=${carHov.arrow.transform}`);
  console.log(`  căn giữa  : lệch ${Math.abs(carHov.textCenter - carHov.btnCenter)}px`);

  await page.hover('#oi');
  await page.waitForTimeout(600);
  const oiHov = await page.evaluate(SNAP, '#oi');
  console.log('\n' + '='.repeat(80));
  console.log('HOVER — OI (reference)');
  console.log('='.repeat(80));
  console.log(`  bg        : ${oiHov.bg}`);
  console.log(`  shadow    : ${oiHov.shadow}`);
  console.log(`  transform : ${oiHov.transform}`);
  console.log(`  arrow     : opacity=${oiHov.arrow.opacity} transform=${oiHov.arrow.transform}`);

  const hovSame = oiHov.bg === carHov.bg && oiHov.shadow === carHov.shadow && oiHov.transform === carHov.transform && oiHov.arrow.opacity === carHov.arrow.opacity;

  // contrast
  console.log('\n' + '='.repeat(80));
  console.log('CONTRAST (text 12px → AA 4.5:1)');
  console.log('='.repeat(80));
  for (const [label, st] of [['OI rest', oi], ['OI hover', oiHov], ['CAREER rest', car], ['CAREER hover', carHov]]) {
    const stops = [...st.bg.matchAll(/rgba?\((\d+),\s*(\d+),\s*(\d+)/g)].map((m) => [+m[1], +m[2], +m[3]]);
    const ink = parseRgb(st.color);
    const rs = stops.map((s) => ratio(s, ink));
    const min = Math.min(...rs);
    console.log(`  ${label.padEnd(12)} ${rs.map((r) => r.toFixed(2)).join(' → ')}:1  min ${min.toFixed(2)}:1  ${min >= 4.5 ? 'AA ✓' : 'FAIL ✗'}`);
  }

  await page.screenshot({ path: path.join(OUT, 'final.png') });
  await browser.close();

  console.log('\n' + '='.repeat(80));
  console.log(allSame && arrowSame && dCar <= 1 && hovSame ? '✓ NÚT CAREERS KHỚP .dg-oi-btn' : '✗ CHƯA KHỚP');
  console.log('='.repeat(80));
})();
