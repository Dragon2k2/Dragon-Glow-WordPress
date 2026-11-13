const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shop/';
const WIDTHS = [1024, 1280];

function snapshot(btn) {
  const cs = getComputedStyle(btn);
  const icon = btn.querySelector('.material-symbols-outlined');
  const iconCs = icon ? getComputedStyle(icon) : null;
  return {
    bg: cs.backgroundColor,
    color: cs.color,
    transform: cs.transform,
    iconColor: iconCs ? iconCs.color : null,
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

    // Force the "added" state (no network).
    await page.evaluate(() => {
      const b = document.querySelector('.dg-add-to-cart');
      b.classList.add('dg-quick-add--added');
      const l = b.querySelector('.dg-quick-add__label');
      if (l) l.textContent = 'Added';
      const ic = b.querySelector('.material-symbols-outlined');
      if (ic) ic.textContent = 'check';
    });

    const btn = page.locator('.dg-add-to-cart').first();
    await btn.scrollIntoViewIfNeeded();
    await page.mouse.move(0, 0);
    await page.waitForTimeout(250);
    const rest = await page.evaluate(snapshot, await btn.elementHandle());

    const box = await btn.boundingBox();
    if (box) await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
    await page.waitForTimeout(700);
    const hovered = await page.evaluate(snapshot, await btn.elementHandle());

    console.log(`\n=== width ${w} (added state) ===`);
    console.log('rest   ', JSON.stringify(rest));
    console.log('hovered', JSON.stringify(hovered));
    console.log('bg same?      ', rest.bg === hovered.bg);
    console.log('color same?   ', rest.color === hovered.color);
    console.log('icon same?    ', rest.iconColor === hovered.iconColor);
    console.log('transform d?  ', rest.transform, '->', hovered.transform);
  }

  await browser.close();
})();
