#!/usr/bin/env node
/** Debug: dump computed styles + a full-page screenshot to see why the nav is blank. */
const path = require('path');
const { chromium } = require('playwright');

const OUT = path.join(__dirname, 'nav-cart-badge');
const htmlPath = path.join(OUT, 'nav.html');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 390, height: 200 }, deviceScaleFactor: 2 });
  page.on('console', (m) => console.log('  [console:' + m.type() + ']', m.text()));
  page.on('pageerror', (e) => console.log('  [pageerror]', e.message));
  await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
  await page.waitForTimeout(2000);

  const info = await page.evaluate(() => {
    const nav = document.querySelector('.header-nav');
    const brand = document.querySelector('.dg-nav-brand-name');
    const actions = document.querySelector('.dg-nav-actions');
    const toggle = document.getElementById('dg-mobile-menu-toggle');
    const badge = toggle.querySelector('.dg-cart-count');
    const pick = (el) => {
      if (!el) return null;
      const cs = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      return {
        rect: { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height) },
        display: cs.display, visibility: cs.visibility, opacity: cs.opacity,
        color: cs.color, bg: cs.backgroundColor, fontSize: cs.fontSize,
        position: cs.position, zIndex: cs.zIndex,
      };
    };
    return {
      nav: pick(nav), brand: pick(brand), actions: pick(actions),
      toggle: pick(toggle), badge: pick(badge),
      bodyBg: getComputedStyle(document.body).backgroundColor,
      tailwindLoaded: typeof window.tailwind !== 'undefined',
    };
  });
  console.log(JSON.stringify(info, null, 2));
  await page.screenshot({ path: path.join(OUT, 'debug-full.png') });
  await browser.close();
})();
