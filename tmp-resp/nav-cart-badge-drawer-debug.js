#!/usr/bin/env node
/** Debug the drawer harness rendering. */
const path = require('path');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 390, height: 700 }, deviceScaleFactor: 2 });
  page.on('pageerror', (e) => console.log('[pageerror]', e.message));
  await page.goto('file:///' + path.join(OUT, 'drawer.html').replace(/\\/g, '/'));
  await page.waitForTimeout(2000);

  const info = await page.evaluate(() => {
    const drawer = document.getElementById('dg-mobile-menu');
    const link = document.querySelector('.dg-mobile-menu-item--cart a');
    const badge = link.querySelector('.dg-cart-count');
    const pick = (el) => {
      const cs = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      return { rect: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) },
        display: cs.display, visibility: cs.visibility, opacity: cs.opacity, color: cs.color, bg: cs.backgroundColor, position: cs.position };
    };
    return {
      drawer: pick(drawer),
      link: pick(link),
      badge: pick(badge),
      drawerInline: drawer.getAttribute('style'),
    };
  });
  console.log(JSON.stringify(info, null, 2));
  await page.screenshot({ path: path.join(OUT, 'drawer-debug.png'), fullPage: true });
  await browser.close();
})();
