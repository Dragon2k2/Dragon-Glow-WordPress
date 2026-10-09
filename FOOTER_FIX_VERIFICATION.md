# Footer Active Link Fix — Verification Guide

## Tổng quan vấn đề & giải pháp

### Vấn đề gốc
- Footer links có CSS selector `.dg-footer-link.is-active` để highlight link active
- PHP template chỉ set `aria-current="page"` server-side khi render page
- **Thiếu:** JavaScript để add `.is-active` class khi user click link
- **Kết quả:** Link không đổi màu active ngay khi click (chỉ đổi sau khi page reload)

### Giải pháp áp dụng
1. **Tạo file** `assets/js/footer.js` — handle click event, add `.is-active` class instantly
2. **Enqueue script** trong `inc/enqueue/scripts.php` — load trên tất cả pages (footer global)
3. **Cải thiện CSS** — thêm `!important` để override Tailwind CDN
4. **Accessibility:** Giữ nguyên `aria-current="page"` server-side, bổ sung `.is-active` class client-side

---

## Files thay đổi

| File | Thay đổi | Mục đích |
|------|----------|---------|
| `assets/js/footer.js` | **Tạo mới** | JS handler cho footer link active state |
| `inc/enqueue/scripts.php` | Thêm enqueue | Load footer.js trên tất cả pages |
| `assets/css/footer.css` | Thêm `!important` | Ensure CSS active state override Tailwind |

---

## Cách hoạt động

### Server-side (PHP, page load)
```php
// Trong template-parts/global/footer-main.php
<a href="/shipping-returns/" 
   class="dg-footer-link"
   <?php echo is_page_template('page-templates/template-shipping-returns.php') ? 'aria-current="page"' : ''; ?>>
   Shipping & Returns
</a>
```

### Client-side (JavaScript, on load + click)

**Trên page load:**
- JS scans tất cả `.dg-footer-link` elements
- Tìm links có `aria-current="page"` (set by PHP)
- Add `.is-active` class nếu link current

**Trên click:**
- Remove `.is-active` từ tất cả footer links
- Add `.is-active` vào clicked link
- Page navigation xảy ra bình thường (browser load trang)
- Khi trang mới load, server-side PHP lại set đúng `aria-current="page"` → JS sync class

### CSS (Visual feedback)
```css
.dg-footer-link.is-active {
 color: var(--color-accent-gold) !important;
 font-weight: 400;
}
```

---

## Verification Steps (Kiểm tra thay đổi)

### 1. **Trên page load**
- [ ] Mở trang `shipping-returns/`, footer link "Shipping & Returns" phải vàng gold
- [ ] Kiểm tra DevTools (F12 → Elements) → link phải có `aria-current="page"` + class `.is-active`
- [ ] Thử trang khác (vd `faq/`) → link "FAQ" phải vàng, link "Shipping & Returns" phải trắng

### 2. **Trên click link footer**
- [ ] Ở trang `shipping-returns/`, click link "About Us" ở footer
- [ ] **Instant visual feedback:** Link "About Us" phải chuyển vàng **ngay** khi click (TRƯỚC khi page load)
- [ ] Link "Shipping & Returns" phải chuyển trắng **ngay**
- [ ] Page navigate, tải trang About Us
- [ ] Verify lại server-side `aria-current="page"` được set đúng

### 3. **Responsive & accessibility**
- [ ] Zoom trang ở 100%, 150%, 200% → link active color vẫn vàng
- [ ] Thử ở mobile (640px viewport) → footer links hiển thị, active state vẫn work
- [ ] Bật "reduce motion" (Settings → Accessibility) → transition animation phải tắt (có `@media (prefers-reduced-motion)` ở CSS)
- [ ] Focus-visible: Tab qua footer links → outline phải hiển thị (2px solid primary color)

### 4. **Console check (Debug)**
- [ ] F12 → Console
- [ ] Không có lỗi JavaScript (red X)
- [ ] Click footer link → check DevTools → class `.is-active` phải được add/remove correctly

### 5. **WooCommerce pages (nếu site có)**
- [ ] Trên trang shop → footer link "Shop" (nếu có) phải active
- [ ] Trên product detail page → footer active state phải clear (không có link highlight)
- [ ] Trên cart page → footer link "Shop" phải normal, khác page links phải normal

### 6. **Navigation consistency**
- [ ] Click "About Us" ở footer → đi đến About Us page → footer "About Us" vàng
- [ ] Click "FAQ" ở footer → đi đến FAQ page → footer "FAQ" vàng
- [ ] Quay lại "Shipping & Returns" page → footer "Shipping & Returns" lại vàng (server render)

---

## Expected Behavior Summary

| Action | Before Fix | After Fix |
|--------|-----------|-----------|
| Page load (current page) | ✅ Active link vàng (server-side `aria-current="page"`) | ✅ Same, + `.is-active` class |
| Click non-active footer link | ❌ No visual change until reload | ✅ **Instant** color change, then navigate |
| Other links after click | ❌ Stay previous color | ✅ Change to normal color instantly |
| New page load | ✅ Correct link active (PHP) | ✅ Same, JS verify + class sync |
| Mobile/responsive | ✅ Works (CSS is responsive) | ✅ Same |
| Keyboard (Tab) | ✅ Focus outline works | ✅ Same |
| Screen reader | ✅ `aria-current="page"` announced | ✅ Same, class added for visual confirmation |

---

## Troubleshooting

### Link vẫn không đổi màu sau khi fix
1. **Kiểm tra cache**: Clear WordPress cache (if W3 Total Cache / WP Super Cache active)
   - Settings → General → Site Address: make note
   - Admin → Plugins → Clear cache button
   - Or: WP-CLI `wp cache flush`

2. **Kiểm tra theme enqueue**: Xác nhận `dg-footer` script đã enqueue
   - F12 → Network tab
   - Look for `footer.js` → should be green 200
   - If not found, check `wp-content/themes/dragon-glow/assets/js/footer.js` exists

3. **Kiểm tra footer CSS load order**: Ensure footer.css comes AFTER main.css
   - F12 → Network tab → filter "css"
   - Order should be: `style.css` → `main.css` → `footer.css`

4. **Check Tailwind override**: Some pages use Tailwind inline utility classes that may override
   - F12 → Elements → Right-click link → Inspect
   - Check "Computed" tab for color: should be `--color-accent-gold` (var(...))
   - If overridden by Tailwind utility, the `!important` flag should win

### JavaScript console error "Cannot read properties of null"
- This means `.dg-footer-link` elements not found
- Check footer HTML in template-parts/global/footer-main.php
- Verify class names match: `.dg-footer-link`

### Active state changes but styling not visible
- Check if color token `--color-accent-gold` is defined in style.css (`:root`)
- F12 → DevTools → search `:root` in Styles panel
- Verify `--color-accent-gold` value is yellow/gold hex color

---

## File Reference

### `assets/js/footer.js` — 70 lines
- IIFE pattern, 'use strict' mode
- `initFooterActiveState()` — main entry point
- `syncActiveStateFromServerAttribute()` — on load sync
- `handleFooterLinkClick()` — on click handler
- DOMContentLoaded guard for late-load scenarios

### `inc/enqueue/scripts.php` — added 11 lines (around line 90)
```php
// Footer link active state handler — sets .is-active class on footer links.
// Runs on all pages since footer appears globally.
wp_enqueue_script(
    'dg-footer',
    DG_URI . '/assets/js/footer.js',
    array( 'dg-main' ),
    DG_VERSION,
    true
);
```

### `assets/css/footer.css` — updated 1 line
```css
.dg-footer-link.is-active {
 color: var(--color-accent-gold) !important; /* Added !important */
 font-weight: 400;
}
```

---

## Performance & Best Practices

✅ **Chuẩn professional WordPress:**
- Script enqueued globally (footer exists on all pages)
- Dependency: `dg-main` (ensures dgDebug + other globals ready)
- Loaded in footer (`true` = `defer` attribute in HTML)
- No bundler/npm — vanilla JS, CDN-free
- Accessible (aria-current + focus-visible)
- Mobile-friendly (responsive CSS)
- Performance: <1KB minified, no layout thrashing

✅ **Aligned with @CLAUDE.md conventions:**
- File naming: `footer.js` matches template `footer-main.php`
- Pattern: IIFE + 'use strict'
- Accessibility: `aria-current="page"` preserved, enhanced with class
- CSS: Token-driven (`--color-accent-gold`), `!important` for Tailwind override
- Comments: English, JSDoc style

---

## Next Steps (if needed)

1. **If you want animations** on active state change (fade in/pulse):
   - Read skill: `.claude/skills/motion-animations/SKILL.md`
   - Enhance `handleFooterLinkClick()` with Motion.animate()

2. **If you want to track analytics** on footer link clicks:
   - Add pixel-tracking in `handleFooterLinkClick()` before navigation

3. **If you want persistent "you are here" state** across page reloads:
   - Already implemented! Server-side `is_page()` + JS sync on load

---

## Questions?

If verification fails at any step:
1. Share the step where it failed + what you observed
2. Send DevTools Console screenshot (F12 → Console tab)
3. Share current URL where you tested
4. I'll diagnose and fix

Happy testing! 🎉
