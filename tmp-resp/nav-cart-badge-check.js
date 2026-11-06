#!/usr/bin/env node
/**
 * Verify the cart-count badge on the mobile/tablet menu toggle:
 *   - badge renders inside #dg-mobile-menu-toggle
 *   - badge is visible (not `.hidden`) when count > 0
 *   - painting a new count updates the text + fires the `is-pulse` animation
 *   - first appearance fires `is-first-show`
 *   - count 0 hides the badge
 *   - desktop (>=1024px): toggle badge still present in DOM but the toggle
 *     itself is hidden (`lg:hidden`) → visually unchanged
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const JS_DIR = path.join(THEME, 'assets/js');
const OUT = path.join(ROOT, 'tmp-resp/nav-cart-badge');

// Mirrors template-parts/global/header-nav.php markup (logged-in branch).
const MARKUP = `
<body style="margin:0; background:#f7f4ef;">
  <nav class="glass-nav header-nav header-nav--logged-in sticky top-0 z-[100] w-full">
    <div class="dg-nav-container flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4 max-w-container-max-width mx-auto">
      <div class="dg-nav-brand-group flex items-center lg:gap-3">
        <a href="#" class="dg-nav-logo"><img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" class="dg-nav-brand__mark" alt=""></a>
        <span class="dg-nav-brand-name font-headline text-headline-md font-bold tracking-tight text-primary">Dragon Glow</span>
      </div>
      <div class="hidden lg:flex gap-6"><ul><li><a href="#">Home</a></li></ul></div>
      <div class="dg-nav-actions flex items-center gap-3">
        <a href="#" class="hidden lg:block p-2 relative" aria-label="Wishlist">
          <span class="material-symbols-outlined">favorite</span>
          <span class="dg-wishlist-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center">1</span>
        </a>
        <a href="#" class="hidden lg:block relative p-2" aria-label="Cart">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span class="dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">3</span>
        </a>
        <button class="dg-nav-toggle lg:hidden p-2 text-primary relative" id="dg-mobile-menu-toggle" aria-label="Menu" aria-expanded="false" aria-controls="dg-mobile-menu" data-dg-label="Menu">
          <span class="material-symbols-outlined">menu</span>
          <span class="dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">3</span>
        </button>
      </div>
    </div>
  </nav>
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
  const htmlPath = path.join(OUT, 'nav.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  for (const w of [320, 390, 768, 1023, 1024, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 800 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(1400);

    const tag = w < 640 ? 'MOBILE' : w < 1024 ? 'TABLET' : 'DESKTOP';
    const r = await page.evaluate(() => {
      const toggle = document.getElementById('dg-mobile-menu-toggle');
      const badge = toggle.querySelector('.dg-cart-count');
      const cs = getComputedStyle(toggle);
      const bs = getComputedStyle(badge);
      const br = badge.getBoundingClientRect();
      const tr = toggle.getBoundingClientRect();
      return {
        toggleDisplay: cs.display,
        toggleVisible: tr.width > 0 && tr.height > 0,
        badgeText: badge.textContent.trim(),
        badgeHidden: badge.classList.contains('hidden'),
        badgeDisplay: bs.display,
        badgeOpacity: bs.opacity,
        badgeW: Math.round(br.width),
        badgeH: Math.round(br.height),
        // Badge must sit on the top-right corner of the toggle, not overlap
        // the glyph in the middle.
        badgeRightOffset: Math.round(tr.right - br.right),
        badgeTopOffset: Math.round(br.top - tr.top),
        hasPulse: badge.classList.contains('is-pulse'),
        hasFirstShow: badge.classList.contains('is-first-show'),
      };
    });

    console.log(`\n--- ${w}px [${tag}] ---`);
    console.log(`  toggle: display=${r.toggleDisplay} visible=${r.toggleVisible}`);
    console.log(`  badge: text="${r.badgeText}" hidden=${r.badgeHidden} display=${r.badgeDisplay} opacity=${r.badgeOpacity} size=${r.badgeW}x${r.badgeH}`);
    console.log(`  badge offset from toggle: right=${r.badgeRightOffset}px top=${r.badgeTopOffset}px`);

    // --- Behaviour checks (run on every viewport, they are JS-level) ---
    const behaviour = await page.evaluate(async () => {
      const badge = document.querySelector('#dg-mobile-menu-toggle .dg-cart-count');
      const out = {};

      // 1. Paint a new count → text updates + is-pulse fires
      window.DGCartBadge.paint(7);
      await new Promise((r) => setTimeout(r, 60));
      out.afterPaint = {
        text: badge.textContent.trim(),
        pulse: badge.classList.contains('is-pulse'),
        hidden: badge.classList.contains('hidden'),
      };

      // 2. Paint 0 → hidden
      window.DGCartBadge.paint(0);
      await new Promise((r) => setTimeout(r, 60));
      out.afterZero = {
        text: badge.textContent.trim(),
        hidden: badge.classList.contains('hidden'),
      };

      // 3. Re-appear from hidden → is-first-show fires
      window.DGCartBadge.paint(2);
      await new Promise((r) => setTimeout(r, 60));
      out.afterReappear = {
        text: badge.textContent.trim(),
        firstShow: badge.classList.contains('is-first-show'),
        pulse: badge.classList.contains('is-pulse'),
        hidden: badge.classList.contains('hidden'),
      };

      // 4. bump(+2)
      window.DGCartBadge.bump(2);
      await new Promise((r) => setTimeout(r, 60));
      out.afterBump = { text: badge.textContent.trim() };

      // 5. aria-label on the toggle must announce the count
      out.ariaAfterBump = { label: document.getElementById('dg-mobile-menu-toggle').getAttribute('aria-label') };

      window.DGCartBadge.paint(0);
      await new Promise((r) => setTimeout(r, 40));
      out.ariaAfterZero = { label: document.getElementById('dg-mobile-menu-toggle').getAttribute('aria-label') };

      return out;
    });

    console.log(`  paint(7)   → text="${behaviour.afterPaint.text}" is-pulse=${behaviour.afterPaint.pulse} hidden=${behaviour.afterPaint.hidden}`);
    console.log(`  paint(0)   → text="${behaviour.afterZero.text}" hidden=${behaviour.afterZero.hidden}`);
    console.log(`  paint(2)   → text="${behaviour.afterReappear.text}" is-first-show=${behaviour.afterReappear.firstShow} hidden=${behaviour.afterReappear.hidden}`);
    console.log(`  bump(+2)   → text="${behaviour.afterBump.text}"`);
    console.log(`  aria-label → "${behaviour.ariaAfterBump.label}" (count=4)`);
    console.log(`  aria-label → "${behaviour.ariaAfterZero.label}" (count=0)`);

    const ok =
      behaviour.afterPaint.text === '7' && behaviour.afterPaint.pulse &&
      behaviour.afterZero.hidden && behaviour.afterZero.text === '0' &&
      behaviour.afterReappear.text === '2' && behaviour.afterReappear.firstShow &&
      behaviour.afterBump.text === '4';
    console.log(`  → ${ok ? '✓ animation + count logic ĐÚNG' : '✗ SAI'}`);

    // Visual expectation: on mobile/tablet the toggle is visible AND has a badge;
    // on desktop the toggle is hidden (lg:hidden) so nothing changes visually.
    if (w < 1024) {
      const visOk = r.toggleVisible && r.badgeDisplay !== 'none' && r.badgeOpacity === '1' && !r.badgeHidden;
      console.log(`  → ${visOk ? '✓ toggle + badge hiển thị' : '✗ SAI — toggle/badge không hiện'}`);
    } else {
      const deskOk = !r.toggleVisible;
      console.log(`  → ${deskOk ? '✓ toggle ẩn (desktop không đổi)' : '✗ SAI — toggle hiện ở desktop'}`);
    }

    await page.screenshot({ path: path.join(OUT, `nav-${w}.png`), fullPage: false });
    await page.close();
  }
  await browser.close();
})();
