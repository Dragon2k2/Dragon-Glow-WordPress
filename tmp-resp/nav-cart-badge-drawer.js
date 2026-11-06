#!/usr/bin/env node
/**
 * Verify the cart-count badge on the drawer "Cart" item:
 *   - badge renders inside `.dg-mobile-menu-item--cart a`
 *   - badge is visible when count > 0, hidden when count = 0
 *   - badge sits at the trailing edge of the row (not over the glyph)
 *   - painting a new count updates the text + fires the animation
 *   - aria-label on the anchor announces the count
 *   - the drawer is `lg:hidden` → desktop unaffected
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const JS_DIR = path.join(THEME, 'assets/js');
const OUT = path.join(ROOT, 'tmp-resp/nav-cart-badge');

const MARKUP = `
<body style="margin:0; background:#f7f4ef;">
  <div class="lg:hidden" id="dg-mobile-menu" style="position:static; visibility:visible; transform:none; width:min(86vw,22.5rem); background:#fff;">
    <div class="dg-mobile-menu__header">
      <span class="dg-mobile-menu__brand-name">Dragon Glow</span>
      <button class="dg-mobile-menu__close" id="dg-mobile-menu-close"><span class="material-symbols-outlined">close</span></button>
    </div>
    <ul class="dg-mobile-menu__list flex flex-col px-margin-mobile py-4 gap-4 border-t border-outline-variant/20">
      <li class="menu-item dg-mobile-menu-item--home"><a href="#"><span class="font-body text-body-md text-on-surface">Home</span></a></li>
      <li class="menu-item dg-mobile-menu-item--shop"><a href="#"><span class="font-body text-body-md text-on-surface">Shop</span></a></li>
      <li class="menu-item dg-mobile-menu-item--divider"><span class="block border-t border-outline-variant/20 my-2"></span></li>
      <li class="menu-item dg-mobile-menu-item--account"><a href="#"><span class="font-body text-body-md text-on-surface">Account</span></a></li>
      <li class="menu-item dg-mobile-menu-item--wishlist"><a href="#"><span class="font-body text-body-md text-on-surface">Wishlist</span></a></li>
      <li class="menu-item dg-mobile-menu-item--cart"><a href="#" data-dg-label="Cart"><span class="font-body text-body-md text-on-surface">Cart</span><span class="dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">3</span></a></li>
    </ul>
  </div>
</body>`;

const CSS = ['../../style.css', 'main.css', 'header-nav.css', 'woocommerce.css']
  .map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`)
  .join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block">
${CSS}
<script src="https://cdn.tailwindcss.com"></script>
<script src="${path.relative(OUT, path.join(OUT, 'tailwind-config.js')).replace(/\\/g, '/')}"></script>
</head>${MARKUP}
<script src="${path.relative(OUT, path.resolve(JS_DIR, 'lib/cart-badge.js')).replace(/\\/g, '/')}"></script>
<script>window.dgAjax = { i18n: { itemSingular: '1 item in bag', itemPlural: '%d items in bag' } };</script>
</html>`;

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'drawer.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  for (const w of [320, 390, 768, 1023]) {
    const page = await browser.newPage({ viewport: { width: w, height: 700 }, deviceScaleFactor: 3 });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(1500);

    const r = await page.evaluate(() => {
      const link = document.querySelector('.dg-mobile-menu-item--cart a');
      const badge = link.querySelector('.dg-cart-count');
      const label = link.querySelector('.font-body');
      const cs = getComputedStyle(badge);
      const br = badge.getBoundingClientRect();
      const lr = link.getBoundingClientRect();
      const tr = label.getBoundingClientRect();
      return {
        badgeText: badge.textContent.trim(),
        hidden: badge.classList.contains('hidden'),
        display: cs.display,
        position: cs.position,
        opacity: cs.opacity,
        w: Math.round(br.width),
        h: Math.round(br.height),
        rightGap: Math.round(lr.right - br.right),
        afterLabel: Math.round(br.left - tr.right),
        linkW: Math.round(lr.width),
        ariaLabel: link.getAttribute('aria-label'),
      };
    });

    console.log(`\n--- ${w}px ---`);
    console.log(`  badge: text="${r.badgeText}" hidden=${r.hidden} display=${r.display} position=${r.position} size=${r.w}x${r.h}`);
    console.log(`  badge sau label: +${r.afterLabel}px, cách mép phải ${r.rightGap}px (link rộng ${r.linkW}px)`);
    console.log(`  aria-label: "${r.ariaLabel}"`);

    const behaviour = await page.evaluate(async () => {
      const link = document.querySelector('.dg-mobile-menu-item--cart a');
      const badge = link.querySelector('.dg-cart-count');
      const out = {};

      window.DGCartBadge.paint(7);
      await new Promise((r) => setTimeout(r, 60));
      out.paint7 = { text: badge.textContent.trim(), pulse: badge.classList.contains('is-pulse'), hidden: badge.classList.contains('hidden'), aria: link.getAttribute('aria-label') };

      window.DGCartBadge.paint(0);
      await new Promise((r) => setTimeout(r, 60));
      out.paint0 = { text: badge.textContent.trim(), hidden: badge.classList.contains('hidden'), display: getComputedStyle(badge).display, aria: link.getAttribute('aria-label') };

      window.DGCartBadge.paint(2);
      await new Promise((r) => setTimeout(r, 60));
      out.paint2 = { text: badge.textContent.trim(), first: badge.classList.contains('is-first-show'), hidden: badge.classList.contains('hidden') };

      window.DGCartBadge.paint(1);
      await new Promise((r) => setTimeout(r, 60));
      out.paint1 = { text: badge.textContent.trim(), aria: link.getAttribute('aria-label') };

      window.DGCartBadge.bump(3);
      await new Promise((r) => setTimeout(r, 60));
      out.bump = { text: badge.textContent.trim() };

      return out;
    });

    console.log(`  paint(7)  → "${behaviour.paint7.text}" is-pulse=${behaviour.paint7.pulse} aria="${behaviour.paint7.aria}"`);
    console.log(`  paint(0)  → hidden=${behaviour.paint0.hidden} display=${behaviour.paint0.display} aria="${behaviour.paint0.aria}"`);
    console.log(`  paint(2)  → "${behaviour.paint2.text}" is-first-show=${behaviour.paint2.first}`);
    console.log(`  paint(1)  → "${behaviour.paint1.text}" aria="${behaviour.paint1.aria}"`);
    console.log(`  bump(+3)  → "${behaviour.bump.text}"`);

    const ok =
      behaviour.paint7.text === '7' && behaviour.paint7.pulse &&
      behaviour.paint0.hidden && behaviour.paint0.display === 'none' &&
      behaviour.paint2.text === '2' && behaviour.paint2.first &&
      behaviour.paint1.text === '1' &&
      behaviour.bump.text === '4';
    console.log(`  → ${ok ? '✓ logic + animation ĐÚNG' : '✗ SAI'}`);

    const layoutOk = r.position === 'static' && r.afterLabel > 0 && r.rightGap >= 0 && r.rightGap < 30;
    console.log(`  → ${layoutOk ? '✓ badge nằm trong flow, sát mép phải, không đè glyph' : '✗ layout SAI'}`);

    await page.screenshot({ path: path.join(OUT, `drawer-${w}.png`), clip: { x: 0, y: 0, width: Math.min(w, 380), height: 420 } });
    await page.close();
  }
  await browser.close();
})();
