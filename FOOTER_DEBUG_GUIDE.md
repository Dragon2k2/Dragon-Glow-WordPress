# Footer Fix Debug Guide — Chi tiết kiểm tra lỗi

## 🔍 Cách bật Debug Mode

### Bước 1: Enable WP_DEBUG

Mở file **`wp-config.php`** (gốc thư mục project):

```php
// Tìm dòng:
define( 'WP_DEBUG', false );

// Đổi thành:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );  // Log file thay vì display
```

Lưu file.

### Bước 2: Xoá cache WordPress

**Nếu dùng W3 Total Cache / WP Super Cache:**
- Admin Dashboard → Performance / Cache → Empty All Caches
- Hoặc xoá folder: `wp-content/cache/`

**Nếu dùng Redis/Memcached:**
- WP-CLI: `wp cache flush`
- Hoặc admin panel

**Xoá browser cache:**
- F12 → Settings (gear icon) → Disable cache (checked) ✅
- Hoặc: Ctrl+Shift+Delete → Clear browsing data

### Bước 3: Reload trang WordPress

Mở trang footer (vd `/shipping-returns/`) trong trình duyệt.

---

## 📊 Debug Output Checklist

### Trong DevTools Console (F12 → Console tab)

**Bạn sẽ thấy các message như:**

```
[DG Footer] Footer script loaded (readyState=complete)
[DG Footer] Document already loaded, initializing immediately
[DG Footer] Initializing footer link active state handler
[DG Footer] Found 16 footer links
[DG Footer] Link 0: https://yoursite.com/shipping-returns/ | aria-current=page
[DG Footer] Link 0 has aria-current="page" → adding .is-active
[DG Footer] ✅ Class .is-active added successfully
[DG Footer] Computed color: rgb(201, 162, 75)  ← Màu vàng gold (đúng!)
[DG Footer] Footer link event listeners attached
```

---

## ❌ Troubleshooting — Nếu thấy lỗi

### Problem 1: **Console không thấy message `[DG Footer]`**

**Nguyên nhân:** Footer.js không load, hoặc không phải trong global scope

**Kiểm tra:**

1. **F12 → Network tab → Filter "js"**
   - Tìm `footer.js`
   - Status phải là **200** (not 404)
   - Nếu không thấy → js chưa enqueue

2. **Kiểm tra enqueue:**
   - Mở file: `inc/enqueue/scripts.php`
   - Tìm: `wp_enqueue_script( 'dg-footer'`
   - Phải ở **sau** newsletter (line ~93)

3. **Nếu vẫn không thấy:**
   ```bash
   # Run: WP-CLI inspect scripts
   wp theme mod get
   wp option get active_plugins  # Check nếu plugin override
   ```

---

### Problem 2: **Thấy `[DG Footer]` nhưng không có `.is-active` class**

**Console output:**
```
[DG Footer] Found 16 footer links
[DG Footer] Link 0: .../shipping-returns/ | aria-current=undefined  ← ❌ undefined!
```

**Nguyên nhân:** PHP không set `aria-current="page"`

**Kiểm tra:**

1. **F12 → Elements → tìm footer link**
   ```html
   <!-- ❌ SAI: Không có aria-current -->
   <a href="..." class="dg-footer-link">Shipping & Returns</a>

   <!-- ✅ ĐÚNG: Có aria-current="page" -->
   <a href="..." class="dg-footer-link" aria-current="page">Shipping & Returns</a>
   ```

2. **Kiểm tra PHP logic:**
   - Mở: `template-parts/global/footer-main.php`
   - Dòng ~65: Kiểm tra `is_page_template()` / `is_page()`
   
   ```php
   <!-- Ví dụ: -->
   <li><a href="<?php echo esc_url( $shipping_url ); ?>" 
          class="dg-footer-link" 
          <?php echo is_page_template( 'page-templates/template-shipping-returns.php' ) ? 'aria-current="page"' : ''; ?>>
   ```

3. **Debug PHP:**
   - Thêm vào template (tạm thời):
   ```php
   <!-- DEBUG: Check if page detection works -->
   Current page slug: <?php echo get_post_field( 'post_name' ); ?><br>
   is_page_template( 'template-shipping-returns' ): <?php var_dump( is_page_template( 'page-templates/template-shipping-returns.php' ) ); ?>
   ```

---

### Problem 3: **Có `aria-current="page"` nhưng `.is-active` vẫn không được add**

**Console output:**
```
[DG Footer] Link 0 has aria-current="page" → adding .is-active
[DG Footer] ❌ ERROR: Failed to add .is-active class
```

**Nguyên nhân:** Element không phải `Element` object, hoặc classList không support

**Kiểm tra:**

1. **Verify element type:**
   ```js
   // Mở console, chạy:
   const link = document.querySelector('.dg-footer-link[aria-current="page"]');
   console.log( link );  // Phải là HTMLAnchorElement
   console.log( link.classList );  // Phải có .add()
   link.classList.add( 'test-class' );  // Thử add
   console.log( link.classList.contains( 'test-class' ) );  // Phải true
   ```

2. **Kiểm tra browser compatibility:**
   - `classList` support từ IE 10+ (bình thường hỗ trợ)
   - Nếu thấy error, check Safari version

---

### Problem 4: **Có `.is-active` class nhưng màu vẫn không đổi**

**Console output:**
```
[DG Footer] ✅ Class .is-active added successfully
[DG Footer] Computed color: rgb(119, 119, 119)  ← Xám, không phải vàng!
```

**Nguyên nhân:** CSS selector không match, hoặc Tailwind override

**Kiểm tra:**

1. **F12 → Elements → Click vào footer link**
   - Right-click → Inspect
   - Scroll "Styles" panel xuống
   - Tìm selector `.dg-footer-link.is-active`
   - Phải thấy: `color: var(--color-accent-gold) !important;`

2. **Nếu thấy strikethrough:** CSS bị override
   ```css
   /* ❌ SAI: Tailwind override (xanh dương) */
   .text-on-secondary-container {
     color: rgb(119, 119, 119) !important;  ← Tailwind, specificity cao
   }

   /* ✅ ĐÚNG: Của chúng ta (vàng) */
   .dg-footer-link.is-active {
     color: var(--color-accent-gold) !important;
   }
   ```

3. **Fix:** Check `footer.css` line 24-27:
   ```css
   .dg-footer-link.is-active {
     color: var(--color-accent-gold) !important;  ← PHẢI CÓ !important
     font-weight: 400;
   }
   ```

4. **Check token định nghĩa:**
   - Mở: `style.css` (`:root`)
   - Tìm: `--color-accent-gold:`
   - Phải là vàng/gold hex (vd `#C9A24B`)
   
   ```css
   :root {
     --color-accent-gold: #C9A24B;  ← Vàng
   }
   ```

---

### Problem 5: **Click link nhưng không có instant feedback**

**Console output:**
```
[DG Footer] Clicked link: https://yoursite.com/about/
[DG Footer] Removed .is-active from all links
[DG Footer] ✅ Class .is-active added to clicked link
[DG Footer] Computed color after click: rgb(201, 162, 75)
```
**Nhưng:** Màu vẫn không đổi instant

**Nguyên nhân:** CSS animation bị disable, hoặc layout recalculation bị delay

**Kiểm tra:**

1. **F12 → DevTools → Performance tab:**
   - Click link
   - Thấy DOM change (class add) không?
   - Computed style update không?

2. **Kiểm tra CSS animation:**
   - Có `.transition` trên `.dg-footer-link` không?
   ```css
   .dg-footer-link {
     transition: color 0.3s ease;  ← OK, animation
   }
   ```

3. **Kiểm tra `prefers-reduced-motion`:**
   - Mac: System Preferences → Accessibility → Display → Reduce motion
   - Windows: Settings → Ease of Access → Display → Show animations
   - Nếu bật, animation tắt (nhưng color vẫn phải đổi)

---

## 🧪 Manual Test Commands

**Chạy trên console (F12):**

### Test 1: Kiểm tra footer links tồn tại
```js
const links = document.querySelectorAll('.dg-footer-link');
console.log('Found ' + links.length + ' footer links');
links.forEach((link, i) => {
  console.log(i + ': ' + link.href + ' | aria-current=' + link.getAttribute('aria-current'));
});
```

### Test 2: Kiểm tra aria-current server-side
```js
const currentLink = document.querySelector('.dg-footer-link[aria-current="page"]');
console.log('Current page link:', currentLink);
```

### Test 3: Manually add class và check color
```js
const link = document.querySelector('.dg-footer-link');
link.classList.add('is-active');
const computed = window.getComputedStyle(link);
console.log('Color after adding .is-active:', computed.color);
console.log('Color value in RGB:', computed.getPropertyValue('color'));
```

### Test 4: Kiểm tra CSS token
```js
const root = document.documentElement;
const goldColor = window.getComputedStyle(root).getPropertyValue('--color-accent-gold');
console.log('Gold token value:', goldColor);
```

### Test 5: Simulate click
```js
const link = document.querySelector('.dg-footer-link:not([aria-current])');
const event = new MouseEvent('click', { bubbles: true });
link.dispatchEvent(event);
// Check nếu link thay đổi color
```

---

## 📝 Log Files

**Nếu enable `WP_DEBUG_LOG`, WordPress ghi log vào:**

```
wp-content/debug.log
```

**Mở file này xem nếu có PHP warning/error:**
```
[10-Oct-2026 12:21:00 UTC] PHP Warning: ...
```

---

## 🎯 Verification Workflow

**Chuỗi kiểm tra để xác định vấn đề:**

1. **Kiểm tra script load:**
   - F12 → Network → `footer.js` status = 200? ✅

2. **Kiểm tra console log:**
   - Console có `[DG Footer]` message? ✅

3. **Kiểm tra HTML:**
   - Element có `aria-current="page"`? ✅

4. **Kiểm tra class add:**
   - Console có `✅ Class .is-active added`? ✅

5. **Kiểm tra CSS color:**
   - Console show `Computed color: rgb(201, 162, 75)`? ✅

6. **Kiểm tra visual:**
   - Mắt thấy link đổi vàng? ✅

**Nếu fail ở bước nào, focus debug bước đó.**

---

## 🚀 Submit Debug Info

**Nếu vẫn không fix được, share:**

1. **Console output** (F12 → Console → right-click → Save as → copy paste)
2. **DevTools Elements screenshot** (F12 → Elements → footer link → screenshot)
3. **Network tab** (show footer.js status)
4. **Current page URL** (vd `/shipping-returns/`)
5. **Browser + OS** (vd Chrome 131 on Windows 11)

Mình sẽ debug cụ thể từ đó! 🎉
