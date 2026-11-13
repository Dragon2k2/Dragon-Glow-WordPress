const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/';
const WIDTHS = [320, 390, 430, 639, 640, 768, 1023, 1024, 1280];

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  for (const w of WIDTHS) {
    await page.setViewportSize({ width: w, height: 900 });
    await page.goto(URL + '?cb=' + Date.now(), { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('#product-tabs', { timeout: 30000 });
    const data = await page.evaluate(() => {
      const s = document.getElementById('product-tabs');
      const cs = getComputedStyle(s);
      const prev = s.previousElementSibling;
      const prevRect = prev ? prev.getBoundingClientRect() : null;
      const rect = s.getBoundingClientRect();
      return {
        innerWidth: window.innerWidth,
        marginTop: cs.marginTop,
        token: getComputedStyle(document.documentElement).getPropertyValue('--product-tabs-gap-mobile').trim(),
        gapToPrev: prevRect ? Math.round(rect.top - prevRect.bottom) : null,
      };
    });
    console.log(`w=${String(w).padEnd(5)} -> innerWidth=${data.innerWidth} marginTop=${data.marginTop} gapFromPrev=${data.gapToPrev}px token=${data.token}`);
  }

  await browser.close();
})();
