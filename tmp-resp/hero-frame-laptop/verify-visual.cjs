const {chromium}=require('playwright');
const fs=require('node:fs');
const path=require('node:path');
const assert=require('node:assert/strict');
(async()=>{
 const theme=path.resolve(__dirname,'../../wp-content/themes/dragon-glow');
 const css=fs.readFileSync(path.join(theme,'assets/css/home.css'),'utf8');
 const tokens=fs.readFileSync(path.join(theme,'style.css'),'utf8');
 const baseline=css.replace(/        width: calc\(100% - var\(--hero-visual-inset-laptop\)\);\r?\n        justify-self: start;\r?\n/,'');
 const browser=await chromium.launch({channel:'chrome',headless:true});
 try{
 const page=await browser.newPage({reducedMotion:'reduce'});
 await page.route('**/dragon-glow/assets/css/home.css*',r=>r.fulfill({contentType:'text/css',body:baseline}));
 await page.route('**/dragon-glow/style.css*',r=>r.fulfill({contentType:'text/css',body:tokens}));
 await page.goto('https://dragonglow.freehosting.dev/',{waitUntil:'networkidle',timeout:60000});
 await page.evaluate(()=>document.fonts.ready);
 async function measure(w){
 await page.setViewportSize({width:w,height:1000});
 await page.evaluate(()=>new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r))));
 return page.evaluate(()=>{
 const v=document.querySelector('.dg-home-hero__visual'),f=v.querySelector('.dg-home-hero__product-frame'),o=v.querySelector('.dg-home-hero__orb');
 const vr=v.getBoundingClientRect(),fr=f.getBoundingClientRect(),or=o.getBoundingClientRect();
 return {visualWidth:vr.width,visualX:vr.x,frameWidth:fr.width,frameHeight:fr.height,orbWidth:getComputedStyle(o).width,edgeGap:innerWidth-or.right,overflow:Math.max(0,document.documentElement.scrollWidth-innerWidth)};
 });}
 const widths=[390,640,768,1023,1024,1025,1280,1440],before={};
 for(const w of widths)before[w]=await measure(w);
 console.log('BEFORE',JSON.stringify(before[1024]));
 if(css===baseline){console.log('BASELINE ONLY');return;}
 await page.unroute('**/dragon-glow/assets/css/home.css*');
 await page.route('**/dragon-glow/assets/css/home.css*',r=>r.fulfill({contentType:'text/css',body:css}));
 await page.reload({waitUntil:'networkidle'});
 await page.evaluate(()=>document.fonts.ready);
 const results=[];
 for(const w of widths){
 const after=await measure(w);
 if(w===1024){
 console.log('AFTER',JSON.stringify(after));
 assert(Math.abs(before[w].visualWidth-after.visualWidth-48)<0.1);
 assert.equal(after.visualX,before[w].visualX);
 assert.equal(after.frameHeight,650);
 assert.equal(after.orbWidth,'80px');
 assert(after.edgeGap>=24,`Edge gap ${after.edgeGap}`);
 assert.equal(after.overflow,0);
 }else{
 for(const key of ['visualWidth','visualX','orbWidth'])assert.equal(after[key],before[w][key],`${key} changed at ${w}`);
 }
 results.push({viewport:w,beforeWidth:before[w].visualWidth,afterWidth:after.visualWidth,edgeGap:after.edgeGap});
 }
 await page.setViewportSize({width:1024,height:1000});
 await page.locator('.dg-home-hero').screenshot({path:path.join(__dirname,'visual-after-1024.png')});
 await page.emulateMedia({reducedMotion:'no-preference'});
 const normal=await measure(1024);assert.equal(normal.orbWidth,'80px');assert(normal.edgeGap>=24);
 console.log(JSON.stringify({status:'PASS',results,normalAndReducedMotion:'PASS'},null,2));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
