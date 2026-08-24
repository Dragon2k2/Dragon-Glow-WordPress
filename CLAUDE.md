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
- 1 concern chỉ có 1 file → **đặt phẳng ở `inc/`**, không tạo folder (vd `class-dg-checkout-router.php` ở `inc/checkout/` đi cùng folder với các class khác — đã có folder chung, không phải loader).

#### 5b. Email HTML templates
> *"Email body là template, không phải inline HTML."*

- Email HTML body cho ứng viên / khách (vd careers approval) đặt ở `template-parts/emails/`, **không viết inline `ob_start()` với HTML trong PHP**.
- Template email nhận context qua biến `$email` (mảng) thay vì extract từng biến → gọn, dễ thêm field.
- Helper chung `dg_render_email_template( $slug, $context )` ở `inc/careers/approval-emails.php` (hoặc nơi khác nếu không phải careers) wrap `locate_template` + `ob_get_clean`.
- **Email wrapper** — chuẩn WP/EDD/WooCommerce: mỗi email có **2 file**:
  - `template-parts/emails/{name}.php` — body chính.
  - `template-parts/emails/{name}-header.php` (optional) — header (logo, preheader).
  - `template-parts/emails/{name}-footer.php` (optional) — footer (legal text, unsubscribe).
- Khi gọi `dg_render_email_template( 'emails/{name}-header', $email )` → header; gọi `dg_render_email_template( 'emails/{name}', $email )` → body; gọi `dg_render_email_template( 'emails/{name}-footer', $email )` → footer.
- Wrapper là **optional** — dùng khi email có nhiều phần (vd marketing email có preheader + body + footer). Cho email đơn giản (vd approval accept/reject hiện tại) → chỉ cần 1 file body.
- **Đặt tên file email** — kebab-case, không phân biệt loại email:
  - `template-parts/emails/approval-accept.php` — interview invitation.
  - `template-parts/emails/approval-reject.php` — rejection email.
  - `template-parts/emails/order-confirmation.php` — body (khi thêm WC integration).
  - `template-parts/emails/order-confirmation-header.php` — header.
- **Không** tạo folder con (`template-parts/emails/careers/`, `template-parts/emails/orders/`) — flat, vì email template thường < 10 file trong 1 theme, không cần chia folder.

#### 5c. Tiêu chí tách folder con vs. để phẳng
> *"Tách khi có nhiều file cùng concern + mỗi file > 20 dòng."*

| Tình huống | Hành động |
|---|---|
| 1 concern, 1 file PHP | Đặt phẳng ở `inc/` |
| 1 concern, 2-3 file PHP, mỗi file < 50 dòng | Có thể gộp 1 file, hoặc tách folder loader |
| 1 concern, ≥ 2 file PHP, có file > 50 dòng hoặc ≥ 3 file | **Tạo folder + loader** |
| File là class OOP đơn giản (1 file < 100 dòng, không có init phức tạp) | Đặt phẳng ở `inc/` |
| **File là class OOP phức tạp** (constructor có nhiều param, file > 100 dòng, hoặc có companion interfaces/traits) | **Tách `class-dg-{name}.php` riêng**, đặt ở folder con `inc/{concern}/` |
| File là data array lớn cho 1 trang | Tách `data-{trang}.php` ở `template-parts/{trang}/` |
| File là section HTML riêng | Tách `section-{n}.php` ở `template-parts/{trang}/` |

**Quy tắc cho class OOP**:
- Class < 100 dòng, không có init phức tạp → đặt phẳng ở `inc/` (vd `class-dg-product.php`).
- Class > 100 dòng HOẶC có logic init phức tạp (DI, factory, multi-step constructor) → tách `class-dg-{name}.php` ở folder con (vd `inc/widgets/class-dg-about-widget.php`).
- Class có companion (interface, trait, abstract parent) → bắt buộc tách riêng, mỗi cái 1 file.
- Khi tạo folder con cho class riêng → đặt tên folder theo concern (vd `inc/widgets/`), không tạo `inc/classes/` (quá generic).
- Mỗi class file **phải có file loader ở `inc/`** (vd `inc/widgets.php` require `inc/widgets/class-dg-about-widget.php`).

#### 5d. CSS file organization
> *"1 trang = 1 file CSS cùng tên template. Không nhân đôi token, không split nhỏ."*

- **Cấu trúc thư mục** `assets/css/`: tất cả file phẳng, **không có folder con** (trừ khi có font/asset đi kèm — vd `assets/css/fonts/`).
- **Đặt tên** `{page-slug}.css` trùng slug của page template tương ứng:
  - `page-templates/template-shop.php` → `assets/css/shop.css`
  - `page-templates/template-our-story.php` → `assets/css/our-story.css`
  - `page-templates/template-{slug}.php` → `assets/css/{slug}.css`
- **CSS global** (dùng cho mọi trang): đặt tên theo scope, không theo page:
  - `main.css` — base, typography, layout chung (luôn load, là dep của mọi CSS trang).
  - `responsive.css` — breakpoint overrides (load global sau main).
  - `woocommerce.css` — WC global styles (load khi WC active).
  - **Không** tạo `base.css`, `common.css`, `general.css` trùng vai trò với `main.css`.
- **Một số page có CSS riêng nhưng không có template riêng** (vd single product page): đặt tên theo scope: `single-product.css` (không phải `product.css`).
- **Không tách nhỏ** 1 page CSS thành nhiều file (`shop.css` + `shop-hero.css` + `shop-grid.css`) — gộp hết vào 1 file. Trừ khi file > 1000 dòng, lúc đó mới cân nhắc tách partials trong cùng 1 file qua `@layer` hoặc section comments.
- **Token & shared selectors** (`--color-*`, `--wp--custom--spacing--*`, utility classes) đặt ở `style.css` (`:root`) — **không lặp lại** trong CSS trang.
- **Minification cho production** — theme này không có build step (chuẩn, xem mục A), nên file CSS giữ nguyên format để dễ đọc. Nếu deploy lên production có performance budget nghiêm ngặt → có thể dùng `wp_enqueue_style` với filter hoặc plugin autoptimize để minify, **không** tự viết CSS đã minify (khó maintain).
- **Cache busting** — mỗi lần sửa CSS, bump `DG_VERSION` ở `functions.php`. Không cần thêm query string (`?v=1.0.7`) vì `DG_VERSION` đã làm cache busting cho WP.
- **Comment header** mỗi file CSS:

  ```css
  /* =========================================
     DRAGON GLOW — {PAGE} STYLES
     {Brief description of layout}
     ========================================= */
  ```

#### 5g. Không inline style — phải đưa vào file CSS
> *"Style thuộc về file CSS, không thuộc về HTML — trừ 4 ngoại lệ chuẩn WP."*

**Nguyên tắc cứng** (áp dụng cho mọi code mới):
- 🚫 **Cấm `style="..."` inline** trong template / template-part — **trừ 4 ngoại lệ dưới đây**.
- 🚫 **Cấm thẻ `<style>...</style>`** trong template / template-part. Không viết inline CSS như `<style>.dg-foo { color: red; }</style>` rồi render trong HTML.

**4 ngoại lệ chuẩn WordPress** (được phép):

| # | Trường hợp | Ví dụ | Lý do |
|---|---|---|---|
| 1 | **CSS variable bind data** | `style="--progress: 60%;"` + CSS `width: var(--progress)` | Chuẩn để pass data vào CSS mà không hard-code value |
| 2 | **Dynamic layout values** | `style="grid-row: span 2;"`, `style="top: 240px;"` cho masonry / slider range | WP Coding Standards cho phép; không thể tách class vì value đến từ data runtime |
| 3 | **SVG inline style** | `<svg style="fill: currentColor;">` | SVG inline không pick up class CSS qua external stylesheet trong mọi context (vd email) |
| 4 | **Email HTML** (`template-parts/emails/*.php`) | Toàn bộ inline style + `<style>` block | Email client (Gmail, Outlook) **không load file CSS ngoài** — inline là cách duy nhất |

**JS runtime style** (không phải HTML inline) — **được phép** cho animation:
- `el.style.transform = '...'` — Motion API runtime animation.
- `el.classList.toggle('is-active')` — ưu tiên cách này hơn inline style.
- `parallaxImg.style.transform = 'translateY(' + yPos + 'px)'` — chấp nhận được vì giá trị tính từ scroll runtime.

**WordPress `wp_add_inline_style`** — chuẩn WP cho customizer/theme mod runtime CSS:
- Dùng khi CSS cần dynamic value từ theme customizer (vd font size do user chọn).
- Khai báo trong `inc/enqueue/styles.php`, **không** viết inline trong template.
- Vd: `wp_add_inline_style( 'dg-main', ':root { --shop-grid-gap: ' . get_theme_mod( 'shop_grid_gap' ) . 'px; }' );`

**Quy trình khi muốn style cho 1 element**:
1. Nếu style là **trang trí** (màu, font, padding, animation) → class BEM trong file CSS.
2. Nếu style **phụ thuộc data runtime** → xét 4 ngoại lệ trên theo thứ tự:
   - Ưu tiên **CSS variable inline** (`style="--progress: 60%;"`) — clean nhất.
   - Cuối cùng mới dùng **dynamic value inline** (`style="grid-row: span 2;"`).
3. Không viết `style="color: red"` (giá trị cố định) — phải qua class.

**Refactor inline style hiện có** (nếu gặp khi sửa code):
1. Đọc `style="..."`.
2. Nếu giá trị **cố định** → tạo class trong CSS file, thay inline bằng class.
3. Nếu giá trị **động** từ data → giữ nguyên (ngoại lệ #2).
4. Nếu là **CSS variable** → giữ nguyên (ngoại lệ #1).
5. Verify trên trình duyệt trước khi commit.

**Bảng ngoại lệ tóm tắt**:

| Trường hợp | Được? |
|---|---|
| `style="font-size: 96px;"` (giá trị cố định) | ❌ Cấm → class `.dg-icon-96` |
| `style="color: red;"` (giá trị cố định) | ❌ Cấm → class hoặc CSS variable |
| `style="--progress: 60%;"` (CSS variable) | ✅ Ngoại lệ #1 |
| `style="grid-row: span 2;"` (dynamic từ data) | ✅ Ngoại lệ #2 |
| `style="top: 240px;"` (slider/masonry) | ✅ Ngoại lệ #2 |
| `<svg style="fill: currentColor;">` | ✅ Ngoại lệ #3 |
| Email HTML inline style + `<style>` | ✅ Ngoại lệ #4 |
| `el.style.transform = '...'` trong JS | ✅ Runtime animation |
| `wp_add_inline_style()` trong `enqueue/styles.php` | ✅ Customizer runtime |

#### 5e. JS file organization
> *"1 trang = 1 file JS. Module dùng nhiều nơi → `lib/`."*

- **Cấu trúc thư mục** `assets/js/`:
  - **Top-level**: mỗi file là 1 module gắn với 1 page/feature. **Không** đặt feature nhỏ vào `lib/` — chỉ `lib/` cho code dùng chung.
  - **`lib/`**: chỉ chứa **shared modules** (nhận 1+ dep khác qua `array( 'dg-foo' )`, hoặc expose global `window.DGXxx`).
- **Đặt tên** `{page-slug}.js` trùng slug của page template — **đối chiếu 1-1** với CSS cùng tên (vd `shop.css` + `shop.js`).
- **JS global** (global dep, luôn load): `main.js`. Đặt tên theo scope, không theo page.
- **Quy tắc `lib/`** — chỉ file thỏa **≥ 1** tiêu chí:
  1. Được ≥ 2 file top-level enqueue làm dependency.
  2. Expose global `window.DGFoo` để các module khác gọi.
  3. Add-on cho 1 thư viện chuẩn (vd `cart-api.js` wrap `window.DGCart`).
- **Cách enqueue** — mỗi file JS top-level là 1 handle `dg-{slug}`:

  ```php
  // Page-specific JS (plain script — không import).
  wp_enqueue_script( 'dg-shop', DG_URI . '/assets/js/shop.js', array( 'dg-main' ), DG_VERSION, true );

  // Page-specific JS dùng Motion ES module.
  wp_enqueue_script_module( 'dg-shipping-returns', DG_URI . '/assets/js/shipping-returns.js', array(), DG_VERSION );

  // Shared module — lib/.
  wp_enqueue_script( 'dg-cart-api', DG_URI . '/assets/js/lib/cart-api.js', array( 'dg-main' ), DG_VERSION, true );
  ```

- **Tiêu chí tách file** (khi nào tạo file mới thay vì gộp):
  - 1 page có logic riêng > 50 dòng → tách file riêng.
  - 1 page có logic < 50 dòng → gộp vào `main.js` (gộp qua function `initXxx()` pattern).
  - Cùng 1 concern cần dùng ở ≥ 2 page → tách `lib/`.
- **Không** tạo file cho UI chỉ cần một vài dòng (accordion 5 dòng, tooltip 10 dòng) → gộp vào `main.js`.
- **Performance** — chuẩn WP cho production:
  - Mặc định `wp_enqueue_script( ..., true )` → footer load, không block render.
  - **Không** dùng `$in_footer = false` (head) trừ khi cần inline ngay (vd critical JS, theme bootstrap).
  - **Không** tự thêm `defer` / `async` — `wp_enqueue_script` đã handle async tự động qua WordPress dependency system.
  - JS không cần thiết cho LCP → load async (chỉ gọi `wp_enqueue_script` ở hook điều kiện, vd `wp_enqueue_scripts`, không phải `wp_head`).
  - ES module (`wp_enqueue_script_module`) — mặc định defer trong WP 6.5+.
- **Comment header** mỗi file JS:

  ```js
  /**
   * Dragon Glow — {Page} JS
   * {Brief description of behaviour + features}
   *
   * @package Dragon_Glow
   */
  ```

- **Quy tắc cho shared module expose global**:
  - Đặt tên `window.DG{Foo}` (PascalCase, prefix `DG`).
  - Vd `cart-api.js` expose `window.DGCart` (object với method `add`, `remove`, `getCount`).
  - Vd `cart-feedback.js` đọc `window.DGCart` để wire UI feedback.
  - Không pollute `window` với quá nhiều global — chỉ những gì thật sự shared.
- **Quy tắc cho async/defer ở module level**:
  - File không phụ thuộc DOM ngay → có thể bọc trong `DOMContentLoaded` listener (mặc định).
  - File phụ thuộc DOM ngay (vd `main.js`) → chạy trực tiếp ở cuối file (vì `wp_enqueue_script` với `$in_footer = true` đã chạy sau DOM ready).

#### 5h. Selector phải khớp DOM thật, không assume markup plugin core
> *"WC render nút Place Order trong `<div id="payment">` — nhưng team refactor layout custom có thể đã move nó ra ngoài. Selector dựa trên giả định plugin core = bug ẩn."*

**Bài học từ bug checkout button** (2026-08-10): CSS rule `.woocommerce-checkout #payment #place_order` không match nút Place Order vì `dg_render_wc_checkout()` render nút trực tiếp trong cột Order Summary, ngoài `<div id="payment">` của WC. Selector WC-default không match → nút rơi về fallback Tailwind `bg-primary` (tertiary), mất visual parity với nút "Proceed to Checkout" trong cart.

**Nguyên tắc cứng — áp dụng khi viết CSS nhắm vào element của WC/plugin core**:

1. **Trước khi viết selector, inspect DOM thật** (F12 → Elements → Ctrl+F tên class/id) — đặc biệt với element WC đã từng nằm trong wrapper cố định (`#payment`, `#order_review`, `.woocommerce-checkout-payment`...).
2. **Không assume DOM mặc định của plugin** chỉ vì nó "chuẩn". Theme refactor → DOM có thể khác. Đặc biệt với checkout / cart / my-account: Dragon Glow render custom 2-cột layout, nhiều element WC core không còn nằm trong wrapper gốc.
3. **Target bằng class theme-controlled** (`dg-{name}`) là an toàn nhất — class này do team đặt, không bao giờ bị WC thay đổi. Selector kiểu `.dg-place-order` ưu tiên hơn `#place_order` (vì id có thể trùng namespace WC).
4. **Nếu selector dựa trên DOM WC, document ngay trong comment** tại sao selector đó hợp lệ (vd `/* matches WC core template checkout/payment.php line 30 */`). Không để selector "trôi nổi" không ai biết tại sao nó match.
5. **Khi theme render custom markup thay cho WC default**, **bắt buộc** thêm comment vào cả file PHP lẫn file CSS nói rõ "element này do theme render, không nằm trong wrapper WC core nữa". Đây là signal để dev sau không tự thêm selector WC-default vào.

**Selector pattern khuyến nghị** — viết **selector phụ bằng class theme** song song với selector WC core:
```css
/* Selector WC core — fallback nếu WC di chuyển element lại vào wrapper gốc.
   Selector chính dựa trên class theme-controlled (dg-place-order) — element
   này do dg_render_wc_checkout() render, không nằm trong <div id="payment">. */
.woocommerce-checkout #payment #place_order,
.woocommerce-checkout .dg-place-order {
    /* ... */
}
```

**Visual parity rule** — khi có 2 element cùng vai trò trên các trang khác nhau (vd nút "Proceed to Checkout" ở cart vs nút "Place Order" ở checkout; nút "Add to Cart" ở shop loop vs single product), **đối chiếu visual trên browser trước khi commit**:
- Render cả 2 trang cạnh nhau (2 tab), so sánh màu, padding, animation.
- Nếu khác → fix ngay, không để "deferred" (theo §4 — kiểm chứng bằng mắt).
- Rule: nếu không chủ động khác biệt (deliberate differentiation) → **phải giống**.

**Checklist khi nghi ngờ selector không match** (debug nhanh trên browser):
```javascript
const btn = document.querySelector('.dg-foo') || document.getElementById('foo');
const cs = getComputedStyle(btn);
console.log('background-image:', cs.backgroundImage); // rỗng = CSS rule không apply
console.log('parent:', btn.closest('#expected-wrapper')); // null = element không nằm trong wrapper
```
Selector đúng → `background-image` chứa `linear-gradient(...)`. Selector sai → `background-image: none`.

#### 5f. Đối chiếu tên file CSS / JS ↔ page template
> *"CSS, JS, data, section — tất cả phải đối chiếu 1-1 qua slug."*

Bảng đối chiếu hiện tại (cập nhật khi thêm trang):

| Page template | CSS | JS | Data | Sections |
|---|---|---|---|---|
| `template-shop.php` | `shop.css` | `shop.js` | `template-parts/shop/{hero,filter-sidebar,active-filters,pagination,philosophy,rituals}.php` | — |
| `template-our-story.php` | `our-story.css` | `our-story.js` | `template-parts/our-story/data-our-story.php` | `section-{hero,philosophy,alchemy,commitment}.php` |
| `template-cookie-policy.php` | `cookie-policy.css` | `cookie-policy.js` | `template-parts/cookie-policy/data-cookie-policy.php` | `section-{hero,toc,section}.php` |
| `template-privacy-policy.php` | `privacy-policy.css` | `privacy-policy.js` | `template-parts/privacy-policy/data-privacy-policy.php` | `section-{hero,toc,section}.php` |
| `template-terms-of-service.php` | `terms-of-service.css` | `terms-of-service.js` | `template-parts/terms-of-service/data-terms-of-service.php` | `section-{hero,toc,section}.php` |
| `template-accessibility-statement.php` | `accessibility-statement.css` | `accessibility-statement.js` | `template-parts/accessibility-statement/data-accessibility-statement.php` | `section-{hero,…}.php` |
| `template-careers.php` | `careers.css` | `careers.js` | `template-parts/careers/data-careers.php` | `section-{hero,…}.php` |
| `template-contact.php` | `contact.css` | `contact.js` | (inline trong template / data-…) | — |
| `template-faq.php` | `faq.css` | `faq.js` | `template-parts/faq/data-faq.php` | `section-{hero,search,categories,cta}.php` |
| `template-gift-cards.php` | `gift-cards.css` | `gift-cards.js` | `template-parts/gift-cards/data-config.php` | `section-{hero,config,bento}.php` |
| `template-help-center.php` | `help-center.css` | `help-center.js` | `template-parts/help-center/data-help-center.php` | `section-{hero,…}.php` |
| `template-our-ingredients.php` | `our-ingredients.css` | `our-ingredients.js` | `template-parts/our-ingredients/data-our-ingredients.php` | `section-{hero,…}.php` |
| `template-shipping-returns.php` | `shipping-returns.css` | `shipping-returns.js` | `template-parts/shipping-returns/data-shipping-returns.php` | `section-{hero,…}.php` |
| `template-sustainability.php` | `sustainability.css` | `sustainability.js` | `template-parts/sustainability/data-sustainability.php` | `section-{hero,…}.php` |
| Single product (WC `is_product()`) | `single-product.css` | `product.js` | — | `template-parts/product/{product-tabs,product-badges}.php` |
| Cart (WC `is_cart()`) | `cart.css` | `cart.js` | — | `woocommerce/cart/{cart,cart-empty}.php` |
| Checkout (WC `is_checkout()`) | `checkout-page.css` + `checkout-payment.css` + `bacs-qr` (in `checkout-payment.css`) | `checkout.js` + `checkout-payment.js` + `checkout-bacs-qr.js` | — | `woocommerce/checkout/{payment-method,review-order,thankyou}.php` |
| Account (WC `is_account_page()`) | `account.css` | `account.js` | — | `woocommerce/myaccount/{…}.php` |
| Wishlist (`template-wishlist.php`) | (dùng `main.css`) | `wishlist.js` | — | — |

**Điều kiện đặt tên**:
- Slug của page template (`template-{slug}.php`) **phải** trùng với tên file CSS/JS (`{slug}.css` / `{slug}.js`).
- **Trừ các ngoại lệ đã định**:
  - Single product page (WC dùng `is_product()`) → `single-product.css` + `product.js` (CSS theo scope, JS theo handle ngắn).
  - Checkout có 2 CSS (`checkout-page.css` + `checkout-payment.css`) vì payment là concern riêng có animation Motion.
  - Wishlist page dùng `main.css` (không có CSS riêng).
- **Khi thêm trang mới**: cập nhật bảng này + đảm bảo 4 file `template-{slug}.php` + `assets/css/{slug}.css` + `assets/js/{slug}.js` + `template-parts/{slug}/data-{slug}.php` cùng tồn tại.

**Lịch sử cập nhật bảng** (thêm dòng khi có thay đổi):
- `2026-08-10`: thêm §5h — selector phải khớp DOM thật; visual parity rule cho 2 element cùng vai trò (rút ra từ bug Place Order button).
- `2026-08-09`: refactor — tách cart helpers, approval handler, legal pages; thêm section-section.php pattern cho legal pages.
- (Trước đó: bảng chưa tồn tại — chỉ áp dụng rule 1-1 thủ công.)

**Cách audit nhanh** (khi nghi ngờ có file lệch tên):
1. Chạy `Get-ChildItem assets/css/ -Name` → so sánh với cột CSS.
2. Chạy `Get-ChildItem assets/js/ -Name` → so sánh với cột JS (bỏ qua `lib/`).
3. File nào có trong folder nhưng không có trong bảng → có thể là dead code → kiểm tra trước khi xoá.
4. File nào có trong bảng nhưng không có trong folder → check git log hoặc người maintain trước.

### 6. Quy ước PHP
> *"An toàn, có tiền tố, dịch được."*

- Hàm prefix `dg_` (vd `dg_is_woocommerce_active()`); class prefix `DG_`, PascalCase (vd `DG_Product`). Tên file = tên class: `class-dg-product.php` ↔ `DG_Product`.
- Đầu **mọi** file PHP: docblock `@package Dragon_Glow` + `defined( 'ABSPATH' ) || exit;`.
- **Luôn escape output:** `esc_html()`, `esc_attr()`, `esc_url()` (và biến thể `esc_html_e()` / `esc_html__()`...). Không echo dữ liệu thô.
- **Luôn i18n** với text-domain `'dragon-glow'`.
- Dùng type hints PHP 8.0+ (`: bool`, `: array`, `: string`, `: void`...) và `@return` trong docblock.
- **Mọi comment (docblock, inline, section divider) viết bằng tiếng Anh** — đây là chuẩn quốc tế, giúp code dễ bảo trì và chia sẻ trong môi trường đa quốc gia.
- **Mọi content hiển thị cho user (text trong `__()`, `esc_html__()`, label, placeholder, message...) viết bằng tiếng Anh** — theme sẽ target thị trường quốc tế, dùng hệ thống i18n để dịch sang các ngôn ngữ khác (Việt/Nhật/Hàn...) qua file `.po/.mo`.

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
