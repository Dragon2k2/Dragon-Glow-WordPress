#!/usr/bin/env node
/** Đo bề rộng thật của các thành phần trong toolbar ở mobile. */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();
  for (const w of [320, 360, 390, 430]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(500);
    const r = await page.evaluate(() => {
      const measure = (el) => {
        if (!el) return null;
        const cs = getComputedStyle(el);
        const rect = el.getBoundingClientRect();
        // Bề rộng text thật: clone vào span ẩn, đo bằng canvas
        const c = document.createElement('canvas').getContext('2d');
        c.font = `${cs.fontWeight} ${cs.fontSize} ${cs.fontFamily}`;
        const textW = Math.ceil(c.measureText(el.textContent.trim()).width);
        return {
          w: Math.round(rect.width),
          textW,
          fs: cs.fontSize,
          ff: cs.fontFamily.split(',')[0],
          pad: cs.paddingLeft + '|' + cs.paddingRight,
        };
      };
      const tb = document.querySelector('.dg-wishlist-toolbar');
      const tbs = getComputedStyle(tb);
      const contentW = tb.getBoundingClientRect().width - parseFloat(tbs.paddingLeft) - parseFloat(tbs.paddingRight);
      return {
        contentW: Math.round(contentW),
        selectAll: measure(document.querySelector('.dg-wishlist-toolbar__select-all')),
        selectLabel: measure(document.querySelector('.dg-wishlist-toolbar__select-all-label')),
        trigger: measure(document.querySelector('.dg-wishlist-toolbar__sort-trigger')),
        current: measure(document.querySelector('.dg-wishlist-toolbar__sort-current')),
        chevron: measure(document.querySelector('.dg-wishlist-toolbar__sort-chevron')),
        fontLoaded: document.fonts.check('600 13px "Plus Jakarta Sans"'),
      };
    });
    console.log(`\n--- ${w}px (content ${r.contentW}px) fontLoaded=${r.fontLoaded} ---`);
    console.log(`  select-all  w=${r.selectAll.w}  text="${r.selectLabel.textW}px" @${r.selectLabel.fs} ${r.selectLabel.ff}`);
    console.log(`  trigger     w=${r.trigger.w}  pad=${r.trigger.pad}`);
    console.log(`  sort-current text=${r.current.textW}px @${r.current.fs} ${r.current.ff}`);
    console.log(`  chevron     w=${r.chevron.w}`);
    const need = r.current.textW + r.chevron.w + 6 + 24;
    console.log(`  → cần tối thiểu cho trigger: ${need}px  | sau select-all(${r.selectAll.w})+gap(10) còn: ${r.contentW - r.selectAll.w - 10}px`);
    await page.close();
  }
  await browser.close();
})();
