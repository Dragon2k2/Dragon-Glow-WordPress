#!/usr/bin/env node
/**
 * Verify bề ngang 2 nút CTA hero Wishlist (Share wishlist / Continue shopping)
 * so với khối stats (.dg-wishlist-hero__stats) ở mobile.
 * - Mobile (< 640px): 2 nút xếp dọc, mỗi nút = đúng bề ngang stats.
 * - Desktop/tablet (≥ 640px): GIỮ NGUYÊN hành vi cũ (ôm chữ, nằm cạnh nhau).
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/wl-btn');

const MARKUP = `
<body style="margin:0; background:#f5f3ef;">
  <main class="dg-wishlist" id="main-content">
    <div class="dg-wishlist-wrap">
      <section class="dg-wishlist-hero" data-sr>
        <div class="dg-wishlist-hero__inner">
          <div class="dg-wishlist-hero__copy">
            <p class="dg-wishlist-hero__eyebrow">
              <span class="material-symbols-outlined dg-wishlist-hero__heart" aria-hidden="true">favorite</span>
              Your saved ritual
            </p>
            <h1 class="dg-wishlist-hero__title">My wishlist</h1>
            <p class="dg-wishlist-hero__sub">Curated pieces you love, kept in one luminous place.</p>
            <div class="dg-wishlist-hero__actions" id="actions">
              <button type="button" class="dg-wishlist-btn dg-wishlist-btn--primary" data-dg-wl-action="share" id="btn-share">
                <span class="material-symbols-outlined" aria-hidden="true">share</span>
                Share wishlist
              </button>
              <a href="/shop/" class="dg-wishlist-btn dg-wishlist-btn--ghost" id="btn-shop">
                <span class="material-symbols-outlined" aria-hidden="true">storefront</span>
                Continue shopping
              </a>
            </div>
          </div>
          <dl class="dg-wishlist-hero__stats" id="stats" data-sr-group>
            <div class="dg-wishlist-hero__stat" data-sr>
              <dt class="dg-wishlist-hero__stat-label">Saved items</dt>
              <dd class="dg-wishlist-hero__stat-value"><span data-dg-wl-stat="total">0</span></dd>
            </div>
            <div class="dg-wishlist-hero__stat" data-sr>
              <dt class="dg-wishlist-hero__stat-label">In stock</dt>
              <dd class="dg-wishlist-hero__stat-value"><span data-dg-wl-stat="in_stock">0</span></dd>
            </div>
            <div class="dg-wishlist-hero__stat" data-sr>
              <dt class="dg-wishlist-hero__stat-label dg-wishlist-hero__stat-label--stacked"><span>On</span><span>sale</span></dt>
              <dd class="dg-wishlist-hero__stat-value"><span data-dg-wl-stat="on_sale">0</span></dd>
            </div>
            <div class="dg-wishlist-hero__stat" data-sr>
              <dt class="dg-wishlist-hero__stat-label">You save</dt>
              <dd class="dg-wishlist-hero__stat-value"><span data-dg-wl-stat="saved_amount">$0.00</span></dd>
            </div>
          </dl>
        </div>
      </section>
    </div>
  </main>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'wishlist.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block">
${LINKS}
</head>${MARKUP}</html>`;

const PROBE = () => {
  const stats = document.querySelector('#stats');
  const share = document.querySelector('#btn-share');
  const shop = document.querySelector('#btn-shop');
  const actions = document.querySelector('#actions');
  const sr = stats.getBoundingClientRect();
  const sh = share.getBoundingClientRect();
  const sp = shop.getBoundingClientRect();
  const acs = getComputedStyle(actions);
  return {
    statsW: Math.round(sr.width),
    shareW: Math.round(sh.width),
    shopW: Math.round(sp.width),
    shareTop: Math.round(sh.top),
    shopTop: Math.round(sp.top),
    flexDir: acs.flexDirection,
    flexWrap: acs.flexWrap,
    shareJustify: getComputedStyle(share).justifyContent,
    shopJustify: getComputedStyle(shop).justifyContent,
  };
};

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'wl-btn.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;
  const fail = [];
  const check = (name, ok, detail) => {
    console.log(`  ${ok ? '✓' : '✗'} ${name}${detail ? ' — ' + detail : ''}`);
    if (!ok) { allPass = false; fail.push(name + (detail ? ': ' + detail : '')); }
  };

  const CASES = [
    { name: 'MOBILE  390px', w: 390, mobile: true },
    { name: 'MOBILE  430px', w: 430, mobile: true },
    { name: 'TABLET 768px', w: 768, mobile: false },
    { name: 'DESKTOP 1280px', w: 1280, mobile: false },
  ];

  for (const c of CASES) {
    const page = await browser.newPage({ viewport: { width: c.w, height: 1000 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(350);

    const p = await page.evaluate(PROBE);
    console.log('\n' + '='.repeat(80));
    console.log(`${c.name}  (${c.mobile ? 'MOBILE — cần sửa' : 'DESKTOP/TABLET — phải giữ nguyên'})`);
    console.log('='.repeat(80));
    console.log(`  stats: ${p.statsW}px | share: ${p.shareW}px | shop: ${p.shopW}px`);
    console.log(`  flex-direction: ${p.flexDir} | flex-wrap: ${p.flexWrap}`);
    console.log(`  share top ${p.shareTop}px, shop top ${p.shopTop}px (xếp dọc nếu shopTop > shareTop)`);

    if (c.mobile) {
      check('nút share = bề ngang stats', p.shareW === p.statsW, `${p.shareW} vs ${p.statsW}`);
      check('nút shop = bề ngang stats', p.shopW === p.statsW, `${p.shopW} vs ${p.statsW}`);
      check('2 nút xếp dọc', p.shopTop > p.shareTop, `share top ${p.shareTop}, shop top ${p.shopTop}`);
      check('flex-direction column', p.flexDir === 'column', p.flexDir);
      check('icon + chữ căn giữa (share)', p.shareJustify === 'center', p.shareJustify);
      check('icon + chữ căn giữa (shop)', p.shopJustify === 'center', p.shopJustify);
    } else {
      check('nút vẫn ôm chữ (không giãn)', p.shareW < p.statsW, `share ${p.shareW} < stats ${p.statsW}`);
      check('2 nút nằm cạnh nhau (cùng hàng)', p.shopTop === p.shareTop, `share top ${p.shareTop}, shop top ${p.shopTop}`);
      check('flex-direction giữ nguyên (row)', p.flexDir === 'row', p.flexDir);
    }

    await page.screenshot({ path: path.join(OUT, `wl-${c.w}.png`), fullPage: true });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(80));
  if (allPass) {
    console.log('✓ PASS — mobile giãn hết bề ngang stats, desktop/tablet giữ nguyên');
  } else {
    console.log('✗ FAIL (' + fail.length + '):');
    fail.forEach((f) => console.log('   - ' + f));
  }
  console.log('='.repeat(80));
  process.exit(allPass ? 0 : 1);
})();
