---
name: motion-animations
description: >-
  Hướng dẫn dùng thư viện Motion (motion.dev, tên cũ Framer Motion) để thiết kế
  và thêm animation cho giao diện theme Dragon Glow. Dùng skill này khi cần tạo
  hiệu ứng chuyển động: enter/exit, hover/tap, scroll reveal, scroll-linked,
  parallax, stagger, layout, spring physics, SVG path-drawing — cho UI của theme.
  QUAN TRỌNG: dự án này là WordPress thuần PHP + vanilla JS (KHÔNG phải React),
  nên dùng API JavaScript thuần của Motion (`import { animate } from "motion"`),
  KHÔNG dùng `motion/react`.
---

# Motion animations cho Dragon Glow

Motion (motion.dev, trước đây là Framer Motion) là thư viện animation hiệu năng cao,
dùng Web Animations API + ScrollTimeline cho 120fps, fallback sang JS khi cần
spring physics / interruptible keyframes / gesture tracking.

## ⚠️ Đọc trước khi viết code

Dự án **Dragon Glow** là **WordPress theme thuần PHP + vanilla JavaScript** (xem
`assets/js/*.js` viết theo IIFE). **KHÔNG có React.** Vì vậy:

- ✅ Dùng **API JS thuần**: `import { animate, inView, scroll, stagger, spring } from "motion"`
- ❌ KHÔNG dùng `import { motion } from "motion/react"`, `<motion.div>`, `whileHover`,
  `useScroll`, `<AnimatePresence>` — đó là API React, sẽ không chạy ở đây.

Tài liệu React mà user cung cấp được giữ lại làm **bảng tham chiếu khái niệm** ở cuối file;
khi triển khai thực tế hãy dịch sang API vanilla theo bảng đó.

## Khi nào dùng Motion, khi nào dùng CSS thuần

- **CSS transition/animation thuần**: hiệu ứng đơn giản, tự đóng (hover đổi màu, fade nhẹ).
  Nhẹ, không cần thêm thư viện. Theme đã có sẵn scroll-reveal qua class `.reveal`
  (xem `assets/js/main.js`) và parallax — ưu tiên dùng lại nếu đủ.
- **Motion**: khi cần spring physics, stagger động, scroll-linked progress, keyframe
  ngắt được, layout animation, hoặc gesture cross-device đáng tin cậy.

## Cài đặt vào theme

Theme đã nạp Tailwind qua CDN trong [inc/enqueue.php](wp-content/themes/dragon-glow/inc/enqueue.php).
Có 2 cách thêm Motion:

### Cách A — CDN (nhanh nhất, hợp với setup hiện tại)

Motion phân phối dạng ES module. Vì các file JS trong theme là script thường (IIFE),
cách gọn nhất là nạp một module nhỏ riêng. Thêm vào `dg_enqueue_assets()` trong
[inc/enqueue.php](wp-content/themes/dragon-glow/inc/enqueue.php):

```php
// Motion animation library (ES module từ CDN) + script khởi tạo của theme.
wp_enqueue_script_module(
    'dg-motion-init',
    DG_URI . '/assets/js/motion-init.js',
    array(),
    DG_VERSION
);
```

Trong `assets/js/motion-init.js` (kiểu module):

```js
import { animate, inView, scroll, stagger } from "https://cdn.jsdelivr.net/npm/motion@latest/+esm";
// ... code animation ở đây
```

> Lưu ý: `wp_enqueue_script_module` cần WordPress 6.5+. Nếu WP cũ hơn, dùng
> `wp_enqueue_script(..., array(), DG_VERSION, true)` rồi thêm thuộc tính
> `type="module"` qua filter `script_loader_tag`, hoặc dùng Cách B.

### Cách B — npm (nếu sau này có bundler)

```bash
npm install motion
```

```js
import { animate, inView, scroll, stagger } from "motion";
```

## API vanilla cốt lõi (dùng cái này cho Dragon Glow)

### animate() — animation cơ bản
```js
// Vật lý (x, scale) mặc định dùng spring; thị giác (opacity) dùng tween.
animate("#hero-title", { opacity: [0, 1], y: [40, 0] }, { duration: 0.8, ease: "easeOut" });
animate(".dg-badge", { scale: [0, 1] }, { type: "spring", stiffness: 300 });
```

### inView() — scroll-triggered (thay cho whileInView)
```js
inView(".dg-product-card", (element) => {
    animate(element, { opacity: [0, 1], y: [30, 0] }, { duration: 0.6 });
    return () => {}; // optional: chạy khi rời viewport
}, { amount: 0.3 });
```
Đây là bản nâng cấp của pattern `.reveal` hiện có trong `main.js` — mượt và đỡ giật hơn
vì không lắng nghe sự kiện `scroll` thủ công.

### scroll() — scroll-linked (thay cho useScroll)
```js
// Thanh progress đọc tiến độ scroll trang.
scroll(animate(".dg-scroll-progress", { scaleX: [0, 1] }));

// Parallax theo vị trí phần tử trong viewport.
scroll(
    animate(".dg-hero-bg", { y: [-100, 100] }),
    { target: document.querySelector(".dg-hero"), offset: ["start end", "end start"] }
);
```

### stagger() — hiệu ứng lần lượt
```js
animate(".dg-nav-item",
    { opacity: [0, 1], y: [20, 0] },
    { delay: stagger(0.08) }
);
```

### Gesture (hover/tap) — vanilla
Motion vanilla không có `whileHover`; dùng event listener + `animate()`, hoặc CSS
`:hover` cho hiệu ứng đơn giản. Ví dụ tap feedback:
```js
btn.addEventListener("pointerdown", () => animate(btn, { scale: 0.95 }));
btn.addEventListener("pointerup",   () => animate(btn, { scale: 1 }, { type: "spring" }));
```

## Quy ước của dự án (BẮT BUỘC tuân theo)

1. **Viết theo IIFE** giống các file trong `assets/js/` (`(function () { 'use strict'; ... })();`)
   trừ file module Motion phải là ES module — tách riêng `motion-init.js`.
2. **Guard null**: luôn kiểm tra phần tử tồn tại trước khi animate (theme dùng `if (el)` khắp nơi).
3. **Tôn trọng `prefers-reduced-motion`** — bắt buộc cho UI luxury, tránh gây khó chịu:
   ```js
   const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
   if (!reduce) animate(/* ... */);
   ```
4. **Đăng ký asset** trong [inc/enqueue.php](wp-content/themes/dragon-glow/inc/enqueue.php),
   theo điều kiện trang (page template / WooCommerce conditional) như pattern hiện có.
5. **Comment song ngữ ngắn gọn** bằng tiếng Việt như phần còn lại của codebase.
6. **Dùng design token**: màu thương hiệu vàng `#d4af37` (primary-container),
   `#735c00` (primary); font hiển thị Playfair Display. Animation nên tinh tế,
   editorial, sang trọng — ưu tiên ease mềm, duration 0.4–0.9s, tránh hiệu ứng "nảy" quá đà.

## Bảng tham chiếu: React API → Vanilla API

| Khái niệm | Motion React (tài liệu user gửi) | Motion Vanilla (DÙNG cho dự án này) |
|---|---|---|
| Animate khi đổi state | `<motion.div animate={{ opacity: 1 }} />` | `animate(el, { opacity: 1 })` |
| Enter animation | `initial={{ scale: 0 }} animate={{ scale: 1 }}` | `animate(el, { scale: [0, 1] })` |
| Hover/tap | `whileHover`, `whileTap` | event listener + `animate()` hoặc CSS `:hover` |
| Scroll-triggered | `whileInView` | `inView(el, cb)` |
| Scroll-linked | `useScroll()` + `MotionValue` | `scroll(animate(...))` |
| Stagger | `transition={{ delay: stagger() }}` | `{ delay: stagger(0.08) }` |
| Exit animation | `<AnimatePresence>` + `exit` | `animate(el, { opacity: 0 }).then(() => el.remove())` |
| Layout animation | `<motion.div layout />` | (không có tương đương trực tiếp — dùng FLIP thủ công) |
| SVG path drawing | `<motion.circle animate={{ pathLength: 1 }} />` | `animate("path", { pathLength: [0, 1] })` |
| Spring mặc định | tự động cho x/scale | `{ type: "spring", stiffness, damping }` |

## Ý tưởng áp dụng cho Dragon Glow

- **Hero**: fade-up tiêu đề + stagger các dòng phụ; parallax background bằng `scroll()`.
- **Product grid / Best Sellers**: `inView` reveal stagger từng card.
- **Add-to-cart / Wishlist**: spring scale feedback thay cho đổi text "✓" thô như hiện tại.
- **Header**: thanh `dg-scroll-progress` scroll-linked; nav item stagger khi load.
- **FAQ accordion**: animate chiều cao mượt thay cho toggle class `.active` tức thì.
- **Ethereal blobs**: thay parallax `mousemove` thủ công trong `main.js` bằng `animate()`
  có spring để mượt hơn.

## Nguồn tài liệu

- Trang chủ & docs: https://motion.dev
- Cài đặt React (tham khảo): https://motion.dev/docs/react-installation
- Quick start vanilla: https://motion.dev/docs/quick-start
