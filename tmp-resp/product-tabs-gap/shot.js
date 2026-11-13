const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  for (const w of [390, 768, 1280]) {
    await page.setViewportSize({ width: w, height: 900 });
    await page.goto(URL + '?cb=' + Date.now(), { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#product-tabs', { timeout: 30000 });
    await page.evaluate(() => {
      const s = document.getElementById('product-tabs');
      s.scrollIntoView({ block: 'start' });
      window.scrollBy(0, -80);
    });
    await page.waitForTimeout(600);
    await page.screenshot({ path: `tmp-resp/product-tabs-gap/tabs-${w}.png` });
    console.log('shot ' + w);
  }

  await browser.close();
})();
