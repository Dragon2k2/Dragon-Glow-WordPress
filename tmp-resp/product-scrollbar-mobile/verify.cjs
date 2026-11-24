const {chromium}=require('playwright');
const fs=require('node:fs');
const assert=require('node:assert/strict');
const path=require('node:path');
const theme=path.resolve(__dirname,'../../wp-content/themes/dragon-glow');
(async()=>{
 const browser=await chromium.launch({channel:'chrome'});
 try {
 const page=await browser.newPage();
 await page.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/',{waitUntil:'domcontentloaded'});
 await page.locator('[data-dg-thumb]').first().waitFor();
 await page.emulateMedia({reducedMotion:'reduce'});
 await page.evaluate(async()=>{await document.fonts.ready;document.querySelector('[data-dg-thumb]').parentElement.classList.remove('dg-product-thumbnails');});
 const measure=()=>page.evaluate(()=>{
  const el=document.querySelector('[data-dg-thumb]').parentElement;
  const get=p=>{const s=getComputedStyle(el,p);return {height:s.height,width:s.width,background:s.backgroundColor,radius:s.borderRadius,border:s.borderWidth,clip:s.backgroundClip,margin:s.marginInline};};
  const box=el.getBoundingClientRect();
  return {bar:get('::-webkit-scrollbar'),track:get('::-webkit-scrollbar-track'),thumb:get('::-webkit-scrollbar-thumb'),layout:{w:box.width,h:box.height},overflow:el.scrollWidth>el.clientWidth};
 });
 const widths=[320,390,639,640,768,1024,1280]; const before={};
 for(const width of widths){await page.setViewportSize({width,height:900});before[width]=await measure();}
 await page.evaluate(()=>document.querySelector('[data-dg-thumb]').parentElement.classList.add('dg-product-thumbnails'));
 await page.addStyleTag({content:fs.readFileSync(path.join(theme,'style.css'),'utf8')});
 await page.addStyleTag({content:fs.readFileSync(path.join(theme,'assets/css/single-product.css'),'utf8')});
 for(const width of widths){
  await page.setViewportSize({width,height:900});const after=await measure();
  if(width<640){
   assert.equal(after.bar.height,'8px');assert.equal(after.thumb.border,'2px');assert.equal(after.thumb.radius,'8px');assert.equal(after.track.margin,'12px');
   assert.equal(after.thumb.background,await page.evaluate(()=>{const s=getComputedStyle(document.documentElement);const e=document.createElement('span');e.style.color=s.getPropertyValue('--gold-500');document.body.append(e);const c=getComputedStyle(e).color;e.remove();return c;}));
   await page.evaluate(()=>{const e=document.querySelector('[data-dg-thumb]').parentElement;e.scrollLeft=100;});
   if(after.overflow)assert.ok(await page.evaluate(()=>document.querySelector('.dg-product-thumbnails').scrollLeft>0));
  }else{assert.deepEqual(after,before[width],`Tablet/desktop unchanged at ${width}`);}
  console.log(JSON.stringify({width,before:before[width],after}));
 }
 console.log('PASS: mobile scrollbar 320/390/639, tablet/desktop unchanged 640/768/1024/1280, horizontal scrolling');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
