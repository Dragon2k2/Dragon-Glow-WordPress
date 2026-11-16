const {chromium}=require('playwright');
const fs=require('node:fs');const assert=require('node:assert/strict');
(async()=>{const browser=await chromium.launch({channel:'msedge'});try{
const page=await browser.newPage();
const base='wp-content/themes/dragon-glow/';
await page.setContent('<body class="woocommerce-checkout"><form class="checkout"><button type="submit" class="dg-place-order" id="place_order" name="woocommerce_checkout_place_order" value="Place Order"><span>lock</span> Place Order</button></form></body>');
await page.addStyleTag({content:fs.readFileSync(base+'style.css','utf8')});await page.addStyleTag({content:fs.readFileSync(base+'assets/css/checkout-page.css','utf8')});
const source=fs.readFileSync(base+'assets/js/checkout.js','utf8');const fn=source.slice(source.indexOf('    function bindPlaceOrderLoading()'),source.indexOf('    // ── Re-bind after each WooCommerce AJAX refresh'));
await page.evaluate(code=>{window.eval(code+';bindPlaceOrderLoading();');},fn);
assert.equal(await page.locator('.dg-place-order__spinner').count(),0);
await page.evaluate(()=>document.querySelector('form').classList.add('processing'));
await page.waitForSelector('.dg-place-order__spinner');
for(const width of [390,640,768,1024]){await page.setViewportSize({width,height:800});const state=await page.locator('button').evaluate(el=>{const icon=el.querySelector('.dg-place-order__spinner');const animation=icon.getAnimations()[0];animation.pause();animation.currentTime=100;const a=getComputedStyle(icon).transform;animation.currentTime=400;return {busy:el.getAttribute('aria-busy'),disabled:el.disabled,changed:a!==getComputedStyle(icon).transform,label:el.textContent,duration:getComputedStyle(icon).animationDuration,formValue:new FormData(el.form,el).get(el.name)};});assert.equal(state.changed,true);assert.equal(state.busy,'true');assert.equal(state.disabled,false);assert.equal(state.formValue,'Place Order');console.log(width,'PASS',JSON.stringify(state));}
await page.evaluate(()=>document.querySelector('form').classList.remove('processing'));await page.waitForFunction(()=>!document.querySelector('[aria-busy]'));
assert.match(await page.locator('button').textContent(),/Place Order/);
await page.evaluate(()=>document.querySelector('form').classList.add('processing'));await page.waitForSelector('.dg-place-order__spinner');
await page.evaluate(()=>{document.querySelector('button').outerHTML='<button class="dg-place-order" id="place_order">Place Order refreshed</button>';});await page.waitForSelector('.dg-place-order__spinner');
await page.emulateMedia({reducedMotion:'reduce'});assert.equal(await page.locator('.dg-place-order__spinner').evaluate(el=>getComputedStyle(el).animationName),'none');
await page.evaluate(()=>window.dispatchEvent(new PageTransitionEvent('pageshow',{persisted:true})));await page.waitForFunction(()=>!document.querySelector('[aria-busy]'));assert.match(await page.locator('button').textContent(),/refreshed/);
console.log('PASS: error recovery, retry, fragment replacement, reduced motion and simulated bfcache restore');
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1});
