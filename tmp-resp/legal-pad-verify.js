#!/usr/bin/env node
/**
 * Verify padding-bottom của các page-level <main> pháp lý:
 *   .dg-privacy · .dg-cookie · .dg-terms · .dg-accessibility
 * Đo khoảng cách thực tế từ nội dung cuối tới footer ở 3 breakpoint.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const OUT = path.join(ROOT, 'tmp-resp/legal-pad');

const PAGES = [
  { key: 'privacy', main: 'dg-privacy', wrap: 'dg-privacy-wrap', css: 'privacy-policy.css' },
  { key: 'cookie', main: 'dg-cookie', wrap: 'dg-cookie-wrap', css: 'cookie-policy.css' },
  { key: 'terms', main: 'dg-terms', wrap: 'dg-terms-wrap', css: 'terms-of-service.css' },
  { key: 'accessibility', main: 'dg-accessibility', wrap: 'dg-accessibility-wrap', css: 'accessibility-statement.css' },
];

const MARKUP = PAGES.map((p) => `
  <main class="${p.main}" id="main-content">
    <div class="${p.wrap}">
      <header class="${p.main}-header">
        <h1 class="${p.main}-title">Legal document</h1>
        <p class="${p.main}-intro">Intro paragraph of the legal document.</p>
      </header>
      <div class="${p.main}-layout">
        <div class="${p.main}-content">
          <section class="${p.main}-section"><p>Body text of the section.</p></section>
        </div>
      </div>
    </div>
  </main>`).join('\n');

const LINKS = [
  '../../style.css', 'main.css', 'privacy-policy.css', 'cookie-policy.css',
  'terms-of-service.css', 'accessibility-statement.css',
].map((f) => `<link rel="stylesheet" href="${path.relative(OUT, path.resolve(CSS_DIR, f)).replace(/\\/g, '/')}">`).join('\n');

const HTML = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
${LINKS}
</head><body style="margin:0;">${MARKUP}
<footer id="colophon" style="background:#222;color:#fff;padding:40px;text-align:center;">FOOTER</footer>
</body></html>`;

const PROBE = (sel) => {
  const main = document.querySelector(sel);
  if (!main) return null;
  const cs = getComputedStyle(main);
  const wrap = main.querySelector('[class*="-wrap"]');
  const wr = wrap.getBoundingClientRect();
  const mr = main.getBoundingClientRect();
  // main là block: border-box bottom = nội dung + padding-bottom.
  // Vì 4 trang nằm cạnh nhau trong 1 test doc, KHÔNG dùng footer để đo.
  return {
    paddingBottom: cs.paddingBottom,
    paddingActual: Math.round(mr.bottom - wr.bottom),
    mainMarginBottom: cs.marginBottom,
  };
};

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const htmlPath = path.join(OUT, 'legal-pad.html');
  fs.writeFileSync(htmlPath, HTML, 'utf8');

  const browser = await chromium.launch();
  let allPass = true;
  const fail = [];
  const check = (name, ok, detail) => {
    console.log(`    ${ok ? '✓' : '✗'} ${name}${detail ? ' — ' + detail : ''}`);
    if (!ok) { allPass = false; fail.push(name + (detail ? ': ' + detail : '')); }
  };

  for (const vp of [{ w: 390, h: 900 }, { w: 768, h: 900 }, { w: 1280, h: 900 }]) {
    const page = await browser.newPage({ viewport: { width: vp.w, height: vp.h } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(250);

    console.log('\n' + '='.repeat(78));
    console.log(`VIEWPORT ${vp.w}px`);
    console.log('='.repeat(78));

    for (const p of PAGES) {
      const r = await page.evaluate(PROBE, '.' + p.main);
      const expected = parseFloat(r.paddingBottom);
      console.log(`  ${p.main}`);
      console.log(`      padding-bottom ${r.paddingBottom} | khoảng đệm thực tế ${r.paddingActual}px`);
      check('padding-bottom ≥ 4rem (64px)', expected >= 64, r.paddingBottom);
      check('khoảng đệm thực tế khớp padding-bottom', Math.abs(r.paddingActual - expected) <= 1, `${r.paddingActual}px ≈ ${expected}px`);
      check('main không có margin-bottom (không nhân đôi khoảng đệm)', r.mainMarginBottom === '0px', r.mainMarginBottom);
    }

    await page.screenshot({ path: path.join(OUT, `pad-${vp.w}.png`), fullPage: true });
    await page.close();
  }

  await browser.close();
  console.log('\n' + '='.repeat(78));
  if (allPass) {
    console.log('✓ CẢ 4 TRANG PHÁP LÝ ĐỀU CÓ PADDING-BOTTOM ĐÚNG');
  } else {
    console.log('✗ FAIL (' + fail.length + '):');
    fail.forEach((f) => console.log('   - ' + f));
  }
  console.log('='.repeat(78));
  process.exit(allPass ? 0 : 1);
})();
