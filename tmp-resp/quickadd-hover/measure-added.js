const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shop/';
const WIDTHS = [390, 768, 1024];

function snapshot(btn) {
  const cs = getComputedStyle(btn);
  const before = getComputedStyle(btn, '::before');
  return {
    bg: cs.backgroundColor,
    color: cs.color,
    transform: cs.transform,
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

    // Force the "added" state without hitting the network.
    await page.evaluate(() => {
      const btn = document.querySelector('.dg-add-to-cart');
      btn.classList.add('dg-quick-add--added');
      const label = btn.querySelector('.dg-quick-add__label');
      if (label) label.textContent = 'Added';
    });

    const btn = page.locator('.dg-add-to-cart').first();
    await btn.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await page.waitForTimeout(200);
    const rest = await page.evaluate(snapshot, await btn.elementHandle());

    const box = await btn.boundingBox();
    if (box) await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
    await page.waitForTimeout(600);
    const hovered = await page.evaluate(snapshot, await btn.elementHandle());

    console.log(`\n=== width ${w} (added state) ===`);
    console.log('rest    ', JSON.stringify(rest));
    console.log('hovered ', JSON.stringify(hovered));
  }

  await browser.close();
})();
