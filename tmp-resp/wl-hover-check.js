#!/usr/bin/env node
/**
 * Verify hover behavior của chip filter:
 *  - chip ACTIVE  → hover KHÔNG đổi style (giữ nền vàng active)
 *  - chip INACTIVE → hover CÓ đổi style (nền chip-bg, chữ primary)
 * Ở mọi viewport (mobile / tablet / desktop).
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();

  for (const w of [320, 390, 639, 640, 768, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(400);

    const res = await page.evaluate(() => {
      const segs = Array.from(document.querySelectorAll('.dg-wishlist-toolbar__seg'));
      const active = segs.find((s) => s.classList.contains('is-active'));
      const inactive = segs.find((s) => !s.classList.contains('is-active'));
      window.__els = { active, inactive };

      const snap = (el) => {
        const cs = getComputedStyle(el);
        const badge = el.querySelector('.dg-wishlist-toolbar__count');
        const bcs = badge ? getComputedStyle(badge) : null;
        return {
          bg: cs.backgroundColor,
          color: cs.color,
          badgeBg: bcs ? bcs.backgroundColor : null,
          badgeColor: bcs ? bcs.color : null,
        };
      };
      return {
        aRest: snap(active),
        iRest: snap(inactive),
      };
    });

    // Hover THẬT qua CDP mouse (synthetic events không kích hoạt CSS :hover)
    await page.hover('.dg-wishlist-toolbar__seg.is-active');
    await page.waitForTimeout(350);
    const aHover = await page.evaluate(() => {
      const cs = getComputedStyle(window.__els.active);
      const b = getComputedStyle(window.__els.active.querySelector('.dg-wishlist-toolbar__count'));
      return { bg: cs.backgroundColor, color: cs.color, badgeBg: b.backgroundColor, badgeColor: b.color };
    });

    await page.hover('.dg-wishlist-toolbar__seg:not(.is-active)');
    await page.waitForTimeout(350);
    const iHover = await page.evaluate(() => {
      const cs = getComputedStyle(window.__els.inactive);
      const b = getComputedStyle(window.__els.inactive.querySelector('.dg-wishlist-toolbar__count'));
      return { bg: cs.backgroundColor, color: cs.color, badgeBg: b.backgroundColor, badgeColor: b.color };
    });

    const activeStable = aHover.bg === res.aRest.bg && aHover.color === res.aRest.color;
    const inactiveChanges = iHover.bg !== res.iRest.bg || iHover.color !== res.iRest.color;

    console.log(`\n--- ${w}px ---`);
    console.log(`  ACTIVE   rest  bg=${res.aRest.bg} color=${res.aRest.color} badge=${res.aRest.badgeBg}/${res.aRest.badgeColor}`);
    console.log(`           hover bg=${aHover.bg} color=${aHover.color} badge=${aHover.badgeBg}/${aHover.badgeColor}`);
    console.log(`           → ${activeStable ? '✓ KHÔNG đổi khi hover (đúng)' : '✗ ĐỔI khi hover (sai)'}`);
    console.log(`  INACTIVE rest  bg=${res.iRest.bg} color=${res.iRest.color} badge=${res.iRest.badgeBg}`);
    console.log(`           hover bg=${iHover.bg} color=${iHover.color} badge=${iHover.badgeBg}/${iHover.badgeColor}`);
    console.log(`           → ${inactiveChanges ? '✓ CÓ đổi khi hover (đúng)' : '✗ KHÔNG đổi (sai)'}`);

    await page.close();
  }
  await browser.close();
})();
