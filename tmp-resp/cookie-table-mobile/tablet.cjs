const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const theme = path.join(process.cwd(), 'wp-content/themes/dragon-glow');
const after = process.argv.includes('--after');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome', headless: true });
 const page = await browser.newPage();
 await page.goto('https://dragonglow.freehosting.dev/cookie-policy/', { waitUntil: 'domcontentloaded' });
 await page.addStyleTag({ content: fs.readFileSync(path.join(theme, 'style.css'), 'utf8') });
 await page.addStyleTag({ content: fs.readFileSync(path.join(theme, 'assets/css/cookie-policy.css'), 'utf8') });
 await page.evaluate(() => document.fonts.ready);
 const results = [];
 for (const width of [320,390,640,641,700,767,768,834,900,1023,1024,1280,1440]) {
  await page.setViewportSize({ width, height: 900 });
  const r = await page.evaluate(() => {
   const wrap = document.querySelector('.dg-cookie-table-scroll'), t = wrap.querySelector('table');
   const box = el => { const r=el.getBoundingClientRect(); return [r.x,r.y,r.width,r.height].map(v=>Math.round(v*100)/100); };
   return { width:innerWidth, wrapper:wrap.clientWidth, scroll:wrap.scrollWidth, overflow:document.documentElement.scrollWidth-innerWidth, table:box(t), cells:[...t.querySelectorAll('th,td')].map(el=>({ text:el.innerText, box:box(el), clipped:el.scrollWidth>el.clientWidth+1, display:getComputedStyle(el).display })), rows:t.tBodies[0].rows.length, headers:[...t.querySelectorAll('thead .dg-cookie-table-col-provider, thead .dg-cookie-table-col-duration')].map(el=>{ const range=document.createRange();range.selectNodeContents(el); const rects=[...range.getClientRects()];const cell=el.getBoundingClientRect();return {lines:rects.length,fits:rects.every(r=>r.left>=cell.left && r.right<=cell.right)};}), badges:[...t.querySelectorAll('.dg-cookie-table-type--strictly')].map(el=>{ const range=document.createRange(); range.selectNodeContents(el); return [...range.getClientRects()].filter(r=>r.width>0).length; }), names:[...t.querySelectorAll('tbody .dg-cookie-table-name')].map(el=>{const range=document.createRange();range.selectNodeContents(el);const rects=[...range.getClientRects()];return {lines:rects.length,fits:rects.every(r=>r.right<=el.closest('th').getBoundingClientRect().right+1)};}) };
  });
  results.push(r);
  if (width===768) {
   await page.locator('.dg-cookie-table-scroll').scrollIntoViewIfNeeded();
   await page.screenshot({ path:path.join(__dirname,`tablet-${after?'after':'before'}-768.png`) });
   // Restore scroll for identical geometry comparisons.
   await page.evaluate(()=>window.scrollTo(0,0));
  }
 }
 const target=path.join(__dirname,`tablet-${after?'after':'before'}.json`);
 fs.writeFileSync(target,JSON.stringify(results,null,2));
 const before=after?JSON.parse(fs.readFileSync(path.join(__dirname,'tablet-before.json'))):[];
 for (const r of results) {
  if(r.width>640 && r.width<1024) {
   const pass=r.scroll<=r.wrapper+1 && r.overflow===0 && r.rows===9 && r.cells.every(c=>!c.clipped) && r.names.every(n=>n.lines===1 && n.fits) && r.badges.every(lines=>lines===2) && r.headers.every(h=>h.lines===1 && h.fits);
   console.log(`${r.width}px tablet: ${pass?'PASS':'FAIL'} wrapper=${r.wrapper} scroll=${r.scroll}`);
   if(after&&!pass) throw Error('Tablet clipping');
  } else if(after) {
   if(JSON.stringify(r)!==JSON.stringify(before.find(b=>b.width===r.width))) throw Error(`Regression ${r.width}`);
   console.log(`${r.width}px mobile/desktop unchanged: PASS`);
  }
 }
 await page.emulateMedia({reducedMotion:'reduce'});
 await page.setViewportSize({width:768,height:900});
 console.log('Reduced motion visible:',await page.locator('.dg-cookie-table').isVisible());
 await browser.close();
})().catch(e=>{ console.error(e);process.exit(1); });
