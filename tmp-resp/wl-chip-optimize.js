#!/usr/bin/env node
/**
 * Tối ưu bộ (font-size, icon-size, badge-size, gap, padding) cho chip filter
 * ở 320px: MAX hoá padding (khoảng cách text → viền chip) trong khi:
 *   - font-size >= 10px (dễ đọc)
 *   - tổng 3 chip + 2 gap <= content khả dụng
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 320, height: 900 } });
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(600);

  const segAvail = await page.evaluate(() => {
    const seg = document.querySelector('.dg-wishlist-toolbar__segment');
    const cs = getComputedStyle(seg);
    return seg.getBoundingClientRect().width
      - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight)
      - parseFloat(cs.borderLeftWidth) - parseFloat(cs.borderRightWidth);
  });

  // Đo bề rộng chữ thật theo font-size (canvas, đúng font đang dùng)
  const textW = await page.evaluate(() => {
    const c = document.createElement('canvas').getContext('2d');
    const seg = document.querySelector('.dg-wishlist-toolbar__segment');
    const cs = getComputedStyle(seg.querySelector('.dg-wishlist-toolbar__seg'));
    const F = `${cs.fontWeight} ${cs.fontSize} ${cs.fontFamily}`;
    const out = {};
    for (let f = 8; f <= 14; f++) {
      c.font = `600 ${f}px ${cs.fontFamily}`;
      out[f] = ['All items', 'In stock', 'On sale'].map((t) => Math.ceil(c.measureText(t).width));
    }
    return out;
  });

  console.log(`content khả dụng ở 320px: ${segAvail}px`);
  console.log(`bề rộng chữ: ${JSON.stringify(textW)}\n`);

  const GRID_GAP = 4;
  const results = [];
  for (let font = 9; font <= 12; font++) {
    for (const icon of [11, 12, 13, 14, 15, 16]) {
      for (const badge of [13, 14, 15, 16, 18, 20]) {
        for (const gap of [2, 3, 4]) {
          for (let pad = 4; pad <= 10; pad += 0.5) {
            const chips = textW[font].map((tw) => pad * 2 + icon + gap + tw + gap + badge);
            const total = chips.reduce((a, b) => a + b, 0) + GRID_GAP * 2;
            if (total <= segAvail) {
              results.push({ font, icon, badge, gap, pad, total, room: Math.round(segAvail - total) });
            }
          }
        }
      }
    }
  }

  // Ưu tiên: font lớn nhất → padding lớn nhất → badge lớn nhất
  results.sort((a, b) => b.font - a.font || b.pad - a.pad || b.badge - a.badge);

  console.log('TOP 12 (ưu tiên font lớn, padding lớn):');
  const seen = new Set();
  let shown = 0;
  for (const r of results) {
    const key = `${r.font}-${r.pad}-${r.badge}-${r.icon}`;
    if (seen.has(key)) continue;
    seen.add(key);
    console.log(`  font=${r.font}px icon=${r.icon}px badge=${r.badge}px gap=${r.gap}px pad=${r.pad}px → tổng ${r.total}px (dư ${r.room}px)`);
    if (++shown >= 12) break;
  }

  console.log('\nTOP 8 ưu tiên PAD lớn nhất (font >= 10):');
  const byPad = results.filter((r) => r.font >= 10).sort((a, b) => b.pad - a.pad || b.font - a.font);
  byPad.slice(0, 8).forEach((r) => console.log(
    `  font=${r.font}px icon=${r.icon}px badge=${r.badge}px gap=${r.gap}px pad=${r.pad}px → tổng ${r.total}px (dư ${r.room}px)`
  ));

  await browser.close();
})();
