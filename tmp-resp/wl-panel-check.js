#!/usr/bin/env node
/** Verify sort panel mở ở mobile không tràn viewport. */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

(async () => {
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  const browser = await chromium.launch();
  for (const w of [320, 390, 430]) {
    const page = await browser.newPage({ viewport: { width: w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(300);
    const r = await page.evaluate(() => {
      const panel = document.querySelector('.dg-wishlist-toolbar__sort-panel');
      panel.removeAttribute('hidden');
      const pr = panel.getBoundingClientRect();
      const tb = document.querySelector('.dg-wishlist-toolbar').getBoundingClientRect();
      return {
        panelL: Math.round(pr.left), panelR: Math.round(pr.right),
        panelW: Math.round(pr.width),
        vw: window.innerWidth,
        toolbarL: Math.round(tb.left), toolbarR: Math.round(tb.right),
      };
    });
    const ok = r.panelL >= 0 && r.panelR <= r.vw;
    console.log(`${w}px → panel [${r.panelL}..${r.panelR}] w=${r.panelW} viewport=${r.vw}  ${ok ? 'OK' : 'TRÀN!'}`);
    await page.screenshot({ path: path.join(OUT, `panel-${w}.png`), fullPage: true });
    await page.close();
  }
  await browser.close();
})();
