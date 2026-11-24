const { chromium }=require('playwright');
const fs=require('fs'),path=require('path');
const theme=path.join(process.cwd(),'wp-content/themes/dragon-glow');
const after=process.argv.includes('--after');
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const page=await browser.newPage();
 await page.goto('https://dragonglow.freehosting.dev/terms-of-service/',{waitUntil:'domcontentloaded'});
 for(const file of ['style.css','assets/css/terms-of-service.css'])await page.addStyleTag({content:fs.readFileSync(path.join(theme,file),'utf8')});
 await page.evaluate(()=>document.fonts.ready);
 const results=[];
 for(const width of [320,390,640,768,834,1023,1024,1440]){
  await page.setViewportSize({width,height:900});
  if(width<1024)await page.locator('.dg-terms-toc-btn').click();
  const r=await page.evaluate(()=>{
   const list=document.querySelector('.dg-terms-toc-list'),sidebar=document.querySelector('.dg-terms-sidebar');
   const rect=list.getBoundingClientRect();
   const pseudo=(el,p)=>{const s=getComputedStyle(el,p);return {width:s.width,bg:s.backgroundColor,radius:s.borderRadius,border:s.borderLeftWidth,clip:s.backgroundClip};};
   list.scrollTop=list.scrollHeight;
   return {width:innerWidth,geometry:[rect.width,rect.height],count:list.children.length,scroll:list.scrollTop,overflow:document.documentElement.scrollWidth-innerWidth,bar:pseudo(list,'::-webkit-scrollbar'),thumb:pseudo(list,'::-webkit-scrollbar-thumb'),track:pseudo(list,'::-webkit-scrollbar-track'),sidebar:pseudo(sidebar,'::-webkit-scrollbar-thumb')};
  });
  results.push(r);
  if(after&&width<1024&&(r.scroll<=0||r.overflow||r.bar.width!=='8px'||r.thumb.border!=='2px'||r.thumb.clip!=='padding-box'||r.thumb.bg!=='rgb(201, 162, 75)'))throw Error(JSON.stringify(r));
  if(after&&width===768){await page.locator('.dg-terms-toc-list').evaluate(el=>el.scrollTop=0);await page.screenshot({path:path.join(__dirname,'terms-scrollbar-768.png')});}
  if(width<1024)await page.keyboard.press('Escape');
  console.log(width,JSON.stringify(r));
 }
 if(after){const before=JSON.parse(fs.readFileSync(path.join(__dirname,'terms-scrollbar-before.json')));for(const r of results){const b=before.find(b=>b.width===r.width);if(r.width>=1024?JSON.stringify(r)!==JSON.stringify(b):JSON.stringify(r.geometry)!==JSON.stringify(b.geometry)||r.count!==b.count)throw Error('Regression '+r.width);}console.log('PASS: mobile/tablet scroll and geometry; desktop unchanged');}
 else fs.writeFileSync(path.join(__dirname,'terms-scrollbar-before.json'),JSON.stringify(results));
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1);});
