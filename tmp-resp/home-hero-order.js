#!/usr/bin/env node
/**
 * Verify thứ tự 3 khối hero ở mobile:
 *   mobile (<640px): badge TRÊN → product frame → copy body (title/desc/CTA/stats)
 *   tablet (640-1023) & desktop (>=1024): KHÔNG ĐỔI (copy cột trái, visual cột phải)
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
      <div class="dg-home-hero__copy lg:col-span-6 flex flex-col items-start gap-6 z-10" data-role="copy">
        <div class="dg-home-hero__badge inline-flex items-center gap-2 px-4 py-1.5 rounded-full" data-role="badge">
          <span class="inline-flex items-center justify-center w-2 h-2 rounded-full"></span>
          <span class="font-label-sm uppercase tracking-widest">2026 Cellular Drop 04</span>
          <span class="dg-home-hero__badge-sep">•</span>
          <span class="font-label-sm">Ultra-Bounce Skin</span>
        </div>
        <div class="space-y-2" data-role="headline">
          <h1 class="dg-home-hero__title font-display-xl text-display-xl lg:text-[72px]">Get Your Dragon Glaze On.</h1>
        </div>
        <p class="dg-home-hero__desc font-body-lg text-body-lg max-w-xl leading-relaxed" data-role="desc">
          An ancient Dragon Superfruit ritual, perfected by clinical skincare.
        </p>
        <div class="flex flex-wrap items-center gap-4 pt-2 w-full sm:w-auto" data-role="cta">
          <a href="#" class="dg-home-hero__cta-primary w-full sm:w-auto">Shop The Glaze Ritual</a>
          <a href="#" class="dg-home-hero__cta-secondary w-full sm:w-auto">Discover Our Story</a>
        </div>
        <div class="dg-home-hero__stats pt-6 w-full flex items-center gap-6 sm:gap-10" data-role="stats">
          <div class="dg-home-hero__stat flex flex-col"><span class="dg-home-hero__stat-value">+98.4%</span><span class="dg-home-hero__stat-label">Instant Surface Glass Reflection</span></div>
          <div class="dg-home-hero__stat flex flex-col"><span class="dg-home-hero__stat-value">72 Hrs</span><span class="dg-home-hero__stat-label">Continuous Lipid Hydration</span></div>
        </div>
      </div>
      <div class="dg-home-hero__visual lg:col-span-6 relative flex justify-center items-center" data-role="visual">
        <div class="dg-home-hero__product-frame relative w-full max-w-[520px] aspect-[4/5] rounded-[2.5rem] p-3" data-role="frame">
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
      const q = (role) => document.querySelector(`[data-role="${role}"]`);
      const box = (role) => {
        const el = q(role);
        if (!el) return null;
        const b = el.getBoundingClientRect();
        return { top: Math.round(b.top), left: Math.round(b.left), w: Math.round(b.width), h: Math.round(b.height) };
      };
      const rows = getComputedStyle(hero).gridTemplateRows.split(' ').filter(Boolean).length;
      const copy = document.querySelector('.dg-home-hero__copy');
      return {
        rows,
        copyDisplay: getComputedStyle(copy).display,
        badge: box('badge'),
        frame: box('frame'),
        visual: box('visual'),
        headline: box('headline'),
        desc: box('desc'),
        cta: box('cta'),
        stats: box('stats'),
        visualOrder: getComputedStyle(q('visual')).order,
        badgeOrder: getComputedStyle(q('badge')).order,
        rowGap: getComputedStyle(hero).rowGap,
        scrollW: document.documentElement.scrollWidth,
        clientW: document.documentElement.clientWidth,
      };
    });

    const sideBySide = r.rows === 1;
    const tag = w < 640 ? 'MOBILE' : w < 1024 ? 'TABLET' : 'DESKTOP';
    console.log(`\n--- ${w}px [${tag}] ---`);
    console.log(`  grid rows=${r.rows} rowGap=${r.rowGap} copy.display=${r.copyDisplay}`);
    console.log(`  badge  order=${r.badgeOrder} top=${r.badge && r.badge.top}`);
    console.log(`  frame  top=${r.frame && r.frame.top} h=${r.frame && r.frame.h}`);
    console.log(`  visual order=${r.visualOrder} top=${r.visual && r.visual.top}`);
    console.log(`  headline top=${r.headline && r.headline.top} | desc top=${r.desc && r.desc.top} | cta top=${r.cta && r.cta.top} | stats top=${r.stats && r.stats.top}`);

    let ok;
    if (w < 1024) {
      // Mobile & tablet: badge TRÊN → frame → copy body
      const b = r.badge, f = r.frame, h = r.headline;
      ok = b && f && h && b.top < f.top && f.top < h.top;
      console.log(`  → ${ok ? '✓ badge TRÊN, frame GIỮA, copy DƯỚI' : '✗ SAI'}`);
    } else {
      // Desktop: copy trái, visual phải
      const c = r.badge, v = r.visual;
      ok = c && v && c.left < v.left;
      console.log(`  → ${ok ? '✓ copy TRÁI, visual PHẢI (không đổi)' : '✗ SAI'}`);
    }

    const overflow = r.scrollW > r.clientW;
    if (overflow) console.log(`  ⚠ OVERFLOW ngang: scrollW=${r.scrollW} > clientW=${r.clientW}`);

    await page.screenshot({ path: path.join(OUT, `hero-${w}.png`), fullPage: true });
    await page.close();
  }
  await browser.close();
})();
