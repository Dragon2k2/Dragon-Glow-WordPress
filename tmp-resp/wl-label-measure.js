#!/usr/bin/env node
/**
 * Đo bề rộng label stats hero Wishlist ở mobile — tìm nguyên nhân label
 * "Saved items" rớt 2 dòng trong grid 2 cột.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/wl-label');

const MARKUP = `
<body style="margin:0; background:#f5f3ef;">
  <main class="dg-wishlist">
    <div class="dg-wishlist-wrap">
      <section class="dg-wishlist-hero">
        <div class="dg-wishlist-hero__inner">
          <div class="dg-wishlist-hero__copy"><h1 class="dg-wishlist-hero__title">My wishlist</h1></div>
          <dl class="dg-wishlist-hero__stats" id="stats">
            <div class="dg-wishlist-hero__stat">
              <dt class="dg-wishlist-hero__stat-label" id="lbl-saved">Saved items</dt>
              <dd class="dg-wishlist-hero__stat-value">0</dd>
            </div>
            <div class="dg-wishlist-hero__stat">
              <dt class="dg-wishlist-hero__stat-label">In stock</dt>
              <dd class="dg-wishlist-hero__stat-value">0</dd>
            </div>
            <div class="dg-wishlist-hero__stat">
              <dt class="dg-wishlist-hero__stat-label dg-wishlist-hero__stat-label--stacked"><span>On</span><span>sale</span></dt>
              <dd class="dg-wishlist-hero__stat-value">0</dd>
            </div>
            <div class="dg-wishlist-hero__stat">
              <dt class="dg-wishlist-hero__stat-label">You save</dt>
              <dd class="dg-wishlist-hero__stat-value">$0.00</dd>
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
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=block">
${LINKS}
</head>${MARKUP}</html>`;

const PROBE = () => {
  const labels = Array.from(document.querySelectorAll('.dg-wishlist-hero__stat-label'));
  const stat = document.querySelector('.dg-wishlist-hero__stat');
  const scs = getComputedStyle(stat);
  const statW = stat.getBoundingClientRect().width;
  const contentW = statW - parseFloat(scs.paddingLeft) - parseFloat(scs.paddingRight);
  return {
    statW: Math.round(statW),
    contentW: Math.round(contentW),
    labels: labels.map((l) => {
      const cs = getComputedStyle(l);
      const r = l.getBoundingClientRect();
      // chiều cao 1 dòng = line-height × 1
      const lh = parseFloat(cs.lineHeight) || parseFloat(cs.fontSize) * 1.2;
      const lines = Math.round(r.height / lh);
      // bề rộng text thật (dùng scrollWidth của span con nếu có)
      const inner = l.querySelector('span');
      const textW = inner ? inner.getBoundingClientRect().width : r.width;
      return {
        text: l.textContent.trim().replace(/\s+/g, ' '),
        w: Math.round(r.width),
        textW: Math.round(textW),
        lines,
        fontSize: cs.fontSize,
        letterSpacing: cs.letterSpacing,
        whiteSpace: cs.whiteSpace,
      };
    }),
  };
};

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'wl-label.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  for (const vp of [{ w: 360, h: 900 }, { w: 390, h: 900 }, { w: 430, h: 900 }, { w: 640, h: 900 }, { w: 1280, h: 900 }]) {
    const page = await browser.newPage({ viewport: { width: vp.w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(500);

    const p = await page.evaluate(PROBE);
    console.log('\n' + '='.repeat(80));
    console.log(`VIEWPORT ${vp.w}px  | stat ${p.statW}px, content ${p.contentW}px`);
    console.log('='.repeat(80));
    p.labels.forEach((l) => {
      const fits = l.textW <= p.contentW;
      console.log(`  ${l.lines > 1 ? '✗' : '✓'} "${l.text}" — ${l.lines} dòng | w ${l.w}px (text ${l.textW}px) | ${l.fontSize} ls=${l.letterSpacing} ws=${l.whiteSpace} | ${fits ? 'vừa content box' : 'TRÀN'}`);
    });

    await page.close();
  }
  await browser.close();
})();
