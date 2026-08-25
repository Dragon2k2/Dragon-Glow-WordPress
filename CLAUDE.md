# CLAUDE.md — Dragon Glow

Hướng dẫn cho Claude khi làm việc trong dự án này. Đọc kỹ trước khi sửa code.

---

## A. Bối cảnh dự án

**Dragon Glow** là một WordPress **classic theme** + WooCommerce cho thương hiệu mỹ phẩm cao cấp, theo design system **"Luminous Ethereal"** (glassmorphism, bảng màu ethereal, cảm giác sang trọng).

### Project Type Classification

- **WordPress Project Kind**: `wp-site` (full site repo) + `wp-theme` (classic theme)
- **NOT** a block theme (no `theme.json`, no `templates/*.html`)
- **NOT** a plugin development project
- Theme location: `wp-content/themes/dragon-glow/`

### Stack & Constraints

- **WordPress**: 6.4+
- **PHP**: 8.0+
- **WooCommerce**: 9.0+
- **Frontend**: PHP templates thuần + vanilla JavaScript (ES modules qua CDN)
- **Styling**: BEM CSS + Tailwind utility classes (qua CDN, không có build step)
- **CDN Resources**: Tailwind CSS, Motion (motion.dev), Google Fonts (Playfair Display, Plus Jakarta Sans), Material Symbols

### Hard Constraints — ĐỌC KỸ

- 🚫 **KHÔNG React/Vue/Svelte.** Đây là PHP templates + vanilla JS.
- 🚫 **KHÔNG build step trong theme.** Theme phải chạy trên shared hosting (AwardSpace, PHP 8.x).
- 🚫 **KHÔNG npm runtime dependencies trong theme.** Mọi thư viện nạp qua CDN.
- ✅ **Dev tooling** (`package.json` ở gốc repo): chỉ cho playwright (verify) + sharp (optimize ảnh), **không phải dependency của theme**.

### Animation Stack

- **Motion vanilla API** (`import { animate, inView, scroll } from "motion"`)
- **KHÔNG dùng** `motion/react`, `<motion.div>`, `whileHover`, `useScroll`
- Khi cần animation: dùng skill **`.claude/skills/motion-animations/SKILL.md`**

---

## B. Skill Compatibility Matrix

### ✅ Compatible Skills (an toàn để invoke)

| Skill | Use Case | Notes |
|-------|----------|-------|
| `.claude/skills/motion-animations/` | Animation hiệu ứng | Đã aligned với vanilla JS |
| `.claude/skills/design/` (Logo/CIP/Icon/Banner) | Tạo static assets | Python scripts + Gemini AI |
| `.claude/skills/brand/` | Brand voice, messaging | Conceptual guidance |
| `.claude/skills/design-system/` | Design tokens | Adapt vào CSS variables |
| `.cursor/skills/wordpress-router/` | Triage project type | Luôn chạy đầu tiên |
| `.cursor/skills/wp-performance/` | Performance audit | Backend-only agent |
| `.cursor/skills/wp-wpcli-and-ops/` | WP-CLI operations | Maintenance tasks |
| `.cursor/skills/wp-rest-api/` | Custom REST endpoints | Nếu cần API riêng |
| `.cursor/skills/wp-phpstan/` | Static analysis | Code quality |

### ❌ Incompatible Skills (KHÔNG invoke cho Dragon Glow)

| Skill | Lý do |
|-------|-------|
| `.claude/skills/ui-styling/` | Requires React + shadcn/ui + build step |
| `.claude/skills/ui-ux-pro-max/` | React/Next/Vue/Svelte stack |
| `.claude/skills/slides/` | Tạo HTML presentations (OK nếu không dùng React components) |
| `.cursor/skills/wp-block-themes/` | Dragon Glow là classic theme, không phải block theme |
| `.cursor/skills/wp-block-development/` | Không develop custom Gutenberg blocks |
| `.cursor/skills/wp-interactivity-api/` | Không dùng @wordpress/interactivity |

### ⚠️ Partial Compatibility (dùng có điều kiện)

| Skill | Điều kiện | Notes |
|-------|-----------|-------|
| `.cursor/skills/wp-plugin-development/` | Chỉ khi cần tạo companion plugin | Theme hiện tại không có plugin đi kèm |
| `.claude/skills/design/` (Slides) | Chỉ tạo static HTML | KHÔNG dùng React components |

---

## C. Workflow — Luôn bắt đầu với triage

Trước khi sửa code, **luôn chạy triage** để xác nhận project structure:

```bash
node .cursor/skills/wp-project-triage/scripts/detect_wp_project.mjs
```

Expected output:
```json
{
  "project": {
    "kind": ["wp-site", "wp-theme"]
  },
  "signals": {
    "wp-content": true,
    "theme-classic": true,
    "theme-block": false
  }
}
```

Nếu output khác → báo ngay, đừng assume.

---

## D. Nguyên tắc làm việc

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
- Tuân theo style sẵn có của theme (xem phần **E. Quy ước dự án**).
- Dead code không liên quan thì **báo lại**, đừng tự ý xoá.
- Xoá import/biến/hàm mà chính sửa đổi của mình vừa làm thừa ra.
- Phép thử: *mỗi dòng thay đổi phải truy ngược được về yêu cầu của user.*

### 4. Thực thi theo mục tiêu — kiểm chứng bằng mắt
> *"Định nghĩa tiêu chí thành công. Lặp tới khi kiểm chứng được."*
> *(Theme này **không có test suite** → tiêu chí thành công kiểm bằng quan sát thực tế.)*

- Biến yêu cầu mơ hồ thành tiêu chí **kiểm được bằng quan sát**: trang render đúng? responsive ở 640/768/1024px? hover/accordion/animation chạy đúng? `prefers-reduced-motion` được tôn trọng? hoạt động khi WooCommerce bật và tắt?
- Việc nhiều bước → liệt kê plan đánh số ngắn gọn, mỗi bước kèm cách kiểm chứng.
- **Kiểm chứng bằng cách chạy thật** trên trình duyệt (playwright có ở gốc repo), không chỉ đọc code rồi tuyên bố xong.
- Không bịa ra lệnh test/CI không tồn tại; dựa vào kiểm chứng trực quan + `php -l` (syntax check) khi cần.

---

## E. Quy ước dự án

> Rút ra từ codebase hiện có. Code mới phải **trông giống** code xung quanh.

### 5. Kiến trúc & tổ chức file
> *"Đúng chỗ, đúng tên."*

- `functions.php` **chỉ** `require_once` các file trong `inc/`; **mọi logic nằm trong `inc/`** (nạp `helpers.php` đầu tiên).
- Mỗi page-template có **1 file CSS** (và **1 file JS** nếu cần) riêng, đặt tên kebab-case trùng tên template: `page-templates/template-shipping-returns.php` → `assets/css/shipping-returns.css` + `assets/js/shipping-returns.js`.
- Template parts gom theo folder từng trang: `template-parts/{trang}/`, với tiền tố `section-` (một section đầy đủ), `tile-` (thẻ/component nhỏ), `data-` (file dữ liệu).
- **Tách dữ liệu khỏi markup:** data đặt trong hàm `dg_{feature}_data(): array` (có `apply_filters` để mở rộng); `require_once locate_template(...)` file data **trước** khi render rồi gọi hàm bên trong template-part.
- Override template WooCommerce đặt trong `woocommerce/`.

#### 5a. Folder loader pattern (khi 1 concern có nhiều file con)
> *"1 concern = 1 folder + 1 loader PHP cùng tên."*

- Khi 1 chức năng có **≥ 2 file PHP cùng concern**, gom vào folder con dưới `inc/` và tạo **file loader** ở `inc/` với cùng tên — loader chỉ `require_once` các file con, **không chứa logic**.
- Folder loader hiện có:

  ```
  inc/cart-functions.php       → inc/cart/{page-setup,url,operations,identifiers,template-redirect}.php
  inc/approval-handler.php     → inc/careers/{approval-route,approval-emails,approval-ics,approval-decision}.php
  inc/woocommerce.php          → inc/woocommerce/{general,shop,single-product,cart,checkout,…}.php
  inc/ajax-handlers.php        → inc/ajax/{brevo,careers,cart,contact,newsletter,returns,reviews,wishlist,…}.php
  inc/widgets.php              → inc/widgets/class-dg-{name}.php
  inc/enqueue.php              → inc/enqueue/{tailwind-config,styles,scripts}.php
  inc/helpers.php              → inc/helpers/{woocommerce,ui-components,utilities}.php
  ```

- Trong `functions.php`, `require_once` **loader**, không require từng file con.
- Quy tắc đặt tên:
  - Folder con đặt tên theo concern (số ít, kebab-case): `inc/cart/`, `inc/careers/`, `inc/ajax/`, `inc/checkout/`, `inc/products/`, `inc/widgets/`.
  - Loader file ở `inc/` đặt tên theo concern + hậu tố phù hợp (`cart-functions.php`, `approval-handler.php`) hoặc trùng tên concern (`woocommerce.php`, `helpers.php`).
- 1 concern chỉ có 1 file → **đặt phẳng ở `inc/`**, không tạo folder.

#### 5b. Email HTML templates
> *"Email body là template, không phải inline HTML."*

- Email HTML body cho ứng viên / khách (vd careers approval) đặt ở `template-parts/emails/`, **không viết inline `ob_start()` với HTML trong PHP**.
- Template email nhận context qua biến `$email` (mảng) thay vì extract từng biến → gọn, dễ thêm field.
- Helper chung `dg_render_email_template( $slug, $context )` wrap `locate_template` + `ob_get_clean`.
- **Email wrapper** — chuẩn WP: mỗi email có **2-3 file**:
  - `template-parts/emails/{name}.php` — body chính.
  - `template-parts/emails/{name}-header.php` (optional) — header (logo, preheader).
  - `template-parts/emails/{name}-footer.php` (optional) — footer (legal text, unsubscribe).
- Wrapper là **optional** — dùng khi email có nhiều phần. Cho email đơn giản → chỉ cần 1 file body.
- **Đặt tên file email** — kebab-case, flat (không tạo folder con).

#### 5c. Tiêu chí tách folder con vs. để phẳng
> *"Tách khi có nhiều file cùng concern + mỗi file > 20 dòng."*

| Tình huống | Hành động |
|---|---|
| 1 concern, 1 file PHP | Đặt phẳng ở `inc/` |
| 1 concern, 2-3 file PHP, mỗi file < 50 dòng | Có thể gộp 1 file, hoặc tách folder loader |
| 1 concern, ≥ 2 file PHP, có file > 50 dòng hoặc ≥ 3 file | **Tạo folder + loader** |
| File là class OOP đơn giản (< 100 dòng) | Đặt phẳng ở `inc/` |
| File là class OOP phức tạp (> 100 dòng, hoặc có companion interfaces/traits) | **Tách `class-dg-{name}.php` riêng**, đặt ở folder con `inc/{concern}/` |
| File là data array lớn cho 1 trang | Tách `data-{trang}.php` ở `template-parts/{trang}/` |
| File là section HTML riêng | Tách `section-{n}.php` ở `template-parts/{trang}/` |

#### 5d. CSS file organization
> *"1 trang = 1 file CSS cùng tên template. Không nhân đôi token, không split nhỏ."*

- **Cấu trúc thư mục** `assets/css/`: tất cả file phẳng, **không có folder con** (trừ khi có font/asset đi kèm).
- **Đặt tên** `{page-slug}.css` trùng slug của page template:
  - `page-templates/template-shop.php` → `assets/css/shop.css`
  - `page-templates/template-our-story.php` → `assets/css/our-story.css`
- **CSS global** (dùng cho mọi trang): đặt tên theo scope:
  - `style.css` — theme header + CSS variables (`:root`)
  - `main.css` — base, typography, layout chung (luôn load)
  - `responsive.css` — breakpoint overrides
  - `woocommerce.css` — WC global styles
- **Không tách nhỏ** 1 page CSS thành nhiều file — gộp hết vào 1 file. Trừ khi file > 1000 dòng.
- **Token & shared selectors** (`--color-*`, `--wp--custom--spacing--*`) đặt ở `style.css` (`:root`) — **không lặp lại** trong CSS trang.
- **Cache busting** — bump `DG_VERSION` ở `functions.php` mỗi lần sửa CSS.
- **Comment header** mỗi file CSS:

  ```css
  /* =========================================
     DRAGON GLOW — {PAGE} STYLES
     {Brief description of layout}
     ========================================= */
  ```

#### 5e. Không inline style — phải đưa vào file CSS
> *"Style thuộc về file CSS, không thuộc về HTML — trừ 4 ngoại lệ chuẩn WP."*

**Nguyên tắc cứng**:
- 🚫 **Cấm `style="..."` inline** trong template/template-part — **trừ 4 ngoại lệ dưới đây**.
- 🚫 **Cấm thẻ `<style>...</style>`** trong template/template-part.

**4 ngoại lệ chuẩn WordPress**:

| # | Trường hợp | Ví dụ | Lý do |
|---|---|---|---|
| 1 | **CSS variable bind data** | `style="--progress: 60%;"` | Pass data vào CSS |
| 2 | **Dynamic layout values** | `style="grid-row: span 2;"` | Value từ data runtime |
| 3 | **SVG inline style** | `<svg style="fill: currentColor;">` | SVG inline không pick up external CSS |
| 4 | **Email HTML** | Toàn bộ inline style + `<style>` block | Email client không load external CSS |

**JS runtime style** — được phép cho animation:
- `el.style.transform = '...'` — Motion API runtime animation.
- `el.classList.toggle('is-active')` — ưu tiên cách này hơn inline style.

**WordPress `wp_add_inline_style`** — chuẩn WP cho customizer runtime CSS:
- Khai báo trong `inc/enqueue/styles.php`, **không** viết inline trong template.

#### 5f. JS file organization
> *"1 trang = 1 file JS. Module dùng nhiều nơi → `lib/`."*

- **Cấu trúc thư mục** `assets/js/`:
  - **Top-level**: mỗi file là 1 module gắn với 1 page/feature.
  - **`lib/`**: chỉ chứa **shared modules** (được ≥ 2 file enqueue, hoặc expose global `window.DGXxx`).
- **Đặt tên** `{page-slug}.js` trùng slug của page template — **đối chiếu 1-1** với CSS cùng tên.
- **JS global**: `main.js`.
- **Cách enqueue** — mỗi file JS top-level là 1 handle `dg-{slug}`:

  ```php
  // Plain script (IIFE).
  wp_enqueue_script( 'dg-shop', DG_URI . '/assets/js/shop.js', array( 'dg-main' ), DG_VERSION, true );

  // ES module (dùng Motion).
  wp_enqueue_script_module( 'dg-shipping-returns', DG_URI . '/assets/js/shipping-returns.js', array(), DG_VERSION );

  // Shared module — lib/.
  wp_enqueue_script( 'dg-cart-api', DG_URI . '/assets/js/lib/cart-api.js', array( 'dg-main' ), DG_VERSION, true );
  ```

- **Tiêu chí tách file**: 1 page có logic > 50 dòng → tách file riêng; < 50 dòng → gộp vào `main.js`.

#### 5g. Selector phải khớp DOM thật, không assume markup plugin core
> *"WC có thể render khác DOM default — selector dựa giả định = bug ẩn."*

**Nguyên tắc cứng**:

1. **Trước khi viết selector, inspect DOM thật** (F12 → Elements).
2. **Không assume DOM mặc định của plugin** — theme có thể refactor layout custom.
3. **Target bằng class theme-controlled** (`dg-{name}`) an toàn nhất.
4. **Nếu selector dựa trên DOM WC, document ngay trong comment**.
5. **Visual parity rule**: 2 element cùng vai trò (vd nút "Proceed to Checkout" vs "Place Order") → **đối chiếu visual trước khi commit**.

#### 5h. Đối chiếu tên file CSS / JS ↔ page template
> *"CSS, JS, data, section — tất cả phải đối chiếu 1-1 qua slug."*

Xem bảng mapping đầy đủ trong phiên bản cũ của `CLAUDE.md` (dòng 316-357).

### 6. Quy ước PHP
> *"An toàn, có tiền tố, dịch được."*

- Hàm prefix `dg_`; class prefix `DG_`, PascalCase. Tên file = tên class: `class-dg-product.php` ↔ `DG_Product`.
- Đầu **mọi** file PHP: docblock `@package Dragon_Glow` + `defined( 'ABSPATH' ) || exit;`.
- **Luôn escape output:** `esc_html()`, `esc_attr()`, `esc_url()`.
- **Luôn i18n** với text-domain `'dragon-glow'`.
- Dùng type hints PHP 8.0+ (`: bool`, `: array`, `: string`, `: void`).
- **Mọi comment (docblock, inline) viết bằng tiếng Anh** — chuẩn quốc tế.
- **Mọi content hiển thị cho user viết bằng tiếng Anh** — dùng i18n để dịch qua `.po/.mo`.

### 7. Tải assets (enqueue)
> *"Tập trung, có điều kiện, có version."*

- **Mọi** enqueue CSS/JS nằm ở `inc/enqueue.php` (single source of truth).
- Tải **có điều kiện** theo trang: `is_page_template()` hoặc WooCommerce conditional (`is_shop()`, `is_product()`, `is_cart()`).
- Handle đặt `dg-{feature}`; version dùng hằng `DG_VERSION`; CSS trang phụ thuộc `dg-main`.
- JS thường: `wp_enqueue_script( handle, src, deps, DG_VERSION, true )` (footer).
- JS dùng `import` (Motion ES module) → `wp_enqueue_script_module(...)`.
- Truyền data PHP→JS qua `wp_localize_script`.

### 8. CSS
> *"BEM có tiền tố, dùng token, responsive & accessible."*

- Class theo **BEM với tiền tố `dg-`**: block `dg-tile`, modifier `dg-tile--free`, trạng thái `.is-open` / `.is-highlight`.
- Dùng **design token**, tránh hard-code: màu `--color-*`, spacing `--wp--custom--spacing--*`. Token định nghĩa ở `style.css` (`:root`) và Tailwind config (trong `enqueue.php`).
- Breakpoints chuẩn của theme: **640 / 768 / 1024px**. Kích thước fluid dùng `clamp()`.
- **Bắt buộc accessibility:** có khối `@media (prefers-reduced-motion: reduce)` để tắt animation, và `:focus-visible` cho phần tử tương tác.
- Comment phân khu theo style sẵn có (dải `═══` cho SECTION lớn).

### 9. JavaScript & Animation
> *"Vanilla + Motion, không React, luôn tôn trọng reduced-motion."*

- **Vanilla JS, KHÔNG React.** Animation dùng **Motion vanilla API**:
  `import { animate, inView, scroll } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";`
  **Không bao giờ** dùng `motion/react`. Cần animation thì dùng skill **`.claude/skills/motion-animations/`**.
- Bọc code trong IIFE + `'use strict'`; tách các hàm khởi tạo `initXxx()` và gọi ở cuối file.
- Đầu file đo `const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches` và **bỏ qua animation** khi `true`.
- **FOUC guard:** template thêm class `dg-js` lên `<html>`; CSS để `.dg-js [data-sr] { opacity: 0 }` rồi JS animate cho hiện ra.
- Cấu hình hiệu ứng qua thuộc tính `data-*` (vd `data-sr`, `data-count-to`, `data-magnetic`); chọn DOM bằng `querySelector` / `querySelectorAll`.
- Không thêm bundler/npm; mọi thư viện nạp qua CDN ES module.

---

## F. Kiểm chứng thay đổi

Trước khi tuyên bố xong, hãy verify:

- Mở trang liên quan trên trình duyệt (playwright có ở gốc repo) và **quan sát**: render đúng, đúng layout/section, animation chạy mượt.
- Kiểm **responsive** ở 3 mốc **640 / 768 / 1024px**.
- Bật "reduce motion" của OS → animation phải tắt, nội dung vẫn hiển thị đầy đủ.
- Nếu đụng phần WooCommerce: thử cả khi plugin **bật** và **tắt** (theme có cơ chế mock khi WC tắt).
- Với file PHP vừa sửa: kiểm cú pháp bằng `php -l` nếu môi trường cho phép.

---

## G. Integration với .cursor/skills WordPress ecosystem

### Khi nào invoke WordPress skills

**Luôn bắt đầu với:**
```bash
node .cursor/skills/wp-project-triage/scripts/detect_wp_project.mjs
```

**Routing theo intent:**

- **Performance audit** → `.cursor/skills/wp-performance/`
- **WP-CLI operations** → `.cursor/skills/wp-wpcli-and-ops/`
- **Custom REST endpoint** → `.cursor/skills/wp-rest-api/`
- **PHPStan static analysis** → `.cursor/skills/wp-phpstan/`
- **Companion plugin cần tạo** → `.cursor/skills/wp-plugin-development/`

**KHÔNG invoke:**

- `wp-block-themes` — Dragon Glow là classic theme
- `wp-block-development` — không develop custom Gutenberg blocks
- `wp-interactivity-api` — không dùng @wordpress/interactivity

### Guardrails khi làm việc với WordPress skills

1. **Confirm project type trước**: classic theme trong full site repo.
2. **Prefer theme conventions**: BEM CSS + vanilla JS, không thêm build step.
3. **Respect PHP/WP version**: PHP 8.0+, WP 6.4+.
4. **Follow WooCommerce integration patterns** đã có trong `inc/woocommerce/`.

---

## H. Changelog

- `2026-08-24`: Refactor toàn bộ để tương thích với `.cursor/skills` ecosystem; thêm skill compatibility matrix; làm rõ project type (classic theme trong full site repo); loại bỏ mâu thuẫn về React/build step.
- `2026-08-10`: thêm §5g — selector phải khớp DOM thật; visual parity rule.
- `2026-08-09`: refactor — tách cart helpers, approval handler, legal pages.
