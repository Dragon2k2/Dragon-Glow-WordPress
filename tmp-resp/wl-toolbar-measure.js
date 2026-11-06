#!/usr/bin/env node
/**
 * Đo toolbar Wishlist ở mobile — tìm nguyên nhân layout vỡ:
 * 3 filter tab wrap, cụm Select/sort bị chật.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/wl-toolbar');

const MARKUP = `
<body style="margin:0; background:#f5f3ef;">
  <main class="dg-wishlist">
    <div class="dg-wishlist-wrap">
      <section class="dg-wishlist-toolbar">
        <div class="dg-wishlist-toolbar__row">
          <div class="dg-wishlist-toolbar__segment" role="tablist">
            <button type="button" class="dg-wishlist-toolbar__seg is-active" role="tab" aria-selected="true">
              <span class="material-symbols-outlined" aria-hidden="true">apps</span>
              <span>All items</span>
              <span class="dg-wishlist-toolbar__count">0</span>
            </button>
            <button type="button" class="dg-wishlist-toolbar__seg" role="tab" aria-selected="false">
              <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
              <span>In stock</span>
              <span class="dg-wishlist-toolbar__count">0</span>
            </button>
            <button type="button" class="dg-wishlist-toolbar__seg" role="tab" aria-selected="false">
              <span class="material-symbols-outlined" aria-hidden="true">local_offer</span>
              <span>On sale</span>
              <span class="dg-wishlist-toolbar__count">0</span>
            </button>
          </div>
          <div class="dg-wishlist-toolbar__right">
            <label class="dg-wishlist-toolbar__select-all">
              <input type="checkbox" class="dg-wishlist-toolbar__checkbox">
              <span class="dg-wishlist-toolbar__checkbox-mark" aria-hidden="true"></span>
              <span class="dg-wishlist-toolbar__select-all-label">Select<span class="dg-wishlist-toolbar__select-count">(0)</span></span>
            </label>
            <div class="dg-wishlist-toolbar__sort">
              <span class="dg-wishlist-toolbar__sort-label">Sort by</span>
              <select id="dg-wl-sort" class="dg-wishlist-toolbar__sort-select">
                <option value="date">Recently saved</option>
              </select>
              <button type="button" class="dg-wishlist-toolbar__sort-trigger">
                <span class="dg-wishlist-toolbar__sort-icon material-symbols-outlined" aria-hidden="true">schedule</span>
                <span class="dg-wishlist-toolbar__sort-current">Recently saved</span>
                <span class="dg-wishlist-toolbar__sort-chevron material-symbols-outlined" aria-hidden="true">expand_more</span>
              </button>
              <div class="dg-wishlist-toolbar__sort-panel" role="listbox" hidden id="dg-wl-sort-panel">
                <button type="button" class="dg-wishlist-toolbar__sort-option is-active" role="option" aria-selected="true">
                  <span class="dg-wishlist-toolbar__sort-option-icon material-symbols-outlined" aria-hidden="true">schedule</span>
                  <span class="dg-wishlist-toolbar__sort-option-body">
                    <span class="dg-wishlist-toolbar__sort-option-label">Recently saved</span>
                    <span class="dg-wishlist-toolbar__sort-option-sub">Newest items first</span>
                  </span>
                  <span class="dg-wishlist-toolbar__sort-option-check material-symbols-outlined" aria-hidden="true">check</span>
                </button>
                <button type="button" class="dg-wishlist-toolbar__sort-option" role="option" aria-selected="false">
                  <span class="dg-wishlist-toolbar__sort-option-icon material-symbols-outlined" aria-hidden="true">arrow_upward</span>
                  <span class="dg-wishlist-toolbar__sort-option-body">
                    <span class="dg-wishlist-toolbar__sort-option-label">Price: low to high</span>
                    <span class="dg-wishlist-toolbar__sort-option-sub">Cheapest first</span>
                  </span>
                  <span class="dg-wishlist-toolbar__sort-option-check material-symbols-outlined" aria-hidden="true">check</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'wishlist.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

// Font icon không có trong repo (woff2 self-hosted thiếu) → ligature render
// thành chữ "expand_more" (109px) thay vì ~20px. Ép chiều rộng = 1em
// (bằng đúng bề rộng ligature Material Symbols thật) để số đo chính xác.
// KHÔNG set `display` ở đây — sẽ ghi đè `display: none` của
// `.dg-wishlist-toolbar__sort-icon` trong theme (specificity (0,2,0) > (0,1,0)).
const ICON_FIX = `
<style>
.dg-wishlist-toolbar .material-symbols-outlined {
  width: 1em;
  overflow: hidden;
  text-align: center;
  vertical-align: middle;
}
</style>`;

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Plus+Jakarta+Sans:wght@600&display=block">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block">
${LINKS}
${ICON_FIX}
</head>${MARKUP}</html>`;

const PROBE = () => {
  const toolbar = document.querySelector('.dg-wishlist-toolbar');
  const row = document.querySelector('.dg-wishlist-toolbar__row');
  const seg = document.querySelector('.dg-wishlist-toolbar__segment');
  const segs = Array.from(document.querySelectorAll('.dg-wishlist-toolbar__seg'));
  const right = document.querySelector('.dg-wishlist-toolbar__right');
  const sort = document.querySelector('.dg-wishlist-toolbar__sort');
  const trigger = document.querySelector('.dg-wishlist-toolbar__sort-trigger');
  const selectAll = document.querySelector('.dg-wishlist-toolbar__select-all');
  const sortLabel = document.querySelector('.dg-wishlist-toolbar__sort-label');
  const tcs = getComputedStyle(toolbar);
  const contentW = toolbar.getBoundingClientRect().width - parseFloat(tcs.paddingLeft) - parseFloat(tcs.paddingRight);
  return {
    contentW: Math.round(contentW),
    segW: Math.round(seg.getBoundingClientRect().width),
    segWrap: getComputedStyle(seg).flexWrap,
    segRows: new Set(segs.map((s) => Math.round(s.getBoundingClientRect().top))).size,
    segs: segs.map((s) => {
      const r = s.getBoundingClientRect();
      return { text: s.textContent.trim().replace(/\s+/g, ' '), w: Math.round(r.width), top: Math.round(r.top) };
    }),
    rightW: Math.round(right.getBoundingClientRect().width),
    rightWrap: getComputedStyle(right).flexWrap,
    rightRows: new Set([right, selectAll, sort].map((e) => Math.round(e.getBoundingClientRect().top))).size,
    selectAllW: Math.round(selectAll.getBoundingClientRect().width),
    sortW: Math.round(sort.getBoundingClientRect().width),
    triggerW: Math.round(trigger.getBoundingClientRect().width),
    sortLabelW: Math.round(sortLabel.getBoundingClientRect().width),
    overflow: right.getBoundingClientRect().width > contentW + 1,
  };
};

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'wl-toolbar.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  for (const vp of [{ w: 320, h: 900 }, { w: 390, h: 900 }, { w: 430, h: 900 }, { w: 640, h: 900 }, { w: 768, h: 900 }, { w: 1280, h: 900 }]) {
    const page = await browser.newPage({ viewport: { width: vp.w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(400);

    const p = await page.evaluate(PROBE);
    console.log('\n' + '='.repeat(80));
    console.log(`VIEWPORT ${vp.w}px  | toolbar content ${p.contentW}px`);
    console.log('='.repeat(80));
    console.log(`  segment ${p.segW}px | wrap=${p.segWrap} | SỐ HÀNG: ${p.segRows} ${p.segRows > 1 ? '← VỠ' : ''}`);
    p.segs.forEach((s) => console.log(`      "${s.text}" w=${s.w}px top=${s.top}`));
    console.log(`  right ${p.rightW}px | wrap=${p.rightWrap} | SỐ HÀNG: ${p.rightRows} | tràn: ${p.overflow}`);
    console.log(`      select-all ${p.selectAllW}px | sort ${p.sortW}px (label ${p.sortLabelW}px + trigger ${p.triggerW}px)`);

    await page.screenshot({ path: path.join(OUT, `tb-${vp.w}.png`), fullPage: true });
    await page.close();
  }
  await browser.close();
})();
