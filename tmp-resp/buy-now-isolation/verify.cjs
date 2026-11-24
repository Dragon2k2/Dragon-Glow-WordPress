const {chromium}=require('playwright');
const path=require('node:path');
const assert=require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({channel:'chrome'});
 try{
 const page=await browser.newPage();
 await page.setContent('<form class="cart"><input name="quantity" value="3"><button type="button" data-buy-now data-product-id="47"><span class="dg-buy-now-icon"></span><span class="dg-buy-now-label">Buy Now</span></button></form>');
 await page.evaluate(()=>{window.dgAjax={url:'https://buy-now.test/ajax',nonce:'test'};window.requests=[];window.fetch=async(url,options)=>{window.requests.push(Object.fromEntries(options.body));return {json:async()=>({success:false,data:{message:'Test response'}})};};});
 await page.addScriptTag({path:path.resolve(__dirname,'../../wp-content/themes/dragon-glow/assets/js/buy-now.js')});
 await page.evaluate(()=>document.dispatchEvent(new Event('DOMContentLoaded')));
 await page.locator('[data-buy-now]').click();
 assert.equal(await page.evaluate(()=>window.requests[0].quantity),'3');
 assert.equal(await page.evaluate(()=>window.requests[0].product_id),'47');
 await page.locator('form.cart').evaluate(el=>el.remove());
 await page.evaluate(()=>{document.body.innerHTML='<span id="dg-qty-display">2</span><button type="button" data-buy-now data-product-id="46">Buy Now</button>';document.dispatchEvent(new Event('DOMContentLoaded'));});
 await page.locator('[data-buy-now]').click();
 assert.equal(await page.evaluate(()=>window.requests[1].quantity),'2');
 console.log('PASS: WC quantity input and legacy quantity display submit requested product and amount');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
