const { chromium } = require('playwright');
const fs=require('fs'),path=require('path');
const theme=path.join(process.cwd(),'wp-content/themes/dragon-glow');
const after=process.argv.includes('--after');
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const page=await browser.newPage();
 await page.goto('https://dragonglow.freehosting.dev/privacy-policy/',{waitUntil:'domcontentloaded'});
 await page.addStyleTag({content:fs.readFileSync(path.join(theme,'style.css'),'utf8')});
 await page.addStyleTag({content:fs.readFileSync(path.join(theme,'assets/css/privacy-policy.css'),'utf8')});
 await page.evaluate(()=>document.fonts.ready);
 const results=[];
 for(const width of [320,390,640,768,834,1023,1024,1440]){
  await page.setViewportSize({width,height:900});
  if(width<1024) await page.locator('.dg-privacy-toc-btn').click();
  const r=await page.evaluate(()=>{
   const list=document.querySelector('.dg-privacy-toc-list');
   const rect=list.getBoundingClientRect();
   const pseudo=p=>{const s=getComputedStyle(list,p);return {width:s.width,bg:s.backgroundColor,radius:s.borderRadius};};
   list.scrollTop=list.scrollHeight;
   return {width:innerWidth,geometry:[rect.width,rect.height],count:list.children.length,scroll:list.scrollTop,overflow:document.documentElement.scrollWidth-innerWidth,bar:pseudo('::-webkit-scrollbar'),thumb:pseudo('::-webkit-scrollbar-thumb'),track:pseudo('::-webkit-scrollbar-track'),native:getComputedStyle(list).scrollbarWidth};
  });
  if(after && width<1024) {
   const thumb = await page.evaluate(()=>{const s=getComputedStyle(document.querySelector('.dg-privacy-toc-list'),'::-webkit-scrollbar-thumb');return {border:s.borderLeftWidth,clip:s.backgroundClip};});
   if(thumb.border!=='2px'||thumb.clip!=='padding-box')throw Error(JSON.stringify(thumb));
   console.log('Visible thumb 4px, drag area 8px: PASS');
  }
  results.push(r);
  if(after&&width<1024&&(!r.scroll||r.count!==20||r.overflow||r.bar.width!=='8px')) throw Error(JSON.stringify(r));
  if(after&&width===768){await page.screenshot({path:path.join(__dirname,'privacy-scrollbar-768.png')});}
  if(width<1024) await page.keyboard.press('Escape');
  console.log(width,JSON.stringify(r));
 }
 if(after){ const before=JSON.parse(fs.readFileSync(path.join(__dirname,'privacy-scrollbar-before.json')));for(const r of results.filter(r=>r.width>=1024))if(JSON.stringify(r)!==JSON.stringify(before.find(b=>b.width===r.width)))throw Error('Desktop changed');console.log('Desktop unchanged PASS');}
 else fs.writeFileSync(path.join(__dirname,'privacy-scrollbar-before.json'),JSON.stringify(results));
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1);});
