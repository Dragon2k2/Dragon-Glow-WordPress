#!/usr/bin/env node
/** Kiểm tra overflow nội dung bên trong chip filter + sort trigger ở mobile. */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();
  for (const w of [320, 360, 390, 430, 639]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);
    const r = await page.evaluate(() => {
      const out = [];
      document.querySelectorAll('.dg-wishlist-toolbar__seg').forEach((s) => {
        out.push({
          el: 'seg:' + s.textContent.trim().replace(/\s+/g, ' '),
          over: s.scrollWidth - s.clientWidth,
          h: Math.round(s.getBoundingClientRect().height),
        });
      });
      const t = document.querySelector('.dg-wishlist-toolbar__sort-current');
      const tg = document.querySelector('.dg-wishlist-toolbar__sort-trigger');
      out.push({ el: 'sort-current', over: t.scrollWidth - t.clientWidth, h: Math.round(tg.getBoundingClientRect().height) });
      const tb = document.querySelector('.dg-wishlist-toolbar').getBoundingClientRect();
      out.push({ el: 'toolbar', over: Math.round(tb.right - window.innerWidth), h: Math.round(tb.height) });
      return out;
    });
    console.log(`\n--- ${w}px ---`);
    r.forEach((o) => console.log(`  ${o.el.padEnd(26)} overflow=${o.over}px  h=${o.h}`));
    await page.close();
  }
  await browser.close();
})();
