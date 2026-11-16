const {chromium}=require('playwright');
const fs=require('node:fs');
const assert=require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({channel:'msedge'});
 try {
 const source=fs.readFileSync('wp-content/themes/dragon-glow/assets/js/cart.js','utf8');
 const fn=source.slice(source.indexOf('    function checkoutWithSelected('),source.indexOf('    function clearSelection('));
 for(const scenario of ['success','server-error','network-error']) {
  const page=await browser.newPage();
  let releaseNavigation;
  const navigationGate=new Promise(resolve=>{releaseNavigation=resolve});
  let navigationStarted;
  const started=new Promise(resolve=>{navigationStarted=resolve});
  await page.route('https://cart.test/**',async route=>{
   if(route.request().url().includes('/checkout/')) {navigationStarted();await navigationGate;}
   await route.fulfill({contentType:'text/html',body:'<button id="checkout">Checkout with selected</button>'});
  });
  await page.goto('https://cart.test/cart/');
  await page.evaluate(({fn,scenario})=>{
   window.dgAjax={url:'/ajax'};
   window.i18n={};window.showToast=()=>{};
   window.getAjaxUrl=()=>'/ajax';window.getNonce=()=> 'test';
   window.fetch=()=>scenario==='network-error'?Promise.reject(Error('offline')):Promise.resolve({json:()=>Promise.resolve({success:scenario==='success',data:{redirect:'/checkout/'}})});
   window.eval(fn.replace('window.location.href = redirect;', 'window.pendingRedirect = redirect;')+';window.runCheckout=checkoutWithSelected;');
   window.runCheckout(['item'],document.querySelector('button'));
  },{fn,scenario});
  if(scenario==='success') await page.waitForFunction(()=>window.pendingRedirect==='/checkout/');
  else await page.waitForFunction(()=>!document.querySelector('button').disabled);
  const state=await page.evaluate(()=>{const el=document.querySelector('button');return {disabled:el.disabled,busy:el.getAttribute('aria-busy'),spinner:!!el.querySelector('.dg-cart-checkout__spinner'),text:el.textContent};});
  if(scenario==='success') {
   assert.equal(state.disabled,true);assert.equal(state.busy,'true');assert.equal(state.spinner,true);
   await page.evaluate(()=>window.dispatchEvent(new PageTransitionEvent('pageshow',{persisted:true})));
   assert.equal(await page.evaluate(()=>!document.querySelector('button').disabled),true);
   assert.equal(await page.evaluate(()=>document.querySelector('button').getAttribute('aria-busy')),null);
  } else {assert.equal(state.disabled,false);assert.equal(state.spinner,false);assert.equal(state.text,'Checkout with selected');assert.equal(state.busy,null);}
  console.log(scenario,'PASS',JSON.stringify(state));
  releaseNavigation();await page.close();
 }
 } finally {await browser.close();}
})().catch(err=>{console.error(err);process.exitCode=1});
