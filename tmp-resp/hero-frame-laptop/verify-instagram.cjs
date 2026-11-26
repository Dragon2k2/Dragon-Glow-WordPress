const {chromium}=require('playwright');
const fs=require('node:fs');const path=require('node:path');const assert=require('node:assert/strict');
(async()=>{
const theme=path.resolve(__dirname,'../../wp-content/themes/dragon-glow');
const css=fs.readFileSync(path.join(theme,'assets/css/home.css'),'utf8'),tokens=fs.readFileSync(path.join(theme,'style.css'),'utf8');
const browser=await chromium.launch({channel:'chrome',headless:true});
try{const page=await browser.newPage({reducedMotion:'reduce'});
await page.route('**/dragon-glow/assets/css/home.css*',r=>r.fulfill({contentType:'text/css',body:css}));
await page.route('**/dragon-glow/style.css*',r=>r.fulfill({contentType:'text/css',body:tokens}));
await page.goto('https://dragonglow.freehosting.dev/',{waitUntil:'domcontentloaded',timeout:60000});await page.evaluate(()=>{document.querySelector('.dg-home-instagram-header__cta-link').parentElement.querySelector('p').classList.add('dg-home-instagram-header__description');return document.fonts.ready;});
async function measure(w){await page.setViewportSize({width:w,height:900});await page.evaluate(()=>new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r))));return page.evaluate(()=>{
const a=document.querySelector('.dg-home-instagram-header__cta-link'),h=a.parentElement,p=h.querySelector('p'),t=a.querySelector('span:nth-child(2)');
function info(e){const r=e.getBoundingClientRect(),s=getComputedStyle(e),range=document.createRange();range.selectNodeContents(e);return {x:r.x,y:r.y,w:r.width,h:r.height,font:s.fontSize,line:s.lineHeight,rects:[...range.getClientRects()].map(x=>({y:x.y,w:x.width}))};}
return {header:info(h),p:info(p),a:info(a),text:info(t),overflow:document.documentElement.scrollWidth-innerWidth};});}
const before=await measure(1024);console.log('BEFORE',JSON.stringify(before));
if(!css.includes('instagram-header-description-size-laptop'))return;
const baseline=css.replace(/\/\* Laptop preset: keep the Instagram description and CTA on single lines\. \*\/[\s\S]*?(?=\/\* Reduced Motion: Disable hover lift \*\/)/,'');
const after={};for(const w of [390,640,768,1023,1024,1025,1280,1440])after[w]=await measure(w);
await page.unroute('**/dragon-glow/assets/css/home.css*');await page.route('**/dragon-glow/assets/css/home.css*',r=>r.fulfill({contentType:'text/css',body:baseline}));await page.reload({waitUntil:'domcontentloaded',timeout:60000});await page.evaluate(()=>{document.querySelector('.dg-home-instagram-header__cta-link').parentElement.querySelector('p').classList.add('dg-home-instagram-header__description');return document.fonts.ready;});
for(const w of [390,640,768,1023,1024,1025,1280,1440]){const b=await measure(w),a=after[w];if(w===1024){assert(new Set(b.p.rects.map(r=>r.y)).size>1);assert.equal(a.p.font,'14px');assert.equal(new Set(a.p.rects.map(r=>r.y)).size,1);assert.equal(new Set(a.text.rects.map(r=>r.y)).size,1);assert(a.p.x+a.p.w<=a.a.x);assert(a.a.x+a.a.w<=1024);console.log('1024 BEFORE/AFTER',JSON.stringify({before:b,after:a}));}else{for(const k of ['p','a','text'])for(const prop of ['w','h','font'])assert.equal(a[k][prop],b[k][prop],`${w} ${k} ${prop}`);}}
await page.unroute('**/dragon-glow/assets/css/home.css*');await page.route('**/dragon-glow/assets/css/home.css*',r=>r.fulfill({contentType:'text/css',body:css}));await page.setViewportSize({width:1024,height:900});await page.reload({waitUntil:'domcontentloaded',timeout:60000});await page.locator('.dg-home-instagram-header__cta-link').evaluate(e=>e.parentElement.scrollIntoView({block:'center'}));await page.screenshot({path:path.join(__dirname,'instagram-after-1024.png')});console.log('PASS: single lines at 1024; 7 other viewports unchanged');
}finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
