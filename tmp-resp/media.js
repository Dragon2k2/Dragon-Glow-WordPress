#!/usr/bin/env node
/**
 * Liệt kê mọi @media + selector trong các file đích để đối chiếu với responsive.css
 */
const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const CSS_DIR = path.resolve(__dirname, '../wp-content/themes/dragon-glow/assets/css');
const targets = process.argv.slice(2);

for (const f of targets) {
  const p = path.join(CSS_DIR, f);
  if (!fs.existsSync(p)) { console.log(`--- ${f}: NOT FOUND`); continue; }
  const root = postcss.parse(fs.readFileSync(p, 'utf8'), { from: f });
  console.log(`\n${'='.repeat(90)}\n### ${f}\n${'='.repeat(90)}`);
  root.walkAtRules('media', (at) => {
    console.log(`\n@media ${at.params}>`);
    at.walkRules((r) => console.log(`   ${r.selector.replace(/\n/g, ' ')}`));
  });
}
