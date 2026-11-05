#!/usr/bin/env node
/**
 * Liveness check chính xác cho responsive.css.
 * - Xử lý escape Tailwind: .md\:flex → token "md:flex"
 * - Class token đọc từ attribute class= (kể cả trong PHP echo)
 * - Tag selector: kiểm tra thẻ có tồn tại trong markup
 */
const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');
const target = path.join(CSS_DIR, 'responsive.css');

const markup = [];
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
  if (!/\.(php|js|html)$/i.test(p)) continue;
  try { markup.push({ p, s: fs.readFileSync(p, 'utf8') }); } catch {}
}

const tokens = new Map();
const ids = new Set();
for (const { p, s } of markup) {
  const rel = path.relative(ROOT, p).replace(/\\/g, '/');
  for (const m of s.matchAll(/class\s*=\s*(["'])([\s\S]*?)\1/g)) {
    const raw = m[2];
    // loại đoạn <?php ... ?> rồi tách token
    const cleaned = raw.replace(/<\?php[\s\S]*?\?>/g, ' ');
    for (const t of cleaned.split(/[\s"'`]+/)) {
      if (!t) continue;
      if (!/^[a-zA-Z][\w:-]*$/.test(t)) continue;
      if (!tokens.has(t)) tokens.set(t, new Set());
      tokens.get(t).add(rel);
    }
  }
  for (const m of s.matchAll(/id\s*=\s*(["'])([\s\S]*?)\1/g)) ids.add(m[2].trim());
}

const root = postcss.parse(fs.readFileSync(target, 'utf8'));
const rows = [];
root.walkAtRules('media', (at) => {
  at.walkRules((rule) => {
    const sel = rule.selector.trim();
    const selClean = sel.replace(/\\/g, ''); // bỏ escape
    const classes = [...new Set([...selClean.matchAll(/\.([a-zA-Z][\w:-]*)/g)].map((m) => m[1]))];
    const idList = [...new Set([...selClean.matchAll(/#([a-zA-Z][\w-]*)/g)].map((m) => m[1]))];
    const tagList = [...new Set([...selClean.matchAll(/(^|[\s,>+~])([a-z][a-z0-9]*)(?=[\s,>:{.#[])/g)].map((m) => m[2]))];

    const missingCls = classes.filter((c) => !tokens.has(c));
    const missingId = idList.filter((i) => !ids.has(i));
    const missingTag = tagList.filter((t) => !markup.some(({ s }) => new RegExp(`<${t}[\\s>/]`, 'i').test(s)));

    rows.push({
      sel, media: at.params, line: rule.source.start.line,
      classes, missingCls, missingId, missingTag, tagList,
      live: missingCls.length === 0 && missingId.length === 0 && missingTag.length === 0,
    });
  });
});

const live = rows.filter((r) => r.live);
const dead = rows.filter((r) => !r.live);

console.log('='.repeat(100));
console.log(`LIVE: ${live.length} / ${rows.length}`);
console.log('='.repeat(100));
for (const r of live) {
  console.log(`  L${String(r.line).padStart(4)}  ${r.sel.replace(/\n/g, ' ')}`);
  console.log(`          @media(${r.media})  classes:[${r.classes.join(',')}]`);
}

console.log('\n' + '='.repeat(100));
console.log(`DEAD: ${dead.length}`);
console.log('='.repeat(100));
for (const r of dead) {
  console.log(`  L${String(r.line).padStart(4)}  ${r.sel.replace(/\n/g, ' ')}  @media(${r.media})`);
  if (r.missingCls.length) console.log(`          MISSING class: ${r.missingCls.join(', ')}`);
  if (r.missingId.length) console.log(`          MISSING id: ${r.missingId.join(', ')}`);
  if (r.missingTag.length) console.log(`          MISSING tag: ${r.missingTag.join(', ')}`);
}
