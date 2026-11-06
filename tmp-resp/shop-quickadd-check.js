#!/usr/bin/env node
/**
 * Verify quick-add CTA (`.dg-add-to-cart.dg-quick-add`) visibility:
 *   mobile (<640px) + tablet (640-1023px) → ALWAYS visible (no hover needed)
 *   desktop (>=1024px)                   → hover-reveal (unchanged)
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/shop-quickadd');

const CARD = `
<div class="stagger-item group product-card-hover reveal-on-scroll active dg-product-card">
  <a href="#" class="dg-product-stretched-link"></a>
  <div class="relative aspect-[3/4] overflow-hidden bg-surface-container-low mb-6 dg-product-image">
    <div class="w-full h-full bg-gradient-to-br from-primary-container to-secondary-container dg-product-img"></div>
    <button class="dg-add-to-cart dg-quick-add inline-flex items-center justify-center gap-2"
      data-product-id="44" data-product-slug="browluxe" data-original-label="Add to Cart" type="button">
      <span class="material-symbols-outlined" style="font-size:16px;line-height:1;">shopping_bag</span>
      <span class="dg-quick-add__label">Add to Cart</span>
    </button>
  </div>
  <a href="#" class="text-center px-2 dg-product-info-link">
    <h3 class="dg-product-name">Browluxe Define Brow Mascara</h3>
    <div class="dg-product-divider"></div>
    <p class="dg-product-price">$28.00</p>
  </a>
</div>`;

const MARKUP = `
<body style="margin:0; background:#f7f4ef;">
  <div class="dg-shop-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-gutter gap-y-32" style="padding:24px;">
    ${CARD}
    ${CARD}
  </div>
</body>`;

const LINKS = ['../../style.css', 'main.css', 'shop.css']
  .map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`)
  .join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=block">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=block">
${LINKS}
<script src="https://cdn.tailwindcss.com"></script>
</head>${MARKUP}</html>`;

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'shop.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  for (const w of [320, 390, 430, 639, 640, 768, 1023, 1024, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(1500);

    const read = () => page.evaluate(() => {
      const btns = [...document.querySelectorAll('.dg-add-to-cart')];
      return btns.map((b) => {
        const cs = getComputedStyle(b);
        const r = b.getBoundingClientRect();
        // Is the element actually painted (visible), not just opacity 1?
        const atPoint = document.elementFromPoint(
          Math.round(r.left + r.width / 2),
          Math.round(r.top + r.height / 2)
        );
        return {
          opacity: cs.opacity,
          transform: cs.transform,
          visibility: cs.visibility,
          pointerEvents: cs.pointerEvents,
          w: Math.round(r.width),
          h: Math.round(r.height),
          top: Math.round(r.top),
          hitTarget: atPoint === b || b.contains(atPoint),
        };
      });
    });

    const before = await read();
    const tag = w < 640 ? 'MOBILE' : w < 1024 ? 'TABLET' : 'DESKTOP';

    console.log(`\n--- ${w}px [${tag}] ---`);
    console.log(`  KHÔNG hover: opacity=${before[0].opacity} transform=${before[0].transform} hit=${before[0].hitTarget} size=${before[0].w}x${before[0].h}`);

    // Now hover the card and re-read
    await page.hover('.dg-product-card');
    await page.waitForTimeout(600);
    const after = await read();
    console.log(`  CÓ hover:    opacity=${after[0].opacity} transform=${after[0].transform}`);

    const visible = (r) => parseFloat(r.opacity) > 0.9 && r.hitTarget && r.h > 0;
    let ok;
    if (w < 1024) {
      ok = visible(before[0]);
      console.log(`  → ${ok ? '✓ LUÔN HIỆN (không cần hover)' : '✗ SAI — vẫn ẩn'}`);
    } else {
      // Desktop: phải ẩn khi không hover, hiện khi hover
      const hiddenFirst = parseFloat(before[0].opacity) < 0.1;
      const shownOnHover = parseFloat(after[0].opacity) > 0.9;
      ok = hiddenFirst && shownOnHover;
      console.log(`  → ${ok ? '✓ ẩn khi không hover, hiện khi hover (không đổi)' : '✗ SAI'}`);
    }

    await page.screenshot({ path: path.join(OUT, `quickadd-${w}.png`), fullPage: true });
    await page.close();
  }
  await browser.close();
})();
