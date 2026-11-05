#!/usr/bin/env node
/**
 * Verify nút "Write to us" (.dg-careers-closing .dg-careers-btn--primary)
 * khớp style hệ gold-pill với .dg-sus-btn (Sustainability closing).
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/careers-btn');

const MARKUP = `
<body style="margin:0; background:#fbf9f5;">
  <main data-page="careers">
    <section class="dg-careers-hero">
      <div class="dg-careers-hero-media"><div style="height:100%;background:#ccc"></div></div>
      <div class="dg-careers-hero-text">
        <h1 class="dg-careers-hero-title">Build the Ritual with Us</h1>
        <a class="dg-careers-btn dg-careers-btn--secondary" href="#open-roles" id="car-hero">
          <span class="dg-careers-btn__label">See open roles</span>
        </a>
      </div>
    </section>

    <section class="dg-careers-closing" id="closing">
      <p class="dg-careers-closing-body">We'd love to hear from you.</p>
      <button type="button" class="dg-careers-btn dg-careers-btn--primary" id="car-close">
        <span class="dg-careers-btn__label">Write to us</span>
      </button>
      <p class="dg-careers-closing-email">careers@dragonglow.com</p>
    </section>
  </main>
  <hr style="margin:40px 0;border:0;border-top:1px solid #ddd">
  <main data-page="sustainability">
    <section class="dg-sus-closing">
      <div class="dg-sus-closing-inner">
        <p class="dg-sus-closing-headline">The earth gives. We give back.</p>
        <a href="https://dragonglow.freehosting.dev/our-story/" class="dg-sus-btn" id="sus-close">
          <span class="dg-sus-btn__label">Discover Our Story</span>
        </a>
      </div>
    </section>
  </main>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'careers.css', 'sustainability.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head>${MARKUP}</html>`;

const PROBE = (sel) => {
  const btn = document.querySelector(sel);
  const cs = getComputedStyle(btn);
  const label = btn.querySelector('[class*="__label"]');
  const after = label ? getComputedStyle(label, '::after') : null;
  const b = btn.getBoundingClientRect();
  const parent = btn.parentElement;
  const pcs = getComputedStyle(parent);
  const pr = parent.getBoundingClientRect();
  const contentBox = pr.width - parseFloat(pcs.paddingLeft) - parseFloat(pcs.paddingRight);
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
    display: cs.display,
    alignSelf: cs.alignSelf,
    width: Math.round(b.width),
    fillRatio: +(b.width / contentBox).toFixed(3),
    contentBox: Math.round(contentBox),
    parentDisplay: pcs.display,
    labelFound: !!label,
    arrowContent: after ? after.content : null,
    arrowOpacity: after ? after.opacity : null,
    arrowTransform: after ? after.transform : null,
    arrowMarginLeft: after ? after.marginLeft : null,
    arrowFontSize: after ? after.fontSize : null,
  };
};

const HOVER = (sel) => {
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
  const htmlPath = path.join(OUT, 'careers-btn.html');
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

    console.log('\n' + '='.repeat(80));
    console.log(`VIEWPORT ${vp.w}px`);
    console.log('='.repeat(80));

    const cc = await page.evaluate(PROBE, '#car-close');
    const sc = await page.evaluate(PROBE, '#sus-close');
    const ch = await page.evaluate(PROBE, '#car-hero');

    console.log('\n[1] Parity — .dg-careers-btn--primary (closing) vs .dg-sus-btn (closing)');
    check('background gradient giống nhau', cc.bg === sc.bg, `car=${cc.bg}\n      sus=${sc.bg}`);
    check('border-radius giống nhau', cc.borderRadius === sc.borderRadius, `car=${cc.borderRadius} sus=${sc.borderRadius}`);
    check('border giống nhau', cc.border === sc.border, `car=${cc.border} sus=${sc.border}`);
    check('màu chữ giống nhau', cc.color === sc.color, `car=${cc.color} sus=${sc.color}`);
    check('font-size giống nhau', cc.fontSize === sc.fontSize, `car=${cc.fontSize} sus=${sc.fontSize}`);
    check('font-weight giống nhau', cc.fontWeight === sc.fontWeight, `car=${cc.fontWeight} sus=${sc.fontWeight}`);
    check('letter-spacing giống nhau', cc.letterSpacing === sc.letterSpacing, `car=${cc.letterSpacing} sus=${sc.letterSpacing}`);
    check('text-transform giống nhau', cc.textTransform === sc.textTransform, `car=${cc.textTransform} sus=${sc.textTransform}`);
    check('padding giống nhau', cc.padding === sc.padding, `car=${cc.padding} sus=${sc.padding}`);
    check('box-shadow giống nhau', cc.boxShadow === sc.boxShadow, `car=${cc.boxShadow}`);
    check('display giống nhau', cc.display === sc.display, `car=${cc.display} sus=${sc.display}`);

    console.log('\n[2] Mũi tên "→"');
    check('có label span', cc.labelFound === true);
    check('arrow content = "→"', cc.arrowContent === '"→"', `car=${cc.arrowContent}`);
    check('arrow opacity mặc định 0', cc.arrowOpacity === '0', `car=${cc.arrowOpacity}`);
    check('arrow margin-left = token', cc.arrowMarginLeft === '8px', `car=${cc.arrowMarginLeft}`);
    check('arrow font-size 14px', cc.arrowFontSize === '14px', `car=${cc.arrowFontSize}`);

    console.log('\n[3] Hover');
    await page.hover('#car-close');
    await page.waitForTimeout(500);
    const ccH = await page.evaluate(HOVER, '#car-close');
    await page.hover('#sus-close');
    await page.waitForTimeout(500);
    const scH = await page.evaluate(HOVER, '#sus-close');

    check('hover đổi gradient', ccH.bg !== cc.bg, ccH.bg);
    check('hover gradient giống sus', ccH.bg === scH.bg);
    check('hover shadow giống sus', ccH.boxShadow === scH.boxShadow);
    check('hover nâng translateY(-2px)', ccH.transform === scH.transform, `car=${ccH.transform} sus=${scH.transform}`);
    check('hover arrow opacity 1', ccH.arrowOpacity === '1', `car=${ccH.arrowOpacity}`);
    check('hover arrow trượt về 0', ccH.transform === ccH.transform && ccH.arrowTransform === scH.arrowTransform, `car=${ccH.arrowTransform}`);

    console.log('\n[4] Hành vi chiều rộng');
    console.log(`      careers closing : ${cc.width}px / content box ${cc.contentBox}px → fill ${(cc.fillRatio * 100).toFixed(1)}% (parent ${cc.parentDisplay})`);
    console.log(`      sus closing     : ${sc.width}px / content box ${sc.contentBox}px → fill ${(sc.fillRatio * 100).toFixed(1)}% (parent ${sc.parentDisplay})`);
    console.log(`      careers hero    : ${ch.width}px / content box ${ch.contentBox}px → fill ${(ch.fillRatio * 100).toFixed(1)}% (align-self ${ch.alignSelf})`);
    // Cả 2 nút closing đều là con của block container (text-align: center)
    // → cùng hành vi ÔM CHỮ (fill < 1), tức là width = text + padding.
    // Không so fill ratio trực tiếp vì độ dài chữ + width parent khác nhau.
    check('nút closing ôm chữ (không giãn hết cột)', cc.fillRatio < 0.99 && sc.fillRatio < 0.99, `car=${cc.fillRatio} sus=${sc.fillRatio}`);
    check('nút closing có cùng padding ngang (=> công thức width giống)', cc.padding === sc.padding, `car=${cc.padding} sus=${sc.padding}`);
    check('nút hero vẫn giãn hết cột', ch.fillRatio >= 0.99, `fill=${ch.fillRatio}`);

    // arrow nằm trong pill
    await page.hover('#car-close');
    await page.waitForTimeout(500);
    const inset = await page.evaluate(() => {
      const btn = document.querySelector('#car-close');
      const label = btn.querySelector('.dg-careers-btn__label');
      const b = btn.getBoundingClientRect();
      const l = label.getBoundingClientRect();
      const cs = getComputedStyle(label, '::after');
      const arrowRight = l.right + parseFloat(cs.marginLeft) + parseFloat(cs.fontSize) * 1.2;
      return { inset: Math.round(b.right - arrowRight), dX: +Math.abs((l.x + l.width / 2) - (b.x + b.width / 2)).toFixed(2), dY: +Math.abs((l.y + l.height / 2) - (b.y + b.height / 2)).toFixed(2) };
    });
    check('arrow nằm trong pill', inset.inset > 0, `inset ${inset.inset}px`);
    check('text căn giữa ngang', inset.dX < 0.6, `Δx=${inset.dX}px`);
    check('text căn giữa dọc', inset.dY < 0.6, `Δy=${inset.dY}px`);

    await page.screenshot({ path: path.join(OUT, `btn-${vp.w}.png`), fullPage: true });
    await page.close();
  }

  // reduced motion
  console.log('\n' + '='.repeat(80));
  console.log('REDUCED MOTION');
  console.log('='.repeat(80));
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(250);
  await page.hover('#car-close');
  await page.waitForTimeout(300);
  const rm = await page.evaluate(HOVER, '#car-close');
  check('transform tắt', rm.transform === 'none', `car=${rm.transform}`);
  check('arrow opacity tắt', rm.arrowOpacity === '0', `car=${rm.arrowOpacity}`);
  await page.close();

  await browser.close();
  console.log('\n' + '='.repeat(80));
  if (allPass) {
    console.log('✓ TOÀN BỘ KIỂM TRA PASS');
  } else {
    console.log('✗ FAIL (' + fail.length + '):');
    fail.forEach((f) => console.log('   - ' + f));
  }
  console.log('='.repeat(80));
  process.exit(allPass ? 0 : 1);
})();
