const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome' });
 try {
  for (const width of [390, 640, 768, 1024]) {
   const page = await browser.newPage({ viewport: { width, height: 900 } });
   let requests = 0;
   let removals = 0;
   let failRemoval = false;
   page.on('dialog', dialog => dialog.dismiss());
   await page.route('**/assets/js/product.js*', route => route.fulfill({ contentType: 'application/javascript', body: fs.readFileSync('wp-content/themes/dragon-glow/assets/js/product.js', 'utf8') }));
   await page.route('**/*admin-ajax.php*', async route => {
    if ((route.request().postData() || '').includes('dg_ajax_add_to_cart')) {
     requests++;
     await new Promise(resolve => setTimeout(resolve, 150));
     return route.fulfill({ json: { success: true, data: { message: 'Added to bag!', cart_item_key: 'verification-only' } } });
    }
    if ((route.request().postData() || '').includes('dg_ajax_remove_cart_items')) {
     removals++;
     assert.ok(route.request().postData().includes('verification-only'), 'Remove the exact added cart key');
     await new Promise(resolve => setTimeout(resolve, 150));
     return route.fulfill({ json: failRemoval ? { success: false, data: { message: 'Please retry removal.' } } : { success: true, data: { count: 0, removed: 1 } } });
    }
    return route.continue();
   });
   await page.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/', { waitUntil: 'domcontentloaded', timeout: 60000 });
   const button = page.locator('#product-info form.cart .single_add_to_cart_button');
   await button.waitFor();
   const original = await button.textContent();
   await button.click();
   await page.waitForFunction(() => document.querySelector('.single_add_to_cart_button').classList.contains('added'));
   await page.waitForTimeout(2500);
   assert.ok(await button.evaluate(b => b.classList.contains('added')), 'Added state must persist beyond the old 2-second timeout');
   assert.equal(await button.isEnabled(), true, 'Added button must be clickable');
   assert.equal(requests, 1);
   await button.click();
   await page.waitForFunction(() => !document.querySelector('.single_add_to_cart_button').disabled);
   assert.equal(removals, 1, 'Confirmation click must remove from the server cart');
   assert.equal(await button.textContent(), original);
   assert.equal(await button.evaluate(b => b.classList.contains('added')), false);
   assert.equal(requests, 1, 'Reset click must not send an add request');
   await button.press('Enter');
   await page.waitForFunction(() => document.querySelector('.single_add_to_cart_button').classList.contains('added'));
   assert.equal(requests, 2, 'Next activation must add again');
   failRemoval = true;
   await button.press('Space');
   await page.waitForFunction(() => !document.querySelector('.single_add_to_cart_button').disabled);
   assert.ok(await button.evaluate(b => b.classList.contains('added')), 'Failed removal must preserve confirmation for retry');
   failRemoval = false;
   await button.press('Space');
   await page.waitForFunction(() => !document.querySelector('.single_add_to_cart_button').disabled);
   assert.equal(removals, 3);
   assert.equal(await button.textContent(), original);
   assert.equal(requests, 2, 'Keyboard reset must not add');
   assert.equal(await button.evaluate(b => b.classList.contains('loading')), false);
   console.log(`PASS ${width}px: persistent confirmation, click reset, Enter add, Space reset, exactly 2 requests`);
   await page.close();
  }
 } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
