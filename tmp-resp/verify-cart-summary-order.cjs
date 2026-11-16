const { chromium } = require('playwright');
const path = require('node:path');
(async () => {
 const browser = await chromium.launch({channel:'chrome',headless:true});
 const page = await browser.newPage();
 await page.goto('https://dragonglow.freehosting.dev/cart/',{waitUntil:'networkidle'});
 await page.evaluate(() => {
  document.body.innerHTML = '<main style="padding:24px;max-width:1280px;margin:auto"><div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start" id="dg-cart-view"><div class="dg-cart-products lg:col-span-8 space-y-6"><div class="dg-glass-panel rounded-3xl p-6 md:p-8 overflow-hidden"><h2>Cart products</h2><div style="height:220px">Product list</div></div><div class="flex flex-wrap gap-3 pt-1 dg-cart-trust-badges"><span class="dg-trust-badge">Vegan Formula</span><span class="dg-trust-badge">Cruelty-Free</span><span class="dg-trust-badge">Organic Ingredients</span></div></div><aside class="dg-cart-summary lg:col-span-4 lg:sticky lg:top-28"><div class="dg-glass-panel rounded-3xl p-6 md:p-8 flex flex-col gap-6"><h2>Order Summary</h2><div style="height:300px">Subtotal / Shipping / Total / Checkout</div></div></aside></div></main>';
 });
 await page.addStyleTag({path:path.resolve('wp-content/themes/dragon-glow/assets/css/cart.css')});
 const toggle = enabled => page.evaluate(enabled => {
  let s=document.getElementById('disable-mobile-order');
  if(!s){s=document.createElement('style');s.id='disable-mobile-order';s.textContent='@media(max-width:1023px){.dg-cart-products{display:block!important}.dg-cart-summary{grid-row:auto!important}.dg-cart-products>.dg-cart-trust-badges{grid-row:auto!important;margin-block:revert-layer!important}}';document.head.append(s);}
  s.disabled=enabled;
 },enabled);
 const measure = () => page.evaluate(() => {
  const rect=s=>{const r=document.querySelector(s).getBoundingClientRect();return {x:r.x,y:r.y,w:r.width,h:r.height}};
  return {products:rect('.dg-cart-products > .dg-glass-panel'),summary:rect('.dg-cart-summary'),badges:rect('.dg-cart-trust-badges'),overflow:document.documentElement.scrollWidth>innerWidth};
 });
 for(const width of [320,390,640,768,1023,1024,1280,1440]){
  await page.setViewportSize({width,height:1100});
  await toggle(false);const before=await measure();
  await toggle(true);const after=await measure();
  const pass=width<1024?after.summary.y>=after.products.y+after.products.h&&after.badges.y>=after.summary.y+after.summary.h&&!after.overflow:JSON.stringify(before)===JSON.stringify(after);
  console.log(JSON.stringify({width,before,after,pass}));if(!pass)throw new Error('Failed at '+width);
 }
 await page.emulateMedia({reducedMotion:'reduce'});
 await page.setViewportSize({width:390,height:1100});
 await page.screenshot({path:'tmp-resp/cart-summary-order-390.png',fullPage:true});
 console.log('Reduced motion layout:',await measure());
 await browser.close();
})().catch(e=>{console.error(e);process.exitCode=1});
