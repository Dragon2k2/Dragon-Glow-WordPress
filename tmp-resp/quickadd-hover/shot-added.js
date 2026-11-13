const { chromium } = require('playwright');

const URL = 'https://dragonglow.freehosting.dev/shop/';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome' });
  const ctx = await browser.newContext();
  const page = await ctx.newPage();

  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto(URL + '?cb=' + Date.now(), { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('.dg-add-to-cart', { timeout: 30000 });
  await page.waitForTimeout(400);

  // Force the "added" state on the first card only.
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
  await page.waitForTimeout(300);

  // Crop the card region for a close-up.
  const card = page.locator('.dg-product-card').first();
  const cardBox = await card.boundingBox();
  await page.screenshot({
    path: 'tmp-resp/quickadd-hover/added-rest-1280.png',
    clip: cardBox,
  });

  const box = await btn.boundingBox();
  if (box) await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
  await page.waitForTimeout(700);
  await page.screenshot({
    path: 'tmp-resp/quickadd-hover/added-hover-1280.png',
    clip: cardBox,
  });

  console.log('shots done');
  await browser.close();
})();
