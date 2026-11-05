#!/usr/bin/env node
/**
 * Render các phương án layout 2×2 cho .dg-sus-numbers ở mobile 390px,
 * so sánh trực quan (screenshot) để chọn cách đẹp + đúng vai trò separator.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const OUT = path.join(path.resolve(__dirname, '..'), 'tmp-resp/sus');

const LABELS = ['100% Vegan', 'Cruelty-Free Certified', 'Carbon-Neutral Shipping', 'FSC-Certified Paper'];

const baseCSS = `
body { margin: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.dg-sus-numbers { background: #1b1c1a; padding: 3rem 0; }
.dg-sus-numbers-inner { max-width: 1440px; margin: 0 auto; padding: 0 24px; display: flex; flex-direction: column; gap: 1.5rem; align-items: center; text-align: center; }
.dg-sus-numbers-item { font-size: 16px; font-weight: 600; line-height: 1.25; letter-spacing: 0.05em; text-transform: uppercase; color: #fff; }
.dg-sus-numbers-dot { display: block; width: 4px; height: 4px; border-radius: 50%; background: rgba(255,255,255,0.3); }
`;

const VARIANTS = {
  'D': `
    .dg-sus-numbers-inner { display: grid; grid-template-columns: minmax(0,1fr) auto minmax(0,1fr) auto; gap: 1.5rem 0.6rem; align-items: center; justify-items: center; }
    .dg-sus-numbers-item { font-size: 13px; letter-spacing: 0.04em; text-align: center; }
  `,
  'E': `
    .dg-sus-numbers-inner { display: grid; grid-template-columns: minmax(0,1fr) auto minmax(0,1fr); gap: 1.5rem 0.6rem; align-items: center; justify-items: center; }
    .dg-sus-numbers-item { font-size: 13px; letter-spacing: 0.04em; text-align: center; }
    .dg-sus-numbers-item:nth-child(1) { grid-area: 1 / 1; }
    .dg-sus-numbers-dot:nth-child(2) { grid-area: 1 / 2; }
    .dg-sus-numbers-item:nth-child(3) { grid-area: 1 / 3; }
    .dg-sus-numbers-item:nth-child(5) { grid-area: 2 / 1; }
    .dg-sus-numbers-dot:nth-child(6) { grid-area: 2 / 2; }
    .dg-sus-numbers-item:nth-child(7) { grid-area: 2 / 3; }
    .dg-sus-numbers-dot:nth-child(8) { display: none; }
  `,
  'F': `
    .dg-sus-numbers-inner { display: flex; flex-direction: row; flex-wrap: wrap; gap: 1.25rem 0.5rem; align-items: center; justify-content: center; }
    .dg-sus-numbers-item { font-size: 13px; letter-spacing: 0.04em; text-align: center; flex: 0 1 calc(50% - 1rem); }
    .dg-sus-numbers-dot { flex: 0 0 auto; }
  `,
};

const MARKUP = `
<section class="dg-sus-numbers">
  <div class="dg-sus-numbers-inner">
    ${LABELS.map((l, i) => `<span class="dg-sus-numbers-item">${l}</span>${
      i < LABELS.length - 1 ? '<span class="dg-sus-numbers-dot" aria-hidden="true"></span>' : ''
    }`).join('\n')}
  </div>
</section>`;

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch();

  for (const [key, css] of Object.entries(VARIANTS)) {
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>${baseCSS}${css}</style></head><body>${MARKUP}</body></html>`;
    const file = path.join(OUT, `var-${key}.html`);
    fs.writeFileSync(file, html, 'utf8');

    const page = await browser.newPage({ viewport: { width: 390, height: 460 } });
    await page.goto('file:///' + file.replace(/\\/g, '/'));
    await page.waitForTimeout(200);
    await page.screenshot({ path: path.join(OUT, `var-${key}.png`) });
    await page.close();
  }

  await browser.close();
  console.log('Đã render D/E/F xong.');
})();
