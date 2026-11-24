const { chromium } = require('playwright');
const fs = require('node:fs');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ channel: 'chrome' });
 try {
  const page = await browser.newPage();
  await page.goto('https://dragonglow.freehosting.dev/', { waitUntil: 'domcontentloaded' });
  // The public guest header omits account actions; reproduce the theme's logged-in badge wrappers.
  await page.evaluate(() => {
   const fixture = document.createElement('div');
   fixture.className = 'dg-nav-actions';
   fixture.innerHTML = '<a class="hidden lg:block relative"><span class="dg-cart-count hidden">0</span></a><button class="dg-nav-toggle"><span class="dg-nav-badge hidden">0</span></button>';
   document.body.prepend(fixture);
  });
  await page.addScriptTag({ path: 'wp-content/themes/dragon-glow/assets/js/lib/nav-badge.js' });
  const markup = await page.locator('.dg-cart-count').first().evaluate(el => ({ badge: el.outerHTML, parent: el.parentElement.outerHTML }));
  console.log('DOM', JSON.stringify(markup));
  await page.addStyleTag({ content: fs.readFileSync('wp-content/themes/dragon-glow/assets/css/header-nav.css', 'utf8') });
  await page.addScriptTag({ path: 'wp-content/themes/dragon-glow/assets/js/lib/cart-badge.js' });
  for (const width of [640, 768, 1024, 1280]) {
   await page.setViewportSize({ width, height: 900 });
   for (const motion of ['no-preference', 'reduce']) {
    await page.emulateMedia({ reducedMotion: motion });
    const result = await page.evaluate(() => {
     const cart = document.querySelector('.dg-cart-count');
     const nav = document.querySelector('.dg-nav-badge');
     const read = el => { const s = getComputedStyle(el); return { name: s.animationName, duration: s.animationDuration, easing: s.animationTimingFunction }; };
     window.DGCartBadge.paint(0);
     window.DGNavBadge.setWishlist(0);
     window.DGCartBadge.paint(8);
     const first = { cart: read(cart), nav: read(nav) };
     window.DGCartBadge.paint(9);
     const pulse = { cart: read(cart), nav: read(nav) };
     const frames = cart.getAnimations().map(a => a.effect.getKeyframes());
     window.DGCartBadge.paint(0);
     return { first, pulse, frames, hidden: getComputedStyle(cart).display };
    });
    if (process.argv.includes('--before')) {
     console.log('BEFORE', JSON.stringify({ width, motion, result }));
     assert.equal(result.pulse.cart.name, 'none');
     return;
    }
    assert.deepEqual(result.first.cart, result.first.nav);
    assert.deepEqual(result.pulse.cart, result.pulse.nav);
    assert.equal(result.pulse.cart.name, motion === 'reduce' ? 'none' : 'dgCartBadgePop');
    assert.equal(result.first.cart.name, motion === 'reduce' ? 'none' : 'dgCartBadgeFirstShow');
    assert.equal(result.hidden, 'none');
    console.log('PASS', JSON.stringify({ width, motion, result }));
   }
  }
 } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
