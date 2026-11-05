#!/usr/bin/env node
/**
 * Kiểm tra từng selector trong responsive.css: có thực sự được dùng trong
 * theme (template PHP / CSS khác) không?
 */
const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const ROOT = path.resolve(__dirname, '..');
const THEME_REL = 'wp-content/themes/dragon-glow';
const THEME = path.join(ROOT, THEME_REL);
const CSS_DIR = path.join(THEME, 'assets/css');

const target = path.join(CSS_DIR, 'responsive.css');
const css = fs.readFileSync(target, 'utf8');
const root = postcss.parse(css);

// Thu thập selector (bỏ @media wrapper)
const selectors = [];
root.walkRules((r) => {
  for (const sel of r.selector.split(',').map((s) => s.trim())) {
    if (!sel) continue;
    selectors.push({ sel, media: r.parent.type === 'atrule' ? r.parent.params : null, line: r.source.start.line });
  }
});

// Load toàn bộ source khác (không tính responsive.css)
const sources = [];
function walk(dir, out = []) {
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    if (e.name === '.git' || e.name === 'node_modules') continue;
    const p = path.join(dir, e.name);
    if (e.isDirectory()) walk(p, out);
    else out.push(p);
  }
  return out;
}
for (const p of walk(THEME)) {
  if (p === target) continue;
  if (!/\.(css|php|js|html)$/i.test(p)) continue;
  try { sources.push({ p, s: fs.readFileSync(p, 'utf8') }); } catch {}
}
const all = sources.map((x) => x.s).join('\n');

/** Tìm nơi selector được dùng (class trong HTML/PHP, hoặc selector trong CSS khác). */
function findUsage(sel) {
  // Bỏ escape backslash của Tailwind (.md\:hidden → .md:hidden)
  const clean = sel.replace(/\\/g, '');
  // class chính: lấy phần sau "." và cắt ở pseudo/descendant
  const cls = [...clean.matchAll(/\.([a-zA-Z0-9_-]+)/g)].map((m) => m[1]);
  const ids = clean.match(/#[a-zA-Z0-9_-]+/g) || [];

  const esc = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const hits = [];
  for (const { p, s } of sources) {
    for (const c of cls) {
      const re = new RegExp(`class=["'][^"']*\\b${esc(c)}\\b`, 'i');
      if (re.test(s)) hits.push(`${path.relative(ROOT, p).replace(/\\/g, '/')}  [class .${c}]`);
    }
    for (const id of ids) {
      const re = new RegExp(`id=["']${esc(id.slice(1))}\\b`, 'i');
      if (re.test(s)) hits.push(`${path.relative(ROOT, p).replace(/\\/g, '/')}  [id ${id}]`);
    }
    if (p.endsWith('.css')) {
      for (const c of cls) {
        if (new RegExp(`\\.${esc(c)}(?![a-zA-Z0-9_-])`).test(s)) {
          hits.push(`${path.relative(ROOT, p).replace(/\\/g, '/')}  [css .${c}]`);
        }
      }
    }
  }
  return [...new Set(hits)];
}

console.log('='.repeat(95));
console.log('PHÂN TÍCH SELECTOR TRONG responsive.css');
console.log('='.repeat(95));

const used = [], unused = [];
for (const s of selectors) {
  const hits = findUsage(s.sel);
  (hits.length ? used : unused).push({ ...s, hits });
}

console.log(`\nTổng selector: ${selectors.length} | CÓ dùng: ${used.length} | KHÔNG dùng: ${unused.length}\n`);

console.log('--- KHÔNG DÙNG (dead selector) ---');
for (const u of unused) {
  console.log(`  L${String(u.line).padStart(4)}  ${u.sel}${u.media ? `   @media(${u.media})` : ''}`);
}

console.log('\n--- CÓ DÙNG ---');
for (const u of used) {
  console.log(`  L${String(u.line).padStart(4)}  ${u.sel}${u.media ? `   @media(${u.media})` : ''}`);
  for (const h of u.hits.slice(0, 3)) console.log(`           → ${h}`);
}
