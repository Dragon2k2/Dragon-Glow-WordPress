#!/usr/bin/env node
/**
 * Thử các phương án layout ngang cho .dg-sus-numbers ở mobile.
 * DOM: [i1][d1][i2][d2][i3][d3][i4]  (4 stat + 3 dot separator)
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/sus');

const LABELS = ['100% Vegan', 'Cruelty-Free Certified', 'Carbon-Neutral Shipping', 'FSC-Certified Paper'];

// 4 phương án: base (column) + 3 biến thể grid ngang
const VARIANTS = {
  'A — grid 4 cột (item|dot|item|dot)': `
    .dg-sus-numbers-inner { display: grid; grid-template-columns: minmax(0,1fr) auto minmax(0,1fr) auto; gap: 1.25rem 0.75rem; align-items: center; justify-items: center; }
    .dg-sus-numbers-item { font-size: 13px; text-align: center; }
  `,
  'B — grid 3 cột (item|dot|item) + ẩn dot thứ 2': `
    .dg-sus-numbers-inner { display: grid; grid-template-columns: minmax(0,1fr) auto minmax(0,1fr); gap: 1.25rem 0.75rem; align-items: center; justify-items: center; }
    .dg-sus-numbers-item { font-size: 13px; text-align: center; }
    .dg-sus-numbers-dot:nth-of-type(4) { display: none; }
  `,
  'C — grid 4 cột, font 12px': `
    .dg-sus-numbers-inner { display: grid; grid-template-columns: minmax(0,1fr) auto minmax(0,1fr) auto; gap: 1.25rem 0.75rem; align-items: center; justify-items: center; }
    .dg-sus-numbers-item { font-size: 12px; text-align: center; }
  `,
};

const baseCSS = `
.dg-sus-numbers { background: #1b1c1a; padding: 4rem 0; }
.dg-sus-numbers-inner { max-width: 1440px; margin: 0 auto; padding: 0 24px; display: flex; flex-direction: column; gap: 1.5rem; align-items: center; text-align: center; }
.dg-sus-numbers-item { font-size: 16px; font-weight: 600; line-height: 1.0; letter-spacing: 0.05em; text-transform: uppercase; color: #fff; }
.dg-sus-numbers-dot { display: block; width: 4px; height: 4px; border-radius: 50%; background: rgba(255,255,255,0.3); }
`;

const MARKUP = `
<body style="margin:0;">
  <section class="dg-sus-numbers">
    <div class="dg-sus-numbers-inner">
      ${LABELS.map((l, i) => `<span class="dg-sus-numbers-item">${l}</span>${
        i < LABELS.length - 1 ? '<span class="dg-sus-numbers-dot" aria-hidden="true"></span>' : ''
      }`).join('\n')}
    </div>
  </section>
</body>`;

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch();

  for (const [name, css] of Object.entries(VARIANTS)) {
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${baseCSS}${css}</style></head>${MARKUP}</html>`;
    const file = path.join(OUT, 'v.html');
    fs.writeFileSync(file, html, 'utf8');

    console.log('\n' + '='.repeat(78));
    console.log(name);
    console.log('='.repeat(78));

    for (const w of [360, 390, 430]) {
      const page = await browser.newPage({ viewport: { width: w, height: 700 } });
      await page.goto('file:///' + file.replace(/\\/g, '/'));
      await page.waitForTimeout(200);

      const r = await page.evaluate(() => {
        const items = [...document.querySelectorAll('.dg-sus-numbers-item')];
        const dots = [...document.querySelectorAll('.dg-sus-numbers-dot')];
        const vis = dots.filter((d) => getComputedStyle(d).display !== 'none');
        const ir = items.map((el) => {
          const b = el.getBoundingClientRect();
          return { top: Math.round(b.top), left: Math.round(b.left), w: Math.round(b.width), h: Math.round(b.height), lines: Math.round(b.height / parseFloat(getComputedStyle(el).lineHeight)) };
        });
        const dr = vis.map((el) => { const b = el.getBoundingClientRect(); return { top: Math.round(b.top), left: Math.round(b.left) }; });
        // dot có nằm giữa 2 stat theo trục ngang không?
        const between = dr.map((d) => {
          const before = ir.filter((s) => s.left + s.w <= d.left + 1);
          const after = ir.filter((s) => s.left >= d.left - 1);
          return before.length && after.length;
        });
        return {
          rows: new Set(ir.map((s) => s.top)).size,
          items: ir, dots: dr, visCount: vis.length,
          overflowX: document.documentElement.scrollWidth > window.innerWidth,
          scrollW: document.documentElement.scrollWidth,
          dotBetween: between.every(Boolean),
        };
      });

      console.log(`  ${w}px → ${r.rows} hàng · dot hiện ${r.visCount}/3 · tràn ngang: ${r.overflowX ? '✗ ' + r.scrollW + 'px' : 'không ✓'} · dot nằm giữa 2 stat: ${r.dotBetween ? '✓' : '✗'}`);
      console.log(`     items: ${r.items.map((s) => `(${s.left},${s.top}) ${s.w}×${s.h}${s.lines > 1 ? ' [' + s.lines + ' dòng]' : ''}`).join('  ')}`);

      if (w === 390) await page.screenshot({ path: path.join(OUT, `v-${name.slice(0, 1)}.png`) });
      await page.close();
    }
  }

  await browser.close();
})();
