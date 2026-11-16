const {chromium}=require('playwright');
const fs=require('node:fs');
(async()=>{
 const browser=await chromium.launch({channel:'msedge',headless:true});
 try {
 const page=await browser.newPage();
 const base='wp-content/themes/dragon-glow/';
 await page.setContent('<button><span class="material-symbols-outlined dg-cart-checkout__spinner" aria-hidden="true">progress_activity</span> Processing…</button>');
 for(const file of ['style.css','assets/css/material-symbols.css','assets/css/main.css','assets/css/cart.css']) await page.addStyleTag({content:fs.readFileSync(base+file,'utf8')});
 for(const width of [390,640,768,1024]) {
  await page.setViewportSize({width,height:600});
  const result=await page.locator('.dg-cart-checkout__spinner').evaluate(async el=>{
   const anim=el.getAnimations()[0];
   if(!anim) throw Error('Missing animation');
   anim.pause();anim.currentTime=100; const first=getComputedStyle(el).transform;
   anim.currentTime=300; const second=getComputedStyle(el).transform;
   const css=getComputedStyle(el);
   return {first,second,duration:css.animationDuration,easing:css.animationTimingFunction,size:css.fontSize};
  });
  if(result.first===result.second || result.easing!=='linear') throw Error('Spinner does not rotate smoothly');
  console.log(width,JSON.stringify(result));
 }
 await page.emulateMedia({reducedMotion:'reduce'});
 const reduced=await page.locator('.dg-cart-checkout__spinner').evaluate(el=>({name:getComputedStyle(el).animationName,animations:el.getAnimations().length}));
 if(reduced.name!=='none'||reduced.animations) throw Error('Reduced motion failed');
 console.log('Reduced motion PASS',JSON.stringify(reduced));
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
