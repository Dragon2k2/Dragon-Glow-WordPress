const { chromium } = require('playwright');
const path = require('path');

const FILE = 'file:///' + path.resolve(__dirname, 'card.html').replace(/\\/g, '/');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  for (const w of [390, 768, 1280]) {
    await page.setViewportSize({ width: w, height: 700 });
    await page.goto(FILE + '?cb=' + Date.now(), { waitUntil: 'load' });
    await page.waitForSelector('.dg-wishlist-card__select');
    await page.waitForTimeout(500);
    await page.mouse.move(0, 0);
    await page.waitForTimeout(300);
    await page.screenshot({ path: `tmp-resp/wl-select/select-${w}.png` });
    console.log('shot ' + w);
  }

  await browser.close();
})();
