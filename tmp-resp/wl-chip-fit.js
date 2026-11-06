#!/usr/bin/env node
/** Tính bề rộng chữ thật để chọn font-size + padding + icon vừa khít ở 320px. */
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

  const r = await page.evaluate(() => {
    const c = document.createElement('canvas').getContext('2d');
    const F = '"Plus Jakarta Sans", sans-serif';
    const texts = ['All items', 'In stock', 'On sale'];
    const out = { text: {}, chip: {} };

    // segment: padding 4px + border 1px mỗi cạnh; grid gap 4px
    const seg = document.querySelector('.dg-wishlist-toolbar__segment');
    const segCs = getComputedStyle(seg);
    const segInner = seg.getBoundingClientRect().width
      - parseFloat(segCs.paddingLeft) - parseFloat(segCs.paddingRight)
      - parseFloat(segCs.borderLeftWidth) - parseFloat(segCs.borderRightWidth);
    const gridGap = 4;
    const chipAvail = (segInner - gridGap * 2) / 3;

    for (const fs_ of [13, 12, 11, 10]) {
      c.font = `600 ${fs_}px ${F}`;
      out.text[fs_] = {};
      for (const t of texts) out.text[fs_][t] = Math.ceil(c.measureText(t).width);
    }
    out.chipAvail = Math.round(chipAvail);
    // count badge
    const badge = document.querySelector('.dg-wishlist-toolbar__count');
    const bcs = getComputedStyle(badge);
    out.badge = { minW: bcs.minWidth, pad: bcs.paddingLeft + '|' + bcs.paddingRight, fs: bcs.fontSize };
    out.segInner = Math.round(segInner);
    return out;
  });

  console.log(`\n=== 320px ===`);
  console.log(`segment inner: ${r.segInner}px → chip khả dụng: ${r.chipAvail}px`);
  console.log(`count badge: minW=${r.badge.minW} pad=${r.badge.pad} fs=${r.badge.fs}`);
  console.log('\nBề rộng chữ (px):');
  for (const fs_ of Object.keys(r.text)) {
    console.log(`  ${fs_}px: ${JSON.stringify(r.text[fs_])}`);
  }

  console.log('\n=== Dự trữ: chip = padL + icon + gap + text + gap + badge + padR ===');
  for (const fs_ of [12, 11, 10]) {
    for (const pad of [8, 6, 5, 4]) {
      for (const icon of [0, 16]) {
        const g = 4;
        const badge = 20;
        const need = pad + icon + (icon ? g : 0) + r.text[fs_]['In stock'] + g + badge + pad;
        const ok = need <= r.chipAvail;
        console.log(`  fs=${fs_} pad=${pad}px icon=${icon}px → cần ${Math.round(need)}px ${ok ? '✓' : '✗'}`);
      }
    }
  }
  await browser.close();
})();
