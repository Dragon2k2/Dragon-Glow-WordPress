#!/usr/bin/env node
/**
 * Đo bề rộng thực tế của 4 stat ở các kích thước mobile
 * để xác định có thể xếp NGANG (giống desktop) được không.
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

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'sus-measure.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();

  for (const w of [360, 390, 430]) {
    const page = await browser.newPage({ viewport: { width: w, height: 800 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(250);

    const r = await page.evaluate(() => {
      const inner = document.querySelector('.dg-sus-numbers-inner');
      const cs = getComputedStyle(inner);
      const items = [...document.querySelectorAll('.dg-sus-numbers-item')];
      // đo bề rộng text tự nhiên (không bị wrap)
      const probe = document.createElement('span');
      probe.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;';
      probe.style.font = items[0] ? getComputedStyle(items[0]).font : '16px sans-serif';
      probe.style.letterSpacing = items[0] ? getComputedStyle(items[0]).letterSpacing : 'normal';
      probe.style.textTransform = items[0] ? getComputedStyle(items[0]).textTransform : 'none';
      probe.style.fontWeight = items[0] ? getComputedStyle(items[0]).fontWeight : '600';
      const widths = items.map((el) => {
        probe.textContent = el.textContent.trim();
        document.body.appendChild(probe);
        const w = probe.getBoundingClientRect().width;
        probe.remove();
        return Math.ceil(w);
      });
      return {
        fontSize: getComputedStyle(items[0]).fontSize,
        letterSpacing: getComputedStyle(items[0]).letterSpacing,
        gap: cs.gap,
        padding: cs.paddingLeft,
        innerWidth: inner.getBoundingClientRect().width,
        widths,
      };
    });

    const totalText = r.widths.reduce((a, b) => a + b, 0);
    const dots = 3 * 4;          // 3 dot × 4px
    const avail = r.innerWidth;

    console.log('='.repeat(78));
    console.log(`MOBILE ${w}px`);
    console.log('='.repeat(78));
    console.log(`  font-size     : ${r.fontSize}  letter-spacing: ${r.letterSpacing}`);
    console.log(`  container rộng: ${Math.round(avail)}px (gap ${r.gap})`);
    console.log(`  bề rộng text  : ${r.widths.join(' + ')} = ${totalText}px`);
    console.log(`  + 3 dot (12px) + gap: 1 hàng cần ~${totalText + dots}px`);
    console.log(`  → 1 hàng ngang: ${totalText + dots <= avail ? 'VỪA ✓' : 'TRÀN ✗ (thiếu ' + (totalText + dots - avail) + 'px)'}`);

    // giả sử 2 hàng: 2 item + 1 dot mỗi hàng
    const pairMax = Math.max(r.widths[0] + r.widths[1], r.widths[2] + r.widths[3]);
    console.log(`  → 2 hàng (2+2): cần ~${pairMax + 4}px/hàng  ${pairMax + 4 <= avail ? 'VỪA ✓' : 'TRÀN ✗'}`);

    // font giảm để vừa 1 hàng?
    for (const scale of [0.85, 0.75, 0.7]) {
      const need = (totalText + dots) * scale;
      console.log(`     font ×${scale} → cần ~${Math.ceil(need)}px  ${need <= avail ? 'VỪA ✓' : 'TRÀN ✗'}`);
    }

    await page.close();
  }

  await browser.close();
})();
