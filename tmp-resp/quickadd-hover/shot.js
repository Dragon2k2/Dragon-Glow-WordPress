const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shop/';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  for (const w of [390, 1280]) {
    await page.setViewportSize({ width: w, height: 900 });
    await page.goto(URL + '?cb=' + Date.now(), { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.dg-add-to-cart', { timeout: 30000 });
    await page.waitForTimeout(500);

    const btn = page.locator('.dg-add-to-cart').first();
    await btn.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await page.waitForTimeout(300);

    const box = await btn.boundingBox();
    if (box) await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
    await page.waitForTimeout(700);

    await page.screenshot({ path: `tmp-resp/quickadd-hover/hover-${w}.png` });
    console.log('shot ' + w);
  }

  await browser.close();
})();
