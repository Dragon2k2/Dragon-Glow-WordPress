#!/usr/bin/env node
/**
 * Verify padding-bottom của <main class="dg-privacy"> — khoảng cách thực tế
 * giữa nội dung cuối và footer, ở 3 breakpoint.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/privacy-pad');

const MARKUP = `
<body style="margin:0;">
  <main class="dg-privacy" id="main-content">
    <div class="dg-privacy-wrap">
      <header class="dg-privacy-header">
        <p class="dg-privacy-eyebrow">Legal</p>
        <h1 class="dg-privacy-title">Privacy Policy</h1>
      </header>
      <div class="dg-privacy-layout">
        <nav class="dg-privacy-toc"><ul><li><a href="#s1">Section 1</a></li></ul></nav>
        <div class="dg-privacy-content">
          <section class="dg-privacy-section"><h2>Section</h2><p>Body text of the legal section goes here.</p></section>
        </div>
      </div>
    </div>
  </main>
  <footer id="colophon" style="background:#222;color:#fff;padding:40px;text-align:center;">FOOTER</footer>
</body>`;

const LINKS = [
  '../../style.css', 'main.css', 'privacy-policy.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head>${MARKUP}</html>`;

const PROBE = () => {
  const main = document.querySelector('.dg-privacy');
  const cs = getComputedStyle(main);
  const footer = document.querySelector('#colophon');
  const mr = main.getBoundingClientRect();
  const fr = footer.getBoundingClientRect();
  // Nội dung cuối cùng trong main (bỏ qua padding-bottom)
  const wrap = document.querySelector('.dg-privacy-wrap');
  const wr = wrap.getBoundingClientRect();
  return {
    paddingBottom: cs.paddingBottom,
    mainBottom: Math.round(mr.bottom),
    footerTop: Math.round(fr.top),
    gap: Math.round(fr.top - mr.bottom),
    wrapBottom: Math.round(wr.bottom),
    contentToFooter: Math.round(fr.top - wr.bottom),
  };
};

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'privacy-pad.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;

  for (const vp of [{ w: 390, h: 900 }, { w: 768, h: 900 }, { w: 1280, h: 900 }]) {
    const page = await browser.newPage({ viewport: { width: vp.w, height: vp.h } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(250);

    const p = await page.evaluate(PROBE);
    console.log('\n' + '='.repeat(76));
    console.log(`VIEWPORT ${vp.w}px`);
    console.log('='.repeat(76));
    console.log(`  padding-bottom của .dg-privacy : ${p.paddingBottom}`);
    console.log(`  main bottom ${p.mainBottom}px → footer top ${p.footerTop}px  (gap ${p.gap}px)`);
    console.log(`  nội dung cuối (wrap bottom ${p.wrapBottom}px) → footer ${p.contentToFooter}px`);

    const expected = parseFloat(p.paddingBottom);
    const ok = expected >= 64;
    console.log(`  ${ok ? '✓' : '✗'} padding-bottom ≥ 4rem (64px) — ${p.paddingBottom}`);
    if (!ok) allPass = false;

    // Padding nằm BÊN TRONG <main> → main.bottom === footer.top (gap 0).
    // Khoảng đệm thực sự là khoảng cách từ NỘI DUNG cuối (wrap bottom)
    // tới footer → phải bằng padding-bottom.
    const gapOk = Math.abs(p.contentToFooter - expected) <= 1;
    console.log(`  ${gapOk ? '✓' : '✗'} khoảng đệm nội dung → footer khớp padding-bottom (${p.contentToFooter}px ≈ ${expected}px)`);
    if (!gapOk) allPass = false;

    await page.screenshot({ path: path.join(OUT, `pad-${vp.w}.png`), fullPage: true });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(76));
  console.log(allPass ? '✓ PADDING-BOTTOM OK Ở MỌI BREAKPOINT' : '✗ FAIL');
  console.log('='.repeat(76));
  process.exit(allPass ? 0 : 1);
})();
