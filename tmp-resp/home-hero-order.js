#!/usr/bin/env node
/**
 * Verify thứ tự 2 khối hero ở mobile:
 *   mobile (<640px): product frame TRÊN, copy column DƯỚI
 *   tablet (640-1023) & desktop (>=1024): copy TRÊN, visual DƯỚI (không đổi)
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/home-hero');

const MARKUP = `
<body style="margin:0; background:#f7f4ef;">
  <section class="dg-home-hero-wrapper">
    <div class="dg-home-hero max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center pt-10 pb-0">
      <div class="lg:col-span-6 flex flex-col items-start gap-6 z-10" data-role="copy">
        <div class="dg-home-hero__badge inline-flex items-center gap-2 px-4 py-1.5 rounded-full">
          <span class="inline-flex items-center justify-center w-2 h-2 rounded-full"></span>
          <span class="font-label-sm uppercase tracking-widest">2026 Cellular Drop 04</span>
        </div>
        <div class="space-y-2">
          <h1 class="dg-home-hero__title font-display-xl text-display-xl lg:text-[72px]">Get Your Dragon Glaze On.</h1>
        </div>
        <p class="dg-home-hero__desc font-body-lg text-body-lg max-w-xl leading-relaxed">
          An ancient Dragon Superfruit ritual, perfected by clinical skincare.
        </p>
        <div class="flex flex-wrap items-center gap-4 pt-2 w-full sm:w-auto">
          <a href="#" class="dg-home-hero__cta-primary w-full sm:w-auto">Shop The Glaze Ritual</a>
          <a href="#" class="dg-home-hero__cta-secondary w-full sm:w-auto">Discover Our Story</a>
        </div>
        <div class="dg-home-hero__stats pt-6 w-full flex items-center gap-6 sm:gap-10">
          <div class="dg-home-hero__stat flex flex-col"><span class="dg-home-hero__stat-value">+98.4%</span><span class="dg-home-hero__stat-label">Instant Surface Glass Reflection</span></div>
          <div class="dg-home-hero__stat flex flex-col"><span class="dg-home-hero__stat-value">72 Hrs</span><span class="dg-home-hero__stat-label">Continuous Lipid Hydration</span></div>
        </div>
      </div>
      <div class="dg-home-hero__visual lg:col-span-6 relative flex justify-center items-center">
        <div class="dg-home-hero__product-frame relative w-full max-w-[520px] aspect-[4/5] rounded-[2.5rem] p-3">
          <div class="w-full h-full bg-gradient-to-br from-primary-container to-secondary-container rounded-[2rem]" style="height:100%;"></div>
          <div class="dg-home-hero__info-card absolute bottom-6 left-6 right-6 rounded-2xl flex items-center justify-between">
            <div class="dg-home-hero__info-main flex items-center"><div class="dg-home-hero__info-text"><h4>Dragon Glaze Elixir</h4><p>Bio-Fermented Superfruit Complex</p></div></div>
            <div class="dg-home-hero__info-meta flex flex-col items-end"><span class="dg-info-card-price">$52.00</span><span class="dg-info-card-restock">Restocked 4h ago</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>`;

const LINKS = ['../../style.css', 'main.css', 'home.css']
  .map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`)
  .join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Plus+Jakarta+Sans:wght@400;600;700&display=block">
${LINKS}
<script src="https://cdn.tailwindcss.com"></script>
</head>${MARKUP}</html>`;

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'hero.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  for (const w of [320, 390, 430, 639, 640, 768, 1023, 1024, 1280]) {
    const page = await browser.newPage({ viewport: { width: w, height: 1000 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(1800);

    const r = await page.evaluate(() => {
      const hero = document.querySelector('.dg-home-hero');
      const copy = document.querySelector('[data-role="copy"]');
      const visual = document.querySelector('.dg-home-hero__visual');
      const frame = document.querySelector('.dg-home-hero__product-frame');
      const cs = getComputedStyle(visual);
      const fcs = getComputedStyle(frame);
      const cr = copy.getBoundingClientRect();
      const vr = visual.getBoundingClientRect();
      // Số hàng của grid: 1 cột → 2 hàng (visual + copy xếp dọc)
      const rows = getComputedStyle(hero).gridTemplateRows.split(' ').filter(Boolean).length;
      return {
        rows,
        cols: getComputedStyle(hero).gridTemplateColumns,
        copyTop: Math.round(cr.top),
        copyLeft: Math.round(cr.left),
        copyH: Math.round(cr.height),
        visualTop: Math.round(vr.top),
        visualLeft: Math.round(vr.left),
        visualH: Math.round(vr.height),
        visualW: Math.round(vr.width),
        frameW: Math.round(frame.getBoundingClientRect().width),
        frameH: Math.round(frame.getBoundingClientRect().height),
        order: cs.order,
        frameMarginTop: fcs.marginTop,
      };
    });

    // Grid 1 hàng (desktop 12 cột) → 2 khối CẠNH NHAU → so theo trục ngang.
    // Grid nhiều hàng (mobile/tablet 1 cột) → 2 khối XẾP DỌC → so theo trục dọc.
    const sideBySide = r.rows === 1;
    const copyFirst = sideBySide ? r.copyLeft < r.visualLeft : r.copyTop < r.visualTop;
    const tag = w < 640 ? 'MOBILE' : w < 1024 ? 'TABLET' : 'DESKTOP';
    const ok = w < 640 ? !copyFirst : copyFirst;

    console.log(`\n--- ${w}px [${tag}] ---`);
    console.log(`  grid: ${r.cols.length > 50 ? r.cols.slice(0, 40) + '…' : r.cols} | SỐ HÀNG: ${r.rows} → ${sideBySide ? 'cạnh nhau' : 'xếp dọc'}`);
    console.log(`  copy   top=${r.copyTop} left=${r.copyLeft} h=${r.copyH}`);
    console.log(`  visual top=${r.visualTop} left=${r.visualLeft} w=${r.visualW} order=${r.order}`);
    console.log(`  frame w=${r.frameW} h=${r.frameH} marginTop=${r.frameMarginTop}`);
    console.log(`  → ${w < 640 ? 'visual TRÊN, copy DƯỚI' : 'copy TRƯỚC, visual SAU'} | ${ok ? '✓ ĐÚNG' : '✗ SAI'}`);

    await page.screenshot({ path: path.join(OUT, `hero-${w}.png`), fullPage: true });
    await page.close();
  }
  await browser.close();
})();
