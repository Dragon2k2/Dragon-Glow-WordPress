#!/usr/bin/env node
/**
 * Tìm cấu hình 3 stat XẾP NGANG không tràn ở mobile.
 * DOM mobile: [i1][d1][i2][d2][i3]  (stat4 + dot3 bị ẩn)
 * Thử nhiều font-size / letter-spacing / gap, đo wrap + overflow thực tế.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const OUT = path.join(path.resolve(__dirname, '..'), 'tmp-resp/sus');
const LABELS = ['100% Vegan', 'Cruelty-Free Certified', 'Carbon-Neutral Shipping', 'FSC-Certified Paper'];

const baseCSS = `
body { margin:0; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }
.dg-sus-numbers { background:#1b1c1a; padding:3rem 0; }
.dg-sus-numbers-inner { max-width:1440px; margin:0 auto; padding:0 24px; display:flex; flex-direction:column; gap:1.5rem; align-items:center; text-align:center; }
.dg-sus-numbers-item { font-size:16px; font-weight:600; line-height:1.0; letter-spacing:0.05em; text-transform:uppercase; color:#fff; }
.dg-sus-numbers-dot { display:block; width:4px; height:4px; border-radius:50%; background:rgba(255,255,255,0.3); }
`;

// biến thể: grid 5 cột (item|dot|item|dot|item)
const gridCSS = `
  .dg-sus-numbers-inner {
    display: grid;
    grid-template-columns: minmax(0,1fr) auto minmax(0,1fr) auto minmax(0,1fr);
    gap: var(--vg) var(--hg);
    align-items: center;
    justify-items: center;
  }
  .dg-sus-numbers-item { font-size: var(--fs); letter-spacing: var(--ls); line-height: 1.2; text-align:center; }
  .dg-sus-numbers-item:nth-child(7) { display:none; }   /* stat 4 */
  .dg-sus-numbers-dot:nth-child(6) { display:none; }     /* dot 3 */
`;

const VARIANTS = [
  { fs: '12px', ls: '0.04em', vg: '1.25rem', hg: '0.5rem' },
  { fs: '11px', ls: '0.03em', vg: '1.25rem', hg: '0.5rem' },
  { fs: '11px', ls: '0.02em', vg: '1rem',   hg: '0.4rem' },
  { fs: '10px', ls: '0.03em', vg: '1rem',   hg: '0.4rem' },
  { fs: '12px', ls: '0.02em', vg: '1.5rem', hg: '0.6rem' },
];

const MARKUP = `
<section class="dg-sus-numbers"><div class="dg-sus-numbers-inner">
  ${LABELS.map((l, i) => `<span class="dg-sus-numbers-item">${l}</span>${
    i < LABELS.length - 1 ? '<span class="dg-sus-numbers-dot" aria-hidden="true"></span>' : ''
  }`).join('\n')}
</div></section>`;

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch();
  const WIDTHS = [320, 360, 390, 430];

  for (const [idx, v] of VARIANTS.entries()) {
    const css = gridCSS
      .replace(/var\(--fs\)/g, v.fs)
      .replace(/var\(--ls\)/g, v.ls)
      .replace(/var\(--vg\)/g, v.vg)
      .replace(/var\(--hg\)/g, v.hg);
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>${baseCSS}${css}</style></head><body>${MARKUP}</body></html>`;
    const file = path.join(OUT, `x.html`);
    fs.writeFileSync(file, html, 'utf8');

    console.log('\n' + '='.repeat(78));
    console.log(`VARIANT ${idx + 1}: font ${v.fs} · ls ${v.ls} · gap ${v.vg}/${v.hg}`);
    console.log('='.repeat(78));

    for (const w of WIDTHS) {
      const page = await browser.newPage({ viewport: { width: w, height: 400 } });
      await page.goto('file:///' + file.replace(/\\/g, '/'));
      await page.waitForTimeout(150);

      const r = await page.evaluate(() => {
        const items = [...document.querySelectorAll('.dg-sus-numbers-item')];
        const vis = items.filter((e) => getComputedStyle(e).display !== 'none');
        const rows = new Set(vis.map((e) => Math.round(e.getBoundingClientRect().top))).size;
        const overflow = [...vis].some((e) => {
          const b = e.getBoundingClientRect();
          const p = e.parentElement.getBoundingClientRect();
          return b.left < p.left - 0.5 || b.right > p.right + 0.5;
        });
        const clipped = [...vis].some((e) => e.scrollWidth > e.clientWidth + 1);
        return {
          rows, overflow, clipped,
          scrollW: document.documentElement.scrollWidth,
          winW: window.innerWidth,
          heights: vis.map((e) => Math.round(e.getBoundingClientRect().height)),
          widths: vis.map((e) => Math.round(e.getBoundingClientRect().width)),
        };
      });

      const ok = r.rows === 1 && !r.overflow && !r.clipped && r.scrollW <= r.winW;
      console.log(`  ${String(w).padStart(3)}px → ${r.rows} hàng · stat w=[${r.widths}] h=[${r.heights}] · tràn: ${r.overflow || r.clipped || r.scrollW > r.winW ? '✗' : 'không ✓'}  ${ok ? '✓ OK' : ''}`);
      await page.close();
    }

    if (idx === 0 || idx === 2) {
      const page = await browser.newPage({ viewport: { width: 390, height: 400 } });
      await page.goto('file:///' + file.replace(/\\/g, '/'));
      await page.waitForTimeout(150);
      await page.screenshot({ path: path.join(OUT, `x-${idx + 1}.png`) });
      await page.close();
    }
  }

  await browser.close();
})();
