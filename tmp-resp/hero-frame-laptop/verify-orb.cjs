const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
(async () => {
 const theme = path.resolve(__dirname, '../../wp-content/themes/dragon-glow');
 const home = fs.readFileSync(path.join(theme, 'assets/css/home.css'), 'utf8');
 const tokens = fs.readFileSync(path.join(theme, 'style.css'), 'utf8');
 const baseline = home.replace(/    \/\* Scale the decorative seal to suit the narrower laptop frame\. \*\/[\s\S]*?(?=    \/\* Keep both hero actions)/, '');
 assert.notEqual(home, baseline);
 const browser = await chromium.launch({ channel: 'chrome', headless: true });
 try {
 const page = await browser.newPage({ reducedMotion: 'reduce' });
 await page.route('**/dragon-glow/assets/css/home.css*', r => r.fulfill({ contentType: 'text/css', body: baseline }));
 await page.route('**/dragon-glow/style.css*', r => r.fulfill({ contentType: 'text/css', body: tokens }));
 await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'networkidle', timeout: 60000 });
 await page.evaluate(() => document.fonts.ready);
 async function measure(width) {
 await page.setViewportSize({ width, height: 1000 });
 await page.evaluate(() => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r))));
 return page.evaluate(() => {
 const e = document.querySelector('.dg-home-hero__orb'), t = e.querySelector('.dg-glaze-orb-text'), c = getComputedStyle(e), r = e.getBoundingClientRect(), tr = t.getBoundingClientRect();
 return { width: parseFloat(c.width), height: parseFloat(c.height), font: getComputedStyle(t).fontSize, right: c.right, bottom: c.bottom, fits: tr.width < r.width && tr.height < r.height, frameHeight: document.querySelector('.dg-home-hero__product-frame').getBoundingClientRect().height };
 });
 }
 const widths = [390,640,768,1023,1024,1025,1280,1440];
 const before = {};
 for (const w of widths) before[w] = await measure(w);
 await page.addStyleTag({ content: home });
 const results = [];
 for (const w of widths) {
 const after = await measure(w);
 if (w === 1024) {
 assert.equal(before[w].width,96); assert.equal(after.width,80); assert.equal(after.height,80); assert.equal(after.font,'10px'); assert(after.fits); assert.equal(after.frameHeight,650);
 assert.equal(after.right,before[w].right); assert.equal(after.bottom,before[w].bottom);
 } else {
 const { fits: afterFits, frameHeight: afterFrameHeight, ...afterStyles } = after;
 const { fits: beforeFits, frameHeight: beforeFrameHeight, ...beforeStyles } = before[w];
 assert.deepEqual(afterStyles,beforeStyles,`Changed outside 1024px: ${w}`);
 }
 results.push({viewport:w,before:before[w].width,after:after.width,font:after.font});
 }
 await page.setViewportSize({width:1024,height:1000});
 await page.locator('.dg-home-hero__visual').screenshot({path:path.join(__dirname,'orb-after-1024.png')});
 await page.emulateMedia({reducedMotion:'no-preference'});
 assert.equal((await measure(1024)).width,80);
 console.log(JSON.stringify({status:'PASS',results,reducedAndNormalMotion:'PASS'},null,2));
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exitCode=1;});
