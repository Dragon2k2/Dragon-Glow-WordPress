# Changelog - Dragon Glow

> Moved out of `CLAUDE.md` so it stops loading on every session.
> `CLAUDE.md` keeps only the 5 most recent entries.

- `2026-11-09e`: My Account → Account details — **nút "Save changes" gọn như nút "Save address" ở mobile**. Yêu cầu: nút hình 2 (`dg-account-form__submit`) full-width, muốn giống kích thước gọn của nút hình 1; chỉ mobile (≤640px), desktop/tablet giữ nguyên, chuẩn enterprise WordPress, không commit git. Root cause: block mobile ép `.dg-account-form__submit { width: 100%; min-width: auto }` nên nút bè full-width, trong khi nút hình 1 là `inline-flex` rộng theo nội dung (`padding 12px 24px`, cùng font Montserrat 14px/600, icon 18px). Sửa trong block `@media (max-width: 640px)`: submit về `width: auto; min-width: 0` (kế thừa padding/font/border-radius pill từ `.dg-btn` gốc → cùng cỡ nút hình 1) + `.dg-account-form__actions` thêm `align-items: center` để nút gọn nằm giữa (đồng nhịp với Save address đã căn giữa ở task trước). Không đụng markup/JS. `DG_VERSION` → `1.16.53`. Không commit git.

- `2026-11-09d`: My Account → Edit Address — **nút "Save address" căn giữa ở mobile**. Yêu cầu: nối tiếp task căn phải, chỉ mobile (≤640px) căn giữa, desktop/tablet giữ căn phải, chuẩn enterprise WordPress, không commit git. Sửa: thêm `justify-content: center` cho `.dg-account-address-edit__actions` trong block `@media (max-width: 640px)` sẵn có của cụm address trong `assets/css/account.css` (breakpoint mobile chuẩn của theme; rule trong media thắng rule gốc cùng specificity vì đứng sau + match). Không đụng markup/JS. `DG_VERSION` → `1.16.52`. Không commit git.

- `2026-11-09c`: My Account → Edit Address — **nút "Save address" căn phải ở desktop, mobile/tablet đồng bộ**. Yêu cầu: `<button class="button dg-account-address-edit__save">` đang nằm trái, chuyển sang phải; làm ở desktop để mobile/tablet đồng bộ theo, chuẩn enterprise WordPress, không commit git. Root cause: `.dg-account-address-edit__actions` là flex nhưng không khai báo `justify-content` → mặc định `flex-start` (căn trái). Sửa: thêm `justify-content: flex-end` vào đúng rule gốc trong `assets/css/account.css` (không media query → mọi breakpoint cùng căn phải, đồng bộ đúng yêu cầu). Không đụng markup (giữ `<p>` bọc nonce/hidden input), không đụng JS submit/spinner. `DG_VERSION` → `1.16.51`. Không commit git.

- `2026-11-09b`: Terms + Cookie — **mang cấu trúc vỏ panel đã thành công từ Privacy sang**. Yêu cầu: tương tự, thanh cuộn dropdown mục lục ôm theo bo góc ở mobile/tablet (≤1023px), không đụng desktop, chuẩn enterprise WordPress, không commit git. Sửa y hệt phương án đã verify: `style.css` thêm `--terms/cookie-toc-panel-scrollbar-inset: 0.75rem`; `section-toc.php` 2 trang bọc `ul` trong div vỏ `hidden`; CSS tách 2 lớp (vỏ `overflow: hidden` giữ border/radius/shadow, ruột `margin-right: -10px` + `padding-right: 10px` + thumb `border 3px transparent` + `content-box`); JS `openToc/closeToc` gỡ/gắn `hidden` cả vỏ; no-JS ẩn cả vỏ. `php -l` sạch 3 file. `DG_VERSION` → `1.16.50`. Không commit git.

- `2026-11-09a`: Privacy Policy — **thanh cuộn dropdown mục lục ôm theo bo góc panel ở mobile/tablet**. Yêu cầu: đầu/cuối thanh cuộn của `<ul class="dg-privacy-toc-list" role="listbox">` đang nằm ngoài border cong; chỉ mobile/tablet (≤1023px), không đụng desktop, chuẩn enterprise WordPress, không commit git. Root cause: panel `border-radius: 1rem` + `overflow-y: auto` nhưng rãnh `::-webkit-scrollbar-track` không có `margin` → thumb WebKit kéo dài full chiều cao, đè lên đường cong ở 2 góc. Sửa lần 1: token scoped `--priv-toc-panel-scrollbar-inset: 0.75rem` (`style.css` `:root`, token-first §8a) + track `margin-top/bottom` theo token trong block `@media (max-width: 1023px)` — debug console của user cho thấy token đã load (`12px 12px`) mà thumb vẫn đè viền → margin track không đủ vì thumb tự vẽ tràn. Sửa lần 2 (chắc chắn): thumb dùng `border: 3px solid transparent` + `background-clip: content-box` (lõi thumb giữ 4px như cũ, rãnh rộng 10px) + `border-radius: 8px` → lõi thumb thụt khỏi mép phải và 2 đầu, ôm theo đường cong. Debug v2 của user (`?ver=1.16.48`): rãnh `10px` + thumb `border 3px transparent` + `background-clip: content-box` + track `margin 12px` đều đã ăn, nhưng `scrollbarW thực tế = 2` (Chrome Mobile scale/rút gọn) + `borderRadius 16px` + `border 0.8px` → rãnh WebKit vẫn vẽ tràn lên viền vì `overflow` và `border` nằm cùng một box. Sửa lần 3 (cấu trúc): tách panel thành 2 lớp — `.dg-privacy-toc-panel` (vỏ: giữ `position/border/radius/shadow/backdrop`, thêm `overflow: hidden` để kẹp rãnh vào trong) + `.dg-privacy-toc-list` (ruột: chỉ `overflow-y: auto` + scrollbar, `margin-right: -10px` + `padding-right: 10px` đẩy rãnh vào trong 10px, nền/border/radius/shadow về 0) → thanh cuộn không bao giờ chạm đường cong. Markup `section-toc.php` bọc thêm div vỏ (giữ nguyên id/role/aria trên `ul`); JS `openToc/closeToc` gỡ/gắn `hidden` cho cả vỏ; CSS no-JS ẩn cả vỏ; bỏ rule `:has()` thừa. `DG_VERSION` → `1.16.49`. Không commit git.

- `2026-11-08r`: Accessibility — **sửa icon dropdown mục lục vẫn lệch 4px (specificity)**. Yêu cầu: nối tiếp task căn giữa, debug console của user cho thấy `iconTop_css: "28px"` + `transform: matrix(1,0,0,1,0,-20)` — tức rule `top: 50%` gốc vẫn thắng; chỉ mobile/tablet, không đụng desktop, chuẩn enterprise WordPress, không commit git. Root cause: rule gốc `.dg-accessibility-toc-icon` (0,1,0) đứng **sau** media query trong file, mà rule sửa trong media cũng chỉ (0,1,0) → theo cascade rule đứng sau thắng bất kể media match → `top: 50%` gốc đè `top: 0.75rem`. Sửa: tăng specificity rule sửa thành `.dg-accessibility-toc-mobile .dg-accessibility-toc-icon` (0,2,0) ở cả 2 nhánh (≤1023px, ≤640px) → thắng rule gốc mà không cần `!important`, không đổi thứ tự file. Kiểm chứng bằng debug của user: kỳ vọng sau deploy `iconTop_css: "12px"`, `iconTransform_css: "none"`, `drift_px: 0`, `centered: true`. `DG_VERSION` → `1.16.45`. Không commit git.

- `2026-11-08q`: Accessibility — **icon dropdown mục lục căn giữa tâm chữ ở mobile/tablet**. Yêu cầu: nối tiếp task pill, icon `expand_more` trong ảnh lệch khỏi tâm text; chỉ mobile/tablet, không đụng desktop, chuẩn enterprise WordPress, không commit git. Root cause: `.dg-accessibility-toc-icon` dùng `top: 50% + translateY(-50%)` nhưng neo theo `.dg-accessibility-toc-mobile` — wrapper có `padding: 0.75rem 0 0.25rem` ở mobile/tablet nên tâm wrapper ≠ tâm select (lệch đúng 4px = nửa chênh padding trên/dưới). Sửa: icon chuyển sang `top: 0.75rem` (= padding-top wrapper) + `height: 44px` (= chiều cao select) + `display: inline-flex; align-items: center` → tâm icon ≡ tâm select mọi breakpoint; nhánh `≤640px` thêm `height: 40px` theo select. Kiểm chứng (Chrome DevTools MCP, 502px): `iconTop: 0`, `iconH: 40` = `selH: 40`, **lệch tâm 0px** (`centered: true`), không tràn ✓; desktop giữ `top: 50%` gốc. Đã dọn DOM/style tạm. `DG_VERSION` → `1.16.44`. Không commit git.

- `2026-11-08p`: Accessibility — **dropdown mục lục (`.dg-accessibility-toc-select`) kiểu pill glass ở mobile/tablet** (≤1023px). Yêu cầu: giao diện đẹp phù hợp hơn; chỉ mobile + tablet, không đụng desktop, chuẩn enterprise WordPress, không commit git. Sửa: `assets/css/accessibility-statement.css` thêm 2 block — (1) `max-width: 1023px`: select thành pill `border-radius: 9999px` + nền glass + `box-shadow` nhẹ, `height: 44px`, `font-size: 0.875rem` + `font-weight: 500`, `ellipsis` khi nhãn dài; wrapper bỏ nền/blur/border cũ cho thoáng; icon chevron nhỏ lại + màu primary; hover/focus viền primary (+ focus ring gold cho a11y). (2) `max-width: 640px`: `height: 40px` + `font-size: 0.8125rem` vừa màn hẹp. Desktop ≥1024px dropdown `display: none` sẵn (sidebar thay thế) → không đổi. Kiểm chứng (Chrome DevTools MCP, dựng đúng DOM + inject đúng thứ tự rule, 502px ~ tablet/mobile): `height: 40px`, `font-size: 13px`, `border-radius: 9999px`, `font-weight: 500`, không tràn ✓; `:focus-visible` giữ ring — bàn phím vẫn thấy. Đã dọn DOM/style tạm. `DG_VERSION` → `1.16.43`. Không commit git.

- `2026-11-08o`: Thank-you — **dòng email xác nhận (`.dg-thankyou-email`) gọn tinh tế ở mobile** (≤767px). Yêu cầu: thu nhỏ font cho tinh tế; chỉ mobile, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Sửa: `assets/css/thankyou.css` thêm block mobile-only → `font-size: 16px → 13px`, `line-height: 1.65`, `max-width: 300px` + `margin: 0 auto 24px` (gọn vào giữa, nhịp đều với subtitle). Kiểm chứng (Chrome DevTools MCP, cột 256px mô phỏng 320px): `font-size: 13px`, cao **64px** (3 dòng đều), căn giữa, `docOverflow = 0` ✓; tablet/desktop không đổi. Đã dọn DOM/style tạm. `DG_VERSION` → `1.16.42`. Không commit git.

- `2026-11-08n`: Thank-you — **2 nút actions nhỏ gọn tinh tế hơn ở mobile** (≤639px). Yêu cầu: nối tiếp task 1 hàng, làm gọn + tinh tế hơn; chỉ mobile, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Sửa (cùng block mobile-only): nút `flex: 1 1 0 → 0 1 auto` (rộng theo nội dung, cụm gọn vào giữa thay vì bè 2 nửa), `padding: 10px 8px → 8px 14px`, `font-size: 11px → 10px`, `border-width: 1.5px → 1px` (viền mảnh thanh thoát), actions thêm `justify-content: center` + `gap: 8px`. Kiểm chứng (Chrome DevTools MCP, cột 256px mô phỏng 320px): cùng 1 hàng, mỗi nút cao **33px** (trước 38px), tổng 256px vừa khít, `docOverflow = 0` ✓; tablet/desktop không đổi. Đã dọn DOM/style tạm. `DG_VERSION` → `1.16.41`. Không commit git.

- `2026-11-08m`: Thank-you — **2 nút actions (`Continue Shopping / My Account`) gọn 1 hàng ngang ở mobile** (≤639px). Yêu cầu: thu nhỏ font + 2 nút cùng 1 dòng ngang ở mobile, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Root cause: `.dg-thankyou-actions` mặc định `flex-direction: column` → mobile 2 nút xếp dọc, nút `padding: 14px 32px` + `font-size: 14px` cồng kềnh trên màn hẹp. Sửa: `assets/css/thankyou.css` thêm block mobile-only → actions `flex-direction: row` + `gap: 10px`, nút `flex: 1 1 0` + `padding: 10px 8px` + `font-size: 11px` + `white-space: nowrap` (chia đều 2 nửa, không rớt dòng). Kiểm chứng (Chrome DevTools MCP, dựng đúng DOM + inject đúng rule, cột 256px mô phỏng 320px): 2 nút **cùng 1 hàng**, mỗi nút cao **38px**, `font-size: 11px`, tổng 256px = vừa khít ✓; `docOverflow = 0` ✓; tablet/desktop giữ nguyên (media `min-width: 640px` không đổi, block mới giới hạn `max-width: 639px`). Đã dọn DOM/style tạm. `DG_VERSION` → `1.16.40`. Không commit git.

- `2026-11-08l`: Thank-you — **chuẩn hoá `padding-bottom: 64px` cho `.dg-thankyou-main`, desktop/mobile/tablet đồng bộ**. Yêu cầu (làm rõ): đáy 64px là chuẩn chung mọi breakpoint, không giới hạn mobile như các task trước. Chuẩn enterprise WordPress, không commit git. Sửa: `assets/css/thankyou.css` → tách shorthand thành `padding-block: 48px 64px` (đáy 64px đặt **một lần duy nhất** ở rule gốc, desktop/mobile/tablet cùng kế thừa) + `padding-inline` theo breakpoint (mobile `var(--spacing-margin-mobile)` / desktop `var(--spacing-margin-desktop)` trong media 768px) — ba breakpoint tự đồng bộ, không lặp đáy. Kiểm chứng (Chrome DevTools MCP, dựng đúng DOM + inject đúng rule): desktop 1282px → `pb: 64px`, `pt: 48px`, đáy nút cách đáy wrapper **64px** ✓; mobile 502px → `pb: 64px` ✓; tablet kế thừa rule gốc + media 768px → đồng bộ 64px (cùng 1 khai báo). Đã dọn DOM/style tạm. `DG_VERSION` → `1.16.39`. Không commit git.

- `2026-11-08k`: Checkout — **cụm `Calculated at next step` ép 1 dòng ở mobile** (≤640px). Yêu cầu: nối tiếp task shipping, chữ đang rớt 2 dòng, chỉ thu nhỏ font ô giá trị cho vừa 1 dòng, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Sửa: `assets/css/checkout-page.css` trong block mobile-only sẵn có → ô giá trị Shipping/Tax thêm `font-size: 10px` + `white-space: nowrap` (kích thước ước lượng ~120px < khoảng trống ~116px ở 320px sau khi trừ nhãn + gap; `nowrap` khóa cứng 1 dòng). Kiểm chứng (Chrome DevTools MCP, inject đúng rule lên trang live): cả 2 ô giá trị `font-size: 10px`, `white-space: nowrap`, cao **20px = đúng 1 dòng** (`rowH: [20,20]`), `docOverflow = 0` ✓; tablet/desktop không thêm rule → giữ nguyên. Đã dọn style tạm. `DG_VERSION` → `1.16.38`. Không commit git.

- `2026-11-08j`: Checkout — **hàng Shipping (`Calculated at next step`) gọn lại ở mobile** (≤640px). Yêu cầu: hàng `Shipping | Calculated at next step` trong ảnh mobile rớt chữ xấu, chỉ làm mobile, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Root cause: hàng là flex `justify-between`, cụm từ dài phía phải không bị giới hạn bề rộng nên ở cột summary hẹp mobile nó rớt dòng, vỡ nhịp với Subtotal/Tax. Sửa: `assets/css/checkout-page.css` thêm block mobile-only → hàng Shipping/Tax `gap: 12px` + `font-size: 0.8125rem`, ô giá trị `text-align: right; flex-shrink: 0; max-width: 55%` (không bị nhãn đẩy, tự xuống dòng gọn trong nửa phải). Kiểm chứng (Chrome DevTools MCP, inject đúng rule lên trang live, 390px): trước `176×24px` rớt xấu → sau giá trị `143×20px` **đúng 1 dòng** (`lines: 1`), cùng hàng với nhãn (`y` bằng nhau), `text-align: right` ✓; hàng Tax cùng hàng ✓; `docOverflow = 0` ✓; tablet/desktop không thêm rule → giữ nguyên. Đã dọn style tạm. `DG_VERSION` → `1.16.37`. Không commit git.

- `2026-11-08i`: Checkout — **xoá 2 div rỗng `.woocommerce-shipping-fields` + `.woocommerce-additional-fields`**. Yêu cầu: xoá hẳn 2 thẻ (không giấu bằng CSS), chuẩn enterprise WordPress, không commit git. Root cause: theme tự render form qua `dg_render_wc_checkout()` (`inc/woocommerce/checkout.php`) và gọi `$dg_wc_checkout->checkout_form_shipping()` → WC nạp `checkout/form-shipping.php`, chính template này in ra cả 2 div; ở store này cả 2 đều rỗng (ship-to-different tắt, `order_comments` đã chuyển vào billing ở `dg_customize_checkout_fields`) → giữ lại chỉ tạo div trống làm vỡ khoảng cách cột trái. Sửa: bỏ lệnh gọi `checkout_form_shipping()`, giữ `checkout_form_billing()` + giải thích trong comment; không đụng hook/filter WC (giữ tương thích khi bật ship-to-different sau này — lúc đó cần render lại). Kiểm chứng: `php -l` sạch; grep `checkout_form_shipping|woocommerce_checkout_shipping` trong theme → chỉ còn comment; đo trên site live (đang chạy code cũ): form chỉ còn `.woocommerce-billing-fields` + card payment, không còn div trống cao >40px → sau deploy 2 div rỗng biến mất, billing + payment + summary giữ nguyên. `DG_VERSION` → `1.16.36`. Không commit git.

- `2026-11-08h`: Cart — **2 nút bulkbar (`Remove selected` / `Clear cart`) nhỏ gọn, tinh tế hơn ở mobile** (≤640px). Yêu cầu: nối tiếp task trust-badge, chỉ làm mobile, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Sửa: `assets/css/cart.css` trong block `@media (max-width: 640px)` sẵn có → `.dg-cart-bulkbar__inner { padding: 10px 12px }` (panel gọn lại), `.dg-cart-bulkbar__actions { gap: 6px }`, nút `padding: 6px 8px` + `font-size: 11px` + `letter-spacing: 0.02em` + `gap: 4px` + `white-space: nowrap` (không rớt 2 dòng), icon `16px → 13px`. Kiểm chứng (Chrome DevTools MCP, dựng đúng DOM bulkbar + inject đúng rule): 390px → 2 nút **cùng 1 hàng**, mỗi nút cao **30px** (gọn hơn gốc nhưng vẫn dễ bấm, giữ nguyên `focus-visible` + `disabled`), tổng 253px ≤ inner 292px ✓; tablet/desktop **không thêm rule** → giữ nguyên. Đã dọn DOM/style tạm. `DG_VERSION` → `1.16.35`. Không commit git.

- `2026-11-08g`: Cart — **căn giữa hàng trust badge (Vegan Formula / Cruelty-Free / Organic Ingredients) ở tablet** (641–1024px). Yêu cầu: nối tiếp task mobile, chỉ làm tablet, không đụng mobile/desktop, chuẩn enterprise WordPress, không commit git. Sửa: `assets/css/cart.css` thêm block tablet-only `@media (min-width: 641px) and (max-width: 1024px)` → `.dg-cart-trust-badges { justify-content: center; }` (tái dụng class móc đã thêm ở task mobile trong `woocommerce/cart/cart.php`, không sửa markup). Kiểm chứng (Chrome DevTools MCP, inject đúng 2 rule mobile/tablet lên trang live): 768/770px → `justify-content: center` ✓; biên 641/643px → `center` ✓; biên 1024/1026px → `normal` (rơi khỏi media query) ✓; desktop 1280/1282px → `normal` (không đổi) ✓; đã dọn style + DOM tạm sau khi đo. `DG_VERSION` → `1.16.34`. Không commit git.

- `2026-11-08e`: My Account → Orders — **sửa bảng lịch sử đơn hàng hiển thị rất xấu ở kích thước mobile** (≤640px). Yêu cầu: tối ưu `<table class="woocommerce-orders-table … account-orders-table">` cho mobile, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Vấn đề (đo bằng Playwright trên mock markup thật, `tmp-resp/account-pagination/measure-orders-mobile.js`): block mobile cũ ở `assets/css/account.css` (`@media (max-width: 768px)`) chỉ style `tbody td` — nhưng WooCommerce render cột đầu là **`<th scope="row">`** (ô Order), không phải `<td>`. Hậu quả đo được ở 390px: ô Order giữ `display: table-cell` (55×47px) → **tự đứng một dòng riêng ở góc trên**, không có nhãn, không có padding đồng nhất, lệch với 4 ô `td` bên dưới (đã là flex, xếp dọc từ y=116) → bảng trông vỡ. Thêm nữa `tbody td:first-child` không còn trỏ vào ô Order (nó là `<th>`) nên **ô Date bị nhận nhầm style "cột đầu"** (bold + màu gold). Root cause: selector dựa trên `:first-child`/`:last-child` thay vì class cột, cộng bỏ sót `<th>`. Sửa — (1) `assets/css/account.css`: thêm block `@media (max-width: 640px)` mới (giữ nguyên block 768px cho tablet 641–768px): `thead` **visually-hidden** (`clip-path: inset(50%)`) thay vì `display: none` → giữ tên cột trong cây trợ năng; `tbody th` + `tbody td` cùng nhận `display: flex; justify-content: space-between; gap: 16px; min-height: 44px` → mọi ô cùng x, cùng rộng, xếp dọc đều; ô Order (`th.woocommerce-orders-table__cell-order-number`) làm **header của card** (số đơn trái, nền riêng, rule đậm hơn) và `::before { content: none }` (không nhãn trùng); ô Date bỏ style "cột đầu" (`font-weight: 400`); ô Actions làm footer, nút "View" full-width + `min-height: 44px` (WCAG 2.5.5); bỏ `transform: translateX(2px)` của `:hover` (không có ý nghĩa trên touch). (2) `woocommerce/myaccount/orders.php`: bọc giá trị ô Total trong `<span class="woocommerce-orders-table__cell-value">` → nếu không, flex tách `<span class="woocommerce-Price-amount">` và text node "for 6 items" thành 2 item. (3) `style.css` `:root`: thêm 9 token component `--account-orders-card-*` / `--account-orders-cell-*` / `--account-orders-button-min-height` (theo §8a token-first, không hard-code trong file trang). Kiểm chứng: `php -l woocommerce/myaccount/orders.php` → không lỗi; Playwright `tmp-resp/account-pagination/verify-orders-mobile.js` (13 viewport 320→1440px) → **ALL PASS**: mobile 320/375/390/414/480/600/639/640px → `thead` còn trong a11y tree, `th`+`td` đều flex, mọi ô cùng x/cùng rộng/xếp dọc, ô Date không còn bold, nút View **44px** ≥ 44px, `docOverflow = 0`; **tablet 641/768px → không đổi** (`th` vẫn `table-cell`, `rowH` 309px, thead `none`); **desktop 1024/1280/1440px → không đổi** (`thead` hiện, mọi ô `table-cell`, `rowH` 72px); so sánh ảnh trước/sau (`orders-before-390.png` ↔ `orders-after3-390.png`) → sau là card gọn gàng, đồng bộ bề rộng. `DG_VERSION` → `1.16.29`. Không commit git.

- `2026-11-08d`: My Account → Orders — **sửa phân trang desktop không hiện dấu `…` (ellipsis)** khi khách có ≤13 trang. Yêu cầu: sau khi tối ưu desktop, người dùng báo *"trong hình là kết quả sau khi bạn làm thì paginate vẫn chưa hiển thị ..."* (kèm ảnh chụp). Không commit git. Chẩn đoán (theo `systematic-debugging`, đo pixel ảnh người dùng): ảnh 1024×514 là ảnh chụp **viewport 1536px đã thu nhỏ còn 1024px** (tỉ lệ 2/3, pill 24px ảnh ↔ 36px CSS); dựng lại 14 cụm pixel trong dải `y=359..382` → #1 là pill active (trang 1), #2–#13 là **11 số (2→12)**, #14 là nút Next → **12 trang, KHÔNG có ellipsis**, khớp 100% với `max_num_pages = 12` của store (113 đơn). Root cause: `woocommerce/myaccount/orders.php` còn nhánh `if ( $total_pages <= $full_limit ) { $visible_wide = range( 1, $total_pages ); }` với `dg_account_orders_pagination_full_limit()` = 13 → 12 ≤ 13 → **cố tình bỏ qua window rộng** và in hết 12 số; điều này **lệch với WooCommerce core** (`paginate_links()`, `mid_size = 2` → 12 trang phải ra `1 2 3 … 12`). Sửa: (1) `inc/woocommerce/account/orders.php` — **xoá hẳn** helper `dg_account_orders_pagination_full_limit()` (kèm filter `dg_account_orders_pagination_full_limit`) vì không còn ngưỡng "hiện đủ"; guard duy nhất còn lại là `$total <= 5` trong `dg_account_orders_page_window()` → danh sách ≤5 trang vẫn hiện đủ, không collapse. (2) `woocommerce/myaccount/orders.php` — xoá nhánh override `$visible_wide = range(1, $total_pages)` → **cả 2 window (compact `current±1`, wide `current±2`) luôn collapse** giống hệt core. (3) `assets/css/account.css` — sửa comment block `@media (min-width: 1280px)` bỏ claim "13 số vừa 1 hàng 745px" (không còn đúng) và mô tả lại hành vi mới. Không đụng CSS layout, không đụng JS. Kiểm chứng: PHP CLI test `tmp-resp/account-pagination/test-window-desktop.php` (bỏ case `full_limit = 13`, thêm case 12 trang khớp ảnh người dùng: `cur=1 total=12 r=2 → 1,2,3,12`, `cur=6 → 1,4..8,12`, `cur=12 → 1,10,11,12`, và case "≤5 trang không collapse") → **ALL PASS**; `php -l` cả 2 file → không lỗi cú pháp; **đo trực tiếp site live** (Chrome DevTools MCP, tab đã đăng nhập, 113 đơn / 12 trang): 1440px trang 1 → `1 2 3 … 12` (**trước: `1 2 3 4 … 12` thiếu dấu `…`**); 1280px trang 6 → `1 … 4 5 6 7 8 … 12`; 1280px trang 12 → `1 … 10 11 12` (`aria-current="page"=12`, đúng 1 nút Previous, không có Next); 1024px → `1 … 5 6 7 … 12` (**không đổi**); 390px → `1 … 5 6 7 … 12` (**không đổi**), `pagH` 93px (Prev/Next 45px ≥ 44px WCAG 2.5.5); ở 1280px dải số 362px ≤ cột 776px → **1 dòng, không tràn**, `docOverflow = 0` ✓. `DG_VERSION` → `1.16.28`. Không commit git.

- `2026-11-08c`: My Account → Orders — **sửa phân trang vỡ ở kích thước desktop** khi lịch sử đơn hàng dài. Yêu cầu: làm tối ưu phân trang cho desktop giống đã làm cho mobile/tablet, chuẩn enterprise WordPress, không commit git. Vấn đề (đo bằng Playwright trên layout thật ở 1280px — viewport desktop hẹp nhất, cột nội dung 776px): hàng phân trang gồm `Previous` (83px) + hàng số trang + `Next` (58px). Với **≤13 trang** hàng vừa khít 1 dòng (745px ≤ 776px) → hiển thị đẹp. Nhưng **từ 14 trang** hàng số vượt cột → `.woocommerce-pagination` (`flex-wrap: wrap`) **gãy thành 2–6 dòng lệch lạch**: `Previous` ở dòng 1, các số trang vắt ngang dòng 2, `Next` rơi xuống dòng cuối (đo ở 50 trang: `prevRow/numsRow/nextRow` = 0/1/5, chiều cao khối 262px). Tình huống này rất phổ biến ở store enterprise (khách có 100+ đơn hàng). Sửa — theo đúng pattern window của WooCommerce core (`paginate_links()`, `mid_size`): (1) `inc/woocommerce/account/orders.php` — helper `dg_account_orders_page_window( int $current, int $total, int $radius = 1 )` nhận thêm `$radius` (bán kính window) + thuật toán 3 nhánh giống hệt core (đầu → liệt kê `1..current+radius` rồi nhảy tới trang cuối; giữa → `1 … current±radius … total`; cuối → `1 … current-radius..total`); thêm helper `dg_account_orders_pagination_full_limit(): int` (mặc định 13, có `apply_filters` `dg_account_orders_pagination_full_limit`) → số trang vẫn nằm trọn 1 dòng ở desktop. (2) `woocommerce/myaccount/orders.php` — render **2 tầng window** (compact `current±1` cho mobile/tablet, wide `current±2` cho desktop); mỗi trang được tag riêng `data-dg-page-out-compact` (ngoài window hẹp) và `data-dg-page-out-wide` (ngoài window rộng), kèm **2 bộ ellipsis riêng** `data-dg-page-ellipsis="compact"|"wide"` → đúng 1 bộ hiển thị theo breakpoint, không bao giờ lệch dấu. Desktop dùng window rộng khi tổng trang > 13, ngược lại giữ **đủ 12 số** như cũ. (3) `assets/css/account.css` — block `@media (min-width: 1280px)` mới: `flex-wrap: nowrap` + ẩn `[data-dg-page-out-wide]` + hiện ellipsis `wide`; block `@media (max-width: 1279px)` dùng `[data-dg-page-out-compact]` + ellipsis `compact`; rule ellipsis gộp ra khối chung (không lặp 2 lần). Kiểm chứng: PHP CLI test `tmp-resp/account-pagination/test-window-desktop.php` (logic window thật, không phải bản port JS) → **ALL PASS** (radius 1/2, biên total=0, current vượt/clamp, radius=0); Playwright `tmp-resp/account-pagination/verify-desktop.js` → desktop 1280/1366/1440/1600/1920px với 12 trang → **đủ 12 số, 1 dòng, 0 ellipsis**; 50 trang → window `1 … 23 24 25 26 27 … 50`, **1 dòng, không tràn**; trang đầu/cuối/second → `1 2 3 … 50` / `1 … 48 49 50` / `1 2 3 4 … 50`; hồi quy mobile 320–640px + tablet 641–1279px → **không đổi** (`1 … 5 6 7 … 12`); a11y `aria-current="page"` + `aria-hidden="true"` giữ nguyên; `prefers-reduced-motion` → transition 0s; edge case 50 trang ở 390/834/1440px → không tràn ✓; thêm `tmp-resp/account-pagination/verify-edge-desktop.js` → **ngưỡng 13/14 trang @1280px** (13 trang = 13 số đủ, dải số 577px ≤ cột 776px; 14 trang → tự collapse `1 … 5 6 7 8 9 … 14`, dải số 364px, 1 dòng, không tràn) ✓; **đo trực tiếp trên site live** (Chrome DevTools MCP, tab đã đăng nhập): 1440px → `1 2 3 … 12` đủ 12 số, 1 dòng, cột 776px, không tràn; 1280px → đủ 12 số, 1 dòng; 834px → `1 2 … 12` (compact window, không đổi); 390px → `1 2 … 12` (không tràn) ✓. `DG_VERSION` → `1.16.27`. Không commit git.

- `2026-11-08b`: My Account → Orders — **sửa phân trang vỡ ở kích thước tablet** (641–1279px). Yêu cầu: làm tối ưu phân trang cho tablet giống đã làm cho mobile, theo chuẩn enterprise WordPress, không commit git. Vấn đề (đo bằng Playwright trên layout thật, có sidebar 280px từ 1024px): (1) `.woocommerce-pagination` là `display: flex; flex-wrap: wrap` → `Previous` + hàng 12 số trang (~530px) + `Next` (~589px tổng) **vượt cột nội dung** (561px ở 641px, 523px ở 1024px) → wrap thành **2–3 dòng lệch lạch** ở 641/768/834/1024/1080/1112px; (2) ở **1024px hàng số tràn khỏi panel** (panel 523px < hàng số 530px) → vỡ layout. Sửa: tái sử dụng đúng cơ chế window đã có (không viết lại markup) — `assets/css/account.css` thêm block `@media (max-width: 1279px)` (1) ẩn `[data-dg-page-hidden]` + hiện `.dg-account-pagination__ellipsis` (tái dụng markup window `dg_account_orders_page_window()` sẵn có trong `inc/woocommerce/account/orders.php`), (2) `.woocommerce-pagination { flex-wrap: nowrap; gap: var(--account-pagination-gap) }` + `.woocommerce-pagination__numbers { flex-wrap: nowrap; flex-shrink: 1; min-width: 0 }` → **ép cả khối nằm 1 hàng duy nhất**, không wrap lệch dòng; (3) `.woocommerce-pagination__numbers` (mặc định) thêm `flex-wrap: wrap; min-width: 0` → desktop **tự wrap xuống dòng 2** thay vì tràn ngang khi khách có **> 12 trang** (edge case). `style.css` `:root` đồng bộ 3 token pill về giá trị desktop (`--account-pagination-pill-min` 34→36px, `--account-pagination-pill-height` 34→36px, `--account-pagination-pill-font` 13→14px) để pill trên tablet **khớp đúng chiều cao/màu** với desktop (trước: tablet dùng pill 34px, desktop 36px → lệch). Phân trang desktop (≥1280px) **giữ nguyên 12 số đầy đủ** — chọn ngưỡng 1280px (không phải 1200px) vì đo thật: hàng đầy đủ cần 606px, cột nội dung ở 1200px chỉ 696px → **vừa sát 1px** nên dễ wrap; từ 1280px cột rộng 776px → dư 170px. Kiểm chứng Playwright (`tmp-resp/account-pagination/final-verify.js`, 20 viewport 320→1920px): mobile 320–640px → **không tràn ngang**, Prev/Next cùng hàng, touch target 45px ≥ 44px (WCAG 2.5.5), window `1 … 5 6 7 … 12`; tablet 641/768/834/900/1023/1024/1080/1112/1200/1279px → **1 hàng duy nhất** (`flexWrap: nowrap`), **không tràn panel**, window `1 … 5 6 7 … 12`; desktop 1280/1440/1920px → **12 số đầy đủ**, 1 hàng, ellipsis ẩn; trang đầu/cuối → đúng 1 nút điều hướng; `prefers-reduced-motion` → transition 0s; edge case **50 trang** ở 390/834/1440px → không tràn ✓. `aria-current="page"` + `aria-hidden="true"` trên ellipsis giữ nguyên. `DG_VERSION` → `1.16.26`. Không commit git.

- `2026-11-08a`: Shipping & Returns — **thanh cuộn của modal Return giống hệt modal Apply** (Careers).

  Yêu cầu: giao diện thanh cuộn của `<div class="dg-rm-dialog" role="document">` phải giống `<div class="dg-apply-dialog" role="document">`.

  Vấn đề: `.dg-rm-dialog` có `overflow-y: auto` nhưng **không có** rule `::-webkit-scrollbar` nào → hiện thanh cuộn mặc định của hệ điều hành (không đồng bộ brand).

  Sửa:
  - `style.css` `:root` (section "Shipping & Returns Component"): thêm 4 token `--sr-scrollbar-size: 8px`, `--sr-scrollbar-track: var(--neutral-warm-100)`, `--sr-scrollbar-thumb: var(--neutral-400)`, `--sr-scrollbar-thumb-hover: var(--neutral-500)` — **cùng bảng màu** với `--a-surface-low` / `--a-outline` / `--a-outline-strong` của Careers (cả 2 đều trỏ về `--neutral-warm-100` / `--neutral-400` / `--neutral-500`).
  - `assets/css/shipping-returns.css`: thêm block `::-webkit-scrollbar` cho `.dg-rm-dialog` (width / track / thumb / thumb:hover) — **mirror y hệt** rule của `.dg-apply-dialog` trong `careers.css`.
  - `careers.css` **không đổi** (baseline tham chiếu).

  Ghi chú kỹ thuật: đo pixel trực tiếp trên máy dev **không đáng tin** vì Chrome dùng *overlay scrollbar* (`offsetWidth - clientWidth === 0` → scrollbar không chiếm layout). Cách kiểm chứng đúng là đọc `getComputedStyle(el, '::-webkit-scrollbar')` / `('::-webkit-scrollbar-thumb')` / `('::-webkit-scrollbar-track')` — đã xác nhận `var()` **resolve bình thường** trong pseudo-element scrollbar của Chrome.

  Kiểm chứng (Playwright, site live — inject nguyên file JS + CSS từ disk):
  - Baseline Apply: `width: 8px`, thumb `rgb(212, 194, 194)`, thumb radius `4px`, track `rgb(250, 249, 246)`, track radius `4px`, transition `background 0.25s`
  - Modal Return ở 320/390/768/1280px: **computed style giống hệt baseline** (từng thuộc tính khớp) ✓; scroll hoạt động (`maxScrollTop` 299/83 ở mobile) ✓; nút Close vẫn đóng modal ✓; không `pageerror` ✓

  `DG_VERSION` → `1.16.25`. Không commit git.

- `2026-11-07n`: Shipping & Returns — **sửa nút Close (×) và Cancel của modal Return không hoạt động** (click trúng nút nhưng modal không đóng / click bị chặn).

  Yêu cầu: `<button class="dg-rm-close" data-rm-close>` và `<button class="dg-rm-btn dg-rm-btn--secondary" data-rm-close>` bấm không có tác dụng.

  Vấn đề — có **3 nguyên nhân độc lập**, tất cả đều lộ ra khi chẩn đoán bằng hit-test (`document.elementFromPoint`) + bắt `pageerror` trên site live:
  1. **CSS — nút Close bị header đè**: `.dg-rm-close` là `position: absolute` (không có `z-index`), còn `.dg-rm-header` là `position: relative` và đứng **sau** trong DOM → header vẽ đè lên nút. Ở 390px, `elementFromPoint` trả về `header.dg-rm-header` → Playwright báo *"header intercepts pointer events"*, click thật của user **không trúng nút**.
  2. **JS — `EASE is not defined`**: `shipping-returns.js` dùng biến `EASE` ở `open()`/`close()` (dòng animate dialog) nhưng **không khai báo** `const EASE` (khác 14 file JS khác trong theme đều có) → `ReferenceError` ném ra **giữa chừng** `open()`, làm animation và các bước sau (focus, `body` scroll lock…) không chạy hết.
  3. **JS — `delay` non-finite**: `animate(children, …, { delay: children.length > 1 ? { each: 0.08 } : 0 })` — Motion v11 không nhận object `{ each }` (đó là cú pháp của `stagger()`) → *"Failed to read the 'delay' property from 'EffectTiming': The provided double value is non-finite"* → vòng reveal của group `[data-sr-group]` chết, kéo theo phần reveal của chính modal.

  Sửa:
  - `assets/js/shipping-returns.js`: thêm `const EASE = [ 0.16, 1, 0.3, 1 ];` (khớp giá trị các file khác); import thêm `stagger` từ Motion và đổi `delay: … { each: 0.08 }` → `stagger(0.08)`.
  - `assets/css/shipping-returns.css`: `.dg-rm-close` → thêm `z-index: 2` để nằm trên `.dg-rm-header` (đã `position: relative`) → không còn bị chặn click.

  Kiểm chứng (Playwright, site live — inject nguyên file JS + CSS từ disk):
  - 320/390/768/1280px: hit-test nút Close → `topElement` = `span` con của nút, `clickable: true` ✓; **click Close** → `hidden: true`, `dataOpen: false`, `bodyLock: false` ✓; **click Cancel** → đóng ✓; **ESC** → đóng ✓; **click overlay** → đóng ✓
  - Focus: mở modal → focus vào `.dg-rm-close`; đóng → focus trả về trigger `.dg-sr-returns-cta` ✓; focus trap: 12 lần Tab **luôn ở trong** dialog ✓
  - `prefers-reduced-motion: reduce` → mọi hành vi trên vẫn đúng ✓
  - **Không còn `pageerror`** (trước: 2 lỗi `EASE is not defined` + 1 lỗi `delay` non-finite) ✓

  `DG_VERSION` → `1.16.24`. Không commit git.

- `2026-11-07l`: Careers — **2 nút trong modal Apply nằm cùng một hàng ngang ở mobile** (chỉ mobile; tablet/desktop giữ nguyên).

  Yêu cầu: trong `<div class="dg-apply-actions">`, nút `Cancel` và `Send application` phải hiển thị cùng 1 dòng ngang ở kích thước mobile; không làm ở desktop và tablet.

  Vấn đề: `.dg-apply-actions` khai báo `flex-direction: column-reverse` ở mobile → 2 nút xếp **dọc** (nút phụ ở trên), chỉ từ `min-width: 640px` mới chuyển sang hàng ngang.

  Sửa (`assets/css/careers.css`):
  - `.dg-apply-actions` → `flex-direction: row` (mặc định) + `gap: 0.875rem`; khối `@media (min-width: 640px)` giữ nguyên `justify-content: flex-end` → tablet/desktop **không đổi** (nút căn phải, primary `min-width: 200px`).
  - Khối `@media (max-width: 639px)` mới: `align-items: stretch` + `gap: 0.625rem`; nút `display: flex` (canh giữa cả 2 chiều), `align-self: stretch` (đè lại `align-self: flex-start` kế thừa từ `.dg-careers-btn--secondary` → 2 nút **bằng nhau chiều cao**), `min-height: 44px` (chuẩn touch target WCAG 2.5.5), `font-size: 0.75rem`, `letter-spacing: 0.04em`, `padding: 0.75rem 0.625rem`.
  - Nút phụ `--secondary` → `flex: 0 0 auto` (chiều rộng theo nội dung, ngắn); nút chính `--primary` → `flex: 1 1 auto` + `min-width: 0` (chiếm phần còn lại → nhãn "SEND APPLICATION" luôn nằm **1 dòng**, không xuống dòng ở 320px).

  Kiểm chứng (Playwright, site live — inject nguyên file `careers.css` từ disk, mở modal qua `[data-apply-trigger]`):
  - 320/360/390/639px: `flexDirection: row`, 2 nút **cùng hàng** (`sameRow: true`), cùng chiều cao **44px**, `submitLines: 1` (nhãn 1 dòng), gap 10px, không overlap, nằm trong dialog, **không tràn ngang** (`scrollWidth == innerWidth`), không cắt chữ, touch target ≥ 44px ✓
  - 640/768/1280px: **không đổi** — `justify-content: flex-end`, gap 14px, cancel 118.2px / submit 201.5px (primary giữ `min-width: 200px`), cao 49.5px ✓
  - Không `pageerror` ✓

  `DG_VERSION` → `1.16.23`. Không commit git.

- `2026-11-07m`: Our Ingredients — **bỏ nền glass card của khối chữ Traceable** (chữ nằm trực tiếp trên ảnh, không còn nền/viền/đổ bóng/blur).

  Yêu cầu: xoá background của `<div class="dg-oi-traceable-content">`.

  Vấn đề phát hiện khi đo pixel thật: ảnh `traceable.webp` **không đồng nhất** — nền kem sáng (luma ~220) ở trái nhưng chai serum tối (luma ~57) ở giữa. Bỏ nền card hoàn toàn → chữ tối nằm trực tiếp trên chai tối → contrast **1.14–1.89:1**, fail WCAG AA. Ảnh cũng không đủ tối để dùng chữ trắng (nền kem sáng → contrast 1.16–2.65:1).

  Sửa — thay vì nền đục (card) hoặc overlay tối ảnh, dùng **halo sáng quanh nét chữ** (`text-shadow`):
  - `assets/css/our-ingredients.css` — `.dg-oi-traceable-content` bỏ `background` / `border` / `border-radius` / `box-shadow` / `backdrop-filter` + khối `@supports`; bỏ `padding` (không còn viền card, chỉ giữ `margin` để chữ không dính mép).
  - `assets/css/our-ingredients.css` — 4 rule text thêm `text-shadow: var(--oi-traceable-halo)` (headline dùng `--oi-traceable-halo-wide`).
  - `style.css` `:root` — xoá 6 token card (`--oi-traceable-card-bg/-bg-solid/-border/-radius/-shadow/-blur`), thêm `--oi-traceable-halo` + `--oi-traceable-halo-wide`; `--oi-traceable-rule` tăng alpha `0.25 → 0.35`, footnote `0.88 → 0.92`.

  Kiểm chứng (Playwright, site live — inject nguyên file `our-ingredients.css` + token `--oi-traceable-*` từ `style.css` trên disk; đo contrast bằng pixel thật: glyph tối nhất vs halo sáng quanh nó):
  - Card: `background: rgba(0,0,0,0)`, `border: 0px solid`, `box-shadow: none`, `backdrop-filter: none`, `padding: 0px` ✓
  - Contrast thực tế: 320px → headline **9.88**, body **4.89**, country **13.37**, footnote **6.86**; 390px → 7.89 / 5.09 / 13.77 / 6.40; 640px → 8.99 / 5.36 / 9.53 / 6.45; 768px → 16.36 / 8.92 / 16.37 / 7.35; 1024px → 13.72 / 7.76 / 14.71 / 6.70; 1280px → 14.32 / 7.90 / 14.76 / 6.80 → **đạt WCAG AA** ở mọi viewport ✓
  - 320/390/640/768/1024/1280px: không tràn ngang ✓; không `pageerror` ✓

  `DG_VERSION` → `1.16.23`. Không commit git.

- `2026-11-07k`: Our Ingredients — **bỏ lớp overlay tối của ảnh section Traceable**, khối chữ chuyển sang glass card.

  Yêu cầu: xoá lớp overlay của ảnh trong `<div class="dg-oi-traceable-bg">`.

  Vấn đề phát hiện khi đo (site live, đọc pixel ảnh `traceable.webp` 1024×570): overlay `rgba(49,48,48,0.6)` + `mix-blend-mode: multiply` làm tối ảnh còn ~51%, nhưng **chữ trong section lại là màu tối** — vì rule `color: var(--oi-bg)` trỏ tới token đã bị xoá lúc migrate (`--oi-bg` nằm trong danh sách "OLD tokens removed") → `var()` rơi về `unset` → kế thừa màu `--color-on-surface` (#1b1c1a). Kết quả: **chữ tối trên nền ảnh tối**. Đo contrast thực tế (chụp ảnh nền thuần + median màu tại bbox từng element): headline **2.61:1**, body **1.68:1**, country **3.73:1**, footnote **3.22:1** → **fail WCAG AA** ở cả 390px lẫn 1280px.

  Sửa:
  - `template-parts/our-ingredients/section-traceable.php` — xoá hẳn `<div class="dg-oi-traceable-overlay" aria-hidden="true"></div>` (không còn element rỗng trong DOM).
  - `assets/css/our-ingredients.css` — xoá rule `.dg-oi-traceable-overlay`; `.dg-oi-traceable-content` nay là **glass card**: nền `var(--oi-traceable-card-bg)` + `backdrop-filter: blur(18px)` (fallback nền đặc khi trình duyệt không hỗ trợ, theo pattern `@supports` sẵn có của `contact.css`), border + radius + shadow; padding `clamp(1.5rem, 4vw, 2.75rem)`.
  - `assets/css/our-ingredients.css` — sửa lỗi token chết: `color: var(--oi-bg)` → `var(--oi-traceable-text)` (headline, country); body → `var(--oi-traceable-text-muted)`; country border-bottom `rgba(252,249,248,0.3)` → `var(--oi-traceable-rule)`; footnote `rgba(220,217,217,0.8)` (màu sáng, vô hình trên nền sáng) → `var(--oi-traceable-footnote)`.
  - `style.css` `:root` — thêm bộ token component `--oi-traceable-*` (card bg/border/radius/shadow/blur, text, rule, footnote) theo Layer 3 component token; không hard-code màu trong file trang.

  Kiểm chứng (Playwright, site live — đo contrast bằng pixel thật: ẩn chữ → chụp nền thuần → median màu tại bbox → tính WCAG):
  - **Trước** (overlay còn): headline 2.61, body 1.68, country 3.73, footnote 3.22 → fail AA ✗
  - **Sau**: headline **14.89**, body **8.39**, country **15.89**, footnote **5.65** → đạt AA ✓ (AAA cho headline/body/country)
  - 320/390/640/768/1024/1280px: không tràn ngang (`scrollWidth == innerWidth`), card nằm trong viewport (left 20px ở mobile, 64px ở ≥768px), `backdrop-filter: blur(18px)` hoạt động ✓
  - `prefers-reduced-motion: reduce` → nội dung `opacity: 1` (hiển thị đầy đủ) ✓
  - Không `pageerror` ✓

  Ghi chú: Motion CDN bị chặn → `[data-sr]` giữ `opacity: 0` (FOUC guard của trang) → chữ tàng hình. Đây là hành vi **có sẵn từ trước** (`data-sr` + reveal animation phụ thuộc Motion), không phát sinh từ thay đổi này — chưa sửa vì ngoài phạm vi yêu cầu.

  `DG_VERSION` → `1.16.22`. Không commit git.

- `2026-11-07j`: Single product — **thoát lightbox thì ảnh bên ngoài giữ nguyên ảnh vừa xem** (parity với Gift Cards).

  Yêu cầu: lightbox đang ở ảnh 3 → thoát ra thì ảnh chính `#dg-main-image` + thumbnail active phải là ảnh 3, không phải ảnh trước đó.

  Nguyên nhân: `onChange` của lib được gọi ở **mọi** lần render, kể cả lúc mở → controller không phân biệt "mở lightbox" (phản ánh state hiện tại) với "user điều hướng" (state phải đổi theo).

  Sửa — lib phân biệt 2 loại render:
  - `assets/js/lib/zoom-lightbox.js` — `render(notify)`: `onChange` chỉ chạy khi `notify === true` (prev/next). `open()` gọi `render(false)` → mở lightbox không đổi state trang. `next()`/`prev()` gọi `render(true)` → user điều hướng thì trang phải theo. Cập nhật JSDoc của `onChange`.
  - `assets/js/product.js` — `items[]` giữ thêm tham chiếu `thumb` (element thumbnail tương ứng); thêm `onChange(index, item)` → gọi lại `dgChangeImage(item.thumb)` để tái dùng đúng hành vi cache-aware (fade khi ảnh chưa load) + cập nhật trạng thái active của thumbnail. Guard: bỏ qua nếu ảnh ngoài đã đúng ảnh này.
  - `assets/js/product.js` — `resolveCurrentIndex()` ưu tiên **thumbnail active** (state hiển thị của trang) thay vì so sánh `src` của ảnh chính (`src` có thể chưa kịp cập nhật nếu ảnh chưa load xong); `src` chỉ là fallback.
  - `assets/js/gift-cards-zoom.js` — `resolveCurrentIndex()` ưu tiên **`window.DGGiftValue.get()`** (state mệnh giá mà form đang gửi) thay vì đọc `src` ảnh preview; `src` chỉ là fallback.

  Kiểm chứng (Playwright, site live):
  - Mở (ảnh 1) → `next` → `next` → ảnh 3: ảnh ngoài `mainSrc` + thumbnail active đều = `shot3` ✓
  - `Escape` → ảnh ngoài **vẫn `shot3`** (không revert về ảnh 1) ✓
  - Mở lại → counter `3 / 4` (không reset về ảnh đầu) ✓
  - Phím `←` → ảnh ngoài = `shot2` ✓; wrap-around → `shot4` ✓; thoát → giữ `shot4` ✓
  - Không `pageerror` ✓
  - Hồi quy Gift Cards: mở → `2 / 4`; `next` → preview/chip/hidden đồng bộ `$250`; đóng giữ state; mở lại `3 / 4` ✓ (390/1280px)
  - Hồi quy a11y: focus trap 5× Tab trong modal ✓; reduced-motion ✓; `role`/`aria-modal`/`aria-live` ✓

  `DG_VERSION` → `1.16.21`. Không commit git.

- `2026-11-07i`: Lightbox — **bộ đếm ảnh không còn dính sát đáy ảnh** (chuyển từ overlay sang flow layout).

  Vấn đề: `.dg-zoom-modal__counter` là `position: absolute; bottom: 1rem` → **phủ lên vùng ảnh**. Khi ảnh cao (chiếm gần hết chiều cao khả dụng) khoảng cách thực tế chỉ còn ~14px → bộ đếm dính sát đáy ảnh, trông như bị dính vào ảnh.

  Sửa — chuyển sang **flow layout** (chuẩn enterprise: khoảng cách xác định bằng layout, không phụ thuộc vị trí tuyệt đối):
  - `.dg-zoom-modal` → `display: flex; flex-direction: column; gap: var(--zoom-counter-gap)` + `padding`. Ảnh (`.dg-zoom-modal__stage`) `flex: 1 1 auto; min-height: 0`, bộ đếm `flex: 0 0 auto` → bộ đếm **nằm trong flow**, khoảng cách với đáy ảnh luôn = `gap`, không bao giờ dính ảnh.
  - `.dg-zoom-modal__counter` bỏ `position: absolute` + `transform: translateX(-50%)`; thêm `font-variant-numeric: tabular-nums` (số không nhảy khi đổi ảnh) + `white-space: nowrap`.
  - Mobile ≤639px: nút chuyển ảnh không thể đặt ở giữa 2 bên (đè lên ảnh) → chuyển modal sang **CSS grid 2 hàng**: hàng 1 = ảnh, hàng 2 = `[prev] [counter] [next]`, `align-content: center` → khoảng trống dư chia đều trên/dưới (trước dồn hết xuống dưới). Nút nav `position: static` + `grid-column` → không còn overlay lên bộ đếm.
  - Token mới `--zoom-counter-gap: 1.25rem`; xoá `--zoom-stage-max-height-offset` (không còn dùng).

  Kiểm chứng (Playwright, site live — đo `getBoundingClientRect`):
  - Khoảng cách đáy ảnh ↔ counter: **20px** ở viewport thấp (1280×560, 1280×600, 1440×700, 768×500) và 29px ở mobile 390px; 88–246px ở viewport cao → trước là 14px ✓
  - Không tràn: `counter.bottom` và `img.bottom` luôn ≤ viewport height ✓
  - Mobile 390×844: cân đối — trống 269px trên / 278px dưới (trước dồn hết xuống dưới) ✓
  - Hồi quy: click ảnh → mở (counter `1 / 4`), `→` → `2 / 4`, đổi thumbnail → `3 / 4` ✓; Gift Cards (đồng bộ mệnh giá, đóng giữ state, mở lại đúng index) ✓ 390/1280px

  `DG_VERSION` → `1.16.20`. Không commit git.

- `2026-11-07h`: Single product — **click vào ảnh chính `#dg-main-image` mở lightbox** + **tách lightbox thành lib dùng chung** (single product + gift cards).

  Yêu cầu: click vào ảnh trong `<div class="flex-grow rounded-3xl overflow-hidden relative group glass-card">` mở lightbox xem ảnh đầy đủ — trước đó chỉ nút zoom `.dg-zoom-btn` mở được.

  Vấn đề kiến trúc phát hiện khi implement: có **2 bản lightbox trùng lặp** — `#dg-zoom-modal` (trang single product, inline trong `content-single-product.php`, JS legacy `window.dgOpenZoom/dgCloseZoom/dgZoomPrev/dgZoomNext` với `setTimeout` lồng nhau) và `.dg-gift-zoom-modal` (trang gift cards, 1 file JS riêng). Cùng 1 UX nhưng 2 markup + 2 CSS + 2 controller → sửa 1 bên là lệch bên kia.

  Kiến trúc mới — 1 lib + 1 partial + 1 CSS dùng chung:
  - `assets/js/lib/zoom-lightbox.js` (mới) — classic script, không phụ thuộc gì → `window.DGZoomLightbox.create({ root, items, scope, resolveIndex, onChange })`. Controller **không gắn listener trực tiếp vào trigger** mà dùng **event delegation** trên `document` (`[data-dg-zoom-trigger]`) → trigger có thể được JS di chuyển (preview thẻ quà bị relocate trên mobile/tablet) vẫn hoạt động. Xử lý: `open/close/next/prev`, focus trap (Tab), `Escape`/`←`/`→`, restore focus, lock scroll body, `aria-hidden` trên root.
  - `template-parts/global/zoom-modal.php` (mới) — markup dùng chung, gắn qua data attribute (`[data-dg-zoom-root|image|counter|prev|next|close]`). Phải include **sau `</main>`** vì `position: fixed` bị neo sai bởi `transform` của animation reveal.
  - `assets/css/zoom-lightbox.css` (mới) — BEM `.dg-zoom-modal__*`, không phụ thuộc Tailwind runtime cho trạng thái mở/đóng (`display:none` + `.is-open`) → Tailwind CDN chặn vẫn chạy. Mobile ≤639px: nút chuyển ảnh xuống đáy (ảnh chiếm gần hết bề ngang).
  - Token `--zoom-*` trong `style.css` `:root`; xoá token `--gift-zoom-*` cũ.

  Sửa:
  - `woocommerce/content-single-product.php` — ảnh chính `#dg-main-image` nay là trigger: `role="button"` + `tabindex="0"` + `aria-label="Zoom image"` + `data-dg-zoom-trigger`; nút `.dg-zoom-btn` bỏ `onclick="dgOpenZoom()"` → `data-dg-zoom-trigger`; **xoá hẳn** markup modal inline `#dg-zoom-modal`; thumbnail bỏ `onclick="dgChangeImage(this)"` → `data-dg-thumb`.
  - `woocommerce/single-product.php` — include `template-parts/global/zoom-modal.php` sau `</main>`.
  - `assets/js/product.js` — **xoá hẳn** block zoom legacy (175 dòng `dgZoomState` + `dgOpenZoom`/`dgZoomPrev`/`dgZoomNext`/`dgUpdateZoomImage`/`dgCloseZoom` + listener phím) → `initZoomLightbox()` dùng lib chung; gallery lấy từ `[data-dg-thumb]`; `resolveIndex()` đọc `src` hiện tại của `#dg-main-image` → mở đúng ảnh user đang xem, đồng bộ bộ đếm & prev/next. `dgChangeImage` chuyển từ `window.dgChangeImage` (inline handler) sang listener delegate.
  - `assets/js/gift-cards-zoom.js` — viết lại dùng lib chung; giữ nguyên hành vi đồng bộ mệnh giá (`onChange` → `window.DGGiftValue.set(item.amount)`).
  - `template-parts/gift-cards/section-bento.php` — ảnh + nút zoom bỏ `onclick="dgOpenZoom()"` → `data-dg-zoom-trigger`.
  - `template-parts/gift-cards/section-zoom-modal.php` — **xoá** (thay bằng partial dùng chung); `page-templates/template-gift-cards.php` include partial mới.
  - `assets/css/single-product.css` — xoá block `#dg-zoom-modal` (chuyển sang lib CSS), giữ rule `#dg-main-image` (cursor + focus ring).
  - `assets/css/gift-cards.css` — xoá block `.dg-gift-zoom-modal*` (~210 dòng), giữ rule trigger riêng của trang.
  - `inc/enqueue/styles.php` — enqueue `dg-zoom-lightbox` (global, vì markup render ở nhiều template).
  - `inc/enqueue/scripts.php` — enqueue `dg-zoom-lightbox`; `dg-product` + `dg-gift-cards-zoom` khai báo dependency.

  Kiểm chứng (Playwright, site live `https://dragonglow.freehosting.dev/`):
  - Click `#dg-main-image` → modal mở (`is-open`, `display: flex`), `aria-hidden=false`, ảnh `shot1.jpg`, counter `1 / 4`, `body` overflow hidden, focus vào nút đóng ✓
  - Click nút `.dg-zoom-btn` → mở ✓; `→` → counter `2 / 4` + ảnh `shot2.jpg` ✓; `Escape` → đóng + overflow reset ✓
  - **Focus restore**: click ảnh (img không focus được khi click) → đóng → focus trả về `#dg-main-image` ✓; click nút zoom → focus trả về nút ✓
  - **Focus trap**: 5 lần `Tab` → focus luôn nằm trong modal ✓ (`prev` → `next` → `close` → `prev` → `next`)
  - **Mở đúng ảnh đang xem**: đổi thumbnail sang `shot3` → mở → counter `3 / 4`, ảnh `shot3` ≡ `#dg-main-image` ✓
  - **Keyboard activation**: focus `#dg-main-image` + `Enter` → mở ✓
  - **A11y**: `role=dialog`, `aria-modal=true`, `aria-hidden` đồng bộ, counter `aria-live="polite"`, `img alt` = "Browluxe Define Brow Mascara shot 1" ✓
  - **Reduced motion**: `transition-duration: 0s`, `transform: none`, ảnh vẫn hiện ✓
  - **Responsive**: 390px → nav `48×48` ở đáy (y=836), 768/1280px → nav giữa chiều cao 2 bên ✓
  - **Hồi quy Gift Cards**: mở từ ảnh thẻ → counter `2 / 4`; `next` → `card-$250` + chip `$250` + hidden `250`; `Escape` → state giữ `$250`; mở lại → `3 / 4` ✓ (390px + 1280px, không `pageerror`)
  - Không còn `onclick` inline nào chứa "zoom" ✓

  `DG_VERSION` → `1.16.18`. Không commit git.

- `2026-11-07g`: Gift Cards — **chuyển ảnh trong lightbox → đồng bộ toàn bộ state mệnh giá bên ngoài** (ảnh preview + chip active + hidden input form + nhãn giá nút "Add to Bag").

  Yêu cầu: khi thoát lightbox, ảnh preview bên ngoài phải khớp với ảnh cuối cùng user xem trong lightbox (lightbox ở ảnh 1 → thoát ra ảnh ngoài cũng là ảnh 1).

  Vấn đề phát hiện khi implement: nếu chỉ đổi `src` ảnh preview thì **form vẫn giữ mệnh giá cũ** → user nhìn thấy ảnh `$250` nhưng bấm "Add to Bag" lại gửi `$100` = đơn hàng sai. Nên phải đồng bộ **toàn bộ** state mệnh giá, không chỉ ảnh.

  Kiến trúc — tách state ra module chung:
  - `assets/js/lib/gift-value.js` (mới) — classic script, **không phụ thuộc gì**, sở hữu DUY NHẤT state mệnh giá: ảnh preview `.dg-gift-card-image` + chip `[data-amount]` (`is-active` / `aria-checked`) + hidden `input[name="value"]` (giá trị gửi form) + nhãn `.dg-gift-submit-value`. API: `window.DGGiftValue.get()` / `.set(amount)`.
  - Vì sao tách module: `assets/js/gift-cards.js` là **ES module** (import Motion từ CDN) → nếu CDN chặn/chậm thì cả module bị skip; `assets/js/gift-cards-zoom.js` là classic script độc lập (lightbox phải sống tối thiểu) → không import được ES module. Module classic độc lập là điểm giao duy nhất hai script dùng chung được.
  - `assets/js/gift-cards.js` — **xoá hẳn** `initValueChips()` + `swapCardImage()` (logic trùng lặp). Chip mệnh giá nay do `gift-value.js` gắn listener → **chip vẫn hoạt động kể cả khi ES module bị skip** (trước đây chip chết theo Motion CDN).
  - `assets/js/gift-cards-zoom.js` — `render()` gọi `window.DGGiftValue.set(item.amount)` sau khi đổi ảnh → đồng bộ **live** (ngay khi chuyển ảnh, không chờ đóng lightbox).
  - `inc/enqueue/scripts.php` — enqueue handle mới `dg-gift-value`; `dg-gift-cards-zoom` khai báo dependency `dg-gift-value` để luôn load trước.

  Kiểm chứng (Playwright, mock `tmp-resp/gift-zoom/sync-mock.html` nạp JS thật của theme + site live `tmp-resp/gift-zoom/sync-live.js`):
  - Chip `$250` → ảnh preview `card-250` + chip active `250` + hidden `250` + nhãn `$250` ✓
  - Mở lightbox → counter `3 / 4`, ảnh `card-250` (đúng mệnh giá hiện tại) ✓
  - `next` → `$500`: preview/chip/hidden/nhãn đều `500`, counter `4 / 4` ✓
  - `Escape` đóng → state bên ngoài giữ nguyên `$500` (không revert) ✓
  - Mở lại → index đúng `500` (không reset về ảnh đầu) ✓; `prev` → `$250` đồng bộ ✓
  - **Regression**: mở rồi đóng ngay (không chuyển ảnh) → state giữ nguyên `$100`, không bị đổi ✓
  - Chip vẫn hoạt động sau khi lightbox đã dùng ✓; đóng bằng click backdrop ✓; không `pageerror` ✓
  - **Site live** `https://dragonglow.freehosting.dev/gift-cards/`: mở → counter `2 / 4`; `next` → preview `card-$250` + chip `250` + hidden `250` + nhãn `$250`; `Escape` → state giữ `$250`; mở lại → counter `3 / 4` ✓
  - **Motion CDN bị chặn** (`tmp-resp/gift-zoom/sync-cdn-blocked.js`, abort mọi request jsDelivr): ES module `gift-cards.js` bị skip → `window.DGGiftValue` vẫn tồn tại, chip `$250` vẫn đổi ảnh + hidden + nhãn, lightbox vẫn mở + chuyển ảnh + đồng bộ `$500` ✓, không `pageerror` ✓
  - **Thứ tự thực thi**: `gift-value.js` (classic, footer) chạy trước `gift-cards.js` (ES module → deferred) → `DGGiftValue` luôn sẵn sàng ✓; load order trên live: `gift-cards.js` → `gift-cards-card-preview.js` → `gift-value.js` → `gift-cards-zoom.js` ✓

  `DG_VERSION` → `1.16.17`. Không commit git.

- `2026-11-07f`: Gift Cards — **click vào ảnh thẻ mở lightbox + thêm nút chuyển ảnh (prev/next) + bộ đếm `1 / 4`** trong lightbox (parity với lightbox trang single product).

  Yêu cầu: click trực tiếp vào ảnh `.dg-gift-card-image` trong `.dg-gift-card-preview` cũng mở lightbox; lightbox có nút chuyển ảnh giống `#dg-zoom-next` của trang single product, cycle qua 4 mệnh giá ($50 / $100 / $250 / $500).

  Sửa:
  - `template-parts/gift-cards/section-bento.php` — ảnh `.dg-gift-card-image` nay là trigger: thêm `role="button"` + `tabindex="0"` + `aria-label="Zoom gift card image"` (WCAG — ảnh không focus được mặc định).
  - `template-parts/gift-cards/section-zoom-modal.php` — thêm 2 nút `__nav--prev` / `__nav--next` (`chevron_left` / `chevron_right`) + khối bộ đếm `__counter` (`#dg-gift-zoom-counter`, `aria-live="polite"`).
  - `assets/js/gift-cards-zoom.js` — dựng gallery từ `data-card-{amount}` trên `.dg-gift-card-preview` (duyệt `preview.attributes` → đúng thứ tự PHP render 50 → 100 → 250 → 500); `resolveInitialIndex()` suy ra index từ `src` hiện tại của `.dg-gift-card-image` (đọc `getAttribute('src')` vì `gift-cards.js` set `img.src` khi đổi mệnh giá, `currentSrc` có thể chưa cập nhật); `goTo()` wrap-around 2 chiều; `render()` cập nhật `src` + bộ đếm + ẩn nút/bộ đếm khi gallery chỉ có 1 ảnh; click `.dg-gift-card-image` → `open()`; `Enter`/`Space` → `open()` (keyboard activation); phím `ArrowLeft`/`ArrowRight` chuyển ảnh, `Escape` đóng (chỉ khi modal đang mở).
  - `assets/css/gift-cards.css` — style `__nav` (tròn 48×48, `top: 50%`, `margin-top: -1.5rem`, hover scale 1.05) + `__counter` (căn giữa đáy) dùng token `--gift-zoom-close-bg` / `--gift-zoom-close-ink`; `[hidden]` phải khai báo lại vì rule `display` đè lên UA `[hidden]{display:none}`; `cursor: zoom-in` cho `.dg-gift-card-image`; `:focus-visible` cho ảnh + nút nav; block `@media (max-width: 639px)` chuyển nút nav xuống đáy modal (ảnh mobile chiếm gần hết bề ngang → nút giữa cạnh sẽ đè lên ảnh); `__stage` giảm `max-height` còn `calc(90vh - 4rem)` để chừa chỗ cho bộ đếm; block `prefers-reduced-motion` riêng (modal nằm ngoài scope `.dg-gift`).

  Kiểm chứng (Playwright, mock `tmp-resp/gift-zoom/gallery-mock.html` nạp CSS/JS thật của theme):
  - Click ảnh → modal `display: flex`, `is-open`, `aria-hidden: false`, `body` overflow `hidden` ✓
  - `next` ×3: `$100`→`$250`→`$500`→`$50` (wrap), bộ đếm `2/4`→`3/4`→`4/4`→`1/4` ✓
  - `prev` từ `$50` → `$500` (wrap ngược) ✓; `ArrowRight` chuyển ảnh ✓; `Escape` đóng + reset overflow ✓
  - Nút zoom cũ vẫn mở modal, giữ đúng index theo mệnh giá hiện tại ✓; không có `pageerror` ✓
  - Keyboard: focus ảnh → `Enter` mở modal, focus nhảy vào nút close ✓
  - 390 / 768 / 1280px → nút nav `48×48`, `z-index: 2`; mobile: nav ở đáy (`y: 836`), ảnh `358×269` không bị đè ✓

  `DG_VERSION` → `1.16.16`. Không commit git.

- `2026-11-07e`: Gift Cards — **thêm nút zoom (`.dg-zoom-btn`) trên ảnh preview thẻ quà + lightbox xem ảnh đầy đủ**.

  Yêu cầu: thêm nút zoom vào `<div class="dg-gift-card-preview">` (markup cho sẵn từ single product).

  Vấn đề: trang Gift Cards là **page template**, không phải product → `assets/js/product.js` (chứa `dgOpenZoom()` + markup `#dg-zoom-modal`) **không được enqueue** ở đây. Nếu chỉ chèn nút theo markup thì `onclick="dgOpenZoom()"` sẽ ném `ReferenceError` → nút chết.

  Sửa:
  - `template-parts/gift-cards/section-bento.php` — thêm nút zoom vào `.dg-gift-card-preview` (đặt sau `<img>`, trước 2 tag), markup mirror `woocommerce/content-single-product.php`.
  - `template-parts/gift-cards/section-zoom-modal.php` (mới) — markup lightbox, đặt **ngoài `<main class="dg-gift">`** để `position: fixed` không bị neo sai bởi transform của animation reveal.
  - `assets/js/gift-cards-zoom.js` (mới) — classic script độc lập (không phụ thuộc Motion, giống `gift-cards-card-preview.js`): mở/đóng modal, đọc `src` ảnh đang chọn theo mệnh giá hiện tại, focus trap vào nút close, restore focus khi đóng, Escape đóng, lock scroll body. Khai báo `window.dgOpenZoom` (guard: bỏ qua nếu trang đã có `#dg-zoom-modal` của product.js).
  - `assets/css/gift-cards.css` — style lightbox (`.dg-gift-zoom-modal`, `__close`, `__stage`, `__image`) + `:focus-visible` cho nút zoom; block `prefers-reduced-motion` riêng vì modal nằm ngoài scope `.dg-gift`.
  - `style.css` — token mới `--gift-zoom-backdrop`, `--gift-zoom-close-bg`, `--gift-zoom-close-bg-hover`, `--gift-zoom-close-ink`.
  - `inc/enqueue/scripts.php` — enqueue `dg-gift-cards-zoom` trong block Gift Cards.
  - `page-templates/template-gift-cards.php` — include partial lightbox sau `</main>`.

  Kiểm chứng (Playwright `tmp-resp/gift-zoom/verify.js` + `measure.js`, mock HTML `tmp-resp/gift-zoom/mock.html` nạp CSS/JS thật của theme):
  - 390 / 768 / 1280px → nút `visible: true`, `position: absolute`, `top/right: 16px`, `z-index: 10`, kích thước `40×40` ✓
  - Click → modal `display: flex`, `is-open: true`, `aria-hidden: false`, ảnh zoom `src` = ảnh preview hiện tại, `opacity: 1` ✓
  - `body` overflow = `hidden` khi mở, reset về `''` khi đóng ✓
  - Focus vào nút close khi mở ✓
  - Escape → modal `display: none`, `is-open: false` ✓
  - Ảnh 1200×900 → render `1080×810` (đúng tỉ lệ, `object-fit: contain`, không méo) ✓

  `DG_VERSION` → `1.16.15`. Không commit git.

- `2026-11-07d`: Wishlist — **ô chọn (`.dg-wishlist-card__select`) luôn hiện ở mobile + tablet**, không cần hover (desktop giữ hover-reveal).

  Yêu cầu: label `.dg-wishlist-card__select` hiển thị sẵn ở mobile và tablet; desktop giữ nguyên.

  Đo trạng thái thật trước khi sửa (Playwright, mock HTML `tmp-resp/wl-select/card.html` nạp CSS live):
  - ≤767px — `opacity: 1` (đã hiện sẵn, do rule cũ trong block `@media (max-width: 767px)`).
  - **768–1023px (tablet) — `opacity: 0.0004`, `translateY(-4px)` → ẨN, chỉ hiện khi hover.** ← gap thật của yêu cầu.
  - ≥1024px — ẩn, hover mới hiện (giữ nguyên).

  Sửa (`assets/css/wishlist.css`):
  - Thêm rule mới ngay sau block `:hover` của `.dg-wishlist-card__select`: `@media (max-width: 1023px) { .dg-wishlist-card__select { opacity: 1; transform: translateY(0); } }` → phủ mobile + tablet.
  - Xoá rule trùng lặp cũ trong block `@media (max-width: 767px)` (§ 12 RESPONSIVE TUNING) — nay đã bao phủ bởi rule mới, tránh hai nguồn sự thật.

  Về thứ tự specificity: rule mới (0,1,0) nằm SAU rule `:hover` (0,3,0) → khi chuột đi qua card ở mobile/tablet, trạng thái hover vẫn ra `opacity: 1` / `translateY(0)` — **trùng khớp với trạng thái nghỉ** → không nhấp nháy khi rê chuột.

  Về JS: `assets/js/wishlist.js` không có handler `mouseenter`/`mouseleave`/`mouseover` nào (chỉ xử lý `click`) → `:hover` là pseudo-class của CSS nên xử lý bằng CSS, không sửa JS.

  Kiểm chứng (Playwright `tmp-resp/wl-select/measure.js`, đo `getComputedStyle` với CSS live):
  - 390 / 640 / 767 / 768 / 1023px → `opacity: 1`, `transform: translateY(0)` khi **không hover** ✓
  - 1024 / 1280px → vẫn `opacity: ~0` khi nghỉ, `1` khi hover ✓ (không regression)

  `DG_VERSION` → `1.16.14`. Không commit git.

- `2026-11-07c`: Shop — **hover nút quick-add ở trạng thái "Added" (desktop) giữ nguyên màu nền như lúc chưa hover**.

  Yêu cầu: ở desktop, hover vào nút `.dg-add-to-cart.dg-quick-add--added` phải giữ màu background giống trạng thái nghỉ.

  Vấn đề: rule `.dg-add-to-cart.dg-quick-add--added:hover` dùng `--color-primary-container` = `#f4c2c2` (**hồng pale pink**) + `--color-on-primary-container` = `#554300`. Trong khi trạng thái nghỉ dùng `--color-tertiary-container` = `#f1ca50` (**vàng brand**) + `--color-on-tertiary-container` = `#6b5500`. Nên hover lệch hẳn sang hồng → nút trông như component khác, vỡ bảng màu.

  Sửa (`assets/css/shop.css`): đổi 2 token trong rule hover sang đúng bộ token của trạng thái nghỉ (`--color-tertiary-container` / `--color-on-tertiary-container`), giữ nguyên `transform: translateY(-2px)` để nút vẫn "có phản hồi" khi hover.

  Kiểm chứng (Playwright `tmp-resp/quickadd-hover/verify-desktop-added.js`, đo `getComputedStyle` trên site live):
  - 1024px / 1280px, trạng thái "Added": bg `rgb(241,202,80)` ≡ `rgb(241,202,80)`, color `rgb(113,85,9)` ≡ `rgb(113,85,9)`, icon color `rgb(115,92,0)` ≡ `rgb(115,92,0)` ✓ — chỉ còn `transform` đổi `0,0` → `0,-2` (nhấc nút 2px) ✓
  - 390px / 768px — không đổi (đã bỏ hover từ `2026-11-07b`) ✓
  - 1024px / 1280px — hover-reveal + sheen sweep của card giữ nguyên ✓

  `DG_VERSION` → `1.16.13`. Không commit git.

- `2026-11-07b`: Shop — **bỏ hover của nút quick-add ở mobile + tablet** (desktop giữ nguyên).

  Yêu cầu: `.dg-add-to-cart.dg-quick-add` không còn phản ứng hover ở kích thước mobile và tablet; desktop giữ nguyên.

  Đo lại trạng thái hover trước khi sửa (Playwright `tmp-resp/quickadd-hover/measure-added.js`):
  - 390px / 768px — hover đổi nền `rgb(241,202,80)` → `rgb(244,194,194)` (hồng `--color-primary-container`) + nhấc nút `translateY(-2px)`.
  - Vấn đề thực tế: touch device **giữ `:hover` dính** sau khi tap → nút kẹt ở màu hồng + bị nhấc lên 2px cho tới lần chạm kế tiếp. Đây chính là "hover state bị dính" trên mobile.
  - Desktop (>= 1024px): hover là **hover-reveal** — nút `opacity: 0` → `1` khi hover card, kèm sheen sweep (`::before`). Phải giữ nguyên.

  Sửa (`assets/css/shop.css`):
  - Thêm block mới `@media (max-width: 1023px)` ở cuối file: khai báo lại nền / màu chữ / transform gốc cho `.dg-add-to-cart:hover` và `.dg-add-to-cart.dg-quick-add--added:hover` → hover không còn thay đổi gì. Kèm `.dg-product-card:hover .dg-add-to-cart::before { left: 160%; transition: none; }` để sheen không chạy theo hover card (tránh vệt sáng đọng lại sau tap).
  - Xoá 2 rule hover cũ trong block media `< 1024px` đã tồn tại từ trước (`transform: translateY(-2px)`) — chúng **cố tình** giữ hiệu ứng nhấc nút, nay trái với yêu cầu mới.

  Về JS: grep toàn bộ `assets/js/` — **không có** handler `mouseenter`/`mouseleave`/`mouseover`/`mouseout` nào cho `.dg-add-to-cart` hay `.dg-quick-add` (chỉ `click` trong `quick-add-to-cart.js`). `:hover` là pseudo-class của CSS, không có class để gỡ bằng script → xử lý đúng là bằng CSS, không cần sửa JS.

  Kiểm chứng (Playwright `tmp-resp/quickadd-hover/verify.js`, đo `getComputedStyle` trên site live):
  - 390px / 768px — rest ≡ hover, cả trạng thái thường lẫn "Added": bg `rgb(241,202,80)`, color `rgb(113,85,9)`, transform `matrix(1,0,0,1,0,0)`, sheen transition `0s` ✓
  - 1024px / 1280px — hover-reveal + sheen + màu hồng trạng thái "Added" **giữ nguyên** ✓

  `DG_VERSION` → `1.16.12`. Không commit git.

- `2026-11-07a`: Single product — **giảm khoảng cách trên của section `#product-tabs` ở mobile** (tablet/desktop giữ nguyên).

  Vấn đề: section dùng class Tailwind `mt-section-gap` → `margin-top: 120px` ở **mọi** breakpoint. Ở mobile (320–767px) khoảng trống này quá lớn, nội dung tab bị đẩy xuống khá xa so với khối thông tin sản phẩm phía trên.

  Sửa:
  - `style.css` (`:root`) — thêm token `--product-tabs-gap-mobile: 48px` (nhóm token single-product hiện có).
  - `assets/css/product.css` — thêm block `@media (max-width: 767px) { #product-tabs { margin-top: var(--product-tabs-gap-mobile); } }`.
  - `functions.php` — `DG_VERSION` → `1.16.11`.

  Ghi chú kỹ thuật (đã kiểm chứng bằng CDP): Tailwind CDN **inject `<style>` runtime vào `<head>`** → nằm **sau** các file CSS của theme. Nếu dùng class thường (specificity 0,1,0) thì utility `.mt-section-gap` của Tailwind sẽ thắng do đứng sau. Vì vậy dùng **ID selector** `#product-tabs` (1,0,0) — cùng pattern đã có trong file này (`#dg-main-image`, `#sticky-add-to-bag`) → luôn thắng bất kể thứ tự nạp.

  Breakpoint `767px` chọn khớp với logic mobile đã có trong `assets/js/product.js` (`window.innerWidth < 768` → coi là mobile, scroll tới tabs). Ở 640–767px section trước đó bị "chật" hơn desktop → nay đồng nhất với mobile.

  Kiểm chứng (Playwright `tmp-resp/product-tabs-gap/check.js`, đo `getComputedStyle().marginTop` trên site live):
  - 320 / 390 / 430 / 639 / 640 / 767px → `48px` ✓
  - 768 / 1023 / 1024 / 1280px → `120px` (không đổi) ✓

  Không commit git.

- `2026-11-06i`: Header — **fix bug text "Menu" thừa hiện ra ở desktop** (trong `div.dg-nav-actions`).

  **Root cause:** `$nav_aria = esc_attr_e( 'Menu', 'dragon-glow' );` — dùng nhầm biến thể `_e` (echo) thay vì `__` (return). `esc_attr_e()` **echo** ra output nên chữ "Menu" bị in thành text node ngay trong markup (hiện ra ở desktop vì nút toggle bị ẩn bởi `lg:hidden` nhưng text node thì không), đồng thời `$nav_aria` thành `null` → `aria-label=""` rỗng.

  Fix: đổi sang `esc_attr__( 'Menu', 'dragon-glow' )` + comment giải thích để không lặp lại lỗi này.

  Kiểm chứng (`tmp-resp/nav-cart-badge/render-header-nav.php` — render template với WP functions stubbed):
  - Markup toggle: `<button ... aria-label="Menu, 4 items" ... data-dg-label="Menu">` ✓
  - Không còn text node "Menu" trong button / trong `.dg-nav-actions` ✓
  - `aria-label` không rỗng ✓

  Không đổi `DG_VERSION` (không có thay đổi asset). Không commit git.

- `2026-11-06h`: Header — **badge wishlist trên menu item "Wishlist" trong drawer mobile/tablet** + **badge TỔNG (giỏ hàng + wishlist) trên nút menu mobile/tablet**.

  Yêu cầu: click `.dg-wishlist-toggle` → badge wishlist phải hiện trên nút menu mobile (`#dg-mobile-menu-toggle`) và trên menu item "Wishlist" trong drawer (`#dg-mobile-menu-item--wishlist`).

  **Quyết định thiết kế (có hỏi user):** nút toggle chỉ 40×40px, không đủ chỗ cho 2 pill → chọn **1 badge TỔNG** (giỏ hàng + wishlist) thay vì 2 badge chồng nhau.

  Sửa:
  - `inc/helpers/ui-components.php` — `dg_render_nav_count_badge()` (helper mới): render pill tổng với class riêng `.dg-nav-badge` (**không** dùng `.dg-cart-count`, vì `DGCartBadge.paint()` sẽ ghi đè giá trị tổng bằng count giỏ hàng). Item "Wishlist" trong drawer giờ render `dg_render_wishlist_count_badge()` + `aria-label` server-side ("Wishlist, 2 saved items"). Item "Cart" cũng bổ sung `aria-label` server-side.
  - `template-parts/global/header-nav.php` — nút toggle dùng `dg_render_nav_count_badge( $cart_count, $wishlist_count )`, `aria-label` server-side ("Menu, 4 items"). `$wishlist_count` tính 1 lần/request, tái dùng cho cả badge desktop.
  - `assets/js/lib/nav-badge.js` (module mới) — sở hữu pill `.dg-nav-badge`: giữ 2 bộ đếm (cart/wishlist), tính tổng, vẽ + animation `is-pulse`/`is-first-show`, đồng bộ `aria-label` của toggle. API: `setCart()`, `setWishlist()`, `refresh()`.
  - `assets/js/lib/cart-badge.js` — `paint()` gọi thêm `DGNavBadge.setCart(total)`; `syncAriaLabels()` bỏ phần toggle (nay do `nav-badge.js` lo).
  - `assets/js/lib/wishlist-toggle.js` — `applyBadgeCount()` / `paintBadgeCount()` gọi thêm `DGNavBadge.setWishlist(n)` + `syncWishlistAriaLabel()` (aria-label cho link "Wishlist" trong drawer).
  - `assets/js/wishlist.js` — `paintWishlistBadges()` (gộp 3 chỗ vẽ badge trùng lặp) + `syncWishlistAriaLabel()`.
  - `assets/css/header-nav.css` — §4d-1 đổi từ `.dg-cart-count` sang `.dg-nav-badge`; §4d-2 gộp badge Cart + badge Wishlist trong drawer (cùng rule `position: static; margin-left: auto`).
  - `inc/helpers/wishlist.php` — `dg_get_wishlist_count()` memoize theo request (bị đọc 3 lần/render), cache bị invalidate trong `dg_set_wishlist()`; `dg_render_wishlist_count_badge()` nhận thêm param `$count` để không gọi lại lookup.
  - `inc/enqueue/scripts.php` — enqueue `dg-nav-badge` + thêm i18n `navItemSingular/navItemPlural/savedSingular/savedPlural`.

  Kiểm chứng (Playwright, `tmp-resp/nav-cart-badge/nav-badge-*.js`):
  - Click `.dg-wishlist-toggle` → badge drawer "Wishlist" 1→2, badge tổng trên toggle 4→5, aria-label rebuild ở cả 2 host ✓ (320/390/768/1023/1024px)
  - `paint(0)` + `setWishlist(0)` → `display: none` (override tường minh `.hidden`) ✓
  - Kích thước: 1–2 chữ số = 20×20px, 4 chữ số = 30×20px, không tràn viewport ở 320px ✓
  - `prefers-reduced-motion: reduce` → `animation-name: none` ✓
  - Desktop 1280px: toggle `display: none`, badge heart/bag desktop giữ nguyên 20px ✓

  `DG_VERSION` → `1.16.7`. Không commit git.

- `2026-11-06g`: Header — **badge số lượng giỏ hàng trên menu item "Cart" trong drawer mobile/tablet** + **fix bug badge không ẩn khi count = 0**.

  Yêu cầu: `<li class="menu-item dg-mobile-menu-item--cart">` trong drawer (`#dg-mobile-menu`) hiện số lượng giỏ hàng, giống badge `.dg-cart-count` ở header.

  Sửa:
  - `inc/helpers/ui-components.php` — `dg_inject_mobile_menu_items()` render thêm `dg_render_cart_count_badge( dg_get_cart_item_count() )` vào sau label trong thẻ `<a>` của item Cart. Tái dùng helper sẵn có → markup/class giống hệt 2 badge còn lại, nên `DGCartBadge.paint()` cập nhật cả 3 cùng lúc.
    - Thẻ `<a>` có `data-dg-label="Cart"` → JS dựng lại `aria-label` ("Cart, 3 items in bag") mỗi lần count đổi.
  - `assets/css/header-nav.css` (block mới §4d-2) — badge trong drawer nằm **trong flow** (không absolute như badge trên toggle): `position: static` + `margin-left: auto` → pill đẩy sát mép phải của row, đọc như cột số lượng. `min-width: 20px; padding: 0 4px` → số 2–3 chữ số tự giãn pill. Animation `is-pulse` / `is-first-show` giống 2 badge kia.
  - `assets/js/lib/cart-badge.js` — `syncToggleAriaLabel()` → `syncAriaLabels()`: đồng bộ `aria-label` cho **cả** toggle và drawer Cart item.

  **BUG FIX (phát hiện khi verify):** rule `.dg-nav-toggle .dg-cart-count { display: inline-flex }` có specificity (0,2,0) **thắng** class `hidden` của Tailwind (0,1,0) → badge **vẫn hiện khi giỏ rỗng**. Đã thêm override tường minh `.dg-nav-toggle .dg-cart-count.hidden { display: none }` (và tương tự cho badge trong drawer). Đây là bug do chính thay đổi ở entry `2026-11-06f` gây ra.

  Kiểm chứng (Playwright, `tmp-resp/nav-cart-badge-drawer*.js`, `nav-cart-badge-hide-check.js`):
  - 320 / 390 / 768 / 1023px → badge trong drawer: `position=static`, 20×20px, cách mép phải row 21px (đúng padding), không đè glyph ✓
  - `paint(7)` → `is-pulse`; `paint(0)` → `display: none`; `paint(2)` → `is-first-show`; `paint(1)` → aria "Cart, 1 item in bag"; `bump(+3)` → `4` ✓
  - Pixel: fill `#735c00` + chữ trắng, badge nằm trong row ✓
  - `prefers-reduced-motion: reduce` → `animation-name: none` ✓
  - Regression toggle badge (entry `2026-11-06f`): 6 breakpoint vẫn ✓, `paint(0)` giờ ẩn đúng ✓

  `DG_VERSION` → `1.16.6`. Không commit git.

- `2026-11-06f`: Header — **badge số lượng giỏ hàng trên nút menu mobile/tablet** (`#dg-mobile-menu-toggle`), có animation pop mượt.

  Vấn đề gốc: icon giỏ hàng ở desktop nằm trong `<a class="hidden lg:block relative">` → **bị ẩn hoàn toàn dưới 1024px**. Badge `.dg-cart-count` vốn chỉ render cạnh icon đó, nên trên mobile/tablet **không có bất kỳ chỗ nào hiển thị số lượng giỏ hàng** — người dùng điện thoại phải mở drawer mới biết túi có hàng hay không.

  Sửa:
  - `template-parts/global/header-nav.php` — render `dg_render_cart_count_badge( $cart_count )` **bên trong** nút `#dg-mobile-menu-toggle` (thêm class `relative` để làm anchor). Tái sử dụng nguyên helper sẵn có → markup/class giống hệt badge desktop, không viết lại markup.
  - `assets/js/lib/cart-badge.js` (module mới) — `window.DGCartBadge.paint(count)` / `.bump(delta)`: nơi duy nhất vẽ badge + kích hoạt animation.
    - `is-pulse` → số đổi → scale pop nhẹ.
    - `is-first-show` → badge hiện lần đầu → entrance mạnh hơn.
    - Cả 2 class luôn được xoá trước khi thêm lại (force reflow) → animation restart sạch khi bấm liên tục, không để class cũ tranh cascade.
  - 6 call site cũ tự vẽ badge riêng (`lib/cart-api.js`, `lib/cart-feedback.js`, `cart.js`, `thankyou.js`, `wishlist.js`) → chuyển sang dùng `DGCartBadge` (giữ fallback cũ nếu module chưa load).
  - `assets/css/header-nav.css` (block mới §4d-1) — style + keyframes `dgCartBadgePop` / `dgCartBadgeFirstShow`, **scope dưới `.dg-nav-toggle`** → desktop badge giữ nguyên 100%, không animation (đúng yêu cầu "không làm ở desktop").
    - Badge trên toggle dùng `width: auto; min-width: 20px; padding: 0 4px` → số 2–3 chữ số tự giãn pill thay vì bị cắt (desktop vẫn giữ `w-5` cố định).
    - `box-shadow: 0 0 0 2px var(--color-surface)` → ring nền tách badge khỏi glyph menu.
  - `inc/enqueue/scripts.php` — enqueue `dg-cart-badge` + thêm vào deps của `dg-cart-api`, `dg-cart`, `dg-thankyou`.

  Kiểm chứng (Playwright, `tmp-resp/nav-cart-badge-*.js`):
  - 320 / 390 / 768 / 1023px → toggle hiện + badge hiện, `size=20x20`, offset `right=-4px top=-4px` (đúng parity với badge wishlist desktop) ✓
  - 1024 / 1280px → toggle `display=none`, badge không render → **desktop không đổi** ✓
  - `paint(7)` → text đổi + `is-pulse` bật; `paint(0)` → ẩn; `paint(2)` → `is-first-show`; `bump(+2)` → `4` ✓
  - `aria-label`: "Menu, 4 items in bag" khi count=4, "Menu" khi count=0 ✓
  - Badge 1–1234 chữ số: pill 20→30px, không cắt chữ ✓
  - `prefers-reduced-motion: reduce` → `animation-name: none` ✓
  - Pixel analysis: badge che 5.4% glyph menu (tương đương badge wishlist) ✓

  A11y:
  - Nút toggle có `data-dg-label` (label gốc) → `cart-badge.js` đồng bộ `aria-label` mỗi lần count đổi: "Menu, 4 items in bag" / về "Menu" khi rỗng. Badge **không** bị `aria-hidden` — theo accname spec, `aria-label` của host thắng descendant text nên số không bị đọc 2 lần.
  - i18n mới trong `dgAjax.i18n`: `itemSingular` ("1 item in bag") + `itemPlural` ("%d items in bag").

  `DG_VERSION` → `1.16.5`. Không commit git.

- `2026-11-06e`: Shop — **nút "Add to Cart" luôn hiển thị ở mobile + tablet**, không cần hover.

  Vấn đề gốc: `.dg-add-to-cart` mặc định `opacity: 0` + `transform: translateY(16px)` (ẩn), chỉ hiện khi `.dg-product-card:hover` — thiết kế cho chuột.
  - Touch device không có hover → nút không bao giờ hiện, không tappable.
  - Tablet có chuột nhưng người dùng vẫn hay dùng chạm → hover không đáng tin.

  Theme đã có tiền lệ ở `home.css` (§ best-sellers, `.dg-home-product .dg-quick-add`) và `main.css` (§ carousel, `#dg-carousel .dg-quick-add`) dùng `@media (hover: none)`. Ở đây dùng **chiều rộng** (`max-width: 1023px`) thay vì `hover: none` vì yêu cầu là "mobile + tablet luôn hiện" — tablet có chuột vẫn phải hiện.

  Sửa (`assets/css/shop.css`, block mới sau `.dg-product-card:hover .dg-add-to-cart`):
  - `.dg-add-to-cart` → `opacity: 1` + `transform: translateY(0)`.
  - `.dg-add-to-cart::before` → `left: 160%` + `transition: none` — sheen dịch ra ngoài khung ngay lập tức, không để lại vệt sáng đọng trên nút (trước nó chỉ chạy khi hover card).
  - `.dg-add-to-cart:hover` → `transform: translateY(-2px)` — hiệu ứng nhấc nút khi hover/tap, khớp với base rule (base rule dùng `translateX(-50%)`, nhưng rule pill ở §13 đặt sau nên `left/right` + `translateY` mới là giá trị thực thi).
  - `.dg-add-to-cart.dg-quick-add--added:hover` → `transform: translateY(-2px)` — giữ trạng thái "added" vĩnh viễn nhất quán.

  Breakpoint 1024px = mốc theme chuyển sang layout 2–3 cột (`lg:grid-cols-12` / `lg:grid-cols-3`). Dưới mốc này grid là 1–2 cột, card nhỏ hơn → nút cần luôn hiện. **Desktop (>= 1024px) giữ nguyên hover-reveal.**

  Kiểm chứng (Playwright, `tmp-resp/shop-quickadd-check.js` — đo cả `elementFromPoint` để xác nhận nút thật sự bắt click, không chỉ `opacity`):
  - 320 / 390 / 430 / 639px (mobile) → không hover: `opacity=1`, `transform=matrix(1,0,0,1,0,0)`, `hit=true` ✓
  - 640 / 768 / 1023px (tablet) → không hover: `opacity=1`, `hit=true` ✓
  - 1024 / 1280px (desktop) → không hover: `opacity=0` + `translateY(16px)`; hover: `opacity=1` — **không đổi** ✓
  - Kiểm tra hồi quy: nút `opacity: 0` ở desktop vẫn bắt `elementFromPoint`, nhưng khi chuột đi tới vị trí đó thì `:hover` của card kích hoạt → `opacity=1` → click vào nút hoạt động đúng (không chặn click ảnh) ✓

  `DG_VERSION` → `1.16.4`. Không commit git.

- `2026-11-06d`: Wishlist — **empty state bullets hiển thị xấu ở mobile**: bị đẩy lệch sang phải, chữ vỡ 2–3 dòng.

  Vấn đề gốc: `.dg-wishlist-empty__bullets` có `padding-left: 105px` **hard-code**, thiết kế cho cột 540px ở desktop (căn list trong khung rộng). Ở mobile khối ngoài chỉ còn ~250–340px → 105px chiếm **39% bề rộng**, list bị đẩy lệch, chữ bó sát mép phải và vỡ dòng:
    - 390px → text rộng 191px (2 dòng)
    - 320px → text rộng 121px (**3 dòng**)

  Không có media query nào cho `.dg-wishlist-empty__bullets` → mobile kế thừa nguyên layout desktop.

  Sửa (`assets/css/wishlist.css`, block mới §6b `@media (max-width: 639px)`):
  - `.dg-wishlist-empty__bullets` → `padding-left: var(--wl-empty-bullets-indent-mobile)` (0px — bỏ indent, icon dẫn hàng), `gap: var(--wl-empty-bullets-row-gap-mobile)`.
  - `.dg-wishlist-empty__bullets li` → `grid-template-columns: var(--wl-empty-bullets-icon-size-mobile) 1fr` (icon 32px → 28px), `gap: var(--wl-empty-bullets-gap-mobile)`, `font-size: var(--wl-empty-bullets-font-size-mobile)`, `line-height: 1.45`.
  - `.dg-wishlist-empty__bullets .material-symbols-outlined` → tile 28×28px, `font-size: 16px`.
  - `.dg-wishlist-empty` → `padding: 2rem 1.25rem` (mobile nhỏ gọn hơn).
  - `.dg-wishlist-empty__actions` → `flex-direction: column` + nút `width: 100%` (2 nút cạnh nhau quá hẹp, chữ "Explore the collection" bị bó).

  Token mới trong `style.css` (§ Wishlist Component): `--wl-empty-bullets-indent-mobile`, `--wl-empty-bullets-icon-size-mobile`, `--wl-empty-bullets-gap-mobile`, `--wl-empty-bullets-row-gap-mobile`, `--wl-empty-bullets-font-size-mobile`.

  Kiểm chứng (Playwright, `tmp-resp/wl-empty-check.js`):
  - 320px → text rộng 121px (3 dòng) → **240px (2 dòng)** ✓
  - 390px → text rộng 191px (2 dòng) → **310px (1 dòng)** ✓
  - 430px → text rộng 231px (2 dòng) → **350px (1 dòng)** ✓
  - 639px → text rộng 391px (1 dòng) → 502px ✓
  - 640 / 768 / 1024 / 1280px (tablet + desktop) → `padding-left` vẫn 105px, icon 32px, text 391px — **không đổi** ✓
  - Không overflow ngang ở mốc nào ✓

  `DG_VERSION` → `1.16.3`. Không commit git.

- `2026-11-06c`: hero trang chủ — **badge pill nằm TRÊN product frame ở tablet** (640–1023px). Trước đó tablet vẫn giữ layout cũ: copy trên, ảnh dưới.

  Sửa (`assets/css/home.css`, block `@media (min-width: 640px) and (max-width: 1023px)`) — **dùng lại đúng kỹ thuật của block mobile**, chỉ khác giá trị token:
  - `.dg-home-hero .dg-home-hero__copy` → `display: contents`.
  - `.dg-home-hero .dg-home-hero__copy > *` → `min-width: 0` + `order: 0` + `justify-self: start`.
  - `.dg-home-hero .dg-home-hero__copy > .dg-home-hero__badge` → `order: -2` (badge trên cùng).
  - `.dg-home-hero .dg-home-hero__visual` → `order: -1` (giữ nguyên: frame ngay dưới badge).
  - `.dg-home-hero-wrapper .dg-home-hero` → `row-gap: var(--hero-stack-gap-tablet)` (2 class để thắng utility `gap-8` của Tailwind CDN — §8b).
  - `.dg-home-hero__product-frame` → `margin-top: 0` + `margin-bottom: var(--hero-frame-bottom-gap-tablet)`.
  - `style.css` — thêm token `--hero-stack-gap-tablet: 1.5rem` và `--hero-frame-bottom-gap-tablet: 0.5rem`.

  Bổ sung `justify-self: start` (áp dụng cho **cả** mobile và tablet):
  - Cột copy gốc là flex `items-start` → khối con co theo nội dung. Khi hòa tan thành grid item, mặc định là `stretch` (full-width) → sai hành vi.
  - Đo được trước khi sửa: title mobile 357px → 390px sau khi `display: contents`. Sau khi thêm `justify-self: start`: title 390px → 357px, khớp lại baseline flex.
  - Các khối tự khai báo width (stats row `w-full`, CTA `w-full sm:w-auto`) vẫn giữ width của chúng.

  Kiểm chứng (Playwright, `tmp-resp/home-hero-order.js` — đã mở rộng nhánh tablet):
  - 640 / 768 / 1023px → `copy.display=contents`, badge `order=-2` top=40, frame top=100, headline top=782 → **badge TRÊN, frame GIỮA, copy DƯỚI** ✓
  - 320 / 390 / 430 / 639px (mobile) → không đổi so với `2026-11-06b` ✓
  - 1024 / 1280px (desktop) → `copy.display=flex`, grid 1 hàng 12 cột, copy trái / visual phải — **không đổi** ✓
  - Độ rộng khối con ở tablet khớp baseline cũ: badge 372px, title 670px, desc 489px, CTA 319px ✓
  - Không overflow ngang ở bất kỳ mốc nào ✓

  `DG_VERSION` → `1.16.2`. Không commit git.

- `2026-11-06b`: hero trang chủ — **badge pill nằm TRÊN product frame ở mobile** (trước: frame trên, badge bị đẩy xuống dưới cả tiêu đề).

  Bối cảnh: sau `2026-11-06a` (đảo 2 khối ở mobile → ảnh lên trên), badge `.dg-home-hero__badge` vẫn nằm trong cột copy nên rơi xuống **dưới** tiêu đề + mô tả + CTA — sai thứ tự đọc trên mobile. Yêu cầu: badge phải nằm trên thẻ product frame, chỉ áp dụng mobile.

  Sửa:
  - `template-parts/home/hero.php` — thêm class theme-controlled `dg-home-hero__copy` cho cột copy (trước chỉ có `lg:col-span-6 flex …`, không có class để target an toàn theo §5g).
  - `assets/css/home.css`, block mobile `@media (max-width: 639px)`:
    - `.dg-home-hero .dg-home-hero__copy` → `display: contents` — hòa tan box của cột copy, các phần tử con (badge / headline / desc / CTA / stats) trở thành grid item trực tiếp của hero grid → mỗi phần tử `order` độc lập.
    - `.dg-home-hero .dg-home-hero__copy > *` → `order: 0` + `min-width: 0` (reset, thay cho `min-width: 0` cũ do `.dg-home-hero > div` đảm nhiệm).
    - `.dg-home-hero .dg-home-hero__copy > .dg-home-hero__badge` → `order: -2` (badge trên cùng).
    - `.dg-home-hero .dg-home-hero__visual` → `order: -1` (giữ nguyên: frame ngay dưới badge).
    - `.dg-home-hero-wrapper .dg-home-hero` → `row-gap: var(--hero-stack-gap-mobile)` (2 class để thắng utility `gap-8` của Tailwind CDN — §8b).
    - `.dg-home-hero__product-frame` → `margin-top: 0` + `margin-bottom: var(--hero-frame-bottom-gap-mobile)`.
  - `style.css` — thêm token `--hero-stack-gap-mobile: 1.5rem` và `--hero-frame-bottom-gap-mobile: 0.5rem`.

  Vì sao dùng `display: contents` chứ không đổi DOM:
  - Badge và product frame **không phải anh em** trong DOM (badge nằm trong cột copy) → `order` đơn thuần không diễn tả được "badge, frame, rồi phần copy còn lại".
  - `display: contents` chỉ ảnh hưởng **layout**, không đổi DOM → screen reader và SEO vẫn đọc copy trước (thông điệp chính), không cần sửa template/touch semantics.
  - `display: contents` chỉ bật trong media query mobile → desktop/tablet giữ nguyên layout 2 cột, `display: flex` của cột copy.

  Kiểm chứng (Playwright, `tmp-resp/home-hero-order.js`):
  - 320 / 390 / 430 / 639px → `copy.display=contents`, badge `order=-2` top=40, frame top=88, headline top=520/608/658/770 → **badge TRÊN, frame GIỮA, copy DƯỚI** ✓
  - 640 / 768 / 1023px (tablet) → `copy.display=flex`, `order=0` → copy trên, visual dưới — **không đổi** ✓
  - 1024 / 1280px (desktop) → grid 1 hàng 12 cột, copy trái / visual phải — **không đổi** ✓
  - Căn trái: badge `left=0` khớp title `left=0`, frame `left=0` (639px: frame `left=60` do đã center trong cột max-width 520px) ✓
  - Không overflow ngang ở bất kỳ mốc nào ✓

  `DG_VERSION` → `1.16.1`. Không commit git.

- `2026-11-06a`: hero trang chủ — **đảo thứ tự 2 khối ở mobile**: product frame (ảnh) LÊN TRÊN, cột copy (tiêu đề + CTA) XUỐNG DƯỚI.

  Bối cảnh: DOM order của `template-parts/home/hero.php` là copy → visual (đúng cho desktop 2 cột và cho screen reader/SEO). Ở mobile grid 1 cột thì copy nằm trên, ảnh sản phẩm nằm dưới — nhưng trên màn hình dọc nên ảnh sản phẩm dẫn nhịp trước.

  Sửa (`assets/css/home.css`, block mobile `@media (max-width: 639px)`):
  - `.dg-home-hero .dg-home-hero__visual` → `order: -1`.
  - `.dg-home-hero .dg-home-hero__product-frame` → `margin-top: 0` (thay cho `var(--hero-frame-margin-top-mobile)`).

  Vì sao dùng `order` chứ không đổi DOM:
  - `order` chỉ sắp xếp lại **hiển thị** grid item, DOM giữ nguyên → screen reader vẫn đọc copy trước (thông điệp chính), SEO không đổi, không cần sửa template.
  - Cột copy không có class theme-controlled nhưng **không cần rule**: grid item mặc định `order: 0`, chỉ cần hạ visual xuống `-1`.
  - `margin-top` cũ của frame được bỏ vì frame giờ là hàng đầu của grid — margin đó chỉ có ý nghĩa khi frame nằm SAU copy. Khoảng cách visual → copy giờ do `gap: 2rem` của grid đảm nhiệm.

  Kiểm chứng (Playwright, `tmp-resp/home-hero-order.js` — mock nạp Tailwind CDN để `lg:grid-cols-12` / `aspect-[4/5]` có tác dụng):
  - 320 / 390 / 430 / 639px → `order: -1`, grid 2 hàng, **visual top < copy top** (ảnh trên, chữ dưới) ✓
  - 640 / 768 / 1023px (tablet) → `order: 0`, copy trước, visual sau — **không đổi** ✓
  - 1024 / 1280px (desktop) → grid 1 hàng 12 cột, copy trái / visual phải — **không đổi** ✓
  - Frame margin-top = 0 ở mobile, 32px ở tablet/desktop ✓

  `DG_VERSION` → `1.16.0`. Không commit git.

- `2026-11-05t`: chip filter Wishlist — chip **active không còn đổi style khi hover**; chip chưa active giữ nguyên hover.

  Vấn đề: `.dg-wishlist-toolbar__seg:hover` và `.dg-wishlist-toolbar__seg.is-active` cùng specificity (0,2,0), nhưng rule hover đứng **trước** → khi hover chip active, `background: var(--wl-chip-bg)` + `color: var(--wl-primary)` ghi đè nền vàng active → chip active bị "bay màu" khi chuột đi qua.

  Sửa (`assets/css/wishlist.css`):
  - `.dg-wishlist-toolbar__seg:hover` → `.dg-wishlist-toolbar__seg:not(.is-active):hover`
  - `.dg-wishlist-toolbar__seg:hover .dg-wishlist-toolbar__count` → `.dg-wishlist-toolbar__seg:not(.is-active):hover .dg-wishlist-toolbar__count`

  Không có rule nào ở media query nào bị ảnh hưởng → **mobile / tablet / desktop đồng bộ** theo yêu cầu.

  Kiểm chứng (Playwright, `tmp-resp/wl-hover-check.js` + `wl-hover-lifecycle.js` — dùng `page.hover()` thật vì synthetic event không kích hoạt CSS `:hover`):
  - 320 / 390 / 639 / 640 / 768 / 1280px → chip ACTIVE hover giữ nguyên `bg=rgb(115,92,0)` + chữ trắng + badge trắng 25% ✓
  - Chip INACTIVE hover → đổi sang `bg=rgba(115,92,0,0.06)` + chữ `rgb(115,92,0)` + badge `rgba(115,92,0,0.12)` ✓
  - Vòng đời chuyển trạng thái (JS đổi `is-active`): chip cũ **lấy lại** hover style, chip mới giữ nền active, `aria-selected` đồng bộ ✓

  `DG_VERSION` → `1.15.99`. Không commit git.

- `2026-11-05s`: chip filter Wishlist ở mobile — **giảm font chữ + icon**, text/icon **không còn sát viền chip**.

  Vấn đề: ở mobile icon + text + badge vẫn dồn sát viền chip (padding 5px, chỉ ~3px từ text tới viền). Bản `2026-11-05r` đã dùng `padding: clamp(5px,...)` nhưng font 10px + icon 13px + badge 15px → nội dung chiếm gần hết chip, không còn dư chỗ thoáng.

  Sửa (`assets/css/wishlist.css`, block `§12b` — `@media (max-width: 639px)`):
  - `.dg-wishlist-toolbar__seg` → `font-size: clamp(10px, 2.5vw, 12px)` (giữ ≥ 10px để dễ đọc), `padding: clamp(5.5px, 1.6vw, 9px)`, `gap: clamp(3px, 0.9vw, 5px)`.
  - `.dg-wishlist-toolbar__seg .material-symbols-outlined` → `font-size: clamp(11px, 2.8vw, 15px)` (giảm từ 13px).
  - `.dg-wishlist-toolbar__count` → `min-width/height: clamp(12px, 3.6vw, 20px)` (giảm từ 15px), `font-size: clamp(9px, 2.2vw, 11px)`.

  Nguyên tắc: **giảm kích thước nội dung để MUA được không gian padding**. Chip co lại → lấy phần không gian đó làm padding lớn hơn → text/icon tách xa viền.

  Tối ưu bằng script brute-force (`tmp-resp/wl-chip-optimize.js`): quét 3.000+ tổ hợp (font, icon, badge, gap, padding) với ràng buộc tổng 3 chip ≤ content 244px ở 320px, ưu tiên **font lớn nhất** rồi **padding lớn nhất**. Kết quả chọn: font 10px / icon 11px / badge 12px / gap 3px / padding 5.5px.

  Kiểm chứng (Playwright):
  - 320/360/390/430/639px → overflow nội dung chip = **0px** ✓
  - Padding chip: 5.5px (320px) → 6.2px (390px) → 6.9px (430px) → 9px (639px) — text không còn sát viền ✓
  - Khoảng cách text ↔ icon / text ↔ badge: 3–5px đều đặn ✓
  - Segment không tràn ngang: 3 chip 236px + 8px gap = 244px = content 254px ✓ (dư 5px mỗi bên)
  - 640px & 768px → **không đổi** (fs 13px, padding 14px, icon 16px như desktop) ✓
  - Select + sort vẫn cùng 1 hàng ngang, "Recently saved" không bị cắt ✓

  `DG_VERSION` → `1.15.98`. Không commit git.

- `2026-11-05r`: chip filter Wishlist ở mobile — **hiện lại icon** (giống desktop) + text **thoải mái, không sát viền**.

  Vấn đề: ở mobile chip bị ép vào 1/3 chiều ngang (`repeat(3, minmax(0, 1fr))`) → chỉ có 79px ở 320px, trong khi bộ "icon + text + badge" cần ~91px ở font 12px → tràn; để vừa phải ẩn icon và rút padding xuống 3.2px → chữ sát viền, không thoải mái.

  Sửa (`assets/css/wishlist.css`, block `§12b` — `@media (max-width: 639px)`):
  - `.dg-wishlist-toolbar__segment` → `grid-template-columns: repeat(3, auto)` + `justify-content: space-between` (thay vì `minmax(0, 1fr)`) → mỗi chip có bề rộng **đúng bằng nội dung** → padding thoải mái, không dồn chữ.
  - `.dg-wishlist-toolbar__seg .material-symbols-outlined` → `display: inline-block` (bỏ `display: none`) + `font-size: clamp(13px, 3.2vw, 16px)` → icon hiện lại giống desktop nhưng co nhỏ theo viewport.
  - Mọi kích thước của chip dùng **fluid `clamp()`** để tự co giãn theo viewport:
    - `font-size: clamp(10px, 2.6vw, 12px)` → 10px ở 320px, 12px ở 460px+
    - `padding: clamp(5px, 1.4vw, 8px)` → 5px ở 320px, 8px ở 570px+
    - `gap: clamp(3px, 0.9vw, 5px)`
    - icon `clamp(13px, 3.2vw, 16px)`, badge `clamp(15px, 4.2vw, 20px)` (+ `flex-shrink: 0` để không bị bóp méo).
  - `justify-content: center` trong chip → nội dung luôn căn giữa.

  Số đo thật (Playwright): Ở 320px 3 chip = 77 + 74 + 72px + 2 gap 4px = **231px** < content 244px → vừa khít, không tràn, không cần cuộn ngang.

  Kiểm chứng (Playwright, `tmp-resp/wl-chip-pad.js` + `wl-overflow-check.js`):
  - 320/360/390/430/639px → icon **CÓ** trong cả 3 chip, overflow nội dung = **0px** ✓
  - Padding chip: 5px (320px) → 5.5px (390px) → 6px (430px) → 8px (639px) — text không còn sát viền ✓
  - Khoảng cách text ↔ icon / text ↔ badge: 3–5px, đều đặn ✓
  - 640px & 768px → **không đổi** (fs 13px, padding 14px, icon 16px như desktop) ✓
  - Select + sort vẫn cùng 1 hàng ngang, "Recently saved" không bị cắt ✓

  `DG_VERSION` → `1.15.97`. Không commit git.

- `2026-11-05q`: toolbar Wishlist — "Select (0)" và nút sort **cùng 1 hàng ngang** ở mobile (thay đổi so với `2026-11-05p` vốn xếp dọc).

  Sửa (`assets/css/wishlist.css`, block `§12b` — `@media (max-width: 639px)`):
  - `.dg-wishlist-toolbar__right` → `grid-template-columns: auto minmax(0, 1fr)` (thay vì `minmax(0, 1fr)` 1 cột) → select-all cột 1, sort cột 2, cùng hàng.
  - `.dg-wishlist-toolbar__select-all` → `flex-shrink: 0` để không bị co lại.
  - `.dg-wishlist-toolbar__sort-trigger` → `width: 100%` + `justify-content: flex-start`; chevron `margin-left: auto` → đẩy mép phải.

  Số đo thật (Playwright) quyết định giữ được 1 hàng:
  - select-all = **85px** (checkbox 20px + gap + chữ "Select (0)" 60px)
  - sort trigger tối thiểu = padding 24 + "Recently saved" **96px** + chevron 20px + gap 6 = **146px**
  - tổng + gap 10px = **241px** < content 256px ở 320px → **vừa đủ**, chữ không bị cắt.

  Vì sao icon `schedule` vẫn ẩn ở mobile: giữ lại thì tổng thành 267px > 256px → "Recently saved" bị cắt thành "Recently save…". Icon là decoration; chevron vẫn giữ nên affordance dropdown rõ ràng.

  Lưu ý kỹ thuật: file `assets/fonts/material-symbols-outlined.woff2` **không có trong repo** → ligature render thành chữ "expand_more" (109px) khi đo. Mock phải ép `width: 1em` cho icon để số đo phản ánh đúng bề rộng ligature thật (~20px).

  Kiểm chứng (Playwright, `tmp-resp/wl-samerow-check.js` + `wl-overflow-check.js` + `wl-panel-check.js`):
  - 320/360/390/430/639px → select-all & trigger **cùng hàng** (center lệch < 3px), gap = 10px ✓
  - "Recently saved" overflow = **0px** ở mọi width ✓
  - Toolbar cao 122px (2 hàng, giảm từ 153px ở bản xếp dọc) ✓
  - Sort panel mở: `[47..287]` / `[117..357]` / `[150..397]` — trọn trong viewport ✓
  - 640 / 768 / 1280px → **không đổi** (vẫn hiện nhãn "Sort by") ✓

  `DG_VERSION` → `1.15.96`. Không commit git.

- `2026-11-05p`: toolbar Wishlist (`.dg-wishlist-toolbar`) **mobile-only** — 3 chip filter về 1 hàng đều nhau, sort full-width.

  Vấn đề: ở mobile toolbar vỡ — 3 chip filter (`All items` / `In stock` / `On sale`) cần tổng ~531px nhưng content chỉ ~252–362px → `flex-wrap` làm "On sale" rớt hàng; sort trigger rộng ~292px > content 252px ở 320px → tràn ngang.

  Sửa (`assets/css/wishlist.css`) — block mới `§12b` dưới `@media (max-width: 639px)`, tách riêng khỏi block `767px` sẵn có để **tablet (640–767px) giữ nguyên layout desktop**:
  - `.dg-wishlist-toolbar__segment` → `display: grid` + `grid-template-columns: repeat(3, minmax(0, 1fr))`, `flex-wrap: nowrap` → 3 chip đều nhau, 1 hàng.
  - `.dg-wishlist-toolbar__seg` → `padding: 8px 6px`, `font-size: 12px`, `letter-spacing: 0.01em`, `white-space: nowrap`.
  - Icon filter (`apps` / `check_circle` / `local_offer`) → `display: none` ở mobile (decoration, chữ đã đủ nghĩa; vẫn còn trong DOM cho a11y).
  - `.dg-wishlist-toolbar__right` → grid 1 cột, xếp dọc: "Select (0)" 1 hàng, sort 1 hàng full-width. *(Sau đó đổi lại thành 1 hàng ngang ở `2026-11-05q`.)*
  - `.dg-wishlist-toolbar__sort-label` ("Sort by") → ẩn; icon `schedule` → ẩn (decoration) để nhường chỗ cho nhãn giá trị; chevron giữ + `margin-left: auto` → đẩy mép phải.
  - `.dg-wishlist-toolbar__sort-trigger` → `width: 100%`, `justify-content: flex-start`.

  Vì sao sort xếp dọc thay vì chung hàng với "Select (0)": ở 320px, sort chỉ còn ~159px (sau khi trừ select-all 85px + gap) trong khi "Recently saved" cần ~236px → bị cắt "Recently save…". Full-width = 254px → vừa đủ, **overflow = 0** ở mọi mobile width.

  Kiểm chứng (Playwright, `tmp-resp/wl-toolbar-measure.js` + `wl-overflow-check.js` + `wl-panel-check.js`):
  - 320/360/390/430/639px → 3 chip **1 hàng**, đều nhau (79/102/115px), overflow nội dung = 0 ✓
  - Sort trigger full-width, "Recently saved" **không bị cắt** (overflow 0px) ✓
  - Sort panel mở: `[40..287]` / `[110..357]` / `[150..397]` — nằm trọn trong viewport ✓
  - 640px & 768px & 1280px → **không đổi** (3 chip inline, còn nhãn "Sort by" 57px) ✓

  `DG_VERSION` → `1.15.95`. Không commit git.

- `2026-11-05o`: nhãn "On sale" (`.dg-wishlist-hero__stat-label--stacked`) cũng về **1 dòng** ở mobile.

  Vấn đề: nhãn này dùng 2 span `display: block` → cố ý xếp 2 dòng ("On" / "sale"). Ở mobile các label khác đã về 1 dòng (lượt trước) nhưng nhãn này vẫn 2 dòng → không đồng nhất.

  Sửa (`assets/css/wishlist.css`):
  - Chuyển block `@media (max-width: 639px)` xuống **sau** các rule base `.dg-wishlist-hero__stat-label--stacked` → cùng specificity, thứ tự sau thắng.
  - Bỏ `:not(--stacked)` ở rule label → giờ áp `letter-spacing: 0.06em` + `white-space: nowrap` cho MỌI label.
  - `--stacked > span` → `display: inline` (từ block) + `span + span` → `margin-left: 0.35em` thay cho `margin-top: 2px`.

  Vì sao cần `margin-left`: markup không có whitespace giữa 2 span → nếu chỉ đổi `display` sang `inline` sẽ ra "Onsale" dính liền. `0.35em` tạo khoảng trắng tương đương word-space.

  Desktop/tablet (≥ 640px) **không đổi**: 2 span vẫn `display: block` → nhãn 2 dòng như cũ.

  Kiểm chứng (Playwright, `tmp-resp/wl-label-verify.js`):
  - 320/360/390/430/639px → "On sale" **1 dòng**, không tràn card ✓
  - Khoảng cách giữa "On" và "sale" = 4px, cùng hàng ✓
  - 640px & 1280px → "On sale" giữ 2 dòng, `white-space: normal`, letter-spacing 1.1px ✓

  `DG_VERSION` → `1.15.94`. Không commit git.

- `2026-11-05n`: label stats hero Wishlist ("Saved items") hiển thị **1 dòng** ở mobile.

  Vấn đề: ở mobile stats là grid 2 cột → mỗi card rộng ~141px (content box ~109px), trong khi "SAVED ITEMS" ở font 11px + `letter-spacing: 0.10em` rộng ~107px → chỉ dư 2px, dễ rớt xuống 2 dòng.

  Sửa (`assets/css/wishlist.css`, trong `@media (max-width: 639px)`):
  - `.dg-wishlist-hero__stat` → `padding: 18px 12px` (tạo thêm headroom ngang)
  - `.dg-wishlist-hero__stat-label:not(--stacked)` → `letter-spacing: 0.06em` + `white-space: nowrap`

  **Tạo headroom TRƯỚC rồi mới ép nowrap** — nhờ vậy chữ luôn nằm gọn 1 dòng mà vẫn nằm trọn trong card (không tràn). KHÔNG dùng `text-overflow: ellipsis` vì cắt "Saved items" → "Saved i…" làm mất nghĩa.

  `.dg-wishlist-hero__stat-label--stacked` (nhãn "On sale" tách 2 span `display: block`) cố ý xếp 2 dòng → loại khỏi `:not()`, giữ nguyên.

  Desktop/tablet (≥ 640px) **không đổi**: không nowrap, letter-spacing giữ 0.10em.

  Kiểm chứng (Playwright, `tmp-resp/wl-label-verify.js`):
  - 320/360/390/430/639px → mọi label thường **1 dòng**, không tràn ra ngoài card ✓
  - Label "On sale" giữ 2 dòng ✓
  - 640px & 1280px → `white-space: normal`, letter-spacing 1.1px (0.10em) — giữ nguyên ✓

  `DG_VERSION` → `1.15.93`. Không commit git.

- `2026-11-05m`: 2 nút CTA hero Wishlist (Share wishlist / Continue shopping) giãn hết bề ngang khối stats ở **mobile**.

  Vấn đề: ở mobile, `.dg-wishlist-hero__actions` là flex `wrap` → 2 nút ôm chữ (187px / 251px) trong khi `.dg-wishlist-hero__stats` full width (294px) → lệch bề ngang, nút trông nhỏ lạc giữa hero.

  Sửa (`assets/css/wishlist.css`) — trong `@media (max-width: 639px)`:
  - `.dg-wishlist-hero__actions` → `flex-direction: column; flex-wrap: nowrap`
  - `.dg-wishlist-hero__actions .dg-wishlist-btn` → `width: 100%; justify-content: center`

  Vì sao dùng `column` chứ không giữ `wrap`: chữ "Share wishlist" ngắn hơn "Continue shopping" → nếu wrap ngang, 2 nút sẽ nằm cùng hàng nhưng khác bề rộng (vẫn lệch). `column` ép cả 2 xếp dọc, mỗi nút chiếm đúng bề ngang stats.

  Desktop/tablet (≥ 640px) **không đổi**: 2 nút vẫn nằm cạnh nhau, ôm chữ.

  Kiểm chứng (Playwright, `tmp-resp/wl-btn-verify.js`):
  - 390px → stats 294px, share 294px, shop 294px ✓ · 430px → stats 334px, cả 2 nút 334px ✓
  - 2 nút xếp dọc, icon + chữ căn giữa ✓
  - 768px → share 187px / shop 251px (ôm chữ), cùng hàng ✓ · 1280px → giữ nguyên ✓

  `DG_VERSION` → `1.15.92`. Không commit git.

- `2026-11-05l`: thêm `padding-bottom` cho 3 trang pháp lý còn lại — Cookie Policy, Terms of Service, Accessibility Statement (bổ sung cho `.dg-privacy` ở `2026-11-05k`).

  Vấn đề: giống `.dg-privacy` — cả 3 page-level `<main>` không có khoảng đệm dưới → nội dung cuối dính sát footer.

  Sửa (mỗi file đúng 1 rule):
  - `assets/css/cookie-policy.css` → `.dg-cookie`
  - `assets/css/terms-of-service.css` → `.dg-terms`
  - `assets/css/accessibility-statement.css` → `.dg-accessibility`

  Cả 3 dùng `padding-bottom: clamp(4rem, 10vw, 7rem)` — **bám đúng convention** của page-level `<main>` trong theme (`.dg-faq`, `.dg-gift`, `.dg-privacy`).

  Kiểm chứng (Playwright, `tmp-resp/legal-pad-verify.js`, 390/768/1280px):
  - Cả 4 trang: padding 64px / 76.8px / 112px ✓
  - Khoảng đệm thực tế (main.bottom − wrap.bottom) khớp chính xác padding-bottom ✓
  - `margin-bottom` của main = 0 → không nhân đôi khoảng đệm ✓

  `DG_VERSION` → `1.15.91`. Không commit git.

- `2026-11-05k`: thêm `padding-bottom` cho `<main class="dg-privacy">` (trang Privacy Policy).

  Vấn đề: `.dg-privacy` không có khoảng đệm dưới → nội dung cuối trang dính sát footer, mất khoảng thở giữa tài liệu pháp lý và footer.

  Sửa (`assets/css/privacy-policy.css`): `.dg-privacy` → `padding-bottom: clamp(4rem, 10vw, 7rem)`.

  Giá trị này **bám đúng convention** của page-level `<main>` trong theme: `.dg-faq` và `.dg-gift` đều dùng `clamp(4rem, 10vw, 7rem)` (flexible → tự co giãn theo viewport, không hard-code px).

  Kiểm chứng (Playwright, `tmp-resp/privacy-pad-verify.js`, 390/768/1280px):
  - 390px → 64px · 768px → 76.8px · 1280px → 112px ✓
  - Khoảng cách thực tế từ nội dung cuối tới footer khớp chính xác padding-bottom ở cả 3 breakpoint ✓

  `DG_VERSION` → `1.15.90`. Không commit git.

- `2026-11-05j`: nút **"Write to us"** (`.dg-careers-closing .dg-careers-btn--primary`) nay dùng chung hệ gold-pill với nút "Discover Our Story" (`.dg-sus-btn`).

  Vấn đề: nút closing của Careers còn style cũ — nền `--color-on-surface` (đen đặc), chữ `--color-background-warm`, bo góc vuông (`border-radius: 0`), không mũi tên → lệch hẳn với hệ gold-pill của các trang khác.

  Sửa (`assets/css/careers.css`):
  - Gộp rule thành **selector list** — `.dg-careers-hero .dg-careers-btn--secondary, .dg-careers-closing .dg-careers-btn--primary` dùng chung 1 bộ rule gold-pill (gradient, pill radius, chữ trắng, shadow, hover nâng/sáng, mũi tên `→`) → 2 nút trong trang Careers **không thể lệch style** về sau.
  - `align-self: stretch` chỉ giữ cho nút hero (giãn hết cột); nút closing là con của block container + `text-align: center` → ôm chữ, khớp `.dg-sus-btn` ở section closing.
  - Xoá rule cũ `.dg-careers-closing .dg-careers-btn--primary` (nền đen, bo vuông) + rule hover cũ.
  - Thêm nhánh `prefers-reduced-motion` cho nút closing (transform + mũi tên).

  `template-parts/careers/section-closing-cta.php`: bọc text trong `<span class="dg-careers-btn__label">` (mốc neo cho mũi tên).

  Kiểm chứng (Playwright, `tmp-resp/careers-btn-verify.js`, 390/768/1280px):
  - Parity 11/11 với `.dg-sus-btn`: gradient, radius, border, màu chữ, font-size/weight/tracking, transform, padding, shadow, display ✓
  - Mũi tên `→`: content đúng, opacity 0 → 1 khi hover, nằm trong pill (inset 8px), text căn giữa (Δx/Δy < 0.6px) ✓
  - Hover: gradient sáng hơn + shadow hover + nâng `translateY(-2px)` — giống hệt ✓
  - Width: closing ôm chữ (fill < 1) như sus closing; nút hero vẫn giãn hết cột (fill 100%) ✓
  - Reduced-motion: transform `none`, arrow opacity `0` ✓

  `DG_VERSION` → `1.15.89`. Không commit git.

- `2026-11-05i`: đổi màu chữ **trắng** cho cả 3 nút CTA editorial gold-pill (OI / Careers / Sustainability) theo yêu cầu chủ design.

  Sửa: `--cta-gold-ink` trong `style.css` `:root` → `var(--neutral-0)` (trước: `var(--primary-900)`). Vì 3 nút dùng chung 1 token nên 1 dòng là cả 3 trang đồng nhất — nền gradient, hover, shadow, bo góc pill, mũi tên `→` giữ nguyên 100%.

  ⚠ Ghi nhận accessibility: chữ trắng trên gradient gold (`f1ca50` → `d4af37`) chỉ đạt **1.58:1** → dưới WCAG 2.1 AA (4.5:1) cho text 12px. Đây là quyết định thiết kế có chủ đích; nếu cần tuân thủ AA thì đổi nền sang `gold-700 → gold-800` (4.85–6.99:1) hoặc giữ chữ `--primary-900` (bản AAA ≥ 7.16:1). Đã ghi chú ngay trong `style.css` và trong script verify.

  Kiểm chứng (Playwright, `tmp-resp/sus-btn-verify.js`, 390/768/1280px): parity 10/10 với OI (gradient/radius/font/tracking/padding/shadow), hover giống hệt, width hero fill 100%, arrow trong pill, reduced-motion OK, 3 nút cùng màu chữ `rgb(255,255,255)` ✓

  `DG_VERSION` → `1.15.88`. Không commit git.

- `2026-11-05h`: nút CTA trang **Sustainability** (`.dg-sus-btn`) nay dùng **chung hệ editorial gold-pill** với Our Ingredients + Careers; gom token `--oi-btn-*` → `--cta-gold-*` (theo vai trò, không theo tên trang).

  Vấn đề: `.dg-sus-btn` còn style cũ — nền gold tối (gold-500 → gold-600), **chữ trắng** (chỉ 2.40:1, dưới WCAG AA 4.5:1), bo góc vuông, không có mũi tên `→` → lệch hẳn so với 2 trang kia.

  Sửa:
  - `style.css` — đổi tên bộ token `--oi-btn-*` → `--cta-gold-*` (dùng chung 3 trang, theo §8a "Define once"), thêm `--cta-gold-tracking`; xoá bộ `--sus-btn-*` cũ.
  - `assets/css/our-ingredients.css`, `assets/css/careers.css` — trỏ sang token mới.
  - `assets/css/sustainability.css` — `.dg-sus-btn` dùng `--cta-gold-*`, bo góc pill (`--cta-gold-radius`), chữ `--cta-gold-ink` (`--primary-900`), thêm `.dg-sus-btn__label` + mũi tên `→` trượt khi hover, focus ring `--gold-dark`, nhánh `prefers-reduced-motion` tắt transform/arrow.
  - `template-parts/sustainability/section-hero.php`, `section-closing.php` — bọc text trong `<span class="dg-sus-btn__label">` (mốc neo cho mũi tên).

  Kiểm chứng (Playwright, `tmp-resp/sus-btn-verify.js`, markup + layout thật, 390/768/1280px):
  - Parity 10/10 với `.dg-oi-btn`: gradient, radius 9999px, ink, font-size/weight/tracking, transform, padding, shadow ✓
  - Mũi tên `→`: content đúng, opacity 0 → 1 khi hover, trượt về 0, luôn nằm trong pill ✓
  - Hover: gradient sáng hơn + shadow hover + nâng `translateY(-2px)` — **giống hệt** OI ✓
  - Width: hero giãn hết cột (fill 100%) như OI ở cả 3 breakpoint ✓
  - Contrast: min **8.17:1** → WCAG AAA (text 12px) ✓
  - Reduced-motion: transform `none`, arrow opacity `0` ✓

  `DG_VERSION` → `1.15.87`. Không commit git.

- `2026-11-05g`: sửa **chiều rộng** nút CTA hero Careers — giãn hết cột, khớp `.dg-oi-btn`.

  Vấn đề: sau `2026-11-05f` màu/icon/hover đã khớp nhưng nút Careers vẫn **ôm chữ** (192px) trong khi nút OI **giãn hết cột** (554px ở desktop) → 2 nút khác hẳn về chiều rộng.

  Nguyên nhân: base class `.dg-careers-btn--secondary` set `align-self: flex-start` (ôm chữ) cho các nút secondary khác; nút OI không set → mặc định `stretch` của flex column.

  Sửa (`assets/css/careers.css`): `.dg-careers-hero .dg-careers-btn--secondary` → `align-self: stretch` (ghi đè base class, có comment giải thích).

  Kiểm chứng (Playwright, markup + layout thật):
  - 390px → OI: content box 350px / nút 350px (fill 100%); Careers: content box 350px / nút 350px (100%) ✓
  - 768px → OI: 275px / 275px (100%); Careers: 320px / 320px (100%) ✓
  - 1280px → OI: 554px / 554px (100%); Careers: 533px / 533px (100%) ✓
  - Mũi tên `→` luôn nằm **bên trong** pill ở cả rest và hover, mọi breakpoint ✓
  - Text căn giữa tuyệt đối trong nút (lệch 0px) ✓
  - Style 10/10 khớp, contrast AA ✓, reduced-motion OK ✓

  `DG_VERSION` → `1.15.86`. Không commit.

- `2026-11-05f`: nút CTA hero **Careers** (`.dg-careers-btn--secondary`) đồng bộ hoàn toàn với nút editorial gold-pill của trang Our Ingredients (`.dg-oi-btn`).

  Yêu cầu: màu, chiều rộng, icon và hover giống hệt thẻ `<a href="#traceable" class="dg-oi-btn">See the sources</a>`.

  Trước: nút Careers là nền ink đặc + chữ trắng, bo góc 0, không gradient, không shadow, không mũi tên, font-weight 500, letter-spacing 0.1em.

  Sửa:
  - `style.css` `:root` — thêm `--oi-btn-radius: 9999px`; bộ token `--oi-btn-*` nay dùng chung cho cả 2 trang (đã ghi chú trong comment).
  - `assets/css/our-ingredients.css` — `.dg-oi-btn` dùng `border-radius: var(--oi-btn-radius)` thay hard-code `9999px`.
  - `assets/css/careers.css` — `.dg-careers-hero .dg-careers-btn--secondary` chuyển sang `display: inline-flex`, gradient gold, chữ `--primary-900`, bo góc pill, `font-weight: 600`, `letter-spacing: 0.12em`, shadow gold, transition `background/box-shadow/transform`, hover nâng `-2px` + gradient sáng hơn, `:active` về 0, focus ring `--gold-dark`.
  - Thêm khối mũi tên `→` (`::after` trên `.dg-careers-btn__label`) mirror đúng kỹ thuật của `.dg-oi-btn__label::after`: absolute + `left: 100%` neo vào mép phải **text** → không chiếm chỗ trong flex flow, không lọt ra ngoài pill.
  - `template-parts/careers/section-hero.php` — bọc label trong `<span class="dg-careers-btn__label">` (cần mốc neo cho mũi tên).
  - `assets/css/careers.css` — thêm nhánh `prefers-reduced-motion: reduce`: tắt transform nâng nút + ẩn mũi tên (mirror `.dg-oi-btn`).

  Kiểm chứng (Playwright, CSS thật của theme):
  - Style so sánh OI vs Careers — **10/10 khớp**: background, color, border, radius, padding, box-shadow, font, letter-spacing, transition, height ✓
  - Mũi tên: content/opacity/transform/margin-left/font-size khớp ✓; text căn giữa tuyệt đối trong nút (lệch 0px) cả trước và sau hover ✓
  - Hover: gradient `rgb(241,202,80)→rgb(212,175,55)`, shadow `0 16px 32px -12px`, `translateY(-2px)` — khớp OI ✓
  - Contrast: 7.16:1 (rest) → 8.17:1 (hover), **WCAG AA ✓** (nút cũ dùng chữ trắng trên gold chỉ 2.40:1 = fail AA)
  - Responsive 320/375/390/768/1024/1280px: không tràn ngang, bo góc pill, text căn giữa ✓
  - `prefers-reduced-motion`: transform `none`, mũi tên opacity 0 ✓

  `DG_VERSION` → `1.15.85`. Không commit.

- `2026-11-05e`: numbers strip (Sustainability) — 3 stat xếp **NGANG** 1 hàng trên MOBILE, dot separator nằm ngang.

  Vấn đề: sau `2026-11-05d` dot đã hiện ở mobile nhưng container là `flex-direction: column` → dot rơi xuống **DƯỚI** mỗi stat (separator dọc), không đúng vai trò như desktop (separator ngang).

  Ràng buộc đo được: 4 stat ở 16px rộng tổng **787px** > màn hình 390px → không thể 1 hàng ngang với 4 stat nếu không cuộn ngang.

  Quyết định (theo yêu cầu user): chỉ hiển thị **3 stat** ở mobile, không cuộn ngang.

  Sửa:
  - `style.css` `:root` — thêm 4 token mobile: `--sus-numbers-font-mobile: 12px`, `--sus-numbers-tracking-mobile: 0.04em`, `--sus-numbers-gap-mobile: 1.25rem`, `--sus-numbers-dot-gap-mobile: 0.5rem`.
  - `assets/css/sustainability.css` — thêm block `@media (max-width: 767px)`: chuyển `.dg-sus-numbers-inner` sang `display: grid` 5 cột `[stat]•[stat]•[stat]`; font 16px → 12px, `line-height` 1.0 → 1.25; ẩn stat thứ 4 (`:nth-child(7)`) + dot thứ 3 (`:nth-child(6)`).
  - ≥768px **không đổi**: vẫn `display: flex` row, 4 stat 16px, 3 dot.

  Kiểm chứng (Playwright, CSS thật của theme):
  - 320px → 3 stat + 2 dot, **1 hàng**, dot ngang giữa 2 stat, không cuộn ngang ✓
  - 390px → 3 stat + 2 dot, 1 hàng, không cuộn ngang ✓
  - 430px → 3 stat + 2 dot, 1 hàng, không cuộn ngang ✓
  - 768px → 4 stat + 3 dot, 1 hàng flex row — **không đổi** ✓
  - 1280px → 4 stat + 3 dot — **không đổi** ✓

  `DG_VERSION` → `1.15.84`. Không commit.

- `2026-11-05d`: hiện `.dg-sus-numbers-dot` (separator) trên **MOBILE** (Sustainability).

  Vấn đề: element `<span class="dg-sus-numbers-dot" aria-hidden="true">` **luôn có trong DOM** (render bởi `section-numbers.php`) nhưng bị CSS ẩn hoàn toàn ở mobile bằng `display: none`. Ở <768px layout là `flex-direction: column` nên 4 stat dồn liền nhau không có nhịp nhấn.

  Sửa (`assets/css/sustainability.css`): đổi `.dg-sus-numbers-dot` sang mobile-first — `display: block` ở base rule, **xoá** media query `min-width: 768px` vì giá trị đã giống nhau (trước đó là override trùng lặp).

  Kiểm chứng (Playwright, computed style thật):
  - 390px → 3/3 dot hiển thị, 4×4px, nằm giữa 2 item kề theo trục y (separator dọc) ✓
  - 640px → 3/3 dot hiển thị ✓
  - 768px → 3/3 dot hiển thị, separator ngang, **không đổi** ✓
  - 1280px → 3/3 dot hiển thị, separator ngang, **không đổi** ✓

  `DG_VERSION` → `1.15.83`. Không commit.

- `2026-11-05c`: giảm `padding-top` của `.dg-sus-hero-inner` trên **MOBILE** (Sustainability).

  Vấn đề: hero là khối đầu tiên của trang nhưng dùng chung `calc(--sus-section-gap * 0.75)` = 90px padding-top → khoảng hở lớn giữa header và ảnh hero trên màn hình nhỏ.

  Sửa:
  - `style.css` `:root` — thêm token `--sus-hero-pad-top-mobile: 32px` (khớp nhịp 8px của design system).
  - `assets/css/sustainability.css` — base (mobile) dùng token mới; media query `min-width: 768px` khai báo lại `padding-top: calc(var(--sus-section-gap) * 0.75)` (= 90px) để tablet/desktop **không đổi**.

  Kiểm chứng (Playwright, computed style thật):
  - 390px → padding-top **32px** ✓ · 640px → **32px** ✓ · 768px → **90px** ✓ · 1280px → **90px** ✓
  - padding-bottom (120px) và padding-left (24px / 64px) không đổi.

  `DG_VERSION` → `1.15.82`. Không commit.

- `2026-11-05b`: hero Sustainability — **đảo thứ tự ảnh/nội dung trên MOBILE**.

  Trước: mobile để nội dung ở trên, ảnh ở dưới (`order: 2` / `order: 1`); ≥768px grid 2 cột → ảnh trái, nội dung phải.

  Sau (mobile-first): `order` cơ bản đổi thành ảnh `1` / nội dung `2` → mobile ảnh trên, chữ dưới. Vì ≥768px vẫn là grid 2 cột với cùng thứ tự (ảnh trái, nội dung phải) nên **xoá được** khai báo `order` trong media query `min-width: 768px` — không còn override trùng lặp.

  Kiểm chứng (Playwright, 3 breakpoint):
  - 390px → IMAGE top=90, CONTENT top=536 (ảnh TRÊN) ✓
  - 768px → IMAGE left=64, CONTENT left=381 (2 cột) ✓
  - 1280px → IMAGE left=64, CONTENT left=627 (2 cột) ✓

  `DG_VERSION` → `1.15.81`. Không commit.

- `2026-11-05a`: hover `.dg-oi-btn` (Our Ingredients) đi **sáng hơn** thay vì đậm lại.

  Vấn đề: token `--oi-btn-bg-hover` dùng `linear-gradient(135deg, var(--gold-500), var(--gold-600))` — hover làm nút **tối đi** (min contrast 4.52:1 = chỉ AA), trái với hướng "Luminous Ethereal" của design system.

  Sửa (chỉ đổi token trong `style.css` `:root`, không đụng `our-ingredients.css`):
  `--oi-btn-bg-hover: linear-gradient(135deg, var(--gold-bright), var(--gold-400))`.

  Kiểm chứng (Playwright, computed style thật):
  - REST `rgb(212,175,55) → rgb(201,162,75)` — contrast 8.17 → 7.16:1 (AAA)
  - HOVER `rgb(241,202,80) → rgb(212,175,55)` — contrast 10.88 → 8.17:1 (AAA)
  - ΔL hover − rest = **+11.38** (sáng hơn) ✓
  - Mũi tên `::after`, transform `translateY(-2px)`, `box-shadow` giữ nguyên.

  `DG_VERSION` → `1.15.80`. Không commit.

- `2026-11-04f`: xoá hẳn hook báo cáo chrome-devtools.

  Lý do: sentinel và phần inject mỗi phiên chỉ để tự mô tả trạng thái server, trong khi
  bản thân MCP server vẫn tự hoạt động. Chi phí ~985 token/phiên không đổi lại được gì
  khi không dùng browser hằng ngày.

  Đã xoá: `.cursor/hooks/chrome-devtools-mcp-session-start.ps1` (16.133 bytes) và
  `.cursor/hooks/.chrome-devtools-tools-cache.json` (1.416 bytes).

  Giữ nguyên: `.cursor/mcp.json` (server vẫn đăng ký, tool vẫn gọi được),
  `scripts/setup-chrome-devtools-mcp.ps1` (tải lại vendor), `vendor/chrome-devtools-mcp/`.

  Kiểm tra server sống hay chết: gọi một tool, vd `list_pages`. Không còn sentinel để soi.
- `2026-11-04d`: phát hiện **docs upstream lệch npm** — nhánh `main` trên GitHub đã tài liệu hoá **61 tool** (thêm `set_logpoints` và `configure_webauthn` qua `--categoryWebauthn`), nhưng `npm view chrome-devtools-mcp dist-tags` vẫn là `1.12.0` với **59 tool**. Probe xác nhận `1.12.0` **bỏ qua âm thầm** `--categoryWebauthn=true` (không lỗi, không thêm tool). Ghi cảnh báo vào §C.1b: đừng tin số tool trong docs, đối chiếu dist-tags.

- `2026-11-04e`: t?t hook chrome-devtools m?c d?nh (-985 tok/phi�n), xo� sentinel `[DG-CHROME-DEVTOOLS v1]`, t�ch changelog sang `docs/CHANGELOG.md`, n�n �5/�8. `CLAUDE.md` 60.365 -> ~42.400 chars (~16.300 -> ~11.400 tok).


- `2026-11-04c`: mở **đủ 59 tool** chrome-devtools-mcp (mặc định chỉ 32) bằng 6 flag — `--memoryDebugging`, `--categoryExtensions`, `--categoryExperimentalThirdParty`, `--categoryExperimentalWebmcp`, `--categoryPwa`, `--experimentalVision`. Mỗi flag đã probe riêng để xác định đúng số tool nó thêm (13/5/2/2/4/1 = 26), không suy từ docs. Hook `sessionStart` giờ **replay flag từ `.cursor/mcp.json`** khi probe `tools/list` thay vì chỉ truyền version — bật/tắt nhóm trong config là output tự theo. Sửa 2 bug phát hiện khi test: (1) probe đọc file args do `Set-Content -Encoding UTF8` của PS 5.1 ghi kèm BOM → `JSON.parse` chết, hook âm thầm rơi về danh sách docs; fix bằng strip `\uFEFF`; (2) patch tự động để lại dòng `if` trùng → PowerShell báo thiếu `}`, hook không xuất JSON.

- `2026-11-04b`: tích hợp **Chrome DevTools MCP** — clone `vendor/chrome-devtools-mcp` (gitignore, tải lại bằng `scripts/setup-chrome-devtools-mcp.ps1`), thêm `.cursor/mcp.json` đăng ký server `chrome-devtools` (npx, pin `1.12.0`), thêm hook `sessionStart` **riêng** `.cursor/hooks/chrome-devtools-mcp-session-start.ps1` (entry thứ 2 trong `hooks.json`, độc lập với superpowers). Hook probe `tools/list` thật thay vì tin docs — phát hiện docs liệt kê ~60 tool nhưng server mặc định chỉ expose **32**; nhóm Memory/Extensions/WebPWA/WebAuthn cần flag riêng. Cache tool list ở `.cursor/hooks/.chrome-devtools-tools-cache.json` (gitignore) để cold start 3.4s, warm 0.5s. 3 nhánh fail-open đã kiểm chứng: thiếu vendor / chưa đăng ký MCP / probe fail. Thêm §C.1b. **Bug quan trọng phát hiện sau khi user reload window:** server register nhưng kẹt `disconnected` — nguyên nhân là Cursor spawn stdio không qua shell, `npx` trên Windows là `.cmd` nên Node ≥20 từ chối spawn (CVE-2024-27980). Fix: bọc `cmd /c` trong `.cursor/mcp.json`; hook giờ tự cảnh báo nếu thiếu wrapper.

- `2026-11-04`: rebuild knowledge graph về đúng `HEAD` (`0914c9c`) — `graphify update .` báo "no code-graph topology changes" nên không ghi lại output, phải chạy `graphify extract . --code-only` (thay 50 node, prune 1) rồi `cluster-only --no-label` + `apply-labels.py` + `cluster-only --no-label` để giữ tên community đã curate. Số liệu sau rebuild: xem `graphify-out/GRAPH_REPORT.md` §Summary (không chép lại vào đây). Đồng thời bỏ hẳn số liệu hard-code khỏi §C.1 — hook `sessionStart` đã in "Graph status" mỗi phiên từ `graph.json`, nên không cần cập nhật tay nữa (drift Check 1 tự no-op khi §C.1 không còn claim). Ghi chú: `apply-labels.py` phải chạy bằng python của venv uv (`%APPDATA%\uv\tools\graphifyy\Scripts\python.exe`), vì `python` hệ thống (3.14) thiếu module `graphify`.

- `2026-11-02e`: hook `sessionStart` thêm **drift detector** — đối chiếu number nodes/edges/communities trong CLAUDE.md, danh sách skill bảng §C.2, `DG_VERSION`, và đường dẫn file theme với thực tế repo; **chỉ inject khi lệch** (khớp thì im lặng, 0 token). Quyết định **không** chép lại CLAUDE.md/rules vào hook vì Cursor đã tự nạp sẵn (~14.300 tokens/phiên) — inject lại là nhân đôi vô ích. Sửa false positive: check skill count ban đầu đếm cả 34 thư mục (15 superpowers + 19 WordPress) thay vì chỉ danh sách được nêu trong CLAUDE.md.

- `2026-11-02d`: mở rộng hook `sessionStart` — ngoài bootstrap superpowers, hook giờ tự quét `.cursor/skills/*/SKILL.md` để inject **catalog 34 skill** (cắt description 95 ký tự) và đọc `graphify-out/graph.json` để inject **con trỏ knowledge graph** kèm số nodes/edges/communities + cảnh báo stale (so `built_at_commit` với `HEAD`). Fail-open từng section. Sửa 2 bug phát hiện khi test: (1) backtick trong here-string bị PowerShell escape → mất ký tự (`assets/js` thành `ssets/js`), (2) mojibake `â€"` do PS 5.1 đọc file không BOM theo ANSI → script nay thuần ASCII + transliterate output.

- `2026-11-02c`: thêm hook `sessionStart` (`.cursor/hooks.json` + `.cursor/hooks/superpowers-session-start.ps1`) để inject bootstrap superpowers vào system context mỗi conversation mới — bám sát cơ chế của upstream thay vì chỉ dựa vào rule always-on. Sửa lại ghi chú sai trước đó ("Cursor không có hook SessionStart").

- `2026-11-02b`: thêm §C.2 — **superpowers** (obra/superpowers v7.0.1, MIT). Copy 15 process skills vào `.cursor/skills/` (mirror `.claude/` + `.agents/`), thêm rule always-on `.cursor/rules/superpowers.mdc` (thay hook `sessionStart` của upstream). Ghi rõ adaptation: repo không có test suite → kiểm chứng bằng `php -l` + Playwright thay cho `npm test`.

- `2026-11-02`: thêm §C.1 — **graphify knowledge graph**. Cài `graphifyy` 0.9.100 qua uv tool, đăng ký skill vào `.agents/skills/graphify/` (mirror sang `.cursor/skills/` + `.claude/skills/`), thêm rule always-on `.cursor/rules/graphify.mdc`, `.graphifyignore` giới hạn corpus vào code theme, script `scripts/graphify/apply-labels.py` + `curated-labels.json` để giữ tên community qua mỗi lần re-cluster.

- `2026-10-27b`: refactored sections 5b/5d/5e/5g/9 — Generalized rules (nói chung không nói riêng). §5b (email) → "Template Organization" (tất cả template). §5d CSS + JS organization combined (formerly 5d/5f). §5e inline styles → "Styling Strategy" (general rule). §5g selectors → removed "WC-specific" language, added general principle. §9 → "Interactivity & Dynamic Behavior" (remove Motion-specific focus, added general JS patterns + Motion as option).

- `2026-10-27a`: thêm §8c — **Component Style Isolation**: rule cứng prevent style pollution across pages (ALL styles, not just gradient). LUÔN tạo token riêng cho component-local styles, KHÔNG dùng global theme tokens. 3-layer token system (Primitive → Semantic → Component), decision tree, scope matrix, comprehensive checklist.

- `2026-10-02`: thêm §8b — Tailwind Forms plugin override: nguyên nhân specificity tie + source order khi Tailwind nạp qua `<script>`, cách debug bằng console, cách khắc phục bằng tăng specificity qua wrapper class (không dùng `!important`).

- `2026-09-29`: thêm §8a — Design Token System: quy tắc cứng về token-first approach, cấm hard-code, visual regression workflow, refactor checklist.

- `2026-08-24`: Refactor toàn bộ để tương thích với `.cursor/skills` ecosystem; thêm skill compatibility matrix; làm rõ project type (classic theme trong full site repo); loại bỏ mâu thuẫn về React/build step.

- `2026-08-10`: thêm §5g — selector phải khớp DOM thật; visual parity rule.

- `2026-08-09`: refactor — tách cart helpers, approval handler, legal pages.
