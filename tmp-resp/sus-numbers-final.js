#!/usr/bin/env node
/**
 * Verify numbers strip với CSS THẬT của theme.
 *   MOBILE  (<768px): 3 stat 1 hàng ngang, 2 dot ngang, không cuộn ngang
 *   >=768px         : 4 stat + 3 dot như cũ — KHÔNG ĐỔI
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/sus');

const LABELS = ['100% Vegan', 'Cruelty-Free Certified', 'Carbon-Neutral Shipping', 'FSC-Certified Paper'];

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
  { name: 'MOBILE  320px', w: 320, mobile: true },
  { name: 'MOBILE  390px', w: 390, mobile: true },
  { name: 'MOBILE  430px', w: 430, mobile: true },
  { name: 'TABLET 768px', w: 768, mobile: false },
  { name: 'DESKTOP 1280px', w: 1280, mobile: false },
];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'sus-final.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  for (const c of CASES) {
    const page = await browser.newPage({ viewport: { width: c.w, height: 600 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(250);

    const r = await page.evaluate(() => {
      const inner = document.querySelector('.dg-sus-numbers-inner');
      const cs = getComputedStyle(inner);
      const items = [...document.querySelectorAll('.dg-sus-numbers-item')];
      const dots = [...document.querySelectorAll('.dg-sus-numbers-dot')];
      const visItems = items.filter((e) => getComputedStyle(e).display !== 'none');
      const visDots = dots.filter((d) => getComputedStyle(d).display !== 'none');
      const ir = visItems.map((e) => { const b = e.getBoundingClientRect(); return { left: Math.round(b.left), right: Math.round(b.right), top: Math.round(b.top), bottom: Math.round(b.bottom), midY: Math.round(b.top + b.height / 2), w: Math.round(b.width), h: Math.round(b.height), fs: getComputedStyle(e).fontSize }; });
      const dr = visDots.map((d) => { const b = d.getBoundingClientRect(); return { left: Math.round(b.left), midY: Math.round(b.top + b.height / 2) }; });
      // tất cả stat cùng hàng? → so tâm dọc (align-items:center khiến top lệch khi nhiều dòng)
      const sameRow = new Set(ir.map((s) => s.midY)).size === 1;
      // mỗi dot nằm ngang giữa 2 stat (cùng hàng, left nằm giữa)?
      const dotBetween = dr.every((d) => {
        const before = ir.filter((s) => s.right <= d.left + 1);
        const after = ir.filter((s) => s.left >= d.left - 1);
        return before.length && after.length && Math.abs(before[0].midY - after[0].midY) <= 1;
      });
      return {
        display: cs.display, cols: cs.gridTemplateColumns, gap: cs.gap,
        itemCount: visItems.length, dotCount: visDots.length,
        items: ir, dots: dr, sameRow, dotBetween,
        overflowX: document.documentElement.scrollWidth > window.innerWidth,
        scrollW: document.documentElement.scrollWidth, winW: window.innerWidth,
      };
    });

    const expectItems = c.mobile ? 3 : 4;
    const expectDots = c.mobile ? 2 : 3;
    const pass =
      r.itemCount === expectItems &&
      r.dotCount === expectDots &&
      r.sameRow &&
      r.dotBetween &&
      !r.overflowX;
    if (!pass) allPass = false;

    console.log('='.repeat(78));
    console.log(`${c.name}`);
    console.log('='.repeat(78));
    console.log(`  display: ${r.display} · cols: ${r.cols} · gap: ${r.gap}`);
    console.log(`  stat hiển thị: ${r.itemCount}/${expectItems} (kỳ vọng ${expectItems}) ${r.itemCount === expectItems ? '✓' : '✗'}`);
    console.log(`  dot hiển thị : ${r.dotCount}/${expectDots} (kỳ vọng ${expectDots}) ${r.dotCount === expectDots ? '✓' : '✗'}`);
    console.log(`  tất cả stat CÙNG HÀNG: ${r.sameRow ? '✓' : '✗'}`);
    console.log(`  dot nằm NGANG giữa 2 stat: ${r.dotBetween ? '✓' : '✗'}`);
    console.log(`  cuộn ngang: ${r.overflowX ? '✗ (' + r.scrollW + 'px)' : 'không ✓'}`);
    console.log(`  stat: ${r.items.map((s) => `${s.fs}@${s.left}`).join(' ')}`);
    console.log(`  → ${pass ? '✓ PASS' : '✗ FAIL'}`);

    await page.screenshot({ path: path.join(OUT, `final-${c.w}.png`) });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(78));
  console.log(allPass ? '✓ TẤT CẢ ĐẠT YÊU CẦU' : '✗ CÓ CASE KHÔNG ĐẠT');
  console.log('='.repeat(78));
})();
