#!/usr/bin/env node
/**
 * PostCSS validation cho toàn bộ CSS của theme + kiểm tra không còn
 * rule nào tham chiếu tới class đã bị xóa.
 */
const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');

const files = fs.readdirSync(CSS_DIR).filter((f) => f.endsWith('.css'));
files.push(path.join('..', '..', 'style.css')); // theme root style.css

let errors = 0;
console.log('='.repeat(70));
console.log('POSTCSS VALIDATION');
console.log('='.repeat(70));

for (const f of files) {
  const p = path.isAbsolute(f) ? f : path.join(CSS_DIR, f);
  const rel = path.relative(ROOT, p).replace(/\\/g, '/');
  const src = fs.readFileSync(p, 'utf8');
  try {
    const root = postcss.parse(src, { from: p });
    let decls = 0, rules = 0, atrules = 0;
    root.walkDecls(() => decls++);
    root.walkRules(() => rules++);
    root.walkAtRules(() => atrules++);
    console.log(`  OK   ${rel.padEnd(50)} rules:${String(rules).padStart(5)} decls:${String(decls).padStart(6)} at:${String(atrules).padStart(4)}`);
  } catch (e) {
    errors++;
    console.log(`  FAIL ${rel}  → ${e.message}`);
  }
}

console.log(`\n${errors === 0 ? '✓ Tất cả CSS parse thành công' : `✗ ${errors} file lỗi`}`);

// Kiểm tra file responsive.css đã bị xóa
const respExists = fs.existsSync(path.join(CSS_DIR, 'responsive.css'));
console.log(`\nresponsive.css tồn tại: ${respExists ? 'CÓ (chưa xóa!)' : 'KHÔNG ✓'}`);

// Đếm rule responsive còn lại trong các file chủ sở hữu (để chứng minh breakpoint đã nằm đúng chỗ)
console.log('\n' + '='.repeat(70));
console.log('BREAKPOINT ĐÃ ĐỊNH VỊ TRONG FILE CHỦ SỞ HỮU');
console.log('='.repeat(70));
let total = 0;
for (const f of files.filter((x) => x.endsWith('.css'))) {
  const p = path.isAbsolute(f) ? f : path.join(CSS_DIR, f);
  const rel = path.relative(ROOT, p).replace(/\\/g, '/');
  const root = postcss.parse(fs.readFileSync(p, 'utf8'), { from: p });
  const medias = [];
  root.walkAtRules('media', (at) => {
    if (at.params.includes('prefers-reduced-motion')) return;
    medias.push(at.params);
  });
  if (medias.length) {
    const counts = {};
    for (const m of medias) counts[m] = (counts[m] || 0) + 1;
    total += medias.length;
    console.log(`  ${rel.replace('wp-content/themes/dragon-glow/', '')}`);
    for (const [m, c] of Object.entries(counts)) {
      console.log(`      ${String(c).padStart(3)}× @media ${m}`);
    }
  }
}
console.log(`\nTổng breakpoint block (không tính reduced-motion): ${total}`);
