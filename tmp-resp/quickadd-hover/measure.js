const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shop/';
const WIDTHS = [390, 768, 1024, 1280];

function snapshot(btn) {
  const cs = getComputedStyle(btn);
  const before = getComputedStyle(btn, '::before');
  return {
    bg: cs.backgroundColor,
    color: cs.color,
    transform: cs.transform,
    opacity: cs.opacity,
    cursor: cs.cursor,
    sheenLeft: before.left,
    sheenTransition: before.transitionDuration,
  };
}

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  for (const w of WIDTHS) {
    await page.setViewportSize({ width: w, height: 900 });
    await page.goto(URL + '?cb=' + Date.now(), { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.dg-add-to-cart', { timeout: 30000 });
    await page.waitForTimeout(500);

    const btn = page.locator('.dg-add-to-cart').first();
    await btn.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await page.waitForTimeout(200);

    const rest = await page.evaluate(snapshot, await btn.elementHandle());

    // Hover the button
    const box = await btn.boundingBox();
    if (box) {
      await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
    }
    await page.waitForTimeout(600);

    const hovered = await page.evaluate(snapshot, await btn.elementHandle());

    // Tap (touch-like) to see the sticky hover state
    await page.mouse.move(0, 0);
    await page.waitForTimeout(200);

    console.log(`\n=== width ${w} ===`);
    console.log('rest    ', JSON.stringify(rest));
    console.log('hovered ', JSON.stringify(hovered));
  }

  await browser.close();
})();
