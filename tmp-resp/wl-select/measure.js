const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file:///' + path.resolve(__dirname, 'card.html').replace(/\\/g, '/');
const WIDTHS = [390, 640, 767, 768, 1023, 1024, 1280];

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  for (const w of WIDTHS) {
    await page.setViewportSize({ width: w, height: 900 });
    await page.goto(FILE + '?cb=' + Date.now(), { waitUntil: 'load' });
    await page.waitForSelector('.dg-wishlist-card__select');
    await page.waitForTimeout(400);

    const read = () => page.evaluate(() => {
      const el = document.querySelector('.dg-wishlist-card__select');
      const cs = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      return { opacity: cs.opacity, transform: cs.transform, w: Math.round(r.width), h: Math.round(r.height) };
    });

    await page.mouse.move(0, 0);
    await page.waitForTimeout(200);
    const rest = await read();

    const box = await page.locator('.dg-wishlist-card__select').boundingBox();
    if (box) await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
    await page.waitForTimeout(500);
    const hovered = await read();

    console.log(`w=${String(w).padEnd(5)} rest=${JSON.stringify(rest)}  hover=${JSON.stringify(hovered)}`);
  }

  await browser.close();
})();
