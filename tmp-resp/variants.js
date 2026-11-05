#!/usr/bin/env node
/**
 * Tạo 3 biến thể harness:
 *   A) baseline  — trạng thái hiện tại (không responsive.css)
 *   B) enqueue   — responsive.css ĐƯỢC enqueue (kịch bản "migrate nguyên file")
 *   C) migrate   — chỉ các rule còn sống được chuyển vào file chủ sở hữu
 * rồi chụp computed style ở 640/768/1024px.
 */
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const WORK = path.join(ROOT, 'tmp-resp/variants');

const ORDER = [
  '../style.css', 'main.css', 'footer.css', 'toast.css', 'header-nav.css',
  'home.css', 'shop.css', 'woocommerce.css', 'cart.css', 'checkout-page.css',
  'single-product.css', 'account.css', 'contact.css', 'faq.css', 'wishlist.css',
  'our-ingredients.css', 'sustainability.css', 'login.css', 'register.css',
  'thankyou.css', 'order-modal.css', 'help-center.css', 'gift-cards.css',
  'privacy-policy.css', 'terms-of-service.css', 'cookie-policy.css',
  'careers.css', 'accessibility-statement.css', 'shipping-returns.css',
  'our-story.css', 'product.css', 'checkout-payment.css',
];

// Nhóm typography/button mobile — rule "còn ý định" duy nhất trong responsive.css
const SURVIVING = `
/* ==== SURVIVING (migrated) ==== */
@media (max-width: 768px) {
    .text-display-lg { font-size: 40px !important; line-height: 1.1 !important; }
    .text-headline-lg { font-size: 28px !important; line-height: 1.2 !important; }
    .text-headline-md { font-size: 24px !important; line-height: 1.3 !important; }
    .font-headline.text-headline-lg { font-size: 28px !important; }
}
@media (max-width: 640px) {
    .btn-primary, .btn-ghost { padding: 14px 28px; font-size: 0.875rem; }
}
`;

const MARKUP = fs.readFileSync(path.join(__dirname, 'markup.html'), 'utf8');

const PROPS = [
  'display', 'position', 'top', 'left', 'right', 'bottom', 'width', 'height',
  'max-width', 'min-height', 'padding', 'padding-left', 'padding-right',
  'padding-top', 'padding-bottom', 'margin', 'margin-top', 'margin-left',
  'font-size', 'font-family', 'font-weight', 'line-height', 'letter-spacing',
  'color', 'background-color', 'background-image', 'border', 'border-radius',
  'border-bottom', 'border-top', 'border-left', 'border-right',
  'box-shadow', 'gap', 'grid-template-columns', 'flex-direction', 'flex-wrap',
  'text-align', 'visibility', 'opacity', 'z-index', 'overflow-x', 'backdrop-filter',
  'transform', 'white-space', 'order', 'float',
];

const SELECTORS = [
  'html', 'body',
  '.glass-nav', '.glass-nav > div', '.dg-nav-container',
  '.dg-nav-brand-name', '.dg-nav-toggle', '#dg-mobile-menu-toggle',
  '#dg-mobile-menu', '#dg-mobile-menu-backdrop',
  '.dg-mobile-menu__header', '.dg-mobile-menu__brand-name', '.dg-mobile-menu__close',
  '.dg-mobile-menu__list', '.dg-mobile-menu__list a',
  '.page-hero-section', '.text-display-lg', '.text-headline-lg', '.text-headline-md',
  '.font-headline', '.grid', '.glass-card', '.btn-primary', '.btn-ghost',
  '.footer-main', '.dg-footer-bottom', '.dg-footer-link', '.dg-footer-social-link',
  '.dg-footer-socials', '.dg-footer-column', '.dg-footer-brand',
  '.dg-nav-brand-group', '.dg-nav-actions',
  '.dg-oi-hero-content', '.dg-sr-timeline', '.dg-sr-timeline-item',
  '.dg-account-nav__list', '.dg-cart-row', '.dg-product-card',
];

const BREAKPOINTS = [640, 768, 1024];

function writeMarkup() {
  const m = `<!DOCTYPE html><html class="dg-js"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!--LINKS-->
</head><body>${MARKUP}</body></html>`;
  return m;
}

async function snapshot(dir, extraCss) {
  const links = ORDER.map((f) => {
    const p = path.resolve(CSS_DIR, f);
    if (!fs.existsSync(p)) return '';
    const rel = path.relative(dir, p).replace(/\\/g, '/');
    return `<link rel="stylesheet" href="${rel}">`;
  }).join('\n');

  let html = writeMarkup();
  if (extraCss) {
    const extraPath = path.join(dir, '_extra.css');
    fs.writeFileSync(extraPath, extraCss, 'utf8');
    html = html.replace('<!--LINKS-->', links + '\n<link rel="stylesheet" href="_extra.css">');
  } else {
    html = html.replace('<!--LINKS-->', links);
  }

  const htmlPath = path.join(dir, 'harness.html');
  fs.writeFileSync(htmlPath, html, 'utf8');

  const browser = await chromium.launch();
  const out = {};
  for (const bp of BREAKPOINTS) {
    const page = await browser.newPage({ viewport: { width: bp, height: 900 } });
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'));
    await page.waitForTimeout(350);
    out[bp] = await page.evaluate(({ SELECTORS, PROPS }) => {
      const r = {};
      for (const sel of SELECTORS) {
        const el = document.querySelector(sel);
        if (!el) { r[sel] = null; continue; }
        const cs = getComputedStyle(el);
        const o = {};
        for (const p of PROPS) o[p] = cs[p];
        const bb = el.getBoundingClientRect();
        o.__rect = `${Math.round(bb.width)}x${Math.round(bb.height)}`;
        r[sel] = o;
      }
      return r;
    }, { SELECTORS, PROPS });
    await page.close();
  }
  await browser.close();
  return out;
}

(async () => {
  // A) baseline
  const dirA = path.join(WORK, 'a-baseline');
  fs.mkdirSync(dirA, { recursive: true });
  const A = await snapshot(dirA, null);
  fs.writeFileSync(path.join(WORK, 'a.json'), JSON.stringify(A, null, 2));
  console.log('✓ A) baseline');

  // B) responsive.css được enqueue (nguyên file, từ HEAD)
  const dirB = path.join(WORK, 'b-enqueue');
  fs.mkdirSync(dirB, { recursive: true });
  const origResp = execSync(
    'git show HEAD:wp-content/themes/dragon-glow/assets/css/responsive.css',
    { cwd: ROOT, encoding: 'utf8', maxBuffer: 1024 * 1024 }
  );
  const B = await snapshot(dirB, origResp);
  fs.writeFileSync(path.join(WORK, 'b.json'), JSON.stringify(B, null, 2));
  console.log('✓ B) enqueue nguyên responsive.css');

  // C) chỉ rule còn sống, migrate vào file chủ sở hữu
  const dirC = path.join(WORK, 'c-migrate');
  fs.mkdirSync(dirC, { recursive: true });
  const C = await snapshot(dirC, SURVIVING);
  fs.writeFileSync(path.join(WORK, 'c.json'), JSON.stringify(C, null, 2));
  console.log('✓ C) migrate rule còn sống');
})();
