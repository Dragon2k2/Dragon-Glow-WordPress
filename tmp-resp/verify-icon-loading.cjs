const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve('wp-content/themes/dragon-glow');
(async () => {
 const browser = await chromium.launch({headless:true,channel:'msedge'});
 const context = await browser.newContext();
 const page = await context.newPage();
 const baseline = process.argv.includes('--baseline');
 if(process.argv.includes('--slow')) {
  const cdp=await context.newCDPSession(page);
  await cdp.send('Network.enable');
  await cdp.send('Network.setCacheDisabled',{cacheDisabled:true});
  await cdp.send('Network.emulateNetworkConditions',{offline:false,latency:150,downloadThroughput:150000,uploadThroughput:75000});
 }
 const errors=[]; page.on('pageerror', e=>errors.push(e.message));
 await page.addInitScript(() => {
  window.iconPaints=[];
  new PerformanceObserver(list=>{
   for(const entry of list.getEntries()) window.iconPaints.push({paint:entry.name,fonts:[...document.fonts].filter(f=>f.family.includes('Material')).map(f=>f.status)});
  }).observe({type:'paint',buffered:true});
 });
 await page.route('**/*', async route=> {
  if(baseline) return route.continue();
  const url = new URL(route.request().url());
  if(url.hostname === 'fonts.googleapis.com' && url.search.includes('Material')) return route.fulfill({contentType:'text/css',body:''});
  if(url.hostname === 'fonts.gstatic.com' && url.pathname.includes('materialsymbols')) return route.abort();
  if(url.pathname.endsWith('/material-symbols-outlined.woff2')) return route.abort();
  const marker='/wp-content/themes/dragon-glow/';
  if(url.pathname.includes(marker) && url.pathname.endsWith('.css')) {
   const local=path.join(root,url.pathname.split(marker)[1]);
   if(fs.existsSync(local)) return route.fulfill({contentType:'text/css',body:fs.readFileSync(local)});
  }
  if(route.request().resourceType()==='document') {
   const response=await route.fetch(); let html=await response.text();
   html=html.replace('</head>',`<link rel="stylesheet" href="https://dragonglow.freehosting.dev/wp-content/themes/dragon-glow/assets/css/material-symbols.css"></head>`);
   return route.fulfill({response,body:html});
  }
  return route.continue();
 });
 await page.goto('https://dragonglow.freehosting.dev/', {waitUntil:'domcontentloaded',timeout:60000});
 await page.evaluate(()=>document.fonts.ready);
 for (const width of [390,640,768,1024,1280]) {
  await page.setViewportSize({width,height:900});
  const result=await page.evaluate(()=>({font:[...document.fonts].filter(f=>f.family.includes('Material')).map(f=>({status:f.status,display:f.display})),overflow:document.documentElement.scrollWidth>innerWidth,icons:[...document.querySelectorAll('.material-symbols-outlined')].map(e=>({name:e.textContent.trim(),width:e.getBoundingClientRect().width}))}));
  if(!result.font.some(f=>f.status==='loaded')) throw Error('Font not loaded');
  // Report existing page overflow separately from font rendering.
  console.log(width,JSON.stringify({...result,icons:result.icons.filter(i=>['bolt','auto_stories','shopping_bag','star','movie'].includes(i.name)).slice(0,10)}));
 }
 await page.emulateMedia({reducedMotion:'reduce'});
 await page.setViewportSize({width:1280,height:900});
 if(!baseline) await page.screenshot({path:'tmp-resp/icons-self-hosted-1280.png'});
 const paints=await page.evaluate(()=>window.iconPaints);
 console.log('first paint',JSON.stringify(paints));
 if(!baseline && paints.some(p=>!p.fonts.length || p.fonts.some(s=>s!=='loaded'))) throw Error('Icon font was not ready at first paint');
 console.log('pageerrors',JSON.stringify(errors));
 await browser.close();
})().catch(e=>{console.error(e);process.exitCode=1});
