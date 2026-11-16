const { chromium } = require('playwright');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome', headless: true });
 try {
  const page = await browser.newPage();
  await page.setContent('<table id="dg-cart-table"><tr><td><label class="dg-cart-select-all-label"><input type="checkbox" checked class="dg-cart-select-all"><span class="dg-cart-select-all-mark"></span></label></td></tr></table>');
  await page.evaluate(() => { const label = document.createElement('label'); label.className = 'dg-cart-row__select-label'; label.innerHTML = '<input type="checkbox" checked class="dg-cart-row__select"><span class="dg-cart-row__select-mark"></span>'; document.querySelector('td').append(label); });
  await page.addStyleTag({ path: 'wp-content/themes/dragon-glow/style.css' });
  await page.addStyleTag({ path: 'wp-content/themes/dragon-glow/assets/css/cart.css' });
  for (const width of [320,390,640,641,768,1024,1280]) {
   await page.setViewportSize({width,height:600});
   const left = await page.evaluate(() => getComputedStyle(document.querySelector('.dg-cart-select-all-mark'),'::after').left);
   if (left !== (width <= 640 ? '5px' : '6px')) throw Error(width + ': ' + left);
   const rowLeft = await page.evaluate(() => getComputedStyle(document.querySelector('.dg-cart-row__select-mark'),'::after').left);
   if (rowLeft !== (width <= 640 ? '5px' : '6px')) throw Error('Row ' + width + ': ' + rowLeft);
   console.log('PASS',width,'master:',left,'row:',rowLeft);
  }
  await page.setViewportSize({width:320,height:600});
  await page.evaluate(() => document.querySelector('input').indeterminate = true);
  const left = await page.evaluate(() => getComputedStyle(document.querySelector('.dg-cart-select-all-mark'),'::after').left);
  if (left !== '4px') throw Error('Indeterminate: ' + left);
  console.log('PASS indeterminate unchanged');
 } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode=1; });
