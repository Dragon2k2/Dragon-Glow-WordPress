#!/usr/bin/env node
/**
 * Với mỗi rule trong responsive.css: tìm rule tương đương đã tồn tại ở file khác chưa.
 */
const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const ROOT = path.resolve(__dirname, '..');
const CSS_DIR = path.join(ROOT, 'wp-content/themes/dragon-glow/assets/css');
const target = path.join(CSS_DIR, 'responsive.css');

const root = postcss.parse(fs.readFileSync(target, 'utf8'));

const files = fs.readdirSync(CSS_DIR).filter((f) => f.endsWith('.css') && f !== 'responsive.css');
const others = {};
for (const f of files) {
  others[f] = fs.readFileSync(path.join(CSS_DIR, f), 'utf8');
}
const styleCss = fs.readFileSync(path.join(ROOT, 'wp-content/themes/dragon-glow/style.css'), 'utf8');
others['style.css'] = styleCss;

const index = new Map(); // selector -> [{file, media, line}]
for (const [f, src] of Object.entries(others)) {
  const r = postcss.parse(src, { from: f });
  r.walkRules((rule) => {
    const media = rule.parent.type === 'atrule' ? rule.parent.params : null;
    for (const sel of rule.selector.split(',').map((s) => s.trim())) {
      if (!index.has(sel)) index.set(sel, []);
      index.get(sel).push({ file: f, media, line: rule.source.start.line, decls: rule.nodes.map((d) => d.prop + ':' + d.value).join(';') });
    }
  });
}

console.log('='.repeat(100));
console.log('RULE TRONG responsive.css  →  ĐÃ CÓ SẴN Ở FILE KHÁC?');
console.log('='.repeat(100));

root.walkAtRules('media', (at) => {
  const media = at.params;
  at.walkRules((rule) => {
    for (const sel of rule.selector.split(',').map((s) => s.trim())) {
      const dup = (index.get(sel) || []).filter((d) => d.media === media);
      const dupAny = index.get(sel) || [];
      const tag = dup.length ? 'DUP-EXACT' : dupAny.length ? 'SEL-EXISTS' : 'NEW';
      console.log(`\n[${tag}] ${sel}   @media(${media})  (L${rule.source.start.line})`);
      for (const d of dup) console.log(`    ≡ ${d.file}:${d.line}  ${d.decls}`);
      if (!dup.length) for (const d of dupAny) console.log(`    ~ ${d.file}:${d.line} @media(${d.media}) ${d.decls}`);
    }
  });
});
