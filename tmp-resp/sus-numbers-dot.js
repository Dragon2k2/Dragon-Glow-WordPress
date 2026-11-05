#!/usr/bin/env node
/**
 * Verify .dg-sus-numbers-dot hiển thị ở MOBILE và không đổi ở desktop/tablet.
 * 4 stat + 3 dot xen kẽ trong flex column (mobile) / flex row (desktop).
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/sus');

const LABELS = ['Cruelty Free', 'Vegan Formula', 'Refillable', 'Carbon Neutral'];
const MARKUP = `
<body style="margin:0; background:#fbf9f5;">
  <main data-page="sustainability">
    <section class="dg-sus-numbers">
      <div class="dg-sus-numbers-inner">
        ${LABELS.map((l, i) => `<span class="dg-sus-numbers-item">${l}</span>${
          i < LABELS.length - 1 ? '<span class="dg-sus-numbers-dot" aria-hidden="true"></span>' : ''
        }`).join('\n')}
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
  { name: 'MOBILE  390px', w: 390, h: 900 },
  { name: 'MOBILE  640px', w: 640, h: 900 },
  { name: 'TABLET 768px', w: 768, h: 700 },
  { name: 'DESKTOP 1280px', w: 1280, h: 500 },
];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'sus-dot.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  for (const c of CASES) {
    const page = await browser.newPage({ viewport: { width: c.w, height: c.h } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(250);

    const r = await page.evaluate(() => {
      const inner = document.querySelector('.dg-sus-numbers-inner');
      const dots = [...document.querySelectorAll('.dg-sus-numbers-dot')];
      const items = [...document.querySelectorAll('.dg-sus-numbers-item')];
      const dotData = dots.map((d) => {
        const cs = getComputedStyle(d);
        const r = d.getBoundingClientRect();
        return {
          display: cs.display,
          w: Math.round(r.width),
          h: Math.round(r.height),
          bg: cs.backgroundColor,
          top: Math.round(r.top),
          left: Math.round(r.left),
          visible: cs.display !== 'none' && r.width > 0 && r.height > 0,
        };
      });
      const ir = items.map((i) => i.getBoundingClientRect());
      return {
        flexDir: getComputedStyle(inner).flexDirection,
        count: dots.length,
        dots: dotData,
        items: ir.map((r) => ({ top: Math.round(r.top), left: Math.round(r.left) })),
      };
    });

    const visible = r.dots.filter((d) => d.visible).length;
    const pass = visible === r.count && r.count === 3;
    if (!pass) allPass = false;

    // Dot phải nằm GIỮA 2 item kề (separator) — kiểm theo vị trí
    let separatorOk = true;
    for (let i = 0; i < r.dots.length; i++) {
      const d = r.dots[i], prev = r.items[i], next = r.items[i + 1];
      if (!prev || !next) { separatorOk = false; continue; }
      if (r.flexDir === 'column') {
        // cột: dot phải nằm giữa theo trục y
        if (!(d.top > prev.top && d.top < next.top)) separatorOk = false;
      } else {
        // hàng: dot phải nằm giữa theo trục x
        if (!(d.left > prev.left && d.left < next.left)) separatorOk = false;
      }
    }
    if (!separatorOk) allPass = false;

    console.log('='.repeat(78));
    console.log(`${c.name}   flex-direction: ${r.flexDir}`);
    console.log('='.repeat(78));
    console.log(`  số dot trong DOM : ${r.count}`);
    console.log(`  dot HIỂN THỊ     : ${visible}/${r.count}  ${visible === r.count ? '✓' : '✗'}`);
    r.dots.forEach((d, i) => {
      console.log(`    dot#${i + 1}: display=${d.display} ${d.w}×${d.h} bg=${d.bg} top=${d.top} left=${d.left}`);
    });
    console.log(`  dot nằm GIỮA 2 item kề (separator): ${separatorOk ? '✓' : '✗'}`);

    await page.screenshot({ path: path.join(OUT, `dot-${c.w}.png`) });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(78));
  console.log(allPass ? '✓ TẤT CẢ ĐẠT YÊU CẦU' : '✗ KHÔNG ĐẠT');
  console.log('='.repeat(78));
})();
