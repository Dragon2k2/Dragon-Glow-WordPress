#!/usr/bin/env node
/**
 * Verify wishlist EMPTY STATE bullets ở mobile.
 * Root cause: `.dg-wishlist-empty__bullets` có `padding-left: 105px` hard-code
 * (căn lề cho icon 32px + gap 12px của layout desktop) → ở mobile nội dung bị
 * đẩy lệch sang phải, cắt chữ.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/wl-empty');

const MARKUP = `
<body style="margin:0; background:#f7f4ef;">
  <div class="dg-wishlist">
    <section class="dg-wishlist-empty">
      <div class="dg-wishlist-empty__inner">
        <div class="dg-wishlist-empty__illustration" aria-hidden="true">
          <div class="dg-wishlist-empty__halo"></div>
          <div class="dg-wishlist-empty__ring dg-wishlist-empty__ring--a"></div>
          <div class="dg-wishlist-empty__ring dg-wishlist-empty__ring--b"></div>
          <div class="dg-wishlist-empty__heart-wrap">
            <span class="material-symbols-outlined dg-wishlist-empty__heart">favorite</span>
          </div>
        </div>
        <h2 class="dg-wishlist-empty__title">Your Wishlist Is Waiting</h2>
        <p class="dg-wishlist-empty__text">
          Save the favourites you love and keep them within reach on every device.
          We'll let you know the moment a saved item drops in price — no spam, just the deals you actually want.
        </p>
        <ul class="dg-wishlist-empty__bullets">
          <li>
            <span class="material-symbols-outlined" aria-hidden="true">savings</span>
            <span>Get notified when saved items drop in price.</span>
          </li>
          <li>
            <span class="material-symbols-outlined" aria-hidden="true">event_available</span>
            <span>See your collection across every device.</span>
          </li>
          <li>
            <span class="material-symbols-outlined" aria-hidden="true">ios_share</span>
            <span>Share a private link with someone who cares.</span>
          </li>
        </ul>
        <div class="dg-wishlist-empty__actions">
          <a href="#" class="dg-wishlist-btn dg-wishlist-btn--primary">
            <span class="material-symbols-outlined" aria-hidden="true">storefront</span>
            Explore the collection
          </a>
          <a href="#" class="dg-wishlist-btn dg-wishlist-btn--ghost">
            <span class="material-symbols-outlined" aria-hidden="true">auto_awesome</span>
            Read our story
          </a>
        </div>
      </div>
    </section>
  </div>
</body>`;

const LINKS = ['../../style.css', 'main.css', 'wishlist.css']
  .map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`)
  .join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=block">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=block">
${LINKS}
</head>${MARKUP}</html>`;

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'empty.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  for (const w of [320, 390, 430, 639, 640, 768, 1024, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 1000 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(900);

    const r = await page.evaluate(() => {
      const ul = document.querySelector('.dg-wishlist-empty__bullets');
      const inner = document.querySelector('.dg-wishlist-empty__inner');
      const lis = [...document.querySelectorAll('.dg-wishlist-empty__bullets li')];
      const ulcs = getComputedStyle(ul);
      const ib = inner.getBoundingClientRect();
      return {
        ulPaddingLeft: ulcs.paddingLeft,
        ulLeft: Math.round(ul.getBoundingClientRect().left),
        ulRight: Math.round(ul.getBoundingClientRect().right),
        innerLeft: Math.round(ib.left),
        innerRight: Math.round(ib.right),
        lis: lis.map((li) => {
          const icon = li.querySelector('.material-symbols-outlined').getBoundingClientRect();
          const txt = li.querySelector('span:not(.material-symbols-outlined)').getBoundingClientRect();
          return {
            iconLeft: Math.round(icon.left),
            iconW: Math.round(icon.width),
            textLeft: Math.round(txt.left),
            textRight: Math.round(txt.right),
            textW: Math.round(txt.width),
            textH: Math.round(txt.height),
          };
        }),
        overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth
          ? `${document.documentElement.scrollWidth} > ${document.documentElement.clientWidth}` : null,
      };
    });

    const tag = w < 640 ? 'MOBILE' : w < 1024 ? 'TABLET' : 'DESKTOP';
    console.log(`\n--- ${w}px [${tag}] ---`);
    console.log(`  ul padding-left=${r.ulPaddingLeft} ul=[${r.ulLeft}..${r.ulRight}] inner=[${r.innerLeft}..${r.innerRight}]`);
    r.lis.forEach((li, i) => {
      console.log(`  li[${i}] icon=[${li.iconLeft} w=${li.iconW}] text=[${li.textLeft}..${li.textRight} w=${li.textW} h=${li.textH}]`);
    });
    if (r.overflow) console.log(`  ⚠ OVERFLOW ngang: ${r.overflow}`);

    await page.screenshot({ path: path.join(OUT, `empty-${w}.png`), fullPage: true });
    await page.close();
  }
  await browser.close();
})();
