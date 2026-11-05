const { chromium } = require('playwright');
const path = require('path');
(async () => {
  const b = await chromium.launch();
  for (const w of [320, 390, 639]) {
    const p = await b.newPage({ viewport: { width: w, height: 900 } });
    await p.goto('file:///' + path.resolve('tmp-resp/wl-label/wl-label.html').replace(/\\/g, '/'));
    await p.waitForTimeout(400);
    const info = await p.evaluate(() => {
      const label = document.querySelector('.dg-wishlist-hero__stat-label--stacked');
      const spans = Array.from(label.querySelectorAll('span'));
      const a = spans[0].getBoundingClientRect();
      const c = spans[1].getBoundingClientRect();
      const cs = getComputedStyle(spans[1]);
      return {
        gap: Math.round(c.left - a.right),
        marginLeft: cs.marginLeft,
        text: spans.map((s) => s.textContent.trim()).join(' | '),
        sameLine: Math.abs(a.top - c.top) < 1,
      };
    });
    console.log(`${w}px → "${info.text}" | cùng dòng: ${info.sameLine} | khoảng cách giữa 2 từ: ${info.gap}px (margin-left ${info.marginLeft})`);
    const el = await p.$('.dg-wishlist-hero__stat:nth-child(3)');
    await el.screenshot({ path: `tmp-resp/wl-label/stacked-${w}.png` });
    await p.close();
  }
  await b.close();
})();
