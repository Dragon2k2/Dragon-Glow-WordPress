#!/usr/bin/env node
/** Kiểm tra segment (3 chip auto-width) có tràn ngang ở mobile không. */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();
  for (const w of [320, 360, 390, 430, 639, 640]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);
    const r = await page.evaluate(() => {
      const seg = document.querySelector('.dg-wishlist-toolbar__segment');
      const tb = document.querySelector('.dg-wishlist-toolbar');
      const chips = Array.from(document.querySelectorAll('.dg-wishlist-toolbar__seg'));
      const sr = seg.getBoundingClientRect();
      const tbcs = getComputedStyle(tb);
      const contentL = tb.getBoundingClientRect().left + parseFloat(tbcs.paddingLeft) + parseFloat(tbcs.borderLeftWidth);
      const contentR = tb.getBoundingClientRect().right - parseFloat(tbcs.paddingRight) - parseFloat(tbcs.borderRightWidth);
      const chipW = chips.map((c) => Math.round(c.getBoundingClientRect().width));
      const last = chips[2].getBoundingClientRect();
      return {
        segScroll: seg.scrollWidth - seg.clientWidth,
        segW: Math.round(sr.width),
        contentW: Math.round(contentR - contentL),
        chipW,
        chipSum: chipW.reduce((a, b) => a + b, 0),
        lastRight: Math.round(last.right),
        contentR: Math.round(contentR),
        overflowRight: Math.round(last.right - contentR),
        overflowLeft: Math.round(contentL - chips[0].getBoundingClientRect().left),
      };
    });
    const ok = r.overflowRight <= 1 && r.overflowLeft <= 1 && r.segScroll <= 1;
    console.log(`${String(w).padStart(4)}px | chips ${r.chipW.join('+')} = ${r.chipSum}px + gridGap 8px = ${r.chipSum + 8}px | content ${r.contentW}px | seg ${r.segW}px (scrollOverflow ${r.segScroll}) | tràn trái=${r.overflowLeft} phải=${r.overflowRight}  ${ok ? '✓' : '✗ TRÀN'}`);
    await page.close();
  }
  await browser.close();
})();
