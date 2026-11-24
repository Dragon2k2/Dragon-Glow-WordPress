const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome' });
 try {
  const page = await browser.newPage();
  await page.goto('https://dragonglow.freehosting.dev/product/browluxe-define-brow-mascara/', { waitUntil: 'domcontentloaded', timeout: 45000 });
  const template = fs.readFileSync('wp-content/themes/dragon-glow/woocommerce/content-single-product.php', 'utf8');
  const block = template.split('<!-- Trust badges -->')[1].split('\n\t</div>')[0];
  const html = block.replace(/<\?php esc_html_e\( '([^']+)', 'dragon-glow' \); \?>/g, '$1');
  await page.locator('#product-info').evaluate((el, html) => {
   const original = [...el.querySelectorAll('div')].find(div => div.classList.contains('grid') && div.textContent.includes('30-Day Ritual Trial'));
   original.outerHTML = html;
  }, html);
  await page.addStyleTag({ content: fs.readFileSync('wp-content/themes/dragon-glow/assets/css/single-product.css', 'utf8') });
  // Tailwind CDN injects utilities after the theme stylesheet at runtime.
  await page.addStyleTag({ content: '.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }' });
  for (const width of [320, 390, 640, 768, 1024, 1280, 1536]) {
   await page.setViewportSize({ width, height: 900 });
   const result = await page.locator('.dg-product-trust').evaluate(el => {
    const boxes = [...el.children].map(child => { const r = child.getBoundingClientRect(); return { x: r.x, y: r.y, width: r.width, scroll: child.scrollWidth, client: child.clientWidth }; });
    return { text: el.textContent.replace(/\s+/g, ' ').trim(), columns: getComputedStyle(el).gridTemplateColumns, boxes, overflow: document.documentElement.scrollWidth - innerWidth, iconHidden: el.lastElementChild.querySelector('span').getAttribute('aria-hidden') };
   });
   assert.ok(result.text.includes('Secure checkout'));
   assert.equal(result.iconHidden, 'true');
   assert.ok(result.overflow <= 0);
   assert.ok(result.boxes.every(b => b.scroll <= b.client + 1));
   if (width >= 640) { assert.ok(result.boxes.every(b => Math.abs(b.y - result.boxes[0].y) < 1)); assert.ok(result.boxes[2].x > result.boxes[1].x); }
   else assert.ok(result.boxes[2].y > result.boxes[1].y);
   console.log('PASS', width, JSON.stringify(result));
  }
 } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
