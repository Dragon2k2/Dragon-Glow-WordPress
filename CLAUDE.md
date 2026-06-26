# CLAUDE.md — Dragon Glow

Hướng dẫn cho Claude khi làm việc trong dự án này. Đọc kỹ trước khi sửa code.

---

## A. Bối cảnh dự án

**Dragon Glow** là một WordPress + WooCommerce theme cho thương hiệu mỹ phẩm cao cấp, theo design
system **"Luminous Ethereal"** (glassmorphism, bảng màu ethereal, cảm giác sang trọng).

- **Stack:** WordPress 6.4+, PHP 8.0+, WooCommerce 9.0+; **PHP templates thuần + vanilla JS**.
- **Tài nguyên qua CDN:** Tailwind CSS, Motion (motion.dev), Google Fonts (Playfair Display, Plus Jakarta Sans), Material Symbols.
- **Đường dẫn theme:** `wp-content/themes/dragon-glow/` (toàn bộ code nằm ở đây).

**Ràng buộc cứng — đọc kỹ:**
- 🚫 **KHÔNG React.** Đây là PHP + vanilla JS. Animation dùng **Motion API vanilla** (`import { animate } from "motion"`), **không** dùng `motion/react`.
- 🚫 **KHÔNG build step, KHÔNG npm runtime deps trong theme.** Theme phải chạy được trên shared hosting (AwardSpace, PHP 8.x) — mọi thư viện nạp qua CDN. (`package.json` ở gốc repo chỉ là dev-tooling: playwright/sharp để verify & tối ưu ảnh, KHÔNG phải dependency của theme.)
- Khi cần thêm animation, dùng skill **`motion-animations`**.

---

## B. Nguyên tắc làm việc

### 1. Suy nghĩ trước khi code
> *"Đừng giả định. Đừng giấu chỗ mơ hồ. Nêu rõ đánh đổi."*

- Nói rõ giả định; hỏi khi chưa chắc chắn.
- Khi yêu cầu có nhiều cách hiểu → trình bày hết các cách, không tự ý chọn một cách trong im lặng.
- Thấy cách đơn giản hơn thì lên tiếng, sẵn sàng phản biện.
- Gặp chỗ chưa rõ thì dừng lại, chỉ ra chính xác điểm mơ hồ và hỏi.

### 2. Đơn giản là trên hết
> *"Code tối thiểu giải quyết đúng vấn đề. Không làm dư."*

- Không thêm tính năng không được yêu cầu.
- Không trừu tượng hoá (abstraction) thứ chỉ dùng một lần.
- Không thêm tính linh hoạt / cấu hình mà không ai cần.
- Không xử lý các case lỗi không thể xảy ra.
- Rút gọn code phình to (vd 200 dòng → 50 dòng) khi có cơ hội hợp lý.
- Tự kiểm: *một senior engineer có nói "phức tạp quá" không?*

### 3. Thay đổi phẫu thuật
> *"Chỉ động vào thứ bắt buộc. Chỉ dọn đống mình bày ra."*

- Để yên code/comment/format xung quanh không liên quan đến yêu cầu.
- Không refactor code đang chạy tốt.
- Tuân theo style sẵn có của theme (xem phần **C. Quy ước dự án**).
- Dead code không liên quan thì **báo lại**, đừng tự ý xoá.
- Xoá import/biến/hàm mà chính sửa đổi của mình vừa làm thừa ra.
- Phép thử: *mỗi dòng thay đổi phải truy ngược được về yêu cầu của user.*

### 4. Thực thi theo mục tiêu — kiểm chứng bằng mắt
> *"Định nghĩa tiêu chí thành công. Lặp tới khi kiểm chứng được."*
> *(Theme này **không có test suite** → tiêu chí thành công kiểm bằng quan sát thực tế, không phải bằng unit test.)*

- Biến yêu cầu mơ hồ thành tiêu chí **kiểm được bằng quan sát**, ví dụ: trang có render đúng không? đúng layout bento/section không? responsive ở 640/768/1024px ổn không? hover/accordion/animation chạy đúng không? `prefers-reduced-motion` có được tôn trọng không? hoạt động cả khi WooCommerce bật và tắt không?
- Việc nhiều bước → liệt kê plan đánh số ngắn gọn, mỗi bước kèm cách kiểm chứng.
- **Kiểm chứng bằng cách chạy thật** trên trình duyệt (skill **`run`** / **`verify`**, có sẵn `playwright` ở gốc repo), không chỉ đọc code rồi tuyên bố xong.
- Không bịa ra lệnh test/CI không tồn tại; dựa vào kiểm chứng trực quan + kiểm cú pháp PHP (`php -l`) khi cần.

---

## C. Quy ước dự án

> Rút ra từ codebase hiện có. Code mới phải **trông giống** code xung quanh.

### 5. Kiến trúc & tổ chức file
> *"Đúng chỗ, đúng tên."*

- `functions.php` **chỉ** `require_once` các file trong `inc/`; **mọi logic nằm trong `inc/`** (nạp `helpers.php` đầu tiên).
- Mỗi page-template có **1 file CSS** (và **1 file JS** nếu cần) riêng, đặt tên kebab-case trùng tên template: `page-templates/template-shipping-returns.php` → `assets/css/shipping-returns.css` + `assets/js/shipping-returns.js`.
- Template parts gom theo folder từng trang: `template-parts/{trang}/`, với tiền tố `section-` (một section đầy đủ), `tile-` (thẻ/component nhỏ), `data-` (file dữ liệu).
- **Tách dữ liệu khỏi markup:** data đặt trong hàm `dg_{feature}_data(): array` (có `apply_filters` để mở rộng); `require_once locate_template(...)` file data **trước** khi render rồi gọi hàm bên trong template-part.
- Override template WooCommerce đặt trong `woocommerce/`.

### 6. Quy ước PHP
> *"An toàn, có tiền tố, dịch được."*

- Hàm prefix `dg_` (vd `dg_is_woocommerce_active()`); class prefix `DG_`, PascalCase (vd `DG_Product`). Tên file = tên class: `class-dg-product.php` ↔ `DG_Product`.
- Đầu **mọi** file PHP: docblock `@package Dragon_Glow` + `defined( 'ABSPATH' ) || exit;`.
- **Luôn escape output:** `esc_html()`, `esc_attr()`, `esc_url()` (và biến thể `esc_html_e()` / `esc_html__()`...). Không echo dữ liệu thô.
- **Luôn i18n** với text-domain `'dragon-glow'`.
- Dùng type hints PHP 8.0+ (`: bool`, `: array`, `: string`, `: void`...) và `@return` trong docblock.

### 7. Tải assets (enqueue)
> *"Tập trung, có điều kiện, có version."*

- **Mọi** enqueue CSS/JS nằm ở `inc/enqueue.php` (single source of truth). Không enqueue rải rác trong template/template-part.
- Tải **có điều kiện** theo trang: `is_page_template('page-templates/template-xxx.php')` hoặc conditional WooCommerce (`is_shop()`, `is_product()`, `is_cart()`...).
- Handle đặt `dg-{feature}` (vd `dg-shipping-returns`); version dùng hằng `DG_VERSION`; CSS trang phụ thuộc `dg-main`.
- JS thường: `wp_enqueue_script( handle, src, deps, DG_VERSION, true )` (footer). JS dùng `import` (Motion ES module) → `wp_enqueue_script_module(...)`.
- Truyền data PHP→JS qua `wp_localize_script` (vd object `dgAjax` chứa url/nonce/i18n). Asset CDN ngoài để version `null`.

### 8. CSS
> *"BEM có tiền tố, dùng token, responsive & accessible."*

- Class theo BEM với tiền tố `dg-`: block `dg-tile`, modifier `dg-tile--free`, trạng thái `.is-open` / `.is-highlight`.
- Dùng **design token**, tránh hard-code: màu `--color-*` (vd `--color-primary`, `--color-surface`), spacing `--wp--custom--spacing--*`. Token định nghĩa ở `style.css` (`:root`) và Tailwind config (trong `enqueue.php`).
- Breakpoints chuẩn của theme: **640 / 768 / 1024px**. Kích thước fluid dùng `clamp()`.
- **Bắt buộc accessibility:** có khối `@media (prefers-reduced-motion: reduce)` để tắt animation/transform, và `:focus-visible` cho phần tử tương tác.
- Comment phân khu theo style sẵn có (dải `═══` cho SECTION lớn).

### 9. JavaScript & Animation
> *"Vanilla + Motion, không React, luôn tôn trọng reduced-motion."*

- **Vanilla JS, KHÔNG React.** Animation dùng **Motion vanilla API**:
  `import { animate, inView, scroll } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";` — **không bao giờ** dùng `motion/react`. Cần animation thì dùng skill **`motion-animations`**.
- Bọc code trong IIFE + `'use strict'`; tách các hàm khởi tạo `initXxx()` và gọi chúng ở cuối file.
- Đầu file đo `const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches` và **bỏ qua animation** khi `true`.
- **FOUC guard:** template thêm class `dg-js` lên `<html>`; CSS để `.dg-js [data-sr] { opacity: 0 }` rồi JS animate cho hiện ra (tránh nội dung nhấp nháy).
- Cấu hình hiệu ứng qua thuộc tính `data-*` (vd `data-sr`, `data-sr-group`, `data-count-to`, `data-magnetic`); chọn DOM bằng `querySelector` / `querySelectorAll`.
- Không thêm bundler/npm; mọi thư viện nạp qua CDN ES module.

---

## D. Kiểm chứng thay đổi

Trước khi tuyên bố xong, hãy verify:

- Mở trang liên quan trên trình duyệt (skill **`run`** / **`verify`**, playwright có ở gốc repo) và **quan sát**: render đúng, đúng layout/section, animation chạy mượt.
- Kiểm **responsive** ở 3 mốc **640 / 768 / 1024px**.
- Bật "reduce motion" của OS → animation phải tắt, nội dung vẫn hiển thị đầy đủ.
- Nếu đụng phần WooCommerce: thử cả khi plugin **bật** và **tắt** (theme có cơ chế mock khi WC tắt).
- Với file PHP vừa sửa: kiểm cú pháp bằng `php -l` nếu môi trường cho phép.
