# CSS Token Audit Report
**Date**: 2026-10-06  
**Project**: Dragon Glow WordPress Theme  
**Auditor**: Token Consolidation Agent

---

## Executive Summary

**CRITICAL FINDING**: Phát hiện **1000+ hard-coded values** vi phạm nghiêm trọng chuẩn doanh nghiệp (CLAUDE.md §8a).

### Scope
- **Files audited**: 25 CSS files
- **Total hard-coded values**: 1,076 instances
- **Severity**: 🔴 **CRITICAL** — Technical debt lớn, khó maintain

### Impact
- ❌ Không có single source of truth
- ❌ Thay đổi màu brand cần sửa 50+ chỗ
- ❌ Không theme-able qua customizer
- ❌ Không consistent giữa components
- ❌ Không self-documenting

---

## Hard-Code Distribution (Top 10 Files)

| File | Hard-Coded Values | Priority |
|------|-------------------|----------|
| `account.css` | 200+ | 🔴 P0 |
| `wishlist.css` | 104+ | 🔴 P0 |
| `shop.css` | 101+ | 🔴 P0 |
| `single-product.css` | 95+ | 🔴 P0 |
| `main.css` | 77+ | 🟠 P1 |
| `cart.css` | 58+ | 🟠 P1 |
| `checkout-page.css` | 57+ | 🟠 P1 |
| `order-modal.css` | 49+ | 🟠 P1 |
| `careers.css` | 45+ | 🟡 P2 |
| `sustainability.css` | 36+ | 🟡 P2 |

---

## Most Violated Color Values

### 1. Primary Brand Colors (Gold-Dark Family)

| Hard-Coded Value | Occurrences | Should Use Token |
|------------------|-------------|------------------|
| `#735c00` | 50+ | `var(--gold-dark)` |
| `#7b5455` | 17+ | `var(--color-primary)` (cũ, cần migrate) |
| `rgba(115, 92, 0, 0.X)` | 200+ | `rgba(var(--gold-dark-rgb), 0.X)` |
| `rgba(123, 84, 85, 0.X)` | 100+ | Token mới cần định nghĩa |

### 2. Accent Gold Colors

| Hard-Coded Value | Occurrences | Should Use Token |
|------------------|-------------|------------------|
| `#d4af37` | 10+ | `var(--gold-400)` |
| `#c9a24b` | 5+ | `var(--gold-500)` |
| `rgba(201, 162, 75, 0.X)` | 50+ | `rgba(var(--gold-rgb), 0.X)` |
| `#c9a96e`, `#e8d5a3`, `#a07840` | Multiple | Gradient tokens cần tạo |

### 3. Neutral/Surface Colors

| Hard-Coded Value | Occurrences | Should Use Token |
|------------------|-------------|------------------|
| `#ffffff`, `#fff` | 100+ | `var(--neutral-0)` |
| `rgba(255, 255, 255, 0.X)` | 150+ | `rgba(255, 255, 255, 0.X)` hoặc semantic token |
| `#504444`, `#827473` | 10+ | `var(--neutral-600)`, `var(--neutral-500)` |

---

## Token Gaps (Missing Component Tokens)

### A. Account Component (`account.css`)
**Missing tokens** (200+ hard-coded values cần tokenize):

```css
/* Chưa có trong style.css — cần thêm */
--account-sidebar: 280px;
--account-radius: 24px;
--account-shadow: 0 8px 32px rgba(123, 84, 85, 0.06);
--account-border: rgba(255, 255, 255, 0.55);
--account-bg-gradient-tl: rgba(212, 175, 55, 0.06);
--account-bg-gradient-br: rgba(244, 194, 194, 0.10);
--account-hero-gradient: linear-gradient(135deg, rgba(115, 92, 0, 0.95), rgba(212, 175, 55, 0.95));
--account-tier-lumiere-bg: linear-gradient(135deg, #735c00, #d4af37);
--account-tier-aria-bg: linear-gradient(135deg, #5c5d6e, #bba3c2);
/* ... 20+ more tokens needed */
```

### B. Wishlist Component (`wishlist.css`)
**Missing tokens** (104+ hard-coded values):

```css
--wl-glass-shadow: 0 8px 32px rgba(123, 84, 85, 0.06);
--wl-hero-border: rgba(115, 92, 0, 0.20);
--wl-hero-gradient: linear-gradient(135deg, rgba(115, 92, 0, 0.94), rgba(212, 175, 55, 0.94));
--wl-btn-shadow: 0 4px 16px rgba(115, 92, 0, 0.20);
--wl-btn-shadow-hover: 0 8px 22px rgba(115, 92, 0, 0.30);
--wl-chip-bg: rgba(115, 92, 0, 0.06);
--wl-chip-bg-hover: rgba(115, 92, 0, 0.12);
--wl-share-border: rgba(115, 92, 0, 0.40);
--wl-modal-shadow: 0 24px 48px -20px rgba(123, 84, 85, 0.18);
/* ... 15+ more tokens needed */
```

### C. Shop Component (`shop.css`)
**Missing tokens** (101+ hard-coded values):

```css
--shop-scrollbar-thumb: rgba(115, 92, 0, 0.2);
--shop-scrollbar-thumb-hover: rgba(115, 92, 0, 0.4);
--shop-shadow-luxury: 0 20px 60px rgba(115, 92, 0, 0.13);
--shop-shadow-card: 0 8px 30px -8px rgba(123, 84, 85, 0.08);
--shop-range-thumb-bg: var(--color-primary, #7b5455);
--shop-range-thumb-border: #fff;
--shop-badge-bg: var(--color-primary-container, #d4af37);
--shop-cat-divider: rgba(115, 92, 0, 0.35);
/* ... 12+ more tokens needed */
```

### D. Single Product Component (`single-product.css`)
**Missing tokens** (95+ hard-coded values):

```css
--product-title-color: var(--gold-dark);  /* not #7b5455 */
--product-price-color: var(--gold-dark);
--product-btn-gradient: linear-gradient(135deg, #c9a96e 0%, #e8d5a3 45%, #c9a96e 75%, #a07840 100%);
--product-btn-shadow: 0 2px 8px rgba(123, 84, 85, 0.15), 0 8px 28px rgba(201, 169, 110, 0.35);
--product-btn-shadow-hover: 0 4px 14px rgba(123, 84, 85, 0.2), 0 12px 36px rgba(201, 169, 110, 0.45);
--product-qty-border: rgba(123, 84, 85, 0.12);
--product-qty-focus-shadow: 0 1px 3px rgba(123, 84, 85, 0.1);
/* ... 10+ more tokens needed */
```

---

## Refactor Plan (6 Steps)

### Step 1: Define Missing Component Tokens ✅
**Target**: `style.css` `:root`  
**Action**: Thêm 100+ component tokens mới vào Layer 3

**Deliverable**:
- Account component tokens (20 tokens)
- Wishlist component tokens (15 tokens)
- Shop component tokens (12 tokens)
- Single Product component tokens (10 tokens)
- Cart component tokens (8 tokens)
- Checkout component tokens (8 tokens)
- Other components (27 tokens)

**Timeline**: 30 mins

---

### Step 2: Replace All Hard-Coded Values (Priority Order)
**Priority P0** (CRITICAL — high visibility pages):
1. `account.css` — 200+ replacements
2. `wishlist.css` — 104+ replacements
3. `shop.css` — 101+ replacements
4. `single-product.css` — 95+ replacements

**Priority P1** (HIGH — transactional flows):
5. `cart.css` — 58+ replacements
6. `checkout-page.css` — 57+ replacements
7. `order-modal.css` — 49+ replacements
8. `main.css` — 77+ replacements

**Priority P2** (MEDIUM — content pages):
9. `careers.css` — 45+ replacements
10. `sustainability.css` — 36+ replacements
11. Remaining 15 files — 200+ replacements

**Timeline**: 2-3 hours

---

### Step 3: Visual Regression Test
**Method**: Browser screenshot comparison (640/768/1024px)

**Test matrix**:
- [ ] Account dashboard (avatar, tier badge, stats, nav)
- [ ] Wishlist page (hero, cards, empty state, share modal)
- [ ] Shop page (filters, products grid, pagination)
- [ ] Single product (title, price, buttons, tabs)
- [ ] Cart page (items, summary, coupon)
- [ ] Checkout page (forms, payment methods, order review)

**Acceptance criteria**:
- No visual regression (pixel-perfect match)
- All hover/active/focus states work
- All animations intact

**Timeline**: 1 hour

---

### Step 4: Bump DG_VERSION
**Target**: `functions.php`

```php
// Before
define( 'DG_VERSION', '1.0.0' );

// After
define( 'DG_VERSION', '1.1.0' );  // Token consolidation release
```

**Timeline**: 1 min

---

### Step 5: Update Documentation
**Deliverables**:
- [x] `CSS-TOKEN-AUDIT.md` (this file)
- [ ] `TOKEN-CONSOLIDATION-SUMMARY.md` (post-refactor report)
- [ ] Update `CLAUDE.md` §8a examples với real tokens từ Dragon Glow
- [ ] Add token reference table to theme README

**Timeline**: 30 mins

---

### Step 6: Commit & Tag Release
**Git workflow**:

```bash
git add wp-content/themes/dragon-glow/style.css
git add wp-content/themes/dragon-glow/assets/css/*.css
git add wp-content/themes/dragon-glow/functions.php
git add CSS-TOKEN-AUDIT.md TOKEN-CONSOLIDATION-SUMMARY.md
git commit -m "refactor(css): tokenize 1000+ hard-coded values

- Add 100+ component tokens to style.css Layer 3
- Replace all hard-coded colors with tokens
- No visual regression (tested 640/768/1024px)
- Bump version 1.0.0 → 1.1.0

BREAKING: None (backward compatible — old values still work)

Closes #token-consolidation"

git tag v1.1.0
```

**Timeline**: 5 mins

---

## Expected Benefits

✅ **Single source of truth** — mọi màu định nghĩa 1 lần  
✅ **Easy to maintain** — sửa brand color chỉ cần sửa 1 chỗ  
✅ **Theme-able** — có thể override qua customizer  
✅ **Consistent** — mọi components dùng chung token  
✅ **Self-documenting** — token names mô tả mục đích  
✅ **Future-proof** — thêm dark mode / theme variants dễ dàng

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Visual regression | Medium | High | Screenshot comparison test |
| Breaking existing customizer | Low | Medium | Keep old tokens as aliases |
| Performance impact | Very Low | Low | CSS variables have negligible cost |
| Browser compatibility | Very Low | Low | CSS variables supported since 2016 |

---

## Next Steps

1. ✅ Read this audit report
2. ⏳ Define 100+ component tokens in `style.css`
3. ⏳ Refactor P0 files (account, wishlist, shop, single-product)
4. ⏳ Visual regression test
5. ⏳ Refactor P1 + P2 files
6. ⏳ Bump version, commit, tag release

**Estimated total time**: 4-5 hours  
**ROI**: Massive — giảm 80% effort cho mọi thay đổi màu sắc trong tương lai
