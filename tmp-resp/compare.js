#!/usr/bin/env node
/**
 * So sánh computed style giữa 2 bản snapshot: baseline (không responsive.css)
 * và hypothetical (responsive.css ĐƯỢC enqueue — tức là kịch bản "migrate").
 */
const fs = require('fs');
const path = require('path');

const [, , aPath, bPath] = process.argv;
const A = JSON.parse(fs.readFileSync(aPath, 'utf8'));
const B = JSON.parse(fs.readFileSync(bPath, 'utf8'));

let totalChecked = 0;
let diffs = [];

for (const bp of Object.keys(A)) {
  const sa = A[bp], sb = B[bp];
  for (const sel of Object.keys(sa)) {
    const ea = sa[sel], eb = sb[sel];
    if (!ea && !eb) continue;
    if (!ea || !eb) {
      diffs.push({ bp, sel, prop: '__presence', from: ea ? 'present' : 'absent', to: eb ? 'present' : 'absent' });
      continue;
    }
    for (const p of Object.keys(ea)) {
      totalChecked++;
      if (ea[p] !== eb[p]) {
        diffs.push({ bp, sel, prop: p, from: ea[p], to: eb[p] });
      }
    }
  }
}

console.log('='.repeat(88));
console.log(`SO SÁNH: ${path.basename(aPath)}  →  ${path.basename(bPath)}`);
console.log('='.repeat(88));
console.log(`Tổng property đã so sánh: ${totalChecked}`);
console.log(`Khác biệt: ${diffs.length}`);

if (diffs.length) {
  console.log('\nDANH SÁCH KHÁC BIỆT:');
  const bySel = {};
  for (const d of diffs) {
    const k = `${d.bp}px ${d.sel}`;
    if (!bySel[k]) bySel[k] = [];
    bySel[k].push(d);
  }
  for (const [k, list] of Object.entries(bySel)) {
    console.log(`\n  ▸ ${k}`);
    for (const d of list) {
      console.log(`      ${d.prop}: ${d.from}  →  ${d.to}`);
    }
  }
} else {
  console.log('\n✓ KHÔNG CÓ KHÁC BIỆT — rendering giống hệt nhau.');
}
