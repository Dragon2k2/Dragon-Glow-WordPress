#!/usr/bin/env node

/**
 * CSS Token Replacement Script
 * Automatically replace hard-coded values with CSS tokens
 * Usage: node tokenize-css.mjs <file.css>
 */

import fs from 'fs';
import path from 'path';

// Token mapping: hard-coded value → CSS variable
// Sort by specificity: longer/more specific patterns first
const TOKEN_MAP = {
  // ========== Gradients (must be first - most specific) ==========
  'linear-gradient(135deg, rgba(115, 92, 0, 0.95), rgba(212, 175, 55, 0.95))': 'var(--account-hero-gradient)',
  'linear-gradient(135deg, rgba(115, 92, 0, 0.12), rgba(212, 175, 55, 0.18))': 'var(--account-nav-bg-active)',
  'linear-gradient(135deg, rgba(115, 92, 0, 0.18), rgba(212, 175, 55, 0.22))': 'var(--account-downloads-badge-bg)',
  'linear-gradient(135deg, rgba(115, 92, 0, 0.20), rgba(212, 175, 55, 0.25))': 'var(--account-downloads-badge-bg-hover)',
  'linear-gradient(135deg, rgba(115, 92, 0, 0.14), rgba(212, 175, 55, 0.22))': 'var(--account-payment-bg-gradient)',
  'linear-gradient(135deg, rgba(115, 92, 0, 0.94), rgba(212, 175, 55, 0.94))': 'var(--wl-hero-gradient)',
  'linear-gradient(135deg, #735c00, #d4af37)': 'var(--account-tier-lumiere)',
  'linear-gradient(135deg, #c9a96e 0%, #e8d5a3 45%, #c9a96e 75%, #a07840 100%)': 'var(--product-btn-gradient)',
  'linear-gradient(180deg, rgba(201, 162, 75, 0.1) 0%, rgba(201, 162, 75, 0.05) 100%)': 'var(--order-modal-header-bg)',
  
  // ========== Complex Shadows (must be before simple ones) ==========
  '0 2px 8px rgba(123, 84, 85, 0.15), 0 8px 28px rgba(201, 169, 110, 0.35)': 'var(--product-btn-shadow)',
  '0 4px 14px rgba(123, 84, 85, 0.2), 0 12px 36px rgba(201, 169, 110, 0.45)': 'var(--product-btn-shadow-hover)',
  '0 1px 4px rgba(123, 84, 85, 0.08), 0 2px 8px rgba(123, 84, 85, 0.1)': 'var(--product-btn-buynow-shadow)',
  '0 2px 8px rgba(123, 84, 85, 0.12), 0 4px 14px rgba(123, 84, 85, 0.14)': 'var(--product-btn-buynow-shadow-hover)',
  
  // ========== Account Component Shadows ==========
  '0 8px 32px rgba(123, 84, 85, 0.06)': 'var(--account-shadow)',
  '0 6px 16px rgba(115, 92, 0, 0.20)': 'var(--account-btn-primary-shadow)',
  '0 10px 22px rgba(115, 92, 0, 0.28)': 'var(--account-btn-primary-shadow-hover)',
  '0 12px 28px rgba(123, 84, 85, 0.08)': 'var(--account-card-shadow)',
  '0 8px 18px rgba(123, 84, 85, 0.06)': 'var(--account-card-shadow-hover)',
  '0 4px 16px rgba(115, 92, 0, 0.08)': 'var(--account-table-shadow)',
  '0 6px 18px rgba(115, 92, 0, 0.28)': 'var(--account-badge-shadow)',
  '0 14px 32px rgba(123, 84, 85, 0.10)': 'var(--account-downloads-shadow)',
  '0 6px 14px rgba(115, 92, 0, 0.10)': 'var(--account-address-btn-shadow)',
  '0 4px 12px rgba(115, 92, 0, 0.18)': 'var(--account-address-btn-shadow-focus)',
  '0 6px 16px rgba(115, 92, 0, 0.22)': 'var(--account-subscription-shadow)',
  '0 10px 22px rgba(115, 92, 0, 0.30)': 'var(--account-subscription-shadow-hover)',
  '0 12px 32px rgba(115, 92, 0, 0.12)': 'var(--account-review-shadow)',
  '0 8px 24px rgba(115, 92, 0, 0.06)': 'var(--account-loyalty-shadow)',
  '0 4px 12px rgba(115, 92, 0, 0.16)': 'var(--account-referral-link-shadow)',
  
  // ========== Wishlist Component ==========
  '0 4px 16px rgba(115, 92, 0, 0.20)': 'var(--wl-btn-shadow)',
  '0 8px 22px rgba(115, 92, 0, 0.30)': 'var(--wl-btn-shadow-hover)',
  '0 2px 10px rgba(115, 92, 0, 0.30)': 'var(--wl-chip-shadow-active)',
  '0 2px 8px rgba(115, 92, 0, 0.06)': 'var(--wl-card-shadow)',
  '0 4px 16px rgba(115, 92, 0, 0.15)': 'var(--wl-card-shadow-hover)',
  '0 6px 20px rgba(115, 92, 0, 0.18)': 'var(--wl-card-shadow-focus)',
  '0 20px 48px rgba(115, 92, 0, 0.18)': 'var(--wl-card-shadow-lift)',
  '0 2px 8px rgba(115, 92, 0, 0.08)': 'var(--wl-card-badge-shadow)',
  '0 24px 48px -20px rgba(123, 84, 85, 0.18)': 'var(--wl-modal-shadow)',
  '0 8px 20px rgba(115, 92, 0, 0.25)': 'var(--wl-modal-btn-shadow)',
  '0 12px 36px rgba(123, 84, 85, 0.16)': 'var(--wl-empty-shadow)',
  '0 18px 48px rgba(123, 84, 85, 0.22)': 'var(--wl-toast-shadow)',
  '0 16px 40px rgba(123, 84, 85, 0.18)': 'var(--wl-fab-shadow)',
  
  // ========== Shop Component ==========
  '0 20px 60px rgba(115, 92, 0, 0.13)': 'var(--shop-shadow-luxury)',
  '0 8px 30px -8px rgba(123, 84, 85, 0.08)': 'var(--shop-shadow-card)',
  '0 1px 4px rgba(123, 84, 85, 0.08)': 'var(--shop-shadow-card-hover)',
  '0 1px 4px rgba(123, 84, 85, 0.06)': 'var(--shop-pagination-shadow)',
  '0 2px 8px rgba(123, 84, 85, 0.1)': 'var(--shop-summary-shadow)',
  'rgba(115, 92, 0, 0.2)': 'var(--shop-scrollbar-thumb)',
  'rgba(115, 92, 0, 0.4)': 'var(--shop-scrollbar-thumb-hover)',
  'rgba(115, 92, 0, 0.35)': 'var(--shop-cat-divider)',
  
  // ========== Single Product Component ==========
  'rgba(123, 84, 85, 0.12)': 'var(--product-qty-border)',
  '0 1px 3px rgba(123, 84, 85, 0.06)': 'var(--product-qty-shadow)',
  '0 2px 6px rgba(123, 84, 85, 0.1)': 'var(--product-qty-shadow-focus)',
  'rgba(123, 84, 85, 0.08)': 'var(--product-qty-btn-border)',
  '0 1px 2px rgba(123, 84, 85, 0.08)': 'var(--product-qty-btn-shadow)',
  '0 1px 3px rgba(123, 84, 85, 0.06)': 'var(--product-qty-btn-shadow-hover)',
  '0 2px 8px rgba(123, 84, 85, 0.1)': 'var(--product-qty-btn-shadow-active)',
  
  // ========== Cart Component ==========
  '0 4px 24px rgba(115, 92, 0, 0.06)': 'var(--cart-shadow-card)',
  '0 12px 32px rgba(115, 92, 0, 0.18)': 'var(--cart-shadow-lift)',
  'rgba(115, 92, 0, 0.10)': 'var(--cart-highlight-bg)',
  'rgba(115, 92, 0, 0.40)': 'var(--cart-input-border)',
  'rgba(115, 92, 0, 0.10)': 'var(--cart-input-focus-ring)',
  'linear-gradient(90deg, rgba(115, 92, 0, 0.04), transparent 70%)': 'var(--cart-btn-gradient)',
  '0 18px 48px rgba(123, 84, 85, 0.22)': 'var(--cart-checkout-shadow)',
  'rgba(115, 92, 0, 0.30)': 'var(--cart-coupon-border)',
  'rgba(115, 92, 0, 0.08)': 'var(--cart-coupon-bg-hover)',
  
  // ========== Order Modal Component ==========
  'rgba(201, 162, 75, 0.3)': 'var(--order-modal-border)',
  'rgba(201, 162, 75, 0.2)': 'var(--order-modal-header-border)',
  'rgba(201, 162, 75, 0.05)': 'var(--order-modal-summary-bg)',
  'rgba(201, 162, 75, 0.5)': 'var(--order-modal-progress-fill)',
  
  // ========== Main Navigation ==========
  '0 4px 30px rgba(123, 84, 85, 0.05)': 'var(--main-header-shadow)',
  '0 20px 40px -15px rgba(123, 84, 85, 0.10)': 'var(--main-header-shadow-scroll)',
  '0 2px 8px rgba(115, 92, 0, 0.12)': 'var(--main-dropdown-shadow)',
  '0 4px 16px rgba(115, 92, 0, 0.16)': 'var(--main-dropdown-shadow-hover)',
  
  // ========== WooCommerce Global ==========
  '0 2px 8px rgba(123, 84, 85, 0.10)': 'var(--wc-input-shadow)',
  '0 4px 14px rgba(123, 84, 85, 0.16)': 'var(--wc-input-shadow-focus)',
  '0 4px 10px rgba(115, 92, 0, 0.25)': 'var(--wc-tabs-shadow)',
  
  // ========== Thank You Page ==========
  '0 4px 12px rgba(115, 92, 0, 0.2)': 'var(--thankyou-btn-shadow)',
  '0 8px 24px rgba(115, 92, 0, 0.3)': 'var(--thankyou-btn-shadow-hover)',
  
  // ========== Responsive ==========
  '0 4px 20px rgba(123, 84, 85, 0.1)': 'var(--responsive-modal-shadow)',
  
  // ========== Account Component Colors ==========
  'rgba(212, 175, 55, 0.06)': 'var(--account-bg-gradient-tl)',
  'rgba(244, 194, 194, 0.10)': 'var(--account-bg-gradient-br)',
  'rgba(255, 255, 255, 0.30)': 'var(--account-hero-glow-top)',
  'rgba(255, 255, 255, 0.20)': 'var(--account-hero-glow-bottom)',
  'rgba(255, 255, 255, 0.85)': 'var(--account-avatar-border)',
  '0 4px 14px rgba(0, 0, 0, 0.18)': 'var(--account-avatar-shadow)',
  'rgba(0, 0, 0, 0.65)': 'var(--account-tier-bg)',
  'rgba(0, 0, 0, 0.6)': 'var(--account-tier-glow)',
  'rgba(115, 92, 0, 0.06)': 'var(--account-nav-bg)',
  'rgba(115, 92, 0, 0.10)': 'var(--account-nav-bg-hover)',
  'rgba(115, 92, 0, 0.18)': 'var(--account-nav-border-active)',
  'rgba(115, 92, 0, 0.12)': 'var(--account-input-focus-ring)',
  'rgba(115, 92, 0, 0.30)': 'var(--account-btn-secondary-border)',
  'rgba(115, 92, 0, 0.06)': 'var(--account-table-border)',
  'rgba(115, 92, 0, 0.08)': 'var(--account-badge-bg-pending)',
  'rgba(115, 92, 0, 0.16)': 'var(--account-badge-bg-processing)',
  'rgba(115, 92, 0, 0.20)': 'var(--account-downloads-border)',
  'rgba(115, 92, 0, 0.10)': 'var(--account-downloads-badge-border)',
  'rgba(115, 92, 0, 0.08)': 'var(--account-address-border)',
  'rgba(115, 92, 0, 0.10)': 'var(--account-address-divider)',
  'rgba(115, 92, 0, 0.20)': 'var(--account-address-btn-border)',
  'rgba(115, 92, 0, 0.08)': 'var(--account-address-btn-bg-hover)',
  'rgba(115, 92, 0, 0.16)': 'var(--account-payment-border)',
  'rgba(115, 92, 0, 0.22)': 'var(--account-subscription-border)',
  'rgba(115, 92, 0, 0.08)': 'var(--account-subscription-bg-hover)',
  'rgba(115, 92, 0, 0.04)': 'var(--account-rewards-empty-bg)',
  'rgba(115, 92, 0, 0.25)': 'var(--account-rewards-border)',
  'rgba(115, 92, 0, 0.15)': 'var(--account-review-input-bg-focus)',
  'rgba(115, 92, 0, 0.14)': 'var(--account-loyalty-tier-border)',
  'rgba(115, 92, 0, 0.45)': 'var(--account-loyalty-tier-border-current)',
  
  // ========== Wishlist Component Colors ==========
  'rgba(115, 92, 0, 0.20)': 'var(--wl-hero-border)',
  'rgba(115, 92, 0, 0.55)': 'var(--wl-btn-border)',
  'rgba(115, 92, 0, 0.06)': 'var(--wl-chip-bg)',
  'rgba(115, 92, 0, 0.12)': 'var(--wl-chip-bg-hover)',
  'rgba(115, 92, 0, 0.40)': 'var(--wl-share-border)',
  'rgba(115, 92, 0, 0.25)': 'var(--wl-empty-border)',
  'rgba(115, 92, 0, 0.12)': 'var(--wl-input-focus-ring)',
  
  // ========== Hex Colors (must be last - least specific) ==========
  '#735c00': 'var(--gold-dark)',
  '#d4af37': 'var(--gold-400)',
  '#7b5455': 'var(--color-primary)',
};

function tokenizeFile(filePath) {
  console.log(`\n📝 Processing: ${path.basename(filePath)}`);
  
  let content = fs.readFileSync(filePath, 'utf8');
  let replaceCount = 0;
  
  // Sort by length (longest first) to avoid partial replacements
  const entries = Object.entries(TOKEN_MAP).sort((a, b) => b[0].length - a[0].length);
  
  for (const [hardCoded, token] of entries) {
    const regex = new RegExp(hardCoded.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
    const matches = content.match(regex);
    if (matches) {
      content = content.replace(regex, token);
      replaceCount += matches.length;
      console.log(`  ✓ ${matches.length}× "${hardCoded.substring(0, 40)}..." → ${token}`);
    }
  }
  
  if (replaceCount > 0) {
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`✅ Replaced ${replaceCount} values in ${path.basename(filePath)}`);
  } else {
    console.log(`⏭️  No replacements needed`);
  }
  
  return replaceCount;
}

// Main
const args = process.argv.slice(2);
if (args.length === 0) {
  console.error('Usage: node tokenize-css.mjs <file.css>');
  process.exit(1);
}

let totalReplacements = 0;
for (const filePath of args) {
  if (!fs.existsSync(filePath)) {
    console.error(`❌ File not found: ${filePath}`);
    continue;
  }
  totalReplacements += tokenizeFile(filePath);
}

console.log(`\n🎉 Total replacements: ${totalReplacements}`);
