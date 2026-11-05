#!/usr/bin/env node
/**
 * Verify label stats hero Wishlist:
 * - Mobile (< 640px): label thường ("Saved items", "In stock", "You save")
 *   hiển thị ĐÚNG 1 DÒNG và KHÔNG tràn ra ngoài card.
 * - Label "On sale" (--stacked) giữ nguyên 2 dòng theo thiết kế.
 * - Desktop/tablet: GIỮ NGUYÊN hành vi cũ (không áp nowrap).
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
          <dl class="dg-wishlist-hero__stats">
            <div class="dg-wishlist-hero__stat">
              <dt class="dg-wishlist-hero__stat-label">Saved items</dt>
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
  const cards = Array.from(document.querySelectorAll('.dg-wishlist-hero__stat'));
  return cards.map((card) => {
    const label = card.querySelector('.dg-wishlist-hero__stat-label');
    const cs = getComputedStyle(label);
    const scs = getComputedStyle(card);
    const cr = card.getBoundingClientRect();
    const lr = label.getBoundingClientRect();
    const lh = parseFloat(cs.lineHeight) || parseFloat(cs.fontSize) * 1.2;
    const lines = Math.round(lr.height / lh);
    const stacked = label.classList.contains('dg-wishlist-hero__stat-label--stacked');
    // Với label 2 span, đo bề rộng TỔNG các span (bỏ margin) để biết
    // nhãn có tràn ra ngoài content box không.
    const spans = Array.from(label.querySelectorAll('span'));
    const textW = spans.length
      ? spans.reduce((sum, s) => sum + s.getBoundingClientRect().width, 0)
      : lr.width;
    return {
      text: label.textContent.trim().replace(/\s+/g, ' '),
      stacked,
      lines,
      whiteSpace: cs.whiteSpace,
      letterSpacing: cs.letterSpacing,
      labelW: Math.round(lr.width),
      textW: Math.round(textW),
      cardContentW: Math.round(cr.width - parseFloat(scs.paddingLeft) - parseFloat(scs.paddingRight)),
      overflow: lr.width > (cr.width - parseFloat(scs.paddingLeft) - parseFloat(scs.paddingRight)) + 0.5,
    };
  });
};

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'wl-label.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;
  const fail = [];
  const check = (name, ok, detail) => {
    console.log(`  ${ok ? '✓' : '✗'} ${name}${detail ? ' — ' + detail : ''}`);
    if (!ok) { allPass = false; fail.push(name + (detail ? ': ' + detail : '')); }
  };

  const CASES = [
    { name: 'MOBILE  320px', w: 320, mobile: true },
    { name: 'MOBILE  360px', w: 360, mobile: true },
    { name: 'MOBILE  390px', w: 390, mobile: true },
    { name: 'MOBILE  430px', w: 430, mobile: true },
    { name: 'MOBILE  639px', w: 639, mobile: true },
    { name: 'TABLET 640px', w: 640, mobile: false },
    { name: 'DESKTOP 1280px', w: 1280, mobile: false },
  ];

  for (const c of CASES) {
    const page = await browser.newPage({ viewport: { width: c.w, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(400);

    const rows = await page.evaluate(PROBE);
    console.log('\n' + '='.repeat(84));
    console.log(`${c.name}  (${c.mobile ? 'MOBILE — label phải 1 dòng' : 'DESKTOP/TABLET — giữ nguyên'})`);
    console.log('='.repeat(84));

    for (const r of rows) {
      const tag = r.stacked ? '[stacked]' : '';
      console.log(`    "${r.text}"${tag} — ${r.lines} dòng | label ${r.labelW}px / content ${r.cardContentW}px | ws=${r.whiteSpace} ls=${r.letterSpacing}${r.overflow ? ' | TRÀN' : ''}`);
    }

    const normal = rows.filter((r) => !r.stacked);
    const stacked = rows.filter((r) => r.stacked);

    if (c.mobile) {
      check('mọi label thường 1 dòng', normal.every((r) => r.lines === 1), normal.map((r) => `${r.text}=${r.lines}`).join(', '));
      check('label "On sale" cũng 1 dòng', stacked.length === 1 && stacked[0].lines === 1, `lines=${stacked[0] && stacked[0].lines}`);
      check('không label nào tràn ra ngoài card', rows.every((r) => !r.overflow), rows.filter((r) => r.overflow).map((r) => r.text).join(', '));
    } else {
      check('KHÔNG áp nowrap (giữ hành vi cũ)', normal.every((r) => r.whiteSpace === 'normal'), normal.map((r) => r.whiteSpace).join(', '));
      check('letter-spacing giữ nguyên 0.10em', normal.every((r) => r.letterSpacing === '1.1px'), normal.map((r) => r.letterSpacing).join(', '));
      check('label "On sale" giữ 2 dòng (thiết kế)', stacked.length === 1 && stacked[0].lines === 2, `lines=${stacked[0] && stacked[0].lines}`);
    }

    await page.screenshot({ path: path.join(OUT, `label-${c.w}.png`), fullPage: true });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(84));
  if (allPass) {
    console.log('✓ PASS — mobile label 1 dòng, desktop/tablet giữ nguyên');
  } else {
    console.log('✗ FAIL (' + fail.length + '):');
    fail.forEach((f) => console.log('   - ' + f));
  }
  console.log('='.repeat(84));
  process.exit(allPass ? 0 : 1);
})();
