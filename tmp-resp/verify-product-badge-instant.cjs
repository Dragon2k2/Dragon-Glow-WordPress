const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome' });
 try {
  for (const width of [390, 640, 768, 1024]) {
   const page = await browser.newPage({ viewport: { width, height: 900 } });
   let failAdd = false, failRemove = false, countRequests = 0;
   page.on('dialog', d => d.dismiss());
   await page.route('**/assets/js/product.js*', r => r.fulfill({ contentType: 'application/javascript', body: fs.readFileSync('wp-content/themes/dragon-glow/assets/js/product.js', 'utf8') }));
   await page.route('**/assets/css/single-product.css*', r => r.fulfill({ contentType: 'text/css', body: fs.readFileSync('wp-content/themes/dragon-glow/assets/css/single-product.css', 'utf8') }));
   await page.route('**/*admin-ajax.php*', async r => {
    const body = r.request().postData() || '';
    if (body.includes('dg_ajax_get_cart_count')) { countRequests++; return r.fulfill({ json: { success: true, data: { count: 6 } } }); }
    if (body.includes('dg_ajax_add_to_cart')) {
     await new Promise(resolve => setTimeout(resolve, 1200));
     return r.fulfill({ json: failAdd ? { success: false, data: { message: 'Add failed' } } : { success: true, data: { message: 'Added to bag!', cart_item_key: 'test-line', cart_item_quantity: 5, count: 9 } } });
    }
    if (body.includes('dg_ajax_remove_cart_items')) {
     await new Promise(resolve => setTimeout(resolve, 1200));
     return r.fulfill({ json: failRemove ? { success: false, data: { message: 'Remove failed' } } : { success: true, data: { count: 4 } } });
    }
    return r.continue();
   });
   await page.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/', { waitUntil: 'domcontentloaded', timeout: 60000 });
   const button = page.locator('#product-info form.cart .single_add_to_cart_button');
   await button.waitFor();
   await page.evaluate(() => { window.DGCartBadge.paint(6); document.querySelector('form.cart [name="quantity"]').value = '3'; });
   const badge = () => page.locator('.dg-cart-count').first().textContent();
   const ready = () => page.waitForFunction(() => !document.querySelector('.single_add_to_cart_button').disabled);
   await button.click();
   assert.equal(await badge(), '9', 'Add badge must change before the delayed server response');
   assert.equal(await button.isDisabled(), true);
   assert.equal(await button.locator('.dg-product-cart__loading-icon').count(), 1, 'Loading icon must appear immediately');
   assert.equal(await button.locator('.dg-product-cart__loading-icon').textContent(), 'sync');
   assert.equal(await button.locator('.dg-product-cart__loading-icon').evaluate(e => getComputedStyle(e).animationName), 'spin');
   await ready();
   assert.equal(await button.locator('.dg-product-cart__loading-icon').count(), 0);
   assert.equal(await button.getAttribute('aria-busy'), null);
   assert.equal(await badge(), '9');
   const initialRequests = countRequests;
   failRemove = true;
   await button.click();
   assert.equal(await badge(), '4', 'Remove must immediately subtract the entire five-unit cart line');
   assert.equal(await button.locator('.dg-product-cart__loading-label').textContent(), 'Removing...', 'Removal must show a loading label before the response');
   assert.equal(await button.getAttribute('aria-busy'), 'true');
   assert.equal(await button.isDisabled(), true);
   assert.equal(await button.locator('.dg-product-cart__loading-icon').count(), 1, 'Loading icon must appear immediately');
   assert.equal(await button.locator('.dg-product-cart__loading-icon').textContent(), 'sync');
   assert.equal(await button.locator('.dg-product-cart__loading-icon').evaluate(e => getComputedStyle(e).animationName), 'spin');
   await ready();
   assert.equal(await button.locator('.dg-product-cart__loading-icon').count(), 0);
   assert.equal(await button.getAttribute('aria-busy'), null);
   assert.equal(await badge(), '9', 'Failed removal rolls back');
   assert.ok(await button.evaluate(b => b.classList.contains('added')));
   failRemove = false;
   await page.emulateMedia({ reducedMotion: 'reduce' });
   await button.click();
   assert.equal(await button.locator('.dg-product-cart__loading-icon').evaluate(e => getComputedStyle(e).animationName), 'none');
   assert.equal(await badge(), '4');
   await ready();
   assert.equal(await badge(), '4');
   failAdd = true;
   await button.click();
   assert.equal(await badge(), '7');
   await ready();
   assert.equal(await badge(), '4', 'Failed add rolls back');
   assert.equal(countRequests, initialRequests, 'No extra cart-count request after add/remove');
   assert.deepEqual(await page.locator('.dg-cart-count').allTextContents(), await page.locator('.dg-cart-count').evaluateAll(es => es.map(() => '4')));
   console.log(`PASS ${width}px: immediate +3/-5 before response, server reconciliation, add/remove rollback, all badges synced, no count round-trip`);
   await page.close();
  }
 } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
