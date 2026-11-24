const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome' });
 try {
  const context = await browser.newContext();
  const page = await context.newPage();
  await page.route('**/assets/js/product.js*', route => route.fulfill({ contentType: 'application/javascript', body: fs.readFileSync('wp-content/themes/dragon-glow/assets/js/product.js', 'utf8') }));
  await page.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/', { waitUntil: 'domcontentloaded', timeout: 60000 });
  const button = page.locator('#product-info form.cart .single_add_to_cart_button');
  await button.click();
  await page.waitForFunction(() => document.querySelector('.single_add_to_cart_button').classList.contains('added'));
  const identifiers = () => page.evaluate(async () => { const r = await window.DGCart.getIdentifiers(); return r; });
  const before = await identifiers();
  console.log('Cart before removal: ' + JSON.stringify(before));
  assert.equal(before.success, true);
  assert.ok(before.data.product_ids.map(Number).includes(44));
  await button.click();
  await page.waitForFunction(() => { const b = document.querySelector('.single_add_to_cart_button'); return !b.disabled && !b.classList.contains('added'); });
  const after = await identifiers();
  console.log('Cart after removal: ' + JSON.stringify(after));
  assert.equal(after.success, true);
  assert.ok(!after.data.product_ids.map(Number).includes(44));
  await page.goto('https://dragonglow.freehosting.dev/cart/', { waitUntil: 'domcontentloaded', timeout: 60000 });
  assert.equal(await page.locator('main').getByText('Browluxe Define Brow Mascara', { exact: true }).count(), 0);
  console.log('PASS: real server add/remove in isolated guest session; product absent on /cart/ after reload');
 } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
