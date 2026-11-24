const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome' });
 try {
  const page = await browser.newPage();
  await page.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/', { waitUntil: 'domcontentloaded' });
  const selector = '#product-info form.cart .single_add_to_cart_button';
  await page.locator(selector).waitFor();
  await page.addStyleTag({ content: fs.readFileSync('wp-content/themes/dragon-glow/style.css', 'utf8') });
  await page.addStyleTag({ content: fs.readFileSync('wp-content/themes/dragon-glow/assets/css/single-product.css', 'utf8') });
  for (const width of [390, 640, 768, 1024, 1280]) {
   await page.setViewportSize({ width, height: 900 });
   await page.locator(selector).evaluate(b => { b.classList.add('added'); b.textContent = 'Added to bag!'; b.disabled = true; });
   await page.locator(selector).scrollIntoViewIfNeeded();
   await page.mouse.move(0, 0);
   await page.waitForTimeout(400);
   const read = () => page.locator(selector).evaluate(b => {
    const s = getComputedStyle(b), r = b.getBoundingClientRect();
    return { bg: s.backgroundImage, color: s.color, sheen: getComputedStyle(b, '::before').content, width: r.width, height: r.height, overflow: document.documentElement.scrollWidth - innerWidth };
   });
   const rest = await read();
   await page.locator(selector).hover();
   await page.waitForTimeout(400);
   const hover = await read();
   assert.equal(rest.color, 'rgb(85, 67, 0)');
   assert.equal(rest.bg, hover.bg);
   assert.equal(rest.color, hover.color);
   assert.equal(rest.sheen, 'none');
   assert.ok(rest.overflow <= 0);
   console.log(JSON.stringify({ width, rest, hover }));
  }
  await page.emulateMedia({ reducedMotion: 'reduce' });
  assert.equal(await page.locator(selector).evaluate(b => getComputedStyle(b).transitionDuration), '0s');
  const luminance = rgb => rgb.map(v => v / 255).map(v => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4).reduce((a, v, i) => a + v * [.2126, .7152, .0722][i], 0);
  const ratios = [[243,234,213],[223,201,151]].map(bg => (luminance(bg)+.05)/(luminance([85,67,0])+.05));
  assert.ok(Math.min(...ratios) >= 4.5);
  console.log('PASS: responsive, hover, disabled success, reduced motion; contrast ' + ratios.map(r => r.toFixed(2)).join('–') + ':1');
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.locator(selector).scrollIntoViewIfNeeded();
  await page.screenshot({ path: 'tmp-resp/product-added-champagne.png' });
 } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
