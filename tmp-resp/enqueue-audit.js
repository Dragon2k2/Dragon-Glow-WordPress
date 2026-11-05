#!/usr/bin/env node
/**
 * Verify: mọi stylesheet được enqueue trong theme đều tồn tại trên disk,
 * và không có file nào bị "mồ côi" (tồn tại nhưng không ai enqueue).
 */
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const THEME = path.join(ROOT, 'wp-content/themes/dragon-glow');
const CSS_DIR = path.join(THEME, 'assets/css');

const phpFiles = [
  'inc/enqueue/styles.php',
  'inc/enqueue/scripts.php',
].map((f) => path.join(THEME, f));

const enqueued = new Set();
for (const p of phpFiles) {
  const src = fs.readFileSync(p, 'utf8');
  for (const m of src.matchAll(/DG_URI\s*\.\s*'\/assets\/css\/([^']+)'/g)) {
    enqueued.add(m[1]);
  }
}

console.log('='.repeat(78));
console.log('STYLESHEET ĐƯỢC ENQUEUE');
console.log('='.repeat(78));

let missing = 0;
for (const f of [...enqueued].sort()) {
  const exists = fs.existsSync(path.join(CSS_DIR, f));
  if (!exists) missing++;
  console.log(`  ${exists ? 'OK  ' : 'MISS'}  ${f}`);
}
console.log(`\nTổng: ${enqueued.size} | Thiếu file: ${missing}`);

// File tồn tại trên disk nhưng không ai enqueue
const onDisk = fs.readdirSync(CSS_DIR).filter((f) => f.endsWith('.css'));
const orphans = onDisk.filter((f) => !enqueued.has(f));

console.log('\n' + '='.repeat(78));
console.log('FILE TỒN TẠI NHƯNG KHÔNG ĐƯỢC ENQUEUE (orphan)');
console.log('='.repeat(78));
if (orphans.length === 0) {
  console.log('  (không có) ✓');
} else {
  for (const o of orphans) console.log(`  ORPHAN  ${o}`);
}

// Kiểm tra theme root style.css (WordPress yêu cầu)
const rootStyle = path.join(THEME, 'style.css');
console.log(`\nTheme root style.css: ${fs.existsSync(rootStyle) ? 'OK ✓' : 'MISSING ✗'}`);
console.log(`\nKẾT QUẢ: ${missing === 0 && orphans.length === 0 ? '✓ Không có orphan, không thiếu file' : '⚠ Cần xử lý'}`);
