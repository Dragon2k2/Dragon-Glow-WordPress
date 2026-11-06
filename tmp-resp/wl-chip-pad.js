#!/usr/bin/env node
/** Đo khoảng cách thoải mái (padding) của chip filter ở mobile. */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();
  for (const w of [320, 360, 390, 430, 639, 640, 768]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);
    const r = await page.evaluate(() => {
      const seg = document.querySelector('.dg-wishlist-toolbar__segment');
      const cs = getComputedStyle(seg);
      const segInset = {
        pl: parseFloat(cs.paddingLeft), pr: parseFloat(cs.paddingRight),
        bl: parseFloat(cs.borderLeftWidth), br: parseFloat(cs.borderRightWidth),
      };
      const chips = Array.from(document.querySelectorAll('.dg-wishlist-toolbar__seg'));
      const out = chips.map((s) => {
        const scs = getComputedStyle(s);
        const sr = s.getBoundingClientRect();
        // text span (không phải icon, không phải badge)
        const txt = Array.from(s.children).find((c) => !c.classList.contains('material-symbols-outlined') && !c.classList.contains('dg-wishlist-toolbar__count'));
        const tr = txt.getBoundingClientRect();
        const icon = s.querySelector('.material-symbols-outlined');
        const ir = icon ? icon.getBoundingClientRect() : null;
        const badge = s.querySelector('.dg-wishlist-toolbar__count').getBoundingClientRect();
        return {
          text: txt.textContent.trim(),
          padL: scs.paddingLeft, padR: scs.paddingRight,
          fs: scs.fontSize,
          // khoảng cách text -> mép phải chip (có badge ở giữa, dùng text->badge)
          gapTextLeft: Math.round(tr.left - sr.left),
          gapTextToIcon: ir ? Math.round(tr.left - ir.right) : null,
          gapTextToBadge: Math.round(badge.left - tr.right),
          gapBadgeToRight: Math.round(sr.right - badge.right),
          iconShown: ir ? ir.width > 0 : false,
          h: Math.round(sr.height),
        };
      });
      return { segInset, out };
    });
    const tag = w < 640 ? 'MOBILE' : 'T/D';
    console.log(`\n--- ${w}px [${tag}] (segment pad ${r.segInset.pl}/${r.segInset.pr}) ---`);
    r.out.forEach((o) => console.log(
      `  "${o.text}" fs=${o.fs} pad=${o.padL}/${o.padR} icon=${o.iconShown ? 'CÓ' : 'không'} | ` +
      `textLeft=${o.gapTextLeft}px text→icon=${o.gapTextToIcon}px text→badge=${o.gapTextToBadge}px badge→right=${o.gapBadgeToRight}px | h=${o.h}`
    ));
    await page.close();
  }
  await browser.close();
})();
