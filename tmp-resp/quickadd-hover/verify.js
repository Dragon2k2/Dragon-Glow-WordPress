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
    await page.waitForTimeout(400);

    const btn = page.locator('.dg-add-to-cart').first();
    await btn.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await page.waitForTimeout(200);
    const rest = await page.evaluate(snapshot, await btn.elementHandle());

    const box = await btn.boundingBox();
    if (box) await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
    await page.waitForTimeout(600);
    const hovered = await page.evaluate(snapshot, await btn.elementHandle());

    // Also test the "added" state hover.
    await page.mouse.move(0, 0);
    await page.evaluate(() => {
      const b = document.querySelector('.dg-add-to-cart');
      b.classList.add('dg-quick-add--added');
      const l = b.querySelector('.dg-quick-add__label');
      if (l) l.textContent = 'Added';
    });
    await page.waitForTimeout(200);
    const addedRest = await page.evaluate(snapshot, await btn.elementHandle());
    const box2 = await btn.boundingBox();
    if (box2) await page.mouse.move(box2.x + box2.width / 2, box2.y + box2.height / 2);
    await page.waitForTimeout(600);
    const addedHovered = await page.evaluate(snapshot, await btn.elementHandle());

    console.log(`\n=== width ${w} ===`);
    console.log('default rest   ', JSON.stringify(rest));
    console.log('default hover  ', JSON.stringify(hovered));
    console.log('added   rest   ', JSON.stringify(addedRest));
    console.log('added   hover  ', JSON.stringify(addedHovered));
  }

  await browser.close();
})();
