const { chromium } = require('playwright');
(async () => {
 const browser = await chromium.launch({headless:true,channel:'msedge'});
 const page = await browser.newPage();
 const responses=[];
 page.on('response',r=>{if(/woff|Material\+Symbols/.test(r.url())) responses.push({url:r.url(),status:r.status()});});
 await page.goto('https://dragonglow.freehosting.dev/',{waitUntil:'domcontentloaded',timeout:60000});
 await page.evaluate(()=>document.fonts.ready);
 console.log(JSON.stringify(await page.evaluate(()=>({fonts:[...document.fonts].map(f=>({family:f.family,status:f.status,display:f.display})),icons:[...document.querySelectorAll('.material-symbols-outlined')].slice(0,15).map(e=>({name:e.textContent.trim(),font:getComputedStyle(e).fontFamily,opacity:getComputedStyle(e).opacity})),styles:[...document.querySelectorAll('link[rel="stylesheet"],link[as="font"]')].map(e=>e.href)})),null,2));
 console.log(JSON.stringify(responses,null,2));
 await browser.close();
})().catch(e=>{console.error(e);process.exitCode=1});
