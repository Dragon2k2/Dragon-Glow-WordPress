#!/usr/bin/env node
/** Xác nhận select-all và sort-trigger nằm CÙNG 1 HÀNG ở mobile. */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();
  for (const w of [320, 360, 390, 430, 639, 640, 768, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);
    const r = await page.evaluate(() => {
      const sa = document.querySelector('.dg-wishlist-toolbar__select-all').getBoundingClientRect();
      const tg = document.querySelector('.dg-wishlist-toolbar__sort-trigger').getBoundingClientRect();
      const cur = document.querySelector('.dg-wishlist-toolbar__sort-current');
      const label = document.querySelector('.dg-wishlist-toolbar__sort-label');
      const seg = document.querySelector('.dg-wishlist-toolbar__segment').getBoundingClientRect();
      return {
        sameRow: Math.abs((sa.top + sa.height / 2) - (tg.top + tg.height / 2)) < 3,
        centers: Math.round(sa.top + sa.height / 2) + ' vs ' + Math.round(tg.top + tg.height / 2),
        saW: Math.round(sa.width), tgW: Math.round(tg.width),
        saL: Math.round(sa.left), tgL: Math.round(tg.left), tgR: Math.round(tg.right),
        curOverflow: cur.scrollWidth - cur.clientWidth,
        labelShown: getComputedStyle(label).display !== 'none',
        segBottom: Math.round(seg.bottom),
        gap: Math.round(tg.left - sa.right),
      };
    });
    const tag = w < 640 ? 'MOBILE' : 'TABLET/DESKTOP';
    console.log(`${String(w).padStart(4)}px [${tag.padEnd(13)}] sameRow=${r.sameRow ? 'YES' : 'NO '} | select-all ${r.saW}px @${r.saL} | trigger ${r.tgW}px @${r.tgL} | gap=${r.gap}px | textOverflow=${r.curOverflow}px | "Sort by" ${r.labelShown ? 'hiện' : 'ẩn'}`);
    await page.close();
  }
  await browser.close();
})();
