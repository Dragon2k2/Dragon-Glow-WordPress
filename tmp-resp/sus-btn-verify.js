#!/usr/bin/env node
/**
 * Verify hệ nút CTA editorial gold-pill cho 3 trang sau khi gom token
 * về --cta-gold-* (Our Ingredients / Careers / Sustainability).
 *
 * Kiểm tra: màu, bo góc, ink, tracking, arrow (nội dung/độ trượt/inset),
 * hover (nâng + sáng hơn), focus ring, contrast WCAG AA/AAA, reduced-motion.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/sus-btn');

const MARKUP = `
<body style="margin:0; background:#fbf9f5;">
  <main data-page="our-ingredients">
    <section class="dg-oi-hero">
      <div class="dg-oi-hero-inner">
        <div class="dg-oi-hero-media"><div style="height:100%;background:#ccc"></div></div>
        <div class="dg-oi-hero-content">
          <h1 class="dg-oi-hero-title">Ingredients</h1>
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
        <h1 class="dg-careers-hero-title">Build the Ritual with Us</h1>
        <a class="dg-careers-btn dg-careers-btn--secondary" href="#open-roles" id="car">
          <span class="dg-careers-btn__label">See open roles</span>
        </a>
      </div>
    </section>
  </main>
  <hr style="margin:40px 0;border:0;border-top:1px solid #ddd">
  <main data-page="sustainability">
    <section class="dg-sus-hero">
      <div class="dg-sus-hero-inner">
        <div class="dg-sus-hero-image"><div style="height:100%;background:#ccc"></div></div>
        <div class="dg-sus-hero-content">
          <h1 class="dg-sus-hero-title">As radiant as your skin.</h1>
          <a href="#practices" class="dg-sus-btn" id="sus">
            <span class="dg-sus-btn__label">See our practice</span>
          </a>
        </div>
      </div>
    </section>
    <section class="dg-sus-closing">
      <div class="dg-sus-closing-inner">
        <p class="dg-sus-closing-headline">Better for the planet.</p>
        <a href="#practices" class="dg-sus-btn" id="sus-close">
          <span class="dg-sus-btn__label">See our practice</span>
        </a>
      </div>
    </section>
  </main>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'our-ingredients.css', 'careers.css', 'sustainability.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head>${MARKUP}</html>`;

/* ── helpers ───────────────────────────────────────────────── */
function lum(rgb) {
  const [r, g, b] = rgb.map((v) => {
    const c = v / 255;
    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
  });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}
function parseRGB(s) {
  const m = s.match(/rgba?\(([^)]+)\)/);
  if (!m) return null;
  const p = m[1].split(',').map((x) => parseFloat(x));
  return { rgb: [p[0], p[1], p[2]], a: p.length > 3 ? p[3] : 1 };
}
function contrast(fg, bg) {
  const l1 = lum(fg), l2 = lum(bg);
  const [a, b] = l1 > l2 ? [l1, l2] : [l2, l1];
  return +((a + 0.05) / (b + 0.05)).toFixed(2);
}
function hexOf(cssColor) {
  const p = parseRGB(cssColor);
  if (!p) return null;
  return p.rgb.map((v) => Math.round(v).toString(16).padStart(2, '0')).join('');
}

const PROBE = (sel) => {
  const btn = document.querySelector(sel);
  if (!btn) return null;
  const cs = getComputedStyle(btn);
  const label = btn.querySelector('[class*="__label"]');
  const lcs = label ? getComputedStyle(label) : null;
  const after = label ? getComputedStyle(label, '::after') : null;
  const b = btn.getBoundingClientRect();
  const lr = label ? label.getBoundingClientRect() : null;
  const parent = btn.parentElement;
  const pcs = getComputedStyle(parent);
  const pr = parent.getBoundingClientRect();
  const padL = parseFloat(pcs.paddingLeft), padR = parseFloat(pcs.paddingRight);
  const contentBox = pr.width - padL - padR;

  return {
    tag: btn.tagName,
    bg: cs.backgroundImage,
    borderRadius: cs.borderRadius,
    border: cs.border,
    color: cs.color,
    fontSize: cs.fontSize,
    fontWeight: cs.fontWeight,
    letterSpacing: cs.letterSpacing,
    textTransform: cs.textTransform,
    padding: cs.padding,
    boxShadow: cs.boxShadow,
    transform: cs.transform,
    alignSelf: cs.alignSelf,
    display: cs.display,
    width: Math.round(b.width),
    height: Math.round(b.height),
    fillRatio: +(b.width / contentBox).toFixed(3),
    contentBox: Math.round(contentBox),
    parentDisplay: pcs.display,
    labelFound: !!label,
    labelDisplay: lcs ? lcs.display : null,
    labelPos: lcs ? lcs.position : null,
    arrowContent: after ? after.content : null,
    arrowOpacity: after ? after.opacity : null,
    arrowTransform: after ? after.transform : null,
    arrowTranslate: after ? after.translate : null,
    arrowMarginLeft: after ? after.marginLeft : null,
    arrowFontSize: after ? after.fontSize : null,
    arrowInsetRight: null, // set below in JS
    labelRect: lr ? { x: lr.x, right: lr.right, midY: lr.y + lr.height / 2 } : null,
    btnRect: { x: b.x, right: b.right, midY: b.y + b.height / 2 },
    rawBg: cs.backgroundImage,
    bgColors: cs.backgroundImage.match(/rgba?\([^)]+\)/g) || [],
  };
};

const HOVER_PROBE = (sel) => {
  const btn = document.querySelector(sel);
  const cs = getComputedStyle(btn);
  const label = btn.querySelector('[class*="__label"]');
  const after = label ? getComputedStyle(label, '::after') : null;
  return {
    bg: cs.backgroundImage,
    boxShadow: cs.boxShadow,
    transform: cs.transform,
    arrowOpacity: after ? after.opacity : null,
    arrowTransform: after ? after.transform : null,
  };
};

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'sus-btn.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;
  const fail = [];
  const check = (name, ok, detail) => {
    console.log(`  ${ok ? '✓' : '✗'} ${name}${detail ? ' — ' + detail : ''}`);
    if (!ok) { allPass = false; fail.push(name + (detail ? ': ' + detail : '')); }
  };

  for (const vp of [{ w: 390, h: 900 }, { w: 768, h: 900 }, { w: 1280, h: 900 }]) {
    const page = await browser.newPage({ viewport: { width: vp.w, height: vp.h } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(250);

    console.log('\n' + '='.repeat(84));
    console.log(`VIEWPORT ${vp.w}px`);
    console.log('='.repeat(84));

    const oi = await page.evaluate(PROBE, '#oi');
    const car = await page.evaluate(PROBE, '#car');
    const sus = await page.evaluate(PROBE, '#sus');
    const susClose = await page.evaluate(PROBE, '#sus-close');

    // 1) token parity: sus vs oi
    console.log('\n[1] Token parity — .dg-sus-btn vs .dg-oi-btn');
    check('background gradient giống nhau', sus.bg === oi.bg, sus.bg === oi.bg ? '' : `\n      oi : ${oi.bg}\n      sus: ${sus.bg}`);
    check('border-radius giống nhau', sus.borderRadius === oi.borderRadius, `sus=${sus.borderRadius} oi=${oi.borderRadius}`);
    check('màu chữ giống nhau', sus.color === oi.color, `sus=${sus.color} oi=${oi.color}`);
    check('font-size giống nhau', sus.fontSize === oi.fontSize, `sus=${sus.fontSize} oi=${oi.fontSize}`);
    check('font-weight giống nhau', sus.fontWeight === oi.fontWeight, `sus=${sus.fontWeight} oi=${oi.fontWeight}`);
    check('letter-spacing giống nhau', sus.letterSpacing === oi.letterSpacing, `sus=${sus.letterSpacing} oi=${oi.letterSpacing}`);
    check('text-transform giống nhau', sus.textTransform === oi.textTransform, `sus=${sus.textTransform} oi=${oi.textTransform}`);
    check('padding giống nhau', sus.padding === oi.padding, `sus=${sus.padding} oi=${oi.padding}`);
    check('box-shadow giống nhau', sus.boxShadow === oi.boxShadow, `sus=${sus.boxShadow}`);

    // 2) arrow
    console.log('\n[2] Mũi tên "→"');
    check('sus có label span', sus.labelFound === true);
    // NOTE: label là flex-item của nút `inline-flex` → bị blockify, computed
    // `display` luôn là `block` (kể cả khi khai inline-block). Vì vậy ta so
    // PARITY với .dg-oi-btn chứ không assert giá trị cụ thể.
    check('sus label display giống oi', sus.labelDisplay === oi.labelDisplay, `sus=${sus.labelDisplay} oi=${oi.labelDisplay}`);
    check('sus label position relative', sus.labelPos === 'relative', `sus=${sus.labelPos}`);
    check('arrow content = "→"', sus.arrowContent === '"→"', `sus=${sus.arrowContent}`);
    check('arrow opacity mặc định 0', sus.arrowOpacity === '0', `sus=${sus.arrowOpacity}`);
    check('arrow translate căn dọc', sus.arrowTranslate === '0px -50%' || sus.arrowTranslate === 'none -50%', `sus=${sus.arrowTranslate}`);
    check('arrow margin-left = token', sus.arrowMarginLeft === '8px', `sus=${sus.arrowMarginLeft}`);
    check('arrow font-size 14px', sus.arrowFontSize === '14px', `sus=${sus.arrowFontSize}`);
    check('arrow opacity giống oi', sus.arrowOpacity === oi.arrowOpacity);

    // 3) hover
    console.log('\n[3] Hover');
    await page.hover('#sus');
    await page.waitForTimeout(500);
    const susH = await page.evaluate(HOVER_PROBE, '#sus');
    await page.hover('#oi');
    await page.waitForTimeout(500);
    const oiH = await page.evaluate(HOVER_PROBE, '#oi');

    check('hover đổi gradient (sáng hơn)', susH.bg !== sus.bg, susH.bg);
    check('hover gradient giống oi', susH.bg === oiH.bg);
    check('hover shadow giống oi', susH.boxShadow === oiH.boxShadow);
    check('hover nâng nút translateY(-2px)', susH.transform === oiH.transform, `sus=${susH.transform}`);
    check('hover arrow opacity 1', susH.arrowOpacity === '1', `sus=${susH.arrowOpacity}`);
    check('hover arrow trượt về 0', susH.arrowTransform === oiH.arrowTransform, `sus=${susH.arrowTransform} oi=${oiH.arrowTransform}`);

    // 4) width fill (hero column stretch)
    console.log('\n[4] Chiều rộng (hero)');
    console.log(`      oi  fill ${(oi.fillRatio * 100).toFixed(1)}% (${oi.width}/${oi.contentBox}px, parent ${oi.parentDisplay})`);
    console.log(`      sus fill ${(sus.fillRatio * 100).toFixed(1)}% (${sus.width}/${sus.contentBox}px, parent ${sus.parentDisplay})`);
    console.log(`      sus-closing fill ${(susClose.fillRatio * 100).toFixed(1)}% (${susClose.width}/${susClose.contentBox}px, parent ${susClose.parentDisplay})`);
    check('sus hero giãn hết cột như oi', Math.abs(sus.fillRatio - oi.fillRatio) < 0.02, `sus=${sus.fillRatio} oi=${oi.fillRatio}`);

    // 5) arrow nằm trong pill
    console.log('\n[5] Mũi tên nằm trong pill');
    await page.hover('#sus');
    await page.waitForTimeout(500);
    const insets = await page.evaluate(() => {
      const btn = document.querySelector('#sus');
      const label = btn.querySelector('.dg-sus-btn__label');
      const b = btn.getBoundingClientRect();
      const l = label.getBoundingClientRect();
      const cs = getComputedStyle(label, '::after');
      const arrowW = parseFloat(cs.fontSize) * 1.2;
      const arrowRight = l.right + parseFloat(cs.marginLeft) + arrowW;
      return { arrowRight: Math.round(arrowRight), btnRight: Math.round(b.right), inset: Math.round(b.right - arrowRight) };
    });
    check('arrow nằm trong nút (> 0 inset)', insets.inset > 0, `inset ${insets.inset}px`);

    // 5b) text luôn căn giữa trong nút (mũi tên nằm ngoài flow nên không
    //     đẩy lệch text)
    const centering = await page.evaluate(() => {
      const btn = document.querySelector('#sus');
      const label = btn.querySelector('.dg-sus-btn__label');
      const b = btn.getBoundingClientRect();
      const l = label.getBoundingClientRect();
      return {
        dY: +Math.abs((l.y + l.height / 2) - (b.y + b.height / 2)).toFixed(2),
        dX: +Math.abs((l.x + l.width / 2) - (b.x + b.width / 2)).toFixed(2),
      };
    });
    check('text căn giữa dọc trong nút (hover)', centering.dY < 0.6, `Δy=${centering.dY}px`);
    check('text căn giữa ngang trong nút (hover)', centering.dX < 0.6, `Δx=${centering.dX}px`);

    // 6) contrast — chữ TRẮNG trên gradient gold là quyết định thiết kế
    //    có chủ đích (theo yêu cầu chủ design) → đo & BÁO CÁO, không assert
    //    fail. Ngưỡng AA cho text 12px = 4.5:1, AAA = 7:1.
    console.log('\n[6] Contrast WCAG (ink trên gradient) — chữ trắng theo thiết kế');
    const cProbe = await page.evaluate(() => {
      const btn = document.querySelector('#sus');
      const cs = getComputedStyle(btn);
      return { color: cs.color, bg: cs.backgroundImage };
    });
    const ink = parseRGB(cProbe.color).rgb;
    const grads = cProbe.bg.match(/rgba?\([^)]+\)/g).map(parseRGB).filter((g) => g && g.a > 0);
    let minC = Infinity;
    grads.forEach((g) => { minC = Math.min(minC, contrast(ink, g.rgb)); });
    const AA = minC >= 4.5, AAA = minC >= 7;
    console.log(`      ink = ${hexOf(cProbe.color)} (${cProbe.color}) | nền = ${grads.map((g) => hexOf(`rgb(${g.rgb.join(',')})`)).join(' → ')}`);
    console.log(`      contrast min = ${minC}:1  →  AA(4.5:1) ${AA ? '✓' : '✗'} | AAA(7:1) ${AAA ? '✓' : '✗'}`);
    if (!AA) {
      console.log('      ⚠ Dưới WCAG AA — thiết kế chủ đích theo yêu cầu chủ design.');
      console.log('        Để đạt AA cần nền tối hơn: gold-700 → gold-800 (4.85–6.99:1).');
    }
    check('màu chữ là trắng', cProbe.color === 'rgb(255, 255, 255)', cProbe.color);
    check('3 nút cùng màu chữ', sus.color === oi.color && oi.color === car.color, `sus=${sus.color} oi=${oi.color} car=${car.color}`);

    await page.screenshot({ path: path.join(OUT, `sus-${vp.w}.png`), fullPage: true });
    await page.close();
  }

  // 7) reduced motion
  console.log('\n' + '='.repeat(84));
  console.log('REDUCED MOTION');
  console.log('='.repeat(84));
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(250);
  await page.hover('#sus');
  await page.waitForTimeout(300);
  const rm = await page.evaluate(HOVER_PROBE, '#sus');
  check('transform bị tắt', rm.transform === 'none', `sus=${rm.transform}`);
  check('arrow opacity tắt (0)', rm.arrowOpacity === '0', `sus=${rm.arrowOpacity}`);
  await page.close();

  await browser.close();
  console.log('\n' + '='.repeat(84));
  if (allPass) {
    console.log('✓ TOÀN BỘ KIỂM TRA PASS');
  } else {
    console.log('✗ FAIL (' + fail.length + '):');
    fail.forEach((f) => console.log('   - ' + f));
  }
  console.log('='.repeat(84));
  process.exit(allPass ? 0 : 1);
})();
