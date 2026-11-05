#!/usr/bin/env node
/**
 * Tính contrast ratio WCAG cho các cặp màu ứng viên hover.
 * Ink (text) = --primary-900 #241a00
 */
function srgbToLin(c) {
  c /= 255;
  return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
}
function lum(hex) {
  const h = hex.replace('#', '');
  const r = parseInt(h.slice(0, 2), 16);
  const g = parseInt(h.slice(2, 4), 16);
  const b = parseInt(h.slice(4, 6), 16);
  return 0.2126 * srgbToLin(r) + 0.7152 * srgbToLin(g) + 0.0722 * srgbToLin(b);
}
function contrast(a, b) {
  const l1 = lum(a), l2 = lum(b);
  const [hi, lo] = l1 > l2 ? [l1, l2] : [l2, l1];
  return (hi + 0.05) / (lo + 0.05);
}

const INK = '#241a00'; // --primary-900

const COLORS = {
  'gold-200  #ffe182': '#ffe182',
  'gold-bright #f1ca50': '#f1ca50',
  'gold-400  #d4af37': '#d4af37',
  'gold-500  #c9a24b': '#c9a24b',
  'gold-600  #a67c1e': '#a67c1e',
  'gold-700  #8c6d23': '#8c6d23',
};

console.log('='.repeat(72));
console.log(`CONTRAST vs ink ${INK} (text 12px → cần ≥ 4.5:1 AA, ≥ 7:1 AAA)`);
console.log('='.repeat(72));
for (const [name, hex] of Object.entries(COLORS)) {
  const c = contrast(hex, INK);
  const tag = c >= 7 ? 'AAA ✓' : c >= 4.5 ? 'AA ✓' : 'FAIL ✗';
  console.log(`  ${name.padEnd(24)} ${c.toFixed(2).padStart(6)}:1   ${tag}`);
}

console.log('\n' + '='.repeat(72));
console.log('ỨNG VIÊN GRADIENT HOVER');
console.log('='.repeat(72));

const candidates = [
  ['HIỆN TẠI (đậm hơn)', ['#c9a24b', '#a67c1e']],
  ['A: gold-bright → gold-400', ['#f1ca50', '#d4af37']],
  ['B: gold-200 → gold-400', ['#ffe182', '#d4af37']],
  ['C: gold-200 → gold-bright', ['#ffe182', '#f1ca50']],
  ['D: gold-bright → gold-500', ['#f1ca50', '#c9a24b']],
  ['E: gold-200 → gold-500', ['#ffe182', '#c9a24b']],
];

for (const [name, [from, to]] of candidates) {
  const c1 = contrast(from, INK), c2 = contrast(to, INK);
  const min = Math.min(c1, c2);
  const tag = min >= 7 ? 'AAA ✓' : min >= 4.5 ? 'AA ✓' : 'FAIL ✗';
  console.log(`  ${name.padEnd(32)} từ ${c1.toFixed(2)} → đến ${c2.toFixed(2)}  (min ${min.toFixed(2)}:1)  ${tag}`);
}

// So sánh độ sáng với base hiện tại (gold-400 → gold-500)
console.log('\n' + '='.repeat(72));
console.log('BASE hiện tại: gold-400 #d4af37 → gold-500 #c9a24b');
console.log('='.repeat(72));
for (const [name, hex] of Object.entries(COLORS)) {
  const d = (lum(hex) - lum('#d4af37')) * 100;
  console.log(`  ${name.padEnd(24)} ΔL vs gold-400 = ${d >= 0 ? '+' : ''}${d.toFixed(2)}  ${d > 0 ? 'sáng hơn ✓' : d === 0 ? 'bằng' : 'tối hơn ✗'}`);
}
